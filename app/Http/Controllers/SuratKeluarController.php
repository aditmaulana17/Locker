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
    /**
     * Status surat keluar yang valid.
     */
    private const STATUS_OPTIONS = [
        'draft',
        'draf',
        'diproses',
        'disetujui',
        'dikirim',
        'diarsipkan',
    ];

    /**
     * Mendapatkan storage disk yang aktif.
     *
     * Bila filesystem default adalah "supabase", gunakan disk supabase.
     * Selain itu gunakan default filesystem Laravel.
     */
    private function getStorageDisk(): string
    {
        $default = (string) config('filesystems.default', 'local');

        return $default === 'supabase'
            ? 'supabase'
            : $default;
    }

    /**
     * Mendapatkan instance filesystem.
     */
    private function storage(): Filesystem
    {
        return Storage::disk($this->getStorageDisk());
    }

    /**
     * Normalisasi status.
     *
     * "draf" dan "draft" akan diseragamkan menjadi "draft".
     */
    private function normalizeStatus(?string $status): string
    {
        $status = strtolower(trim((string) $status));

        if ($status === 'draf') {
            return 'draft';
        }

        return $status;
    }

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
            (string) $request->input('search', '')
        );

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('perihal', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('pengirim', 'like', "%{$search}%")
                    ->orWhere('asal_surat', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategoriIds = collect(
            $request->input('kategori_id', [])
        )
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
            $query->where(function ($q) use ($kategoriIds) {
                $q->whereIn(
                    'kategori_surat_id',
                    $kategoriIds->all()
                )->orWhereIn(
                    'kategori_id',
                    $kategoriIds->all()
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        $statuses = collect(
            $request->input('status', [])
        )
            ->filter(
                fn ($status) => is_scalar($status)
            )
            ->map(
                fn ($status) =>
                    $this->normalizeStatus((string) $status)
            )
            ->filter(
                fn ($status) =>
                    in_array(
                        $status,
                        [
                            'draft',
                            'diproses',
                            'disetujui',
                            'dikirim',
                            'diarsipkan',
                        ],
                        true
                    )
            )
            ->unique()
            ->values();

        if ($statuses->isNotEmpty()) {
            $query->where(function ($q) use ($statuses) {
                foreach ($statuses as $index => $status) {
                    if ($index === 0) {
                        $q->where('status', $status)
                            ->orWhere(function ($q2) use ($status) {
                                if ($status === 'draft') {
                                    $q2->where('status', 'draf');
                                }
                            });
                    } else {
                        $q->orWhere('status', $status);

                        if ($status === 'draft') {
                            $q->orWhere('status', 'draf');
                        }
                    }
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL MULAI
        |--------------------------------------------------------------------------
        */

        $dariTanggal = trim(
            (string) $request->input('dari_tanggal', '')
        );

        if ($this->isValidDate($dariTanggal)) {
            $query->whereDate(
                'tanggal_surat',
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
            (string) $request->input('sampai_tanggal', '')
        );

        if ($this->isValidDate($sampaiTanggal)) {
            $query->whereDate(
                'tanggal_surat',
                '<=',
                $sampaiTanggal
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ORDER + PAGINATION
        |--------------------------------------------------------------------------
        */

        $suratKeluars = $query
            ->orderByDesc('tanggal_surat')
            ->orderByDesc('created_at')
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
            'surat_keluar.index',
            compact(
                'suratKeluars',
                'kategoris'
            )
        );
    }

    /**
     * Form tambah surat keluar.
     */
    public function create()
    {
        $this->ensureCanManageSurat();

        $kategoris = KategoriSurat::query()
            ->orderBy('nama_kategori')
            ->get();

        $users = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'surat_keluar.create',
            compact(
                'kategoris',
                'users'
            )
        );
    }

    /**
     * Menyimpan surat keluar baru.
     */
    public function store(SuratKeluarRequest $request)
    {
        $this->ensureCanManageSurat();

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

        $data['status'] = $this->normalizeStatus(
            $data['status'] ?? 'draft'
        );

        if (
            !in_array(
                $data['status'],
                [
                    'draft',
                    'diproses',
                    'disetujui',
                    'dikirim',
                    'diarsipkan',
                ],
                true
            )
        ) {
            $data['status'] = 'draft';
        }

        /*
        |--------------------------------------------------------------------------
        | FILE / KAMERA
        |--------------------------------------------------------------------------
        */

        $capturedImage = $data['captured_image'] ?? null;

        unset($data['captured_image']);

        if (
            is_string($capturedImage)
            && trim($capturedImage) !== ''
        ) {
            $data['lampiran_file'] = $this->saveCapturedImage(
                $capturedImage
            );
        } elseif ($request->hasFile('lampiran_file')) {
            $data['lampiran_file'] = $this->storeUploadedFile(
                $request->file('lampiran_file')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SIMPAN DATABASE
        |--------------------------------------------------------------------------
        */

        $suratKeluar = SuratKeluar::create($data);

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
            ->route('surat-keluar.index')
            ->with(
                'success',
                'Surat keluar berhasil ditambahkan.'
            );
    }

    /**
     * Menampilkan detail surat keluar.
     */
    public function show(SuratKeluar $suratKeluar)
    {
        $this->ensureAuthenticated();

        $suratKeluar->load([
            'kategori',
            'pembuat',
            'penandatangan',
        ]);

        return view(
            'surat_keluar.show',
            compact('suratKeluar')
        );
    }

    /**
     * Form edit surat keluar.
     */
    public function edit(SuratKeluar $suratKeluar)
    {
        $this->ensureCanManageSurat();

        $kategoris = KategoriSurat::query()
            ->orderBy('nama_kategori')
            ->get();

        $users = User::query()
            ->where('is_active', true)
            ->orderBy('name')
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

    /**
     * Memperbarui surat keluar.
     */
    public function update(
        SuratKeluarRequest $request,
        SuratKeluar $suratKeluar
    ) {
        $this->ensureCanManageSurat();

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        if (array_key_exists('status', $data)) {
            $status = $this->normalizeStatus(
                $data['status']
            );

            if (
                in_array(
                    $status,
                    [
                        'draft',
                        'diproses',
                        'disetujui',
                        'dikirim',
                        'diarsipkan',
                    ],
                    true
                )
            ) {
                $data['status'] = $status;
            } else {
                unset($data['status']);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FILE / KAMERA BARU
        |--------------------------------------------------------------------------
        |
        | Penting:
        | File lama tidak dihapus sebelum file baru berhasil disimpan.
        |
        */

        $oldAttachment = $suratKeluar->lampiran_file;
        $newAttachment = null;

        $capturedImage = $data['captured_image'] ?? null;

        unset($data['captured_image']);

        try {
            if (
                is_string($capturedImage)
                && trim($capturedImage) !== ''
            ) {
                $newAttachment = $this->saveCapturedImage(
                    $capturedImage
                );
            } elseif ($request->hasFile('lampiran_file')) {
                $newAttachment = $this->storeUploadedFile(
                    $request->file('lampiran_file')
                );
            }

            /*
            |--------------------------------------------------------------------------
            | JIKA ADA FILE BARU
            |--------------------------------------------------------------------------
            */

            if ($newAttachment !== null) {
                $data['lampiran_file'] = $newAttachment;
            } else {
                /*
                |--------------------------------------------------------------------------
                | TIDAK ADA FILE BARU
                |--------------------------------------------------------------------------
                |
                | Biarkan lampiran lama tetap digunakan.
                |
                */
                unset($data['lampiran_file']);
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE DATABASE
            |--------------------------------------------------------------------------
            */

            $suratKeluar->update($data);

            /*
            |--------------------------------------------------------------------------
            | HAPUS FILE LAMA SETELAH DATABASE BERHASIL
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
            | BERSIHKAN FILE BARU JIKA UPDATE GAGAL
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
                    'surat_keluar_id' => $suratKeluar->id,
                    'message' => $e->getMessage(),
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
            ->route('surat-keluar.index')
            ->with(
                'success',
                'Surat keluar berhasil diperbarui.'
            );
    }

    /**
     * Menghapus surat keluar.
     *
     * Jika model menggunakan SoftDeletes,
     * data akan masuk ke tempat sampah.
     */
    public function destroy(SuratKeluar $suratKeluar)
    {
        $this->ensureCanManageSurat();

        $id = $suratKeluar->id;

        /*
        |--------------------------------------------------------------------------
        | FILE TIDAK DIHAPUS
        |--------------------------------------------------------------------------
        |
        | File sengaja dipertahankan karena data dapat direstore.
        |
        */

        $suratKeluar->delete();

        ActivityLog::catat(
            'delete',
            'surat_keluar',
            'Menghapus surat keluar #' . $id
        );

        return redirect()
            ->route('surat-keluar.index')
            ->with(
                'success',
                'Surat keluar berhasil dipindahkan ke sampah.'
            );
    }

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
        $path = $suratKeluar->lampiran_file;

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
        | FALLBACK STREAM
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $disk->exists($path),
            404,
            'File lampiran tidak ditemukan.'
        );

        $content = $disk->get($path);

        $extension = strtolower(
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
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];

        $mimeType = $mimeTypes[$extension]
            ?? 'application/octet-stream';

        return response(
            $content,
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline',
                'Cache-Control' => 'private, max-age=300',
            ]
        );
    }

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
            compact('suratKeluar')
        );
    }

    /**
     * Halaman label surat keluar.
     */
    public function label(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureAuthenticated();

        $suratKeluar->load('kategori');

        return view(
            'surat_keluar.label',
            compact('suratKeluar')
        );
    }

    /**
     * Menyimpan log surat keluar.
     */
    public function storeLog(
        Request $request,
        SuratKeluar $suratKeluar
    ) {
        $this->ensureCanManageSurat();

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

    /**
     * Menyimpan file upload dengan nama unik.
     *
     * Mendukung:
     * - PDF
     * - JPG
     * - JPEG
     * - PNG
     * - WEBP
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

        $diskName = $this->getStorageDisk();
        $disk = Storage::disk($diskName);

        /*
        |--------------------------------------------------------------------------
        | MIME AKTUAL
        |--------------------------------------------------------------------------
        */

        $mimeType = strtolower(
            (string) $file->getMimeType()
        );

        $extensionMap = [
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if (!isset($extensionMap[$mimeType])) {
            throw new RuntimeException(
                'Format file tidak didukung. Gunakan PDF, JPG, JPEG, PNG, atau WEBP.'
            );
        }

        $extension = $extensionMap[$mimeType];

        /*
        |--------------------------------------------------------------------------
        | NAMA FILE UNIK
        |--------------------------------------------------------------------------
        */

        $fileName =
            now()->format('Ymd_His')
            . '_'
            . Str::lower(
                Str::random(12)
            )
            . '.'
            . $extension;

        $directory = 'surat-keluar';

        /*
        |--------------------------------------------------------------------------
        | SIMPAN FILE
        |--------------------------------------------------------------------------
        */

        $stored = $disk->putFileAs(
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

        return $directory . '/' . $fileName;
    }

    /**
     * Menyimpan gambar hasil kamera.
     */
    private function saveCapturedImage(
        string $image
    ): string {
        $image = trim($image);

        /*
        |--------------------------------------------------------------------------
        | VALIDASI DATA URI
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '/^data:image\/(jpeg|jpg|png|webp);base64,/i',
                $image
            )
        ) {
            throw new RuntimeException(
                'Format gambar hasil kamera tidak valid.'
            );
        }

        [
            $meta,
            $content
        ] = explode(
            ',',
            $image,
            2
        );

        /*
        |--------------------------------------------------------------------------
        | TENTUKAN EXTENSION
        |--------------------------------------------------------------------------
        */

        $meta = strtolower($meta);

        $extension = match (true) {
            str_contains($meta, 'image/png') => 'png',
            str_contains($meta, 'image/webp') => 'webp',
            default => 'jpg',
        };

        /*
        |--------------------------------------------------------------------------
        | DECODE BASE64
        |--------------------------------------------------------------------------
        */

        $decoded = base64_decode(
            $content,
            true
        );

        if ($decoded === false) {
            throw new RuntimeException(
                'Data gambar kamera tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BATAS UKURAN 15 MB
        |--------------------------------------------------------------------------
        */

        if (
            strlen($decoded)
            > 15 * 1024 * 1024
        ) {
            throw new RuntimeException(
                'Ukuran gambar hasil kamera maksimal 15MB.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI IMAGE SEBENARNYA
        |--------------------------------------------------------------------------
        */

        $imageInfo = @getimagesizefromstring(
            $decoded
        );

        if ($imageInfo === false) {
            throw new RuntimeException(
                'Data hasil kamera bukan gambar yang valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PATH
        |--------------------------------------------------------------------------
        */

        $path =
            'surat-keluar/'
            . now()->format('Ymd_His')
            . '_capture_'
            . Str::lower(Str::random(12))
            . '.'
            . $extension;

        /*
        |--------------------------------------------------------------------------
        | SIMPAN STORAGE
        |--------------------------------------------------------------------------
        */

        $disk = $this->storage();

        $mimeType = match ($extension) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        $saved = $disk->put(
            $path,
            $decoded,
            [
                'visibility' => 'private',
                'ContentType' => $mimeType,
            ]
        );

        if ($saved === false) {
            throw new RuntimeException(
                'Gambar hasil kamera gagal disimpan ke storage.'
            );
        }

        return $path;
    }

    /**
     * Menghapus lampiran.
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

        try {
            $disk = $this->storage();

            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        } catch (Throwable $e) {
            Log::warning(
                'Gagal menghapus lampiran surat keluar.',
                [
                    'path' => $path,
                    'message' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Mendapatkan pesan upload berdasarkan kode error PHP.
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
                'Folder temporary upload tidak tersedia.',
            UPLOAD_ERR_CANT_WRITE =>
                'Server gagal menulis file upload.',
            UPLOAD_ERR_EXTENSION =>
                'Upload dihentikan oleh ekstensi PHP.',
            default =>
                'Terjadi kesalahan saat mengunggah file.',
        };
    }

    /**
     * Validasi tanggal format YYYY-MM-DD.
     */
    private function isValidDate(
        ?string $date
    ): bool {
        if (!$date) {
            return false;
        }

        $date = trim($date);

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
}