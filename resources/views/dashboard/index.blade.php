@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
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

    if ($role === 'staff') {
        $role = 'staf';
    }

    $isStaf = $role === 'staf';
    $isAdmin = $role === 'admin';
    $isPimpinan = $role === 'pimpinan';
@endphp

<div class="space-y-5 sm:space-y-6 lg:space-y-7">

    {{-- ============================================================
       WELCOME BANNER
    ============================================================ --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 p-5 text-white shadow-xl shadow-blue-500/10 sm:rounded-3xl sm:p-7 lg:p-8">

        <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            <div class="min-w-0 space-y-2">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[10px] font-semibold text-blue-100 backdrop-blur-md sm:text-xs">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span>
                    Sistem E-Arsip Aktif
                </div>

                <h1 class="text-xl font-bold leading-snug tracking-tight sm:text-2xl lg:text-3xl">
                    Selamat Datang Kembali,
                    {{ $user->name ?? 'Pengguna' }}!
                </h1>

                <p class="max-w-2xl text-xs leading-relaxed text-blue-100/90 sm:text-sm">
                    @if($isStaf)
                        Kelola tugas disposisi surat masuk yang telah diteruskan kepada Anda dengan cepat dan terstruktur.
                    @else
                        Kelola arsip surat masuk, surat keluar, dan disposisi dokumen dengan cepat, terstruktur, dan aman.
                    @endif
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap gap-2.5 sm:gap-3">

                @if(!$isStaf)

                    <a
                        href="{{ route('surat-masuk.create') }}"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-white px-3.5 py-2.5 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-50 sm:flex-none sm:px-4"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Surat Masuk</span>
                    </a>

                    <a
                        href="{{ route('surat-keluar.create') }}"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-white/20 bg-blue-500/30 px-3.5 py-2.5 text-xs font-semibold text-white backdrop-blur-md transition hover:bg-blue-500/40 sm:flex-none sm:px-4"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Surat Keluar</span>
                    </a>

                @else

                    <a
                        href="{{ route('disposisi.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-xs font-semibold text-blue-700 shadow-sm transition hover:bg-blue-50"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 6h6m-6 4h4"/>
                        </svg>
                        <span>Daftar Disposisi Tugas Saya</span>
                    </a>

                @endif

            </div>
        </div>

        <div class="pointer-events-none absolute -bottom-10 -right-10 h-64 w-64 rounded-full bg-white/5 blur-2xl"></div>
        <div class="pointer-events-none absolute -top-16 right-1/4 h-40 w-40 rounded-full bg-white/5 blur-3xl"></div>
    </div>

    {{-- ============================================================
       SCORECARDS ADMIN / PIMPINAN
    ============================================================ --}}
    @if(!$isStaf)

        <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">

            {{-- SURAT MASUK --}}
            <a
                href="{{ route('surat-masuk.index') }}"
                class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5"
            >
                <div class="flex items-start justify-between gap-3">

                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-xs">
                        Surat Masuk
                    </span>

                    <div class="rounded-xl bg-blue-50 p-2 text-blue-600 transition group-hover:bg-blue-600 group-hover:text-white sm:p-2.5">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293H8.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 004.586 13H4"/>
                        </svg>
                    </div>

                </div>

                <div class="mt-4 flex items-end justify-between gap-2">
                    <span class="text-2xl font-extrabold tracking-tight text-slate-800 sm:text-3xl">
                        {{ $totalSuratMasuk ?? 0 }}
                    </span>

                    <span class="rounded-lg bg-emerald-50 px-2 py-1 text-[10px] font-semibold text-emerald-600 sm:px-2.5 sm:text-xs">
                        Arsip Masuk
                    </span>
                </div>
            </a>

            {{-- SURAT KELUAR --}}
            <a
                href="{{ route('surat-keluar.index') }}"
                class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5"
            >
                <div class="flex items-start justify-between gap-3">

                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-xs">
                        Surat Keluar
                    </span>

                    <div class="rounded-xl bg-emerald-50 p-2 text-emerald-600 transition group-hover:bg-emerald-600 group-hover:text-white sm:p-2.5">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </div>

                </div>

                <div class="mt-4 flex items-end justify-between gap-2">
                    <span class="text-2xl font-extrabold tracking-tight text-slate-800 sm:text-3xl">
                        {{ $totalSuratKeluar ?? 0 }}
                    </span>

                    <span class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-semibold text-blue-600 sm:px-2.5 sm:text-xs">
                        Arsip Keluar
                    </span>
                </div>
            </a>

            {{-- BELUM DIPROSES --}}
            <a
                href="{{ route('surat-masuk.index', ['status' => ['baru']]) }}"
                class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5"
            >
                <div class="flex items-start justify-between gap-3">

                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-xs">
                        Belum Diproses
                    </span>

                    <div class="rounded-xl bg-amber-50 p-2 text-amber-600 transition group-hover:bg-amber-500 group-hover:text-white sm:p-2.5">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0a9 9 0 0118 0z"/>
                        </svg>
                    </div>

                </div>

                <div class="mt-4 flex items-end justify-between gap-2">
                    <span class="text-2xl font-extrabold tracking-tight text-slate-800 sm:text-3xl">
                        {{ $suratPending ?? 0 }}
                    </span>

                    <span class="rounded-lg bg-amber-50 px-2 py-1 text-[10px] font-semibold text-amber-600 sm:px-2.5 sm:text-xs">
                        Pending
                    </span>
                </div>
            </a>

            {{-- SELESAI --}}
            <a
                href="{{ route('surat-masuk.index', ['status' => ['selesai']]) }}"
                class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5"
            >
                <div class="flex items-start justify-between gap-3">

                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-xs">
                        Selesai Diproses
                    </span>

                    <div class="rounded-xl bg-teal-50 p-2 text-teal-600 transition group-hover:bg-teal-600 group-hover:text-white sm:p-2.5">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0a9 9 0 11-18 0z"/>
                        </svg>
                    </div>

                </div>

                <div class="mt-4 flex items-end justify-between gap-2">
                    <span class="text-2xl font-extrabold tracking-tight text-slate-800 sm:text-3xl">
                        {{ $suratSelesai ?? 0 }}
                    </span>

                    <span class="rounded-lg bg-teal-50 px-2 py-1 text-[10px] font-semibold text-teal-600 sm:px-2.5 sm:text-xs">
                        Selesai
                    </span>
                </div>
            </a>

        </div>

    @else

        {{-- ========================================================
           SCORECARDS STAF
        ========================================================= --}}
        <div class="grid grid-cols-2 gap-3 sm:gap-4">

            {{-- MENUNGGU --}}
            <a
                href="{{ route('disposisi.index', ['status' => ['menunggu']]) }}"
                class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5"
            >
                <div class="flex items-start justify-between gap-3">

                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-xs">
                        Disposisi Masuk
                    </span>

                    <div class="rounded-xl bg-purple-50 p-2 text-purple-600 transition group-hover:bg-purple-600 group-hover:text-white sm:p-2.5">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4M4 17h12m0 0l-4-4m4 4l-4 4"/>
                        </svg>
                    </div>

                </div>

                <div class="mt-4 flex items-end justify-between gap-2">
                    <span class="text-2xl font-extrabold tracking-tight text-slate-800 sm:text-3xl">
                        {{ $disposisiMenunggu ?? 0 }}
                    </span>

                    <span class="rounded-lg bg-purple-50 px-2 py-1 text-[10px] font-semibold text-purple-600 sm:text-xs">
                        Tindak Lanjut
                    </span>
                </div>
            </a>

            {{-- SELESAI --}}
            <a
                href="{{ route('disposisi.index', ['status' => ['selesai']]) }}"
                class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5"
            >
                <div class="flex items-start justify-between gap-3">

                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-xs">
                        Disposisi Selesai
                    </span>

                    <div class="rounded-xl bg-teal-50 p-2 text-teal-600 transition group-hover:bg-teal-600 group-hover:text-white sm:p-2.5">
                        <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0a9 9 0 0118 0z"/>
                        </svg>
                    </div>

                </div>

                <div class="mt-4 flex items-end justify-between gap-2">
                    <span class="text-2xl font-extrabold tracking-tight text-slate-800 sm:text-3xl">
                        {{ $disposisiSelesai ?? 0 }}
                    </span>

                    <span class="rounded-lg bg-teal-50 px-2 py-1 text-[10px] font-semibold text-teal-600 sm:text-xs">
                        Tuntas
                    </span>
                </div>
            </a>

        </div>

    @endif


    {{-- ============================================================
       KONTEN UTAMA
    ============================================================ --}}
    @if(!$isStaf)

        {{-- ========================================================
           ADMIN / PIMPINAN
           HANYA STATISTIK + SURAT TERBARU
        ========================================================= --}}
        <div class="grid grid-cols-1 gap-5">

            {{-- CHART --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-6">

                <div class="mb-5 flex flex-col gap-3 sm:mb-6 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <h2 class="text-sm font-bold text-slate-800 sm:text-base">
                            Statistik Surat 12 Bulan Terakhir
                        </h2>

                        <p class="mt-0.5 text-[11px] text-slate-500 sm:text-xs">
                            Grafik perbandingan volume surat masuk dan surat keluar.
                        </p>
                    </div>

                    <div class="flex items-center gap-4 text-[11px] font-medium text-slate-600 sm:text-xs">

                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full bg-blue-600"></span>
                            Masuk
                        </span>

                        <span class="flex items-center gap-1.5">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            Keluar
                        </span>

                    </div>

                </div>

                <div class="relative h-64 w-full sm:h-72 lg:h-80">
                    <canvas id="suratChart"></canvas>
                </div>

            </div>

        </div>

    @else

        {{-- ========================================================
           STAF
           HANYA DISPOSISI TUGAS UNTUK SAYA
        ========================================================= --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm">

            <div class="border-b border-slate-100 p-4 sm:p-6">

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex min-w-0 items-center gap-2.5">

                        <div class="shrink-0 rounded-xl bg-blue-50 p-2 text-blue-600">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 6h6m-6 4h4"/>
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <h2 class="truncate text-sm font-bold text-slate-800 sm:text-base">
                                Disposisi Tugas Untuk Saya
                            </h2>

                            <p class="mt-0.5 text-[11px] text-slate-500 sm:text-xs">
                                Daftar tugas disposisi surat masuk yang perlu Anda tindak lanjuti.
                            </p>
                        </div>

                    </div>

                    <a
                        href="{{ route('disposisi.index') }}"
                        class="shrink-0 text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline"
                    >
                        Lihat Semua →
                    </a>

                </div>

            </div>

            <div class="p-4 sm:p-6">

                <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-3">

                    @forelse($listDisposisi ?? [] as $d)

                        @php
                            $status = strtolower(
                                trim(
                                    (string) ($d->status ?? 'menunggu')
                                )
                            );

                            $stConfig = [
                                'menunggu' => [
                                    'bg' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'border' => 'border-l-amber-500',
                                ],
                                'diproses' => [
                                    'bg' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'border' => 'border-l-blue-500',
                                ],
                                'selesai' => [
                                    'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'border' => 'border-l-emerald-500',
                                ],
                            ];

                            $currentConfig =
                                $stConfig[$status]
                                ?? [
                                    'bg' => 'bg-slate-50 text-slate-700 border-slate-200',
                                    'border' => 'border-l-slate-400',
                                ];
                        @endphp

                        <a
                            href="{{ route('disposisi.show', $d->id) }}"
                            class="group relative block rounded-xl border border-slate-200/80 border-l-4 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:bg-slate-50 hover:shadow-md {{ $currentConfig['border'] }}"
                        >

                            <div class="flex items-start justify-between gap-3">

                                <h3 class="min-w-0 flex-1 truncate text-xs font-bold text-slate-800 transition group-hover:text-blue-600 sm:text-sm">
                                    {{ $d->suratMasuk->perihal ?? 'Surat Disposisi' }}
                                </h3>

                                <span class="shrink-0 rounded-full border px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide {{ $currentConfig['bg'] }}">
                                    {{ ucfirst($status) }}
                                </span>

                            </div>

                            <div class="mt-3 space-y-1.5 text-[11px] text-slate-500">

                                <div class="flex items-center gap-1.5">

                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0a4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>

                                    <span>
                                        Dari:
                                        <strong class="text-slate-700">
                                            {{ $d->dari->name ?? $d->dari->nama ?? '-' }}
                                        </strong>
                                    </span>

                                </div>

                                <div class="flex items-center gap-1.5">

                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h4m-6 5h10a2 2 0 002-2V7.414a1 1 0 00-.293-.707l-4.414-4.414A1 1 0 0013.586 2H7a2 2 0 00-2 2v15a2 2 0 002 2z"/>
                                    </svg>

                                    <span class="font-mono">
                                        {{ $d->suratMasuk->nomor_agenda ?? '-' }}
                                    </span>

                                </div>

                                @if($d->batas_waktu)

                                    <div class="flex items-center gap-1.5">

                                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0a9 9 0 0118 0z"/>
                                        </svg>

                                        <span>
                                            Batas:
                                            <strong class="text-slate-700">
                                                {{ optional($d->batas_waktu)->format('d M Y') }}
                                            </strong>
                                        </span>

                                    </div>

                                @endif

                            </div>

                        </a>

                    @empty

                        <div class="md:col-span-2 xl:col-span-3">

                            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/60 px-6 py-12 text-center">

                                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>

                                <p class="mt-3 text-xs font-bold text-slate-700">
                                    Tidak ada tugas baru
                                </p>

                                <p class="mt-1 max-w-md text-[11px] leading-relaxed text-slate-400">
                                    Belum ada disposisi surat masuk yang perlu ditindaklanjuti.
                                </p>

                            </div>

                        </div>

                    @endforelse

                </div>

            </div>

            <div class="border-t border-slate-100 p-4 sm:p-5">

                <a
                    href="{{ route('disposisi.index') }}"
                    class="block w-full rounded-xl bg-slate-100 px-4 py-2.5 text-center text-xs font-bold text-slate-700 transition hover:bg-blue-600 hover:text-white"
                >
                    Kelola Semua Disposisi →
                </a>

            </div>

        </div>

    @endif


    {{-- ============================================================
       SURAT MASUK TERBARU
       HANYA ADMIN / PIMPINAN
    ============================================================ --}}
    @if(!$isStaf)

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-6">

                <div>
                    <h2 class="text-sm font-bold text-slate-800 sm:text-base">
                        Surat Masuk Terbaru
                    </h2>

                    <p class="mt-0.5 text-[11px] text-slate-500 sm:text-xs">
                        Daftar arsip surat yang baru diterima ke sistem.
                    </p>
                </div>

                <a
                    href="{{ route('surat-masuk.index') }}"
                    class="self-start rounded-xl bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-200 sm:self-auto"
                >
                    Lihat Semua
                </a>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full border-collapse whitespace-nowrap text-left">

                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-[10px] font-bold uppercase tracking-wider text-slate-400 sm:text-[11px]">

                            <th class="px-4 py-3 sm:px-6">
                                Nomor Agenda
                            </th>

                            <th class="px-4 py-3 sm:px-6">
                                Perihal
                            </th>

                            <th class="px-4 py-3 sm:px-6">
                                Instansi Pengirim
                            </th>

                            <th class="px-4 py-3 sm:px-6">
                                Kategori
                            </th>

                            <th class="px-4 py-3 sm:px-6">
                                Status
                            </th>

                            <th class="px-4 py-3 text-right sm:px-6">
                                Aksi
                            </th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">

                        @forelse($suratMasukTerbaru ?? [] as $sm)

                            @php
                                $currentStatus = strtolower(
                                    trim(
                                        (string) ($sm->status ?? 'baru')
                                    )
                                );

                                $statusClasses = [
                                    'baru' => 'bg-blue-50 text-blue-600 border-blue-200',
                                    'proses' => 'bg-amber-50 text-amber-600 border-amber-200',
                                    'diproses' => 'bg-amber-50 text-amber-600 border-amber-200',
                                    'didisposisikan' => 'bg-purple-50 text-purple-600 border-purple-200',
                                    'selesai' => 'bg-emerald-50 text-emerald-600 border-emerald-200',
                                    'diarsipkan' => 'bg-slate-100 text-slate-600 border-slate-200',
                                ];

                                $badgeStyle =
                                    $statusClasses[$currentStatus]
                                    ?? 'bg-slate-50 text-slate-600 border-slate-200';
                            @endphp

                            <tr class="transition hover:bg-slate-50/60">

                                <td class="px-4 py-3.5 font-mono font-bold text-blue-600 sm:px-6">
                                    {{ $sm->nomor_agenda ?? '-' }}
                                </td>

                                <td class="max-w-xs truncate px-4 py-3.5 font-semibold text-slate-800 sm:px-6">
                                    {{ $sm->perihal ?? '-' }}
                                </td>

                                <td class="px-4 py-3.5 sm:px-6">
                                    {{ $sm->pengirim ?? '-' }}
                                </td>

                                <td class="px-4 py-3.5 sm:px-6">
                                    {{ $sm->kategori->nama_kategori ?? '-' }}
                                </td>

                                <td class="px-4 py-3.5 sm:px-6">
                                    <span class="rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $badgeStyle }}">
                                        {{ ucfirst($currentStatus) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3.5 text-right sm:px-6">

                                    <a
                                        href="{{ route('surat-masuk.show', $sm->id) }}"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800"
                                    >
                                        Detail →
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td
                                    colspan="6"
                                    class="px-4 py-8 text-center italic text-slate-400 sm:px-6"
                                >
                                    Belum ada data surat masuk terbaru.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    @endif

</div>


{{-- ================================================================
   CHART JS
   HANYA ADMIN / PIMPINAN
================================================================ --}}
@if(!$isStaf)

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('suratChart');

        if (!canvas) {
            return;
        }

        const ctx = canvas.getContext('2d');

        if (!ctx) {
            return;
        }

        const chartLabels =
            @json($chartLabels ?? []);

        const dataMasuk =
            @json($chartDataMasuk ?? []);

        const dataKeluar =
            @json($chartDataKeluar ?? []);

        const gradientMasuk =
            ctx.createLinearGradient(
                0,
                0,
                0,
                320
            );

        gradientMasuk.addColorStop(
            0,
            'rgba(37, 99, 235, 0.20)'
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
            'rgba(16, 185, 129, 0.20)'
        );

        gradientKeluar.addColorStop(
            1,
            'rgba(16, 185, 129, 0)'
        );

        new Chart(ctx, {
            type: 'line',

            data: {
                labels: chartLabels,

                datasets: [
                    {
                        label: 'Surat Masuk',
                        data: dataMasuk,
                        borderColor: '#2563eb',
                        backgroundColor: gradientMasuk,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Surat Keluar',
                        data: dataKeluar,
                        borderColor: '#10b981',
                        backgroundColor: gradientKeluar,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2
                    }
                ]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                interaction: {
                    intersect: false,
                    mode: 'index'
                },

                plugins: {
                    legend: {
                        display: false
                    },

                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: {
                            size: 11,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 11
                        },
                        padding: 10,
                        cornerRadius: 8
                    }
                },

                scales: {
                    x: {
                        grid: {
                            display: false
                        },

                        ticks: {
                            color: '#94a3b8',

                            font: {
                                size: 10
                            },

                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 12
                        }
                    },

                    y: {
                        beginAtZero: true,

                        ticks: {
                            precision: 0,
                            stepSize: 1,
                            color: '#94a3b8',

                            font: {
                                size: 10
                            }
                        },

                        grid: {
                            color: '#f1f5f9'
                        }
                    }
                }
            }
        });
    });
    </script>

@endif

@endsection