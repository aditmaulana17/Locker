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
     * - Filter role
     * - Pagination
     * - Query string
     */
    public function index(Request $request)
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $role = strtolower(
            trim(
                (string) $request->input('role', '')
            )
        );

        /*
         * Aplikasi menerima "staf" sebagai alias,
         * tetapi database menggunakan "staff".
         */
        if ($role === 'staf') {
            $role = 'staff';
        }

        $users = User::query()
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $keyword = "%{$search}%";

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
                in_array(
                    $role,
                    [
                        'admin',
                        'pimpinan',
                        'staff',
                    ],
                    true
                ),
                function ($query) use ($role) {
                    $query->where(
                        'role',
                        $role
                    );
                }
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view(
            'users.index',
            compact('users')
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
    public function store(Request $request)
    {
        $validated = $request->validate([
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
             * "staf" diterima sebagai alias
             * lalu diubah menjadi "staff".
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
             * Status aplikasi:
             * aktif / nonaktif
             */
            'status' => [
                'nullable',
                'string',
                'in:aktif,nonaktif',
            ],
        ]);

        /*
         * Normalisasi role sebelum disimpan.
         *
         * staf -> staff
         */
        $validated['role'] =
            $this->normalizeRoleForDatabase(
                $validated['role']
            );

        /*
         * Hash password.
         */
        $validated['password'] =
            Hash::make(
                $validated['password']
            );

        /*
         * Default status.
         */
        $validated['status'] =
            $validated['status']
            ?? 'aktif';

        /*
         * Jika aplikasi Anda juga menggunakan
         * is_active pada tabel users, sinkronkan
         * nilainya berdasarkan status.
         *
         * Bagian ini hanya dijalankan jika
         * field is_active memang dikirim dari form.
         */
        if (
            $request->has('is_active')
        ) {
            $validated['is_active'] =
                $validated['status'] === 'aktif';
        }

        /*
         * Buat user baru.
         */
        $user = User::create(
            $validated
        );

        /*
         * Activity log.
         */
        $this->logActivity(
            'create',
            'user',
            "Menambah pengguna {$user->name}"
        );

        return redirect()
            ->route('users.index')
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
            compact('user')
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
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * Email user yang sedang diedit
             * tidak dianggap sebagai duplikat.
             */
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(
                    'users',
                    'email'
                )->ignore($user->id),
            ],

            /*
             * Password boleh kosong saat edit.
             * Jika kosong, password lama dipertahankan.
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
        ]);

        /*
         * =====================================================
         * ROLE
         * =====================================================
         *
         * Form boleh mengirim:
         * staf
         * staff
         *
         * Database selalu menerima:
         * staff
         */
        $validated['role'] =
            $this->normalizeRoleForDatabase(
                $validated['role']
            );

        /*
         * =====================================================
         * PASSWORD
         * =====================================================
         *
         * Password hanya diubah jika diisi.
         */
        if (
            isset($validated['password']) &&
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
         * STATUS
         * =====================================================
         *
         * Jika form Anda juga memiliki
         * is_active, sinkronkan nilainya.
         */
        if (
            $request->has('is_active')
        ) {
            $validated['is_active'] =
                $validated['status'] === 'aktif';
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
         * Activity log.
         */
        $this->logActivity(
            'update',
            'user',
            "Mengubah data pengguna {$user->name}"
        );

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
    public function destroy(
        User $user
    ) {
        /*
         * User tidak boleh menghapus
         * akun sendiri.
         */
        if (
            (int) Auth::id() ===
            (int) $user->id
        ) {
            return back()->with(
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
            "Menghapus pengguna {$nama}"
        );

        return redirect()
            ->route('users.index')
            ->with(
                'success',
                'Pengguna berhasil dihapus!'
            );
    }

    /**
     * Normalisasi role sebelum disimpan ke database.
     *
     * Database menggunakan:
     * admin
     * pimpinan
     * staff
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
             * Abaikan error activity log.
             */
        }
    }
}