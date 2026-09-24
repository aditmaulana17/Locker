<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
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
         * Dashboard versi baru menggunakan donut chart berdasarkan
         * total surat masuk dan total surat keluar. Karena data donut
         * berasal dari dua total di atas, tidak diperlukan lagi query
         * grafik 12 bulan dan tidak ada data chart yang di-reset ketika
         * bulan berubah.
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
}
