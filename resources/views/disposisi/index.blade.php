@extends('layouts.app')

@section('title', 'Disposisi Surat')

@section('content')
@php
    $user = auth()->user();
    $userRole = strtolower(trim((string) ($user->role ?? $user->jabatan ?? '')));
    $userRole = $userRole === 'staff' ? 'staf' : $userRole;

    $statusOptions = [
        'menunggu' => 'Menunggu',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
    ];

    $statusBadgeClasses = [
        'menunggu' => 'border-amber-200 bg-amber-50 text-amber-700',
        'diproses' => 'border-blue-200 bg-blue-50 text-blue-700',
        'selesai' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
    ];

    $selectedStatus = collect(request('status', []))
        ->filter(fn ($status) => is_scalar($status))
        ->map(fn ($status) => strtolower(trim((string) $status)))
        ->filter(fn ($status) => array_key_exists($status, $statusOptions))
        ->unique()
        ->values()
        ->all();

    $dariTanggal = request('dari_tanggal');
    $sampaiTanggal = request('sampai_tanggal');
    $dateRangeValue = '';

    try {
        if ($dariTanggal && $sampaiTanggal) {
            $dateRangeValue = \Carbon\Carbon::parse($dariTanggal)->format('d/m/Y').' - '.\Carbon\Carbon::parse($sampaiTanggal)->format('d/m/Y');
        } elseif ($dariTanggal) {
            $dateRangeValue = \Carbon\Carbon::parse($dariTanggal)->format('d/m/Y');
        } elseif ($sampaiTanggal) {
            $dateRangeValue = \Carbon\Carbon::parse($sampaiTanggal)->format('d/m/Y');
        }
    } catch (\Throwable $e) {
        $dateRangeValue = '';
    }

    $hasFilters = request()->filled('search')
        || !empty($selectedStatus)
        || request()->filled('dari_tanggal')
        || request()->filled('sampai_tanggal');

    $getNomorSurat = function ($disposisi) {
        return data_get($disposisi, 'suratMasuk.nomor_surat')
            ?? data_get($disposisi, 'surat_masuk.nomor_surat')
            ?? data_get($disposisi, 'surat.nomor_surat')
            ?? data_get($disposisi, 'nomor_surat')
            ?? '-';
    };

    $getPenerima = function ($disposisi) {
        $penerima = data_get($disposisi, 'kepada');

        if (!$penerima) {
            return ['nama' => '-', 'jabatan' => null];
        }

        return [
            'nama' => $penerima->name ?? $penerima->nama ?? '-',
            'jabatan' => $penerima->jabatan ?? null,
        ];
    };

    $getInstruksi = function ($disposisi) {
        return data_get($disposisi, 'isi_disposisi')
            ?? data_get($disposisi, 'instruksi')
            ?? data_get($disposisi, 'isi_instruksi')
            ?? '-';
    };

    $getTanggalDisposisi = function ($disposisi) {
        $tanggal = data_get($disposisi, 'tanggal_disposisi')
            ?? data_get($disposisi, 'created_at');

        if (!$tanggal) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($tanggal)->format('d/m/Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    $getBatasWaktu = function ($disposisi) {
        $tanggal = data_get($disposisi, 'batas_waktu')
            ?? data_get($disposisi, 'tanggal_batas');

        if (!$tanggal) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($tanggal)->format('d/m/Y');
        } catch (\Throwable $e) {
            return '-';
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Statistik
    |--------------------------------------------------------------------------
    | Mendukung baik $disposisiStats dari controller maupun variabel
    | statistik terpisah jika controller mengirimkannya.
    */
    $disposisiStats = $disposisiStats ?? [];

    $statTotal = (int) (
        $disposisiStats['total']
        ?? $totalDisposisi
        ?? 0
    );

    $statMenunggu = (int) (
        $disposisiStats['menunggu']
        ?? $disposisiMenunggu
        ?? 0
    );

    $statDiproses = (int) (
        $disposisiStats['diproses']
        ?? $disposisiDiproses
        ?? 0
    );

    $statSelesai = (int) (
        $disposisiStats['selesai']
        ?? $disposisiSelesai
        ?? 0
    );
@endphp

@push('styles')
<style>
.disposisi-page{min-width:0;color:#172033}
.disposisi-header{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:18px}
.disposisi-header-main{display:flex;align-items:center;gap:13px;min-width:0}
.disposisi-header-icon{display:flex;align-items:center;justify-content:center;width:50px;height:50px;flex:0 0 50px;border-radius:15px;background:linear-gradient(135deg,#e0e7ff,#eef2ff);color:#4f46e5;box-shadow:inset 0 0 0 1px rgba(79,70,229,.06)}
.disposisi-header-title{margin:0;color:#172554;font-size:27px;font-weight:800;line-height:1.1;letter-spacing:-.025em}
.disposisi-header-subtitle{margin-top:4px;color:#64748b;font-size:11px;line-height:1.5}
.disposisi-header-actions{display:flex;align-items:center;justify-content:flex-end;gap:7px;flex-wrap:wrap}
.disposisi-top-button{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:38px;padding:0 12px;border-radius:10px;font-size:10px;font-weight:800;line-height:1;text-decoration:none;transition:.15s ease}
.disposisi-top-button.excel{border:1px solid #a7f3d0;background:#ecfdf5;color:#047857}
.disposisi-top-button.excel:hover{background:#d1fae5;border-color:#6ee7b7}
.disposisi-top-button.pdf{border:1px solid #fecdd3;background:#fff1f2;color:#be123c}
.disposisi-top-button.pdf:hover{background:#ffe4e6;border-color:#fda4af}
.disposisi-top-button.primary{border:1px solid #2563eb;background:#2563eb;color:#fff;box-shadow:0 7px 18px rgba(37,99,235,.18)}
.disposisi-top-button.primary:hover{background:#1d4ed8;border-color:#1d4ed8;transform:translateY(-1px)}

.disposisi-filter-card{padding:13px;border:1px solid #dbe4f0;border-radius:16px;background:#fff;box-shadow:0 3px 14px rgba(15,23,42,.035)}
.disposisi-filter-form{display:flex;flex-direction:column;gap:9px}
.disposisi-filter-main{display:grid;grid-template-columns:minmax(0,1.2fr) minmax(260px,1.8fr) auto;gap:9px;align-items:center}
.disposisi-search-wrap,.disposisi-date-wrap{position:relative;min-width:0}
.disposisi-input{width:100%;height:40px;border:1px solid #d6e0ec;border-radius:10px;outline:none;background:#fff;color:#334155;font-size:10.5px;transition:.15s ease}
.disposisi-input:hover{border-color:#b8c5d6}
.disposisi-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.08)}
.disposisi-search-wrap .search-icon{position:absolute;top:50%;left:12px;z-index:2;color:#94a3b8;transform:translateY(-50%)}
.disposisi-search-input{padding:0 12px 0 38px}
.disposisi-date-input{padding:0 66px 0 38px;cursor:pointer}
.disposisi-date-tools{position:absolute;top:50%;right:5px;display:flex;align-items:center;gap:2px;transform:translateY(-50%)}
.disposisi-date-clear{display:flex;align-items:center;justify-content:center;width:27px;height:27px;border:0;border-radius:7px;background:transparent;color:#94a3b8;cursor:pointer}
.disposisi-date-clear.hidden{display:none}
.disposisi-date-clear:hover{background:#fff1f2;color:#e11d48}
.disposisi-filter-action{display:flex;align-items:center;gap:5px}
.disposisi-filter-button{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:40px;min-width:82px;padding:0 13px;border:1px solid #2563eb;border-radius:10px;background:#2563eb;color:#fff;font-size:10px;font-weight:800;cursor:pointer;box-shadow:0 6px 14px rgba(37,99,235,.15);transition:.15s ease}
.disposisi-filter-button:hover{background:#1d4ed8;border-color:#1d4ed8}
.disposisi-reset-button{display:inline-flex;align-items:center;justify-content:center;width:40px;height:40px;border:1px solid #dbe4f0;border-radius:10px;background:#f8fafc;color:#64748b;text-decoration:none;transition:.15s ease}
.disposisi-reset-button:hover{border-color:#fecdd3;background:#fff1f2;color:#e11d48}

.archive-date-picker,.archive-status-dropdown{position:relative}
.archive-date-panel{position:absolute;top:calc(100% + 8px);left:0;z-index:9999;width:650px;max-width:calc(100vw - 24px);overflow:hidden;border:1px solid #dbe4f0;border-radius:14px;background:#fff;box-shadow:0 22px 55px rgba(15,23,42,.16),0 8px 22px rgba(15,23,42,.08)}
.archive-date-panel.hidden,.archive-status-panel.hidden,.archive-picker-view.hidden{display:none}
.archive-date-header{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 12px;border-bottom:1px solid #edf2f7;background:#f8fafc}
.archive-date-header-title{color:#334155;font-size:11px;font-weight:800}
.archive-date-nav{display:inline-flex;width:30px;height:30px;align-items:center;justify-content:center;flex:none;border:1px solid #dbe4f0;border-radius:8px;background:#fff;color:#64748b;cursor:pointer;transition:.15s}
.archive-date-nav:hover{border-color:#bfdbfe;background:#eff6ff;color:#2563eb}
.archive-date-months{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}
.archive-date-month{min-width:0;padding:12px;border-right:1px solid #edf2f7}
.archive-date-month:last-child{border-right:0}
.archive-month-header{display:flex;align-items:center;justify-content:center;gap:2px;margin-bottom:7px}
.archive-month-header button{display:inline-flex;align-items:center;gap:2px;padding:5px 7px;border:1px solid #dbe3ed;border-radius:7px;background:#f8fafc;color:#334155;font-size:9px;font-weight:800;cursor:pointer;transition:.15s}
.archive-month-header button:hover{border-color:#93c5fd;background:#eff6ff;color:#2563eb}
.archive-month-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:2px}
.archive-calendar-weekday{display:flex;height:24px;align-items:center;justify-content:center;color:#94a3b8;font-size:8px;font-weight:800;text-transform:uppercase}
.archive-calendar-day{display:flex;width:100%;height:31px;align-items:center;justify-content:center;border:1px solid transparent;border-radius:7px;background:transparent;color:#475569;font-size:9.5px;font-weight:600;cursor:pointer;transition:.15s}
.archive-calendar-day:hover{border-color:#bfdbfe;background:#eff6ff;color:#2563eb}
.archive-calendar-day.other-month{color:#cbd5e1}
.archive-calendar-day.today{box-shadow:inset 0 0 0 1px #93c5fd;color:#2563eb}
.archive-calendar-day.in-range{border-radius:0;background:#eff6ff;color:#2563eb}
.archive-calendar-day.start-date{border-radius:999px 5px 5px 999px;background:#2563eb;color:#fff}
.archive-calendar-day.end-date{border-radius:5px 999px 999px 5px;background:#2563eb;color:#fff}
.archive-calendar-day.start-date.end-date{border-radius:999px}
.archive-date-footer{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:9px 12px;border-top:1px solid #edf2f7;background:#fafcff}
.archive-date-footer-info{min-width:0;overflow:hidden;color:#64748b;font-size:8.5px;font-weight:700;text-overflow:ellipsis;white-space:nowrap}
.archive-date-footer-actions{display:flex;align-items:center;gap:5px;flex:none}
.archive-date-btn{min-height:31px;padding:0 11px;border-radius:8px;font-size:8.5px;font-weight:800;cursor:pointer;transition:.15s}
.archive-date-btn.secondary{border:1px solid #dbe4f0;background:#fff;color:#475569}
.archive-date-btn.primary{border:1px solid #2563eb;background:#2563eb;color:#fff}

.archive-picker-toolbar,.archive-year-toolbar{display:flex;align-items:center;justify-content:center;gap:10px;padding:12px;border-bottom:1px solid #edf2f7;background:#f8fafc;color:#334155;font-size:10px;font-weight:800}
.archive-picker-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:7px;padding:12px}
.archive-picker-item{min-height:36px;padding:0 8px;border:1px solid #dbe4f0;border-radius:8px;background:#fff;color:#475569;font-size:9px;font-weight:700;cursor:pointer}
.archive-picker-item:hover,.archive-picker-item.active{border-color:#93c5fd;background:#eff6ff;color:#2563eb}
.archive-year-nav button{width:28px;height:28px;border:1px solid #dbe4f0;border-radius:7px;background:#fff;color:#64748b;cursor:pointer}
.archive-year-nav button:hover{border-color:#93c5fd;background:#eff6ff;color:#2563eb}

.archive-status-panel{position:absolute;top:calc(100% + 8px);left:0;right:0;z-index:9999;overflow:hidden;border:1px solid #dbe4f0;border-radius:13px;background:#fff;box-shadow:0 22px 55px rgba(15,23,42,.16),0 8px 22px rgba(15,23,42,.08)}
.archive-status-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:10px 12px;border-bottom:1px solid #edf2f7;background:#f8fafc}
.archive-status-head-title strong{display:block;color:#334155;font-size:10px;font-weight:800}
.archive-status-head-title span{display:block;margin-top:2px;color:#94a3b8;font-size:8px}
.archive-status-count{display:inline-flex;min-height:22px;align-items:center;justify-content:center;padding:0 8px;border:1px solid #bfdbfe;border-radius:999px;background:#eff6ff;color:#2563eb;font-size:8px;font-weight:800}
.archive-status-actions{display:flex;align-items:center;justify-content:flex-end;gap:12px;padding:7px 12px;border-bottom:1px solid #edf2f7}
.archive-status-action{padding:0;border:0;background:transparent;color:#2563eb;font-size:8px;font-weight:800;cursor:pointer}
.archive-status-action.muted{color:#64748b}
.archive-status-options{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:7px;padding:9px 12px}
.archive-status-item{display:flex;min-height:37px;align-items:center;gap:8px;padding:6px 8px;border:1px solid #dbe3ed;border-radius:9px;background:#fff;cursor:pointer;transition:.15s}
.archive-status-item:hover,.archive-status-item:has(input:checked){border-color:#93c5fd;background:#eff6ff}
.archive-status-item input{width:14px;height:14px;accent-color:#2563eb}
.archive-status-item-label{min-width:0;flex:1;color:#475569;font-size:8.5px;font-weight:700}
.archive-status-dot{width:7px;height:7px;flex:none;border-radius:999px}
.archive-status-dot.menunggu{background:#f59e0b}
.archive-status-dot.diproses{background:#3b82f6}
.archive-status-dot.selesai{background:#10b981}
.archive-status-footer{display:flex;justify-content:flex-end;padding:7px 12px;border-top:1px solid #edf2f7;background:#fafcff}
.archive-status-close{min-height:29px;padding:0 11px;border:1px solid #dbe4f0;border-radius:8px;background:#fff;color:#475569;font-size:8.5px;font-weight:800;cursor:pointer}

.disposisi-filter-status-row{display:flex;align-items:center;gap:9px}
.disposisi-filter-status-row .archive-status-dropdown{width:min(100%,360px)}
.disposisi-status-trigger{display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;min-height:48px;padding:7px 10px;border:1px solid #dbe4f0;border-radius:11px;background:#fff;cursor:pointer;transition:.15s ease}
.disposisi-status-trigger:hover{border-color:#bfdbfe;background:#f8fbff}
.disposisi-status-trigger[aria-expanded="true"]{border-color:#93c5fd;background:#f8fbff;box-shadow:0 0 0 3px rgba(59,130,246,.08)}
.disposisi-status-left{display:flex;align-items:center;gap:9px;min-width:0}
.disposisi-status-icon{display:flex;align-items:center;justify-content:center;width:31px;height:31px;flex:0 0 31px;border-radius:9px;background:#fffbeb;color:#d97706}
.disposisi-status-title{color:#334155;font-size:9.5px;font-weight:800}
.disposisi-status-subtitle{margin-top:1px;color:#94a3b8;font-size:8px}
.disposisi-status-meta{display:flex;align-items:center;gap:7px;flex:none}
.disposisi-status-count{display:inline-flex;min-height:22px;align-items:center;justify-content:center;padding:0 8px;border:1px solid #dbe4f0;border-radius:999px;background:#f8fafc;color:#64748b;font-size:8px;font-weight:800}
.disposisi-status-trigger svg{color:#94a3b8;transition:.15s}
.disposisi-status-trigger[aria-expanded="true"]>div>svg{transform:rotate(180deg);color:#2563eb}

.disposisi-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px}
.disposisi-summary-card{display:flex;align-items:center;gap:10px;min-width:0;padding:11px 12px;border:1px solid #dbe4f0;border-radius:12px;background:#fff;box-shadow:0 2px 10px rgba(15,23,42,.025)}
.disposisi-summary-icon{display:flex;align-items:center;justify-content:center;width:32px;height:32px;flex:0 0 32px;border-radius:9px}
.disposisi-summary-icon.blue{background:#eff6ff;color:#2563eb}
.disposisi-summary-icon.amber{background:#fffbeb;color:#d97706}
.disposisi-summary-icon.purple{background:#f5f3ff;color:#7c3aed}
.disposisi-summary-icon.green{background:#ecfdf5;color:#059669}
.disposisi-summary-label{color:#94a3b8;font-size:7.5px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
.disposisi-summary-value{margin-top:1px;color:#172033;font-size:17px;font-weight:800;line-height:1}
.disposisi-summary-note{margin-top:2px;color:#cbd5e1;font-size:7.5px}

.disposisi-table-card{overflow:hidden;border:1px solid #dbe4f0;border-radius:16px;background:#fff;box-shadow:0 3px 14px rgba(15,23,42,.035)}
.disposisi-table-header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid #edf2f7}
.disposisi-table-title-wrap{display:flex;align-items:center;gap:9px;min-width:0}
.disposisi-table-icon{display:flex;align-items:center;justify-content:center;width:34px;height:34px;flex:0 0 34px;border-radius:10px;background:#eff6ff;color:#2563eb}
.disposisi-table-title{margin:0;color:#172033;font-size:12px;font-weight:800}
.disposisi-table-subtitle{margin-top:2px;color:#94a3b8;font-size:8px}
.disposisi-table-meta{display:inline-flex;align-items:center;gap:6px;min-height:29px;padding:0 9px;border:1px solid #dbe4f0;border-radius:9px;background:#f8fafc;color:#64748b;font-size:8px;font-weight:800}
.disposition-table-wrapper{overflow:hidden}
.disposition-table-scroll{overflow-x:auto}
.disposition-table{width:100%;min-width:930px;border-collapse:collapse;border-spacing:0;background:#fff}
.disposition-table thead{background:#f8fafc}
.disposition-table thead tr{border-bottom:1px solid #e2e8f0}
.disposition-table thead th{padding:10px 12px;color:#64748b;font-size:7.5px;font-weight:800;letter-spacing:.05em;line-height:1.3;text-align:left;text-transform:uppercase;white-space:nowrap}
.disposition-table thead th:last-child{text-align:center}
.disposition-table tbody tr{background:#fff;transition:.15s ease}
.disposition-table tbody tr:nth-child(even){background:#fbfdff}
.disposition-table tbody tr:hover{background:#f8fbff}
.disposition-table tbody td{padding:11px 12px;border-bottom:1px solid #edf2f7;color:#475569;font-size:9.5px;line-height:1.4;vertical-align:middle}
.disposition-table tbody tr:last-child td{border-bottom:0}
.disposition-table .cell-number{color:#334155;font-weight:700}
.disposition-table .cell-date{color:#64748b;font-weight:600;white-space:nowrap}
.disposition-table .cell-instruction{max-width:250px;overflow:hidden;color:#334155;font-weight:600}
.disposition-table .cell-deadline,.disposition-table .cell-status{white-space:nowrap}
.disposition-table .receiver-box{display:flex;min-width:140px;max-width:190px;flex-direction:column;gap:1px;padding:6px 8px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc}
.disposition-table .receiver-name{overflow:hidden;color:#334155;font-size:9px;font-weight:700;text-overflow:ellipsis;white-space:nowrap}
.disposition-table .receiver-position{overflow:hidden;color:#94a3b8;font-size:7.5px;text-overflow:ellipsis;white-space:nowrap}
.disposition-table .status-badge{display:inline-flex;align-items:center;border-width:1px;border-radius:999px;padding:4px 8px;font-size:7.5px;font-weight:800}
.disposition-table .action-cell{width:110px;text-align:center}
.disposition-table .action-buttons{display:inline-flex;align-items:center;justify-content:center;gap:4px}
.disposition-table .action-button{display:inline-flex;width:29px;height:29px;align-items:center;justify-content:center;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#64748b;transition:.15s}
.disposition-table .action-button.detail:hover{border-color:#bfdbfe;background:#eff6ff;color:#2563eb}
.disposition-table .action-button.edit:hover{border-color:#fde68a;background:#fffbeb;color:#d97706}
.disposition-table .action-button.delete:hover{border-color:#fecdd3;background:#fff1f2;color:#e11d48}
.disposition-table-empty{padding:62px 18px!important;text-align:center}
.disposisi-empty-icon{display:flex;align-items:center;justify-content:center;width:58px;height:58px;margin:0 auto 12px;border-radius:18px;background:linear-gradient(135deg,#eef2ff,#f8fafc);color:#6366f1;box-shadow:inset 0 0 0 1px #e0e7ff}
.disposisi-empty-title{color:#172033;font-size:12px;font-weight:800}
.disposisi-empty-text{max-width:360px;margin:5px auto 0;color:#94a3b8;font-size:9px;line-height:1.6}
.disposisi-empty-button{display:inline-flex;align-items:center;gap:6px;margin-top:13px;min-height:31px;padding:0 11px;border-radius:8px;background:#2563eb;color:#fff;font-size:8.5px;font-weight:800;text-decoration:none;box-shadow:0 6px 14px rgba(37,99,235,.14)}
.disposisi-pagination{padding:10px 14px;border-top:1px solid #edf2f7}

@media(max-width:1000px){
    .disposisi-filter-status-row .archive-status-dropdown{width:100%;max-width:360px}
    .disposisi-header{align-items:flex-start;flex-direction:column}
    .disposisi-header-actions{width:100%}
    .disposisi-filter-main{grid-template-columns:minmax(0,1fr) auto}
    .disposisi-date-wrap{grid-column:1/-1}
    .disposisi-summary{grid-template-columns:repeat(2,minmax(0,1fr))}
    .archive-status-options{grid-template-columns:repeat(3,minmax(0,1fr))}
}

@media(max-width:767px){
    .disposisi-filter-status-row{width:100%}
    .disposisi-filter-status-row .archive-status-dropdown{max-width:none}
    .disposisi-page{padding-bottom:8px}
    .disposisi-header-main{align-items:flex-start}
    .disposisi-header-icon{width:44px;height:44px;flex-basis:44px}
    .disposisi-header-title{font-size:23px}
    .disposisi-header-subtitle{font-size:9.5px}
    .disposisi-header-actions{display:grid;grid-template-columns:1fr 1fr;gap:6px}
    .disposisi-top-button.primary{grid-column:1/-1}
    .disposisi-filter-card{padding:10px}
    .disposisi-filter-main{grid-template-columns:1fr;gap:7px}
    .disposisi-date-wrap{grid-column:auto}
    .disposisi-filter-action{width:100%}
    .disposisi-filter-button{flex:1}
    .disposisi-summary{grid-template-columns:1fr 1fr;gap:7px}
    .disposisi-summary-card{padding:9px 10px}
    .disposisi-summary-value{font-size:16px}
    .disposisi-table-header{padding:12px}
    .disposisi-table-meta{display:none}
    .disposition-table-scroll{overflow:visible}
    .disposition-table{min-width:0}
    .disposition-table thead{display:none}
    .disposition-table,.disposition-table tbody,.disposition-table tr,.disposition-table td{display:block;width:100%}
    .disposition-table tbody tr{padding:9px 12px;border-bottom:1px solid #edf2f7}
    .disposition-table tbody td{display:grid;grid-template-columns:92px minmax(0,1fr);gap:9px;align-items:start;padding:5px 0;border:0;font-size:9px}
    .disposition-table tbody td::before{content:attr(data-label);color:#94a3b8;font-size:7px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
    .disposition-table tbody td.action-cell{display:flex;align-items:center;justify-content:space-between;padding-top:8px;margin-top:4px;border-top:1px solid #f1f5f9;text-align:left}
    .disposition-table tbody td.action-cell::before{content:attr(data-label)}
    .disposition-table .receiver-box{max-width:none}
    .disposition-table .cell-instruction{max-width:none}
    .archive-date-panel,.archive-status-panel{position:fixed;top:50%;left:50%;right:auto;width:calc(100vw - 20px);max-width:430px;max-height:calc(100vh - 20px);transform:translate(-50%,-50%)}
    .archive-date-months{grid-template-columns:1fr;max-height:55vh;overflow-y:auto}
    .archive-date-month{border-right:0;border-bottom:1px solid #edf2f7}
    .archive-date-month:last-child{border-bottom:0}
    .archive-status-options{grid-template-columns:repeat(2,minmax(0,1fr));max-height:55vh;overflow-y:auto}
    .archive-date-footer{position:sticky;bottom:0;background:#fafcff}
    .disposisi-status-trigger{min-height:46px}
}

@media(max-width:480px){
    .disposisi-header-actions{grid-template-columns:1fr}
    .disposisi-top-button.primary{grid-column:auto}
    .disposisi-summary-card{padding:9px}
    .disposisi-summary-icon{width:29px;height:29px;flex-basis:29px}
    .disposisi-table-title{font-size:11px}
    .disposisi-table-subtitle{font-size:7.5px}
    .archive-status-options{grid-template-columns:1fr}
    .archive-picker-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
}
</style>
@endpush

<div class="disposisi-page space-y-4 sm:space-y-5">

    {{-- HEADER --}}
    <div class="disposisi-header">
        <div class="disposisi-header-main">
            <div class="disposisi-header-icon">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h6m0 0l-3-3m3 3l-3 3M19 12h-6m0 0l3-3m-3 3l3 3"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 5h10a2 2 0 012 2v2M17 19H7a2 2 0 01-2-2v-2"/>
                </svg>
            </div>

            <div class="min-w-0">
                <h1 class="disposisi-header-title">Disposisi Surat</h1>
                <p class="disposisi-header-subtitle">Kelola dan pantau instruksi disposisi dari pimpinan ke unit kerja.</p>
            </div>
        </div>

        <div class="disposisi-header-actions">
            @if(Route::has('export.disposisi.excel'))
                <a href="{{ route('export.disposisi.excel', request()->query()) }}" class="disposisi-top-button excel">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 00.707.293l5.414 5.414a1 1 0 00.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Excel
                </a>
            @endif

            @if(Route::has('export.disposisi.pdf'))
                <a href="{{ route('export.disposisi.pdf', request()->query()) }}" target="_blank" rel="noopener noreferrer" class="disposisi-top-button pdf">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF
                </a>
            @endif

            @if(in_array($userRole, ['admin', 'pimpinan'], true) && Route::has('surat-masuk.index'))
                <a href="{{ route('surat-masuk.index') }}" title="Pilih surat masuk untuk membuat disposisi" class="disposisi-top-button primary">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Disposisi
                </a>
            @endif
        </div>
    </div>

    {{-- FILTER --}}
    <div class="disposisi-filter-card">
        <form id="filterForm" method="GET" action="{{ route('disposisi.index') }}" class="disposisi-filter-form">

            <div class="disposisi-filter-main">

                {{-- SEARCH --}}
                <div class="disposisi-search-wrap">
                    <div class="relative">
                        <div class="search-icon">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari nomor surat, penerima atau isi instruksi..."
                            autocomplete="off"
                            class="disposisi-input disposisi-search-input"
                        >
                    </div>
                </div>

                {{-- DATE --}}
                <div class="disposisi-date-wrap">
                    <div class="archive-date-picker">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5v12a2 2 0 002 2z"/>
                                </svg>
                            </div>

                            <input
                                type="text"
                                id="date-range"
                                value="{{ $dateRangeValue }}"
                                readonly
                                autocomplete="off"
                                placeholder="Pilih rentang tanggal..."
                                class="disposisi-input disposisi-date-input"
                            >

                            <div class="disposisi-date-tools">
                                <button
                                    type="button"
                                    id="clearDateRange"
                                    title="Hapus tanggal"
                                    aria-label="Hapus tanggal"
                                    class="disposisi-date-clear {{ $dateRangeValue ? '' : 'hidden' }}"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <input type="hidden" name="dari_tanggal" id="dari_tanggal" value="{{ $dariTanggal }}">
                        <input type="hidden" name="sampai_tanggal" id="sampai_tanggal" value="{{ $sampaiTanggal }}">

                        <div id="datePickerPanel" class="archive-date-panel hidden">
                            <div class="archive-date-header">
                                <button type="button" id="datePrev" class="archive-date-nav" aria-label="Bulan sebelumnya">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>

                                <div class="archive-date-header-title">Pilih Rentang Tanggal</div>

                                <button type="button" id="dateNext" class="archive-date-nav" aria-label="Bulan berikutnya">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>

                            <div id="datePickerContent" class="archive-date-months"></div>
                            <div id="monthPickerView" class="archive-picker-view hidden"></div>
                            <div id="yearPickerView" class="archive-picker-view hidden"></div>

                            <div class="archive-date-footer">
                                <div id="datePickerInfo" class="archive-date-footer-info">Pilih tanggal mulai</div>

                                <div class="archive-date-footer-actions">
                                    <button type="button" id="clearPickerButton" class="archive-date-btn secondary">Bersihkan</button>
                                    <button type="button" id="applyPickerButton" class="archive-date-btn primary">Terapkan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BUTTON --}}
                <div class="disposisi-filter-action">
                    <div class="contents">
                        <button type="submit" class="disposisi-filter-button">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 00-.293.707l-6.414 6.414a1 1 0 00-.293.707L13 17v4l-4-4v-4.293a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filter
                        </button>

                        @if($hasFilters)
                            <a href="{{ route('disposisi.index') }}" title="Reset Filter" aria-label="Reset Filter" class="disposisi-reset-button">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- STATUS DISPOSISI --}}
            <div class="disposisi-filter-status-row">
                <div class="archive-status-dropdown">

                    <button
                        type="button"
                        id="statusDropdownButton"
                        class="disposisi-status-trigger"
                        aria-expanded="false"
                    >
                        <div class="disposisi-status-left">
                            <div class="disposisi-status-icon">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z"/>
                                </svg>
                            </div>

                            <div class="min-w-0">
                                <div class="disposisi-status-title">Status Disposisi</div>
                                <div id="statusSummary" class="disposisi-status-subtitle">Semua status</div>
                            </div>
                        </div>

                        <div class="disposisi-status-meta">
                            <span id="statusCount" class="disposisi-status-count">
                                {{ count($selectedStatus) }} dipilih
                            </span>

                            <svg id="statusDropdownIcon" class="h-4 w-4 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <div id="statusDropdownPanel" class="archive-status-panel hidden">
                        <div class="archive-status-head">
                            <div class="archive-status-head-title">
                                <strong>Pilih Status</strong>
                                <span>Satu atau beberapa status dapat dipilih</span>
                            </div>

                            <span id="statusPanelCount" class="archive-status-count">
                                {{ count($selectedStatus) }} dipilih
                            </span>
                        </div>

                        <div class="archive-status-actions">
                            <button type="button" id="selectAllStatus" class="archive-status-action">Pilih Semua</button>
                            <button type="button" id="clearAllStatus" class="archive-status-action muted">Batalkan</button>
                        </div>

                        <div class="archive-status-options">
                            @foreach($statusOptions as $value => $label)
                                <label class="archive-status-item">
                                    <input
                                        type="checkbox"
                                        name="status[]"
                                        value="{{ $value }}"
                                        class="status-checkbox"
                                        @checked(in_array($value, $selectedStatus, true))
                                    >

                                    <span class="archive-status-dot {{ $value }}"></span>
                                    <span class="archive-status-item-label">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="archive-status-footer">
                            <button type="button" id="closeStatusDropdown" class="archive-status-close">Selesai</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- RINGKASAN --}}
    <div class="disposisi-summary">
        <div class="disposisi-summary-card">
            <div class="disposisi-summary-icon blue">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 4h10a2 2 0 012 2v12a2 2 0 01-2 2H7a2 2 0 01-2-2V6a2 2 0 012-2z"/>
                    <path stroke-linecap="round" d="M8.5 9h7M8.5 13h7M8.5 17h4"/>
                </svg>
            </div>

            <div>
                <div class="disposisi-summary-label">Total</div>
                <div class="disposisi-summary-value">{{ $statTotal }}</div>
                <div class="disposisi-summary-note">Semua disposisi</div>
            </div>
        </div>

        <div class="disposisi-summary-card">
            <div class="disposisi-summary-icon amber">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="8.5" stroke-width="1.8"/>
                    <path stroke-linecap="round" d="M12 7.5v5l3 2"/>
                </svg>
            </div>

            <div>
                <div class="disposisi-summary-label">Menunggu</div>
                <div class="disposisi-summary-value">{{ $statMenunggu }}</div>
                <div class="disposisi-summary-note">Belum ditindaklanjuti</div>
            </div>
        </div>

        <div class="disposisi-summary-card">
            <div class="disposisi-summary-icon purple">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h10M7 12h7M7 17h4"/>
                    <circle cx="18" cy="17" r="3" stroke-width="1.8"/>
                </svg>
            </div>

            <div>
                <div class="disposisi-summary-label">Diproses</div>
                <div class="disposisi-summary-value">{{ $statDiproses }}</div>
                <div class="disposisi-summary-note">Sedang ditangani</div>
            </div>
        </div>

        <div class="disposisi-summary-card">
            <div class="disposisi-summary-icon green">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="8.5" stroke-width="1.8"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12.5l2.5 2.5L16 9"/>
                </svg>
            </div>

            <div>
                <div class="disposisi-summary-label">Selesai</div>
                <div class="disposisi-summary-value">{{ $statSelesai }}</div>
                <div class="disposisi-summary-note">Telah ditindaklanjuti</div>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="disposisi-table-card">
        <div class="disposisi-table-header">
            <div class="disposisi-table-title-wrap">
                <div class="disposisi-table-icon">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 5h14M5 10h14M5 15h9M5 20h6"/>
                    </svg>
                </div>

                <div>
                    <h2 class="disposisi-table-title">Daftar Disposisi</h2>
                    <p class="disposisi-table-subtitle">Daftar instruksi disposisi sesuai filter yang dipilih.</p>
                </div>
            </div>

            <div class="disposisi-table-meta">
                <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                {{ isset($disposisis) && method_exists($disposisis, 'total') ? $disposisis->total() : $statTotal }} disposisi
            </div>
        </div>

        <div class="disposition-table-wrapper">
            <div class="disposition-table-scroll">
                <table class="disposition-table">
                    <thead>
                        <tr>
                            <th>No. Surat</th>
                            <th>Tanggal Disposisi</th>
                            <th>Tujuan / Penerima</th>
                            <th>Isi Instruksi</th>
                            <th>Status</th>
                            <th>Batas Waktu</th>
                            <th class="action-cell">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($disposisis ?? [] as $d)
                            @php
                                $status = strtolower(trim((string) ($d->status ?? 'menunggu')));
                                $badgeClass = $statusBadgeClasses[$status] ?? 'border-slate-200 bg-slate-100 text-slate-600';
                                $nomorSurat = $getNomorSurat($d);
                                $penerimaData = $getPenerima($d);
                                $instruksi = $getInstruksi($d);
                                $tanggalDisposisi = $getTanggalDisposisi($d);
                                $batasWaktu = $getBatasWaktu($d);
                                $isLate = false;

                                if ($batasWaktu !== '-') {
                                    try {
                                        $tanggalBatas = data_get($d, 'batas_waktu')
                                            ?? data_get($d, 'tanggal_batas');

                                        $isLate = \Carbon\Carbon::parse($tanggalBatas)->isPast()
                                            && $status !== 'selesai';
                                    } catch (\Throwable $e) {
                                        $isLate = false;
                                    }
                                }
                            @endphp

                            <tr>
                                <td class="cell-number" data-label="No. Surat">
                                    <span class="inline-block max-w-[190px] truncate" title="{{ $nomorSurat }}">
                                        {{ $nomorSurat }}
                                    </span>
                                </td>

                                <td class="cell-date" data-label="Tanggal Disposisi">
                                    {{ $tanggalDisposisi }}
                                </td>

                                <td data-label="Tujuan / Penerima">
                                    @if($penerimaData['nama'] !== '-')
                                        <div
                                            class="receiver-box"
                                            title="{{ $penerimaData['nama'] }}{{ $penerimaData['jabatan'] ? ' - '.$penerimaData['jabatan'] : '' }}"
                                        >
                                            <span class="receiver-name">{{ $penerimaData['nama'] }}</span>

                                            @if($penerimaData['jabatan'])
                                                <span class="receiver-position">{{ $penerimaData['jabatan'] }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                                <td class="cell-instruction" data-label="Isi Instruksi" title="{{ $instruksi }}">
                                    {{ $instruksi }}
                                </td>

                                <td class="cell-status" data-label="Status">
                                    <span class="status-badge {{ $badgeClass }}">
                                        {{ $statusOptions[$status] ?? ucfirst($status) }}
                                    </span>
                                </td>

                                <td class="cell-deadline" data-label="Batas Waktu">
                                    @if($batasWaktu !== '-')
                                        <span class="{{ $isLate ? 'font-semibold text-rose-600' : '' }}">
                                            {{ $batasWaktu }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                                <td class="action-cell" data-label="Aksi">
                                    <div class="action-buttons">

                                        @if(Route::has('disposisi.show'))
                                            <a
                                                href="{{ route('disposisi.show', $d) }}"
                                                title="Lihat Detail"
                                                aria-label="Lihat detail disposisi"
                                                class="action-button detail"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0a3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7z"/>
                                                </svg>
                                            </a>
                                        @endif

                                        @if(in_array($userRole, ['admin', 'pimpinan'], true) && Route::has('disposisi.edit'))
                                            <a
                                                href="{{ route('disposisi.edit', $d) }}"
                                                title="Ubah Disposisi"
                                                aria-label="Ubah disposisi"
                                                class="action-button edit"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif

                                        @if(in_array($userRole, ['admin', 'pimpinan'], true) && Route::has('disposisi.destroy'))
                                            <form action="{{ route('disposisi.destroy', $d) }}" method="POST" class="delete-form inline">
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="button"
                                                    title="Hapus Disposisi"
                                                    aria-label="Hapus disposisi"
                                                    class="action-button delete delete-btn"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 01-1-1h-4a1 1 0 01-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                    </div>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="disposition-table-empty">
                                    <div class="flex flex-col items-center justify-center">

                                        <div class="disposisi-empty-icon">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-1.414 0l-2.414-2.414A1 1 0 006.586 13H4"/>
                                            </svg>
                                        </div>

                                        <p class="disposisi-empty-title">Belum ada data disposisi</p>

                                        <p class="disposisi-empty-text">
                                            @if($hasFilters)
                                                Tidak ada disposisi yang sesuai dengan filter yang digunakan.
                                            @else
                                                Belum ada data disposisi yang tersimpan.
                                            @endif
                                        </p>

                                        @if($hasFilters)
                                            <a href="{{ route('disposisi.index') }}" class="disposisi-empty-button">
                                                Reset Filter
                                            </a>
                                        @elseif(in_array($userRole, ['admin', 'pimpinan'], true) && Route::has('surat-masuk.index'))
                                            <a href="{{ route('surat-masuk.index') }}" class="disposisi-empty-button">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Buat Disposisi Baru
                                            </a>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($disposisis) && method_exists($disposisis, 'hasPages') && $disposisis->hasPages())
                <div class="disposisi-pagination">
                    {{ $disposisis->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    'use strict';

    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const weekdays = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];

    const dateInput = document.getElementById('date-range');
    const dariInput = document.getElementById('dari_tanggal');
    const sampaiInput = document.getElementById('sampai_tanggal');
    const clearDateRange = document.getElementById('clearDateRange');
    const datePanel = document.getElementById('datePickerPanel');
    const dateContent = document.getElementById('datePickerContent');
    const monthPickerView = document.getElementById('monthPickerView');
    const yearPickerView = document.getElementById('yearPickerView');
    const dateInfo = document.getElementById('datePickerInfo');
    const datePrev = document.getElementById('datePrev');
    const dateNext = document.getElementById('dateNext');
    const applyPickerButton = document.getElementById('applyPickerButton');
    const clearPickerButton = document.getElementById('clearPickerButton');

    let tempStart = parseDate(dariInput?.value || '');
    let tempEnd = parseDate(sampaiInput?.value || '');
    let viewDate = cloneDate(tempStart) || cloneDate(tempEnd) || today();

    viewDate = new Date(viewDate.getFullYear(), viewDate.getMonth(), 1);

    let pickerView = 'calendar';
    let pickerYear = viewDate.getFullYear();
    let pickerMonth = viewDate.getMonth();

    function today(){
        const date = new Date();
        date.setHours(0,0,0,0);
        return date;
    }

    function pad(number){
        return String(number).padStart(2,'0');
    }

    function cloneDate(date){
        return date ? new Date(date.getFullYear(),date.getMonth(),date.getDate()) : null;
    }

    function parseDate(value){
        if(!value) return null;

        const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})$/);

        if(!match) return null;

        const year = Number(match[1]);
        const month = Number(match[2]) - 1;
        const day = Number(match[3]);

        const date = new Date(year,month,day);

        if(
            date.getFullYear() !== year ||
            date.getMonth() !== month ||
            date.getDate() !== day
        ){
            return null;
        }

        date.setHours(0,0,0,0);

        return date;
    }

    function dateKey(date){
        return date
            ? `${date.getFullYear()}-${pad(date.getMonth()+1)}-${pad(date.getDate())}`
            : '';
    }

    function formatDisplay(date){
        return date
            ? `${pad(date.getDate())}/${pad(date.getMonth()+1)}/${date.getFullYear()}`
            : '';
    }

    function formatRange(start,end){
        return start && end
            ? `${formatDisplay(start)} - ${formatDisplay(end)}`
            : '';
    }

    function isSameDay(first,second){
        return !!(
            first &&
            second &&
            dateKey(first) === dateKey(second)
        );
    }

    function isBefore(first,second){
        return dateKey(first) < dateKey(second);
    }

    function isAfter(first,second){
        return dateKey(first) > dateKey(second);
    }

    function updateInputDisplay(){
        const start = parseDate(dariInput.value);
        const end = parseDate(sampaiInput.value);

        if(start && end){
            dateInput.value = formatRange(start,end);
            clearDateRange.classList.remove('hidden');
            clearDateRange.classList.add('flex');
        }else{
            dateInput.value = '';
            clearDateRange.classList.remove('flex');
            clearDateRange.classList.add('hidden');
        }
    }

    function updateDateInfo(){
        if(tempStart && tempEnd){
            dateInfo.textContent = formatRange(tempStart,tempEnd);
        }else if(tempStart){
            dateInfo.textContent = `${formatDisplay(tempStart)} - pilih tanggal akhir`;
        }else{
            dateInfo.textContent = 'Pilih tanggal mulai';
        }
    }

    function buildDayButton(date,otherMonth){
        const classes = ['archive-calendar-day'];

        if(otherMonth) classes.push('other-month');
        if(isSameDay(date,today())) classes.push('today');

        if(
            tempStart &&
            tempEnd &&
            !isBefore(date,tempStart) &&
            !isAfter(date,tempEnd)
        ){
            classes.push('in-range');
        }

        if(isSameDay(date,tempStart)) classes.push('start-date');
        if(isSameDay(date,tempEnd)) classes.push('end-date');

        return `<button type="button" class="${classes.join(' ')}" data-date="${dateKey(date)}">${date.getDate()}</button>`;
    }

    function renderCalendar(year,month){
        let html = `<div class="archive-date-month">
            <div class="archive-month-header">
                <button type="button" data-action="month" data-month="${month}" data-year="${year}">
                    ${months[month]}
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <button type="button" data-action="year" data-month="${month}" data-year="${year}">
                    ${year}
                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            <div class="archive-month-grid">`;

        weekdays.forEach(day => {
            html += `<div class="archive-calendar-weekday">${day}</div>`;
        });

        const firstDay = new Date(year,month,1);
        const firstWeekday = (firstDay.getDay()+6)%7;
        const daysInMonth = new Date(year,month+1,0).getDate();
        const daysInPreviousMonth = new Date(year,month,0).getDate();

        for(let index = firstWeekday - 1; index >= 0; index--){
            html += buildDayButton(
                new Date(year,month-1,daysInPreviousMonth-index),
                true
            );
        }

        for(let day = 1; day <= daysInMonth; day++){
            html += buildDayButton(
                new Date(year,month,day),
                false
            );
        }

        const totalDays = firstWeekday + daysInMonth;
        const remaining = (7 - totalDays % 7) % 7;

        for(let day = 1; day <= remaining; day++){
            html += buildDayButton(
                new Date(year,month+1,day),
                true
            );
        }

        return html + '</div></div>';
    }

    function bindCalendarEvents(){
        dateContent.querySelectorAll('[data-date]').forEach(button => {
            button.addEventListener('click',function(event){
                event.preventDefault();
                event.stopPropagation();
                selectDate(parseDate(this.dataset.date));
            });
        });

        dateContent.querySelectorAll('[data-action="month"]').forEach(button => {
            button.addEventListener('click',function(event){
                event.preventDefault();
                event.stopPropagation();

                pickerMonth = Number(this.dataset.month);
                pickerYear = Number(this.dataset.year);
                pickerView = 'month';

                renderPicker();
            });
        });

        dateContent.querySelectorAll('[data-action="year"]').forEach(button => {
            button.addEventListener('click',function(event){
                event.preventDefault();
                event.stopPropagation();

                pickerMonth = Number(this.dataset.month);
                pickerYear = Number(this.dataset.year);
                pickerView = 'year';

                renderPicker();
            });
        });
    }

    function renderCalendarView(){
        dateContent.classList.remove('hidden');
        monthPickerView.classList.add('hidden');
        yearPickerView.classList.add('hidden');

        const firstYear = viewDate.getFullYear();
        const firstMonth = viewDate.getMonth();
        const secondDate = new Date(firstYear,firstMonth+1,1);

        dateContent.innerHTML =
            renderCalendar(firstYear,firstMonth) +
            renderCalendar(secondDate.getFullYear(),secondDate.getMonth());

        bindCalendarEvents();
        updateDateInfo();
    }

    function renderMonthPicker(){
        dateContent.classList.add('hidden');
        yearPickerView.classList.add('hidden');
        monthPickerView.classList.remove('hidden');

        monthPickerView.innerHTML = `
            <div class="archive-picker-toolbar">
                <span>${pickerYear}</span>
            </div>

            <div class="archive-picker-grid">
                ${months.map((month,index) => `
                    <button
                        type="button"
                        class="archive-picker-item ${index === pickerMonth ? 'active' : ''}"
                        data-picker-month="${index}"
                    >
                        ${month}
                    </button>
                `).join('')}
            </div>
        `;

        monthPickerView.querySelectorAll('[data-picker-month]').forEach(button => {
            button.addEventListener('click',function(event){
                event.preventDefault();
                event.stopPropagation();

                const month = Number(this.dataset.pickerMonth);

                viewDate = new Date(pickerYear,month,1);
                pickerMonth = month;
                pickerView = 'calendar';

                renderPicker();
            });
        });
    }

    function renderYearPicker(){
        dateContent.classList.add('hidden');
        monthPickerView.classList.add('hidden');
        yearPickerView.classList.remove('hidden');

        const startYear = Math.floor((pickerYear-2000)/12)*12+2000;
        const endYear = startYear + 11;

        let html = `
            <div class="archive-year-toolbar">
                <div class="archive-year-nav">
                    <button type="button" id="yearPrev">‹</button>
                </div>

                <span class="archive-year-range">${startYear} - ${endYear}</span>

                <div class="archive-year-nav">
                    <button type="button" id="yearNext">›</button>
                </div>
            </div>

            <div class="archive-picker-grid">
        `;

        for(let year = startYear; year <= endYear; year++){
            html += `
                <button
                    type="button"
                    class="archive-picker-item ${year === pickerYear ? 'active' : ''}"
                    data-picker-year="${year}"
                >
                    ${year}
                </button>
            `;
        }

        yearPickerView.innerHTML = html + '</div>';

        document.getElementById('yearPrev')?.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();

            pickerYear = startYear - 1;
            renderYearPicker();
        });

        document.getElementById('yearNext')?.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();

            pickerYear = endYear + 1;
            renderYearPicker();
        });

        yearPickerView.querySelectorAll('[data-picker-year]').forEach(button => {
            button.addEventListener('click',function(event){
                event.preventDefault();
                event.stopPropagation();

                pickerYear = Number(this.dataset.pickerYear);
                viewDate = new Date(pickerYear,pickerMonth,1);
                pickerView = 'calendar';

                renderPicker();
            });
        });
    }

    function renderPicker(){
        if(pickerView === 'month'){
            datePrev.style.visibility = 'hidden';
            dateNext.style.visibility = 'hidden';
            renderMonthPicker();
            return;
        }

        if(pickerView === 'year'){
            datePrev.style.visibility = 'hidden';
            dateNext.style.visibility = 'hidden';
            renderYearPicker();
            return;
        }

        datePrev.style.visibility = 'visible';
        dateNext.style.visibility = 'visible';

        renderCalendarView();
    }

    function selectDate(date){
        if(!date) return;

        if(!tempStart || tempEnd){
            tempStart = cloneDate(date);
            tempEnd = null;
        }else if(isBefore(date,tempStart)){
            tempEnd = cloneDate(tempStart);
            tempStart = cloneDate(date);
        }else{
            tempEnd = cloneDate(date);
        }

        viewDate = new Date(date.getFullYear(),date.getMonth(),1);
        pickerYear = date.getFullYear();
        pickerMonth = date.getMonth();
        pickerView = 'calendar';

        renderPicker();
        updateDateInfo();
    }

    function openDatePicker(){
        closeStatusDropdownPanel();

        tempStart = parseDate(dariInput.value);
        tempEnd = parseDate(sampaiInput.value);

        viewDate = cloneDate(tempStart) || cloneDate(tempEnd) || today();
        viewDate = new Date(viewDate.getFullYear(),viewDate.getMonth(),1);

        pickerYear = viewDate.getFullYear();
        pickerMonth = viewDate.getMonth();
        pickerView = 'calendar';

        datePanel.classList.remove('hidden');

        renderPicker();
        updateDateInfo();
    }

    function closeDatePicker(){
        datePanel.classList.add('hidden');
        pickerView = 'calendar';
    }

    function showWarning(title,text){
        if(typeof Swal !== 'undefined'){
            Swal.fire({
                icon:'warning',
                title:title,
                text:text,
                confirmButtonText:'Mengerti',
                confirmButtonColor:'#2563eb',
                customClass:{
                    popup:'rounded-2xl',
                    confirmButton:'rounded-xl text-xs font-semibold px-4 py-2.5'
                }
            });
        }else{
            alert(text);
        }
    }

    function applyDateRange(){
        if(!tempStart || !tempEnd){
            showWarning(
                'Tanggal belum lengkap',
                'Pilih tanggal mulai dan tanggal akhir terlebih dahulu.'
            );
            return;
        }

        if(isAfter(tempStart,tempEnd)){
            const oldStart = cloneDate(tempStart);
            tempStart = cloneDate(tempEnd);
            tempEnd = oldStart;
        }

        dariInput.value = dateKey(tempStart);
        sampaiInput.value = dateKey(tempEnd);

        updateInputDisplay();
        closeDatePicker();
    }

    function clearDateValue(){
        tempStart = null;
        tempEnd = null;

        dariInput.value = '';
        sampaiInput.value = '';
        dateInput.value = '';

        clearDateRange.classList.remove('flex');
        clearDateRange.classList.add('hidden');

        viewDate = new Date(
            today().getFullYear(),
            today().getMonth(),
            1
        );

        pickerYear = viewDate.getFullYear();
        pickerMonth = viewDate.getMonth();
        pickerView = 'calendar';

        renderPicker();
        updateDateInfo();
    }

    dateInput?.addEventListener('click',function(event){
        event.preventDefault();
        event.stopPropagation();

        datePanel.classList.contains('hidden')
            ? openDatePicker()
            : closeDatePicker();
    });

    clearDateRange?.addEventListener('click',function(event){
        event.preventDefault();
        event.stopPropagation();
        clearDateValue();
    });

    datePrev?.addEventListener('click',function(event){
        event.preventDefault();
        event.stopPropagation();

        if(pickerView !== 'calendar') return;

        viewDate = new Date(
            viewDate.getFullYear(),
            viewDate.getMonth()-1,
            1
        );

        pickerYear = viewDate.getFullYear();
        pickerMonth = viewDate.getMonth();

        renderPicker();
    });

    dateNext?.addEventListener('click',function(event){
        event.preventDefault();
        event.stopPropagation();

        if(pickerView !== 'calendar') return;

        viewDate = new Date(
            viewDate.getFullYear(),
            viewDate.getMonth()+1,
            1
        );

        pickerYear = viewDate.getFullYear();
        pickerMonth = viewDate.getMonth();

        renderPicker();
    });

    applyPickerButton?.addEventListener('click',function(event){
        event.preventDefault();
        event.stopPropagation();
        applyDateRange();
    });

    clearPickerButton?.addEventListener('click',function(event){
        event.preventDefault();
        event.stopPropagation();
        clearDateValue();
    });

    const statusDropdownButton = document.getElementById('statusDropdownButton');
    const statusDropdownPanel = document.getElementById('statusDropdownPanel');
    const statusDropdownIcon = document.getElementById('statusDropdownIcon');
    const statusCheckboxes = Array.from(
        document.querySelectorAll('.status-checkbox')
    );
    const statusCount = document.getElementById('statusCount');
    const statusPanelCount = document.getElementById('statusPanelCount');
    const statusSummary = document.getElementById('statusSummary');
    const selectAllStatus = document.getElementById('selectAllStatus');
    const clearAllStatus = document.getElementById('clearAllStatus');
    const closeStatusDropdown = document.getElementById('closeStatusDropdown');

    function openStatusDropdownPanel(){
        closeDatePicker();

        statusDropdownPanel.classList.remove('hidden');
        statusDropdownIcon.classList.add('rotate-180');

        statusDropdownButton?.setAttribute('aria-expanded','true');

        updateStatusSummary();
    }

    function closeStatusDropdownPanel(){
        statusDropdownPanel.classList.add('hidden');
        statusDropdownIcon.classList.remove('rotate-180');

        statusDropdownButton?.setAttribute('aria-expanded','false');
    }

    function updateStatusSummary(){
        const checked = statusCheckboxes
            .filter(checkbox => checkbox.checked)
            .map(checkbox => {
                const label = checkbox.parentElement?.querySelector(
                    '.archive-status-item-label'
                );

                return label?.textContent.trim() || '';
            })
            .filter(Boolean);

        const count = checked.length;

        statusCount.textContent = `${count} dipilih`;
        statusPanelCount.textContent = `${count} dipilih`;

        if(!count){
            statusSummary.textContent = 'Semua status';
            return;
        }

        if(count === 1){
            statusSummary.textContent = checked[0];
            return;
        }

        statusSummary.textContent =
            checked.slice(0,2).join(', ') +
            (count > 2 ? ` +${count-2}` : '');
    }

    statusDropdownButton?.addEventListener('click',function(event){
        event.preventDefault();
        event.stopPropagation();

        statusDropdownPanel.classList.contains('hidden')
            ? openStatusDropdownPanel()
            : closeStatusDropdownPanel();
    });

    statusCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change',updateStatusSummary);
    });

    selectAllStatus?.addEventListener('click',function(event){
        event.preventDefault();
        event.stopPropagation();

        statusCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
        });

        updateStatusSummary();
    });

    clearAllStatus?.addEventListener('click',function(event){
        event.preventDefault();
        event.stopPropagation();

        statusCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        updateStatusSummary();
    });

    closeStatusDropdown?.addEventListener('click',function(event){
        event.preventDefault();
        event.stopPropagation();
        closeStatusDropdownPanel();
    });

    document.addEventListener('click',function(event){
        const target = event.target;

        if(!(target instanceof Element)) return;

        if(!target.closest('.archive-date-picker')){
            closeDatePicker();
        }

        if(!target.closest('.archive-status-dropdown')){
            closeStatusDropdownPanel();
        }
    });

    document.getElementById('filterForm')?.addEventListener('submit',function(event){
        const start = parseDate(dariInput.value);
        const end = parseDate(sampaiInput.value);

        if(!start && !end) return;

        if(!start || !end){
            event.preventDefault();

            showWarning(
                'Rentang tanggal belum lengkap',
                'Pilih tanggal mulai dan tanggal akhir.'
            );

            return;
        }

        if(isAfter(start,end)){
            event.preventDefault();

            showWarning(
                'Rentang tanggal tidak valid',
                'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.'
            );
        }
    });

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();

            const form = this.closest('.delete-form');

            if(!form) return;

            if(typeof Swal !== 'undefined'){
                Swal.fire({
                    title:'Hapus Disposisi?',
                    text:'Data disposisi akan dipindahkan ke tempat sampah.',
                    icon:'warning',
                    showCancelButton:true,
                    confirmButtonColor:'#ef4444',
                    cancelButtonColor:'#64748b',
                    confirmButtonText:'Ya, Hapus!',
                    cancelButtonText:'Batal',
                    reverseButtons:true,
                    customClass:{
                        popup:'rounded-2xl',
                        confirmButton:'rounded-xl text-xs font-semibold px-4 py-2.5',
                        cancelButton:'rounded-xl text-xs font-semibold px-4 py-2.5'
                    }
                }).then(result => {
                    if(result.isConfirmed){
                        form.submit();
                    }
                });
            }else if(window.confirm('Yakin ingin menghapus disposisi ini?')){
                form.submit();
            }
        });
    });

    updateInputDisplay();
    updateDateInfo();
    updateStatusSummary();
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection
