<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratKeluarRequest;
use App\Models\ActivityLog;
use App\Models\KategoriSurat;
use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SuratKeluarController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI
    |--------------------------------------------------------------------------
    */

    /**
     * Status yang diperbolehkan.
     *
     * Database menggunakan "draft".
     * "draf" tetap diterima sebagai input legacy,
     * kemudian dinormalisasi menjadi "draft".
     */
    private const STATUS_OPTIONS = [
        'draft',
        'diproses',
        'disetujui',
        'dikirim',
        'diarsipkan',
    ];

    /**
     * Ekstensi file yang diperbolehkan.
     */
    private const ALLOWED_FILE_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
    ];

    /**
     * Ukuran maksimal file: 10 MB.
     */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /*
    |--------------------------------------------------------------------------
    | STORAGE
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil nama disk storage aktif.
     *
     * FILESYSTEM_DISK=supabase akan menggunakan
     * disk "supabase" yang sudah dikonfigurasi.
     */
    private function getStorageDisk(): string
    {
        $default = (string) config(
            'filesystems.default',
            'local'
        );

        return $default !== ''
            ? $default
            : 'local';
    }

    /**
     * Mengambil instance filesystem.
     */
    private function storage(): Filesystem
    {
        return Storage::disk(
            $this->getStorageDisk()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ROLE & AUTHORIZATION
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil role user yang sedang login.
     *
     * Normalisasi:
     * - staf -> staff
     * - Staff -> staff
     * - ADMIN -> admin
     */
    private function userRole(): string
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

        if ($role === 'staf') {
            $role = 'staff';
        }

        return $role;
    }

    /**
     * Admin dan pimpinan dapat mengelola surat keluar.
     */
    private function canManageSurat(): bool
    {
        return in_array(
            $this->userRole(),
            [
                'admin',
                'pimpinan',
            ],
            true
        );
    }

    /**
     * Memastikan hanya user yang memiliki hak
     * dapat mengelola surat keluar.
     */
    private function authorizeManageSurat(): void
    {
        abort_unless(
            $this->canManageSurat(),
            403,
            'Anda tidak memiliki hak akses untuk mengelola surat keluar.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    /**
     * Menormalisasi status.
     *
     * draf -> draft
     */
    private function normalizeStatus(?string $status): string
    {
        $status = strtolower(
            trim(
                (string) $status
            )
        );

        if ($status === 'draf') {
            return 'draft';
        }

        return $status;
    }

    /**
     * Mengambil status yang valid untuk database.
     */
    private function getValidStatus(?string $status): string
    {
        $status = $this->normalizeStatus($status);

        return in_array(
            $status,
            self::STATUS_OPTIONS,
            true
        )
            ? $status
            : 'draft';
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan daftar surat keluar.
     */
    public function index(Request $request)
    {
        $this->ensureAuthenticated();

        $query = SuratKeluar::query()
            ->with([
                'kategori',
                'pembuat',
                'penandatangan',
            ]);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        |
        | Field yang memang tersedia di surat_keluars:
        | - nomor_surat
        | - pengirim
        | - perihal
        |
        */

        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        if ($search !== '') {
            $query->where(
                function ($q) use ($search) {
                    $q->where(
                        'nomor_surat',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'pengirim',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'perihal',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'ringkasan',
                        'like',
                        "%{$search}%"
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        |
        | Form menggunakan kategori_id[].
        | Kolom database menggunakan kategori_surat_id.
        |
        */

        $kategoriInput = $request->input(
            'kategori_id',
            []
        );

        if (!is_array($kategoriInput)) {
            $kategoriInput = [$kategoriInput];
        }

        $kategoriIds = collect($kategoriInput)
            ->filter(
                fn ($id) =>
                    is_scalar($id)
                    && ctype_digit((string) $id)
            )
            ->map(
                fn ($id) => (int) $id
            )
            ->unique()
            ->values();

        if ($kategoriIds->isNotEmpty()) {
            $query->whereIn(
                'kategori_surat_id',
                $kategoriIds->all()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        $statusInput = $request->input(
            'status',
            []
        );

        if (!is_array($statusInput)) {
            $statusInput = [$statusInput];
        }

        $statuses = collect($statusInput)
            ->filter(
                fn ($status) =>
                    is_scalar($status)
            )
            ->map(
                fn ($status) =>
                    $this->normalizeStatus(
                        (string) $status
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
            ->values();

        if ($statuses->isNotEmpty()) {
            $query->whereIn(
                'status',
                $statuses->all()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL MULAI
        |--------------------------------------------------------------------------
        |
        | Surat Keluar menggunakan tanggal_keluar.
        |
        */

        $dariTanggal = trim(
            (string) $request->input(
                'dari_tanggal',
                ''
            )
        );

        if ($this->isValidDate($dariTanggal)) {
            $query->whereDate(
                'tanggal_keluar',
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
                'tanggal_keluar',
                '<=',
                $sampaiTanggal
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SORTING
        |--------------------------------------------------------------------------
        |
        | Prioritaskan tanggal_keluar.
        | Data lama yang NULL tetap diurutkan dengan created_at.
        |
        */

        $suratKeluars = $query
            ->orderByRaw(
                'tanggal_keluar IS NULL ASC'
            )
            ->orderByDesc(
                'tanggal_keluar'
            )
            ->orderByDesc(
                'created_at'
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
            'surat_keluar.index',
            compact(
                'suratKeluars',
                'kategoris'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    /**
     * Form tambah surat keluar.
     */
    public function create()
    {
        $this->ensureAuthenticated();

        $this->authorizeManageSurat();

        $kategoris = KategoriSurat::query()
            ->orderBy(
                'nama_kategori'
            )
            ->get();

        $users = User::query()
            ->where(
                'is_active',
                true
            )
            ->orderBy(
                'name'
            )
            ->get();

        return view(
            'surat_keluar.create',
            compact(
                'kategoris',
                'users'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    /**
     * Menyimpan surat keluar baru.
     *
     * Fitur:
     * - PDF
     * - JPG
     * - JPEG
     * - PNG
     * - maksimal 10 MB
     * - tanpa scan/kamera
     * - tanpa kompresi gambar
     */
    public function store(
        SuratKeluarRequest $request
    ) {
        $this->ensureAuthenticated();

        $this->authorizeManageSurat();

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | PEMBUAT
        |--------------------------------------------------------------------------
        */

        $data['dibuat_oleh'] = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $data['status'] = $this->getValidStatus(
            $data['status'] ?? 'draft'
        );

        /*
        |--------------------------------------------------------------------------
        | UPLOAD
        |--------------------------------------------------------------------------
        */

        $storedAttachment = null;

        try {
            if ($request->hasFile('lampiran_file')) {
                $storedAttachment =
                    $this->storeUploadedFile(
                        $request->file('lampiran_file')
                    );
            }

            if ($storedAttachment !== null) {
                $data['lampiran_file'] =
                    $storedAttachment;
            }

            /*
            |--------------------------------------------------------------------------
            | DATABASE
            |--------------------------------------------------------------------------
            */

            $suratKeluar =
                SuratKeluar::create($data);

            /*
            |--------------------------------------------------------------------------
            | ACTIVITY LOG
            |--------------------------------------------------------------------------
            */

            ActivityLog::catat(
                'create',
                'surat_keluar',
                'Menambahkan surat keluar #' . $suratKeluar->id
            );

            return redirect()
                ->route(
                    'surat-keluar.index'
                )
                ->with(
                    'success',
                    'Surat keluar berhasil ditambahkan.'
                );
        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | CLEANUP FILE
            |--------------------------------------------------------------------------
            */

            if ($storedAttachment !== null) {
                $this->deleteAttachment(
                    $storedAttachment
                );
            }

            Log::error(
                'Gagal menyimpan surat keluar.',
                [
                    'message' => $e->getMessage(),
                    'user_id' => Auth::id(),
                ]
            );

            throw $e;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan detail surat keluar.
     */
    public function show(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureAuthenticated();

        $suratKeluar->load([
            'kategori',
            'pembuat',
            'penandatangan',
        ]);

        return view(
            'surat_keluar.show',
            compact(
                'suratKeluar'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    /**
     * Form edit surat keluar.
     */
    public function edit(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureAuthenticated();

        $this->authorizeManageSurat();

        $kategoris = KategoriSurat::query()
            ->orderBy(
                'nama_kategori'
            )
            ->get();

        $users = User::query()
            ->where(
                'is_active',
                true
            )
            ->orderBy(
                'name'
            )
            ->get();

        return view(
            'surat_keluar.edit',
            compact(
                'suratKeluar',
                'kategoris',
                'users'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    /**
     * Memperbarui surat keluar.
     */
    public function update(
        SuratKeluarRequest $request,
        SuratKeluar $suratKeluar
    ) {
        $this->ensureAuthenticated();

        $this->authorizeManageSurat();

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        if (array_key_exists('status', $data)) {
            $data['status'] =
                $this->getValidStatus(
                    $data['status']
                );
        }

        /*
        |--------------------------------------------------------------------------
        | FILE LAMA
        |--------------------------------------------------------------------------
        */

        $oldAttachment =
            $suratKeluar->lampiran_file;

        $newAttachment = null;

        try {

            /*
            |--------------------------------------------------------------------------
            | FILE BARU
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('lampiran_file')) {
                $newAttachment =
                    $this->storeUploadedFile(
                        $request->file('lampiran_file')
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE LAMPIRAN
            |--------------------------------------------------------------------------
            */

            if ($newAttachment !== null) {

                $data['lampiran_file'] =
                    $newAttachment;

            } else {

                /*
                | Tidak ada file baru.
                | File lama tetap dipertahankan.
                */

                unset(
                    $data['lampiran_file']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE DATABASE
            |--------------------------------------------------------------------------
            */

            $suratKeluar->update(
                $data
            );

            /*
            |--------------------------------------------------------------------------
            | HAPUS FILE LAMA
            |--------------------------------------------------------------------------
            */

            if (
                $newAttachment !== null
                && !empty($oldAttachment)
                && $oldAttachment !== $newAttachment
            ) {
                $this->deleteAttachment(
                    $oldAttachment
                );
            }
        } catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | CLEANUP FILE BARU
            |--------------------------------------------------------------------------
            */

            if (
                $newAttachment !== null
                && $newAttachment !== $oldAttachment
            ) {
                $this->deleteAttachment(
                    $newAttachment
                );
            }

            Log::error(
                'Gagal memperbarui surat keluar.',
                [
                    'surat_keluar_id' =>
                        $suratKeluar->id,
                    'message' =>
                        $e->getMessage(),
                ]
            );

            throw $e;
        }

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        ActivityLog::catat(
            'update',
            'surat_keluar',
            'Mengubah surat keluar #' . $suratKeluar->id
        );

        return redirect()
            ->route(
                'surat-keluar.index'
            )
            ->with(
                'success',
                'Surat keluar berhasil diperbarui.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    /**
     * Menghapus surat keluar.
     *
     * Soft delete digunakan jika model memiliki
     * SoftDeletes.
     */
    public function destroy(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureAuthenticated();

        $this->authorizeManageSurat();

        $id = $suratKeluar->id;

        $suratKeluar->delete();

        ActivityLog::catat(
            'delete',
            'surat_keluar',
            'Menghapus surat keluar #' . $id
        );

        return redirect()
            ->route(
                'surat-keluar.index'
            )
            ->with(
                'success',
                'Surat keluar berhasil dipindahkan ke sampah.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | PREVIEW LAMPIRAN
    |--------------------------------------------------------------------------
    */

    /**
     * Preview lampiran surat keluar.
     */
    public function previewLampiran(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureAuthenticated();

        abort_if(
            empty($suratKeluar->lampiran_file),
            404,
            'Lampiran tidak tersedia.'
        );

        $disk = $this->storage();

        $path =
            $suratKeluar->lampiran_file;

        /*
        |--------------------------------------------------------------------------
        | URL EKSTERNAL
        |--------------------------------------------------------------------------
        */

        if (
            filter_var(
                $path,
                FILTER_VALIDATE_URL
            )
        ) {
            return redirect()->away(
                $path
            );
        }

        /*
        |--------------------------------------------------------------------------
        | TEMPORARY URL
        |--------------------------------------------------------------------------
        */

        if (
            method_exists(
                $disk,
                'temporaryUrl'
            )
        ) {
            try {

                $temporaryUrl =
                    $disk->temporaryUrl(
                        $path,
                        now()->addMinutes(10)
                    );

                return redirect()->away(
                    $temporaryUrl
                );
            } catch (Throwable $e) {

                Log::warning(
                    'Gagal membuat temporary URL surat keluar.',
                    [
                        'path' => $path,
                        'message' =>
                            $e->getMessage(),
                    ]
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK STORAGE
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $disk->exists($path),
            404,
            'File lampiran tidak ditemukan.'
        );

        $content =
            $disk->get($path);

        $extension =
            strtolower(
                pathinfo(
                    $path,
                    PATHINFO_EXTENSION
                )
            );

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        $mimeType =
            $mimeTypes[$extension]
            ?? 'application/octet-stream';

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
    }

    /*
    |--------------------------------------------------------------------------
    | CETAK
    |--------------------------------------------------------------------------
    */

    /**
     * Halaman cetak surat keluar.
     */
    public function cetak(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureAuthenticated();

        $suratKeluar->load([
            'kategori',
            'pembuat',
            'penandatangan',
        ]);

        return view(
            'surat_keluar.cetak',
            compact(
                'suratKeluar'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LABEL
    |--------------------------------------------------------------------------
    */

    /**
     * Halaman label surat keluar.
     */
    public function label(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureAuthenticated();

        $suratKeluar->load(
            'kategori'
        );

        return view(
            'surat_keluar.label',
            compact(
                'suratKeluar'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    /**
     * Menyimpan log surat keluar.
     */
    public function storeLog(
        Request $request,
        SuratKeluar $suratKeluar
    ) {
        $this->ensureAuthenticated();

        $this->authorizeManageSurat();

        ActivityLog::catat(
            'log',
            'surat_keluar',
            'Menambahkan log surat keluar #' . $suratKeluar->id
        );

        return back()->with(
            'success',
            'Log surat berhasil disimpan.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD FILE
    |--------------------------------------------------------------------------
    */

    /**
     * Menyimpan file upload ke storage.
     *
     * Format:
     * - PDF
     * - JPG
     * - JPEG
     * - PNG
     *
     * Maksimal:
     * - 10 MB
     *
     * Tidak menggunakan:
     * - scan
     * - kamera
     * - captured_image
     * - kompresi gambar
     */
    private function storeUploadedFile(
        ?UploadedFile $file
    ): string {
        if (!$file) {
            throw new RuntimeException(
                'File lampiran tidak ditemukan.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI FILE
        |--------------------------------------------------------------------------
        */

        if (!$file->isValid()) {
            throw new RuntimeException(
                $this->getUploadErrorMessage(
                    $file->getError()
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | UKURAN
        |--------------------------------------------------------------------------
        */

        $fileSize =
            $file->getSize();

        if (
            $fileSize !== false
            && $fileSize > self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                'Ukuran file lampiran maksimal 10 MB.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | EXTENSION
        |--------------------------------------------------------------------------
        */

        $extension = strtolower(
            (string) $file->getClientOriginalExtension()
        );

        if ($extension === 'jpeg') {
            $normalizedExtension = 'jpg';
        } else {
            $normalizedExtension = $extension;
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI EXTENSION
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $extension,
                self::ALLOWED_FILE_EXTENSIONS,
                true
            )
        ) {
            throw new RuntimeException(
                'Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MIME ASLI
        |--------------------------------------------------------------------------
        */

        $realMime =
            strtolower(
                (string) $file->getMimeType()
            );

        $allowedMimes = [
            'pdf' => [
                'application/pdf',
            ],

            'jpg' => [
                'image/jpeg',
                'image/jpg',
            ],

            'jpeg' => [
                'image/jpeg',
                'image/jpg',
            ],

            'png' => [
                'image/png',
            ],
        ];

        if (
            !isset(
                $allowedMimes[$extension]
            )
            || !in_array(
                $realMime,
                $allowedMimes[$extension],
                true
            )
        ) {
            throw new RuntimeException(
                'Isi file tidak sesuai dengan format yang diperbolehkan.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | MIME STORAGE
        |--------------------------------------------------------------------------
        */

        $mimeType = match ($normalizedExtension) {
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };

        /*
        |--------------------------------------------------------------------------
        | NAMA FILE
        |--------------------------------------------------------------------------
        */

        $fileName =
            now()->format('Ymd_His')
            . '_'
            . Str::lower(
                Str::random(12)
            )
            . '.'
            . $normalizedExtension;

        $directory =
            'surat-keluar';

        /*
        |--------------------------------------------------------------------------
        | STORAGE
        |--------------------------------------------------------------------------
        */

        $disk =
            $this->storage();

        $stored =
            $disk->putFileAs(
                $directory,
                $file,
                $fileName,
                [
                    'visibility' => 'private',
                    'ContentType' => $mimeType,
                ]
            );

        if (!$stored) {
            throw new RuntimeException(
                'Lampiran gagal disimpan ke storage.'
            );
        }

        return $directory
            . '/'
            . $fileName;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE ATTACHMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Menghapus lampiran dari storage.
     */
    private function deleteAttachment(
        ?string $path
    ): void {
        if (
            empty($path)
            || trim($path) === ''
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | JANGAN HAPUS URL EKSTERNAL
        |--------------------------------------------------------------------------
        */

        if (
            filter_var(
                $path,
                FILTER_VALIDATE_URL
            )
        ) {
            return;
        }

        try {
            $disk =
                $this->storage();

            if (
                $disk->exists($path)
            ) {
                $disk->delete($path);
            }
        } catch (Throwable $e) {

            Log::warning(
                'Gagal menghapus lampiran surat keluar.',
                [
                    'path' => $path,
                    'message' =>
                        $e->getMessage(),
                ]
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD ERROR
    |--------------------------------------------------------------------------
    */

    /**
     * Pesan error upload PHP.
     */
    private function getUploadErrorMessage(
        int $error
    ): string {
        return match ($error) {

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
                'Upload dihentikan oleh ekstensi PHP.',

            default =>
                'Terjadi kesalahan saat mengunggah file.',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI TANGGAL
    |--------------------------------------------------------------------------
    */

    /**
     * Memastikan format tanggal YYYY-MM-DD valid.
     */
    private function isValidDate(
        ?string $date
    ): bool {
        if (!$date) {
            return false;
        }

        $date = trim($date);

        /*
        |--------------------------------------------------------------------------
        | REGEX YYYY-MM-DD
        |--------------------------------------------------------------------------
        */

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