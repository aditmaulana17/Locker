@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')

@php
    use Illuminate\Support\Carbon;

    $tanggalSurat = old('tanggal_surat');

    if ($tanggalSurat === null && $suratMasuk->tanggal_surat) {
        try {
            $tanggalSurat = Carbon::parse($suratMasuk->tanggal_surat)->format('Y-m-d');
        } catch (\Throwable $e) {
            $tanggalSurat = substr((string) $suratMasuk->tanggal_surat, 0, 10);
        }
    }

    $tanggalTerima = old('tanggal_terima');

    if ($tanggalTerima === null && $suratMasuk->tanggal_terima) {
        try {
            $tanggalTerima = Carbon::parse($suratMasuk->tanggal_terima)->format('Y-m-d');
        } catch (\Throwable $e) {
            $tanggalTerima = substr((string) $suratMasuk->tanggal_terima, 0, 10);
        }
    }

    $selectedKategori = old('kategori_surat_id', $suratMasuk->kategori_surat_id);

    $currentStatus = strtolower(trim((string) old(
        'status',
        $suratMasuk->status ?: 'baru'
    )));

    $statusOptions = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'didisposisikan' => 'Didisposisikan',
        'selesai' => 'Selesai',
        'diarsipkan' => 'Diarsipkan',
    ];
@endphp

<style>
    .edit-surat-page {
        max-width: 1240px;
        margin: 0 auto;
        padding: 0 2px 20px;
        color: #334155;
    }

    .edit-surat-page .edit-header {
        margin-bottom: 16px;
    }

    .edit-surat-page .main-card {
        overflow: hidden;
        border: 1.5px solid #64748b;
        border-radius: 18px;
        background: #fff;
        box-shadow:
            0 2px 6px rgba(15, 23, 42, .06),
            0 12px 28px rgba(15, 23, 42, .05);
    }

    .edit-surat-page .section-body {
        padding: 20px;
    }

    .edit-surat-page .section-body > * + * {
        margin-top: 20px !important;
    }

    .edit-surat-page .section-title {
        margin-bottom: 14px;
    }

    .edit-surat-page .field-grid {
        gap: 14px;
    }

    .edit-surat-page input:not([type=file]),
    .edit-surat-page select,
    .edit-surat-page textarea {
        border-color: #cbd5e1;
        border-radius: 11px;
        min-height: 42px;
    }

    .edit-surat-page input:not([type=file]),
    .edit-surat-page select {
        padding-top: .65rem;
        padding-bottom: .65rem;
    }

    .edit-surat-page textarea {
        min-height: 104px;
    }

    .edit-surat-page label {
        font-size: 13px;
    }

    .edit-surat-page .agenda-bar {
        padding: 13px 18px;
        border-bottom: 1.5px solid #94a3b8;
        background: #f8fafc;
    }

    .edit-surat-page .agenda-bar .text-base {
        font-size: 15px;
    }

    .edit-surat-page .digital-card,
    .edit-surat-page .physical-card {
        display: flex;
        min-height: 100%;
        flex-direction: column;
        padding: 15px !important;
        border: 1.5px solid #64748b !important;
        border-radius: 16px !important;
        background: #f8fafc !important;
        box-shadow:
            0 1px 3px rgba(15, 23, 42, .05),
            0 6px 16px rgba(15, 23, 42, .03);
    }

    .edit-surat-page .attachment-grid {
        align-items: stretch;
        gap: 18px;
    }

    .edit-surat-page .digital-inner,
    .edit-surat-page .physical-inner,
    .edit-surat-page .physical-card > .rounded-2xl {
        border: 1px solid #cbd5e1 !important;
        border-radius: 13px !important;
        background: #fff;
    }

    .edit-surat-page .physical-card > .digital-inner {
        height: 100%;
    }

    .edit-surat-page .mode-button {
        min-height: 66px;
        border-radius: 11px !important;
    }

    .edit-surat-page #upload-box {
        min-height: 165px;
        padding: 24px 16px;
        border-width: 2px;
    }

    .edit-surat-page #scan-panel > div {
        border-color: #94a3b8;
    }

    .edit-surat-page #scan-panel .relative.aspect-video {
        min-height: 240px;
    }

    .edit-surat-page #selected-file,
    .edit-surat-page #scan-result {
        border-width: 1px;
    }

    .edit-surat-page .info-box {
        border: 1px solid #cbd5e1 !important;
        border-radius: 11px !important;
    }

    .edit-surat-page .footer-bar {
        padding: 13px 18px;
        border-top: 1.5px solid #94a3b8;
        background: #f8fafc;
    }

    .edit-surat-page .footer-bar a,
    .edit-surat-page .footer-bar button,
    .edit-surat-page .btn-main {
        min-height: 42px;
        border-radius: 11px;
    }

    .edit-surat-page .error-box {
        border-width: 1px;
        border-radius: 13px;
    }

    @media (max-width: 767px) {
        .edit-surat-page {
            padding: 0 0 16px;
        }

        .edit-surat-page .section-body {
            padding: 15px;
        }

        .edit-surat-page .agenda-bar {
            padding: 12px 14px;
        }

        .edit-surat-page .field-grid,
        .edit-surat-page .attachment-grid {
            gap: 12px;
        }

        .edit-surat-page #scan-panel .relative.aspect-video {
            min-height: 200px;
        }

        .edit-surat-page #upload-box {
            min-height: 145px;
            padding: 20px 12px;
        }

        .edit-surat-page .footer-bar {
            padding: 11px 14px;
        }
    }
</style>

<div class="edit-surat-page">

    {{-- HEADER --}}
    <div class="mb-6 edit-header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-7.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 7.5-7.5z" />
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">
                            Edit Surat Masuk
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Perbarui informasi arsip surat masuk yang tersimpan di dalam sistem.
                        </p>
                    </div>
                </div>
            </div>

            <a href="{{ route('surat-masuk.index') }}"
                class="inline-flex w-fit items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>
    </div>

    {{-- VALIDATION ERROR --}}
    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 error-box">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z" />
                    </svg>
                </div>

                <div class="min-w-0">
                    <p class="font-semibold text-red-800">
                        Data belum dapat diperbarui.
                    </p>

                    <ul class="mt-2 space-y-1 text-sm text-red-700">
                        @foreach($errors->all() as $error)
                            <li class="flex gap-2">
                                <span>•</span>
                                <span>{{ $error }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- FORM --}}
    <form id="form-surat"
        action="{{ route('surat-masuk.update', $suratMasuk->id) }}"
        method="POST"
        enctype="multipart/form-data"
        class="pb-8">

        @csrf
        @method('PUT')

        <input type="hidden"
            name="nomor_agenda"
            value="{{ old('nomor_agenda', $suratMasuk->nomor_agenda) }}">

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm main-card">

            {{-- AGENDA --}}
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-4 sm:px-6 agenda-bar">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 013-3h0a3 3 0 013 3" />
                            </svg>
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                Nomor Agenda Sistem
                            </p>
                            <p class="mt-0.5 truncate text-base font-bold text-slate-800">
                                {{ $suratMasuk->nomor_agenda ?: '-' }}
                            </p>
                        </div>
                    </div>

                    <span class="w-fit rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-600">
                        Nomor agenda tidak diubah
                    </span>
                </div>
            </div>

            {{-- FORM BODY --}}
            <div class="space-y-8 p-4 sm:p-6 lg:p-8 section-body">

                {{-- INFORMASI UTAMA --}}
                <section>
                    <div class="mb-5 flex items-start gap-3">
                        <div class="mt-1 h-8 w-1 shrink-0 rounded-full bg-blue-500"></div>

                        <div>
                            <h2 class="text-base font-bold text-slate-800 sm:text-lg">
                                Informasi Utama Surat
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Perbarui identitas dan informasi utama surat masuk.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 field-grid">

                        {{-- NOMOR SURAT --}}
                        <div>
                            <label for="nomor_surat" class="mb-2 block text-sm font-semibold text-slate-700">
                                Nomor Surat <span class="text-red-500">*</span>
                            </label>

                            <input id="nomor_surat"
                                name="nomor_surat"
                                type="text"
                                required
                                autocomplete="off"
                                value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}"
                                placeholder="Contoh: 005/B/I/2026"
                                class="block w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:ring-2 @error('nomor_surat') border-red-300 focus:border-red-500 focus:ring-red-100 @else border-slate-300 focus:border-blue-500 focus:ring-blue-100 @enderror">

                            @error('nomor_surat')
                                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PENGIRIM --}}
                        <div>
                            <label for="pengirim" class="mb-2 block text-sm font-semibold text-slate-700">
                                Instansi Pengirim <span class="text-red-500">*</span>
                            </label>

                            <input id="pengirim"
                                name="pengirim"
                                type="text"
                                required
                                autocomplete="organization"
                                value="{{ old('pengirim', $suratMasuk->pengirim) }}"
                                placeholder="Masukkan nama instansi pengirim"
                                class="block w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:ring-2 @error('pengirim') border-red-300 focus:border-red-500 focus:ring-red-100 @else border-slate-300 focus:border-blue-500 focus:ring-blue-100 @enderror">

                            @error('pengirim')
                                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- TANGGAL SURAT --}}
                        <div>
                            <label for="tanggal_surat" class="mb-2 block text-sm font-semibold text-slate-700">
                                Tanggal Surat <span class="text-red-500">*</span>
                            </label>

                            <input id="tanggal_surat"
                                name="tanggal_surat"
                                type="date"
                                required
                                value="{{ $tanggalSurat }}"
                                class="block w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:ring-2 @error('tanggal_surat') border-red-300 focus:border-red-500 focus:ring-red-100 @else border-slate-300 focus:border-blue-500 focus:ring-blue-100 @enderror">

                            @error('tanggal_surat')
                                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- TANGGAL DITERIMA --}}
                        <div>
                            <label for="tanggal_terima" class="mb-2 block text-sm font-semibold text-slate-700">
                                Tanggal Diterima <span class="text-red-500">*</span>
                            </label>

                            <input id="tanggal_terima"
                                name="tanggal_terima"
                                type="date"
                                required
                                value="{{ $tanggalTerima }}"
                                class="block w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:ring-2 @error('tanggal_terima') border-red-300 focus:border-red-500 focus:ring-red-100 @else border-slate-300 focus:border-blue-500 focus:ring-blue-100 @enderror">

                            @error('tanggal_terima')
                                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- KATEGORI --}}
                        <div>
                            <label for="kategori_surat_id" class="mb-2 block text-sm font-semibold text-slate-700">
                                Kategori Surat <span class="text-red-500">*</span>
                            </label>

                            <select id="kategori_surat_id"
                                name="kategori_surat_id"
                                required
                                class="block w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:ring-2 @error('kategori_surat_id') border-red-300 focus:border-red-500 focus:ring-red-100 @else border-slate-300 focus:border-blue-500 focus:ring-blue-100 @enderror">

                                <option value="" disabled @selected(!$selectedKategori)>
                                    Pilih kategori surat
                                </option>

                                @foreach(($kategoris ?? collect()) as $kategori)
                                    <option value="{{ $kategori->id }}"
                                        @selected((string) $selectedKategori === (string) $kategori->id)>
                                        {{ $kategori->nama_kategori }}
                                        @if(!empty($kategori->sifat))
                                            ({{ ucfirst($kategori->sifat) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            @error('kategori_surat_id')
                                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- STATUS --}}
                        <div>
                            <label for="status" class="mb-2 block text-sm font-semibold text-slate-700">
                                Status Surat <span class="text-red-500">*</span>
                            </label>

                            <select id="status"
                                name="status"
                                required
                                class="block w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-800 outline-none transition focus:ring-2 @error('status') border-red-300 focus:border-red-500 focus:ring-red-100 @else border-slate-300 focus:border-blue-500 focus:ring-blue-100 @enderror">

                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($currentStatus === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @error('status')
                                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PERIHAL --}}
                        <div class="md:col-span-2">
                            <label for="perihal" class="mb-2 block text-sm font-semibold text-slate-700">
                                Perihal <span class="text-red-500">*</span>
                            </label>

                            <textarea id="perihal"
                                name="perihal"
                                rows="4"
                                required
                                placeholder="Tuliskan perihal surat"
                                class="block w-full resize-y rounded-xl border bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:ring-2 @error('perihal') border-red-300 focus:border-red-500 focus:ring-red-100 @else border-slate-300 focus:border-blue-500 focus:ring-blue-100 @enderror">{{ old('perihal', $suratMasuk->perihal) }}</textarea>

                            @error('perihal')
                                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- RINGKASAN --}}
                        <div class="md:col-span-2">
                            <label for="ringkasan" class="mb-2 block text-sm font-semibold text-slate-700">
                                Ringkasan
                                <span class="font-normal text-slate-400">(opsional)</span>
                            </label>

                            <textarea id="ringkasan"
                                name="ringkasan"
                                rows="4"
                                placeholder="Ringkasan isi surat, bila diperlukan..."
                                class="block w-full resize-y rounded-xl border bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:ring-2 @error('ringkasan') border-red-300 focus:border-red-500 focus:ring-red-100 @else border-slate-300 focus:border-blue-500 focus:ring-blue-100 @enderror">{{ old('ringkasan', $suratMasuk->ringkasan) }}</textarea>

                            @error('ringkasan')
                                <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- LAMPIRAN --}}
                <section class="border-t border-slate-200 pt-8">
                    <div class="mb-5 flex items-start gap-3">
                        <div class="mt-1 h-8 w-1 shrink-0 rounded-full bg-indigo-500"></div>

                        <div>
                            <h2 class="text-base font-bold text-slate-800 sm:text-lg">
                                Lampiran Dokumen & Arsip Fisik
                            </h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Ganti dokumen melalui upload atau scan, serta perbarui lokasi arsip fisik.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2 attachment-grid">

                        {{-- DOKUMEN DIGITAL --}}
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5 digital-card">

                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-bold text-slate-800">Berkas Digital</h3>
                                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                                        Upload file baru atau scan menggunakan kamera.
                                    </p>
                                </div>

                                <span class="shrink-0 rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                    Opsional
                                </span>
                            </div>

                            {{-- INFO KOMPRESI --}}
                            <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 p-3">
                                <div class="flex items-start gap-2">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                                    </svg>

                                    <p class="text-xs leading-relaxed text-blue-700">
                                        Maksimal file <strong>10 MB</strong>.
                                        PDF disimpan tanpa kompresi.
                                        JPG, JPEG, dan PNG akan dikompres otomatis
                                        sebelum dikirim ke server.
                                    </p>
                                </div>
                            </div>

                            {{-- MODE BUTTON --}}
                            <div class="mb-4 grid grid-cols-2 gap-2">
                                <button type="button"
                                    id="mode-upload-btn"
                                    aria-selected="true"
                                    class="mode-button active flex items-center gap-2 rounded-xl border-2 border-indigo-500 bg-indigo-50 px-3 py-3 text-left transition">

                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l3 3m-3-3v12" />
                                        </svg>
                                    </span>

                                    <span class="min-w-0">
                                        <span class="block text-xs font-bold text-slate-800">Upload File</span>
                                        <span class="mt-0.5 block truncate text-[11px] text-slate-500">
                                            Dari perangkat
                                        </span>
                                    </span>
                                </button>

                                <button type="button"
                                    id="mode-scan-btn"
                                    aria-selected="false"
                                    class="mode-button flex items-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-3 py-3 text-left transition hover:border-indigo-300 hover:bg-indigo-50/50">

                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 7V5a1 1 0 011-1h2M17 4h2a1 1 0 011 1v2M20 17v2a1 1 0 01-1 1h-2M7 20H5a1 1 0 01-1-1v-2M7 12h10M7 9h10M7 15h6" />
                                        </svg>
                                    </span>

                                    <span class="min-w-0">
                                        <span class="block text-xs font-bold text-slate-800">Scan Dokumen</span>
                                        <span class="mt-0.5 block truncate text-[11px] text-slate-500">
                                            Gunakan kamera
                                        </span>
                                    </span>
                                </button>
                            </div>

                            {{-- FILE LAMA --}}
                            <div class="mb-4 rounded-xl border border-slate-200 bg-white p-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.414l4.414 4.414A2 2 0 0118 8.414V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        @if($suratMasuk->lampiran_file)
                                            <p class="text-xs font-semibold text-slate-500">Dokumen saat ini</p>
                                            <p class="mt-0.5 truncate text-sm font-semibold text-slate-800"
                                                title="{{ basename($suratMasuk->lampiran_file) }}">
                                                {{ basename($suratMasuk->lampiran_file) }}
                                            </p>
                                        @else
                                            <p class="text-xs font-semibold text-amber-600">Belum ada dokumen</p>
                                            <p class="mt-0.5 text-xs text-slate-500">
                                                Upload atau scan dokumen baru.
                                            </p>
                                        @endif
                                    </div>

                                    @if($suratMasuk->lampiran_file)
                                        <a href="{{ route('surat-masuk.preview-lampiran', $suratMasuk->id) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="shrink-0 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-indigo-100 hover:text-indigo-700">
                                            Lihat
                                        </a>
                                    @endif
                                </div>
                            </div>

                            {{-- UPLOAD PANEL --}}
                            <div id="upload-panel">
                                <label for="lampiran_file"
                                    id="upload-box"
                                    class="group flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white px-4 py-8 text-center transition hover:border-indigo-400 hover:bg-indigo-50/30">

                                    <span class="mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 transition group-hover:scale-105">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l3 3m-3-3v12" />
                                        </svg>
                                    </span>

                                    <span id="file-label-text" class="text-sm font-bold text-slate-700">
                                        Pilih file baru
                                    </span>

                                    <span class="mt-1 text-xs text-slate-500">
                                        PDF, JPG, JPEG, PNG
                                    </span>

                                    <span class="mt-2 text-[11px] text-slate-400">
                                        Maksimal 10 MB • Gambar dikompres otomatis
                                    </span>

                                    <input type="file"
                                        id="lampiran_file"
                                        name="lampiran_file"
                                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                        class="sr-only">
                                </label>

                                {{-- FILE BARU --}}
                                <div id="selected-file"
                                    class="mt-3 hidden rounded-xl border border-emerald-200 bg-emerald-50 p-3">

                                    <div class="flex items-center gap-3">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold text-emerald-700">
                                                File baru dipilih
                                            </p>
                                            <p id="selected-file-name"
                                                class="mt-0.5 truncate text-sm font-semibold text-slate-700"></p>
                                            <p id="selected-file-size"
                                                class="mt-0.5 text-xs text-slate-500"></p>
                                        </div>

                                        <button type="button"
                                            id="clear-file-btn"
                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-100 hover:text-red-600"
                                            title="Hapus file"
                                            aria-label="Hapus file">

                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- SCAN PANEL --}}
                            <div id="scan-panel" class="hidden">
                                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">

                                    <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <h4 class="text-sm font-bold text-slate-800">
                                                Scan Dokumen
                                            </h4>
                                            <p class="mt-1 text-xs leading-relaxed text-slate-500">
                                                Arahkan kamera ke dokumen lalu ambil gambar.
                                            </p>
                                        </div>

                                        <span class="w-fit rounded-full bg-indigo-100 px-2.5 py-1 text-[11px] font-semibold text-indigo-700">
                                            Kamera
                                        </span>
                                    </div>

                                    {{-- CAMERA --}}
                                    <div class="relative aspect-video overflow-hidden bg-slate-950">
                                        <video id="camera-video"
                                            class="h-full w-full object-contain"
                                            autoplay
                                            playsinline
                                            muted></video>

                                        <div id="camera-placeholder"
                                            class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 px-4 text-center">

                                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 text-slate-400">
                                                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M3 7h4l2-3h6l2 3h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z" />
                                                    <circle cx="12" cy="13" r="3" />
                                                </svg>
                                            </div>

                                            <p class="mt-3 text-sm font-semibold text-slate-300">
                                                Kamera belum aktif
                                            </p>

                                            <p class="mt-1 text-xs text-slate-500">
                                                Klik tombol "Aktifkan Kamera".
                                            </p>
                                        </div>

                                        <div id="camera-error"
                                            class="absolute bottom-3 left-3 right-3 hidden rounded-xl border border-red-400/30 bg-red-950/90 p-3 text-xs font-medium text-red-200"
                                            role="alert"></div>

                                        <div class="pointer-events-none absolute inset-8 rounded-xl border-2 border-dashed border-white/40 sm:inset-12"></div>
                                    </div>

                                    {{-- CAMERA ACTIONS --}}
                                    <div class="grid grid-cols-1 gap-2 p-4 sm:grid-cols-3">
                                        <button type="button"
                                            id="start-camera-btn"
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 7h4l2-3h6l2 3h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z" />
                                            </svg>
                                            Aktifkan Kamera
                                        </button>

                                        <button type="button"
                                            id="capture-btn"
                                            disabled
                                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="9" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                            Ambil Gambar
                                        </button>

                                        <button type="button"
                                            id="stop-camera-btn"
                                            disabled
                                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
                                            Matikan Kamera
                                        </button>
                                    </div>

                                    {{-- SCAN RESULT --}}
                                    <div id="scan-result"
                                        class="hidden border-t border-slate-200 bg-emerald-50 p-4">

                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="text-sm font-bold text-emerald-800">
                                                    Dokumen hasil scan
                                                </p>
                                                <p class="mt-1 text-xs leading-relaxed text-emerald-700">
                                                    Hasil scan akan menggantikan lampiran lama setelah data diperbarui.
                                                </p>
                                            </div>

                                            <button type="button"
                                                id="retake-btn"
                                                class="w-fit rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                                Scan Ulang
                                            </button>
                                        </div>

                                        <div class="mt-4 overflow-hidden rounded-xl border border-emerald-200 bg-white">
                                            <img id="scan-preview-image"
                                                src=""
                                                alt="Hasil scan dokumen"
                                                class="max-h-[420px] w-full object-contain">
                                        </div>

                                        <div id="scan-compression-info"
                                            class="mt-2 text-center text-xs font-semibold text-emerald-700"></div>
                                    </div>
                                </div>

                                <input type="hidden"
                                    name="captured_image"
                                    id="captured_image"
                                    value="{{ old('captured_image') }}">
                            </div>

                            {{-- INFO --}}
                            <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 info-box">
                                <p class="text-xs leading-relaxed text-slate-500">
                                    Jika tidak memilih file baru dan tidak melakukan scan,
                                    dokumen lama akan tetap dipertahankan.
                                    PDF tidak dikompres, sedangkan JPG, JPEG, dan PNG
                                    dikompres otomatis sebelum upload.
                                </p>
                            </div>

                            @error('lampiran_file')
                                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror

                            @error('captured_image')
                                <p class="mt-2 text-xs font-medium text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- ARSIP FISIK --}}
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5 physical-card">

                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-bold text-slate-800">
                                        Lokasi Arsip Fisik
                                    </h3>
                                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                                        Perbarui lokasi penyimpanan arsip fisik.
                                    </p>
                                </div>

                                <span class="shrink-0 rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                    Opsional
                                </span>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-5 digital-inner">

                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>

                                <h4 class="mt-4 font-bold text-slate-800">
                                    Lokasi Penyimpanan
                                </h4>

                                <p class="mt-1 text-sm leading-relaxed text-slate-500">
                                    Masukkan posisi rak, lemari, box, atau map tempat arsip disimpan.
                                </p>

                                <div class="mt-5">
                                    <label for="lokasi_arsip_fisik"
                                        class="mb-2 block text-sm font-semibold text-slate-700">
                                        Detail Posisi Lemari / Box
                                    </label>

                                    <input type="text"
                                        id="lokasi_arsip_fisik"
                                        name="lokasi_arsip_fisik"
                                        value="{{ old('lokasi_arsip_fisik', $suratMasuk->lokasi_arsip_fisik) }}"
                                        placeholder="Contoh: Rak A-3 Box 12"
                                        class="block w-full rounded-xl border bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:ring-2 @error('lokasi_arsip_fisik') border-red-300 focus:border-red-500 focus:ring-red-100 @else border-slate-300 focus:border-indigo-500 focus:ring-indigo-100 @enderror">

                                    @error('lokasi_arsip_fisik')
                                        <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="mt-4 rounded-xl bg-slate-50 p-3">
                                    <p class="text-xs leading-relaxed text-slate-500">
                                        <span class="font-semibold text-slate-700">Contoh:</span>
                                        Rak A-3 Box 12
                                        <span class="mx-1">atau</span>
                                        Lemari B-2 Map 07
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- FOOTER --}}
            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-6 footer-bar">

                <a href="{{ route('surat-masuk.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                    Batal
                </a>

                <button type="submit"
                    id="submit-btn"
                    class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-70">

                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 13l4 4L19 7" />
                    </svg>

                    <span id="submit-text">
                        Perbarui Surat Masuk
                    </span>
                </button>
            </div>
        </div>
    </form>
</div>

{{-- JAVASCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const form = document.getElementById('form-surat');
    const modeUploadBtn = document.getElementById('mode-upload-btn');
    const modeScanBtn = document.getElementById('mode-scan-btn');
    const uploadPanel = document.getElementById('upload-panel');
    const scanPanel = document.getElementById('scan-panel');
    const fileInput = document.getElementById('lampiran_file');
    const uploadBox = document.getElementById('upload-box');
    const selectedFile = document.getElementById('selected-file');
    const selectedFileName = document.getElementById('selected-file-name');
    const selectedFileSize = document.getElementById('selected-file-size');
    const clearFileBtn = document.getElementById('clear-file-btn');
    const cameraVideo = document.getElementById('camera-video');
    const cameraPlaceholder = document.getElementById('camera-placeholder');
    const cameraError = document.getElementById('camera-error');
    const startCameraBtn = document.getElementById('start-camera-btn');
    const captureBtn = document.getElementById('capture-btn');
    const stopCameraBtn = document.getElementById('stop-camera-btn');
    const retakeBtn = document.getElementById('retake-btn');
    const scanResult = document.getElementById('scan-result');
    const scanPreviewImage = document.getElementById('scan-preview-image');
    const scanCompressionInfo = document.getElementById('scan-compression-info');
    const capturedInput = document.getElementById('captured_image');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');

    if (!form || !fileInput || !capturedInput) {
        return;
    }

    const MAX_FILE_SIZE = 10 * 1024 * 1024;
    const TARGET_IMAGE_SIZE = 2.5 * 1024 * 1024;
    const MAX_COMPRESSED_IMAGE_SIZE = 5 * 1024 * 1024;
    const MAX_IMAGE_DIMENSION = 2200;
    const MIN_IMAGE_DIMENSION = 1200;

    const JPEG_QUALITIES = [
        0.84, 0.80, 0.76, 0.72, 0.68, 0.64,
        0.60, 0.56, 0.52, 0.48, 0.44, 0.40,
        0.36, 0.32
    ];

    let cameraStream = null;
    let previewObjectUrl = null;
    let isSubmitting = false;

    function formatFileSize(bytes) {
        if (!Number.isFinite(bytes) || bytes <= 0) {
            return '0 KB';
        }

        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(1)} KB`;
        }

        return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
    }

    function getReductionPercent(originalSize, compressedSize) {
        if (!originalSize || originalSize <= 0) {
            return 0;
        }

        return Math.max(
            0,
            Math.round((1 - compressedSize / originalSize) * 100)
        );
    }

    function showCameraError(message) {
        if (!cameraError) {
            return;
        }

        cameraError.textContent = message;
        cameraError.classList.remove('hidden');
    }

    function clearCameraError() {
        if (!cameraError) {
            return;
        }

        cameraError.textContent = '';
        cameraError.classList.add('hidden');
    }

    function activateUploadMode() {
        modeUploadBtn?.classList.add('border-indigo-500', 'bg-indigo-50');
        modeUploadBtn?.classList.remove('border-slate-200', 'bg-white');

        modeScanBtn?.classList.remove('border-indigo-500', 'bg-indigo-50');
        modeScanBtn?.classList.add('border-slate-200', 'bg-white');

        modeUploadBtn?.setAttribute('aria-selected', 'true');
        modeScanBtn?.setAttribute('aria-selected', 'false');

        uploadPanel?.classList.remove('hidden');
        scanPanel?.classList.add('hidden');

        stopCamera();
        clearCameraError();
    }

    function activateScanMode() {
        modeUploadBtn?.classList.remove('border-indigo-500', 'bg-indigo-50');
        modeUploadBtn?.classList.add('border-slate-200', 'bg-white');

        modeScanBtn?.classList.add('border-indigo-500', 'bg-indigo-50');
        modeScanBtn?.classList.remove('border-slate-200', 'bg-white');

        modeUploadBtn?.setAttribute('aria-selected', 'false');
        modeScanBtn?.setAttribute('aria-selected', 'true');

        uploadPanel?.classList.add('hidden');
        scanPanel?.classList.remove('hidden');

        clearFileSelection();
        clearCameraError();
    }

    modeUploadBtn?.addEventListener('click', activateUploadMode);
    modeScanBtn?.addEventListener('click', activateScanMode);

    fileInput.addEventListener('change', async () => {
        clearCameraError();

        const file = fileInput.files?.[0];

        if (!file) {
            return;
        }

        const fileName = String(file.name || '');
        const extension = fileName.split('.').pop()?.toLowerCase();

        const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

        if (!extension || !allowedExtensions.includes(extension)) {
            clearFileSelection();
            alert(
                'Format file tidak didukung.\n\n' +
                'Gunakan PDF, JPG, JPEG, atau PNG.'
            );
            return;
        }

        if (file.size > MAX_FILE_SIZE) {
            clearFileSelection();
            alert(
                'Ukuran file terlalu besar.\n\n' +
                'Maksimal ukuran file adalah 10 MB.'
            );
            return;
        }

        if (extension === 'pdf' || file.type === 'application/pdf') {
            capturedInput.value = '';
            clearScanResult();
            showSelectedFile(file, 'PDF disimpan tanpa kompresi');
            return;
        }

        if (!['jpg', 'jpeg', 'png'].includes(extension)) {
            clearFileSelection();
            alert(
                'Format file tidak didukung.\n\n' +
                'Gunakan PDF, JPG, JPEG, atau PNG.'
            );
            return;
        }

        try {
            const originalSize = file.size;
            const compressedFile = await compressImageFile(file);

            let finalFile = compressedFile;

            if (compressedFile.size >= originalSize) {
                finalFile = file;
            }

            if (finalFile.size > MAX_FILE_SIZE) {
                clearFileSelection();
                alert(
                    'Gambar masih terlalu besar setelah dikompres.\n\n' +
                    'Silakan pilih gambar dengan resolusi lebih rendah.'
                );
                return;
            }

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(finalFile);
            fileInput.files = dataTransfer.files;

            capturedInput.value = '';
            clearScanResult();

            const reduction = getReductionPercent(
                originalSize,
                finalFile.size
            );

            const note = finalFile === file
                ? `Tidak dikompres • ${formatFileSize(finalFile.size)}`
                : `Dikompres ${reduction}% • ${formatFileSize(finalFile.size)}`;

            showSelectedFile(finalFile, note);
        } catch (error) {
            console.error('Compression upload gagal:', error);

            clearFileSelection();

            alert(
                error?.message ||
                'Gagal memproses gambar.'
            );
        }
    });

    function showSelectedFile(file, note = '') {
        if (!selectedFile) {
            return;
        }

        if (selectedFileName) {
            selectedFileName.textContent = file.name;
        }

        if (selectedFileSize) {
            selectedFileSize.textContent =
                `${formatFileSize(file.size)}${note ? ` • ${note}` : ''}`;
        }

        selectedFile.classList.remove('hidden');

        uploadBox?.classList.add(
            'border-emerald-400',
            'bg-emerald-50/30'
        );
    }

    function clearFileSelection() {
        if (fileInput) {
            fileInput.value = '';
        }

        selectedFile?.classList.add('hidden');

        if (selectedFileName) {
            selectedFileName.textContent = '';
        }

        if (selectedFileSize) {
            selectedFileSize.textContent = '';
        }

        uploadBox?.classList.remove(
            'border-emerald-400',
            'bg-emerald-50/30'
        );
    }

    clearFileBtn?.addEventListener('click', clearFileSelection);

    function loadImageFromFile(file) {
        return new Promise((resolve, reject) => {
            const objectUrl = URL.createObjectURL(file);
            const image = new Image();

            image.onload = () => {
                URL.revokeObjectURL(objectUrl);
                resolve(image);
            };

            image.onerror = () => {
                URL.revokeObjectURL(objectUrl);

                reject(
                    new Error(
                        'Gambar tidak dapat dibaca oleh browser.'
                    )
                );
            };

            image.src = objectUrl;
        });
    }

    async function compressImageFile(file) {
        const image = await loadImageFromFile(file);

        return compressImageElement(
            image,
            file.name
        );
    }

    function calculateDimensions(width, height, maxDimension) {
        if (
            width <= maxDimension &&
            height <= maxDimension
        ) {
            return { width, height };
        }

        const scale = Math.min(
            maxDimension / width,
            maxDimension / height
        );

        return {
            width: Math.max(1, Math.round(width * scale)),
            height: Math.max(1, Math.round(height * scale))
        };
    }

    function buildDimensionList(sourceWidth, sourceHeight) {
        const maxDimensions = [
            2200, 2000, 1800, 1600, 1400, 1200
        ];

        const list = [];

        for (const maxDimension of maxDimensions) {
            const dimensions = calculateDimensions(
                sourceWidth,
                sourceHeight,
                maxDimension
            );

            if (
                dimensions.width < MIN_IMAGE_DIMENSION &&
                dimensions.height < MIN_IMAGE_DIMENSION
            ) {
                continue;
            }

            list.push(dimensions);
        }

        list.unshift(
            calculateDimensions(
                sourceWidth,
                sourceHeight,
                MAX_IMAGE_DIMENSION
            )
        );

        return list.filter(
            (item, index, array) =>
                index === array.findIndex(
                    value =>
                        value.width === item.width &&
                        value.height === item.height
                )
        );
    }

    async function compressImageElement(image, originalName) {
        const sourceWidth =
            image.naturalWidth || image.width;

        const sourceHeight =
            image.naturalHeight || image.height;

        if (
            sourceWidth <= 0 ||
            sourceHeight <= 0
        ) {
            throw new Error(
                'Dimensi gambar tidak valid.'
            );
        }

        const dimensionsList = buildDimensionList(
            sourceWidth,
            sourceHeight
        );

        let smallestResult = null;

        for (const dimensions of dimensionsList) {
            for (const quality of JPEG_QUALITIES) {
                const result = await compressCanvasAtDimensions(
                    image,
                    dimensions.width,
                    dimensions.height,
                    originalName,
                    quality
                );

                if (
                    !smallestResult ||
                    result.size < smallestResult.size
                ) {
                    smallestResult = result;
                }

                if (result.size <= TARGET_IMAGE_SIZE) {
                    return result;
                }
            }
        }

        if (
            smallestResult &&
            smallestResult.size <= MAX_COMPRESSED_IMAGE_SIZE
        ) {
            return smallestResult;
        }

        throw new Error(
            'Gambar masih terlalu besar setelah dikompres. ' +
            'Silakan gunakan gambar dengan resolusi lebih rendah.'
        );
    }

    function compressCanvasAtDimensions(
        image,
        width,
        height,
        originalName,
        quality
    ) {
        return new Promise((resolve, reject) => {
            const canvas = document.createElement('canvas');

            canvas.width = width;
            canvas.height = height;

            const context = canvas.getContext(
                '2d',
                { alpha: false }
            );

            if (!context) {
                reject(
                    new Error(
                        'Browser tidak mendukung pemrosesan gambar.'
                    )
                );
                return;
            }

            context.fillStyle = '#ffffff';
            context.fillRect(
                0,
                0,
                width,
                height
            );

            context.imageSmoothingEnabled = true;
            context.imageSmoothingQuality = 'high';

            context.drawImage(
                image,
                0,
                0,
                width,
                height
            );

            canvas.toBlob(
                blob => {
                    if (!blob) {
                        reject(
                            new Error(
                                'Browser gagal membuat file hasil kompresi.'
                            )
                        );
                        return;
                    }

                    resolve(
                        createCompressedFile(
                            blob,
                            originalName
                        )
                    );
                },
                'image/jpeg',
                quality
            );
        });
    }

    function createCompressedFile(blob, originalName) {
        const baseName = String(
            originalName || 'lampiran'
        )
            .replace(/\.[^/.]+$/, '')
            .replace(/[^a-zA-Z0-9_-]/g, '_');

        return new File(
            [blob],
            `${baseName || 'lampiran'}_compressed.jpg`,
            {
                type: 'image/jpeg',
                lastModified: Date.now()
            }
        );
    }

    startCameraBtn?.addEventListener(
        'click',
        startCamera
    );

    async function startCamera() {
        clearCameraError();

        if (
            !navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia
        ) {
            showCameraError(
                'Browser atau perangkat ini tidak mendukung akses kamera.'
            );
            return;
        }

        try {
            stopCameraTracksOnly();

            cameraStream =
                await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: {
                            ideal: 'environment'
                        },
                        width: {
                            ideal: 1920
                        },
                        height: {
                            ideal: 1080
                        }
                    },
                    audio: false
                });

            cameraVideo.srcObject = cameraStream;

            await cameraVideo.play();

            cameraPlaceholder?.classList.add('hidden');

            startCameraBtn.disabled = true;
            captureBtn.disabled = false;
            stopCameraBtn.disabled = false;
        } catch (error) {
            console.error('Camera error:', error);

            showCameraError(
                'Kamera tidak dapat diakses. ' +
                'Pastikan izin kamera diberikan pada browser.'
            );
        }
    }

    captureBtn?.addEventListener(
        'click',
        async () => {
            clearCameraError();

            if (
                !cameraVideo.videoWidth ||
                !cameraVideo.videoHeight
            ) {
                showCameraError(
                    'Kamera belum siap. Silakan tunggu sebentar.'
                );
                return;
            }

            try {
                const width = cameraVideo.videoWidth;
                const height = cameraVideo.videoHeight;

                const dimensions = calculateDimensions(
                    width,
                    height,
                    MAX_IMAGE_DIMENSION
                );

                const canvas = document.createElement('canvas');

                canvas.width = dimensions.width;
                canvas.height = dimensions.height;

                const context = canvas.getContext(
                    '2d',
                    { alpha: false }
                );

                if (!context) {
                    throw new Error(
                        'Browser tidak mendukung canvas.'
                    );
                }

                context.fillStyle = '#ffffff';

                context.fillRect(
                    0,
                    0,
                    canvas.width,
                    canvas.height
                );

                context.imageSmoothingEnabled = true;
                context.imageSmoothingQuality = 'high';

                context.drawImage(
                    cameraVideo,
                    0,
                    0,
                    canvas.width,
                    canvas.height
                );

                const image = await canvasToImage(canvas);

                const compressedFile =
                    await compressImageElement(
                        image,
                        'scan_dokumen.jpg'
                    );

                capturedInput.value =
                    await fileToDataUrl(
                        compressedFile
                    );

                if (previewObjectUrl) {
                    URL.revokeObjectURL(
                        previewObjectUrl
                    );
                }

                previewObjectUrl =
                    URL.createObjectURL(
                        compressedFile
                    );

                if (scanPreviewImage) {
                    scanPreviewImage.src =
                        previewObjectUrl;
                }

                scanResult?.classList.remove('hidden');

                if (scanCompressionInfo) {
                    scanCompressionInfo.textContent =
                        `Hasil scan: ${formatFileSize(compressedFile.size)} • JPEG terkompresi`;
                }

                clearFileSelection();

                stopCameraTracksOnly();

                if (cameraVideo) {
                    cameraVideo.srcObject = null;
                }

                captureBtn.disabled = true;
                stopCameraBtn.disabled = true;
                startCameraBtn.disabled = false;

                cameraPlaceholder?.classList.remove('hidden');
            } catch (error) {
                console.error(
                    'Gagal mengambil scan:',
                    error
                );

                showCameraError(
                    error?.message ||
                    'Gagal memproses hasil scan.'
                );
            }
        }
    );

    function canvasToImage(canvas) {
        return new Promise((resolve, reject) => {
            const image = new Image();

            image.onload = () => {
                resolve(image);
            };

            image.onerror = () => {
                reject(
                    new Error(
                        'Gagal membaca hasil scan.'
                    )
                );
            };

            image.src = canvas.toDataURL(
                'image/jpeg',
                0.95
            );
        });
    }

    retakeBtn?.addEventListener(
        'click',
        async () => {
            clearScanResult();
            capturedInput.value = '';

            await startCamera();
        }
    );

    function clearScanResult() {
        capturedInput.value = '';

        scanResult?.classList.add('hidden');

        if (scanPreviewImage) {
            scanPreviewImage.src = '';
        }

        if (scanCompressionInfo) {
            scanCompressionInfo.textContent = '';
        }

        if (previewObjectUrl) {
            URL.revokeObjectURL(previewObjectUrl);
            previewObjectUrl = null;
        }
    }

    function fileToDataUrl(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = () => {
                resolve(reader.result);
            };

            reader.onerror = () => {
                reject(
                    new Error(
                        'Gagal menyiapkan data scan.'
                    )
                );
            };

            reader.readAsDataURL(file);
        });
    }

    stopCameraBtn?.addEventListener(
        'click',
        stopCamera
    );

    function stopCamera() {
        stopCameraTracksOnly();

        if (cameraVideo) {
            cameraVideo.srcObject = null;
        }

        cameraPlaceholder?.classList.remove('hidden');

        if (captureBtn) {
            captureBtn.disabled = true;
        }

        if (stopCameraBtn) {
            stopCameraBtn.disabled = true;
        }

        if (startCameraBtn) {
            startCameraBtn.disabled = false;
        }
    }

    function stopCameraTracksOnly() {
        if (!cameraStream) {
            return;
        }

        cameraStream
            .getTracks()
            .forEach(track => track.stop());

        cameraStream = null;
    }

    form.addEventListener(
        'submit',
        event => {
            if (isSubmitting) {
                event.preventDefault();
                return;
            }

            if (
                capturedInput.value &&
                capturedInput.value.length > 7_000_000
            ) {
                event.preventDefault();

                alert(
                    'Hasil scan terlalu besar.\n\n' +
                    'Silakan scan ulang dengan resolusi lebih rendah.'
                );

                return;
            }

            const selected = fileInput.files?.[0];

            if (
                selected &&
                selected.size > MAX_FILE_SIZE
            ) {
                event.preventDefault();

                alert(
                    'Ukuran file melebihi 10 MB.'
                );

                return;
            }

            stopCameraTracksOnly();

            isSubmitting = true;

            if (submitBtn) {
                submitBtn.disabled = true;

                submitBtn.classList.add(
                    'opacity-70',
                    'cursor-not-allowed'
                );
            }

            if (submitText) {
                submitText.textContent =
                    'Memproses & menyimpan...';
            }
        }
    );

    window.addEventListener(
        'beforeunload',
        () => {
            stopCameraTracksOnly();

            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = null;
            }
        }
    );
});
</script>

@endsection