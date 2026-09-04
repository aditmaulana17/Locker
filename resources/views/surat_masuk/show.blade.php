@extends('layouts.app')

@section('title', 'Detail Surat Masuk')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6">

    {{-- Alert Success --}}
    @if(session('success'))
        <div class="flex items-center justify-between p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm transition duration-200">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-emerald-100 rounded-xl text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-700 p-1.5 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    {{-- Alert Error --}}
    @if(session('error'))
        <div class="flex items-center justify-between p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm transition duration-200">
            <div class="flex items-center gap-3">
                <div class="p-1.5 bg-rose-100 rounded-xl text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs sm:text-sm font-semibold">{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-700 p-1.5 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    {{-- Header Page & Actions (Dengan Gradien) --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 p-6 sm:p-7 rounded-3xl border border-slate-800 shadow-md text-white">
        <div class="flex items-center gap-4">
            <a href="{{ route('surat-masuk.index') }}" class="w-10 h-10 rounded-2xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition shrink-0 backdrop-blur-sm" title="Kembali ke Daftar Surat">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-300 font-medium mb-1">
                    <a href="{{ route('surat-masuk.index') }}" class="hover:text-white transition">Surat Masuk</a>
                    <span>/</span>
                    <span class="text-slate-200">Detail Arsip</span>
                </nav>
                <h1 class="text-lg sm:text-xl font-bold tracking-tight">Detail Surat Masuk</h1>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 pt-3 md:pt-0 border-t md:border-t-0 border-white/10">
            @if(Route::has('surat-masuk.edit'))
                <a href="{{ route('surat-masuk.edit', $suratMasuk) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 rounded-2xl transition border border-amber-500/30 backdrop-blur-sm shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit Surat</span>
                </a>
            @endif

            @if(Route::has('surat-masuk.cetak-disposisi'))
                <a href="{{ route('surat-masuk.cetak-disposisi', $suratMasuk) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold bg-emerald-500/20 text-emerald-300 hover:bg-emerald-500/30 rounded-2xl transition border border-emerald-500/30 backdrop-blur-sm shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Cetak Disposisi</span>
                </a>
            @endif

            @if(Route::has('surat-masuk.cetak-label'))
                <a href="{{ route('surat-masuk.cetak-label', $suratMasuk) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold bg-blue-500/20 text-blue-300 hover:bg-blue-500/30 rounded-2xl transition border border-blue-500/30 backdrop-blur-sm shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h10M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                    <span>Cetak Label</span>
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        {{-- Kolom Utama Kiri (Background Gradien & Kotak Lebih Tebal) --}}
        <div class="lg:col-span-2 bg-gradient-to-b from-white via-slate-50/50 to-slate-100/60 rounded-3xl shadow-md border border-slate-200 p-6 sm:p-8 space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-start justify-between pb-6 border-b-2 border-slate-200 gap-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shadow-sm shadow-indigo-600/50"></span>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-700">Arsip Surat Masuk</span>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug break-words">
                        {{ $suratMasuk->perihal ?? 'Tanpa Perihal' }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-2.5 pt-1">
                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-mono font-bold bg-indigo-100 text-indigo-800 border border-indigo-300 shadow-2xs">
                            #{{ $suratMasuk->nomor_agenda ?? $suratMasuk->id }}
                        </span>
                        <span class="text-slate-400">&bull;</span>
                        <span class="text-xs text-slate-600">No. Surat: <strong class="text-slate-900 font-semibold break-all">{{ $suratMasuk->nomor_surat ?? '-' }}</strong></span>
                    </div>
                </div>

                @php
                    $status = strtolower($suratMasuk->status ?? 'baru');
                    $badgeClass = match($status) {
                        'baru'                 => 'bg-blue-100 text-blue-800 border-blue-300',
                        'diproses', 'proses' => 'bg-amber-100 text-amber-800 border-amber-300',
                        'didisposisikan', 'disposisi' => 'bg-purple-100 text-purple-800 border-purple-300',
                        'selesai'            => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                        'diarsipkan'         => 'bg-slate-200 text-slate-800 border-slate-300',
                        default              => 'bg-slate-200 text-slate-800 border-slate-300'
                    };
                @endphp
                <div class="self-start sm:self-auto shrink-0">
                    <span class="inline-flex items-center px-4 py-2 rounded-2xl text-xs font-bold border-2 shadow-xs {{ $badgeClass }}">
                        {{ ucfirst($status) }}
                    </span>
                </div>
            </div>

            {{-- Kotak Metadata Dibuat Lebih Tebal / Berisi (border-2, p-5) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-5 bg-white rounded-2xl border-2 border-slate-200 space-y-1.5 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Pengirim</span>
                    <p class="text-sm font-bold text-slate-900 break-words">
                        {{ $suratMasuk->pengirim ?? $suratMasuk->instansi?->nama_instansi ?? '-' }}
                    </p>
                </div>
                <div class="p-5 bg-white rounded-2xl border-2 border-slate-200 space-y-1.5 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Kategori Surat</span>
                    <p class="text-sm font-bold text-slate-900 break-words">
                        {{ $suratMasuk->kategori?->nama_kategori ?? $suratMasuk->kategori ?? '-' }}
                    </p>
                </div>
                <div class="p-5 bg-white rounded-2xl border-2 border-slate-200 space-y-1.5 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Tanggal Surat</span>
                    <p class="text-sm font-bold text-slate-900">
                        {{ $suratMasuk->tanggal_surat ? \Carbon\Carbon::parse($suratMasuk->tanggal_surat)->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>
                <div class="p-5 bg-white rounded-2xl border-2 border-slate-200 space-y-1.5 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Tanggal Diterima</span>
                    <p class="text-sm font-bold text-slate-900">
                        {{ $suratMasuk->tanggal_terima ? \Carbon\Carbon::parse($suratMasuk->tanggal_terima)->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>
                <div class="p-5 bg-white rounded-2xl border-2 border-slate-200 space-y-1.5 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Diterima Oleh</span>
                    <p class="text-sm font-bold text-slate-900 truncate">
                        {{ $suratMasuk->penerima?->name ?? $suratMasuk->penerima ?? '-' }}
                    </p>
                </div>
                <div class="p-5 bg-white rounded-2xl border-2 border-slate-200 space-y-1.5 shadow-sm">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Lokasi Arsip Fisik</span>
                    <p class="text-sm font-bold text-slate-900 truncate">
                        {{ $suratMasuk->lokasi_arsip_fisik ?? '-' }}
                    </p>
                </div>
            </div>

            @if(!empty($suratMasuk->ringkasan) || !empty($suratMasuk->isi_surat))
                <div class="pt-2">
                    <div class="p-5 bg-white rounded-2xl border-2 border-slate-200 space-y-2 shadow-sm">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Ringkasan / Isi Surat</span>
                        <div class="text-sm text-slate-800 font-medium leading-relaxed whitespace-pre-line break-words">
                            {{ $suratMasuk->ringkasan ?? $suratMasuk->isi_surat }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="pt-4 border-t-2 border-slate-200 space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600 block">Berkas Lampiran Digital</span>
                
                @php
                    $rawPath = $suratMasuk->lampiran_file ?? $suratMasuk->file_surat ?? $suratMasuk->lampiran ?? null;
                    $resolvedUrl = null;

                    if (isset($fileUrl)) {
                        $resolvedUrl = $fileUrl;
                    } elseif ($rawPath) {
                        $resolvedUrl = filter_var($rawPath, FILTER_VALIDATE_URL) ? $rawPath : route('surat-masuk.preview-lampiran', $suratMasuk);
                    }
                    
                    $cleanPath = $rawPath ? parse_url($rawPath, PHP_URL_PATH) : '';
                    $extension = $cleanPath ? strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION)) : '';
                @endphp

                @if(!empty($resolvedUrl))
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border-2 border-slate-200 shadow-2xs">
                            <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                                <a href="{{ $resolvedUrl }}" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-sm shadow-indigo-600/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>Buka Berkas</span>
                                </a>
                                <a href="{{ $resolvedUrl }}" download class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold bg-white text-slate-700 hover:bg-slate-100 transition border-2 border-slate-200 shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Unduh</span>
                                </a>
                            </div>
                            @if($extension)
                                <span class="text-[10px] font-mono font-bold uppercase px-3 py-1 bg-slate-100 text-slate-800 rounded-xl border border-slate-200">
                                    .{{ $extension }}
                                </span>
                            @endif
                        </div>

                        <div class="bg-slate-900/10 p-3 rounded-2xl border-2 border-slate-300 overflow-hidden backdrop-blur-sm">
                            @if(in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                                <div class="text-center overflow-hidden rounded-xl bg-white p-2">
                                    <img src="{{ $resolvedUrl }}" alt="Lampiran Surat Masuk" class="max-h-[500px] mx-auto object-contain rounded-xl shadow-xs">
                                </div>
                            @elseif($extension === 'pdf')
                                <iframe src="{{ $resolvedUrl }}" class="w-full h-[500px] border-0 rounded-xl bg-white shadow-sm" title="Pratinjau PDF"></iframe>
                            @else
                                <div class="text-center py-10 bg-white rounded-xl border border-slate-200">
                                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="text-xs text-slate-600 font-medium">Pratinjau tidak tersedia untuk format <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800">.{{ $extension ?: 'berkas' }}</code></p>
                                    <p class="text-[11px] text-slate-400 mt-1">Silakan gunakan tombol unduh di atas untuk melihat dokumen.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="w-full inline-flex items-center justify-center gap-2 p-6 rounded-2xl text-xs font-medium bg-white text-slate-500 border-2 border-slate-200 shadow-sm">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        <span>Tidak ada lampiran berkas digital.</span>
                    </div>
                @endif
            </div>

        </div>

        {{-- Kolom Kanan (Riwayat Disposisi - Tampilan Lebih Tebal) --}}
        <div class="bg-gradient-to-b from-white via-slate-50/50 to-slate-100/60 rounded-3xl shadow-md border border-slate-200 p-5 sm:p-6 flex flex-col justify-between space-y-5">
            <div>
                <div class="flex items-center justify-between pb-4 border-b-2 border-slate-200">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 tracking-tight">Riwayat Disposisi</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Instruksi & tindak lanjut</p>
                    </div>

                    @if(Route::has('disposisi.create'))
                        <a href="{{ route('disposisi.create', $suratMasuk) }}" class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-purple-700 bg-purple-100 hover:bg-purple-200 border border-purple-300 rounded-xl transition shadow-2xs">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            Buat
                        </a>
                    @elseif(Route::has('surat-masuk.disposisi.store'))
                        <button type="button" onclick="toggleModalDisposisi(true)" class="inline-flex items-center px-3.5 py-2 text-xs font-bold text-purple-700 bg-purple-100 hover:bg-purple-200 border border-purple-300 rounded-xl transition shadow-2xs">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            Buat
                        </button>
                    @endif
                </div>
                
                <div class="space-y-4 max-h-[520px] overflow-y-auto pr-1 pt-4">
                    @forelse($suratMasuk->disposisi ?? [] as $d)
                        <div class="relative pl-6 space-y-2 group">
                            <div class="absolute left-2.5 top-2 bottom-0 w-0.5 bg-purple-300 group-last:bg-transparent"></div>
                            <div class="absolute left-[7px] top-1.5 w-3 h-3 rounded-full bg-purple-600 ring-4 ring-purple-100 group-hover:scale-110 transition z-10"></div>
                            
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-xs font-bold text-slate-900 flex items-center gap-1.5 flex-wrap">
                                    <span class="truncate max-w-[110px]" title="{{ $d->dari?->name ?? 'Admin' }}">{{ $d->dari?->name ?? 'Admin' }}</span>
                                    <svg class="w-3 h-3 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    <span class="text-purple-700 truncate max-w-[110px]" title="{{ $d->kepada?->name ?? $d->tujuan ?? '-' }}">{{ $d->kepada?->name ?? $d->tujuan ?? '-' }}</span>
                                </div>

                                @php
                                    $disposisiStatus = strtolower($d->status ?? 'dikirim');
                                    $statusClass = match($disposisiStatus) {
                                        'selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'diproses', 'proses' => 'bg-amber-100 text-amber-800 border-amber-300',
                                        default => 'bg-purple-100 text-purple-800 border-purple-300'
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg border shadow-2xs shrink-0 {{ $statusClass }}">
                                    {{ ucfirst($d->status ?? 'Dikirim') }}
                                </span>
                            </div>

                            <div class="p-3.5 bg-white border-2 border-slate-200 rounded-2xl text-xs text-slate-700 leading-relaxed shadow-2xs break-words font-medium">
                                {{ $d->instruksi ?? $d->catatan ?? '-' }}
                            </div>

                            <div class="text-[10px] text-slate-500 font-semibold pt-0.5">
                                {{ $d->created_at ? \Carbon\Carbon::parse($d->created_at)->translatedFormat('d/m/Y H:i') : '-' }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 space-y-2">
                            <div class="w-10 h-10 mx-auto rounded-full bg-white text-slate-400 flex items-center justify-center border-2 border-slate-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            </div>
                            <p class="text-xs text-slate-500 font-medium">Belum ada riwayat disposisi.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-t border-slate-200 text-center">
                <span class="text-[10px] font-bold text-slate-500 tracking-wide uppercase">Sistem Kendali Surat Masuk</span>
            </div>
        </div>

    </div>
</div>

@if(Route::has('surat-masuk.disposisi.store'))
<div id="modal-disposisi" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 transition-all">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-xl border border-slate-200 space-y-4 relative" onclick="event.stopPropagation()">
        <div class="flex justify-between items-center border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-base">Tambah Disposisi Surat</h3>
            <button type="button" onclick="toggleModalDisposisi(false)" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form id="form-disposisi" action="{{ route('surat-masuk.disposisi.store', $suratMasuk) }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1.5">Diteruskan Kepada <span class="text-rose-500">*</span></label>
                <input type="text" name="tujuan" required class="w-full text-xs rounded-xl border-slate-200 focus:border-purple-500 focus:ring-purple-500 p-3" placeholder="Nama pejabat / divisi tujuan">
            </div>
            <div>
                <label class="block text-xs font-bold uppercase text-slate-500 mb-1.5">Instruksi / Catatan <span class="text-rose-500">*</span></label>
                <textarea name="instruksi" rows="3" required class="w-full text-xs rounded-xl border-slate-200 focus:border-purple-500 focus:ring-purple-500 p-3" placeholder="Tuliskan instruksi disposisi..."></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="toggleModalDisposisi(false)" class="px-4 py-2.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">Batal</button>
                <button type="submit" class="px-4 py-2.5 text-xs font-bold text-white bg-purple-600 hover:bg-purple-700 rounded-xl transition shadow-xs">Simpan Disposisi</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleModalDisposisi(show) {
        const modal = document.getElementById('modal-disposisi');
        const form = document.getElementById('form-disposisi');
        if (modal) {
            if (show) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            } else {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                if (form) form.reset();
            }
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") {
            toggleModalDisposisi(false);
        }
    });

    document.getElementById('modal-disposisi')?.addEventListener('click', function(e) {
        if (e.target === this) {
            toggleModalDisposisi(false);
        }
    });
</script>
@endpush
@endif
@endsection