@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $user = auth()->user();

    $role = strtolower(trim((string) ($user->role ?? $user->jabatan ?? '')));
    $role = $role === 'staff' ? 'staf' : $role;

    $isStaf = $role === 'staf';
    $isAdmin = $role === 'admin';
    $isPimpinan = $role === 'pimpinan';

    $roleLabel = match ($role) {
        'admin' => 'Administrator',
        'pimpinan' => 'Pimpinan',
        'staf' => 'Staf',
        default => 'Pengguna',
    };

    $today = now()->translatedFormat('l, d F Y');

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
@endphp

<div class="dashboard-page space-y-6 sm:space-y-7 lg:space-y-8">

    {{-- HERO --}}
    <section class="hero-dashboard relative overflow-hidden rounded-[28px] bg-slate-950 shadow-2xl">
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-blue-600/30 blur-3xl"></div>
            <div class="absolute -bottom-32 left-1/3 h-80 w-80 rounded-full bg-indigo-600/20 blur-3xl"></div>
            <div class="absolute right-1/4 top-1/2 h-40 w-40 rounded-full bg-cyan-400/10 blur-3xl"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 via-transparent to-indigo-600/20"></div>
        </div>

        <div class="relative z-10 p-5 sm:p-7 lg:p-9">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_auto] lg:items-center">

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
                        <span class="text-blue-400">{{ $user->name ?? 'Pengguna' }}</span>
                    </h1>

                    <p class="mt-3 max-w-2xl text-xs leading-relaxed text-slate-300 sm:text-sm">
                        @if($isStaf)
                            Kelola dan tindak lanjuti disposisi surat yang diberikan kepada Anda dengan lebih cepat dan terstruktur.
                        @else
                            Pantau aktivitas arsip surat masuk, surat keluar, serta proses disposisi dalam satu dashboard terpusat.
                        @endif
                    </p>

                    <div class="mt-5 flex items-center gap-2 text-[11px] font-medium text-slate-400 sm:text-xs">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="17" rx="2"/>
                            <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                        </svg>
                        {{ $today }}
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                    @if(!$isStaf)

                        <a href="{{ route('surat-masuk.create') }}" class="dashboard-action group">
                            <span class="dashboard-action-icon bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16M4 12h16"/>
                                </svg>
                            </span>

                            <span class="text-left">
                                <span class="block text-xs font-bold text-slate-800">Surat Masuk</span>
                                <span class="block text-[10px] text-slate-500">Tambah arsip baru</span>
                            </span>

                            <svg class="ml-auto h-4 w-4 text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                        <a href="{{ route('surat-keluar.create') }}" class="dashboard-action group">
                            <span class="dashboard-action-icon bg-emerald-50 text-emerald-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h11"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 6l6 6-6 6"/>
                                </svg>
                            </span>

                            <span class="text-left">
                                <span class="block text-xs font-bold text-slate-800">Surat Keluar</span>
                                <span class="block text-[10px] text-slate-500">Tambah surat keluar</span>
                            </span>

                            <svg class="ml-auto h-4 w-4 text-slate-300 transition group-hover:translate-x-1 group-hover:text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                    @else

                        <a href="{{ route('disposisi.index') }}" class="dashboard-action group">
                            <span class="dashboard-action-icon bg-purple-50 text-purple-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <rect x="5" y="4" width="14" height="16" rx="2"/>
                                    <path stroke-linecap="round" d="M9 4.5V3h6v1.5M9 9h6M9 13h6M9 17h4"/>
                                </svg>
                            </span>

                            <span class="text-left">
                                <span class="block text-xs font-bold text-slate-800">Disposisi Saya</span>
                                <span class="block text-[10px] text-slate-500">Lihat tugas yang diberikan</span>
                            </span>

                            <svg class="ml-auto h-4 w-4 text-slate-300 transition group-hover:translate-x-1 group-hover:text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>

                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- ADMIN / PIMPINAN --}}
    @if(!$isStaf)

        <section>
            <div class="mb-4 flex items-end justify-between">
                <div>
                    <span class="section-eyebrow">Ringkasan</span>
                    <h2 class="mt-1 text-base font-extrabold tracking-tight text-slate-800 sm:text-lg">
                        Ikhtisar Arsip
                    </h2>
                </div>

                <span class="hidden text-[11px] font-medium text-slate-400 sm:block">
                    Data terkini
                </span>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">

                <a href="{{ route('surat-masuk.index') }}" class="stat-card group">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="stat-label">Surat Masuk</span>
                            <div class="mt-3">
                                <span class="stat-number">{{ $totalSuratMasuk ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="stat-icon bg-blue-50 text-blue-600 ring-blue-100 group-hover:bg-blue-600 group-hover:text-white group-hover:ring-blue-600">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <rect x="3" y="5" width="18" height="14" rx="2.5"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 7.5l7.1 5.3a2.25 2.25 0 002.8 0l7.1-5.3"/>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="stat-pill bg-blue-50 text-blue-600">Arsip Masuk</span>
                        <span class="stat-arrow text-blue-500">→</span>
                    </div>
                </a>

                <a href="{{ route('surat-keluar.index') }}" class="stat-card group">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="stat-label">Surat Keluar</span>
                            <div class="mt-3">
                                <span class="stat-number">{{ $totalSuratKeluar ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="stat-icon bg-emerald-50 text-emerald-600 ring-emerald-100 group-hover:bg-emerald-600 group-hover:text-white group-hover:ring-emerald-600">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h11"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 6l6 6-6 6"/>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="stat-pill bg-emerald-50 text-emerald-600">Arsip Keluar</span>
                        <span class="stat-arrow text-emerald-500">→</span>
                    </div>
                </a>

                <a href="{{ route('surat-masuk.index', ['status' => ['baru']]) }}" class="stat-card group">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="stat-label">Belum Diproses</span>
                            <div class="mt-3">
                                <span class="stat-number">{{ $suratPending ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="stat-icon bg-amber-50 text-amber-600 ring-amber-100 group-hover:bg-amber-500 group-hover:text-white group-hover:ring-amber-500">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8.5"/>
                                <path stroke-linecap="round" d="M12 7.5v5l3 2"/>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="stat-pill bg-amber-50 text-amber-600">Perlu Tindakan</span>
                        <span class="stat-arrow text-amber-500">→</span>
                    </div>
                </a>

                <a href="{{ route('surat-masuk.index', ['status' => ['selesai']]) }}" class="stat-card group">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="stat-label">Selesai</span>
                            <div class="mt-3">
                                <span class="stat-number">{{ $suratSelesai ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="stat-icon bg-teal-50 text-teal-600 ring-teal-100 group-hover:bg-teal-600 group-hover:text-white group-hover:ring-teal-600">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12.5l2.5 2.5L16.5 9"/>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="stat-pill bg-teal-50 text-teal-600">Terselesaikan</span>
                        <span class="stat-arrow text-teal-500">→</span>
                    </div>
                </a>

            </div>
        </section>

        {{-- ANALYTICS --}}
        <section class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_280px]">

            <div class="dashboard-panel overflow-hidden">
                <div class="border-b border-slate-100 px-4 py-4 sm:px-6 sm:py-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5M4 19h16"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 15l4-4 3 2 5-6"/>
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
            </div>

            <div class="grid grid-cols-2 gap-3 xl:grid-cols-1">

                <div class="dashboard-mini-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                Total Arsip
                            </span>

                            <div class="mt-2 text-2xl font-extrabold tracking-tight text-slate-800">
                                {{ ($totalSuratMasuk ?? 0) + ($totalSuratKeluar ?? 0) }}
                            </div>
                        </div>

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <rect x="4" y="4" width="16" height="16" rx="2"/>
                                <path stroke-linecap="round" d="M8 9h8M8 13h8M8 17h4"/>
                            </svg>
                        </div>
                    </div>

                    <p class="mt-2 text-[10px] text-slate-400">
                        Akumulasi surat masuk & keluar
                    </p>
                </div>

                <div class="dashboard-mini-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                Perlu Perhatian
                            </span>

                            <div class="mt-2 text-2xl font-extrabold tracking-tight text-amber-600">
                                {{ $suratPending ?? 0 }}
                            </div>
                        </div>

                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8.5"/>
                                <path stroke-linecap="round" d="M12 7.5v5l3 2"/>
                            </svg>
                        </div>
                    </div>

                    <p class="mt-2 text-[10px] text-slate-400">
                        Surat berstatus baru
                    </p>
                </div>

            </div>
        </section>

        {{-- RECENT MAIL --}}
        <section class="dashboard-panel overflow-hidden">
            <div class="flex flex-col gap-4 border-b border-slate-100 px-4 py-4 sm:px-6 sm:py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <span class="section-eyebrow">Aktivitas Terbaru</span>

                    <h2 class="mt-1 text-base font-extrabold tracking-tight text-slate-800 sm:text-lg">
                        Surat Masuk Terbaru
                    </h2>

                    <p class="mt-1 text-[10px] text-slate-400 sm:text-xs">
                        Arsip surat yang terakhir masuk ke dalam sistem.
                    </p>
                </div>

                <a href="{{ route('surat-masuk.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 px-3.5 py-2 text-[10px] font-bold text-slate-600 transition hover:bg-blue-600 hover:text-white sm:text-xs">
                    Lihat Semua
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/70">
                            <th class="table-heading">Nomor Agenda</th>
                            <th class="table-heading">Perihal</th>
                            <th class="table-heading">Pengirim</th>
                            <th class="table-heading">Kategori</th>
                            <th class="table-heading">Status</th>
                            <th class="table-heading text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($suratMasukTerbaru ?? [] as $sm)
                            @php
                                $currentStatus = strtolower(trim((string) ($sm->status ?? 'baru')));
                                $currentStatusConfig = $statusConfig[$currentStatus] ?? [
                                    'label' => ucfirst($currentStatus),
                                    'class' => 'bg-slate-50 text-slate-600 border-slate-200',
                                    'dot' => 'bg-slate-400',
                                ];
                            @endphp

                            <tr class="group transition hover:bg-slate-50/70">
                                <td class="table-cell">
                                    <span class="inline-flex items-center rounded-lg bg-blue-50 px-2.5 py-1.5 font-mono text-[10px] font-bold text-blue-700">
                                        {{ $sm->nomor_agenda ?? '-' }}
                                    </span>
                                </td>

                                <td class="table-cell">
                                    <div class="max-w-[260px]">
                                        <p class="truncate text-xs font-bold text-slate-800">
                                            {{ $sm->perihal ?? '-' }}
                                        </p>
                                    </div>
                                </td>

                                <td class="table-cell">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[10px] font-bold uppercase text-slate-500">
                                            {{ strtoupper(substr($sm->pengirim ?? 'P', 0, 1)) }}
                                        </span>

                                        <span class="max-w-[150px] truncate text-xs text-slate-600">
                                            {{ $sm->pengirim ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="table-cell">
                                    <span class="text-xs text-slate-600">
                                        {{ $sm->kategori->nama_kategori ?? '-' }}
                                    </span>
                                </td>

                                <td class="table-cell">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $currentStatusConfig['class'] }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $currentStatusConfig['dot'] }}"></span>
                                        {{ $currentStatusConfig['label'] }}
                                    </span>
                                </td>

                                <td class="table-cell text-right">
                                    <a href="{{ route('surat-masuk.show', $sm->id) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[10px] font-bold text-blue-600 transition hover:bg-blue-50 hover:text-blue-700">
                                        Detail
                                        <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                                <rect x="3" y="5" width="18" height="14" rx="2"/>
                                                <path stroke-linecap="round" d="M3.5 7.5l7.1 5.3a2.25 2.25 0 002.8 0l7.1-5.3"/>
                                            </svg>
                                        </div>

                                        <p class="mt-3 text-xs font-bold text-slate-700">
                                            Belum ada surat masuk
                                        </p>

                                        <p class="mt-1 text-[10px] leading-relaxed text-slate-400">
                                            Belum terdapat arsip surat masuk terbaru di dalam sistem.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-slate-100 md:hidden">
                @forelse($suratMasukTerbaru ?? [] as $sm)
                    @php
                        $currentStatus = strtolower(trim((string) ($sm->status ?? 'baru')));
                        $currentStatusConfig = $statusConfig[$currentStatus] ?? [
                            'label' => ucfirst($currentStatus),
                            'class' => 'bg-slate-50 text-slate-600 border-slate-200',
                            'dot' => 'bg-slate-400',
                        ];
                    @endphp

                    <a href="{{ route('surat-masuk.show', $sm->id) }}" class="block p-4 transition active:bg-slate-50">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <rect x="3" y="5" width="18" height="14" rx="2.5"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 7.5l7.1 5.3a2.25 2.25 0 002.8 0l7.1-5.3"/>
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-xs font-bold text-slate-800">
                                            {{ $sm->perihal ?? '-' }}
                                        </p>

                                        <p class="mt-1 font-mono text-[10px] font-semibold text-blue-600">
                                            {{ $sm->nomor_agenda ?? '-' }}
                                        </p>
                                    </div>

                                    <span class="shrink-0 rounded-full border px-2 py-1 text-[8px] font-bold {{ $currentStatusConfig['class'] }}">
                                        {{ $currentStatusConfig['label'] }}
                                    </span>
                                </div>

                                <div class="mt-2 flex items-center gap-2 text-[10px] text-slate-400">
                                    <span class="truncate">{{ $sm->pengirim ?? '-' }}</span>
                                    <span>•</span>
                                    <span class="truncate">{{ $sm->kategori->nama_kategori ?? '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-5 py-12 text-center">
                        <p class="text-xs font-bold text-slate-700">Belum ada surat masuk</p>
                        <p class="mt-1 text-[10px] text-slate-400">
                            Data surat terbaru akan tampil di sini.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

    @else

        {{-- STAFF DASHBOARD --}}
        <section>
            <div class="mb-4 flex items-end justify-between">
                <div>
                    <span class="section-eyebrow">Workspace Saya</span>
                    <h2 class="mt-1 text-base font-extrabold tracking-tight text-slate-800 sm:text-lg">
                        Tugas Disposisi
                    </h2>
                </div>

                <a href="{{ route('disposisi.index') }}" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 transition hover:text-blue-700 sm:text-xs">
                    Semua Disposisi
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4">

                <a href="{{ route('disposisi.index', ['status' => ['menunggu']]) }}" class="stat-card group">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="stat-label">Menunggu</span>
                            <div class="mt-3">
                                <span class="stat-number">{{ $disposisiMenunggu ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="stat-icon bg-amber-50 text-amber-600 ring-amber-100 group-hover:bg-amber-500 group-hover:text-white group-hover:ring-amber-500">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="8.5"/>
                                <path stroke-linecap="round" d="M12 7.5v5l3 2"/>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-4">
                        <span class="stat-pill bg-amber-50 text-amber-600">
                            Perlu Ditindaklanjuti
                        </span>
                    </div>
                </a>

                <a href="{{ route('disposisi.index', ['status' => ['selesai']]) }}" class="stat-card group">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="stat-label">Selesai</span>
                            <div class="mt-3">
                                <span class="stat-number">{{ $disposisiSelesai ?? 0 }}</span>
                            </div>
                        </div>

                        <div class="stat-icon bg-emerald-50 text-emerald-600 ring-emerald-100 group-hover:bg-emerald-600 group-hover:text-white group-hover:ring-emerald-600">
                            <svg class="h-5 w-5 sm:h-6 sm:w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="9"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12.5l2.5 2.5L16.5 9"/>
                            </svg>
                        </div>
                    </div>

                    <div class="mt-4">
                        <span class="stat-pill bg-emerald-50 text-emerald-600">
                            Tuntas
                        </span>
                    </div>
                </a>

            </div>
        </section>

        {{-- STAFF TASK LIST --}}
        <section class="dashboard-panel overflow-hidden">
            <div class="border-b border-slate-100 px-4 py-4 sm:px-6 sm:py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="5" y="4" width="14" height="16" rx="2"/>
                            <path stroke-linecap="round" d="M9 4.5V3h6v1.5M9 9h6M9 13h6M9 17h4"/>
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
                            $status = strtolower(trim((string) ($d->status ?? 'menunggu')));

                            $currentDisposisiConfig = $disposisiConfig[$status] ?? [
                                'label' => ucfirst($status),
                                'class' => 'bg-slate-50 text-slate-700 border-slate-200',
                                'border' => 'border-l-slate-400',
                                'icon' => 'bg-slate-50 text-slate-500',
                            ];
                        @endphp

                        <a href="{{ route('disposisi.show', $d->id) }}" class="task-card {{ $currentDisposisiConfig['border'] }}">
                            <div class="flex items-start gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $currentDisposisiConfig['icon'] }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <rect x="5" y="4" width="14" height="16" rx="2"/>
                                        <path stroke-linecap="round" d="M9 9h6M9 13h6M9 17h4"/>
                                    </svg>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h3 class="line-clamp-2 text-xs font-bold leading-relaxed text-slate-800 transition group-hover:text-blue-600 sm:text-sm">
                                        {{ $d->suratMasuk->perihal ?? 'Surat Disposisi' }}
                                    </h3>
                                </div>

                                <span class="shrink-0 rounded-full border px-2 py-1 text-[8px] font-bold {{ $currentDisposisiConfig['class'] }}">
                                    {{ $currentDisposisiConfig['label'] }}
                                </span>
                            </div>

                            <div class="mt-4 space-y-2.5">

                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <circle cx="12" cy="8" r="3.5"/>
                                            <path stroke-linecap="round" d="M5 20a7 7 0 0114 0"/>
                                        </svg>
                                    </span>

                                    <div class="min-w-0">
                                        <span class="block text-[9px] font-semibold uppercase tracking-wide text-slate-400">
                                            Dari
                                        </span>

                                        <span class="block truncate text-[11px] font-semibold text-slate-700">
                                            {{ $d->dari->name ?? $d->dari->nama ?? '-' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <rect x="5" y="3" width="14" height="18" rx="2"/>
                                            <path stroke-linecap="round" d="M9 8h6M9 12h6M9 16h4"/>
                                        </svg>
                                    </span>

                                    <div>
                                        <span class="block text-[9px] font-semibold uppercase tracking-wide text-slate-400">
                                            Nomor Agenda
                                        </span>

                                        <span class="block font-mono text-[11px] font-bold text-slate-700">
                                            {{ $d->suratMasuk->nomor_agenda ?? '-' }}
                                        </span>
                                    </div>
                                </div>

                                @if($d->batas_waktu)
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-400">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="8.5"/>
                                                <path stroke-linecap="round" d="M12 7.5v5l3 2"/>
                                            </svg>
                                        </span>

                                        <div>
                                            <span class="block text-[9px] font-semibold uppercase tracking-wide text-slate-400">
                                                Batas Waktu
                                            </span>

                                            <span class="block text-[11px] font-bold text-slate-700">
                                                {{ optional($d->batas_waktu)->format('d M Y') }}
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
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                        <rect x="5" y="4" width="14" height="16" rx="2"/>
                                        <path stroke-linecap="round" d="M9 4.5V3h6v1.5M9 9h6M9 13h6M9 17h4"/>
                                    </svg>
                                </div>

                                <p class="mt-4 text-xs font-bold text-slate-700">
                                    Tidak ada tugas baru
                                </p>

                                <p class="mt-1 max-w-sm text-center text-[10px] leading-relaxed text-slate-400">
                                    Saat ada disposisi baru yang diberikan kepada Anda, tugas tersebut akan tampil di halaman ini.
                                </p>
                            </div>
                        </div>

                    @endforelse
                </div>
            </div>

            <div class="border-t border-slate-100 bg-slate-50/50 p-4 sm:p-5">
                <a href="{{ route('disposisi.index') }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-[10px] font-bold text-white transition hover:bg-blue-600 sm:text-xs">
                    Kelola Semua Disposisi
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
        </section>

    @endif
</div>

<style>
.dashboard-page{animation:dashboardFade .45s ease-out}
@keyframes dashboardFade{
    from{opacity:0;transform:translateY(8px)}
    to{opacity:1;transform:translateY(0)}
}
.hero-dashboard{isolation:isolate}
.dashboard-action{
    display:flex;
    align-items:center;
    gap:.75rem;
    min-width:230px;
    border-radius:1rem;
    background:rgba(255,255,255,.96);
    padding:.7rem .8rem;
    box-shadow:0 10px 30px rgba(0,0,0,.12);
    transition:transform .2s ease,box-shadow .2s ease,background .2s ease;
}
.dashboard-action:hover{
    transform:translateY(-2px);
    box-shadow:0 15px 35px rgba(0,0,0,.18);
}
.dashboard-action-icon{
    display:flex;
    height:2.5rem;
    width:2.5rem;
    flex-shrink:0;
    align-items:center;
    justify-content:center;
    border-radius:.8rem;
}
.section-eyebrow{
    display:inline-block;
    font-size:9px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.12em;
    color:#94a3b8;
}
.stat-card{
    display:block;
    border:1px solid rgba(226,232,240,.85);
    border-radius:1.25rem;
    background:#fff;
    padding:1rem;
    box-shadow:0 4px 18px rgba(15,23,42,.04);
    transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease;
}
.stat-card:hover{
    transform:translateY(-4px);
    border-color:rgba(148,163,184,.35);
    box-shadow:0 18px 40px rgba(15,23,42,.09);
}
.stat-label{
    display:block;
    font-size:9px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#94a3b8;
}
.stat-number{
    display:block;
    font-size:1.65rem;
    line-height:1;
    font-weight:900;
    letter-spacing:-.04em;
    color:#0f172a;
}
.stat-icon{
    display:flex;
    height:2.75rem;
    width:2.75rem;
    flex-shrink:0;
    align-items:center;
    justify-content:center;
    border-radius:.9rem;
    box-shadow:0 0 0 1px;
    transition:background .2s ease,color .2s ease,transform .2s ease;
}
.stat-card:hover .stat-icon{transform:scale(1.05)}
.stat-pill{
    display:inline-flex;
    align-items:center;
    border-radius:.55rem;
    padding:.3rem .55rem;
    font-size:9px;
    font-weight:800;
}
.stat-arrow{
    font-size:.9rem;
    font-weight:800;
    opacity:.7;
    transition:transform .2s ease;
}
.stat-card:hover .stat-arrow{transform:translateX(3px)}
.dashboard-panel{
    border:1px solid rgba(226,232,240,.85);
    border-radius:1.25rem;
    background:#fff;
    box-shadow:0 4px 18px rgba(15,23,42,.04);
}
.dashboard-mini-card{
    border:1px solid rgba(226,232,240,.85);
    border-radius:1.1rem;
    background:#fff;
    padding:1rem;
    box-shadow:0 4px 18px rgba(15,23,42,.04);
}
.table-heading{
    padding:.75rem 1.5rem;
    font-size:9px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#94a3b8;
}
.table-cell{
    padding:.9rem 1.5rem;
    font-size:.75rem;
    color:#475569;
}
.task-card{
    position:relative;
    display:block;
    border:1px solid rgba(226,232,240,.9);
    border-left-width:4px;
    border-radius:1rem;
    background:#fff;
    padding:1rem;
    box-shadow:0 3px 15px rgba(15,23,42,.035);
    transition:transform .2s ease,box-shadow .2s ease,background .2s ease;
}
.task-card:hover{
    transform:translateY(-3px);
    background:#fcfdff;
    box-shadow:0 14px 30px rgba(15,23,42,.08);
}
.empty-state{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    min-height:220px;
    border:1px dashed #e2e8f0;
    border-radius:1rem;
    background:rgba(248,250,252,.65);
    padding:2rem;
}
@media(min-width:640px){
    .stat-card{padding:1.25rem}
    .stat-label{font-size:10px}
    .stat-number{font-size:2rem}
    .stat-icon{height:3rem;width:3rem}
}
@media(max-width:639px){
    .dashboard-action{
        min-width:0;
        width:100%;
    }
}
@media(prefers-reduced-motion:reduce){
    .dashboard-page,
    .stat-card,
    .dashboard-action,
    .task-card,
    .stat-icon,
    .stat-arrow{
        animation:none!important;
        transition:none!important;
    }
}
</style>

@if(!$isStaf)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded',function(){
            const canvas=document.getElementById('suratChart');
            if(!canvas)return;

            const ctx=canvas.getContext('2d');
            if(!ctx)return;

            const chartLabels=@json($chartLabels ?? []);
            const dataMasuk=@json($chartDataMasuk ?? []);
            const dataKeluar=@json($chartDataKeluar ?? []);

            const gradientMasuk=ctx.createLinearGradient(0,0,0,320);
            gradientMasuk.addColorStop(0,'rgba(37,99,235,.18)');
            gradientMasuk.addColorStop(1,'rgba(37,99,235,0)');

            const gradientKeluar=ctx.createLinearGradient(0,0,0,320);
            gradientKeluar.addColorStop(0,'rgba(16,185,129,.18)');
            gradientKeluar.addColorStop(1,'rgba(16,185,129,0)');

            new Chart(ctx,{
                type:'line',
                data:{
                    labels:chartLabels,
                    datasets:[
                        {
                            label:'Surat Masuk',
                            data:dataMasuk,
                            borderColor:'#2563eb',
                            backgroundColor:gradientMasuk,
                            borderWidth:2.5,
                            fill:true,
                            tension:.4,
                            pointRadius:3,
                            pointHoverRadius:6,
                            pointBackgroundColor:'#2563eb',
                            pointBorderColor:'#ffffff',
                            pointBorderWidth:2
                        },
                        {
                            label:'Surat Keluar',
                            data:dataKeluar,
                            borderColor:'#10b981',
                            backgroundColor:gradientKeluar,
                            borderWidth:2.5,
                            fill:true,
                            tension:.4,
                            pointRadius:3,
                            pointHoverRadius:6,
                            pointBackgroundColor:'#10b981',
                            pointBorderColor:'#ffffff',
                            pointBorderWidth:2
                        }
                    ]
                },
                options:{
                    responsive:true,
                    maintainAspectRatio:false,
                    interaction:{
                        intersect:false,
                        mode:'index'
                    },
                    animation:{
                        duration:900,
                        easing:'easeOutQuart'
                    },
                    plugins:{
                        legend:{display:false},
                        tooltip:{
                            backgroundColor:'#0f172a',
                            titleColor:'#ffffff',
                            bodyColor:'#cbd5e1',
                            borderColor:'#1e293b',
                            borderWidth:1,
                            titleFont:{size:11,weight:'bold'},
                            bodyFont:{size:11},
                            padding:11,
                            cornerRadius:10,
                            displayColors:true
                        }
                    },
                    scales:{
                        x:{
                            grid:{display:false},
                            border:{display:false},
                            ticks:{
                                color:'#94a3b8',
                                font:{size:10},
                                maxRotation:0,
                                autoSkip:true,
                                maxTicksLimit:12
                            }
                        },
                        y:{
                            beginAtZero:true,
                            border:{display:false},
                            ticks:{
                                precision:0,
                                stepSize:1,
                                color:'#94a3b8',
                                font:{size:10}
                            },
                            grid:{color:'#f1f5f9'}
                        }
                    }
                }
            });
        });
    </script>
@endif

@endsection