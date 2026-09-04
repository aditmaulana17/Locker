<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Menampilkan daftar pengguna (dengan Pencarian, Filter Role, & Query String).
     */
    public function index(Request $request)
    {
        $users = User::when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        })
        ->when($request->role, function ($query, $role) {
            $query->where('role', $role);
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Menampilkan form tambah pengguna.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Menyimpan data pengguna baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role'     => ['required', 'string', 'in:admin,pimpinan,staf,staff'],
            'jabatan'  => ['nullable', 'string', 'max:255'],
            'status'   => ['nullable', 'string', 'in:aktif,nonaktif'],
        ]);

        // Standardisasi nilai role 'staff' menjadi 'staf'
        if ($validated['role'] === 'staff') {
            $validated['role'] = 'staf';
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['status']   = $request->status ?? 'aktif';

        $user = User::create($validated);
        
        // Catat log aktivitas jika model ActivityLog tersedia
        if (class_exists(ActivityLog::class)) {
            ActivityLog::catat('create', 'user', "Menambah pengguna {$user->name}");
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail pengguna.
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * Menampilkan form edit pengguna.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Memperbarui data pengguna.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'], 
            'role'     => ['required', 'string', 'in:admin,pimpinan,staf,staff'],
            'jabatan'  => ['nullable', 'string', 'max:255'],
            'status'   => ['required', 'string', 'in:aktif,nonaktif'],
        ]);

        // Standardisasi nilai role 'staff' menjadi 'staf'
        if ($validated['role'] === 'staff') {
            $validated['role'] = 'staf';
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if (class_exists(ActivityLog::class)) {
            ActivityLog::catat('update', 'user', "Mengubah data pengguna {$user->name}");
        }

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui!');
    }

    /**
     * Menghapus pengguna.
     */
    public function destroy(User $user)
    {
        // Mencegah hapus akun sendiri
        if (Auth::id() === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $nama = $user->name;
        $user->delete();

        if (class_exists(ActivityLog::class)) {
            ActivityLog::catat('delete', 'user', "Menghapus pengguna {$nama}");
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus!');
    }
}