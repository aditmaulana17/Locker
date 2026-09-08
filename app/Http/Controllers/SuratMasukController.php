<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratMasukRequest;
use App\Models\ActivityLog;
use App\Models\KategoriSurat;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     * Mendapatkan nama disk storage yang digunakan aplikasi.
     *
     * Jika FILESYSTEM_DISK=supabase maka menggunakan supabase.
     * Selain itu file surat masuk menggunakan public.
     */
    private function getStorageDisk(): string
    {
        $disk = (string) config(
            'filesystems.default',
            'public'
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
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(
            $this->getStorageDisk()
        );

        return $disk;
    }

    /**
     * Mendapatkan role user yang sedang login.
     */
    private function getUserRole(): string
    {
        $user = Auth::user();

        if (!$user) {
            return '';
        }

        $role = strtolower(
            trim(
                (string) (
                    $user->role
                    ?? $user->jabatan
                    ?? ''
                )
            )
        );

        if ($role === 'staff') {
            $role = 'staf';
        }

        return $role;
    }

    /**
     * Mengecek apakah user adalah staf.
     */
    private function isStaff(): bool
    {
        return $this->getUserRole() === 'staf';
    }

    /**
     * Admin dan pimpinan dapat mengelola surat.
     */
    private function canManage(): bool
    {
        return in_array(
            $this->getUserRole(),
            ['admin', 'pimpinan'],
            true
        );
    }

    /**
     * Pastikan user mempunyai hak kelola.
     */
    private function ensureCanManage(): void
    {
        abort_unless(
            $this->canManage(),
            403,
            'Akses ditolak. Anda tidak memiliki hak untuk mengelola surat masuk.'
        );
    }

    /**
     * Staff hanya dapat melihat surat yang
     * mempunyai disposisi kepada dirinya sendiri.
     */
    private function staffCanAccess(
        SuratMasuk $suratMasuk
    ): bool {
        if (!$this->isStaff()) {
            return true;
        }

        return $suratMasuk
            ->disposisi()
            ->where(
                'kepada_user_id',
                Auth::id()
            )
            ->exists();
    }

    /**
     * Pastikan user boleh melihat surat.
     */
    private function ensureCanView(
        SuratMasuk $suratMasuk
    ): void {
        abort_unless(
            $this->staffCanAccess($suratMasuk),
            403,
            'Akses ditolak. Anda tidak memiliki hak akses untuk melihat surat ini.'
        );
    }

    /**
     * Menampilkan daftar surat masuk.
     *
     * Filter:
     * - search
     * - kategori_id[]
     * - status[]
     * - dari_tanggal
     * - sampai_tanggal
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        */
        $kategoriIds = collect(
            $request->input(
                'kategori_id',
                []
            )
        )
            ->filter(
                fn ($id) =>
                    is_scalar($id)
                    && ctype_digit((string) $id)
            )
            ->map(
                fn ($id) =>
                    (int) $id
            )
            ->unique()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */
        $statuses = collect(
            $request->input(
                'status',
                []
            )
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
        | QUERY DASAR
        |--------------------------------------------------------------------------
        */
        $query = SuratMasuk::query()
            ->with([
                'kategori',
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
            $query->where(function ($q) use ($search) {
                $q->where(
                    'nomor_agenda',
                    'like',
                    '%' . $search . '%'
                )
                    ->orWhere(
                        'nomor_surat',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'perihal',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'asal_surat',
                        'like',
                        '%' . $search . '%'
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        |
        | Mengikuti struktur database yang Anda gunakan:
        | kategori_surat_id
        |
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
        |
        | Contoh:
        |
        | /surat-masuk?status[]=baru
        |
        | hanya menampilkan surat status baru.
        |
        */
        if (!empty($statuses)) {
            $query->whereIn(
                'status',
                $statuses
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL MULAI
        |--------------------------------------------------------------------------
        */
        $dariTanggal = trim(
            (string) $request->input(
                'dari_tanggal',
                ''
            )
        );

        if ($this->isValidDate($dariTanggal)) {
            $query->whereDate(
                'tanggal_terima',
                '>=',
                $dariTanggal
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL AKHIR
        |--------------------------------------------------------------------------
        */
        $sampaiTanggal = trim(
            (string) $request->input(
                'sampai_tanggal',
                ''
            )
        );

        if ($this->isValidDate($sampaiTanggal)) {
            $query->whereDate(
                'tanggal_terima',
                '<=',
                $sampaiTanggal
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BATASI DATA UNTUK STAFF
        |--------------------------------------------------------------------------
        |
        | Staff hanya melihat surat yang pernah didisposisikan
        | kepada dirinya.
        |
        */
        if ($this->isStaff()) {
            $query->whereHas(
                'disposisi',
                function ($q) {
                    $q->where(
                        'kepada_user_id',
                        Auth::id()
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ORDER + PAGINATION
        |--------------------------------------------------------------------------
        */
        $suratMasuks = $query
            ->orderByDesc('tanggal_terima')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | DATA KATEGORI
        |--------------------------------------------------------------------------
        */
        $kategoris = KategoriSurat::query()
            ->orderBy('nama_kategori')
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
            ->orderBy('nama_kategori')
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

        $data =
            $request->validated();

        $diskName =
            $this->getStorageDisk();

        $uploadedFile = null;

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
                ||
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
            |--------------------------------------------------------------------------
            | USER PENERIMA
            |--------------------------------------------------------------------------
            */
            $data['diterima_oleh'] =
                Auth::id();

            /*
            |--------------------------------------------------------------------------
            | STATUS DEFAULT
            |--------------------------------------------------------------------------
            */
            if (
                empty($data['status'])
                ||
                !in_array(
                    strtolower(
                        trim(
                            (string) $data['status']
                        )
                    ),
                    self::STATUS_OPTIONS,
                    true
                )
            ) {
                $data['status'] = 'baru';
            } else {
                $data['status'] =
                    strtolower(
                        trim(
                            (string) $data['status']
                        )
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | UPLOAD DARI KAMERA
            |--------------------------------------------------------------------------
            */
            if (
                $request->filled(
                    'captured_image'
                )
            ) {
                $uploadedFile =
                    $this->uploadBase64Image(
                        $request->input(
                            'captured_image'
                        )
                    );

                $data['lampiran_file'] =
                    $uploadedFile;
            }

            /*
            |--------------------------------------------------------------------------
            | UPLOAD FILE BIASA
            |--------------------------------------------------------------------------
            */
            elseif (
                $request->hasFile(
                    'lampiran_file'
                )
                &&
                $request
                    ->file('lampiran_file')
                    ->isValid()
            ) {
                $uploadedFile =
                    $request
                        ->file('lampiran_file')
                        ->store(
                            'lampiran/surat_masuk',
                            $diskName
                        );

                if (!$uploadedFile) {
                    throw new \Exception(
                        'File lampiran gagal disimpan ke storage.'
                    );
                }

                $data['lampiran_file'] =
                    $uploadedFile;
            }

            /*
            |--------------------------------------------------------------------------
            | HAPUS FIELD KAMERA DARI DATA DATABASE
            |--------------------------------------------------------------------------
            */
            unset(
                $data['captured_image']
            );

            /*
            |--------------------------------------------------------------------------
            | SIMPAN SURAT
            |--------------------------------------------------------------------------
            */
            $surat = SuratMasuk::create(
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
                'Menambah surat masuk ' .
                $surat->nomor_agenda .
                ' - ' .
                $surat->perihal
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
            |--------------------------------------------------------------------------
            | HAPUS FILE YANG SUDAH TERUPLOAD JIKA DATABASE GAGAL
            |--------------------------------------------------------------------------
            */
            if ($uploadedFile) {
                $this->deleteStorageFile(
                    $uploadedFile,
                    $diskName
                );
            }

            Log::error(
                'Gagal Simpan Surat Masuk',
                [
                    'message' =>
                        $e->getMessage(),

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
            'disposisi.dari',
            'disposisi.kepada',
        ]);

        $daftarStaf = User::query()
            ->whereIn(
                'role',
                ['staf', 'staff']
            )
            ->where(
                'is_active',
                true
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
     * Membuat disposisi surat masuk.
     */
    public function storeDisposisi(
        Request $request,
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanManage();

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
                            fn ($query) =>
                                $query->whereIn(
                                    'role',
                                    ['staf', 'staff']
                                )->where(
                                    'is_active',
                                    true
                                )
                        ),
                    ],

                    'instruksi' => [
                        'required',
                        'string',
                        'max:500',
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
                        'Staf tujuan tidak valid.',

                    'instruksi.required' =>
                        'Instruksi disposisi wajib diisi.',

                    'instruksi.max' =>
                        'Instruksi disposisi maksimal 500 karakter.',

                    'batas_waktu.date' =>
                        'Batas waktu disposisi tidak valid.',
                ]
            );

        DB::beginTransaction();

        try {
            $instruksi =
                trim(
                    $validated['instruksi']
                );

            /*
            |--------------------------------------------------------------------------
            | SIMPAN DISPOSISI
            |--------------------------------------------------------------------------
            |
            | Gunakan status lowercase agar sama dengan dashboard:
            | menunggu / diproses / selesai
            |
            */
            $suratMasuk
                ->disposisi()
                ->create([
                    'dari_user_id' =>
                        Auth::id(),

                    'kepada_user_id' =>
                        $validated['tujuan_user_id'],

                    'instruksi' =>
                        $instruksi,

                    'isi_disposisi' =>
                        $instruksi,

                    'batas_waktu' =>
                        $validated['batas_waktu']
                        ?? null,

                    'status' =>
                        'menunggu',
                ]);

            /*
            |--------------------------------------------------------------------------
            | UPDATE STATUS SURAT
            |--------------------------------------------------------------------------
            */
            $suratMasuk->update([
                'status' =>
                    'didisposisikan',
            ]);

            /*
            |--------------------------------------------------------------------------
            | ACTIVITY LOG
            |--------------------------------------------------------------------------
            */
            $this->logActivity(
                'disposisi',
                'surat_masuk',
                'Melakukan disposisi surat masuk ' .
                $suratMasuk->nomor_agenda
            );

            DB::commit();

            return back()->with(
                'success',
                'Disposisi surat berhasil dikirim ke staf yang dituju.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error(
                'Gagal Disposisi Surat Masuk',
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

        $kategoris = KategoriSurat::query()
            ->orderBy('nama_kategori')
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
            |--------------------------------------------------------------------------
            | NORMALISASI STATUS
            |--------------------------------------------------------------------------
            */
            if (
                isset($data['status'])
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
                } else {
                    unset(
                        $data['status']
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FILE DARI KAMERA
            |--------------------------------------------------------------------------
            */
            if (
                $request->filled(
                    'captured_image'
                )
            ) {
                $newFile =
                    $this->uploadBase64Image(
                        $request->input(
                            'captured_image'
                        )
                    );

                $data['lampiran_file'] =
                    $newFile;
            }

            /*
            |--------------------------------------------------------------------------
            | FILE UPLOAD BARU
            |--------------------------------------------------------------------------
            */
            elseif (
                $request->hasFile(
                    'lampiran_file'
                )
                &&
                $request
                    ->file('lampiran_file')
                    ->isValid()
            ) {
                $newFile =
                    $request
                        ->file('lampiran_file')
                        ->store(
                            'lampiran/surat_masuk',
                            $diskName
                        );

                if (!$newFile) {
                    throw new \Exception(
                        'File lampiran baru gagal disimpan ke storage.'
                    );
                }

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
            | UPDATE SURAT
            |--------------------------------------------------------------------------
            */
            $suratMasuk->update(
                $data
            );

            /*
            |--------------------------------------------------------------------------
            | ACTIVITY LOG
            |--------------------------------------------------------------------------
            */
            $this->logActivity(
                'update',
                'surat_masuk',
                'Mengubah surat masuk ' .
                $suratMasuk->nomor_agenda
            );

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | HAPUS FILE LAMA SETELAH UPDATE BERHASIL
            |--------------------------------------------------------------------------
            */
            if (
                $newFile
                &&
                $oldFile
                &&
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
            |--------------------------------------------------------------------------
            | HAPUS FILE BARU JIKA UPDATE GAGAL
            |--------------------------------------------------------------------------
            */
            if (
                $newFile
                &&
                $newFile !== $oldFile
            ) {
                $this->deleteStorageFile(
                    $newFile,
                    $diskName
                );
            }

            Log::error(
                'Gagal Update Surat Masuk',
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

        DB::beginTransaction();

        try {
            $suratMasuk->delete();

            $this->logActivity(
                'delete',
                'surat_masuk',
                'Menghapus surat masuk ' .
                $nomor
            );

            DB::commit();

            return back()->with(
                'success',
                'Surat masuk berhasil dipindahkan ke arsip sampah.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error(
                'Gagal Hapus Surat Masuk',
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
        |--------------------------------------------------------------------------
        | JIKA SUDAH BERUPA URL
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

            /*
            |--------------------------------------------------------------------------
            | CEK FILE
            |--------------------------------------------------------------------------
            */
            if (!$disk->exists($file)) {
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
            | LOCAL / PUBLIC
            |--------------------------------------------------------------------------
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
                'Gagal Preview Lampiran Surat Masuk',
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
        |--------------------------------------------------------------------------
        | JIKA SUDAH BERUPA URL
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

            /*
            |--------------------------------------------------------------------------
            | CEK FILE
            |--------------------------------------------------------------------------
            */
            if (!$disk->exists($file)) {
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
            | LOCAL / PUBLIC
            |--------------------------------------------------------------------------
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
                'Gagal Download Lampiran Surat Masuk',
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
     * Upload gambar hasil kamera Base64.
     */
    private function uploadBase64Image(
        string $base64String
    ): string {
        $base64String =
            trim($base64String);

        if (
            $base64String === ''
        ) {
            throw new \Exception(
                'Data gambar kamera kosong.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT
        |--------------------------------------------------------------------------
        */
        $extension =
            'jpg';

        $contentType =
            'image/jpeg';

        /*
        |--------------------------------------------------------------------------
        | BACA DATA URI
        |--------------------------------------------------------------------------
        */
        if (
            preg_match(
                '/^data:(image\/(?:png|jpeg|jpg|webp));base64,/i',
                $base64String,
                $matches
            )
        ) {
            $contentType =
                strtolower(
                    $matches[1]
                );

            $extension =
                str_replace(
                    'image/',
                    '',
                    $contentType
                );

            if (
                $extension ===
                'jpeg'
            ) {
                $extension = 'jpg';
            }

            $base64String =
                substr(
                    $base64String,
                    strpos(
                        $base64String,
                        ','
                    ) + 1
                );
        }

        /*
        |--------------------------------------------------------------------------
        | DECODE BASE64
        |--------------------------------------------------------------------------
        */
        $decodedData =
            base64_decode(
                $base64String,
                true
            );

        if (
            $decodedData === false
            ||
            $decodedData === ''
        ) {
            throw new \Exception(
                'Gagal memproses gambar kamera. Format Base64 tidak valid.'
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
            throw new \Exception(
                'Data dari kamera bukan merupakan gambar yang valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MIME AKTUAL
        |--------------------------------------------------------------------------
        */
        if (
            !empty(
                $imageInfo['mime']
            )
            &&
            str_starts_with(
                strtolower(
                    $imageInfo['mime']
                ),
                'image/'
            )
        ) {
            $contentType =
                strtolower(
                    $imageInfo['mime']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | EXTENSION MIME
        |--------------------------------------------------------------------------
        */
        $mimeToExtension = [
            'image/jpeg' =>
                'jpg',

            'image/png' =>
                'png',

            'image/webp' =>
                'webp',
        ];

        $extension =
            $mimeToExtension[
                $contentType
            ]
            ?? $extension;

        /*
        |--------------------------------------------------------------------------
        | NAMA FILE
        |--------------------------------------------------------------------------
        */
        $fileName =
            'scan_' .
            now()->format(
                'YmdHis'
            ) .
            '_' .
            Str::random(8) .
            '.' .
            $extension;

        $path =
            'lampiran/surat_masuk/' .
            $fileName;

        /*
        |--------------------------------------------------------------------------
        | STORAGE
        |--------------------------------------------------------------------------
        */
        $diskName =
            $this->getStorageDisk();

        $disk =
            $this->storage();

        $options = [
            'ContentType' =>
                $contentType,
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
        $saved =
            $disk->put(
                $path,
                $decodedData,
                $options
            );

        if (!$saved) {
            throw new \Exception(
                'Gagal menyimpan hasil scan kamera ke storage.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFIKASI FILE
        |--------------------------------------------------------------------------
        */
        if (
            !$disk->exists($path)
        ) {
            throw new \Exception(
                'File hasil scan tidak ditemukan setelah proses upload.'
            );
        }

        return $path;
    }

    /**
     * Mendapatkan URL lampiran.
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
                !$disk->exists($file)
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
                return $disk->temporaryUrl(
                    $file,
                    now()->addMinutes(30)
                );
            }

            /*
            |--------------------------------------------------------------------------
            | PUBLIC / LOCAL
            |--------------------------------------------------------------------------
            */
            return $disk->url(
                $file
            );
        } catch (\Throwable $e) {
            Log::warning(
                'Gagal membuat URL lampiran Surat Masuk',
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
            ||
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
                'Gagal menghapus file Surat Masuk dari storage',
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
                )
                &&
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
                'Gagal mencatat Activity Log Surat Masuk',
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

    /**
     * Validasi tanggal YYYY-MM-DD.
     */
    private function isValidDate(
        ?string $date
    ): bool {
        if (!$date) {
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
}