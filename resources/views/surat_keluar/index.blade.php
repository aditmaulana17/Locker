@extends('layouts.app')

@section('title', 'Surat Keluar')

@section('content')

@php
    $user = auth()->user();

    $userRole = strtolower(trim((string) ($user->role ?? $user->jabatan ?? '')));
    $userRole = $userRole === 'staf' ? 'staff' : $userRole;
    $canManage = in_array($userRole, ['admin', 'pimpinan'], true);

    $selectedKategori = collect(request('kategori_id', []))
        ->filter(fn ($id) => is_scalar($id))
        ->map(fn ($id) => (string) $id)
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

    $selectedStatus = collect(request('status', []))
        ->filter(fn ($status) => is_scalar($status))
        ->map(fn ($status) => strtolower(trim((string) $status)))
        ->filter(fn ($status) => array_key_exists($status, $statusOptions))
        ->unique()
        ->values()
        ->all();

    $statusBadgeClasses = [
        'draf' => 'bg-slate-50 text-slate-700 border-slate-200',
        'diproses' => 'bg-amber-50 text-amber-700 border-amber-200',
        'disetujui' => 'bg-blue-50 text-blue-700 border-blue-200',
        'dikirim' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'diarsipkan' => 'bg-purple-50 text-purple-700 border-purple-200',
    ];

    $dariTanggal = request('dari_tanggal');
    $sampaiTanggal = request('sampai_tanggal');
    $visibleDateRange = '';

    try {
        if ($dariTanggal && $sampaiTanggal) {
            $visibleDateRange =
                \Illuminate\Support\Carbon::parse($dariTanggal)->format('d/m/Y') .
                ' - ' .
                \Illuminate\Support\Carbon::parse($sampaiTanggal)->format('d/m/Y');
        } elseif ($dariTanggal) {
            $visibleDateRange =
                \Illuminate\Support\Carbon::parse($dariTanggal)->format('d/m/Y');
        } elseif ($sampaiTanggal) {
            $visibleDateRange =
                \Illuminate\Support\Carbon::parse($sampaiTanggal)->format('d/m/Y');
        }
    } catch (\Throwable $e) {
        $visibleDateRange = '';
    }

    $hasFilters =
        request()->filled('search') ||
        !empty($selectedKategori) ||
        !empty($selectedStatus) ||
        request()->filled('dari_tanggal') ||
        request()->filled('sampai_tanggal');
@endphp

@push('styles')
<style>
/* ==========================================================================
   DATE PICKER
   ========================================================================== */

.date-range-wrapper {
    position: relative;
}

.date-range-input {
    cursor: pointer;
}

.custom-date-picker,
.custom-picker-panel {
    border: 1px solid #cbd5e1;
    background: #fff;
    box-shadow:
        0 24px 70px rgba(15, 23, 42, .18),
        0 8px 25px rgba(15, 23, 42, .08);
}

.custom-date-picker {
    position: fixed;
    z-index: 999999;
    width: 720px;
    max-width: calc(100vw - 20px);
    overflow: hidden;
    border-radius: 18px;
}

.custom-date-picker.hidden,
.custom-picker-panel.hidden {
    display: none;
}

.custom-date-picker-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 13px 15px;
    border-bottom: 1px solid #cbd5e1;
}

.custom-date-picker-title {
    color: #334155;
    font-size: 13px;
    font-weight: 800;
}

.custom-date-picker-close,
.custom-picker-panel-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    background: #f8fafc;
    color: #64748b;
    cursor: pointer;
}

.custom-date-picker-close {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    font-size: 20px;
}

.custom-date-picker-close:hover,
.custom-picker-panel-close:hover {
    background: #f1f5f9;
    color: #ef4444;
}

.custom-date-calendars {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.custom-calendar {
    padding: 15px 17px 13px;
}

.custom-calendar + .custom-calendar {
    border-left: 1px solid #cbd5e1;
}

.custom-calendar-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    margin-bottom: 7px;
}

.custom-calendar-heading {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
    gap: 4px;
}

.custom-calendar-nav {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    border: 0;
    border-radius: 9px;
    background: transparent;
    color: #475569;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
}

.custom-calendar-nav:hover {
    background: #eff6ff;
    color: #2563eb;
}

.custom-calendar-month-button,
.custom-calendar-year-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    min-height: 33px;
    padding: 6px 10px;
    border: 1px solid transparent;
    border-radius: 9px;
    background: #f8fafc;
    color: #334155;
    font-family: inherit;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
}

.custom-calendar-month-button:hover,
.custom-calendar-year-button:hover {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #2563eb;
}

.custom-calendar-month-button::after,
.custom-calendar-year-button::after {
    content: '';
    width: 5px;
    height: 5px;
    margin-top: -3px;
    border-right: 1.5px solid currentColor;
    border-bottom: 1.5px solid currentColor;
    transform: rotate(45deg);
}

.custom-calendar-weekdays,
.custom-calendar-days {
    display: grid;
    grid-template-columns: repeat(7, minmax(0, 1fr));
    gap: 3px;
}

.custom-calendar-weekday {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 27px;
    color: #94a3b8;
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
}

.custom-calendar-day {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #475569;
    font-family: inherit;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
}

.custom-calendar-day:hover {
    background: #eff6ff;
    color: #2563eb;
}

.custom-calendar-day.other-month {
    color: #cbd5e1;
}

.custom-calendar-day.today {
    box-shadow: inset 0 0 0 1px #93c5fd;
    color: #2563eb;
}

.custom-calendar-day.in-range {
    border-radius: 0;
    background: #eff6ff;
    color: #2563eb;
}

.custom-calendar-day.range-start {
    border-radius: 999px 0 0 999px;
    background: #2563eb;
    color: #fff;
}

.custom-calendar-day.range-end {
    border-radius: 0 999px 999px 0;
    background: #2563eb;
    color: #fff;
}

.custom-calendar-day.range-start.range-end {
    border-radius: 999px;
}

.custom-calendar-day.range-start:hover,
.custom-calendar-day.range-end:hover {
    background: #1d4ed8;
    color: #fff;
}

.custom-date-picker-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 14px;
    border-top: 1px solid #cbd5e1;
}

.custom-date-picker-selected {
    min-width: 0;
    color: #64748b;
    font-size: 11px;
    font-weight: 700;
}

.custom-date-picker-actions {
    display: flex;
    align-items: center;
    gap: 7px;
}

.custom-date-picker-button {
    height: 36px;
    padding: 0 14px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #f8fafc;
    color: #475569;
    font-family: inherit;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
}

.custom-date-picker-button:hover {
    background: #f1f5f9;
}

.custom-date-picker-button.apply {
    border-color: #2563eb;
    background: #2563eb;
    color: #fff;
}

.custom-date-picker-button.apply:hover {
    background: #1d4ed8;
}

/* ==========================================================================
   MONTH / YEAR PANEL
   ========================================================================== */

.custom-picker-panel {
    position: fixed;
    z-index: 1000000;
    width: 320px;
    max-width: calc(100vw - 20px);
    padding: 14px;
    border-radius: 16px;
}

.custom-picker-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 7px;
    margin-bottom: 12px;
}

.custom-picker-panel-title {
    flex: 1;
    color: #334155;
    font-size: 13px;
    font-weight: 800;
    text-align: center;
}

.custom-picker-panel-nav,
.custom-picker-panel-close {
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    border-radius: 9px;
    font-size: 19px;
}

.custom-picker-panel-nav {
    background: #f8fafc;
    color: #64748b;
}

.custom-picker-panel-nav:hover {
    background: #eff6ff;
    color: #2563eb;
}

.custom-picker-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 8px;
}

.custom-picker-option {
    min-height: 42px;
    padding: 0 6px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #fff;
    color: #475569;
    font-family: inherit;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition:
        background-color .15s ease,
        border-color .15s ease,
        color .15s ease;
}

.custom-picker-option:hover {
    border-color: #93c5fd;
    background: #eff6ff;
    color: #2563eb;
}

.custom-picker-option.active {
    border-color: #2563eb;
    background: #2563eb;
    color: #fff;
}

.custom-picker-option.current:not(.active) {
    box-shadow: inset 0 0 0 1px #93c5fd;
}

/* ==========================================================================
   FILTER DROPDOWN
   ========================================================================== */

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
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    background: #fff;
    color: #334155;
    text-align: left;
    cursor: pointer;
    transition:
        border-color .15s ease,
        background-color .15s ease,
        box-shadow .15s ease;
}

.filter-dropdown-trigger:hover {
    border-color: #94a3b8;
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
    align-items: center;
    justify-content: center;
    flex: 0 0 36px;
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

.filter-dropdown-title,
.filter-dropdown-subtitle {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.filter-dropdown-title {
    color: #334155;
    font-size: 13px;
    font-weight: 700;
}

.filter-dropdown-subtitle {
    margin-top: 3px;
    color: #94a3b8;
    font-size: 10px;
    font-weight: 500;
}

.filter-dropdown-count {
    display: inline-flex;
    min-width: 72px;
    min-height: 25px;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 999px;
    padding: 0 9px;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.filter-dropdown-count.category {
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    color: #2563eb;
}

.filter-dropdown-count.status {
    border: 1px solid #fde68a;
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
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    background: #fff;
    box-shadow:
        0 20px 50px rgba(15, 23, 42, .14),
        0 6px 18px rgba(15, 23, 42, .07);
}

.filter-dropdown.open .filter-dropdown-menu {
    display: block;
}

.filter-dropdown-menu-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 12px 14px;
    border-bottom: 1px solid #cbd5e1;
    background: #f8fafc;
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
    gap: 8px;
    max-height: 280px;
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
    padding: 8px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #fff;
    cursor: pointer;
    transition:
        border-color .15s ease,
        background-color .15s ease;
}

.filter-dropdown-option:hover {
    border-color: #93c5fd;
    background: #f8fbff;
}

.filter-dropdown-option.status-option:hover {
    border-color: #fde68a;
    background: #fffcf3;
}

.filter-dropdown-option:has(input:checked) {
    border-color: #93c5fd;
    background: #eff6ff;
}

.filter-dropdown-option.status-option:has(input:checked) {
    border-color: #fde68a;
    background: #fffbeb;
}

.filter-dropdown-option input {
    width: 16px;
    height: 16px;
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
    padding: 10px 14px;
    border-top: 1px solid #cbd5e1;
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

/* ==========================================================================
   TABLE
   ========================================================================== */

.archive-table-wrapper {
    overflow: hidden;
    border: 1px solid #94a3b8;
    border-radius: 16px;
    background: #fff;
    box-shadow:
        0 1px 3px rgba(15, 23, 42, .06),
        0 8px 24px rgba(15, 23, 42, .04);
}

.archive-table-scroll {
    overflow-x: auto;
}

.archive-table {
    width: 100%;
    min-width: 850px;
    border-collapse: collapse;
    border-spacing: 0;
    background: #fff;
}

.archive-table thead tr {
    background: #f1f5f9;
}

.archive-table thead th {
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

.archive-table thead th:last-child {
    border-right: 0;
    text-align: center;
}

.archive-table tbody tr {
    background: #fff;
}

.archive-table tbody tr:nth-child(even) {
    background: #f8fafc;
}

.archive-table tbody tr:hover {
    background: #eff6ff;
}

.archive-table tbody td {
    padding: 14px 16px;
    border-right: 1px solid #cbd5e1;
    border-bottom: 1px solid #cbd5e1;
    color: #475569;
    font-size: 12px;
    line-height: 1.5;
    vertical-align: middle;
}

.archive-table tbody td:last-child {
    border-right: 0;
    text-align: center;
}

.archive-table tbody tr:last-child td {
    border-bottom: 0;
}

.archive-table .cell-date {
    color: #475569;
    font-weight: 600;
}

.archive-table .cell-sender {
    color: #334155;
}

.archive-table .cell-subject {
    color: #1e293b;
    font-weight: 700;
}

.archive-table .cell-category {
    color: #64748b;
}

.archive-table .cell-status {
    white-space: nowrap;
}

.archive-table .sender-badge {
    display: inline-block;
    max-width: 220px;
    overflow: hidden;
    padding: 5px 9px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #f1f5f9;
    color: #334155;
    font-weight: 600;
    text-overflow: ellipsis;
    vertical-align: middle;
    white-space: nowrap;
}

.archive-table .status-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    border-width: 1px;
    padding: 5px 10px;
    font-size: 10px;
    font-weight: 800;
}

.archive-table .action-cell {
    width: 130px;
}

.archive-table .action-buttons {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
}

.archive-table .action-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    transition:
        background .15s ease,
        color .15s ease;
}

.archive-table .action-button.detail,
.archive-table .action-button.edit,
.archive-table .action-button.delete {
    color: #64748b;
}

.archive-table .action-button.detail:hover {
    background: #dbeafe;
    color: #2563eb;
}

.archive-table .action-button.edit:hover {
    background: #fef3c7;
    color: #d97706;
}

.archive-table .action-button.delete:hover {
    background: #ffe4e6;
    color: #e11d48;
}

.archive-table-empty {
    padding: 48px 16px;
    text-align: center;
}

/* ==========================================================================
   MOBILE
   ========================================================================== */

@media (max-width: 767px) {
    .filter-row {
        grid-template-columns: 1fr;
    }

    .custom-date-picker {
        top: 50%;
        left: 50%;
        width: calc(100vw - 16px);
        max-height: calc(100vh - 16px);
        overflow-y: auto;
        transform: translate(-50%, -50%);
    }

    .custom-date-calendars {
        grid-template-columns: 1fr;
    }

    .custom-calendar + .custom-calendar {
        border-top: 1px solid #cbd5e1;
        border-left: 0;
    }

    .custom-calendar-day {
        height: 39px;
    }

    .custom-picker-panel {
        top: 50% !important;
        left: 50% !important;
        width: calc(100vw - 24px);
        max-width: 380px;
        transform: translate(-50%, -50%);
    }

    .filter-dropdown-menu {
        position: fixed;
        top: 50%;
        left: 50%;
        right: auto;
        width: calc(100vw - 24px);
        max-width: 430px;
        max-height: 80vh;
        transform: translate(-50%, -50%);
    }

    .filter-dropdown-options {
        max-height: calc(80vh - 155px);
        grid-template-columns: 1fr 1fr;
    }

    body.date-picker-lock {
        overflow: hidden;
    }
}

@media (max-width: 480px) {
    .filter-dropdown-options {
        grid-template-columns: 1fr;
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 00.707.293l5.414 5.414a1 1 0 00.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Excel
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
                PDF
            </a>

            @if($canManage)
                <a
                    href="{{ route('surat-keluar.create') }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-2.5 py-2 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 sm:px-4"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Surat Keluar
                </a>
            @endif
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="rounded-2xl border border-slate-300 bg-white p-3 shadow-sm sm:p-4">
        <form
            id="filterForm"
            method="GET"
            action="{{ route('surat-keluar.index') }}"
            class="space-y-3"
        >
            <div class="grid grid-cols-1 gap-2.5 lg:grid-cols-12">

                {{-- SEARCH --}}
                <div class="lg:col-span-5">
                    <label for="search" class="sr-only">Pencarian</label>

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
                            class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                        >
                    </div>
                </div>

                {{-- DATE RANGE --}}
                <div class="lg:col-span-4">
                    <div class="date-range-wrapper">
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
                                class="date-range-input h-11 w-full rounded-xl border border-slate-300 bg-slate-50 pl-9 pr-10 text-xs font-medium text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
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

                        <input type="hidden" name="dari_tanggal" id="dari_tanggal" value="{{ $dariTanggal }}">
                        <input type="hidden" name="sampai_tanggal" id="sampai_tanggal" value="{{ $sampaiTanggal }}">
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707l-1.293.707V17l-4-4v-4.293a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filter
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
                                <span class="filter-dropdown-title">Kategori Surat</span>
                                <span class="filter-dropdown-subtitle" id="kategoriSummary">Semua kategori</span>
                            </span>

                            <span class="filter-dropdown-count category" id="kategoriCount">
                                {{ count($selectedKategori) }} dipilih
                            </span>
                        </span>

                        <svg
                            class="filter-dropdown-arrow h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6l6-6"/>
                        </svg>
                    </button>

                    <div id="kategoriDropdownMenu" class="filter-dropdown-menu">
                        <div class="filter-dropdown-menu-header">
                            <div class="filter-dropdown-menu-title-wrap">
                                <span class="filter-dropdown-menu-title">Pilih Kategori</span>
                                <span class="filter-dropdown-menu-description">Checkbox dapat dipilih lebih dari satu</span>
                            </div>

                            <div class="filter-dropdown-actions">
                                <button type="button" id="selectAllKategori" class="filter-dropdown-action category">
                                    Pilih Semua
                                </button>

                                <span class="filter-dropdown-divider">|</span>

                                <button type="button" id="clearAllKategori" class="filter-dropdown-action clear">
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
                            <span id="kategoriFooterCount" class="filter-dropdown-footer-count">
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
                                <span class="filter-dropdown-title">Status Surat</span>
                                <span class="filter-dropdown-subtitle" id="statusSummary">Semua status</span>
                            </span>

                            <span class="filter-dropdown-count status" id="statusCount">
                                {{ count($selectedStatus) }} dipilih
                            </span>
                        </span>

                        <svg
                            class="filter-dropdown-arrow h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9l6 6l6-6"/>
                        </svg>
                    </button>

                    <div id="statusDropdownMenu" class="filter-dropdown-menu">
                        <div class="filter-dropdown-menu-header">
                            <div class="filter-dropdown-menu-title-wrap">
                                <span class="filter-dropdown-menu-title">Pilih Status</span>
                                <span class="filter-dropdown-menu-description">Checkbox dapat dipilih lebih dari satu</span>
                            </div>

                            <div class="filter-dropdown-actions">
                                <button type="button" id="selectAllStatus" class="filter-dropdown-action status">
                                    Pilih Semua
                                </button>

                                <span class="filter-dropdown-divider">|</span>

                                <button type="button" id="clearAllStatus" class="filter-dropdown-action clear">
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

                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="filter-dropdown-menu-footer">
                            <span id="statusFooterCount" class="filter-dropdown-footer-count">
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
    <div class="archive-table-wrapper">
        <div class="archive-table-scroll">
            <table class="archive-table">
                <thead>
                    <tr>
                        <th>Tanggal Keluar</th>
                        <th>Instansi Tujuan</th>
                        <th>Perihal</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th class="action-cell">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($suratKeluars ?? [] as $s)
                        @php
                            $status = strtolower(trim((string) ($s->status ?? 'draf')));
                            $badgeClass = $statusBadgeClasses[$status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                            $tujuan = $s->pengirim ?? $s->asal_surat ?? '-';

                            $tanggalKeluar = '-';

                            if ($s->tanggal_surat) {
                                try {
                                    $tanggalKeluar = \Illuminate\Support\Carbon::parse($s->tanggal_surat)->format('d/m/Y');
                                } catch (\Throwable $e) {
                                    $tanggalKeluar = '-';
                                }
                            }
                        @endphp

                        <tr>
                            <td class="cell-date">
                                {{ $tanggalKeluar }}
                            </td>

                            <td class="cell-sender">
                                <span
                                    class="sender-badge"
                                    title="{{ $tujuan }}"
                                >
                                    {{ $tujuan }}
                                </span>
                            </td>

                            <td
                                class="cell-subject max-w-xs truncate"
                                title="{{ $s->perihal ?? '-' }}"
                            >
                                {{ $s->perihal ?? '-' }}
                            </td>

                            <td class="cell-category">
                                {{ $s->kategori?->nama_kategori ?? '-' }}
                            </td>

                            <td class="cell-status">
                                <span class="status-badge {{ $badgeClass }}">
                                    {{ $statusOptions[$status] ?? ucfirst($status) }}
                                </span>
                            </td>

                            <td class="action-cell">
                                <div class="action-buttons">

                                    {{-- DETAIL --}}
                                    <a
                                        href="{{ route('surat-keluar.show', $s) }}"
                                        class="action-button detail"
                                        title="Lihat Detail"
                                        aria-label="Lihat detail surat"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0a3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7z"/>
                                        </svg>
                                    </a>

                                    @if($canManage)

                                        {{-- EDIT --}}
                                        <a
                                            href="{{ route('surat-keluar.edit', $s) }}"
                                            class="action-button edit"
                                            title="Ubah Data"
                                            aria-label="Ubah data surat"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L11.828 15H9v-2.828l9.5-9.5z"/>
                                            </svg>
                                        </a>

                                        {{-- DELETE --}}
                                        <form
                                            action="{{ route('surat-keluar.destroy', $s) }}"
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
                            <td colspan="6" class="archive-table-empty">
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

        {{-- PAGINATION --}}
        @if(isset($suratKeluars) && method_exists($suratKeluars, 'hasPages') && $suratKeluars->hasPages())
            <div class="border-t border-slate-300 px-4 py-3 sm:px-6 sm:py-4">
                {{ $suratKeluars->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

{{-- CUSTOM DATE PICKER --}}
<div id="customDatePicker" class="custom-date-picker hidden">
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

    <div id="customDateCalendars" class="custom-date-calendars"></div>

    <div class="custom-date-picker-footer">
        <div id="datePickerSelected" class="custom-date-picker-selected">
            Pilih tanggal awal
        </div>

        <div class="custom-date-picker-actions">
            <button type="button" id="datePickerClear" class="custom-date-picker-button">
                Bersihkan
            </button>

            <button type="button" id="datePickerApply" class="custom-date-picker-button apply">
                Terapkan
            </button>
        </div>
    </div>
</div>

<div id="customPickerPanel" class="custom-picker-panel hidden"></div>

@push('scripts')
<script>
(function () {
    'use strict';

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
    const MAX_YEAR = new Date().getFullYear() + 20;

    const dateInput = document.getElementById('date-range');
    const dariInput = document.getElementById('dari_tanggal');
    const sampaiInput = document.getElementById('sampai_tanggal');
    const clearDateButton = document.getElementById('clearDateRange');
    const datePicker = document.getElementById('customDatePicker');
    const calendars = document.getElementById('customDateCalendars');
    const pickerPanel = document.getElementById('customPickerPanel');
    const closeDatePickerButton = document.getElementById('datePickerClose');
    const clearPickerButton = document.getElementById('datePickerClear');
    const applyPickerButton = document.getElementById('datePickerApply');
    const selectedLabel = document.getElementById('datePickerSelected');

    let selectedStart = parseDate(dariInput?.value || '');
    let selectedEnd = parseDate(sampaiInput?.value || '');
    let tempStart = selectedStart ? cloneDate(selectedStart) : null;
    let tempEnd = selectedEnd ? cloneDate(selectedEnd) : null;
    let viewMonth = selectedStart
        ? new Date(selectedStart.getFullYear(), selectedStart.getMonth(), 1)
        : new Date();

    let activePanelSide = 'left';
    let activePanelYear = new Date().getFullYear();

    viewMonth.setDate(1);

    function cloneDate(date) {
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

        const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})$/);

        if (!match) {
            return null;
        }

        const year = Number(match[1]);
        const month = Number(match[2]) - 1;
        const day = Number(match[3]);

        const date = new Date(year, month, day);

        if (
            date.getFullYear() !== year ||
            date.getMonth() !== month ||
            date.getDate() !== day
        ) {
            return null;
        }

        return date;
    }

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function toISO(date) {
        if (!date) {
            return '';
        }

        return [
            date.getFullYear(),
            pad(date.getMonth() + 1),
            pad(date.getDate())
        ].join('-');
    }

    function formatDate(date) {
        if (!date) {
            return '';
        }

        return [
            pad(date.getDate()),
            pad(date.getMonth() + 1),
            date.getFullYear()
        ].join('/');
    }

    function sameDate(a, b) {
        return !!(
            a &&
            b &&
            a.getFullYear() === b.getFullYear() &&
            a.getMonth() === b.getMonth() &&
            a.getDate() === b.getDate()
        );
    }

    function addMonths(date, amount) {
        return new Date(
            date.getFullYear(),
            date.getMonth() + amount,
            1
        );
    }

    function isBetween(date, start, end) {
        if (!date || !start || !end) {
            return false;
        }

        return (
            toISO(date) > toISO(start) &&
            toISO(date) < toISO(end)
        );
    }

    function renderCalendars() {
        if (!calendars) {
            return;
        }

        calendars.innerHTML = '';

        const leftDate =
            new Date(
                viewMonth.getFullYear(),
                viewMonth.getMonth(),
                1
            );

        const rightDate =
            addMonths(leftDate, 1);

        calendars.appendChild(
            createCalendar(leftDate, 'left')
        );

        calendars.appendChild(
            createCalendar(rightDate, 'right')
        );

        updateSelectedLabel();
        requestAnimationFrame(positionDatePicker);
    }

    function createCalendar(date, side) {
        const calendar = document.createElement('div');
        calendar.className = 'custom-calendar';

        const header = document.createElement('div');
        header.className = 'custom-calendar-head';

        const previous = document.createElement('button');
        previous.type = 'button';
        previous.className = 'custom-calendar-nav';
        previous.innerHTML = '&#8249;';
        previous.setAttribute('aria-label', 'Bulan sebelumnya');

        const heading = document.createElement('div');
        heading.className = 'custom-calendar-heading';

        const monthButton = document.createElement('button');
        monthButton.type = 'button';
        monthButton.className = 'custom-calendar-month-button';
        monthButton.textContent = MONTHS[date.getMonth()];

        const yearButton = document.createElement('button');
        yearButton.type = 'button';
        yearButton.className = 'custom-calendar-year-button';
        yearButton.textContent = String(date.getFullYear());

        heading.append(monthButton, yearButton);

        const next = document.createElement('button');
        next.type = 'button';
        next.className = 'custom-calendar-nav';
        next.innerHTML = '&#8250;';
        next.setAttribute('aria-label', 'Bulan berikutnya');

        header.append(previous, heading, next);

        monthButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            showMonthPanel(
                date,
                monthButton,
                side
            );
        });

        yearButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            showYearPanel(
                date,
                yearButton,
                side
            );
        });

        previous.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            viewMonth = addMonths(viewMonth, -1);
            renderCalendars();
        });

        next.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            viewMonth = addMonths(viewMonth, 1);
            renderCalendars();
        });

        calendar.appendChild(header);

        const weekdays = document.createElement('div');
        weekdays.className = 'custom-calendar-weekdays';

        WEEKDAYS.forEach(function (day) {
            const element = document.createElement('div');

            element.className = 'custom-calendar-weekday';
            element.textContent = day;

            weekdays.appendChild(element);
        });

        calendar.appendChild(weekdays);

        const days = document.createElement('div');
        days.className = 'custom-calendar-days';

        const year = date.getFullYear();
        const month = date.getMonth();
        const firstDay = new Date(year, month, 1).getDay();
        const mondayOffset = firstDay === 0 ? 6 : firstDay - 1;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPreviousMonth = new Date(year, month, 0).getDate();

        for (let index = 0; index < 42; index++) {
            let dayNumber;
            let cellDate;
            let otherMonth = false;

            if (index < mondayOffset) {
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

                otherMonth = true;
            } else if (
                index >=
                mondayOffset + daysInMonth
            ) {
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

                otherMonth = true;
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
                document.createElement('button');

            button.type = 'button';
            button.className = 'custom-calendar-day';
            button.textContent = String(dayNumber);

            if (otherMonth) {
                button.classList.add('other-month');
            }

            if (sameDate(cellDate, new Date())) {
                button.classList.add('today');
            }

            if (
                tempStart &&
                tempEnd &&
                isBetween(cellDate, tempStart, tempEnd)
            ) {
                button.classList.add('in-range');
            }

            if (
                tempStart &&
                sameDate(cellDate, tempStart)
            ) {
                button.classList.add('range-start');
            }

            if (
                tempEnd &&
                sameDate(cellDate, tempEnd)
            ) {
                button.classList.add('range-end');
            }

            button.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                selectDate(cellDate);
            });

            days.appendChild(button);
        }

        calendar.appendChild(days);

        return calendar;
    }

    function selectDate(date) {
        const chosen = cloneDate(date);

        if (!tempStart || tempEnd) {
            tempStart = chosen;
            tempEnd = null;
        } else if (toISO(chosen) < toISO(tempStart)) {
            tempEnd = cloneDate(tempStart);
            tempStart = chosen;
        } else {
            tempEnd = chosen;
        }

        renderCalendars();
    }

    function updateSelectedLabel() {
        if (!selectedLabel) {
            return;
        }

        if (tempStart && tempEnd) {
            selectedLabel.textContent =
                formatDate(tempStart) +
                ' - ' +
                formatDate(tempEnd);

            return;
        }

        if (tempStart) {
            selectedLabel.textContent =
                formatDate(tempStart) +
                ' - pilih tanggal akhir';

            return;
        }

        selectedLabel.textContent =
            'Pilih tanggal awal';
    }

    function positionDatePicker() {
        if (
            !datePicker ||
            !dateInput ||
            window.innerWidth <= 767
        ) {
            return;
        }

        const rect =
            dateInput.getBoundingClientRect();

        const width =
            datePicker.offsetWidth || 720;

        const height =
            datePicker.offsetHeight || 500;

        let left =
            rect.left +
            rect.width / 2 -
            width / 2;

        let top =
            rect.bottom + 8;

        if (
            left + width >
            window.innerWidth - 10
        ) {
            left =
                window.innerWidth -
                width -
                10;
        }

        if (left < 10) {
            left = 10;
        }

        if (
            top + height >
            window.innerHeight - 10
        ) {
            top =
                rect.top -
                height -
                8;
        }

        if (top < 10) {
            top = 10;
        }

        datePicker.style.left =
            left + 'px';

        datePicker.style.top =
            top + 'px';
    }

    function showDatePicker() {
        if (!datePicker) {
            return;
        }

        closePickerPanel();

        tempStart =
            selectedStart
                ? cloneDate(selectedStart)
                : null;

        tempEnd =
            selectedEnd
                ? cloneDate(selectedEnd)
                : null;

        if (tempStart) {
            viewMonth =
                new Date(
                    tempStart.getFullYear(),
                    tempStart.getMonth(),
                    1
                );
        }

        renderCalendars();

        datePicker.classList.remove('hidden');
        document.body.classList.add('date-picker-lock');

        requestAnimationFrame(positionDatePicker);
    }

    function hideDatePicker() {
        if (!datePicker) {
            return;
        }

        datePicker.classList.add('hidden');
        closePickerPanel();
        document.body.classList.remove('date-picker-lock');
    }

    function showMonthPanel(calendarDate, anchor, side) {
        if (!pickerPanel) {
            return;
        }

        activePanelSide = side;
        activePanelYear = calendarDate.getFullYear();

        pickerPanel.innerHTML = '';
        pickerPanel.classList.remove('hidden');

        const header =
            document.createElement('div');

        header.className =
            'custom-picker-panel-header';

        const previous =
            document.createElement('button');

        previous.type = 'button';
        previous.className = 'custom-picker-panel-nav';
        previous.innerHTML = '&#8249;';

        const title =
            document.createElement('div');

        title.className =
            'custom-picker-panel-title';

        const next =
            document.createElement('button');

        next.type = 'button';
        next.className = 'custom-picker-panel-nav';
        next.innerHTML = '&#8250;';

        const close =
            document.createElement('button');

        close.type = 'button';
        close.className = 'custom-picker-panel-close';
        close.innerHTML = '&times;';

        header.append(
            previous,
            title,
            next,
            close
        );

        pickerPanel.appendChild(header);

        const grid =
            document.createElement('div');

        grid.className =
            'custom-picker-grid';

        pickerPanel.appendChild(grid);

        function renderMonths() {
            title.textContent =
                String(activePanelYear);

            grid.innerHTML = '';

            MONTHS.forEach(
                function (monthName, monthIndex) {
                    const button =
                        document.createElement('button');

                    button.type = 'button';
                    button.className =
                        'custom-picker-option';

                    button.textContent =
                        monthName;

                    if (
                        activePanelYear ===
                            calendarDate.getFullYear() &&
                        monthIndex ===
                            calendarDate.getMonth()
                    ) {
                        button.classList.add(
                            'active'
                        );
                    }

                    button.addEventListener(
                        'click',
                        function (event) {
                            event.preventDefault();
                            event.stopPropagation();

                            const target =
                                new Date(
                                    activePanelYear,
                                    monthIndex,
                                    1
                                );

                            viewMonth =
                                activePanelSide === 'left'
                                    ? target
                                    : addMonths(target, -1);

                            closePickerPanel();
                            renderCalendars();
                        }
                    );

                    grid.appendChild(button);
                }
            );

            positionPickerPanel(
                pickerPanel,
                anchor
            );
        }

        previous.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            activePanelYear =
                Math.max(
                    MIN_YEAR,
                    activePanelYear - 1
                );

            renderMonths();
        });

        next.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            activePanelYear =
                Math.min(
                    MAX_YEAR,
                    activePanelYear + 1
                );

            renderMonths();
        });

        close.addEventListener(
            'click',
            closePickerPanel
        );

        renderMonths();
    }

    function showYearPanel(calendarDate, anchor, side) {
        if (!pickerPanel) {
            return;
        }

        activePanelSide = side;

        let startYear =
            Math.floor(
                calendarDate.getFullYear() / 12
            ) * 12;

        startYear =
            Math.max(
                MIN_YEAR,
                startYear
            );

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
    ) {
        pickerPanel.innerHTML = '';
        pickerPanel.classList.remove('hidden');

        const header =
            document.createElement('div');

        header.className =
            'custom-picker-panel-header';

        const previous =
            document.createElement('button');

        previous.type = 'button';
        previous.className =
            'custom-picker-panel-nav';

        previous.innerHTML =
            '&#8249;';

        const title =
            document.createElement('div');

        title.className =
            'custom-picker-panel-title';

        const next =
            document.createElement('button');

        next.type = 'button';
        next.className =
            'custom-picker-panel-nav';

        next.innerHTML =
            '&#8250;';

        const close =
            document.createElement('button');

        close.type = 'button';
        close.className =
            'custom-picker-panel-close';

        close.innerHTML =
            '&times;';

        header.append(
            previous,
            title,
            next,
            close
        );

        pickerPanel.appendChild(header);

        const grid =
            document.createElement('div');

        grid.className =
            'custom-picker-grid';

        pickerPanel.appendChild(grid);

        function renderYears() {
            title.textContent =
                startYear +
                ' - ' +
                (startYear + 11);

            grid.innerHTML = '';

            for (
                let index = 0;
                index < 12;
                index++
            ) {
                const year =
                    startYear + index;

                const button =
                    document.createElement('button');

                button.type =
                    'button';

                button.className =
                    'custom-picker-option';

                button.textContent =
                    String(year);

                if (
                    year ===
                    calendarDate.getFullYear()
                ) {
                    button.classList.add(
                        'active'
                    );
                }

                if (
                    year ===
                    new Date().getFullYear()
                ) {
                    button.classList.add(
                        'current'
                    );
                }

                if (
                    year >= MIN_YEAR &&
                    year <= MAX_YEAR
                ) {
                    button.addEventListener(
                        'click',
                        function (event) {
                            event.preventDefault();
                            event.stopPropagation();

                            const month =
                                calendarDate.getMonth();

                            viewMonth =
                                activePanelSide === 'left'
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

                            closePickerPanel();
                            renderCalendars();
                        }
                    );
                } else {
                    button.disabled = true;
                }

                grid.appendChild(button);
            }

            positionPickerPanel(
                pickerPanel,
                anchor
            );
        }

        previous.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                startYear =
                    Math.max(
                        MIN_YEAR,
                        startYear - 12
                    );

                renderYears();
            }
        );

        next.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                const maxStart =
                    Math.floor(
                        MAX_YEAR / 12
                    ) * 12;

                startYear =
                    Math.min(
                        maxStart,
                        startYear + 12
                    );

                renderYears();
            }
        );

        close.addEventListener(
            'click',
            closePickerPanel
        );

        renderYears();
    }

    function positionPickerPanel(element, anchor) {
        if (
            !element ||
            !anchor
        ) {
            return;
        }

        if (
            window.innerWidth <= 767
        ) {
            element.style.left =
                '50%';

            element.style.top =
                '50%';

            return;
        }

        const rect =
            anchor.getBoundingClientRect();

        const width =
            element.offsetWidth || 320;

        const height =
            element.offsetHeight || 280;

        let left =
            rect.left;

        let top =
            rect.bottom + 8;

        if (
            left + width >
            window.innerWidth - 10
        ) {
            left =
                window.innerWidth -
                width -
                10;
        }

        if (left < 10) {
            left = 10;
        }

        if (
            top + height >
            window.innerHeight - 10
        ) {
            top =
                rect.top -
                height -
                8;
        }

        if (top < 10) {
            top = 10;
        }

        element.style.left =
            left + 'px';

        element.style.top =
            top + 'px';
    }

    function closePickerPanel() {
        if (!pickerPanel) {
            return;
        }

        pickerPanel.classList.add(
            'hidden'
        );

        pickerPanel.innerHTML = '';
    }

    function applyDateRange() {
        if (
            !tempStart ||
            !tempEnd
        ) {
            if (
                typeof window.Swal !==
                'undefined'
            ) {
                window.Swal.fire({
                    icon: 'info',
                    title: 'Pilih rentang tanggal',
                    text: 'Silakan pilih tanggal awal dan tanggal akhir terlebih dahulu.',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#2563eb'
                });
            }

            return;
        }

        selectedStart =
            cloneDate(tempStart);

        selectedEnd =
            cloneDate(tempEnd);

        if (dariInput) {
            dariInput.value =
                toISO(selectedStart);
        }

        if (sampaiInput) {
            sampaiInput.value =
                toISO(selectedEnd);
        }

        if (dateInput) {
            dateInput.value =
                formatDate(selectedStart) +
                ' - ' +
                formatDate(selectedEnd);
        }

        if (clearDateButton) {
            clearDateButton.classList.remove(
                'hidden'
            );

            clearDateButton.classList.add(
                'flex'
            );
        }

        hideDatePicker();
    }

    function clearDateRange() {
        selectedStart = null;
        selectedEnd = null;
        tempStart = null;
        tempEnd = null;

        if (dariInput) {
            dariInput.value = '';
        }

        if (sampaiInput) {
            sampaiInput.value = '';
        }

        if (dateInput) {
            dateInput.value = '';
        }

        if (clearDateButton) {
            clearDateButton.classList.remove(
                'flex'
            );

            clearDateButton.classList.add(
                'hidden'
            );
        }

        viewMonth =
            new Date();

        viewMonth.setDate(1);

        hideDatePicker();
    }

    if (
        dateInput &&
        datePicker
    ) {
        dateInput.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                if (
                    datePicker.classList.contains(
                        'hidden'
                    )
                ) {
                    showDatePicker();
                } else {
                    hideDatePicker();
                }
            }
        );

        dateInput.addEventListener(
            'focus',
            function () {
                if (
                    datePicker.classList.contains(
                        'hidden'
                    )
                ) {
                    showDatePicker();
                }
            }
        );

        closeDatePickerButton?.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                hideDatePicker();
            }
        );

        clearPickerButton?.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                clearDateRange();
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

        clearDateButton?.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                clearDateRange();
            }
        );

        document.addEventListener(
            'mousedown',
            function (event) {
                if (
                    datePicker.classList.contains(
                        'hidden'
                    )
                ) {
                    return;
                }

                if (
                    datePicker.contains(
                        event.target
                    )
                ) {
                    return;
                }

                if (
                    pickerPanel &&
                    pickerPanel.contains(
                        event.target
                    )
                ) {
                    return;
                }

                if (
                    dateInput.contains(
                        event.target
                    )
                ) {
                    return;
                }

                if (
                    clearDateButton &&
                    clearDateButton.contains(
                        event.target
                    )
                ) {
                    return;
                }

                hideDatePicker();
            }
        );
    }

    window.addEventListener(
        'resize',
        function () {
            if (
                datePicker &&
                !datePicker.classList.contains(
                    'hidden'
                )
            ) {
                positionDatePicker();
            }

            if (
                pickerPanel &&
                !pickerPanel.classList.contains(
                    'hidden'
                )
            ) {
                closePickerPanel();
            }
        }
    );

    window.addEventListener(
        'scroll',
        function () {
            if (
                datePicker &&
                !datePicker.classList.contains(
                    'hidden'
                ) &&
                window.innerWidth > 767
            ) {
                positionDatePicker();
            }
        },
        true
    );

    /* ================================================================
       FILTER DROPDOWN
    ================================================================ */

    const kategoriDropdown =
        document.getElementById(
            'kategoriDropdown'
        );

    const statusDropdown =
        document.getElementById(
            'statusDropdown'
        );

    const kategoriButton =
        document.getElementById(
            'kategoriDropdownButton'
        );

    const statusButton =
        document.getElementById(
            'statusDropdownButton'
        );

    const kategoriCount =
        document.getElementById(
            'kategoriCount'
        );

    const statusCount =
        document.getElementById(
            'statusCount'
        );

    const kategoriSummary =
        document.getElementById(
            'kategoriSummary'
        );

    const statusSummary =
        document.getElementById(
            'statusSummary'
        );

    const kategoriFooterCount =
        document.getElementById(
            'kategoriFooterCount'
        );

    const statusFooterCount =
        document.getElementById(
            'statusFooterCount'
        );

    const kategoriCheckboxes =
        document.querySelectorAll(
            '.kategori-checkbox'
        );

    const statusCheckboxes =
        document.querySelectorAll(
            '.status-checkbox'
        );

    function closeDropdown(dropdown) {
        if (!dropdown) {
            return;
        }

        dropdown.classList.remove('open');

        const button =
            dropdown.querySelector(
                '.filter-dropdown-trigger'
            );

        if (button) {
            button.setAttribute(
                'aria-expanded',
                'false'
            );
        }
    }

    function closeAllDropdowns() {
        closeDropdown(kategoriDropdown);
        closeDropdown(statusDropdown);
    }

    function openDropdown(dropdown) {
        if (!dropdown) {
            return;
        }

        if (
            dropdown ===
            kategoriDropdown
        ) {
            closeDropdown(
                statusDropdown
            );
        }

        if (
            dropdown ===
            statusDropdown
        ) {
            closeDropdown(
                kategoriDropdown
            );
        }

        dropdown.classList.add('open');

        const button =
            dropdown.querySelector(
                '.filter-dropdown-trigger'
            );

        if (button) {
            button.setAttribute(
                'aria-expanded',
                'true'
            );
        }
    }

    function getCheckedLabels(selector) {
        return Array.from(
            document.querySelectorAll(
                selector + ':checked'
            )
        )
            .map(
                function (checkbox) {
                    const label =
                        checkbox.closest(
                            'label'
                        );

                    const span =
                        label?.querySelector(
                            'span'
                        );

                    return (
                        span?.textContent.trim() ||
                        ''
                    );
                }
            )
            .filter(Boolean);
    }

    function updateKategoriFilter() {
        const checked =
            document.querySelectorAll(
                '.kategori-checkbox:checked'
            );

        const count =
            checked.length;

        if (kategoriCount) {
            kategoriCount.textContent =
                count + ' dipilih';
        }

        if (kategoriFooterCount) {
            kategoriFooterCount.textContent =
                count +
                ' kategori dipilih';
        }

        if (kategoriSummary) {
            const labels =
                getCheckedLabels(
                    '.kategori-checkbox'
                );

            if (!labels.length) {
                kategoriSummary.textContent =
                    'Semua kategori';
            } else if (
                labels.length <= 2
            ) {
                kategoriSummary.textContent =
                    labels.join(', ');
            } else {
                kategoriSummary.textContent =
                    labels.length +
                    ' kategori dipilih';
            }
        }
    }

    function updateStatusFilter() {
        const checked =
            document.querySelectorAll(
                '.status-checkbox:checked'
            );

        const count =
            checked.length;

        if (statusCount) {
            statusCount.textContent =
                count + ' dipilih';
        }

        if (statusFooterCount) {
            statusFooterCount.textContent =
                count +
                ' status dipilih';
        }

        if (statusSummary) {
            const labels =
                getCheckedLabels(
                    '.status-checkbox'
                );

            if (!labels.length) {
                statusSummary.textContent =
                    'Semua status';
            } else if (
                labels.length <= 2
            ) {
                statusSummary.textContent =
                    labels.join(', ');
            } else {
                statusSummary.textContent =
                    labels.length +
                    ' status dipilih';
            }
        }
    }

    kategoriButton?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (
                kategoriDropdown?.classList.contains(
                    'open'
                )
            ) {
                closeDropdown(
                    kategoriDropdown
                );
            } else {
                openDropdown(
                    kategoriDropdown
                );
            }
        }
    );

    statusButton?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (
                statusDropdown?.classList.contains(
                    'open'
                )
            ) {
                closeDropdown(
                    statusDropdown
                );
            } else {
                openDropdown(
                    statusDropdown
                );
            }
        }
    );

    kategoriCheckboxes.forEach(
        function (checkbox) {
            checkbox.addEventListener(
                'change',
                updateKategoriFilter
            );
        }
    );

    statusCheckboxes.forEach(
        function (checkbox) {
            checkbox.addEventListener(
                'change',
                updateStatusFilter
            );
        }
    );

    document
        .getElementById(
            'selectAllKategori'
        )
        ?.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                kategoriCheckboxes.forEach(
                    function (checkbox) {
                        checkbox.checked =
                            true;
                    }
                );

                updateKategoriFilter();
            }
        );

    document
        .getElementById(
            'clearAllKategori'
        )
        ?.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                kategoriCheckboxes.forEach(
                    function (checkbox) {
                        checkbox.checked =
                            false;
                    }
                );

                updateKategoriFilter();
            }
        );

    document
        .getElementById(
            'selectAllStatus'
        )
        ?.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                statusCheckboxes.forEach(
                    function (checkbox) {
                        checkbox.checked =
                            true;
                    }
                );

                updateStatusFilter();
            }
        );

    document
        .getElementById(
            'clearAllStatus'
        )
        ?.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                statusCheckboxes.forEach(
                    function (checkbox) {
                        checkbox.checked =
                            false;
                    }
                );

                updateStatusFilter();
            }
        );

    document.addEventListener(
        'click',
        function (event) {
            const target =
                event.target;

            if (
                !(target instanceof Element)
            ) {
                return;
            }

            if (
                !target.closest(
                    '.filter-dropdown'
                )
            ) {
                closeAllDropdowns();
            }
        }
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key ===
                'Escape'
            ) {
                closeAllDropdowns();

                if (
                    datePicker &&
                    !datePicker.classList.contains(
                        'hidden'
                    )
                ) {
                    hideDatePicker();
                }
            }
        }
    );

    updateKategoriFilter();
    updateStatusFilter();

    /* ================================================================
       DELETE CONFIRMATION
    ================================================================ */

    document
        .querySelectorAll('.delete-btn')
        .forEach(
            function (button) {
                button.addEventListener(
                    'click',
                    function () {
                        const form =
                            this.closest(
                                '.delete-form'
                            );

                        if (!form) {
                            return;
                        }

                        if (
                            typeof window.Swal !==
                            'undefined'
                        ) {
                            window.Swal.fire({
                                title:
                                    'Hapus Surat Keluar?',
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
                                'Yakin ingin menghapus surat keluar ini?'
                            )
                        ) {
                            form.submit();
                        }
                    }
                );
            }
        );

    /* ================================================================
       FILTER DATE VALIDATION
    ================================================================ */

    const filterForm =
        document.getElementById(
            'filterForm'
        );

    filterForm?.addEventListener(
        'submit',
        function (event) {
            const start =
                dariInput?.value || '';

            const end =
                sampaiInput?.value || '';

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
                        icon:
                            'warning',
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

    /* ================================================================
       INITIALIZE
    ================================================================ */

    if (
        selectedStart &&
        selectedEnd &&
        dateInput &&
        clearDateButton
    ) {
        dateInput.value =
            formatDate(selectedStart) +
            ' - ' +
            formatDate(selectedEnd);

        clearDateButton.classList.remove(
            'hidden'
        );

        clearDateButton.classList.add(
            'flex'
        );
    }
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection