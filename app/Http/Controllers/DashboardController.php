<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan data utama untuk Dashboard
     */
    public function index()
    {
        $userId = Auth::id();
        $user = Auth::user();
        $role = strtolower($user->role ?? '');
        $isStaf = in_array($role, ['staf', 'staff']);

        // 1. Logika Jika User adalah Staf
        if ($isStaf) {
            // Disposisi Menunggu/Belum Selesai milik staf yang login
            $disposisiMenunggu = Disposisi::where('kepada_user_id', $userId)
                ->where('status', 'menunggu')
                ->count();

            // Disposisi Selesai milik staf
            $disposisiSelesai = Disposisi::where('kepada_user_id', $userId)
                ->where('status', 'selesai')
                ->count();

            // List Disposisi Tugas Untuk Saya
            $listDisposisi = Disposisi::with(['suratMasuk', 'dari'])
                ->where('kepada_user_id', $userId)
                ->where('status', 'menunggu')
                ->latest()
                ->take(5)
                ->get();

            // Kosongkan variabel non-staf agar aman dipanggil di view
            $totalSuratMasuk = 0;
            $totalSuratKeluar = 0;
            $suratPending = 0;
            $suratSelesai = 0;
            $suratMasukTerbaru = collect();
            $chartLabels = [];
            $chartDataMasuk = [];
            $chartDataKeluar = [];

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
                'chartDataKeluar'
            ));
        }

        // 2. Logika Jika User BUKAN Staf (Admin / Pimpinan)
        $totalSuratMasuk   = SuratMasuk::count();
        $totalSuratKeluar  = SuratKeluar::count();
        $suratPending      = SuratMasuk::whereIn('status', ['pending', 'baru', 'proses'])->count();
        $suratSelesai      = SuratMasuk::where('status', 'selesai')->count();
        
        // Karena admin/pimpinan tidak ada disposisi masuk, set nilai default 0 / kosong
        $disposisiMenunggu = 0;
        $disposisiSelesai  = 0;
        $listDisposisi     = collect();

        $suratMasukTerbaru = SuratMasuk::with(['kategori'])
            ->latest()
            ->take(5)
            ->get();

        // Data Grafik Chart.js (12 Bulan Terakhir)
        $startDate = Carbon::now()->startOfMonth()->subMonths(11);

        $masukPerBulan = SuratMasuk::query()
            ->selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, COUNT(*) as total')
            ->where('created_at', '>=', $startDate)
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->tahun, $row->bulan));

        $keluarPerBulan = SuratKeluar::query()
            ->selectRaw('YEAR(created_at) as tahun, MONTH(created_at) as bulan, COUNT(*) as total')
            ->where('created_at', '>=', $startDate)
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(fn ($row) => sprintf('%04d-%02d', $row->tahun, $row->bulan));

        $chartLabels = [];
        $chartDataMasuk = [];
        $chartDataKeluar = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->startOfMonth()->subMonths($i);
            $key = $date->format('Y-m');

            $chartLabels[] = $date->translatedFormat('M');
            $chartDataMasuk[] = (int) ($masukPerBulan[$key]->total ?? 0);
            $chartDataKeluar[] = (int) ($keluarPerBulan[$key]->total ?? 0);
        }

        // Return ke view
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
            'chartDataKeluar'
        ));
    }
}