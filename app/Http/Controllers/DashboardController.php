<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Illuminate\Support\Carbon;
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

        /*
         * Normalisasi:
         * staf  -> staf
         * staff -> staf
         */
        if ($role === 'staff') {
            $role = 'staf';
        }

        $isStaf = $role === 'staf';

        /*
         * ==========================================================
         * DEFAULT DATA
         * ==========================================================
         *
         * Semua nilai default agar view tetap aman ketika dashboard
         * digunakan oleh role Staff maupun Admin/Pimpinan.
         */

        $totalSuratMasuk = 0;
        $totalSuratKeluar = 0;
        $suratPending = 0;
        $suratSelesai = 0;

        $disposisiMenunggu = 0;
        $disposisiSelesai = 0;

        $listDisposisi = collect();
        $suratMasukTerbaru = collect();
        $riwayatSuratKeluar = collect();

        /*
         * ==========================================================
         * DASHBOARD STAF
         * ==========================================================
         *
         * Staf hanya melihat disposisi yang ditujukan kepada akun
         * staf yang sedang login.
         */

        if ($isStaf) {

            /*
             * ======================================================
             * QUERY DASAR DISPOSISI
             * ======================================================
             */

            $disposisiDasar = Disposisi::query()
                ->where(
                    'kepada_user_id',
                    $userId
                );

            /*
             * ======================================================
             * DISPOSISI MENUNGGU
             * ======================================================
             */

            $disposisiMenunggu =
                (clone $disposisiDasar)
                    ->whereRaw(
                        'LOWER(TRIM(status)) = ?',
                        ['menunggu']
                    )
                    ->count();

            /*
             * ======================================================
             * DISPOSISI SELESAI
             * ======================================================
             */

            $disposisiSelesai =
                (clone $disposisiDasar)
                    ->whereRaw(
                        'LOWER(TRIM(status)) = ?',
                        ['selesai']
                    )
                    ->count();

            /*
             * ======================================================
             * DAFTAR DISPOSISI UNTUK STAFF
             * ======================================================
             */

            $listDisposisi =
                Disposisi::query()
                    ->with([
                        'suratMasuk',
                        'dari',
                        'kepada',
                    ])
                    ->where(
                        'kepada_user_id',
                        $userId
                    )
                    ->whereRaw(
                        'LOWER(TRIM(status)) = ?',
                        ['menunggu']
                    )
                    ->orderByDesc(
                        'created_at'
                    )
                    ->orderByDesc(
                        'id'
                    )
                    ->limit(5)
                    ->get();

            /*
             * ======================================================
             * RETURN DASHBOARD STAFF
             * ======================================================
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
                    'riwayatSuratKeluar',
                    'isStaf'
                )
            );
        }

        /*
         * ==========================================================
         * DASHBOARD ADMIN / PIMPINAN
         * ==========================================================
         *
         * Scorecard dan statistik dihitung dari seluruh arsip.
         * Tidak dibatasi bulan, sehingga pergantian bulan tidak
         * mereset atau mengosongkan data dashboard.
         */

        /*
         * ==========================================================
         * TOTAL SURAT MASUK
         * ==========================================================
         */

        $totalSuratMasuk =
            SuratMasuk::query()
                ->count();

        /*
         * ==========================================================
         * TOTAL SURAT KELUAR
         * ==========================================================
         */

        $totalSuratKeluar =
            SuratKeluar::query()
                ->count();

        /*
         * ==========================================================
         * STATISTIK BULANAN BERDASARKAN WAKTU INPUT
         * ==========================================================
         * Admin/Pimpinan dapat memilih bulan untuk melihat berapa
         * banyak data surat yang benar-benar dimasukkan ke sistem
         * pada bulan tersebut.
         *
         * Dasar perhitungan menggunakan created_at, bukan
         * tanggal_surat, sehingga perubahan tanggal pada isi surat
         * tidak memindahkan statistik ke bulan lain.
         *
         * Pilihan statistik minimal tersedia untuk 12 bulan terakhir,
         * sehingga bulan tanpa input tetap dapat dipilih dan bernilai 0.
         * Jika arsip lebih lama tersedia, periode tersebut juga tetap
         * dimasukkan ke daftar pilihan.
         */
        $requestedStatMonth = trim(
            (string) request()->query('stat_month', now()->format('Y-m'))
        );

        try {
            $selectedStatMonthDate = Carbon::createFromFormat(
                'Y-m',
                $requestedStatMonth
            )->startOfMonth();
        } catch (\Throwable $e) {
            $selectedStatMonthDate = now()->startOfMonth();
        }

        $selectedStatMonth = $selectedStatMonthDate->format('Y-m');
        $selectedStatMonthLabel = $selectedStatMonthDate->translatedFormat('F Y');
        $statMonthStart = $selectedStatMonthDate->copy()->startOfMonth();
        $statMonthEnd = $selectedStatMonthDate->copy()->endOfMonth();

        $statSuratMasuk =
            SuratMasuk::query()
                ->whereBetween(
                    'created_at',
                    [
                        $statMonthStart,
                        $statMonthEnd,
                    ]
                )
                ->count();

        $statSuratKeluar =
            SuratKeluar::query()
                ->whereBetween(
                    'created_at',
                    [
                        $statMonthStart,
                        $statMonthEnd,
                    ]
                )
                ->count();

        $statTotalArsip = $statSuratMasuk + $statSuratKeluar;

        $statFirstIncomingDate =
            SuratMasuk::query()
                ->whereNotNull('created_at')
                ->min('created_at');

        $statFirstOutgoingDate =
            SuratKeluar::query()
                ->whereNotNull('created_at')
                ->min('created_at');

        $statFirstDates = collect([
            $statFirstIncomingDate,
            $statFirstOutgoingDate,
        ])->filter();

        if ($statFirstDates->isNotEmpty()) {
            $statFirstDate = Carbon::parse(
                $statFirstDates->sort()->first()
            )->startOfMonth();
        } else {
            $statFirstDate = now()->startOfMonth();
        }

        $statCurrentDate = now()->startOfMonth();
        $minimumStatStart = $statCurrentDate->copy()->subMonths(11)->startOfMonth();

        if ($statFirstDate->greaterThan($minimumStatStart)) {
            $statFirstDate = $minimumStatStart;
        }

        if ($statFirstDate->greaterThan($statCurrentDate)) {
            $statFirstDate = $statCurrentDate->copy();
        }

        $statMonthOptions = collect();
        $monthsToShow = $statFirstDate->diffInMonths($statCurrentDate);

        for ($monthOffset = 0; $monthOffset <= $monthsToShow; $monthOffset++) {
            $month = $statCurrentDate->copy()->subMonths($monthOffset);

            $statMonthOptions->push([
                'value' => $month->format('Y-m'),
                'label' => $month->translatedFormat('F Y'),
            ]);
        }

        /*
         * ==========================================================
         * SURAT BELUM DIPROSES
         * ==========================================================
         *
         * Menghitung seluruh surat masuk dengan status "baru".
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
         *
         * Menghitung seluruh surat masuk dengan status "selesai".
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
         * Selalu mengambil 5 surat masuk terbaru.
         * Ketika bulan berganti, data lama tidak dihapus atau di-reset;
         * daftar hanya bergeser apabila ada surat yang lebih baru.
         */

        $suratMasukTerbaru =
            SuratMasuk::query()
                ->with('kategori')
                ->orderByDesc(
                    'tanggal_terima'
                )
                ->orderByDesc(
                    'created_at'
                )
                ->limit(5)
                ->get();

        /*
         * ==========================================================
         * RIWAYAT SURAT KELUAR
         * ==========================================================
         *
         * Data diambil langsung dari tabel surat_keluar, bukan
         * ActivityLog, sehingga daftar selalu mengikuti data surat
         * keluar yang sebenarnya.
         *
         * Maksimal 5 surat keluar terbaru.
         * Pergantian bulan tidak mereset data.
         */

        $riwayatSuratKeluar =
            SuratKeluar::query()
                ->with('kategori')
                ->orderByDesc(
                    'created_at'
                )
                ->orderByDesc(
                    'id'
                )
                ->limit(5)
                ->get();

        /*
         * ==========================================================
         * RETURN DASHBOARD ADMIN / PIMPINAN
         * ==========================================================
         *
         * Dashboard menggunakan donut chart berdasarkan statistik
         * bulan yang dipilih. Scorecard tetap menggunakan total seluruh
         * arsip sehingga tidak berubah ketika periode statistik diganti.
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
                'riwayatSuratKeluar',
                'isStaf',
                'selectedStatMonth',
                'selectedStatMonthLabel',
                'statSuratMasuk',
                'statSuratKeluar',
                'statTotalArsip',
                'statMonthOptions'
            )
        );
    }
}
