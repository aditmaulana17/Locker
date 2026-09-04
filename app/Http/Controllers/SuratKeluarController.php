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
    private function role(): string
    {
        $role = strtolower((string) (Auth::user()->role ?? ''));
        return $role === 'staff' ? 'staf' : $role;
    }

    private function canManage(): bool
    {
        return in_array($this->role(), ['admin', 'pimpinan'], true);
    }

    private function ensureCanManage(): void
    {
        abort_unless($this->canManage(), 403);
    }

    private function ensureCanView(): void
    {
        abort_unless(
            in_array($this->role(), ['admin', 'pimpinan', 'staf'], true),
            403
        );
    }

    private function getStorageDisk(): string
    {
        return (string) config('filesystems.default', 'local');
    }

    private function storage(): Filesystem
    {
        return Storage::disk($this->getStorageDisk());
    }

    public function index(Request $request)
    {
        $this->ensureCanView();

        $suratKeluars = SuratKeluar::with([
            'kategori',
            'pembuat',
            'penandatangan',
        ])
            ->filter($request->all())
            ->latest('tanggal_surat')
            ->paginate(10)
            ->withQueryString();

        $kategoris = KategoriSurat::orderBy('nama_kategori')->get();

        return view(
            'surat_keluar.index',
            compact('suratKeluars', 'kategoris')
        );
    }

    public function create()
    {
        $this->ensureCanManage();

        $kategoris = KategoriSurat::orderBy('nama_kategori')->get();

        $users = User::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view(
            'surat_keluar.create',
            compact('kategoris', 'users')
        );
    }

    public function store(SuratKeluarRequest $request)
    {
        $this->ensureCanManage();

        $data = $request->validated();
        $data['dibuat_oleh'] = Auth::id();

        if (!empty($data['captured_image'])) {
            $data['lampiran_file'] = $this->saveCapturedImage(
                $data['captured_image']
            );

            unset($data['captured_image']);
        } elseif ($request->hasFile('lampiran_file')) {
            $data['lampiran_file'] = $request
                ->file('lampiran_file')
                ->store(
                    'surat-keluar',
                    $this->getStorageDisk()
                );
        }

        unset($data['captured_image']);

        $suratKeluar = SuratKeluar::create($data);

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

    public function show(SuratKeluar $suratKeluar)
    {
        $this->ensureCanView();

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

    public function edit(SuratKeluar $suratKeluar)
    {
        $this->ensureCanManage();

        $kategoris = KategoriSurat::orderBy('nama_kategori')->get();

        $users = User::where('is_active', true)
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

    public function update(
        SuratKeluarRequest $request,
        SuratKeluar $suratKeluar
    ) {
        $this->ensureCanManage();

        $data = $request->validated();

        if (!empty($data['captured_image'])) {
            $this->deleteAttachment(
                $suratKeluar->lampiran_file
            );

            $data['lampiran_file'] = $this->saveCapturedImage(
                $data['captured_image']
            );

            unset($data['captured_image']);
        } elseif ($request->hasFile('lampiran_file')) {
            $this->deleteAttachment(
                $suratKeluar->lampiran_file
            );

            $data['lampiran_file'] = $request
                ->file('lampiran_file')
                ->store(
                    'surat-keluar',
                    $this->getStorageDisk()
                );
        } else {
            unset($data['lampiran_file']);
        }

        unset($data['captured_image']);

        $suratKeluar->update($data);

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

    public function destroy(SuratKeluar $suratKeluar)
    {
        $this->ensureCanManage();

        $id = $suratKeluar->id;

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

    public function previewLampiran(SuratKeluar $suratKeluar)
    {
        $this->ensureCanView();

        abort_if(
            empty($suratKeluar->lampiran_file),
            404,
            'Lampiran tidak tersedia.'
        );

        $disk = $this->storage();
        $path = $suratKeluar->lampiran_file;

        if (method_exists($disk, 'temporaryUrl')) {
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

        abort_unless(
            $disk->exists($path),
            404,
            'File lampiran tidak ditemukan.'
        );

        $content = $disk->get($path);

        $extension = strtolower(
            pathinfo($path, PATHINFO_EXTENSION)
        );

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
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

    public function cetak(SuratKeluar $suratKeluar)
    {
        $this->ensureCanView();

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

    public function label(SuratKeluar $suratKeluar)
    {
        $this->ensureCanView();

        $suratKeluar->load('kategori');

        return view(
            'surat_keluar.label',
            compact('suratKeluar')
        );
    }

    public function storeLog(
        Request $request,
        SuratKeluar $suratKeluar
    ) {
        $this->ensureCanManage();

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

    private function saveCapturedImage(string $image): string
    {
        if (!str_contains($image, ',')) {
            throw new \RuntimeException(
                'Format gambar hasil kamera tidak valid.'
            );
        }

        [$meta, $content] = explode(',', $image, 2);

        $extension = 'jpg';

        if (str_contains($meta, 'image/png')) {
            $extension = 'png';
        } elseif (str_contains($meta, 'image/webp')) {
            $extension = 'webp';
        }

        $decoded = base64_decode($content, true);

        if ($decoded === false) {
            throw new \RuntimeException(
                'Data gambar tidak valid.'
            );
        }

        $path = 'surat-keluar/' .
            uniqid('capture_', true) .
            '.' .
            $extension;

        $this->storage()->put($path, $decoded);

        return $path;
    }

    private function deleteAttachment(?string $path): void
    {
        if (!$path) {
            return;
        }

        try {
            $this->storage()->delete($path);
        } catch (\Throwable $e) {
            Log::warning(
                'Gagal menghapus lampiran surat keluar: ' .
                $e->getMessage()
            );
        }
    }
}