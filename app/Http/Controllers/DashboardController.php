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
     * Dashboard utama.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            abort(401);
        }

        $userId = (int) $user->id;

        /*
         * ==========================================================
         * NORMALISASI ROLE
         * ==========================================================
         */
        $role = strtolower(
            trim(
                (string) (
                    $user->role
                    ?? $user->jabatan
                    ?? ''
                )
            )
        );

        if ($role === 'staff') {
            $role = 'staf';
        }

        $isStaf = $role === 'staf';

        /*
         * ==========================================================
         * DEFAULT DATA
         * ==========================================================
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
         * ==========================================================
         * DASHBOARD STAF
         * ==========================================================
         *
         * Staf hanya membutuhkan data disposisi
         * yang diberikan kepada dirinya sendiri.
         */
        if ($isStaf) {
            $disposisiDasar = Disposisi::query()
                ->where(
                    'kepada_user_id',
                    $userId
                );

            /*
             * Disposisi menunggu.
             */
            $disposisiMenunggu = (clone $disposisiDasar)
                ->whereRaw(
                    'LOWER(TRIM(status)) = ?',
                    ['menunggu']
                )
                ->count();

            /*
             * Disposisi selesai.
             */
            $disposisiSelesai = (clone $disposisiDasar)
                ->whereRaw(
                    'LOWER(TRIM(status)) = ?',
                    ['selesai']
                )
                ->count();

            /*
             * Daftar 5 disposisi terbaru
             * yang masih menunggu tindak lanjut.
             */
            $listDisposisi = Disposisi::query()
                ->with([
                    'suratMasuk',
                    'dari',
                ])
                ->where(
                    'kepada_user_id',
                    $userId
                )
                ->whereRaw(
                    'LOWER(TRIM(status)) = ?',
                    ['menunggu']
                )
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get();

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
         * ==========================================================
         * DASHBOARD ADMIN / PIMPINAN
         * ==========================================================
         *
         * Admin dan pimpinan tidak membutuhkan listDisposisi
         * pada dashboard.
         */

        /*
         * ==========================================================
         * TOTAL SURAT MASUK
         * ==========================================================
         */
        $totalSuratMasuk =
            SuratMasuk::query()->count();

        /*
         * ==========================================================
         * TOTAL SURAT KELUAR
         * ==========================================================
         */
        $totalSuratKeluar =
            SuratKeluar::query()->count();

        /*
         * ==========================================================
         * SURAT BELUM DIPROSES
         * ==========================================================
         */
        $suratPending =
            SuratMasuk::query()
                ->whereRaw(
                    'LOWER(TRIM(status)) = ?',
                    ['baru']
                )
                ->count();

        /*
         * ==========================================================
         * SURAT SELESAI
         * ==========================================================
         */
        $suratSelesai =
            SuratMasuk::query()
                ->whereRaw(
                    'LOWER(TRIM(status)) = ?',
                    ['selesai']
                )
                ->count();

        /*
         * ==========================================================
         * SURAT MASUK TERBARU
         * ==========================================================
         *
         * Relasi yang digunakan hanya kategori.
         * Tidak menggunakan relasi instansi.
         *
         * Pengirim berasal dari:
         * surat_masuks.pengirim
         */
        $suratMasukTerbaru =
            SuratMasuk::query()
                ->with('kategori')
                ->orderByDesc('tanggal_terima')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

        /*
         * ==========================================================
         * GRAFIK 12 BULAN TERAKHIR
         * ==========================================================
         */
        $startMonth = Carbon::now()
            ->startOfMonth()
            ->subMonths(11);

        $endMonth = Carbon::now()
            ->endOfMonth();

        /*
         * ==========================================================
         * SURAT MASUK PER BULAN
         * ==========================================================
         *
         * Menggunakan tanggal_terima.
         */
        $masukPerBulan =
            SuratMasuk::query()
                ->selectRaw(
                    '
                    YEAR(tanggal_terima) AS tahun,
                    MONTH(tanggal_terima) AS bulan,
                    COUNT(*) AS total
                    '
                )
                ->whereNotNull(
                    'tanggal_terima'
                )
                ->whereBetween(
                    'tanggal_terima',
                    [
                        $startMonth
                            ->copy()
                            ->startOfDay(),

                        $endMonth
                            ->copy()
                            ->endOfDay(),
                    ]
                )
                ->groupByRaw(
                    '
                    YEAR(tanggal_terima),
                    MONTH(tanggal_terima)
                    '
                )
                ->get()
                ->keyBy(
                    function ($row) {
                        return sprintf(
                            '%04d-%02d',
                            $row->tahun,
                            $row->bulan
                        );
                    }
                );

        /*
         * ==========================================================
         * SURAT KELUAR PER BULAN
         * ==========================================================
         *
         * Menggunakan tanggal_surat.
         */
        $keluarPerBulan =
            SuratKeluar::query()
                ->selectRaw(
                    '
                    YEAR(tanggal_surat) AS tahun,
                    MONTH(tanggal_surat) AS bulan,
                    COUNT(*) AS total
                    '
                )
                ->whereNotNull(
                    'tanggal_surat'
                )
                ->whereBetween(
                    'tanggal_surat',
                    [
                        $startMonth
                            ->copy()
                            ->startOfDay(),

                        $endMonth
                            ->copy()
                            ->endOfDay(),
                    ]
                )
                ->groupByRaw(
                    '
                    YEAR(tanggal_surat),
                    MONTH(tanggal_surat)
                    '
                )
                ->get()
                ->keyBy(
                    function ($row) {
                        return sprintf(
                            '%04d-%02d',
                            $row->tahun,
                            $row->bulan
                        );
                    }
                );

        /*
         * ==========================================================
         * NAMA BULAN
         * ==========================================================
         */
        $namaBulan = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        /*
         * ==========================================================
         * SUSUN DATA GRAFIK
         * ==========================================================
         */
        for (
            $i = 0;
            $i < 12;
            $i++
        ) {
            $currentMonth =
                $startMonth
                    ->copy()
                    ->addMonths($i);

            $key =
                $currentMonth->format('Y-m');

            $chartLabels[] =
                $namaBulan[
                    (int) $currentMonth->month
                ];

            $chartDataMasuk[] =
                (int) (
                    $masukPerBulan[$key]->total
                    ?? 0
                );

            $chartDataKeluar[] =
                (int) (
                    $keluarPerBulan[$key]->total
                    ?? 0
                );
        }

        /*
         * ==========================================================
         * RETURN DASHBOARD ADMIN / PIMPINAN
         * ==========================================================
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