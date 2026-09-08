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
            $dateRangeValue =
                \Carbon\Carbon::parse($dariTanggal)->format('d/m/Y') .
                ' - ' .
                \Carbon\Carbon::parse($sampaiTanggal)->format('d/m/Y');
        } elseif ($dariTanggal) {
            $dateRangeValue =
                \Carbon\Carbon::parse($dariTanggal)->format('d/m/Y');
        } elseif ($sampaiTanggal) {
            $dateRangeValue =
                \Carbon\Carbon::parse($sampaiTanggal)->format('d/m/Y');
        }
    } catch (\Throwable $e) {
        $dateRangeValue = '';
    }

    $hasFilters =
        request()->filled('search') ||
        !empty($selectedStatus) ||
        request()->filled('dari_tanggal') ||
        request()->filled('sampai_tanggal');

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
            return [
                'nama' => '-',
                'jabatan' => null,
            ];
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
        $tanggal =
            data_get($disposisi, 'tanggal_disposisi')
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
        $tanggal =
            data_get($disposisi, 'batas_waktu')
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
@endphp

@push('styles')
<style>
/* ==========================================================================
   DATE PICKER
   ========================================================================== */

.archive-date-picker,
.archive-status-dropdown {
    position: relative;
}

.archive-date-panel,
.archive-status-panel {
    border: 1px solid #cbd5e1;
    background: #fff;
    box-shadow:
        0 18px 45px rgba(15,23,42,.14),
        0 6px 18px rgba(15,23,42,.07);
}

.archive-date-panel {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    z-index: 9999;
    width: 620px;
    max-width: calc(100vw - 24px);
    overflow: hidden;
    border-radius: 16px;
}

.archive-date-panel.hidden,
.archive-status-panel.hidden,
.archive-picker-view.hidden {
    display: none;
}

.archive-date-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 10px 12px;
    border-bottom: 1px solid #cbd5e1;
    background: #f8fafc;
}

.archive-date-header-title {
    color: #334155;
    font-size: 12px;
    font-weight: 800;
}

.archive-date-nav {
    display: inline-flex;
    width: 32px;
    height: 32px;
    align-items: center;
    justify-content: center;
    flex: none;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #fff;
    color: #64748b;
    cursor: pointer;
    transition: .15s;
}

.archive-date-nav:hover {
    border-color: #93c5fd;
    background: #eff6ff;
    color: #2563eb;
}

.archive-date-months {
    display: grid;
    grid-template-columns: repeat(2,minmax(0,1fr));
}

.archive-date-month {
    min-width: 0;
    padding: 12px;
    border-right: 1px solid #cbd5e1;
}

.archive-date-month:last-child {
    border-right: 0;
}

.archive-month-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2px;
    margin-bottom: 8px;
}

.archive-month-header button {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    padding: 5px 6px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #334155;
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
    transition: .15s;
}

.archive-month-header button:hover {
    background: #eff6ff;
    color: #2563eb;
}

.archive-month-grid {
    display: grid;
    grid-template-columns: repeat(7,minmax(0,1fr));
    gap: 2px;
}

.archive-calendar-weekday {
    display: flex;
    height: 24px;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 8px;
    font-weight: 800;
    text-transform: uppercase;
}

.archive-calendar-day {
    display: flex;
    width: 100%;
    height: 31px;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #475569;
    font-size: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: .15s;
}

.archive-calendar-day:hover {
    background: #eff6ff;
    color: #2563eb;
}

.archive-calendar-day:active {
    transform: scale(.95);
}

.archive-calendar-day.other-month {
    color: #cbd5e1;
}

.archive-calendar-day.today {
    box-shadow: inset 0 0 0 1px #93c5fd;
    color: #2563eb;
}

.archive-calendar-day.in-range {
    border-radius: 0;
    background: #eff6ff;
    color: #2563eb;
}

.archive-calendar-day.start-date {
    border-radius: 999px 6px 6px 999px;
    background: #2563eb;
    color: #fff;
}

.archive-calendar-day.end-date {
    border-radius: 6px 999px 999px 6px;
    background: #2563eb;
    color: #fff;
}

.archive-calendar-day.start-date.end-date {
    border-radius: 999px;
}

.archive-calendar-day.start-date:hover,
.archive-calendar-day.end-date:hover {
    background: #1d4ed8;
    color: #fff;
}

.archive-date-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 9px 12px 11px;
    border-top: 1px solid #cbd5e1;
}

.archive-date-footer-info {
    min-width: 0;
    overflow: hidden;
    color: #64748b;
    font-size: 9px;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.archive-date-footer-actions {
    display: flex;
    align-items: center;
    gap: 5px;
    flex: none;
}

.archive-date-btn {
    min-height: 32px;
    padding: 0 11px;
    border-radius: 9px;
    font-size: 9px;
    font-weight: 800;
    cursor: pointer;
    transition: .15s;
}

.archive-date-btn.secondary {
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #475569;
}

.archive-date-btn.secondary:hover {
    background: #f1f5f9;
    color: #334155;
}

.archive-date-btn.primary {
    border: 1px solid #2563eb;
    background: #2563eb;
    color: #fff;
}

.archive-date-btn.primary:hover {
    background: #1d4ed8;
}

/* ==========================================================================
   MONTH / YEAR PICKER
   ========================================================================== */

.archive-picker-view {
    padding: 12px;
}

.archive-picker-toolbar,
.archive-year-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 9px;
}

.archive-picker-toolbar {
    color: #475569;
    font-size: 10px;
    font-weight: 800;
}

.archive-picker-grid {
    display: grid;
    grid-template-columns: repeat(4,minmax(0,1fr));
    gap: 6px;
}

.archive-picker-item {
    display: flex;
    min-height: 36px;
    align-items: center;
    justify-content: center;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #fff;
    color: #475569;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    transition: .15s;
}

.archive-picker-item:hover {
    border-color: #93c5fd;
    background: #eff6ff;
    color: #2563eb;
}

.archive-picker-item.active {
    border-color: #2563eb;
    background: #2563eb;
    color: #fff;
}

.archive-picker-item.current:not(.active) {
    box-shadow: inset 0 0 0 1px #93c5fd;
}

.archive-year-range {
    color: #475569;
    font-size: 10px;
    font-weight: 800;
}

.archive-year-nav {
    display: flex;
    align-items: center;
    gap: 5px;
}

.archive-year-nav button {
    display: inline-flex;
    width: 29px;
    height: 29px;
    align-items: center;
    justify-content: center;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #64748b;
    font-size: 14px;
    cursor: pointer;
}

.archive-year-nav button:hover {
    border-color: #93c5fd;
    background: #eff6ff;
    color: #2563eb;
}

/* ==========================================================================
   STATUS DROPDOWN
   ========================================================================== */

.archive-status-panel {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    z-index: 9999;
    width: 370px;
    overflow: hidden;
    border-radius: 16px;
}

.archive-status-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 14px;
    border-bottom: 1px solid #cbd5e1;
    background: #f8fafc;
}

.archive-status-head-title {
    min-width: 0;
}

.archive-status-head-title strong {
    display: block;
    color: #334155;
    font-size: 11px;
    font-weight: 800;
}

.archive-status-head-title span {
    display: block;
    margin-top: 2px;
    color: #94a3b8;
    font-size: 9px;
}

.archive-status-count {
    display: inline-flex;
    min-height: 25px;
    align-items: center;
    justify-content: center;
    padding: 0 9px;
    border: 1px solid #fde68a;
    border-radius: 999px;
    background: #fffbeb;
    color: #d97706;
    font-size: 9px;
    font-weight: 800;
}

.archive-status-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 9px 14px;
    border-bottom: 1px solid #e2e8f0;
}

.archive-status-action {
    border: 0;
    background: transparent;
    padding: 0;
    color: #2563eb;
    font-size: 9px;
    font-weight: 800;
    cursor: pointer;
}

.archive-status-action:hover {
    color: #1d4ed8;
}

.archive-status-action.muted {
    color: #64748b;
}

.archive-status-options {
    max-height: 210px;
    overflow-y: auto;
    padding: 9px 14px 11px;
}

.archive-status-item {
    display: flex;
    min-height: 43px;
    align-items: center;
    gap: 9px;
    margin-bottom: 6px;
    padding: 8px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #fff;
    cursor: pointer;
    transition: .15s;
}

.archive-status-item:last-child {
    margin-bottom: 0;
}

.archive-status-item:hover {
    border-color: #93c5fd;
    background: #eff6ff;
}

.archive-status-item input {
    width: 16px;
    height: 16px;
    flex: none;
    accent-color: #2563eb;
}

.archive-status-item-label {
    min-width: 0;
    flex: 1;
    color: #475569;
    font-size: 10px;
    font-weight: 700;
}

.archive-status-dot {
    width: 7px;
    height: 7px;
    flex: none;
    border-radius: 999px;
}

.archive-status-dot.menunggu {
    background: #f59e0b;
}

.archive-status-dot.diproses {
    background: #3b82f6;
}

.archive-status-dot.selesai {
    background: #10b981;
}

.archive-status-footer {
    display: flex;
    justify-content: flex-end;
    padding: 9px 14px;
    border-top: 1px solid #cbd5e1;
    background: #f8fafc;
}

.archive-status-close {
    min-height: 32px;
    padding: 0 12px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #fff;
    color: #475569;
    font-size: 9px;
    font-weight: 800;
    cursor: pointer;
}

.archive-status-close:hover {
    background: #f1f5f9;
}

/* ==========================================================================
   TABLE
   ========================================================================== */

.disposition-table-wrapper {
    overflow: hidden;
    border: 1px solid #94a3b8;
    border-radius: 16px;
    background: #fff;
    box-shadow:
        0 1px 3px rgba(15,23,42,.06),
        0 8px 24px rgba(15,23,42,.04);
}

.disposition-table-scroll {
    overflow-x: auto;
}

.disposition-table {
    width: 100%;
    min-width: 950px;
    border-collapse: collapse;
    border-spacing: 0;
    background: #fff;
}

.disposition-table thead {
    background: #f1f5f9;
}

.disposition-table thead th {
    padding: 13px 16px;
    border-right: 1px solid #cbd5e1;
    border-bottom: 2px solid #94a3b8;
    color: #475569;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    line-height: 1.4;
    text-align: left;
    text-transform: uppercase;
    vertical-align: middle;
}

.disposition-table thead th:last-child {
    border-right: 0;
    text-align: center;
}

.disposition-table tbody tr {
    background: #fff;
    transition: background-color .15s ease;
}

.disposition-table tbody tr:nth-child(even) {
    background: #f8fafc;
}

.disposition-table tbody tr:hover {
    background: #eff6ff;
}

.disposition-table tbody td {
    padding: 14px 16px;
    border-right: 1px solid #cbd5e1;
    border-bottom: 1px solid #cbd5e1;
    color: #475569;
    font-size: 12px;
    line-height: 1.5;
    vertical-align: middle;
}

.disposition-table tbody td:last-child {
    border-right: 0;
    text-align: center;
}

.disposition-table tbody tr:last-child td {
    border-bottom: 0;
}

.disposition-table .cell-number {
    color: #334155;
    font-weight: 600;
}

.disposition-table .cell-date {
    color: #475569;
    font-weight: 600;
}

.disposition-table .cell-instruction {
    max-width: 280px;
    color: #334155;
    font-weight: 600;
    white-space: normal;
}

.disposition-table .cell-deadline {
    white-space: nowrap;
}

.disposition-table .cell-status {
    white-space: nowrap;
}

.disposition-table .receiver-box {
    display: flex;
    min-width: 150px;
    max-width: 210px;
    flex-direction: column;
    gap: 1px;
    padding: 6px 9px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #f1f5f9;
}

.disposition-table .receiver-name {
    overflow: hidden;
    color: #334155;
    font-weight: 700;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.disposition-table .receiver-position {
    overflow: hidden;
    color: #94a3b8;
    font-size: 10px;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.disposition-table .status-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    border-width: 1px;
    padding: 5px 10px;
    font-size: 10px;
    font-weight: 800;
}

.disposition-table .action-cell {
    width: 125px;
}

.disposition-table .action-buttons {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
}

.disposition-table .action-button {
    display: inline-flex;
    width: 30px;
    height: 30px;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: #64748b;
    transition: .15s;
}

.disposition-table .action-button.detail:hover {
    background: #dbeafe;
    color: #2563eb;
}

.disposition-table .action-button.edit:hover {
    background: #fef3c7;
    color: #d97706;
}

.disposition-table .action-button.delete:hover {
    background: #ffe4e6;
    color: #e11d48;
}

.disposition-table-empty {
    padding: 48px 16px;
    text-align: center;
}

/* ==========================================================================
   MOBILE
   ========================================================================== */

@media (max-width: 767px) {
    .archive-date-panel,
    .archive-status-panel {
        position: fixed;
        top: 50%;
        left: 50%;
        right: auto;
        width: calc(100vw - 24px);
        max-width: 410px;
        max-height: calc(100vh - 24px);
        transform: translate(-50%, -50%);
    }

    .archive-date-months {
        grid-template-columns: 1fr;
        max-height: 55vh;
        overflow-y: auto;
    }

    .archive-date-month {
        border-right: 0;
        border-bottom: 1px solid #cbd5e1;
    }

    .archive-date-month:last-child {
        border-bottom: 0;
    }

    .archive-picker-grid {
        grid-template-columns: repeat(3,minmax(0,1fr));
    }
}
</style>
@endpush

<div class="space-y-4 sm:space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">
                Disposisi Surat
            </h1>

            <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                Kelola dan pantau instruksi disposisi dari pimpinan ke unit kerja.
            </p>
        </div>

        <div class="grid w-full grid-cols-3 gap-2 sm:flex sm:w-auto">

            @if(Route::has('export.disposisi.excel'))
                <a
                    href="{{ route('export.disposisi.excel', request()->query()) }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-2.5 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 sm:px-3.5"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 00.707.293l5.414 5.414a1 1 0 00.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Excel</span>
                </a>
            @else
                <div></div>
            @endif

            @if(Route::has('export.disposisi.pdf'))
                <a
                    href="{{ route('export.disposisi.pdf', request()->query()) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-2.5 py-2 text-xs font-semibold text-rose-700 shadow-sm transition hover:bg-rose-100 sm:px-3.5"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>PDF</span>
                </a>
            @else
                <div></div>
            @endif

            @if(
                in_array($userRole, ['admin', 'pimpinan'], true) &&
                Route::has('surat-masuk.index')
            )
                <a
                    href="{{ route('surat-masuk.index') }}"
                    title="Pilih surat masuk untuk membuat disposisi"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-2.5 py-2 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 sm:px-4"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Disposisi</span>
                </a>
            @else
                <div></div>
            @endif
        </div>
    </div>

    {{-- FILTER --}}
    <div class="rounded-2xl border border-slate-300 bg-white p-3 shadow-sm sm:p-4">
        <form
            id="filterForm"
            method="GET"
            action="{{ route('disposisi.index') }}"
            class="space-y-3"
        >
            <div class="grid grid-cols-1 gap-2.5 lg:grid-cols-12">

                {{-- SEARCH --}}
                <div class="lg:col-span-5">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ request('search') }}"
                            placeholder="Cari nomor surat, penerima atau isi instruksi..."
                            autocomplete="off"
                            class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                        >
                    </div>
                </div>

                {{-- DATE RANGE --}}
                <div class="lg:col-span-4">
                    <div class="archive-date-picker">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5v12a2 2 0 002 2z"/>
                                </svg>
                            </div>

                            <input
                                id="date-range"
                                type="text"
                                value="{{ $dateRangeValue }}"
                                readonly
                                autocomplete="off"
                                placeholder="Pilih rentang tanggal..."
                                class="h-11 w-full cursor-pointer rounded-xl border border-slate-300 bg-slate-50 pl-9 pr-10 text-xs font-medium text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                            >

                            <button
                                type="button"
                                id="clearDateRange"
                                title="Hapus tanggal"
                                aria-label="Hapus tanggal"
                                class="{{ $dateRangeValue ? 'flex' : 'hidden' }} absolute inset-y-0 right-0 z-10 w-10 items-center justify-center text-slate-400 transition hover:text-rose-500"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
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

                                <div class="archive-date-header-title">
                                    Pilih Rentang Tanggal
                                </div>

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
                                <div id="datePickerInfo" class="archive-date-footer-info">
                                    Pilih tanggal mulai
                                </div>

                                <div class="archive-date-footer-actions">
                                    <button type="button" id="clearPickerButton" class="archive-date-btn secondary">
                                        Bersihkan
                                    </button>

                                    <button type="button" id="applyPickerButton" class="archive-date-btn primary">
                                        Terapkan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FILTER BUTTON --}}
                <div class="lg:col-span-3">
                    <div class="flex h-11 gap-2">
                        <button
                            type="submit"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-900 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:text-sm"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 00-.293.707l-6.414 6.414a1 1 0 00-.293.707L13 17v4l-4-4v-4.293a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span>Filter</span>
                        </button>

                        @if($hasFilters)
                            <a
                                href="{{ route('disposisi.index') }}"
                                title="Reset Filter"
                                aria-label="Reset Filter"
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- STATUS DROPDOWN --}}
            <div class="archive-status-dropdown">
                <button
                    type="button"
                    id="statusDropdownButton"
                    class="flex w-full items-center justify-between gap-3 rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-left transition hover:border-slate-400 hover:bg-slate-50 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z"/>
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-700 sm:text-sm">
                                Status Disposisi
                            </div>

                            <div id="statusSummary" class="mt-0.5 truncate text-[10px] text-slate-400 sm:text-xs">
                                Semua status
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <span id="statusCount" class="inline-flex min-h-7 items-center rounded-full border border-amber-100 bg-amber-50 px-2.5 text-[10px] font-bold text-amber-600">
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
                        <button type="button" id="selectAllStatus" class="archive-status-action">
                            Pilih Semua
                        </button>

                        <button type="button" id="clearAllStatus" class="archive-status-action muted">
                            Batalkan
                        </button>
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

                                <span class="archive-status-item-label">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="archive-status-footer">
                        <button type="button" id="closeStatusDropdown" class="archive-status-close">
                            Selesai
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
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

                            $badgeClass =
                                $statusBadgeClasses[$status]
                                ?? 'border-slate-200 bg-slate-100 text-slate-600';

                            $nomorSurat = $getNomorSurat($d);
                            $penerimaData = $getPenerima($d);
                            $instruksi = $getInstruksi($d);
                            $tanggalDisposisi = $getTanggalDisposisi($d);
                            $batasWaktu = $getBatasWaktu($d);
                            $isLate = false;

                            if ($batasWaktu !== '-') {
                                try {
                                    $tanggalBatas =
                                        data_get($d, 'batas_waktu')
                                        ?? data_get($d, 'tanggal_batas');

                                    $isLate =
                                        \Carbon\Carbon::parse($tanggalBatas)->isPast()
                                        && $status !== 'selesai';
                                } catch (\Throwable $e) {
                                    $isLate = false;
                                }
                            }
                        @endphp

                        <tr>

                            {{-- NOMOR SURAT --}}
                            <td class="cell-number">
                                <span
                                    class="inline-block max-w-[190px] truncate"
                                    title="{{ $nomorSurat }}"
                                >
                                    {{ $nomorSurat }}
                                </span>
                            </td>

                            {{-- TANGGAL --}}
                            <td class="cell-date">
                                {{ $tanggalDisposisi }}
                            </td>

                            {{-- PENERIMA --}}
                            <td>
                                @if($penerimaData['nama'] !== '-')
                                    <div
                                        class="receiver-box"
                                        title="{{ $penerimaData['nama'] }}{{ $penerimaData['jabatan'] ? ' - ' . $penerimaData['jabatan'] : '' }}"
                                    >
                                        <span class="receiver-name">
                                            {{ $penerimaData['nama'] }}
                                        </span>

                                        @if($penerimaData['jabatan'])
                                            <span class="receiver-position">
                                                {{ $penerimaData['jabatan'] }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- INSTRUKSI --}}
                            <td
                                class="cell-instruction"
                                title="{{ $instruksi }}"
                            >
                                {{ $instruksi }}
                            </td>

                            {{-- STATUS --}}
                            <td class="cell-status">
                                <span class="status-badge {{ $badgeClass }}">
                                    {{ $statusOptions[$status] ?? ucfirst($status) }}
                                </span>
                            </td>

                            {{-- BATAS WAKTU --}}
                            <td class="cell-deadline">
                                @if($batasWaktu !== '-')
                                    <span class="{{ $isLate ? 'font-semibold text-rose-600' : '' }}">
                                        {{ $batasWaktu }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td class="action-cell">
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

                                    @if(
                                        in_array($userRole, ['admin', 'pimpinan'], true) &&
                                        Route::has('disposisi.edit')
                                    )
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

                                    @if(
                                        in_array($userRole, ['admin', 'pimpinan'], true) &&
                                        Route::has('disposisi.destroy')
                                    )
                                        <form
                                            action="{{ route('disposisi.destroy', $d) }}"
                                            method="POST"
                                            class="delete-form inline"
                                        >
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

                                    <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-1.414 0l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>

                                    <p class="text-sm font-semibold text-slate-700 sm:text-base">
                                        Belum ada data disposisi
                                    </p>

                                    <p class="mt-0.5 max-w-md px-4 text-[11px] text-slate-400 sm:text-xs">
                                        @if($hasFilters)
                                            Tidak ada disposisi yang sesuai dengan filter yang digunakan.
                                        @else
                                            Belum ada data disposisi yang tersimpan.
                                        @endif
                                    </p>

                                    @if($hasFilters)
                                        <a
                                            href="{{ route('disposisi.index') }}"
                                            class="mt-3 inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-[11px] font-semibold text-white transition hover:bg-slate-800"
                                        >
                                            Reset Filter
                                        </a>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(
            isset($disposisis) &&
            method_exists($disposisis, 'hasPages') &&
            $disposisis->hasPages()
        )
            <div class="border-t border-slate-300 px-4 py-3 sm:px-6 sm:py-4">
                {{ $disposisis->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    const months = [
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

    const weekdays = [
        'Sen',
        'Sel',
        'Rab',
        'Kam',
        'Jum',
        'Sab',
        'Min'
    ];

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

    let tempStart = parseDate(dariInput?.value);
    let tempEnd = parseDate(sampaiInput?.value);

    let viewDate =
        cloneDate(tempStart) ||
        cloneDate(tempEnd) ||
        today();

    viewDate =
        new Date(
            viewDate.getFullYear(),
            viewDate.getMonth(),
            1
        );

    let pickerView = 'calendar';
    let pickerYear = viewDate.getFullYear();
    let pickerMonth = viewDate.getMonth();

    function today() {
        const date = new Date();

        date.setHours(
            0,
            0,
            0,
            0
        );

        return date;
    }

    function pad(number) {
        return String(number).padStart(2, '0');
    }

    function cloneDate(date) {
        if (!date) {
            return null;
        }

        return new Date(
            date.getFullYear(),
            date.getMonth(),
            date.getDate()
        );
    }

    function parseDate(value) {
        if (!value) {
            return null;
        }

        const match =
            String(value).match(
                /^(\d{4})-(\d{2})-(\d{2})$/
            );

        if (!match) {
            return null;
        }

        const year = Number(match[1]);
        const month = Number(match[2]) - 1;
        const day = Number(match[3]);

        const date =
            new Date(
                year,
                month,
                day
            );

        if (
            date.getFullYear() !== year ||
            date.getMonth() !== month ||
            date.getDate() !== day
        ) {
            return null;
        }

        date.setHours(
            0,
            0,
            0,
            0
        );

        return date;
    }

    function dateKey(date) {
        if (!date) {
            return '';
        }

        return (
            date.getFullYear() +
            '-' +
            pad(date.getMonth() + 1) +
            '-' +
            pad(date.getDate())
        );
    }

    function formatDisplay(date) {
        if (!date) {
            return '';
        }

        return (
            pad(date.getDate()) +
            '/' +
            pad(date.getMonth() + 1) +
            '/' +
            date.getFullYear()
        );
    }

    function formatRange(start, end) {
        if (!start || !end) {
            return '';
        }

        return (
            formatDisplay(start) +
            ' - ' +
            formatDisplay(end)
        );
    }

    function isSameDay(first, second) {
        return !!(
            first &&
            second &&
            dateKey(first) ===
                dateKey(second)
        );
    }

    function isBefore(first, second) {
        return (
            dateKey(first) <
            dateKey(second)
        );
    }

    function isAfter(first, second) {
        return (
            dateKey(first) >
            dateKey(second)
        );
    }

    function updateInputDisplay() {
        const start =
            parseDate(
                dariInput.value
            );

        const end =
            parseDate(
                sampaiInput.value
            );

        if (
            start &&
            end
        ) {
            dateInput.value =
                formatRange(
                    start,
                    end
                );

            clearDateRange.classList.remove('hidden');
            clearDateRange.classList.add('flex');
        } else {
            dateInput.value = '';

            clearDateRange.classList.remove('flex');
            clearDateRange.classList.add('hidden');
        }
    }

    function updateDateInfo() {
        if (
            tempStart &&
            tempEnd
        ) {
            dateInfo.textContent =
                formatRange(
                    tempStart,
                    tempEnd
                );

            return;
        }

        if (tempStart) {
            dateInfo.textContent =
                formatDisplay(tempStart) +
                ' - pilih tanggal akhir';

            return;
        }

        dateInfo.textContent =
            'Pilih tanggal mulai';
    }

    function buildDayButton(date, otherMonth) {
        const classes = [
            'archive-calendar-day'
        ];

        if (otherMonth) {
            classes.push(
                'other-month'
            );
        }

        if (
            isSameDay(
                date,
                today()
            )
        ) {
            classes.push(
                'today'
            );
        }

        if (
            tempStart &&
            tempEnd &&
            !isBefore(date, tempStart) &&
            !isAfter(date, tempEnd)
        ) {
            classes.push(
                'in-range'
            );
        }

        if (
            isSameDay(
                date,
                tempStart
            )
        ) {
            classes.push(
                'start-date'
            );
        }

        if (
            isSameDay(
                date,
                tempEnd
            )
        ) {
            classes.push(
                'end-date'
            );
        }

        return `
            <button
                type="button"
                class="${classes.join(' ')}"
                data-date="${dateKey(date)}">
                ${date.getDate()}
            </button>
        `;
    }

    function renderCalendar(year, month) {
        let html = `
            <div class="archive-date-month">
                <div class="archive-month-header">

                    <button
                        type="button"
                        data-action="month"
                        data-month="${month}"
                        data-year="${year}">
                        ${months[month]}

                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <button
                        type="button"
                        data-action="year"
                        data-month="${month}"
                        data-year="${year}">
                        ${year}

                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                </div>

                <div class="archive-month-grid">
        `;

        weekdays.forEach(function (day) {
            html += `
                <div class="archive-calendar-weekday">
                    ${day}
                </div>
            `;
        });

        const firstDay =
            new Date(
                year,
                month,
                1
            );

        const firstWeekday =
            (
                firstDay.getDay() +
                6
            ) % 7;

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

        for (
            let index = firstWeekday - 1;
            index >= 0;
            index--
        ) {
            const day =
                daysInPreviousMonth -
                index;

            const date =
                new Date(
                    year,
                    month - 1,
                    day
                );

            html +=
                buildDayButton(
                    date,
                    true
                );
        }

        for (
            let day = 1;
            day <= daysInMonth;
            day++
        ) {
            const date =
                new Date(
                    year,
                    month,
                    day
                );

            html +=
                buildDayButton(
                    date,
                    false
                );
        }

        const totalDays =
            firstWeekday +
            daysInMonth;

        const remaining =
            (
                7 -
                (
                    totalDays %
                    7
                )
            ) % 7;

        for (
            let day = 1;
            day <= remaining;
            day++
        ) {
            const date =
                new Date(
                    year,
                    month + 1,
                    day
                );

            html +=
                buildDayButton(
                    date,
                    true
                );
        }

        html += `
                </div>
            </div>
        `;

        return html;
    }

    function bindCalendarEvents() {
        dateContent
            .querySelectorAll('[data-date]')
            .forEach(function (button) {
                button.addEventListener(
                    'click',
                    function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        selectDate(
                            parseDate(
                                this.dataset.date
                            )
                        );
                    }
                );
            });

        dateContent
            .querySelectorAll(
                '[data-action="month"]'
            )
            .forEach(function (button) {
                button.addEventListener(
                    'click',
                    function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        pickerMonth =
                            Number(
                                this.dataset.month
                            );

                        pickerYear =
                            Number(
                                this.dataset.year
                            );

                        pickerView =
                            'month';

                        renderPicker();
                    }
                );
            });

        dateContent
            .querySelectorAll(
                '[data-action="year"]'
            )
            .forEach(function (button) {
                button.addEventListener(
                    'click',
                    function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        pickerMonth =
                            Number(
                                this.dataset.month
                            );

                        pickerYear =
                            Number(
                                this.dataset.year
                            );

                        pickerView =
                            'year';

                        renderPicker();
                    }
                );
            });
    }

    function renderCalendarView() {
        dateContent.classList.remove(
            'hidden'
        );

        monthPickerView.classList.add(
            'hidden'
        );

        yearPickerView.classList.add(
            'hidden'
        );

        const firstYear =
            viewDate.getFullYear();

        const firstMonth =
            viewDate.getMonth();

        const secondDate =
            new Date(
                firstYear,
                firstMonth + 1,
                1
            );

        dateContent.innerHTML =
            renderCalendar(
                firstYear,
                firstMonth
            ) +
            renderCalendar(
                secondDate.getFullYear(),
                secondDate.getMonth()
            );

        bindCalendarEvents();
        updateDateInfo();
    }

    function renderMonthPicker() {
        dateContent.classList.add(
            'hidden'
        );

        yearPickerView.classList.add(
            'hidden'
        );

        monthPickerView.classList.remove(
            'hidden'
        );

        monthPickerView.innerHTML = `
            <div class="archive-picker-toolbar">
                <span>${pickerYear}</span>
            </div>

            <div class="archive-picker-grid">
                ${months.map(function (month, index) {
                    const active =
                        index === pickerMonth
                            ? 'active'
                            : '';

                    return `
                        <button
                            type="button"
                            class="archive-picker-item ${active}"
                            data-picker-month="${index}">
                            ${month}
                        </button>
                    `;
                }).join('')}
            </div>
        `;

        monthPickerView
            .querySelectorAll(
                '[data-picker-month]'
            )
            .forEach(function (button) {
                button.addEventListener(
                    'click',
                    function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const month =
                            Number(
                                this.dataset.pickerMonth
                            );

                        viewDate =
                            new Date(
                                pickerYear,
                                month,
                                1
                            );

                        pickerMonth =
                            month;

                        pickerView =
                            'calendar';

                        renderPicker();
                    }
                );
            });
    }

    function renderYearPicker() {
        dateContent.classList.add(
            'hidden'
        );

        monthPickerView.classList.add(
            'hidden'
        );

        yearPickerView.classList.remove(
            'hidden'
        );

        const startYear =
            Math.floor(
                (pickerYear - 2000) / 12
            ) * 12 + 2000;

        const endYear =
            startYear + 11;

        let html = `
            <div class="archive-year-toolbar">
                <div class="archive-year-nav">
                    <button type="button" id="yearPrev">‹</button>
                </div>

                <span class="archive-year-range">
                    ${startYear} - ${endYear}
                </span>

                <div class="archive-year-nav">
                    <button type="button" id="yearNext">›</button>
                </div>
            </div>

            <div class="archive-picker-grid">
        `;

        for (
            let year = startYear;
            year <= endYear;
            year++
        ) {
            const active =
                year === pickerYear
                    ? 'active'
                    : '';

            html += `
                <button
                    type="button"
                    class="archive-picker-item ${active}"
                    data-picker-year="${year}">
                    ${year}
                </button>
            `;
        }

        html += `
            </div>
        `;

        yearPickerView.innerHTML =
            html;

        document
            .getElementById('yearPrev')
            ?.addEventListener(
                'click',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    pickerYear =
                        startYear - 1;

                    renderYearPicker();
                }
            );

        document
            .getElementById('yearNext')
            ?.addEventListener(
                'click',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    pickerYear =
                        endYear + 1;

                    renderYearPicker();
                }
            );

        yearPickerView
            .querySelectorAll(
                '[data-picker-year]'
            )
            .forEach(function (button) {
                button.addEventListener(
                    'click',
                    function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        pickerYear =
                            Number(
                                this.dataset
                                    .pickerYear
                            );

                        viewDate =
                            new Date(
                                pickerYear,
                                pickerMonth,
                                1
                            );

                        pickerView =
                            'calendar';

                        renderPicker();
                    }
                );
            });
    }

    function renderPicker() {
        if (
            pickerView === 'month'
        ) {
            datePrev.style.visibility =
                'hidden';

            dateNext.style.visibility =
                'hidden';

            renderMonthPicker();
            return;
        }

        if (
            pickerView === 'year'
        ) {
            datePrev.style.visibility =
                'hidden';

            dateNext.style.visibility =
                'hidden';

            renderYearPicker();
            return;
        }

        datePrev.style.visibility =
            'visible';

        dateNext.style.visibility =
            'visible';

        renderCalendarView();
    }

    function selectDate(date) {
        if (!date) {
            return;
        }

        if (
            !tempStart ||
            tempEnd
        ) {
            tempStart =
                cloneDate(date);

            tempEnd =
                null;
        } else {
            if (
                isBefore(
                    date,
                    tempStart
                )
            ) {
                tempEnd =
                    cloneDate(
                        tempStart
                    );

                tempStart =
                    cloneDate(date);
            } else {
                tempEnd =
                    cloneDate(date);
            }
        }

        viewDate =
            new Date(
                date.getFullYear(),
                date.getMonth(),
                1
            );

        pickerYear =
            date.getFullYear();

        pickerMonth =
            date.getMonth();

        pickerView =
            'calendar';

        renderPicker();
        updateDateInfo();
    }

    function openDatePicker() {
        closeStatusDropdownPanel();

        tempStart =
            parseDate(
                dariInput.value
            );

        tempEnd =
            parseDate(
                sampaiInput.value
            );

        viewDate =
            cloneDate(tempStart) ||
            cloneDate(tempEnd) ||
            today();

        viewDate =
            new Date(
                viewDate.getFullYear(),
                viewDate.getMonth(),
                1
            );

        pickerYear =
            viewDate.getFullYear();

        pickerMonth =
            viewDate.getMonth();

        pickerView =
            'calendar';

        datePanel.classList.remove(
            'hidden'
        );

        renderPicker();
        updateDateInfo();
    }

    function closeDatePicker() {
        datePanel.classList.add(
            'hidden'
        );

        pickerView =
            'calendar';
    }

    function showWarning(title, text) {
        if (
            typeof Swal !==
            'undefined'
        ) {
            Swal.fire({
                icon: 'warning',
                title,
                text,
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#2563eb',
                customClass: {
                    popup: 'rounded-2xl',
                    confirmButton:
                        'rounded-xl text-xs font-semibold px-4 py-2.5'
                }
            });

            return;
        }

        alert(text);
    }

    function applyDateRange() {
        if (
            !tempStart ||
            !tempEnd
        ) {
            showWarning(
                'Tanggal belum lengkap',
                'Pilih tanggal mulai dan tanggal akhir terlebih dahulu.'
            );

            return;
        }

        if (
            isAfter(
                tempStart,
                tempEnd
            )
        ) {
            const oldStart =
                cloneDate(
                    tempStart
                );

            tempStart =
                cloneDate(
                    tempEnd
                );

            tempEnd =
                oldStart;
        }

        dariInput.value =
            dateKey(tempStart);

        sampaiInput.value =
            dateKey(tempEnd);

        updateInputDisplay();
        closeDatePicker();
    }

    function clearDateValue() {
        tempStart =
            null;

        tempEnd =
            null;

        dariInput.value =
            '';

        sampaiInput.value =
            '';

        dateInput.value =
            '';

        clearDateRange.classList.remove(
            'flex'
        );

        clearDateRange.classList.add(
            'hidden'
        );

        viewDate =
            new Date(
                today().getFullYear(),
                today().getMonth(),
                1
            );

        pickerYear =
            viewDate.getFullYear();

        pickerMonth =
            viewDate.getMonth();

        pickerView =
            'calendar';

        renderPicker();
        updateDateInfo();
    }

    dateInput?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (
                datePanel.classList.contains(
                    'hidden'
                )
            ) {
                openDatePicker();
            } else {
                closeDatePicker();
            }
        }
    );

    clearDateRange?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            clearDateValue();
        }
    );

    datePrev?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (
                pickerView !==
                'calendar'
            ) {
                return;
            }

            viewDate =
                new Date(
                    viewDate.getFullYear(),
                    viewDate.getMonth() - 1,
                    1
                );

            pickerYear =
                viewDate.getFullYear();

            pickerMonth =
                viewDate.getMonth();

            renderPicker();
        }
    );

    dateNext?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (
                pickerView !==
                'calendar'
            ) {
                return;
            }

            viewDate =
                new Date(
                    viewDate.getFullYear(),
                    viewDate.getMonth() + 1,
                    1
                );

            pickerYear =
                viewDate.getFullYear();

            pickerMonth =
                viewDate.getMonth();

            renderPicker();
        }
    );

    applyPickerButton?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            applyDateRange();
        }
    );

    clearPickerButton?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            clearDateValue();
        }
    );

    /* ==========================================================================
       STATUS DROPDOWN
       ========================================================================== */

    const statusDropdownButton =
        document.getElementById(
            'statusDropdownButton'
        );

    const statusDropdownPanel =
        document.getElementById(
            'statusDropdownPanel'
        );

    const statusDropdownIcon =
        document.getElementById(
            'statusDropdownIcon'
        );

    const statusCheckboxes =
        Array.from(
            document.querySelectorAll(
                '.status-checkbox'
            )
        );

    const statusCount =
        document.getElementById(
            'statusCount'
        );

    const statusPanelCount =
        document.getElementById(
            'statusPanelCount'
        );

    const statusSummary =
        document.getElementById(
            'statusSummary'
        );

    const selectAllStatus =
        document.getElementById(
            'selectAllStatus'
        );

    const clearAllStatus =
        document.getElementById(
            'clearAllStatus'
        );

    const closeStatusDropdown =
        document.getElementById(
            'closeStatusDropdown'
        );

    function openStatusDropdownPanel() {
        closeDatePicker();

        statusDropdownPanel.classList.remove(
            'hidden'
        );

        statusDropdownIcon.classList.add(
            'rotate-180'
        );

        updateStatusSummary();
    }

    function closeStatusDropdownPanel() {
        statusDropdownPanel.classList.add(
            'hidden'
        );

        statusDropdownIcon.classList.remove(
            'rotate-180'
        );
    }

    function updateStatusSummary() {
        const checked =
            statusCheckboxes
                .filter(
                    checkbox =>
                        checkbox.checked
                )
                .map(
                    checkbox => {
                        const label =
                            checkbox.parentElement
                                ?.querySelector(
                                    '.archive-status-item-label'
                                );

                        return label
                            ? label.textContent.trim()
                            : '';
                    }
                )
                .filter(Boolean);

        const count =
            checked.length;

        statusCount.textContent =
            count +
            ' dipilih';

        statusPanelCount.textContent =
            count +
            ' dipilih';

        if (!count) {
            statusSummary.textContent =
                'Semua status';

            return;
        }

        if (count === 1) {
            statusSummary.textContent =
                checked[0];

            return;
        }

        statusSummary.textContent =
            checked
                .slice(0, 2)
                .join(', ') +
            (
                count > 2
                    ? ' +' +
                        (
                            count - 2
                        )
                    : ''
            );
    }

    statusDropdownButton?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (
                statusDropdownPanel.classList.contains(
                    'hidden'
                )
            ) {
                openStatusDropdownPanel();
            } else {
                closeStatusDropdownPanel();
            }
        }
    );

    statusCheckboxes.forEach(
        checkbox => {
            checkbox.addEventListener(
                'change',
                updateStatusSummary
            );
        }
    );

    selectAllStatus?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            statusCheckboxes.forEach(
                checkbox => {
                    checkbox.checked = true;
                }
            );

            updateStatusSummary();
        }
    );

    clearAllStatus?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            statusCheckboxes.forEach(
                checkbox => {
                    checkbox.checked = false;
                }
            );

            updateStatusSummary();
        }
    );

    closeStatusDropdown?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            closeStatusDropdownPanel();
        }
    );

    /* ==========================================================================
       CLICK OUTSIDE
       ========================================================================== */

    document.addEventListener(
        'click',
        function (event) {
            if (
                !event.target.closest(
                    '.archive-date-picker'
                )
            ) {
                closeDatePicker();
            }

            if (
                !event.target.closest(
                    '.archive-status-dropdown'
                )
            ) {
                closeStatusDropdownPanel();
            }
        }
    );

    /* ==========================================================================
       FILTER VALIDATION
       ========================================================================== */

    document
        .getElementById('filterForm')
        ?.addEventListener(
            'submit',
            function (event) {
                const start =
                    parseDate(
                        dariInput.value
                    );

                const end =
                    parseDate(
                        sampaiInput.value
                    );

                if (
                    !start &&
                    !end
                ) {
                    return;
                }

                if (
                    !start ||
                    !end
                ) {
                    event.preventDefault();

                    showWarning(
                        'Rentang tanggal belum lengkap',
                        'Pilih tanggal mulai dan tanggal akhir.'
                    );

                    return;
                }

                if (
                    isAfter(
                        start,
                        end
                    )
                ) {
                    event.preventDefault();

                    showWarning(
                        'Rentang tanggal tidak valid',
                        'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.'
                    );
                }
            }
        );

    /* ==========================================================================
       DELETE CONFIRMATION
       ========================================================================== */

    document
        .querySelectorAll('.delete-btn')
        .forEach(
            function (button) {
                button.addEventListener(
                    'click',
                    function (event) {
                        event.preventDefault();
                        event.stopPropagation();

                        const form =
                            this.closest(
                                '.delete-form'
                            );

                        if (!form) {
                            return;
                        }

                        if (
                            typeof Swal !==
                            'undefined'
                        ) {
                            Swal.fire({
                                title:
                                    'Hapus Disposisi?',
                                text:
                                    'Data disposisi akan dipindahkan ke tempat sampah.',
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
                                    true,
                                customClass: {
                                    popup:
                                        'rounded-2xl',
                                    confirmButton:
                                        'rounded-xl text-xs font-semibold px-4 py-2.5',
                                    cancelButton:
                                        'rounded-xl text-xs font-semibold px-4 py-2.5'
                                }
                            }).then(
                                function (result) {
                                    if (
                                        result.isConfirmed
                                    ) {
                                        form.submit();
                                    }
                                }
                            );

                            return;
                        }

                        if (
                            window.confirm(
                                'Yakin ingin menghapus disposisi ini?'
                            )
                        ) {
                            form.submit();
                        }
                    }
                );
            }
        );

    updateInputDisplay();
    updateDateInfo();
    updateStatusSummary();
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection