<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratKeluarRequest;
use App\Models\ActivityLog;
use App\Models\KategoriSurat;
use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
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
     * Status surat keluar yang valid.
     */
    private const STATUS_OPTIONS = [
        'draft',
        'diproses',
        'disetujui',
        'dikirim',
        'diarsipkan',
    ];

    /**
     * Extension file yang diperbolehkan.
     */
    private const ALLOWED_FILE_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
    ];

    /**
     * Maksimal file input.
     *
     * 10 MB.
     */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /**
     * Target maksimal gambar setelah compression.
     *
     * Dibuat di bawah 10 MB agar ada margin keamanan.
     */
    private const MAX_COMPRESSED_IMAGE_SIZE = 9 * 1024 * 1024;

    /**
     * Maksimal dimensi gambar.
     */
    private const MAX_IMAGE_WIDTH = 2500;

    private const MAX_IMAGE_HEIGHT = 2500;

    /**
     * Kualitas JPEG awal.
     */
    private const JPEG_QUALITY = 82;

    /*
    |--------------------------------------------------------------------------
    | ROLE
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil role user yang sedang login.
     *
     * Normalisasi:
     * - staf  -> staff
     * - staff -> staff
     * - admin -> admin
     * - pimpinan -> pimpinan
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
     * Memastikan user memiliki hak pengelolaan surat keluar.
     *
     * Admin dan pimpinan diperbolehkan.
     */
    private function authorizeManageSurat(): void
    {
        $this->ensureAuthenticated();

        abort_unless(
            in_array(
                $this->userRole(),
                [
                    'admin',
                    'pimpinan',
                ],
                true
            ),
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
    private function normalizeStatus(
        ?string $status
    ): string {
        $status = strtolower(
            trim(
                (string) $status
            )
        );

        return $status === 'draf'
            ? 'draft'
            : $status;
    }

    /**
     * Mengambil status yang valid.
     */
    private function getValidStatus(
        ?string $status
    ): string {
        $status = $this->normalizeStatus(
            $status
        );

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
        */

        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        if ($search !== '') {
            $keyword = '%' . $search . '%';

            $query->where(
                function (Builder $q) use ($keyword): void {
                    $q->where(
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
                            function (Builder $kategori) use ($keyword): void {
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

        $kategoriInput = $request->input(
            'kategori_id',
            []
        );

        if (!is_array($kategoriInput)) {
            $kategoriInput = [
                $kategoriInput,
            ];
        }

        $kategoriIds = collect(
            $kategoriInput
        )
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
            ->values();

        if (
            $kategoriIds->isNotEmpty()
        ) {
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
            $statusInput = [
                $statusInput,
            ];
        }

        $statuses = collect(
            $statusInput
        )
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

        if (
            $statuses->isNotEmpty()
        ) {
            $query->whereIn(
                'status',
                $statuses->all()
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
                'tanggal_keluar',
                '>=',
                $dariTanggal
            );
        }

        if ($validSampaiTanggal) {
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
     * PDF:
     * - disimpan tanpa perubahan.
     *
     * JPG/JPEG/PNG:
     * - resize
     * - convert ke JPG
     * - compression
     */
    public function store(
        SuratKeluarRequest $request
    ) {
        $this->authorizeManageSurat();

        $data = $request->validated();

        $data['dibuat_oleh'] =
            Auth::id();

        $data['status'] =
            $this->getValidStatus(
                $data['status'] ?? 'draft'
            );

        $storedAttachment = null;

        try {
            if (
                $request->hasFile(
                    'lampiran_file'
                )
            ) {
                $storedAttachment =
                    $this->storeUploadedFile(
                        $request->file(
                            'lampiran_file'
                        )
                    );

                $data['lampiran_file'] =
                    $storedAttachment;
            }

            $suratKeluar =
                SuratKeluar::create(
                    $data
                );

            $this->logActivity(
                'create',
                'surat_keluar',
                'Menambahkan surat keluar #' .
                $suratKeluar->id
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
            if ($storedAttachment) {
                $this->deleteAttachment(
                    $storedAttachment
                );
            }

            Log::error(
                'Gagal menyimpan surat keluar.',
                [
                    'message' =>
                        $e->getMessage(),

                    'user_id' =>
                        Auth::id(),

                    'disk' =>
                        $this->getStorageDisk(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menyimpan surat keluar: ' .
                    $e->getMessage()
                );
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
     *
     * File lama dipertahankan jika tidak ada
     * file baru.
     */
    public function update(
        SuratKeluarRequest $request,
        SuratKeluar $suratKeluar
    ) {
        $this->authorizeManageSurat();

        $data = $request->validated();

        if (
            array_key_exists(
                'status',
                $data
            )
        ) {
            $data['status'] =
                $this->getValidStatus(
                    $data['status']
                );
        }

        $oldAttachment =
            $suratKeluar->lampiran_file;

        $newAttachment = null;

        try {
            /*
            |--------------------------------------------------------------------------
            | FILE BARU
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile(
                    'lampiran_file'
                )
            ) {
                $newAttachment =
                    $this->storeUploadedFile(
                        $request->file(
                            'lampiran_file'
                        )
                    );

                $data['lampiran_file'] =
                    $newAttachment;
            } else {
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
                $newAttachment
                && $oldAttachment
                && $oldAttachment !== $newAttachment
            ) {
                $this->deleteAttachment(
                    $oldAttachment
                );
            }

            $this->logActivity(
                'update',
                'surat_keluar',
                'Mengubah surat keluar #' .
                $suratKeluar->id
            );

            return redirect()
                ->route(
                    'surat-keluar.index'
                )
                ->with(
                    'success',
                    'Surat keluar berhasil diperbarui.'
                );
        } catch (Throwable $e) {
            /*
            |--------------------------------------------------------------------------
            | CLEANUP FILE BARU
            |--------------------------------------------------------------------------
            */

            if (
                $newAttachment
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

                    'user_id' =>
                        Auth::id(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal memperbarui surat keluar: ' .
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    /**
     * Menghapus surat keluar.
     */
    public function destroy(
        SuratKeluar $suratKeluar
    ) {
        $this->authorizeManageSurat();

        $id = $suratKeluar->id;

        try {
            $suratKeluar->delete();

            $this->logActivity(
                'delete',
                'surat_keluar',
                'Menghapus surat keluar #' .
                $id
            );

            return redirect()
                ->route(
                    'surat-keluar.index'
                )
                ->with(
                    'success',
                    'Surat keluar berhasil dipindahkan ke sampah.'
                );
        } catch (Throwable $e) {
            Log::error(
                'Gagal menghapus surat keluar.',
                [
                    'surat_keluar_id' =>
                        $id,

                    'message' =>
                        $e->getMessage(),

                    'user_id' =>
                        Auth::id(),
                ]
            );

            return back()->with(
                'error',
                'Gagal menghapus surat keluar: ' .
                $e->getMessage()
            );
        }
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

        $path =
            $suratKeluar->lampiran_file;

        if (!$path) {
            abort(
                404,
                'Lampiran tidak tersedia.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | URL EXTERNAL
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

        $diskName =
            $this->getStorageDisk();

        try {
            $disk =
                $this->storage();

            if (!$disk->exists($path)) {
                abort(
                    404,
                    'File lampiran tidak ditemukan di storage.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SUPABASE TEMPORARY URL
            |--------------------------------------------------------------------------
            */

            if (
                $diskName === 'supabase'
                && method_exists(
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
                            'path' =>
                                $path,

                            'message' =>
                                $e->getMessage(),
                        ]
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FALLBACK
            |--------------------------------------------------------------------------
            */

            $content =
                $disk->get($path);

            return response(
                $content,
                200,
                [
                    'Content-Type' =>
                        $this->getMimeTypeFromPath(
                            $path
                        ),

                    'Content-Disposition' =>
                        'inline',

                    'Cache-Control' =>
                        'private, max-age=300',
                ]
            );
        } catch (Throwable $e) {
            Log::error(
                'Gagal preview lampiran surat keluar.',
                [
                    'path' =>
                        $path,

                    'message' =>
                        $e->getMessage(),

                    'disk' =>
                        $diskName,

                    'surat_keluar_id' =>
                        $suratKeluar->id,
                ]
            );

            return back()->with(
                'error',
                'Gagal membuka lampiran file: ' .
                $e->getMessage()
            );
        }
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
     * Mencatat activity log.
     */
    public function storeLog(
        Request $request,
        SuratKeluar $suratKeluar
    ) {
        $this->authorizeManageSurat();

        $this->logActivity(
            'log',
            'surat_keluar',
            'Menambahkan log surat keluar #' .
            $suratKeluar->id
        );

        return back()->with(
            'success',
            'Log surat berhasil disimpan.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORAGE
    |--------------------------------------------------------------------------
    */

    /**
     * Mendapatkan disk storage aktif.
     *
     * FILESYSTEM_DISK=supabase
     * akan menggunakan disk supabase.
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

        return $disk !== ''
            ? $disk
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

    /*
    |--------------------------------------------------------------------------
    | UPLOAD FILE
    |--------------------------------------------------------------------------
    */

    /**
     * Menyimpan file upload.
     *
     * PDF:
     * - disimpan apa adanya.
     *
     * JPG/JPEG/PNG:
     * - diproses dengan GD
     * - resize
     * - convert ke JPG
     * - compression
     */
    private function storeUploadedFile(
        ?UploadedFile $file
    ): string {
        if (!$file) {
            throw new RuntimeException(
                'File lampiran tidak ditemukan.'
            );
        }

        if (!$file->isValid()) {
            throw new RuntimeException(
                $this->getUploadErrorMessage(
                    $file->getError()
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | UKURAN INPUT
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
            trim(
                (string) $file->getClientOriginalExtension()
            )
        );

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        if (
            !in_array(
                $extension,
                self::ALLOWED_FILE_EXTENSIONS,
                true
            )
        ) {
            throw new RuntimeException(
                'Format file tidak didukung. ' .
                'Gunakan PDF, JPG, JPEG, atau PNG.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        if ($extension === 'pdf') {
            $contents =
                file_get_contents(
                    $file->getRealPath()
                );

            if (
                $contents === false
                || $contents === ''
            ) {
                throw new RuntimeException(
                    'Gagal membaca file PDF.'
                );
            }

            return $this->storeBinaryFile(
                $contents,
                'pdf',
                'application/pdf',
                'surat-keluar'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | IMAGE
        |--------------------------------------------------------------------------
        */

        $contents =
            file_get_contents(
                $file->getRealPath()
            );

        if (
            $contents === false
            || $contents === ''
        ) {
            throw new RuntimeException(
                'Gagal membaca file gambar.'
            );
        }

        return $this->storeCompressedImage(
            $contents
        );
    }

    /*
    |--------------------------------------------------------------------------
    | COMPRESS IMAGE
    |--------------------------------------------------------------------------
    */

    /**
     * Compress gambar menggunakan GD.
     *
     * Hasil akhir selalu JPG.
     */
    private function storeCompressedImage(
        string $contents
    ): string {
        if (
            !function_exists(
                'imagecreatefromstring'
            )
            || !function_exists(
                'imagejpeg'
            )
        ) {
            throw new RuntimeException(
                'PHP GD belum tersedia. Aktifkan ekstensi GD pada server.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | IMAGE INFO
        |--------------------------------------------------------------------------
        */

        $imageInfo =
            @getimagesizefromstring(
                $contents
            );

        if ($imageInfo === false) {
            throw new RuntimeException(
                'File bukan gambar yang valid.'
            );
        }

        $mime =
            strtolower(
                (string) (
                    $imageInfo['mime']
                    ?? ''
                )
            );

        if ($mime === 'image/jpg') {
            $mime = 'image/jpeg';
        }

        if (
            !in_array(
                $mime,
                [
                    'image/jpeg',
                    'image/png',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Format gambar tidak didukung.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD IMAGE
        |--------------------------------------------------------------------------
        */

        $source =
            @imagecreatefromstring(
                $contents
            );

        if ($source === false) {
            throw new RuntimeException(
                'Gagal membaca gambar menggunakan GD.'
            );
        }

        $sourceWidth =
            imagesx($source);

        $sourceHeight =
            imagesy($source);

        if (
            $sourceWidth <= 0
            || $sourceHeight <= 0
        ) {
            imagedestroy(
                $source
            );

            throw new RuntimeException(
                'Dimensi gambar tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RESIZE
        |--------------------------------------------------------------------------
        */

        $scale = min(
            self::MAX_IMAGE_WIDTH /
                $sourceWidth,

            self::MAX_IMAGE_HEIGHT /
                $sourceHeight,

            1
        );

        $newWidth =
            max(
                1,
                (int) round(
                    $sourceWidth *
                    $scale
                )
            );

        $newHeight =
            max(
                1,
                (int) round(
                    $sourceHeight *
                    $scale
                )
            );

        /*
        |--------------------------------------------------------------------------
        | CANVAS
        |--------------------------------------------------------------------------
        */

        $canvas =
            imagecreatetruecolor(
                $newWidth,
                $newHeight
            );

        if ($canvas === false) {
            imagedestroy(
                $source
            );

            throw new RuntimeException(
                'Gagal membuat canvas gambar.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | WHITE BACKGROUND
        |--------------------------------------------------------------------------
        |
        | Dibutuhkan untuk PNG transparan karena hasil akhir adalah JPG.
        |
        */

        $white =
            imagecolorallocate(
                $canvas,
                255,
                255,
                255
            );

        imagefill(
            $canvas,
            0,
            0,
            $white
        );

        /*
        |--------------------------------------------------------------------------
        | RESAMPLE
        |--------------------------------------------------------------------------
        */

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $sourceWidth,
            $sourceHeight
        );

        imagedestroy(
            $source
        );

        /*
        |--------------------------------------------------------------------------
        | QUALITY LOOP
        |--------------------------------------------------------------------------
        */

        $qualities = [
            self::JPEG_QUALITY,
            72,
            62,
            52,
            45,
        ];

        $compressedData = null;

        foreach (
            $qualities as $quality
        ) {
            ob_start();

            $success =
                imagejpeg(
                    $canvas,
                    null,
                    $quality
                );

            $output =
                ob_get_clean();

            if (
                !$success
                || $output === false
                || $output === ''
            ) {
                imagedestroy(
                    $canvas
                );

                throw new RuntimeException(
                    'Gagal melakukan compression gambar.'
                );
            }

            $compressedData =
                $output;

            if (
                strlen(
                    $compressedData
                ) <= self::MAX_COMPRESSED_IMAGE_SIZE
            ) {
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RESIZE LANJUTAN
        |--------------------------------------------------------------------------
        */

        if (
            $compressedData === null
            || strlen(
                $compressedData
            ) > self::MAX_COMPRESSED_IMAGE_SIZE
        ) {
            $ratio = 0.75;

            $smallerWidth =
                max(
                    1,
                    (int) floor(
                        $newWidth *
                        $ratio
                    )
                );

            $smallerHeight =
                max(
                    1,
                    (int) floor(
                        $newHeight *
                        $ratio
                    )
                );

            $smallerCanvas =
                imagecreatetruecolor(
                    $smallerWidth,
                    $smallerHeight
                );

            if ($smallerCanvas === false) {
                imagedestroy(
                    $canvas
                );

                throw new RuntimeException(
                    'Gagal melakukan resize lanjutan gambar.'
                );
            }

            $white =
                imagecolorallocate(
                    $smallerCanvas,
                    255,
                    255,
                    255
                );

            imagefill(
                $smallerCanvas,
                0,
                0,
                $white
            );

            imagecopyresampled(
                $smallerCanvas,
                $canvas,
                0,
                0,
                0,
                0,
                $smallerWidth,
                $smallerHeight,
                $newWidth,
                $newHeight
            );

            imagedestroy(
                $canvas
            );

            ob_start();

            $success =
                imagejpeg(
                    $smallerCanvas,
                    null,
                    45
                );

            $compressedData =
                ob_get_clean();

            imagedestroy(
                $smallerCanvas
            );

            if (
                !$success
                || $compressedData === false
                || $compressedData === ''
            ) {
                throw new RuntimeException(
                    'Gagal melakukan compression lanjutan gambar.'
                );
            }
        } else {
            imagedestroy(
                $canvas
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $compressedData === false
            || $compressedData === ''
        ) {
            throw new RuntimeException(
                'Hasil compression gambar kosong.'
            );
        }

        if (
            strlen(
                $compressedData
            ) > self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                'Gambar masih melebihi batas 10 MB setelah compression.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STORAGE
        |--------------------------------------------------------------------------
        */

        return $this->storeBinaryFile(
            $compressedData,
            'jpg',
            'image/jpeg',
            'surat-keluar'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE BINARY
    |--------------------------------------------------------------------------
    */

    /**
     * Menyimpan binary data ke storage.
     */
    private function storeBinaryFile(
        string $contents,
        string $extension,
        string $mimeType,
        string $prefix
    ): string {
        if ($contents === '') {
            throw new RuntimeException(
                'Data file kosong.'
            );
        }

        $safePrefix =
            Str::slug(
                $prefix,
                '-'
            );

        $fileName =
            $safePrefix .
            '_' .
            now()->format(
                'Ymd_His'
            ) .
            '_' .
            Str::lower(
                Str::random(12)
            ) .
            '.' .
            $extension;

        $directory =
            'lampiran/surat_keluar';

        $path =
            $directory .
            '/' .
            $fileName;

        $options = [
            'ContentType' =>
                $mimeType,
        ];

        if (
            $this->getStorageDisk()
            === 'supabase'
        ) {
            $options['visibility'] =
                'private';
        }

        try {
            $disk =
                $this->storage();

            $saved =
                $disk->put(
                    $path,
                    $contents,
                    $options
                );

            if (!$saved) {
                throw new RuntimeException(
                    'File gagal disimpan ke storage.'
                );
            }

            return $path;
        } catch (Throwable $e) {
            Log::error(
                'Gagal menyimpan binary surat keluar.',
                [
                    'message' =>
                        $e->getMessage(),

                    'path' =>
                        $path,

                    'extension' =>
                        $extension,

                    'mime' =>
                        $mimeType,

                    'size' =>
                        strlen($contents),

                    'disk' =>
                        $this->getStorageDisk(),
                ]
            );

            throw new RuntimeException(
                'Gagal menyimpan file ke storage: ' .
                $e->getMessage(),
                previous: $e
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE ATTACHMENT
    |--------------------------------------------------------------------------
    */

    /**
     * Menghapus attachment dari storage.
     */
    private function deleteAttachment(
        ?string $path
    ): void {
        if (
            !$path
            || trim($path) === ''
        ) {
            return;
        }

        /*
        | Jangan hapus URL eksternal.
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
                    'path' =>
                        $path,

                    'message' =>
                        $e->getMessage(),

                    'disk' =>
                        $this->getStorageDisk(),
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
     * Mengubah kode error upload PHP
     * menjadi pesan yang mudah dimengerti.
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
                'File hanya terupload sebagian. Silakan coba lagi.',

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
                $error,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | MIME TYPE
    |--------------------------------------------------------------------------
    */

    /**
     * Mendapatkan MIME type berdasarkan extension.
     */
    private function getMimeTypeFromPath(
        string $path
    ): string {
        $extension =
            strtolower(
                pathinfo(
                    $path,
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

    /*
    |--------------------------------------------------------------------------
    | VALIDASI TANGGAL
    |--------------------------------------------------------------------------
    */

    /**
     * Memvalidasi tanggal format YYYY-MM-DD.
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

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    /**
     * Mencatat activity log dengan aman.
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
                'Gagal mencatat Activity Log surat keluar.',
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