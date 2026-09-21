@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | USER & ROLE
    |--------------------------------------------------------------------------
    */

    $user = auth()->user();

    $role = strtolower(
        trim(
            (string) (
                $user->role
                ?? $user->jabatan
                ?? ''
            )
        )
    );

    /*
     * Normalisasi:
     * staff -> staf
     */
    $role = $role === 'staff'
        ? 'staf'
        : $role;

    $isStaf = $role === 'staf';
    $isAdmin = $role === 'admin';
    $isPimpinan = $role === 'pimpinan';

    $roleLabel = match ($role) {
        'admin' => 'Admin',
        'pimpinan' => 'Pimpinan',
        'staf' => 'Staf',
        default => 'Pengguna',
    };

    $today = now()->translatedFormat('l, d F Y');


    /*
    |--------------------------------------------------------------------------
    | STATUS SURAT MASUK
    |--------------------------------------------------------------------------
    */

    $statusConfig = [
        'baru' => [
            'label' => 'Baru',
            'class' => 'bg-blue-50 text-blue-700 border-blue-200',
            'dot' => 'bg-blue-500',
        ],

        'proses' => [
            'label' => 'Diproses',
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dot' => 'bg-amber-500',
        ],

        'diproses' => [
            'label' => 'Diproses',
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dot' => 'bg-amber-500',
        ],

        'didisposisikan' => [
            'label' => 'Didisposisikan',
            'class' => 'bg-purple-50 text-purple-700 border-purple-200',
            'dot' => 'bg-purple-500',
        ],

        'selesai' => [
            'label' => 'Selesai',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot' => 'bg-emerald-500',
        ],

        'diarsipkan' => [
            'label' => 'Diarsipkan',
            'class' => 'bg-slate-100 text-slate-700 border-slate-200',
            'dot' => 'bg-slate-500',
        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | STATUS SURAT KELUAR
    |--------------------------------------------------------------------------
    */

    $statusKeluarConfig = [
        'draft' => [
            'label' => 'Draft',
            'class' => 'bg-slate-50 text-slate-700 border-slate-200',
            'dot' => 'bg-slate-500',
            'icon' => 'bg-slate-50 text-slate-600 ring-slate-100',
        ],

        'proses' => [
            'label' => 'Diproses',
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dot' => 'bg-amber-500',
            'icon' => 'bg-amber-50 text-amber-600 ring-amber-100',
        ],

        'diproses' => [
            'label' => 'Diproses',
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
            'dot' => 'bg-amber-500',
            'icon' => 'bg-amber-50 text-amber-600 ring-amber-100',
        ],

        'disetujui' => [
            'label' => 'Disetujui',
            'class' => 'bg-blue-50 text-blue-700 border-blue-200',
            'dot' => 'bg-blue-500',
            'icon' => 'bg-blue-50 text-blue-600 ring-blue-100',
        ],

        'dikirim' => [
            'label' => 'Dikirim',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'dot' => 'bg-emerald-500',
            'icon' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        ],

        'diarsipkan' => [
            'label' => 'Diarsipkan',
            'class' => 'bg-purple-50 text-purple-700 border-purple-200',
            'dot' => 'bg-purple-500',
            'icon' => 'bg-purple-50 text-purple-600 ring-purple-100',
        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | STATUS DISPOSISI
    |--------------------------------------------------------------------------
    */

    $disposisiConfig = [
        'menunggu' => [
            'label' => 'Menunggu',
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
            'border' => 'border-l-amber-500',
            'icon' => 'bg-amber-50 text-amber-600',
        ],

        'diproses' => [
            'label' => 'Diproses',
            'class' => 'bg-blue-50 text-blue-700 border-blue-200',
            'border' => 'border-l-blue-500',
            'icon' => 'bg-blue-50 text-blue-600',
        ],

        'selesai' => [
            'label' => 'Selesai',
            'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'border' => 'border-l-emerald-500',
            'icon' => 'bg-emerald-50 text-emerald-600',
        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | DATA RIWAYAT SURAT KELUAR
    |--------------------------------------------------------------------------
    |
    | Data dikirim dari DashboardController.
    |
    | TIDAK ADA QUERY ActivityLog di Blade.
    |
    */

    $riwayatSuratKeluar =
        $riwayatSuratKeluar
        ?? collect();

@endphp


<div class="dashboard-page space-y-6 sm:space-y-7 lg:space-y-8">


    {{-- =====================================================================
        HERO
    ====================================================================== --}}

    <section class="hero-dashboard relative overflow-hidden rounded-[28px] bg-slate-950 shadow-2xl">

        <div class="pointer-events-none absolute inset-0 overflow-hidden">

            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-blue-600/30 blur-3xl"></div>

            <div class="absolute -bottom-32 left-1/3 h-80 w-80 rounded-full bg-indigo-600/20 blur-3xl"></div>

            <div class="absolute right-1/4 top-1/2 h-40 w-40 rounded-full bg-cyan-400/10 blur-3xl"></div>

            <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 via-transparent to-indigo-600/20"></div>

        </div>


        <div class="relative z-10 p-5 sm:p-7 lg:p-9">

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_auto] lg:items-center">


                {{-- HERO CONTENT --}}

                <div class="min-w-0">

                    <div class="mb-4 flex flex-wrap items-center gap-2">

                        <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-blue-100 backdrop-blur-md sm:text-xs">

                            <span class="relative flex h-2 w-2">

                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>

                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>

                            </span>

                            Sistem Aktif

                        </span>


                        <span class="rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-[10px] font-semibold text-slate-300 sm:text-xs">

                            {{ $roleLabel }}

                        </span>

                    </div>


                    <h1 class="max-w-3xl text-2xl font-extrabold leading-tight tracking-tight text-white sm:text-3xl lg:text-4xl">

                        Selamat Datang,

                        <span class="text-blue-400">
                            {{ $user->name ?? 'Pengguna' }}
                        </span>

                    </h1>


                    <p class="mt-3 max-w-2xl text-xs leading-relaxed text-slate-300 sm:text-sm">

                        @if($isStaf)

                            Kelola dan tindak lanjuti disposisi surat yang diberikan kepada Anda dengan lebih cepat dan terstruktur.

                        @else

                            Pantau aktivitas arsip surat masuk, surat keluar, serta proses disposisi dalam satu dashboard terpusat.

                        @endif

                    </p>


                    <div class="mt-5 flex items-center gap-2 text-[11px] font-medium text-slate-400 sm:text-xs">

                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >
                            <rect
                                x="3"
                                y="4"
                                width="18"
                                height="17"
                                rx="2"
                            />

                            <path
                                stroke-linecap="round"
                                d="M16 2v4M8 2v4M3 10h18"
                            />
                        </svg>

                        {{ $today }}

                    </div>

                </div>


                {{-- HERO ACTION --}}

                <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">

                    @if(!$isStaf)

                        {{-- SURAT MASUK --}}

                        <a
                            href="{{ route('surat-masuk.create') }}"
                            class="dashboard-action group"
                        >

                            <span class="dashboard-action-icon bg-blue-50 text-blue-600">

                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 4v16M4 12h16"
                                    />
                                </svg>

                            </span>


                            <span class="text-left">

                                <span class="block text-xs font-bold text-slate-800">
                                    Surat Masuk
                                </span>

                                <span class="block text-[10px] text-slate-500">
                                    Tambah arsip baru
                                </span>

                            </span>


                            <svg
                                class="ml-auto h-4 w-4 text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-500"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>

                        </a>


                        {{-- SURAT KELUAR --}}

                        <a
                            href="{{ route('surat-keluar.create') }}"
                            class="dashboard-action group"
                        >

                            <span class="dashboard-action-icon bg-emerald-50 text-emerald-600">

                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5 12h11"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13 6l6 6-6 6"
                                    />
                                </svg>

                            </span>


                            <span class="text-left">

                                <span class="block text-xs font-bold text-slate-800">
                                    Surat Keluar
                                </span>

                                <span class="block text-[10px] text-slate-500">
                                    Tambah surat keluar
                                </span>

                            </span>


                            <svg
                                class="ml-auto h-4 w-4 text-slate-300 transition group-hover:translate-x-1 group-hover:text-emerald-500"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>

                        </a>

                    @else

                        {{-- DISPOSISI STAFF --}}

                        <a
                            href="{{ route('disposisi.index') }}"
                            class="dashboard-action group"
                        >

                            <span class="dashboard-action-icon bg-purple-50 text-purple-600">

                                <svg
                                    class="h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >
                                    <rect
                                        x="5"
                                        y="4"
                                        width="14"
                                        height="16"
                                        rx="2"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        d="M9 4.5V3h6v1.5M9 9h6M9 13h6M9 17h4"
                                    />
                                </svg>

                            </span>


                            <span class="text-left">

                                <span class="block text-xs font-bold text-slate-800">
                                    Disposisi Saya
                                </span>

                                <span class="block text-[10px] text-slate-500">
                                    Lihat tugas yang diberikan
                                </span>

                            </span>


                            <svg
                                class="ml-auto h-4 w-4 text-slate-300 transition group-hover:translate-x-1 group-hover:text-purple-500"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 5l7 7-7 7"
                                />
                            </svg>

                        </a>

                    @endif

                </div>

            </div>

        </div>

    </section>


    {{-- =====================================================================
        ADMIN / PIMPINAN
    ====================================================================== --}}

    @if(!$isStaf)

        {{-- ================================================================
            STATISTIK
        ================================================================= --}}

        <section>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">


                {{-- SURAT MASUK --}}

                <a
                    href="{{ route('surat-masuk.index') }}"
                    class="stat-card stat-card-blue group"
                >

                    <div class="stat-color-bar bg-blue-500"></div>

                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <span class="stat-label">
                                Surat Masuk
                            </span>


                            <div class="mt-3 flex items-baseline gap-1">

                                <span class="stat-number">
                                    {{ $totalSuratMasuk ?? 0 }}
                                </span>


                                <span class="hidden text-[10px] font-semibold text-slate-400 sm:inline">
                                    surat
                                </span>

                            </div>

                        </div>


                        <div class="stat-icon stat-icon-blue">

                            <svg
                                class="h-5 w-5 sm:h-6 sm:w-6"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                viewBox="0 0 24 24"
                            >
                                <rect
                                    x="3"
                                    y="5"
                                    width="18"
                                    height="14"
                                    rx="2.5"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3.5 7.5l7.1 5.3a2.25 2.25 0 002.8 0l7.1-5.3"
                                />
                            </svg>

                        </div>

                    </div>


                    <div class="mt-4 flex items-center justify-between">

                        <span class="stat-pill bg-blue-50 text-blue-600">
                            Arsip Masuk
                        </span>


                        <span class="stat-arrow text-blue-500">
                            →
                        </span>

                    </div>

                </a>


                {{-- SURAT KELUAR --}}

                <a
                    href="{{ route('surat-keluar.index') }}"
                    class="stat-card stat-card-emerald group"
                >

                    <div class="stat-color-bar bg-emerald-500"></div>


                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <span class="stat-label">
                                Surat Keluar
                            </span>


                            <div class="mt-3 flex items-baseline gap-1">

                                <span class="stat-number">
                                    {{ $totalSuratKeluar ?? 0 }}
                                </span>


                                <span class="hidden text-[10px] font-semibold text-slate-400 sm:inline">
                                    surat
                                </span>

                            </div>

                        </div>


                        <div class="stat-icon stat-icon-emerald">

                            <svg
                                class="h-5 w-5 sm:h-6 sm:w-6"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12h11"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13 6l6 6-6 6"
                                />
                            </svg>

                        </div>

                    </div>


                    <div class="mt-4 flex items-center justify-between">

                        <span class="stat-pill bg-emerald-50 text-emerald-600">
                            Arsip Keluar
                        </span>


                        <span class="stat-arrow text-emerald-500">
                            →
                        </span>

                    </div>

                </a>


                {{-- BELUM DIPROSES --}}

                <a
                    href="{{ route('surat-masuk.index', ['status' => ['baru']]) }}"
                    class="stat-card stat-card-amber group"
                >

                    <div class="stat-color-bar bg-amber-500"></div>


                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <span class="stat-label">
                                Belum Diproses
                            </span>


                            <div class="mt-3 flex items-baseline gap-1">

                                <span class="stat-number">
                                    {{ $suratPending ?? 0 }}
                                </span>


                                <span class="hidden text-[10px] font-semibold text-slate-400 sm:inline">
                                    surat
                                </span>

                            </div>

                        </div>


                        <div class="stat-icon stat-icon-amber">

                            <svg
                                class="h-5 w-5 sm:h-6 sm:w-6"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="8.5"
                                />

                                <path
                                    stroke-linecap="round"
                                    d="M12 7.5v5l3 2"
                                />
                            </svg>

                        </div>

                    </div>


                    <div class="mt-4 flex items-center justify-between">

                        <span class="stat-pill bg-amber-50 text-amber-600">
                            Perlu Tindakan
                        </span>


                        <span class="stat-arrow text-amber-500">
                            →
                        </span>

                    </div>

                </a>


                {{-- SELESAI --}}

                <a
                    href="{{ route('surat-masuk.index', ['status' => ['selesai']]) }}"
                    class="stat-card stat-card-teal group"
                >

                    <div class="stat-color-bar bg-teal-500"></div>


                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <span class="stat-label">
                                Selesai
                            </span>


                            <div class="mt-3 flex items-baseline gap-1">

                                <span class="stat-number">
                                    {{ $suratSelesai ?? 0 }}
                                </span>


                                <span class="hidden text-[10px] font-semibold text-slate-400 sm:inline">
                                    surat
                                </span>

                            </div>

                        </div>


                        <div class="stat-icon stat-icon-teal">

                            <svg
                                class="h-5 w-5 sm:h-6 sm:w-6"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8 12.5l2.5 2.5L16.5 9"
                                />
                            </svg>

                        </div>

                    </div>


                    <div class="mt-4 flex items-center justify-between">

                        <span class="stat-pill bg-teal-50 text-teal-600">
                            Terselesaikan
                        </span>


                        <span class="stat-arrow text-teal-500">
                            →
                        </span>

                    </div>

                </a>

            </div>

        </section>


        {{-- ================================================================
            GRAFIK
        ================================================================= --}}

        <section class="dashboard-panel overflow-hidden">

            <div class="border-b border-slate-100 px-4 py-4 sm:px-6 sm:py-5">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div>

                        <div class="flex items-center gap-2">

                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">

                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M4 19V5M4 19h16"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M7 15l4-4 3 2 5-6"
                                    />
                                </svg>

                            </span>


                            <h2 class="text-sm font-extrabold text-slate-800 sm:text-base">
                                Aktivitas Surat
                            </h2>

                        </div>


                        <p class="mt-1 pl-10 text-[10px] text-slate-400 sm:text-xs">
                            Perbandingan surat masuk dan surat keluar selama 12 bulan terakhir.
                        </p>

                    </div>


                    <div class="flex items-center gap-4 pl-10 sm:pl-0">

                        <span class="flex items-center gap-2 text-[10px] font-semibold text-slate-500 sm:text-xs">

                            <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>

                            Masuk

                        </span>


                        <span class="flex items-center gap-2 text-[10px] font-semibold text-slate-500 sm:text-xs">

                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>

                            Keluar

                        </span>

                    </div>

                </div>

            </div>


            <div class="p-4 sm:p-6">

                <div class="relative h-64 w-full sm:h-72 lg:h-80">

                    <canvas id="suratChart"></canvas>

                </div>

            </div>

        </section>


        {{-- ================================================================
            SURAT MASUK TERBARU
        ================================================================= --}}

        <section class="dashboard-panel overflow-hidden">

            <div class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-br from-white via-white to-blue-50/40 px-4 py-5 sm:px-6 sm:py-6">

                <div class="pointer-events-none absolute -right-12 -top-16 h-40 w-40 rounded-full bg-blue-500/5 blur-3xl"></div>

                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex min-w-0 items-start gap-3">

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 shadow-sm ring-1 ring-blue-100">

                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                viewBox="0 0 24 24"
                            >
                                <rect
                                    x="3"
                                    y="5"
                                    width="18"
                                    height="14"
                                    rx="2.5"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3.5 7.5l7.1 5.3a2.25 2.25 0 002.8 0l7.1-5.3"
                                />
                            </svg>

                        </div>


                        <div class="min-w-0">

                            <div class="flex flex-wrap items-center gap-2">

                                <span class="section-eyebrow">
                                    Aktivitas Terbaru
                                </span>


                                @if(($suratMasukTerbaru ?? collect())->count() > 0)

                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[9px] font-bold text-blue-600 ring-1 ring-blue-100">

                                        <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>

                                        {{ ($suratMasukTerbaru ?? collect())->count() }} terbaru

                                    </span>

                                @endif

                            </div>


                            <h2 class="mt-1 text-base font-extrabold tracking-tight text-slate-800 sm:text-lg">
                                Surat Masuk Terbaru
                            </h2>


                            <p class="mt-1 max-w-xl text-[10px] leading-relaxed text-slate-400 sm:text-xs">
                                Pantau surat masuk terbaru yang saat ini tersimpan di arsip.
                            </p>

                        </div>

                    </div>


                    <a
                        href="{{ route('surat-masuk.index') }}"
                        class="group inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[10px] font-bold text-slate-600 shadow-sm transition-all duration-200 hover:border-blue-200 hover:bg-blue-600 hover:text-white sm:w-auto sm:text-xs"
                    >
                        Lihat Semua

                        <svg
                            class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>

                    </a>

                </div>

            </div>


            {{-- DESKTOP --}}

            <div class="hidden md:block">

                @forelse(($suratMasukTerbaru ?? []) as $sm)

                    @php

                        $currentStatus =
                            strtolower(
                                trim(
                                    (string) (
                                        $sm->status
                                        ?? 'baru'
                                    )
                                )
                            );


                        $currentStatusConfig =
                            $statusConfig[$currentStatus]
                            ?? [
                                'label' => ucfirst($currentStatus),
                                'class' => 'bg-slate-50 text-slate-600 border-slate-200',
                                'dot' => 'bg-slate-400',
                            ];


                        $nomorSurat =
                            trim(
                                (string) (
                                    $sm->nomor_surat
                                    ?? ''
                                )
                            );

                        if ($nomorSurat === '') {
                            $nomorSurat = '-';
                        }


                        $tanggalSurat = '-';

                        if ($sm->tanggal_surat) {

                            try {

                                $tanggalSurat =
                                    \Illuminate\Support\Carbon::parse(
                                        $sm->tanggal_surat
                                    )->translatedFormat('d M Y');

                            } catch (\Throwable $e) {

                                $tanggalSurat =
                                    (string) $sm->tanggal_surat;

                            }

                        }

                    @endphp


                    <div class="group relative border-b border-slate-100 px-4 py-4 last:border-b-0 sm:px-6">

                        <div class="absolute inset-y-0 left-0 w-1 origin-left scale-y-0 rounded-r-full bg-blue-500 transition-transform duration-200 group-hover:scale-y-100"></div>


                        <div class="flex items-center gap-4">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100 transition group-hover:bg-blue-600 group-hover:text-white">

                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >
                                    <rect
                                        x="3"
                                        y="5"
                                        width="18"
                                        height="14"
                                        rx="2.5"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M3.5 7.5l7.1 5.3a2.25 2.25 0 002.8 0l7.1-5.3"
                                    />
                                </svg>

                            </div>


                            <div class="min-w-0 flex-1">

                                <div class="flex items-start justify-between gap-4">

                                    <div class="min-w-0">

                                        <h3
                                            class="truncate text-xs font-extrabold text-slate-800 group-hover:text-blue-600 sm:text-sm"
                                            title="{{ $sm->perihal ?? '-' }}"
                                        >
                                            {{ $sm->perihal ?? '-' }}
                                        </h3>


                                        <div class="mt-1 flex items-center gap-2">

                                            <span class="truncate font-mono text-[9px] font-bold text-blue-600 sm:text-[10px]">
                                                {{ $nomorSurat }}
                                            </span>


                                            <span class="h-1 w-1 rounded-full bg-slate-300"></span>


                                            <span class="text-[9px] text-slate-400 sm:text-[10px]">
                                                {{ $tanggalSurat }}
                                            </span>

                                        </div>

                                    </div>


                                    <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-[9px] font-bold {{ $currentStatusConfig['class'] }}">

                                        <span class="h-1.5 w-1.5 rounded-full {{ $currentStatusConfig['dot'] }}"></span>

                                        {{ $currentStatusConfig['label'] }}

                                    </span>

                                </div>


                                <div class="mt-2.5 flex flex-wrap items-center gap-x-6 gap-y-1.5">

                                    <span class="text-[10px] font-semibold text-slate-500">
                                        {{ $sm->pengirim ?? '-' }}
                                    </span>


                                    <span class="text-[10px] font-semibold text-slate-500">
                                        {{ $sm->kategori->nama_kategori ?? '-' }}
                                    </span>

                                </div>

                            </div>


                            <a
                                href="{{ route('surat-masuk.show', $sm->id) }}"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[10px] font-bold text-slate-500 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600"
                            >
                                Detail

                                <svg
                                    class="h-3.5 w-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>

                            </a>

                        </div>

                    </div>

                @empty

                    <div class="px-6 py-14 text-center">

                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">

                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                                viewBox="0 0 24 24"
                            >
                                <rect
                                    x="3"
                                    y="5"
                                    width="18"
                                    height="14"
                                    rx="2"
                                />

                                <path
                                    stroke-linecap="round"
                                    d="M3.5 7.5l7.1 5.3a2.25 2.25 0 002.8 0l7.1-5.3"
                                />
                            </svg>

                        </div>


                        <h3 class="mt-4 text-sm font-extrabold text-slate-700">
                            Belum Ada Surat Masuk
                        </h3>


                        <p class="mx-auto mt-1 max-w-sm text-[10px] leading-relaxed text-slate-400 sm:text-xs">
                            Surat masuk terbaru akan tampil di sini.
                        </p>

                    </div>

                @endforelse

            </div>


            {{-- MOBILE --}}

            <div class="md:hidden">

                @forelse(($suratMasukTerbaru ?? []) as $sm)

                    @php

                        $currentStatus =
                            strtolower(
                                trim(
                                    (string) (
                                        $sm->status
                                        ?? 'baru'
                                    )
                                )
                            );


                        $currentStatusConfig =
                            $statusConfig[$currentStatus]
                            ?? [
                                'label' => ucfirst($currentStatus),
                                'class' => 'bg-slate-50 text-slate-600 border-slate-200',
                                'dot' => 'bg-slate-400',
                            ];


                        $nomorSurat =
                            trim(
                                (string) (
                                    $sm->nomor_surat
                                    ?? ''
                                )
                            );

                        $nomorSurat =
                            $nomorSurat !== ''
                                ? $nomorSurat
                                : '-';


                        $tanggalSurat = '-';

                        if ($sm->tanggal_surat) {

                            try {

                                $tanggalSurat =
                                    \Illuminate\Support\Carbon::parse(
                                        $sm->tanggal_surat
                                    )->translatedFormat('d M Y');

                            } catch (\Throwable $e) {

                                $tanggalSurat =
                                    (string) $sm->tanggal_surat;

                            }

                        }

                    @endphp


                    <a
                        href="{{ route('surat-masuk.show', $sm->id) }}"
                        class="group relative block border-b border-slate-100 px-4 py-4 active:bg-slate-50"
                    >

                        <div class="flex gap-3">

                            <div class="relative flex w-9 shrink-0 justify-center">

                                @if(!$loop->last)

                                    <span class="absolute left-1/2 top-9 bottom-[-1rem] w-px -translate-x-1/2 bg-slate-200"></span>

                                @endif


                                <div class="relative z-10 flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-4 ring-white">

                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        viewBox="0 0 24 24"
                                    >
                                        <rect
                                            x="3"
                                            y="5"
                                            width="18"
                                            height="14"
                                            rx="2.5"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3.5 7.5l7.1 5.3a2.25 2.25 0 002.8 0l7.1-5.3"
                                        />
                                    </svg>

                                </div>

                            </div>


                            <div class="min-w-0 flex-1">

                                <div class="flex items-start justify-between gap-2">

                                    <div class="min-w-0">

                                        <h3
                                            class="line-clamp-2 text-xs font-extrabold leading-relaxed text-slate-800"
                                            title="{{ $sm->perihal ?? '-' }}"
                                        >
                                            {{ $sm->perihal ?? '-' }}
                                        </h3>


                                        <p class="mt-1 truncate font-mono text-[9px] font-bold text-blue-600">
                                            {{ $nomorSurat }}
                                        </p>

                                    </div>


                                    <span class="shrink-0 rounded-full border px-2 py-1 text-[8px] font-bold {{ $currentStatusConfig['class'] }}">
                                        {{ $currentStatusConfig['label'] }}
                                    </span>

                                </div>


                                <div class="mt-2 text-[10px] text-slate-500">
                                    {{ $sm->pengirim ?? '-' }}
                                </div>


                                <div class="mt-1 flex items-center gap-2 text-[9px] text-slate-400">
                                    {{ $sm->kategori->nama_kategori ?? '-' }}
                                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                    {{ $tanggalSurat }}
                                </div>


                                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2.5">

                                    <span class="text-[9px] text-slate-400">
                                        Ketuk untuk melihat detail
                                    </span>


                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                        →
                                    </span>

                                </div>

                            </div>

                        </div>

                    </a>

                @empty

                    <div class="px-5 py-14 text-center">

                        <p class="text-xs font-extrabold text-slate-700">
                            Belum Ada Surat Masuk
                        </p>

                        <p class="mx-auto mt-1 max-w-xs text-[10px] text-slate-400">
                            Surat masuk terbaru akan tampil di sini.
                        </p>

                    </div>

                @endforelse

            </div>


            @if(($suratMasukTerbaru ?? collect())->count() > 0)

                <div class="border-t border-slate-100 bg-slate-50/60 px-4 py-3 sm:px-6">

                    <div class="flex items-center justify-between gap-3">

                        <p class="text-[9px] text-slate-400 sm:text-[10px]">

                            Menampilkan

                            <span class="font-bold text-slate-600">
                                {{ ($suratMasukTerbaru ?? collect())->count() }}
                            </span>

                            surat terbaru

                        </p>


                        <a
                            href="{{ route('surat-masuk.index') }}"
                            class="text-[9px] font-bold text-blue-600 sm:text-[10px]"
                        >
                            Buka arsip
                        </a>

                    </div>

                </div>

            @endif

        </section>


        {{-- ================================================================
            RIWAYAT SURAT KELUAR
            SUMBER DATA LANGSUNG DARI TABEL SURAT_KELUAR
        ================================================================= --}}

        <section class="dashboard-panel overflow-hidden">

            {{-- HEADER --}}

            <div class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-br from-white via-white to-emerald-50/40 px-4 py-5 sm:px-6 sm:py-6">

                <div class="pointer-events-none absolute -right-12 -top-16 h-40 w-40 rounded-full bg-emerald-500/5 blur-3xl"></div>

                <div class="pointer-events-none absolute -bottom-16 left-1/3 h-32 w-32 rounded-full bg-teal-500/5 blur-3xl"></div>


                <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex min-w-0 items-start gap-3">

                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 shadow-sm ring-1 ring-emerald-100">

                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12h11"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13 6l6 6-6 6"
                                />
                            </svg>

                        </div>


                        <div class="min-w-0">

                            <div class="flex flex-wrap items-center gap-2">

                                <span class="section-eyebrow">
                                    Arsip Surat Keluar
                                </span>


                                @if($riwayatSuratKeluar->count() > 0)

                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-bold text-emerald-600 ring-1 ring-emerald-100">

                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                        {{ $riwayatSuratKeluar->count() }} terbaru

                                    </span>

                                @endif

                            </div>


                            <h2 class="mt-1 text-base font-extrabold tracking-tight text-slate-800 sm:text-lg">
                                Riwayat Surat Keluar
                            </h2>


                            <p class="mt-1 max-w-xl text-[10px] leading-relaxed text-slate-400 sm:text-xs">
                                Menampilkan surat keluar terbaru yang masih tersimpan di dalam arsip.
                            </p>

                        </div>

                    </div>


                    <a
                        href="{{ route('surat-keluar.index') }}"
                        class="group inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-[10px] font-bold text-slate-600 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-600 hover:text-white sm:w-auto sm:text-xs"
                    >

                        <span>
                            Lihat Arsip
                        </span>


                        <svg
                            class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>

                    </a>

                </div>

            </div>


            {{-- DESKTOP --}}

            <div class="hidden md:block">

                @forelse($riwayatSuratKeluar as $suratKeluar)

                    @php

                        $statusKeluar =
                            strtolower(
                                trim(
                                    (string) (
                                        $suratKeluar->status
                                        ?? 'draft'
                                    )
                                )
                            );


                        $currentStatusConfig =
                            $statusKeluarConfig[
                                $statusKeluar
                            ]
                            ?? [
                                'label' => ucfirst($statusKeluar),
                                'class' => 'bg-slate-50 text-slate-600 border-slate-200',
                                'dot' => 'bg-slate-400',
                                'icon' => 'bg-slate-50 text-slate-600 ring-slate-100',
                            ];


                        $nomorSurat =
                            trim(
                                (string) (
                                    $suratKeluar->nomor_surat
                                    ?? ''
                                )
                            );

                        if ($nomorSurat === '') {
                            $nomorSurat = '-';
                        }


                        $perihalSurat =
                            trim(
                                (string) (
                                    $suratKeluar->perihal
                                    ?? ''
                                )
                            );

                        if ($perihalSurat === '') {
                            $perihalSurat = 'Tanpa Perihal';
                        }


                        $tanggalSurat = '-';

                        if ($suratKeluar->tanggal_surat) {

                            try {

                                $tanggalSurat =
                                    \Illuminate\Support\Carbon::parse(
                                        $suratKeluar->tanggal_surat
                                    )->translatedFormat(
                                        'd M Y'
                                    );

                            } catch (\Throwable $e) {

                                $tanggalSurat =
                                    (string) $suratKeluar->tanggal_surat;

                            }

                        }


                        $kategoriSurat =
                            optional(
                                $suratKeluar->kategori
                            )->nama_kategori
                            ?? '-';

                    @endphp


                    <div class="group relative border-b border-slate-100 px-4 py-4 last:border-b-0 sm:px-6">

                        <div class="absolute inset-y-0 left-0 w-1 origin-left scale-y-0 rounded-r-full bg-emerald-500 transition-transform duration-200 group-hover:scale-y-100"></div>


                        <div class="flex items-center gap-4">

                            {{-- ICON --}}

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $currentStatusConfig['icon'] }} ring-1 transition-all duration-200 group-hover:scale-105 group-hover:bg-emerald-600 group-hover:text-white">

                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5 12h11"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M13 6l6 6-6 6"
                                    />
                                </svg>

                            </div>


                            {{-- CONTENT --}}

                            <div class="min-w-0 flex-1">

                                <div class="flex items-start justify-between gap-4">

                                    <div class="min-w-0">

                                        <h3
                                            class="truncate text-xs font-extrabold text-slate-800 transition-colors group-hover:text-emerald-600 sm:text-sm"
                                            title="{{ $perihalSurat }}"
                                        >
                                            {{ $perihalSurat }}
                                        </h3>


                                        <div class="mt-1 flex flex-wrap items-center gap-2">

                                            <span
                                                class="truncate font-mono text-[9px] font-bold text-emerald-600 sm:text-[10px]"
                                                title="{{ $nomorSurat }}"
                                            >
                                                {{ $nomorSurat }}
                                            </span>


                                            <span class="h-1 w-1 shrink-0 rounded-full bg-slate-300"></span>


                                            <span class="shrink-0 text-[9px] text-slate-400 sm:text-[10px]">
                                                {{ $tanggalSurat }}
                                            </span>

                                        </div>

                                    </div>


                                    {{-- STATUS --}}

                                    <span
                                        class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-2.5 py-1 text-[9px] font-bold {{ $currentStatusConfig['class'] }}"
                                    >

                                        <span class="h-1.5 w-1.5 rounded-full {{ $currentStatusConfig['dot'] }}"></span>

                                        {{ $currentStatusConfig['label'] }}

                                    </span>

                                </div>


                                <div class="mt-2.5 flex flex-wrap items-center gap-x-6 gap-y-1.5">

                                    <div class="flex items-center gap-1.5">

                                        <svg
                                            class="h-3.5 w-3.5 shrink-0 text-slate-400"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M4 7.5A2.5 2.5 0 016.5 5h3l2 2h6A2.5 2.5 0 0120 9.5v7A2.5 2.5 0 0117.5 19h-11A2.5 2.5 0 014 16.5v-9z"
                                            />
                                        </svg>


                                        <span class="text-[10px] font-semibold text-slate-500">
                                            {{ $kategoriSurat }}
                                        </span>

                                    </div>


                                    <div class="flex items-center gap-1.5">

                                        <svg
                                            class="h-3.5 w-3.5 shrink-0 text-slate-400"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            viewBox="0 0 24 24"
                                        >
                                            <rect
                                                x="3"
                                                y="5"
                                                width="18"
                                                height="15"
                                                rx="2"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                d="M8 3v4M16 3v4M3 10h18"
                                            />
                                        </svg>


                                        <span class="text-[10px] font-semibold text-slate-500">
                                            {{ $tanggalSurat }}
                                        </span>

                                    </div>

                                </div>

                            </div>


                            {{-- DETAIL --}}

                            <a
                                href="{{ route('surat-keluar.show', $suratKeluar->id) }}"
                                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-[10px] font-bold text-slate-500 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600"
                            >

                                Detail

                                <svg
                                    class="h-3.5 w-3.5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 5l7 7-7 7"
                                    />
                                </svg>

                            </a>

                        </div>

                    </div>

                @empty

                    <div class="px-6 py-14 text-center">

                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">

                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12h11"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13 6l6 6-6 6"
                                />
                            </svg>

                        </div>


                        <h3 class="mt-4 text-sm font-extrabold text-slate-700">
                            Belum Ada Surat Keluar
                        </h3>


                        <p class="mx-auto mt-1 max-w-sm text-[10px] leading-relaxed text-slate-400 sm:text-xs">
                            Surat keluar yang tersimpan di arsip akan muncul di bagian ini.
                        </p>


                        <a
                            href="{{ route('surat-keluar.create') }}"
                            class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-[10px] font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-700 sm:text-xs"
                        >

                            <svg
                                class="h-3.5 w-3.5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    d="M12 5v14M5 12h14"
                                />
                            </svg>

                            Tambah Surat Keluar

                        </a>

                    </div>

                @endforelse

            </div>


            {{-- MOBILE --}}

            <div class="md:hidden">

                @forelse($riwayatSuratKeluar as $suratKeluar)

                    @php

                        $statusKeluar =
                            strtolower(
                                trim(
                                    (string) (
                                        $suratKeluar->status
                                        ?? 'draft'
                                    )
                                )
                            );


                        $currentStatusConfig =
                            $statusKeluarConfig[
                                $statusKeluar
                            ]
                            ?? [
                                'label' => ucfirst($statusKeluar),
                                'class' => 'bg-slate-50 text-slate-600 border-slate-200',
                                'dot' => 'bg-slate-400',
                                'icon' => 'bg-slate-50 text-slate-600 ring-slate-100',
                            ];


                        $nomorSurat =
                            trim(
                                (string) (
                                    $suratKeluar->nomor_surat
                                    ?? ''
                                )
                            );

                        if ($nomorSurat === '') {
                            $nomorSurat = '-';
                        }


                        $perihalSurat =
                            trim(
                                (string) (
                                    $suratKeluar->perihal
                                    ?? ''
                                )
                            );

                        if ($perihalSurat === '') {
                            $perihalSurat = 'Tanpa Perihal';
                        }


                        $tanggalSurat = '-';

                        if ($suratKeluar->tanggal_surat) {

                            try {

                                $tanggalSurat =
                                    \Illuminate\Support\Carbon::parse(
                                        $suratKeluar->tanggal_surat
                                    )->translatedFormat(
                                        'd M Y'
                                    );

                            } catch (\Throwable $e) {

                                $tanggalSurat =
                                    (string) $suratKeluar->tanggal_surat;

                            }

                        }

                    @endphp


                    <a
                        href="{{ route('surat-keluar.show', $suratKeluar->id) }}"
                        class="group relative block border-b border-slate-100 px-4 py-4 transition-colors duration-200 active:bg-slate-50"
                    >

                        <div class="flex gap-3">

                            {{-- TIMELINE --}}

                            <div class="relative flex w-9 shrink-0 justify-center">

                                @if(!$loop->last)

                                    <span class="absolute left-1/2 top-9 bottom-[-1rem] w-px -translate-x-1/2 bg-slate-200"></span>

                                @endif


                                <div class="relative z-10 flex h-9 w-9 items-center justify-center rounded-xl {{ $currentStatusConfig['icon'] }} ring-4 ring-white">

                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5 12h11"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M13 6l6 6-6 6"
                                        />
                                    </svg>


                                    <span
                                        class="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full border-2 border-white {{ $currentStatusConfig['dot'] }}"
                                    ></span>

                                </div>

                            </div>


                            {{-- CONTENT --}}

                            <div class="min-w-0 flex-1">

                                <div class="flex items-start justify-between gap-2">

                                    <div class="min-w-0 flex-1">

                                        <h3
                                            class="line-clamp-2 text-xs font-extrabold leading-relaxed text-slate-800 group-active:text-emerald-600"
                                            title="{{ $perihalSurat }}"
                                        >
                                            {{ $perihalSurat }}
                                        </h3>


                                        <p
                                            class="mt-1 truncate font-mono text-[9px] font-bold text-emerald-600"
                                            title="{{ $nomorSurat }}"
                                        >
                                            {{ $nomorSurat }}
                                        </p>

                                    </div>


                                    <span
                                        class="shrink-0 rounded-full border px-2 py-1 text-[8px] font-bold {{ $currentStatusConfig['class'] }}"
                                    >
                                        {{ $currentStatusConfig['label'] }}
                                    </span>

                                </div>


                                <div class="mt-2.5 flex items-center gap-1.5 text-[10px] text-slate-500">

                                    <svg
                                        class="h-3 w-3 shrink-0 text-slate-400"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M4 7.5A2.5 2.5 0 016.5 5h3l2 2h6A2.5 2.5 0 0120 9.5v7A2.5 2.5 0 0117.5 19h-11A2.5 2.5 0 014 16.5v-9z"
                                        />
                                    </svg>


                                    <span class="truncate">
                                        {{ $suratKeluar->kategori->nama_kategori ?? '-' }}
                                    </span>

                                </div>


                                <div class="mt-1.5 flex items-center gap-2 text-[9px] text-slate-400">

                                    <svg
                                        class="h-3 w-3 shrink-0"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        viewBox="0 0 24 24"
                                    >
                                        <rect
                                            x="3"
                                            y="5"
                                            width="18"
                                            height="15"
                                            rx="2"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            d="M8 3v4M16 3v4M3 10h18"
                                        />
                                    </svg>


                                    {{ $tanggalSurat }}

                                </div>


                                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2.5">

                                    <span class="text-[9px] font-medium text-slate-400">
                                        Ketuk untuk melihat detail
                                    </span>


                                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-slate-50 text-slate-400">

                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M9 5l7 7-7 7"
                                            />
                                        </svg>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </a>

                @empty

                    <div class="px-5 py-14 text-center">

                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">

                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.6"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 12h11"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13 6l6-6 6"
                                />
                            </svg>

                        </div>


                        <p class="mt-4 text-xs font-extrabold text-slate-700">
                            Belum Ada Surat Keluar
                        </p>


                        <p class="mx-auto mt-1 max-w-xs text-[10px] leading-relaxed text-slate-400">
                            Surat keluar terbaru akan tampil di sini setelah ditambahkan.
                        </p>

                    </div>

                @endforelse

            </div>


            {{-- FOOTER --}}

            @if($riwayatSuratKeluar->count() > 0)

                <div class="border-t border-slate-100 bg-slate-50/60 px-4 py-3 sm:px-6">

                    <div class="flex items-center justify-between gap-3">

                        <p class="text-[9px] text-slate-400 sm:text-[10px]">

                            Menampilkan

                            <span class="font-bold text-slate-600">
                                {{ $riwayatSuratKeluar->count() }}
                            </span>

                            surat keluar terbaru

                        </p>


                        <a
                            href="{{ route('surat-keluar.index') }}"
                            class="text-[9px] font-bold text-emerald-600 transition hover:text-emerald-700 sm:text-[10px]"
                        >
                            Buka arsip surat keluar
                        </a>

                    </div>

                </div>

            @endif

        </section>

    @else

        {{-- =================================================================
            STAFF SCORECARD
        ================================================================== --}}

        <section>

            <div class="grid grid-cols-2 gap-3 sm:gap-4">


                {{-- MENUNGGU --}}

                <a
                    href="{{ route('disposisi.index', ['status' => ['menunggu']]) }}"
                    class="stat-card stat-card-amber group"
                >

                    <div class="stat-color-bar bg-amber-500"></div>


                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <span class="stat-label">
                                Menunggu
                            </span>


                            <div class="mt-3 flex items-baseline gap-1">

                                <span class="stat-number">
                                    {{ $disposisiMenunggu ?? 0 }}
                                </span>


                                <span class="hidden text-[10px] font-semibold text-slate-400 sm:inline">
                                    tugas
                                </span>

                            </div>

                        </div>


                        <div class="stat-icon stat-icon-amber">

                            <svg
                                class="h-5 w-5 sm:h-6 sm:w-6"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="8.5"
                                />

                                <path
                                    stroke-linecap="round"
                                    d="M12 7.5v5l3 2"
                                />
                            </svg>

                        </div>

                    </div>


                    <div class="mt-4 flex items-center justify-between">

                        <span class="stat-pill bg-amber-50 text-amber-600">
                            Perlu Ditindaklanjuti
                        </span>


                        <span class="stat-arrow text-amber-500">
                            →
                        </span>

                    </div>

                </a>


                {{-- SELESAI --}}

                <a
                    href="{{ route('disposisi.index', ['status' => ['selesai']]) }}"
                    class="stat-card stat-card-teal group"
                >

                    <div class="stat-color-bar bg-teal-500"></div>


                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <span class="stat-label">
                                Selesai
                            </span>


                            <div class="mt-3 flex items-baseline gap-1">

                                <span class="stat-number">
                                    {{ $disposisiSelesai ?? 0 }}
                                </span>


                                <span class="hidden text-[10px] font-semibold text-slate-400 sm:inline">
                                    tugas
                                </span>

                            </div>

                        </div>


                        <div class="stat-icon stat-icon-teal">

                            <svg
                                class="h-5 w-5 sm:h-6 sm:w-6"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M8 12.5l2.5 2.5L16.5 9"
                                />
                            </svg>

                        </div>

                    </div>


                    <div class="mt-4 flex items-center justify-between">

                        <span class="stat-pill bg-teal-50 text-teal-600">
                            Tuntas
                        </span>


                        <span class="stat-arrow text-teal-500">
                            →
                        </span>

                    </div>

                </a>

            </div>

        </section>


        {{-- =================================================================
            STAFF TASK LIST
        ================================================================== --}}

        <section class="dashboard-panel overflow-hidden">

            <div class="border-b border-slate-100 px-4 py-4 sm:px-6 sm:py-5">

                <div class="flex items-center gap-3">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600">

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >
                            <rect
                                x="5"
                                y="4"
                                width="14"
                                height="16"
                                rx="2"
                            />

                            <path
                                stroke-linecap="round"
                                d="M9 4.5V3h6v1.5M9 9h6M9 13h6M9 17h4"
                            />
                        </svg>

                    </div>


                    <div>

                        <h2 class="text-sm font-extrabold text-slate-800 sm:text-base">
                            Disposisi Tugas Untuk Saya
                        </h2>


                        <p class="mt-0.5 text-[10px] text-slate-400 sm:text-xs">
                            Tugas surat masuk yang perlu Anda tindak lanjuti.
                        </p>

                    </div>

                </div>

            </div>


            <div class="p-4 sm:p-6">

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">

                    @forelse($listDisposisi ?? [] as $d)

                        @php

                            $status =
                                strtolower(
                                    trim(
                                        (string) (
                                            $d->status
                                            ?? 'menunggu'
                                        )
                                    )
                                );


                            $currentDisposisiConfig =
                                $disposisiConfig[
                                    $status
                                ]
                                ?? [
                                    'label' => ucfirst($status),
                                    'class' => 'bg-slate-50 text-slate-700 border-slate-200',
                                    'border' => 'border-l-slate-400',
                                    'icon' => 'bg-slate-50 text-slate-500',
                                ];


                            $tanggalSurat = '-';

                            if ($d->suratMasuk?->tanggal_surat) {

                                try {

                                    $tanggalSurat =
                                        \Illuminate\Support\Carbon::parse(
                                            $d->suratMasuk->tanggal_surat
                                        )->format(
                                            'd/m/Y'
                                        );

                                } catch (\Throwable $e) {

                                    $tanggalSurat =
                                        (string) $d->suratMasuk->tanggal_surat;

                                }

                            }

                        @endphp


                        <a
                            href="{{ route('disposisi.show', $d->id) }}"
                            class="task-card group {{ $currentDisposisiConfig['border'] }}"
                        >

                            <div class="flex items-start gap-3">

                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $currentDisposisiConfig['icon'] }}">

                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        viewBox="0 0 24 24"
                                    >
                                        <rect
                                            x="5"
                                            y="4"
                                            width="14"
                                            height="16"
                                            rx="2"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            d="M9 9h6M9 13h6M9 17h4"
                                        />
                                    </svg>

                                </div>


                                <div class="min-w-0 flex-1">

                                    <h3 class="line-clamp-2 text-xs font-bold leading-relaxed text-slate-800 transition group-hover:text-blue-600 sm:text-sm">
                                        {{ $d->suratMasuk?->perihal ?? 'Surat Disposisi' }}
                                    </h3>

                                </div>


                                <span class="shrink-0 rounded-full border px-2 py-1 text-[8px] font-bold {{ $currentDisposisiConfig['class'] }}">
                                    {{ $currentDisposisiConfig['label'] }}
                                </span>

                            </div>


                            <div class="mt-4 space-y-2.5">

                                {{-- DARI --}}

                                <div class="flex items-center gap-2">

                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">

                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            viewBox="0 0 24 24"
                                        >
                                            <circle
                                                cx="12"
                                                cy="8"
                                                r="3.5"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                d="M5 20a7 7 0 0114 0"
                                            />
                                        </svg>

                                    </span>


                                    <div class="min-w-0">

                                        <span class="block text-[9px] font-semibold uppercase tracking-wide text-slate-400">
                                            Dari
                                        </span>


                                        <span class="block truncate text-[11px] font-semibold text-slate-700">
                                            {{ $d->dari?->name ?? $d->dari?->nama ?? '-' }}
                                        </span>

                                    </div>

                                </div>


                                {{-- TANGGAL --}}

                                <div class="flex items-center gap-2">

                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">

                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            viewBox="0 0 24 24"
                                        >
                                            <rect
                                                x="3"
                                                y="5"
                                                width="18"
                                                height="15"
                                                rx="2"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                d="M8 3v4M16 3v4M3 10h18"
                                            />
                                        </svg>

                                    </span>


                                    <div>

                                        <span class="block text-[9px] font-semibold uppercase tracking-wide text-slate-400">
                                            Tanggal Surat
                                        </span>


                                        <span class="block text-[11px] font-bold text-slate-700">
                                            {{ $tanggalSurat }}
                                        </span>

                                    </div>

                                </div>


                                {{-- BATAS WAKTU --}}

                                @if($d->batas_waktu)

                                    @php

                                        $batasWaktu = '-';

                                        try {

                                            $batasWaktu =
                                                \Illuminate\Support\Carbon::parse(
                                                    $d->batas_waktu
                                                )->format(
                                                    'd M Y'
                                                );

                                        } catch (\Throwable $e) {

                                            $batasWaktu =
                                                (string) $d->batas_waktu;

                                        }

                                    @endphp


                                    <div class="flex items-center gap-2">

                                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">

                                            <svg
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="8.5"
                                                />

                                                <path
                                                    stroke-linecap="round"
                                                    d="M12 7.5v5l3 2"
                                                />
                                            </svg>

                                        </span>


                                        <div>

                                            <span class="block text-[9px] font-semibold uppercase tracking-wide text-slate-400">
                                                Batas Waktu
                                            </span>


                                            <span class="block text-[11px] font-bold text-slate-700">
                                                {{ $batasWaktu }}
                                            </span>

                                        </div>

                                    </div>

                                @endif

                            </div>


                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">

                                <span class="text-[10px] font-medium text-slate-400">
                                    Lihat detail tugas
                                </span>


                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-slate-50 text-slate-400 transition group-hover:bg-blue-50 group-hover:text-blue-600">
                                    →
                                </span>

                            </div>

                        </a>

                    @empty

                        <div class="md:col-span-2 xl:col-span-3">

                            <div class="empty-state">

                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">

                                    <svg
                                        class="h-7 w-7"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.6"
                                        viewBox="0 0 24 24"
                                    >
                                        <rect
                                            x="5"
                                            y="4"
                                            width="14"
                                            height="16"
                                            rx="2"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            d="M9 4.5V3h6v1.5M9 9h6M9 13h6M9 17h4"
                                        />
                                    </svg>

                                </div>


                                <p class="mt-4 text-xs font-bold text-slate-700">
                                    Tidak ada tugas baru
                                </p>


                                <p class="mt-1 max-w-sm text-center text-[10px] leading-relaxed text-slate-400">
                                    Saat ada disposisi baru yang diberikan kepada Anda,
                                    tugas tersebut akan tampil di halaman ini.
                                </p>

                            </div>

                        </div>

                    @endforelse

                </div>

            </div>


            <div class="border-t border-slate-100 bg-slate-50/50 p-4 sm:p-5">

                <a
                    href="{{ route('disposisi.index') }}"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-[10px] font-bold text-white transition hover:bg-blue-600 sm:text-xs"
                >

                    Kelola Semua Disposisi

                    <svg
                        class="h-3.5 w-3.5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>

                </a>

            </div>

        </section>

    @endif

</div>


{{-- ========================================================================
    DASHBOARD STYLE
============================================================================ --}}

<style>

    .dashboard-page {
        animation: dashboardFade .45s ease-out;
    }


    @keyframes dashboardFade {

        from {
            opacity: 0;
            transform: translateY(8px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }

    }


    .hero-dashboard {
        isolation: isolate;
    }


    .dashboard-action {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 230px;
        border-radius: 1rem;
        background: rgba(255, 255, 255, .96);
        padding: .7rem .8rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .12);
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            background .2s ease;
    }


    .dashboard-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, .18);
    }


    .dashboard-action-icon {
        display: flex;
        height: 2.5rem;
        width: 2.5rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: .8rem;
    }


    .stat-card {
        position: relative;
        display: block;
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, .9);
        border-radius: 1.25rem;
        background: #ffffff;
        padding: 1rem;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .045);
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            border-color .2s ease;
    }


    .stat-card:hover {
        transform: translateY(-4px);
        border-color: rgba(148, 163, 184, .35);
        box-shadow: 0 18px 40px rgba(15, 23, 42, .10);
    }


    .stat-color-bar {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 4px;
        border-radius: 0 4px 4px 0;
        opacity: .95;
    }


    .stat-label {
        display: block;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
    }


    .stat-number {
        display: block;
        font-size: 1.65rem;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -.04em;
        color: #0f172a;
    }


    .stat-icon {
        display: flex;
        height: 2.75rem;
        width: 2.75rem;
        flex-shrink: 0;
        align-items: center;
        justify-content: center;
        border-radius: .9rem;
        transition:
            background .2s ease,
            color .2s ease,
            transform .2s ease,
            box-shadow .2s ease;
    }


    .stat-card:hover .stat-icon {
        transform: scale(1.07);
    }


    .stat-icon-blue {
        background: #eff6ff;
        color: #2563eb;
        box-shadow: 0 0 0 1px #dbeafe;
    }


    .stat-card-blue:hover .stat-icon-blue {
        background: #2563eb;
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(37, 99, 235, .25);
    }


    .stat-icon-emerald {
        background: #ecfdf5;
        color: #059669;
        box-shadow: 0 0 0 1px #d1fae5;
    }


    .stat-card-emerald:hover .stat-icon-emerald {
        background: #059669;
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(5, 150, 105, .25);
    }


    .stat-icon-amber {
        background: #fffbeb;
        color: #d97706;
        box-shadow: 0 0 0 1px #fef3c7;
    }


    .stat-card-amber:hover .stat-icon-amber {
        background: #f59e0b;
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(245, 158, 11, .25);
    }


    .stat-icon-teal {
        background: #f0fdfa;
        color: #0d9488;
        box-shadow: 0 0 0 1px #ccfbf1;
    }


    .stat-card-teal:hover .stat-icon-teal {
        background: #0d9488;
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(13, 148, 136, .25);
    }


    .stat-pill {
        display: inline-flex;
        align-items: center;
        border-radius: .55rem;
        padding: .3rem .55rem;
        font-size: 9px;
        font-weight: 800;
    }


    .stat-arrow {
        font-size: .9rem;
        font-weight: 800;
        opacity: .7;
        transition: transform .2s ease;
    }


    .stat-card:hover .stat-arrow {
        transform: translateX(4px);
    }


    .dashboard-panel {
        border: 1px solid rgba(226, 232, 240, .85);
        border-radius: 1.25rem;
        background: #ffffff;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
    }


    .section-eyebrow {
        display: inline-block;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: #94a3b8;
    }


    .task-card {
        position: relative;
        display: block;
        border: 1px solid rgba(226, 232, 240, .9);
        border-left-width: 4px;
        border-radius: 1rem;
        background: #ffffff;
        padding: 1rem;
        box-shadow: 0 3px 15px rgba(15, 23, 42, .035);
        transition:
            transform .2s ease,
            box-shadow .2s ease,
            background .2s ease;
    }


    .task-card:hover {
        transform: translateY(-3px);
        background: #fcfdff;
        box-shadow: 0 14px 30px rgba(15, 23, 42, .08);
    }


    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 220px;
        border: 1px dashed #e2e8f0;
        border-radius: 1rem;
        background: rgba(248, 250, 252, .65);
        padding: 2rem;
    }


    @media (min-width: 640px) {

        .stat-card {
            padding: 1.25rem;
        }

        .stat-label {
            font-size: 10px;
        }

        .stat-number {
            font-size: 2rem;
        }

        .stat-icon {
            height: 3rem;
            width: 3rem;
        }

    }


    @media (max-width: 639px) {

        .dashboard-action {
            min-width: 0;
            width: 100%;
        }


        .stat-card {
            min-height: 145px;
        }


        .stat-number {
            font-size: 1.5rem;
        }


        .stat-pill {
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

    }


    @media (prefers-reduced-motion: reduce) {

        .dashboard-page,
        .stat-card,
        .dashboard-action,
        .task-card,
        .stat-icon,
        .stat-arrow {
            animation: none !important;
            transition: none !important;
        }

    }

</style>


{{-- ========================================================================
    CHART JS
============================================================================ --}}

@if(!$isStaf)

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const canvas =
                    document.getElementById(
                        'suratChart'
                    );

                if (!canvas) {
                    return;
                }


                const ctx =
                    canvas.getContext(
                        '2d'
                    );

                if (!ctx) {
                    return;
                }


                const chartLabels =
                    @json(
                        $chartLabels ?? []
                    );

                const dataMasuk =
                    @json(
                        $chartDataMasuk ?? []
                    );

                const dataKeluar =
                    @json(
                        $chartDataKeluar ?? []
                    );


                const gradientMasuk =
                    ctx.createLinearGradient(
                        0,
                        0,
                        0,
                        320
                    );


                gradientMasuk.addColorStop(
                    0,
                    'rgba(37, 99, 235, .18)'
                );


                gradientMasuk.addColorStop(
                    1,
                    'rgba(37, 99, 235, 0)'
                );


                const gradientKeluar =
                    ctx.createLinearGradient(
                        0,
                        0,
                        0,
                        320
                    );


                gradientKeluar.addColorStop(
                    0,
                    'rgba(16, 185, 129, .18)'
                );


                gradientKeluar.addColorStop(
                    1,
                    'rgba(16, 185, 129, 0)'
                );


                if (
                    window.suratChartInstance
                ) {

                    window.suratChartInstance.destroy();

                }


                window.suratChartInstance =
                    new Chart(
                        ctx,
                        {

                            type: 'line',

                            data: {

                                labels:
                                    chartLabels,

                                datasets: [

                                    {

                                        label:
                                            'Surat Masuk',

                                        data:
                                            dataMasuk,

                                        borderColor:
                                            '#2563eb',

                                        backgroundColor:
                                            gradientMasuk,

                                        borderWidth:
                                            2.5,

                                        fill:
                                            true,

                                        tension:
                                            .4,

                                        pointRadius:
                                            3,

                                        pointHoverRadius:
                                            6,

                                        pointBackgroundColor:
                                            '#2563eb',

                                        pointBorderColor:
                                            '#ffffff',

                                        pointBorderWidth:
                                            2,

                                    },


                                    {

                                        label:
                                            'Surat Keluar',

                                        data:
                                            dataKeluar,

                                        borderColor:
                                            '#10b981',

                                        backgroundColor:
                                            gradientKeluar,

                                        borderWidth:
                                            2.5,

                                        fill:
                                            true,

                                        tension:
                                            .4,

                                        pointRadius:
                                            3,

                                        pointHoverRadius:
                                            6,

                                        pointBackgroundColor:
                                            '#10b981',

                                        pointBorderColor:
                                            '#ffffff',

                                        pointBorderWidth:
                                            2,

                                    }

                                ]

                            },


                            options: {

                                responsive:
                                    true,

                                maintainAspectRatio:
                                    false,

                                interaction: {

                                    intersect:
                                        false,

                                    mode:
                                        'index',

                                },


                                animation: {

                                    duration:
                                        900,

                                    easing:
                                        'easeOutQuart',

                                },


                                plugins: {

                                    legend: {

                                        display:
                                            false,

                                    },


                                    tooltip: {

                                        backgroundColor:
                                            '#0f172a',

                                        titleColor:
                                            '#ffffff',

                                        bodyColor:
                                            '#cbd5e1',

                                        borderColor:
                                            '#1e293b',

                                        borderWidth:
                                            1,

                                        titleFont: {

                                            size:
                                                11,

                                            weight:
                                                'bold',

                                        },

                                        bodyFont: {

                                            size:
                                                11,

                                        },

                                        padding:
                                            11,

                                        cornerRadius:
                                            10,

                                        displayColors:
                                            true,

                                    }

                                },


                                scales: {

                                    x: {

                                        grid: {

                                            display:
                                                false,

                                        },

                                        border: {

                                            display:
                                                false,

                                        },

                                        ticks: {

                                            color:
                                                '#94a3b8',

                                            font: {

                                                size:
                                                    10,

                                            },

                                            maxRotation:
                                                0,

                                            autoSkip:
                                                true,

                                            maxTicksLimit:
                                                12,

                                        }

                                    },


                                    y: {

                                        beginAtZero:
                                            true,

                                        border: {

                                            display:
                                                false,

                                        },

                                        ticks: {

                                            precision:
                                                0,

                                            stepSize:
                                                1,

                                            color:
                                                '#94a3b8',

                                            font: {

                                                size:
                                                    10,

                                            }

                                        },

                                        grid: {

                                            color:
                                                '#f1f5f9',

                                        }

                                    }

                                }

                            }

                        }
                    );

            }
        );

    </script>

@endif

@endsection