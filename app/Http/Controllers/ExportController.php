<?php

namespace App\Http\Controllers;

use App\Exports\SuratKeluarExport;
use App\Exports\SuratMasukExport;
use App\Models\ActivityLog;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Export Surat Masuk ke Excel.
     */
    public function suratMasukExcel(Request $request)
    {
        ActivityLog::catat(
            'export',
            'surat_masuk',
            'Export surat masuk ke Excel'
        );

        return Excel::download(
            new SuratMasukExport($request->all()),
            'surat-masuk-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    /**
     * Export Surat Masuk ke PDF.
     */
    public function suratMasukPdf(Request $request)
    {
        $suratMasuks = SuratMasuk::query()
            ->with('kategori')
            ->filter($request->all())
            ->orderBy('tanggal_terima', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        ActivityLog::catat(
            'export',
            'surat_masuk',
            'Export surat masuk ke PDF'
        );

        $pdf = Pdf::loadView(
            'exports.surat_masuk_pdf',
            compact('suratMasuks')
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'surat-masuk-' . now()->format('Ymd-His') . '.pdf'
        );
    }

    /**
     * Export Surat Keluar ke Excel.
     */
    public function suratKeluarExcel(Request $request)
    {
        ActivityLog::catat(
            'export',
            'surat_keluar',
            'Export surat keluar ke Excel'
        );

        return Excel::download(
            new SuratKeluarExport($request->all()),
            'surat-keluar-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    /**
     * Export Surat Keluar ke PDF.
     */
    public function suratKeluarPdf(Request $request)
    {
        $suratKeluars = SuratKeluar::query()
            ->with('kategori')
            ->filter($request->all())
            ->orderBy('tanggal_surat', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        ActivityLog::catat(
            'export',
            'surat_keluar',
            'Export surat keluar ke PDF'
        );

        $pdf = Pdf::loadView(
            'exports.surat_keluar_pdf',
            compact('suratKeluars')
        )->setPaper('a4', 'landscape');

        return $pdf->download(
            'surat-keluar-' . now()->format('Ymd-His') . '.pdf'
        );
    }
}