<?php

use App\Http\Middleware\CheckActiveAccount;
use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        /*
        |--------------------------------------------------------------------------
        | ALIAS MIDDLEWARE
        |--------------------------------------------------------------------------
        |
        | role
        | → Mengatur hak akses berdasarkan role:
        |   Admin / Pimpinan / Staff
        |
        | check.active
        | → Memastikan akun yang sedang login masih aktif.
        |
        */

        $middleware->alias([
            'role' => CheckRole::class,
            'check.active' => CheckActiveAccount::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | TRUST PROXIES
        |--------------------------------------------------------------------------
        |
        | Mempercayai proxy agar HTTPS dapat terdeteksi
        | dengan benar ketika aplikasi berada di belakang
        | reverse proxy / Cloudflare / Railway.
        |
        */

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
