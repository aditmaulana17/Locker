<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratMasukRequest;
use App\Models\ActivityLog;
use App\Models\KategoriSurat;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SuratMasukController extends Controller
{
    /**
     * Status surat masuk yang valid.
     */
    private const STATUS_OPTIONS = [
        'baru',
        'diproses',
        'didisposisikan',
        'selesai',
        'diarsipkan',
    ];

    /**
     * Disk storage yang digunakan aplikasi.
     *
     * Jika filesystem.default = supabase,
     * maka file disimpan ke disk supabase.
     *
     * Selain itu gunakan public.
     */
    private function getStorageDisk(): string
    {
        $disk = strtolower(
            trim(
                (string) config(
                    'filesystems.default',
                    'public'
                )
            )
        );

        return $disk === 'supabase'
            ? 'supabase'
            : 'public';
    }

    /**
     * Mendapatkan filesystem adapter.
     */
    private function storage(): FilesystemAdapter
    {
        return Storage::disk(
            $this->getStorageDisk()
        );
    }

    /**
     * Memastikan user boleh mengelola surat.
     *
     * Admin dan pimpinan dapat:
     * - create
     * - edit
     * - update
     * - delete
     */
    private function ensureCanManage(): void
    {
        $this->ensureCanManageSurat();
    }

    /**
     * Memastikan user boleh melihat surat.
     *
     * Staff hanya dapat melihat surat yang
     * mempunyai disposisi kepada dirinya.
     */
    private function ensureCanView(
        SuratMasuk $suratMasuk
    ): void {
        $this->ensureAuthenticated();

        if (!$this->isStaff()) {
            return;
        }

        $userId = (int) Auth::id();

        $allowed = $suratMasuk
            ->disposisi()
            ->where(
                'kepada_user_id',
                $userId
            )
            ->exists();

        abort_unless(
            $allowed,
            403,
            'Akses ditolak. Anda tidak memiliki hak akses untuk melihat surat ini.'
        );
    }

    /**
     * Menampilkan daftar surat masuk.
     */
    public function index(Request $request)
    {
        $this->ensureAuthenticated();

        /*
         * =========================================================
         * FILTER KATEGORI
         * =========================================================
         *
         * Mendukung:
         * kategori_surat_id[]
         * kategori_id[]
         */
        $rawKategoriIds = $request->input(
            'kategori_surat_id',
            $request->input(
                'kategori_id',
                []
            )
        );

        if (
            is_scalar($rawKategoriIds) &&
            trim((string) $rawKategoriIds) !== ''
        ) {
            $rawKategoriIds = [
                $rawKategoriIds,
            ];
        }

        $kategoriIds = collect(
            $rawKategoriIds
        )
            ->flatten()
            ->filter(
                fn ($id) =>
                    is_scalar($id) &&
                    is_numeric($id) &&
                    (int) $id > 0
            )
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values()
            ->all();

        /*
         * =========================================================
         * FILTER STATUS
         * =========================================================
         */
        $rawStatuses = $request->input(
            'status',
            []
        );

        if (!is_array($rawStatuses)) {
            $rawStatuses = [
                $rawStatuses,
            ];
        }

        $statuses = collect(
            $rawStatuses
        )
            ->filter(
                fn ($status) =>
                    is_scalar($status)
            )
            ->map(
                fn ($status) =>
                    strtolower(
                        trim(
                            (string) $status
                        )
                    )
            )
            ->filter(
                fn ($status) =>
                    in_array(
                        $status,
                        self::STATUS_OPTIONS,
                        true
                    )
            )
            ->unique()
            ->values()
            ->all();

        /*
         * =========================================================
         * QUERY DASAR
         * =========================================================
         */
        $query = SuratMasuk::query()
            ->with([
                'kategori',
                'penerima',
            ]);

        /*
         * =========================================================
         * SEARCH
         * =========================================================
         */
        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        if ($search !== '') {
            $keyword = "%{$search}%";

            $query->where(
                function (Builder $q) use ($keyword): void {
                    $q->where(
                        'nomor_agenda',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'nomor_surat',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'pengirim',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'perihal',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'ringkasan',
                        'like',
                        $keyword
                    )
                    ->orWhereHas(
                        'kategori',
                        function (
                            Builder $kategori
                        ) use ($keyword): void {
                            $kategori
                                ->where(
                                    'nama_kategori',
                                    'like',
                                    $keyword
                                )
                                ->orWhere(
                                    'kode',
                                    'like',
                                    $keyword
                                )
                                ->orWhere(
                                    'sifat',
                                    'like',
                                    $keyword
                                );
                        }
                    );
                }
            );
        }

        /*
         * =========================================================
         * FILTER KATEGORI
         * =========================================================
         */
        if (!empty($kategoriIds)) {
            $query->whereIn(
                'kategori_surat_id',
                $kategoriIds
            );
        }

        /*
         * =========================================================
         * FILTER STATUS
         * =========================================================
         */
        if (!empty($statuses)) {
            $query->whereIn(
                'status',
                $statuses
            );
        }

        /*
         * =========================================================
         * FILTER TANGGAL
         * =========================================================
         */
        $dariTanggal = trim(
            (string) $request->input(
                'dari_tanggal',
                ''
            )
        );

        $sampaiTanggal = trim(
            (string) $request->input(
                'sampai_tanggal',
                ''
            )
        );

        $validDariTanggal = $this->isValidDate(
            $dariTanggal
        );

        $validSampaiTanggal = $this->isValidDate(
            $sampaiTanggal
        );

        /*
         * Tukar tanggal jika terbalik.
         */
        if (
            $validDariTanggal &&
            $validSampaiTanggal &&
            $dariTanggal > $sampaiTanggal
        ) {
            [
                $dariTanggal,
                $sampaiTanggal,
            ] = [
                $sampaiTanggal,
                $dariTanggal,
            ];
        }

        if ($validDariTanggal) {
            $query->whereDate(
                'tanggal_terima',
                '>=',
                $dariTanggal
            );
        }

        if ($validSampaiTanggal) {
            $query->whereDate(
                'tanggal_terima',
                '<=',
                $sampaiTanggal
            );
        }

        /*
         * =========================================================
         * BATAS DATA STAFF
         * =========================================================
         *
         * Staff hanya melihat surat yang memiliki
         * disposisi kepada dirinya.
         */
        if ($this->isStaff()) {
            $query->untukStaff(
                (int) Auth::id()
            );
        }

        /*
         * =========================================================
         * PAGINATION
         * =========================================================
         */
        $suratMasuks = $query
            ->orderByDesc(
                'tanggal_terima'
            )
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        /*
         * =========================================================
         * DATA KATEGORI
         * =========================================================
         */
        $kategoris = KategoriSurat::query()
            ->orderBy(
                'nama_kategori'
            )
            ->get();

        return view(
            'surat_masuk.index',
            compact(
                'suratMasuks',
                'kategoris'
            )
        );
    }

    /**
     * Form tambah surat masuk.
     */
    public function create()
    {
        $this->ensureCanManage();

        $kategoris = KategoriSurat::query()
            ->orderBy(
                'nama_kategori'
            )
            ->get();

        $nomorAgenda =
            SuratMasuk::generateNomorAgenda();

        return view(
            'surat_masuk.create',
            compact(
                'kategoris',
                'nomorAgenda'
            )
        );
    }

    /**
     * Menyimpan surat masuk baru.
     */
    public function store(
        SuratMasukRequest $request
    ) {
        $this->ensureCanManage();

        $data = $request->validated();

        $diskName =
            $this->getStorageDisk();

        $uploadedPath = null;

        DB::beginTransaction();

        try {
            /*
             * =====================================================
             * NOMOR AGENDA
             * =====================================================
             */
            $nomorInput = trim(
                (string) $request->input(
                    'nomor_agenda',
                    ''
                )
            );

            if (
                $nomorInput === '' ||
                SuratMasuk::where(
                    'nomor_agenda',
                    $nomorInput
                )->exists()
            ) {
                $data['nomor_agenda'] =
                    SuratMasuk::generateNomorAgenda();
            } else {
                $data['nomor_agenda'] =
                    $nomorInput;
            }

            /*
             * =====================================================
             * USER PENERIMA
             * =====================================================
             */
            $data['diterima_oleh'] =
                (int) Auth::id();

            /*
             * =====================================================
             * STATUS
             * =====================================================
             */
            $status = strtolower(
                trim(
                    (string) (
                        $data['status']
                        ?? 'baru'
                    )
                )
            );

            $data['status'] =
                in_array(
                    $status,
                    self::STATUS_OPTIONS,
                    true
                )
                    ? $status
                    : 'baru';

            /*
             * =====================================================
             * FILE / KAMERA
             * =====================================================
             */
            if (
                $request->filled(
                    'captured_image'
                )
            ) {
                $uploadedPath =
                    $this->uploadBase64Image(
                        (string) $request->input(
                            'captured_image'
                        )
                    );

                $data['lampiran_file'] =
                    $uploadedPath;
            } elseif (
                $request->hasFile(
                    'lampiran_file'
                )
            ) {
                $uploadedPath =
                    $this->storeUploadedFile(
                        $request->file(
                            'lampiran_file'
                        ),
                        $diskName
                    );

                $data['lampiran_file'] =
                    $uploadedPath;
            }

            /*
             * Field kamera tidak disimpan.
             */
            unset(
                $data['captured_image']
            );

            /*
             * =====================================================
             * SIMPAN DATABASE
             * =====================================================
             */
            $surat =
                SuratMasuk::create(
                    $data
                );

            /*
             * =====================================================
             * ACTIVITY LOG
             * =====================================================
             */
            $this->logActivity(
                'create',
                'surat_masuk',
                sprintf(
                    'Menambah surat masuk %s - %s',
                    $surat->nomor_agenda,
                    $surat->perihal
                )
            );

            DB::commit();

            return redirect()
                ->route(
                    'surat-masuk.index'
                )
                ->with(
                    'success',
                    'Surat masuk berhasil dicatat dengan nomor agenda ' .
                    $surat->nomor_agenda
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            /*
             * Hapus file jika database gagal.
             */
            if ($uploadedPath) {
                $this->deleteStorageFile(
                    $uploadedPath,
                    $diskName
                );
            }

            Log::error(
                'Gagal menyimpan Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'user_id' =>
                        Auth::id(),

                    'disk' =>
                        $diskName,

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menyimpan surat masuk: ' .
                    $e->getMessage()
                );
        }
    }

    /**
     * Detail surat masuk.
     */
    public function show(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanView(
            $suratMasuk
        );

        $suratMasuk->load([
            'kategori',
            'penerima',
            'disposisi.dari',
            'disposisi.kepada',
        ]);

        /*
         * Staff aktif.
         */
        $daftarStaf =
            User::aktif()
                ->staff()
                ->where(
                    'id',
                    '!=',
                    Auth::id()
                )
                ->orderBy('name')
                ->get();

        $fileUrl =
            $this->getFileUrl(
                $suratMasuk->lampiran_file
            );

        return view(
            'surat_masuk.show',
            compact(
                'suratMasuk',
                'fileUrl',
                'daftarStaf'
            )
        );
    }

    /**
     * Endpoint disposisi legacy.
     */
    public function storeDisposisi(
        Request $request,
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanManage();

        $validated = $request->validate(
            [
                'tujuan_user_id' => [
                    'required',
                    'integer',
                    Rule::exists(
                        'users',
                        'id'
                    )->where(
                        function ($query) {
                            $query
                                ->where(
                                    'is_active',
                                    true
                                )
                                ->whereRaw(
                                    'LOWER(TRIM(role)) = ?',
                                    ['staff']
                                );
                        }
                    ),
                ],

                'instruksi' => [
                    'required',
                    'string',
                    'max:5000',
                ],

                'batas_waktu' => [
                    'nullable',
                    'date',
                ],
            ],
            [
                'tujuan_user_id.required' =>
                    'Staf tujuan wajib dipilih.',

                'tujuan_user_id.exists' =>
                    'Staf tujuan tidak valid atau tidak aktif.',

                'instruksi.required' =>
                    'Instruksi disposisi wajib diisi.',

                'instruksi.max' =>
                    'Instruksi disposisi maksimal 5.000 karakter.',

                'batas_waktu.date' =>
                    'Batas waktu disposisi tidak valid.',
            ]
        );

        $tujuanUserId =
            (int) $validated[
                'tujuan_user_id'
            ];

        if (
            $tujuanUserId ===
            (int) Auth::id()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'tujuan_user_id' =>
                        'Anda tidak dapat mengirim disposisi kepada diri sendiri.',
                ]);
        }

        DB::beginTransaction();

        try {
            $instruksi = trim(
                (string) $validated[
                    'instruksi'
                ]
            );

            $disposisiData = [
                'surat_masuk_id' =>
                    $suratMasuk->id,

                'dari_user_id' =>
                    (int) Auth::id(),

                'kepada_user_id' =>
                    $tujuanUserId,

                'instruksi' =>
                    $instruksi,

                'status' =>
                    'menunggu',

                'batas_waktu' =>
                    $validated[
                        'batas_waktu'
                    ] ?? null,
            ];

            /*
             * Kompatibilitas isi_disposisi.
             */
            if (
                Schema::hasColumn(
                    'disposisis',
                    'isi_disposisi'
                )
            ) {
                $disposisiData[
                    'isi_disposisi'
                ] = $instruksi;
            }

            /*
             * Sifat jika kolom tersedia.
             */
            if (
                Schema::hasColumn(
                    'disposisis',
                    'sifat'
                )
            ) {
                $disposisiData[
                    'sifat'
                ] = 'biasa';
            }

            $suratMasuk
                ->disposisi()
                ->create(
                    $disposisiData
                );

            $suratMasuk->update([
                'status' =>
                    'didisposisikan',
            ]);

            $this->logActivity(
                'disposisi',
                'surat_masuk',
                'Mendisposisikan surat masuk ' .
                $suratMasuk->nomor_agenda
            );

            DB::commit();

            return back()->with(
                'success',
                'Disposisi surat berhasil dikirim ke staff yang dituju.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error(
                'Gagal melakukan disposisi Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'surat_id' =>
                        $suratMasuk->id,

                    'user_id' =>
                        Auth::id(),

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal memproses disposisi: ' .
                    $e->getMessage()
                );
        }
    }

    /**
     * Form edit surat masuk.
     */
    public function edit(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanManage();

        $kategoris =
            KategoriSurat::query()
                ->orderBy(
                    'nama_kategori'
                )
                ->get();

        return view(
            'surat_masuk.edit',
            compact(
                'suratMasuk',
                'kategoris'
            )
        );
    }

    /**
     * Update surat masuk.
     *
     * File lama tetap digunakan jika user
     * tidak memilih file baru.
     */
    public function update(
        SuratMasukRequest $request,
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanManage();

        $data =
            $request->validated();

        $diskName =
            $this->getStorageDisk();

        $oldFile =
            $suratMasuk->lampiran_file;

        $newFile = null;

        DB::beginTransaction();

        try {
            /*
             * =====================================================
             * STATUS
             * =====================================================
             */
            if (isset($data['status'])) {
                $status =
                    strtolower(
                        trim(
                            (string)
                                $data['status']
                        )
                    );

                if (
                    in_array(
                        $status,
                        self::STATUS_OPTIONS,
                        true
                    )
                ) {
                    $data['status'] =
                        $status;
                }
            }

            /*
             * =====================================================
             * FILE KAMERA
             * =====================================================
             */
            if (
                $request->filled(
                    'captured_image'
                )
            ) {
                $newFile =
                    $this->uploadBase64Image(
                        (string)
                            $request->input(
                                'captured_image'
                            )
                    );

                $data['lampiran_file'] =
                    $newFile;
            }

            /*
             * =====================================================
             * FILE BARU
             * =====================================================
             */
            elseif (
                $request->hasFile(
                    'lampiran_file'
                )
            ) {
                $file =
                    $request->file(
                        'lampiran_file'
                    );

                $newFile =
                    $this->storeUploadedFile(
                        $file,
                        $diskName
                    );

                $data['lampiran_file'] =
                    $newFile;
            }

            /*
             * =====================================================
             * TIDAK ADA FILE BARU
             * =====================================================
             *
             * Biarkan nilai database yang lama tetap digunakan.
             */
            else {
                unset(
                    $data['lampiran_file']
                );
            }

            /*
             * Field kamera tidak disimpan.
             */
            unset(
                $data['captured_image']
            );

            /*
             * =====================================================
             * UPDATE DATABASE
             * =====================================================
             */
            $suratMasuk->update(
                $data
            );

            $this->logActivity(
                'update',
                'surat_masuk',
                'Mengubah surat masuk ' .
                $suratMasuk->nomor_agenda
            );

            DB::commit();

            /*
             * =====================================================
             * HAPUS FILE LAMA
             * =====================================================
             *
             * Hanya setelah database berhasil.
             */
            if (
                $newFile &&
                $oldFile &&
                $oldFile !== $newFile
            ) {
                $this->deleteStorageFile(
                    $oldFile,
                    $diskName
                );
            }

            return redirect()
                ->route(
                    'surat-masuk.index'
                )
                ->with(
                    'success',
                    'Surat masuk berhasil diperbarui.'
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            /*
             * Jika file baru sempat tersimpan
             * tetapi database gagal, hapus file baru.
             */
            if (
                $newFile &&
                $newFile !== $oldFile
            ) {
                $this->deleteStorageFile(
                    $newFile,
                    $diskName
                );
            }

            Log::error(
                'Gagal memperbarui Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'surat_id' =>
                        $suratMasuk->id,

                    'user_id' =>
                        Auth::id(),

                    'disk' =>
                        $diskName,

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal memperbarui surat masuk: ' .
                    $e->getMessage()
                );
        }
    }

    /**
     * Soft delete surat masuk.
     */
    public function destroy(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanManage();

        $nomor =
            $suratMasuk->nomor_agenda;

        try {
            DB::transaction(
                function () use (
                    $suratMasuk,
                    $nomor
                ): void {
                    $suratMasuk->delete();

                    $this->logActivity(
                        'delete',
                        'surat_masuk',
                        'Menghapus surat masuk ' .
                        $nomor
                    );
                }
            );

            return back()->with(
                'success',
                'Surat masuk berhasil dipindahkan ke arsip sampah.'
            );
        } catch (\Throwable $e) {
            Log::error(
                'Gagal menghapus Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'surat_id' =>
                        $suratMasuk->id,

                    'user_id' =>
                        Auth::id(),
                ]
            );

            return back()->with(
                'error',
                'Gagal menghapus surat masuk: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Cetak label surat masuk.
     */
    public function cetakLabel(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanView(
            $suratMasuk
        );

        $suratMasuk->load(
            'kategori'
        );

        return view(
            'surat_masuk.label',
            compact(
                'suratMasuk'
            )
        );
    }

    /**
     * Preview lampiran surat masuk.
     */
    public function previewLampiran(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanView(
            $suratMasuk
        );

        $file =
            $suratMasuk->lampiran_file;

        if (!$file) {
            abort(
                404,
                'File lampiran tidak ditemukan.'
            );
        }

        /*
         * Jika database menyimpan URL langsung.
         */
        if (
            filter_var(
                $file,
                FILTER_VALIDATE_URL
            )
        ) {
            return redirect()->away(
                $file
            );
        }

        $diskName =
            $this->getStorageDisk();

        try {
            $disk =
                $this->storage();

            if (!$disk->exists($file)) {
                abort(
                    404,
                    'File lampiran tidak ditemukan di storage.'
                );
            }

            /*
             * Supabase/private storage.
             */
            if (
                $diskName ===
                'supabase'
            ) {
                $url =
                    $disk->temporaryUrl(
                        $file,
                        now()->addMinutes(30)
                    );

                return redirect()->away(
                    $url
                );
            }

            /*
             * Local/public.
             */
            return $disk->response(
                $file
            );
        } catch (
            \Symfony\Component\HttpKernel\Exception\HttpException $e
        ) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error(
                'Gagal preview lampiran Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $file,

                    'disk' =>
                        $diskName,

                    'surat_id' =>
                        $suratMasuk->id,
                ]
            );

            return back()->with(
                'error',
                'Gagal membuka lampiran file: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Download lampiran surat masuk.
     */
    public function downloadLampiran(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanView(
            $suratMasuk
        );

        $file =
            $suratMasuk->lampiran_file;

        if (!$file) {
            abort(
                404,
                'File lampiran tidak ditemukan.'
            );
        }

        /*
         * Jika database menyimpan URL langsung.
         */
        if (
            filter_var(
                $file,
                FILTER_VALIDATE_URL
            )
        ) {
            return redirect()->away(
                $file
            );
        }

        $diskName =
            $this->getStorageDisk();

        try {
            $disk =
                $this->storage();

            if (!$disk->exists($file)) {
                abort(
                    404,
                    'File lampiran tidak ditemukan di storage.'
                );
            }

            /*
             * Supabase.
             */
            if (
                $diskName ===
                'supabase'
            ) {
                $url =
                    $disk->temporaryUrl(
                        $file,
                        now()->addMinutes(30)
                    );

                return redirect()->away(
                    $url
                );
            }

            /*
             * Local/public.
             */
            return $disk->download(
                $file,
                basename($file)
            );
        } catch (
            \Symfony\Component\HttpKernel\Exception\HttpException $e
        ) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error(
                'Gagal download lampiran Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $file,

                    'disk' =>
                        $diskName,

                    'surat_id' =>
                        $suratMasuk->id,
                ]
            );

            return back()->with(
                'error',
                'Gagal mengunduh lampiran file: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Cetak disposisi surat masuk.
     */
    public function cetakDisposisi(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanView(
            $suratMasuk
        );

        $suratMasuk->load([
            'kategori',
            'disposisi.dari',
            'disposisi.kepada',
        ]);

        return view(
            'surat_masuk.disposisi_pdf',
            compact(
                'suratMasuk'
            )
        );
    }

    /**
     * Menyimpan UploadedFile ke storage.
     *
     * Digunakan untuk:
     * - PDF
     * - JPG
     * - JPEG
     * - PNG
     * - WEBP
     */
    private function storeUploadedFile(
        ?UploadedFile $file,
        string $diskName
    ): string {
        if (!$file) {
            throw new \RuntimeException(
                'File lampiran tidak ditemukan.'
            );
        }

        if (!$file->isValid()) {
            throw new \RuntimeException(
                $this->getUploadErrorMessage(
                    $file
                )
            );
        }

        /*
         * MIME aktual dari file.
         */
        $mime = strtolower(
            (string) $file->getMimeType()
        );

        /*
         * Pemetaan MIME → extension.
         */
        $extension = match ($mime) {
            'application/pdf' => 'pdf',

            'image/jpeg',
            'image/jpg' => 'jpg',

            'image/png' => 'png',

            'image/webp' => 'webp',

            default => strtolower(
                $file->getClientOriginalExtension()
            ),
        };

        /*
         * Pastikan extension tetap salah satu
         * yang memang didukung aplikasi.
         */
        if (
            !in_array(
                $extension,
                [
                    'pdf',
                    'jpg',
                    'jpeg',
                    'png',
                    'webp',
                ],
                true
            )
        ) {
            throw new \RuntimeException(
                'Format file tidak didukung. Gunakan PDF, JPG, JPEG, PNG, atau WEBP.'
            );
        }

        /*
         * Nama file unik.
         */
        $fileName =
            'surat-masuk_' .
            now()->format('YmdHis') .
            '_' .
            Str::lower(
                Str::random(12)
            ) .
            '.' .
            $extension;

        $directory =
            'lampiran/surat_masuk';

        /*
         * Opsi storage.
         */
        $options = [
            'ContentType' =>
                $mime !== ''
                    ? $mime
                    : $file->getClientMimeType(),
        ];

        /*
         * Supabase/private storage.
         */
        if ($diskName === 'supabase') {
            $options['visibility'] =
                'private';
        }

        $disk =
            Storage::disk(
                $diskName
            );

        /*
         * Gunakan putFileAs agar extension,
         * MIME, dan nama file ditentukan secara
         * eksplisit.
         */
        $stored =
            $disk->putFileAs(
                $directory,
                $file,
                $fileName,
                $options
            );

        if (!$stored) {
            throw new \RuntimeException(
                'File "' .
                $file->getClientOriginalName() .
                '" gagal disimpan ke storage.'
            );
        }

        return $stored;
    }

    /**
     * Mendapatkan pesan error upload PHP.
     */
    private function getUploadErrorMessage(
        UploadedFile $file
    ): string {
        return match ($file->getError()) {
            UPLOAD_ERR_INI_SIZE,
            UPLOAD_ERR_FORM_SIZE =>
                'Ukuran file terlalu besar. Maksimal 15 MB.',

            UPLOAD_ERR_PARTIAL =>
                'File hanya terupload sebagian. Silakan coba lagi.',

            UPLOAD_ERR_NO_FILE =>
                'Tidak ada file yang dipilih.',

            UPLOAD_ERR_NO_TMP_DIR =>
                'Folder temporary upload PHP tidak tersedia.',

            UPLOAD_ERR_CANT_WRITE =>
                'Server gagal menulis file upload.',

            UPLOAD_ERR_EXTENSION =>
                'Upload file dihentikan oleh konfigurasi PHP.',

            default =>
                'File gagal diupload. Silakan coba lagi.',
        };
    }

    /**
     * Upload hasil kamera Base64.
     */
    private function uploadBase64Image(
        string $base64String
    ): string {
        $base64String =
            trim($base64String);

        if ($base64String === '') {
            throw new \RuntimeException(
                'Data gambar kamera kosong.'
            );
        }

        /*
         * Format yang diterima:
         * data:image/jpeg;base64,...
         * data:image/jpg;base64,...
         * data:image/png;base64,...
         * data:image/webp;base64,...
         */
        if (
            !preg_match(
                '/^data:image\/(png|jpeg|jpg|webp);base64,/i',
                $base64String
            )
        ) {
            throw new \RuntimeException(
                'Format hasil scan kamera tidak valid.'
            );
        }

        $commaPosition =
            strpos(
                $base64String,
                ','
            );

        if (
            $commaPosition === false
        ) {
            throw new \RuntimeException(
                'Data gambar kamera tidak valid.'
            );
        }

        /*
         * Ambil MIME dari prefix data URI.
         */
        $header =
            substr(
                $base64String,
                0,
                $commaPosition
            );

        if (
            !preg_match(
                '/^data:(image\/(?:png|jpeg|jpg|webp));base64$/i',
                $header,
                $matches
            )
        ) {
            throw new \RuntimeException(
                'MIME gambar kamera tidak valid.'
            );
        }

        $mime =
            strtolower(
                $matches[1]
            );

        /*
         * Normalisasi image/jpg ke image/jpeg.
         */
        if ($mime === 'image/jpg') {
            $mime = 'image/jpeg';
        }

        $encoded =
            substr(
                $base64String,
                $commaPosition + 1
            );

        $decodedData =
            base64_decode(
                $encoded,
                true
            );

        if (
            $decodedData === false ||
            $decodedData === ''
        ) {
            throw new \RuntimeException(
                'Gagal memproses gambar kamera. Data Base64 tidak valid.'
            );
        }

        /*
         * Maksimal 15 MB.
         */
        if (
            strlen($decodedData) >
            15 * 1024 * 1024
        ) {
            throw new \RuntimeException(
                'Ukuran hasil scan kamera terlalu besar. Maksimal 15 MB.'
            );
        }

        /*
         * Pastikan benar-benar gambar.
         */
        $imageInfo =
            @getimagesizefromstring(
                $decodedData
            );

        if ($imageInfo === false) {
            throw new \RuntimeException(
                'Data kamera bukan merupakan gambar yang valid.'
            );
        }

        /*
         * MIME aktual.
         */
        $actualMime =
            strtolower(
                (string) (
                    $imageInfo['mime']
                    ?? ''
                )
            );

        if (
            $actualMime ===
            'image/jpg'
        ) {
            $actualMime =
                'image/jpeg';
        }

        if (
            !in_array(
                $actualMime,
                [
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ],
                true
            )
        ) {
            throw new \RuntimeException(
                'Jenis gambar hasil scan tidak didukung.'
            );
        }

        /*
         * Extension berdasarkan MIME aktual.
         */
        $extension = match ($actualMime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg',
        };

        /*
         * Nama file unik.
         */
        $fileName =
            'scan_' .
            now()->format('YmdHis') .
            '_' .
            Str::random(12) .
            '.' .
            $extension;

        $path =
            'lampiran/surat_masuk/' .
            $fileName;

        $diskName =
            $this->getStorageDisk();

        $disk =
            Storage::disk(
                $diskName
            );

        $options = [
            'ContentType' =>
                $actualMime,
        ];

        if (
            $diskName ===
            'supabase'
        ) {
            $options['visibility'] =
                'private';
        }

        $saved =
            $disk->put(
                $path,
                $decodedData,
                $options
            );

        if (!$saved) {
            throw new \RuntimeException(
                'Gagal menyimpan hasil scan kamera ke storage.'
            );
        }

        return $path;
    }

    /**
     * Membuat URL lampiran.
     */
    private function getFileUrl(
        ?string $file
    ): ?string {
        if (!$file) {
            return null;
        }

        /*
         * URL langsung.
         */
        if (
            filter_var(
                $file,
                FILTER_VALIDATE_URL
            )
        ) {
            return $file;
        }

        $diskName =
            $this->getStorageDisk();

        try {
            $disk =
                $this->storage();

            if (!$disk->exists($file)) {
                return null;
            }

            /*
             * Supabase/private.
             */
            if (
                $diskName ===
                'supabase'
            ) {
                return $disk->temporaryUrl(
                    $file,
                    now()->addMinutes(30)
                );
            }

            /*
             * Public/local.
             */
            return $disk->url(
                $file
            );
        } catch (\Throwable $e) {
            Log::warning(
                'Gagal membuat URL lampiran Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $file,

                    'disk' =>
                        $diskName,
                ]
            );

            return null;
        }
    }

    /**
     * Menghapus file dari storage.
     */
    private function deleteStorageFile(
        ?string $file,
        ?string $diskName = null
    ): void {
        if (
            !$file ||
            filter_var(
                $file,
                FILTER_VALIDATE_URL
            )
        ) {
            return;
        }

        $diskName ??=
            $this->getStorageDisk();

        try {
            $disk =
                Storage::disk(
                    $diskName
                );

            if (
                $disk->exists($file)
            ) {
                $disk->delete(
                    $file
                );
            }
        } catch (\Throwable $e) {
            Log::warning(
                'Gagal menghapus file Surat Masuk dari storage.',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $file,

                    'disk' =>
                        $diskName,
                ]
            );
        }
    }

    /**
     * Validasi tanggal YYYY-MM-DD.
     */
    private function isValidDate(
        ?string $date
    ): bool {
        if ($date === null) {
            return false;
        }

        $date =
            trim($date);

        if (
            !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $date
            )
        ) {
            return false;
        }

        [
            $year,
            $month,
            $day,
        ] = array_map(
            'intval',
            explode(
                '-',
                $date
            )
        );

        return checkdate(
            $month,
            $day,
            $year
        );
    }

    /**
     * Activity log.
     */
    private function logActivity(
        string $action,
        string $module,
        string $description
    ): void {
        try {
            if (
                class_exists(
                    ActivityLog::class
                ) &&
                method_exists(
                    ActivityLog::class,
                    'catat'
                )
            ) {
                ActivityLog::catat(
                    $action,
                    $module,
                    $description
                );
            }
        } catch (\Throwable $e) {
            Log::warning(
                'Gagal mencatat Activity Log Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'action' =>
                        $action,

                    'module' =>
                        $module,
                ]
            );
        }
    }
}