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

        $query = Disposisi::query()->with([
            'suratMasuk',
            'dari',
            'kepada',
        ]);

        if ($role === 'staf') {
            $query->where(
                'kepada_user_id',
                $user->id
            );
        }

        $search = trim(
            (string) $request->input('search', '')
        );

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $keyword = "%{$search}%";

                $query
                    ->where('instruksi', 'like', $keyword)
                    ->orWhere('isi_disposisi', 'like', $keyword)
                    ->orWhere('catatan', 'like', $keyword)
                    ->orWhereHas('suratMasuk', function (Builder $suratQuery) use ($keyword) {
                        $suratQuery
                            ->where('nomor_surat', 'like', $keyword)
                            ->orWhere('nomor_agenda', 'like', $keyword)
                            ->orWhere('pengirim', 'like', $keyword)
                            ->orWhere('perihal', 'like', $keyword);
                    })
                    ->orWhereHas('kepada', function (Builder $userQuery) use ($keyword) {
                        $userQuery
                            ->where('name', 'like', $keyword)
                            ->orWhere('nama', 'like', $keyword)
                            ->orWhere('jabatan', 'like', $keyword);
                    });
            });
        }

        $allowedStatuses = [
            'menunggu',
            'diproses',
            'selesai',
        ];

        $statuses = $request->input('status', []);

        if (!is_array($statuses)) {
            $statuses = [$statuses];
        }

        $statuses = collect($statuses)
            ->filter(fn ($status) => is_scalar($status))
            ->map(fn ($status) => strtolower(trim((string) $status)))
            ->filter(fn ($status) => in_array($status, $allowedStatuses, true))
            ->unique()
            ->values()
            ->all();

        if (!empty($statuses)) {
            $query->whereIn(
                'status',
                $statuses
            );
        }

        $dateColumn = $this->getDateColumn();

        $dariTanggal = $request->input('dari_tanggal');
        $sampaiTanggal = $request->input('sampai_tanggal');

        if ($this->isValidDate($dariTanggal)) {
            $query->whereDate(
                $dateColumn,
                '>=',
                $dariTanggal
            );
        }

        if ($this->isValidDate($sampaiTanggal)) {
            $query->whereDate(
                $dateColumn,
                '<=',
                $sampaiTanggal
            );
        }

        if (
            $this->isValidDate($dariTanggal) &&
            $this->isValidDate($sampaiTanggal) &&
            $dariTanggal > $sampaiTanggal
        ) {
            [$dariTanggal, $sampaiTanggal] = [
                $sampaiTanggal,
                $dariTanggal,
            ];

            $query = Disposisi::query()->with([
                'suratMasuk',
                'dari',
                'kepada',
            ]);

            if ($role === 'staf') {
                $query->where(
                    'kepada_user_id',
                    $user->id
                );
            }

            if ($search !== '') {
                $query->where(function (Builder $query) use ($search) {
                    $keyword = "%{$search}%";

                    $query
                        ->where('instruksi', 'like', $keyword)
                        ->orWhere('isi_disposisi', 'like', $keyword)
                        ->orWhere('catatan', 'like', $keyword)
                        ->orWhereHas('suratMasuk', function (Builder $suratQuery) use ($keyword) {
                            $suratQuery
                                ->where('nomor_surat', 'like', $keyword)
                                ->orWhere('nomor_agenda', 'like', $keyword)
                                ->orWhere('pengirim', 'like', $keyword)
                                ->orWhere('perihal', 'like', $keyword);
                        })
                        ->orWhereHas('kepada', function (Builder $userQuery) use ($keyword) {
                            $userQuery
                                ->where('name', 'like', $keyword)
                                ->orWhere('nama', 'like', $keyword)
                                ->orWhere('jabatan', 'like', $keyword);
                        });
                });
            }

            if (!empty($statuses)) {
                $query->whereIn(
                    'status',
                    $statuses
                );
            }

            $query
                ->whereDate(
                    $dateColumn,
                    '>=',
                    $dariTanggal
                )
                ->whereDate(
                    $dateColumn,
                    '<=',
                    $sampaiTanggal
                );
        }

        $disposisis = $query
            ->orderByDesc($dateColumn)
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view(
            'disposisi.index',
            compact('disposisis')
        );
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

            $suratMasuk = SuratMasuk::findOrFail(
                $suratMasukId
            );
        }

        $users = $this->getStaffUsers();

        return view(
            'disposisi.create',
            compact(
                'suratMasuk',
                'users'
            )
        );
    }

    public function store(
        DisposisiRequest $request
    ) {
        $this->authorizeManageAccess();

        $data = $request->validated();

        $isiDisposisi =
            $data['isi_disposisi']
            ?? $data['instruksi']
            ?? $data['catatan']
            ?? '';

        $isiDisposisi = trim(
            (string) $isiDisposisi
        );

        if ($isiDisposisi === '') {
            return back()
                ->withInput()
                ->withErrors([
                    'instruksi' =>
                        'Instruksi disposisi wajib diisi.',
                ]);
        }

        $data['isi_disposisi'] = $isiDisposisi;
        $data['instruksi'] = $isiDisposisi;

        unset(
            $data['penerima'],
            $data['catatan']
        );

        $data['dari_user_id'] = Auth::id();

        $kepadaUserId = (int) (
            $data['kepada_user_id'] ?? 0
        );

        $this->validateStaffRecipient(
            $kepadaUserId
        );

        $status = strtolower(
            trim(
                (string) (
                    $data['status'] ?? 'menunggu'
                )
            )
        );

        if (!in_array(
            $status,
            [
                'menunggu',
                'diproses',
                'selesai',
            ],
            true
        )) {
            $status = 'menunggu';
        }

        $data['status'] = $status;

        $disposisi = DB::transaction(
            function () use ($data) {
                $disposisi = Disposisi::create(
                    $data
                );

                $this->syncSuratMasukStatus(
                    $disposisi->fresh([
                        'suratMasuk',
                    ])
                );

                return $disposisi->load([
                    'suratMasuk',
                    'dari',
                    'kepada',
                ]);
            }
        );

        $this->logActivity(
            'create',
            'disposisi',
            sprintf(
                'Membuat disposisi surat %s kepada %s',
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

    public function show(
        Disposisi $disposisi
    ) {
        $this->authorizeViewAccess(
            $disposisi
        );

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

    public function edit(
        Disposisi $disposisi
    ) {
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

        $hasInstruction =
            array_key_exists(
                'isi_disposisi',
                $data
            ) ||
            array_key_exists(
                'instruksi',
                $data
            ) ||
            array_key_exists(
                'catatan',
                $data
            );

        if ($hasInstruction) {
            $isiDisposisi =
                $data['isi_disposisi']
                ?? $data['instruksi']
                ?? $data['catatan']
                ?? $disposisi->isi_disposisi
                ?? $disposisi->instruksi
                ?? '';

            $isiDisposisi = trim(
                (string) $isiDisposisi
            );

            if ($isiDisposisi === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'instruksi' =>
                            'Instruksi disposisi wajib diisi.',
                    ]);
            }

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

        if (isset($data['status'])) {
            $data['status'] = strtolower(
                trim(
                    (string) $data['status']
                )
            );

            if (!in_array(
                $data['status'],
                [
                    'menunggu',
                    'diproses',
                    'selesai',
                ],
                true
            )) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'status' =>
                            'Status disposisi tidak valid.',
                    ]);
            }
        }

        $oldStatus = strtolower(
            trim(
                (string) $disposisi->status
            )
        );

        DB::transaction(
            function () use (
                $disposisi,
                $data
            ) {
                $disposisi->update(
                    $data
                );

                $this->syncSuratMasukStatus(
                    $disposisi->fresh([
                        'suratMasuk',
                    ])
                );
            }
        );

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
        $this->authorizeViewAccess(
            $disposisi
        );

        $validated = $request->validate([
            'status' => [
                'required',
                'string',
                'in:menunggu,diproses,selesai',
            ],
        ]);

        $oldStatus = strtolower(
            trim(
                (string) $disposisi->status
            )
        );

        $newStatus = strtolower(
            trim(
                (string) $validated['status']
            )
        );

        if ($oldStatus === $newStatus) {
            return back()->with(
                'info',
                'Status disposisi tidak berubah.'
            );
        }

        DB::transaction(
            function () use (
                $disposisi,
                $newStatus
            ) {
                $disposisi->update([
                    'status' => $newStatus,
                ]);

                $this->syncSuratMasukStatus(
                    $disposisi->fresh([
                        'suratMasuk',
                    ])
                );
            }
        );

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

    public function destroy(
        Disposisi $disposisi
    ) {
        $this->authorizeManageAccess();

        $disposisiId = $disposisi->id;

        $suratMasuk = $disposisi->suratMasuk;

        DB::transaction(
            function () use (
                $disposisi,
                $suratMasuk
            ) {
                $disposisi->delete();

                if ($suratMasuk) {
                    $this->syncSuratMasukStatus(
                        new Disposisi([
                            'surat_masuk_id' =>
                                $suratMasuk->id,
                        ])
                    );
                }
            }
        );

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
            trim(
                (string) (
                    $user->role
                    ?? $user->jabatan
                    ?? ''
                )
            )
        );

        return $role === 'staff'
            ? 'staf'
            : $role;
    }

    private function authorizeManageAccess(): void
    {
        if (!in_array(
            $this->role(),
            [
                'admin',
                'pimpinan',
            ],
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
            [
                'admin',
                'pimpinan',
            ],
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
                    ->where(function (Builder $query) {
                        $query
                            ->whereNotNull('role')
                            ->whereRaw(
                                'LOWER(TRIM(role)) IN (?, ?)',
                                [
                                    'staf',
                                    'staff',
                                ]
                            );
                    })
                    ->orWhere(function (Builder $query) {
                        $query
                            ->whereNotNull('jabatan')
                            ->whereRaw(
                                'LOWER(TRIM(jabatan)) IN (?, ?)',
                                [
                                    'staf',
                                    'staff',
                                ]
                            );
                    });
            });

        if ($currentUserId) {
            $query->where(
                'id',
                '!=',
                $currentUserId
            );
        }

        if ($selectedId) {
            $selectedExists = $query
                ->clone()
                ->whereKey($selectedId)
                ->exists();

            if (!$selectedExists) {
                $selectedUser = User::query()
                    ->whereKey($selectedId)
                    ->where('is_active', true)
                    ->where(function (Builder $query) {
                        $query
                            ->where(function (Builder $query) {
                                $query
                                    ->whereNotNull('role')
                                    ->whereRaw(
                                        'LOWER(TRIM(role)) IN (?, ?)',
                                        [
                                            'staf',
                                            'staff',
                                        ]
                                    );
                            })
                            ->orWhere(function (Builder $query) {
                                $query
                                    ->whereNotNull('jabatan')
                                    ->whereRaw(
                                        'LOWER(TRIM(jabatan)) IN (?, ?)',
                                        [
                                            'staf',
                                            'staff',
                                        ]
                                    );
                            });
                    })
                    ->first();

                if ($selectedUser) {
                    $query = User::query()
                        ->where(function (Builder $query) use ($selectedId) {
                            $query
                                ->whereKey($selectedId)
                                ->orWhere(function (Builder $query) {
                                    $query
                                        ->where('is_active', true)
                                        ->where(function (Builder $query) {
                                            $query
                                                ->where(function (Builder $query) {
                                                    $query
                                                        ->whereNotNull('role')
                                                        ->whereRaw(
                                                            'LOWER(TRIM(role)) IN (?, ?)',
                                                            [
                                                                'staf',
                                                                'staff',
                                                            ]
                                                        );
                                                })
                                                ->orWhere(function (Builder $query) {
                                                    $query
                                                        ->whereNotNull('jabatan')
                                                        ->whereRaw(
                                                            'LOWER(TRIM(jabatan)) IN (?, ?)',
                                                            [
                                                                'staf',
                                                                'staff',
                                                            ]
                                                        );
                                                });
                                        });
                                });
                        })
                        ->where('is_active', true);

                    if ($currentUserId) {
                        $query->where(
                            'id',
                            '!=',
                            $currentUserId
                        );
                    }
                }
            }
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
                    ->where(function (Builder $query) {
                        $query
                            ->whereNotNull('role')
                            ->whereRaw(
                                'LOWER(TRIM(role)) IN (?, ?)',
                                [
                                    'staf',
                                    'staff',
                                ]
                            );
                    })
                    ->orWhere(function (Builder $query) {
                        $query
                            ->whereNotNull('jabatan')
                            ->whereRaw(
                                'LOWER(TRIM(jabatan)) IN (?, ?)',
                                [
                                    'staf',
                                    'staff',
                                ]
                            );
                    });
            })
            ->first();

        if (!$recipient) {
            abort(
                422,
                'Penerima disposisi harus merupakan user staf yang aktif.'
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

        if (!$suratMasuk && $disposisi->surat_masuk_id) {
            $suratMasuk = SuratMasuk::find(
                $disposisi->surat_masuk_id
            );
        }

        if (!$suratMasuk) {
            return;
        }

        $hasDisposisi = $suratMasuk
            ->disposisi()
            ->exists();

        if (!$hasDisposisi) {
            $suratMasuk->update([
                'status' => 'baru',
            ]);

            return;
        }

        $hasUnfinished = $suratMasuk
            ->disposisi()
            ->whereRaw(
                'LOWER(TRIM(status)) != ?',
                ['selesai']
            )
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

    private function isValidDate(
        mixed $value
    ): bool {
        if (!$value) {
            return false;
        }

        $value = trim((string) $value);

        if (!preg_match(
            '/^\d{4}-\d{2}-\d{2}$/',
            $value
        )) {
            return false;
        }

        [$year, $month, $day] = array_map(
            'intval',
            explode('-', $value)
        );

        return checkdate(
            $month,
            $day,
            $year
        );
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