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
    /**
     * Menampilkan daftar disposisi.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = $this->role();

        $query = Disposisi::query()
            ->with([
                'suratMasuk',
                'dari',
                'kepada',
            ]);

        /*
         * Staf hanya melihat disposisi
         * yang ditujukan kepada dirinya sendiri.
         */
        if ($role === 'staf') {
            $query->where(
                'kepada_user_id',
                (int) $user->id
            );
        }

        /*
         * =====================================================
         * FILTER STATUS
         * =====================================================
         */
        $allowedStatuses = [
            'menunggu',
            'diproses',
            'selesai',
        ];

        $statuses = $request->input(
            'status',
            []
        );

        if (!is_array($statuses)) {
            $statuses = [$statuses];
        }

        $statuses = collect($statuses)
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
                    $allowedStatuses,
                    true
                )
            )
            ->unique()
            ->values()
            ->all();

        if (!empty($statuses)) {
            $query->whereIn(
                'status',
                $statuses
            );
        }

        /*
         * =====================================================
         * SEARCH
         * =====================================================
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
         * =====================================================
         * FILTER TANGGAL DISPOSISI
         * =====================================================
         *
         * Prioritas:
         * tanggal_disposisi
         *
         * Jika kolom tidak tersedia:
         * created_at
         */
        $dateColumn =
            $this->getDateColumn();

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

        /*
         * Jika tanggal awal lebih besar
         * dari tanggal akhir, tukarkan.
         */
        if (
            $validDariTanggal &&
            $validSampaiTanggal &&
            $dariTanggal > $sampaiTanggal
        ) {
            [
                $dariTanggal,
                $sampaiTanggal
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
         * =====================================================
         * HASIL DATA
         * =====================================================
         */
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

    /**
     * Form tambah disposisi.
     */
    public function create(
        Request $request,
        ?SuratMasuk $suratMasuk = null
    ) {
        $this->authorizeManageAccess();

        /*
         * Jika model SuratMasuk belum tersedia,
         * ambil dari query string.
         */
        if (
            !$suratMasuk ||
            !$suratMasuk->exists
        ) {
            $suratMasukId =
                $request->query(
                    'surat_masuk_id'
                );

            if (!$suratMasukId) {
                return redirect()
                    ->route(
                        'surat-masuk.index'
                    )
                    ->with(
                        'warning',
                        'Silakan pilih surat masuk terlebih dahulu.'
                    );
            }

            $suratMasuk =
                SuratMasuk::findOrFail(
                    $suratMasukId
                );
        }

        /*
         * Ambil daftar staf aktif.
         */
        $users =
            $this->getStaffUsers();

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

        $data =
            $request->validated();

        /*
         * =====================================================
         * INSTRUKSI
         * =====================================================
         */
        $isiDisposisi =
            $data['isi_disposisi']
            ?? $data['instruksi']
            ?? $data['catatan']
            ?? '';

        $isiDisposisi =
            trim(
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

        /*
         * Simpan ke kedua field.
         */
        $data['isi_disposisi'] =
            $isiDisposisi;

        $data['instruksi'] =
            $isiDisposisi;

        /*
         * Field form yang bukan kolom
         * yang perlu disimpan.
         */
        unset(
            $data['penerima'],
            $data['catatan']
        );

        /*
         * User pembuat disposisi.
         */
        $data['dari_user_id'] =
            (int) Auth::id();

        /*
         * =====================================================
         * VALIDASI PENERIMA
         * =====================================================
         */
        $kepadaUserId =
            (int) (
                $data['kepada_user_id']
                ?? 0
            );

        $this->validateStaffRecipient(
            $kepadaUserId
        );

        /*
         * =====================================================
         * STATUS
         * =====================================================
         */
        $status =
            strtolower(
                trim(
                    (string) (
                        $data['status']
                        ?? 'menunggu'
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

        $data['status'] =
            $status;

        /*
         * =====================================================
         * TRANSACTION
         * =====================================================
         */
        $disposisi = DB::transaction(
            function () use ($data) {

                $disposisi =
                    Disposisi::create(
                        $data
                    );

                /*
                 * Sinkronisasi status
                 * surat masuk.
                 */
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

        /*
         * =====================================================
         * ACTIVITY LOG
         * =====================================================
         */
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
            ->route(
                'disposisi.index'
            )
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

        $users =
            $this->getStaffUsers(
                (int) $disposisi->kepada_user_id
            );

        $suratMasuk =
            $disposisi->suratMasuk;

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

        $data =
            $request->validated();

        /*
         * =====================================================
         * INSTRUKSI
         * =====================================================
         */
        $hasInstruction =
            array_key_exists(
                'isi_disposisi',
                $data
            )
            ||
            array_key_exists(
                'instruksi',
                $data
            )
            ||
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

            $isiDisposisi =
                trim(
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

            $data['isi_disposisi'] =
                $isiDisposisi;

            $data['instruksi'] =
                $isiDisposisi;
        }

        /*
         * Jangan simpan field tampilan.
         */
        unset(
            $data['penerima'],
            $data['catatan']
        );

        /*
         * =====================================================
         * VALIDASI PENERIMA
         * =====================================================
         */
        if (
            isset(
                $data['kepada_user_id']
            )
        ) {
            $this->validateStaffRecipient(
                (int) $data['kepada_user_id'],
                (int) $disposisi->kepada_user_id
            );
        }

        /*
         * =====================================================
         * VALIDASI STATUS
         * =====================================================
         */
        if (
            isset($data['status'])
        ) {
            $data['status'] =
                strtolower(
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

        $oldStatus =
            strtolower(
                trim(
                    (string) $disposisi->status
                )
            );

        /*
         * =====================================================
         * UPDATE TRANSACTION
         * =====================================================
         */
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

        /*
         * Activity log.
         */
        $this->logActivity(
            'update',
            'disposisi',
            "Memperbarui data disposisi #{$disposisi->id}"
        );

        /*
         * Log perubahan status.
         */
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
            ->route(
                'disposisi.index'
            )
            ->with(
                'success',
                'Data disposisi berhasil diperbarui.'
            );
    }

    /**
     * Update status oleh staf.
     *
     * Admin/pimpinan juga dapat menggunakan endpoint ini,
     * sedangkan staf hanya dapat mengubah disposisinya sendiri.
     */
    public function updateStatus(
        Request $request,
        Disposisi $disposisi
    ) {
        $this->authorizeViewAccess(
            $disposisi
        );

        $validated =
            $request->validate([
                'status' => [
                    'required',
                    'string',
                    'in:menunggu,diproses,selesai',
                ],
            ]);

        $oldStatus =
            strtolower(
                trim(
                    (string) $disposisi->status
                )
            );

        $newStatus =
            strtolower(
                trim(
                    (string) $validated['status']
                )
            );

        if (
            $oldStatus ===
            $newStatus
        ) {
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
                    'status' =>
                        $newStatus,
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

        $disposisiId =
            $disposisi->id;

        $suratMasuk =
            $disposisi->suratMasuk;

        DB::transaction(
            function () use (
                $disposisi,
                $suratMasuk
            ) {

                $disposisi->delete();

                /*
                 * Setelah disposisi dihapus,
                 * sinkronkan ulang status surat masuk.
                 */
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
            ->route(
                'disposisi.index'
            )
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
        $user =
            Auth::user();

        if (!$user) {
            return '';
        }

        $role =
            strtolower(
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
        $role =
            $this->role();

        /*
         * Admin dan pimpinan dapat melihat semua.
         */
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

        /*
         * Staf hanya dapat melihat
         * disposisi yang ditujukan kepadanya.
         */
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
        $currentUserId =
            Auth::id();

        $query =
            User::query()
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    function (
                        Builder $query
                    ) {
                        $query
                            ->where(
                                function (
                                    Builder $query
                                ) {
                                    $query
                                        ->whereNotNull(
                                            'role'
                                        )
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
                                function (
                                    Builder $query
                                ) {
                                    $query
                                        ->whereNotNull(
                                            'jabatan'
                                        )
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

        /*
         * Jangan masukkan user yang sedang login
         * sebagai penerima baru.
         */
        if ($currentUserId) {
            $query->where(
                'id',
                '!=',
                $currentUserId
            );
        }

        /*
         * Pada mode edit, pastikan penerima lama
         * tetap ada pada pilihan selama masih valid.
         */
        if (
            $selectedId &&
            $selectedId !== (int) $currentUserId
        ) {
            $selectedUser =
                User::query()
                    ->whereKey(
                        $selectedId
                    )
                    ->where(
                        'is_active',
                        true
                    )
                    ->where(
                        function (
                            Builder $query
                        ) {
                            $query
                                ->where(
                                    function (
                                        Builder $query
                                    ) {
                                        $query
                                            ->whereNotNull(
                                                'role'
                                            )
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
                                    function (
                                        Builder $query
                                    ) {
                                        $query
                                            ->whereNotNull(
                                                'jabatan'
                                            )
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

            /*
             * Tambahkan selected user secara manual
             * jika belum masuk hasil utama.
             */
            if ($selectedUser) {

                $exists =
                    $query
                        ->clone()
                        ->whereKey(
                            $selectedId
                        )
                        ->exists();

                if (!$exists) {
                    $users =
                        $query
                            ->orderBy(
                                'name'
                            )
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
            ->orderBy(
                'name'
            )
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

        $recipient =
            User::query()
                ->whereKey($userId)
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    function (
                        Builder $query
                    ) {
                        $query
                            ->where(
                                function (
                                    Builder $query
                                ) {
                                    $query
                                        ->whereNotNull(
                                            'role'
                                        )
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
                                function (
                                    Builder $query
                                ) {
                                    $query
                                        ->whereNotNull(
                                            'jabatan'
                                        )
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

        /*
         * Saat create, user tidak boleh mengirim
         * disposisi kepada dirinya sendiri.
         */
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
     * Sinkronisasi status surat masuk
     * berdasarkan status seluruh disposisi.
     *
     * Aturan:
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
        $suratMasuk =
            $disposisi->suratMasuk;

        /*
         * Fallback jika relasi belum termuat.
         */
        if (
            !$suratMasuk &&
            $disposisi->surat_masuk_id
        ) {
            $suratMasuk =
                SuratMasuk::find(
                    $disposisi->surat_masuk_id
                );
        }

        if (!$suratMasuk) {
            return;
        }

        /*
         * Cek apakah surat memiliki disposisi.
         *
         * Relasi menggunakan SoftDeletes,
         * sehingga disposisi yang sudah dihapus
         * tidak ikut dihitung.
         */
        $hasDisposisi =
            $suratMasuk
                ->disposisi()
                ->exists();

        if (!$hasDisposisi) {
            $suratMasuk->update([
                'status' => 'baru',
            ]);

            return;
        }

        /*
         * Cek apakah masih ada disposisi
         * yang belum selesai.
         */
        $hasUnfinished =
            $suratMasuk
                ->disposisi()
                ->whereRaw(
                    'LOWER(TRIM(status)) != ?',
                    ['selesai']
                )
                ->exists();

        if ($hasUnfinished) {
            $suratMasuk->update([
                'status' =>
                    'didisposisikan',
            ]);

            return;
        }

        /*
         * Semua disposisi selesai.
         */
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

        $value =
            trim(
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
            $day
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
        if (!class_exists(
            ActivityLog::class
        )) {
            return;
        }

        try {
            ActivityLog::catat(
                $action,
                $module,
                $description
            );
        } catch (\Throwable) {
            /*
             * Kegagalan activity log tidak boleh
             * menggagalkan proses utama.
             */
        }
    }
}