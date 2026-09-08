<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $role = strtolower(trim((string) ($user->role ?? $user->jabatan ?? '')));

        if ($role === 'staff') {
            $role = 'staf';
        }

        $isStaf = $role === 'staf';

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
            $disposisiMenunggu = Disposisi::query()
                ->where('kepada_user_id', $userId)
                ->whereRaw('LOWER(TRIM(status)) = ?', ['menunggu'])
                ->count();

            $disposisiSelesai = Disposisi::query()
                ->where('kepada_user_id', $userId)
                ->whereRaw('LOWER(TRIM(status)) = ?', ['selesai'])
                ->count();

            $listDisposisi = Disposisi::query()
                ->with([
                    'suratMasuk',
                    'dari',
                ])
                ->where('kepada_user_id', $userId)
                ->whereRaw('LOWER(TRIM(status)) = ?', ['menunggu'])
                ->latest('created_at')
                ->limit(5)
                ->get();

            return view('dashboard.index', compact(
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
            ));
        }

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD ADMIN / PIMPINAN
        |--------------------------------------------------------------------------
        */

        $totalSuratMasuk = SuratMasuk::query()
            ->count();

        $totalSuratKeluar = SuratKeluar::query()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | SURAT BELUM DIPROSES
        |--------------------------------------------------------------------------
        */
        $suratPending = SuratMasuk::query()
            ->whereRaw('LOWER(TRIM(status)) = ?', ['baru'])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | SURAT SELESAI DIPROSES
        |--------------------------------------------------------------------------
        */
        $suratSelesai = SuratMasuk::query()
            ->whereRaw('LOWER(TRIM(status)) = ?', ['selesai'])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | SURAT MASUK TERBARU
        |--------------------------------------------------------------------------
        |
        | Tidak ada lagi relasi "instansi".
        | Data pengirim langsung berasal dari kolom "pengirim"
        | pada tabel surat_masuks.
        |
        */
        $suratMasukTerbaru = SuratMasuk::query()
            ->with('kategori')
            ->orderByDesc('tanggal_terima')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | GRAFIK 12 BULAN TERAKHIR
        |--------------------------------------------------------------------------
        |
        | Surat Masuk  -> tanggal_terima
        | Surat Keluar -> tanggal_surat
        |
        */
        $startMonth = Carbon::now()
            ->startOfMonth()
            ->subMonths(11);

        $endMonth = Carbon::now()
            ->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | SURAT MASUK PER BULAN
        |--------------------------------------------------------------------------
        */
        $masukPerBulan = SuratMasuk::query()
            ->selectRaw('
                YEAR(tanggal_terima) as tahun,
                MONTH(tanggal_terima) as bulan,
                COUNT(*) as total
            ')
            ->whereNotNull('tanggal_terima')
            ->whereBetween('tanggal_terima', [
                $startMonth->copy()->startOfDay(),
                $endMonth->copy()->endOfDay(),
            ])
            ->groupByRaw('
                YEAR(tanggal_terima),
                MONTH(tanggal_terima)
            ')
            ->get()
            ->keyBy(function ($row) {
                return sprintf(
                    '%04d-%02d',
                    $row->tahun,
                    $row->bulan
                );
            });

        /*
        |--------------------------------------------------------------------------
        | SURAT KELUAR PER BULAN
        |--------------------------------------------------------------------------
        */
        $keluarPerBulan = SuratKeluar::query()
            ->selectRaw('
                YEAR(tanggal_surat) as tahun,
                MONTH(tanggal_surat) as bulan,
                COUNT(*) as total
            ')
            ->whereNotNull('tanggal_surat')
            ->whereBetween('tanggal_surat', [
                $startMonth->copy()->startOfDay(),
                $endMonth->copy()->endOfDay(),
            ])
            ->groupByRaw('
                YEAR(tanggal_surat),
                MONTH(tanggal_surat)
            ')
            ->get()
            ->keyBy(function ($row) {
                return sprintf(
                    '%04d-%02d',
                    $row->tahun,
                    $row->bulan
                );
            });

        /*
        |--------------------------------------------------------------------------
        | SUSUN DATA GRAFIK
        |--------------------------------------------------------------------------
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

        for ($i = 0; $i < 12; $i++) {
            $currentMonth = $startMonth
                ->copy()
                ->addMonths($i);

            $key = $currentMonth->format('Y-m');

            $chartLabels[] = $namaBulan[(int) $currentMonth->month];

            $chartDataMasuk[] = (int) (
                $masukPerBulan[$key]->total ?? 0
            );

            $chartDataKeluar[] = (int) (
                $keluarPerBulan[$key]->total ?? 0
            );
        }

        return view('dashboard.index', compact(
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
        ));
    }
}