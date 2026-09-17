<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveAccount
{
    /**
     * Memeriksa status akun pengguna yang sedang login.
     *
     * Jika akun aktif:
     * - Request dilanjutkan.
     *
     * Jika akun nonaktif:
     * - Pengguna dikeluarkan dari sistem.
     * - Session dihapus.
     * - CSRF token dibuat ulang.
     * - Pengguna diarahkan ke halaman login.
     * - Notifikasi akun diblokir dikirim.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | CEK LOGIN
        |--------------------------------------------------------------------------
        */

        if (!Auth::check()) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | AMBIL USER
        |--------------------------------------------------------------------------
        |
        | Auth::user() dapat berupa Authenticatable|null.
        | Kita pastikan terlebih dahulu bahwa user merupakan
        | instance dari App\Models\User.
        |
        */

        $user = Auth::user();

        if (!$user instanceof User) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | CEK STATUS AKUN
        |--------------------------------------------------------------------------
        */

        if (!$user->isActive()) {
            /*
            |--------------------------------------------------------------------------
            | LOGOUT
            |--------------------------------------------------------------------------
            */

            Auth::logout();

            /*
            |--------------------------------------------------------------------------
            | INVALIDATE SESSION
            |--------------------------------------------------------------------------
            */

            $request->session()->invalidate();

            /*
            |--------------------------------------------------------------------------
            | REGENERATE CSRF TOKEN
            |--------------------------------------------------------------------------
            */

            $request->session()->regenerateToken();

            /*
            |--------------------------------------------------------------------------
            | REDIRECT KE LOGIN
            |--------------------------------------------------------------------------
            */

            return redirect()
                ->route('login')
                ->with(
                    'account_blocked',
                    'Akun Anda telah dinonaktifkan oleh administrator. Anda telah dikeluarkan dari sistem.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | AKUN MASIH AKTIF
        |--------------------------------------------------------------------------
        */

        return $next($request);
    }
}
