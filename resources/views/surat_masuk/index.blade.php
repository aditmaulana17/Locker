@extends('layouts.app')

@section('title', 'Surat Masuk')

@section('content')

@php
use Illuminate\Support\Carbon;

$user = auth()->user();

$userRole = strtolower(trim((string) ($user->role ?? $user->jabatan ?? '')));
if ($userRole === 'staff') {
    $userRole = 'staf';
}

$canManage = in_array($userRole, ['admin', 'pimpinan'], true);

$selectedKategori = collect(request('kategori_id', []))
    ->filter(fn ($id) => is_scalar($id) && ctype_digit((string) $id))
    ->map(fn ($id) => (string) $id)
    ->unique()
    ->values()
    ->all();

$statusOptions = [
    'baru' => 'Baru',
    'diproses' => 'Diproses',
    'didisposisikan' => 'Didisposisikan',
    'selesai' => 'Selesai',
    'diarsipkan' => 'Diarsipkan',
];

$selectedStatus = collect(request('status', []))
    ->filter(fn ($status) => is_scalar($status))
    ->map(fn ($status) => strtolower(trim((string) $status)))
    ->filter(fn ($status) => array_key_exists($status, $statusOptions))
    ->unique()
    ->values()
    ->all();

$statusBadgeClasses = [
    'baru' => 'bg-blue-50 text-blue-700 border-blue-200',
    'diproses' => 'bg-amber-50 text-amber-700 border-amber-200',
    'didisposisikan' => 'bg-purple-50 text-purple-700 border-purple-200',
    'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'diarsipkan' => 'bg-slate-100 text-slate-700 border-slate-200',
];

$dariTanggal = request('dari_tanggal');
$sampaiTanggal = request('sampai_tanggal');
$visibleDateRange = '';

if ($dariTanggal && $sampaiTanggal) {
    try {
        $start = Carbon::createFromFormat('Y-m-d', $dariTanggal);
        $end = Carbon::createFromFormat('Y-m-d', $sampaiTanggal);
        $visibleDateRange = $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');
    } catch (\Throwable $e) {
        $visibleDateRange = '';
    }
} elseif ($dariTanggal) {
    try {
        $visibleDateRange = Carbon::createFromFormat('Y-m-d', $dariTanggal)->format('d/m/Y');
    } catch (\Throwable $e) {
        $visibleDateRange = '';
    }
} elseif ($sampaiTanggal) {
    try {
        $visibleDateRange = Carbon::createFromFormat('Y-m-d', $sampaiTanggal)->format('d/m/Y');
    } catch (\Throwable $e) {
        $visibleDateRange = '';
    }
}

$hasFilters = request()->filled('search')
    || !empty($selectedKategori)
    || !empty($selectedStatus)
    || request()->filled('dari_tanggal')
    || request()->filled('sampai_tanggal');
@endphp

@push('styles')
<style>
.archive-filter-input{border:2px solid #94a3b8!important;background:#fff!important;box-shadow:0 1px 2px rgba(15,23,42,.04)}.archive-filter-input:hover{border-color:#64748b!important}.archive-filter-input:focus{border-color:#2563eb!important;background:#fff!important;box-shadow:0 0 0 3px rgba(37,99,235,.12)!important;outline:none!important}.date-range-wrapper{position:relative}.date-range-input{cursor:pointer}

.custom-date-picker,.custom-picker-panel{border:2px solid #64748b;background:#fff;box-shadow:0 24px 70px rgba(15,23,42,.18),0 8px 25px rgba(15,23,42,.08)}.custom-date-picker{position:fixed;z-index:999999;width:720px;max-width:calc(100vw - 20px);overflow:hidden;border-radius:16px;font-family:inherit}.custom-date-picker.hidden,.custom-picker-panel.hidden,.archive-filter-menu.hidden{display:none!important}.custom-date-picker-header{display:flex;align-items:center;justify-content:space-between;padding:10px 13px;border-bottom:2px solid #cbd5e1}.custom-date-picker-title{color:#334155;font-size:12px;font-weight:800}.custom-date-picker-close,.custom-picker-panel-close{display:inline-flex;align-items:center;justify-content:center;border:0;background:#f8fafc;color:#64748b;cursor:pointer}.custom-date-picker-close{width:30px;height:30px;border-radius:8px;font-size:18px}.custom-date-picker-close:hover,.custom-picker-panel-close:hover{background:#f1f5f9;color:#ef4444}.custom-date-picker-calendars{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.custom-calendar{padding:13px 15px 11px}.custom-calendar+.custom-calendar{border-left:2px solid #cbd5e1}.custom-calendar-head{display:flex;align-items:center;justify-content:space-between;gap:5px;min-height:38px;margin-bottom:5px}.custom-calendar-month-buttons{display:flex;align-items:center;justify-content:center;flex:1;gap:3px}.custom-calendar-nav{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;flex:0 0 32px;border:0;border-radius:8px;background:transparent;color:#475569;font-size:21px;line-height:1;cursor:pointer}.custom-calendar-nav:hover{background:#eff6ff;color:#2563eb}.custom-calendar-month,.custom-calendar-year{position:relative;display:inline-flex;align-items:center;justify-content:center;gap:5px;min-height:32px;border:1px solid #cbd5e1;border-radius:8px;padding:6px 9px;background:#f8fafc;color:#334155;font-family:inherit;font-size:11px;font-weight:800;cursor:pointer}.custom-calendar-month:hover,.custom-calendar-year:hover{border-color:#93c5fd;background:#eff6ff;color:#2563eb}.custom-calendar-month::after,.custom-calendar-year::after{content:'';width:6px;height:6px;margin-top:-3px;border-right:1.5px solid currentColor;border-bottom:1.5px solid currentColor;transform:rotate(45deg)}.custom-calendar-weekdays,.custom-calendar-days{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:2px}.custom-calendar-weekdays{margin-bottom:3px}.custom-calendar-weekday{display:flex;align-items:center;justify-content:center;height:26px;color:#94a3b8;font-size:9px;font-weight:800}.custom-calendar-day{display:flex;align-items:center;justify-content:center;height:34px;border:0;border-radius:7px;background:transparent;color:#475569;font-family:inherit;font-size:10px;font-weight:600;cursor:pointer}.custom-calendar-day:hover{background:#eff6ff;color:#2563eb}.custom-calendar-day.other-month{color:#cbd5e1}.custom-calendar-day.today{box-shadow:inset 0 0 0 1px #93c5fd;color:#2563eb}.custom-calendar-day.in-range{border-radius:0;background:#eff6ff;color:#2563eb}.custom-calendar-day.range-start{border-radius:999px 0 0 999px;background:#2563eb;color:#fff}.custom-calendar-day.range-end{border-radius:0 999px 999px 0;background:#2563eb;color:#fff}.custom-calendar-day.range-start.range-end{border-radius:999px}.custom-calendar-day.range-start:hover,.custom-calendar-day.range-end:hover{background:#1d4ed8;color:#fff}.custom-date-picker-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:9px 12px;border-top:2px solid #cbd5e1}.custom-date-picker-selected{min-width:0;color:#64748b;font-size:10px;font-weight:700}.custom-date-picker-actions{display:flex;align-items:center;gap:6px}.custom-date-picker-button{height:34px;border:1px solid #cbd5e1;border-radius:8px;padding:0 12px;background:#f8fafc;color:#475569;font-family:inherit;font-size:10px;font-weight:700;cursor:pointer}.custom-date-picker-button:hover{background:#f1f5f9}.custom-date-picker-button.apply{border-color:#2563eb;background:#2563eb;color:#fff}.custom-date-picker-button.apply:hover{background:#1d4ed8}

.custom-picker-panel{position:fixed;z-index:1000000;width:310px;max-width:calc(100vw - 20px);padding:12px;border-radius:14px}.custom-picker-panel-header{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px;padding-bottom:9px;border-bottom:2px solid #cbd5e1}.custom-picker-panel-title{flex:1;color:#334155;font-size:12px;font-weight:800;text-align:center}.custom-picker-panel-close{width:28px;height:28px;border-radius:7px;font-size:17px}.custom-picker-month-grid,.custom-picker-year-grid{display:grid;gap:7px}.custom-picker-month-grid{grid-template-columns:repeat(3,1fr)}.custom-picker-year-grid{grid-template-columns:repeat(4,1fr)}.custom-picker-option{display:flex;align-items:center;justify-content:center;min-height:40px;border:1px solid #cbd5e1;border-radius:9px;background:#fff;color:#475569;font-family:inherit;font-size:10px;font-weight:700;cursor:pointer}.custom-picker-option:hover{border-color:#93c5fd;background:#eff6ff;color:#2563eb}.custom-picker-option.active{border-color:#2563eb;background:#2563eb;color:#fff}.custom-picker-option.current{box-shadow:inset 0 0 0 1px #93c5fd}.custom-picker-option.active.current{box-shadow:none}.custom-picker-year-navigation{display:flex;align-items:center;gap:4px}.custom-picker-year-nav{display:inline-flex;align-items:center;justify-content:center;width:29px;height:29px;border:0;border-radius:7px;background:#f8fafc;color:#64748b;cursor:pointer}.custom-picker-year-nav:hover{background:#eff6ff;color:#2563eb}

.archive-filter-dropdown{position:relative;min-width:0}.archive-filter-trigger{display:flex;align-items:center;width:100%;min-height:52px;gap:10px;padding:8px 12px;border:2px solid #94a3b8;border-radius:12px;background:#fff;color:#334155;text-align:left;cursor:pointer;transition:border-color .15s ease,background .15s ease,box-shadow .15s ease}.archive-filter-trigger:hover{border-color:#64748b;background:#f8fafc}.archive-filter-trigger.is-open,.archive-filter-trigger:focus{outline:none;border-color:#2563eb;background:#f8fbff;box-shadow:0 0 0 3px rgba(37,99,235,.12)}.archive-filter-trigger.is-active{border-color:#2563eb;background:#f8fbff}.archive-filter-icon{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;flex:0 0 34px;border-radius:9px}.archive-filter-icon.category{background:#eff6ff;color:#2563eb}.archive-filter-icon.status{background:#fffbeb;color:#d97706}.archive-filter-trigger-content{min-width:0;flex:1}.archive-filter-trigger-title{display:block;color:#334155;font-size:12px;font-weight:700;line-height:1.2}.archive-filter-trigger-subtitle{display:block;margin-top:2px;overflow:hidden;color:#94a3b8;font-size:9px;line-height:1.2;text-overflow:ellipsis;white-space:nowrap}.archive-filter-count{display:inline-flex;align-items:center;justify-content:center;min-width:68px;min-height:23px;flex:0 0 auto;padding:0 8px;border-radius:999px;font-size:9px;font-weight:700;white-space:nowrap}.archive-filter-count.category{border:1px solid #bfdbfe;background:#eff6ff;color:#2563eb}.archive-filter-count.status{border:1px solid #fde68a;background:#fffbeb;color:#d97706}.archive-filter-chevron{display:inline-flex;align-items:center;justify-content:center;width:19px;height:19px;flex:0 0 19px;color:#64748b;transition:transform .2s ease}.archive-filter-trigger.is-open .archive-filter-chevron{transform:rotate(180deg)}.archive-filter-menu{position:absolute;top:calc(100% + 6px);right:0;left:0;z-index:9998;overflow:hidden;border:2px solid #64748b;border-radius:12px;background:#fff;box-shadow:0 20px 50px rgba(15,23,42,.14),0 5px 15px rgba(15,23,42,.08)}.archive-filter-menu-header{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:10px 12px;border-bottom:2px solid #cbd5e1;background:#f8fafc}.archive-filter-menu-heading{min-width:0}.archive-filter-menu-title{display:block;color:#334155;font-size:11px;font-weight:800}.archive-filter-menu-description{display:block;margin-top:2px;color:#94a3b8;font-size:9px}.archive-filter-menu-actions{display:flex;align-items:center;gap:4px;flex:0 0 auto}.archive-filter-action{border:0;border-radius:6px;padding:5px 7px;background:transparent;font-size:9px;font-weight:700;cursor:pointer}.archive-filter-action.select{color:#2563eb}.archive-filter-action.select:hover{background:#eff6ff}.archive-filter-action.clear{color:#64748b}.archive-filter-action.clear:hover{background:#f1f5f9}.archive-filter-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:7px;max-height:260px;overflow-y:auto;padding:10px}.archive-filter-options::-webkit-scrollbar{width:5px}.archive-filter-options::-webkit-scrollbar-track{background:#f8fafc}.archive-filter-options::-webkit-scrollbar-thumb{border-radius:999px;background:#94a3b8}.archive-filter-option{display:flex;align-items:center;min-width:0;min-height:40px;gap:8px;padding:7px 9px;border:1.5px solid #94a3b8;border-radius:9px;background:#fff;cursor:pointer;transition:border-color .15s ease,background .15s ease}.archive-filter-option:hover,.archive-filter-option.is-selected{border-color:#2563eb;background:#eff6ff}.archive-filter-option input{width:15px;height:15px;flex:0 0 15px;margin:0;accent-color:#2563eb;cursor:pointer}.archive-filter-option-text{min-width:0;overflow:hidden;color:#475569;font-size:10px;font-weight:600;text-overflow:ellipsis;white-space:nowrap}.archive-filter-option.is-selected .archive-filter-option-text{color:#1d4ed8}.archive-filter-menu-footer{display:flex;align-items:center;justify-content:space-between;gap:8px;padding:8px 12px;border-top:2px solid #cbd5e1}.archive-filter-footer-count,.archive-filter-footer-hint{font-size:9px}.archive-filter-footer-count{color:#64748b;font-weight:600}.archive-filter-footer-hint{color:#94a3b8}.archive-filter-empty{grid-column:1/-1;padding:18px 10px;color:#94a3b8;font-size:10px;text-align:center}

.archive-table-wrapper{overflow:hidden;border:2px solid #64748b;border-radius:14px;background:#fff;box-shadow:0 1px 3px rgba(15,23,42,.06),0 8px 24px rgba(15,23,42,.04)}.archive-table-scroll{overflow-x:auto}.archive-table{width:100%;min-width:1000px;border-collapse:collapse;border-spacing:0;background:#fff}.archive-table thead{background:#e2e8f0}.archive-table thead tr{border-bottom:2px solid #475569}.archive-table thead th{padding:11px 14px;border-right:1.5px solid #64748b;border-bottom:2px solid #475569;color:#334155;font-size:9px;font-weight:800;letter-spacing:.04em;line-height:1.3;text-align:left;text-transform:uppercase;vertical-align:middle;white-space:nowrap}.archive-table thead th:last-child{border-right:0;text-align:center}.archive-table tbody tr{background:#fff;transition:background-color .15s ease}.archive-table tbody tr:nth-child(even){background:#f8fafc}.archive-table tbody tr:hover{background:#eff6ff}.archive-table tbody td{padding:11px 14px;border-right:1px solid #94a3b8;border-bottom:1px solid #94a3b8;color:#475569;font-size:11px;line-height:1.4;vertical-align:middle}.archive-table tbody td:last-child{border-right:0;text-align:center}.archive-table tbody tr:last-child td{border-bottom:0}.archive-table .cell-date{color:#475569;font-weight:600;white-space:nowrap}.archive-table .cell-sender{color:#334155}.archive-table .cell-subject{color:#1e293b;font-weight:700}.archive-table .cell-category{color:#64748b}.archive-table .cell-status{white-space:nowrap}.archive-table .sender-badge{display:inline-block;max-width:220px;overflow:hidden;padding:4px 8px;border:1px solid #94a3b8;border-radius:7px;background:#f1f5f9;color:#334155;font-weight:600;text-overflow:ellipsis;vertical-align:middle;white-space:nowrap}.archive-table .status-badge{display:inline-flex;align-items:center;border-width:1px;border-radius:999px;padding:4px 9px;font-size:9px;font-weight:800}.archive-table .action-cell{width:120px;white-space:nowrap}.archive-table .action-buttons{display:inline-flex;align-items:center;justify-content:center;gap:2px}.archive-table .action-button{display:inline-flex;align-items:center;justify-content:center;width:29px;height:29px;border-radius:7px;transition:background .15s ease,color .15s ease}.archive-table .action-button.detail{color:#64748b}.archive-table .action-button.detail:hover{background:#dbeafe;color:#2563eb}.archive-table .action-button.edit{color:#64748b}.archive-table .action-button.edit:hover{background:#fef3c7;color:#d97706}.archive-table .action-button.delete{color:#64748b}.archive-table .action-button.delete:hover{background:#ffe4e6;color:#e11d48}.archive-table-empty{padding:42px 16px;text-align:center}

@media(max-width:767px){.custom-date-picker{top:50%;left:50%;width:calc(100vw - 16px);max-height:calc(100vh - 16px);overflow-y:auto;transform:translate(-50%,-50%)}.custom-date-picker-calendars{grid-template-columns:1fr}.custom-calendar+.custom-calendar{border-top:2px solid #cbd5e1;border-left:0}.custom-calendar-day{height:38px}.custom-picker-panel{top:50%!important;left:50%!important;width:calc(100vw - 24px);transform:translate(-50%,-50%)}.custom-date-picker-footer{position:sticky;bottom:0;background:#fff}.archive-filter-menu{position:fixed;top:50%;left:50%;right:auto;width:calc(100vw - 24px);max-width:430px;max-height:80vh;transform:translate(-50%,-50%)}.archive-filter-options{max-height:calc(80vh - 145px);grid-template-columns:1fr 1fr}body.date-picker-lock{overflow:hidden}.archive-table thead th,.archive-table tbody td{padding:10px 12px}}
@media(max-width:480px){.archive-filter-options{grid-template-columns:1fr}}
</style>
@endpush

<div class="space-y-3 sm:space-y-4">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">Surat Masuk</h1>
            <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Kelola dan pantau seluruh arsip surat masuk organisasi Anda.</p>
        </div>

        <div class="grid w-full grid-cols-3 gap-1.5 sm:flex sm:w-auto sm:gap-2">
            <a href="{{ route('export.surat-masuk.excel', request()->query()) }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-300 bg-emerald-50 px-2.5 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 sm:px-3.5">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Excel
            </a>

            <a href="{{ route('export.surat-masuk.pdf', request()->query()) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-300 bg-rose-50 px-2.5 py-2 text-xs font-semibold text-rose-700 shadow-sm transition hover:bg-rose-100 sm:px-3.5">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                PDF
            </a>

            @if($canManage)
                <a href="{{ route('surat-masuk.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-2.5 py-2 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 sm:px-4">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Surat Masuk
                </a>
            @endif
        </div>
    </div>

    <div class="rounded-xl border-2 border-slate-400 bg-white p-3 shadow-sm sm:p-4">
        <form id="filterForm" method="GET" action="{{ route('surat-masuk.index') }}" class="space-y-2.5">
            <div class="grid grid-cols-1 gap-2 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Cari perihal, nomor surat, atau pengirim..." autocomplete="off" class="archive-filter-input h-11 w-full rounded-xl pl-9 pr-3 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 sm:text-sm">
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="date-range-wrapper">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5v12a2 2 0 002 2z"/>
                                </svg>
                            </div>

                            <input type="text" id="date-range" value="{{ $visibleDateRange }}" readonly autocomplete="off" placeholder="Pilih rentang tanggal..." class="archive-filter-input date-range-input h-11 w-full rounded-xl pl-9 pr-10 text-xs font-medium text-slate-700 outline-none transition placeholder:text-slate-400 sm:text-sm" aria-label="Pilih rentang tanggal">

                            <button type="button" id="clearDateRange" title="Hapus tanggal" aria-label="Hapus tanggal" class="{{ $visibleDateRange ? 'flex' : 'hidden' }} absolute inset-y-0 right-0 z-20 w-10 items-center justify-center text-slate-400 transition hover:text-rose-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <input type="hidden" name="dari_tanggal" id="dari_tanggal" value="{{ $dariTanggal }}">
                        <input type="hidden" name="sampai_tanggal" id="sampai_tanggal" value="{{ $sampaiTanggal }}">
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="flex h-11 gap-1.5">
                        <button type="submit" class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-900 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:text-sm">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707v3.414L9 14V9.707a1 1 0 00-.293-.707L3.293 6.293A1 1 0 013 5.586V4z"/>
                            </svg>
                            Filter
                        </button>

                        @if($hasFilters)
                            <a href="{{ route('surat-masuk.index') }}" title="Reset Filter" aria-label="Reset Filter" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2 lg:grid-cols-2">
                <div class="archive-filter-dropdown" data-filter-dropdown="kategori">
                    <button type="button" class="archive-filter-trigger" data-dropdown-trigger aria-expanded="false" aria-haspopup="true">
                        <span class="archive-filter-icon category">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/>
                            </svg>
                        </span>
                        <span class="archive-filter-trigger-content">
                            <span class="archive-filter-trigger-title">Kategori Surat</span>
                            <span class="archive-filter-trigger-subtitle">Pilih satu atau beberapa kategori</span>
                        </span>
                        <span class="archive-filter-count category" data-filter-count>{{ count($selectedKategori) }} dipilih</span>
                        <span class="archive-filter-chevron">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6l6-6"/>
                            </svg>
                        </span>
                    </button>

                    <div class="archive-filter-menu hidden" data-dropdown-menu>
                        <div class="archive-filter-menu-header">
                            <div class="archive-filter-menu-heading">
                                <span class="archive-filter-menu-title">Pilih Kategori</span>
                                <span class="archive-filter-menu-description">Checkbox dapat dipilih lebih dari satu</span>
                            </div>
                            <div class="archive-filter-menu-actions">
                                <button type="button" class="archive-filter-action select" data-action="select-all">Pilih Semua</button>
                                <button type="button" class="archive-filter-action clear" data-action="clear-all">Batalkan</button>
                            </div>
                        </div>

                        <div class="archive-filter-options">
                            @forelse(($kategoris ?? collect()) as $kategori)
                                <label class="archive-filter-option">
                                    <input type="checkbox" name="kategori_id[]" value="{{ $kategori->id }}" class="kategori-checkbox" @checked(in_array((string) $kategori->id, $selectedKategori, true))>
                                    <span class="archive-filter-option-text" title="{{ $kategori->nama_kategori }}">{{ $kategori->nama_kategori }}</span>
                                </label>
                            @empty
                                <div class="archive-filter-empty">Belum ada kategori surat.</div>
                            @endforelse
                        </div>

                        <div class="archive-filter-menu-footer">
                            <span class="archive-filter-footer-count" data-footer-count>{{ count($selectedKategori) }} kategori dipilih</span>
                            <span class="archive-filter-footer-hint">Klik Filter untuk menerapkan</span>
                        </div>
                    </div>
                </div>

                <div class="archive-filter-dropdown" data-filter-dropdown="status">
                    <button type="button" class="archive-filter-trigger" data-dropdown-trigger aria-expanded="false" aria-haspopup="true">
                        <span class="archive-filter-icon status">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span class="archive-filter-trigger-content">
                            <span class="archive-filter-trigger-title">Status Surat</span>
                            <span class="archive-filter-trigger-subtitle">Pilih satu atau beberapa status</span>
                        </span>
                        <span class="archive-filter-count status" data-filter-count>{{ count($selectedStatus) }} dipilih</span>
                        <span class="archive-filter-chevron">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6l6-6"/>
                            </svg>
                        </span>
                    </button>

                    <div class="archive-filter-menu hidden" data-dropdown-menu>
                        <div class="archive-filter-menu-header">
                            <div class="archive-filter-menu-heading">
                                <span class="archive-filter-menu-title">Pilih Status</span>
                                <span class="archive-filter-menu-description">Checkbox dapat dipilih lebih dari satu</span>
                            </div>
                            <div class="archive-filter-menu-actions">
                                <button type="button" class="archive-filter-action select" data-action="select-all">Pilih Semua</button>
                                <button type="button" class="archive-filter-action clear" data-action="clear-all">Batalkan</button>
                            </div>
                        </div>

                        <div class="archive-filter-options">
                            @foreach($statusOptions as $value => $label)
                                <label class="archive-filter-option">
                                    <input type="checkbox" name="status[]" value="{{ $value }}" class="status-checkbox" @checked(in_array($value, $selectedStatus, true))>
                                    <span class="archive-filter-option-text">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="archive-filter-menu-footer">
                            <span class="archive-filter-footer-count" data-footer-count>{{ count($selectedStatus) }} status dipilih</span>
                            <span class="archive-filter-footer-hint">Klik Filter untuk menerapkan</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="archive-table-wrapper">
        <div class="archive-table-scroll">
            <table class="archive-table">
                <thead>
                    <tr>
                        <th>Nomor Agenda</th>
                        <th>Tanggal Terima</th>
                        <th>Pengirim</th>
                        <th>Perihal</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th class="action-cell">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse(($suratMasuks ?? collect()) as $surat)
                        @php
                            $status = strtolower(trim((string) ($surat->status ?? 'baru')));
                            $statusLabel = $statusOptions[$status] ?? ucfirst($status);
                            $badgeClass = $statusBadgeClasses[$status] ?? 'bg-slate-100 text-slate-600 border-slate-200';

                            $tanggalTerima = '-';
                            if ($surat->tanggal_terima) {
                                try {
                                    $tanggalTerima = Carbon::parse($surat->tanggal_terima)->format('d/m/Y');
                                } catch (\Throwable $e) {
                                    $tanggalTerima = '-';
                                }
                            }
                        @endphp

                        <tr>
                            <td>
                                <span class="font-semibold text-slate-700">
                                    {{ $surat->nomor_agenda ?? '-' }}
                                </span>
                            </td>

                            <td class="cell-date">{{ $tanggalTerima }}</td>

                            <td class="cell-sender">
                                <span class="sender-badge" title="{{ $surat->pengirim ?? '-' }}">
                                    {{ $surat->pengirim ?? '-' }}
                                </span>
                            </td>

                            <td class="cell-subject max-w-xs truncate" title="{{ $surat->perihal ?? '-' }}">
                                {{ $surat->perihal ?? '-' }}
                            </td>

                            <td class="cell-category">
                                {{ $surat->kategori?->nama_kategori ?? '-' }}
                            </td>

                            <td class="cell-status">
                                <span class="status-badge {{ $badgeClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <td class="action-cell">
                                <div class="action-buttons">
                                    <a href="{{ route('surat-masuk.show', $surat) }}" class="action-button detail" title="Lihat Detail & Disposisi" aria-label="Lihat detail surat">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0a3 3 0 006 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7z"/>
                                        </svg>
                                    </a>

                                    @if($canManage)
                                        <a href="{{ route('surat-masuk.edit', $surat) }}" class="action-button edit" title="Ubah Data" aria-label="Ubah data surat">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L11.828 15H9v-2.828L18.5 2.5z"/>
                                            </svg>
                                        </a>

                                        <form action="{{ route('surat-masuk.destroy', $surat) }}" method="POST" class="delete-form inline">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button" class="action-button delete delete-btn" title="Hapus Surat" aria-label="Hapus surat">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6m4-6v6"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16m-5-3H9a1 1 0 00-1 1v2h8V5a1 1 0 00-1-1z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="archive-table-empty">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-1.414 0l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>

                                    <p class="text-sm font-semibold text-slate-700 sm:text-base">
                                        Belum ada data surat masuk
                                    </p>

                                    <p class="mt-0.5 max-w-md px-4 text-center text-[11px] text-slate-400 sm:text-xs">
                                        @if($hasFilters)
                                            Tidak ada surat yang sesuai dengan filter yang digunakan.
                                        @else
                                            Belum ada data surat masuk yang tersimpan.
                                        @endif
                                    </p>

                                    @if($hasFilters)
                                        <a href="{{ route('surat-masuk.index') }}" class="mt-3 inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-[11px] font-semibold text-white transition hover:bg-slate-800">
                                            Reset Filter
                                        </a>
                                    @elseif($canManage)
                                        <a href="{{ route('surat-masuk.create') }}" class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-[11px] font-semibold text-white transition hover:bg-blue-700">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
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

        @if(isset($suratMasuks) && method_exists($suratMasuks, 'hasPages') && $suratMasuks->hasPages())
            <div class="border-t-2 border-slate-300 px-4 py-3 sm:px-6 sm:py-4">
                {{ $suratMasuks->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<div id="customDatePicker" class="custom-date-picker hidden">
    <div class="custom-date-picker-header">
        <div class="custom-date-picker-title">Pilih Rentang Tanggal</div>
        <button type="button" id="datePickerClose" class="custom-date-picker-close" aria-label="Tutup">&times;</button>
    </div>

    <div id="customDateCalendars" class="custom-date-picker-calendars"></div>

    <div class="custom-date-picker-footer">
        <div id="datePickerSelected" class="custom-date-picker-selected">
            Pilih tanggal awal
        </div>

        <div class="custom-date-picker-actions">
            <button type="button" id="datePickerClear" class="custom-date-picker-button">Bersihkan</button>
            <button type="button" id="datePickerApply" class="custom-date-picker-button apply">Terapkan</button>
        </div>
    </div>
</div>

<div id="customPickerPanel" class="custom-picker-panel hidden"></div>

@push('scripts')
<script>
(function(){
    'use strict';

    const MONTHS=['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const WEEKDAYS=['Sn','Sl','Rb','Km','Jm','Sb','Mg'];
    const MIN_YEAR=2000;
    const MAX_YEAR=new Date().getFullYear()+20;

    const dateInput=document.getElementById('date-range');
    const dariInput=document.getElementById('dari_tanggal');
    const sampaiInput=document.getElementById('sampai_tanggal');
    const clearButton=document.getElementById('clearDateRange');
    const picker=document.getElementById('customDatePicker');
    const calendars=document.getElementById('customDateCalendars');
    const panel=document.getElementById('customPickerPanel');
    const closePickerButton=document.getElementById('datePickerClose');
    const clearPickerButton=document.getElementById('datePickerClear');
    const applyPickerButton=document.getElementById('datePickerApply');
    const selectedLabel=document.getElementById('datePickerSelected');

    let selectedStart=parseDate(dariInput?.value||'');
    let selectedEnd=parseDate(sampaiInput?.value||'');
    let tempStart=selectedStart?cloneDate(selectedStart):null;
    let tempEnd=selectedEnd?cloneDate(selectedEnd):null;
    let viewLeft=selectedStart?new Date(selectedStart.getFullYear(),selectedStart.getMonth(),1):new Date();
    let panelSide='left';

    viewLeft.setDate(1);

    function cloneDate(date){
        return new Date(date.getFullYear(),date.getMonth(),date.getDate());
    }

    function parseDate(value){
        if(!value)return null;

        const match=String(value).match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if(!match)return null;

        const year=Number(match[1]);
        const month=Number(match[2])-1;
        const day=Number(match[3]);
        const date=new Date(year,month,day);

        if(date.getFullYear()!==year||date.getMonth()!==month||date.getDate()!==day){
            return null;
        }

        return date;
    }

    function pad(value){
        return String(value).padStart(2,'0');
    }

    function toISO(date){
        if(!date)return '';

        return [
            date.getFullYear(),
            pad(date.getMonth()+1),
            pad(date.getDate())
        ].join('-');
    }

    function formatDate(date){
        if(!date)return '';

        return [
            pad(date.getDate()),
            pad(date.getMonth()+1),
            date.getFullYear()
        ].join('/');
    }

    function sameDate(a,b){
        return !!(
            a &&
            b &&
            a.getFullYear()===b.getFullYear() &&
            a.getMonth()===b.getMonth() &&
            a.getDate()===b.getDate()
        );
    }

    function addMonths(date,amount){
        return new Date(
            date.getFullYear(),
            date.getMonth()+amount,
            1
        );
    }

    function isBetween(date,start,end){
        if(!date||!start||!end)return false;

        const value=toISO(date);

        return value>toISO(start)&&value<toISO(end);
    }

    function renderCalendars(){
        if(!calendars)return;

        calendars.innerHTML='';

        const leftDate=new Date(
            viewLeft.getFullYear(),
            viewLeft.getMonth(),
            1
        );

        const rightDate=addMonths(leftDate,1);

        calendars.appendChild(createCalendar(leftDate,'left'));
        calendars.appendChild(createCalendar(rightDate,'right'));

        updateSelectedLabel();
        requestAnimationFrame(positionPicker);
    }

    function createCalendar(date,side){
        const calendar=document.createElement('div');
        calendar.className='custom-calendar';

        const header=document.createElement('div');
        header.className='custom-calendar-head';

        const previous=document.createElement('button');
        previous.type='button';
        previous.className='custom-calendar-nav';
        previous.innerHTML='&#8249;';
        previous.setAttribute('aria-label','Bulan sebelumnya');

        const next=document.createElement('button');
        next.type='button';
        next.className='custom-calendar-nav';
        next.innerHTML='&#8250;';
        next.setAttribute('aria-label','Bulan berikutnya');

        const monthButtons=document.createElement('div');
        monthButtons.className='custom-calendar-month-buttons';

        const monthButton=document.createElement('button');
        monthButton.type='button';
        monthButton.className='custom-calendar-month';
        monthButton.textContent=MONTHS[date.getMonth()];

        const yearButton=document.createElement('button');
        yearButton.type='button';
        yearButton.className='custom-calendar-year';
        yearButton.textContent=String(date.getFullYear());

        monthButtons.append(monthButton,yearButton);
        header.append(previous,monthButtons,next);

        monthButton.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            showMonthPanel(date,monthButton,side);
        });

        yearButton.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            showYearPanel(date,yearButton,side);
        });

        previous.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            viewLeft=addMonths(viewLeft,-1);
            renderCalendars();
        });

        next.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            viewLeft=addMonths(viewLeft,1);
            renderCalendars();
        });

        calendar.appendChild(header);

        const weekdays=document.createElement('div');
        weekdays.className='custom-calendar-weekdays';

        WEEKDAYS.forEach(function(day){
            const element=document.createElement('div');
            element.className='custom-calendar-weekday';
            element.textContent=day;
            weekdays.appendChild(element);
        });

        calendar.appendChild(weekdays);

        const days=document.createElement('div');
        days.className='custom-calendar-days';

        const year=date.getFullYear();
        const month=date.getMonth();
        const firstDay=new Date(year,month,1).getDay();
        const mondayOffset=firstDay===0?6:firstDay-1;
        const daysInMonth=new Date(year,month+1,0).getDate();
        const daysInPreviousMonth=new Date(year,month,0).getDate();

        for(let index=0;index<42;index++){
            let dayNumber;
            let cellDate;
            let otherMonth=false;

            if(index<mondayOffset){
                dayNumber=daysInPreviousMonth-mondayOffset+index+1;
                cellDate=new Date(year,month-1,dayNumber);
                otherMonth=true;
            }else if(index>=mondayOffset+daysInMonth){
                dayNumber=index-mondayOffset-daysInMonth+1;
                cellDate=new Date(year,month+1,dayNumber);
                otherMonth=true;
            }else{
                dayNumber=index-mondayOffset+1;
                cellDate=new Date(year,month,dayNumber);
            }

            const button=document.createElement('button');
            button.type='button';
            button.className='custom-calendar-day';
            button.textContent=String(dayNumber);

            if(otherMonth)button.classList.add('other-month');
            if(sameDate(cellDate,new Date()))button.classList.add('today');
            if(tempStart&&tempEnd&&isBetween(cellDate,tempStart,tempEnd))button.classList.add('in-range');
            if(tempStart&&sameDate(cellDate,tempStart))button.classList.add('range-start');
            if(tempEnd&&sameDate(cellDate,tempEnd))button.classList.add('range-end');

            button.addEventListener('click',function(event){
                event.preventDefault();
                event.stopPropagation();
                selectDate(cellDate);
            });

            days.appendChild(button);
        }

        calendar.appendChild(days);
        return calendar;
    }

    function selectDate(date){
        const chosen=cloneDate(date);

        if(!tempStart||tempEnd){
            tempStart=chosen;
            tempEnd=null;
        }else if(toISO(chosen)<toISO(tempStart)){
            tempEnd=cloneDate(tempStart);
            tempStart=chosen;
        }else{
            tempEnd=chosen;
        }

        renderCalendars();
    }

    function updateSelectedLabel(){
        if(!selectedLabel)return;

        if(tempStart&&tempEnd){
            selectedLabel.textContent=formatDate(tempStart)+' - '+formatDate(tempEnd);
            return;
        }

        if(tempStart){
            selectedLabel.textContent=formatDate(tempStart)+' - pilih tanggal akhir';
            return;
        }

        selectedLabel.textContent='Pilih tanggal awal';
    }

    function positionPicker(){
        if(!picker||!dateInput||window.innerWidth<=767)return;

        const rect=dateInput.getBoundingClientRect();
        const width=picker.offsetWidth||720;
        const height=picker.offsetHeight||500;

        let left=rect.left+rect.width/2-width/2;
        let top=rect.bottom+8;

        if(left+width>window.innerWidth-10){
            left=window.innerWidth-width-10;
        }

        if(left<10)left=10;

        if(top+height>window.innerHeight-10){
            top=rect.top-height-8;
        }

        if(top<10)top=10;

        picker.style.left=left+'px';
        picker.style.top=top+'px';
    }

    function showPicker(){
        if(!picker)return;

        closePanel();

        tempStart=selectedStart?cloneDate(selectedStart):null;
        tempEnd=selectedEnd?cloneDate(selectedEnd):null;

        if(tempStart){
            viewLeft=new Date(
                tempStart.getFullYear(),
                tempStart.getMonth(),
                1
            );
        }

        renderCalendars();
        picker.classList.remove('hidden');
        document.body.classList.add('date-picker-lock');

        requestAnimationFrame(positionPicker);
    }

    function hidePicker(){
        if(!picker)return;

        picker.classList.add('hidden');
        closePanel();
        document.body.classList.remove('date-picker-lock');
    }

    function showMonthPanel(calendarDate,anchor,side){
        if(!panel)return;

        panelSide=side;
        panel.innerHTML='';
        panel.classList.remove('hidden');

        const header=document.createElement('div');
        header.className='custom-picker-panel-header';

        const title=document.createElement('div');
        title.className='custom-picker-panel-title';
        title.textContent='Pilih Bulan '+calendarDate.getFullYear();

        const close=document.createElement('button');
        close.type='button';
        close.className='custom-picker-panel-close';
        close.innerHTML='&times;';
        close.addEventListener('click',closePanel);

        header.append(title,close);
        panel.appendChild(header);

        const grid=document.createElement('div');
        grid.className='custom-picker-month-grid';

        MONTHS.forEach(function(monthName,monthIndex){
            const button=document.createElement('button');
            button.type='button';
            button.className='custom-picker-option';
            button.textContent=monthName;

            if(monthIndex===calendarDate.getMonth()){
                button.classList.add('active');
            }

            button.addEventListener('click',function(event){
                event.preventDefault();
                event.stopPropagation();

                const newDate=new Date(
                    calendarDate.getFullYear(),
                    monthIndex,
                    1
                );

                viewLeft=panelSide==='left'
                    ? newDate
                    : addMonths(newDate,-1);

                closePanel();
                renderCalendars();
            });

            grid.appendChild(button);
        });

        panel.appendChild(grid);
        positionPanel(panel,anchor);
    }

    function showYearPanel(calendarDate,anchor,side){
        if(!panel)return;

        panelSide=side;

        const startYear=Math.floor(
            calendarDate.getFullYear()/12
        )*12;

        renderYearPanel(
            calendarDate,
            anchor,
            startYear
        );
    }

    function renderYearPanel(calendarDate,anchor,startYear){
        panel.innerHTML='';
        panel.classList.remove('hidden');

        const header=document.createElement('div');
        header.className='custom-picker-panel-header';

        const title=document.createElement('div');
        title.className='custom-picker-panel-title';
        title.textContent=startYear+' - '+(startYear+11);

        const navigation=document.createElement('div');
        navigation.className='custom-picker-year-navigation';

        const previous=document.createElement('button');
        previous.type='button';
        previous.className='custom-picker-year-nav';
        previous.innerHTML='&#8249;';

        const next=document.createElement('button');
        next.type='button';
        next.className='custom-picker-year-nav';
        next.innerHTML='&#8250;';

        const close=document.createElement('button');
        close.type='button';
        close.className='custom-picker-panel-close';
        close.innerHTML='&times;';

        previous.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            renderYearPanel(calendarDate,anchor,startYear-12);
        });

        next.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            renderYearPanel(calendarDate,anchor,startYear+12);
        });

        close.addEventListener('click',closePanel);

        navigation.append(previous,next,close);
        header.append(title,navigation);
        panel.appendChild(header);

        const grid=document.createElement('div');
        grid.className='custom-picker-year-grid';

        for(let year=startYear;year<startYear+12;year++){
            const button=document.createElement('button');
            button.type='button';
            button.className='custom-picker-option';
            button.textContent=String(year);

            if(year===calendarDate.getFullYear()){
                button.classList.add('active');
            }

            if(year===new Date().getFullYear()){
                button.classList.add('current');
            }

            button.disabled=year<MIN_YEAR||year>MAX_YEAR;

            if(!button.disabled){
                button.addEventListener('click',function(event){
                    event.preventDefault();
                    event.stopPropagation();

                    const month=calendarDate.getMonth();

                    viewLeft=panelSide==='left'
                        ? new Date(year,month,1)
                        : new Date(year,month-1,1);

                    closePanel();
                    renderCalendars();
                });
            }

            grid.appendChild(button);
        }

        panel.appendChild(grid);
        positionPanel(panel,anchor);
    }

    function positionPanel(element,anchor){
        if(!element||!anchor)return;

        if(window.innerWidth<=767){
            element.style.left='';
            element.style.top='';
            return;
        }

        const rect=anchor.getBoundingClientRect();
        const width=element.offsetWidth||310;
        const height=element.offsetHeight||300;

        let left=rect.left+rect.width/2-width/2;
        let top=rect.bottom+8;

        if(left+width>window.innerWidth-10){
            left=window.innerWidth-width-10;
        }

        if(left<10)left=10;

        if(top+height>window.innerHeight-10){
            top=rect.top-height-8;
        }

        if(top<10)top=10;

        element.style.left=left+'px';
        element.style.top=top+'px';
    }

    function closePanel(){
        if(!panel)return;

        panel.classList.add('hidden');
        panel.innerHTML='';
    }

    function applyDateRange(){
        if(!tempStart||!tempEnd){
            if(typeof window.Swal!=='undefined'){
                window.Swal.fire({
                    icon:'info',
                    title:'Pilih rentang tanggal',
                    text:'Silakan pilih tanggal awal dan tanggal akhir terlebih dahulu.',
                    confirmButtonText:'Mengerti',
                    confirmButtonColor:'#2563eb'
                });
            }else{
                window.alert(
                    'Silakan pilih tanggal awal dan tanggal akhir terlebih dahulu.'
                );
            }

            return;
        }

        selectedStart=cloneDate(tempStart);
        selectedEnd=cloneDate(tempEnd);

        if(dariInput)dariInput.value=toISO(selectedStart);
        if(sampaiInput)sampaiInput.value=toISO(selectedEnd);
        if(dateInput)dateInput.value=formatDate(selectedStart)+' - '+formatDate(selectedEnd);

        if(clearButton){
            clearButton.classList.remove('hidden');
            clearButton.classList.add('flex');
        }

        hidePicker();
    }

    function clearDateRange(){
        selectedStart=null;
        selectedEnd=null;
        tempStart=null;
        tempEnd=null;

        if(dariInput)dariInput.value='';
        if(sampaiInput)sampaiInput.value='';
        if(dateInput)dateInput.value='';

        if(clearButton){
            clearButton.classList.remove('flex');
            clearButton.classList.add('hidden');
        }

        viewLeft=new Date();
        viewLeft.setDate(1);

        hidePicker();
    }

    if(dateInput&&picker){
        dateInput.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();

            if(picker.classList.contains('hidden')){
                showPicker();
            }else{
                hidePicker();
            }
        });

        dateInput.addEventListener('focus',function(){
            if(picker.classList.contains('hidden')){
                showPicker();
            }
        });

        closePickerButton?.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            hidePicker();
        });

        clearPickerButton?.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            clearDateRange();
        });

        applyPickerButton?.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            applyDateRange();
        });

        clearButton?.addEventListener('click',function(event){
            event.preventDefault();
            event.stopPropagation();
            clearDateRange();
        });

        document.addEventListener('mousedown',function(event){
            if(picker.classList.contains('hidden'))return;
            if(picker.contains(event.target))return;
            if(panel&&panel.contains(event.target))return;
            if(dateInput.contains(event.target))return;
            if(clearButton&&clearButton.contains(event.target))return;

            hidePicker();
        });
    }

    window.addEventListener('resize',function(){
        if(picker&&!picker.classList.contains('hidden')){
            positionPicker();
        }

        if(panel&&!panel.classList.contains('hidden')){
            closePanel();
        }
    });

    window.addEventListener('scroll',function(){
        if(
            picker &&
            !picker.classList.contains('hidden') &&
            window.innerWidth>767
        ){
            positionPicker();
        }
    },true);

    function initializeFilterDropdowns(){
        const dropdowns=document.querySelectorAll('[data-filter-dropdown]');
        if(!dropdowns.length)return;

        function closeDropdown(dropdown){
            const trigger=dropdown.querySelector('[data-dropdown-trigger]');
            const menu=dropdown.querySelector('[data-dropdown-menu]');

            if(!trigger||!menu)return;

            menu.classList.add('hidden');
            trigger.classList.remove('is-open');
            trigger.setAttribute('aria-expanded','false');
        }

        function closeAllDropdowns(except=null){
            dropdowns.forEach(function(dropdown){
                if(dropdown!==except){
                    closeDropdown(dropdown);
                }
            });
        }

        function updateDropdown(dropdown){
            const type=dropdown.dataset.filterDropdown;

            const selector=type==='kategori'
                ? '.kategori-checkbox'
                : '.status-checkbox';

            const checkboxes=dropdown.querySelectorAll(selector);
            const checked=dropdown.querySelectorAll(selector+':checked');
            const count=checked.length;

            const countElement=dropdown.querySelector('[data-filter-count]');
            const footerCount=dropdown.querySelector('[data-footer-count]');
            const trigger=dropdown.querySelector('[data-dropdown-trigger]');

            if(countElement){
                countElement.textContent=count+' dipilih';
            }

            if(footerCount){
                footerCount.textContent=
                    count+' '+
                    (type==='kategori'?'kategori':'status')+
                    ' dipilih';
            }

            checkboxes.forEach(function(checkbox){
                const option=checkbox.closest('.archive-filter-option');

                if(option){
                    option.classList.toggle(
                        'is-selected',
                        checkbox.checked
                    );
                }
            });

            if(trigger){
                trigger.classList.toggle(
                    'is-active',
                    count>0
                );
            }
        }

        dropdowns.forEach(function(dropdown){
            const trigger=dropdown.querySelector('[data-dropdown-trigger]');
            const menu=dropdown.querySelector('[data-dropdown-menu]');

            if(!trigger||!menu)return;

            const type=dropdown.dataset.filterDropdown;

            const selector=type==='kategori'
                ? '.kategori-checkbox'
                : '.status-checkbox';

            const checkboxes=dropdown.querySelectorAll(selector);

            const selectAll=dropdown.querySelector(
                '[data-action="select-all"]'
            );

            const clearAll=dropdown.querySelector(
                '[data-action="clear-all"]'
            );

            trigger.addEventListener('click',function(event){
                event.preventDefault();
                event.stopPropagation();

                const isOpen=!menu.classList.contains('hidden');

                closeAllDropdowns(dropdown);

                if(isOpen){
                    closeDropdown(dropdown);
                    return;
                }

                menu.classList.remove('hidden');
                trigger.classList.add('is-open');
                trigger.setAttribute('aria-expanded','true');

                updateDropdown(dropdown);
            });

            menu.addEventListener('click',function(event){
                event.stopPropagation();
            });

            checkboxes.forEach(function(checkbox){
                checkbox.addEventListener('change',function(){
                    updateDropdown(dropdown);
                });
            });

            selectAll?.addEventListener('click',function(event){
                event.preventDefault();
                event.stopPropagation();

                checkboxes.forEach(function(checkbox){
                    checkbox.checked=true;
                });

                updateDropdown(dropdown);
            });

            clearAll?.addEventListener('click',function(event){
                event.preventDefault();
                event.stopPropagation();

                checkboxes.forEach(function(checkbox){
                    checkbox.checked=false;
                });

                updateDropdown(dropdown);
            });

            updateDropdown(dropdown);
        });

        document.addEventListener('click',function(event){
            if(event.target.closest('[data-filter-dropdown]'))return;
            closeAllDropdowns();
        });

        document.addEventListener('keydown',function(event){
            if(event.key==='Escape'){
                closeAllDropdowns();
            }
        });
    }

    const filterForm=document.getElementById('filterForm');

    filterForm?.addEventListener('submit',function(event){
        const start=dariInput?.value||'';
        const end=sampaiInput?.value||'';

        if(start&&end&&start>end){
            event.preventDefault();

            if(typeof window.Swal!=='undefined'){
                window.Swal.fire({
                    icon:'warning',
                    title:'Rentang tanggal tidak valid',
                    text:'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.',
                    confirmButtonText:'Mengerti',
                    confirmButtonColor:'#2563eb',
                    customClass:{
                        popup:'rounded-2xl',
                        confirmButton:'rounded-xl text-xs font-semibold px-4 py-2.5'
                    }
                });
            }else{
                window.alert(
                    'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.'
                );
            }
        }
    });

    document.querySelectorAll('.delete-btn').forEach(function(button){
        button.addEventListener('click',function(){
            const form=this.closest('.delete-form');
            if(!form)return;

            if(typeof window.Swal!=='undefined'){
                window.Swal.fire({
                    title:'Hapus Surat Masuk?',
                    text:'Data yang dihapus akan dipindahkan ke tempat sampah.',
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
                }).then(function(result){
                    if(result.isConfirmed){
                        form.submit();
                    }
                });

                return;
            }

            if(window.confirm('Yakin ingin menghapus surat masuk ini?')){
                form.submit();
            }
        });
    });

    initializeFilterDropdowns();

    if(selectedStart&&selectedEnd&&dateInput&&clearButton){
        dateInput.value=
            formatDate(selectedStart)+
            ' - '+
            formatDate(selectedEnd);

        clearButton.classList.remove('hidden');
        clearButton.classList.add('flex');
    }
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
@endpush

@endsection