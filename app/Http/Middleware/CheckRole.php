<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // 1. Cek autentikasi pengguna
        if (! $user) {
            return redirect()->route('login');
        }

        // 2. Cek status keaktifan akun (Mendukung kolom 'status' atau 'is_active')
        $isInactive = (isset($user->status) && strtolower($user->status) === 'nonaktif') || 
                      (isset($user->is_active) && ! $user->is_active);

        if ($isInactive) {
            Auth::logout();
            
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator.');
        }

        // 3. Normalisasi Role (Menangani variasi penulisan 'staf' & 'staff')
        $normalizeRole = fn ($r) => strtolower($r) === 'staff' ? 'staf' : strtolower($r);

        $userRole = $normalizeRole($user->role ?? '');

        // 4. Admin selalu memiliki akses penuh ke seluruh rute (Bypass)
        if ($userRole === 'admin') {
            return $next($request);
        }

        // 5. Cek kesesuaian role dengan parameter yang diizinkan
        $allowedRoles = array_map($normalizeRole, $roles);

        if (! in_array($userRole, $allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}