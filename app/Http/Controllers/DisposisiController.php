<?php

namespace App\Http\Controllers;

use App\Http\Requests\DisposisiRequest;
use App\Models\ActivityLog;
use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DisposisiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $this->role();

        $query = Disposisi::query()
            ->with(['suratMasuk', 'dari', 'kepada']);

        if ($role === 'staf') {
            $query->where('kepada_user_id', $user->id);
        }

        $search = trim((string) $request->input('search', ''));

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $query->where('isi_disposisi', 'like', "%{$search}%")
                    ->orWhere('instruksi', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%")
                    ->orWhereHas('suratMasuk', function (Builder $suratQuery) use ($search) {
                        $suratQuery
                            ->where('nomor_surat', 'like', "%{$search}%")
                            ->orWhere('nomor_agenda', 'like', "%{$search}%")
                            ->orWhere('perihal', 'like', "%{$search}%");
                    })
                    ->orWhereHas('kepada', function (Builder $userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('nama', 'like', "%{$search}%")
                            ->orWhere('jabatan', 'like', "%{$search}%");
                    });
            });
        }

        $statuses = $request->input('status', []);

        if (!is_array($statuses)) {
            $statuses = [$statuses];
        }

        $statuses = collect($statuses)
            ->map(fn ($status) => strtolower(trim((string) $status)))
            ->filter(fn ($status) => in_array($status, [
                'menunggu',
                'diproses',
                'selesai',
            ], true))
            ->unique()
            ->values()
            ->all();

        if (!empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        $dateColumn = $this->getDateColumn();

        if ($request->filled('dari_tanggal')) {
            $query->whereDate(
                $dateColumn,
                '>=',
                $request->input('dari_tanggal')
            );
        }

        if ($request->filled('sampai_tanggal')) {
            $query->whereDate(
                $dateColumn,
                '<=',
                $request->input('sampai_tanggal')
            );
        }

        $disposisis = $query
            ->latest($dateColumn)
            ->paginate(10)
            ->withQueryString();

        return view('disposisi.index', compact('disposisis'));
    }

    public function create(
        Request $request,
        ?SuratMasuk $suratMasuk = null
    ) {
        $this->authorizeManageAccess();

        if (!$suratMasuk || !$suratMasuk->exists) {
            $suratMasukId = $request->query('surat_masuk_id');

            if (!$suratMasukId) {
                return redirect()
                    ->route('surat-masuk.index')
                    ->with(
                        'warning',
                        'Silakan pilih surat masuk terlebih dahulu.'
                    );
            }

            $suratMasuk = SuratMasuk::findOrFail($suratMasukId);
        }

        $users = $this->getStaffUsers();

        return view(
            'disposisi.create',
            compact('suratMasuk', 'users')
        );
    }

    public function store(DisposisiRequest $request)
    {
        $this->authorizeManageAccess();

        $data = $request->validated();

        /*
         * ==========================================================
         * NORMALISASI INSTRUKSI DISPOSISI
         * ==========================================================
         *
         * Form saat ini menggunakan:
         *
         *     name="instruksi"
         *
         * Sedangkan aplikasi/database juga menggunakan:
         *
         *     isi_disposisi
         *
         * Supaya keduanya tetap kompatibel, kita jadikan
         * isi_disposisi sebagai sumber utama lalu salin nilainya
         * ke instruksi.
         */

        $isiDisposisi = $data['isi_disposisi']
            ?? $data['instruksi']
            ?? $data['catatan']
            ?? '';

        $isiDisposisi = trim((string) $isiDisposisi);

        $data['isi_disposisi'] = $isiDisposisi;

        /*
         * Database production masih memiliki kolom instruksi
         * yang NOT NULL dan tidak memiliki default value.
         *
         * Karena itu instruksi harus tetap dikirim.
         */
        $data['instruksi'] = $isiDisposisi;

        /*
         * Field berikut bukan kolom yang perlu disimpan langsung
         * melalui mass assignment.
         */
        unset(
            $data['penerima'],
            $data['catatan']
        );

        $data['dari_user_id'] = Auth::id();

        $this->validateStaffRecipient(
            (int) $data['kepada_user_id']
        );

        $data['status'] = $data['status'] ?? 'menunggu';

        $disposisi = DB::transaction(function () use ($data) {
            $disposisi = Disposisi::create($data);

            $suratMasuk = $disposisi->suratMasuk;

            if ($suratMasuk) {
                $suratMasuk->update([
                    'status' => 'didisposisikan',
                ]);
            }

            return $disposisi->load([
                'suratMasuk',
                'dari',
                'kepada',
            ]);
        });

        $this->logActivity(
            'disposisi',
            'disposisi',
            sprintf(
                'Mendisposisikan surat %s kepada %s',
                $disposisi->suratMasuk?->nomor_agenda
                    ?? $disposisi->suratMasuk?->nomor_surat
                    ?? '-',
                $disposisi->kepada?->name
                    ?? $disposisi->kepada?->nama
                    ?? '-'
            )
        );

        return redirect()
            ->route('disposisi.index')
            ->with(
                'success',
                'Disposisi berhasil dikirim.'
            );
    }

    public function show(Disposisi $disposisi)
    {
        $this->authorizeViewAccess($disposisi);

        $disposisi->load([
            'suratMasuk',
            'dari',
            'kepada',
        ]);

        return view(
            'disposisi.show',
            compact('disposisi')
        );
    }

    public function edit(Disposisi $disposisi)
    {
        $this->authorizeManageAccess();

        $disposisi->load([
            'suratMasuk',
            'dari',
            'kepada',
        ]);

        $users = $this->getStaffUsers(
            (int) $disposisi->kepada_user_id
        );

        $suratMasuk = $disposisi->suratMasuk;

        return view(
            'disposisi.edit',
            compact(
                'disposisi',
                'users',
                'suratMasuk'
            )
        );
    }

    public function update(
        DisposisiRequest $request,
        Disposisi $disposisi
    ) {
        $this->authorizeManageAccess();

        $data = $request->validated();

        /*
         * ==========================================================
         * NORMALISASI INSTRUKSI UNTUK UPDATE
         * ==========================================================
         */

        if (
            array_key_exists('isi_disposisi', $data) ||
            array_key_exists('instruksi', $data)
        ) {
            $isiDisposisi = $data['isi_disposisi']
                ?? $data['instruksi']
                ?? $disposisi->isi_disposisi
                ?? $disposisi->instruksi
                ?? '';

            $isiDisposisi = trim((string) $isiDisposisi);

            $data['isi_disposisi'] = $isiDisposisi;
            $data['instruksi'] = $isiDisposisi;
        }

        unset(
            $data['penerima'],
            $data['catatan']
        );

        if (!empty($data['kepada_user_id'])) {
            $this->validateStaffRecipient(
                (int) $data['kepada_user_id'],
                (int) $disposisi->kepada_user_id
            );
        }

        $oldStatus = $disposisi->status;

        DB::transaction(function () use (
            $disposisi,
            $data
        ) {
            $disposisi->update($data);

            $this->syncSuratMasukStatus(
                $disposisi->fresh()
            );
        });

        $this->logActivity(
            'update',
            'disposisi',
            "Memperbarui data disposisi #{$disposisi->id}"
        );

        if (
            isset($data['status']) &&
            $data['status'] !== $oldStatus
        ) {
            $this->logActivity(
                'update',
                'disposisi',
                "Mengubah status disposisi #{$disposisi->id} dari {$oldStatus} menjadi {$data['status']}"
            );
        }

        return redirect()
            ->route('disposisi.index')
            ->with(
                'success',
                'Data disposisi berhasil diperbarui.'
            );
    }

    public function updateStatus(
        Request $request,
        Disposisi $disposisi
    ) {
        $this->authorizeViewAccess($disposisi);

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:menunggu,diproses,selesai',
            ],
        ]);

        $oldStatus = $disposisi->status;
        $newStatus = $validated['status'];

        if ($oldStatus === $newStatus) {
            return back()->with(
                'info',
                'Status disposisi tidak berubah.'
            );
        }

        DB::transaction(function () use (
            $disposisi,
            $newStatus
        ) {
            $disposisi->update([
                'status' => $newStatus,
            ]);

            $this->syncSuratMasukStatus(
                $disposisi->fresh()
            );
        });

        $this->logActivity(
            'update',
            'disposisi',
            "Mengubah status disposisi #{$disposisi->id} dari {$oldStatus} menjadi {$newStatus}"
        );

        return back()->with(
            'success',
            'Status disposisi berhasil diperbarui.'
        );
    }

    public function destroy(Disposisi $disposisi)
    {
        $this->authorizeManageAccess();

        $disposisiId = $disposisi->id;

        DB::transaction(function () use ($disposisi) {
            $suratMasuk = $disposisi->suratMasuk;

            $disposisi->delete();

            if (
                $suratMasuk &&
                !$suratMasuk->disposisi()->exists()
            ) {
                $suratMasuk->update([
                    'status' => 'diterima',
                ]);
            }
        });

        $this->logActivity(
            'delete',
            'disposisi',
            "Menghapus data disposisi #{$disposisiId}"
        );

        return redirect()
            ->route('disposisi.index')
            ->with(
                'success',
                'Disposisi berhasil dihapus.'
            );
    }

    private function role(): string
    {
        $user = Auth::user();

        if (!$user) {
            return '';
        }

        $role = strtolower(
            trim((string) $user->role)
        );

        return $role === 'staff'
            ? 'staf'
            : $role;
    }

    private function authorizeManageAccess(): void
    {
        if (!in_array(
            $this->role(),
            ['admin', 'pimpinan'],
            true
        )) {
            abort(
                403,
                'Anda tidak memiliki hak akses untuk mengelola disposisi.'
            );
        }
    }

    private function authorizeViewAccess(
        Disposisi $disposisi
    ): void {
        $role = $this->role();

        if (in_array(
            $role,
            ['admin', 'pimpinan'],
            true
        )) {
            return;
        }

        if ($role === 'staf') {
            if (
                (int) $disposisi->kepada_user_id !==
                (int) Auth::id()
            ) {
                abort(
                    403,
                    'Anda tidak memiliki akses ke data disposisi ini.'
                );
            }

            return;
        }

        abort(
            403,
            'Anda tidak memiliki hak akses ke data disposisi ini.'
        );
    }

    private function getStaffUsers(
        ?int $selectedId = null
    ) {
        $currentUserId = Auth::id();

        $query = User::query()
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query
                    ->whereRaw(
                        'LOWER(role) IN (?, ?)',
                        ['staf', 'staff']
                    )
                    ->orWhereRaw(
                        'LOWER(jabatan) IN (?, ?)',
                        ['staf', 'staff']
                    );
            });

        if ($currentUserId) {
            $query->where(
                'id',
                '!=',
                $currentUserId
            );
        }

        if ($selectedId) {
            $query->orWhere(function (
                Builder $query
            ) use (
                $selectedId,
                $currentUserId
            ) {
                $query
                    ->whereKey($selectedId)
                    ->where('is_active', true)
                    ->where(function (
                        Builder $query
                    ) {
                        $query
                            ->whereRaw(
                                'LOWER(role) IN (?, ?)',
                                ['staf', 'staff']
                            )
                            ->orWhereRaw(
                                'LOWER(jabatan) IN (?, ?)',
                                ['staf', 'staff']
                            );
                    });

                if ($currentUserId) {
                    $query->where(
                        'id',
                        '!=',
                        $currentUserId
                    );
                }
            });
        }

        return $query
            ->orderBy('name')
            ->get();
    }

    private function validateStaffRecipient(
        int $userId,
        ?int $currentUserId = null
    ): void {
        $recipient = User::query()
            ->whereKey($userId)
            ->where('is_active', true)
            ->where(function (Builder $query) {
                $query
                    ->whereRaw(
                        'LOWER(role) IN (?, ?)',
                        ['staf', 'staff']
                    )
                    ->orWhereRaw(
                        'LOWER(jabatan) IN (?, ?)',
                        ['staf', 'staff']
                    );
            })
            ->first();

        if (!$recipient) {
            abort(
                422,
                'Penerima disposisi harus merupakan user Staff yang aktif.'
            );
        }

        if (
            $userId === (int) Auth::id() &&
            $userId !== $currentUserId
        ) {
            abort(
                422,
                'Anda tidak dapat mengirim disposisi kepada diri sendiri.'
            );
        }
    }

    private function getDateColumn(): string
    {
        return Schema::hasColumn(
            'disposisis',
            'tanggal_disposisi'
        )
            ? 'tanggal_disposisi'
            : 'created_at';
    }

    private function syncSuratMasukStatus(
        Disposisi $disposisi
    ): void {
        $suratMasuk = $disposisi->suratMasuk;

        if (!$suratMasuk) {
            return;
        }

        $hasDisposisi = $suratMasuk
            ->disposisi()
            ->exists();

        if (!$hasDisposisi) {
            $suratMasuk->update([
                'status' => 'diterima',
            ]);

            return;
        }

        $hasUnfinished = $suratMasuk
            ->disposisi()
            ->where('status', '!=', 'selesai')
            ->exists();

        if ($hasUnfinished) {
            $suratMasuk->update([
                'status' => 'didisposisikan',
            ]);

            return;
        }

        $suratMasuk->update([
            'status' => 'selesai',
        ]);
    }

    private function logActivity(
        string $action,
        string $module,
        string $description
    ): void {
        if (!class_exists(ActivityLog::class)) {
            return;
        }

        try {
            ActivityLog::catat(
                $action,
                $module,
                $description
            );
        } catch (\Throwable) {
            // Activity log tidak boleh menggagalkan proses utama.
        }
    }
}