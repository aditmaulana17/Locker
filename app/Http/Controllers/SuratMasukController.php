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
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

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
     * Extension file yang didukung.
     */
    private const ALLOWED_FILE_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
    ];

    /**
     * Ukuran maksimum file = 15 MB.
     */
    private const MAX_FILE_SIZE = 15 * 1024 * 1024;

    /**
     * Mendapatkan disk storage aktif.
     *
     * Jika filesystem.default = supabase,
     * maka gunakan disk supabase.
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
     * Mendapatkan adapter filesystem.
     */
    private function storage(): FilesystemAdapter
    {
        return Storage::disk(
            $this->getStorageDisk()
        );
    }

    /**
     * Memastikan user dapat melihat surat.
     *
     * Method ensureAuthenticated() dan isStaff()
     * diwarisi dari Controller.php.
     *
     * Admin dan pimpinan dapat melihat semua surat.
     * Staff hanya dapat melihat surat yang didisposisikan kepadanya.
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
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        */

        $rawKategoriIds = $request->input(
            'kategori_surat_id',
            $request->input(
                'kategori_id',
                []
            )
        );

        if (
            is_scalar($rawKategoriIds)
            && trim((string) $rawKategoriIds) !== ''
        ) {
            $rawKategoriIds = [
                $rawKategoriIds,
            ];
        }

        if (!is_array($rawKategoriIds)) {
            $rawKategoriIds = [];
        }

        $kategoriIds = collect(
            $rawKategoriIds
        )
            ->flatten()
            ->filter(
                fn ($id) =>
                    is_scalar($id)
                    && is_numeric($id)
                    && (int) $id > 0
            )
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | QUERY
        |--------------------------------------------------------------------------
        */

        $query = SuratMasuk::query()
            ->with([
                'kategori',
                'penerima',
            ]);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
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
                            ) use (
                                $keyword
                            ): void {
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
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        */

        if (!empty($kategoriIds)) {
            $query->whereIn(
                'kategori_surat_id',
                $kategoriIds
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        if (!empty($statuses)) {
            $query->whereIn(
                'status',
                $statuses
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL
        |--------------------------------------------------------------------------
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

        $validDariTanggal =
            $this->isValidDate(
                $dariTanggal
            );

        $validSampaiTanggal =
            $this->isValidDate(
                $sampaiTanggal
            );

        if (
            $validDariTanggal
            && $validSampaiTanggal
            && $dariTanggal > $sampaiTanggal
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
        |--------------------------------------------------------------------------
        | BATAS STAFF
        |--------------------------------------------------------------------------
        */

        if ($this->isStaff()) {
            if (
                method_exists(
                    $query->getModel(),
                    'scopeUntukStaff'
                )
            ) {
                $query->untukStaff(
                    (int) Auth::id()
                );
            } else {
                $query->whereHas(
                    'disposisi',
                    function (
                        Builder $disposisi
                    ): void {
                        $disposisi->where(
                            'kepada_user_id',
                            (int) Auth::id()
                        );
                    }
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $suratMasuks = $query
            ->orderByDesc(
                'tanggal_terima'
            )
            ->orderByDesc(
                'id'
            )
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
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
        $this->ensureCanManageSurat();

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
        $this->ensureCanManageSurat();

        $data = $request->validated();

        $diskName =
            $this->getStorageDisk();

        $uploadedPath = null;

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | NOMOR AGENDA
            |--------------------------------------------------------------------------
            */

            $nomorInput = trim(
                (string) $request->input(
                    'nomor_agenda',
                    ''
                )
            );

            if (
                $nomorInput === ''
                || SuratMasuk::where(
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
            |--------------------------------------------------------------------------
            | USER PENERIMA
            |--------------------------------------------------------------------------
            */

            $data['diterima_oleh'] =
                (int) Auth::id();

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
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
            |--------------------------------------------------------------------------
            | KAMERA
            |--------------------------------------------------------------------------
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
                        ),
                        $diskName
                    );

                $data['lampiran_file'] =
                    $uploadedPath;
            }

            /*
            |--------------------------------------------------------------------------
            | FILE UPLOAD
            |--------------------------------------------------------------------------
            */

            elseif (
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

            unset(
                $data['captured_image']
            );

            /*
            |--------------------------------------------------------------------------
            | SIMPAN DATABASE
            |--------------------------------------------------------------------------
            */

            $surat =
                SuratMasuk::create(
                    $data
                );

            /*
            |--------------------------------------------------------------------------
            | ACTIVITY LOG
            |--------------------------------------------------------------------------
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
        } catch (Throwable $e) {
            DB::rollBack();

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
     * Menampilkan detail surat masuk.
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

        $daftarStaf = User::query()
            ->where(
                'id',
                '!=',
                Auth::id()
            )
            ->where(
                'is_active',
                true
            )
            ->where(
                function ($query): void {
                    $query
                        ->whereRaw(
                            'LOWER(TRIM(role)) = ?',
                            ['staff']
                        )
                        ->orWhereRaw(
                            'LOWER(TRIM(role)) = ?',
                            ['staf']
                        );
                }
            )
            ->orderBy(
                'name'
            )
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
        $this->ensureCanManageSurat();

        $validated =
            $request->validate(
                [
                    'tujuan_user_id' => [
                        'required',
                        'integer',

                        Rule::exists(
                            'users',
                            'id'
                        )->where(
                            function ($query): void {
                                $query
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->where(
                                        function ($q): void {
                                            $q
                                                ->whereRaw(
                                                    'LOWER(TRIM(role)) = ?',
                                                    ['staff']
                                                )
                                                ->orWhereRaw(
                                                    'LOWER(TRIM(role)) = ?',
                                                    ['staf']
                                                );
                                        }
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
            $instruksi =
                trim(
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
        } catch (Throwable $e) {
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
        $this->ensureCanManageSurat();

        $kategoris = KategoriSurat::query()
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
     * File lama dipertahankan jika
     * tidak ada file baru.
     */
    public function update(
        SuratMasukRequest $request,
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanManageSurat();

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
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            if (
                array_key_exists(
                    'status',
                    $data
                )
            ) {
                $status =
                    strtolower(
                        trim(
                            (string) $data['status']
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
            |--------------------------------------------------------------------------
            | KAMERA
            |--------------------------------------------------------------------------
            */

            if (
                $request->filled(
                    'captured_image'
                )
            ) {
                $newFile =
                    $this->uploadBase64Image(
                        (string) $request->input(
                            'captured_image'
                        ),
                        $diskName
                    );

                $data['lampiran_file'] =
                    $newFile;
            }

            /*
            |--------------------------------------------------------------------------
            | FILE UPLOAD
            |--------------------------------------------------------------------------
            */

            elseif (
                $request->hasFile(
                    'lampiran_file'
                )
            ) {
                $newFile =
                    $this->storeUploadedFile(
                        $request->file(
                            'lampiran_file'
                        ),
                        $diskName
                    );

                $data['lampiran_file'] =
                    $newFile;
            }

            /*
            |--------------------------------------------------------------------------
            | TIDAK ADA FILE BARU
            |--------------------------------------------------------------------------
            */

            else {
                unset(
                    $data['lampiran_file']
                );
            }

            unset(
                $data['captured_image']
            );

            /*
            |--------------------------------------------------------------------------
            | UPDATE DATABASE
            |--------------------------------------------------------------------------
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
            |--------------------------------------------------------------------------
            | HAPUS FILE LAMA
            |--------------------------------------------------------------------------
            */

            if (
                $newFile
                && $oldFile
                && $oldFile !== $newFile
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
        } catch (Throwable $e) {
            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | HAPUS FILE BARU JIKA UPDATE GAGAL
            |--------------------------------------------------------------------------
            */

            if (
                $newFile
                && $newFile !== $oldFile
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
        $this->ensureCanManageSurat();

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
        } catch (Throwable $e) {
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
     * Preview lampiran.
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
        |--------------------------------------------------------------------------
        | URL LANGSUNG
        |--------------------------------------------------------------------------
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

            if (
                !$disk->exists(
                    $file
                )
            ) {
                abort(
                    404,
                    'File lampiran tidak ditemukan di storage.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SUPABASE
            |--------------------------------------------------------------------------
            */

            if (
                $diskName ===
                'supabase'
            ) {
                if (
                    !method_exists(
                        $disk,
                        'temporaryUrl'
                    )
                ) {
                    throw new RuntimeException(
                        'Storage Supabase tidak mendukung temporary URL.'
                    );
                }

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
            |--------------------------------------------------------------------------
            | PUBLIC
            |--------------------------------------------------------------------------
            */

            if (
                method_exists(
                    $disk,
                    'response'
                )
            ) {
                return $disk->response(
                    $file
                );
            }

            /*
            |--------------------------------------------------------------------------
            | FALLBACK
            |--------------------------------------------------------------------------
            */

            $content =
                $disk->get($file);

            $mimeType =
                $this->getMimeTypeFromPath(
                    $file
                );

            return response(
                $content,
                200,
                [
                    'Content-Type' =>
                        $mimeType,

                    'Content-Disposition' =>
                        'inline',

                    'Cache-Control' =>
                        'private, max-age=300',
                ]
            );
        } catch (
            HttpException $e
        ) {
            throw $e;
        } catch (Throwable $e) {
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
     * Download lampiran.
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
        |--------------------------------------------------------------------------
        | URL LANGSUNG
        |--------------------------------------------------------------------------
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

            if (
                !$disk->exists(
                    $file
                )
            ) {
                abort(
                    404,
                    'File lampiran tidak ditemukan di storage.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SUPABASE
            |--------------------------------------------------------------------------
            */

            if (
                $diskName ===
                'supabase'
            ) {
                if (
                    !method_exists(
                        $disk,
                        'temporaryUrl'
                    )
                ) {
                    throw new RuntimeException(
                        'Storage Supabase tidak mendukung temporary URL.'
                    );
                }

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
            |--------------------------------------------------------------------------
            | PUBLIC
            |--------------------------------------------------------------------------
            */

            if (
                method_exists(
                    $disk,
                    'download'
                )
            ) {
                return $disk->download(
                    $file,
                    basename($file)
                );
            }

            /*
            |--------------------------------------------------------------------------
            | FALLBACK
            |--------------------------------------------------------------------------
            */

            $content =
                $disk->get($file);

            $mimeType =
                $this->getMimeTypeFromPath(
                    $file
                );

            return response(
                $content,
                200,
                [
                    'Content-Type' =>
                        $mimeType,

                    'Content-Disposition' =>
                        'attachment; filename="' .
                        basename($file) .
                        '"',
                ]
            );
        } catch (
            HttpException $e
        ) {
            throw $e;
        } catch (Throwable $e) {
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
     * Cetak disposisi.
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
     * Menyimpan file upload ke storage.
     *
     * Mendukung:
     * - PDF
     * - JPG
     * - JPEG
     * - PNG
     */
    private function storeUploadedFile(
        ?UploadedFile $file,
        string $diskName
    ): string {
        if (!$file) {
            throw new RuntimeException(
                'File lampiran tidak ditemukan.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CEK STATUS UPLOAD PHP
        |--------------------------------------------------------------------------
        */

        if (!$file->isValid()) {
            throw new RuntimeException(
                $this->getUploadErrorMessage(
                    $file
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | UKURAN FILE
        |--------------------------------------------------------------------------
        */

        $fileSize =
            $file->getSize();

        if (
            $fileSize !== false
            && $fileSize > self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                'Ukuran file lampiran maksimal 15 MB.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | EXTENSION
        |--------------------------------------------------------------------------
        */

        $extension =
            strtolower(
                trim(
                    (string)
                    $file->getClientOriginalExtension()
                )
            );

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI JPEG
        |--------------------------------------------------------------------------
        */

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        /*
        |--------------------------------------------------------------------------
        | FORMAT DIDUKUNG
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $extension,
                [
                    'pdf',
                    'jpg',
                    'png',
                ],
                true
            )
        ) {
            /*
            |--------------------------------------------------------------------------
            | FALLBACK MIME
            |--------------------------------------------------------------------------
            */

            $clientMime =
                strtolower(
                    trim(
                        (string)
                        $file->getClientMimeType()
                    )
                );

            $detectedMime =
                strtolower(
                    trim(
                        (string)
                        $file->getMimeType()
                    )
                );

            $mime =
                $detectedMime !== ''
                    ? $detectedMime
                    : $clientMime;

            $extension = match ($mime) {
                'application/pdf' =>
                    'pdf',

                'image/jpeg',
                'image/jpg',
                'image/pjpeg' =>
                    'jpg',

                'image/png' =>
                    'png',

                default =>
                    '',
            };

            if ($extension === '') {
                throw new RuntimeException(
                    'Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | MIME STORAGE
        |--------------------------------------------------------------------------
        */

        $mimeType = match ($extension) {
            'pdf' =>
                'application/pdf',

            'png' =>
                'image/png',

            'jpg' =>
                'image/jpeg',

            default =>
                'application/octet-stream',
        };

        /*
        |--------------------------------------------------------------------------
        | NAMA FILE
        |--------------------------------------------------------------------------
        */

        $fileName =
            'surat-masuk_' .
            now()->format('Ymd_His') .
            '_' .
            Str::lower(
                Str::random(12)
            ) .
            '.' .
            $extension;

        /*
        |--------------------------------------------------------------------------
        | FOLDER
        |--------------------------------------------------------------------------
        */

        $directory =
            'lampiran/surat_masuk';

        /*
        |--------------------------------------------------------------------------
        | OPTIONS
        |--------------------------------------------------------------------------
        */

        $options = [
            'ContentType' =>
                $mimeType,
        ];

        if (
            $diskName ===
            'supabase'
        ) {
            $options['visibility'] =
                'private';
        }

        /*
        |--------------------------------------------------------------------------
        | SIMPAN
        |--------------------------------------------------------------------------
        */

        try {
            $disk =
                Storage::disk(
                    $diskName
                );

            $stored =
                $disk->putFileAs(
                    $directory,
                    $file,
                    $fileName,
                    $options
                );

            if (!$stored) {
                throw new RuntimeException(
                    'File "' .
                    $file->getClientOriginalName() .
                    '" gagal disimpan ke storage.'
                );
            }

            return $stored;
        } catch (Throwable $e) {
            Log::error(
                'Gagal upload lampiran Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'original_name' =>
                        $file->getClientOriginalName(),

                    'extension' =>
                        $extension,

                    'client_mime' =>
                        $file->getClientMimeType(),

                    'detected_mime' =>
                        $file->getMimeType(),

                    'size' =>
                        $file->getSize(),

                    'disk' =>
                        $diskName,
                ]
            );

            throw new RuntimeException(
                'Gagal menyimpan file lampiran: ' .
                $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Mendapatkan pesan error upload PHP.
     */
    private function getUploadErrorMessage(
        UploadedFile $file
    ): string {
        return match (
            $file->getError()
        ) {
            UPLOAD_ERR_INI_SIZE =>
                'Ukuran file melebihi batas upload server.',

            UPLOAD_ERR_FORM_SIZE =>
                'Ukuran file melebihi batas form.',

            UPLOAD_ERR_PARTIAL =>
                'File hanya terupload sebagian.',

            UPLOAD_ERR_NO_FILE =>
                'Tidak ada file yang dipilih.',

            UPLOAD_ERR_NO_TMP_DIR =>
                'Folder temporary upload PHP tidak tersedia.',

            UPLOAD_ERR_CANT_WRITE =>
                'PHP gagal menulis file upload.',

            UPLOAD_ERR_EXTENSION =>
                'Upload file dihentikan oleh konfigurasi PHP.',

            default =>
                'File gagal diupload. Kode upload PHP: ' .
                $file->getError(),
        };
    }

    /**
     * Menyimpan hasil scan kamera.
     */
    private function uploadBase64Image(
        string $base64String,
        string $diskName
    ): string {
        $base64String =
            trim(
                $base64String
            );

        if (
            $base64String === ''
        ) {
            throw new RuntimeException(
                'Data gambar kamera kosong.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI DATA URI
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '/^data:image\/(png|jpeg|jpg);base64,/i',
                $base64String
            )
        ) {
            throw new RuntimeException(
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
            throw new RuntimeException(
                'Data gambar kamera tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $header =
            substr(
                $base64String,
                0,
                $commaPosition
            );

        if (
            !preg_match(
                '/^data:image\/(png|jpeg|jpg);base64$/i',
                $header
            )
        ) {
            throw new RuntimeException(
                'MIME gambar kamera tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DECODE BASE64
        |--------------------------------------------------------------------------
        */

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
            $decodedData === false
            || $decodedData === ''
        ) {
            throw new RuntimeException(
                'Gagal memproses gambar kamera. Data Base64 tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | UKURAN
        |--------------------------------------------------------------------------
        */

        if (
            strlen($decodedData)
            > self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                'Ukuran hasil scan kamera terlalu besar. Maksimal 15 MB.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI GAMBAR
        |--------------------------------------------------------------------------
        */

        $imageInfo =
            @getimagesizefromstring(
                $decodedData
            );

        if (
            $imageInfo === false
        ) {
            throw new RuntimeException(
                'Data kamera bukan merupakan gambar yang valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MIME AKTUAL
        |--------------------------------------------------------------------------
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
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Jenis gambar hasil scan tidak didukung.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | EXTENSION
        |--------------------------------------------------------------------------
        */

        $extension =
            $actualMime ===
            'image/png'
                ? 'png'
                : 'jpg';

        /*
        |--------------------------------------------------------------------------
        | FILE NAME
        |--------------------------------------------------------------------------
        */

        $fileName =
            'scan_' .
            now()->format('Ymd_His') .
            '_' .
            Str::lower(
                Str::random(12)
            ) .
            '.' .
            $extension;

        $path =
            'lampiran/surat_masuk/' .
            $fileName;

        /*
        |--------------------------------------------------------------------------
        | OPTIONS
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | STORAGE
        |--------------------------------------------------------------------------
        */

        try {
            $disk =
                Storage::disk(
                    $diskName
                );

            $saved =
                $disk->put(
                    $path,
                    $decodedData,
                    $options
                );

            if (!$saved) {
                throw new RuntimeException(
                    'Gagal menyimpan hasil scan kamera ke storage.'
                );
            }

            return $path;
        } catch (Throwable $e) {
            Log::error(
                'Gagal menyimpan scan kamera Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'mime' =>
                        $actualMime,

                    'extension' =>
                        $extension,

                    'size' =>
                        strlen($decodedData),

                    'disk' =>
                        $diskName,
                ]
            );

            throw new RuntimeException(
                'Gagal menyimpan hasil scan kamera: ' .
                $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Membuat URL file.
     */
    private function getFileUrl(
        ?string $file
    ): ?string {
        if (!$file) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | URL LANGSUNG
        |--------------------------------------------------------------------------
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

            if (
                !$disk->exists(
                    $file
                )
            ) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | SUPABASE
            |--------------------------------------------------------------------------
            */

            if (
                $diskName ===
                'supabase'
            ) {
                if (
                    !method_exists(
                        $disk,
                        'temporaryUrl'
                    )
                ) {
                    return null;
                }

                return $disk->temporaryUrl(
                    $file,
                    now()->addMinutes(30)
                );
            }

            /*
            |--------------------------------------------------------------------------
            | PUBLIC
            |--------------------------------------------------------------------------
            */

            if (
                method_exists(
                    $disk,
                    'url'
                )
            ) {
                return $disk->url(
                    $file
                );
            }

            return null;
        } catch (Throwable $e) {
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
            !$file
            || filter_var(
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
                $disk->exists(
                    $file
                )
            ) {
                $disk->delete(
                    $file
                );
            }
        } catch (Throwable $e) {
            Log::warning(
                'Gagal menghapus file Surat Masuk.',
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
     * Mendapatkan MIME berdasarkan extension.
     */
    private function getMimeTypeFromPath(
        string $file
    ): string {
        $extension =
            strtolower(
                pathinfo(
                    $file,
                    PATHINFO_EXTENSION
                )
            );

        return match ($extension) {
            'pdf' =>
                'application/pdf',

            'jpg',
            'jpeg' =>
                'image/jpeg',

            'png' =>
                'image/png',

            default =>
                'application/octet-stream',
        };
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

        $parts =
            explode(
                '-',
                $date
            );

        if (
            count($parts) !== 3
        ) {
            return false;
        }

        return checkdate(
            (int) $parts[1],
            (int) $parts[2],
            (int) $parts[0]
        );
    }

    /**
     * Mencatat activity log.
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
                )
                && method_exists(
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
        } catch (Throwable $e) {
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