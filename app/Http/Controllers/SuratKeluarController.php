<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratKeluarRequest;
use App\Models\ActivityLog;
use App\Models\KategoriSurat;
use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SuratKeluarController extends Controller
{
    /**
     * Status surat keluar yang valid.
     */
    private const STATUS_OPTIONS = [
        'draf',
        'diproses',
        'disetujui',
        'dikirim',
        'diarsipkan',
    ];

    /**
     * Mendapatkan role user yang sedang login.
     */
    private function role(): string
    {
        $user = Auth::user();

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
     * Mengecek apakah user boleh mengelola surat.
     */
    private function canManage(): bool
    {
        return in_array(
            $this->role(),
            ['admin', 'pimpinan'],
            true
        );
    }

    /**
     * Pastikan user memiliki akses kelola.
     */
    private function ensureCanManage(): void
    {
        abort_unless(
            $this->canManage(),
            403,
            'Anda tidak memiliki izin untuk mengelola surat keluar.'
        );
    }

    /**
     * Pastikan user memiliki akses melihat.
     */
    private function ensureCanView(): void
    {
        abort_unless(
            in_array(
                $this->role(),
                ['admin', 'pimpinan', 'staf'],
                true
            ),
            403,
            'Anda tidak memiliki izin untuk melihat surat keluar.'
        );
    }

    /**
     * Mendapatkan disk storage aktif.
     */
    private function getStorageDisk(): string
    {
        return (string) config(
            'filesystems.default',
            'local'
        );
    }

    /**
     * Mendapatkan instance filesystem.
     */
    private function storage(): Filesystem
    {
        return Storage::disk(
            $this->getStorageDisk()
        );
    }

    /**
     * Menampilkan daftar surat keluar.
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
        $this->ensureCanView();

        /*
        |--------------------------------------------------------------------------
        | QUERY DASAR
        |--------------------------------------------------------------------------
        */
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
            $query->where(function ($q) use ($search) {
                $q->where(
                    'perihal',
                    'like',
                    '%' . $search . '%'
                )
                    ->orWhere(
                        'nomor_surat',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'nomor_agenda',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'pengirim',
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
                    && ctype_digit(
                        (string) $id
                    )
            )
            ->map(
                fn ($id) =>
                    (int) $id
            )
            ->unique()
            ->values();

        if ($kategoriIds->isNotEmpty()) {
            $query->whereIn(
                'kategori_id',
                $kategoriIds->all()
            );
        }

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
        */
        $dariTanggal = trim(
            (string) $request->input(
                'dari_tanggal',
                ''
            )
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
            (string) $request->input(
                'sampai_tanggal',
                ''
            )
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
        $this->ensureCanManage();

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
        $this->ensureCanManage();

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | PEMBUAT
        |--------------------------------------------------------------------------
        */
        $data['dibuat_oleh'] = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | STATUS DEFAULT
        |--------------------------------------------------------------------------
        */
        if (
            empty($data['status'])
            || !in_array(
                strtolower(
                    trim(
                        (string) $data['status']
                    )
                ),
                self::STATUS_OPTIONS,
                true
            )
        ) {
            $data['status'] = 'draf';
        } else {
            $data['status'] = strtolower(
                trim(
                    (string) $data['status']
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | UPLOAD FILE / HASIL KAMERA
        |--------------------------------------------------------------------------
        */
        if (
            !empty(
                $data['captured_image']
            )
        ) {
            $data['lampiran_file'] =
                $this->saveCapturedImage(
                    $data['captured_image']
                );

            unset(
                $data['captured_image']
            );
        } elseif (
            $request->hasFile(
                'lampiran_file'
            )
        ) {
            $data['lampiran_file'] =
                $request
                    ->file('lampiran_file')
                    ->store(
                        'surat-keluar',
                        $this->getStorageDisk()
                    );
        }

        unset(
            $data['captured_image']
        );

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
        ActivityLog::catat(
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
    }

    /**
     * Menampilkan detail surat keluar.
     */
    public function show(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureCanView();

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

    /**
     * Form edit surat keluar.
     */
    public function edit(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureCanManage();

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
        $this->ensureCanManage();

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI STATUS
        |--------------------------------------------------------------------------
        */
        if (
            isset($data['status'])
        ) {
            $status = strtolower(
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
                $data['status'] = $status;
            } else {
                unset(
                    $data['status']
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | LAMPIRAN BARU DARI KAMERA
        |--------------------------------------------------------------------------
        */
        if (
            !empty(
                $data['captured_image']
            )
        ) {
            $this->deleteAttachment(
                $suratKeluar->lampiran_file
            );

            $data['lampiran_file'] =
                $this->saveCapturedImage(
                    $data['captured_image']
                );

            unset(
                $data['captured_image']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LAMPIRAN BARU DARI FILE UPLOAD
        |--------------------------------------------------------------------------
        */
        elseif (
            $request->hasFile(
                'lampiran_file'
            )
        ) {
            $this->deleteAttachment(
                $suratKeluar->lampiran_file
            );

            $data['lampiran_file'] =
                $request
                    ->file('lampiran_file')
                    ->store(
                        'surat-keluar',
                        $this->getStorageDisk()
                    );
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
        $suratKeluar->update(
            $data
        );

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */
        ActivityLog::catat(
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
    }

    /**
     * Menghapus surat keluar.
     *
     * Jika model menggunakan SoftDeletes,
     * data akan masuk ke tempat sampah.
     */
    public function destroy(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureCanManage();

        $id = $suratKeluar->id;

        $suratKeluar->delete();

        ActivityLog::catat(
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
    }

    /**
     * Preview lampiran surat keluar.
     */
    public function previewLampiran(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureCanView();

        abort_if(
            empty(
                $suratKeluar->lampiran_file
            ),
            404,
            'Lampiran tidak tersedia.'
        );

        $disk = $this->storage();

        $path =
            $suratKeluar->lampiran_file;

        /*
        |--------------------------------------------------------------------------
        | TEMPORARY URL
        |--------------------------------------------------------------------------
        |
        | Digunakan terutama untuk Supabase/S3.
        |
        */
        if (
            method_exists(
                $disk,
                'temporaryUrl'
            )
        ) {
            try {
                return redirect()->away(
                    $disk->temporaryUrl(
                        $path,
                        now()->addMinutes(10)
                    )
                );
            } catch (\Throwable $e) {
                Log::warning(
                    'Gagal membuat temporary URL surat keluar: ' .
                    $e->getMessage()
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK STREAM / FILE
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
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];

        $mimeType =
            $mimeTypes[$extension]
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
        $this->ensureCanView();

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

    /**
     * Halaman label surat keluar.
     */
    public function label(
        SuratKeluar $suratKeluar
    ) {
        $this->ensureCanView();

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

    /**
     * Menyimpan log surat keluar.
     */
    public function storeLog(
        Request $request,
        SuratKeluar $suratKeluar
    ) {
        $this->ensureCanManage();

        ActivityLog::catat(
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

    /**
     * Menyimpan gambar hasil kamera.
     */
    private function saveCapturedImage(
        string $image
    ): string {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI FORMAT DATA
        |--------------------------------------------------------------------------
        */
        if (
            !str_contains(
                $image,
                ','
            )
        ) {
            throw new \RuntimeException(
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
        $extension = 'jpg';

        if (
            str_contains(
                $meta,
                'image/png'
            )
        ) {
            $extension = 'png';
        } elseif (
            str_contains(
                $meta,
                'image/webp'
            )
        ) {
            $extension = 'webp';
        } elseif (
            str_contains(
                $meta,
                'image/jpeg'
            )
        ) {
            $extension = 'jpg';
        }

        /*
        |--------------------------------------------------------------------------
        | DECODE BASE64
        |--------------------------------------------------------------------------
        */
        $decoded =
            base64_decode(
                $content,
                true
            );

        if (
            $decoded === false
        ) {
            throw new \RuntimeException(
                'Data gambar tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | NAMA FILE
        |--------------------------------------------------------------------------
        */
        $path =
            'surat-keluar/' .
            uniqid(
                'capture_',
                true
            ) .
            '.' .
            $extension;

        /*
        |--------------------------------------------------------------------------
        | SIMPAN STORAGE
        |--------------------------------------------------------------------------
        */
        $saved =
            $this->storage()->put(
                $path,
                $decoded
            );

        if (
            $saved === false
        ) {
            throw new \RuntimeException(
                'Gambar hasil kamera gagal disimpan ke storage.'
            );
        }

        return $path;
    }

    /**
     * Menghapus lampiran lama.
     */
    private function deleteAttachment(
        ?string $path
    ): void {
        if (
            empty($path)
        ) {
            return;
        }

        try {
            $disk = $this->storage();

            if (
                $disk->exists($path)
            ) {
                $disk->delete($path);
            }
        } catch (\Throwable $e) {
            Log::warning(
                'Gagal menghapus lampiran surat keluar: ' .
                $e->getMessage()
            );
        }
    }

    /**
     * Validasi tanggal format YYYY-MM-DD.
     */
    private function isValidDate(
        ?string $date
    ): bool {
        if (
            !$date
        ) {
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