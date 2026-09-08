```blade
@extends('layouts.app')

@section('title', 'Surat Keluar')

@section('content')

@php
    /* ================================================================
       USER & ROLE
    ================================================================ */
    $user = auth()->user();
    $userRole = strtolower(trim((string) ($user->role ?? $user->jabatan ?? '')));
    $userRole = $userRole === 'staf' ? 'staff' : $userRole;
    $canManage = in_array($userRole, ['admin', 'pimpinan'], true);

    /* ================================================================
       FILTER KATEGORI
    ================================================================ */
    $selectedKategori = collect(request('kategori_id', []))
        ->filter(fn ($id) => is_scalar($id))
        ->map(fn ($id) => (string) $id)
        ->unique()
        ->values()
        ->all();

    /* ================================================================
       FILTER STATUS
    ================================================================ */
    $selectedStatus = collect(request('status', []))
        ->filter(fn ($status) => is_scalar($status))
        ->map(fn ($status) => strtolower(trim((string) $status)))
        ->filter(fn ($status) => in_array($status, [
            'draf',
            'diproses',
            'disetujui',
            'dikirim',
            'diarsipkan',
        ], true))
        ->unique()
        ->values()
        ->all();

    $statusOptions = [
        'draf' => 'Draf',
        'diproses' => 'Diproses',
        'disetujui' => 'Disetujui',
        'dikirim' => 'Dikirim',
        'diarsipkan' => 'Diarsipkan',
    ];

    $statusBadgeClasses = [
        'draf' => 'bg-slate-50 text-slate-700 border-slate-200',
        'diproses' => 'bg-amber-50 text-amber-700 border-amber-200',
        'disetujui' => 'bg-blue-50 text-blue-700 border-blue-200',
        'dikirim' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'diarsipkan' => 'bg-purple-50 text-purple-700 border-purple-200',
    ];

    /* ================================================================
       FILTER TANGGAL
    ================================================================ */
    $dariTanggal = request('dari_tanggal');
    $sampaiTanggal = request('sampai_tanggal');
    $visibleDateRange = '';

    try {
        if ($dariTanggal && $sampaiTanggal) {
            $visibleDateRange =
                \Illuminate\Support\Carbon::parse($dariTanggal)->format('d/m/Y')
                . ' - ' .
                \Illuminate\Support\Carbon::parse($sampaiTanggal)->format('d/m/Y');
        } elseif ($dariTanggal) {
            $visibleDateRange = \Illuminate\Support\Carbon::parse($dariTanggal)->format('d/m/Y');
        } elseif ($sampaiTanggal) {
            $visibleDateRange = \Illuminate\Support\Carbon::parse($sampaiTanggal)->format('d/m/Y');
        }
    } catch (\Throwable $e) {
        $visibleDateRange = '';
    }

    $hasFilters =
        request()->filled('search')
        || !empty($selectedKategori)
        || !empty($selectedStatus)
        || request()->filled('dari_tanggal')
        || request()->filled('sampai_tanggal');
@endphp

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

<style>
/* ================================================================
   DATE PICKER
================================================================ */
.date-range-input {
    cursor: pointer;
}

.date-range-input::selection {
    background: transparent;
}

.daterangepicker {
    z-index: 99999 !important;
    width: auto;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #fff;
    box-shadow:
        0 24px 70px rgba(15, 23, 42, .18),
        0 8px 24px rgba(15, 23, 42, .08);
    padding: 11px;
    font-family: inherit;
}

.daterangepicker .calendar-table {
    border: 0;
    background: transparent;
}

.daterangepicker .calendar-table table {
    border-collapse: separate;
    border-spacing: 3px;
}

.daterangepicker .calendar {
    min-width: 280px;
}

.daterangepicker .calendar-table th {
    color: #94a3b8;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
}

.daterangepicker .calendar-table th.month {
    height: 42px;
    padding: 0;
}

.daterangepicker .calendar-table td {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    transition: background-color .15s ease, color .15s ease;
}

.daterangepicker td.available:hover,
.daterangepicker th.available:hover {
    background: #eff6ff;
    color: #2563eb;
}

.daterangepicker td.in-range {
    background: #eff6ff;
    color: #2563eb;
}

.daterangepicker td.active,
.daterangepicker td.active:hover {
    background: #2563eb;
    color: #fff;
    border-radius: 999px;
}

.daterangepicker td.start-date {
    border-radius: 999px 0 0 999px;
}

.daterangepicker td.end-date {
    border-radius: 0 999px 999px 0;
}

.daterangepicker td.start-date.end-date {
    border-radius: 999px;
}

.daterangepicker td.off,
.daterangepicker td.off.in-range {
    background: transparent;
    color: #cbd5e1;
}

.daterangepicker .prev,
.daterangepicker .next {
    border-radius: 9px;
    color: #64748b;
    transition: background-color .15s ease, color .15s ease;
}

.daterangepicker .prev:hover,
.daterangepicker .next:hover {
    background: #eff6ff;
    color: #2563eb;
}

.daterangepicker .drp-buttons {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 7px;
    border-top: 1px solid #e2e8f0;
    margin-top: 8px;
    padding: 11px 4px 2px;
}

.daterangepicker .drp-selected {
    margin-right: auto;
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
}

.daterangepicker .applyBtn,
.daterangepicker .cancelBtn {
    border-radius: 10px;
    padding: 9px 14px;
    font-family: inherit;
    font-size: 11px;
    font-weight: 700;
}

.daterangepicker .applyBtn {
    border: 0;
    background: #2563eb;
    color: #fff;
}

.daterangepicker .applyBtn:hover {
    background: #1d4ed8;
}

.daterangepicker .cancelBtn {
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
}

.daterangepicker .cancelBtn:hover {
    background: #f1f5f9;
    color: #334155;
}

/* ================================================================
   CUSTOM MONTH / YEAR HEADER
================================================================ */
.custom-date-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    width: 100%;
    min-height: 38px;
}

.custom-date-header-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 32px;
    border: 1px solid transparent;
    border-radius: 9px;
    background: #f8fafc;
    color: #334155;
    padding: 7px 10px;
    font-family: inherit;
    font-size: 12px;
    font-weight: 800;
    line-height: 1;
    cursor: pointer !important;
    user-select: none;
    transition:
        background-color .15s ease,
        color .15s ease,
        border-color .15s ease,
        transform .15s ease;
}

.custom-date-header-button:hover {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #2563eb;
}

.custom-date-header-button:active {
    transform: scale(.96);
}

.custom-date-header-button.month {
    min-width: 96px;
}

.custom-date-header-button.year {
    min-width: 64px;
}

/* ================================================================
   CUSTOM MONTH / YEAR PANEL
================================================================ */
.custom-date-panel {
    position: fixed;
    z-index: 100001;
    width: 320px;
    max-width: calc(100vw - 24px);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #fff;
    box-shadow:
        0 24px 65px rgba(15, 23, 42, .18),
        0 8px 24px rgba(15, 23, 42, .08);
    padding: 14px;
    animation: customDatePanelShow .12s ease-out;
}

@keyframes customDatePanelShow {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.custom-date-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 12px;
}

.custom-date-panel-title {
    flex: 1;
    min-width: 0;
    color: #334155;
    font-size: 13px;
    font-weight: 800;
    text-align: center;
}

.custom-date-panel-nav {
    display: inline-flex;
    width: 32px;
    height: 32px;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9px;
    background: #f8fafc;
    color: #64748b;
    font-size: 21px;
    line-height: 1;
    cursor: pointer;
    transition: background-color .15s ease, color .15s ease;
}

.custom-date-panel-nav:hover {
    background: #eff6ff;
    color: #2563eb;
}

.custom-date-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
}

.custom-date-option {
    min-height: 42px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    color: #475569;
    padding: 0 6px;
    font-family: inherit;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition:
        background-color .15s ease,
        border-color .15s ease,
        color .15s ease,
        transform .15s ease;
}

.custom-date-option:hover:not(:disabled) {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #2563eb;
}

.custom-date-option:active:not(:disabled) {
    transform: scale(.97);
}

.custom-date-option.active {
    border-color: #2563eb;
    background: #2563eb;
    color: #fff;
}

.custom-date-option.current:not(.active) {
    box-shadow: inset 0 0 0 1px #93c5fd;
}

.custom-date-option:disabled {
    opacity: .35;
    cursor: not-allowed;
}

/* ================================================================
   FILTER DROPDOWN
================================================================ */
.filter-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.filter-dropdown {
    position: relative;
    min-width: 0;
}

.filter-dropdown-trigger {
    display: flex;
    width: 100%;
    min-height: 56px;
    align-items: center;
    gap: 12px;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    padding: 10px 14px;
    color: #334155;
    text-align: left;
    cursor: pointer;
    transition:
        border-color .15s ease,
        background-color .15s ease,
        box-shadow .15s ease;
}

.filter-dropdown-trigger:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.filter-dropdown-trigger[aria-expanded="true"] {
    border-color: #93c5fd;
    background: #f8fbff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
}

.filter-dropdown-trigger.status-trigger[aria-expanded="true"] {
    border-color: #fde68a;
    background: #fffcf0;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, .08);
}

.filter-dropdown-trigger-content {
    display: flex;
    min-width: 0;
    flex: 1;
    align-items: center;
    gap: 10px;
}

.filter-dropdown-icon {
    display: inline-flex;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #eff6ff;
    color: #2563eb;
}

.filter-dropdown-icon.status {
    background: #fffbeb;
    color: #d97706;
}

.filter-dropdown-text {
    min-width: 0;
    flex: 1;
}

.filter-dropdown-title {
    display: block;
    overflow: hidden;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
    line-height: 1.25;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.filter-dropdown-subtitle {
    display: block;
    margin-top: 3px;
    overflow: hidden;
    color: #94a3b8;
    font-size: 10px;
    font-weight: 500;
    line-height: 1.25;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.filter-dropdown-count {
    display: inline-flex;
    min-width: 72px;
    min-height: 25px;
    flex: 0 0 auto;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0 9px;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.filter-dropdown-count.category {
    border: 1px solid #dbeafe;
    background: #eff6ff;
    color: #2563eb;
}

.filter-dropdown-count.status {
    border: 1px solid #fef3c7;
    background: #fffbeb;
    color: #d97706;
}

.filter-dropdown-arrow {
    flex: 0 0 auto;
    color: #94a3b8;
    transition: transform .15s ease, color .15s ease;
}

.filter-dropdown-trigger[aria-expanded="true"] .filter-dropdown-arrow {
    transform: rotate(180deg);
    color: #2563eb;
}

.filter-dropdown-trigger.status-trigger[aria-expanded="true"] .filter-dropdown-arrow {
    color: #d97706;
}

.filter-dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    left: 0;
    z-index: 10020;
    display: none;
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    background: #fff;
    box-shadow:
        0 20px 50px rgba(15, 23, 42, .14),
        0 6px 18px rgba(15, 23, 42, .07);
}

.filter-dropdown.open .filter-dropdown-menu {
    display: block;
    animation: filterDropdownShow .12s ease-out;
}

@keyframes filterDropdownShow {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.filter-dropdown-menu-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
    padding: 12px 14px;
}

.filter-dropdown-menu-title-wrap {
    min-width: 0;
}

.filter-dropdown-menu-title {
    display: block;
    color: #334155;
    font-size: 12px;
    font-weight: 800;
}

.filter-dropdown-menu-description {
    display: block;
    margin-top: 3px;
    color: #94a3b8;
    font-size: 10px;
}

.filter-dropdown-actions {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    flex: 0 0 auto;
}

.filter-dropdown-action {
    border: 0;
    border-radius: 7px;
    background: transparent;
    padding: 6px 8px;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
}

.filter-dropdown-action.category {
    color: #2563eb;
}

.filter-dropdown-action.status {
    color: #d97706;
}

.filter-dropdown-action.clear {
    color: #64748b;
}

.filter-dropdown-action.category:hover {
    background: #eff6ff;
}

.filter-dropdown-action.status:hover {
    background: #fffbeb;
}

.filter-dropdown-action.clear:hover {
    background: #f1f5f9;
}

.filter-dropdown-divider {
    color: #cbd5e1;
    font-size: 10px;
}

.filter-dropdown-options {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    max-height: 280px;
    gap: 8px;
    overflow-y: auto;
    padding: 12px;
}

.filter-dropdown-options::-webkit-scrollbar {
    width: 6px;
}

.filter-dropdown-options::-webkit-scrollbar-track {
    background: #f8fafc;
}

.filter-dropdown-options::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: #cbd5e1;
}

.filter-dropdown-option {
    display: flex;
    min-width: 0;
    min-height: 44px;
    align-items: center;
    gap: 9px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #fff;
    padding: 8px 10px;
    cursor: pointer;
    transition:
        border-color .15s ease,
        background-color .15s ease;
}

.filter-dropdown-option:hover {
    border-color: #bfdbfe;
    background: #f8fbff;
}

.filter-dropdown-option.status-option:hover {
    border-color: #fde68a;
    background: #fffcf3;
}

.filter-dropdown-option:has(input:checked) {
    border-color: #bfdbfe;
    background: #eff6ff;
}

.filter-dropdown-option.status-option:has(input:checked) {
    border-color: #fde68a;
    background: #fffbeb;
}

.filter-dropdown-option input {
    width: 16px;
    height: 16px;
    flex: 0 0 16px;
    margin: 0;
    accent-color: #2563eb;
    cursor: pointer;
}

.filter-dropdown-option span {
    min-width: 0;
    overflow: hidden;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.3;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.filter-dropdown-option:has(input:checked) span {
    color: #1d4ed8;
}

.filter-dropdown-option.status-option:has(input:checked) span {
    color: #b45309;
}

.filter-dropdown-empty {
    padding: 22px 12px;
    color: #94a3b8;
    font-size: 11px;
    text-align: center;
}

.filter-dropdown-menu-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    border-top: 1px solid #e2e8f0;
    background: #fff;
    padding: 10px 14px;
}

.filter-dropdown-footer-count {
    color: #64748b;
    font-size: 10px;
    font-weight: 600;
}

.filter-dropdown-footer-hint {
    color: #cbd5e1;
    font-size: 10px;
}

/* ================================================================
   MOBILE
================================================================ */
@media (max-width: 767px) {
    .filter-row {
        grid-template-columns: 1fr;
    }

    .filter-dropdown-options {
        max-height: calc(80vh - 150px);
        grid-template-columns: 1fr 1fr;
    }

    .daterangepicker {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        right: auto !important;
        bottom: auto !important;
        width: calc(100vw - 20px) !important;
        max-width: 430px;
        max-height: calc(100vh - 24px);
        overflow-y: auto;
        transform: translate(-50%, -50%);
    }

    .daterangepicker .calendar {
        float: none !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        margin: 0 !important;
    }

    .daterangepicker .calendar.right {
        margin-top: 10px !important;
    }

    .daterangepicker .calendar-table td {
        width: 38px;
        height: 38px;
    }

    .custom-date-panel {
        position: fixed !important;
        top: 50% !important;
        left: 50% !important;
        right: auto !important;
        width: calc(100vw - 28px);
        max-width: 380px;
        transform: translate(-50%, -50%);
    }

    body.daterangepicker-open {
        overflow: hidden;
    }
}

@media (max-width: 480px) {
    .filter-dropdown-options {
        grid-template-columns: 1fr;
    }

    .filter-dropdown-menu-header,
    .filter-dropdown-menu-footer {
        padding-left: 12px;
        padding-right: 12px;
    }

    .filter-dropdown-count {
        min-width: 62px;
        padding-left: 7px;
        padding-right: 7px;
    }
}
</style>
@endpush

<div class="space-y-4 sm:space-y-6">
    {{-- PAGE HEADER --}}
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">
                Surat Keluar
            </h1>

            <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                Kelola dan pantau seluruh arsip surat keluar organisasi Anda.
            </p>
        </div>

        <div class="grid w-full grid-cols-3 gap-2 sm:flex sm:w-auto">
            <a
                href="{{ route('export.surat-keluar.excel', request()->query()) }}"
                class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-2.5 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 sm:px-3.5"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Excel</span>
            </a>

            <a
                href="{{ route('export.surat-keluar.pdf', request()->query()) }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-2.5 py-2 text-xs font-semibold text-rose-700 shadow-sm transition hover:bg-rose-100 sm:px-3.5"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <span>PDF</span>
            </a>

            @if($canManage)
                <a
                    href="{{ route('surat-keluar.create') }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-2.5 py-2 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 sm:px-4"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Surat Keluar</span>
                </a>
            @else
                <div></div>
            @endif
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm sm:p-4">
        <form
            method="GET"
            action="{{ route('surat-keluar.index') }}"
            id="filterForm"
            class="space-y-3"
        >
            {{-- SEARCH + DATE + FILTER --}}
            <div class="grid grid-cols-1 gap-2.5 lg:grid-cols-12">
                {{-- SEARCH --}}
                <div class="lg:col-span-5">
                    <label for="search" class="sr-only">
                        Pencarian
                    </label>

                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Cari perihal, nomor surat, atau instansi..."
                            autocomplete="off"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                        >
                    </div>
                </div>

                {{-- DATE RANGE --}}
                <div class="lg:col-span-4">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5v12a2 2 0 002 2z"/>
                            </svg>
                        </div>

                        <input
                            type="text"
                            id="date-range"
                            value="{{ $visibleDateRange }}"
                            readonly
                            autocomplete="off"
                            placeholder="Pilih rentang tanggal..."
                            class="date-range-input h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-10 text-xs font-medium text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                        >

                        <button
                            type="button"
                            id="clearDateRange"
                            title="Hapus tanggal"
                            aria-label="Hapus tanggal"
                            class="{{ $visibleDateRange ? 'flex' : 'hidden' }} absolute inset-y-0 right-0 z-20 w-10 items-center justify-center text-slate-400 transition hover:text-rose-500"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
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
                                href="{{ route('surat-keluar.index') }}"
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

            {{-- KATEGORI + STATUS --}}
            <div class="filter-row">
                {{-- KATEGORI --}}
                <div class="filter-dropdown" id="kategoriDropdown">
                    <button
                        type="button"
                        id="kategoriDropdownButton"
                        class="filter-dropdown-trigger"
                        aria-expanded="false"
                        aria-controls="kategoriDropdownMenu"
                    >
                        <span class="filter-dropdown-icon">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </span>

                        <span class="filter-dropdown-trigger-content">
                            <span class="filter-dropdown-text">
                                <span class="filter-dropdown-title">
                                    Kategori Surat
                                </span>

                                <span
                                    class="filter-dropdown-subtitle"
                                    id="kategoriSummary"
                                >
                                    Semua kategori
                                </span>
                            </span>

                            <span
                                class="filter-dropdown-count category"
                                id="kategoriCount"
                            >
                                {{ count($selectedKategori) }} dipilih
                            </span>
                        </span>

                        <svg
                            class="filter-dropdown-arrow h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 9l6 6l6-6"
                            />
                        </svg>
                    </button>

                    <div
                        id="kategoriDropdownMenu"
                        class="filter-dropdown-menu"
                        role="menu"
                    >
                        <div class="filter-dropdown-menu-header">
                            <div class="filter-dropdown-menu-title-wrap">
                                <span class="filter-dropdown-menu-title">
                                    Pilih Kategori
                                </span>

                                <span class="filter-dropdown-menu-description">
                                    Checkbox dapat dipilih lebih dari satu
                                </span>
                            </div>

                            <div class="filter-dropdown-actions">
                                <button
                                    type="button"
                                    id="selectAllKategori"
                                    class="filter-dropdown-action category"
                                >
                                    Pilih Semua
                                </button>

                                <span class="filter-dropdown-divider">|</span>

                                <button
                                    type="button"
                                    id="clearAllKategori"
                                    class="filter-dropdown-action clear"
                                >
                                    Batalkan
                                </button>
                            </div>
                        </div>

                        @if(isset($kategoris) && $kategoris->count())
                            <div class="filter-dropdown-options">
                                @foreach($kategoris as $kategori)
                                    <label class="filter-dropdown-option">
                                        <input
                                            type="checkbox"
                                            name="kategori_id[]"
                                            value="{{ $kategori->id }}"
                                            class="kategori-checkbox"
                                            @checked(in_array((string) $kategori->id, $selectedKategori, true))
                                        >

                                        <span title="{{ $kategori->nama_kategori }}">
                                            {{ $kategori->nama_kategori }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="filter-dropdown-empty">
                                Belum ada kategori surat.
                            </div>
                        @endif

                        <div class="filter-dropdown-menu-footer">
                            <span
                                class="filter-dropdown-footer-count"
                                id="kategoriFooterCount"
                            >
                                {{ count($selectedKategori) }} kategori dipilih
                            </span>

                            <span class="filter-dropdown-footer-hint">
                                Klik Filter untuk menerapkan
                            </span>
                        </div>
                    </div>
                </div>

                {{-- STATUS --}}
                <div class="filter-dropdown" id="statusDropdown">
                    <button
                        type="button"
                        id="statusDropdownButton"
                        class="filter-dropdown-trigger status-trigger"
                        aria-expanded="false"
                        aria-controls="statusDropdownMenu"
                    >
                        <span class="filter-dropdown-icon status">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z"/>
                            </svg>
                        </span>

                        <span class="filter-dropdown-trigger-content">
                            <span class="filter-dropdown-text">
                                <span class="filter-dropdown-title">
                                    Status Surat
                                </span>

                                <span
                                    class="filter-dropdown-subtitle"
                                    id="statusSummary"
                                >
                                    Semua status
                                </span>
                            </span>

                            <span
                                class="filter-dropdown-count status"
                                id="statusCount"
                            >
                                {{ count($selectedStatus) }} dipilih
                            </span>
                        </span>

                        <svg
                            class="filter-dropdown-arrow h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 9l6 6l6-6"
                            />
                        </svg>
                    </button>

                    <div
                        id="statusDropdownMenu"
                        class="filter-dropdown-menu"
                        role="menu"
                    >
                        <div class="filter-dropdown-menu-header">
                            <div class="filter-dropdown-menu-title-wrap">
                                <span class="filter-dropdown-menu-title">
                                    Pilih Status
                                </span>

                                <span class="filter-dropdown-menu-description">
                                    Checkbox dapat dipilih lebih dari satu
                                </span>
                            </div>

                            <div class="filter-dropdown-actions">
                                <button
                                    type="button"
                                    id="selectAllStatus"
                                    class="filter-dropdown-action status"
                                >
                                    Pilih Semua
                                </button>

                                <span class="filter-dropdown-divider">|</span>

                                <button
                                    type="button"
                                    id="clearAllStatus"
                                    class="filter-dropdown-action clear"
                                >
                                    Batalkan
                                </button>
                            </div>
                        </div>

                        <div class="filter-dropdown-options">
                            @foreach($statusOptions as $value => $label)
                                <label class="filter-dropdown-option status-option">
                                    <input
                                        type="checkbox"
                                        name="status[]"
                                        value="{{ $value }}"
                                        class="status-checkbox"
                                        @checked(in_array($value, $selectedStatus, true))
                                    >

                                    <span>
                                        {{ $label }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="filter-dropdown-menu-footer">
                            <span
                                class="filter-dropdown-footer-count"
                                id="statusFooterCount"
                            >
                                {{ count($selectedStatus) }} status dipilih
                            </span>

                            <span class="filter-dropdown-footer-hint">
                                Klik Filter untuk menerapkan
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[850px] border-collapse whitespace-nowrap text-left text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-[11px]">
                        <th class="px-4 py-3 sm:px-6 sm:py-4">
                            Tanggal Keluar
                        </th>

                        <th class="px-4 py-3 sm:px-6 sm:py-4">
                            Instansi Tujuan
                        </th>

                        <th class="px-4 py-3 sm:px-6 sm:py-4">
                            Perihal
                        </th>

                        <th class="px-4 py-3 sm:px-6 sm:py-4">
                            Kategori
                        </th>

                        <th class="px-4 py-3 sm:px-6 sm:py-4">
                            Status
                        </th>

                        <th class="px-4 py-3 text-center sm:px-6 sm:py-4">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($suratKeluars ?? [] as $s)
                        @php
                            $status = strtolower(trim((string) ($s->status ?? 'draf')));
                            $badgeClass = $statusBadgeClasses[$status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                            $tujuan = $s->pengirim ?? $s->asal_surat ?? '-';
                        @endphp

                        <tr class="transition duration-150 hover:bg-slate-50/60">
                            <td class="px-4 py-3.5 text-slate-500 sm:px-6 sm:py-4">
                                @if($s->tanggal_surat)
                                    @try
                                        {{ \Illuminate\Support\Carbon::parse($s->tanggal_surat)->format('d/m/Y') }}
                                    @catch(\Throwable $e)
                                        -
                                    @endtry
                                @else
                                    -
                                @endif
                            </td>

                            <td class="px-4 py-3.5 text-slate-700 sm:px-6 sm:py-4">
                                <span
                                    class="inline-block max-w-[220px] truncate rounded-lg border border-slate-200/60 bg-slate-100 px-2.5 py-1 font-medium text-slate-700"
                                    title="{{ $tujuan }}"
                                >
                                    {{ $tujuan }}
                                </span>
                            </td>

                            <td
                                class="max-w-xs truncate px-4 py-3.5 font-medium text-slate-800 sm:px-6 sm:py-4"
                                title="{{ $s->perihal ?? '-' }}"
                            >
                                {{ $s->perihal ?? '-' }}
                            </td>

                            <td class="px-4 py-3.5 text-slate-500 sm:px-6 sm:py-4">
                                {{ $s->kategori?->nama_kategori ?? '-' }}
                            </td>

                            <td class="px-4 py-3.5 sm:px-6 sm:py-4">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $badgeClass }}">
                                    {{ $statusOptions[$status] ?? ucfirst($status) }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 text-center sm:px-6 sm:py-4">
                                <div class="inline-flex items-center gap-1">
                                    <a
                                        href="{{ route('surat-keluar.show', $s) }}"
                                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600"
                                        title="Lihat Detail"
                                        aria-label="Lihat detail surat"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0a3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7z"/>
                                        </svg>
                                    </a>

                                    @if($canManage)
                                        <a
                                            href="{{ route('surat-keluar.edit', $s) }}"
                                            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600"
                                            title="Ubah Data"
                                            aria-label="Ubah data surat"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L11.828 15H9v-2.828l9.5-9.5z"/>
                                            </svg>
                                        </a>

                                        <form
                                            action="{{ route('surat-keluar.destroy', $s) }}"
                                            method="POST"
                                            class="delete-form inline"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="button"
                                                class="delete-btn rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                                                title="Hapus Surat"
                                                aria-label="Hapus surat"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11v6m4-6v6m1-10V4a1 1 0 01-1-1h-4a1 1 0 01-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center sm:py-12">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-1.414 0l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>

                                    <p class="text-sm font-semibold text-slate-700 sm:text-base">
                                        Belum ada data surat keluar
                                    </p>

                                    <p class="mt-0.5 max-w-md px-4 text-[11px] text-slate-400 sm:text-xs">
                                        @if($hasFilters)
                                            Tidak ada surat yang sesuai dengan filter yang digunakan.
                                        @else
                                            Belum ada data surat keluar yang tersimpan.
                                        @endif
                                    </p>

                                    @if($hasFilters)
                                        <a
                                            href="{{ route('surat-keluar.index') }}"
                                            class="mt-3 inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-[11px] font-semibold text-white transition hover:bg-slate-800"
                                        >
                                            Reset Filter
                                        </a>
                                    @elseif($canManage)
                                        <a
                                            href="{{ route('surat-keluar.create') }}"
                                            class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-[11px] font-semibold text-white transition hover:bg-blue-700"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Tambah Surat Keluar
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($suratKeluars) && method_exists($suratKeluars, 'hasPages') && $suratKeluars->hasPages())
            <div class="border-t border-slate-100 px-4 py-3 sm:px-6 sm:py-4">
                {{ $suratKeluars->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    'use strict';

    /* ================================================================
       LOAD SCRIPT
    ================================================================ */
    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            const existingScript = document.querySelector(
                'script[src="' + src + '"]'
            );

            if (existingScript) {
                if (
                    (src.includes('jquery') && window.jQuery) ||
                    (src.includes('moment') && window.moment) ||
                    (
                        src.includes('daterangepicker') &&
                        window.jQuery?.fn?.daterangepicker
                    )
                ) {
                    resolve();
                    return;
                }

                existingScript.addEventListener('load', resolve, { once: true });
                existingScript.addEventListener('error', reject, { once: true });
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = false;
            script.onload = resolve;
            script.onerror = reject;

            document.head.appendChild(script);
        });
    }

    /* ================================================================
       INIT ASSETS
    ================================================================ */
    async function initializePageAssets() {
        try {
            if (!window.jQuery) {
                await loadScript(
                    'https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js'
                );
            }

            if (!window.moment) {
                await loadScript(
                    'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js'
                );
            }

            if (!window.jQuery.fn.daterangepicker) {
                await loadScript(
                    'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js'
                );
            }

            initializePage();
        } catch (error) {
            console.error(
                'Gagal memuat komponen Surat Keluar:',
                error
            );
        }
    }

    /* ================================================================
       PAGE
    ================================================================ */
    function initializePage() {
        const $ = window.jQuery;

        /* ============================================================
           FILTER DROPDOWN
        ============================================================ */
        const kategoriDropdown = document.getElementById('kategoriDropdown');
        const statusDropdown = document.getElementById('statusDropdown');
        const kategoriButton = document.getElementById('kategoriDropdownButton');
        const statusButton = document.getElementById('statusDropdownButton');
        const kategoriCount = document.getElementById('kategoriCount');
        const statusCount = document.getElementById('statusCount');
        const kategoriSummary = document.getElementById('kategoriSummary');
        const statusSummary = document.getElementById('statusSummary');
        const kategoriFooterCount = document.getElementById('kategoriFooterCount');
        const statusFooterCount = document.getElementById('statusFooterCount');

        const kategoriCheckboxes =
            document.querySelectorAll('.kategori-checkbox');

        const statusCheckboxes =
            document.querySelectorAll('.status-checkbox');

        function closeFilterDropdown(dropdown) {
            if (!dropdown) {
                return;
            }

            dropdown.classList.remove('open');

            const button =
                dropdown.querySelector('.filter-dropdown-trigger');

            if (button) {
                button.setAttribute('aria-expanded', 'false');
            }
        }

        function closeAllFilterDropdowns() {
            closeFilterDropdown(kategoriDropdown);
            closeFilterDropdown(statusDropdown);
        }

        function openFilterDropdown(dropdown) {
            if (!dropdown) {
                return;
            }

            if (dropdown === kategoriDropdown) {
                closeFilterDropdown(statusDropdown);
            }

            if (dropdown === statusDropdown) {
                closeFilterDropdown(kategoriDropdown);
            }

            dropdown.classList.add('open');

            const button =
                dropdown.querySelector('.filter-dropdown-trigger');

            if (button) {
                button.setAttribute('aria-expanded', 'true');
            }
        }

        function getCheckedLabels(selector) {
            return Array.from(
                document.querySelectorAll(selector + ':checked')
            )
                .map(function (checkbox) {
                    const label = checkbox.closest('label');
                    const span = label?.querySelector('span');

                    return span?.textContent.trim() || '';
                })
                .filter(Boolean);
        }

        function updateKategoriFilter() {
            const checked =
                document.querySelectorAll(
                    '.kategori-checkbox:checked'
                );

            const count = checked.length;

            if (kategoriCount) {
                kategoriCount.textContent = count + ' dipilih';
            }

            if (kategoriFooterCount) {
                kategoriFooterCount.textContent =
                    count + ' kategori dipilih';
            }

            if (kategoriSummary) {
                const labels =
                    getCheckedLabels('.kategori-checkbox');

                if (!labels.length) {
                    kategoriSummary.textContent = 'Semua kategori';
                } else if (labels.length <= 2) {
                    kategoriSummary.textContent = labels.join(', ');
                } else {
                    kategoriSummary.textContent =
                        labels.length + ' kategori dipilih';
                }
            }
        }

        function updateStatusFilter() {
            const checked =
                document.querySelectorAll(
                    '.status-checkbox:checked'
                );

            const count = checked.length;

            if (statusCount) {
                statusCount.textContent = count + ' dipilih';
            }

            if (statusFooterCount) {
                statusFooterCount.textContent =
                    count + ' status dipilih';
            }

            if (statusSummary) {
                const labels =
                    getCheckedLabels('.status-checkbox');

                if (!labels.length) {
                    statusSummary.textContent = 'Semua status';
                } else if (labels.length <= 2) {
                    statusSummary.textContent = labels.join(', ');
                } else {
                    statusSummary.textContent =
                        labels.length + ' status dipilih';
                }
            }
        }

        kategoriButton?.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (
                kategoriDropdown?.classList.contains('open')
            ) {
                closeFilterDropdown(kategoriDropdown);
            } else {
                openFilterDropdown(kategoriDropdown);
            }
        });

        statusButton?.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (
                statusDropdown?.classList.contains('open')
            ) {
                closeFilterDropdown(statusDropdown);
            } else {
                openFilterDropdown(statusDropdown);
            }
        });

        kategoriCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', updateKategoriFilter);
        });

        statusCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', updateStatusFilter);
        });

        document
            .getElementById('selectAllKategori')
            ?.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                kategoriCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = true;
                });

                updateKategoriFilter();
            });

        document
            .getElementById('clearAllKategori')
            ?.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                kategoriCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                });

                updateKategoriFilter();
            });

        document
            .getElementById('selectAllStatus')
            ?.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                statusCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = true;
                });

                updateStatusFilter();
            });

        document
            .getElementById('clearAllStatus')
            ?.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                statusCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                });

                updateStatusFilter();
            });

        document.addEventListener('click', function (event) {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            if (!target.closest('.filter-dropdown')) {
                closeAllFilterDropdowns();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAllFilterDropdowns();
            }
        });

        updateKategoriFilter();
        updateStatusFilter();

        /* ============================================================
           DATE PICKER
        ============================================================ */
        const dateInput = $('#date-range');
        const dariTanggal = $('#dari_tanggal');
        const sampaiTanggal = $('#sampai_tanggal');
        const clearDateButton = $('#clearDateRange');

        if (
            !dateInput.length ||
            !$.fn.daterangepicker
        ) {
            return;
        }

        const monthNames = [
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

        const dayNames = [
            'Mg',
            'Sn',
            'Sl',
            'Rb',
            'Km',
            'Jm',
            'Sb'
        ];

        const MIN_YEAR = 2000;
        const MAX_YEAR = new Date().getFullYear() + 10;

        const pickerOptions = {
            autoUpdateInput: false,
            showDropdowns: false,
            minYear: MIN_YEAR,
            maxYear: MAX_YEAR,
            linkedCalendars: true,
            alwaysShowCalendars: true,
            autoApply: false,
            opens: 'left',
            drops: 'down',
            parentEl: 'body',
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Terapkan',
                cancelLabel: 'Bersihkan',
                fromLabel: 'Dari',
                toLabel: 'Sampai',
                customRangeLabel: 'Pilih Rentang',
                weekLabel: 'Mg',
                daysOfWeek: dayNames,
                monthNames: monthNames,
                firstDay: 1
            }
        };

        const startValue = dariTanggal.val();
        const endValue = sampaiTanggal.val();

        if (startValue && endValue) {
            const startMoment =
                moment(startValue, 'YYYY-MM-DD', true);

            const endMoment =
                moment(endValue, 'YYYY-MM-DD', true);

            if (
                startMoment.isValid() &&
                endMoment.isValid()
            ) {
                pickerOptions.startDate = startMoment;
                pickerOptions.endDate = endMoment;
            }
        }

        dateInput.daterangepicker(pickerOptions);

        const picker =
            dateInput.data('daterangepicker');

        if (!picker) {
            return;
        }

        let customPanel = null;
        let activePanel = null;
        let renderingHeader = false;

        /* ============================================================
           PANEL HELPERS
        ============================================================ */
        function removeCustomPanel() {
            if (customPanel) {
                customPanel.remove();
                customPanel = null;
            }

            activePanel = null;
        }

        function getCalendarMoment(side) {
            if (
                side === 'right' &&
                picker.rightCalendar?.month
            ) {
                return picker.rightCalendar.month.clone();
            }

            if (picker.leftCalendar?.month) {
                return picker.leftCalendar.month.clone();
            }

            return moment();
        }

        function createHeaderButton(text, type, side) {
            const button = document.createElement('button');

            button.type = 'button';
            button.className =
                'custom-date-header-button ' + type;

            button.dataset.type = type;
            button.dataset.side = side;
            button.textContent = text;

            return button;
        }

        /* ============================================================
           RENDER HEADER
        ============================================================ */
        function renderHeaderButtons() {
            if (
                !picker ||
                !picker.container ||
                renderingHeader
            ) {
                return;
            }

            renderingHeader = true;

            try {
                const headers =
                    picker.container.find(
                        '.calendar thead tr:first-child th.month'
                    );

                headers.each(function (index) {
                    const side =
                        index === 0 ? 'left' : 'right';

                    const calendarMoment =
                        getCalendarMoment(side);

                    this.innerHTML = '';
                    this.classList.add(
                        'custom-month-year-header'
                    );

                    const wrapper =
                        document.createElement('div');

                    wrapper.className =
                        'custom-date-header';

                    const monthButton =
                        createHeaderButton(
                            monthNames[calendarMoment.month()],
                            'month',
                            side
                        );

                    const yearButton =
                        createHeaderButton(
                            calendarMoment.year(),
                            'year',
                            side
                        );

                    wrapper.appendChild(monthButton);
                    wrapper.appendChild(yearButton);
                    this.appendChild(wrapper);
                });
            } finally {
                renderingHeader = false;
            }
        }

        /* ============================================================
           POSITION CUSTOM PANEL
        ============================================================ */
        function positionPanel(panelElement, anchor) {
            if (window.innerWidth <= 767) {
                panelElement.style.left = '50%';
                panelElement.style.top = '50%';
                return;
            }

            const rect = anchor.getBoundingClientRect();
            const panelWidth = panelElement.offsetWidth || 320;
            const panelHeight = panelElement.offsetHeight || 280;

            let left = rect.left;
            let top = rect.bottom + 8;

            if (
                left + panelWidth >
                window.innerWidth - 12
            ) {
                left =
                    window.innerWidth -
                    panelWidth -
                    12;
            }

            if (left < 12) {
                left = 12;
            }

            if (
                top + panelHeight >
                window.innerHeight - 12
            ) {
                top =
                    rect.top -
                    panelHeight -
                    8;
            }

            if (top < 12) {
                top = 12;
            }

            panelElement.style.left = left + 'px';
            panelElement.style.top = top + 'px';
        }

        /* ============================================================
           CREATE PANEL
        ============================================================ */
        function createPanelBase(title, isYearPanel) {
            removeCustomPanel();

            customPanel =
                document.createElement('div');

            customPanel.className =
                'custom-date-panel' +
                (isYearPanel ? ' year-panel' : '');

            customPanel.setAttribute('role', 'dialog');
            customPanel.setAttribute('aria-modal', 'false');

            const header =
                document.createElement('div');

            header.className =
                'custom-date-panel-header';

            const previous =
                document.createElement('button');

            previous.type = 'button';
            previous.className = 'custom-date-panel-nav';
            previous.dataset.action = 'previous';
            previous.setAttribute(
                'aria-label',
                'Sebelumnya'
            );
            previous.innerHTML = '&lsaquo;';

            const titleElement =
                document.createElement('div');

            titleElement.className =
                'custom-date-panel-title';

            titleElement.textContent =
                title;

            const next =
                document.createElement('button');

            next.type = 'button';
            next.className = 'custom-date-panel-nav';
            next.dataset.action = 'next';
            next.setAttribute(
                'aria-label',
                'Berikutnya'
            );
            next.innerHTML = '&rsaquo;';

            header.appendChild(previous);
            header.appendChild(titleElement);
            header.appendChild(next);

            const grid =
                document.createElement('div');

            grid.className =
                'custom-date-grid';

            customPanel.appendChild(header);
            customPanel.appendChild(grid);

            document.body.appendChild(customPanel);

            return {
                panel: customPanel,
                grid,
                title: titleElement,
                previous,
                next
            };
        }

        /* ============================================================
           MONTH PANEL
        ============================================================ */
        function openMonthPanel(side, anchor) {
            const current =
                getCalendarMoment(side);

            const ui =
                createPanelBase(
                    String(current.year()),
                    false
                );

            activePanel = {
                type: 'month',
                side,
                year: current.year()
            };

            function renderMonths() {
                ui.title.textContent =
                    String(activePanel.year);

                ui.grid.innerHTML = '';

                monthNames.forEach(
                    function (monthName, monthIndex) {
                        const button =
                            document.createElement(
                                'button'
                            );

                        button.type = 'button';
                        button.className =
                            'custom-date-option';

                        if (
                            activePanel.year === current.year() &&
                            monthIndex === current.month()
                        ) {
                            button.classList.add('active');
                        }

                        button.textContent =
                            monthName;

                        button.dataset.action =
                            'select-month';

                        button.dataset.side =
                            side;

                        button.dataset.year =
                            activePanel.year;

                        button.dataset.month =
                            monthIndex;

                        ui.grid.appendChild(button);
                    }
                );

                positionPanel(
                    ui.panel,
                    anchor
                );
            }

            ui.previous.addEventListener(
                'click',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    activePanel.year--;

                    if (
                        activePanel.year <
                        MIN_YEAR
                    ) {
                        activePanel.year =
                            MIN_YEAR;
                    }

                    renderMonths();
                }
            );

            ui.next.addEventListener(
                'click',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    activePanel.year++;

                    if (
                        activePanel.year >
                        MAX_YEAR
                    ) {
                        activePanel.year =
                            MAX_YEAR;
                    }

                    renderMonths();
                }
            );

            renderMonths();
        }

        /* ============================================================
           YEAR PANEL
        ============================================================ */
        function openYearPanel(side, anchor) {
            const current =
                getCalendarMoment(side);

            const pageSize = 12;

            let pageStart =
                Math.floor(
                    current.year() /
                    pageSize
                ) * pageSize;

            if (pageStart < MIN_YEAR) {
                pageStart = MIN_YEAR;
            }

            const ui =
                createPanelBase(
                    pageStart +
                    ' - ' +
                    (pageStart + pageSize - 1),
                    true
                );

            activePanel = {
                type: 'year',
                side
            };

            function renderYears() {
                ui.title.textContent =
                    pageStart +
                    ' - ' +
                    (pageStart + pageSize - 1);

                ui.grid.innerHTML = '';

                for (
                    let index = 0;
                    index < pageSize;
                    index++
                ) {
                    const year =
                        pageStart + index;

                    const button =
                        document.createElement(
                            'button'
                        );

                    button.type = 'button';
                    button.className =
                        'custom-date-option';

                    button.textContent =
                        String(year);

                    button.dataset.action =
                        'select-year';

                    button.dataset.side =
                        side;

                    button.dataset.year =
                        year;

                    if (year === current.year()) {
                        button.classList.add('active');
                    }

                    if (
                        year ===
                        new Date().getFullYear()
                    ) {
                        button.classList.add('current');
                    }

                    if (
                        year < MIN_YEAR ||
                        year > MAX_YEAR
                    ) {
                        button.disabled = true;
                    }

                    ui.grid.appendChild(button);
                }

                positionPanel(
                    ui.panel,
                    anchor
                );
            }

            ui.previous.addEventListener(
                'click',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    pageStart -= pageSize;

                    if (pageStart < MIN_YEAR) {
                        pageStart = MIN_YEAR;
                    }

                    renderYears();
                }
            );

            ui.next.addEventListener(
                'click',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    pageStart += pageSize;

                    const maxPageStart =
                        Math.floor(
                            MAX_YEAR /
                            pageSize
                        ) * pageSize;

                    if (pageStart > maxPageStart) {
                        pageStart =
                            maxPageStart;
                    }

                    renderYears();
                }
            );

            renderYears();
        }

        /* ============================================================
           SET CALENDAR MONTH
        ============================================================ */
        function setCalendarMonth(
            side,
            year,
            month
        ) {
            year = parseInt(year, 10);
            month = parseInt(month, 10);

            if (
                Number.isNaN(year) ||
                Number.isNaN(month)
            ) {
                return;
            }

            if (
                year < MIN_YEAR ||
                year > MAX_YEAR ||
                month < 0 ||
                month > 11
            ) {
                return;
            }

            const target =
                moment([year, month, 1]);

            if (!target.isValid()) {
                return;
            }

            if (side === 'right') {
                picker.rightCalendar.month =
                    target.clone();

                if (picker.linkedCalendars) {
                    picker.leftCalendar.month =
                        target.clone().subtract(1, 'month');
                }
            } else {
                picker.leftCalendar.month =
                    target.clone();

                if (picker.linkedCalendars) {
                    picker.rightCalendar.month =
                        target.clone().add(1, 'month');
                }
            }

            removeCustomPanel();
            picker.updateCalendars();

            setTimeout(
                renderHeaderButtons,
                0
            );
        }

        /* ============================================================
           SET CALENDAR YEAR
        ============================================================ */
        function setCalendarYear(side, year) {
            year = parseInt(year, 10);

            if (Number.isNaN(year)) {
                return;
            }

            const current =
                getCalendarMoment(side);

            setCalendarMonth(
                side,
                year,
                current.month()
            );
        }

        /* ============================================================
           PATCH CALENDAR UPDATE
        ============================================================ */
        const originalUpdateCalendars =
            picker.updateCalendars.bind(picker);

        picker.updateCalendars =
            function () {
                originalUpdateCalendars();

                setTimeout(
                    renderHeaderButtons,
                    0
                );
            };

        /* ============================================================
           CUSTOM HEADER CLICK
        ============================================================ */
        document.addEventListener(
            'click',
            function (event) {
                const target =
                    event.target;

                if (!(target instanceof Element)) {
                    return;
                }

                const monthButton =
                    target.closest(
                        '.custom-date-header-button.month'
                    );

                if (monthButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();

                    openMonthPanel(
                        monthButton.dataset.side,
                        monthButton
                    );

                    return;
                }

                const yearButton =
                    target.closest(
                        '.custom-date-header-button.year'
                    );

                if (yearButton) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();

                    openYearPanel(
                        yearButton.dataset.side,
                        yearButton
                    );

                    return;
                }

                const monthOption =
                    target.closest(
                        '[data-action="select-month"]'
                    );

                if (monthOption) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();

                    setCalendarMonth(
                        monthOption.dataset.side,
                        monthOption.dataset.year,
                        monthOption.dataset.month
                    );

                    return;
                }

                const yearOption =
                    target.closest(
                        '[data-action="select-year"]'
                    );

                if (yearOption) {
                    event.preventDefault();
                    event.stopPropagation();
                    event.stopImmediatePropagation();

                    setCalendarYear(
                        yearOption.dataset.side,
                        yearOption.dataset.year
                    );

                    return;
                }

                if (
                    customPanel &&
                    !customPanel.contains(target)
                ) {
                    removeCustomPanel();
                }
            },
            true
        );

        /* ============================================================
           SHOW
        ============================================================ */
        dateInput.on(
            'show.daterangepicker',
            function () {
                document.body.classList.add(
                    'daterangepicker-open'
                );

                setTimeout(
                    function () {
                        picker.updateCalendars();
                        renderHeaderButtons();
                    },
                    0
                );
            }
        );

        /* ============================================================
           HIDE
        ============================================================ */
        dateInput.on(
            'hide.daterangepicker',
            function () {
                removeCustomPanel();

                document.body.classList.remove(
                    'daterangepicker-open'
                );
            }
        );

        /* ============================================================
           APPLY
        ============================================================ */
        dateInput.on(
            'apply.daterangepicker',
            function (event, selectedPicker) {
                const start =
                    selectedPicker.startDate;

                const end =
                    selectedPicker.endDate;

                if (!start || !end) {
                    return;
                }

                dariTanggal.val(
                    start.format('YYYY-MM-DD')
                );

                sampaiTanggal.val(
                    end.format('YYYY-MM-DD')
                );

                dateInput.val(
                    start.format('DD/MM/YYYY') +
                    ' - ' +
                    end.format('DD/MM/YYYY')
                );

                clearDateButton
                    .removeClass('hidden')
                    .addClass('flex');

                removeCustomPanel();
            }
        );

        /* ============================================================
           CANCEL
        ============================================================ */
        dateInput.on(
            'cancel.daterangepicker',
            function () {
                dariTanggal.val('');
                sampaiTanggal.val('');
                dateInput.val('');

                clearDateButton
                    .removeClass('flex')
                    .addClass('hidden');

                removeCustomPanel();
            }
        );

        /* ============================================================
           CLEAR DATE
        ============================================================ */
        clearDateButton.on(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                dariTanggal.val('');
                sampaiTanggal.val('');
                dateInput.val('');

                clearDateButton
                    .removeClass('flex')
                    .addClass('hidden');

                removeCustomPanel();

                const today =
                    moment();

                picker.setStartDate(
                    today.clone()
                );

                picker.setEndDate(
                    today.clone()
                );

                picker.updateCalendars();
            }
        );

        /* ============================================================
           RESTORE DATE VALUE
        ============================================================ */
        function updateVisibleDate() {
            const start =
                dariTanggal.val();

            const end =
                sampaiTanggal.val();

            if (start && end) {
                const startDate =
                    moment(
                        start,
                        'YYYY-MM-DD',
                        true
                    );

                const endDate =
                    moment(
                        end,
                        'YYYY-MM-DD',
                        true
                    );

                if (
                    startDate.isValid() &&
                    endDate.isValid()
                ) {
                    dateInput.val(
                        startDate.format('DD/MM/YYYY') +
                        ' - ' +
                        endDate.format('DD/MM/YYYY')
                    );

                    clearDateButton
                        .removeClass('hidden')
                        .addClass('flex');

                    return;
                }
            }

            dateInput.val('');

            clearDateButton
                .removeClass('flex')
                .addClass('hidden');
        }

        updateVisibleDate();

        /* ============================================================
           INITIAL HEADER
        ============================================================ */
        setTimeout(
            function () {
                picker.updateCalendars();
                renderHeaderButtons();
            },
            100
        );

        /* ============================================================
           RESIZE
        ============================================================ */
        window.addEventListener(
            'resize',
            function () {
                if (customPanel) {
                    removeCustomPanel();
                }
            }
        );

        /* ============================================================
           FILTER VALIDATION
        ============================================================ */
        const filterForm =
            document.getElementById('filterForm');

        filterForm?.addEventListener(
            'submit',
            function (event) {
                const start =
                    dariTanggal.val();

                const end =
                    sampaiTanggal.val();

                if (
                    start &&
                    end &&
                    start > end
                ) {
                    event.preventDefault();

                    if (
                        typeof window.Swal !==
                        'undefined'
                    ) {
                        window.Swal.fire({
                            icon: 'warning',
                            title: 'Rentang tanggal tidak valid',
                            text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.',
                            confirmButtonText: 'Mengerti',
                            confirmButtonColor: '#2563eb',
                            customClass: {
                                popup: 'rounded-2xl',
                                confirmButton:
                                    'rounded-xl text-xs font-semibold px-4 py-2.5'
                            }
                        });
                    } else {
                        window.alert(
                            'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.'
                        );
                    }
                }
            }
        );

        /* ============================================================
           DELETE CONFIRMATION
        ============================================================ */
        document
            .querySelectorAll('.delete-btn')
            .forEach(function (button) {
                button.addEventListener(
                    'click',
                    function () {
                        const form =
                            this.closest('.delete-form');

                        if (
                            typeof window.Swal !==
                            'undefined'
                        ) {
                            window.Swal.fire({
                                title: 'Hapus Surat Keluar?',
                                text: 'Data yang dihapus akan dipindahkan ke tempat sampah.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#ef4444',
                                cancelButtonColor: '#64748b',
                                confirmButtonText: 'Ya, Hapus!',
                                cancelButtonText: 'Batal',
                                reverseButtons: true,
                                customClass: {
                                    popup: 'rounded-2xl',
                                    confirmButton:
                                        'rounded-xl text-xs font-semibold px-4 py-2.5',
                                    cancelButton:
                                        'rounded-xl text-xs font-semibold px-4 py-2.5'
                                }
                            }).then(
                                function (result) {
                                    if (
                                        result.isConfirmed &&
                                        form
                                    ) {
                                        form.submit();
                                    }
                                }
                            );

                            return;
                        }

                        if (
                            form &&
                            window.confirm(
                                'Yakin ingin menghapus surat keluar ini?'
                            )
                        ) {
                            form.submit();
                        }
                    }
                );
            });
    }

    /* ================================================================
       START
    ================================================================ */
    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initializePageAssets,
            { once: true }
        );
    } else {
        initializePageAssets();
    }
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection
```
