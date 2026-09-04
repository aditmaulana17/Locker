<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisposisiController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\KategoriSuratController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])->name('login.attempt');
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register'])->name('register.attempt');
});

Route::post('logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::get('clear-cache', function () {
            Artisan::call('optimize:clear');
            return response()->json([
                'status' => 'success',
                'message' => 'Cache aplikasi berhasil dibersihkan.',
            ]);
        })->name('clear-cache');

        Route::get('link-storage', function () {
            try {
                Artisan::call('storage:link');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Storage link berhasil dibuat.',
                ]);
            } catch (\Throwable $e) {
                report($e);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal membuat storage link: ' . $e->getMessage(),
                ], 500);
            }
        })->name('link-storage');
    });

    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('surat-masuk/create', [SuratMasukController::class, 'create'])
            ->name('surat-masuk.create');

        Route::post('surat-masuk', [SuratMasukController::class, 'store'])
            ->name('surat-masuk.store');

        Route::get('surat-keluar/create', [SuratKeluarController::class, 'create'])
            ->name('surat-keluar.create');

        Route::post('surat-keluar', [SuratKeluarController::class, 'store'])
            ->name('surat-keluar.store');

        Route::post('surat-keluar/{suratKeluar}/log', [SuratKeluarController::class, 'storeLog'])
            ->name('surat-keluar.log.store');
    });

    Route::middleware('role:admin,pimpinan,staf')->group(function () {
        Route::get('surat-masuk', [SuratMasukController::class, 'index'])
            ->name('surat-masuk.index');

        Route::get('surat-masuk/{suratMasuk}/preview-lampiran', [SuratMasukController::class, 'previewLampiran'])
            ->name('surat-masuk.preview-lampiran');

        Route::get('surat-masuk/{suratMasuk}/label', [SuratMasukController::class, 'cetakLabel'])
            ->name('surat-masuk.label');

        Route::get('surat-masuk/{suratMasuk}/cetak-disposisi', [SuratMasukController::class, 'cetakDisposisi'])
            ->name('surat-masuk.cetak-disposisi');

        Route::get('surat-masuk/{suratMasuk}', [SuratMasukController::class, 'show'])
            ->name('surat-masuk.show');

        Route::get('surat-keluar', [SuratKeluarController::class, 'index'])
            ->name('surat-keluar.index');

        Route::get('surat-keluar/{suratKeluar}/preview-lampiran', [SuratKeluarController::class, 'previewLampiran'])
            ->name('surat-keluar.preview-lampiran');

        Route::get('surat-keluar/{suratKeluar}/cetak', [SuratKeluarController::class, 'cetak'])
            ->name('surat-keluar.cetak');

        Route::get('surat-keluar/{suratKeluar}/label', [SuratKeluarController::class, 'cetakLabel'])
            ->name('surat-keluar.label');

        Route::get('surat-keluar/{suratKeluar}', [SuratKeluarController::class, 'show'])
            ->name('surat-keluar.show');
    });

    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('surat-masuk/{suratMasuk}/edit', [SuratMasukController::class, 'edit'])
            ->name('surat-masuk.edit');

        Route::put('surat-masuk/{suratMasuk}', [SuratMasukController::class, 'update'])
            ->name('surat-masuk.update');

        Route::delete('surat-masuk/{suratMasuk}', [SuratMasukController::class, 'destroy'])
            ->name('surat-masuk.destroy');

        Route::get('surat-keluar/{suratKeluar}/edit', [SuratKeluarController::class, 'edit'])
            ->name('surat-keluar.edit');

        Route::put('surat-keluar/{suratKeluar}', [SuratKeluarController::class, 'update'])
            ->name('surat-keluar.update');

        Route::delete('surat-keluar/{suratKeluar}', [SuratKeluarController::class, 'destroy'])
            ->name('surat-keluar.destroy');
    });

    Route::middleware('role:admin,pimpinan,staf')->group(function () {
        Route::get('disposisi', [DisposisiController::class, 'index'])
            ->name('disposisi.index');

        Route::get('disposisi/{disposisi}', [DisposisiController::class, 'show'])
            ->name('disposisi.show');

        Route::patch('disposisi/{disposisi}/status', [DisposisiController::class, 'updateStatus'])
            ->name('disposisi.status');
    });

    Route::middleware('role:admin,pimpinan')->group(function () {
        Route::get('surat-masuk/{suratMasuk}/disposisi/create', [DisposisiController::class, 'create'])
            ->name('disposisi.create');

        Route::resource('disposisi', DisposisiController::class)
            ->except(['index', 'show', 'create']);
    });

    Route::middleware('role:admin,pimpinan,staf')
        ->prefix('export')
        ->name('export.')
        ->group(function () {
            Route::get('surat-masuk/excel', [ExportController::class, 'suratMasukExcel'])
                ->name('surat-masuk.excel');
            Route::get('surat-masuk/pdf', [ExportController::class, 'suratMasukPdf'])
                ->name('surat-masuk.pdf');
            Route::get('surat-keluar/excel', [ExportController::class, 'suratKeluarExcel'])
                ->name('surat-keluar.excel');
            Route::get('surat-keluar/pdf', [ExportController::class, 'suratKeluarPdf'])
                ->name('surat-keluar.pdf');
        });

    Route::middleware('role:admin')->group(function () {
        Route::resource('kategori', KategoriSuratController::class)
            ->except(['show']);

        Route::resource('users', UserController::class)
            ->except(['show']);
    });
});

Route::fallback(function () {
    if (view()->exists('errors.404')) {
        return response()->view('errors.404', [], 404);
    }

    abort(404);
});