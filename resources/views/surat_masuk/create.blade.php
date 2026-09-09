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

        <a href="{{ route('surat-masuk.index') }}"
           class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 text-sm font-semibold text-slate-700 bg-white border-2 border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 transition">

            <svg class="w-4 h-4 mr-2"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24"
                 aria-hidden="true">

                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
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

                <svg class="w-5 h-5 mt-0.5 text-rose-600 shrink-0"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24"
                     aria-hidden="true">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z"/>

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
    <form method="POST"
          action="{{ route('surat-masuk.store') }}"
          enctype="multipart/form-data"
          id="form-surat">

        @csrf

        <input type="hidden"
               name="nomor_agenda"
               value="{{ old('nomor_agenda', $nomorAgenda) }}">


        <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-sm overflow-hidden">


            {{-- =================================================
                 NOMOR AGENDA
            ================================================== --}}
            <div class="px-4 sm:px-5 py-2.5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-200">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">

                    <div class="flex items-center min-w-0">

                        <div class="flex items-center justify-center w-8 h-8 mr-2.5 bg-blue-100 border-2 border-blue-200 rounded-lg text-blue-600 shrink-0">

                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 viewBox="0 0 24 24"
                                 aria-hidden="true">

                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 013-3h0a3 3 0 013 3m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
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

                            <label for="nomor_surat"
                                   class="form-label">

                                Nomor Surat
                                <span class="text-rose-500">*</span>

                            </label>

                            <input type="text"
                                   id="nomor_surat"
                                   name="nomor_surat"
                                   value="{{ old('nomor_surat') }}"
                                   required
                                   autocomplete="off"
                                   placeholder="Contoh: 005/B/I/2026"
                                   class="form-control-custom @error('nomor_surat') form-error @enderror">

                            @error('nomor_surat')

                                <p class="form-error-text">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Pengirim --}}
                        <div class="form-group">

                            <label for="pengirim"
                                   class="form-label">

                                Instansi Pengirim
                                <span class="text-rose-500">*</span>

                            </label>

                            <input type="text"
                                   id="pengirim"
                                   name="pengirim"
                                   value="{{ old('pengirim') }}"
                                   required
                                   autocomplete="organization"
                                   placeholder="Masukkan nama pengirim surat..."
                                   class="form-control-custom @error('pengirim') form-error @enderror">

                            @error('pengirim')

                                <p class="form-error-text">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Tanggal Surat --}}
                        <div class="form-group">

                            <label for="tanggal_surat"
                                   class="form-label">

                                Tanggal Surat
                                <span class="text-rose-500">*</span>

                            </label>

                            <input type="date"
                                   id="tanggal_surat"
                                   name="tanggal_surat"
                                   value="{{ old('tanggal_surat') }}"
                                   required
                                   class="form-control-custom @error('tanggal_surat') form-error @enderror">

                            @error('tanggal_surat')

                                <p class="form-error-text">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Tanggal Diterima --}}
                        <div class="form-group">

                            <label for="tanggal_terima"
                                   class="form-label">

                                Tanggal Diterima
                                <span class="text-rose-500">*</span>

                            </label>

                            <input type="date"
                                   id="tanggal_terima"
                                   name="tanggal_terima"
                                   value="{{ old('tanggal_terima', now()->format('Y-m-d')) }}"
                                   required
                                   class="form-control-custom @error('tanggal_terima') form-error @enderror">

                            @error('tanggal_terima')

                                <p class="form-error-text">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Kategori --}}
                        <div class="form-group">

                            <label for="kategori_surat_id"
                                   class="form-label">

                                Kategori Surat
                                <span class="text-rose-500">*</span>

                            </label>

                            <div class="relative">

                                <select id="kategori_surat_id"
                                        name="kategori_surat_id"
                                        required
                                        class="form-control-custom appearance-none pr-10 @error('kategori_surat_id') form-error @enderror">

                                    <option value=""
                                            disabled
                                            {{ old('kategori_surat_id') ? '' : 'selected' }}>

                                        Pilih kategori surat

                                    </option>

                                    @foreach($kategoris as $kategori)

                                        <option value="{{ $kategori->id }}"
                                            {{ (string) old('kategori_surat_id') === (string) $kategori->id ? 'selected' : '' }}>

                                            {{ $kategori->nama_kategori }}

                                            @if(!empty($kategori->sifat))
                                                ({{ ucfirst($kategori->sifat) }})
                                            @endif

                                        </option>

                                    @endforeach

                                </select>


                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24"
                                     aria-hidden="true">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>

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

                            <label for="status"
                                   class="form-label">

                                Status Surat
                                <span class="text-rose-500">*</span>

                            </label>

                            <div class="relative">

                                <select id="status"
                                        name="status"
                                        required
                                        class="form-control-custom appearance-none pr-10 @error('status') form-error @enderror">

                                    <option value="baru"
                                        {{ old('status', 'baru') === 'baru' ? 'selected' : '' }}>
                                        Baru
                                    </option>

                                    <option value="diproses"
                                        {{ old('status') === 'diproses' ? 'selected' : '' }}>
                                        Diproses
                                    </option>

                                    <option value="didisposisikan"
                                        {{ old('status') === 'didisposisikan' ? 'selected' : '' }}>
                                        Didisposisikan
                                    </option>

                                    <option value="selesai"
                                        {{ old('status') === 'selesai' ? 'selected' : '' }}>
                                        Selesai
                                    </option>

                                    <option value="diarsipkan"
                                        {{ old('status') === 'diarsipkan' ? 'selected' : '' }}>
                                        Diarsipkan
                                    </option>

                                </select>


                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24"
                                     aria-hidden="true">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M19 9l-7 7-7-7"/>

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

                            <label for="perihal"
                                   class="form-label">

                                Perihal / Isi Ringkas
                                <span class="text-rose-500">*</span>

                            </label>

                            <textarea id="perihal"
                                      name="perihal"
                                      rows="2"
                                      required
                                      placeholder="Tuliskan perihal atau isi ringkas surat secara jelas..."
                                      class="form-control-custom form-textarea @error('perihal') form-error @enderror">{{ old('perihal') }}</textarea>

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

                                        Upload PDF, JPG, JPEG, PNG, WEBP atau scan menggunakan kamera.

                                    </p>

                                </div>

                                <span class="archive-card-badge required">
                                    Wajib
                                </span>

                            </div>


                            {{-- Mode Selector --}}
                            <div class="mode-selector">

                                <button type="button"
                                        id="btn-upload"
                                        onclick="switchMode('upload')"
                                        class="mode-button mode-button-active">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24"
                                         aria-hidden="true">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V4m0 0L8 8m4-4l4 4"/>

                                    </svg>

                                    Upload File

                                </button>


                                <button type="button"
                                        id="btn-camera"
                                        onclick="switchMode('camera')"
                                        class="mode-button">

                                    <svg class="w-4 h-4"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24"
                                         aria-hidden="true">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M3 8h2l2-3h10l2 3h2a2 2 0 012 2v9a2 2 0 01-2 2H3a2 2 0 01-2-2v-9a2 2 0 012-2zm9 3a3 3 0 100 6 3 3 0 000-6z"/>

                                    </svg>

                                    Scan Kamera

                                </button>

                            </div>


                            {{-- =================================================
                                 UPLOAD MODE
                            ================================================== --}}
                            <div id="mode-upload">

                                <div id="upload-box"
                                     class="upload-box @error('lampiran_file') upload-box-error @enderror">

                                    <label for="lampiran_file"
                                           class="absolute inset-0 cursor-pointer z-10">
                                    </label>


                                    <div class="upload-icon">

                                        <svg class="w-6 h-6"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24"
                                             aria-hidden="true">

                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>

                                        </svg>

                                    </div>


                                    <span id="file-label-text"
                                          class="text-sm font-bold text-slate-700 text-center">

                                        Klik untuk memilih file

                                    </span>


                                    <span class="mt-1 text-xs text-slate-500 text-center">

                                        PDF, JPG, JPEG, PNG, WEBP

                                    </span>


                                    <span class="mt-0.5 text-[11px] font-medium text-slate-400 text-center">

                                        Maksimal 15 MB

                                    </span>


                                    <input type="file"
                                           name="lampiran_file"
                                           id="lampiran_file"
                                           accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp"
                                           class="absolute w-px h-px opacity-0 pointer-events-none"
                                           tabindex="-1">

                                </div>


                                {{-- File terpilih --}}
                                <div id="selected-file"
                                     class="hidden mt-2 px-3 py-2.5 rounded-lg border-2 border-emerald-200 bg-emerald-50">

                                    <div class="flex items-center gap-2">

                                        <div class="flex items-center justify-center w-7 h-7 rounded-md bg-emerald-100 border border-emerald-200 text-emerald-600 shrink-0">

                                            <svg class="w-4 h-4"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24"
                                                 aria-hidden="true">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M5 13l4 4L19 7"/>

                                            </svg>

                                        </div>


                                        <div class="min-w-0 flex-1">

                                            <p class="text-xs font-bold text-emerald-700">
                                                File siap diupload
                                            </p>

                                            <p id="selected-file-name"
                                               class="text-xs text-emerald-600 truncate">
                                            </p>

                                        </div>


                                        <button type="button"
                                                id="clear-file-btn"
                                                class="inline-flex items-center justify-center w-7 h-7 rounded-md border-2 border-emerald-200 bg-white text-emerald-600 hover:bg-emerald-100 transition"
                                                title="Hapus pilihan file">

                                            <svg class="w-3.5 h-3.5"
                                                 fill="none"
                                                 stroke="currentColor"
                                                 viewBox="0 0 24 24"
                                                 aria-hidden="true">

                                                <path stroke-linecap="round"
                                                      stroke-linejoin="round"
                                                      stroke-width="2"
                                                      d="M6 18L18 6M6 6l12 12"/>

                                            </svg>

                                        </button>

                                    </div>

                                </div>

                            </div>


                            {{-- =================================================
                                 CAMERA MODE
                            ================================================== --}}
                            <div id="mode-camera"
                                 class="hidden">

                                <div id="camera-container"
                                     class="camera-container">

                                    <video id="video"
                                           autoplay
                                           muted
                                           playsinline
                                           class="w-full h-full object-cover">
                                    </video>


                                    <img id="image-preview"
                                         src=""
                                         alt="Preview hasil scan"
                                         class="hidden absolute inset-0 w-full h-full object-contain bg-slate-950">


                                    <div id="camera-placeholder"
                                         class="camera-placeholder">

                                        Kamera belum aktif

                                    </div>

                                </div>


                                <div class="flex flex-wrap justify-center gap-1.5 mt-2">

                                    <button type="button"
                                            id="start-cam-btn"
                                            onclick="startCamera()"
                                            class="camera-button bg-blue-600 hover:bg-blue-700 border-blue-600">

                                        Nyalakan Kamera

                                    </button>


                                    <button type="button"
                                            id="capture-btn"
                                            onclick="takeSnapshot()"
                                            class="hidden camera-button bg-emerald-600 hover:bg-emerald-700 border-emerald-600">

                                        Ambil Foto

                                    </button>


                                    <button type="button"
                                            id="retake-btn"
                                            onclick="retakeSnapshot()"
                                            class="hidden camera-button bg-amber-500 hover:bg-amber-600 border-amber-500">

                                        Foto Ulang

                                    </button>


                                    <button type="button"
                                            id="stop-cam-btn"
                                            onclick="stopCamera()"
                                            class="hidden camera-button bg-rose-600 hover:bg-rose-700 border-rose-600">

                                        Tutup Kamera

                                    </button>

                                </div>


                                <input type="hidden"
                                       name="captured_image"
                                       id="captured_image"
                                       value="{{ old('captured_image') }}">


                                <div id="snapshot-preview"
                                     class="{{ old('captured_image') ? '' : 'hidden' }} mt-1.5 text-center text-xs font-semibold text-emerald-600">

                                    ✓ Hasil scan berhasil diambil.

                                </div>

                            </div>


                            {{-- Error file --}}
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

                                    <svg class="w-5 h-5"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24"
                                         aria-hidden="true">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>

                                    </svg>

                                </div>


                                <h4 class="text-sm font-bold text-slate-700">
                                    Lokasi Penyimpanan
                                </h4>


                                <p class="mt-0.5 max-w-sm text-xs leading-relaxed text-slate-400">

                                    Masukkan posisi rak, lemari, box, atau map tempat arsip disimpan.

                                </p>


                                <div class="w-full mt-3">

                                    <label for="lokasi_arsip_fisik"
                                           class="form-label text-left">

                                        Detail Posisi Lemari / Box

                                    </label>


                                    <input type="text"
                                           id="lokasi_arsip_fisik"
                                           name="lokasi_arsip_fisik"
                                           value="{{ old('lokasi_arsip_fisik') }}"
                                           placeholder="Contoh: Rak A-3 Box 12"
                                           class="form-control-custom @error('lokasi_arsip_fisik') form-error @enderror">


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

                <a href="{{ route('surat-masuk.index') }}"
                   class="action-button action-button-secondary">

                    Batal

                </a>


                <button type="submit"
                        id="submit-btn"
                        class="action-button action-button-primary">

                    <svg class="w-4 h-4 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24"
                         aria-hidden="true">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"/>

                    </svg>

                    <span id="submit-text">
                        Simpan Surat Masuk
                    </span>

                </button>

            </div>

        </div>

    </form>

</div>


{{-- =============================================================
     STYLE
============================================================= --}}
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

    .camera-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: .68rem;
        font-weight: 500;
        background: rgba(2, 6, 23, .35);
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


{{-- =============================================================
     JAVASCRIPT
============================================================= --}}
@push('scripts')
<script>
    let videoStream = null;

    const form = document.getElementById('form-surat');

    const fileInput = document.getElementById('lampiran_file');
    const capturedInput = document.getElementById('captured_image');

    const uploadMode = document.getElementById('mode-upload');
    const cameraMode = document.getElementById('mode-camera');

    const uploadButton = document.getElementById('btn-upload');
    const cameraButton = document.getElementById('btn-camera');

    const uploadBox = document.getElementById('upload-box');

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


    /*
     * ============================================================
     * PILIH FILE
     * ============================================================
     *
     * Label dibuat sebagai area klik.
     * Input file tetap elemen input asli.
     */
    if (uploadBox && fileInput) {

        uploadBox.addEventListener(
            'click',
            function (event) {

                /*
                 * Jangan membuka file picker dua kali
                 * apabila yang diklik adalah label.
                 */
                if (
                    event.target === fileInput ||
                    event.target.closest('button')
                ) {
                    return;
                }

                fileInput.click();
            }
        );

    }


    /*
     * ============================================================
     * FILE CHANGE
     * ============================================================
     */
    if (fileInput) {

        fileInput.addEventListener(
            'change',
            function () {

                if (
                    !this.files ||
                    !this.files.length
                ) {
                    resetFileDisplay();
                    return;
                }

                const file = this.files[0];

                /*
                 * Maksimal 15 MB.
                 */
                const maxSize =
                    15 * 1024 * 1024;

                if (file.size > maxSize) {

                    alert(
                        'Ukuran file terlalu besar. Maksimal 15 MB.'
                    );

                    this.value = '';

                    resetFileDisplay();

                    return;
                }

                /*
                 * Jangan melakukan validasi MIME
                 * secara ketat di Javascript.
                 *
                 * Laravel akan melakukan validasi
                 * PDF/JPG/JPEG/PNG/WEBP.
                 *
                 * Ini mencegah JPG/PNG yang MIME-nya
                 * dibaca browser secara berbeda
                 * menjadi ditolak di sisi browser.
                 */

                /*
                 * File baru dipilih,
                 * hasil kamera harus dihapus.
                 */
                clearCapturedImage();

                fileLabelText.textContent =
                    'Terpilih: ' + file.name;

                fileLabelText.classList.remove(
                    'text-slate-700'
                );

                fileLabelText.classList.add(
                    'text-blue-600'
                );

                selectedFile.classList.remove(
                    'hidden'
                );

                selectedFileName.textContent =
                    file.name;

                uploadBox.classList.remove(
                    'upload-box-error'
                );

                stopCamera();

            }
        );

    }


    /*
     * ============================================================
     * HAPUS FILE
     * ============================================================
     */
    if (clearFileButton) {

        clearFileButton.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                fileInput.value = '';

                resetFileDisplay();

            }
        );

    }


    function resetFileDisplay() {

        if (fileLabelText) {

            fileLabelText.textContent =
                'Klik untuk memilih file';

            fileLabelText.classList.remove(
                'text-blue-600'
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

            selectedFileName.textContent = '';

        }

    }


    /*
     * ============================================================
     * CLEAR CAPTURED IMAGE
     * ============================================================
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

    }


    /*
     * ============================================================
     * SWITCH MODE
     * ============================================================
     */
    function switchMode(mode) {

        /*
         * Hentikan stream lama.
         */
        stopCamera();

        if (mode === 'upload') {

            uploadMode.classList.remove(
                'hidden'
            );

            cameraMode.classList.add(
                'hidden'
            );

            uploadButton.classList.add(
                'mode-button-active'
            );

            cameraButton.classList.remove(
                'mode-button-active'
            );

            /*
             * Saat memilih upload,
             * hasil kamera tidak digunakan.
             */
            clearCapturedImage();

            return;
        }


        /*
         * CAMERA
         */
        uploadMode.classList.add(
            'hidden'
        );

        cameraMode.classList.remove(
            'hidden'
        );

        cameraButton.classList.add(
            'mode-button-active'
        );

        uploadButton.classList.remove(
            'mode-button-active'
        );

        /*
         * Saat berpindah ke kamera,
         * file upload dibersihkan agar hanya
         * satu sumber dokumen yang dikirim.
         */
        if (fileInput) {

            fileInput.value = '';

        }

        resetFileDisplay();

    }


    /*
     * ============================================================
     * START CAMERA
     * ============================================================
     */
    async function startCamera() {

        if (
            !navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia
        ) {

            alert(
                'Browser Anda tidak mendukung akses kamera.'
            );

            return;
        }


        try {

            stopCamera();

            clearCapturedImage();

            cameraPlaceholder.classList.add(
                'hidden'
            );

            imagePreview.classList.add(
                'hidden'
            );

            video.classList.remove(
                'hidden'
            );


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


            video.srcObject =
                videoStream;

            await video.play();


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

            cameraPlaceholder.classList.remove(
                'hidden'
            );

            alert(
                'Gagal mengakses kamera. Pastikan izin kamera diberikan pada browser.'
            );
        }
    }


    /*
     * ============================================================
     * STOP CAMERA
     * ============================================================
     */
    function stopCamera() {

        if (videoStream) {

            videoStream
                .getTracks()
                .forEach(
                    track => {
                        try {
                            track.stop();
                        } catch (error) {
                            console.warn(error);
                        }
                    }
                );

            videoStream = null;
        }

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

        if (stopCamButton) {

            stopCamButton.classList.add(
                'hidden'
            );

        }

    }


    /*
     * ============================================================
     * TAKE SNAPSHOT
     * ============================================================
     */
    async function takeSnapshot() {

        if (!videoStream) {

            alert(
                'Kamera belum aktif.'
            );

            return;
        }

        try {

            const track =
                videoStream.getVideoTracks()[0];


            if (
                typeof ImageCapture !==
                'undefined'
            ) {

                const imageCapture =
                    new ImageCapture(track);

                const blob =
                    await imageCapture.takePhoto();


                const reader =
                    new FileReader();


                reader.onloadend =
                    function () {

                        const result =
                            reader.result;


                        if (
                            !result ||
                            typeof result !== 'string'
                        ) {

                            alert(
                                'Hasil kamera tidak valid.'
                            );

                            return;
                        }


                        capturedInput.value =
                            result;


                        imagePreview.src =
                            result;

                        imagePreview.classList.remove(
                            'hidden'
                        );

                        video.classList.add(
                            'hidden'
                        );


                        snapshotPreview.classList.remove(
                            'hidden'
                        );


                        /*
                         * Setelah foto berhasil,
                         * file upload tetap kosong.
                         */
                        if (fileInput) {

                            fileInput.value = '';

                        }

                        resetFileDisplay();

                        stopCamera();

                        startCamButton.classList.add(
                            'hidden'
                        );

                        retakeButton.classList.remove(
                            'hidden'
                        );

                    };


                reader.readAsDataURL(
                    blob
                );

                return;
            }


            alert(
                'Browser ini tidak mendukung pengambilan foto langsung. Silakan gunakan Upload File.'
            );

        } catch (error) {

            console.error(
                'Capture error:',
                error
            );

            alert(
                'Gagal mengambil foto dari kamera.'
            );
        }
    }


    /*
     * ============================================================
     * RETAKE
     * ============================================================
     */
    function retakeSnapshot() {

        clearCapturedImage();

        startCamera();

    }


    /*
     * ============================================================
     * FORM SUBMIT
     * ============================================================
     */
    if (form) {

        form.addEventListener(
            'submit',
            function (event) {

                /*
                 * Hentikan kamera sebelum submit.
                 */
                stopCamera();


                const hasFile =
                    fileInput &&
                    fileInput.files &&
                    fileInput.files.length > 0;


                const hasCamera =
                    capturedInput &&
                    capturedInput.value.trim() !== '';


                /*
                 * Harus memilih salah satu:
                 * file atau kamera.
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
                        'Berkas digital wajib diisi. Silakan upload PDF/JPG/PNG atau scan menggunakan kamera.'
                    );

                    switchMode('upload');

                    return;
                }


                /*
                 * Tidak boleh keduanya.
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
                 * Cegah double submit.
                 */
                submitButton.disabled =
                    true;


                submitText.textContent =
                    'Menyimpan...';

            }
        );

    }


    /*
     * ============================================================
     * INITIAL STATE
     * ============================================================
     */
    document.addEventListener(
        'DOMContentLoaded',
        function () {

            switchMode(
                'upload'
            );

        }
    );


    /*
     * ============================================================
     * PAGE LEAVE
     * ============================================================
     */
    window.addEventListener(
        'beforeunload',
        function () {

            stopCamera();

        }
    );


    /*
     * Untuk onclick di HTML.
     */
    window.switchMode =
        switchMode;

    window.startCamera =
        startCamera;

    window.takeSnapshot =
        takeSnapshot;

    window.retakeSnapshot =
        retakeSnapshot;

    window.stopCamera =
        stopCamera;
</script>
@endpush

@endsection