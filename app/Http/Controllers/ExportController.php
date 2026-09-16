<?php

namespace App\Http\Controllers;

use App\Exports\SuratKeluarExport;
use App\Exports\SuratMasukExport;
use App\Models\ActivityLog;
use App\Models\KategoriSurat;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ExportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | SURAT MASUK - EXCEL
    |--------------------------------------------------------------------------
    */

    public function suratMasukExcel(Request $request)
    {
        $this->ensureUserAuthenticated();

        $filters = $request->all();

        $this->logExport(
            'surat_masuk',
            'Export surat masuk ke Excel'
        );

        return Excel::download(
            new SuratMasukExport($filters),
            'laporan-surat-masuk-' .
                now()->format('Ymd-His') .
                '.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SURAT MASUK - PDF
    |--------------------------------------------------------------------------
    */

    public function suratMasukPdf(Request $request)
    {
        $this->ensureUserAuthenticated();

        $query = $this->buildSuratMasukQuery(
            $request
        );

        $suratMasuks = $query
            ->orderByRaw(
                'tanggal_terima IS NULL ASC'
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

        $filterInfo = $this->buildFilterInfo(
            $request
        );

        $this->logExport(
            'surat_masuk',
            'Export surat masuk ke PDF'
        );

        $pdf = Pdf::loadView(
            'exports.surat_masuk_pdf',
            [
                'suratMasuks' => $suratMasuks,
                'filterInfo' => $filterInfo,
            ]
        )
            ->setPaper(
                'a4',
                'landscape'
            )
            ->setOption(
                'isHtml5ParserEnabled',
                true
            )
            ->setOption(
                'isRemoteEnabled',
                true
            );

        return $pdf->download(
            'laporan-surat-masuk-' .
                now()->format('Ymd-His') .
                '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SURAT KELUAR - EXCEL
    |--------------------------------------------------------------------------
    */

    public function suratKeluarExcel(Request $request)
    {
        $this->ensureUserAuthenticated();

        $filters = $request->all();

        $this->logExport(
            'surat_keluar',
            'Export surat keluar ke Excel'
        );

        return Excel::download(
            new SuratKeluarExport($filters),
            'laporan-surat-keluar-' .
                now()->format('Ymd-His') .
                '.xlsx'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SURAT KELUAR - PDF
    |--------------------------------------------------------------------------
    */

    public function suratKeluarPdf(Request $request)
    {
        $this->ensureUserAuthenticated();

        $query = SuratKeluar::query()
            ->with([
                'kategori',
                'pembuat',
                'penandatangan',
            ]);

        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        */

        $query->filter(
            $request->all()
        );

        $suratKeluars = $query
            ->orderByRaw(
                'tanggal_surat IS NULL ASC'
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

        $filterInfo = $this->buildFilterInfo(
            $request
        );

        $this->logExport(
            'surat_keluar',
            'Export surat keluar ke PDF'
        );

        $pdf = Pdf::loadView(
            'exports.surat_keluar_pdf',
            [
                'suratKeluars' => $suratKeluars,
                'filterInfo' => $filterInfo,
            ]
        )
            ->setPaper(
                'a4',
                'landscape'
            )
            ->setOption(
                'isHtml5ParserEnabled',
                true
            )
            ->setOption(
                'isRemoteEnabled',
                true
            );

        return $pdf->download(
            'laporan-surat-keluar-' .
                now()->format('Ymd-His') .
                '.pdf'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY SURAT MASUK
    |--------------------------------------------------------------------------
    */

    private function buildSuratMasukQuery(
        Request $request
    ): Builder {
        $query = SuratMasuk::query()
            ->with([
                'kategori',
                'penerima',
                'disposisi.kepada',
            ]);

        /*
        |--------------------------------------------------------------------------
        | ROLE USER
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        $role = strtolower(
            trim(
                (string) (
                    $user->role ??
                    $user->jabatan ??
                    ''
                )
            )
        );

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI ROLE
        |--------------------------------------------------------------------------
        */

        if (
            $role === 'staf'
        ) {
            $role = 'staff';
        }

        /*
        |--------------------------------------------------------------------------
        | STAFF HANYA MELIHAT SURAT YANG DIDISPOSISIKAN
        |--------------------------------------------------------------------------
        */

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

        $query->filter(
            $request->all()
        );

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | INFORMASI FILTER
    |--------------------------------------------------------------------------
    */

    private function buildFilterInfo(
        Request $request
    ): array {
        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori = $request->input(
            'kategori_id',
            $request->input(
                'kategori_surat_id',
                []
            )
        );

        if (
            is_scalar($kategori) &&
            trim(
                (string) $kategori
            ) !== ''
        ) {
            $kategori = [
                $kategori,
            ];
        }

        if (
            !is_array($kategori)
        ) {
            $kategori = [];
        }

        $kategori = collect(
            $kategori
        )
            ->filter(
                fn ($value) =>
                    is_scalar($value) &&
                    is_numeric($value)
            )
            ->map(
                fn ($value) =>
                    (int) $value
            )
            ->filter(
                fn ($value) =>
                    $value > 0
            )
            ->unique()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status = $request->input(
            'status',
            []
        );

        if (
            is_scalar($status) &&
            trim(
                (string) $status
            ) !== ''
        ) {
            $status = [
                $status,
            ];
        }

        if (
            !is_array($status)
        ) {
            $status = [];
        }

        $status = collect(
            $status
        )
            ->filter(
                fn ($value) =>
                    is_scalar($value)
            )
            ->map(
                fn ($value) =>
                    strtolower(
                        trim(
                            (string) $value
                        )
                    )
            )
            ->unique()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | NAMA KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategoriNames = [];

        if (
            !empty($kategori)
        ) {
            $kategoriNames =
                KategoriSurat::query()
                    ->whereIn(
                        'id',
                        $kategori
                    )
                    ->orderBy(
                        'nama_kategori'
                    )
                    ->pluck(
                        'nama_kategori'
                    )
                    ->values()
                    ->all();
        }

        /*
        |--------------------------------------------------------------------------
        | LABEL STATUS
        |--------------------------------------------------------------------------
        */

        $statusLabels = [
            'baru' =>
                'Baru',

            'diproses' =>
                'Diproses',

            'proses' =>
                'Diproses',

            'didisposisikan' =>
                'Didisposisikan',

            'selesai' =>
                'Selesai',

            'diarsipkan' =>
                'Diarsipkan',

            'draft' =>
                'Draft',

            'disetujui' =>
                'Disetujui',

            'dikirim' =>
                'Dikirim',
        ];

        $statusNames = collect(
            $status
        )
            ->map(
                fn ($value) =>
                    $statusLabels[$value]
                    ?? ucfirst($value)
            )
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | TANGGAL
        |--------------------------------------------------------------------------
        */

        $dari = trim(
            (string) $request->input(
                'dari_tanggal',
                ''
            )
        );

        $sampai = trim(
            (string) $request->input(
                'sampai_tanggal',
                ''
            )
        );

        $tanggalLabel =
            'Semua tanggal';

        if (
            $dari !== '' &&
            $sampai !== ''
        ) {
            $tanggalLabel =
                $this->formatExportDate(
                    $dari
                ) .
                ' - ' .
                $this->formatExportDate(
                    $sampai
                );
        } elseif (
            $dari !== ''
        ) {
            $tanggalLabel =
                'Mulai ' .
                $this->formatExportDate(
                    $dari
                );
        } elseif (
            $sampai !== ''
        ) {
            $tanggalLabel =
                'Sampai ' .
                $this->formatExportDate(
                    $sampai
                );
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return [
            'search' =>
                $search !== ''
                    ? $search
                    : 'Semua',

            'kategori' =>
                !empty($kategoriNames)
                    ? implode(
                        ', ',
                        $kategoriNames
                    )
                    : 'Semua kategori',

            'status' =>
                !empty($statusNames)
                    ? implode(
                        ', ',
                        $statusNames
                    )
                    : 'Semua status',

            'tanggal' =>
                $tanggalLabel,

            'dicetak_pada' =>
                now()->translatedFormat(
                    'd F Y H:i'
                ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FORMAT TANGGAL
    |--------------------------------------------------------------------------
    */

    private function formatExportDate(
        ?string $date
    ): string {
        if (
            !$date
        ) {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse(
                $date
            )->format(
                'd/m/Y'
            );
        } catch (
            Throwable
        ) {
            return $date;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATION
    |--------------------------------------------------------------------------
    */

    private function ensureUserAuthenticated(): void
    {
        if (
            !Auth::check()
        ) {
            abort(
                401,
                'Anda harus login terlebih dahulu.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    private function logExport(
        string $module,
        string $description
    ): void {
        try {
            if (
                class_exists(
                    ActivityLog::class
                ) &&
                method_exists(
                    ActivityLog::class,
                    'catat'
                )
            ) {
                ActivityLog::catat(
                    'export',
                    $module,
                    $description
                );
            }
        } catch (
            Throwable $e
        ) {
            Log::warning(
                'Gagal mencatat activity log export.',
                [
                    'message' =>
                        $e->getMessage(),

                    'module' =>
                        $module,
                ]
            );
        }
    }
}