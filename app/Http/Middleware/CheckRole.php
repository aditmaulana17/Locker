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
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | AUTENTIKASI
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return redirect()->route('login');
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS AKUN
        |--------------------------------------------------------------------------
        |
        | Mendukung:
        | - status = nonaktif
        | - is_active = false
        |
        */

        $status =
            strtolower(
                trim(
                    (string) (
                        $user->status
                        ?? ''
                    )
                )
            );

        $isInactive =
            $status === 'nonaktif'
            || (
                isset($user->is_active)
                && !$user->is_active
            );

        if ($isInactive) {

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with(
                    'error',
                    'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI ROLE
        |--------------------------------------------------------------------------
        |
        | Standar internal:
        | - admin
        | - pimpinan
        | - staff
        |
        | "staf" dianggap alias "staff".
        |
        */

        $normalizeRole = static function ($role): string {
            $role = strtolower(
                trim(
                    (string) $role
                )
            );

            return match ($role) {
                'staf' => 'staff',
                'staff' => 'staff',
                'pimpinan' => 'pimpinan',
                'admin' => 'admin',
                default => $role,
            };
        };

        $userRole = $normalizeRole(
            $user->role
            ?? $user->jabatan
            ?? ''
        );

        /*
        |--------------------------------------------------------------------------
        | ADMIN BYPASS
        |--------------------------------------------------------------------------
        |
        | Admin memiliki akses penuh.
        |
        */

        if ($userRole === 'admin') {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | ROLE YANG DIIZINKAN
        |--------------------------------------------------------------------------
        */

        $allowedRoles = array_map(
            $normalizeRole,
            $roles
        );

        /*
        |--------------------------------------------------------------------------
        | AUTHORIZATION
        |--------------------------------------------------------------------------
        */

        if (
            !in_array(
                $userRole,
                $allowedRoles,
                true
            )
        ) {
            abort(
                403,
                'Anda tidak memiliki hak akses ke halaman ini.'
            );
        }

        return $next($request);
    }
}