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
     * - Filter beberapa role sekaligus
     * - Alias staf -> staff
     * - Pagination
     * - Query string
     */
    public function index(
        Request $request
    ) {
        /*
         * =====================================================
         * SEARCH
         * =====================================================
         */

        $search =
            trim(
                (string) $request->input(
                    'search',
                    ''
                )
            );

        /*
         * =====================================================
         * ROLE FILTER
         * =====================================================
         *
         * Form sekarang menggunakan:
         *
         * role[]=admin
         * role[]=pimpinan
         * role[]=staff
         *
         * Tetapi controller juga tetap menerima:
         *
         * role=admin
         *
         * untuk menjaga kompatibilitas.
         */

        $rawRoles =
            $request->input(
                'role',
                []
            );

        if (
            is_scalar($rawRoles) &&
            trim(
                (string) $rawRoles
            ) !== ''
        ) {
            $rawRoles = [
                $rawRoles,
            ];
        }

        if (
            !is_array($rawRoles)
        ) {
            $rawRoles = [];
        }

        /*
         * Flatten + normalize + validasi role.
         */

        $roles =
            collect(
                $rawRoles
            )
                ->flatten()
                ->filter(
                    fn ($role) =>
                        is_scalar($role)
                )
                ->map(
                    fn ($role) =>
                        strtolower(
                            trim(
                                (string) $role
                            )
                        )
                )
                ->map(
                    fn ($role) =>
                        $role === 'staf'
                            ? 'staff'
                            : $role
                )
                ->filter(
                    fn ($role) =>
                        in_array(
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
         * =====================================================
         * QUERY
         * =====================================================
         */

        $users =
            User::query()

                /*
                 * SEARCH
                 */

                ->when(
                    $search !== '',
                    function (
                        $query
                    ) use (
                        $search
                    ) {

                        $keyword =
                            '%' .
                            $search .
                            '%';

                        $query->where(
                            function (
                                $q
                            ) use (
                                $keyword
                            ) {

                                $q
                                    ->where(
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

                /*
                 * ROLE
                 *
                 * Jika kosong:
                 * semua role ditampilkan.
                 *
                 * Jika satu:
                 * whereIn tetap aman.
                 *
                 * Jika beberapa:
                 * ditampilkan semuanya.
                 */

                ->when(
                    $roles->isNotEmpty(),
                    function (
                        $query
                    ) use (
                        $roles
                    ) {

                        $query->whereIn(
                            'role',
                            $roles->all()
                        );
                    }
                )

                /*
                 * URUTAN
                 */

                ->orderBy(
                    'created_at',
                    'desc'
                )
                ->orderBy(
                    'id',
                    'desc'
                )

                /*
                 * PAGINATION
                 */

                ->paginate(
                    10
                )

                /*
                 * PERTAHANKAN FILTER
                 */

                ->withQueryString();

        return view(
            'users.index',
            [
                'users' =>
                    $users,

                /*
                 * Dikirim ke Blade agar
                 * checkbox tetap tercentang
                 * setelah submit/filter.
                 */

                'selectedRoles' =>
                    $roles->all(),
            ]
        );
    }

    /**
     * Menampilkan form tambah pengguna.
     */
    public function create()
    {
        return view(
            'users.create'
        );
    }

    /**
     * Menyimpan pengguna baru.
     */
    public function store(
        Request $request
    ) {
        $validated =
            $request->validate(
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

                    /*
                     * Database:
                     * admin
                     * pimpinan
                     * staff
                     *
                     * Form boleh mengirim staf.
                     */

                    'role' => [
                        'required',
                        'string',
                        'in:admin,pimpinan,staf,staff',
                    ],

                    'jabatan' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    /*
                     * Status:
                     * aktif / nonaktif
                     */

                    'status' => [
                        'nullable',
                        'string',
                        'in:aktif,nonaktif',
                    ],
                ]
            );

        /*
         * =====================================================
         * NORMALISASI ROLE
         * =====================================================
         */

        $validated['role'] =
            $this->normalizeRoleForDatabase(
                $validated['role']
            );

        /*
         * =====================================================
         * PASSWORD
         * =====================================================
         */

        $validated['password'] =
            Hash::make(
                $validated['password']
            );

        /*
         * =====================================================
         * DEFAULT STATUS
         * =====================================================
         */

        $validated['status'] =
            $validated['status']
            ??
            'aktif';

        /*
         * =====================================================
         * IS ACTIVE
         * =====================================================
         *
         * Hanya disinkronkan jika field
         * is_active ada pada request.
         */

        if (
            $request->has(
                'is_active'
            )
        ) {
            $validated['is_active'] =
                $validated['status'] ===
                'aktif';
        }

        /*
         * =====================================================
         * CREATE
         * =====================================================
         */

        $user =
            User::create(
                $validated
            );

        /*
         * =====================================================
         * ACTIVITY LOG
         * =====================================================
         */

        $this->logActivity(
            'create',
            'user',
            'Menambah pengguna ' .
            $user->name
        );

        return redirect()
            ->route(
                'users.index'
            )
            ->with(
                'success',
                'Pengguna berhasil ditambahkan!'
            );
    }

    /**
     * Menampilkan detail pengguna.
     */
    public function show(
        User $user
    ) {
        return view(
            'users.show',
            compact(
                'user'
            )
        );
    }

    /**
     * Menampilkan form edit pengguna.
     */
    public function edit(
        User $user
    ) {
        return view(
            'users.edit',
            compact(
                'user'
            )
        );
    }

    /**
     * Memperbarui pengguna.
     */
    public function update(
        Request $request,
        User $user
    ) {
        $validated =
            $request->validate(
                [
                    'name' => [
                        'required',
                        'string',
                        'max:255',
                    ],

                    /*
                     * Email user sendiri tidak
                     * dianggap duplikat.
                     */

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

                    /*
                     * Password boleh kosong.
                     */

                    'password' => [
                        'nullable',
                        'string',
                        'min:6',
                        'confirmed',
                    ],

                    /*
                     * Role database.
                     */

                    'role' => [
                        'required',
                        'string',
                        'in:admin,pimpinan,staf,staff',
                    ],

                    'jabatan' => [
                        'nullable',
                        'string',
                        'max:255',
                    ],

                    'status' => [
                        'required',
                        'string',
                        'in:aktif,nonaktif',
                    ],
                ]
            );

        /*
         * =====================================================
         * ROLE
         * =====================================================
         */

        $validated['role'] =
            $this->normalizeRoleForDatabase(
                $validated['role']
            );

        /*
         * =====================================================
         * PASSWORD
         * =====================================================
         */

        if (
            isset(
                $validated['password']
            ) &&
            trim(
                (string) $validated['password']
            ) !== ''
        ) {

            $validated['password'] =
                Hash::make(
                    $validated['password']
                );

        } else {

            unset(
                $validated['password']
            );
        }

        /*
         * =====================================================
         * IS ACTIVE
         * =====================================================
         */

        if (
            $request->has(
                'is_active'
            )
        ) {
            $validated['is_active'] =
                $validated['status'] ===
                'aktif';
        }

        /*
         * =====================================================
         * UPDATE
         * =====================================================
         */

        $user->update(
            $validated
        );

        /*
         * =====================================================
         * ACTIVITY LOG
         * =====================================================
         */

        $this->logActivity(
            'update',
            'user',
            'Mengubah data pengguna ' .
            $user->name
        );

        return redirect()
            ->route(
                'users.index'
            )
            ->with(
                'success',
                'Data pengguna berhasil diperbarui!'
            );
    }

    /**
     * Menghapus pengguna.
     */
    public function destroy(
        User $user
    ) {
        /*
         * Tidak boleh menghapus akun sendiri.
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

        $nama =
            $user->name;

        $user->delete();

        /*
         * Activity log.
         */

        $this->logActivity(
            'delete',
            'user',
            'Menghapus pengguna ' .
            $nama
        );

        return redirect()
            ->route(
                'users.index'
            )
            ->with(
                'success',
                'Pengguna berhasil dihapus!'
            );
    }

    /**
     * Normalisasi role sebelum disimpan.
     *
     * Database menggunakan:
     * admin
     * pimpinan
     * staff
     */
    private function normalizeRoleForDatabase(
        string $role
    ): string {

        $role =
            strtolower(
                trim(
                    $role
                )
            );

        if (
            $role ===
            'staf'
        ) {
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

        } catch (
            \Throwable
        ) {

            /*
             * Abaikan error activity log.
             */
        }
    }
}