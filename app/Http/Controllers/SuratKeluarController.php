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

    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /*
    |--------------------------------------------------------------------------
    | AUTH / ROLE
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil role user yang sedang login.
     *
     * staf -> staff
     */
    private function userRole(): string
    {
        $user = Auth::user();

        if (!$user) {
            return '';
        }

        $role = strtolower(
            trim(
                (string) ($user->role ?? '')
            )
        );

        if ($role === 'staf') {
            $role = 'staff';
        }

        return $role;
    }

    /**
     * Nama method sengaja dibuat berbeda dari Controller::ensureAuthenticated()
     * agar tidak bentrok dengan method parent.
     */
    private function ensureUserAuthenticated(): void
    {
        abort_unless(
            Auth::check(),
            401,
            'Anda harus login terlebih dahulu.'
        );
    }

    /**
     * Memastikan user boleh mengelola surat keluar.
     *
     * Admin dan Pimpinan:
     * - tambah
     * - edit
     * - hapus
     *
     * Staff:
     * - tidak boleh mengelola
     */
    private function authorizeManageSurat(): void
    {
        $this->ensureUserAuthenticated();

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
     * Normalisasi status.
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

        return $status === 'draf'
            ? 'draft'
            : $status;
    }

    /**
     * Mengambil status yang valid.
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
        $this->ensureUserAuthenticated();

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
                            'tujuan_surat',
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

        $kategoriIds = collect($kategoriInput)
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
            $statusInput = [
                $statusInput,
            ];
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
            | UPLOAD LAMPIRAN
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('lampiran_file')) {
                $storedAttachment = $this->storeUploadedFile(
                    $request->file('lampiran_file')
                );

                $data['lampiran_file'] = $storedAttachment;
            }

            /*
            |--------------------------------------------------------------------------
            | SIMPAN DATABASE
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
            | HAPUS FILE JIKA DATABASE GAGAL
            |--------------------------------------------------------------------------
            */

            if ($storedAttachment) {
                $this->deleteAttachment(
                    $storedAttachment
                );
            }

            Log::error(
                'Gagal menyimpan surat keluar.',
                [
                    'message' => $e->getMessage(),
                    'user_id' => Auth::id(),
                    'disk' => $this->getStorageDisk(),
                    'file' => $request->hasFile('lampiran_file')
                        ? $request
                            ->file('lampiran_file')
                            ->getClientOriginalName()
                        : null,
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
     */
    public function update(
        SuratKeluarRequest $request,
        SuratKeluar $suratKeluar
    ) {
        $this->authorizeManageSurat();

        $data = $request->validated();

        if (array_key_exists('status', $data)) {
            $data['status'] = $this->getValidStatus(
                $data['status']
            );
        }

        $oldAttachment = $suratKeluar->lampiran_file;
        $newAttachment = null;

        try {
            /*
            |--------------------------------------------------------------------------
            | UPLOAD FILE BARU
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('lampiran_file')) {
                $newAttachment = $this->storeUploadedFile(
                    $request->file('lampiran_file')
                );

                $data['lampiran_file'] = $newAttachment;
            } else {
                /*
                 * Jangan menghapus attachment lama.
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
                $newAttachment
                && $oldAttachment
                && $oldAttachment !== $newAttachment
            ) {
                $this->deleteAttachment(
                    $oldAttachment
                );
            }

            /*
            |--------------------------------------------------------------------------
            | ACTIVITY LOG
            |--------------------------------------------------------------------------
            */

            $this->logActivity(
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
        } catch (Throwable $e) {
            /*
            |--------------------------------------------------------------------------
            | HAPUS FILE BARU JIKA UPDATE GAGAL
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
                    'surat_keluar_id' => $suratKeluar->id,
                    'message' => $e->getMessage(),
                    'user_id' => Auth::id(),
                    'disk' => $this->getStorageDisk(),
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

        $attachment = $suratKeluar->lampiran_file;

        try {
            $suratKeluar->delete();

            /*
             * File attachment ikut dihapus.
             */
            if ($attachment) {
                $this->deleteAttachment(
                    $attachment
                );
            }

            $this->logActivity(
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
        } catch (Throwable $e) {
            Log::error(
                'Gagal menghapus surat keluar.',
                [
                    'surat_keluar_id' => $id,
                    'message' => $e->getMessage(),
                    'user_id' => Auth::id(),
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

    /**
     * Preview lampiran surat keluar.
     */
    public function previewLampiran(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureUserAuthenticated();

        $path = $suratKeluar->lampiran_file;

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

        $diskName = $this->getStorageDisk();

        try {
            $disk = $this->storage();

            /*
            |--------------------------------------------------------------------------
            | CEK FILE
            |--------------------------------------------------------------------------
            */

            if (!$disk->exists($path)) {
                abort(
                    404,
                    'File lampiran tidak ditemukan di storage.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SUPABASE PRIVATE URL
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
                    $temporaryUrl = $disk->temporaryUrl(
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
                            'message' => $e->getMessage(),
                        ]
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FALLBACK
            |--------------------------------------------------------------------------
            */

            $content = $disk->get($path);

            return response(
                $content,
                200,
                [
                    'Content-Type' => $this->getMimeTypeFromPath(
                        $path
                    ),
                    'Content-Disposition' => 'inline',
                    'Cache-Control' => 'private, max-age=300',
                ]
            );
        } catch (Throwable $e) {
            Log::error(
                'Gagal preview lampiran surat keluar.',
                [
                    'path' => $path,
                    'message' => $e->getMessage(),
                    'disk' => $diskName,
                    'surat_keluar_id' => $suratKeluar->id,
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

    /**
     * Halaman cetak surat keluar.
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

    /**
     * Halaman label surat keluar.
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

    /**
     * Menambahkan activity log surat keluar.
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
    | STORAGE
    |--------------------------------------------------------------------------
    */

    /**
     * Mendapatkan disk storage aktif.
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
     * Menyimpan file upload langsung ke storage.
     *
     * Tidak menggunakan:
     * - GD
     * - imagecreatefromstring()
     * - imagejpeg()
     * - file_get_contents()
     *
     * sehingga tidak membuat bitmap besar di RAM PHP.
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
        | CEK UPLOAD PHP
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
        | CEK UKURAN
        |--------------------------------------------------------------------------
        */

        $fileSize = $file->getSize();

        if (
            $fileSize === false
            || $fileSize <= 0
        ) {
            throw new RuntimeException(
                'Ukuran file tidak dapat dibaca.'
            );
        }

        if (
            $fileSize > self::MAX_FILE_SIZE
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
        | MIME TYPE
        |--------------------------------------------------------------------------
        */

        $mimeType = strtolower(
            (string) $file->getMimeType()
        );

        if (
            !in_array(
                $mimeType,
                self::ALLOWED_MIME_TYPES,
                true
            )
        ) {
            throw new RuntimeException(
                'Tipe file tidak didukung. ' .
                'Gunakan PDF, JPG, JPEG, atau PNG.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PASTIKAN EXTENSION DAN MIME SESUAI
        |--------------------------------------------------------------------------
        */

        if (
            $extension === 'pdf'
            && $mimeType !== 'application/pdf'
        ) {
            throw new RuntimeException(
                'File PDF tidak valid.'
            );
        }

        if (
            $extension === 'jpg'
            && $mimeType !== 'image/jpeg'
        ) {
            throw new RuntimeException(
                'File JPG/JPEG tidak valid.'
            );
        }

        if (
            $extension === 'png'
            && $mimeType !== 'image/png'
        ) {
            throw new RuntimeException(
                'File PNG tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NAMA FILE
        |--------------------------------------------------------------------------
        */

        $fileName =
            'surat-keluar_' .
            now()->format('Ymd_His') .
            '_' .
            Str::lower(
                Str::random(16)
            ) .
            '.' .
            $extension;

        /*
        |--------------------------------------------------------------------------
        | DIRECTORY
        |--------------------------------------------------------------------------
        */

        $directory = 'lampiran/surat_keluar';

        /*
        |--------------------------------------------------------------------------
        | STORAGE OPTIONS
        |--------------------------------------------------------------------------
        */

        $options = [
            'ContentType' => $mimeType,
        ];

        /*
         * Bucket Supabase bersifat private.
         */
        if (
            $this->getStorageDisk() === 'supabase'
        ) {
            $options['visibility'] = 'private';
        }

        /*
        |--------------------------------------------------------------------------
        | SIMPAN FILE
        |--------------------------------------------------------------------------
        |
        | putFileAs() menggunakan UploadedFile secara langsung.
        |
        | Ini penting agar PHP tidak melakukan:
        |
        | file_get_contents()
        | imagecreatefromstring()
        | imagejpeg()
        |
        | sehingga tidak terjadi penggunaan RAM besar seperti sebelumnya.
        |
        */

        try {
            $disk = $this->storage();

            $savedPath = $disk->putFileAs(
                $directory,
                $file,
                $fileName,
                $options
            );

            if (
                $savedPath === false
                || $savedPath === null
                || $savedPath === ''
            ) {
                throw new RuntimeException(
                    'File gagal disimpan ke storage.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | PASTIKAN FILE BENAR-BENAR ADA
            |--------------------------------------------------------------------------
            */

            if (
                !$disk->exists(
                    $savedPath
                )
            ) {
                throw new RuntimeException(
                    'File berhasil diproses tetapi tidak ditemukan di storage.'
                );
            }

            return $savedPath;
        } catch (Throwable $e) {
            Log::error(
                'Gagal menyimpan file surat keluar.',
                [
                    'message' => $e->getMessage(),
                    'path' => $directory . '/' . $fileName,
                    'extension' => $extension,
                    'mime' => $mimeType,
                    'size' => $fileSize,
                    'disk' => $this->getStorageDisk(),
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
        |--------------------------------------------------------------------------
        | JANGAN HAPUS URL EXTERNAL
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
            $disk = $this->storage();

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
                    'path' => $path,
                    'message' => $e->getMessage(),
                    'disk' => $this->getStorageDisk(),
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

    /**
     * Memvalidasi tanggal format YYYY-MM-DD.
     */
    private function isValidDate(
        ?string $date
    ): bool {
        if ($date === null) {
            return false;
        }

        $date = trim($date);

        /*
         * Regex yang benar:
         * YYYY-MM-DD
         */
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
            /*
             * Activity log tidak boleh
             * menggagalkan proses surat.
             */
            Log::warning(
                'Gagal mencatat Activity Log surat keluar.',
                [
                    'message' => $e->getMessage(),
                    'action' => $action,
                    'module' => $module,
                ]
            );
        }
    }
}