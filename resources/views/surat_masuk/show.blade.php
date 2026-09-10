@extends('layouts.app')

@section('title', 'Detail Surat Masuk')

@section('content')

@php
use Illuminate\Support\Carbon;

$status = strtolower(trim((string) ($suratMasuk->status ?? 'baru')));

$statusLabels = [
    'baru' => 'Baru',
    'diproses' => 'Diproses',
    'didisposisikan' => 'Didisposisikan',
    'selesai' => 'Selesai',
    'diarsipkan' => 'Diarsipkan',
];

$statusClasses = [
    'baru' => 'bg-blue-100 text-blue-800 border-blue-300',
    'diproses' => 'bg-amber-100 text-amber-800 border-amber-300',
    'didisposisikan' => 'bg-purple-100 text-purple-800 border-purple-300',
    'selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
    'diarsipkan' => 'bg-slate-200 text-slate-800 border-slate-300',
];

$statusLabel = $statusLabels[$status] ?? ucfirst($status);
$statusClass = $statusClasses[$status] ?? 'bg-slate-200 text-slate-800 border-slate-300';

$tanggalSurat = '-';
$tanggalTerima = '-';

if ($suratMasuk->tanggal_surat) {
    try {
        $tanggalSurat = Carbon::parse($suratMasuk->tanggal_surat)->translatedFormat('d F Y');
    } catch (\Throwable $e) {
        $tanggalSurat = (string) $suratMasuk->tanggal_surat;
    }
}

if ($suratMasuk->tanggal_terima) {
    try {
        $tanggalTerima = Carbon::parse($suratMasuk->tanggal_terima)->translatedFormat('d F Y');
    } catch (\Throwable $e) {
        $tanggalTerima = (string) $suratMasuk->tanggal_terima;
    }
}

$lampiranPath = $suratMasuk->lampiran_file ?? null;
$lampiranUrl = null;
$lampiranExtension = '';

if ($lampiranPath) {
    $lampiranUrl = filter_var($lampiranPath, FILTER_VALIDATE_URL)
        ? $lampiranPath
        : route('surat-masuk.preview-lampiran', $suratMasuk);

    $cleanPath = parse_url($lampiranPath, PHP_URL_PATH) ?: $lampiranPath;
    $lampiranExtension = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
}

$isImage = in_array($lampiranExtension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
$isPdf = $lampiranExtension === 'pdf';

$user = auth()->user();

$userRole = strtolower(trim((string) ($user->role ?? $user->jabatan ?? '')));
if ($userRole === 'staff') {
    $userRole = 'staf';
}

$canManage = in_array($userRole, ['admin', 'pimpinan'], true);
$disposisis = $suratMasuk->disposisi ?? collect();
@endphp

{{-- FLASH MESSAGE --}}
@if(session('success'))
    <div role="alert" class="flex items-start justify-between gap-3 rounded-2xl border-2 border-emerald-200 bg-emerald-50 p-4 text-emerald-800 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="pt-1 text-xs font-semibold sm:text-sm">{{ session('success') }}</span>
        </div>

        <button type="button" onclick="this.closest('[role=alert]')?.remove()" class="rounded-lg p-1.5 text-emerald-400 transition hover:text-emerald-700" aria-label="Tutup">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

@if(session('error'))
    <div role="alert" class="flex items-start justify-between gap-3 rounded-2xl border-2 border-rose-200 bg-rose-50 p-4 text-rose-800 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <span class="pt-1 text-xs font-semibold sm:text-sm">{{ session('error') }}</span>
        </div>

        <button type="button" onclick="this.closest('[role=alert]')?.remove()" class="rounded-lg p-1.5 text-rose-400 transition hover:text-rose-700" aria-label="Tutup">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>
@endif

{{-- HEADER --}}
<div class="rounded-3xl border border-slate-800 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-5 text-white shadow-md sm:p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex min-w-0 items-start gap-3">
            <a href="{{ route('surat-masuk.index') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white backdrop-blur-sm transition hover:bg-white/20" title="Kembali ke daftar surat" aria-label="Kembali ke daftar surat">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>

            <div class="min-w-0">
                <nav class="mb-1 flex flex-wrap items-center gap-2 text-[11px] font-medium text-slate-300">
                    <a href="{{ route('surat-masuk.index') }}" class="transition hover:text-white">Surat Masuk</a>
                    <span>/</span>
                    <span class="text-slate-200">Detail Arsip</span>
                </nav>

                <h1 class="text-lg font-bold tracking-tight sm:text-xl">Detail Surat Masuk</h1>
                <p class="mt-1 text-[11px] text-slate-400 sm:text-xs">
                    Informasi surat, lampiran digital, dan riwayat disposisi.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 border-t border-white/10 pt-3 lg:border-t-0 lg:pt-0">
            @if($canManage && Route::has('surat-masuk.edit'))
                <a href="{{ route('surat-masuk.edit', $suratMasuk) }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-500/30 bg-amber-500/20 px-4 py-2.5 text-xs font-semibold text-amber-300 transition hover:bg-amber-500/30">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Surat
                </a>
            @endif

            @if(Route::has('surat-masuk.cetak-disposisi'))
                <a href="{{ route('surat-masuk.cetak-disposisi', $suratMasuk) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-emerald-500/30 bg-emerald-500/20 px-4 py-2.5 text-xs font-semibold text-emerald-300 transition hover:bg-emerald-500/30">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Disposisi
                </a>
            @endif

            @if(Route::has('surat-masuk.cetak-label'))
                <a href="{{ route('surat-masuk.cetak-label', $suratMasuk) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-blue-500/30 bg-blue-500/20 px-4 py-2.5 text-xs font-semibold text-blue-300 transition hover:bg-blue-500/30">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h10M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                    </svg>
                    Cetak Label
                </a>
            @endif
        </div>
    </div>
</div>

{{-- CONTENT --}}
<div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-3">

    {{-- INFORMASI SURAT --}}
    <div class="space-y-5 lg:col-span-2">

        <div class="rounded-3xl border border-slate-200 bg-gradient-to-b from-white via-slate-50/50 to-slate-100/60 p-5 shadow-md sm:p-6">
            <div class="flex flex-col gap-4 border-b-2 border-slate-200 pb-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <div class="mb-2 flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-indigo-600"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Arsip Surat Masuk</span>
                    </div>

                    <h2 class="break-words text-xl font-black leading-snug text-slate-900 sm:text-2xl">
                        {{ $suratMasuk->perihal ?? 'Tanpa Perihal' }}
                    </h2>

                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-xl border border-indigo-300 bg-indigo-100 px-3 py-1 font-mono text-xs font-bold text-indigo-800">
                            {{ $suratMasuk->nomor_agenda ?? '#' . $suratMasuk->id }}
                        </span>

                        <span class="text-slate-400">•</span>

                        <span class="break-all text-xs text-slate-600">
                            No. Surat:
                            <strong class="font-semibold text-slate-900">
                                {{ $suratMasuk->nomor_surat ?? '-' }}
                            </strong>
                        </span>
                    </div>
                </div>

                <div class="shrink-0">
                    <span class="inline-flex items-center rounded-2xl border-2 px-4 py-2 text-xs font-bold shadow-sm {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>
                </div>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Pengirim</span>
                    <p class="mt-1 break-words text-sm font-bold text-slate-900">{{ $suratMasuk->pengirim ?? '-' }}</p>
                </div>

                <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Kategori Surat</span>
                    <p class="mt-1 break-words text-sm font-bold text-slate-900">{{ $suratMasuk->kategori?->nama_kategori ?? '-' }}</p>
                </div>

                <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Tanggal Surat</span>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $tanggalSurat }}</p>
                </div>

                <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Tanggal Diterima</span>
                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $tanggalTerima }}</p>
                </div>

                <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Diterima Oleh</span>
                    <p class="mt-1 break-words text-sm font-bold text-slate-900">{{ $suratMasuk->penerima?->name ?? '-' }}</p>
                </div>

                <div class="rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Lokasi Arsip Fisik</span>
                    <p class="mt-1 break-words text-sm font-bold text-slate-900">{{ $suratMasuk->lokasi_arsip_fisik ?: '-' }}</p>
                </div>
            </div>

            @if(filled($suratMasuk->ringkasan))
                <div class="mt-5 rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm">
                    <span class="block text-[10px] font-extrabold uppercase tracking-wider text-slate-500">Ringkasan</span>
                    <div class="mt-2 whitespace-pre-line break-words text-sm font-medium leading-relaxed text-slate-800">
                        {{ $suratMasuk->ringkasan }}
                    </div>
                </div>
            @endif
        </div>

        {{-- LAMPIRAN --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-md sm:p-6">
            <div class="mb-4 flex flex-col gap-2 border-b-2 border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-sm font-bold uppercase tracking-wide text-slate-800">Berkas Lampiran Digital</h3>
                    <p class="mt-0.5 text-[11px] text-slate-500">Dokumen yang tersimpan pada arsip surat ini.</p>
                </div>

                @if($lampiranExtension)
                    <span class="inline-flex w-fit items-center rounded-xl border border-slate-200 bg-slate-100 px-3 py-1 font-mono text-[10px] font-bold uppercase text-slate-700">
                        .{{ $lampiranExtension }}
                    </span>
                @endif
            </div>

            @if($lampiranUrl)
                <div class="space-y-4">
                    <div class="flex flex-col gap-2 rounded-2xl border-2 border-slate-200 bg-slate-50 p-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-700">{{ basename($lampiranPath) }}</p>
                            <p class="mt-0.5 text-[10px] text-slate-400">Lampiran tersimpan</p>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row">
                            <a href="{{ $lampiranUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-indigo-700">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Buka Berkas
                            </a>

                            <a href="{{ $lampiranUrl }}" download class="inline-flex items-center justify-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-100">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Unduh
                            </a>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-2xl border-2 border-slate-300 bg-slate-100 p-2">
                        @if($isImage)
                            <div class="flex min-h-[220px] items-center justify-center overflow-hidden rounded-xl bg-white p-2 sm:min-h-[360px]">
                                <img src="{{ $lampiranUrl }}" alt="Lampiran Surat Masuk" class="max-h-[600px] max-w-full rounded-xl object-contain shadow-sm" loading="lazy">
                            </div>
                        @elseif($isPdf)
                            <iframe src="{{ $lampiranUrl }}" title="Pratinjau PDF Surat Masuk" class="h-[520px] w-full rounded-xl border-0 bg-white sm:h-[650px]"></iframe>
                        @else
                            <div class="flex min-h-[260px] flex-col items-center justify-center rounded-xl bg-white px-5 text-center">
                                <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.414l4.414 4.414A2 2 0 0118 8.414V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>

                                <p class="text-sm font-semibold text-slate-700">Pratinjau tidak tersedia</p>
                                <p class="mt-1 text-xs text-slate-400">
                                    Gunakan tombol <strong>Unduh</strong> untuk membuka berkas.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-5 py-12 text-center">
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-400 shadow-sm">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                    </div>

                    <p class="text-sm font-semibold text-slate-700">Tidak ada lampiran digital</p>
                    <p class="mt-1 text-xs text-slate-400">Surat ini belum memiliki berkas lampiran.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- DISPOSISI --}}
    <div class="rounded-3xl border border-slate-200 bg-gradient-to-b from-white via-slate-50/50 to-slate-100/60 p-5 shadow-md sm:p-6">
        <div class="flex items-center justify-between gap-3 border-b-2 border-slate-200 pb-4">
            <div class="min-w-0">
                <h3 class="text-sm font-bold tracking-tight text-slate-900">Riwayat Disposisi</h3>
                <p class="mt-0.5 text-[11px] text-slate-500">Instruksi dan tindak lanjut surat.</p>
            </div>

            @if($canManage && Route::has('disposisi.create'))
                <a href="{{ route('disposisi.create', $suratMasuk) }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-purple-300 bg-purple-100 px-3 py-2 text-xs font-bold text-purple-700 transition hover:bg-purple-200">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat
                </a>
            @endif
        </div>

        <div class="max-h-[650px] space-y-4 overflow-y-auto pr-1 pt-4">
            @forelse($disposisis as $d)
                @php
                    $disposisiStatus = strtolower(trim((string) ($d->status ?? 'menunggu')));

                    $disposisiStatusLabels = [
                        'menunggu' => 'Menunggu',
                        'diproses' => 'Diproses',
                        'selesai' => 'Selesai',
                        'ditolak' => 'Ditolak',
                    ];

                    $disposisiClasses = [
                        'menunggu' => 'bg-purple-100 text-purple-800 border-purple-300',
                        'diproses' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'ditolak' => 'bg-rose-100 text-rose-800 border-rose-300',
                    ];

                    $disposisiLabel = $disposisiStatusLabels[$disposisiStatus] ?? ucfirst($disposisiStatus);
                    $disposisiClass = $disposisiClasses[$disposisiStatus] ?? 'bg-slate-100 text-slate-700 border-slate-300';

                    $tanggalDisposisi = '-';

                    if ($d->created_at) {
                        try {
                            $tanggalDisposisi = Carbon::parse($d->created_at)->translatedFormat('d/m/Y H:i');
                        } catch (\Throwable $e) {
                            $tanggalDisposisi = (string) $d->created_at;
                        }
                    }

                    $batasWaktu = null;

                    if ($d->batas_waktu) {
                        try {
                            $batasWaktu = Carbon::parse($d->batas_waktu)->translatedFormat('d/m/Y');
                        } catch (\Throwable $e) {
                            $batasWaktu = (string) $d->batas_waktu;
                        }
                    }
                @endphp

                <div class="relative pl-6">
                    <div class="absolute bottom-0 left-2.5 top-2 w-0.5 bg-purple-200"></div>
                    <div class="absolute left-1 top-1 h-4 w-4 rounded-full border-4 border-purple-100 bg-purple-600"></div>

                    <div class="space-y-2">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold text-slate-900">
                                    <span class="break-words">{{ $d->dari?->name ?? '-' }}</span>

                                    <svg class="h-3 w-3 shrink-0 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>

                                    <span class="break-words text-purple-700">{{ $d->kepada?->name ?? '-' }}</span>
                                </div>
                            </div>

                            <span class="inline-flex w-fit shrink-0 items-center rounded-lg border px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider {{ $disposisiClass }}">
                                {{ $disposisiLabel }}
                            </span>
                        </div>

                        <div class="rounded-2xl border-2 border-slate-200 bg-white p-3.5 text-xs font-medium leading-relaxed text-slate-700 shadow-sm">
                            @if(filled($d->isi_disposisi))
                                <div class="whitespace-pre-line break-words">{{ $d->isi_disposisi }}</div>
                            @elseif(filled($d->instruksi))
                                <div class="whitespace-pre-line break-words">{{ $d->instruksi }}</div>
                            @elseif(filled($d->catatan))
                                <div class="whitespace-pre-line break-words">{{ $d->catatan }}</div>
                            @else
                                <span class="text-slate-400">Tidak ada instruksi/catatan.</span>
                            @endif
                        </div>

                        @if($batasWaktu)
                            <div class="text-[10px] font-semibold text-amber-600">
                                Batas waktu: {{ $batasWaktu }}
                            </div>
                        @endif

                        <div class="text-[10px] font-semibold text-slate-400">
                            {{ $tanggalDisposisi }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full border-2 border-slate-200 bg-white text-slate-400">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>

                    <p class="text-xs font-medium text-slate-500">
                        Belum ada riwayat disposisi.
                    </p>

                    @if($canManage && Route::has('disposisi.create'))
                        <a href="{{ route('disposisi.create', $suratMasuk) }}" class="mt-3 inline-flex items-center gap-1.5 rounded-xl bg-purple-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-purple-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                            Buat Disposisi
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        <div class="mt-5 border-t border-slate-200 pt-3 text-center">
            <span class="text-[10px] font-bold uppercase tracking-wide text-slate-400">
                Sistem Kendali Surat Masuk
            </span>
        </div>
    </div>
</div>

@endsection