<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna.
     *
     * Mendukung:
     * - Pencarian nama
     * - Pencarian email
     * - Pencarian jabatan
     * - Filter beberapa role
     * - Filter status akun
     * - Alias staf -> staff
     * - Pagination
     * - Query string
     *
     * Scorecard:
     * - Total Pengguna
     * - Akun Aktif
     * - Akun Nonaktif
     * - Jumlah Role
     */
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input('search', '')
        );


        /*
        |--------------------------------------------------------------------------
        | ROLE FILTER
        |--------------------------------------------------------------------------
        */

        $rawRoles = $request->input('role', []);

        if (
            is_scalar($rawRoles) &&
            trim((string) $rawRoles) !== ''
        ) {
            $rawRoles = [$rawRoles];
        }

        if (!is_array($rawRoles)) {
            $rawRoles = [];
        }

        $roles = collect($rawRoles)
            ->flatten()
            ->filter(
                fn ($role) => is_scalar($role)
            )
            ->map(
                fn ($role) => strtolower(
                    trim((string) $role)
                )
            )
            ->map(
                fn ($role) => $role === 'staf'
                    ? 'staff'
                    : $role
            )
            ->filter(
                fn ($role) => in_array(
                    $role,
                    [
                        'admin',
                        'pimpinan',
                        'staff',
                    ],
                    true
                )
            )
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | STATUS FILTER
        |--------------------------------------------------------------------------
        |
        | 1 = Aktif
        | 0 = Nonaktif
        |
        */

        $rawStatuses = $request->input(
            'is_active',
            []
        );

        if (
            is_scalar($rawStatuses) &&
            trim((string) $rawStatuses) !== ''
        ) {
            $rawStatuses = [$rawStatuses];
        }

        if (!is_array($rawStatuses)) {
            $rawStatuses = [];
        }

        $statuses = collect($rawStatuses)
            ->flatten()
            ->filter(
                fn ($status) => is_scalar($status)
            )
            ->map(
                fn ($status) => (string) $status
            )
            ->filter(
                fn ($status) => in_array(
                    $status,
                    [
                        '0',
                        '1',
                    ],
                    true
                )
            )
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | SCORECARD
        |--------------------------------------------------------------------------
        |
        | Perhitungan dilakukan sebelum query filter tabel.
        |
        | Artinya:
        | - Search tidak memengaruhi scorecard
        | - Filter role tidak memengaruhi scorecard
        | - Filter status tidak memengaruhi scorecard
        | - Pagination tidak memengaruhi scorecard
        |
        */

        $userStatistics = User::query()
            ->selectRaw(
                'COUNT(*) AS total_users'
            )
            ->selectRaw(
                'SUM(
                    CASE
                        WHEN is_active = 1 THEN 1
                        ELSE 0
                    END
                ) AS active_users'
            )
            ->selectRaw(
                'SUM(
                    CASE
                        WHEN is_active = 0 THEN 1
                        ELSE 0
                    END
                ) AS inactive_users'
            )
            ->first();


        /*
        |--------------------------------------------------------------------------
        | NILAI SCORECARD
        |--------------------------------------------------------------------------
        */

        $allUsersCount = (int) (
            $userStatistics->total_users ?? 0
        );

        $activeUsersCount = (int) (
            $userStatistics->active_users ?? 0
        );

        $inactiveUsersCount = (int) (
            $userStatistics->inactive_users ?? 0
        );


        /*
        |--------------------------------------------------------------------------
        | JUMLAH ROLE
        |--------------------------------------------------------------------------
        |
        | Sistem menggunakan 3 role:
        | - Admin
        | - Pimpinan
        | - Staff
        |
        */

        $roleCount = 3;


        /*
        |--------------------------------------------------------------------------
        | QUERY DATA USER
        |--------------------------------------------------------------------------
        */

        $users = User::query()

            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $keyword =
                        '%' .
                        $search .
                        '%';

                    $query->where(
                        function ($q) use ($keyword) {

                            $q->where(
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
            )

            ->when(
                $roles->isNotEmpty(),
                function ($query) use ($roles) {

                    /*
                    |--------------------------------------------------------------------------
                    | STAFF / STAF
                    |--------------------------------------------------------------------------
                    |
                    | Staff pada filter dianggap cocok dengan:
                    | - staff
                    | - staf
                    |
                    */

                    $databaseRoles = $roles
                        ->flatMap(
                            function ($role) {

                                return $role === 'staff'
                                    ? [
                                        'staff',
                                        'staf',
                                    ]
                                    : [
                                        $role,
                                    ];
                            }
                        )
                        ->unique()
                        ->values()
                        ->all();

                    $query->whereIn(
                        'role',
                        $databaseRoles
                    );
                }
            )

            ->when(
                $statuses->isNotEmpty(),
                function ($query) use ($statuses) {

                    $query->whereIn(
                        'is_active',
                        $statuses->all()
                    );
                }
            )

            ->orderBy(
                'created_at',
                'desc'
            )
            ->orderBy(
                'id',
                'desc'
            )
            ->paginate(10)
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'users.index',
            [
                /*
                |--------------------------------------------------------------
                | TABLE
                |--------------------------------------------------------------
                */

                'users' => $users,

                /*
                |--------------------------------------------------------------
                | FILTER
                |--------------------------------------------------------------
                */

                'selectedRoles' => $roles->all(),

                'selectedStatuses' => $statuses->all(),

                /*
                |--------------------------------------------------------------
                | SCORECARD
                |--------------------------------------------------------------
                */

                'allUsersCount' => $allUsersCount,

                'activeUsersCount' => $activeUsersCount,

                'inactiveUsersCount' => $inactiveUsersCount,

                'roleCount' => $roleCount,
            ]
        );
    }


    /**
     * Menampilkan form tambah pengguna.
     */
    public function create()
    {
        return view('users.create');
    }


    /**
     * Menyimpan pengguna baru.
     */
    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    'unique:users,email',
                ],

                'password' => [
                    'required',
                    'string',
                    'min:6',
                    'confirmed',
                ],

                'role' => [
                    'required',
                    'string',
                    'in:admin,pimpinan,staff,staf',
                ],

                'jabatan' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'is_active' => [
                    'required',
                    'boolean',
                ],
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | NORMALISASI ROLE
        |--------------------------------------------------------------------------
        */

        $validated['role'] =
            $this->normalizeRoleForDatabase(
                $validated['role']
            );


        /*
        |--------------------------------------------------------------------------
        | NORMALISASI STATUS
        |--------------------------------------------------------------------------
        */

        $validated['is_active'] =
            filter_var(
                $validated['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );

        if ($validated['is_active'] === null) {
            $validated['is_active'] = false;
        }


        /*
        |--------------------------------------------------------------------------
        | PASSWORD
        |--------------------------------------------------------------------------
        */

        $validated['password'] =
            Hash::make(
                $validated['password']
            );


        /*
        |--------------------------------------------------------------------------
        | CREATE USER
        |--------------------------------------------------------------------------
        */

        $user = User::create(
            [
                'name' => $validated['name'],

                'email' => $validated['email'],

                'password' => $validated['password'],

                'role' => $validated['role'],

                'jabatan' =>
                    $validated['jabatan'] ?? null,

                'is_active' =>
                    $validated['is_active'],
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        $this->logActivity(
            'create',
            'user',
            'Menambah pengguna ' . $user->name
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Pengguna berhasil ditambahkan!'
            );
    }


    /**
     * Menampilkan detail pengguna.
     *
     * Digunakan oleh:
     * GET /users/{user}
     */
    public function show(User $user)
    {
        return view(
            'users.show',
            compact('user')
        );
    }


    /**
     * Menampilkan form edit pengguna.
     */
    public function edit(User $user)
    {
        return view(
            'users.edit',
            compact('user')
        );
    }


    /**
     * Memperbarui pengguna.
     */
    public function update(
        Request $request,
        User $user
    ) {
        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate(
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique(
                        'users',
                        'email'
                    )->ignore(
                        $user->id
                    ),
                ],

                'password' => [
                    'nullable',
                    'string',
                    'min:6',
                    'confirmed',
                ],

                'role' => [
                    'required',
                    'string',
                    'in:admin,pimpinan,staff,staf',
                ],

                'jabatan' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'is_active' => [
                    'required',
                    'boolean',
                ],
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | NORMALISASI STATUS
        |--------------------------------------------------------------------------
        */

        $requestedIsActive =
            filter_var(
                $validated['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );


        if ($requestedIsActive === null) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Status akun tidak valid.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | CEGAH MENONAKTIFKAN DIRI SENDIRI
        |--------------------------------------------------------------------------
        */

        if (
            (int) Auth::id() ===
            (int) $user->id &&
            $requestedIsActive === false
        ) {

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Anda tidak dapat menonaktifkan akun Anda sendiri.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | NORMALISASI ROLE
        |--------------------------------------------------------------------------
        */

        $validated['role'] =
            $this->normalizeRoleForDatabase(
                $validated['role']
            );


        /*
        |--------------------------------------------------------------------------
        | DATA UPDATE
        |--------------------------------------------------------------------------
        */

        $updateData = [

            'name' =>
                $validated['name'],

            'email' =>
                $validated['email'],

            'role' =>
                $validated['role'],

            'jabatan' =>
                $validated['jabatan'] ?? null,

            'is_active' =>
                $requestedIsActive,
        ];


        /*
        |--------------------------------------------------------------------------
        | PASSWORD OPTIONAL
        |--------------------------------------------------------------------------
        */

        if (
            isset($validated['password']) &&
            trim(
                (string) $validated['password']
            ) !== ''
        ) {

            $updateData['password'] =
                Hash::make(
                    $validated['password']
                );
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE USER
        |--------------------------------------------------------------------------
        */

        $user->update(
            $updateData
        );


        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        $this->logActivity(
            'update',
            'user',
            'Mengubah data pengguna ' . $user->name
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Data pengguna berhasil diperbarui!'
            );
    }


    /**
     * Menghapus pengguna.
     */
    public function destroy(User $user)
    {
        /*
        |--------------------------------------------------------------------------
        | CEGAH MENGHAPUS DIRI SENDIRI
        |--------------------------------------------------------------------------
        */

        if (
            (int) Auth::id() ===
            (int) $user->id
        ) {

            return back()
                ->with(
                    'error',
                    'Anda tidak dapat menghapus akun Anda sendiri.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | SIMPAN NAMA
        |--------------------------------------------------------------------------
        */

        $nama = $user->name;


        /*
        |--------------------------------------------------------------------------
        | DELETE
        |--------------------------------------------------------------------------
        */

        $user->delete();


        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        $this->logActivity(
            'delete',
            'user',
            'Menghapus pengguna ' . $nama
        );


        /*
        |--------------------------------------------------------------------------
        | REDIRECT
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Pengguna berhasil dihapus!'
            );
    }


    /**
     * Normalisasi role sebelum disimpan.
     *
     * Database:
     * - admin
     * - pimpinan
     * - staff
     */
    private function normalizeRoleForDatabase(
        string $role
    ): string {

        $role = strtolower(
            trim($role)
        );


        if ($role === 'staf') {
            return 'staff';
        }


        return $role;
    }


    /**
     * Mencatat activity log.
     *
     * Error activity log tidak boleh
     * menghentikan proses utama.
     */
    private function logActivity(
        string $action,
        string $module,
        string $description
    ): void {

        if (
            !class_exists(
                ActivityLog::class
            )
        ) {
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
            |--------------------------------------------------------------------------
            | Abaikan error activity log.
            |--------------------------------------------------------------------------
            */

        }
    }
}