<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratKeluarRequest;
use App\Models\ActivityLog;
use App\Models\KategoriSurat;
use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\File;
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

    private const STATUS_OPTIONS = [
        'draft',
        'diproses',
        'disetujui',
        'dikirim',
        'diarsipkan',
    ];

    private const ALLOWED_FILE_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
    ];

    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /*
    |--------------------------------------------------------------------------
    | IMAGE COMPRESSION
    |--------------------------------------------------------------------------
    */

    private const MAX_IMAGE_WIDTH = 2500;
    private const MAX_IMAGE_HEIGHT = 2500;
    private const JPEG_QUALITY = 78;

    /*
    |--------------------------------------------------------------------------
    | AUTH / ROLE
    |--------------------------------------------------------------------------
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

    private function ensureUserAuthenticated(): void
    {
        if (!Auth::check()) {
            abort(
                401,
                'Anda harus login terlebih dahulu.'
            );
        }
    }

    private function authorizeManageSurat(): void
    {
        $this->ensureUserAuthenticated();

        if (
            !in_array(
                $this->userRole(),
                [
                    'admin',
                    'pimpinan',
                ],
                true
            )
        ) {
            abort(
                403,
                'Anda tidak memiliki hak akses untuk mengelola surat keluar.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
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
    | BUILD FILTERED QUERY
    |--------------------------------------------------------------------------
    */

    public function buildFilteredQuery(
        Request $request
    ): Builder {
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
                function (
                    Builder $q
                ) use (
                    $keyword
                ): void {
                    $q
                        ->where(
                            'nomor_surat',
                            'like',
                            $keyword
                        )
                        ->orWhere(
                            'perihal',
                            'like',
                            $keyword
                        )
                        ->orWhere(
                            'pengirim',
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
                        )
                        ->orWhereHas(
                            'pembuat',
                            function (
                                Builder $user
                            ) use (
                                $keyword
                            ): void {
                                $user
                                    ->where(
                                        'name',
                                        'like',
                                        $keyword
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        $keyword
                                    )
                                    ->orWhere(
                                        'jabatan',
                                        'like',
                                        $keyword
                                    );
                            }
                        )
                        ->orWhereHas(
                            'penandatangan',
                            function (
                                Builder $user
                            ) use (
                                $keyword
                            ): void {
                                $user
                                    ->where(
                                        'name',
                                        'like',
                                        $keyword
                                    )
                                    ->orWhere(
                                        'email',
                                        'like',
                                        $keyword
                                    )
                                    ->orWhere(
                                        'jabatan',
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
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategoriInput = $request->input(
            'kategori_id',
            $request->input(
                'kategori_surat_id',
                []
            )
        );

        if (
            is_scalar($kategoriInput)
            && trim((string) $kategoriInput) !== ''
        ) {
            $kategoriInput = [
                $kategoriInput,
            ];
        }

        if (!is_array($kategoriInput)) {
            $kategoriInput = [];
        }

        $kategoriIds = collect(
            $kategoriInput
        )
            ->flatten()
            ->filter(
                fn ($id) =>
                    is_scalar($id)
                    && is_numeric($id)
                    && (int) $id > 0
            )
            ->map(
                fn ($id) =>
                    (int) $id
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
        | STATUS
        |--------------------------------------------------------------------------
        */

        $statusInput = $request->input(
            'status',
            []
        );

        if (
            is_scalar($statusInput)
            && trim((string) $statusInput) !== ''
        ) {
            $statusInput = [
                $statusInput,
            ];
        }

        if (!is_array($statusInput)) {
            $statusInput = [];
        }

        $statuses = collect(
            $statusInput
        )
            ->flatten()
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
        | TANGGAL
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

        $validDariTanggal = $this->isValidDate(
            $dariTanggal
        );

        $validSampaiTanggal = $this->isValidDate(
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

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ) {
        $this->ensureUserAuthenticated();

        $query = $this->buildFilteredQuery(
            $request
        );

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
            ->orderByDesc(
                'id'
            )
            ->paginate(10)
            ->withQueryString();

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

    public function store(
        SuratKeluarRequest $request
    ) {
        $this->authorizeManageSurat();

        $data = $request->validated();

        $data['dibuat_oleh'] = Auth::id();

        $data['status'] = $this->getValidStatus(
            $data['status'] ?? 'draft'
        );

        $storedAttachment = null;

        try {
            /*
            |--------------------------------------------------------------------------
            | UPLOAD + COMPRESSION
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile(
                    'lampiran_file'
                )
            ) {
                $storedAttachment = $this->storeUploadedFile(
                    $request->file(
                        'lampiran_file'
                    )
                );

                $data['lampiran_file'] = $storedAttachment;
            }

            /*
            |--------------------------------------------------------------------------
            | DATABASE
            |--------------------------------------------------------------------------
            */

            $suratKeluar = SuratKeluar::create(
                $data
            );

            /*
            |--------------------------------------------------------------------------
            | ACTIVITY LOG
            |--------------------------------------------------------------------------
            */

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

    public function show(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureUserAuthenticated();

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
            $data['status'] = $this->getValidStatus(
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
                $newAttachment = $this->storeUploadedFile(
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
                && $newAttachment !==
                    $oldAttachment
            ) {
                $this->deleteAttachment(
                    $oldAttachment
                );
            }

            /*
            |--------------------------------------------------------------------------
            | LOG
            |--------------------------------------------------------------------------
            */

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
            if (
                $newAttachment
                && $newAttachment !==
                    $oldAttachment
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
                    'disk' =>
                        $this->getStorageDisk(),
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

    public function destroy(
        SuratKeluar $suratKeluar
    ) {
        $this->authorizeManageSurat();

        $id = $suratKeluar->id;
        $attachment =
            $suratKeluar->lampiran_file;

        try {
            $suratKeluar->delete();

            if ($attachment) {
                $this->deleteAttachment(
                    $attachment
                );
            }

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

            return back()
                ->with(
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

    public function previewLampiran(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureUserAuthenticated();

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
        | EXTERNAL URL
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

            /*
            |--------------------------------------------------------------------------
            | TEMPORARY URL SUPABASE
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

                    if ($temporaryUrl) {
                        return redirect()->away(
                            $temporaryUrl
                        );
                    }
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
            | FALLBACK STREAM
            |--------------------------------------------------------------------------
            */

            $stream =
                $disk->readStream(
                    $path
                );

            if ($stream === false) {
                throw new RuntimeException(
                    'File lampiran tidak dapat dibaca dari storage.'
                );
            }

            return response()->stream(
                function () use (
                    $stream
                ): void {
                    fpassthru(
                        $stream
                    );

                    if (
                        is_resource(
                            $stream
                        )
                    ) {
                        fclose(
                            $stream
                        );
                    }
                },
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

            return back()
                ->with(
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

    public function cetak(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureUserAuthenticated();

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

    public function label(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureUserAuthenticated();

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

        return back()
            ->with(
                'success',
                'Log surat berhasil disimpan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | STORAGE DISK
    |--------------------------------------------------------------------------
    */

    private function getStorageDisk(): string
    {
        $disk = strtolower(
            trim(
                (string) config(
                    'filesystems.default',
                    'supabase'
                )
            )
        );

        if ($disk === '') {
            return 'supabase';
        }

        if ($disk !== 'supabase') {
            throw new RuntimeException(
                'FILESYSTEM_DISK harus diset ke "supabase".'
            );
        }

        return 'supabase';
    }

    private function storage(): FilesystemAdapter
    {
        return Storage::disk(
            $this->getStorageDisk()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE UPLOADED FILE
    |--------------------------------------------------------------------------
    |
    | PDF:
    |   Tidak dikompres, hanya divalidasi dan disimpan.
    |
    | JPG/JPEG/PNG:
    |   Dibaca dengan GD.
    |   Resize maksimal 2500x2500.
    |   Dikonversi menjadi JPG.
    |   Quality 78.
    |
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
        | VALIDASI UPLOAD PHP
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
        | UKURAN AWAL
        |--------------------------------------------------------------------------
        */

        $fileSize = $file->getSize();

        if (
            $fileSize === false ||
            $fileSize <= 0
        ) {
            throw new RuntimeException(
                'Ukuran file tidak dapat dibaca.'
            );
        }

        if (
            $fileSize >
            self::MAX_FILE_SIZE
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

        $originalExtension = strtolower(
            trim(
                (string) $file->getClientOriginalExtension()
            )
        );

        if (
            !in_array(
                $originalExtension,
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
        | PATH TEMPORARY
        |--------------------------------------------------------------------------
        */

        $realPath = $file->getRealPath();

        if (
            !$realPath ||
            !is_readable($realPath)
        ) {
            throw new RuntimeException(
                'File upload tidak dapat dibaca oleh server.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SIGNATURE
        |--------------------------------------------------------------------------
        */

        $handle = fopen(
            $realPath,
            'rb'
        );

        if ($handle === false) {
            throw new RuntimeException(
                'File upload tidak dapat dibuka oleh server.'
            );
        }

        $header = fread(
            $handle,
            16
        );

        fclose(
            $handle
        );

        if (
            $header === false ||
            $header === ''
        ) {
            throw new RuntimeException(
                'File upload kosong atau tidak dapat dibaca.'
            );
        }

        $isPng = str_starts_with(
            $header,
            "\x89PNG\r\n\x1a\n"
        );

        $isJpeg = str_starts_with(
            $header,
            "\xFF\xD8\xFF"
        );

        $isPdf = str_starts_with(
            $header,
            '%PDF'
        );

        /*
        |--------------------------------------------------------------------------
        | VALIDASI EXTENSION + SIGNATURE
        |--------------------------------------------------------------------------
        */

        if (
            $originalExtension === 'png'
            && !$isPng
        ) {
            throw new RuntimeException(
                'File PNG tidak valid.'
            );
        }

        if (
            in_array(
                $originalExtension,
                [
                    'jpg',
                    'jpeg',
                ],
                true
            )
            && !$isJpeg
        ) {
            throw new RuntimeException(
                'File JPG/JPEG tidak valid.'
            );
        }

        if (
            $originalExtension === 'pdf'
            && !$isPdf
        ) {
            throw new RuntimeException(
                'File PDF tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        if ($isPdf) {
            return $this->storeOriginalPdf(
                $file
            );
        }

        /*
        |--------------------------------------------------------------------------
        | IMAGE
        |--------------------------------------------------------------------------
        */

        if (
            !$isJpeg
            && !$isPng
        ) {
            throw new RuntimeException(
                'Isi file tidak dikenali sebagai JPG, JPEG, PNG, atau PDF.'
            );
        }

        return $this->compressAndStoreImage(
            $file,
            $realPath,
            $isPng
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE ORIGINAL PDF
    |--------------------------------------------------------------------------
    */

    private function storeOriginalPdf(
        UploadedFile $file
    ): string {
        $fileName =
            'surat-keluar_' .
            now()->format(
                'Ymd_His'
            ) .
            '_' .
            Str::lower(
                Str::random(20)
            ) .
            '.pdf';

        $directory =
            'lampiran/surat_keluar';

        try {
            $disk = $this->storage();

            $savedPath =
                $disk->putFileAs(
                    $directory,
                    $file,
                    $fileName,
                    [
                        'visibility' =>
                            'private',
                        'ContentType' =>
                            'application/pdf',
                    ]
                );

            if (
                !$savedPath
            ) {
                throw new RuntimeException(
                    'PDF gagal disimpan ke Supabase.'
                );
            }

            return $savedPath;
        } catch (Throwable $e) {
            Log::error(
                'Gagal menyimpan PDF surat keluar.',
                [
                    'message' =>
                        $e->getMessage(),
                    'disk' =>
                        $this->getStorageDisk(),
                ]
            );

            throw new RuntimeException(
                'Gagal menyimpan PDF ke Supabase: ' .
                    $e->getMessage(),
                previous: $e
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | COMPRESS IMAGE
    |--------------------------------------------------------------------------
    */

    private function compressAndStoreImage(
        UploadedFile $file,
        string $realPath,
        bool $isPng
    ): string {
        /*
        |--------------------------------------------------------------------------
        | CEK GD
        |--------------------------------------------------------------------------
        */

        if (!extension_loaded('gd')) {
            throw new RuntimeException(
                'PHP GD belum aktif. Aktifkan ekstensi GD untuk compression gambar.'
            );
        }

        $source = null;
        $temporaryPath = null;

        try {
            /*
            |--------------------------------------------------------------------------
            | LOAD IMAGE
            |--------------------------------------------------------------------------
            */

            $source = $isPng
                ? @imagecreatefrompng(
                    $realPath
                )
                : @imagecreatefromjpeg(
                    $realPath
                );

            if (!$source) {
                throw new RuntimeException(
                    'Gambar tidak dapat diproses oleh GD.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | UKURAN ASLI
            |--------------------------------------------------------------------------
            */

            $sourceWidth =
                imagesx(
                    $source
                );

            $sourceHeight =
                imagesy(
                    $source
                );

            if (
                $sourceWidth <= 0 ||
                $sourceHeight <= 0
            ) {
                throw new RuntimeException(
                    'Ukuran gambar tidak valid.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | HITUNG RESIZE
            |--------------------------------------------------------------------------
            */

            $scale =
                min(
                    self::MAX_IMAGE_WIDTH /
                        $sourceWidth,
                    self::MAX_IMAGE_HEIGHT /
                        $sourceHeight,
                    1
                );

            $targetWidth =
                max(
                    1,
                    (int) round(
                        $sourceWidth *
                            $scale
                    )
                );

            $targetHeight =
                max(
                    1,
                    (int) round(
                        $sourceHeight *
                            $scale
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | BUAT CANVAS
            |--------------------------------------------------------------------------
            */

            $destination =
                imagecreatetruecolor(
                    $targetWidth,
                    $targetHeight
                );

            if (!$destination) {
                throw new RuntimeException(
                    'Canvas gambar gagal dibuat.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | BACKGROUND PUTIH
            |--------------------------------------------------------------------------
            |
            | PNG transparan akan dikonversi menjadi JPG.
            | Karena JPG tidak mendukung transparansi,
            | background dibuat putih.
            |
            */

            $white =
                imagecolorallocate(
                    $destination,
                    255,
                    255,
                    255
                );

            imagefill(
                $destination,
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
                $destination,
                $source,
                0,
                0,
                0,
                0,
                $targetWidth,
                $targetHeight,
                $sourceWidth,
                $sourceHeight
            );

            /*
            |--------------------------------------------------------------------------
            | TEMPORARY FILE
            |--------------------------------------------------------------------------
            */

            $temporaryPath =
                tempnam(
                    sys_get_temp_dir(),
                    'e_arsip_sk_'
                );

            if (
                !$temporaryPath
            ) {
                throw new RuntimeException(
                    'File temporary untuk compression gagal dibuat.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | JPEG COMPRESSION
            |--------------------------------------------------------------------------
            */

            $saved =
                imagejpeg(
                    $destination,
                    $temporaryPath,
                    self::JPEG_QUALITY
                );

            if (!$saved) {
                throw new RuntimeException(
                    'Compression gambar gagal.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | UKURAN HASIL
            |--------------------------------------------------------------------------
            */

            $compressedSize =
                filesize(
                    $temporaryPath
                );

            if (
                $compressedSize === false ||
                $compressedSize <= 0
            ) {
                throw new RuntimeException(
                    'Ukuran hasil compression tidak dapat dibaca.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | FILE HASIL
            |--------------------------------------------------------------------------
            */

            $compressedFile =
                new File(
                    $temporaryPath
                );

            $fileName =
                'surat-keluar_' .
                now()->format(
                    'Ymd_His'
                ) .
                '_' .
                Str::lower(
                    Str::random(20)
                ) .
                '.jpg';

            $directory =
                'lampiran/surat_keluar';

            /*
            |--------------------------------------------------------------------------
            | SIMPAN SUPABASE
            |--------------------------------------------------------------------------
            */

            $disk =
                $this->storage();

            $savedPath =
                $disk->putFileAs(
                    $directory,
                    $compressedFile,
                    $fileName,
                    [
                        'visibility' =>
                            'private',
                        'ContentType' =>
                            'image/jpeg',
                    ]
                );

            if (
                !$savedPath
            ) {
                throw new RuntimeException(
                    'Hasil compression gagal disimpan ke Supabase.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | LOG COMPRESSION
            |--------------------------------------------------------------------------
            */

            Log::info(
                'Compression gambar surat keluar berhasil.',
                [
                    'original_name' =>
                        $file->getClientOriginalName(),
                    'original_size' =>
                        $file->getSize(),
                    'compressed_size' =>
                        $compressedSize,
                    'original_width' =>
                        $sourceWidth,
                    'original_height' =>
                        $sourceHeight,
                    'target_width' =>
                        $targetWidth,
                    'target_height' =>
                        $targetHeight,
                    'quality' =>
                        self::JPEG_QUALITY,
                    'output' =>
                        $savedPath,
                ]
            );

            return $savedPath;
        } catch (Throwable $e) {
            Log::error(
                'Gagal compression gambar surat keluar.',
                [
                    'message' =>
                        $e->getMessage(),
                    'original_name' =>
                        $file->getClientOriginalName(),
                    'original_size' =>
                        $file->getSize(),
                    'disk' =>
                        $this->getStorageDisk(),
                ]
            );

            throw new RuntimeException(
                'Gagal memproses dan menyimpan gambar: ' .
                    $e->getMessage(),
                previous: $e
            );
        } finally {
            /*
            |--------------------------------------------------------------------------
            | CLEANUP GD
            |--------------------------------------------------------------------------
            */

            if (
                is_resource($source)
                || $source instanceof \GdImage
            ) {
                imagedestroy(
                    $source
                );
            }

            if (
                isset($destination)
                && (
                    is_resource($destination)
                    || $destination instanceof \GdImage
                )
            ) {
                imagedestroy(
                    $destination
                );
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE TEMPORARY FILE
            |--------------------------------------------------------------------------
            */

            if (
                $temporaryPath
                && is_file($temporaryPath)
            ) {
                @unlink(
                    $temporaryPath
                );
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE ATTACHMENT
    |--------------------------------------------------------------------------
    */

    private function deleteAttachment(
        ?string $path
    ): void {
        if (
            !$path ||
            trim($path) === ''
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | URL EXTERNAL JANGAN DIHAPUS
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
                $disk->exists(
                    $path
                )
            ) {
                $disk->delete(
                    $path
                );
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

    private function getMimeTypeFromPath(
        string $path
    ): string {
        $extension = strtolower(
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

    private function isValidDate(
        ?string $date
    ): bool {
        if ($date === null) {
            return false;
        }

        $date = trim(
            $date
        );

        if (
            !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $date
            )
        ) {
            return false;
        }

        $parts = explode(
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