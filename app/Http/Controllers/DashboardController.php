<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
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

        $chartLabels = [];
        $chartDataMasuk = [];
        $chartDataKeluar = [];

        /*
         * ==========================================================
         * DASHBOARD STAF
         * ==========================================================
         *
         * Staf hanya melihat disposisi yang ditujukan
         * kepada akun staf yang sedang login.
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
         * RIWAYAT SURAT KELUAR TERBARU
         * ==========================================================
         *
         * Struktur activity_logs:
         *
         * id
         * user_id
         * aktivitas
         * modul
         * deskripsi
         * created_at
         * updated_at
         *
         * Tidak ada kolom surat_keluar_id.
         *
         * Karena itu ID surat keluar dibaca dari deskripsi aktivitas,
         * contohnya:
         *
         * "Menambahkan surat keluar #9"
         * "Mengubah surat keluar #9"
         * "Menghapus surat keluar #9"
         *
         * Hanya aktivitas yang ID suratnya masih ada di tabel
         * surat_keluar yang akan ditampilkan.
         */

        /*
         * Ambil seluruh ID surat keluar yang masih ada.
         *
         * Surat yang sudah dihapus/soft deleted tidak masuk
         * ke query ini apabila model SuratKeluar menggunakan
         * SoftDeletes.
         */

        $suratKeluarIds =
            SuratKeluar::query()
                ->pluck('id')
                ->map(
                    function ($id) {
                        return (int) $id;
                    }
                )
                ->filter()
                ->values();

        /*
         * Default kosong.
         */

        $riwayatSuratKeluar = collect();

        /*
         * Hanya lakukan query ActivityLog jika masih ada
         * surat keluar aktif.
         */

        if ($suratKeluarIds->isNotEmpty()) {

            /*
             * Bentuk pola REGEXP.
             *
             * Contoh apabila ID aktif:
             *
             * 9, 10, 12
             *
             * menjadi:
             *
             * (^|[^0-9])#(9|10|12)([^0-9]|$)
             *
             * Dengan pembatas angka ini:
             *
             * #9
             *
             * tidak akan salah dianggap sebagai:
             *
             * #90
             * #91
             * #99
             */

            $idAlternatives =
                $suratKeluarIds
                    ->implode('|');

            $activityPattern =
                '(^|[^0-9])#(' .
                $idAlternatives .
                ')([^0-9]|$)';

            /*
             * Ambil aktivitas hanya dari modul surat_keluar
             * dan hanya jika deskripsinya mengandung ID surat
             * yang masih aktif.
             */

            $riwayatSuratKeluar =
                ActivityLog::query()
                    ->where(
                        'modul',
                        'surat_keluar'
                    )
                    ->whereNotNull(
                        'deskripsi'
                    )
                    ->whereRaw(
                        'deskripsi REGEXP ?',
                        [
                            $activityPattern
                        ]
                    )
                    ->orderByDesc(
                        'created_at'
                    )
                    ->orderByDesc(
                        'id'
                    )
                    ->limit(5)
                    ->get();
        }

        /*
         * ==========================================================
         * GRAFIK 12 BULAN TERAKHIR
         * ==========================================================
         */

        $startMonth =
            Carbon::now()
                ->startOfMonth()
                ->subMonths(11);

        $endMonth =
            Carbon::now()
                ->endOfMonth();

        /*
         * ==========================================================
         * SURAT MASUK PER BULAN
         * ==========================================================
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
                $currentMonth->format(
                    'Y-m'
                );

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
                'riwayatSuratKeluar',
                'chartLabels',
                'chartDataMasuk',
                'chartDataKeluar',
                'isStaf'
            )
        );
    }
}