<?php

namespace App\Http\Controllers;

use App\Exports\SuratKeluarExport;
use App\Exports\SuratMasukExport;
use App\Models\ActivityLog;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SURAT MASUK - EXCEL
    |--------------------------------------------------------------------------
    */

    /**
     * Export Surat Masuk ke Excel.
     *
     * Filter tetap mengikuti request:
     * - search
     * - kategori_surat_id / kategori_id
     * - status
     * - dari_tanggal
     * - sampai_tanggal
     *
     * Pembatasan akses Staff akan diterapkan di
     * SuratMasukExport setelah class tersebut disesuaikan.
     */
    public function suratMasukExcel(
        Request $request
    ) {
        if (!Auth::check()) {
            abort(
                401,
                'Anda harus login terlebih dahulu.'
            );
        }

        ActivityLog::catat(
            'export',
            'surat_masuk',
            'Export surat masuk ke Excel'
        );

        return Excel::download(
            new SuratMasukExport(
                $request->all()
            ),
            'surat-masuk-' .
            now()->format('Ymd-His') .
            '.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SURAT MASUK - PDF
    |--------------------------------------------------------------------------
    */

    /**
     * Export Surat Masuk ke PDF.
     *
     * Admin dan Pimpinan:
     * semua surat yang tersedia.
     *
     * Staff:
     * hanya surat yang memiliki disposisi
     * kepada dirinya.
     */
    public function suratMasukPdf(
        Request $request
    ) {
        if (!Auth::check()) {
            abort(
                401,
                'Anda harus login terlebih dahulu.'
            );
        }

        $query = SuratMasuk::query()
            ->with([
                'kategori',
                'penerima',
                'disposisi.kepada',
            ]);

        /*
        |--------------------------------------------------------------------------
        | HAK AKSES USER
        |--------------------------------------------------------------------------
        */

        $role = strtolower(
            trim(
                (string) (
                    Auth::user()->role ??
                    ''
                )
            )
        );

        if ($role === 'staf') {
            $role = 'staff';
        }

        if (
            $role === 'staff'
        ) {
            $query->untukStaff(
                (int) Auth::id()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */

        $suratMasuks = $query
            ->filter(
                $request->all()
            )
            ->orderBy(
                'tanggal_terima',
                'asc'
            )
            ->orderBy(
                'id',
                'asc'
            )
            ->get();

        ActivityLog::catat(
            'export',
            'surat_masuk',
            'Export surat masuk ke PDF'
        );

        $pdf = Pdf::loadView(
            'exports.surat_masuk_pdf',
            compact(
                'suratMasuks'
            )
        )->setPaper(
            'a4',
            'landscape'
        );

        return $pdf->download(
            'surat-masuk-' .
            now()->format('Ymd-His') .
            '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SURAT KELUAR - EXCEL
    |--------------------------------------------------------------------------
    */

    /**
     * Export Surat Keluar ke Excel.
     */
    public function suratKeluarExcel(
        Request $request
    ) {
        if (!Auth::check()) {
            abort(
                401,
                'Anda harus login terlebih dahulu.'
            );
        }

        ActivityLog::catat(
            'export',
            'surat_keluar',
            'Export surat keluar ke Excel'
        );

        return Excel::download(
            new SuratKeluarExport(
                $request->all()
            ),
            'surat-keluar-' .
            now()->format('Ymd-His') .
            '.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SURAT KELUAR - PDF
    |--------------------------------------------------------------------------
    */

    /**
     * Export Surat Keluar ke PDF.
     */
    public function suratKeluarPdf(
        Request $request
    ) {
        if (!Auth::check()) {
            abort(
                401,
                'Anda harus login terlebih dahulu.'
            );
        }

        $suratKeluars = SuratKeluar::query()
            ->with(
                'kategori'
            )
            ->filter(
                $request->all()
            )
            ->orderBy(
                'tanggal_surat',
                'asc'
            )
            ->orderBy(
                'id',
                'asc'
            )
            ->get();

        ActivityLog::catat(
            'export',
            'surat_keluar',
            'Export surat keluar ke PDF'
        );

        $pdf = Pdf::loadView(
            'exports.surat_keluar_pdf',
            compact(
                'suratKeluars'
            )
        )->setPaper(
            'a4',
            'landscape'
        );

        return $pdf->download(
            'surat-keluar-' .
            now()->format('Ymd-His') .
            '.pdf'
        );
    }
}