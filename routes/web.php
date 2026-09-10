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

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| GUEST
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get(
        'login',
        [LoginController::class, 'showLoginForm']
    )->name('login');

    Route::post(
        'login',
        [LoginController::class, 'login']
    )->name('login.attempt');

    Route::get(
        'register',
        [RegisterController::class, 'showRegistrationForm']
    )->name('register');

    Route::post(
        'register',
        [RegisterController::class, 'register']
    )->name('register.attempt');
});


/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post(
    'logout',
    [LoginController::class, 'logout']
)
    ->middleware('auth')
    ->name('logout');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get(
        'dashboard',
        [DashboardController::class, 'index']
    )->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | ADMIN ONLY - SYSTEM
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        /*
        |----------------------------------------------------------------------
        | Clear Cache
        |----------------------------------------------------------------------
        */

        Route::get(
            'clear-cache',
            function () {
                Artisan::call('optimize:clear');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Cache aplikasi berhasil dibersihkan.',
                ]);
            }
        )->name('clear-cache');


        /*
        |----------------------------------------------------------------------
        | Storage Link
        |----------------------------------------------------------------------
        */

        Route::get(
            'link-storage',
            function () {
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
            }
        )->name('link-storage');
    });


    /*
    |--------------------------------------------------------------------------
    | SURAT MASUK
    |--------------------------------------------------------------------------
    |
    | PENTING:
    | Route statis seperti /create harus diletakkan SEBELUM
    | route dinamis /{suratMasuk}.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | SURAT MASUK - ADMIN / PIMPINAN
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pimpinan')->group(function () {

        /*
        |----------------------------------------------------------------------
        | Create
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk/create',
            [SuratMasukController::class, 'create']
        )->name('surat-masuk.create');


        /*
        |----------------------------------------------------------------------
        | Store
        |----------------------------------------------------------------------
        */

        Route::post(
            'surat-masuk',
            [SuratMasukController::class, 'store']
        )->name('surat-masuk.store');


        /*
        |----------------------------------------------------------------------
        | Edit
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk/{suratMasuk}/edit',
            [SuratMasukController::class, 'edit']
        )->name('surat-masuk.edit');


        /*
        |----------------------------------------------------------------------
        | Update
        |----------------------------------------------------------------------
        */

        Route::put(
            'surat-masuk/{suratMasuk}',
            [SuratMasukController::class, 'update']
        )->name('surat-masuk.update');


        /*
        |----------------------------------------------------------------------
        | Delete
        |----------------------------------------------------------------------
        */

        Route::delete(
            'surat-masuk/{suratMasuk}',
            [SuratMasukController::class, 'destroy']
        )->name('surat-masuk.destroy');
    });


    /*
    |--------------------------------------------------------------------------
    | SURAT MASUK - ADMIN / PIMPINAN / STAFF
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pimpinan,staff')->group(function () {

        /*
        |----------------------------------------------------------------------
        | Index
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk',
            [SuratMasukController::class, 'index']
        )->name('surat-masuk.index');


        /*
        |----------------------------------------------------------------------
        | Preview Lampiran
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk/{suratMasuk}/preview-lampiran',
            [SuratMasukController::class, 'previewLampiran']
        )->name('surat-masuk.preview-lampiran');


        /*
        |----------------------------------------------------------------------
        | Label
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk/{suratMasuk}/label',
            [SuratMasukController::class, 'cetakLabel']
        )->name('surat-masuk.label');


        /*
        |----------------------------------------------------------------------
        | Cetak Disposisi
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk/{suratMasuk}/cetak-disposisi',
            [SuratMasukController::class, 'cetakDisposisi']
        )->name('surat-masuk.cetak-disposisi');


        /*
        |----------------------------------------------------------------------
        | Disposisi Create
        |----------------------------------------------------------------------
        |
        | Route ini lebih spesifik daripada /{suratMasuk}.
        | Tetap diletakkan sebelum route detail.
        |
        */

        Route::get(
            'surat-masuk/{suratMasuk}/disposisi/create',
            [DisposisiController::class, 'create']
        )
            ->middleware('role:admin,pimpinan')
            ->name('disposisi.create');


        /*
        |----------------------------------------------------------------------
        | Detail
        |----------------------------------------------------------------------
        |
        | Route parameter DINAMIS selalu diletakkan paling bawah.
        |
        */

        Route::get(
            'surat-masuk/{suratMasuk}',
            [SuratMasukController::class, 'show']
        )->name('surat-masuk.show');
    });


    /*
    |--------------------------------------------------------------------------
    | SURAT KELUAR
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | SURAT KELUAR - ADMIN / PIMPINAN
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pimpinan')->group(function () {

        /*
        |----------------------------------------------------------------------
        | Create
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/create',
            [SuratKeluarController::class, 'create']
        )->name('surat-keluar.create');


        /*
        |----------------------------------------------------------------------
        | Store
        |----------------------------------------------------------------------
        */

        Route::post(
            'surat-keluar',
            [SuratKeluarController::class, 'store']
        )->name('surat-keluar.store');


        /*
        |----------------------------------------------------------------------
        | Edit
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/{suratKeluar}/edit',
            [SuratKeluarController::class, 'edit']
        )->name('surat-keluar.edit');


        /*
        |----------------------------------------------------------------------
        | Update
        |----------------------------------------------------------------------
        */

        Route::put(
            'surat-keluar/{suratKeluar}',
            [SuratKeluarController::class, 'update']
        )->name('surat-keluar.update');


        /*
        |----------------------------------------------------------------------
        | Delete
        |----------------------------------------------------------------------
        */

        Route::delete(
            'surat-keluar/{suratKeluar}',
            [SuratKeluarController::class, 'destroy']
        )->name('surat-keluar.destroy');


        /*
        |----------------------------------------------------------------------
        | Activity Log
        |----------------------------------------------------------------------
        */

        Route::post(
            'surat-keluar/{suratKeluar}/log',
            [SuratKeluarController::class, 'storeLog']
        )->name('surat-keluar.log.store');
    });


    /*
    |--------------------------------------------------------------------------
    | SURAT KELUAR - ADMIN / PIMPINAN / STAFF
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pimpinan,staff')->group(function () {

        /*
        |----------------------------------------------------------------------
        | Index
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar',
            [SuratKeluarController::class, 'index']
        )->name('surat-keluar.index');


        /*
        |----------------------------------------------------------------------
        | Preview Lampiran
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/{suratKeluar}/preview-lampiran',
            [SuratKeluarController::class, 'previewLampiran']
        )->name('surat-keluar.preview-lampiran');


        /*
        |----------------------------------------------------------------------
        | Cetak
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/{suratKeluar}/cetak',
            [SuratKeluarController::class, 'cetak']
        )->name('surat-keluar.cetak');


        /*
        |----------------------------------------------------------------------
        | Label
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/{suratKeluar}/label',
            [SuratKeluarController::class, 'label']
        )->name('surat-keluar.label');


        /*
        |----------------------------------------------------------------------
        | Detail
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/{suratKeluar}',
            [SuratKeluarController::class, 'show']
        )->name('surat-keluar.show');
    });


    /*
    |--------------------------------------------------------------------------
    | DISPOSISI
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | DISPOSISI - ADMIN / PIMPINAN
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pimpinan')->group(function () {

        /*
        |----------------------------------------------------------------------
        | Create Disposisi dari Surat Masuk
        |----------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk/{suratMasuk}/disposisi/create',
            [DisposisiController::class, 'create']
        )->name('disposisi.create');


        /*
        |----------------------------------------------------------------------
        | Resource Disposisi
        |----------------------------------------------------------------------
        */

        Route::resource(
            'disposisi',
            DisposisiController::class
        )->except([
            'index',
            'show',
            'create',
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | DISPOSISI - ADMIN / PIMPINAN / STAFF
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pimpinan,staff')->group(function () {

        /*
        |----------------------------------------------------------------------
        | Index
        |----------------------------------------------------------------------
        */

        Route::get(
            'disposisi',
            [DisposisiController::class, 'index']
        )->name('disposisi.index');


        /*
        |----------------------------------------------------------------------
        | Detail
        |----------------------------------------------------------------------
        */

        Route::get(
            'disposisi/{disposisi}',
            [DisposisiController::class, 'show']
        )->name('disposisi.show');


        /*
        |----------------------------------------------------------------------
        | Update Status
        |----------------------------------------------------------------------
        */

        Route::patch(
            'disposisi/{disposisi}/status',
            [DisposisiController::class, 'updateStatus']
        )->name('disposisi.status');
    });


    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pimpinan,staff')
        ->prefix('export')
        ->name('export.')
        ->group(function () {

            /*
            |------------------------------------------------------------------
            | Surat Masuk - Excel
            |------------------------------------------------------------------
            */

            Route::get(
                'surat-masuk/excel',
                [ExportController::class, 'suratMasukExcel']
            )->name('surat-masuk.excel');


            /*
            |------------------------------------------------------------------
            | Surat Masuk - PDF
            |------------------------------------------------------------------
            */

            Route::get(
                'surat-masuk/pdf',
                [ExportController::class, 'suratMasukPdf']
            )->name('surat-masuk.pdf');


            /*
            |------------------------------------------------------------------
            | Surat Keluar - Excel
            |------------------------------------------------------------------
            */

            Route::get(
                'surat-keluar/excel',
                [ExportController::class, 'suratKeluarExcel']
            )->name('surat-keluar.excel');


            /*
            |------------------------------------------------------------------
            | Surat Keluar - PDF
            |------------------------------------------------------------------
            */

            Route::get(
                'surat-keluar/pdf',
                [ExportController::class, 'suratKeluarPdf']
            )->name('surat-keluar.pdf');
        });


    /*
    |--------------------------------------------------------------------------
    | MASTER DATA - ADMIN ONLY
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin')->group(function () {

        /*
        |----------------------------------------------------------------------
        | Kategori Surat
        |----------------------------------------------------------------------
        */

        Route::resource(
            'kategori',
            KategoriSuratController::class
        )->except([
            'show',
        ]);


        /*
        |----------------------------------------------------------------------
        | Users
        |----------------------------------------------------------------------
        */

        Route::resource(
            'users',
            UserController::class
        )->except([
            'show',
        ]);
    });
});


/*
|--------------------------------------------------------------------------
| FALLBACK
|--------------------------------------------------------------------------
*/

Route::fallback(function () {

    if (view()->exists('errors.404')) {
        return response()->view(
            'errors.404',
            [],
            404
        );
    }

    abort(404);
});