@extends('layouts.app')

@section('title', 'Surat Masuk')

@section('content')
@php
    $user = auth()->user();

    $userRole = strtolower(trim((string) ($user->role ?? $user->jabatan ?? '')));
    $userRole = $userRole === 'staff' ? 'staf' : $userRole;

    $canManage = in_array($userRole, ['admin', 'pimpinan'], true);

    $selectedKategori = collect(request('kategori_id', []))
        ->filter(fn ($id) => is_scalar($id) && ctype_digit((string) $id))
        ->map(fn ($id) => (string) $id)
        ->unique()
        ->values()
        ->all();

    $selectedStatus = collect(request('status', []))
        ->filter(fn ($status) => is_scalar($status))
        ->map(fn ($status) => strtolower(trim((string) $status)))
        ->filter(fn ($status) => in_array($status, [
            'baru',
            'diproses',
            'didisposisikan',
            'selesai',
            'diarsipkan'
        ], true))
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

    try {
        if ($dariTanggal && $sampaiTanggal) {
            $visibleDateRange =
                \Illuminate\Support\Carbon::parse($dariTanggal)->format('d/m/Y')
                . ' - ' .
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
        request()->filled('search')
        || !empty($selectedKategori)
        || !empty($selectedStatus)
        || request()->filled('dari_tanggal')
        || request()->filled('sampai_tanggal');
@endphp

@push('styles')
<style>
.date-range-wrapper {
    position: relative;
}

.date-range-input {
    cursor: pointer;
}

.custom-date-picker {
    position: fixed;
    z-index: 999999;
    width: 690px;
    max-width: calc(100vw - 20px);
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #fff;
    box-shadow:
        0 24px 70px rgba(15, 23, 42, .20),
        0 8px 25px rgba(15, 23, 42, .10);
    overflow: hidden;
    font-family: inherit;
}

.custom-date-picker.hidden {
    display: none;
}

.custom-date-picker-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    background: #fff;
}

.custom-date-picker-title {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
}

.custom-date-picker-close {
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9px;
    background: #f8fafc;
    color: #64748b;
    cursor: pointer;
    font-size: 18px;
}

.custom-date-picker-close:hover {
    background: #f1f5f9;
    color: #ef4444;
}

.custom-date-picker-calendars {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}

.custom-calendar {
    padding: 14px 16px 12px;
}

.custom-calendar + .custom-calendar {
    border-left: 1px solid #e2e8f0;
}

.custom-calendar-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 38px;
    margin-bottom: 8px;
}

.custom-calendar-month-buttons {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
    gap: 2px;
}

.custom-calendar-nav {
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 9px;
    background: transparent;
    color: #475569;
    cursor: pointer;
    font-size: 20px;
    line-height: 1;
}

.custom-calendar-nav:hover {
    background: #eff6ff;
    color: #2563eb;
}

.custom-calendar-month,
.custom-calendar-year {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    border: 0;
    border-radius: 9px;
    background: transparent;
    color: #334155;
    padding: 7px 8px;
    font-family: inherit;
    font-size: 11px;
    font-weight: 800;
    cursor: pointer;
}

.custom-calendar-month:hover,
.custom-calendar-year:hover {
    background: #eff6ff;
    color: #2563eb;
}

.custom-calendar-month::after,
.custom-calendar-year::after {
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
    grid-template-columns: repeat(7, 1fr);
    gap: 2px;
}

.custom-calendar-weekdays {
    margin-bottom: 4px;
}

.custom-calendar-weekday {
    height: 27px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
}

.custom-calendar-day {
    position: relative;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #475569;
    font-family: inherit;
    font-size: 10px;
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
    box-shadow: inset 0 0 0 1px #bfdbfe;
    color: #2563eb;
}

.custom-calendar-day.in-range {
    background: #eff6ff;
    color: #2563eb;
    border-radius: 0;
}

.custom-calendar-day.range-start {
    background: #2563eb;
    color: #fff;
    border-radius: 999px 0 0 999px;
}

.custom-calendar-day.range-end {
    background: #2563eb;
    color: #fff;
    border-radius: 0 999px 999px 0;
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
    gap: 10px;
    padding: 10px 14px;
    border-top: 1px solid #e2e8f0;
}

.custom-date-picker-selected {
    min-width: 0;
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
}

.custom-date-picker-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.custom-date-picker-button {
    height: 34px;
    border-radius: 9px;
    padding: 0 13px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
    font-family: inherit;
    font-size: 10px;
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

.custom-picker-panel {
    position: fixed;
    z-index: 1000000;
    width: 310px;
    max-width: calc(100vw - 20px);
    padding: 14px;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    background: #fff;
    box-shadow:
        0 24px 60px rgba(15, 23, 42, .20),
        0 8px 24px rgba(15, 23, 42, .10);
}

.custom-picker-panel.hidden {
    display: none;
}

.custom-picker-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2e8f0;
}

.custom-picker-panel-title {
    color: #334155;
    font-size: 12px;
    font-weight: 800;
}

.custom-picker-panel-close {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 8px;
    background: #f8fafc;
    color: #64748b;
    cursor: pointer;
    font-size: 18px;
}

.custom-picker-panel-close:hover {
    background: #f1f5f9;
    color: #ef4444;
}

.custom-picker-month-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 7px;
}

.custom-picker-year-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 7px;
}

.custom-picker-option {
    min-height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #fff;
    color: #475569;
    font-family: inherit;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
}

.custom-picker-option:hover {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #2563eb;
}

.custom-picker-option.active {
    border-color: #2563eb;
    background: #2563eb;
    color: #fff;
}

.custom-picker-option.current {
    box-shadow: inset 0 0 0 1px #93c5fd;
}

.custom-picker-option.active.current {
    box-shadow: none;
}

.custom-picker-year-navigation {
    display: flex;
    align-items: center;
    gap: 5px;
}

.custom-picker-year-nav {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 8px;
    background: #f8fafc;
    color: #64748b;
    cursor: pointer;
}

.custom-picker-year-nav:hover {
    background: #eff6ff;
    color: #2563eb;
}

@media (max-width: 767px) {
    .custom-date-picker {
        position: fixed;
        top: 50%;
        left: 50%;
        width: calc(100vw - 16px);
        max-height: calc(100vh - 16px);
        overflow-y: auto;
        transform: translate(-50%, -50%);
    }

    .custom-date-picker-calendars {
        grid-template-columns: 1fr;
    }

    .custom-calendar {
        padding: 12px;
    }

    .custom-calendar + .custom-calendar {
        border-left: 0;
        border-top: 1px solid #e2e8f0;
    }

    .custom-calendar-day {
        height: 35px;
    }

    .custom-picker-panel {
        top: 50% !important;
        left: 50% !important;
        width: calc(100vw - 24px);
        transform: translate(-50%, -50%);
    }

    .custom-date-picker-footer {
        position: sticky;
        bottom: 0;
        background: #fff;
    }

    body.date-picker-lock {
        overflow: hidden;
    }
}
</style>
@endpush

<div class="space-y-4 sm:space-y-6">

    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">
                Surat Masuk
            </h1>
            <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                Kelola dan pantau seluruh arsip surat masuk organisasi Anda.
            </p>
        </div>

        <div class="grid w-full grid-cols-3 gap-2 sm:flex sm:w-auto">
            <a
                href="{{ route('export.surat-masuk.excel', request()->query()) }}"
                class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-2.5 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 sm:px-3.5"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 00.707.293l5.414 5.414a1 1 0 00.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Excel
            </a>

            <a
                href="{{ route('export.surat-masuk.pdf', request()->query()) }}"
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
                    href="{{ route('surat-masuk.create') }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-2.5 py-2 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 sm:px-4"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Surat Masuk
                </a>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm sm:p-5">

        <form
            method="GET"
            action="{{ route('surat-masuk.index') }}"
            id="filterForm"
            class="space-y-3"
        >

            <div class="grid grid-cols-1 gap-2.5 lg:grid-cols-12">

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
                            placeholder="Cari perihal atau nomor surat..."
                            autocomplete="off"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                        >
                    </div>
                </div>

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
                </div>

                <div class="lg:col-span-3">
                    <div class="flex h-11 gap-2">

                        <button
                            type="submit"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-900 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:text-sm"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707L13 17v4l-4-4v-4.293a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filter
                        </button>

                        @if($hasFilters)
                            <a
                                href="{{ route('surat-masuk.index') }}"
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

            <div class="overflow-hidden rounded-xl border border-slate-200">

                <div class="flex flex-col justify-between gap-2 border-b border-slate-200 bg-slate-50/80 px-3 py-2.5 sm:flex-row sm:items-center sm:px-3.5">

                    <div class="flex min-w-0 items-center gap-2">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <h3 class="text-[11px] font-bold text-slate-700 sm:text-xs">
                                Kategori Surat
                            </h3>
                            <p class="text-[9px] text-slate-400 sm:text-[10px]">
                                Pilih satu atau beberapa kategori
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pl-8 sm:pl-0">
                        <span
                            id="kategoriCount"
                            class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-2 py-0.5 text-[9px] font-semibold text-blue-600"
                        >
                            {{ count($selectedKategori) }} dipilih
                        </span>

                        <button
                            type="button"
                            id="selectAllKategori"
                            class="text-[10px] font-semibold text-blue-600 transition hover:text-blue-700"
                        >
                            Pilih Semua
                        </button>

                        <span class="text-[10px] text-slate-300">|</span>

                        <button
                            type="button"
                            id="clearAllKategori"
                            class="text-[10px] font-semibold text-slate-500 transition hover:text-slate-700"
                        >
                            Batalkan
                        </button>
                    </div>
                </div>

                <div class="p-2.5 sm:p-3">
                    @if(isset($kategoris) && $kategoris->count())
                        <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                            @foreach($kategoris as $kategori)
                                <label class="group flex h-9 min-w-0 cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 transition hover:border-blue-200 hover:bg-blue-50/60">
                                    <input
                                        type="checkbox"
                                        name="kategori_id[]"
                                        value="{{ $kategori->id }}"
                                        class="kategori-checkbox h-3.5 w-3.5 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-1 focus:ring-blue-500/30"
                                        @checked(in_array((string) $kategori->id, $selectedKategori, true))
                                    >

                                    <span
                                        class="truncate text-[11px] font-medium text-slate-700 transition group-hover:text-blue-700"
                                        title="{{ $kategori->nama_kategori }}"
                                    >
                                        {{ $kategori->nama_kategori }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-lg border border-dashed border-slate-200 px-3 py-3 text-center text-[10px] text-slate-400">
                            Belum ada kategori surat.
                        </div>
                    @endif
                </div>

            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200">

                <div class="flex flex-col justify-between gap-2 border-b border-slate-200 bg-slate-50/80 px-3 py-2.5 sm:flex-row sm:items-center sm:px-3.5">

                    <div class="flex min-w-0 items-center gap-2">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z"/>
                            </svg>
                        </div>

                        <div>
                            <h3 class="text-[11px] font-bold text-slate-700 sm:text-xs">
                                Status Surat
                            </h3>
                            <p class="text-[9px] text-slate-400 sm:text-[10px]">
                                Pilih satu atau beberapa status
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pl-8 sm:pl-0">
                        <span
                            id="statusCount"
                            class="inline-flex items-center rounded-full border border-amber-100 bg-amber-50 px-2 py-0.5 text-[9px] font-semibold text-amber-600"
                        >
                            {{ count($selectedStatus) }} dipilih
                        </span>

                        <button
                            type="button"
                            id="selectAllStatus"
                            class="text-[10px] font-semibold text-amber-600 transition hover:text-amber-700"
                        >
                            Pilih Semua
                        </button>

                        <span class="text-[10px] text-slate-300">|</span>

                        <button
                            type="button"
                            id="clearAllStatus"
                            class="text-[10px] font-semibold text-slate-500 transition hover:text-slate-700"
                        >
                            Batalkan
                        </button>
                    </div>
                </div>

                <div class="p-2.5 sm:p-3">
                    <div class="grid grid-cols-2 gap-1.5 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">

                        @foreach($statusOptions as $value => $label)
                            <label class="group flex h-9 min-w-0 cursor-pointer items-center gap-2 rounded-lg border border-slate-200 bg-white px-2.5 transition hover:border-blue-200 hover:bg-blue-50/60">

                                <input
                                    type="checkbox"
                                    name="status[]"
                                    value="{{ $value }}"
                                    class="status-checkbox h-3.5 w-3.5 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-1 focus:ring-blue-500/30"
                                    @checked(in_array($value, $selectedStatus, true))
                                >

                                <span class="truncate text-[11px] font-medium text-slate-700 transition group-hover:text-blue-700">
                                    {{ $label }}
                                </span>

                            </label>
                        @endforeach

                    </div>
                </div>

            </div>

        </form>

    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[850px] border-collapse whitespace-nowrap text-left text-xs sm:text-sm">

                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-[11px]">
                        <th class="px-4 py-3 sm:px-6 sm:py-4">Tanggal Terima</th>
                        <th class="px-4 py-3 sm:px-6 sm:py-4">Instansi Pengirim</th>
                        <th class="px-4 py-3 sm:px-6 sm:py-4">Perihal</th>
                        <th class="px-4 py-3 sm:px-6 sm:py-4">Kategori</th>
                        <th class="px-4 py-3 sm:px-6 sm:py-4">Status</th>
                        <th class="px-4 py-3 text-center sm:px-6 sm:py-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-xs">

                    @forelse($suratMasuks ?? [] as $surat)

                        @php
                            $status = strtolower(trim((string) ($surat->status ?? 'baru')));
                            $badgeClass = $statusBadgeClasses[$status] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                            $pengirim = $surat->asal_surat ?? $surat->pengirim ?? '-';
                        @endphp

                        <tr class="transition duration-150 hover:bg-slate-50/60">

                            <td class="px-4 py-3.5 text-slate-500 sm:px-6 sm:py-4">
                                @if($surat->tanggal_terima)
                                    @try
                                        {{ \Illuminate\Support\Carbon::parse($surat->tanggal_terima)->format('d/m/Y') }}
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
                                    title="{{ $pengirim }}"
                                >
                                    {{ $pengirim }}
                                </span>
                            </td>

                            <td
                                class="max-w-xs truncate px-4 py-3.5 font-medium text-slate-800 sm:px-6 sm:py-4"
                                title="{{ $surat->perihal ?? '-' }}"
                            >
                                {{ $surat->perihal ?? '-' }}
                            </td>

                            <td class="px-4 py-3.5 text-slate-500 sm:px-6 sm:py-4">
                                {{ $surat->kategori?->nama_kategori ?? '-' }}
                            </td>

                            <td class="px-4 py-3.5 sm:px-6 sm:py-4">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $badgeClass }}">
                                    {{ $statusOptions[$status] ?? ucfirst($status) }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 text-center sm:px-6 sm:py-4">

                                <div class="inline-flex items-center gap-1">

                                    <a
                                        href="{{ route('surat-masuk.show', $surat) }}"
                                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600"
                                        title="Lihat Detail & Disposisi"
                                        aria-label="Lihat detail surat"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0a3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7c-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @if($canManage)

                                        <a
                                            href="{{ route('surat-masuk.edit', $surat) }}"
                                            class="rounded-lg p-1.5 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600"
                                            title="Ubah Data"
                                            aria-label="Ubah data surat"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.5 2.5a2.121 2.121 0 013 3L11.828 15H9v-2.828L18.5 2.5z"/>
                                            </svg>
                                        </a>

                                        <form
                                            action="{{ route('surat-masuk.destroy', $surat) }}"
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
                            <td colspan="6" class="py-10 text-center sm:py-12">

                                <div class="flex flex-col items-center justify-center">

                                    <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-1.414 0l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>

                                    <p class="text-sm font-semibold text-slate-700 sm:text-base">
                                        Belum ada data surat masuk
                                    </p>

                                    <p class="mt-0.5 max-w-md px-4 text-[11px] text-slate-400 sm:text-xs">
                                        @if($hasFilters)
                                            Tidak ada surat yang sesuai dengan filter yang digunakan.
                                        @else
                                            Belum ada data surat masuk yang tersimpan.
                                        @endif
                                    </p>

                                    @if($hasFilters)

                                        <a
                                            href="{{ route('surat-masuk.index') }}"
                                            class="mt-3 inline-flex items-center rounded-lg bg-slate-900 px-3 py-2 text-[11px] font-semibold text-white transition hover:bg-slate-800"
                                        >
                                            Reset Filter
                                        </a>

                                    @elseif($canManage)

                                        <a
                                            href="{{ route('surat-masuk.create') }}"
                                            class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-[11px] font-semibold text-white transition hover:bg-blue-700"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="border-t border-slate-100 px-4 py-3 sm:px-6 sm:py-4">
                {{ $suratMasuks->withQueryString()->links() }}
            </div>
        @endif

    </div>

</div>

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

    <div id="customDateCalendars" class="custom-date-picker-calendars"></div>

    <div class="custom-date-picker-footer">
        <div id="datePickerSelected" class="custom-date-picker-selected">
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

    const MONTHS_SHORT = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'Mei',
        'Jun',
        'Jul',
        'Agu',
        'Sep',
        'Okt',
        'Nov',
        'Des'
    ];

    const WEEKDAYS = [
        'Mg',
        'Sn',
        'Sl',
        'Rb',
        'Km',
        'Jm',
        'Sb'
    ];

    const dateInput = document.getElementById('date-range');
    const dariInput = document.getElementById('dari_tanggal');
    const sampaiInput = document.getElementById('sampai_tanggal');
    const clearButton = document.getElementById('clearDateRange');

    const picker = document.getElementById('customDatePicker');
    const calendars = document.getElementById('customDateCalendars');
    const panel = document.getElementById('customPickerPanel');

    const closePickerButton = document.getElementById('datePickerClose');
    const clearPickerButton = document.getElementById('datePickerClear');
    const applyPickerButton = document.getElementById('datePickerApply');
    const selectedLabel = document.getElementById('datePickerSelected');

    if (!dateInput || !picker || !calendars) {
        return;
    }

    let viewLeft = new Date();
    let selectedStart = parseDate(dariInput ? dariInput.value : '');
    let selectedEnd = parseDate(sampaiInput ? sampaiInput.value : '');
    let tempStart = selectedStart ? cloneDate(selectedStart) : null;
    let tempEnd = selectedEnd ? cloneDate(selectedEnd) : null;
    let panelSide = 'left';
    let panelYear = new Date().getFullYear();

    viewLeft.setDate(1);

    if (selectedStart) {
        viewLeft = new Date(
            selectedStart.getFullYear(),
            selectedStart.getMonth(),
            1
        );
    }

    if (selectedEnd && !selectedStart) {
        viewLeft = new Date(
            selectedEnd.getFullYear(),
            selectedEnd.getMonth(),
            1
        );
    }

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

        const match = String(value).match(
            /^(\d{4})-(\d{2})-(\d{2})$/
        );

        if (!match) {
            return null;
        }

        const date = new Date(
            Number(match[1]),
            Number(match[2]) - 1,
            Number(match[3])
        );

        if (
            date.getFullYear() !== Number(match[1]) ||
            date.getMonth() !== Number(match[2]) - 1 ||
            date.getDate() !== Number(match[3])
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

    function dateValue(date) {
        return date ? toISO(date) : '';
    }

    function isBetween(date, start, end) {
        if (!date || !start || !end) {
            return false;
        }

        const value = dateValue(date);
        const from = dateValue(start);
        const to = dateValue(end);

        return value > from && value < to;
    }

    function addMonths(date, amount) {
        return new Date(
            date.getFullYear(),
            date.getMonth() + amount,
            1
        );
    }

    function setViewForMonth(year, month) {
        viewLeft = new Date(
            Number(year),
            Number(month),
            1
        );

        renderCalendars();
    }

    function positionPicker() {
        if (window.innerWidth <= 767) {
            return;
        }

        const rect = dateInput.getBoundingClientRect();
        const pickerWidth = 690;

        let left =
            rect.left +
            rect.width / 2 -
            pickerWidth / 2;

        let top =
            rect.bottom + 8;

        if (left < 10) {
            left = 10;
        }

        if (
            left + pickerWidth >
            window.innerWidth - 10
        ) {
            left =
                window.innerWidth -
                pickerWidth -
                10;
        }

        const pickerHeight =
            picker.offsetHeight || 500;

        if (
            top + pickerHeight >
            window.innerHeight - 10
        ) {
            top =
                rect.top -
                pickerHeight -
                8;
        }

        if (top < 10) {
            top = 10;
        }

        picker.style.left = left + 'px';
        picker.style.top = top + 'px';
    }

    function renderCalendars() {
        calendars.innerHTML = '';

        const leftDate = new Date(
            viewLeft.getFullYear(),
            viewLeft.getMonth(),
            1
        );

        const rightDate = addMonths(
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

        requestAnimationFrame(function () {
            positionPicker();
        });
    }

    function createCalendar(date, side) {
        const calendar =
            document.createElement('div');

        calendar.className =
            'custom-calendar';

        const header =
            document.createElement('div');

        header.className =
            'custom-calendar-head';

        const prev =
            document.createElement('button');

        prev.type = 'button';
        prev.className =
            'custom-calendar-nav';

        prev.innerHTML = '&#8249;';
        prev.setAttribute(
            'aria-label',
            'Bulan sebelumnya'
        );

        const next =
            document.createElement('button');

        next.type = 'button';
        next.className =
            'custom-calendar-nav';

        next.innerHTML = '&#8250;';
        next.setAttribute(
            'aria-label',
            'Bulan berikutnya'
        );

        const monthButtons =
            document.createElement('div');

        monthButtons.className =
            'custom-calendar-month-buttons';

        const monthButton =
            document.createElement('button');

        monthButton.type = 'button';
        monthButton.className =
            'custom-calendar-month';

        monthButton.textContent =
            MONTHS[date.getMonth()];

        const yearButton =
            document.createElement('button');

        yearButton.type = 'button';
        yearButton.className =
            'custom-calendar-year';

        yearButton.textContent =
            String(date.getFullYear());

        monthButtons.appendChild(
            monthButton
        );

        monthButtons.appendChild(
            yearButton
        );

        header.appendChild(prev);
        header.appendChild(monthButtons);
        header.appendChild(next);

        monthButton.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                panelSide = side;
                panelYear = date.getFullYear();

                showMonthPanel(
                    date,
                    monthButton
                );
            }
        );

        yearButton.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                panelSide = side;
                panelYear = date.getFullYear();

                showYearPanel(
                    date,
                    yearButton
                );
            }
        );

        prev.addEventListener(
            'click',
            function (event) {
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
            function (event) {
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

        calendar.appendChild(header);

        const weekdays =
            document.createElement('div');

        weekdays.className =
            'custom-calendar-weekdays';

        WEEKDAYS.forEach(function (day) {
            const element =
                document.createElement('div');

            element.className =
                'custom-calendar-weekday';

            element.textContent = day;

            weekdays.appendChild(element);
        });

        calendar.appendChild(weekdays);

        const days =
            document.createElement('div');

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
            let index = 0;
            index < 42;
            index++
        ) {
            let dayNumber;
            let cellDate;
            let otherMonth = false;

            if (index < firstDay) {
                dayNumber =
                    daysInPreviousMonth -
                    firstDay +
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
                firstDay + daysInMonth
            ) {
                dayNumber =
                    index -
                    firstDay -
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
                    firstDay +
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
            button.className =
                'custom-calendar-day';

            button.textContent =
                String(dayNumber);

            if (otherMonth) {
                button.classList.add(
                    'other-month'
                );
            }

            const today =
                new Date();

            if (sameDate(
                cellDate,
                today
            )) {
                button.classList.add(
                    'today'
                );
            }

            if (
                tempStart &&
                tempEnd &&
                isBetween(
                    cellDate,
                    tempStart,
                    tempEnd
                )
            ) {
                button.classList.add(
                    'in-range'
                );
            }

            if (
                tempStart &&
                sameDate(
                    cellDate,
                    tempStart
                )
            ) {
                button.classList.add(
                    'range-start'
                );
            }

            if (
                tempEnd &&
                sameDate(
                    cellDate,
                    tempEnd
                )
            ) {
                button.classList.add(
                    'range-end'
                );
            }

            button.addEventListener(
                'click',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    selectDate(cellDate);
                }
            );

            days.appendChild(button);
        }

        calendar.appendChild(days);

        return calendar;
    }

    function selectDate(date) {
        const chosen =
            cloneDate(date);

        if (
            !tempStart ||
            (tempStart && tempEnd)
        ) {
            tempStart = chosen;
            tempEnd = null;

            updateSelectedLabel();
            renderCalendars();

            return;
        }

        if (
            tempStart &&
            !tempEnd
        ) {
            if (
                dateValue(chosen) <
                dateValue(tempStart)
            ) {
                tempEnd =
                    cloneDate(tempStart);

                tempStart =
                    chosen;
            } else {
                tempEnd =
                    chosen;
            }

            updateSelectedLabel();
            renderCalendars();
        }
    }

    function updateSelectedLabel() {
        if (
            tempStart &&
            tempEnd
        ) {
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

    function showPicker() {
        closePanel();

        tempStart =
            selectedStart
                ? cloneDate(selectedStart)
                : null;

        tempEnd =
            selectedEnd
                ? cloneDate(selectedEnd)
                : null;

        if (tempStart) {
            viewLeft =
                new Date(
                    tempStart.getFullYear(),
                    tempStart.getMonth(),
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

        requestAnimationFrame(function () {
            positionPicker();
        });
    }

    function hidePicker() {
        picker.classList.add(
            'hidden'
        );

        closePanel();

        document.body.classList.remove(
            'date-picker-lock'
        );
    }

    function showMonthPanel(
        calendarDate,
        anchor
    ) {
        closePanel();

        panel.innerHTML = '';

        panel.classList.remove(
            'hidden'
        );

        const header =
            document.createElement('div');

        header.className =
            'custom-picker-panel-header';

        const title =
            document.createElement('div');

        title.className =
            'custom-picker-panel-title';

        title.textContent =
            'Pilih Bulan ' +
            calendarDate.getFullYear();

        const close =
            document.createElement('button');

        close.type = 'button';
        close.className =
            'custom-picker-panel-close';

        close.innerHTML =
            '&times;';

        close.addEventListener(
            'click',
            function () {
                closePanel();
            }
        );

        header.appendChild(title);
        header.appendChild(close);

        panel.appendChild(header);

        const grid =
            document.createElement('div');

        grid.className =
            'custom-picker-month-grid';

        MONTHS.forEach(function (
            monthName,
            monthIndex
        ) {
            const button =
                document.createElement('button');

            button.type = 'button';

            button.className =
                'custom-picker-option';

            button.textContent =
                monthName;

            if (
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

                    if (
                        panelSide ===
                        'left'
                    ) {
                        viewLeft =
                            new Date(
                                calendarDate.getFullYear(),
                                monthIndex,
                                1
                            );
                    } else {
                        const right =
                            new Date(
                                calendarDate.getFullYear(),
                                monthIndex,
                                1
                            );

                        viewLeft =
                            addMonths(
                                right,
                                -1
                            );
                    }

                    closePanel();
                    renderCalendars();
                }
            );

            grid.appendChild(button);
        });

        panel.appendChild(grid);

        positionPanel(
            panel,
            anchor
        );
    }

    function showYearPanel(
        calendarDate,
        anchor
    ) {
        closePanel();

        let startYear =
            Math.floor(
                calendarDate.getFullYear() /
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
    ) {
        panel.innerHTML = '';

        panel.classList.remove(
            'hidden'
        );

        const header =
            document.createElement('div');

        header.className =
            'custom-picker-panel-header';

        const title =
            document.createElement('div');

        title.className =
            'custom-picker-panel-title';

        title.textContent =
            startYear +
            ' - ' +
            (startYear + 11);

        const navigation =
            document.createElement('div');

        navigation.className =
            'custom-picker-year-navigation';

        const previous =
            document.createElement('button');

        previous.type = 'button';
        previous.className =
            'custom-picker-year-nav';

        previous.innerHTML =
            '&#8249;';

        const next =
            document.createElement('button');

        next.type = 'button';
        next.className =
            'custom-picker-year-nav';

        next.innerHTML =
            '&#8250;';

        const close =
            document.createElement('button');

        close.type = 'button';
        close.className =
            'custom-picker-panel-close';

        close.innerHTML =
            '&times;';

        previous.addEventListener(
            'click',
            function (event) {
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
            function (event) {
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
            function () {
                closePanel();
            }
        );

        navigation.appendChild(previous);
        navigation.appendChild(next);
        navigation.appendChild(close);

        header.appendChild(title);
        header.appendChild(navigation);

        panel.appendChild(header);

        const grid =
            document.createElement('div');

        grid.className =
            'custom-picker-year-grid';

        for (
            let year =
                startYear;
            year <
                startYear + 12;
            year++
        ) {
            const button =
                document.createElement('button');

            button.type = 'button';

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

            button.addEventListener(
                'click',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    const month =
                        calendarDate.getMonth();

                    if (
                        panelSide ===
                        'left'
                    ) {
                        viewLeft =
                            new Date(
                                year,
                                month,
                                1
                            );
                    } else {
                        viewLeft =
                            new Date(
                                year,
                                month - 1,
                                1
                            );
                    }

                    closePanel();
                    renderCalendars();
                }
            );

            grid.appendChild(button);
        }

        panel.appendChild(grid);

        positionPanel(
            panel,
            anchor
        );
    }

    function positionPanel(
        element,
        anchor
    ) {
        if (
            window.innerWidth <= 767
        ) {
            element.style.left = '';
            element.style.top = '';

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
            rect.bottom + 8;

        if (left < 10) {
            left = 10;
        }

        if (
            left + width >
            window.innerWidth - 10
        ) {
            left =
                window.innerWidth -
                width -
                10;
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

    function closePanel() {
        panel.classList.add(
            'hidden'
        );

        panel.innerHTML = '';
    }

    function applyDateRange() {
        if (
            !tempStart ||
            !tempEnd
        ) {
            return;
        }

        selectedStart =
            cloneDate(tempStart);

        selectedEnd =
            cloneDate(tempEnd);

        dariInput.value =
            toISO(selectedStart);

        sampaiInput.value =
            toISO(selectedEnd);

        dateInput.value =
            formatDate(selectedStart) +
            ' - ' +
            formatDate(selectedEnd);

        clearButton.classList.remove(
            'hidden'
        );

        clearButton.classList.add(
            'flex'
        );

        hidePicker();
    }

    function clearDateRange() {
        selectedStart = null;
        selectedEnd = null;
        tempStart = null;
        tempEnd = null;

        dariInput.value = '';
        sampaiInput.value = '';
        dateInput.value = '';

        clearButton.classList.remove(
            'flex'
        );

        clearButton.classList.add(
            'hidden'
        );

        viewLeft = new Date();

        viewLeft.setDate(1);

        hidePicker();
    }

    dateInput.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (
                picker.classList.contains(
                    'hidden'
                )
            ) {
                showPicker();
            } else {
                hidePicker();
            }
        }
    );

    dateInput.addEventListener(
        'focus',
        function () {
            if (
                picker.classList.contains(
                    'hidden'
                )
            ) {
                showPicker();
            }
        }
    );

    closePickerButton.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            hidePicker();
        }
    );

    clearPickerButton.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            clearDateRange();
        }
    );

    applyPickerButton.addEventListener(
        'click',
        function (event) {
            event.preventDefault();
            event.stopPropagation();

            applyDateRange();
        }
    );

    clearButton.addEventListener(
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
                picker.classList.contains(
                    'hidden'
                )
            ) {
                return;
            }

            if (
                picker.contains(
                    event.target
                )
            ) {
                return;
            }

            if (
                panel.contains(
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
                clearButton.contains(
                    event.target
                )
            ) {
                return;
            }

            hidePicker();
        }
    );

    window.addEventListener(
        'resize',
        function () {
            if (
                !picker.classList.contains(
                    'hidden'
                )
            ) {
                positionPicker();
            }
        }
    );

    window.addEventListener(
        'scroll',
        function () {
            if (
                window.innerWidth > 767 &&
                !picker.classList.contains(
                    'hidden'
                )
            ) {
                positionPicker();
            }
        },
        true
    );

    function initializeCheckboxFilters() {

        const kategoriCheckboxes =
            document.querySelectorAll(
                '.kategori-checkbox'
            );

        const kategoriCount =
            document.getElementById(
                'kategoriCount'
            );

        function updateKategoriCount() {
            if (!kategoriCount) {
                return;
            }

            const count =
                document.querySelectorAll(
                    '.kategori-checkbox:checked'
                ).length;

            kategoriCount.textContent =
                count + ' dipilih';
        }

        kategoriCheckboxes.forEach(
            function (checkbox) {
                checkbox.addEventListener(
                    'change',
                    updateKategoriCount
                );
            }
        );

        document
            .getElementById(
                'selectAllKategori'
            )
            ?.addEventListener(
                'click',
                function () {
                    kategoriCheckboxes.forEach(
                        function (checkbox) {
                            checkbox.checked =
                                true;
                        }
                    );

                    updateKategoriCount();
                }
            );

        document
            .getElementById(
                'clearAllKategori'
            )
            ?.addEventListener(
                'click',
                function () {
                    kategoriCheckboxes.forEach(
                        function (checkbox) {
                            checkbox.checked =
                                false;
                        }
                    );

                    updateKategoriCount();
                }
            );

        updateKategoriCount();

        const statusCheckboxes =
            document.querySelectorAll(
                '.status-checkbox'
            );

        const statusCount =
            document.getElementById(
                'statusCount'
            );

        function updateStatusCount() {
            if (!statusCount) {
                return;
            }

            const count =
                document.querySelectorAll(
                    '.status-checkbox:checked'
                ).length;

            statusCount.textContent =
                count + ' dipilih';
        }

        statusCheckboxes.forEach(
            function (checkbox) {
                checkbox.addEventListener(
                    'change',
                    updateStatusCount
                );
            }
        );

        document
            .getElementById(
                'selectAllStatus'
            )
            ?.addEventListener(
                'click',
                function () {
                    statusCheckboxes.forEach(
                        function (checkbox) {
                            checkbox.checked =
                                true;
                        }
                    );

                    updateStatusCount();
                }
            );

        document
            .getElementById(
                'clearAllStatus'
            )
            ?.addEventListener(
                'click',
                function () {
                    statusCheckboxes.forEach(
                        function (checkbox) {
                            checkbox.checked =
                                false;
                        }
                    );

                    updateStatusCount();
                }
            );

        updateStatusCount();
    }

    function initializeDeleteConfirmation() {
        document
            .querySelectorAll(
                '.delete-btn'
            )
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
                                    title: 'Hapus Surat Masuk?',
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
                                        confirmButton: 'rounded-xl text-xs font-semibold px-4 py-2.5',
                                        cancelButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
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
                                    'Yakin ingin menghapus surat masuk ini?'
                                )
                            ) {
                                form.submit();
                            }
                        }
                    );
                }
            );
    }

    initializeCheckboxFilters();
    initializeDeleteConfirmation();

    if (
        selectedStart &&
        selectedEnd
    ) {
        dateInput.value =
            formatDate(selectedStart) +
            ' - ' +
            formatDate(selectedEnd);

        clearButton.classList.remove(
            'hidden'
        );

        clearButton.classList.add(
            'flex'
        );
    }

})();
</script>
@endpush

@endsection