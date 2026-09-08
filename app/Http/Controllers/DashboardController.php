<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard sesuai role pengguna.
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        /*
        |--------------------------------------------------------------------------
        | ROLE
        |--------------------------------------------------------------------------
        */
        $role = strtolower(
            trim(
                (string) ($user->role ?? $user->jabatan ?? '')
            )
        );

        $isStaf = in_array(
            $role,
            ['staf', 'staff'],
            true
        );

        /*
        |--------------------------------------------------------------------------
        | DEFAULT VALUE
        |--------------------------------------------------------------------------
        |
        | Variabel ini dibuat terlebih dahulu agar view tetap aman.
        |
        */
        $totalSuratMasuk = 0;
        $totalSuratKeluar = 0;
        $suratPending = 0;
        $suratSelesai = 0;

        $disposisiMenunggu = 0;
        $disposisiSelesai = 0;

        $listDisposisi = collect();
        $suratMasukTerbaru = collect();

        $chartLabels = [];
        $chartDataMasuk = [];
        $chartDataKeluar = [];

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD STAF
        |--------------------------------------------------------------------------
        */
        if ($isStaf) {
            /*
             * Disposisi yang ditujukan kepada staf yang sedang login
             * dan masih menunggu untuk ditindaklanjuti.
             */
            $disposisiMenunggu = Disposisi::query()
                ->where('kepada_user_id', $userId)
                ->whereRaw(
                    'LOWER(TRIM(status)) = ?',
                    ['menunggu']
                )
                ->count();

            /*
             * Disposisi yang sudah selesai milik staf.
             */
            $disposisiSelesai = Disposisi::query()
                ->where('kepada_user_id', $userId)
                ->whereRaw(
                    'LOWER(TRIM(status)) = ?',
                    ['selesai']
                )
                ->count();

            /*
             * Daftar disposisi yang masih menunggu.
             * Maksimal 5 data ditampilkan di dashboard.
             */
            $listDisposisi = Disposisi::query()
                ->with([
                    'suratMasuk',
                    'dari',
                ])
                ->where('kepada_user_id', $userId)
                ->whereRaw(
                    'LOWER(TRIM(status)) = ?',
                    ['menunggu']
                )
                ->latest('created_at')
                ->limit(5)
                ->get();

            /*
             * Staf tidak membutuhkan statistik surat utama.
             */
            return view(
                'dashboard.index',
                compact(
                    'totalSuratMasuk',
                    'totalSuratKeluar',
                    'suratPending',
                    'suratSelesai',
                    'disposisiMenunggu',
                    'disposisiSelesai',
                    'listDisposisi',
                    'suratMasukTerbaru',
                    'chartLabels',
                    'chartDataMasuk',
                    'chartDataKeluar',
                    'isStaf'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD ADMIN / PIMPINAN
        |--------------------------------------------------------------------------
        */

        /*
         * TOTAL SEMUA SURAT MASUK
         */
        $totalSuratMasuk = SuratMasuk::query()
            ->count();

        /*
         * TOTAL SEMUA SURAT KELUAR
         */
        $totalSuratKeluar = SuratKeluar::query()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | SURAT BELUM DIPROSES
        |--------------------------------------------------------------------------
        |
        | Hanya status "baru".
        |
        */
        $suratPending = SuratMasuk::query()
            ->whereRaw(
                'LOWER(TRIM(status)) = ?',
                ['baru']
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | SURAT SELESAI DIPROSES
        |--------------------------------------------------------------------------
        |
        | Hanya status "selesai".
        |
        */
        $suratSelesai = SuratMasuk::query()
            ->whereRaw(
                'LOWER(TRIM(status)) = ?',
                ['selesai']
            )
            ->count();

        /*
        |--------------------------------------------------------------------------
        | DISPOSISI
        |--------------------------------------------------------------------------
        |
        | Admin dan pimpinan tidak menggunakan scorecard disposisi staf.
        |
        */
        $disposisiMenunggu = 0;
        $disposisiSelesai = 0;

        $listDisposisi = collect();

        /*
        |--------------------------------------------------------------------------
        | SURAT MASUK TERBARU
        |--------------------------------------------------------------------------
        */
        $suratMasukTerbaru = SuratMasuk::query()
            ->with([
                'kategori',
                'instansi',
            ])
            ->orderByDesc('tanggal_terima')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | GRAFIK 12 BULAN TERAKHIR
        |--------------------------------------------------------------------------
        |
        | Surat Masuk  = tanggal_terima
        | Surat Keluar = tanggal_surat
        |
        | Tidak menggunakan created_at agar grafik berdasarkan tanggal surat
        | yang sebenarnya.
        |
        */

        $startMonth = Carbon::now()
            ->startOfMonth()
            ->subMonths(11);

        $endMonth = Carbon::now()
            ->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | DATA SURAT MASUK PER BULAN
        |--------------------------------------------------------------------------
        */
        $masukPerBulan = SuratMasuk::query()
            ->selectRaw(
                'YEAR(tanggal_terima) as tahun,
                 MONTH(tanggal_terima) as bulan,
                 COUNT(*) as total'
            )
            ->whereNotNull('tanggal_terima')
            ->whereBetween(
                'tanggal_terima',
                [
                    $startMonth->copy()->startOfDay(),
                    $endMonth->copy()->endOfDay(),
                ]
            )
            ->groupByRaw(
                'YEAR(tanggal_terima),
                 MONTH(tanggal_terima)'
            )
            ->get()
            ->keyBy(
                fn ($row) => sprintf(
                    '%04d-%02d',
                    $row->tahun,
                    $row->bulan
                )
            );

        /*
        |--------------------------------------------------------------------------
        | DATA SURAT KELUAR PER BULAN
        |--------------------------------------------------------------------------
        */
        $keluarPerBulan = SuratKeluar::query()
            ->selectRaw(
                'YEAR(tanggal_surat) as tahun,
                 MONTH(tanggal_surat) as bulan,
                 COUNT(*) as total'
            )
            ->whereNotNull('tanggal_surat')
            ->whereBetween(
                'tanggal_surat',
                [
                    $startMonth->copy()->startOfDay(),
                    $endMonth->copy()->endOfDay(),
                ]
            )
            ->groupByRaw(
                'YEAR(tanggal_surat),
                 MONTH(tanggal_surat)'
            )
            ->get()
            ->keyBy(
                fn ($row) => sprintf(
                    '%04d-%02d',
                    $row->tahun,
                    $row->bulan
                )
            );

        /*
        |--------------------------------------------------------------------------
        | SUSUN DATA CHART
        |--------------------------------------------------------------------------
        */
        for ($i = 0; $i < 12; $i++) {
            $currentMonth = $startMonth
                ->copy()
                ->addMonths($i);

            $key = $currentMonth->format('Y-m');

            /*
             * Label bulan.
             */
            $chartLabels[] =
                $currentMonth->translatedFormat('M');

            /*
             * Jumlah surat masuk.
             */
            $chartDataMasuk[] =
                (int) (
                    $masukPerBulan[$key]->total
                    ?? 0
                );

            /*
             * Jumlah surat keluar.
             */
            $chartDataKeluar[] =
                (int) (
                    $keluarPerBulan[$key]->total
                    ?? 0
                );
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN DASHBOARD
        |--------------------------------------------------------------------------
        */
        return view(
            'dashboard.index',
            compact(
                'totalSuratMasuk',
                'totalSuratKeluar',
                'suratPending',
                'suratSelesai',
                'disposisiMenunggu',
                'disposisiSelesai',
                'listDisposisi',
                'suratMasukTerbaru',
                'chartLabels',
                'chartDataMasuk',
                'chartDataKeluar',
                'isStaf'
            )
        );
    }
}