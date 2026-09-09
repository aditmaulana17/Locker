@extends('layouts.app')

@section('title', 'Catat Surat Masuk')

@section('content')

<div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">

        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-800">
                Catat Surat Masuk
            </h1>

            <p class="mt-0.5 text-sm text-slate-500">
                Isi formulir di bawah ini untuk menambahkan data arsip surat masuk baru.
            </p>
        </div>

        <a
            href="{{ route('surat-masuk.index') }}"
            class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 text-sm font-semibold text-slate-700 bg-white border-2 border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 transition"
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
         ERROR
    ========================================================== --}}
    @if ($errors->any())

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

                <div>

                    <p class="text-sm font-bold text-rose-700">
                        Data belum dapat disimpan.
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
        action="{{ route('surat-masuk.store') }}"
        enctype="multipart/form-data"
        id="form-surat"
    >

        @csrf

        <input
            type="hidden"
            name="nomor_agenda"
            value="{{ $nomorAgenda }}"
        >


        <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-sm overflow-hidden">

            {{-- =================================================
                 NOMOR AGENDA
            ================================================== --}}
            <div class="px-4 sm:px-5 py-2.5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-200">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">

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
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 013-3h0a3 3 0 013 3m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                />
                            </svg>

                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 min-w-0">

                            <span class="text-sm font-medium text-blue-900">
                                Nomor Agenda Sistem:
                            </span>

                            <strong class="inline-flex items-center w-fit px-2.5 py-1 bg-white border-2 border-blue-200 rounded-lg text-xs font-bold font-mono text-blue-700">
                                {{ $nomorAgenda }}
                            </strong>

                        </div>

                    </div>

                    <span class="text-xs font-medium text-blue-600">
                        Nomor agenda dibuat otomatis oleh sistem
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
                                Lengkapi identitas dan informasi utama surat masuk.
                            </p>
                        </div>

                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3">

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
                                value="{{ old('pengirim') }}"
                                required
                                autocomplete="organization"
                                placeholder="Masukkan nama pengirim surat..."
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
                                value="{{ old('tanggal_surat') }}"
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
                                value="{{ old('tanggal_terima', now()->format('Y-m-d')) }}"
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
                                        {{ old('kategori_surat_id') ? '' : 'selected' }}
                                    >
                                        Pilih kategori surat
                                    </option>

                                    @foreach ($kategoris as $kategori)

                                        <option
                                            value="{{ $kategori->id }}"
                                            {{ (string) old('kategori_surat_id') === (string) $kategori->id ? 'selected' : '' }}
                                        >
                                            {{ $kategori->nama_kategori }}

                                            @if (!empty($kategori->sifat))
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
                                        {{ old('status', 'baru') === 'baru' ? 'selected' : '' }}
                                    >
                                        Baru
                                    </option>

                                    <option
                                        value="diproses"
                                        {{ old('status') === 'diproses' ? 'selected' : '' }}
                                    >
                                        Diproses
                                    </option>

                                    <option
                                        value="didisposisikan"
                                        {{ old('status') === 'didisposisikan' ? 'selected' : '' }}
                                    >
                                        Didisposisikan
                                    </option>

                                    <option
                                        value="selesai"
                                        {{ old('status') === 'selesai' ? 'selected' : '' }}
                                    >
                                        Selesai
                                    </option>

                                    <option
                                        value="diarsipkan"
                                        {{ old('status') === 'diarsipkan' ? 'selected' : '' }}
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
                                rows="2"
                                required
                                placeholder="Tuliskan perihal atau isi ringkas surat secara jelas..."
                                class="form-control-custom form-textarea @error('perihal') form-error @enderror"
                            >{{ old('perihal') }}</textarea>

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


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-stretch">

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
                                        Upload PDF, JPG, JPEG, atau PNG.
                                        Anda juga dapat menggunakan kamera.
                                    </p>

                                </div>

                                <span class="archive-card-badge required">
                                    Wajib
                                </span>

                            </div>


                            {{-- Mode Selector --}}
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
                                 UPLOAD MODE
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
                                        Klik untuk memilih file
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


                                {{-- Selected file --}}
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
                                                File siap diupload
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
                                            title="Hapus pilihan file"
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
                                        <div class="text-center px-4">

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


                                {{-- Camera controls --}}
                                <div class="flex flex-wrap justify-center gap-1.5 mt-2">

                                    <button
                                        type="button"
                                        id="start-cam-btn"
                                        class="camera-button bg-blue-600 hover:bg-blue-700 border-blue-600"
                                    >
                                        Nyalakan Kamera
                                    </button>


                                    <button
                                        type="button"
                                        id="capture-btn"
                                        class="hidden camera-button bg-emerald-600 hover:bg-emerald-700 border-emerald-600"
                                    >
                                        Ambil Foto
                                    </button>


                                    <button
                                        type="button"
                                        id="retake-btn"
                                        class="hidden camera-button bg-amber-500 hover:bg-amber-600 border-amber-500"
                                    >
                                        Foto Ulang
                                    </button>


                                    <button
                                        type="button"
                                        id="stop-cam-btn"
                                        class="hidden camera-button bg-rose-600 hover:bg-rose-700 border-rose-600"
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
                                    class="{{ old('captured_image') ? '' : 'hidden' }} mt-1.5 text-center text-xs font-semibold text-emerald-600"
                                >
                                    ✓ Hasil scan berhasil diambil.
                                </div>

                            </div>


                            {{-- Errors --}}
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


                                <p class="mt-0.5 max-w-sm text-xs leading-relaxed text-slate-400">
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
                                        class="form-control-custom @error('lokasi_arsip_fisik') form-error @enderror"
                                    >


                                    @error('lokasi_arsip_fisik')
                                        <p class="form-error-text text-left">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>


                                <div class="w-full mt-2 px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg">

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
            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2 px-4 sm:px-5 py-2.5 bg-slate-50 border-t-2 border-slate-300">

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

</div>


<style>
    .section-heading {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding-bottom: .45rem;
        margin-bottom: .75rem;
        border-bottom: 2px solid #cbd5e1;
    }

    .section-marker {
        width: .35rem;
        height: 1.2rem;
        border-radius: 9999px;
        flex-shrink: 0;
    }

    .section-title {
        color: #334155;
        font-size: .82rem;
        line-height: 1rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .section-description {
        margin-top: .05rem;
        color: #94a3b8;
        font-size: .68rem;
        line-height: .9rem;
    }

    .form-group {
        width: 100%;
    }

    .form-label {
        display: block;
        margin-bottom: .3rem;
        color: #334155;
        font-size: .68rem;
        line-height: .9rem;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .form-control-custom {
        width: 100%;
        min-height: 40px;
        padding: .48rem .7rem;
        background: #fff;
        color: #334155;
        border: 2px solid #94a3b8;
        border-radius: .6rem;
        outline: none;
        font-size: .8rem;
        line-height: 1.25;
        transition:
            border-color .15s ease,
            background-color .15s ease,
            box-shadow .15s ease;
    }

    .form-control-custom:hover {
        border-color: #64748b;
    }

    .form-control-custom:focus {
        border-color: #2563eb;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }

    .form-control-custom::placeholder {
        color: #94a3b8;
    }

    select.form-control-custom {
        cursor: pointer;
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
        margin-top: .2rem;
        color: #e11d48;
        font-size: .68rem;
        line-height: .9rem;
        font-weight: 500;
    }

    .archive-card {
        display: flex;
        flex-direction: column;
        min-height: 275px;
        padding: .8rem;
        background: #f8fafc;
        border: 2px solid #94a3b8;
        border-radius: .8rem;
        transition: border-color .15s ease;
    }

    .archive-card:hover {
        border-color: #64748b;
    }

    .archive-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .6rem;
        margin-bottom: .55rem;
    }

    .archive-card-title {
        color: #334155;
        font-size: .8rem;
        line-height: 1rem;
        font-weight: 700;
    }

    .archive-card-description {
        margin-top: .05rem;
        color: #94a3b8;
        font-size: .66rem;
        line-height: .9rem;
    }

    .archive-card-badge {
        flex-shrink: 0;
        padding: .22rem .45rem;
        background: #fff;
        border: 2px solid #cbd5e1;
        border-radius: .4rem;
        color: #475569;
        font-size: .6rem;
        line-height: .8rem;
        font-weight: 700;
    }

    .archive-card-badge.required {
        color: #b91c1c;
        border-color: #fecaca;
        background: #fff1f2;
    }

    .mode-selector {
        display: inline-flex;
        width: fit-content;
        padding: .12rem;
        margin-bottom: .5rem;
        background: #e2e8f0;
        border: 2px solid #94a3b8;
        border-radius: .6rem;
    }

    .mode-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .3rem;
        padding: .34rem .65rem;
        border: 1px solid transparent;
        border-radius: .38rem;
        color: #475569;
        background: transparent;
        font-size: .66rem;
        line-height: .9rem;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s ease;
    }

    .mode-button:hover {
        color: #2563eb;
        background: #f8fafc;
    }

    .mode-button-active {
        background: #fff;
        color: #2563eb;
        border-color: #cbd5e1;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .07);
    }

    .upload-box {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 175px;
        padding: .9rem;
        text-align: center;
        background: #fff;
        border: 2px dashed #64748b;
        border-radius: .6rem;
        cursor: pointer;
        transition:
            background-color .15s ease,
            border-color .15s ease;
    }

    .upload-box:hover {
        background: #f8fafc;
        border-color: #2563eb;
    }

    .upload-box-error {
        border-color: #f43f5e !important;
        background: #fff1f2 !important;
    }

    .upload-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        margin-bottom: .45rem;
        border-radius: 9999px;
        border: 2px solid #dbeafe;
        background: #eff6ff;
        color: #2563eb;
    }

    .camera-container {
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 3;
        overflow: hidden;
        background: #020617;
        border: 2px solid #64748b;
        border-radius: .6rem;
    }

    .camera-container video {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        background: #020617;
    }

    .camera-container video.is-hidden {
        display: none !important;
    }

    .camera-container img {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #020617;
    }

    .camera-container img[hidden] {
        display: none !important;
    }

    .camera-placeholder {
        position: absolute;
        inset: 0;
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: .68rem;
        font-weight: 500;
        text-align: center;
        background: rgba(2, 6, 23, .42);
        pointer-events: none;
    }

    .camera-placeholder.is-hidden {
        display: none !important;
    }

    .camera-error {
        position: absolute;
        left: .75rem;
        right: .75rem;
        bottom: .75rem;
        z-index: 10;
        padding: .65rem .75rem;
        color: #ffe4e6;
        background: rgba(127, 29, 29, .88);
        border: 1px solid rgba(254, 202, 202, .5);
        border-radius: .5rem;
        font-size: .68rem;
        line-height: 1rem;
        font-weight: 600;
        text-align: center;
    }

    .camera-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .42rem .7rem;
        color: #fff;
        border-width: 2px;
        border-radius: .55rem;
        font-size: .66rem;
        line-height: .9rem;
        font-weight: 700;
        cursor: pointer;
        transition: background-color .15s ease;
    }

    .location-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        margin-bottom: .45rem;
        border-radius: 9999px;
        border: 2px solid #e0e7ff;
        background: #eef2ff;
        color: #4f46e5;
    }

    .location-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 175px;
        padding: .9rem;
        text-align: center;
        background: #fff;
        border: 2px dashed #64748b;
        border-radius: .6rem;
        transition:
            background-color .15s ease,
            border-color .15s ease;
    }

    .location-box:hover {
        background: #f8fafc;
        border-color: #475569;
    }

    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 40px;
        padding: .5rem .9rem;
        border-width: 2px;
        border-radius: .6rem;
        font-size: .76rem;
        font-weight: 700;
        transition: all .15s ease;
    }

    .action-button-secondary {
        background: #fff;
        color: #334155;
        border-color: #cbd5e1;
    }

    .action-button-secondary:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    .action-button-primary {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
        box-shadow: 0 4px 10px rgba(37, 99, 235, .15);
    }

    .action-button-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    .action-button-primary:disabled {
        opacity: .65;
        cursor: not-allowed;
    }

    @media (min-width: 640px) {
        .action-button {
            width: auto;
        }
    }

    @media (max-width: 640px) {
        .archive-card {
            min-height: auto;
        }

        .upload-box,
        .location-box {
            min-height: 165px;
        }

        .form-control-custom {
            min-height: 40px;
        }
    }
</style>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ELEMENT
    |--------------------------------------------------------------------------
    */

    const form = document.getElementById('form-surat');

    const fileInput = document.getElementById('lampiran_file');
    const capturedInput = document.getElementById('captured_image');

    const btnUpload = document.getElementById('btn-upload');
    const btnCamera = document.getElementById('btn-camera');

    const uploadPanel = document.getElementById('upload-panel');
    const cameraPanel = document.getElementById('camera-panel');

    const uploadBox = document.getElementById('upload-box');
    const fileLabelText = document.getElementById('file-label-text');

    const selectedFile = document.getElementById('selected-file');
    const selectedFileName = document.getElementById('selected-file-name');
    const clearFileButton = document.getElementById('clear-file-btn');

    const video = document.getElementById('video');
    const imagePreview = document.getElementById('image-preview');

    const cameraPlaceholder =
        document.getElementById('camera-placeholder');

    const cameraError =
        document.getElementById('camera-error');

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


    /*
    |--------------------------------------------------------------------------
    | KONSTANTA
    |--------------------------------------------------------------------------
    */

    const MAX_FILE_SIZE = 15 * 1024 * 1024;


    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */

    let videoStream = null;
    let isSubmitting = false;


    /*
    |--------------------------------------------------------------------------
    | UTILITY
    |--------------------------------------------------------------------------
    */

    function setHidden(element, hidden) {

        if (!element) {
            return;
        }

        if (hidden) {
            element.classList.add('hidden');
        } else {
            element.classList.remove('hidden');
        }
    }


    function showCameraPlaceholder(show, text = 'Kamera belum aktif') {

        if (!cameraPlaceholder) {
            return;
        }

        const textElement =
            document.getElementById('camera-placeholder-text');

        if (textElement) {
            textElement.textContent = text;
        }

        if (show) {
            cameraPlaceholder.classList.remove('is-hidden');
        } else {
            cameraPlaceholder.classList.add('is-hidden');
        }
    }


    function showCameraError(message = '') {

        if (!cameraError) {
            return;
        }

        cameraError.textContent = message;

        if (message) {
            cameraError.classList.remove('hidden');
        } else {
            cameraError.classList.add('hidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FILE RESET
    |--------------------------------------------------------------------------
    */

    function resetFileDisplay() {

        if (fileInput) {
            fileInput.value = '';
        }

        if (fileLabelText) {

            fileLabelText.textContent =
                'Klik untuk memilih file';

            fileLabelText.classList.remove(
                'text-blue-600',
                'text-rose-600'
            );

            fileLabelText.classList.add(
                'text-slate-700'
            );
        }

        if (selectedFile) {
            selectedFile.classList.add('hidden');
        }

        if (selectedFileName) {
            selectedFileName.textContent = '';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR CAPTURED IMAGE
    |--------------------------------------------------------------------------
    */

    function clearCapturedImage() {

        if (capturedInput) {
            capturedInput.value = '';
        }

        if (imagePreview) {
            imagePreview.removeAttribute('src');
            imagePreview.hidden = true;
        }

        if (snapshotPreview) {
            snapshotPreview.classList.add('hidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RESET CAMERA VIEW
    |--------------------------------------------------------------------------
    */

    function resetCameraView() {

        if (video) {
            video.classList.remove('is-hidden');
            video.srcObject = null;
        }

        if (imagePreview) {
            imagePreview.hidden = true;
            imagePreview.removeAttribute('src');
        }

        showCameraPlaceholder(true);

        showCameraError('');

        if (snapshotPreview) {
            snapshotPreview.classList.add('hidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | STOP STREAM SAJA
    |--------------------------------------------------------------------------
    |
    | Penting:
    | fungsi ini hanya menghentikan stream.
    | Tidak mengubah UI kamera.
    |
    */

    function stopCameraStream() {

        if (!videoStream) {
            return;
        }

        videoStream
            .getTracks()
            .forEach(function (track) {

                try {
                    track.stop();
                } catch (error) {
                    console.warn(
                        'Gagal menghentikan track kamera:',
                        error
                    );
                }
            });

        videoStream = null;
    }


    /*
    |--------------------------------------------------------------------------
    | STOP CAMERA
    |--------------------------------------------------------------------------
    */

    function stopCamera() {

        stopCameraStream();

        if (video) {
            video.pause();
            video.srcObject = null;
            video.classList.remove('is-hidden');
        }

        showCameraPlaceholder(true);
        showCameraError('');

        if (startCamButton) {
            startCamButton.classList.remove('hidden');
        }

        if (captureButton) {
            captureButton.classList.add('hidden');
        }

        if (stopCamButton) {
            stopCamButton.classList.add('hidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CAMERA READY UI
    |--------------------------------------------------------------------------
    */

    function setCameraActiveUI() {

        showCameraPlaceholder(false);
        showCameraError('');

        if (startCamButton) {
            startCamButton.classList.add('hidden');
        }

        if (captureButton) {
            captureButton.classList.remove('hidden');
        }

        if (retakeButton) {
            retakeButton.classList.add('hidden');
        }

        if (stopCamButton) {
            stopCamButton.classList.remove('hidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CAMERA CAPTURED UI
    |--------------------------------------------------------------------------
    */

    function setCameraCapturedUI() {

        showCameraPlaceholder(false);

        if (startCamButton) {
            startCamButton.classList.add('hidden');
        }

        if (captureButton) {
            captureButton.classList.add('hidden');
        }

        if (retakeButton) {
            retakeButton.classList.remove('hidden');
        }

        if (stopCamButton) {
            stopCamButton.classList.remove('hidden');
        }

        if (snapshotPreview) {
            snapshotPreview.classList.remove('hidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SWITCH MODE
    |--------------------------------------------------------------------------
    */

    function switchMode(mode) {

        stopCameraStream();

        showCameraError('');

        if (mode === 'upload') {

            btnUpload.classList.add(
                'mode-button-active'
            );

            btnCamera.classList.remove(
                'mode-button-active'
            );

            uploadPanel.classList.remove(
                'hidden'
            );

            cameraPanel.classList.add(
                'hidden'
            );

            clearCapturedImage();

            resetCameraView();

            stopCamera();

            return;
        }


        /*
        |----------------------------------------------------------------------
        | CAMERA MODE
        |----------------------------------------------------------------------
        */

        btnCamera.classList.add(
            'mode-button-active'
        );

        btnUpload.classList.remove(
            'mode-button-active'
        );

        cameraPanel.classList.remove(
            'hidden'
        );

        uploadPanel.classList.add(
            'hidden'
        );


        /*
        | Hanya satu sumber file.
        */

        resetFileDisplay();

        resetCameraView();

        stopCamera();

        /*
        | Pastikan hasil kamera lama tidak ikut terkirim
        | ketika membuka kamera baru.
        */

        clearCapturedImage();
    }


    /*
    |--------------------------------------------------------------------------
    | MODE BUTTON
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


    /*
    |--------------------------------------------------------------------------
    | FILE INPUT
    |--------------------------------------------------------------------------
    */

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
            |----------------------------------------------------------------------
            | UKURAN
            |----------------------------------------------------------------------
            */

            if (file.size > MAX_FILE_SIZE) {

                this.value = '';

                resetFileDisplay();

                fileLabelText.textContent =
                    'Ukuran file melebihi 15 MB';

                fileLabelText.classList.remove(
                    'text-slate-700',
                    'text-blue-600'
                );

                fileLabelText.classList.add(
                    'text-rose-600'
                );

                return;
            }


            /*
            |----------------------------------------------------------------------
            | FILE DIPILIH
            |----------------------------------------------------------------------
            */

            capturedInput.value = '';

            clearCapturedImage();

            fileLabelText.textContent =
                'Terpilih: ' + file.name;

            fileLabelText.classList.remove(
                'text-slate-700',
                'text-rose-600'
            );

            fileLabelText.classList.add(
                'text-blue-600'
            );


            selectedFileName.textContent =
                file.name;

            selectedFile.classList.remove(
                'hidden'
            );


            uploadBox.classList.remove(
                'upload-box-error'
            );


            stopCamera();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLEAR FILE
    |--------------------------------------------------------------------------
    */

    clearFileButton.addEventListener(
        'click',
        function (event) {

            event.preventDefault();
            event.stopPropagation();

            resetFileDisplay();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | START CAMERA
    |--------------------------------------------------------------------------
    */

    async function startCamera() {

        showCameraError('');

        if (
            !navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia
        ) {

            showCameraError(
                'Browser tidak mendukung akses kamera.'
            );

            return;
        }


        try {

            /*
            | Bersihkan stream sebelumnya.
            */

            stopCameraStream();


            /*
            | Bersihkan hasil sebelumnya.
            */

            clearCapturedImage();


            if (video) {
                video.classList.remove('is-hidden');
                video.srcObject = null;
            }


            /*
            | Request camera.
            */

            videoStream =
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


            /*
            | Hubungkan stream ke video.
            */

            video.srcObject =
                videoStream;


            /*
            | Tunggu metadata video.
            */

            await new Promise(function (resolve) {

                if (
                    video.readyState >= 2
                ) {
                    resolve();
                    return;
                }

                video.onloadedmetadata =
                    function () {
                        resolve();
                    };
            });


            await video.play();


            /*
            | Kamera benar-benar aktif.
            */

            setCameraActiveUI();

        } catch (error) {

            console.error(
                'Camera error:',
                error
            );

            stopCameraStream();

            if (video) {
                video.srcObject = null;
            }

            showCameraPlaceholder(
                true,
                'Kamera belum aktif'
            );


            let message =
                'Gagal mengakses kamera.';

            if (
                error &&
                error.name === 'NotAllowedError'
            ) {
                message =
                    'Izin kamera ditolak. Izinkan akses kamera pada browser lalu coba lagi.';
            } else if (
                error &&
                error.name === 'NotFoundError'
            ) {
                message =
                    'Kamera tidak ditemukan pada perangkat.';
            } else if (
                error &&
                error.name === 'NotReadableError'
            ) {
                message =
                    'Kamera sedang digunakan aplikasi lain.';
            } else if (
                error &&
                error.name === 'OverconstrainedError'
            ) {
                message =
                    'Kamera tidak mendukung konfigurasi yang diminta.';
            } else if (
                error &&
                error.message
            ) {
                message =
                    error.message;
            }

            showCameraError(message);


            if (startCamButton) {
                startCamButton.classList.remove('hidden');
            }

            if (captureButton) {
                captureButton.classList.add('hidden');
            }

            if (stopCamButton) {
                stopCamButton.classList.add('hidden');
            }
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


        if (blob.size > MAX_FILE_SIZE) {
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

                    reader.readAsDataURL(blob);
                }
            );


        if (
            typeof dataUrl !== 'string' ||
            !dataUrl.startsWith('data:image/')
        ) {
            throw new Error(
                'Data hasil kamera tidak valid.'
            );
        }


        /*
        |----------------------------------------------------------------------
        | SIMPAN DATA KAMERA
        |----------------------------------------------------------------------
        */

        capturedInput.value =
            dataUrl;


        /*
        | File upload harus kosong.
        */

        if (fileInput) {
            fileInput.value = '';
        }

        resetFileDisplay();


        /*
        | Tampilkan hasil foto.
        */

        if (imagePreview) {

            imagePreview.src =
                dataUrl;

            imagePreview.hidden =
                false;
        }


        /*
        | Sembunyikan video.
        */

        if (video) {
            video.pause();
            video.classList.add('is-hidden');
        }


        /*
        | Stop hardware camera tanpa mereset preview.
        */

        stopCameraStream();


        /*
        | Tampilkan UI hasil.
        */

        setCameraCapturedUI();
    }


    /*
    |--------------------------------------------------------------------------
    | TAKE SNAPSHOT
    |--------------------------------------------------------------------------
    */

    async function takeSnapshot() {

        if (!videoStream) {

            showCameraError(
                'Kamera belum aktif. Klik "Nyalakan Kamera" terlebih dahulu.'
            );

            return;
        }


        try {

            const track =
                videoStream.getVideoTracks()[0];


            if (!track) {

                throw new Error(
                    'Track kamera tidak tersedia.'
                );
            }


            /*
            |----------------------------------------------------------------------
            | IMAGE CAPTURE
            |----------------------------------------------------------------------
            */

            if (
                typeof ImageCapture !== 'undefined'
            ) {

                try {

                    const imageCapture =
                        new ImageCapture(track);

                    const blob =
                        await imageCapture.takePhoto();

                    await processCameraBlob(
                        blob
                    );

                    return;

                } catch (imageCaptureError) {

                    console.warn(
                        'ImageCapture gagal, menggunakan canvas:',
                        imageCaptureError
                    );
                }
            }


            /*
            |----------------------------------------------------------------------
            | FALLBACK CANVAS
            |----------------------------------------------------------------------
            */

            if (
                !video.videoWidth ||
                !video.videoHeight
            ) {
                throw new Error(
                    'Video kamera belum siap. Silakan tunggu sebentar lalu coba lagi.'
                );
            }


            const canvas =
                document.createElement('canvas');


            canvas.width =
                video.videoWidth;

            canvas.height =
                video.videoHeight;


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


            const blob =
                await new Promise(
                    function (resolve, reject) {

                        canvas.toBlob(
                            function (result) {

                                if (!result) {
                                    reject(
                                        new Error(
                                            'Gagal membuat gambar dari kamera.'
                                        )
                                    );

                                    return;
                                }

                                resolve(result);
                            },
                            'image/jpeg',
                            0.90
                        );
                    }
                );


            await processCameraBlob(
                blob
            );

        } catch (error) {

            console.error(
                'Capture error:',
                error
            );

            showCameraError(
                error.message ||
                'Gagal mengambil foto dari kamera.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RETAKE PHOTO
    |--------------------------------------------------------------------------
    */

    async function retakePhoto() {

        clearCapturedImage();

        if (video) {
            video.classList.remove('is-hidden');
        }

        showCameraPlaceholder(
            true,
            'Menyiapkan kamera...'
        );

        if (retakeButton) {
            retakeButton.classList.add('hidden');
        }

        showCameraError('');

        await startCamera();
    }


    /*
    |--------------------------------------------------------------------------
    | TOMBOL KAMERA
    |--------------------------------------------------------------------------
    */

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
        retakePhoto
    );


    stopCamButton.addEventListener(
        'click',
        function () {

            clearCapturedImage();

            stopCamera();

        }
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
            |----------------------------------------------------------------------
            | WAJIB FILE ATAU KAMERA
            |----------------------------------------------------------------------
            */

            if (
                !hasFile &&
                !hasCamera
            ) {

                event.preventDefault();

                uploadBox.classList.add(
                    'upload-box-error'
                );

                alert(
                    'Berkas digital wajib diupload atau discan menggunakan kamera.'
                );

                switchMode(
                    'upload'
                );

                return;
            }


            /*
            |----------------------------------------------------------------------
            | TIDAK BOLEH KEDUANYA
            |----------------------------------------------------------------------
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
            |----------------------------------------------------------------------
            | SUBMIT
            |----------------------------------------------------------------------
            */

            isSubmitting =
                true;


            submitButton.disabled =
                true;


            submitText.textContent =
                'Menyimpan...';


            /*
            | Stop hardware camera.
            | Tidak menghapus captured_image.
            */

            stopCameraStream();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | STATE AWAL
    |--------------------------------------------------------------------------
    */

    switchMode('upload');


    /*
    |--------------------------------------------------------------------------
    | CLEANUP
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'beforeunload',
        function () {
            stopCameraStream();
        }
    );

});
</script>
@endpush

@endsection