@extends('layouts.app')

@section('title', 'Catat Surat Masuk')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Error Bag
    |--------------------------------------------------------------------------
    | Aman ketika view dirender melalui request normal maupun Tinker.
    */
    $formErrors = $errors ?? session('errors');

    if (
        !$formErrors ||
        !is_object($formErrors) ||
        !method_exists($formErrors, 'any')
    ) {
        $formErrors = new \Illuminate\Support\ViewErrorBag();

        $messageBag = session('errors');

        if ($messageBag instanceof \Illuminate\Support\MessageBag) {
            $formErrors->put('default', $messageBag);
        }
    }
@endphp

{{-- ================================================================
     HEADER
================================================================ --}}
<div class="flex flex-col gap-2 mb-3 sm:flex-row sm:items-center sm:justify-between">

    <div class="min-w-0">
        <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">
            Catat Surat Masuk
        </h1>

        <p class="mt-0.5 text-sm text-slate-500">
            Isi formulir di bawah ini untuk menambahkan data arsip surat masuk baru.
        </p>
    </div>

    <a
        href="{{ route('surat-masuk.index') }}"
        class="inline-flex items-center justify-center w-full px-4 py-2 text-sm font-semibold text-slate-700 bg-white border-2 border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 transition sm:w-auto"
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

{{-- ================================================================
     GENERAL VALIDATION ERROR
================================================================ --}}
@if($formErrors->any())

    <div class="mb-3 rounded-xl border-2 border-rose-200 bg-rose-50 px-4 py-3">

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

                <p class="text-sm font-bold text-rose-700">
                    Data belum dapat disimpan.
                </p>

                <ul class="mt-1 space-y-0.5 text-xs text-rose-600">

                    @foreach($formErrors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        </div>

    </div>

@endif

{{-- ================================================================
     FORM
================================================================ --}}
<form
    method="POST"
    action="{{ route('surat-masuk.store') }}"
    enctype="multipart/form-data"
    id="form-surat"
    novalidate
>

    @csrf

    {{-- Nomor agenda dikirim ke controller --}}
    <input
        type="hidden"
        name="nomor_agenda"
        value="{{ old('nomor_agenda', $nomorAgenda ?? '') }}"
    >

    <div class="overflow-hidden bg-white border-2 border-slate-300 rounded-2xl shadow-sm">

        {{-- ========================================================
             NOMOR AGENDA
        ========================================================= --}}
        <div class="px-4 py-2.5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-200 sm:px-5">

            <div class="flex flex-col gap-1.5 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center min-w-0">

                    <div class="flex items-center justify-center w-8 h-8 mr-2.5 bg-blue-100 border-2 border-blue-200 rounded-lg text-blue-600 shrink-0">

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
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 01-3-3h0a3 3 0 013 3m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                            />
                        </svg>

                    </div>

                    <div class="flex flex-col gap-1 min-w-0 sm:flex-row sm:items-center">

                        <span class="text-sm font-medium text-blue-900">
                            Nomor Agenda Sistem:
                        </span>

                        <strong class="inline-flex items-center w-fit px-2.5 py-1 bg-white border-2 border-blue-200 rounded-lg text-xs font-bold font-mono text-blue-700">
                            {{ $nomorAgenda ?? '-' }}
                        </strong>

                    </div>

                </div>

                <span class="text-xs font-medium text-blue-600">
                    Nomor agenda dibuat otomatis oleh sistem
                </span>

            </div>

        </div>

        {{-- ========================================================
             MAIN CONTENT
        ========================================================= --}}
        <div class="p-4 sm:p-5">

            {{-- ====================================================
                 INFORMASI UTAMA
            ===================================================== --}}
            <section>

                <div class="section-heading">

                    <div class="section-marker bg-blue-600"></div>

                    <div>
                        <h2 class="section-title">
                            Informasi Utama Surat
                        </h2>

                        <p class="section-description">
                            Lengkapi identitas dan informasi utama surat masuk.
                        </p>
                    </div>

                </div>

                <div class="grid grid-cols-1 gap-x-4 gap-y-3 md:grid-cols-2">

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
                            value="{{ old('nomor_surat') }}"
                            required
                            autocomplete="off"
                            placeholder="Contoh: 005/B/I/2026"
                            class="form-control-custom @if($formErrors->has('nomor_surat')) form-error @endif"
                        >

                        @if($formErrors->has('nomor_surat'))
                            <p class="form-error-text">
                                {{ $formErrors->first('nomor_surat') }}
                            </p>
                        @endif

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
                            value="{{ old('pengirim') }}"
                            required
                            autocomplete="organization"
                            placeholder="Masukkan nama pengirim surat..."
                            class="form-control-custom @if($formErrors->has('pengirim')) form-error @endif"
                        >

                        @if($formErrors->has('pengirim'))
                            <p class="form-error-text">
                                {{ $formErrors->first('pengirim') }}
                            </p>
                        @endif

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
                            value="{{ old('tanggal_surat') }}"
                            required
                            class="form-control-custom @if($formErrors->has('tanggal_surat')) form-error @endif"
                        >

                        @if($formErrors->has('tanggal_surat'))
                            <p class="form-error-text">
                                {{ $formErrors->first('tanggal_surat') }}
                            </p>
                        @endif

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
                            value="{{ old('tanggal_terima', now()->format('Y-m-d')) }}"
                            required
                            class="form-control-custom @if($formErrors->has('tanggal_terima')) form-error @endif"
                        >

                        @if($formErrors->has('tanggal_terima'))
                            <p class="form-error-text">
                                {{ $formErrors->first('tanggal_terima') }}
                            </p>
                        @endif

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
                                class="form-control-custom appearance-none pr-10 @if($formErrors->has('kategori_surat_id')) form-error @endif"
                            >

                                <option
                                    value=""
                                    disabled
                                    @selected(!old('kategori_surat_id'))
                                >
                                    Pilih kategori surat
                                </option>

                                @foreach(($kategoris ?? collect()) as $kategori)

                                    <option
                                        value="{{ $kategori->id }}"
                                        @selected(
                                            (string) old('kategori_surat_id') ===
                                            (string) $kategori->id
                                        )
                                    >
                                        {{ $kategori->nama_kategori }}

                                        @if(!empty($kategori->sifat))
                                            ({{ ucfirst($kategori->sifat) }})
                                        @endif
                                    </option>

                                @endforeach

                            </select>

                            <svg
                                class="absolute right-3 top-1/2 w-4 h-4 text-slate-500 -translate-y-1/2 pointer-events-none"
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

                        @if($formErrors->has('kategori_surat_id'))
                            <p class="form-error-text">
                                {{ $formErrors->first('kategori_surat_id') }}
                            </p>
                        @endif

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
                                class="form-control-custom appearance-none pr-10 @if($formErrors->has('status')) form-error @endif"
                            >

                                @foreach([
                                    'baru' => 'Baru',
                                    'diproses' => 'Diproses',
                                    'didisposisikan' => 'Didisposisikan',
                                    'selesai' => 'Selesai',
                                    'diarsipkan' => 'Diarsipkan',
                                ] as $value => $label)

                                    <option
                                        value="{{ $value }}"
                                        @selected(old('status', 'baru') === $value)
                                    >
                                        {{ $label }}
                                    </option>

                                @endforeach

                            </select>

                            <svg
                                class="absolute right-3 top-1/2 w-4 h-4 text-slate-500 -translate-y-1/2 pointer-events-none"
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

                        @if($formErrors->has('status'))
                            <p class="form-error-text">
                                {{ $formErrors->first('status') }}
                            </p>
                        @endif

                    </div>

                    {{-- Perihal --}}
                    <div class="md:col-span-2 form-group">

                        <label
                            for="perihal"
                            class="form-label"
                        >
                            Perihal
                            <span class="text-rose-500">*</span>
                        </label>

                        <textarea
                            id="perihal"
                            name="perihal"
                            rows="2"
                            required
                            placeholder="Tuliskan perihal surat secara jelas..."
                            class="form-control-custom form-textarea @if($formErrors->has('perihal')) form-error @endif"
                        >{{ old('perihal') }}</textarea>

                        @if($formErrors->has('perihal'))
                            <p class="form-error-text">
                                {{ $formErrors->first('perihal') }}
                            </p>
                        @endif

                    </div>

                    {{-- Ringkasan --}}
                    <div class="md:col-span-2 form-group">

                        <label
                            for="ringkasan"
                            class="form-label"
                        >
                            Ringkasan
                        </label>

                        <textarea
                            id="ringkasan"
                            name="ringkasan"
                            rows="3"
                            placeholder="Ringkasan isi surat, bila diperlukan..."
                            class="form-control-custom form-textarea @if($formErrors->has('ringkasan')) form-error @endif"
                        >{{ old('ringkasan') }}</textarea>

                        @if($formErrors->has('ringkasan'))
                            <p class="form-error-text">
                                {{ $formErrors->first('ringkasan') }}
                            </p>
                        @endif

                    </div>

                </div>

            </section>

            {{-- ====================================================
                 LAMPIRAN
            ===================================================== --}}
            <section class="mt-5">

                <div class="section-heading">

                    <div class="section-marker bg-indigo-600"></div>

                    <div>
                        <h2 class="section-title">
                            Lampiran Dokumen & Arsip Fisik
                        </h2>

                        <p class="section-description">
                            Upload dokumen digital atau scan langsung menggunakan kamera.
                        </p>
                    </div>

                </div>

                <div class="grid grid-cols-1 items-stretch gap-3 md:grid-cols-2">

                    {{-- =================================================
                         BERKAS DIGITAL
                    ================================================== --}}
                    <div class="archive-card">

                        <div class="archive-card-header">

                            <div>

                                <h3 class="archive-card-title">
                                    Berkas Digital
                                    <span class="text-rose-500">*</span>
                                </h3>

                                <p class="archive-card-description">
                                    Upload PDF, JPG, JPEG, atau PNG. Anda juga dapat menggunakan kamera.
                                </p>

                            </div>

                            <span class="archive-card-badge required">
                                Wajib
                            </span>

                        </div>

                        <div class="mb-2 flex flex-col gap-1 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2">

                            <span class="text-[11px] font-semibold text-blue-700">
                                Maksimal 10 MB
                            </span>

                            <span class="text-[10px] leading-relaxed text-blue-600">
                                PDF disimpan tanpa kompresi. Gambar akan diperkecil dan dikompres di browser sebelum dikirim.
                            </span>

                        </div>

                        {{-- MODE SELECTOR --}}
                        <div class="mode-selector">

                            <button
                                type="button"
                                id="btn-upload"
                                class="mode-button mode-button-active"
                                aria-controls="upload-panel"
                                aria-selected="true"
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
                                aria-controls="camera-panel"
                                aria-selected="false"
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
                             UPLOAD MODE
                        ================================================== --}}
                        <div id="upload-panel">

                            <label
                                for="lampiran_file"
                                id="upload-box"
                                class="upload-box @if($formErrors->has('lampiran_file')) upload-box-error @endif"
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
                                    class="text-sm font-bold text-center text-slate-700"
                                >
                                    Klik untuk memilih file
                                </span>

                                <span class="mt-1 text-xs text-center text-slate-500">
                                    PDF, JPG, JPEG, PNG
                                </span>

                                <span class="mt-0.5 text-[11px] font-medium text-center text-slate-400">
                                    Gambar otomatis dikompres
                                </span>

                                <span class="text-[11px] font-medium text-center text-slate-400">
                                    Maksimal 10 MB
                                </span>

                                <input
                                    type="file"
                                    name="lampiran_file"
                                    id="lampiran_file"
                                    accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                    class="sr-only"
                                >

                            </label>

                            {{-- SELECTED FILE --}}
                            <div
                                id="selected-file"
                                class="hidden px-3 py-2.5 mt-2 bg-emerald-50 border-2 border-emerald-200 rounded-lg"
                            >

                                <div class="flex items-center gap-2">

                                    <div class="flex items-center justify-center w-7 h-7 bg-emerald-100 border border-emerald-200 rounded-md text-emerald-600 shrink-0">

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
                                            File siap diupload
                                        </p>

                                        <p
                                            id="selected-file-name"
                                            class="text-xs text-emerald-600 truncate"
                                        ></p>

                                        <p
                                            id="selected-file-size"
                                            class="mt-0.5 text-[10px] text-emerald-500"
                                        ></p>

                                    </div>

                                    <button
                                        type="button"
                                        id="clear-file-btn"
                                        class="inline-flex items-center justify-center w-7 h-7 bg-white border-2 border-emerald-200 rounded-md text-emerald-600 hover:bg-emerald-100 transition shrink-0"
                                        title="Hapus pilihan file"
                                        aria-label="Hapus pilihan file"
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

                        </div>

                        {{-- =================================================
                             CAMERA MODE
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
                                ></video>

                                <img
                                    id="image-preview"
                                    src=""
                                    alt="Preview hasil scan"
                                    hidden
                                >

                                <div
                                    id="camera-placeholder"
                                    class="camera-placeholder"
                                >

                                    <div class="px-4 text-center">

                                        <svg
                                            class="w-8 h-8 mx-auto mb-2 opacity-90"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"
                                            />
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="3.5"
                                            />
                                        </svg>

                                        <span id="camera-placeholder-text">
                                            Kamera belum aktif
                                        </span>

                                    </div>

                                </div>

                                <div
                                    id="camera-error"
                                    class="camera-error hidden"
                                    role="alert"
                                ></div>

                            </div>

                            <div class="flex flex-wrap justify-center gap-1.5 mt-2">

                                <button
                                    type="button"
                                    id="start-cam-btn"
                                    class="camera-button bg-blue-600 border-blue-600 hover:bg-blue-700"
                                >
                                    Nyalakan Kamera
                                </button>

                                <button
                                    type="button"
                                    id="capture-btn"
                                    class="hidden camera-button bg-emerald-600 border-emerald-600 hover:bg-emerald-700"
                                >
                                    Ambil Foto
                                </button>

                                <button
                                    type="button"
                                    id="retake-btn"
                                    class="hidden camera-button bg-amber-500 border-amber-500 hover:bg-amber-600"
                                >
                                    Foto Ulang
                                </button>

                                <button
                                    type="button"
                                    id="stop-cam-btn"
                                    class="hidden camera-button bg-rose-600 border-rose-600 hover:bg-rose-700"
                                >
                                    Tutup Kamera
                                </button>

                            </div>

                            {{-- Backend saat ini memang menggunakan field ini --}}
                            <input
                                type="hidden"
                                name="captured_image"
                                id="captured_image"
                                value="{{ old('captured_image') }}"
                            >

                            <div
                                id="snapshot-preview"
                                class="{{ old('captured_image') ? '' : 'hidden' }} mt-1.5 text-center text-xs font-semibold text-emerald-600"
                            >
                                ✓ Hasil scan berhasil diambil dan akan dikompres sebelum dikirim.
                            </div>

                        </div>

                        {{-- FILE ERRORS --}}
                        <div class="mt-2">

                            @if($formErrors->has('lampiran_file'))
                                <p class="form-error-text">
                                    {{ $formErrors->first('lampiran_file') }}
                                </p>
                            @endif

                            @if($formErrors->has('captured_image'))
                                <p class="form-error-text">
                                    {{ $formErrors->first('captured_image') }}
                                </p>
                            @endif

                        </div>

                    </div>

                    {{-- =================================================
                         ARSIP FISIK
                    ================================================== --}}
                    <div class="archive-card">

                        <div class="archive-card-header">

                            <div>

                                <h3 class="archive-card-title">
                                    Lokasi Arsip Fisik
                                </h3>

                                <p class="archive-card-description">
                                    Tentukan lokasi penyimpanan arsip fisik.
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

                            <p class="max-w-sm mt-0.5 text-xs leading-relaxed text-center text-slate-400">
                                Masukkan posisi rak, lemari, box, atau map tempat arsip disimpan.
                            </p>

                            <div class="w-full mt-3">

                                <label
                                    for="lokasi_arsip_fisik"
                                    class="form-label text-left"
                                >
                                    Detail Posisi Lemari / Box
                                </label>

                                <input
                                    type="text"
                                    id="lokasi_arsip_fisik"
                                    name="lokasi_arsip_fisik"
                                    value="{{ old('lokasi_arsip_fisik') }}"
                                    placeholder="Contoh: Rak A-3 Box 12"
                                    class="form-control-custom @if($formErrors->has('lokasi_arsip_fisik')) form-error @endif"
                                >

                                @if($formErrors->has('lokasi_arsip_fisik'))
                                    <p class="form-error-text text-left">
                                        {{ $formErrors->first('lokasi_arsip_fisik') }}
                                    </p>
                                @endif

                            </div>

                            <div class="w-full px-3 py-2 mt-2 bg-slate-50 border-2 border-slate-200 rounded-lg">

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

        {{-- ========================================================
             FOOTER
        ========================================================= --}}
        <div class="flex flex-col-reverse gap-2 px-4 py-2.5 bg-slate-50 border-t-2 border-slate-300 sm:flex-row sm:items-center sm:justify-end sm:px-5">

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
                    Simpan Surat Masuk
                </span>

            </button>

        </div>

    </div>

</form>

{{-- ================================================================
     JAVASCRIPT
================================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    'use strict';

    /*
    |--------------------------------------------------------------------------
    | DOM
    |--------------------------------------------------------------------------
    */

    const form = document.getElementById('form-surat');

    const uploadBtn = document.getElementById('btn-upload');
    const cameraBtn = document.getElementById('btn-camera');

    const uploadPanel = document.getElementById('upload-panel');
    const cameraPanel = document.getElementById('camera-panel');

    const fileInput = document.getElementById('lampiran_file');
    const uploadBox = document.getElementById('upload-box');

    const selectedFile = document.getElementById('selected-file');
    const selectedFileName = document.getElementById('selected-file-name');
    const selectedFileSize = document.getElementById('selected-file-size');
    const clearFileBtn = document.getElementById('clear-file-btn');

    const video = document.getElementById('video');
    const imagePreview = document.getElementById('image-preview');

    const cameraPlaceholder = document.getElementById('camera-placeholder');
    const cameraPlaceholderText = document.getElementById('camera-placeholder-text');
    const cameraError = document.getElementById('camera-error');

    const startCamBtn = document.getElementById('start-cam-btn');
    const captureBtn = document.getElementById('capture-btn');
    const retakeBtn = document.getElementById('retake-btn');
    const stopCamBtn = document.getElementById('stop-cam-btn');

    const capturedInput = document.getElementById('captured_image');
    const snapshotPreview = document.getElementById('snapshot-preview');

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');

    if (
        !form ||
        !fileInput ||
        !capturedInput
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    const CLIENT_MAX_FILE_SIZE =
        10 * 1024 * 1024;

    const CLIENT_TARGET_IMAGE_SIZE =
        2.5 * 1024 * 1024;

    const MAX_IMAGE_WIDTH = 2200;
    const MAX_IMAGE_HEIGHT = 2200;

    const JPEG_QUALITY = 0.78;

    let cameraStream = null;

    let previewObjectUrl = null;

    let isSubmitting = false;

    /*
    |--------------------------------------------------------------------------
    | Utility
    |--------------------------------------------------------------------------
    */

    function formatFileSize(bytes) {

        if (
            !Number.isFinite(bytes) ||
            bytes <= 0
        ) {
            return '0 KB';
        }

        if (
            bytes < 1024 * 1024
        ) {
            return `${(bytes / 1024).toFixed(1)} KB`;
        }

        return `${(
            bytes / (1024 * 1024)
        ).toFixed(2)} MB`;
    }

    function showCameraError(message) {

        if (!cameraError) {
            return;
        }

        cameraError.textContent = message || '';

        cameraError.classList.remove(
            'hidden'
        );
    }

    function clearCameraError() {

        if (!cameraError) {
            return;
        }

        cameraError.textContent = '';

        cameraError.classList.add(
            'hidden'
        );
    }

    function revokePreviewUrl() {

        if (!previewObjectUrl) {
            return;
        }

        URL.revokeObjectURL(
            previewObjectUrl
        );

        previewObjectUrl = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Upload / Camera Mode
    |--------------------------------------------------------------------------
    */

    function setModeButtonState(
        uploadActive
    ) {

        uploadBtn?.classList.toggle(
            'mode-button-active',
            uploadActive
        );

        cameraBtn?.classList.toggle(
            'mode-button-active',
            !uploadActive
        );

        uploadBtn?.setAttribute(
            'aria-selected',
            uploadActive ? 'true' : 'false'
        );

        cameraBtn?.setAttribute(
            'aria-selected',
            uploadActive ? 'false' : 'true'
        );
    }

    function activateUploadMode() {

        clearCameraError();

        uploadPanel?.classList.remove(
            'hidden'
        );

        cameraPanel?.classList.add(
            'hidden'
        );

        setModeButtonState(true);

        stopCamera();

    }

    function activateCameraMode() {

        clearCameraError();

        uploadPanel?.classList.add(
            'hidden'
        );

        cameraPanel?.classList.remove(
            'hidden'
        );

        setModeButtonState(false);

        clearFileSelection();

    }

    uploadBtn?.addEventListener(
        'click',
        activateUploadMode
    );

    cameraBtn?.addEventListener(
        'click',
        activateCameraMode
    );

    /*
    |--------------------------------------------------------------------------
    | File Upload
    |--------------------------------------------------------------------------
    */

    fileInput.addEventListener(
        'change',
        async () => {

            clearCameraError();

            const file =
                fileInput.files?.[0];

            if (!file) {
                return;
            }

            try {

                /*
                |--------------------------------------------------------------------------
                | PDF
                |--------------------------------------------------------------------------
                */

                if (
                    file.type ===
                    'application/pdf'
                ) {

                    if (
                        file.size >
                        CLIENT_MAX_FILE_SIZE
                    ) {

                        clearFileSelection();

                        alert(
                            'Ukuran PDF maksimal 10 MB.'
                        );

                        return;
                    }

                    /*
                    | Hapus hasil kamera.
                    */
                    clearCapturedImage();

                    showSelectedFile(
                        file,
                        'PDF disimpan tanpa kompresi'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | IMAGE
                |--------------------------------------------------------------------------
                */

                const allowedImageTypes = [
                    'image/jpeg',
                    'image/jpg',
                    'image/png'
                ];

                if (
                    !allowedImageTypes.includes(
                        file.type
                    )
                ) {

                    clearFileSelection();

                    alert(
                        'Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.'
                    );

                    return;
                }

                if (
                    file.size >
                    CLIENT_MAX_FILE_SIZE
                ) {

                    clearFileSelection();

                    alert(
                        'Ukuran file gambar maksimal 10 MB.'
                    );

                    return;
                }

                const originalSize =
                    file.size;

                /*
                |--------------------------------------------------------------------------
                | Kompres gambar
                |--------------------------------------------------------------------------
                */

                const compressedFile =
                    await compressImageFile(
                        file
                    );

                /*
                |--------------------------------------------------------------------------
                | Masukkan kembali file hasil
                | kompresi ke input file.
                |--------------------------------------------------------------------------
                */

                if (
                    typeof DataTransfer !==
                    'undefined'
                ) {

                    const dataTransfer =
                        new DataTransfer();

                    dataTransfer.items.add(
                        compressedFile
                    );

                    fileInput.files =
                        dataTransfer.files;

                }

                /*
                |--------------------------------------------------------------------------
                | Hapus hasil kamera.
                |--------------------------------------------------------------------------
                */

                clearCapturedImage();

                const reduction =
                    originalSize > 0
                        ? Math.max(
                            0,
                            Math.round(
                                (
                                    1 -
                                    (
                                        compressedFile.size /
                                        originalSize
                                    )
                                ) * 100
                            )
                        )
                        : 0;

                showSelectedFile(
                    compressedFile,
                    `Kompresi browser • ukuran turun ${reduction}%`
                );

            } catch (error) {

                console.error(
                    'Gagal melakukan kompresi gambar:',
                    error
                );

                clearFileSelection();

                alert(
                    error?.message ||
                    'Gagal memproses gambar. Silakan coba file lain.'
                );

            }

        }
    );

    function showSelectedFile(
        file,
        note = ''
    ) {

        if (!selectedFile) {
            return;
        }

        if (selectedFileName) {

            selectedFileName.textContent =
                file.name;

        }

        if (selectedFileSize) {

            selectedFileSize.textContent =
                `${formatFileSize(file.size)}${note ? ` • ${note}` : ''}`;

        }

        selectedFile.classList.remove(
            'hidden'
        );

        uploadBox?.classList.add(
            'border-emerald-300'
        );

    }

    function clearFileSelection() {

        if (fileInput) {
            fileInput.value = '';
        }

        selectedFile?.classList.add(
            'hidden'
        );

        if (selectedFileName) {
            selectedFileName.textContent = '';
        }

        if (selectedFileSize) {
            selectedFileSize.textContent = '';
        }

        uploadBox?.classList.remove(
            'border-emerald-300'
        );

    }

    clearFileBtn?.addEventListener(
        'click',
        clearFileSelection
    );

    /*
    |--------------------------------------------------------------------------
    | Captured Image
    |--------------------------------------------------------------------------
    */

    function clearCapturedImage() {

        if (capturedInput) {
            capturedInput.value = '';
        }

        snapshotPreview?.classList.add(
            'hidden'
        );

        revokePreviewUrl();

        if (imagePreview) {

            imagePreview.src = '';

            imagePreview.setAttribute(
                'hidden',
                ''
            );

        }

    }

    /*
    |--------------------------------------------------------------------------
    | Image Compression
    |--------------------------------------------------------------------------
    */

    function loadImageFromFile(file) {

        return new Promise(
            (resolve, reject) => {

                const objectUrl =
                    URL.createObjectURL(file);

                const image =
                    new Image();

                image.onload = () => {

                    URL.revokeObjectURL(
                        objectUrl
                    );

                    resolve(image);

                };

                image.onerror = () => {

                    URL.revokeObjectURL(
                        objectUrl
                    );

                    reject(
                        new Error(
                            'Gambar tidak dapat dibaca oleh browser.'
                        )
                    );

                };

                image.src =
                    objectUrl;

            }
        );
    }

    async function compressImageFile(file) {

        const image =
            await loadImageFromFile(file);

        return compressImageElement(
            image,
            file.name
        );
    }

    function compressImageElement(
        image,
        originalName = 'lampiran.jpg'
    ) {

        return new Promise(
            (resolve, reject) => {

                let width =
                    image.naturalWidth ||
                    image.width;

                let height =
                    image.naturalHeight ||
                    image.height;

                if (
                    width <= 0 ||
                    height <= 0
                ) {

                    reject(
                        new Error(
                            'Dimensi gambar tidak valid.'
                        )
                    );

                    return;
                }

                const scale =
                    Math.min(
                        MAX_IMAGE_WIDTH / width,
                        MAX_IMAGE_HEIGHT / height,
                        1
                    );

                width =
                    Math.max(
                        1,
                        Math.round(
                            width * scale
                        )
                    );

                height =
                    Math.max(
                        1,
                        Math.round(
                            height * scale
                        )
                    );

                const canvas =
                    document.createElement(
                        'canvas'
                    );

                canvas.width =
                    width;

                canvas.height =
                    height;

                const context =
                    canvas.getContext(
                        '2d',
                        {
                            alpha: false
                        }
                    );

                if (!context) {

                    reject(
                        new Error(
                            'Browser tidak mendukung pemrosesan gambar.'
                        )
                    );

                    return;
                }

                context.fillStyle =
                    '#ffffff';

                context.fillRect(
                    0,
                    0,
                    width,
                    height
                );

                context.imageSmoothingEnabled =
                    true;

                context.imageSmoothingQuality =
                    'high';

                context.drawImage(
                    image,
                    0,
                    0,
                    width,
                    height
                );

                const qualities = [
                    JPEG_QUALITY,
                    0.70,
                    0.62,
                    0.54
                ];

                const tryQuality =
                    index => {

                        if (
                            index >=
                            qualities.length
                        ) {

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
                                0.48
                            );

                            return;
                        }

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

                                if (
                                    blob.size <=
                                    CLIENT_TARGET_IMAGE_SIZE
                                ) {

                                    resolve(
                                        createCompressedFile(
                                            blob,
                                            originalName
                                        )
                                    );

                                    return;
                                }

                                tryQuality(
                                    index + 1
                                );

                            },
                            'image/jpeg',
                            qualities[index]
                        );

                    };

                tryQuality(0);

            }
        );
    }

    function createCompressedFile(
        blob,
        originalName
    ) {

        const baseName =
            originalName
                .replace(
                    /\.[^/.]+$/,
                    ''
                )
                .replace(
                    /[^a-zA-Z0-9_-]/g,
                    '_'
                );

        const filename =
            `${baseName || 'lampiran'}_compressed.jpg`;

        return new File(
            [blob],
            filename,
            {
                type: 'image/jpeg',
                lastModified: Date.now()
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Camera
    |--------------------------------------------------------------------------
    */

    startCamBtn?.addEventListener(
        'click',
        async () => {

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

                /*
                | Pastikan stream lama sudah berhenti.
                */
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

                video.srcObject =
                    cameraStream;

                await video.play();

                cameraPlaceholder?.classList.add(
                    'hidden'
                );

                imagePreview?.setAttribute(
                    'hidden',
                    ''
                );

                startCamBtn?.classList.add(
                    'hidden'
                );

                captureBtn?.classList.remove(
                    'hidden'
                );

                retakeBtn?.classList.add(
                    'hidden'
                );

                stopCamBtn?.classList.remove(
                    'hidden'
                );

            } catch (error) {

                console.error(
                    'Camera error:',
                    error
                );

                stopCameraTracksOnly();

                showCameraError(
                    'Kamera tidak dapat diakses. Pastikan izin kamera sudah diberikan pada browser.'
                );

            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Capture
    |--------------------------------------------------------------------------
    */

    captureBtn?.addEventListener(
        'click',
        async () => {

            clearCameraError();

            if (
                !video.videoWidth ||
                !video.videoHeight
            ) {

                showCameraError(
                    'Kamera belum siap. Silakan tunggu sebentar.'
                );

                return;
            }

            try {

                const canvas =
                    document.createElement(
                        'canvas'
                    );

                let width =
                    video.videoWidth;

                let height =
                    video.videoHeight;

                const scale =
                    Math.min(
                        MAX_IMAGE_WIDTH / width,
                        MAX_IMAGE_HEIGHT / height,
                        1
                    );

                width =
                    Math.max(
                        1,
                        Math.round(
                            width * scale
                        )
                    );

                height =
                    Math.max(
                        1,
                        Math.round(
                            height * scale
                        )
                    );

                canvas.width =
                    width;

                canvas.height =
                    height;

                const context =
                    canvas.getContext(
                        '2d',
                        {
                            alpha: false
                        }
                    );

                if (!context) {

                    throw new Error(
                        'Browser tidak mendukung canvas.'
                    );

                }

                context.fillStyle =
                    '#ffffff';

                context.fillRect(
                    0,
                    0,
                    width,
                    height
                );

                context.imageSmoothingEnabled =
                    true;

                context.imageSmoothingQuality =
                    'high';

                context.drawImage(
                    video,
                    0,
                    0,
                    width,
                    height
                );

                /*
                | Kompres hasil kamera.
                */
                const blob =
                    await canvasToCompressedBlob(
                        canvas
                    );

                if (
                    blob.size >
                    CLIENT_MAX_FILE_SIZE
                ) {

                    throw new Error(
                        'Hasil scan masih terlalu besar. Silakan ambil foto ulang.'
                    );

                }

                /*
                | Hentikan preview URL sebelumnya.
                */
                revokePreviewUrl();

                previewObjectUrl =
                    URL.createObjectURL(
                        blob
                    );

                imagePreview.src =
                    previewObjectUrl;

                imagePreview.removeAttribute(
                    'hidden'
                );

                video.classList.add(
                    'hidden'
                );

                /*
                |--------------------------------------------------------------------------
                | Backend saat ini membaca Base64
                | dari captured_image.
                |--------------------------------------------------------------------------
                */
                capturedInput.value =
                    await blobToDataUrl(
                        blob
                    );

                /*
                | Kamera dan upload saling eksklusif.
                */
                clearFileSelection();

                snapshotPreview?.classList.remove(
                    'hidden'
                );

                captureBtn?.classList.add(
                    'hidden'
                );

                retakeBtn?.classList.remove(
                    'hidden'
                );

                cameraPlaceholder?.classList.add(
                    'hidden'
                );

                /*
                | Kamera tidak perlu tetap menyala
                | setelah foto diambil.
                */
                stopCameraTracksOnly();

                stopCamBtn?.classList.add(
                    'hidden'
                );

            } catch (error) {

                console.error(
                    'Gagal mengambil foto:',
                    error
                );

                showCameraError(
                    error?.message ||
                    'Gagal memproses hasil scan kamera.'
                );

            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Camera Compression
    |--------------------------------------------------------------------------
    */

    function canvasToCompressedBlob(
        canvas
    ) {

        return new Promise(
            (resolve, reject) => {

                const qualities = [
                    0.78,
                    0.70,
                    0.62,
                    0.54
                ];

                const tryQuality =
                    index => {

                        if (
                            index >=
                            qualities.length
                        ) {

                            canvas.toBlob(
                                blob => {

                                    if (!blob) {

                                        reject(
                                            new Error(
                                                'Gagal membuat hasil scan.'
                                            )
                                        );

                                        return;
                                    }

                                    resolve(
                                        blob
                                    );

                                },
                                'image/jpeg',
                                0.48
                            );

                            return;
                        }

                        canvas.toBlob(
                            blob => {

                                if (!blob) {

                                    reject(
                                        new Error(
                                            'Gagal membuat hasil scan.'
                                        )
                                    );

                                    return;
                                }

                                if (
                                    blob.size <=
                                    CLIENT_TARGET_IMAGE_SIZE
                                ) {

                                    resolve(
                                        blob
                                    );

                                    return;
                                }

                                tryQuality(
                                    index + 1
                                );

                            },
                            'image/jpeg',
                            qualities[index]
                        );

                    };

                tryQuality(0);

            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Blob → Base64
    |--------------------------------------------------------------------------
    */

    function blobToDataUrl(blob) {

        return new Promise(
            (resolve, reject) => {

                const reader =
                    new FileReader();

                reader.onload = () => {

                    resolve(
                        reader.result
                    );

                };

                reader.onerror = () => {

                    reject(
                        new Error(
                            'Gagal menyiapkan file hasil scan.'
                        )
                    );

                };

                reader.readAsDataURL(
                    blob
                );

            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Retake
    |--------------------------------------------------------------------------
    */

    retakeBtn?.addEventListener(
        'click',
        async () => {

            clearCapturedImage();

            video.classList.remove(
                'hidden'
            );

            retakeBtn?.classList.add(
                'hidden'
            );

            clearCameraError();

            try {

                await startCameraAgain();

            } catch (error) {

                console.error(
                    error
                );

                showCameraError(
                    'Kamera gagal diaktifkan kembali.'
                );

            }

        }
    );

    async function startCameraAgain() {

        if (
            !navigator.mediaDevices?.getUserMedia
        ) {

            throw new Error(
                'Browser tidak mendukung kamera.'
            );

        }

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

        video.srcObject =
            cameraStream;

        await video.play();

        cameraPlaceholder?.classList.add(
            'hidden'
        );

        captureBtn?.classList.remove(
            'hidden'
        );

        stopCamBtn?.classList.remove(
            'hidden'
        );

        startCamBtn?.classList.add(
            'hidden'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Stop Camera
    |--------------------------------------------------------------------------
    */

    stopCamBtn?.addEventListener(
        'click',
        stopCamera
    );

    function stopCamera() {

        stopCameraTracksOnly();

        if (video) {

            video.srcObject = null;

            video.classList.remove(
                'hidden'
            );

        }

        cameraPlaceholder?.classList.remove(
            'hidden'
        );

        if (cameraPlaceholderText) {

            cameraPlaceholderText.textContent =
                'Kamera belum aktif';

        }

        startCamBtn?.classList.remove(
            'hidden'
        );

        captureBtn?.classList.add(
            'hidden'
        );

        retakeBtn?.classList.add(
            'hidden'
        );

        stopCamBtn?.classList.add(
            'hidden'
        );

    }

    function stopCameraTracksOnly() {

        if (!cameraStream) {
            return;
        }

        cameraStream
            .getTracks()
            .forEach(
                track => track.stop()
            );

        cameraStream = null;

    }

    /*
    |--------------------------------------------------------------------------
    | Form Submit
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        event => {

            if (isSubmitting) {

                event.preventDefault();

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Pastikan minimal salah satu lampiran
            | tersedia.
            |
            | Backend dapat menggunakan:
            | 1. captured_image
            | 2. lampiran_file
            |--------------------------------------------------------------------------
            */

            const hasCapturedImage =
                Boolean(
                    capturedInput.value
                );

            const hasUploadedFile =
                Boolean(
                    fileInput.files?.length
                );

            if (
                !hasCapturedImage &&
                !hasUploadedFile
            ) {

                event.preventDefault();

                alert(
                    'Silakan upload berkas digital atau ambil foto menggunakan kamera.'
                );

                return;
            }

            isSubmitting = true;

            if (submitBtn) {

                submitBtn.disabled =
                    true;

                submitBtn.classList.add(
                    'opacity-70',
                    'cursor-not-allowed'
                );

            }

            if (submitText) {

                submitText.textContent =
                    'Memproses & menyimpan...';

            }

            stopCameraTracksOnly();

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Page Exit
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'beforeunload',
        () => {

            stopCameraTracksOnly();

            revokePreviewUrl();

        }
    );

});
</script>

@endsection