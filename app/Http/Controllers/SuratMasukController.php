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
    private function getStorageDisk(): string
    {
        return config('filesystems.default', 'public') === 'supabase'
            ? 'supabase'
            : 'public';
    }

    private function storage(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($this->getStorageDisk());
        return $disk;
    }

    private function getUserRole(): string
    {
        $user = Auth::user();

        if (!$user) {
            return '';
        }

        $role = strtolower(trim((string) ($user->role ?? $user->jabatan ?? '')));
        return $role === 'staff' ? 'staf' : $role;
    }

    private function isStaff(): bool
    {
        return $this->getUserRole() === 'staf';
    }

    private function canManage(): bool
    {
        return in_array($this->getUserRole(), ['admin', 'pimpinan'], true);
    }

    private function ensureCanManage(): void
    {
        if (!$this->canManage()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki hak untuk mengelola surat masuk.');
        }
    }

    private function staffCanAccess(SuratMasuk $suratMasuk): bool
    {
        if (!$this->isStaff()) {
            return true;
        }

        return $suratMasuk->disposisi()
            ->where('kepada_user_id', Auth::id())
            ->exists();
    }

    private function ensureCanView(SuratMasuk $suratMasuk): void
    {
        if (!$this->staffCanAccess($suratMasuk)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki hak akses untuk melihat surat ini.');
        }
    }

    public function index(Request $request)
    {
        $statusOptions = [
            'baru',
            'diproses',
            'didisposisikan',
            'selesai',
            'diarsipkan',
        ];

        $kategoriIds = collect((array) $request->input('kategori_id', []))
            ->filter(fn ($id) => is_scalar($id) && ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $statuses = collect((array) $request->input('status', []))
            ->filter(fn ($status) => is_scalar($status))
            ->map(fn ($status) => strtolower(trim((string) $status)))
            ->filter(fn ($status) => in_array($status, $statusOptions, true))
            ->unique()
            ->values()
            ->all();

        $query = SuratMasuk::query()->with('kategori');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('perihal', 'like', "%{$search}%")
                    ->orWhere('asal_surat', 'like', "%{$search}%");
            });
        }

        if ($kategoriIds) {
            $query->whereIn('kategori_surat_id', $kategoriIds);
        }

        if ($statuses) {
            $query->whereIn('status', $statuses);
        }

        if ($request->filled('dari_tanggal')) {
            $query->whereDate(
                'tanggal_terima',
                '>=',
                $request->input('dari_tanggal')
            );
        }

        if ($request->filled('sampai_tanggal')) {
            $query->whereDate(
                'tanggal_terima',
                '<=',
                $request->input('sampai_tanggal')
            );
        }

        if ($this->isStaff()) {
            $query->whereHas('disposisi', function ($q) {
                $q->where('kepada_user_id', Auth::id());
            });
        }

        $suratMasuks = $query
            ->latest('tanggal_terima')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $kategoris = KategoriSurat::query()
            ->orderBy('nama_kategori')
            ->get();

        return view('surat_masuk.index', compact('suratMasuks', 'kategoris'));
    }

    public function create()
    {
        $this->ensureCanManage();

        $kategoris = KategoriSurat::query()
            ->orderBy('nama_kategori')
            ->get();

        $nomorAgenda = SuratMasuk::generateNomorAgenda();

        return view('surat_masuk.create', compact('kategoris', 'nomorAgenda'));
    }

    public function store(SuratMasukRequest $request)
    {
        $this->ensureCanManage();

        $data = $request->validated();
        $diskName = $this->getStorageDisk();
        $uploadedFile = null;

        DB::beginTransaction();

        try {
            $nomorInput = trim((string) $request->input('nomor_agenda', ''));

            if (
                $nomorInput === '' ||
                SuratMasuk::where('nomor_agenda', $nomorInput)->exists()
            ) {
                $data['nomor_agenda'] = SuratMasuk::generateNomorAgenda();
            } else {
                $data['nomor_agenda'] = $nomorInput;
            }

            $data['diterima_oleh'] = Auth::id();

            if ($request->filled('captured_image')) {
                $uploadedFile = $this->uploadBase64Image(
                    $request->input('captured_image')
                );

                $data['lampiran_file'] = $uploadedFile;
            } elseif (
                $request->hasFile('lampiran_file') &&
                $request->file('lampiran_file')->isValid()
            ) {
                $uploadedFile = $request->file('lampiran_file')->store(
                    'lampiran/surat_masuk',
                    $diskName
                );

                if (!$uploadedFile) {
                    throw new \Exception('File lampiran gagal disimpan ke storage.');
                }

                $data['lampiran_file'] = $uploadedFile;
            }

            $surat = SuratMasuk::create($data);

            $this->logActivity(
                'create',
                'surat_masuk',
                "Menambah surat masuk {$surat->nomor_agenda} - {$surat->perihal}"
            );

            DB::commit();

            return redirect()
                ->route('surat-masuk.index')
                ->with(
                    'success',
                    'Surat masuk berhasil dicatat dengan nomor agenda ' .
                    $surat->nomor_agenda
                );
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($uploadedFile) {
                $this->deleteStorageFile($uploadedFile, $diskName);
            }

            Log::error('Gagal Simpan Surat Masuk', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan surat masuk: ' . $e->getMessage());
        }
    }

    public function show(SuratMasuk $suratMasuk)
    {
        $this->ensureCanView($suratMasuk);

        $suratMasuk->load([
            'kategori',
            'disposisi.dari',
            'disposisi.kepada',
        ]);

        $daftarStaf = User::query()
            ->whereIn('role', ['staf', 'staff'])
            ->orderBy('name')
            ->get();

        $fileUrl = $this->getFileUrl($suratMasuk->lampiran_file);

        return view(
            'surat_masuk.show',
            compact('suratMasuk', 'fileUrl', 'daftarStaf')
        );
    }

    public function storeDisposisi(Request $request, SuratMasuk $suratMasuk)
    {
        if (!$this->canManage()) {
            return back()->with(
                'error',
                'Anda tidak memiliki hak akses untuk melakukan disposisi surat.'
            );
        }

        $validated = $request->validate([
            'tujuan_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->whereIn('role', ['staf', 'staff'])
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
        ], [
            'tujuan_user_id.required' => 'Staf tujuan wajib dipilih.',
            'tujuan_user_id.exists' => 'Staf tujuan tidak valid.',
            'instruksi.required' => 'Instruksi disposisi wajib diisi.',
            'instruksi.max' => 'Instruksi disposisi maksimal 500 karakter.',
            'batas_waktu.date' => 'Batas waktu disposisi tidak valid.',
        ]);

        DB::beginTransaction();

        try {
            $instruksi = trim($validated['instruksi']);

            $suratMasuk->disposisi()->create([
                'dari_user_id' => Auth::id(),
                'kepada_user_id' => $validated['tujuan_user_id'],
                'instruksi' => $instruksi,
                'isi_disposisi' => $instruksi,
                'batas_waktu' => $validated['batas_waktu'] ?? null,
                'status' => 'Pending',
            ]);

            $suratMasuk->update([
                'status' => 'didisposisikan',
            ]);

            $this->logActivity(
                'disposisi',
                'surat_masuk',
                "Melakukan disposisi surat masuk {$suratMasuk->nomor_agenda}"
            );

            DB::commit();

            return back()->with(
                'success',
                'Disposisi surat berhasil dikirim ke staf yang dituju.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal Disposisi Surat Masuk', [
                'message' => $e->getMessage(),
                'surat_id' => $suratMasuk->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal memproses disposisi: ' . $e->getMessage());
        }
    }

    public function edit(SuratMasuk $suratMasuk)
    {
        $this->ensureCanManage();

        $kategoris = KategoriSurat::query()
            ->orderBy('nama_kategori')
            ->get();

        return view('surat_masuk.edit', compact('suratMasuk', 'kategoris'));
    }

    public function update(SuratMasukRequest $request, SuratMasuk $suratMasuk)
    {
        $this->ensureCanManage();

        $data = $request->validated();
        $diskName = $this->getStorageDisk();
        $oldFile = $suratMasuk->lampiran_file;
        $newFile = null;

        DB::beginTransaction();

        try {
            if ($request->filled('captured_image')) {
                $newFile = $this->uploadBase64Image(
                    $request->input('captured_image')
                );

                $data['lampiran_file'] = $newFile;
            } elseif (
                $request->hasFile('lampiran_file') &&
                $request->file('lampiran_file')->isValid()
            ) {
                $newFile = $request->file('lampiran_file')->store(
                    'lampiran/surat_masuk',
                    $diskName
                );

                if (!$newFile) {
                    throw new \Exception(
                        'File lampiran baru gagal disimpan ke storage.'
                    );
                }

                $data['lampiran_file'] = $newFile;
            } else {
                unset($data['lampiran_file']);
            }

            $suratMasuk->update($data);

            $this->logActivity(
                'update',
                'surat_masuk',
                "Mengubah surat masuk {$suratMasuk->nomor_agenda}"
            );

            DB::commit();

            if ($newFile && $oldFile && $oldFile !== $newFile) {
                $this->deleteStorageFile($oldFile, $diskName);
            }

            return redirect()
                ->route('surat-masuk.index')
                ->with('success', 'Surat masuk berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($newFile && $newFile !== $oldFile) {
                $this->deleteStorageFile($newFile, $diskName);
            }

            Log::error('Gagal Update Surat Masuk', [
                'message' => $e->getMessage(),
                'surat_id' => $suratMasuk->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal memperbarui surat masuk: ' . $e->getMessage()
                );
        }
    }

    public function destroy(SuratMasuk $suratMasuk)
    {
        $this->ensureCanManage();

        $nomor = $suratMasuk->nomor_agenda;

        DB::beginTransaction();

        try {
            $suratMasuk->delete();

            $this->logActivity(
                'delete',
                'surat_masuk',
                "Menghapus surat masuk {$nomor}"
            );

            DB::commit();

            return back()->with(
                'success',
                'Surat masuk berhasil dipindahkan ke arsip sampah.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Gagal Hapus Surat Masuk', [
                'message' => $e->getMessage(),
                'surat_id' => $suratMasuk->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with(
                'error',
                'Gagal menghapus surat masuk: ' . $e->getMessage()
            );
        }
    }

    public function cetakLabel(SuratMasuk $suratMasuk)
    {
        $this->ensureCanView($suratMasuk);
        $suratMasuk->load('kategori');

        return view('surat_masuk.label', compact('suratMasuk'));
    }

    public function previewLampiran(SuratMasuk $suratMasuk)
    {
        $this->ensureCanView($suratMasuk);

        $file = $suratMasuk->lampiran_file;

        if (!$file) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        if (filter_var($file, FILTER_VALIDATE_URL)) {
            return redirect()->away($file);
        }

        $diskName = $this->getStorageDisk();

        try {
            $disk = $this->storage();

            if (!$disk->exists($file)) {
                abort(404, 'File lampiran tidak ditemukan di storage.');
            }

            if ($diskName === 'supabase') {
                $url = $disk->temporaryUrl(
                    $file,
                    now()->addMinutes(30)
                );

                return redirect()->away($url);
            }

            return $disk->response($file);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Gagal Preview Lampiran Surat Masuk', [
                'message' => $e->getMessage(),
                'file' => $file,
                'disk' => $diskName,
                'surat_id' => $suratMasuk->id,
            ]);

            return back()->with(
                'error',
                'Gagal membuka lampiran file: ' . $e->getMessage()
            );
        }
    }

    public function downloadLampiran(SuratMasuk $suratMasuk)
    {
        $this->ensureCanView($suratMasuk);

        $file = $suratMasuk->lampiran_file;

        if (!$file) {
            abort(404, 'File lampiran tidak ditemukan.');
        }

        if (filter_var($file, FILTER_VALIDATE_URL)) {
            return redirect()->away($file);
        }

        $diskName = $this->getStorageDisk();

        try {
            $disk = $this->storage();

            if (!$disk->exists($file)) {
                abort(404, 'File lampiran tidak ditemukan di storage.');
            }

            if ($diskName === 'supabase') {
                $url = $disk->temporaryUrl(
                    $file,
                    now()->addMinutes(30)
                );

                return redirect()->away($url);
            }

            return $disk->download($file, basename($file));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Gagal Download Lampiran Surat Masuk', [
                'message' => $e->getMessage(),
                'file' => $file,
                'disk' => $diskName,
                'surat_id' => $suratMasuk->id,
            ]);

            return back()->with(
                'error',
                'Gagal mengunduh lampiran file: ' . $e->getMessage()
            );
        }
    }

    public function cetakDisposisi(SuratMasuk $suratMasuk)
    {
        $this->ensureCanView($suratMasuk);

        $suratMasuk->load([
            'kategori',
            'disposisi.dari',
            'disposisi.kepada',
        ]);

        return view(
            'surat_masuk.disposisi_pdf',
            compact('suratMasuk')
        );
    }

    private function uploadBase64Image(string $base64String): string
    {
        $base64String = trim($base64String);

        if ($base64String === '') {
            throw new \Exception('Data gambar kamera kosong.');
        }

        $extension = 'jpg';
        $contentType = 'image/jpeg';

        if (preg_match(
            '/^data:(image\/(?:png|jpeg|jpg|webp));base64,/i',
            $base64String,
            $matches
        )) {
            $contentType = strtolower($matches[1]);
            $extension = str_replace('image/', '', $contentType);

            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }

            $base64String = substr(
                $base64String,
                strpos($base64String, ',') + 1
            );
        }

        $decodedData = base64_decode($base64String, true);

        if ($decodedData === false || $decodedData === '') {
            throw new \Exception(
                'Gagal memproses gambar kamera. Format Base64 tidak valid.'
            );
        }

        $imageInfo = @getimagesizefromstring($decodedData);

        if ($imageInfo === false) {
            throw new \Exception(
                'Data dari kamera bukan merupakan gambar yang valid.'
            );
        }

        if (
            !empty($imageInfo['mime']) &&
            str_starts_with(strtolower($imageInfo['mime']), 'image/')
        ) {
            $contentType = strtolower($imageInfo['mime']);
        }

        $mimeToExtension = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        $extension = $mimeToExtension[$contentType] ?? $extension;

        $fileName = 'scan_' .
            now()->format('YmdHis') .
            '_' .
            Str::random(8) .
            '.' .
            $extension;

        $path = 'lampiran/surat_masuk/' . $fileName;
        $diskName = $this->getStorageDisk();
        $disk = $this->storage();

        $options = [
            'ContentType' => $contentType,
        ];

        if ($diskName === 'supabase') {
            $options['visibility'] = 'private';
        }

        $saved = $disk->put(
            $path,
            $decodedData,
            $options
        );

        if (!$saved) {
            throw new \Exception(
                'Gagal menyimpan hasil scan kamera ke storage.'
            );
        }

        if (!$disk->exists($path)) {
            throw new \Exception(
                'File hasil scan tidak ditemukan setelah proses upload.'
            );
        }

        return $path;
    }

    private function getFileUrl(?string $file): ?string
    {
        if (!$file) {
            return null;
        }

        if (filter_var($file, FILTER_VALIDATE_URL)) {
            return $file;
        }

        $diskName = $this->getStorageDisk();

        try {
            $disk = $this->storage();

            if (!$disk->exists($file)) {
                return null;
            }

            if ($diskName === 'supabase') {
                return $disk->temporaryUrl(
                    $file,
                    now()->addMinutes(30)
                );
            }

            return $disk->url($file);
        } catch (\Throwable $e) {
            Log::warning('Gagal membuat URL lampiran Surat Masuk', [
                'message' => $e->getMessage(),
                'file' => $file,
                'disk' => $diskName,
            ]);

            return null;
        }
    }

    private function deleteStorageFile(
        ?string $file,
        ?string $diskName = null
    ): void {
        if (
            !$file ||
            filter_var($file, FILTER_VALIDATE_URL)
        ) {
            return;
        }

        $diskName ??= $this->getStorageDisk();

        try {
            $disk = Storage::disk($diskName);

            if ($disk->exists($file)) {
                $disk->delete($file);
            }
        } catch (\Throwable $e) {
            Log::warning(
                'Gagal menghapus file Surat Masuk dari storage',
                [
                    'message' => $e->getMessage(),
                    'file' => $file,
                    'disk' => $diskName,
                ]
            );
        }
    }

    private function logActivity(
        string $action,
        string $module,
        string $description
    ): void {
        try {
            if (
                class_exists(ActivityLog::class) &&
                method_exists(ActivityLog::class, 'catat')
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
                    'message' => $e->getMessage(),
                    'action' => $action,
                    'module' => $module,
                ]
            );
        }
    }
}