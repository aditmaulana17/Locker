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

    if ($dariTanggal && $sampaiTanggal) {
        try {
            $dateRangeValue =
                \Carbon\Carbon::parse($dariTanggal)->format('d/m/Y')
                . ' - ' .
                \Carbon\Carbon::parse($sampaiTanggal)->format('d/m/Y');
        } catch (\Throwable) {
            $dateRangeValue = '';
        }
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
        $tanggal = data_get($disposisi, 'tanggal_disposisi')
            ?? data_get($disposisi, 'created_at');

        if (!$tanggal) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($tanggal)->format('d/m/Y');
        } catch (\Throwable) {
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
        } catch (\Throwable) {
            return '-';
        }
    };
@endphp

@push('styles')
<style>
    .archive-date-picker,
    .archive-status-dropdown {
        position:relative;
    }

    .archive-date-panel {
        position:absolute;
        top:calc(100% + 8px);
        left:0;
        z-index:9999;
        width:340px;
        overflow:hidden;
        border:1px solid #e2e8f0;
        border-radius:18px;
        background:#fff;
        box-shadow:0 20px 50px rgba(15,23,42,.14),0 8px 22px rgba(15,23,42,.07);
    }

    .archive-status-panel {
        position:absolute;
        top:calc(100% + 8px);
        right:0;
        z-index:9999;
        width:360px;
        overflow:hidden;
        border:1px solid #e2e8f0;
        border-radius:18px;
        background:#fff;
        box-shadow:0 20px 50px rgba(15,23,42,.14),0 8px 22px rgba(15,23,42,.07);
    }

    .archive-date-header {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:8px;
        padding:12px;
        border-bottom:1px solid #e2e8f0;
        background:#f8fafc;
    }

    .archive-date-nav {
        width:34px;
        height:34px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        flex:none;
        border:1px solid #e2e8f0;
        border-radius:10px;
        background:#fff;
        color:#64748b;
        cursor:pointer;
        transition:.15s;
    }

    .archive-date-nav:hover {
        border-color:#bfdbfe;
        background:#eff6ff;
        color:#2563eb;
    }

    .archive-date-title {
        display:flex;
        align-items:center;
        justify-content:center;
        gap:4px;
        min-width:0;
        flex:1;
    }

    .archive-date-title button {
        min-width:0;
        border:0;
        border-radius:10px;
        background:transparent;
        padding:7px 9px;
        color:#334155;
        font-size:13px;
        font-weight:800;
        cursor:pointer;
        transition:.15s;
    }

    .archive-date-title button:hover {
        background:#eff6ff;
        color:#2563eb;
    }

    .archive-date-content {
        padding:12px;
    }

    .archive-calendar-grid {
        display:grid;
        grid-template-columns:repeat(7,minmax(0,1fr));
        gap:3px;
    }

    .archive-calendar-weekday {
        display:flex;
        align-items:center;
        justify-content:center;
        height:28px;
        color:#94a3b8;
        font-size:9px;
        font-weight:800;
        text-transform:uppercase;
    }

    .archive-calendar-day {
        height:37px;
        display:flex;
        align-items:center;
        justify-content:center;
        border:0;
        border-radius:10px;
        background:transparent;
        color:#475569;
        font-size:11px;
        font-weight:600;
        cursor:pointer;
        transition:.15s;
    }

    .archive-calendar-day:hover {
        background:#eff6ff;
        color:#2563eb;
    }

    .archive-calendar-day.other-month {
        color:#cbd5e1;
    }

    .archive-calendar-day.today {
        box-shadow:inset 0 0 0 1px #bfdbfe;
        color:#2563eb;
    }

    .archive-calendar-day.in-range {
        border-radius:8px;
        background:#eff6ff;
        color:#2563eb;
    }

    .archive-calendar-day.start-date {
        border-radius:999px 8px 8px 999px;
        background:#2563eb;
        color:#fff;
    }

    .archive-calendar-day.end-date {
        border-radius:8px 999px 999px 8px;
        background:#2563eb;
        color:#fff;
    }

    .archive-calendar-day.start-date.end-date {
        border-radius:999px;
    }

    .archive-calendar-day.start-date:hover,
    .archive-calendar-day.end-date:hover {
        background:#1d4ed8;
        color:#fff;
    }

    .archive-date-footer {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:8px;
        padding:10px 12px 12px;
        border-top:1px solid #e2e8f0;
    }

    .archive-date-footer-info {
        min-width:0;
        color:#64748b;
        font-size:10px;
        font-weight:600;
    }

    .archive-date-footer-actions {
        display:flex;
        align-items:center;
        gap:6px;
        flex:none;
    }

    .archive-date-btn {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:34px;
        padding:0 12px;
        border-radius:10px;
        font-size:10px;
        font-weight:800;
        cursor:pointer;
        transition:.15s;
    }

    .archive-date-btn.secondary {
        border:1px solid #e2e8f0;
        background:#f8fafc;
        color:#475569;
    }

    .archive-date-btn.secondary:hover {
        background:#f1f5f9;
        color:#334155;
    }

    .archive-date-btn.primary {
        border:1px solid #2563eb;
        background:#2563eb;
        color:#fff;
    }

    .archive-date-btn.primary:hover {
        background:#1d4ed8;
    }

    .archive-month-grid {
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:8px;
    }

    .archive-month-item,
    .archive-year-item {
        display:flex;
        align-items:center;
        justify-content:center;
        min-height:48px;
        border:1px solid #e2e8f0;
        border-radius:12px;
        background:#fff;
        color:#475569;
        font-size:11px;
        font-weight:700;
        cursor:pointer;
        transition:.15s;
    }

    .archive-month-item:hover,
    .archive-year-item:hover {
        border-color:#bfdbfe;
        background:#eff6ff;
        color:#2563eb;
    }

    .archive-month-item.active,
    .archive-year-item.active {
        border-color:#2563eb;
        background:#2563eb;
        color:#fff;
    }

    .archive-year-toolbar {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:8px;
        margin-bottom:10px;
    }

    .archive-year-range {
        color:#475569;
        font-size:11px;
        font-weight:800;
    }

    .archive-year-grid {
        display:grid;
        grid-template-columns:repeat(3,minmax(0,1fr));
        gap:8px;
    }

    .archive-status-head {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        padding:12px 14px;
        border-bottom:1px solid #e2e8f0;
        background:#f8fafc;
    }

    .archive-status-head-title {
        min-width:0;
    }

    .archive-status-head-title strong {
        display:block;
        color:#334155;
        font-size:12px;
        font-weight:800;
    }

    .archive-status-head-title span {
        display:block;
        margin-top:2px;
        color:#94a3b8;
        font-size:10px;
    }

    .archive-status-count {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:26px;
        padding:0 9px;
        border:1px solid #fde68a;
        border-radius:999px;
        background:#fffbeb;
        color:#d97706;
        font-size:10px;
        font-weight:800;
        white-space:nowrap;
    }

    .archive-status-actions {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:8px;
        padding:10px 14px;
        border-bottom:1px solid #f1f5f9;
    }

    .archive-status-action {
        border:0;
        background:transparent;
        padding:0;
        color:#2563eb;
        font-size:10px;
        font-weight:800;
        cursor:pointer;
    }

    .archive-status-action.muted {
        color:#64748b;
    }

    .archive-status-action:hover {
        color:#1d4ed8;
    }

    .archive-status-options {
        max-height:230px;
        overflow:auto;
        padding:10px 14px 12px;
    }

    .archive-status-item {
        display:flex;
        align-items:center;
        gap:10px;
        min-height:46px;
        margin-bottom:7px;
        padding:9px 11px;
        border:1px solid #e2e8f0;
        border-radius:12px;
        background:#fff;
        cursor:pointer;
        transition:.15s;
    }

    .archive-status-item:last-child {
        margin-bottom:0;
    }

    .archive-status-item:hover {
        border-color:#bfdbfe;
        background:#eff6ff;
    }

    .archive-status-item input {
        width:16px;
        height:16px;
        flex:none;
        accent-color:#2563eb;
    }

    .archive-status-item-label {
        min-width:0;
        flex:1;
        color:#475569;
        font-size:11px;
        font-weight:700;
    }

    .archive-status-dot {
        width:8px;
        height:8px;
        flex:none;
        border-radius:999px;
    }

    .archive-status-dot.menunggu {
        background:#f59e0b;
    }

    .archive-status-dot.diproses {
        background:#3b82f6;
    }

    .archive-status-dot.selesai {
        background:#10b981;
    }

    .archive-status-footer {
        display:flex;
        align-items:center;
        justify-content:flex-end;
        padding:10px 14px;
        border-top:1px solid #e2e8f0;
        background:#f8fafc;
    }

    .archive-status-close {
        min-height:34px;
        padding:0 13px;
        border:1px solid #e2e8f0;
        border-radius:10px;
        background:#fff;
        color:#475569;
        font-size:10px;
        font-weight:800;
        cursor:pointer;
        transition:.15s;
    }

    .archive-status-close:hover {
        background:#f1f5f9;
        color:#334155;
    }

    @media(max-width:767px){
        .archive-date-panel,
        .archive-status-panel {
            position:fixed;
            top:50%;
            left:50%;
            right:auto;
            width:calc(100vw - 24px);
            max-width:390px;
            transform:translate(-50%,-50%);
            max-height:calc(100vh - 30px);
        }

        .archive-date-content {
            max-height:56vh;
            overflow:auto;
        }

        .archive-status-options {
            max-height:38vh;
        }
    }
</style>
@endpush

<div class="space-y-4 sm:space-y-6">
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
                <a href="{{ route('export.disposisi.excel', request()->query()) }}"
                   class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-2.5 py-2 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-100 sm:px-3.5">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Excel</span>
                </a>
            @else
                <div></div>
            @endif

            @if(Route::has('export.disposisi.pdf'))
                <a href="{{ route('export.disposisi.pdf', request()->query()) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-2.5 py-2 text-xs font-semibold text-rose-700 shadow-sm transition hover:bg-rose-100 sm:px-3.5">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>PDF</span>
                </a>
            @else
                <div></div>
            @endif

            @if(in_array($userRole, ['admin', 'pimpinan'], true) && Route::has('surat-masuk.index'))
                <a href="{{ route('surat-masuk.index') }}"
                   title="Pilih surat masuk untuk membuat disposisi"
                   class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-2.5 py-2 text-xs font-semibold text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 sm:px-4">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Disposisi</span>
                </a>
            @else
                <div></div>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm sm:p-5">
        <form id="filterForm"
              method="GET"
              action="{{ route('disposisi.index') }}"
              class="space-y-3">

            <div class="grid grid-cols-1 gap-2.5 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 21l-6-6m2-5a7 7 0 11-14 0a7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input id="search"
                               name="search"
                               type="text"
                               value="{{ request('search') }}"
                               placeholder="Cari nomor surat, penerima atau isi instruksi..."
                               autocomplete="off"
                               class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm">
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <div class="archive-date-picker">
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3 text-slate-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5v12a2 2 0 002 2z"/>
                                </svg>
                            </div>

                            <input id="date-range"
                                   type="text"
                                   value="{{ $dateRangeValue }}"
                                   readonly
                                   autocomplete="off"
                                   placeholder="Pilih rentang tanggal..."
                                   class="h-11 w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-10 text-xs font-medium text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm">

                            <button type="button"
                                    id="clearDateRange"
                                    title="Hapus tanggal"
                                    class="absolute inset-y-0 right-0 z-10 hidden w-10 items-center justify-center text-slate-400 transition hover:text-rose-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <input type="hidden" name="dari_tanggal" id="dari_tanggal" value="{{ $dariTanggal }}">
                        <input type="hidden" name="sampai_tanggal" id="sampai_tanggal" value="{{ $sampaiTanggal }}">

                        <div id="datePickerPanel" class="archive-date-panel hidden">
                            <div class="archive-date-header">
                                <button type="button" id="datePrev" class="archive-date-nav" aria-label="Sebelumnya">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15 19l-7-7 7-7"/>
                                    </svg>
                                </button>

                                <div class="archive-date-title">
                                    <button type="button" id="monthButton"></button>
                                    <button type="button" id="yearButton"></button>
                                </div>

                                <button type="button" id="dateNext" class="archive-date-nav" aria-label="Berikutnya">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>

                            <div id="datePickerContent" class="archive-date-content"></div>

                            <div class="archive-date-footer">
                                <div id="datePickerInfo" class="archive-date-footer-info">
                                    Pilih tanggal mulai
                                </div>

                                <div class="archive-date-footer-actions">
                                    <button type="button"
                                            id="clearPickerButton"
                                            class="archive-date-btn secondary">
                                        Bersihkan
                                    </button>
                                    <button type="button"
                                            id="applyPickerButton"
                                            class="archive-date-btn primary">
                                        Terapkan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <div class="flex h-11 gap-2">
                        <button type="submit"
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-900 px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:text-sm">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707L13 17v4l-4-4v-4.293a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span>Filter</span>
                        </button>

                        @if($hasFilters)
                            <a href="{{ route('disposisi.index') }}"
                               title="Reset Filter"
                               class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200 hover:text-slate-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="archive-status-dropdown">
                <button type="button"
                        id="statusDropdownButton"
                        class="flex w-full items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-left transition hover:border-slate-300 hover:bg-slate-50 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2l4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z"/>
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-700 sm:text-sm">
                                Status Disposisi
                            </div>
                            <div id="statusSummary"
                                 class="mt-0.5 truncate text-[10px] text-slate-400 sm:text-xs">
                                Semua status
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <span id="statusCount"
                              class="inline-flex min-h-7 items-center rounded-full border border-amber-100 bg-amber-50 px-2.5 text-[10px] font-bold text-amber-600">
                            {{ count($selectedStatus) }} dipilih
                        </span>

                        <svg id="statusDropdownIcon"
                             class="h-4 w-4 text-slate-400 transition-transform"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
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
                                <input type="checkbox"
                                       name="status[]"
                                       value="{{ $value }}"
                                       class="status-checkbox"
                                       @checked(in_array($value, $selectedStatus, true))>

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

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[950px] border-collapse whitespace-nowrap text-left text-xs sm:text-sm">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-[11px]">
                        <th class="px-4 py-3 sm:px-5 sm:py-4">No. Surat</th>
                        <th class="px-4 py-3 sm:px-5 sm:py-4">Tanggal Disposisi</th>
                        <th class="px-4 py-3 sm:px-5 sm:py-4">Tujuan / Penerima</th>
                        <th class="px-4 py-3 sm:px-5 sm:py-4">Isi Instruksi</th>
                        <th class="px-4 py-3 sm:px-5 sm:py-4">Status</th>
                        <th class="px-4 py-3 sm:px-5 sm:py-4">Batas Waktu</th>
                        <th class="px-4 py-3 text-center sm:px-5 sm:py-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-xs">
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
                                    $tanggalBatas =
                                        data_get($d, 'batas_waktu')
                                        ?? data_get($d, 'tanggal_batas');

                                    $isLate =
                                        \Carbon\Carbon::parse($tanggalBatas)->isPast()
                                        && $status !== 'selesai';
                                } catch (\Throwable) {
                                    $isLate = false;
                                }
                            }
                        @endphp

                        <tr class="transition duration-150 hover:bg-slate-50/60">
                            <td class="px-4 py-3.5 text-slate-700 sm:px-5 sm:py-4">
                                <span class="inline-block max-w-[190px] truncate font-medium"
                                      title="{{ $nomorSurat }}">
                                    {{ $nomorSurat }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 text-slate-500 sm:px-5 sm:py-4">
                                {{ $tanggalDisposisi }}
                            </td>

                            <td class="px-4 py-3.5 sm:px-5 sm:py-4">
                                @if($penerimaData['nama'] !== '-')
                                    <div class="flex min-w-[150px] max-w-[200px] flex-col rounded-lg border border-slate-200/60 bg-slate-100 px-2.5 py-1.5"
                                         title="{{ $penerimaData['nama'] }}{{ $penerimaData['jabatan'] ? ' - '.$penerimaData['jabatan'] : '' }}">
                                        <span class="truncate font-semibold text-slate-700">
                                            {{ $penerimaData['nama'] }}
                                        </span>

                                        @if($penerimaData['jabatan'])
                                            <span class="truncate text-[10px] text-slate-400">
                                                {{ $penerimaData['jabatan'] }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <td class="max-w-[260px] truncate px-4 py-3.5 font-medium text-slate-700 sm:px-5 sm:py-4"
                                title="{{ $instruksi }}">
                                {{ $instruksi }}
                            </td>

                            <td class="px-4 py-3.5 sm:px-5 sm:py-4">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $badgeClass }}">
                                    {{ $statusOptions[$status] ?? ucfirst($status) }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 text-slate-500 sm:px-5 sm:py-4">
                                @if($batasWaktu !== '-')
                                    <span class="{{ $isLate ? 'font-semibold text-rose-600' : '' }}">
                                        {{ $batasWaktu }}
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 text-center sm:px-5 sm:py-4">
                                <div class="inline-flex items-center gap-1">
                                    @if(Route::has('disposisi.show'))
                                        <a href="{{ route('disposisi.show', $d) }}"
                                           title="Lihat Detail"
                                           class="rounded-lg p-1.5 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 12a3 3 0 11-6 0a3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7c-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(in_array($userRole, ['admin', 'pimpinan'], true) && Route::has('disposisi.edit'))
                                        <a href="{{ route('disposisi.edit', $d) }}"
                                           title="Ubah Disposisi"
                                           class="rounded-lg p-1.5 text-slate-400 transition hover:bg-amber-50 hover:text-amber-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                    @endif

                                    @if(in_array($userRole, ['admin', 'pimpinan'], true) && Route::has('disposisi.destroy'))
                                        <form action="{{ route('disposisi.destroy', $d) }}"
                                              method="POST"
                                              class="delete-form inline">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button"
                                                    title="Hapus Disposisi"
                                                    class="delete-btn rounded-lg p-1.5 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 01-1-1h-4a1 1 0 01-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center sm:py-12">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-1.414 0l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>

                                    <p class="text-sm font-semibold text-slate-700 sm:text-base">
                                        Belum ada data disposisi
                                    </p>

                                    <p class="mt-0.5 max-w-md px-4 text-[11px] text-slate-400 sm:text-xs">
                                        Coba sesuaikan pencarian atau filter yang digunakan.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($disposisis) && method_exists($disposisis, 'hasPages') && $disposisis->hasPages())
            <div class="border-t border-slate-100 px-4 py-3 sm:px-6 sm:py-4">
                {{ $disposisis->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function(){
    'use strict';

    const months = [
        'Januari','Februari','Maret','April','Mei','Juni',
        'Juli','Agustus','September','Oktober','November','Desember'
    ];

    const shortMonths = [
        'Jan','Feb','Mar','Apr','Mei','Jun',
        'Jul','Agu','Sep','Okt','Nov','Des'
    ];

    const weekdays = ['Sen','Sel','Rab','Kam','Jum','Sab','Min'];

    const dateInput = document.getElementById('date-range');
    const dariInput = document.getElementById('dari_tanggal');
    const sampaiInput = document.getElementById('sampai_tanggal');
    const clearDateRange = document.getElementById('clearDateRange');
    const datePanel = document.getElementById('datePickerPanel');
    const dateContent = document.getElementById('datePickerContent');
    const dateInfo = document.getElementById('datePickerInfo');
    const monthButton = document.getElementById('monthButton');
    const yearButton = document.getElementById('yearButton');
    const datePrev = document.getElementById('datePrev');
    const dateNext = document.getElementById('dateNext');
    const applyPickerButton = document.getElementById('applyPickerButton');
    const clearPickerButton = document.getElementById('clearPickerButton');

    const statusDropdownButton = document.getElementById('statusDropdownButton');
    const statusDropdownPanel = document.getElementById('statusDropdownPanel');
    const statusDropdownIcon = document.getElementById('statusDropdownIcon');
    const statusCheckboxes = Array.from(document.querySelectorAll('.status-checkbox'));
    const statusCount = document.getElementById('statusCount');
    const statusPanelCount = document.getElementById('statusPanelCount');
    const statusSummary = document.getElementById('statusSummary');
    const selectAllStatus = document.getElementById('selectAllStatus');
    const clearAllStatus = document.getElementById('clearAllStatus');
    const closeStatusDropdown = document.getElementById('closeStatusDropdown');

    const initialStart = dariInput?.value || '';
    const initialEnd = sampaiInput?.value || '';

    let viewDate = parseDate(initialStart) || parseDate(initialEnd) || new Date();
    let tempStart = parseDate(initialStart);
    let tempEnd = parseDate(initialEnd);
    let pickerMode = 'calendar';
    let yearPageStart = Math.floor((viewDate.getFullYear() - 2000) / 12) * 12 + 2000;

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function dateKey(date) {
        if (!date) return '';
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
    }

    function parseDate(value) {
        if (!value) return null;

        const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})$/);

        if (!match) return null;

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

        date.setHours(0, 0, 0, 0);
        return date;
    }

    function cloneDate(date) {
        return date ? new Date(date.getTime()) : null;
    }

    function formatDisplay(date) {
        if (!date) return '';
        return pad(date.getDate()) + '/' + pad(date.getMonth() + 1) + '/' + date.getFullYear();
    }

    function formatRange(start, end) {
        if (!start || !end) return '';
        return formatDisplay(start) + ' - ' + formatDisplay(end);
    }

    function isSameDay(a, b) {
        return !!a && !!b && dateKey(a) === dateKey(b);
    }

    function isBefore(a, b) {
        return dateKey(a) < dateKey(b);
    }

    function isAfter(a, b) {
        return dateKey(a) > dateKey(b);
    }

    function getToday() {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return today;
    }

    function formatInfo() {
        if (tempStart && tempEnd) {
            dateInfo.textContent = formatRange(tempStart, tempEnd);
            return;
        }

        if (tempStart) {
            dateInfo.textContent = formatDisplay(tempStart) + ' - pilih tanggal akhir';
            return;
        }

        dateInfo.textContent = 'Pilih tanggal mulai';
    }

    function updateVisibleInput() {
        const start = parseDate(dariInput.value);
        const end = parseDate(sampaiInput.value);

        if (start && end) {
            dateInput.value = formatRange(start, end);
            clearDateRange.style.display = 'flex';
        } else {
            dateInput.value = '';
            clearDateRange.style.display = 'none';
        }
    }

    function renderCalendar() {
        const year = viewDate.getFullYear();
        const month = viewDate.getMonth();

        monthButton.textContent = months[month];
        yearButton.textContent = year;

        let html = '<div class="archive-calendar-grid">';

        weekdays.forEach(function(day){
            html += '<div class="archive-calendar-weekday">' + day + '</div>';
        });

        const firstDay = new Date(year, month, 1);
        const firstWeekday = (firstDay.getDay() + 6) % 7;
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const daysInPrevMonth = new Date(year, month, 0).getDate();

        for (let i = firstWeekday - 1; i >= 0; i--) {
            const day = daysInPrevMonth - i;
            const date = new Date(year, month - 1, day);
            html += buildDayButton(date, true);
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            html += buildDayButton(date, false);
        }

        const totalCells = firstWeekday + daysInMonth;
        const nextDays = (7 - (totalCells % 7)) % 7;

        for (let day = 1; day <= nextDays; day++) {
            const date = new Date(year, month + 1, day);
            html += buildDayButton(date, true);
        }

        html += '</div>';

        dateContent.innerHTML = html;

        dateContent.querySelectorAll('[data-date]').forEach(function(button){
            button.addEventListener('click', function(){
                selectDate(parseDate(this.dataset.date));
            });
        });
    }

    function buildDayButton(date, otherMonth) {
        const today = getToday();
        const classes = ['archive-calendar-day'];

        const inRange =
            tempStart &&
            tempEnd &&
            !isBefore(date, tempStart) &&
            !isAfter(date, tempEnd);

        if (otherMonth) classes.push('other-month');
        if (isSameDay(date, today)) classes.push('today');
        if (inRange) classes.push('in-range');
        if (isSameDay(date, tempStart)) classes.push('start-date');
        if (isSameDay(date, tempEnd)) classes.push('end-date');

        return (
            '<button type="button" class="' + classes.join(' ') + '" data-date="' +
            dateKey(date) + '">' +
            date.getDate() +
            '</button>'
        );
    }

    function selectDate(date) {
        if (!date) return;

        if (!tempStart || (tempStart && tempEnd)) {
            tempStart = cloneDate(date);
            tempEnd = null;
        } else if (isBefore(date, tempStart)) {
            tempEnd = cloneDate(tempStart);
            tempStart = cloneDate(date);
        } else {
            tempEnd = cloneDate(date);
        }

        viewDate = cloneDate(date);
        pickerMode = 'calendar';
        renderPicker();
        formatInfo();
    }

    function renderMonthPanel() {
        monthButton.textContent = months[viewDate.getMonth()];
        yearButton.textContent = viewDate.getFullYear();

        let html = '<div class="archive-month-grid">';

        months.forEach(function(month, index){
            const active = index === viewDate.getMonth() ? ' active' : '';

            html +=
                '<button type="button" class="archive-month-item' + active + '" data-month="' +
                index + '">' +
                month +
                '</button>';
        });

        html += '</div>';

        dateContent.innerHTML = html;

        dateContent.querySelectorAll('[data-month]').forEach(function(button){
            button.addEventListener('click', function(){
                viewDate.setMonth(Number(this.dataset.month));
                pickerMode = 'calendar';
                renderPicker();
            });
        });
    }

    function renderYearPanel() {
        const start = yearPageStart;
        const end = start + 11;

        monthButton.textContent = months[viewDate.getMonth()];
        yearButton.textContent = viewDate.getFullYear();

        let html =
            '<div class="archive-year-toolbar">' +
                '<button type="button" class="archive-date-nav" id="yearPrev">' +
                    '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>' +
                    '</svg>' +
                '</button>' +
                '<div class="archive-year-range">' + start + ' - ' + end + '</div>' +
                '<button type="button" class="archive-date-nav" id="yearNext">' +
                    '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>' +
                    '</svg>' +
                '</button>' +
            '</div>' +
            '<div class="archive-year-grid">';

        for (let year = start; year <= end; year++) {
            const active = year === viewDate.getFullYear() ? ' active' : '';

            html +=
                '<button type="button" class="archive-year-item' + active + '" data-year="' +
                year + '">' +
                year +
                '</button>';
        }

        html += '</div>';

        dateContent.innerHTML = html;

        document.getElementById('yearPrev')?.addEventListener('click', function(){
            yearPageStart -= 12;
            renderYearPanel();
        });

        document.getElementById('yearNext')?.addEventListener('click', function(){
            yearPageStart += 12;
            renderYearPanel();
        });

        dateContent.querySelectorAll('[data-year]').forEach(function(button){
            button.addEventListener('click', function(){
                viewDate.setFullYear(Number(this.dataset.year));
                pickerMode = 'calendar';
                renderPicker();
            });
        });
    }

    function renderPicker() {
        datePrev.style.visibility = pickerMode === 'calendar' ? 'visible' : 'hidden';
        dateNext.style.visibility = pickerMode === 'calendar' ? 'visible' : 'hidden';

        if (pickerMode === 'calendar') {
            renderCalendar();
        } else if (pickerMode === 'month') {
            renderMonthPanel();
        } else {
            renderYearPanel();
        }

        formatInfo();
    }

    function openDatePicker() {
        closeStatusDropdownPanel();

        if (datePanel.classList.contains('hidden')) {
            datePanel.classList.remove('hidden');

            tempStart = parseDate(dariInput.value);
            tempEnd = parseDate(sampaiInput.value);

            viewDate =
                cloneDate(tempStart) ||
                cloneDate(tempEnd) ||
                new Date();

            yearPageStart =
                Math.floor((viewDate.getFullYear() - 2000) / 12) * 12 + 2000;

            pickerMode = 'calendar';
            renderPicker();
        } else {
            datePanel.classList.add('hidden');
        }
    }

    function closeDatePicker() {
        datePanel.classList.add('hidden');
        pickerMode = 'calendar';
    }

    function applyDateRangeValue() {
        if (!tempStart || !tempEnd) {
            return;
        }

        dariInput.value = dateKey(tempStart);
        sampaiInput.value = dateKey(tempEnd);

        updateVisibleInput();
        closeDatePicker();
    }

    function clearDateValue() {
        tempStart = null;
        tempEnd = null;
        dariInput.value = '';
        sampaiInput.value = '';

        dateInput.value = '';
        clearDateRange.style.display = 'none';

        viewDate = new Date();
        pickerMode = 'calendar';
        renderPicker();
        formatInfo();
    }

    dateInput?.addEventListener('click', openDatePicker);

    clearDateRange?.addEventListener('click', function(event){
        event.preventDefault();
        event.stopPropagation();
        clearDateValue();
    });

    monthButton?.addEventListener('click', function(event){
        event.stopPropagation();
        pickerMode = pickerMode === 'month' ? 'calendar' : 'month';
        renderPicker();
    });

    yearButton?.addEventListener('click', function(event){
        event.stopPropagation();

        if (pickerMode === 'year') {
            pickerMode = 'calendar';
        } else {
            yearPageStart =
                Math.floor((viewDate.getFullYear() - 2000) / 12) * 12 + 2000;
            pickerMode = 'year';
        }

        renderPicker();
    });

    datePrev?.addEventListener('click', function(){
        if (pickerMode !== 'calendar') return;
        viewDate.setMonth(viewDate.getMonth() - 1);
        renderPicker();
    });

    dateNext?.addEventListener('click', function(){
        if (pickerMode !== 'calendar') return;
        viewDate.setMonth(viewDate.getMonth() + 1);
        renderPicker();
    });

    applyPickerButton?.addEventListener('click', function(){
        applyDateRangeValue();
    });

    clearPickerButton?.addEventListener('click', function(){
        clearDateValue();
    });

    updateVisibleInput();

    function openStatusDropdownPanel() {
        closeDatePicker();
        statusDropdownPanel.classList.remove('hidden');
        statusDropdownIcon.classList.add('rotate-180');
        updateStatusSummary();
    }

    function closeStatusDropdownPanel() {
        statusDropdownPanel.classList.add('hidden');
        statusDropdownIcon.classList.remove('rotate-180');
    }

    function updateStatusSummary() {
        const checked = statusCheckboxes
            .filter(function(checkbox){
                return checkbox.checked;
            })
            .map(function(checkbox){
                return checkbox.parentElement
                    .querySelector('.archive-status-item-label')
                    ?.textContent
                    ?.trim() || '';
            })
            .filter(Boolean);

        const count = checked.length;

        statusCount.textContent = count + ' dipilih';
        statusPanelCount.textContent = count + ' dipilih';

        if (!count) {
            statusSummary.textContent = 'Semua status';
            return;
        }

        if (count === 1) {
            statusSummary.textContent = checked[0];
            return;
        }

        statusSummary.textContent =
            checked.slice(0, 2).join(', ') +
            (count > 2 ? ' +' + (count - 2) : '');
    }

    statusDropdownButton?.addEventListener('click', function(event){
        event.stopPropagation();

        if (statusDropdownPanel.classList.contains('hidden')) {
            openStatusDropdownPanel();
        } else {
            closeStatusDropdownPanel();
        }
    });

    statusCheckboxes.forEach(function(checkbox){
        checkbox.addEventListener('change', updateStatusSummary);
    });

    selectAllStatus?.addEventListener('click', function(){
        statusCheckboxes.forEach(function(checkbox){
            checkbox.checked = true;
        });

        updateStatusSummary();
    });

    clearAllStatus?.addEventListener('click', function(){
        statusCheckboxes.forEach(function(checkbox){
            checkbox.checked = false;
        });

        updateStatusSummary();
    });

    closeStatusDropdown?.addEventListener('click', function(){
        closeStatusDropdownPanel();
    });

    document.addEventListener('click', function(event){
        if (!event.target.closest('.archive-date-picker')) {
            closeDatePicker();
        }

        if (!event.target.closest('.archive-status-dropdown')) {
            closeStatusDropdownPanel();
        }
    });

    function initializeDeleteConfirmation() {
        const buttons = document.querySelectorAll('.delete-btn');

        buttons.forEach(function(button){
            button.addEventListener('click', function(){
                const form = this.closest('.delete-form');

                if (!form) return;

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Hapus Disposisi?',
                        text: 'Data disposisi akan dipindahkan ke tempat sampah.',
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
                    }).then(function(result){
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                } else if (confirm('Yakin ingin menghapus disposisi ini?')) {
                    form.submit();
                }
            });
        });
    }

    document.getElementById('filterForm')?.addEventListener('submit', function(event){
        const start = parseDate(dariInput.value);
        const end = parseDate(sampaiInput.value);

        if (!start && !end) {
            return;
        }

        if (!start || !end || isAfter(start, end)) {
            event.preventDefault();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Rentang tanggal tidak valid',
                    text: 'Silakan pilih tanggal mulai dan tanggal akhir yang benar.',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#2563eb',
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton: 'rounded-xl text-xs font-semibold px-4 py-2.5'
                    }
                });
            } else {
                alert('Silakan pilih tanggal mulai dan tanggal akhir yang benar.');
            }
        }
    });

    updateStatusSummary();
    initializeDeleteConfirmation();
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection