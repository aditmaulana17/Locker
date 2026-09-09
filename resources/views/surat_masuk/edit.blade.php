@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FORMAT TANGGAL
    |--------------------------------------------------------------------------
    */

    $tanggalSuratValue = old('tanggal_surat');

    if (
        $tanggalSuratValue === null &&
        !empty($suratMasuk->tanggal_surat)
    ) {
        try {
            $tanggalSuratValue = \Illuminate\Support\Carbon::parse(
                $suratMasuk->tanggal_surat
            )->format('Y-m-d');
        } catch (\Throwable $e) {
            $tanggalSuratValue = substr(
                (string) $suratMasuk->tanggal_surat,
                0,
                10
            );
        }
    }

    $tanggalTerimaValue = old('tanggal_terima');

    if (
        $tanggalTerimaValue === null &&
        !empty($suratMasuk->tanggal_terima)
    ) {
        try {
            $tanggalTerimaValue = \Illuminate\Support\Carbon::parse(
                $suratMasuk->tanggal_terima
            )->format('Y-m-d');
        } catch (\Throwable $e) {
            $tanggalTerimaValue = substr(
                (string) $suratMasuk->tanggal_terima,
                0,
                10
            );
        }
    }

    $currentStatus = strtolower(
        trim(
            (string) old(
                'status',
                $suratMasuk->status ?: 'baru'
            )
        )
    );

    $currentKategori = old(
        'kategori_surat_id',
        $suratMasuk->kategori_surat_id
    );
@endphp


<div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">

        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-800">
                Edit Surat Masuk
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Perbarui informasi data arsip surat masuk yang tersimpan di dalam sistem.
            </p>
        </div>

        <a
            href="{{ route('surat-masuk.index') }}"
            class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border-2 border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 transition"
        >
            <svg
                class="w-4 h-4 mr-2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                />
            </svg>

            Kembali
        </a>

    </div>


    {{-- =========================================================
         ERROR GLOBAL
    ========================================================== --}}
    @if ($errors->any())

        <div class="mb-4 rounded-xl border-2 border-rose-200 bg-rose-50 px-4 py-3">

            <div class="flex items-start gap-3">

                <svg
                    class="w-5 h-5 mt-0.5 text-rose-600 shrink-0"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z"
                    />
                </svg>

                <div class="min-w-0">

                    <p class="font-bold text-sm text-rose-700">
                        Data belum dapat diperbarui.
                    </p>

                    <ul class="mt-1 text-xs text-rose-600 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>
                                • {{ $error }}
                            </li>
                        @endforeach
                    </ul>

                </div>

            </div>

        </div>

    @endif


    {{-- =========================================================
         FORM
    ========================================================== --}}
    <form
        method="POST"
        action="{{ route('surat-masuk.update', $suratMasuk->id) }}"
        enctype="multipart/form-data"
        id="form-surat"
    >

        @csrf
        @method('PUT')

        {{-- Nomor agenda --}}
        <input
            type="hidden"
            name="nomor_agenda"
            value="{{ old('nomor_agenda', $suratMasuk->nomor_agenda) }}"
        >


        <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-sm overflow-hidden">


            {{-- =================================================
                 NOMOR AGENDA
            ================================================== --}}
            <div class="px-4 sm:px-5 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-200">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">

                    <div class="flex items-center min-w-0">

                        <div class="flex items-center justify-center w-9 h-9 mr-3 bg-blue-600 border-2 border-blue-600 rounded-lg text-white shrink-0">

                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 013-3h0a3 3 0 013 3m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                />
                            </svg>

                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-1.5 min-w-0">

                            <span class="text-sm font-semibold text-blue-900">
                                Nomor Agenda Sistem:
                            </span>

                            <strong class="inline-flex items-center w-fit px-2.5 py-1 bg-white border-2 border-blue-200 rounded-lg text-xs font-bold font-mono text-blue-700">
                                {{ $suratMasuk->nomor_agenda }}
                            </strong>

                        </div>

                    </div>

                    <span class="text-xs font-medium text-blue-600">
                        Nomor agenda tidak diubah saat edit
                    </span>

                </div>

            </div>


            {{-- =================================================
                 MAIN CONTENT
            ================================================== --}}
            <div class="p-4 sm:p-5">


                {{-- =================================================
                     INFORMASI UTAMA
                ================================================== --}}
                <section>

                    <div class="section-heading">

                        <div class="section-marker bg-blue-600"></div>

                        <div>

                            <h2 class="section-title">
                                Informasi Utama Surat
                            </h2>

                            <p class="section-description">
                                Perbarui identitas dan informasi utama surat masuk.
                            </p>

                        </div>

                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">


                        {{-- Nomor Surat --}}
                        <div class="form-group">

                            <label
                                for="nomor_surat"
                                class="form-label"
                            >
                                Nomor Surat
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="text"
                                id="nomor_surat"
                                name="nomor_surat"
                                value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}"
                                required
                                autocomplete="off"
                                placeholder="Contoh: 005/B/I/2026"
                                class="form-control-custom @error('nomor_surat') form-error @enderror"
                            >

                            @error('nomor_surat')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Pengirim --}}
                        <div class="form-group">

                            <label
                                for="pengirim"
                                class="form-label"
                            >
                                Instansi Pengirim
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="text"
                                id="pengirim"
                                name="pengirim"
                                value="{{ old('pengirim', $suratMasuk->pengirim) }}"
                                required
                                autocomplete="organization"
                                placeholder="Masukkan nama instansi pengirim..."
                                class="form-control-custom @error('pengirim') form-error @enderror"
                            >

                            @error('pengirim')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Tanggal Surat --}}
                        <div class="form-group">

                            <label
                                for="tanggal_surat"
                                class="form-label"
                            >
                                Tanggal Surat
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="date"
                                id="tanggal_surat"
                                name="tanggal_surat"
                                value="{{ $tanggalSuratValue }}"
                                required
                                class="form-control-custom @error('tanggal_surat') form-error @enderror"
                            >

                            @error('tanggal_surat')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Tanggal Diterima --}}
                        <div class="form-group">

                            <label
                                for="tanggal_terima"
                                class="form-label"
                            >
                                Tanggal Diterima
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="date"
                                id="tanggal_terima"
                                name="tanggal_terima"
                                value="{{ $tanggalTerimaValue }}"
                                required
                                class="form-control-custom @error('tanggal_terima') form-error @enderror"
                            >

                            @error('tanggal_terima')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Kategori --}}
                        <div class="form-group">

                            <label
                                for="kategori_surat_id"
                                class="form-label"
                            >
                                Kategori Surat
                                <span class="text-rose-500">*</span>
                            </label>

                            <div class="relative">

                                <select
                                    id="kategori_surat_id"
                                    name="kategori_surat_id"
                                    required
                                    class="form-control-custom appearance-none pr-10 @error('kategori_surat_id') form-error @enderror"
                                >

                                    <option
                                        value=""
                                        disabled
                                        {{ $currentKategori ? '' : 'selected' }}
                                    >
                                        Pilih kategori surat
                                    </option>

                                    @foreach($kategoris as $kategori)

                                        <option
                                            value="{{ $kategori->id }}"
                                            {{ (string) $currentKategori === (string) $kategori->id ? 'selected' : '' }}
                                        >
                                            {{ $kategori->nama_kategori }}

                                            @if(!empty($kategori->sifat))
                                                ({{ ucfirst($kategori->sifat) }})
                                            @endif
                                        </option>

                                    @endforeach

                                </select>

                                <svg
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>

                            </div>

                            @error('kategori_surat_id')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Status --}}
                        <div class="form-group">

                            <label
                                for="status"
                                class="form-label"
                            >
                                Status Surat
                                <span class="text-rose-500">*</span>
                            </label>

                            <div class="relative">

                                <select
                                    id="status"
                                    name="status"
                                    required
                                    class="form-control-custom appearance-none pr-10 @error('status') form-error @enderror"
                                >

                                    <option
                                        value="baru"
                                        {{ $currentStatus === 'baru' ? 'selected' : '' }}
                                    >
                                        Baru
                                    </option>

                                    <option
                                        value="diproses"
                                        {{ $currentStatus === 'diproses' ? 'selected' : '' }}
                                    >
                                        Diproses
                                    </option>

                                    <option
                                        value="didisposisikan"
                                        {{ $currentStatus === 'didisposisikan' ? 'selected' : '' }}
                                    >
                                        Didisposisikan
                                    </option>

                                    <option
                                        value="selesai"
                                        {{ $currentStatus === 'selesai' ? 'selected' : '' }}
                                    >
                                        Selesai
                                    </option>

                                    <option
                                        value="diarsipkan"
                                        {{ $currentStatus === 'diarsipkan' ? 'selected' : '' }}
                                    >
                                        Diarsipkan
                                    </option>

                                </select>

                                <svg
                                    class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>

                            </div>

                            @error('status')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Perihal --}}
                        <div class="md:col-span-2 form-group">

                            <label
                                for="perihal"
                                class="form-label"
                            >
                                Perihal / Isi Ringkas
                                <span class="text-rose-500">*</span>
                            </label>

                            <textarea
                                id="perihal"
                                name="perihal"
                                rows="3"
                                required
                                placeholder="Tuliskan perihal atau isi ringkas surat secara jelas..."
                                class="form-control-custom form-textarea @error('perihal') form-error @enderror"
                            >{{ old('perihal', $suratMasuk->perihal) }}</textarea>

                            @error('perihal')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     LAMPIRAN
                ================================================== --}}
                <section class="mt-6">

                    <div class="section-heading">

                        <div class="section-marker bg-indigo-600"></div>

                        <div>

                            <h2 class="section-title">
                                Lampiran Dokumen & Arsip Fisik
                            </h2>

                            <p class="section-description">
                                Ganti dokumen digital atau perbarui lokasi penyimpanan fisik.
                            </p>

                        </div>

                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">


                        {{-- =================================================
                             BERKAS DIGITAL
                        ================================================== --}}
                        <div class="archive-card">

                            <div class="archive-card-header">

                                <div>

                                    <h3 class="archive-card-title">
                                        Berkas Digital
                                    </h3>

                                    <p class="archive-card-description">
                                        File lama tetap digunakan jika tidak diganti.
                                    </p>

                                </div>

                                <span class="archive-card-badge">
                                    Opsional
                                </span>

                            </div>


                            {{-- =================================================
                                 FILE SAAT INI
                            ================================================== --}}
                            @if(!empty($suratMasuk->lampiran_file))

                                <div class="current-file">

                                    <div class="current-file-icon">

                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414A1 1 0 0118 8.414V19a2 2 0 01-2 2z"
                                            />
                                        </svg>

                                    </div>


                                    <div class="min-w-0 flex-1">

                                        <p class="text-xs font-bold text-slate-700">
                                            Dokumen saat ini
                                        </p>

                                        <p class="mt-0.5 text-xs text-slate-500 truncate">
                                            {{ basename($suratMasuk->lampiran_file) }}
                                        </p>

                                    </div>


                                    <a
                                        href="{{ route('surat-masuk.preview-lampiran', $suratMasuk->id) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center justify-center px-2.5 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border-2 border-blue-200 rounded-lg hover:bg-blue-100 transition"
                                    >
                                        Lihat
                                    </a>

                                </div>

                            @else

                                <div class="current-file">

                                    <div class="current-file-icon current-file-warning">

                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z"
                                            />
                                        </svg>

                                    </div>


                                    <div class="min-w-0">

                                        <p class="text-xs font-bold text-amber-700">
                                            Belum ada dokumen
                                        </p>

                                        <p class="mt-0.5 text-xs text-amber-600">
                                            Pilih file baru atau gunakan kamera.
                                        </p>

                                    </div>

                                </div>

                            @endif


                            {{-- =================================================
                                 MODE SELECTOR
                            ================================================== --}}
                            <div class="mode-selector">

                                <button
                                    type="button"
                                    id="btn-upload"
                                    class="mode-button mode-button-active"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V4m0 0L8 8m4-4l4 4"
                                        />
                                    </svg>

                                    Upload File
                                </button>


                                <button
                                    type="button"
                                    id="btn-camera"
                                    class="mode-button"
                                >
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 8h2l2-3h10l2 3h2a2 2 0 012 2v9a2 2 0 01-2 2H3a2 2 0 01-2-2v-9a2 2 0 012-2zm9 3a3 3 0 100 6 3 3 0 000-6z"
                                        />
                                    </svg>

                                    Scan Kamera
                                </button>

                            </div>


                            {{-- =================================================
                                 UPLOAD PANEL
                            ================================================== --}}
                            <div id="upload-panel">

                                <label
                                    for="lampiran_file"
                                    id="upload-box"
                                    class="upload-box @error('lampiran_file') upload-box-error @enderror"
                                >

                                    <div class="upload-icon">

                                        <svg
                                            class="w-6 h-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                            />
                                        </svg>

                                    </div>


                                    <span
                                        id="file-label-text"
                                        class="text-sm font-bold text-slate-700 text-center"
                                    >
                                        Pilih file baru
                                    </span>


                                    <span class="mt-1 text-xs text-slate-500 text-center">
                                        PDF, JPG, JPEG, PNG
                                    </span>


                                    <span class="mt-0.5 text-[11px] font-medium text-slate-400 text-center">
                                        Maksimal 15 MB
                                    </span>


                                    <input
                                        type="file"
                                        name="lampiran_file"
                                        id="lampiran_file"
                                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                        class="sr-only"
                                    >

                                </label>


                                {{-- File terpilih --}}
                                <div
                                    id="selected-file"
                                    class="hidden mt-2 px-3 py-2.5 rounded-lg border-2 border-emerald-200 bg-emerald-50"
                                >

                                    <div class="flex items-center gap-2">

                                        <div class="flex items-center justify-center w-7 h-7 rounded-md bg-emerald-100 border border-emerald-200 text-emerald-600 shrink-0">

                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>

                                        </div>


                                        <div class="min-w-0 flex-1">

                                            <p class="text-xs font-bold text-emerald-700">
                                                File baru dipilih
                                            </p>

                                            <p
                                                id="selected-file-name"
                                                class="text-xs text-emerald-600 truncate"
                                            ></p>

                                        </div>


                                        <button
                                            type="button"
                                            id="clear-file-btn"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-md border-2 border-emerald-200 bg-white text-emerald-600 hover:bg-emerald-100 transition"
                                            title="Batalkan file baru"
                                        >
                                            <svg
                                                class="w-3.5 h-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                aria-hidden="true"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>
                                        </button>

                                    </div>

                                </div>


                                <div class="mt-2 px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg">

                                    <p class="text-xs leading-relaxed text-slate-500">
                                        Tidak memilih file baru berarti dokumen lama tetap dipertahankan.
                                    </p>

                                </div>

                            </div>


                            {{-- =================================================
                                 CAMERA PANEL
                            ================================================== --}}
                            <div
                                id="camera-panel"
                                class="hidden"
                            >

                                <div
                                    id="camera-container"
                                    class="camera-container"
                                >

                                    <video
                                        id="video"
                                        autoplay
                                        muted
                                        playsinline
                                        class="w-full h-full object-cover"
                                    ></video>


                                    <img
                                        id="image-preview"
                                        src=""
                                        alt="Preview hasil scan"
                                        class="hidden absolute inset-0 w-full h-full object-contain bg-slate-950"
                                    >


                                    <div
                                        id="camera-placeholder"
                                        class="camera-placeholder"
                                    >
                                        Kamera belum aktif
                                    </div>

                                </div>


                                <div class="mt-2 flex flex-wrap justify-center gap-2">

                                    <button
                                        type="button"
                                        id="start-cam-btn"
                                        class="camera-button camera-start"
                                    >
                                        Nyalakan Kamera
                                    </button>


                                    <button
                                        type="button"
                                        id="capture-btn"
                                        class="hidden camera-button camera-capture"
                                    >
                                        Ambil Foto
                                    </button>


                                    <button
                                        type="button"
                                        id="retake-btn"
                                        class="hidden camera-button camera-retake"
                                    >
                                        Foto Ulang
                                    </button>


                                    <button
                                        type="button"
                                        id="stop-cam-btn"
                                        class="hidden camera-button camera-stop"
                                    >
                                        Tutup Kamera
                                    </button>

                                </div>


                                <input
                                    type="hidden"
                                    name="captured_image"
                                    id="captured_image"
                                    value="{{ old('captured_image') }}"
                                >


                                <div
                                    id="snapshot-preview"
                                    class="{{ old('captured_image') ? '' : 'hidden' }} mt-2 text-center text-xs font-bold text-emerald-600"
                                >
                                    ✓ Hasil scan berhasil diambil.
                                </div>


                                <div class="mt-2 px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg">

                                    <p class="text-xs leading-relaxed text-slate-500">
                                        Mengambil foto baru akan mengganti file lampiran lama.
                                    </p>

                                </div>

                            </div>


                            {{-- Error --}}
                            <div class="mt-2">

                                @error('lampiran_file')
                                    <p class="form-error-text">
                                        {{ $message }}
                                    </p>
                                @enderror

                                @error('captured_image')
                                    <p class="form-error-text">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>


                        {{-- =================================================
                             LOKASI ARSIP FISIK
                        ================================================== --}}
                        <div class="archive-card">

                            <div class="archive-card-header">

                                <div>

                                    <h3 class="archive-card-title">
                                        Lokasi Arsip Fisik
                                    </h3>

                                    <p class="archive-card-description">
                                        Perbarui lokasi penyimpanan arsip fisik.
                                    </p>

                                </div>

                                <span class="archive-card-badge">
                                    Opsional
                                </span>

                            </div>


                            <div class="location-box">

                                <div class="location-icon">

                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                        />
                                    </svg>

                                </div>


                                <h4 class="text-sm font-bold text-slate-700">
                                    Lokasi Penyimpanan
                                </h4>


                                <p class="mt-1 text-xs leading-relaxed text-slate-400 text-center">
                                    Masukkan posisi rak, lemari, box, atau map tempat arsip disimpan.
                                </p>


                                <div class="w-full mt-4">

                                    <label
                                        for="lokasi_arsip_fisik"
                                        class="form-label"
                                    >
                                        Detail Posisi Lemari / Box
                                    </label>


                                    <input
                                        type="text"
                                        id="lokasi_arsip_fisik"
                                        name="lokasi_arsip_fisik"
                                        value="{{ old('lokasi_arsip_fisik', $suratMasuk->lokasi_arsip_fisik) }}"
                                        placeholder="Contoh: Rak A-3 Box 12"
                                        class="form-control-custom @error('lokasi_arsip_fisik') form-error @enderror"
                                    >


                                    @error('lokasi_arsip_fisik')
                                        <p class="form-error-text">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>


                                <div class="w-full mt-3 px-3 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-lg">

                                    <p class="text-xs leading-relaxed text-slate-500">

                                        Contoh:

                                        <span class="font-semibold text-slate-700">
                                            Rak A-3 Box 12
                                        </span>

                                        atau

                                        <span class="font-semibold text-slate-700">
                                            Lemari B-2 Map 07
                                        </span>.

                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </section>

            </div>


            {{-- =================================================
                 FOOTER
            ================================================== --}}
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 px-4 sm:px-5 py-3 bg-slate-50 border-t-2 border-slate-300">

                <a
                    href="{{ route('surat-masuk.index') }}"
                    class="action-button action-button-secondary"
                >
                    Batal
                </a>


                <button
                    type="submit"
                    id="submit-btn"
                    class="action-button action-button-primary"
                >
                    <svg
                        class="w-4 h-4 mr-2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>

                    <span id="submit-text">
                        Perbarui Surat Masuk
                    </span>
                </button>

            </div>

        </div>

    </form>

</div>


{{-- =============================================================
     STYLE
============================================================= --}}
@push('styles')
<style>
    .section-heading {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 14px;
    }

    .section-marker {
        width: 4px;
        min-width: 4px;
        height: 38px;
        border-radius: 999px;
        margin-top: 2px;
    }

    .section-title {
        font-size: 0.95rem;
        line-height: 1.35rem;
        font-weight: 800;
        color: #1e293b;
    }

    .section-description {
        margin-top: 2px;
        font-size: 0.75rem;
        line-height: 1.15rem;
        color: #64748b;
    }

    .form-group {
        min-width: 0;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-size: 0.75rem;
        line-height: 1rem;
        font-weight: 800;
        color: #334155;
    }

    .form-control-custom {
        display: block;
        width: 100%;
        min-height: 42px;
        padding: 9px 12px;
        border: 2px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff;
        color: #1e293b;
        font-size: 0.875rem;
        line-height: 1.25rem;
        outline: none;
        transition:
            border-color 0.15s ease,
            box-shadow 0.15s ease,
            background-color 0.15s ease;
    }

    .form-control-custom:hover {
        border-color: #94a3b8;
    }

    .form-control-custom:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
    }

    .form-control-custom::placeholder {
        color: #94a3b8;
    }

    .form-textarea {
        min-height: 82px;
        resize: vertical;
    }

    .form-error {
        border-color: #f43f5e !important;
        background: #fff1f2 !important;
    }

    .form-error-text {
        margin-top: 5px;
        font-size: 0.7rem;
        line-height: 1rem;
        font-weight: 600;
        color: #e11d48;
    }

    .archive-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
        padding: 14px;
        border: 2px solid #cbd5e1;
        border-radius: 14px;
        background: #ffffff;
        transition: border-color 0.15s ease;
    }

    .archive-card:hover {
        border-color: #94a3b8;
    }

    .archive-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
    }

    .archive-card-title {
        font-size: 0.875rem;
        line-height: 1.2rem;
        font-weight: 800;
        color: #334155;
    }

    .archive-card-description {
        margin-top: 2px;
        font-size: 0.7rem;
        line-height: 1rem;
        color: #94a3b8;
    }

    .archive-card-badge {
        display: inline-flex;
        flex-shrink: 0;
        align-items: center;
        padding: 4px 8px;
        border: 2px solid #cbd5e1;
        border-radius: 999px;
        background: #f8fafc;
        color: #64748b;
        font-size: 0.65rem;
        line-height: 1;
        font-weight: 800;
    }

    .archive-card-badge.required {
        border-color: #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .current-file {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 10px;
        padding: 9px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }

    .current-file-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        min-width: 32px;
        height: 32px;
        border: 2px solid #bfdbfe;
        border-radius: 8px;
        background: #eff6ff;
        color: #2563eb;
    }

    .current-file-warning {
        border-color: #fde68a;
        background: #fffbeb;
        color: #d97706;
    }

    .mode-selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
        margin-bottom: 10px;
        padding: 4px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }

    .mode-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 38px;
        padding: 7px 10px;
        border: 2px solid transparent;
        border-radius: 8px;
        background: transparent;
        color: #64748b;
        font-size: 0.75rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .mode-button:hover {
        background: #ffffff;
        color: #334155;
    }

    .mode-button-active {
        border-color: #bfdbfe;
        background: #ffffff;
        color: #1d4ed8;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    }

    .upload-box {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 150px;
        padding: 18px;
        border: 2px dashed #94a3b8;
        border-radius: 12px;
        background: #f8fafc;
        cursor: pointer;
        overflow: hidden;
        transition: all 0.15s ease;
    }

    .upload-box:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .upload-box-error {
        border-color: #fb7185;
        background: #fff1f2;
    }

    .upload-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        margin-bottom: 9px;
        border: 2px solid #bfdbfe;
        border-radius: 10px;
        background: #eff6ff;
        color: #2563eb;
    }

    .camera-container {
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 3;
        overflow: hidden;
        background: #020617;
        border: 2px solid #334155;
        border-radius: 12px;
    }

    .camera-container video {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .camera-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        color: #cbd5e1;
        font-size: 0.8rem;
        font-weight: 700;
        text-align: center;
        background: rgba(2, 6, 23, 0.35);
    }

    .camera-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 8px 12px;
        border-width: 2px;
        border-radius: 9px;
        color: #ffffff;
        font-size: 0.75rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .camera-start {
        background: #2563eb;
        border-color: #2563eb;
    }

    .camera-start:hover {
        background: #1d4ed8;
    }

    .camera-capture {
        background: #059669;
        border-color: #059669;
    }

    .camera-capture:hover {
        background: #047857;
    }

    .camera-retake {
        background: #d97706;
        border-color: #d97706;
    }

    .camera-retake:hover {
        background: #b45309;
    }

    .camera-stop {
        background: #e11d48;
        border-color: #e11d48;
    }

    .camera-stop:hover {
        background: #be123c;
    }

    .location-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 100%;
        padding: 18px 14px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: #f8fafc;
        text-align: center;
    }

    .location-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        margin-bottom: 10px;
        border: 2px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff;
        color: #475569;
    }

    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 9px 15px;
        border: 2px solid transparent;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .action-button-secondary {
        border-color: #cbd5e1;
        background: #ffffff;
        color: #475569;
    }

    .action-button-secondary:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }

    .action-button-primary {
        border-color: #2563eb;
        background: #2563eb;
        color: #ffffff;
    }

    .action-button-primary:hover {
        border-color: #1d4ed8;
        background: #1d4ed8;
    }

    .action-button-primary:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    @media (max-width: 640px) {

        .mode-selector {
            grid-template-columns: 1fr;
        }

        .archive-card {
            padding: 12px;
        }

        .camera-container {
            min-height: 210px;
        }
    }
</style>
@endpush


{{-- =============================================================
     JAVASCRIPT
============================================================= --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ELEMENT
    |--------------------------------------------------------------------------
    */

    const form =
        document.getElementById('form-surat');

    const fileInput =
        document.getElementById('lampiran_file');

    const capturedInput =
        document.getElementById('captured_image');

    const uploadPanel =
        document.getElementById('upload-panel');

    const cameraPanel =
        document.getElementById('camera-panel');

    const btnUpload =
        document.getElementById('btn-upload');

    const btnCamera =
        document.getElementById('btn-camera');

    const uploadBox =
        document.getElementById('upload-box');

    const fileLabelText =
        document.getElementById('file-label-text');

    const selectedFile =
        document.getElementById('selected-file');

    const selectedFileName =
        document.getElementById('selected-file-name');

    const clearFileButton =
        document.getElementById('clear-file-btn');

    const video =
        document.getElementById('video');

    const imagePreview =
        document.getElementById('image-preview');

    const cameraPlaceholder =
        document.getElementById('camera-placeholder');

    const startCamButton =
        document.getElementById('start-cam-btn');

    const captureButton =
        document.getElementById('capture-btn');

    const retakeButton =
        document.getElementById('retake-btn');

    const stopCamButton =
        document.getElementById('stop-cam-btn');

    const snapshotPreview =
        document.getElementById('snapshot-preview');

    const submitButton =
        document.getElementById('submit-btn');

    const submitText =
        document.getElementById('submit-text');


    const MAX_FILE_SIZE =
        15 * 1024 * 1024;


    let mediaStream = null;
    let imageCapture = null;
    let isSubmitting = false;


    /*
    |--------------------------------------------------------------------------
    | RESET FILE
    |--------------------------------------------------------------------------
    */

    function resetFileDisplay() {

        if (fileLabelText) {

            fileLabelText.textContent =
                'Pilih file baru';

            fileLabelText.classList.remove(
                'text-blue-600',
                'text-rose-600'
            );

            fileLabelText.classList.add(
                'text-slate-700'
            );
        }

        if (selectedFile) {

            selectedFile.classList.add(
                'hidden'
            );
        }

        if (selectedFileName) {

            selectedFileName.textContent =
                '';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR FILE
    |--------------------------------------------------------------------------
    */

    function clearUploadFile() {

        if (fileInput) {
            fileInput.value = '';
        }

        resetFileDisplay();
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR CAMERA
    |--------------------------------------------------------------------------
    */

    function clearCapturedImage() {

        if (capturedInput) {
            capturedInput.value = '';
        }

        if (imagePreview) {

            imagePreview.src = '';

            imagePreview.classList.add(
                'hidden'
            );
        }

        if (snapshotPreview) {

            snapshotPreview.classList.add(
                'hidden'
            );
        }

        if (video) {

            video.classList.remove(
                'hidden'
            );
        }

        if (cameraPlaceholder) {

            cameraPlaceholder.classList.remove(
                'hidden'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | STOP CAMERA
    |--------------------------------------------------------------------------
    */

    function stopCamera() {

        if (mediaStream) {

            mediaStream
                .getTracks()
                .forEach(function (track) {

                    try {
                        track.stop();
                    } catch (error) {
                        console.warn(error);
                    }

                });

            mediaStream = null;
        }

        imageCapture = null;

        if (video) {
            video.srcObject = null;
        }

        if (startCamButton) {
            startCamButton.classList.remove(
                'hidden'
            );
        }

        if (captureButton) {
            captureButton.classList.add(
                'hidden'
            );
        }

        if (retakeButton) {
            retakeButton.classList.add(
                'hidden'
            );
        }

        if (stopCamButton) {
            stopCamButton.classList.add(
                'hidden'
            );
        }

        if (
            cameraPlaceholder &&
            capturedInput &&
            !capturedInput.value
        ) {
            cameraPlaceholder.classList.remove(
                'hidden'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SWITCH MODE
    |--------------------------------------------------------------------------
    */

    function switchMode(mode) {

        stopCamera();

        if (mode === 'camera') {

            btnUpload.classList.remove(
                'mode-button-active'
            );

            btnCamera.classList.add(
                'mode-button-active'
            );

            uploadPanel.classList.add(
                'hidden'
            );

            cameraPanel.classList.remove(
                'hidden'
            );

            /*
             * File upload dibersihkan ketika
             * user memilih mode kamera.
             */
            clearUploadFile();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | UPLOAD MODE
        |--------------------------------------------------------------------------
        */

        btnCamera.classList.remove(
            'mode-button-active'
        );

        btnUpload.classList.add(
            'mode-button-active'
        );

        cameraPanel.classList.add(
            'hidden'
        );

        uploadPanel.classList.remove(
            'hidden'
        );

        /*
         * Hasil kamera dibersihkan.
         */
        clearCapturedImage();
    }


    /*
    |--------------------------------------------------------------------------
    | FILE CHANGE
    |--------------------------------------------------------------------------
    |
    | Tidak ada pengecekan file.type.
    |
    | JPG/JPEG dibiarkan sebagai file asli.
    |
    */

    if (fileInput) {

        fileInput.addEventListener(
            'change',
            function () {

                if (
                    !this.files ||
                    this.files.length === 0
                ) {
                    resetFileDisplay();
                    return;
                }

                const file =
                    this.files[0];


                /*
                |--------------------------------------------------------------------------
                | BATAS UKURAN
                |--------------------------------------------------------------------------
                */

                if (
                    file.size >
                    MAX_FILE_SIZE
                ) {

                    this.value = '';

                    resetFileDisplay();

                    if (fileLabelText) {

                        fileLabelText.textContent =
                            'Ukuran file melebihi 15 MB';

                        fileLabelText.classList.remove(
                            'text-slate-700',
                            'text-blue-600'
                        );

                        fileLabelText.classList.add(
                            'text-rose-600'
                        );

                    }

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | FILE DIPILIH
                |--------------------------------------------------------------------------
                */

                clearCapturedImage();

                if (fileLabelText) {

                    fileLabelText.textContent =
                        'File baru dipilih';

                    fileLabelText.classList.remove(
                        'text-slate-700',
                        'text-rose-600'
                    );

                    fileLabelText.classList.add(
                        'text-blue-600'
                    );
                }


                if (selectedFile) {

                    selectedFile.classList.remove(
                        'hidden'
                    );

                }


                if (selectedFileName) {

                    selectedFileName.textContent =
                        file.name;

                }


                if (uploadBox) {

                    uploadBox.classList.remove(
                        'upload-box-error'
                    );

                }


                stopCamera();
            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR FILE BUTTON
    |--------------------------------------------------------------------------
    */

    if (clearFileButton) {

        clearFileButton.addEventListener(
            'click',
            function (event) {

                event.preventDefault();

                event.stopPropagation();

                clearUploadFile();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | START CAMERA
    |--------------------------------------------------------------------------
    */

    async function startCamera() {

        if (
            !navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia
        ) {

            alert(
                'Browser tidak mendukung akses kamera.'
            );

            return;
        }


        try {

            stopCamera();

            clearCapturedImage();

            clearUploadFile();


            mediaStream =
                await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: {
                            ideal: 'environment'
                        },

                        width: {
                            ideal: 1280
                        },

                        height: {
                            ideal: 720
                        }
                    },

                    audio: false
                });


            video.srcObject =
                mediaStream;

            await video.play();


            /*
            |--------------------------------------------------------------------------
            | IMAGE CAPTURE
            |--------------------------------------------------------------------------
            */

            const track =
                mediaStream.getVideoTracks()[0];

            if (
                track &&
                typeof ImageCapture !==
                'undefined'
            ) {

                imageCapture =
                    new ImageCapture(track);

            }


            cameraPlaceholder.classList.add(
                'hidden'
            );

            startCamButton.classList.add(
                'hidden'
            );

            captureButton.classList.remove(
                'hidden'
            );

            stopCamButton.classList.remove(
                'hidden'
            );

            retakeButton.classList.add(
                'hidden'
            );

        } catch (error) {

            console.error(
                'Camera error:',
                error
            );

            stopCamera();

            alert(
                'Kamera tidak dapat diakses. Pastikan izin kamera telah diberikan.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | PROCESS CAMERA BLOB
    |--------------------------------------------------------------------------
    */

    async function processCameraBlob(blob) {

        if (!blob) {

            throw new Error(
                'Hasil kamera kosong.'
            );
        }


        if (
            blob.size >
            MAX_FILE_SIZE
        ) {

            throw new Error(
                'Ukuran hasil kamera melebihi 15 MB.'
            );
        }


        const reader =
            new FileReader();


        const dataUrl =
            await new Promise(
                function (resolve, reject) {

                    reader.onload =
                        function () {
                            resolve(
                                reader.result
                            );
                        };

                    reader.onerror =
                        function () {
                            reject(
                                new Error(
                                    'Gagal membaca hasil kamera.'
                                )
                            );
                        };

                    reader.readAsDataURL(
                        blob
                    );
                }
            );


        if (
            typeof dataUrl !== 'string' ||
            !dataUrl.startsWith(
                'data:image/'
            )
        ) {

            throw new Error(
                'Data hasil kamera tidak valid.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | SIMPAN HASIL KAMERA
        |--------------------------------------------------------------------------
        */

        capturedInput.value =
            dataUrl;


        clearUploadFile();


        imagePreview.src =
            dataUrl;

        imagePreview.classList.remove(
            'hidden'
        );

        video.classList.add(
            'hidden'
        );

        cameraPlaceholder.classList.add(
            'hidden'
        );

        snapshotPreview.classList.remove(
            'hidden'
        );

        startCamButton.classList.add(
            'hidden'
        );

        captureButton.classList.add(
            'hidden'
        );

        retakeButton.classList.remove(
            'hidden'
        );

        stopCamButton.classList.add(
            'hidden'
        );


        /*
        |--------------------------------------------------------------------------
        | STREAM SUDAH TIDAK DIPERLUKAN
        |--------------------------------------------------------------------------
        */

        stopCamera();
    }


    /*
    |--------------------------------------------------------------------------
    | TAKE SNAPSHOT
    |--------------------------------------------------------------------------
    */

    async function takeSnapshot() {

        if (!mediaStream) {

            alert(
                'Kamera belum aktif.'
            );

            return;
        }


        try {

            const track =
                mediaStream.getVideoTracks()[0];


            if (!track) {

                throw new Error(
                    'Track kamera tidak tersedia.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | IMAGE CAPTURE
            |--------------------------------------------------------------------------
            */

            if (
                imageCapture &&
                typeof imageCapture.takePhoto ===
                'function'
            ) {

                const blob =
                    await imageCapture.takePhoto();

                await processCameraBlob(
                    blob
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | FALLBACK CANVAS
            |--------------------------------------------------------------------------
            */

            const canvas =
                document.createElement(
                    'canvas'
                );

            canvas.width =
                video.videoWidth || 1280;

            canvas.height =
                video.videoHeight || 720;


            const context =
                canvas.getContext('2d');


            if (!context) {

                throw new Error(
                    'Canvas kamera tidak tersedia.'
                );
            }


            context.drawImage(
                video,
                0,
                0,
                canvas.width,
                canvas.height
            );


            canvas.toBlob(
                async function (blob) {

                    try {

                        await processCameraBlob(
                            blob
                        );

                    } catch (error) {

                        console.error(
                            error
                        );

                        alert(
                            error.message ||
                            'Gagal memproses hasil kamera.'
                        );
                    }

                },
                'image/jpeg',
                0.9
            );

        } catch (error) {

            console.error(
                'Capture error:',
                error
            );

            alert(
                error.message ||
                'Gagal mengambil foto dari kamera.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RETAKE
    |--------------------------------------------------------------------------
    */

    function retakeSnapshot() {

        clearCapturedImage();

        startCamera();
    }


    /*
    |--------------------------------------------------------------------------
    | BUTTON EVENTS
    |--------------------------------------------------------------------------
    */

    btnUpload.addEventListener(
        'click',
        function () {
            switchMode('upload');
        }
    );


    btnCamera.addEventListener(
        'click',
        function () {
            switchMode('camera');
        }
    );


    startCamButton.addEventListener(
        'click',
        startCamera
    );


    captureButton.addEventListener(
        'click',
        takeSnapshot
    );


    retakeButton.addEventListener(
        'click',
        retakeSnapshot
    );


    stopCamButton.addEventListener(
        'click',
        stopCamera
    );


    /*
    |--------------------------------------------------------------------------
    | SUBMIT
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        function (event) {

            if (isSubmitting) {

                event.preventDefault();

                return;
            }


            const hasFile =
                fileInput &&
                fileInput.files &&
                fileInput.files.length > 0;


            const hasCamera =
                capturedInput &&
                capturedInput.value.trim() !== '';


            /*
            |--------------------------------------------------------------------------
            | FILE + CAMERA TIDAK BOLEH BERSAMAAN
            |--------------------------------------------------------------------------
            */

            if (
                hasFile &&
                hasCamera
            ) {

                event.preventDefault();

                alert(
                    'Gunakan salah satu metode saja: Upload File atau Scan Kamera.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | EDIT BOLEH TANPA FILE BARU
            |--------------------------------------------------------------------------
            |
            | Controller akan mempertahankan file lama.
            |
            */

            isSubmitting =
                true;


            submitButton.disabled =
                true;


            submitText.textContent =
                'Menyimpan...';


            stopCamera();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | STATE AWAL
    |--------------------------------------------------------------------------
    */

    switchMode(
        'upload'
    );


    /*
    |--------------------------------------------------------------------------
    | RESTORE CAMERA SETELAH VALIDATION ERROR
    |--------------------------------------------------------------------------
    */

    @if(old('captured_image'))

        switchMode('camera');

        imagePreview.src =
            @json(old('captured_image'));

        imagePreview.classList.remove(
            'hidden'
        );

        video.classList.add(
            'hidden'
        );

        cameraPlaceholder.classList.add(
            'hidden'
        );

        snapshotPreview.classList.remove(
            'hidden'
        );

        startCamButton.classList.add(
            'hidden'
        );

        retakeButton.classList.remove(
            'hidden'
        );

    @endif


    /*
    |--------------------------------------------------------------------------
    | STOP CAMERA SAAT PAGE DITINGGALKAN
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'beforeunload',
        function () {

            stopCamera();

        }
    );

});
</script>
@endpush

@endsection