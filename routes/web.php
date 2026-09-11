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

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */

    Route::get(
        'login',
        [LoginController::class, 'showLoginForm']
    )->name('login');

    Route::post(
        'login',
        [LoginController::class, 'login']
    )->name('login.attempt');


    /*
    |--------------------------------------------------------------------------
    | REGISTER
    |--------------------------------------------------------------------------
    */

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
        |--------------------------------------------------------------------------
        | CLEAR CACHE
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | STORAGE LINK
        |--------------------------------------------------------------------------
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
                        'message' => 'Gagal membuat storage link: ' .
                            $e->getMessage(),
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
    | Struktur route:
    |
    | GET    /surat-masuk
    | POST   /surat-masuk
    | GET    /surat-masuk/create
    | GET    /surat-masuk/{id}
    | GET    /surat-masuk/{id}/edit
    | PUT    /surat-masuk/{id}
    | DELETE /surat-masuk/{id}
    |
    | Route statis dan route khusus diletakkan lebih dahulu.
    | Route parameter {suratMasuk} diletakkan paling bawah.
    |
    */


    /*
    |--------------------------------------------------------------------------
    | SURAT MASUK - ADMIN / PIMPINAN
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pimpinan')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | CREATE SURAT MASUK
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk/create',
            [SuratMasukController::class, 'create']
        )->name('surat-masuk.create');


        /*
        |--------------------------------------------------------------------------
        | STORE SURAT MASUK
        |--------------------------------------------------------------------------
        */

        Route::post(
            'surat-masuk',
            [SuratMasukController::class, 'store']
        )->name('surat-masuk.store');


        /*
        |--------------------------------------------------------------------------
        | EDIT SURAT MASUK
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk/{suratMasuk}/edit',
            [SuratMasukController::class, 'edit']
        )->name('surat-masuk.edit');


        /*
        |--------------------------------------------------------------------------
        | UPDATE SURAT MASUK
        |--------------------------------------------------------------------------
        */

        Route::put(
            'surat-masuk/{suratMasuk}',
            [SuratMasukController::class, 'update']
        )->name('surat-masuk.update');


        /*
        |--------------------------------------------------------------------------
        | DELETE SURAT MASUK
        |--------------------------------------------------------------------------
        */

        Route::delete(
            'surat-masuk/{suratMasuk}',
            [SuratMasukController::class, 'destroy']
        )->name('surat-masuk.destroy');


        /*
        |--------------------------------------------------------------------------
        | STORE DISPOSISI
        |--------------------------------------------------------------------------
        |
        | POST:
        | /surat-masuk/{suratMasuk}/disposisi
        |
        | Ditangani oleh:
        | SuratMasukController@storeDisposisi
        |
        */

        Route::post(
            'surat-masuk/{suratMasuk}/disposisi',
            [SuratMasukController::class, 'storeDisposisi']
        )->name('surat-masuk.disposisi.store');
    });


    /*
    |--------------------------------------------------------------------------
    | SURAT MASUK - ADMIN / PIMPINAN / STAFF
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,pimpinan,staff')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | INDEX SURAT MASUK
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk',
            [SuratMasukController::class, 'index']
        )->name('surat-masuk.index');


        /*
        |--------------------------------------------------------------------------
        | PREVIEW LAMPIRAN
        |--------------------------------------------------------------------------
        |
        | File diambil dari Supabase menggunakan temporary URL.
        |
        */

        Route::get(
            'surat-masuk/{suratMasuk}/preview-lampiran',
            [SuratMasukController::class, 'previewLampiran']
        )->name('surat-masuk.preview-lampiran');


        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD LAMPIRAN
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk/{suratMasuk}/download-lampiran',
            [SuratMasukController::class, 'downloadLampiran']
        )->name('surat-masuk.download-lampiran');


        /*
        |--------------------------------------------------------------------------
        | CETAK LABEL
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk/{suratMasuk}/label',
            [SuratMasukController::class, 'cetakLabel']
        )->name('surat-masuk.label');


        /*
        |--------------------------------------------------------------------------
        | CETAK DISPOSISI
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-masuk/{suratMasuk}/cetak-disposisi',
            [SuratMasukController::class, 'cetakDisposisi']
        )->name('surat-masuk.cetak-disposisi');


        /*
        |--------------------------------------------------------------------------
        | DETAIL SURAT MASUK
        |--------------------------------------------------------------------------
        |
        | PENTING:
        | Route ini harus paling bawah karena menggunakan parameter dinamis.
        |
        */

        Route::get(
            'surat-masuk/{suratMasuk}',
            [SuratMasukController::class, 'show']
        )->name('surat-masuk.show');
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
        |--------------------------------------------------------------------------
        | CREATE DISPOSISI
        |--------------------------------------------------------------------------
        |
        | GET:
        | /surat-masuk/{suratMasuk}/disposisi/create
        |
        | Digunakan untuk membuka halaman form disposisi.
        |
        */

        Route::get(
            'surat-masuk/{suratMasuk}/disposisi/create',
            [DisposisiController::class, 'create']
        )->name('disposisi.create');


        /*
        |--------------------------------------------------------------------------
        | RESOURCE DISPOSISI
        |--------------------------------------------------------------------------
        |
        | index, show, create dibuat manual di bawah.
        |
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
        |--------------------------------------------------------------------------
        | INDEX DISPOSISI
        |--------------------------------------------------------------------------
        */

        Route::get(
            'disposisi',
            [DisposisiController::class, 'index']
        )->name('disposisi.index');


        /*
        |--------------------------------------------------------------------------
        | DETAIL DISPOSISI
        |--------------------------------------------------------------------------
        */

        Route::get(
            'disposisi/{disposisi}',
            [DisposisiController::class, 'show']
        )->name('disposisi.show');


        /*
        |--------------------------------------------------------------------------
        | UPDATE STATUS DISPOSISI
        |--------------------------------------------------------------------------
        */

        Route::patch(
            'disposisi/{disposisi}/status',
            [DisposisiController::class, 'updateStatus']
        )->name('disposisi.status');
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
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/create',
            [SuratKeluarController::class, 'create']
        )->name('surat-keluar.create');


        /*
        |--------------------------------------------------------------------------
        | STORE
        |--------------------------------------------------------------------------
        */

        Route::post(
            'surat-keluar',
            [SuratKeluarController::class, 'store']
        )->name('surat-keluar.store');


        /*
        |--------------------------------------------------------------------------
        | EDIT
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/{suratKeluar}/edit',
            [SuratKeluarController::class, 'edit']
        )->name('surat-keluar.edit');


        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */

        Route::put(
            'surat-keluar/{suratKeluar}',
            [SuratKeluarController::class, 'update']
        )->name('surat-keluar.update');


        /*
        |--------------------------------------------------------------------------
        | DELETE
        |--------------------------------------------------------------------------
        */

        Route::delete(
            'surat-keluar/{suratKeluar}',
            [SuratKeluarController::class, 'destroy']
        )->name('surat-keluar.destroy');


        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | INDEX
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar',
            [SuratKeluarController::class, 'index']
        )->name('surat-keluar.index');


        /*
        |--------------------------------------------------------------------------
        | PREVIEW LAMPIRAN
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/{suratKeluar}/preview-lampiran',
            [SuratKeluarController::class, 'previewLampiran']
        )->name('surat-keluar.preview-lampiran');


        /*
        |--------------------------------------------------------------------------
        | CETAK
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/{suratKeluar}/cetak',
            [SuratKeluarController::class, 'cetak']
        )->name('surat-keluar.cetak');


        /*
        |--------------------------------------------------------------------------
        | LABEL
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/{suratKeluar}/label',
            [SuratKeluarController::class, 'label']
        )->name('surat-keluar.label');


        /*
        |--------------------------------------------------------------------------
        | DETAIL
        |--------------------------------------------------------------------------
        */

        Route::get(
            'surat-keluar/{suratKeluar}',
            [SuratKeluarController::class, 'show']
        )->name('surat-keluar.show');
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
            |--------------------------------------------------------------------------
            | SURAT MASUK - EXCEL
            |--------------------------------------------------------------------------
            */

            Route::get(
                'surat-masuk/excel',
                [ExportController::class, 'suratMasukExcel']
            )->name('surat-masuk.excel');


            /*
            |--------------------------------------------------------------------------
            | SURAT MASUK - PDF
            |--------------------------------------------------------------------------
            */

            Route::get(
                'surat-masuk/pdf',
                [ExportController::class, 'suratMasukPdf']
            )->name('surat-masuk.pdf');


            /*
            |--------------------------------------------------------------------------
            | SURAT KELUAR - EXCEL
            |--------------------------------------------------------------------------
            */

            Route::get(
                'surat-keluar/excel',
                [ExportController::class, 'suratKeluarExcel']
            )->name('surat-keluar.excel');


            /*
            |--------------------------------------------------------------------------
            | SURAT KELUAR - PDF
            |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | KATEGORI SURAT
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'kategori',
            KategoriSuratController::class
        )->except([
            'show',
        ]);


        /*
        |--------------------------------------------------------------------------
        | USERS
        |--------------------------------------------------------------------------
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