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
    private const STATUS_OPTIONS = [
        'menunggu',
        'diproses',
        'selesai',
    ];

    /**
     * Menampilkan daftar disposisi.
     *
     * Data dibagi menjadi dua kebutuhan:
     * - Statistik: seluruh disposisi yang dapat dilihat user.
     * - Board: seluruh hasil filter, tanpa batas pagination.
     * - Pagination: 10 data per halaman untuk kebutuhan navigasi daftar.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $this->role();

        /*
        |----------------------------------------------------------------------
        | QUERY DASAR + HAK AKSES
        |----------------------------------------------------------------------
        */
        $baseQuery = Disposisi::query()
            ->with([
                'suratMasuk',
                'dari',
                'kepada',
            ]);

        if ($role === 'staf') {
            $baseQuery->where(
                'kepada_user_id',
                (int) $user->id
            );
        }

        /*
        |----------------------------------------------------------------------
        | STATISTIK
        |----------------------------------------------------------------------
        | Tidak dipengaruhi search, filter status, filter tanggal, atau
        | pagination. Untuk staf, hanya disposisi miliknya yang dihitung.
        */
        $statistics = (clone $baseQuery)
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw("SUM(
                CASE
                    WHEN LOWER(TRIM(COALESCE(status, 'menunggu'))) = 'menunggu'
                    THEN 1
                    ELSE 0
                END
            ) AS menunggu")
            ->selectRaw("SUM(
                CASE
                    WHEN LOWER(TRIM(COALESCE(status, 'menunggu'))) = 'diproses'
                    THEN 1
                    ELSE 0
                END
            ) AS diproses")
            ->selectRaw("SUM(
                CASE
                    WHEN LOWER(TRIM(COALESCE(status, 'menunggu'))) = 'selesai'
                    THEN 1
                    ELSE 0
                END
            ) AS selesai")
            ->first();

        $totalDisposisi = (int) (
            $statistics->total ?? 0
        );

        $disposisiMenunggu = (int) (
            $statistics->menunggu ?? 0
        );

        $disposisiDiproses = (int) (
            $statistics->diproses ?? 0
        );

        $disposisiSelesai = (int) (
            $statistics->selesai ?? 0
        );

        /*
        |----------------------------------------------------------------------
        | FILTER STATUS
        |----------------------------------------------------------------------
        */
        $statuses = $request->input(
            'status',
            []
        );

        if (
            is_scalar($statuses) &&
            trim((string) $statuses) !== ''
        ) {
            $statuses = [$statuses];
        }

        if (!is_array($statuses)) {
            $statuses = [];
        }

        $statuses = collect($statuses)
            ->flatten()
            ->filter(
                fn ($status) => is_scalar($status)
            )
            ->map(
                fn ($status) => strtolower(
                    trim((string) $status)
                )
            )
            ->filter(
                fn ($status) => in_array(
                    $status,
                    self::STATUS_OPTIONS,
                    true
                )
            )
            ->unique()
            ->values()
            ->all();

        /*
        |----------------------------------------------------------------------
        | QUERY HASIL FILTER
        |----------------------------------------------------------------------
        */
        $query = clone $baseQuery;

        if (!empty($statuses)) {
            $query->whereIn(
                'status',
                $statuses
            );
        }

        /*
        |----------------------------------------------------------------------
        | SEARCH
        |----------------------------------------------------------------------
        */
        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        if ($search !== '') {
            $keyword = "%{$search}%";

            $query->where(
                function (Builder $query) use ($keyword) {
                    $query
                        ->where(
                            'instruksi',
                            'like',
                            $keyword
                        )
                        ->orWhere(
                            'isi_disposisi',
                            'like',
                            $keyword
                        )
                        ->orWhere(
                            'catatan',
                            'like',
                            $keyword
                        )
                        ->orWhereHas(
                            'suratMasuk',
                            function (
                                Builder $suratQuery
                            ) use ($keyword) {
                                $suratQuery
                                    ->where(
                                        'nomor_surat',
                                        'like',
                                        $keyword
                                    )
                                    ->orWhere(
                                        'nomor_agenda',
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
                                    );
                            }
                        )
                        ->orWhereHas(
                            'kepada',
                            function (
                                Builder $userQuery
                            ) use ($keyword) {
                                $userQuery
                                    ->where(
                                        'name',
                                        'like',
                                        $keyword
                                    )
                                    ->orWhere(
                                        'nama',
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
        |----------------------------------------------------------------------
        | FILTER TANGGAL
        |----------------------------------------------------------------------
        */
        $dateColumn = $this->getDateColumn();

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
            $validDariTanggal &&
            $validSampaiTanggal &&
            $dariTanggal > $sampaiTanggal
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
                $dateColumn,
                '>=',
                $dariTanggal
            );
        }

        if ($validSampaiTanggal) {
            $query->whereDate(
                $dateColumn,
                '<=',
                $sampaiTanggal
            );
        }

        /*
        |----------------------------------------------------------------------
        | URUTAN DATA
        |----------------------------------------------------------------------
        */
        $sortedQuery = $query
            ->orderByDesc($dateColumn)
            ->orderByDesc('id');

        /*
        |----------------------------------------------------------------------
        | PAGINATION
        |----------------------------------------------------------------------
        | Tetap 10 data per halaman untuk kebutuhan pagination.
        */
        $disposisis = (clone $sortedQuery)
            ->paginate(10)
            ->withQueryString();

        /*
        |----------------------------------------------------------------------
        | BOARD
        |----------------------------------------------------------------------
        | Board mengambil seluruh hasil filter tanpa limit pagination.
        */
        $boardDisposisis = (clone $sortedQuery)
            ->get()
            ->groupBy(function ($disposisi) {
                $status = strtolower(
                    trim(
                        (string) (
                            $disposisi->status ??
                            'menunggu'
                        )
                    )
                );

                return in_array(
                    $status,
                    self::STATUS_OPTIONS,
                    true
                )
                    ? $status
                    : 'menunggu';
            });

        return view(
            'disposisi.index',
            compact(
                'disposisis',
                'boardDisposisis',
                'statuses',
                'dariTanggal',
                'sampaiTanggal',
                'totalDisposisi',
                'disposisiMenunggu',
                'disposisiDiproses',
                'disposisiSelesai'
            )
        );
    }

    /**
     * Form tambah disposisi.
     */
    public function create(
        Request $request,
        ?SuratMasuk $suratMasuk = null
    ) {
        $this->authorizeManageAccess();

        if (
            !$suratMasuk ||
            !$suratMasuk->exists
        ) {
            $suratMasukId = $request->query(
                'surat_masuk_id'
            );

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

    /**
     * Simpan disposisi baru.
     */
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

        $data['dari_user_id'] = (int) Auth::id();

        $kepadaUserId = (int) (
            $data['kepada_user_id'] ?? 0
        );

        $this->validateStaffRecipient(
            $kepadaUserId
        );

        $status = strtolower(
            trim(
                (string) (
                    $data['status'] ??
                    'menunggu'
                )
            )
        );

        if (!in_array(
            $status,
            self::STATUS_OPTIONS,
            true
        )) {
            $status = 'menunggu';
        }

        $data['status'] = $status;

        $disposisi = DB::transaction(
            function () use ($data) {
                $disposisi = Disposisi::create($data);

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

        $nomorSurat =
            $disposisi->suratMasuk?->nomor_agenda
            ?? $disposisi->suratMasuk?->nomor_surat
            ?? '-';

        $namaPenerima =
            $disposisi->kepada?->name
            ?? $disposisi->kepada?->nama
            ?? '-';

        $this->logActivity(
            'create',
            'disposisi',
            sprintf(
                'Membuat disposisi surat %s kepada %s',
                $nomorSurat,
                $namaPenerima
            )
        );

        return redirect()
            ->route('disposisi.index')
            ->with(
                'success',
                'Disposisi berhasil dikirim.'
            );
    }

    /**
     * Detail disposisi.
     */
    public function show(
        Disposisi $disposisi
    ) {
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

    /**
     * Form edit disposisi.
     */
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

    /**
     * Update disposisi.
     */
    public function update(
        DisposisiRequest $request,
        Disposisi $disposisi
    ) {
        $this->authorizeManageAccess();

        $data = $request->validated();

        $hasInstruction =
            array_key_exists('isi_disposisi', $data)
            ||
            array_key_exists('instruksi', $data)
            ||
            array_key_exists('catatan', $data);

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

        if (isset($data['kepada_user_id'])) {
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
                self::STATUS_OPTIONS,
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
                $disposisi->update($data);

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

    /**
     * Update status oleh staf.
     *
     * Admin/pimpinan dapat menggunakan endpoint ini.
     * Staf hanya dapat mengubah disposisi yang ditujukan kepadanya.
     */
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

    /**
     * Hapus disposisi.
     */
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

    /**
     * Normalisasi role user.
     */
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

    /**
     * Hak akses pengelolaan disposisi.
     */
    private function authorizeManageAccess(): void
    {
        if (!Auth::check()) {
            abort(
                401,
                'Anda harus login terlebih dahulu.'
            );
        }

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

    /**
     * Hak akses melihat disposisi.
     */
    private function authorizeViewAccess(
        Disposisi $disposisi
    ): void {
        if (!Auth::check()) {
            abort(
                401,
                'Anda harus login terlebih dahulu.'
            );
        }

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

    /**
     * Mengambil user staf aktif.
     */
    private function getStaffUsers(
        ?int $selectedId = null
    ) {
        $currentUserId = Auth::id();

        $query = User::query()
            ->where(
                'is_active',
                true
            )
            ->where(
                function (Builder $query) {
                    $query
                        ->where(
                            function (Builder $query) {
                                $query
                                    ->whereNotNull('role')
                                    ->whereRaw(
                                        'LOWER(TRIM(role)) IN (?, ?)',
                                        [
                                            'staf',
                                            'staff',
                                        ]
                                    );
                            }
                        )
                        ->orWhere(
                            function (Builder $query) {
                                $query
                                    ->whereNotNull('jabatan')
                                    ->whereRaw(
                                        'LOWER(TRIM(jabatan)) IN (?, ?)',
                                        [
                                            'staf',
                                            'staff',
                                        ]
                                    );
                            }
                        );
                }
            );

        if ($currentUserId) {
            $query->where(
                'id',
                '!=',
                $currentUserId
            );
        }

        if (
            $selectedId &&
            $selectedId !== (int) $currentUserId
        ) {
            $selectedUser = User::query()
                ->whereKey($selectedId)
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    function (Builder $query) {
                        $query
                            ->where(
                                function (Builder $query) {
                                    $query
                                        ->whereNotNull('role')
                                        ->whereRaw(
                                            'LOWER(TRIM(role)) IN (?, ?)',
                                            [
                                                'staf',
                                                'staff',
                                            ]
                                        );
                                }
                            )
                            ->orWhere(
                                function (Builder $query) {
                                    $query
                                        ->whereNotNull('jabatan')
                                        ->whereRaw(
                                            'LOWER(TRIM(jabatan)) IN (?, ?)',
                                            [
                                                'staf',
                                                'staff',
                                            ]
                                        );
                                }
                            );
                    }
                )
                ->first();

            if ($selectedUser) {
                $exists = $query
                    ->clone()
                    ->whereKey($selectedId)
                    ->exists();

                if (!$exists) {
                    $users = $query
                        ->orderBy('name')
                        ->get();

                    return $users
                        ->push($selectedUser)
                        ->unique('id')
                        ->sortBy('name')
                        ->values();
                }
            }
        }

        return $query
            ->orderBy('name')
            ->get();
    }

    /**
     * Memastikan penerima merupakan staf aktif.
     */
    private function validateStaffRecipient(
        int $userId,
        ?int $currentUserId = null
    ): void {
        if ($userId <= 0) {
            abort(
                422,
                'Penerima disposisi wajib dipilih.'
            );
        }

        $recipient = User::query()
            ->whereKey($userId)
            ->where(
                'is_active',
                true
            )
            ->where(
                function (Builder $query) {
                    $query
                        ->where(
                            function (Builder $query) {
                                $query
                                    ->whereNotNull('role')
                                    ->whereRaw(
                                        'LOWER(TRIM(role)) IN (?, ?)',
                                        [
                                            'staf',
                                            'staff',
                                        ]
                                    );
                            }
                        )
                        ->orWhere(
                            function (Builder $query) {
                                $query
                                    ->whereNotNull('jabatan')
                                    ->whereRaw(
                                        'LOWER(TRIM(jabatan)) IN (?, ?)',
                                        [
                                            'staf',
                                            'staff',
                                        ]
                                    );
                            }
                        );
                }
            )
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

    /**
     * Menentukan kolom tanggal disposisi.
     */
    private function getDateColumn(): string
    {
        return Schema::hasColumn(
            'disposisis',
            'tanggal_disposisi'
        )
            ? 'tanggal_disposisi'
            : 'created_at';
    }

    /**
     * Sinkronisasi status surat masuk berdasarkan seluruh disposisinya.
     *
     * Tidak ada disposisi
     *      -> baru
     *
     * Ada disposisi dan masih ada yang belum selesai
     *      -> didisposisikan
     *
     * Semua disposisi selesai
     *      -> selesai
     */
    private function syncSuratMasukStatus(
        Disposisi $disposisi
    ): void {
        $suratMasuk = $disposisi->suratMasuk;

        if (
            !$suratMasuk &&
            $disposisi->surat_masuk_id
        ) {
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
                'LOWER(TRIM(COALESCE(status, ?))) != ?',
                [
                    'menunggu',
                    'selesai',
                ]
            )
            ->exists();

        if ($hasUnfinished) {
            $suratMasuk->update([
                'status' => 'didisposisikan',
            ]);

            return;
        }

        $hasWaiting = $suratMasuk
            ->disposisi()
            ->whereRaw(
                "LOWER(TRIM(COALESCE(status, 'menunggu'))) = ?",
                ['menunggu']
            )
            ->exists();

        if ($hasWaiting) {
            $suratMasuk->update([
                'status' => 'didisposisikan',
            ]);

            return;
        }

        $suratMasuk->update([
            'status' => 'selesai',
        ]);
    }

    /**
     * Validasi format tanggal YYYY-MM-DD.
     */
    private function isValidDate(
        mixed $value
    ): bool {
        if ($value === null) {
            return false;
        }

        $value = trim(
            (string) $value
        );

        if (!preg_match(
            '/^\d{4}-\d{2}-\d{2}$/',
            $value
        )) {
            return false;
        }

        [
            $year,
            $month,
            $day,
        ] = array_map(
            'intval',
            explode(
                '-',
                $value
            )
        );

        return checkdate(
            $month,
            $day,
            $year
        );
    }

    /**
     * Mencatat activity log.
     */
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
            /* Activity log tidak boleh menggagalkan proses utama. */
        }
    }
}
