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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

<style>
.date-range-input {
    cursor: pointer;
}

.date-range-input::selection {
    background: transparent;
}

.daterangepicker {
    z-index: 99999 !important;
    width: auto;
    min-width: 620px;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    background: #fff;
    box-shadow:
        0 20px 50px rgba(15, 23, 42, .14),
        0 8px 20px rgba(15, 23, 42, .06);
    font-family: inherit;
    padding: 10px;
}

.daterangepicker .calendar-table {
    border: 0;
    background: transparent;
}

.daterangepicker .calendar-table table {
    border-collapse: separate;
    border-spacing: 2px;
}

.daterangepicker .calendar-table th {
    color: #94a3b8;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}

.daterangepicker .calendar-table td {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    color: #475569;
    font-size: 11px;
    font-weight: 500;
    transition:
        background-color .15s ease,
        color .15s ease;
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
    position: relative;
    z-index: 20;
    border-radius: 8px;
    color: #64748b;
    transition:
        background-color .15s ease,
        color .15s ease;
}

.daterangepicker .prev:hover,
.daterangepicker .next:hover {
    background: #f1f5f9;
    color: #2563eb;
}

/*
|--------------------------------------------------------------------------
| HEADER BULAN / TAHUN
|--------------------------------------------------------------------------
*/

.daterangepicker .calendar-table th.month {
    position: relative;
    padding: 4px 0 8px !important;
    height: 40px;
    white-space: nowrap;
    overflow: visible !important;
}

.date-picker-header {
    position: relative;
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 2px;
    width: 100%;
    min-height: 32px;
}

.date-picker-month,
.date-picker-year {
    position: relative;
    z-index: 100001;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 0 !important;
    outline: 0 !important;
    border-radius: 8px;

    background: transparent !important;
    color: #334155;

    font-family: inherit;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;

    padding: 7px 8px;
    margin: 0;

    cursor: pointer !important;
    pointer-events: auto !important;

    -webkit-appearance: none;
    appearance: none;

    transition:
        background-color .15s ease,
        color .15s ease;
}

.date-picker-month:hover,
.date-picker-year:hover {
    background: #eff6ff !important;
    color: #2563eb !important;
}

.date-picker-month:focus,
.date-picker-year:focus {
    outline: none !important;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, .12);
}

.date-picker-month::after,
.date-picker-year::after {
    content: '';
    display: inline-block;
    width: 5px;
    height: 5px;
    margin-left: 6px;
    margin-top: -3px;

    border-right: 1.5px solid currentColor;
    border-bottom: 1.5px solid currentColor;

    transform: rotate(45deg);
}

/*
|--------------------------------------------------------------------------
| PANEL BULAN / TAHUN
|--------------------------------------------------------------------------
*/

.date-picker-panel {
    position: fixed;
    z-index: 1000000 !important;

    width: 300px;
    max-width: calc(100vw - 20px);

    padding: 14px;

    border: 1px solid #e2e8f0;
    border-radius: 16px;

    background: #fff;

    box-shadow:
        0 24px 60px rgba(15, 23, 42, .20),
        0 8px 24px rgba(15, 23, 42, .10);
}

.date-picker-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-bottom: 12px;
    padding-bottom: 10px;

    border-bottom: 1px solid #e2e8f0;
}

.date-picker-panel-title {
    color: #334155;
    font-size: 12px;
    font-weight: 700;
}

.date-picker-panel-close {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    width: 28px;
    height: 28px;

    border: 0;
    border-radius: 8px;

    background: #f8fafc;
    color: #64748b;

    font-size: 18px;
    line-height: 1;

    cursor: pointer;
}

.date-picker-panel-close:hover {
    background: #f1f5f9;
    color: #2563eb;
}

.date-picker-month-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 7px;
}

.date-picker-year-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 7px;

    max-height: 270px;
    overflow-y: auto;

    padding-right: 3px;
}

.date-picker-option {
    display: flex;
    align-items: center;
    justify-content: center;

    min-height: 38px;

    border: 1px solid #e2e8f0;
    border-radius: 9px;

    background: #fff;
    color: #475569;

    font-family: inherit;
    font-size: 11px;
    font-weight: 600;

    cursor: pointer;

    transition:
        background-color .15s ease,
        color .15s ease,
        border-color .15s ease;
}

.date-picker-option:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #2563eb;
}

.date-picker-option.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}

.date-picker-option.current {
    box-shadow: inset 0 0 0 1px #93c5fd;
    color: #2563eb;
}

.date-picker-option.active.current {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
    box-shadow: none;
}

/*
|--------------------------------------------------------------------------
| FOOTER DATE RANGE
|--------------------------------------------------------------------------
*/

.daterangepicker .drp-buttons {
    border-top: 1px solid #e2e8f0;
    margin-top: 8px;
    padding: 10px 5px 2px;
}

.daterangepicker .drp-selected {
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
}

.daterangepicker .applyBtn {
    border: 0;
    border-radius: 10px;

    background: #2563eb;
    color: #fff;

    font-size: 11px;
    font-weight: 700;

    padding: 8px 14px;
}

.daterangepicker .applyBtn:hover {
    background: #1d4ed8;
}

.daterangepicker .cancelBtn {
    border: 1px solid #e2e8f0;
    border-radius: 10px;

    background: #f8fafc;
    color: #475569;

    font-size: 11px;
    font-weight: 700;

    padding: 8px 14px;
}

.daterangepicker .cancelBtn:hover {
    background: #f1f5f9;
    color: #334155;
}

/*
|--------------------------------------------------------------------------
| MOBILE
|--------------------------------------------------------------------------
*/

@media (max-width: 767px) {
    .daterangepicker {
        position: fixed !important;

        top: 50% !important;
        left: 50% !important;
        right: auto !important;
        bottom: auto !important;

        width: calc(100vw - 20px) !important;
        min-width: 0 !important;
        max-width: 420px;

        max-height: calc(100vh - 20px);
        overflow-y: auto;

        transform: translate(-50%, -50%) !important;
    }

    .daterangepicker .calendar {
        float: none !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
    }

    .daterangepicker .calendar.right {
        margin-top: 10px !important;
    }

    .daterangepicker .calendar-table td {
        width: 36px;
        height: 36px;
    }

    .date-picker-panel {
        position: fixed !important;

        top: 50% !important;
        left: 50% !important;

        width: calc(100vw - 30px);
        max-width: 340px;

        transform: translate(-50%, -50%) !important;
    }

    .date-picker-year-grid {
        grid-template-columns: repeat(3, 1fr);
        max-height: 300px;
    }

    body.daterangepicker-open {
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
                <span>Excel</span>
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
                <span>PDF</span>
            </a>

            @if($canManage)
                <a
                    href="{{ route('surat-masuk.create') }}"
                    class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-2.5 py-2 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 sm:px-4"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Surat Masuk</span>
                </a>
            @else
                <div></div>
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
                            placeholder="Cari perihal atau nomor surat..."
                            autocomplete="off"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                        >

                    </div>
                </div>

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
                            class="{{ $visibleDateRange ? 'flex' : 'hidden' }} absolute inset-y-0 right-0 z-10 w-10 items-center justify-center text-slate-400 transition hover:text-rose-500"
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

                <div class="lg:col-span-3">

                    <div class="flex h-11 gap-2">

                        <button
                            type="submit"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-900 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:text-sm"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707L13 17v4l-4-4v-4.293a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span>Filter</span>
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

@push('scripts')

<script>
(function () {
    'use strict';

    const CDN = {
        jquery: 'https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js',
        moment: 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js',
        daterangepicker: 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',
        sweetalert: 'https://cdn.jsdelivr.net/npm/sweetalert2@11'
    };

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

    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            const existing = document.querySelector(
                'script[src="' + src + '"]'
            );

            if (existing) {
                if (
                    src === CDN.jquery &&
                    window.jQuery
                ) {
                    resolve();
                    return;
                }

                if (
                    src === CDN.moment &&
                    window.moment
                ) {
                    resolve();
                    return;
                }

                if (
                    src === CDN.daterangepicker &&
                    window.jQuery &&
                    window.jQuery.fn &&
                    window.jQuery.fn.daterangepicker
                ) {
                    resolve();
                    return;
                }

                if (
                    src === CDN.sweetalert &&
                    window.Swal
                ) {
                    resolve();
                    return;
                }

                existing.addEventListener(
                    'load',
                    resolve,
                    { once: true }
                );

                existing.addEventListener(
                    'error',
                    reject,
                    { once: true }
                );

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

    async function loadDependencies() {
        try {
            if (!window.jQuery) {
                await loadScript(CDN.jquery);
            }

            if (!window.moment) {
                await loadScript(CDN.moment);
            }

            if (
                !window.jQuery ||
                !window.jQuery.fn ||
                !window.jQuery.fn.daterangepicker
            ) {
                await loadScript(CDN.daterangepicker);
            }

            if (!window.Swal) {
                await loadScript(CDN.sweetalert);
            }

            initializePage();

        } catch (error) {
            console.error(
                'Gagal memuat komponen Surat Masuk:',
                error
            );

            initializeBasicFeatures();
        }
    }

    function initializePage() {
        initializeBasicFeatures();
        initializeDateRange();
    }

    function initializeBasicFeatures() {
        initializeKategoriFilter();
        initializeStatusFilter();
        initializeDeleteConfirmation();
    }

    function initializeKategoriFilter() {
        const checkboxes = document.querySelectorAll(
            '.kategori-checkbox'
        );

        const countElement = document.getElementById(
            'kategoriCount'
        );

        function updateCount() {
            if (!countElement) return;

            const checked =
                document.querySelectorAll(
                    '.kategori-checkbox:checked'
                ).length;

            countElement.textContent =
                checked + ' dipilih';
        }

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener(
                'change',
                updateCount
            );
        });

        document
            .getElementById('selectAllKategori')
            ?.addEventListener('click', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = true;
                });

                updateCount();
            });

        document
            .getElementById('clearAllKategori')
            ?.addEventListener('click', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                });

                updateCount();
            });

        updateCount();
    }

    function initializeStatusFilter() {
        const checkboxes = document.querySelectorAll(
            '.status-checkbox'
        );

        const countElement = document.getElementById(
            'statusCount'
        );

        function updateCount() {
            if (!countElement) return;

            const checked =
                document.querySelectorAll(
                    '.status-checkbox:checked'
                ).length;

            countElement.textContent =
                checked + ' dipilih';
        }

        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener(
                'change',
                updateCount
            );
        });

        document
            .getElementById('selectAllStatus')
            ?.addEventListener('click', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = true;
                });

                updateCount();
            });

        document
            .getElementById('clearAllStatus')
            ?.addEventListener('click', function () {
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = false;
                });

                updateCount();
            });

        updateCount();
    }

    function initializeDateRange() {
        const $ = window.jQuery;

        if (
            !$ ||
            !$.fn ||
            !$.fn.daterangepicker ||
            !window.moment
        ) {
            console.error(
                'jQuery, Moment atau DateRangePicker belum tersedia.'
            );

            return;
        }

        const dateInput = $('#date-range');
        const dariTanggal = $('#dari_tanggal');
        const sampaiTanggal = $('#sampai_tanggal');
        const clearButton = $('#clearDateRange');

        if (!dateInput.length) return;

        if (
            dateInput.data('daterangepicker')
        ) {
            return;
        }

        const startValue = dariTanggal.val();
        const endValue = sampaiTanggal.val();

        const options = {
            autoUpdateInput: false,
            showDropdowns: false,
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

                daysOfWeek: [
                    'Mg',
                    'Sn',
                    'Sl',
                    'Rb',
                    'Km',
                    'Jm',
                    'Sb'
                ],

                monthNames: MONTHS,

                firstDay: 1
            }
        };

        if (startValue && endValue) {
            const start = moment(
                startValue,
                'YYYY-MM-DD',
                true
            );

            const end = moment(
                endValue,
                'YYYY-MM-DD',
                true
            );

            if (
                start.isValid() &&
                end.isValid()
            ) {
                options.startDate = start;
                options.endDate = end;
            }
        }

        dateInput.daterangepicker(options);

        const picker =
            dateInput.data('daterangepicker');

        if (!picker) return;

        /*
        |--------------------------------------------------------------------------
        | PANEL
        |--------------------------------------------------------------------------
        */

        function removePanel() {
            document
                .querySelectorAll(
                    '.date-picker-panel'
                )
                .forEach(function (panel) {
                    panel.remove();
                });
        }

        function getCalendar(side) {
            return side === 'right'
                ? picker.rightCalendar
                : picker.leftCalendar;
        }

        function getCalendarMonth(side) {
            const calendar =
                getCalendar(side);

            if (
                !calendar ||
                !calendar.month
            ) {
                return moment();
            }

            return calendar.month.clone();
        }

        /*
        |--------------------------------------------------------------------------
        | RENDER HEADER
        |--------------------------------------------------------------------------
        */

        function renderHeaderButtons() {
            if (
                !picker ||
                !picker.container ||
                !picker.container.length
            ) {
                return;
            }

            picker.container
                .find('.calendar')
                .each(function () {

                    const calendarElement = this;

                    const side =
                        calendarElement.classList.contains(
                            'right'
                        )
                            ? 'right'
                            : 'left';

                    const header =
                        calendarElement.querySelector(
                            'th.month'
                        );

                    if (!header) return;

                    const current =
                        getCalendarMonth(side);

                    const monthIndex =
                        current.month();

                    const year =
                        current.year();

                    /*
                    Jangan mencari .monthselect / .yearselect.
                    Kita buat tombol sendiri.
                    */

                    header.innerHTML = '';

                    const wrapper =
                        document.createElement('div');

                    wrapper.className =
                        'date-picker-header';

                    const monthButton =
                        document.createElement('button');

                    monthButton.type = 'button';
                    monthButton.className =
                        'date-picker-month';

                    monthButton.dataset.side =
                        side;

                    monthButton.dataset.month =
                        String(monthIndex);

                    monthButton.dataset.year =
                        String(year);

                    monthButton.textContent =
                        MONTHS[monthIndex];

                    monthButton.title =
                        'Pilih bulan';

                    const yearButton =
                        document.createElement('button');

                    yearButton.type = 'button';
                    yearButton.className =
                        'date-picker-year';

                    yearButton.dataset.side =
                        side;

                    yearButton.dataset.year =
                        String(year);

                    yearButton.textContent =
                        String(year);

                    yearButton.title =
                        'Pilih tahun';

                    wrapper.appendChild(
                        monthButton
                    );

                    wrapper.appendChild(
                        yearButton
                    );

                    header.appendChild(
                        wrapper
                    );
                });
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE CALENDAR
        |--------------------------------------------------------------------------
        */

        const originalUpdateCalendars =
            picker.updateCalendars.bind(picker);

        picker.updateCalendars = function () {

            originalUpdateCalendars();

            setTimeout(
                renderHeaderButtons,
                0
            );
        };

        /*
        |--------------------------------------------------------------------------
        | SET BULAN / TAHUN
        |--------------------------------------------------------------------------
        */

        function setCalendarMonth(
            side,
            year,
            month
        ) {
            const target =
                moment([
                    Number(year),
                    Number(month),
                    1
                ]);

            if (!target.isValid()) {
                return;
            }

            if (side === 'left') {

                picker.leftCalendar.month =
                    target.clone();

                if (picker.linkedCalendars) {
                    picker.rightCalendar.month =
                        target.clone().add(
                            1,
                            'month'
                        );
                }

            } else {

                picker.rightCalendar.month =
                    target.clone();

                if (picker.linkedCalendars) {
                    picker.leftCalendar.month =
                        target.clone().subtract(
                            1,
                            'month'
                        );
                }
            }

            picker.updateCalendars();

            setTimeout(
                renderHeaderButtons,
                10
            );
        }

        /*
        |--------------------------------------------------------------------------
        | POSITION PANEL
        |--------------------------------------------------------------------------
        */

        function positionPanel(
            panel,
            anchor
        ) {
            if (
                window.innerWidth <= 767
            ) {
                return;
            }

            const rect =
                anchor.getBoundingClientRect();

            const width = 300;

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
                top + 360 >
                window.innerHeight
            ) {
                top =
                    rect.top -
                    368;
            }

            if (top < 10) {
                top = 10;
            }

            panel.style.left =
                left + 'px';

            panel.style.top =
                top + 'px';
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE PANEL
        |--------------------------------------------------------------------------
        */

        function createPanel(
            title,
            anchor,
            gridClass
        ) {
            removePanel();

            const panel =
                document.createElement('div');

            panel.className =
                'date-picker-panel';

            const panelHeader =
                document.createElement('div');

            panelHeader.className =
                'date-picker-panel-header';

            const titleElement =
                document.createElement('div');

            titleElement.className =
                'date-picker-panel-title';

            titleElement.textContent =
                title;

            const closeButton =
                document.createElement('button');

            closeButton.type = 'button';
            closeButton.className =
                'date-picker-panel-close';

            closeButton.innerHTML =
                '&times;';

            closeButton.title =
                'Tutup';

            closeButton.addEventListener(
                'click',
                function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    removePanel();
                }
            );

            const grid =
                document.createElement('div');

            grid.className =
                gridClass;

            panelHeader.appendChild(
                titleElement
            );

            panelHeader.appendChild(
                closeButton
            );

            panel.appendChild(
                panelHeader
            );

            panel.appendChild(
                grid
            );

            document.body.appendChild(
                panel
            );

            positionPanel(
                panel,
                anchor
            );

            return {
                panel: panel,
                grid: grid
            };
        }

        /*
        |--------------------------------------------------------------------------
        | BULAN
        |--------------------------------------------------------------------------
        */

        function showMonthPanel(
            side,
            anchor
        ) {
            const current =
                getCalendarMonth(side);

            const year =
                current.year();

            const selectedMonth =
                current.month();

            const result =
                createPanel(
                    'Pilih Bulan ' + year,
                    anchor,
                    'date-picker-month-grid'
                );

            for (
                let month = 0;
                month < 12;
                month++
            ) {
                const button =
                    document.createElement('button');

                button.type = 'button';

                button.className =
                    'date-picker-option';

                button.textContent =
                    MONTHS[month];

                if (
                    month === selectedMonth
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

                        setCalendarMonth(
                            side,
                            year,
                            month
                        );

                        removePanel();
                    }
                );

                result.grid.appendChild(
                    button
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | TAHUN
        |--------------------------------------------------------------------------
        */

        function showYearPanel(
            side,
            anchor
        ) {
            const current =
                getCalendarMonth(side);

            const currentYear =
                current.year();

            const currentMonth =
                current.month();

            const minYear =
                Math.min(
                    2000,
                    currentYear - 20
                );

            const maxYear =
                Math.max(
                    moment().year() + 10,
                    currentYear + 10
                );

            const result =
                createPanel(
                    'Pilih Tahun',
                    anchor,
                    'date-picker-year-grid'
                );

            for (
                let year = minYear;
                year <= maxYear;
                year++
            ) {
                const button =
                    document.createElement('button');

                button.type = 'button';

                button.className =
                    'date-picker-option';

                button.textContent =
                    String(year);

                if (
                    year === currentYear
                ) {
                    button.classList.add(
                        'active'
                    );
                }

                if (
                    year === moment().year()
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

                        setCalendarMonth(
                            side,
                            year,
                            currentMonth
                        );

                        removePanel();
                    }
                );

                result.grid.appendChild(
                    button
                );
            }

            setTimeout(function () {

                const active =
                    result.grid.querySelector(
                        '.active'
                    );

                if (active) {
                    active.scrollIntoView({
                        block: 'center'
                    });
                }

            }, 0);
        }

        /*
        |--------------------------------------------------------------------------
        | EVENT KLIK BULAN
        |--------------------------------------------------------------------------
        */

        $(document)
            .off(
                'click.suratMasukMonth'
            )
            .on(
                'click.suratMasukMonth',
                '.date-picker-month',
                function (event) {

                    event.preventDefault();
                    event.stopImmediatePropagation();

                    showMonthPanel(
                        this.dataset.side,
                        this
                    );

                    return false;
                }
            );

        /*
        |--------------------------------------------------------------------------
        | EVENT KLIK TAHUN
        |--------------------------------------------------------------------------
        */

        $(document)
            .off(
                'click.suratMasukYear'
            )
            .on(
                'click.suratMasukYear',
                '.date-picker-year',
                function (event) {

                    event.preventDefault();
                    event.stopImmediatePropagation();

                    showYearPanel(
                        this.dataset.side,
                        this
                    );

                    return false;
                }
            );

        /*
        |--------------------------------------------------------------------------
        | TANGGAL DIPILIH
        |--------------------------------------------------------------------------
        */

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
                        startDate.format(
                            'DD/MM/YYYY'
                        ) +
                        ' - ' +
                        endDate.format(
                            'DD/MM/YYYY'
                        )
                    );

                    clearButton.css(
                        'display',
                        'flex'
                    );

                    return;
                }
            }

            dateInput.val('');
            clearButton.hide();
        }

        /*
        |--------------------------------------------------------------------------
        | SHOW
        |--------------------------------------------------------------------------
        */

        dateInput.on(
            'show.daterangepicker',
            function () {

                removePanel();

                setTimeout(
                    renderHeaderButtons,
                    20
                );

                if (
                    window.innerWidth <= 767
                ) {
                    document.body.classList.add(
                        'daterangepicker-open'
                    );
                }
            }
        );

        dateInput.on(
            'shown.daterangepicker',
            function () {

                setTimeout(
                    renderHeaderButtons,
                    20
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | APPLY
        |--------------------------------------------------------------------------
        */

        dateInput.on(
            'apply.daterangepicker',
            function (
                event,
                selectedPicker
            ) {

                const start =
                    selectedPicker.startDate;

                const end =
                    selectedPicker.endDate;

                if (
                    !start ||
                    !end
                ) {
                    return;
                }

                dariTanggal.val(
                    start.format(
                        'YYYY-MM-DD'
                    )
                );

                sampaiTanggal.val(
                    end.format(
                        'YYYY-MM-DD'
                    )
                );

                dateInput.val(
                    start.format(
                        'DD/MM/YYYY'
                    ) +
                    ' - ' +
                    end.format(
                        'DD/MM/YYYY'
                    )
                );

                clearButton.css(
                    'display',
                    'flex'
                );

                removePanel();
            }
        );

        /*
        |--------------------------------------------------------------------------
        | BERSIHKAN
        |--------------------------------------------------------------------------
        */

        dateInput.on(
            'cancel.daterangepicker',
            function () {

                dariTanggal.val('');
                sampaiTanggal.val('');

                dateInput.val('');

                clearButton.hide();

                removePanel();

                const today =
                    moment();

                picker.setStartDate(
                    today
                );

                picker.setEndDate(
                    today
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | HIDE
        |--------------------------------------------------------------------------
        */

        dateInput.on(
            'hide.daterangepicker',
            function () {

                document.body.classList.remove(
                    'daterangepicker-open'
                );

                removePanel();

                setTimeout(
                    renderHeaderButtons,
                    10
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | NAVIGASI PREV / NEXT
        |--------------------------------------------------------------------------
        */

        picker.container.on(
            'click.suratMasukNavigation',
            '.prev, .next',
            function () {

                removePanel();

                setTimeout(
                    renderHeaderButtons,
                    30
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | CLEAR BUTTON
        |--------------------------------------------------------------------------
        */

        clearButton.on(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                dariTanggal.val('');
                sampaiTanggal.val('');

                dateInput.val('');

                clearButton.hide();

                removePanel();

                const today =
                    moment();

                picker.setStartDate(
                    today
                );

                picker.setEndDate(
                    today
                );

                picker.updateCalendars();

                setTimeout(
                    renderHeaderButtons,
                    20
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | KLIK DI LUAR PANEL
        |--------------------------------------------------------------------------
        */

        $(document)
            .off(
                'mousedown.suratMasukDateOutside'
            )
            .on(
                'mousedown.suratMasukDateOutside',
                function (event) {

                    const target =
                        $(event.target);

                    if (
                        target.closest(
                            '.date-picker-panel'
                        ).length
                    ) {
                        return;
                    }

                    if (
                        target.closest(
                            '.date-picker-month'
                        ).length
                    ) {
                        return;
                    }

                    if (
                        target.closest(
                            '.date-picker-year'
                        ).length
                    ) {
                        return;
                    }

                    if (
                        target.closest(
                            '.daterangepicker'
                        ).length
                    ) {
                        return;
                    }

                    if (
                        target.closest(
                            '#date-range'
                        ).length
                    ) {
                        return;
                    }

                    removePanel();
                }
            );

        /*
        |--------------------------------------------------------------------------
        | INITIAL RENDER
        |--------------------------------------------------------------------------
        */

        setTimeout(
            renderHeaderButtons,
            30
        );

        updateVisibleDate();
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE CONFIRMATION
    |--------------------------------------------------------------------------
    */

    function initializeDeleteConfirmation() {

        document
            .querySelectorAll('.delete-btn')
            .forEach(function (button) {

                if (
                    button.dataset.deleteInitialized === '1'
                ) {
                    return;
                }

                button.dataset.deleteInitialized =
                    '1';

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
                            typeof Swal !==
                            'undefined'
                        ) {

                            Swal.fire({
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
            });
    }

    function start() {
        loadDependencies();
    }

    if (
        document.readyState ===
        'loading'
    ) {

        document.addEventListener(
            'DOMContentLoaded',
            start,
            { once: true }
        );

    } else {

        start();

    }

})();
</script>

@endpush

@endsection