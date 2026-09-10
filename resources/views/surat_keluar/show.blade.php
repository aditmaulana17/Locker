@extends('layouts.app')

@section('title', 'Detail Surat Keluar')

@section('content')
@php
    $status = strtolower(trim((string) ($suratKeluar->status ?? 'draft')));
    if ($status === 'draf') {
        $status = 'draft';
    }

    $statusLabels = [
        'draft' => 'Draft',
        'diproses' => 'Diproses',
        'disetujui' => 'Disetujui',
        'dikirim' => 'Dikirim',
        'diarsipkan' => 'Diarsipkan',
    ];

    $statusBadgeClasses = [
        'draft' => 'bg-slate-100 text-slate-700 border-slate-300',
        'diproses' => 'bg-amber-100 text-amber-800 border-amber-300',
        'disetujui' => 'bg-blue-100 text-blue-800 border-blue-300',
        'dikirim' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        'diarsipkan' => 'bg-purple-100 text-purple-800 border-purple-300',
    ];

    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
    $statusBadgeClass = $statusBadgeClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-300';

    $tanggalSurat = '-';
    $tanggalKeluar = '-';

    try {
        if ($suratKeluar->tanggal_surat) {
            $tanggalSurat = \Illuminate\Support\Carbon::parse($suratKeluar->tanggal_surat)->format('d/m/Y');
        }
    } catch (\Throwable $e) {
        $tanggalSurat = '-';
    }

    try {
        if ($suratKeluar->tanggal_keluar) {
            $tanggalKeluar = \Illuminate\Support\Carbon::parse($suratKeluar->tanggal_keluar)->format('d/m/Y');
        }
    } catch (\Throwable $e) {
        $tanggalKeluar = '-';
    }

    $tujuanSurat = trim((string) ($suratKeluar->pengirim ?? ''));
    $tujuanSurat = $tujuanSurat !== '' ? $tujuanSurat : '-';

    $nomorSurat = trim((string) ($suratKeluar->nomor_surat ?? ''));
    $nomorSurat = $nomorSurat !== '' ? $nomorSurat : '-';

    $perihal = trim((string) ($suratKeluar->perihal ?? ''));
    $perihal = $perihal !== '' ? $perihal : 'Tanpa Perihal';

    $ringkasan = trim((string) ($suratKeluar->ringkasan ?? ''));
    $kategoriNama = $suratKeluar->kategori?->nama_kategori ?? '-';
    $pembuatNama = $suratKeluar->pembuat?->name ?? '-';
    $penandatanganNama = $suratKeluar->penandatangan?->name ?? '-';

    $lampiranPath = $suratKeluar->lampiran_file ?? null;
    $lampiranUrl = null;

    if (!empty($lampiranPath)) {
        if (filter_var($lampiranPath, FILTER_VALIDATE_URL)) {
            $lampiranUrl = $lampiranPath;
        } elseif (\Illuminate\Support\Facades\Route::has('surat-keluar.preview-lampiran')) {
            $lampiranUrl = route('surat-keluar.preview-lampiran', $suratKeluar);
        }
    }

    $lampiranExtension = !empty($lampiranPath)
        ? strtolower(pathinfo($lampiranPath, PATHINFO_EXTENSION))
        : '';

    $lampiranNama = !empty($lampiranPath) ? basename($lampiranPath) : null;
@endphp

<div class="mx-auto w-full max-w-7xl space-y-4 px-4 py-5 sm:space-y-5 sm:px-6 lg:px-8">
    {{-- SUCCESS --}}
    @if (session('success'))
        <div id="success-alert" class="flex items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm" role="alert">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-xs font-medium sm:text-sm">{{ session('success') }}</span>
            </div>
            <button
                type="button"
                onclick="document.getElementById('success-alert')?.remove()"
                class="shrink-0 rounded-lg p-1 text-emerald-400 transition hover:bg-emerald-100/50 hover:text-emerald-700"
                aria-label="Tutup pemberitahuan"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- ERROR --}}
    @if (session('error'))
        <div id="error-alert" class="flex items-center justify-between gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-rose-800 shadow-sm" role="alert">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-100">
                    <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-xs font-medium sm:text-sm">{{ session('error') }}</span>
            </div>
            <button
                type="button"
                onclick="document.getElementById('error-alert')?.remove()"
                class="shrink-0 rounded-lg p-1 text-rose-400 transition hover:bg-rose-100/50 hover:text-rose-700"
                aria-label="Tutup pemberitahuan"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="flex flex-col justify-between gap-4 rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 p-5 text-white shadow-md sm:flex-row sm:items-center sm:p-6">
        <div class="flex min-w-0 items-center gap-3 sm:gap-4">
            <a
                href="{{ route('surat-keluar.index') }}"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white backdrop-blur-sm transition hover:bg-white/20"
                title="Kembali"
                aria-label="Kembali ke surat keluar"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div class="min-w-0">
                <h1 class="text-lg font-bold tracking-tight sm:text-xl">Detail Surat Keluar</h1>
                <p class="mt-0.5 text-xs text-slate-300 sm:text-sm">
                    Informasi lengkap dan arsip dokumen surat keluar.
                </p>
            </div>
        </div>

        {{-- ACTION --}}
        <div class="flex w-full items-center gap-2 sm:w-auto">
            @if (\Illuminate\Support\Facades\Route::has('surat-keluar.edit'))
                <a
                    href="{{ route('surat-keluar.edit', $suratKeluar) }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-amber-500/30 bg-amber-500/20 px-4 py-2.5 text-xs font-semibold text-amber-300 shadow-sm backdrop-blur-sm transition hover:bg-amber-500/30 sm:w-auto"
                >
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Arsip
                </a>
            @endif
        </div>
    </div>

    {{-- MAIN CARD --}}
    <div class="space-y-5 rounded-3xl border border-slate-200 bg-gradient-to-b from-white via-slate-50/50 to-slate-100/60 p-5 shadow-md sm:p-7">
        {{-- TITLE + STATUS --}}
        <div class="flex flex-col justify-between gap-4 border-b-2 border-slate-200 pb-5 sm:flex-row sm:items-start">
            <div class="min-w-0 space-y-2">
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-sm shadow-emerald-500/50"></span>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">
                        Arsip Surat Keluar
                    </span>
                </div>

                <h2 class="break-words text-xl font-black leading-snug tracking-tight text-slate-900 sm:text-2xl">
                    {{ $perihal }}
                </h2>

                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center rounded-xl border border-emerald-300 bg-emerald-100 px-3 py-1 font-mono text-xs font-bold text-emerald-800">
                        #{{ $suratKeluar->id }}
                    </span>
                    <span class="text-slate-400">&bull;</span>
                    <span class="break-all text-xs text-slate-600">
                        No. Surat:
                        <strong class="font-semibold text-slate-900">{{ $nomorSurat }}</strong>
                    </span>
                </div>
            </div>

            {{-- STATUS --}}
            <div class="shrink-0">
                <span class="inline-flex items-center rounded-2xl border-2 px-4 py-2 text-xs font-bold shadow-sm {{ $statusBadgeClass }}">
                    {{ $statusLabel }}
                </span>
            </div>
        </div>

        {{-- METADATA --}}
        <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                <dt class="mb-2 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Tujuan Surat</dt>
                <dd class="break-words text-sm font-bold text-slate-900">{{ $tujuanSurat }}</dd>
            </div>

            <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                <dt class="mb-2 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Kategori Surat</dt>
                <dd class="break-words text-sm font-bold text-slate-900">{{ $kategoriNama }}</dd>
            </div>

            <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                <dt class="mb-2 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Tanggal Surat</dt>
                <dd class="text-sm font-bold text-slate-900">{{ $tanggalSurat }}</dd>
            </div>

            <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                <dt class="mb-2 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Tanggal Keluar</dt>
                <dd class="text-sm font-bold text-slate-900">{{ $tanggalKeluar }}</dd>
            </div>

            <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm sm:col-span-2">
                <dt class="mb-2 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Dibuat Oleh</dt>
                <dd class="break-words text-sm font-bold text-slate-900">{{ $pembuatNama }}</dd>
            </div>

            <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm sm:col-span-2">
                <dt class="mb-2 text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Ditandatangani Oleh</dt>
                <dd class="break-words text-sm font-bold text-slate-900">{{ $penandatanganNama }}</dd>
            </div>
        </dl>

        {{-- PERIHAL --}}
        <div class="border-t-2 border-slate-200 pt-4">
            <div class="space-y-2 rounded-2xl border-2 border-slate-200 bg-white p-5 shadow-sm">
                <span class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500">Perihal Surat</span>
                <div class="whitespace-pre-line break-words text-sm font-medium leading-relaxed text-slate-800">
                    {{ $perihal }}
                </div>
            </div>
        </div>

        {{-- RINGKASAN --}}
        @if ($ringkasan !== '')
            <div>
                <div class="space-y-2 rounded-2xl border-2 border-slate-200 bg-white p-5 shadow-sm">
                    <span class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500">
                        Ringkasan Isi Surat
                    </span>
                    <div class="whitespace-pre-line break-words text-sm font-medium leading-relaxed text-slate-700">
                        {{ $ringkasan }}
                    </div>
                </div>
            </div>
        @endif

        {{-- LAMPIRAN --}}
        <div class="space-y-4 border-t-2 border-slate-200 pt-5">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                        Berkas Lampiran Digital
                    </span>
                    @if ($lampiranNama)
                        <span class="mt-1 block break-all text-[11px] text-slate-400">
                            {{ $lampiranNama }}
                        </span>
                    @endif
                </div>
            </div>

            @if (!empty($lampiranPath) && !empty($lampiranUrl))
                {{-- ACTION --}}
                <div class="flex flex-wrap items-center gap-2.5">
                    <a
                        href="{{ $lampiranUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Buka Lampiran
                    </a>

                    <a
                        href="{{ $lampiranUrl }}"
                        download
                        class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Unduh Berkas
                    </a>
                </div>

                {{-- VIEWER --}}
                <div class="overflow-hidden rounded-2xl border-2 border-slate-300 bg-slate-900/10 p-3">
                    @if (in_array($lampiranExtension, ['jpg', 'jpeg', 'png'], true))
                        <div class="py-2 text-center">
                            <img
                                src="{{ $lampiranUrl }}"
                                alt="Lampiran Surat Keluar"
                                class="mx-auto max-h-[600px] rounded-xl border border-white object-contain shadow-md"
                                loading="lazy"
                            >
                        </div>
                    @elseif ($lampiranExtension === 'pdf')
                        <iframe
                            src="{{ $lampiranUrl }}"
                            class="h-[600px] w-full rounded-xl border-0 bg-white shadow-sm"
                            title="Pratinjau PDF Surat Keluar"
                            loading="lazy"
                        ></iframe>
                    @else
                        <div class="rounded-xl border border-slate-200 bg-white py-12 text-center">
                            <svg class="mx-auto mb-3 h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-xs font-medium text-slate-700">
                                Pratinjau langsung tidak tersedia untuk format
                                <code class="rounded bg-slate-100 px-1.5 py-0.5 font-bold text-slate-900">
                                    .{{ $lampiranExtension ?: 'dokumen' }}
                                </code>
                            </p>
                            <p class="mt-1 text-[11px] text-slate-500">
                                Gunakan tombol buka atau unduh untuk melihat berkas.
                            </p>
                        </div>
                    @endif
                </div>
            @else
                <div class="flex w-full items-center justify-center gap-3 rounded-2xl border-2 border-slate-200 bg-white p-6 text-slate-500 shadow-sm">
                    <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                    <span class="text-xs font-medium">
                        Tidak ada berkas digital yang dilampirkan pada surat ini.
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection