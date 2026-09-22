@extends('layouts.app')
@section('title', 'Surat Masuk')
@section('content')
@php
    use Illuminate\Support\Carbon;
    /*
    |--------------------------------------------------------------------------
    | USER
    |--------------------------------------------------------------------------
    */
    $user = auth()->user();
    $userRole = strtolower(
        trim(
            (string) (
                $user->role ??
                $user->jabatan ??
                ''
            )
        )
    );
    if ($userRole === 'staf') {
        $userRole = 'staff';
    }
    /*
    |--------------------------------------------------------------------------
    | HAK AKSES
    |--------------------------------------------------------------------------
    */
    $canManage = in_array(
        $userRole,
        [
            'admin',
            'pimpinan',
        ],
        true
    );
    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */
    $statusOptions = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'didisposisikan' => 'Didisposisikan',
        'selesai' => 'Selesai',
        'diarsipkan' => 'Diarsipkan',
    ];
    /*
    |--------------------------------------------------------------------------
    | SCORECARD
    |--------------------------------------------------------------------------
    |
    | Controller mengirim $statusCounts yang dihitung dari seluruh data
    | yang boleh dilihat user, bukan dari data pagination.
    |
    | Struktur:
    |
    | [
    |     'total' => 4,
    |     'baru' => 4,
    |     'diproses' => 0,
    |     'didisposisikan' => 0,
    |     'selesai' => 0,
    |     'diarsipkan' => 0,
    | ]
    |
    */
    $statusCounts = is_array($statusCounts ?? null)
        ? $statusCounts
        : [];
    $totalSuratMasuk = (int) (
        $statusCounts['total']
        ?? 0
    );
    $suratBaru = (int) (
        $statusCounts['baru']
        ?? 0
    );
    $suratDiproses = (int) (
        $statusCounts['diproses']
        ?? 0
    );
    $suratSelesai = (int) (
        $statusCounts['selesai']
        ?? 0
    );
    /*
    |--------------------------------------------------------------------------
    | FALLBACK
    |--------------------------------------------------------------------------
    |
    | Hanya digunakan apabila Controller belum mengirim $statusCounts.
    | Untuk data sedikit, halaman tetap bisa tampil.
    |
    */
    if (
        !isset($statusCounts['total']) &&
        isset($suratMasuks)
    ) {
        if (
            method_exists(
                $suratMasuks,
                'total'
            )
        ) {
            $totalSuratMasuk =
                (int) $suratMasuks->total();
        } else {
            $totalSuratMasuk =
                (int) $suratMasuks->count();
        }
        if (
            method_exists(
                $suratMasuks,
                'getCollection'
            )
        ) {
            $scorecardCollection =
                collect(
                    $suratMasuks->getCollection()
                );
        } else {
            $scorecardCollection =
                collect(
                    $suratMasuks
                );
        }
        $suratBaru =
            $scorecardCollection
                ->filter(
                    fn ($item) =>
                        strtolower(
                            trim(
                                (string) (
                                    $item->status ??
                                    ''
                                )
                            )
                        ) === 'baru'
                )
                ->count();
        $suratDiproses =
            $scorecardCollection
                ->filter(
                    fn ($item) =>
                        strtolower(
                            trim(
                                (string) (
                                    $item->status ??
                                    ''
                                )
                            )
                        ) === 'diproses'
                )
                ->count();
        $suratSelesai =
            $scorecardCollection
                ->filter(
                    fn ($item) =>
                        strtolower(
                            trim(
                                (string) (
                                    $item->status ??
                                    ''
                                )
                            )
                        ) === 'selesai'
                )
                ->count();
    }
    /*
    |--------------------------------------------------------------------------
    | KATEGORI TERPILIH
    |--------------------------------------------------------------------------
    */
    $rawKategori = request(
        'kategori_id',
        request(
            'kategori_surat_id',
            []
        )
    );
    if (
        is_scalar($rawKategori) &&
        trim((string) $rawKategori) !== ''
    ) {
        $rawKategori = [
            $rawKategori
        ];
    }
    if (!is_array($rawKategori)) {
        $rawKategori = [];
    }
    $selectedKategori =
        collect(
            $rawKategori
        )
        ->flatten()
        ->filter(
            fn ($id) =>
                is_scalar($id) &&
                is_numeric($id) &&
                (int) $id > 0
        )
        ->map(
            fn ($id) =>
                (string) (
                    (int) $id
                )
        )
        ->unique()
        ->values()
        ->all();
    /*
    |--------------------------------------------------------------------------
    | STATUS TERPILIH
    |--------------------------------------------------------------------------
    */
    $rawStatus =
        request(
            'status',
            []
        );
    if (
        is_scalar($rawStatus) &&
        trim((string) $rawStatus) !== ''
    ) {
        $rawStatus = [
            $rawStatus
        ];
    }
    if (!is_array($rawStatus)) {
        $rawStatus = [];
    }
    $selectedStatus =
        collect(
            $rawStatus
        )
        ->filter(
            fn ($status) =>
                is_scalar($status)
        )
        ->map(
            fn ($status) =>
                strtolower(
                    trim(
                        (string) $status
                    )
                )
        )
        ->filter(
            fn ($status) =>
                array_key_exists(
                    $status,
                    $statusOptions
                )
        )
        ->unique()
        ->values()
        ->all();
    /*
    |--------------------------------------------------------------------------
    | RENTANG TANGGAL
    |--------------------------------------------------------------------------
    */
    $dariTanggal =
        request(
            'dari_tanggal'
        );
    $sampaiTanggal =
        request(
            'sampai_tanggal'
        );
    $visibleDateRange = '';
    if (
        $dariTanggal &&
        $sampaiTanggal
    ) {
        try {
            $start =
                Carbon::createFromFormat(
                    'Y-m-d',
                    $dariTanggal
                );
            $end =
                Carbon::createFromFormat(
                    'Y-m-d',
                    $sampaiTanggal
                );
            $visibleDateRange =
                $start->format('d/m/Y') .
                ' - ' .
                $end->format('d/m/Y');
        } catch (\Throwable $e) {
            $visibleDateRange = '';
        }
    } elseif ($dariTanggal) {
        try {
            $visibleDateRange =
                Carbon::createFromFormat(
                    'Y-m-d',
                    $dariTanggal
                )->format('d/m/Y');
        } catch (\Throwable $e) {
            $visibleDateRange = '';
        }
    } elseif ($sampaiTanggal) {
        try {
            $visibleDateRange =
                Carbon::createFromFormat(
                    'Y-m-d',
                    $sampaiTanggal
                )->format('d/m/Y');
        } catch (\Throwable $e) {
            $visibleDateRange = '';
        }
    }
    /*
    |--------------------------------------------------------------------------
    | FILTER AKTIF
    |--------------------------------------------------------------------------
    */
    $hasFilters =
        request()->filled('search') ||
        !empty($selectedKategori) ||
        !empty($selectedStatus) ||
        request()->filled('dari_tanggal') ||
        request()->filled('sampai_tanggal');
    /*
    |--------------------------------------------------------------------------
    | DATA TABEL
    |--------------------------------------------------------------------------
    */
    $hasVisibleData =
        $totalSuratMasuk > 0;
    /*
    |--------------------------------------------------------------------------
    | EXPORT QUERY
    |--------------------------------------------------------------------------
    */
    $exportQuery =
        request()->query();
@endphp
@push('styles')
<style>
/* ==========================================================================
   PAGE
   ========================================================================== */
.surat-page{
    min-width:0;
}
/* ==========================================================================
   HEADER
   ========================================================================== */
.surat-page-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    margin-bottom:20px;
}
.surat-page-header-left{
    display:flex;
    align-items:center;
    gap:16px;
    min-width:0;
}
.surat-page-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    width:60px;
    height:60px;
    flex:0 0 60px;
    border-radius:15px;
    background:
        linear-gradient(
            135deg,
            #dbeafe,
            #eff6ff
        );
    color:#2563eb;
}
.surat-page-title{
    margin:0;
    color:#172554;
    font-size:30px;
    font-weight:800;
    line-height:1.1;
    letter-spacing:-.025em;
}
.surat-page-description{
    margin-top:5px;
    color:#64748b;
    font-size:14px;
    line-height:1.5;
}
.surat-create-button{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    min-height:46px;
    padding:0 20px;
    border-radius:10px;
    background:#2563eb;
    color:#fff;
    font-size:13px;
    font-weight:700;
    text-decoration:none;
    box-shadow:
        0 8px 20px
        rgba(37,99,235,.20);
    transition:
        background .15s ease,
        transform .15s ease,
        box-shadow .15s ease;
}
.surat-create-button:hover{
    background:#1d4ed8;
    transform:
        translateY(-1px);
    box-shadow:
        0 12px 25px
        rgba(37,99,235,.25);
}
.surat-header-actions{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    gap:8px;
    flex-wrap:wrap;
}
.surat-export-button{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:6px;
    min-height:40px;
    padding:0 13px;
    border:1px solid #dbe4f0;
    border-radius:10px;
    background:#fff;
    color:#475569;
    font-size:11px;
    font-weight:700;
    line-height:1;
    text-decoration:none;
    box-shadow:0 2px 8px rgba(15,23,42,.035);
    transition:background .15s ease,border-color .15s ease,color .15s ease,transform .15s ease;
}
.surat-export-button:hover{
    transform:translateY(-1px);
}
.surat-export-button.excel:hover{
    border-color:#a7f3d0;
    background:#ecfdf5;
    color:#047857;
}
.surat-export-button.pdf:hover{
    border-color:#fecdd3;
    background:#fff1f2;
    color:#be123c;
}
.surat-header-actions .surat-create-button{
    min-height:40px;
    padding:0 13px;
    font-size:11px;
    gap:6px;
}
/* ==========================================================================
   SCORECARD
   ========================================================================== */
.surat-stat-grid{
    display:grid;
    grid-template-columns:
        repeat(
            4,
            minmax(0,1fr)
        );
    gap:16px;
    margin-bottom:20px;
}
.surat-stat-card{
    position:relative;
    display:flex;
    align-items:center;
    gap:15px;
    min-height:124px;
    padding:18px 20px;
    overflow:hidden;
    border:1px solid #dbe4f0;
    border-radius:12px;
    background:#fff;
    box-shadow:
        0 2px 8px
        rgba(15,23,42,.035);
}
.surat-stat-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    width:52px;
    height:52px;
    flex:0 0 52px;
    border-radius:14px;
}
.surat-stat-icon.blue{
    background:#eaf2ff;
    color:#2563eb;
}
.surat-stat-icon.red{
    background:#ffe9ec;
    color:#e11d48;
}
.surat-stat-icon.amber{
    background:#fff4dc;
    color:#d97706;
}
.surat-stat-icon.green{
    background:#dcf8ee;
    color:#059669;
}
.surat-stat-content{
    min-width:0;
}
.surat-stat-label{
    color:#64748b;
    font-size:12px;
    font-weight:600;
}
.surat-stat-value{
    margin-top:4px;
    color:#172033;
    font-size:28px;
    font-weight:800;
    line-height:1;
}
.surat-stat-value.red{
    color:#e11d48;
}
.surat-stat-description{
    margin-top:7px;
    color:#94a3b8;
    font-size:11px;
}
/* ==========================================================================
   FILTER
   ========================================================================== */
.surat-filter-card{
    margin-bottom:14px;
    padding:18px;
    border:1px solid #dbe4f0;
    border-radius:12px;
    background:#fff;
    box-shadow:
        0 2px 10px
        rgba(15,23,42,.035);
}
.surat-filter-main{
    display:grid;
    grid-template-columns:
        minmax(0,1fr)
        auto
        290px;
    gap:10px;
    align-items:center;
}
.surat-search-wrapper{
    position:relative;
    min-width:0;
}
.surat-search-icon{
    position:absolute;
    top:50%;
    left:14px;
    z-index:2;
    display:flex;
    align-items:center;
    justify-content:center;
    width:18px;
    height:18px;
    color:#64748b;
    transform:
        translateY(-50%);
    pointer-events:none;
}
.surat-search-input{
    width:100%;
    height:44px;
    padding:
        0
        14px
        0
        43px;
    border:1px solid #d6e0ec;
    border-radius:10px;
    outline:none;
    background:#fff;
    color:#334155;
    font-size:12px;
    transition:
        border-color .15s ease,
        box-shadow .15s ease;
}
.surat-search-input::placeholder{
    color:#94a3b8;
}
.surat-search-input:hover{
    border-color:#b8c5d6;
}
.surat-search-input:focus{
    border-color:#3b82f6;
    box-shadow:
        0 0 0 3px
        rgba(59,130,246,.10);
}
/* ==========================================================================
   FILTER BUTTON
   ========================================================================== */
.surat-filter-submit{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    height:44px;
    min-width:112px;
    padding:0 17px;
    border:1px solid #d6e0ec;
    border-radius:10px;
    background:#fff;
    color:#475569;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
    transition:
        background .15s ease,
        border-color .15s ease,
        color .15s ease;
}
.surat-filter-submit:hover{
    border-color:#94a3b8;
    background:#f8fafc;
    color:#1e293b;
}
.surat-filter-submit.has-filter{
    border-color:#bfdbfe;
    background:#eff6ff;
    color:#2563eb;
}
/* ==========================================================================
   DATE RANGE
   ========================================================================== */
.surat-date-wrapper{
    position:relative;
}
.surat-date-input{
    width:100%;
    height:44px;
    padding:
        0
        38px
        0
        42px;
    border:1px solid #d6e0ec;
    border-radius:10px;
    outline:none;
    background:#fff;
    color:#475569;
    font-size:12px;
    font-weight:600;
    cursor:pointer;
    transition:
        border-color .15s ease,
        box-shadow .15s ease;
}
.surat-date-input:hover{
    border-color:#b8c5d6;
}
.surat-date-input:focus{
    border-color:#3b82f6;
    box-shadow:
        0 0 0 3px
        rgba(59,130,246,.10);
}
.surat-date-left-icon{
    position:absolute;
    top:50%;
    left:14px;
    z-index:2;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#64748b;
    transform:
        translateY(-50%);
    pointer-events:none;
}
.surat-date-clear{
    position:absolute;
    top:50%;
    right:4px;
    display:none;
    align-items:center;
    justify-content:center;
    width:34px;
    height:34px;
    border:0;
    border-radius:8px;
    background:transparent;
    color:#94a3b8;
    cursor:pointer;
    transform:
        translateY(-50%);
}
.surat-date-clear:hover{
    background:#fff1f2;
    color:#e11d48;
}
/* ==========================================================================
   DROPDOWN
   ========================================================================== */
.surat-filter-secondary{
    display:grid;
    grid-template-columns:
        repeat(
            2,
            minmax(0,1fr)
        );
    gap:10px;
    margin-top:10px;
}
.surat-filter-dropdown{
    position:relative;
    min-width:0;
}
.surat-filter-trigger{
    display:flex;
    align-items:center;
    width:100%;
    min-height:48px;
    gap:10px;
    padding:
        7px
        12px;
    border:1px solid #d6e0ec;
    border-radius:10px;
    background:#fff;
    color:#334155;
    text-align:left;
    cursor:pointer;
    transition:
        border-color .15s ease,
        background .15s ease,
        box-shadow .15s ease;
}
.surat-filter-trigger:hover{
    border-color:#b8c5d6;
    background:#f8fafc;
}
.surat-filter-trigger.is-open,
.surat-filter-trigger:focus{
    outline:none;
    border-color:#3b82f6;
    box-shadow:
        0 0 0 3px
        rgba(59,130,246,.10);
}
.surat-filter-trigger.is-active{
    border-color:#93c5fd;
    background:#f8fbff;
}
.surat-filter-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    width:32px;
    height:32px;
    flex:0 0 32px;
    border-radius:8px;
}
.surat-filter-icon.category{
    background:#eff6ff;
    color:#2563eb;
}
.surat-filter-icon.status{
    background:#fff7ed;
    color:#d97706;
}
.surat-filter-trigger-content{
    min-width:0;
    flex:1;
}
.surat-filter-trigger-title{
    display:block;
    color:#334155;
    font-size:11px;
    font-weight:700;
}
.surat-filter-trigger-subtitle{
    display:block;
    margin-top:2px;
    overflow:hidden;
    color:#94a3b8;
    font-size:9px;
    line-height:1.2;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.surat-filter-count{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-width:60px;
    height:23px;
    padding:0 8px;
    border-radius:999px;
    font-size:9px;
    font-weight:700;
    white-space:nowrap;
}
.surat-filter-count.category{
    background:#eff6ff;
    color:#2563eb;
}
.surat-filter-count.status{
    background:#fff7ed;
    color:#d97706;
}
.surat-filter-chevron{
    display:flex;
    align-items:center;
    justify-content:center;
    width:18px;
    height:18px;
    color:#64748b;
    transition:
        transform .2s ease;
}
.surat-filter-trigger.is-open
.surat-filter-chevron{
    transform:
        rotate(180deg);
}
/* ==========================================================================
   DROPDOWN MENU
   ========================================================================== */
.surat-filter-menu{
    position:absolute;
    top:calc(100% + 7px);
    right:0;
    left:0;
    z-index:9998;
    overflow:hidden;
    border:1px solid #cbd5e1;
    border-radius:12px;
    background:#fff;
    box-shadow:
        0 20px 45px
        rgba(15,23,42,.15),
        0 5px 15px
        rgba(15,23,42,.06);
}
.surat-filter-menu.hidden{
    display:none!important;
}
.surat-filter-menu-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:
        11px
        12px;
    border-bottom:1px solid #e2e8f0;
    background:#f8fafc;
}
.surat-filter-menu-title{
    color:#334155;
    font-size:11px;
    font-weight:800;
}
.surat-filter-menu-description{
    margin-top:2px;
    color:#94a3b8;
    font-size:9px;
}
.surat-filter-menu-actions{
    display:flex;
    gap:3px;
}
.surat-filter-action{
    border:0;
    border-radius:6px;
    padding:
        5px
        7px;
    background:transparent;
    font-size:9px;
    font-weight:700;
    cursor:pointer;
}
.surat-filter-action.select{
    color:#2563eb;
}
.surat-filter-action.select:hover{
    background:#eff6ff;
}
.surat-filter-action.clear{
    color:#64748b;
}
.surat-filter-action.clear:hover{
    background:#f1f5f9;
}
.surat-filter-options{
    display:grid;
    grid-template-columns:
        repeat(
            2,
            minmax(0,1fr)
        );
    gap:7px;
    max-height:260px;
    padding:10px;
    overflow-y:auto;
}
.surat-filter-option{
    display:flex;
    align-items:center;
    min-height:38px;
    gap:8px;
    padding:
        7px
        9px;
    border:1px solid #dbe3ed;
    border-radius:8px;
    background:#fff;
    cursor:pointer;
    transition:
        border-color .15s ease,
        background .15s ease;
}
.surat-filter-option:hover,
.surat-filter-option.is-selected{
    border-color:#93c5fd;
    background:#eff6ff;
}
.surat-filter-option input{
    width:15px;
    height:15px;
    margin:0;
    accent-color:#2563eb;
    cursor:pointer;
}
.surat-filter-option-text{
    min-width:0;
    overflow:hidden;
    color:#475569;
    font-size:10px;
    font-weight:600;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.surat-filter-option.is-selected
.surat-filter-option-text{
    color:#1d4ed8;
}
.surat-filter-menu-footer{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    padding:
        8px
        12px;
    border-top:1px solid #e2e8f0;
}
.surat-filter-footer-count{
    color:#64748b;
    font-size:9px;
    font-weight:600;
}
.surat-filter-footer-hint{
    color:#94a3b8;
    font-size:9px;
}
/* ==========================================================================
   DATE PICKER
   ========================================================================== */
.custom-date-picker,
.custom-picker-panel{
    border:1px solid #cbd5e1;
    background:#fff;
    box-shadow:
        0 24px 70px
        rgba(15,23,42,.18),
        0 8px 25px
        rgba(15,23,42,.08);
}
.custom-date-picker{
    position:fixed;
    z-index:999999;
    width:720px;
    max-width:
        calc(100vw - 20px);
    overflow:hidden;
    border-radius:14px;
}
.custom-date-picker.hidden,
.custom-picker-panel.hidden{
    display:none!important;
}
.custom-date-picker-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:
        11px
        14px;
    border-bottom:
        1px solid
        #e2e8f0;
}
.custom-date-picker-title{
    color:#334155;
    font-size:12px;
    font-weight:800;
}
.custom-date-picker-close,
.custom-picker-panel-close{
    display:flex;
    align-items:center;
    justify-content:center;
    border:0;
    background:#f8fafc;
    color:#64748b;
    cursor:pointer;
}
.custom-date-picker-close{
    width:30px;
    height:30px;
    border-radius:8px;
    font-size:18px;
}
.custom-date-picker-close:hover,
.custom-picker-panel-close:hover{
    background:#f1f5f9;
    color:#ef4444;
}
.custom-date-picker-calendars{
    display:grid;
    grid-template-columns:
        repeat(
            2,
            minmax(0,1fr)
        );
}
.custom-calendar{
    padding:
        13px
        15px
        11px;
}
.custom-calendar+.custom-calendar{
    border-left:
        1px solid
        #e2e8f0;
}
.custom-calendar-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:5px;
    min-height:38px;
    margin-bottom:5px;
}
.custom-calendar-month-buttons{
    display:flex;
    align-items:center;
    justify-content:center;
    flex:1;
    gap:4px;
}
.custom-calendar-nav{
    display:flex;
    align-items:center;
    justify-content:center;
    width:32px;
    height:32px;
    flex:0 0 32px;
    border:0;
    border-radius:8px;
    background:transparent;
    color:#64748b;
    font-size:21px;
    cursor:pointer;
}
.custom-calendar-nav:hover{
    background:#eff6ff;
    color:#2563eb;
}
.custom-calendar-month,
.custom-calendar-year{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:5px;
    min-height:32px;
    border:1px solid #dbe3ed;
    border-radius:8px;
    padding:
        6px
        9px;
    background:#f8fafc;
    color:#334155;
    font-family:inherit;
    font-size:11px;
    font-weight:800;
    cursor:pointer;
}
.custom-calendar-month:hover,
.custom-calendar-year:hover{
    border-color:#93c5fd;
    background:#eff6ff;
    color:#2563eb;
}
.custom-calendar-month::after,
.custom-calendar-year::after{
    content:'';
    width:6px;
    height:6px;
    margin-top:-3px;
    border-right:
        1.5px
        solid
        currentColor;
    border-bottom:
        1.5px
        solid
        currentColor;
    transform:
        rotate(45deg);
}
.custom-calendar-weekdays,
.custom-calendar-days{
    display:grid;
    grid-template-columns:
        repeat(
            7,
            minmax(0,1fr)
        );
    gap:2px;
}
.custom-calendar-weekdays{
    margin-bottom:3px;
}
.custom-calendar-weekday{
    display:flex;
    align-items:center;
    justify-content:center;
    height:26px;
    color:#94a3b8;
    font-size:9px;
    font-weight:800;
}
.custom-calendar-day{
    display:flex;
    align-items:center;
    justify-content:center;
    height:34px;
    border:0;
    border-radius:7px;
    background:transparent;
    color:#475569;
    font-family:inherit;
    font-size:10px;
    font-weight:600;
    cursor:pointer;
}
.custom-calendar-day:hover{
    background:#eff6ff;
    color:#2563eb;
}
.custom-calendar-day.other-month{
    color:#cbd5e1;
}
.custom-calendar-day.today{
    box-shadow:
        inset 0 0 0 1px
        #93c5fd;
    color:#2563eb;
}
.custom-calendar-day.in-range{
    border-radius:0;
    background:#eff6ff;
    color:#2563eb;
}
.custom-calendar-day.range-start{
    border-radius:
        999px
        0
        0
        999px;
    background:#2563eb;
    color:#fff;
}
.custom-calendar-day.range-end{
    border-radius:
        0
        999px
        999px
        0;
    background:#2563eb;
    color:#fff;
}
.custom-calendar-day.range-start.range-end{
    border-radius:999px;
}
.custom-date-picker-footer{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:
        10px
        12px;
    border-top:
        1px solid
        #e2e8f0;
}
.custom-date-picker-selected{
    min-width:0;
    color:#64748b;
    font-size:10px;
    font-weight:700;
}
.custom-date-picker-actions{
    display:flex;
    align-items:center;
    gap:6px;
}
.custom-date-picker-button{
    height:34px;
    border:1px solid #dbe3ed;
    border-radius:8px;
    padding:0 12px;
    background:#f8fafc;
    color:#475569;
    font-family:inherit;
    font-size:10px;
    font-weight:700;
    cursor:pointer;
}
.custom-date-picker-button:hover{
    background:#f1f5f9;
}
.custom-date-picker-button.apply{
    border-color:#2563eb;
    background:#2563eb;
    color:#fff;
}
.custom-date-picker-button.apply:hover{
    background:#1d4ed8;
}
.custom-picker-panel{
    position:fixed;
    z-index:1000000;
    width:310px;
    max-width:
        calc(100vw - 20px);
    padding:12px;
    border-radius:12px;
}
.custom-picker-panel-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    margin-bottom:10px;
    padding-bottom:9px;
    border-bottom:
        1px solid
        #e2e8f0;
}
.custom-picker-panel-title{
    flex:1;
    color:#334155;
    font-size:12px;
    font-weight:800;
    text-align:center;
}
.custom-picker-panel-close{
    width:28px;
    height:28px;
    border-radius:7px;
    font-size:17px;
}
.custom-picker-month-grid,
.custom-picker-year-grid{
    display:grid;
    gap:7px;
}
.custom-picker-month-grid{
    grid-template-columns:
        repeat(
            3,
            1fr
        );
}
.custom-picker-year-grid{
    grid-template-columns:
        repeat(
            4,
            1fr
        );
}
.custom-picker-option{
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:40px;
    border:1px solid #dbe3ed;
    border-radius:9px;
    background:#fff;
    color:#475569;
    font-family:inherit;
    font-size:10px;
    font-weight:700;
    cursor:pointer;
}
.custom-picker-option:hover{
    border-color:#93c5fd;
    background:#eff6ff;
    color:#2563eb;
}
.custom-picker-option.active{
    border-color:#2563eb;
    background:#2563eb;
    color:#fff;
}
.custom-picker-option.current{
    box-shadow:
        inset 0 0 0 1px
        #93c5fd;
}
.custom-picker-option.active.current{
    box-shadow:none;
}
.custom-picker-year-navigation{
    display:flex;
    align-items:center;
    gap:4px;
}
.custom-picker-year-nav{
    display:flex;
    align-items:center;
    justify-content:center;
    width:29px;
    height:29px;
    border:0;
    border-radius:7px;
    background:#f8fafc;
    color:#64748b;
    cursor:pointer;
}
.custom-picker-year-nav:hover{
    background:#eff6ff;
    color:#2563eb;
}
/* ==========================================================================
   TABLE
   ========================================================================== */
.archive-table-wrapper{
    overflow:hidden;
    border:1px solid #dbe4f0;
    border-radius:12px;
    background:#fff;
    box-shadow:
        0 2px 10px
        rgba(15,23,42,.035);
}
.archive-table-scroll{
    overflow-x:auto;
}
.archive-table{
    width:100%;
    min-width:1000px;
    border-collapse:collapse;
    border-spacing:0;
    background:#fff;
}
.archive-table thead{
    background:#f8fafc;
}
.archive-table thead tr{
    border-bottom:
        1px solid
        #e2e8f0;
}
.archive-table thead th{
    padding:
        14px
        16px;
    border-bottom:
        1px solid
        #e2e8f0;
    color:#64748b;
    font-size:9px;
    font-weight:800;
    letter-spacing:.04em;
    line-height:1.3;
    text-align:left;
    text-transform:uppercase;
    white-space:nowrap;
}
.archive-table thead th:first-child{
    padding-left:22px;
}
.archive-table thead th:last-child{
    text-align:center;
}
.archive-table tbody tr{
    background:#fff;
    transition:
        background-color .15s ease;
}
.archive-table tbody tr:hover{
    background:#f8fbff;
}
.archive-table tbody td{
    padding:
        14px
        16px;
    border-bottom:
        1px solid
        #edf2f7;
    color:#475569;
    font-size:11px;
    line-height:1.4;
    vertical-align:middle;
}
.archive-table tbody td:first-child{
    padding-left:22px;
}
.archive-table tbody tr:last-child td{
    border-bottom:0;
}
.archive-table .cell-date{
    color:#334155;
    font-weight:700;
    white-space:nowrap;
}
.archive-table .cell-sender{
    color:#334155;
}
.archive-table .cell-subject{
    color:#1e293b;
    font-weight:700;
}
.archive-table .cell-category{
    color:#64748b;
}
.archive-table .sender-name{
    display:block;
    max-width:220px;
    overflow:hidden;
    color:#334155;
    font-weight:700;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.archive-table .sender-email{
    display:block;
    max-width:220px;
    margin-top:2px;
    overflow:hidden;
    color:#94a3b8;
    font-size:9px;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.archive-table .subject-number{
    display:block;
    margin-top:3px;
    color:#94a3b8;
    font-size:9px;
    font-weight:500;
}
/* ==========================================================================
   CATEGORY BADGE
   ========================================================================== */
.category-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    min-height:28px;
    padding:
        0
        10px;
    border-radius:999px;
    background:#eff6ff;
    color:#2563eb;
    font-size:9px;
    font-weight:700;
    white-space:nowrap;
}
.category-badge.education{
    background:#dcf8ee;
    color:#059669;
}
.category-badge.invitation{
    background:#f0e9ff;
    color:#7c3aed;
}
.category-badge.report{
    background:#fff0d7;
    color:#d97706;
}
/* ==========================================================================
   STATUS BADGE
   ========================================================================== */
.status-badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    min-height:28px;
    padding:
        0
        10px;
    border-radius:999px;
    font-size:9px;
    font-weight:800;
    white-space:nowrap;
}
.status-dot{
    width:7px;
    height:7px;
    border-radius:999px;
    background:
        currentColor;
}
.status-baru{
    background:#eaf2ff;
    color:#2563eb;
}
.status-diproses{
    background:#fff4dc;
    color:#d97706;
}
.status-didisposisikan{
    background:#f0e9ff;
    color:#7c3aed;
}
.status-selesai{
    background:#dcf8ee;
    color:#059669;
}
.status-diarsipkan{
    background:#f1f5f9;
    color:#64748b;
}
/* ==========================================================================
   ACTION
   ========================================================================== */
.action-cell{
    width:130px;
    text-align:center!important;
    white-space:nowrap;
}
.action-buttons{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:5px;
}
.action-button{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:38px;
    height:38px;
    border:1px solid #e2e8f0;
    border-radius:9px;
    background:#fff;
    color:#64748b;
    transition:
        background .15s ease,
        color .15s ease,
        border-color .15s ease;
}
.action-button:hover{
    border-color:#bfdbfe;
    background:#eff6ff;
    color:#2563eb;
}
.action-button.edit:hover{
    border-color:#fde68a;
    background:#fffbeb;
    color:#d97706;
}
.action-button.delete:hover{
    border-color:#fecdd3;
    background:#fff1f2;
    color:#e11d48;
}
/* ==========================================================================
   EMPTY
   ========================================================================== */
.archive-table-empty{
    padding:
        55px
        20px!important;
    text-align:center;
}
.archive-empty-icon{
    display:flex;
    align-items:center;
    justify-content:center;
    width:56px;
    height:56px;
    margin:
        0
        auto
        12px;
    border-radius:16px;
    background:#f1f5f9;
    color:#94a3b8;
}
/* ==========================================================================
   PAGINATION
   ========================================================================== */
.archive-pagination{
    padding:
        12px
        20px;
    border-top:
        1px solid
        #e2e8f0;
}
/* ==========================================================================
   RESPONSIVE
   ========================================================================== */
@media(max-width:1100px){
    .surat-stat-grid{
        grid-template-columns:
            repeat(
                2,
                minmax(0,1fr)
            );
    }
    .surat-filter-main{
        grid-template-columns:
            minmax(0,1fr)
            auto;
    }
    .surat-date-wrapper{
        grid-column:1/-1;
    }
}
@media(max-width:767px){
    .surat-page-header{
        align-items:flex-start;
        flex-direction:column;
    }
    .surat-page-header-left{
        width:100%;
    }
    .surat-header-actions{
        width:100%;
        gap:6px;
        flex-wrap:nowrap;
    }
    .surat-header-actions .surat-export-button,
    .surat-header-actions .surat-create-button{
        flex:1 1 0;
        width:auto;
        min-width:0;
        padding:0 9px;
        font-size:10px;
    }
    .surat-page-title{
        font-size:24px;
    }
    .surat-page-description{
        font-size:12px;
    }
    .surat-stat-grid{
        grid-template-columns:
            1fr
            1fr;
        gap:10px;
    }
    .surat-stat-card{
        min-height:105px;
        padding:13px;
        gap:10px;
    }
    .surat-stat-icon{
        width:42px;
        height:42px;
        flex-basis:42px;
    }
    .surat-stat-value{
        font-size:22px;
    }
    .surat-stat-label{
        font-size:10px;
    }
    .surat-stat-description{
        font-size:9px;
    }
    .surat-filter-card{
        padding:12px;
    }
    .surat-filter-main{
        grid-template-columns:1fr;
    }
    .surat-filter-submit{
        width:100%;
    }
    .surat-filter-secondary{
        grid-template-columns:1fr;
    }
    .surat-filter-menu{
        position:fixed;
        top:50%;
        right:auto;
        left:50%;
        width:
            calc(100vw - 24px);
        max-width:430px;
        transform:
            translate(
                -50%,
                -50%
            );
    }
    .surat-filter-options{
        max-height:55vh;
    }
    .custom-date-picker{
        top:50%;
        left:50%;
        width:
            calc(100vw - 16px);
        max-height:
            calc(100vh - 16px);
        overflow-y:auto;
        transform:
            translate(
                -50%,
                -50%
            );
    }
    .custom-date-picker-calendars{
        grid-template-columns:1fr;
    }
    .custom-calendar+.custom-calendar{
        border-top:
            1px solid
            #e2e8f0;
        border-left:0;
    }
    .custom-calendar-day{
        height:38px;
    }
    .custom-picker-panel{
        top:50%!important;
        left:50%!important;
        width:
            calc(100vw - 24px);
        transform:
            translate(
                -50%,
                -50%
            );
    }
    .custom-date-picker-footer{
        position:sticky;
        bottom:0;
        background:#fff;
    }
    body.date-picker-lock{
        overflow:hidden;
    }
}
@media(max-width:480px){
    .surat-stat-grid{
        grid-template-columns:1fr;
    }
    .surat-filter-options{
        grid-template-columns:1fr;
    }
    .surat-page-header-left{
        gap:11px;
    }
    .surat-page-icon{
        width:48px;
        height:48px;
        flex-basis:48px;
    }
}
</style>
@endpush
<div class="surat-page space-y-4">
    {{-- =====================================================================
         HEADER
    ====================================================================== --}}
    <div class="surat-page-header">
        <div class="surat-page-header-left">
            <div class="surat-page-icon">
                <svg
                    class="h-7 w-7"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 8l9 6 9-6"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                    />
                </svg>
            </div>
            <div class="min-w-0">
                <h1 class="surat-page-title">
                    Surat Masuk
                </h1>
                <p class="surat-page-description">
                    Kelola dan pantau seluruh arsip surat masuk organisasi Anda.
                </p>
            </div>
        </div>
        <div class="surat-header-actions">
            @if($hasVisibleData)
                <a
                    href="{{ route('export.surat-masuk.excel', $exportQuery) }}"
                    class="surat-export-button excel"
                    title="Export Excel"
                    aria-label="Export Excel"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 4h16v16H4zM8 8l8 8m0-8l-8 8"
                        />
                    </svg>
                    Excel
                </a>
                <a
                    href="{{ route('export.surat-masuk.pdf', $exportQuery) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="surat-export-button pdf"
                    title="Export PDF"
                    aria-label="Export PDF"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M7 3h7l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2zm7 0v5h5"
                        />
                    </svg>
                    PDF
                </a>
            @endif
            @if($canManage)
                <a
                    href="{{ route('surat-masuk.create') }}"
                    class="surat-create-button"
                    title="Tambah Surat Masuk"
                    aria-label="Tambah Surat Masuk"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 4v16m8-8H4"
                        />
                    </svg>
                    Surat Masuk
                </a>
            @endif
        </div>
    </div>
    {{-- =====================================================================
         SCORECARD
    ====================================================================== --}}
    <div class="surat-stat-grid">
        {{-- TOTAL --}}
        <div class="surat-stat-card">
            <div class="surat-stat-icon blue">
                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 8h6M9 12h6M9 16h4"
                    />
                </svg>
            </div>
            <div class="surat-stat-content">
                <div class="surat-stat-label">
                    Total Surat Masuk
                </div>
                <div class="surat-stat-value">
                    {{ number_format($totalSuratMasuk) }}
                </div>
                <div class="surat-stat-description">
                    Semua surat masuk
                </div>
            </div>
        </div>
        {{-- BARU --}}
        <div class="surat-stat-card">
            <div class="surat-stat-icon red">
                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 8l9 6 9-6"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                    />
                </svg>
            </div>
            <div class="surat-stat-content">
                <div class="surat-stat-label">
                    Surat Baru
                </div>
                <div class="surat-stat-value red">
                    {{ number_format($suratBaru) }}
                </div>
                <div class="surat-stat-description">
                    Perlu dicek
                </div>
            </div>
        </div>
        {{-- DIPROSES --}}
        <div class="surat-stat-card">
            <div class="surat-stat-icon amber">
                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                        stroke-width="2"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 7v5l3 2"
                    />
                </svg>
            </div>
            <div class="surat-stat-content">
                <div class="surat-stat-label">
                    Diproses
                </div>
                <div class="surat-stat-value">
                    {{ number_format($suratDiproses) }}
                </div>
                <div class="surat-stat-description">
                    Sedang proses
                </div>
            </div>
        </div>
        {{-- SELESAI --}}
        <div class="surat-stat-card">
            <div class="surat-stat-icon green">
                <svg
                    class="h-6 w-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                        stroke-width="2"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M8 12l2.5 2.5L16 9"
                    />
                </svg>
            </div>
            <div class="surat-stat-content">
                <div class="surat-stat-label">
                    Selesai
                </div>
                <div class="surat-stat-value">
                    {{ number_format($suratSelesai) }}
                </div>
                <div class="surat-stat-description">
                    Telah ditindaklanjuti
                </div>
            </div>
        </div>
    </div>
    {{-- =====================================================================
         FILTER
    ====================================================================== --}}
    <div class="surat-filter-card">
        <form
            id="filterForm"
            method="GET"
            action="{{ route('surat-masuk.index') }}"
        >
            <div class="surat-filter-main">
                {{-- SEARCH --}}
                <div class="surat-search-wrapper">
                    <div class="surat-search-icon">
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0a7 7 0 0114 0z"
                            />
                        </svg>
                    </div>
                    <input
                        type="search"
                        id="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari perihal, nomor surat, atau pengirim..."
                        autocomplete="off"
                        class="surat-search-input"
                    >
                </div>
                {{-- FILTER --}}
                <button
                    type="submit"
                    class="surat-filter-submit {{ $hasFilters ? 'has-filter' : '' }}"
                    title="Terapkan filter"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707v3.414L9 14V9.707a1 1 0 00-.293-.707L3.293 6.293A1 1 0 013 5.586V4z"
                        />
                    </svg>
                    Filter
                </button>
                {{-- RENTANG TANGGAL --}}
                <div class="surat-date-wrapper">
                    <div class="relative">
                        <div class="surat-date-left-icon">
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5v12a2 2 0 002 2z"
                                />
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="date-range"
                            value="{{ $visibleDateRange }}"
                            readonly
                            autocomplete="off"
                            placeholder="Rentang tanggal"
                            class="surat-date-input"
                            aria-label="Pilih rentang tanggal"
                        >
                        <button
                            type="button"
                            id="clearDateRange"
                            class="surat-date-clear"
                            title="Hapus rentang tanggal"
                            aria-label="Hapus rentang tanggal"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"
                                />
                            </svg>
                        </button>
                    </div>
                    <input
                        type="hidden"
                        name="dari_tanggal"
                        id="dari_tanggal"
                        value="{{ $dariTanggal }}"
                    >
                    <input
                        type="hidden"
                        name="sampai_tanggal"
                        id="sampai_tanggal"
                        value="{{ $sampaiTanggal }}"
                    >
                </div>
            </div>
            {{-- KATEGORI + STATUS --}}
            <div class="surat-filter-secondary">
                {{-- KATEGORI --}}
                <div
                    class="surat-filter-dropdown"
                    data-filter-dropdown="kategori"
                >
                    <button
                        type="button"
                        class="surat-filter-trigger"
                        data-dropdown-trigger
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <span class="surat-filter-icon category">
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h10"
                                />
                            </svg>
                        </span>
                        <span class="surat-filter-trigger-content">
                            <span class="surat-filter-trigger-title">
                                Kategori Surat
                            </span>
                            <span class="surat-filter-trigger-subtitle">
                                Pilih satu atau beberapa kategori
                            </span>
                        </span>
                        <span
                            class="surat-filter-count category"
                            data-filter-count
                        >
                            {{ count($selectedKategori) }} dipilih
                        </span>
                        <span class="surat-filter-chevron">
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 9l6 6l6-6"
                                />
                            </svg>
                        </span>
                    </button>
                    <div
                        class="surat-filter-menu hidden"
                        data-dropdown-menu
                    >
                        <div class="surat-filter-menu-header">
                            <div>
                                <div class="surat-filter-menu-title">
                                    Pilih Kategori
                                </div>
                                <div class="surat-filter-menu-description">
                                    Checkbox dapat dipilih lebih dari satu
                                </div>
                            </div>
                            <div class="surat-filter-menu-actions">
                                <button
                                    type="button"
                                    class="surat-filter-action select"
                                    data-action="select-all"
                                >
                                    Pilih Semua
                                </button>
                                <button
                                    type="button"
                                    class="surat-filter-action clear"
                                    data-action="clear-all"
                                >
                                    Batalkan
                                </button>
                            </div>
                        </div>
                        <div class="surat-filter-options">
                            @forelse(
                                ($kategoris ?? collect())
                                as $kategori
                            )
                                <label
                                    class="surat-filter-option"
                                >
                                    <input
                                        type="checkbox"
                                        name="kategori_id[]"
                                        value="{{ $kategori->id }}"
                                        class="kategori-checkbox"
                                        @checked(
                                            in_array(
                                                (string) $kategori->id,
                                                $selectedKategori,
                                                true
                                            )
                                        )
                                    >
                                    <span
                                        class="surat-filter-option-text"
                                        title="{{ $kategori->nama_kategori }}"
                                    >
                                        {{ $kategori->nama_kategori }}
                                    </span>
                                </label>
                            @empty
                                <div class="col-span-full px-3 py-6 text-center text-xs text-slate-400">
                                    Belum ada kategori surat.
                                </div>
                            @endforelse
                        </div>
                        <div class="surat-filter-menu-footer">
                            <span
                                class="surat-filter-footer-count"
                                data-footer-count
                            >
                                {{ count($selectedKategori) }}
                                kategori dipilih
                            </span>
                            <span class="surat-filter-footer-hint">
                                Klik Filter untuk menerapkan
                            </span>
                        </div>
                    </div>
                </div>
                {{-- STATUS --}}
                <div
                    class="surat-filter-dropdown"
                    data-filter-dropdown="status"
                >
                    <button
                        type="button"
                        class="surat-filter-trigger"
                        data-dropdown-trigger
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <span class="surat-filter-icon status">
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z"
                                />
                            </svg>
                        </span>
                        <span class="surat-filter-trigger-content">
                            <span class="surat-filter-trigger-title">
                                Status Surat
                            </span>
                            <span class="surat-filter-trigger-subtitle">
                                Pilih satu atau beberapa status
                            </span>
                        </span>
                        <span
                            class="surat-filter-count status"
                            data-filter-count
                        >
                            {{ count($selectedStatus) }} dipilih
                        </span>
                        <span class="surat-filter-chevron">
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 9l6 6l6-6"
                                />
                            </svg>
                        </span>
                    </button>
                    <div
                        class="surat-filter-menu hidden"
                        data-dropdown-menu
                    >
                        <div class="surat-filter-menu-header">
                            <div>
                                <div class="surat-filter-menu-title">
                                    Pilih Status
                                </div>
                                <div class="surat-filter-menu-description">
                                    Checkbox dapat dipilih lebih dari satu
                                </div>
                            </div>
                            <div class="surat-filter-menu-actions">
                                <button
                                    type="button"
                                    class="surat-filter-action select"
                                    data-action="select-all"
                                >
                                    Pilih Semua
                                </button>
                                <button
                                    type="button"
                                    class="surat-filter-action clear"
                                    data-action="clear-all"
                                >
                                    Batalkan
                                </button>
                            </div>
                        </div>
                        <div class="surat-filter-options">
                            @foreach(
                                $statusOptions
                                as $value => $label
                            )
                                <label
                                    class="surat-filter-option"
                                >
                                    <input
                                        type="checkbox"
                                        name="status[]"
                                        value="{{ $value }}"
                                        class="status-checkbox"
                                        @checked(
                                            in_array(
                                                $value,
                                                $selectedStatus,
                                                true
                                            )
                                        )
                                    >
                                    <span
                                        class="surat-filter-option-text"
                                    >
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <div class="surat-filter-menu-footer">
                            <span
                                class="surat-filter-footer-count"
                                data-footer-count
                            >
                                {{ count($selectedStatus) }}
                                status dipilih
                            </span>
                            <span class="surat-filter-footer-hint">
                                Klik Filter untuk menerapkan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    {{-- =====================================================================
         TABLE
    ====================================================================== --}}
    <div class="archive-table-wrapper">
        <div class="archive-table-scroll">
            <table class="archive-table">
                <thead>
                    <tr>
                        <th>
                            Tanggal
                        </th>
                        <th>
                            Pengirim
                        </th>
                        <th>
                            Perihal
                        </th>
                        <th>
                            Kategori
                        </th>
                        <th>
                            Status
                        </th>
                        <th class="action-cell">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(
                        ($suratMasuks ?? collect())
                        as $surat
                    )
                        @php
                            /*
                            |--------------------------------------------------------------------------
                            | STATUS
                            |--------------------------------------------------------------------------
                            */
                            $status =
                                strtolower(
                                    trim(
                                        (string) (
                                            $surat->status ??
                                            'baru'
                                        )
                                    )
                                );
                            $statusLabel =
                                $statusOptions[
                                    $status
                                ]
                                ??
                                ucfirst(
                                    $status
                                );
                            /*
                            |--------------------------------------------------------------------------
                            | TANGGAL
                            |--------------------------------------------------------------------------
                            */
                            $tanggalTerima =
                                '-';
                            if (
                                $surat->tanggal_terima
                            ) {
                                try {
                                    $tanggalTerima =
                                        Carbon::parse(
                                            $surat->tanggal_terima
                                        )->format(
                                            'd M Y'
                                        );
                                } catch (
                                    \Throwable $e
                                ) {
                                    $tanggalTerima =
                                        '-';
                                }
                            }
                            /*
                            |--------------------------------------------------------------------------
                            | KATEGORI STYLE
                            |--------------------------------------------------------------------------
                            */
                            $kategoriNama =
                                strtolower(
                                    (string) (
                                        $surat
                                            ->kategori
                                            ?->nama_kategori
                                        ?? ''
                                    )
                                );
                            $categoryClass =
                                '';
                            if (
                                str_contains(
                                    $kategoriNama,
                                    'pendidikan'
                                )
                            ) {
                                $categoryClass =
                                    'education';
                            } elseif (
                                str_contains(
                                    $kategoriNama,
                                    'undangan'
                                )
                            ) {
                                $categoryClass =
                                    'invitation';
                            } elseif (
                                str_contains(
                                    $kategoriNama,
                                    'laporan'
                                )
                            ) {
                                $categoryClass =
                                    'report';
                            }
                        @endphp
                        <tr>
                            {{-- TANGGAL --}}
                            <td class="cell-date">
                                <div>
                                    {{ $tanggalTerima }}
                                </div>
                                @if(
                                    $surat->created_at
                                )
                                    <div
                                        class="mt-1 text-[9px] font-normal text-slate-400"
                                    >
                                        {{
                                            Carbon::parse(
                                                $surat->created_at
                                            )->format('H:i')
                                        }}
                                    </div>
                                @endif
                            </td>
                            {{-- PENGIRIM --}}
                            <td class="cell-sender">
                                <span
                                    class="sender-name"
                                    title="{{ $surat->pengirim ?? '-' }}"
                                >
                                    {{ $surat->pengirim ?? '-' }}
                                </span>
                                @if(
                                    !empty(
                                        $surat
                                            ->email_pengirim
                                    )
                                )
                                    <span
                                        class="sender-email"
                                        title="{{ $surat->email_pengirim }}"
                                    >
                                        {{ $surat->email_pengirim }}
                                    </span>
                                @endif
                            </td>
                            {{-- PERIHAL --}}
                            <td>
                                <div
                                    class="cell-subject max-w-[280px] truncate"
                                    title="{{ $surat->perihal ?? '-' }}"
                                >
                                    {{ $surat->perihal ?? '-' }}
                                </div>
                                @if(
                                    !empty(
                                        $surat
                                            ->nomor_surat
                                    )
                                )
                                    <div
                                        class="subject-number"
                                        title="{{ $surat->nomor_surat }}"
                                    >
                                        No.
                                        {{ $surat->nomor_surat }}
                                    </div>
                                @endif
                            </td>
                            {{-- KATEGORI --}}
                            <td class="cell-category">
                                <span
                                    class="category-badge {{ $categoryClass }}"
                                >
                                    <svg
                                        class="h-3.5 w-3.5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 7h10M7 12h10M7 17h6"
                                        />
                                    </svg>
                                    {{
                                        $surat
                                            ->kategori
                                            ?->nama_kategori
                                        ?? '-'
                                    }}
                                </span>
                            </td>
                            {{-- STATUS --}}
                            <td>
                                <span
                                    class="
                                        status-badge
                                        status-{{ $status }}
                                    "
                                >
                                    <span
                                        class="status-dot"
                                    ></span>
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            {{-- AKSI --}}
                            <td class="action-cell">
                                <div class="action-buttons">
                                    {{-- DETAIL --}}
                                    <a
                                        href="{{
                                            route(
                                                'surat-masuk.show',
                                                $surat
                                            )
                                        }}"
                                        class="action-button"
                                        title="Lihat Detail & Disposisi"
                                        aria-label="Lihat detail surat"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0a3 3 0 006 0z"
                                            />
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7z"
                                            />
                                        </svg>
                                    </a>
                                    @if($canManage)
                                        {{-- EDIT --}}
                                        <a
                                            href="{{
                                                route(
                                                    'surat-masuk.edit',
                                                    $surat
                                                )
                                            }}"
                                            class="action-button edit"
                                            title="Ubah Data"
                                            aria-label="Ubah data surat"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M18.5 2.5a2.121 2.121 0 013 3L11.828 15H9v-2.828L18.5 2.5z"
                                                />
                                            </svg>
                                        </a>
                                        {{-- DELETE --}}
                                        <form
                                            action="{{
                                                route(
                                                    'surat-masuk.destroy',
                                                    $surat
                                                )
                                            }}"
                                            method="POST"
                                            class="delete-form inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                class="action-button delete delete-btn"
                                                title="Hapus Surat"
                                                aria-label="Hapus surat"
                                            >
                                                <svg
                                                    class="h-4 w-4"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                    aria-hidden="true"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M10 11v6m4-6v6"
                                                    />
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M4 7h16m-5-3H9a1 1 0 00-1 1v2h8V5a1 1 0 00-1-1z"
                                                    />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="archive-table-empty"
                            >
                                <div>
                                    <div
                                        class="archive-empty-icon"
                                    >
                                        <svg
                                            class="h-7 w-7"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-1.414 0l-2.414-2.414A1 1 0 006.586 13H4"
                                            />
                                        </svg>
                                    </div>
                                    <p
                                        class="text-sm font-bold text-slate-700 sm:text-base"
                                    >
                                        Belum ada data surat masuk
                                    </p>
                                    <p
                                        class="mx-auto mt-1 max-w-md text-xs text-slate-400"
                                    >
                                        @if($hasFilters)
                                            Tidak ada surat yang sesuai dengan filter yang digunakan.
                                        @elseif(
                                            $userRole === 'staff'
                                        )
                                            Belum ada surat masuk yang didisposisikan kepada Anda.
                                        @else
                                            Belum ada data surat masuk yang tersimpan.
                                        @endif
                                    </p>
                                    @if($hasFilters)
                                        <a
                                            href="{{
                                                route(
                                                    'surat-masuk.index'
                                                )
                                            }}"
                                            class="mt-4 inline-flex items-center rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition hover:bg-slate-800"
                                        >
                                            Reset Filter
                                        </a>
                                    @elseif($canManage)
                                        <a
                                            href="{{
                                                route(
                                                    'surat-masuk.create'
                                                )
                                            }}"
                                            class="mt-4 inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-blue-700"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 4v16m8-8H4"
                                                />
                                            </svg>
                                            Tambah Surat Masuk
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- PAGINATION --}}
        @if(
            isset($suratMasuks) &&
            method_exists(
                $suratMasuks,
                'hasPages'
            ) &&
            $suratMasuks->hasPages()
        )
            <div class="archive-pagination">
                {{
                    $suratMasuks
                        ->withQueryString()
                        ->links()
                }}
            </div>
        @endif
    </div>
</div>
{{-- ==========================================================================
     DATE PICKER
     ========================================================================== --}}
<div
    id="customDatePicker"
    class="custom-date-picker hidden"
>
    <div class="custom-date-picker-header">
        <div class="custom-date-picker-title">
            Pilih Rentang Tanggal
        </div>
        <button
            type="button"
            id="datePickerClose"
            class="custom-date-picker-close"
            aria-label="Tutup"
        >
            &times;
        </button>
    </div>
    <div
        id="customDateCalendars"
        class="custom-date-picker-calendars"
    ></div>
    <div class="custom-date-picker-footer">
        <div
            id="datePickerSelected"
            class="custom-date-picker-selected"
        >
            Pilih tanggal awal
        </div>
        <div class="custom-date-picker-actions">
            <button
                type="button"
                id="datePickerClear"
                class="custom-date-picker-button"
            >
                Bersihkan
            </button>
            <button
                type="button"
                id="datePickerApply"
                class="custom-date-picker-button apply"
            >
                Terapkan
            </button>
        </div>
    </div>
</div>
<div
    id="customPickerPanel"
    class="custom-picker-panel hidden"
></div>
@push('scripts')
<script>
(function(){
    'use strict';
    /*
    |--------------------------------------------------------------------------
    | CONFIG
    |--------------------------------------------------------------------------
    */
    const MONTHS = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    const WEEKDAYS = [
        'Sn',
        'Sl',
        'Rb',
        'Km',
        'Jm',
        'Sb',
        'Mg'
    ];
    const MIN_YEAR = 2000;
    const MAX_YEAR =
        new Date()
            .getFullYear() +
        20;
    /*
    |--------------------------------------------------------------------------
    | ELEMENT
    |--------------------------------------------------------------------------
    */
    const dateInput =
        document.getElementById(
            'date-range'
        );
    const dariInput =
        document.getElementById(
            'dari_tanggal'
        );
    const sampaiInput =
        document.getElementById(
            'sampai_tanggal'
        );
    const clearButton =
        document.getElementById(
            'clearDateRange'
        );
    const picker =
        document.getElementById(
            'customDatePicker'
        );
    const calendars =
        document.getElementById(
            'customDateCalendars'
        );
    const panel =
        document.getElementById(
            'customPickerPanel'
        );
    const closePickerButton =
        document.getElementById(
            'datePickerClose'
        );
    const clearPickerButton =
        document.getElementById(
            'datePickerClear'
        );
    const applyPickerButton =
        document.getElementById(
            'datePickerApply'
        );
    const selectedLabel =
        document.getElementById(
            'datePickerSelected'
        );
    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */
    let selectedStart =
        parseDate(
            dariInput?.value || ''
        );
    let selectedEnd =
        parseDate(
            sampaiInput?.value || ''
        );
    let tempStart =
        selectedStart
            ? cloneDate(
                selectedStart
            )
            : null;
    let tempEnd =
        selectedEnd
            ? cloneDate(
                selectedEnd
            )
            : null;
    let viewLeft =
        selectedStart
            ? new Date(
                selectedStart
                    .getFullYear(),
                selectedStart
                    .getMonth(),
                1
            )
            : new Date();
    let panelSide =
        'left';
    viewLeft.setDate(1);
    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */
    function cloneDate(date){
        return new Date(
            date.getFullYear(),
            date.getMonth(),
            date.getDate()
        );
    }
    function parseDate(value){
        if(!value){
            return null;
        }
        const match =
            String(value).match(
                /^(\d{4})-(\d{2})-(\d{2})$/
            );
        if(!match){
            return null;
        }
        const year =
            Number(match[1]);
        const month =
            Number(match[2]) - 1;
        const day =
            Number(match[3]);
        const date =
            new Date(
                year,
                month,
                day
            );
        if(
            date.getFullYear() !== year ||
            date.getMonth() !== month ||
            date.getDate() !== day
        ){
            return null;
        }
        return date;
    }
    function pad(value){
        return String(value)
            .padStart(
                2,
                '0'
            );
    }
    function toISO(date){
        if(!date){
            return '';
        }
        return [
            date.getFullYear(),
            pad(
                date.getMonth() + 1
            ),
            pad(
                date.getDate()
            )
        ].join('-');
    }
    function formatDate(date){
        if(!date){
            return '';
        }
        return [
            pad(
                date.getDate()
            ),
            pad(
                date.getMonth() + 1
            ),
            date.getFullYear()
        ].join('/');
    }
    function sameDate(
        a,
        b
    ){
        return !!(
            a &&
            b &&
            a.getFullYear() ===
                b.getFullYear() &&
            a.getMonth() ===
                b.getMonth() &&
            a.getDate() ===
                b.getDate()
        );
    }
    function addMonths(
        date,
        amount
    ){
        return new Date(
            date.getFullYear(),
            date.getMonth() +
                amount,
            1
        );
    }
    function isBetween(
        date,
        start,
        end
    ){
        if(
            !date ||
            !start ||
            !end
        ){
            return false;
        }
        const value =
            toISO(date);
        return (
            value >
                toISO(start) &&
            value <
                toISO(end)
        );
    }
    /*
    |--------------------------------------------------------------------------
    | RENDER CALENDAR
    |--------------------------------------------------------------------------
    */
    function renderCalendars(){
        if(!calendars){
            return;
        }
        calendars.innerHTML =
            '';
        const leftDate =
            new Date(
                viewLeft.getFullYear(),
                viewLeft.getMonth(),
                1
            );
        const rightDate =
            addMonths(
                leftDate,
                1
            );
        calendars.appendChild(
            createCalendar(
                leftDate,
                'left'
            )
        );
        calendars.appendChild(
            createCalendar(
                rightDate,
                'right'
            )
        );
        updateSelectedLabel();
        requestAnimationFrame(
            positionPicker
        );
    }
    function createCalendar(
        date,
        side
    ){
        const calendar =
            document.createElement(
                'div'
            );
        calendar.className =
            'custom-calendar';
        const header =
            document.createElement(
                'div'
            );
        header.className =
            'custom-calendar-head';
        const previous =
            document.createElement(
                'button'
            );
        previous.type =
            'button';
        previous.className =
            'custom-calendar-nav';
        previous.innerHTML =
            '&#8249;';
        previous.setAttribute(
            'aria-label',
            'Bulan sebelumnya'
        );
        const next =
            document.createElement(
                'button'
            );
        next.type =
            'button';
        next.className =
            'custom-calendar-nav';
        next.innerHTML =
            '&#8250;';
        next.setAttribute(
            'aria-label',
            'Bulan berikutnya'
        );
        const monthButtons =
            document.createElement(
                'div'
            );
        monthButtons.className =
            'custom-calendar-month-buttons';
        const monthButton =
            document.createElement(
                'button'
            );
        monthButton.type =
            'button';
        monthButton.className =
            'custom-calendar-month';
        monthButton.textContent =
            MONTHS[
                date.getMonth()
            ];
        const yearButton =
            document.createElement(
                'button'
            );
        yearButton.type =
            'button';
        yearButton.className =
            'custom-calendar-year';
        yearButton.textContent =
            String(
                date.getFullYear()
            );
        monthButtons.append(
            monthButton,
            yearButton
        );
        header.append(
            previous,
            monthButtons,
            next
        );
        monthButton.addEventListener(
            'click',
            function(event){
                event.preventDefault();
                event.stopPropagation();
                showMonthPanel(
                    date,
                    monthButton,
                    side
                );
            }
        );
        yearButton.addEventListener(
            'click',
            function(event){
                event.preventDefault();
                event.stopPropagation();
                showYearPanel(
                    date,
                    yearButton,
                    side
                );
            }
        );
        previous.addEventListener(
            'click',
            function(event){
                event.preventDefault();
                event.stopPropagation();
                viewLeft =
                    addMonths(
                        viewLeft,
                        -1
                    );
                renderCalendars();
            }
        );
        next.addEventListener(
            'click',
            function(event){
                event.preventDefault();
                event.stopPropagation();
                viewLeft =
                    addMonths(
                        viewLeft,
                        1
                    );
                renderCalendars();
            }
        );
        calendar.appendChild(
            header
        );
        const weekdays =
            document.createElement(
                'div'
            );
        weekdays.className =
            'custom-calendar-weekdays';
        WEEKDAYS.forEach(
            function(day){
                const element =
                    document.createElement(
                        'div'
                    );
                element.className =
                    'custom-calendar-weekday';
                element.textContent =
                    day;
                weekdays.appendChild(
                    element
                );
            }
        );
        calendar.appendChild(
            weekdays
        );
        const days =
            document.createElement(
                'div'
            );
        days.className =
            'custom-calendar-days';
        const year =
            date.getFullYear();
        const month =
            date.getMonth();
        const firstDay =
            new Date(
                year,
                month,
                1
            ).getDay();
        const mondayOffset =
            firstDay === 0
                ? 6
                : firstDay - 1;
        const daysInMonth =
            new Date(
                year,
                month + 1,
                0
            ).getDate();
        const daysInPreviousMonth =
            new Date(
                year,
                month,
                0
            ).getDate();
        for(
            let index = 0;
            index < 42;
            index++
        ){
            let dayNumber;
            let cellDate;
            let otherMonth =
                false;
            if(
                index <
                mondayOffset
            ){
                dayNumber =
                    daysInPreviousMonth -
                    mondayOffset +
                    index +
                    1;
                cellDate =
                    new Date(
                        year,
                        month - 1,
                        dayNumber
                    );
                otherMonth =
                    true;
            } else if(
                index >=
                mondayOffset +
                daysInMonth
            ){
                dayNumber =
                    index -
                    mondayOffset -
                    daysInMonth +
                    1;
                cellDate =
                    new Date(
                        year,
                        month + 1,
                        dayNumber
                    );
                otherMonth =
                    true;
            } else {
                dayNumber =
                    index -
                    mondayOffset +
                    1;
                cellDate =
                    new Date(
                        year,
                        month,
                        dayNumber
                    );
            }
            const button =
                document.createElement(
                    'button'
                );
            button.type =
                'button';
            button.className =
                'custom-calendar-day';
            button.textContent =
                String(
                    dayNumber
                );
            if(
                otherMonth
            ){
                button.classList.add(
                    'other-month'
                );
            }
            if(
                sameDate(
                    cellDate,
                    new Date()
                )
            ){
                button.classList.add(
                    'today'
                );
            }
            if(
                tempStart &&
                tempEnd &&
                isBetween(
                    cellDate,
                    tempStart,
                    tempEnd
                )
            ){
                button.classList.add(
                    'in-range'
                );
            }
            if(
                tempStart &&
                sameDate(
                    cellDate,
                    tempStart
                )
            ){
                button.classList.add(
                    'range-start'
                );
            }
            if(
                tempEnd &&
                sameDate(
                    cellDate,
                    tempEnd
                )
            ){
                button.classList.add(
                    'range-end'
                );
            }
            button.addEventListener(
                'click',
                function(event){
                    event.preventDefault();
                    event.stopPropagation();
                    selectDate(
                        cellDate
                    );
                }
            );
            days.appendChild(
                button
            );
        }
        calendar.appendChild(
            days
        );
        return calendar;
    }
    /*
    |--------------------------------------------------------------------------
    | SELECT DATE
    |--------------------------------------------------------------------------
    */
    function selectDate(
        date
    ){
        const chosen =
            cloneDate(
                date
            );
        if(
            !tempStart ||
            tempEnd
        ){
            tempStart =
                chosen;
            tempEnd =
                null;
        } else if(
            toISO(chosen) <
            toISO(tempStart)
        ){
            tempEnd =
                cloneDate(
                    tempStart
                );
            tempStart =
                chosen;
        } else {
            tempEnd =
                chosen;
        }
        renderCalendars();
    }
    /*
    |--------------------------------------------------------------------------
    | LABEL
    |--------------------------------------------------------------------------
    */
    function updateSelectedLabel(){
        if(!selectedLabel){
            return;
        }
        if(
            tempStart &&
            tempEnd
        ){
            selectedLabel.textContent =
                formatDate(
                    tempStart
                ) +
                ' - ' +
                formatDate(
                    tempEnd
                );
            return;
        }
        if(tempStart){
            selectedLabel.textContent =
                formatDate(
                    tempStart
                ) +
                ' - pilih tanggal akhir';
            return;
        }
        selectedLabel.textContent =
            'Pilih tanggal awal';
    }
    /*
    |--------------------------------------------------------------------------
    | POSITION
    |--------------------------------------------------------------------------
    */
    function positionPicker(){
        if(
            !picker ||
            !dateInput ||
            window.innerWidth <= 767
        ){
            return;
        }
        const rect =
            dateInput.getBoundingClientRect();
        const width =
            picker.offsetWidth ||
            720;
        const height =
            picker.offsetHeight ||
            500;
        let left =
            rect.left +
            rect.width / 2 -
            width / 2;
        let top =
            rect.bottom +
            8;
        if(
            left + width >
            window.innerWidth - 10
        ){
            left =
                window.innerWidth -
                width -
                10;
        }
        if(left < 10){
            left =
                10;
        }
        if(
            top + height >
            window.innerHeight - 10
        ){
            top =
                rect.top -
                height -
                8;
        }
        if(top < 10){
            top =
                10;
        }
        picker.style.left =
            left + 'px';
        picker.style.top =
            top + 'px';
    }
    /*
    |--------------------------------------------------------------------------
    | SHOW / HIDE
    |--------------------------------------------------------------------------
    */
    function showPicker(){
        if(!picker){
            return;
        }
        closePanel();
        tempStart =
            selectedStart
                ? cloneDate(
                    selectedStart
                )
                : null;
        tempEnd =
            selectedEnd
                ? cloneDate(
                    selectedEnd
                )
                : null;
        if(tempStart){
            viewLeft =
                new Date(
                    tempStart
                        .getFullYear(),
                    tempStart
                        .getMonth(),
                    1
                );
        }
        renderCalendars();
        picker.classList.remove(
            'hidden'
        );
        document.body.classList.add(
            'date-picker-lock'
        );
        requestAnimationFrame(
            positionPicker
        );
    }
    function hidePicker(){
        if(!picker){
            return;
        }
        picker.classList.add(
            'hidden'
        );
        closePanel();
        document.body.classList.remove(
            'date-picker-lock'
        );
    }
    /*
    |--------------------------------------------------------------------------
    | MONTH PANEL
    |--------------------------------------------------------------------------
    */
    function showMonthPanel(
        calendarDate,
        anchor,
        side
    ){
        if(!panel){
            return;
        }
        panelSide =
            side;
        panel.innerHTML =
            '';
        panel.classList.remove(
            'hidden'
        );
        const header =
            document.createElement(
                'div'
            );
        header.className =
            'custom-picker-panel-header';
        const title =
            document.createElement(
                'div'
            );
        title.className =
            'custom-picker-panel-title';
        title.textContent =
            'Pilih Bulan ' +
            calendarDate.getFullYear();
        const close =
            document.createElement(
                'button'
            );
        close.type =
            'button';
        close.className =
            'custom-picker-panel-close';
        close.innerHTML =
            '&times;';
        close.addEventListener(
            'click',
            closePanel
        );
        header.append(
            title,
            close
        );
        panel.appendChild(
            header
        );
        const grid =
            document.createElement(
                'div'
            );
        grid.className =
            'custom-picker-month-grid';
        MONTHS.forEach(
            function(
                monthName,
                monthIndex
            ){
                const button =
                    document.createElement(
                        'button'
                    );
                button.type =
                    'button';
                button.className =
                    'custom-picker-option';
                button.textContent =
                    monthName;
                if(
                    monthIndex ===
                    calendarDate.getMonth()
                ){
                    button.classList.add(
                        'active'
                    );
                }
                button.addEventListener(
                    'click',
                    function(event){
                        event.preventDefault();
                        event.stopPropagation();
                        const newDate =
                            new Date(
                                calendarDate
                                    .getFullYear(),
                                monthIndex,
                                1
                            );
                        viewLeft =
                            panelSide ===
                            'left'
                                ? newDate
                                : addMonths(
                                    newDate,
                                    -1
                                );
                        closePanel();
                        renderCalendars();
                    }
                );
                grid.appendChild(
                    button
                );
            }
        );
        panel.appendChild(
            grid
        );
        positionPanel(
            panel,
            anchor
        );
    }
    /*
    |--------------------------------------------------------------------------
    | YEAR PANEL
    |--------------------------------------------------------------------------
    */
    function showYearPanel(
        calendarDate,
        anchor,
        side
    ){
        if(!panel){
            return;
        }
        panelSide =
            side;
        const startYear =
            Math.floor(
                calendarDate
                    .getFullYear() /
                12
            ) * 12;
        renderYearPanel(
            calendarDate,
            anchor,
            startYear
        );
    }
    function renderYearPanel(
        calendarDate,
        anchor,
        startYear
    ){
        panel.innerHTML =
            '';
        panel.classList.remove(
            'hidden'
        );
        const header =
            document.createElement(
                'div'
            );
        header.className =
            'custom-picker-panel-header';
        const title =
            document.createElement(
                'div'
            );
        title.className =
            'custom-picker-panel-title';
        title.textContent =
            startYear +
            ' - ' +
            (
                startYear +
                11
            );
        const navigation =
            document.createElement(
                'div'
            );
        navigation.className =
            'custom-picker-year-navigation';
        const previous =
            document.createElement(
                'button'
            );
        previous.type =
            'button';
        previous.className =
            'custom-picker-year-nav';
        previous.innerHTML =
            '&#8249;';
        const next =
            document.createElement(
                'button'
            );
        next.type =
            'button';
        next.className =
            'custom-picker-year-nav';
        next.innerHTML =
            '&#8250;';
        const close =
            document.createElement(
                'button'
            );
        close.type =
            'button';
        close.className =
            'custom-picker-panel-close';
        close.innerHTML =
            '&times;';
        previous.addEventListener(
            'click',
            function(event){
                event.preventDefault();
                event.stopPropagation();
                renderYearPanel(
                    calendarDate,
                    anchor,
                    startYear - 12
                );
            }
        );
        next.addEventListener(
            'click',
            function(event){
                event.preventDefault();
                event.stopPropagation();
                renderYearPanel(
                    calendarDate,
                    anchor,
                    startYear + 12
                );
            }
        );
        close.addEventListener(
            'click',
            closePanel
        );
        navigation.append(
            previous,
            next,
            close
        );
        header.append(
            title,
            navigation
        );
        panel.appendChild(
            header
        );
        const grid =
            document.createElement(
                'div'
            );
        grid.className =
            'custom-picker-year-grid';
        for(
            let year = startYear;
            year < startYear + 12;
            year++
        ){
            const button =
                document.createElement(
                    'button'
                );
            button.type =
                'button';
            button.className =
                'custom-picker-option';
            button.textContent =
                String(year);
            if(
                year ===
                calendarDate.getFullYear()
            ){
                button.classList.add(
                    'active'
                );
            }
            if(
                year ===
                new Date()
                    .getFullYear()
            ){
                button.classList.add(
                    'current'
                );
            }
            button.disabled =
                year < MIN_YEAR ||
                year > MAX_YEAR;
            if(
                !button.disabled
            ){
                button.addEventListener(
                    'click',
                    function(event){
                        event.preventDefault();
                        event.stopPropagation();
                        const month =
                            calendarDate
                                .getMonth();
                        viewLeft =
                            panelSide ===
                            'left'
                                ? new Date(
                                    year,
                                    month,
                                    1
                                )
                                : new Date(
                                    year,
                                    month - 1,
                                    1
                                );
                        closePanel();
                        renderCalendars();
                    }
                );
            }
            grid.appendChild(
                button
            );
        }
        panel.appendChild(
            grid
        );
        positionPanel(
            panel,
            anchor
        );
    }
    /*
    |--------------------------------------------------------------------------
    | POSITION PANEL
    |--------------------------------------------------------------------------
    */
    function positionPanel(
        element,
        anchor
    ){
        if(
            !element ||
            !anchor
        ){
            return;
        }
        if(
            window.innerWidth <= 767
        ){
            element.style.left =
                '';
            element.style.top =
                '';
            return;
        }
        const rect =
            anchor.getBoundingClientRect();
        const width =
            element.offsetWidth ||
            310;
        const height =
            element.offsetHeight ||
            300;
        let left =
            rect.left +
            rect.width / 2 -
            width / 2;
        let top =
            rect.bottom +
            8;
        if(
            left + width >
            window.innerWidth - 10
        ){
            left =
                window.innerWidth -
                width -
                10;
        }
        if(left < 10){
            left =
                10;
        }
        if(
            top + height >
            window.innerHeight - 10
        ){
            top =
                rect.top -
                height -
                8;
        }
        if(top < 10){
            top =
                10;
        }
        element.style.left =
            left + 'px';
        element.style.top =
            top + 'px';
    }
    function closePanel(){
        if(!panel){
            return;
        }
        panel.classList.add(
            'hidden'
        );
        panel.innerHTML =
            '';
    }
    /*
    |--------------------------------------------------------------------------
    | APPLY DATE RANGE
    |--------------------------------------------------------------------------
    */
    function applyDateRange(){
        if(
            !tempStart ||
            !tempEnd
        ){
            if(
                typeof window.Swal !==
                'undefined'
            ){
                window.Swal.fire({
                    icon:'info',
                    title:'Pilih rentang tanggal',
                    text:
                        'Silakan pilih tanggal awal dan tanggal akhir terlebih dahulu.',
                    confirmButtonText:
                        'Mengerti',
                    confirmButtonColor:
                        '#2563eb'
                });
            } else {
                window.alert(
                    'Silakan pilih tanggal awal dan tanggal akhir terlebih dahulu.'
                );
            }
            return;
        }
        selectedStart =
            cloneDate(
                tempStart
            );
        selectedEnd =
            cloneDate(
                tempEnd
            );
        if(dariInput){
            dariInput.value =
                toISO(
                    selectedStart
                );
        }
        if(sampaiInput){
            sampaiInput.value =
                toISO(
                    selectedEnd
                );
        }
        if(dateInput){
            dateInput.value =
                formatDate(
                    selectedStart
                ) +
                ' - ' +
                formatDate(
                    selectedEnd
                );
        }
        if(clearButton){
            clearButton.style.display =
                'flex';
        }
        hidePicker();
    }
    /*
    |--------------------------------------------------------------------------
    | CLEAR DATE
    |--------------------------------------------------------------------------
    */
    function clearDateRange(){
        selectedStart =
            null;
        selectedEnd =
            null;
        tempStart =
            null;
        tempEnd =
            null;
        if(dariInput){
            dariInput.value =
                '';
        }
        if(sampaiInput){
            sampaiInput.value =
                '';
        }
        if(dateInput){
            dateInput.value =
                '';
        }
        if(clearButton){
            clearButton.style.display =
                'none';
        }
        viewLeft =
            new Date();
        viewLeft.setDate(
            1
        );
        hidePicker();
    }
    /*
    |--------------------------------------------------------------------------
    | DATE EVENTS
    |--------------------------------------------------------------------------
    */
    if(
        dateInput &&
        picker
    ){
        dateInput.addEventListener(
            'click',
            function(event){
                event.preventDefault();
                event.stopPropagation();
                if(
                    picker.classList.contains(
                        'hidden'
                    )
                ){
                    showPicker();
                } else {
                    hidePicker();
                }
            }
        );
        dateInput.addEventListener(
            'focus',
            function(){
                if(
                    picker.classList.contains(
                        'hidden'
                    )
                ){
                    showPicker();
                }
            }
        );
        closePickerButton?.addEventListener(
            'click',
            function(event){
                event.preventDefault();
                event.stopPropagation();
                hidePicker();
            }
        );
        clearPickerButton?.addEventListener(
            'click',
            function(event){
                event.preventDefault();
                event.stopPropagation();
                clearDateRange();
            }
        );
        applyPickerButton?.addEventListener(
            'click',
            function(event){
                event.preventDefault();
                event.stopPropagation();
                applyDateRange();
            }
        );
        clearButton?.addEventListener(
            'click',
            function(event){
                event.preventDefault();
                event.stopPropagation();
                clearDateRange();
            }
        );
        document.addEventListener(
            'mousedown',
            function(event){
                if(
                    picker.classList.contains(
                        'hidden'
                    )
                ){
                    return;
                }
                if(
                    picker.contains(
                        event.target
                    )
                ){
                    return;
                }
                if(
                    panel &&
                    panel.contains(
                        event.target
                    )
                ){
                    return;
                }
                if(
                    dateInput.contains(
                        event.target
                    )
                ){
                    return;
                }
                if(
                    clearButton &&
                    clearButton.contains(
                        event.target
                    )
                ){
                    return;
                }
                hidePicker();
            }
        );
    }
    /*
    |--------------------------------------------------------------------------
    | RESIZE
    |--------------------------------------------------------------------------
    */
    window.addEventListener(
        'resize',
        function(){
            if(
                picker &&
                !picker.classList.contains(
                    'hidden'
                )
            ){
                positionPicker();
            }
            if(
                panel &&
                !panel.classList.contains(
                    'hidden'
                )
            ){
                closePanel();
            }
        }
    );
    /*
    |--------------------------------------------------------------------------
    | SCROLL
    |--------------------------------------------------------------------------
    */
    window.addEventListener(
        'scroll',
        function(){
            if(
                picker &&
                !picker.classList.contains(
                    'hidden'
                ) &&
                window.innerWidth > 767
            ){
                positionPicker();
            }
        },
        true
    );
    /*
    |--------------------------------------------------------------------------
    | FILTER DROPDOWNS
    |--------------------------------------------------------------------------
    */
    function initializeFilterDropdowns(){
        const dropdowns =
            document.querySelectorAll(
                '[data-filter-dropdown]'
            );
        if(!dropdowns.length){
            return;
        }
        function closeDropdown(
            dropdown
        ){
            const trigger =
                dropdown.querySelector(
                    '[data-dropdown-trigger]'
                );
            const menu =
                dropdown.querySelector(
                    '[data-dropdown-menu]'
                );
            if(
                !trigger ||
                !menu
            ){
                return;
            }
            menu.classList.add(
                'hidden'
            );
            trigger.classList.remove(
                'is-open'
            );
            trigger.setAttribute(
                'aria-expanded',
                'false'
            );
        }
        function closeAllDropdowns(
            except = null
        ){
            dropdowns.forEach(
                function(dropdown){
                    if(
                        dropdown !==
                        except
                    ){
                        closeDropdown(
                            dropdown
                        );
                    }
                }
            );
        }
        function updateDropdown(
            dropdown
        ){
            const type =
                dropdown.dataset
                    .filterDropdown;
            const selector =
                type === 'kategori'
                    ? '.kategori-checkbox'
                    : '.status-checkbox';
            const checkboxes =
                dropdown.querySelectorAll(
                    selector
                );
            const checked =
                dropdown.querySelectorAll(
                    selector +
                    ':checked'
                );
            const count =
                checked.length;
            const countElement =
                dropdown.querySelector(
                    '[data-filter-count]'
                );
            const footerCount =
                dropdown.querySelector(
                    '[data-footer-count]'
                );
            const trigger =
                dropdown.querySelector(
                    '[data-dropdown-trigger]'
                );
            if(countElement){
                countElement.textContent =
                    count +
                    ' dipilih';
            }
            if(footerCount){
                footerCount.textContent =
                    count +
                    ' ' +
                    (
                        type ===
                        'kategori'
                            ? 'kategori'
                            : 'status'
                    ) +
                    ' dipilih';
            }
            checkboxes.forEach(
                function(checkbox){
                    const option =
                        checkbox.closest(
                            '.surat-filter-option'
                        );
                    if(option){
                        option.classList.toggle(
                            'is-selected',
                            checkbox.checked
                        );
                    }
                }
            );
            if(trigger){
                trigger.classList.toggle(
                    'is-active',
                    count > 0
                );
            }
        }
        dropdowns.forEach(
            function(dropdown){
                const trigger =
                    dropdown.querySelector(
                        '[data-dropdown-trigger]'
                    );
                const menu =
                    dropdown.querySelector(
                        '[data-dropdown-menu]'
                    );
                if(
                    !trigger ||
                    !menu
                ){
                    return;
                }
                const type =
                    dropdown.dataset
                        .filterDropdown;
                const selector =
                    type === 'kategori'
                        ? '.kategori-checkbox'
                        : '.status-checkbox';
                const checkboxes =
                    dropdown.querySelectorAll(
                        selector
                    );
                const selectAll =
                    dropdown.querySelector(
                        '[data-action="select-all"]'
                    );
                const clearAll =
                    dropdown.querySelector(
                        '[data-action="clear-all"]'
                    );
                trigger.addEventListener(
                    'click',
                    function(event){
                        event.preventDefault();
                        event.stopPropagation();
                        const isOpen =
                            !menu.classList.contains(
                                'hidden'
                            );
                        closeAllDropdowns(
                            dropdown
                        );
                        if(isOpen){
                            closeDropdown(
                                dropdown
                            );
                            return;
                        }
                        menu.classList.remove(
                            'hidden'
                        );
                        trigger.classList.add(
                            'is-open'
                        );
                        trigger.setAttribute(
                            'aria-expanded',
                            'true'
                        );
                        updateDropdown(
                            dropdown
                        );
                    }
                );
                menu.addEventListener(
                    'click',
                    function(event){
                        event.stopPropagation();
                    }
                );
                checkboxes.forEach(
                    function(checkbox){
                        checkbox.addEventListener(
                            'change',
                            function(){
                                updateDropdown(
                                    dropdown
                                );
                            }
                        );
                    }
                );
                selectAll?.addEventListener(
                    'click',
                    function(event){
                        event.preventDefault();
                        event.stopPropagation();
                        checkboxes.forEach(
                            function(checkbox){
                                checkbox.checked =
                                    true;
                            }
                        );
                        updateDropdown(
                            dropdown
                        );
                    }
                );
                clearAll?.addEventListener(
                    'click',
                    function(event){
                        event.preventDefault();
                        event.stopPropagation();
                        checkboxes.forEach(
                            function(checkbox){
                                checkbox.checked =
                                    false;
                            }
                        );
                        updateDropdown(
                            dropdown
                        );
                    }
                );
                updateDropdown(
                    dropdown
                );
            }
        );
        document.addEventListener(
            'click',
            function(event){
                if(
                    event.target.closest(
                        '[data-filter-dropdown]'
                    )
                ){
                    return;
                }
                closeAllDropdowns();
            }
        );
        document.addEventListener(
            'keydown',
            function(event){
                if(
                    event.key === 'Escape'
                ){
                    closeAllDropdowns();
                }
            }
        );
    }
    /*
    |--------------------------------------------------------------------------
    | VALIDASI
    |--------------------------------------------------------------------------
    */
    const filterForm =
        document.getElementById(
            'filterForm'
        );
    filterForm?.addEventListener(
        'submit',
        function(event){
            const start =
                dariInput?.value ||
                '';
            const end =
                sampaiInput?.value ||
                '';
            if(
                start &&
                end &&
                start > end
            ){
                event.preventDefault();
                if(
                    typeof window.Swal !==
                    'undefined'
                ){
                    window.Swal.fire({
                        icon:'warning',
                        title:
                            'Rentang tanggal tidak valid',
                        text:
                            'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.',
                        confirmButtonText:
                            'Mengerti',
                        confirmButtonColor:
                            '#2563eb'
                    });
                } else {
                    window.alert(
                        'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.'
                    );
                }
            }
        }
    );
    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll(
        '.delete-btn'
    ).forEach(
        function(button){
            button.addEventListener(
                'click',
                function(){
                    const form =
                        this.closest(
                            '.delete-form'
                        );
                    if(!form){
                        return;
                    }
                    if(
                        typeof window.Swal !==
                        'undefined'
                    ){
                        window.Swal.fire({
                            title:
                                'Hapus Surat Masuk?',
                            text:
                                'Data yang dihapus akan dipindahkan ke tempat sampah.',
                            icon:
                                'warning',
                            showCancelButton:
                                true,
                            confirmButtonColor:
                                '#ef4444',
                            cancelButtonColor:
                                '#64748b',
                            confirmButtonText:
                                'Ya, Hapus!',
                            cancelButtonText:
                                'Batal',
                            reverseButtons:
                                true
                        }).then(
                            function(result){
                                if(
                                    result.isConfirmed
                                ){
                                    form.submit();
                                }
                            }
                        );
                        return;
                    }
                    if(
                        window.confirm(
                            'Yakin ingin menghapus surat masuk ini?'
                        )
                    ){
                        form.submit();
                    }
                }
            );
        }
    );
    /*
    |--------------------------------------------------------------------------
    | INIT
    |--------------------------------------------------------------------------
    */
    initializeFilterDropdowns();
    if(
        selectedStart &&
        selectedEnd &&
        dateInput &&
        clearButton
    ){
        dateInput.value =
            formatDate(
                selectedStart
            ) +
            ' - ' +
            formatDate(
                selectedEnd
            );
        clearButton.style.display =
            'flex';
    }
})();
</script>
<script
    src="https://cdn.jsdelivr.net/npm/sweetalert2@11"
    defer
></script>
@endpush
@endsection
