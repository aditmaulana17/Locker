@extends('layouts.app')

@section('title', 'Catat Surat Masuk')

@section('content')

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">

```
{{-- =========================================================
     HEADER
========================================================== --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">

    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-800">
            Catat Surat Masuk
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Isi formulir di bawah ini untuk menambahkan data arsip surat masuk baru.
        </p>
    </div>

    <a href="{{ route('surat-masuk.index') }}"
       class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2.5
              text-sm font-semibold text-slate-700 bg-white
              border-2 border-slate-300 rounded-xl
              hover:bg-slate-50 hover:border-slate-400
              transition duration-150">

        <svg class="w-4 h-4 mr-2"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>

        Kembali
    </a>
</div>


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
           value="{{ $nomorAgenda }}">


    {{-- =====================================================
         CARD UTAMA
    ====================================================== --}}
    <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-sm overflow-hidden">


        {{-- =================================================
             NOMOR AGENDA
        ================================================== --}}
        <div class="px-5 sm:px-6 py-4
                    bg-gradient-to-r from-blue-50 to-indigo-50
                    border-b-2 border-blue-200">

            <div class="flex flex-col sm:flex-row
                        sm:items-center sm:justify-between gap-3">

                <div class="flex items-center">

                    <div class="flex items-center justify-center
                                w-9 h-9 mr-3
                                bg-blue-100
                                border-2 border-blue-200
                                rounded-xl
                                text-blue-600
                                shrink-0">

                        <svg class="w-4 h-4"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 013-3h0a3 3 0 013 3m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>

                    <div class="flex flex-col sm:flex-row
                                sm:items-center gap-1.5">

                        <span class="text-sm font-medium text-blue-900">
                            Nomor Agenda Sistem:
                        </span>

                        <strong class="inline-flex items-center w-fit
                                       px-3 py-1.5
                                       bg-white
                                       border-2 border-blue-200
                                       rounded-lg
                                       text-xs font-bold font-mono
                                       text-blue-700">
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
             CONTENT
        ================================================== --}}
        <div class="p-5 sm:p-6 lg:p-7">


            {{-- =================================================
                 SECTION 1
            ================================================== --}}
            <section>

                <div class="flex items-center gap-3
                            pb-3 mb-5
                            border-b-2 border-slate-200">

                    <div class="w-2 h-6 bg-blue-600 rounded-full shrink-0"></div>

                    <div>
                        <h2 class="text-sm font-bold
                                   uppercase tracking-wide
                                   text-slate-700">
                            Informasi Utama Surat
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-400">
                            Lengkapi identitas dan informasi utama surat masuk.
                        </p>
                    </div>
                </div>


                {{-- FORM GRID --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-5">


                    {{-- Nomor Surat --}}
                    <div class="form-group">

                        <label class="form-label">
                            Nomor Surat
                            <span class="text-rose-500">*</span>
                        </label>

                        <input type="text"
                               name="nomor_surat"
                               value="{{ old('nomor_surat') }}"
                               required
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

                        <label class="form-label">
                            Instansi Pengirim
                            <span class="text-rose-500">*</span>
                        </label>

                        <input type="text"
                               name="pengirim"
                               value="{{ old('pengirim') }}"
                               required
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

                        <label class="form-label">
                            Tanggal Surat
                            <span class="text-rose-500">*</span>
                        </label>

                        <input type="date"
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

                        <label class="form-label">
                            Tanggal Diterima
                            <span class="text-rose-500">*</span>
                        </label>

                        <input type="date"
                               name="tanggal_terima"
                               value="{{ old('tanggal_terima', date('Y-m-d')) }}"
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

                        <label class="form-label">
                            Kategori Surat
                            <span class="text-rose-500">*</span>
                        </label>

                        <select name="kategori_surat_id"
                                required
                                class="form-control-custom @error('kategori_surat_id') form-error @enderror">

                            <option value=""
                                    disabled
                                    {{ old('kategori_surat_id') ? '' : 'selected' }}>
                                Pilih kategori surat
                            </option>

                            @foreach($kategoris as $k)

                                <option value="{{ $k->id }}"
                                    {{ old('kategori_surat_id') == $k->id ? 'selected' : '' }}>

                                    {{ $k->nama_kategori }}

                                    @if(isset($k->sifat))
                                        ({{ ucfirst($k->sifat) }})
                                    @endif

                                </option>

                            @endforeach

                        </select>

                        @error('kategori_surat_id')
                            <p class="form-error-text">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Status --}}
                    <div class="form-group">

                        <label class="form-label">
                            Status Surat
                            <span class="text-rose-500">*</span>
                        </label>

                        <select name="status"
                                required
                                class="form-control-custom @error('status') form-error @enderror">

                            <option value="baru"
                                {{ old('status', 'baru') == 'baru' ? 'selected' : '' }}>
                                Baru
                            </option>

                            <option value="diproses"
                                {{ old('status') == 'diproses' ? 'selected' : '' }}>
                                Diproses
                            </option>

                            <option value="didisposisikan"
                                {{ old('status') == 'didisposisikan' ? 'selected' : '' }}>
                                Didisposisikan
                            </option>

                            <option value="selesai"
                                {{ old('status') == 'selesai' ? 'selected' : '' }}>
                                Selesai
                            </option>

                            <option value="diarsipkan"
                                {{ old('status') == 'diarsipkan' ? 'selected' : '' }}>
                                Diarsipkan
                            </option>

                        </select>

                        @error('status')
                            <p class="form-error-text">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>


                    {{-- Perihal --}}
                    <div class="md:col-span-2 form-group">

                        <label class="form-label">
                            Perihal / Isi Ringkas
                            <span class="text-rose-500">*</span>
                        </label>

                        <textarea name="perihal"
                                  rows="3"
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
                 SECTION 2
            ================================================== --}}
            <section class="mt-8">

                <div class="flex items-center gap-3
                            pb-3 mb-5
                            border-b-2 border-slate-200">

                    <div class="w-2 h-6 bg-indigo-600 rounded-full shrink-0"></div>

                    <div>
                        <h2 class="text-sm font-bold
                                   uppercase tracking-wide
                                   text-slate-700">
                            Lampiran Dokumen & Arsip Fisik
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-400">
                            Tambahkan dokumen digital dan lokasi penyimpanan arsip fisik.
                        </p>
                    </div>
                </div>


                {{-- TWO COLUMN --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-stretch">


                    {{-- =================================================
                         BERKAS DIGITAL
                    ================================================== --}}
                    <div class="flex flex-col
                                p-5
                                bg-slate-50
                                border-2 border-slate-300
                                rounded-2xl">

                        <div class="flex items-center
                                    justify-between gap-3 mb-4">

                            <div>
                                <h3 class="text-sm font-bold text-slate-700">
                                    Berkas Digital
                                </h3>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    Upload dokumen atau gunakan kamera.
                                </p>
                            </div>

                            <span class="px-2.5 py-1
                                         text-[11px] font-semibold
                                         text-slate-600
                                         bg-white
                                         border-2 border-slate-300
                                         rounded-lg">
                                Opsional
                            </span>
                        </div>


                        {{-- TAB MODE --}}
                        <div class="flex w-fit p-1 mb-4
                                    bg-slate-200
                                    border-2 border-slate-300
                                    rounded-xl">

                            <button type="button"
                                    onclick="switchMode('upload')"
                                    id="btn-upload"
                                    class="px-4 py-2
                                           rounded-lg
                                           bg-white
                                           text-blue-600
                                           border border-slate-200
                                           shadow-sm
                                           text-xs font-semibold
                                           transition">

                                Upload File
                            </button>

                            <button type="button"
                                    onclick="switchMode('camera')"
                                    id="btn-camera"
                                    class="px-4 py-2
                                           rounded-lg
                                           text-slate-600
                                           text-xs font-semibold
                                           transition">

                                Scan Kamera
                            </button>
                        </div>


                        {{-- =================================================
                             MODE UPLOAD
                        ================================================== --}}
                        <div id="mode-upload" class="flex-1">

                            <label id="upload-box-label"
                                   class="relative flex flex-col
                                          items-center justify-center
                                          h-full min-h-[190px]
                                          p-6
                                          text-center
                                          bg-white
                                          border-2 border-dashed
                                          border-slate-400
                                          rounded-xl
                                          cursor-pointer
                                          hover:bg-slate-50
                                          hover:border-slate-500
                                          transition
                                          group
                                          @error('lampiran_file')
                                              !border-rose-500 !bg-rose-50/30
                                          @enderror">

                                <div class="flex items-center justify-center
                                            w-12 h-12 mb-3
                                            bg-blue-50
                                            border-2 border-blue-100
                                            rounded-full
                                            text-blue-600
                                            group-hover:scale-105
                                            transition-transform">

                                    <svg class="w-6 h-6"
                                         fill="none"
                                         stroke="currentColor"
                                         viewBox="0 0 24 24">

                                        <path stroke-linecap="round"
                                              stroke-linejoin="round"
                                              stroke-width="2"
                                              d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>

                                <span id="file-label-text"
                                      class="text-sm font-bold text-slate-700">
                                    Klik untuk memilih file
                                </span>

                                <span class="mt-1 text-xs text-slate-400">
                                    PDF, JPG, JPEG, PNG · Maks. 10MB
                                </span>

                                <input type="file"
                                       name="lampiran_file"
                                       id="lampiran_file"
                                       accept=".pdf,.jpg,.jpeg,.png"
                                       class="absolute inset-0
                                              w-full h-full
                                              opacity-0 cursor-pointer"
                                       onchange="updateFileName(this)">
                            </label>
                        </div>


                        {{-- =================================================
                             MODE CAMERA
                        ================================================== --}}
                        <div id="mode-camera"
                             class="hidden flex-1">

                            <div id="camera-container"
                                 class="relative w-full
                                        aspect-[4/3]
                                        bg-slate-950
                                        border-2 border-slate-400
                                        rounded-xl
                                        overflow-hidden">

                                {{-- VIDEO CAMERA --}}
                                <video id="video"
                                       autoplay
                                       playsinline
                                       class="w-full h-full object-cover">
                                </video>

                                {{-- PREVIEW FOTO --}}
                                <img id="image-preview"
                                     class="hidden
                                            absolute inset-0
                                            w-full h-full
                                            object-contain
                                            bg-slate-950"
                                     alt="Preview Scan">

                                <div id="camera-placeholder"
                                     class="absolute inset-0
                                            flex items-center
                                            justify-center
                                            text-xs font-medium
                                            text-white
                                            bg-slate-950/40">
                                    Kamera belum aktif
                                </div>
                            </div>


                            {{-- CAMERA BUTTONS --}}
                            <div class="flex flex-wrap
                                        justify-center gap-2 mt-4">

                                <button type="button"
                                        id="start-cam-btn"
                                        onclick="startCamera()"
                                        class="px-4 py-2.5
                                               text-xs font-bold
                                               text-white
                                               bg-blue-600
                                               border-2 border-blue-600
                                               rounded-xl
                                               hover:bg-blue-700
                                               transition">

                                    Nyalakan Kamera
                                </button>

                                <button type="button"
                                        id="capture-btn"
                                        onclick="takeSnapshot()"
                                        class="hidden
                                               px-4 py-2.5
                                               text-xs font-bold
                                               text-white
                                               bg-emerald-600
                                               border-2 border-emerald-600
                                               rounded-xl
                                               hover:bg-emerald-700
                                               transition">

                                    Ambil Foto
                                </button>

                                <button type="button"
                                        id="retake-btn"
                                        onclick="retakeSnapshot()"
                                        class="hidden
                                               px-4 py-2.5
                                               text-xs font-bold
                                               text-white
                                               bg-amber-500
                                               border-2 border-amber-500
                                               rounded-xl
                                               hover:bg-amber-600
                                               transition">

                                    Foto Ulang
                                </button>

                                <button type="button"
                                        id="stop-cam-btn"
                                        onclick="stopCamera()"
                                        class="hidden
                                               px-4 py-2.5
                                               text-xs font-bold
                                               text-white
                                               bg-rose-600
                                               border-2 border-rose-600
                                               rounded-xl
                                               hover:bg-rose-700
                                               transition">

                                    Tutup Kamera
                                </button>
                            </div>


                            <input type="hidden"
                                   name="captured_image"
                                   id="captured_image"
                                   value="{{ old('captured_image') }}">


                            <div id="snapshot-preview"
                                 class="{{ old('captured_image') ? '' : 'hidden' }}
                                        mt-3
                                        text-center
                                        text-xs
                                        font-semibold
                                        text-emerald-600">

                                ✓ Hasil scan berhasil diambil!
                            </div>
                        </div>


                        {{-- ERROR --}}
                        <div class="mt-3 space-y-1">

                            @error('lampiran_file')
                                <p class="text-xs font-medium text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror

                            @error('captured_image')
                                <p class="text-xs font-medium text-rose-600">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>
                    </div>


                    {{-- =================================================
                         ARSIP FISIK
                    ================================================== --}}
                    <div class="flex flex-col
                                p-5
                                bg-slate-50
                                border-2 border-slate-300
                                rounded-2xl">

                        <div class="flex items-center
                                    justify-between gap-3 mb-4">

                            <div>
                                <h3 class="text-sm font-bold text-slate-700">
                                    Lokasi Arsip Fisik
                                </h3>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    Catat posisi berkas asli di ruang penyimpanan.
                                </p>
                            </div>

                            <div class="flex items-center justify-center
                                        w-9 h-9
                                        bg-white
                                        border-2 border-slate-300
                                        rounded-lg
                                        text-slate-500">

                                <svg class="w-4 h-4"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                        </div>


                        {{-- PHYSICAL LOCATION --}}
                        <div class="flex-1
                                    p-5
                                    bg-white
                                    border-2 border-slate-300
                                    rounded-xl">

                            <label class="flex items-center gap-2
                                          mb-3
                                          text-xs font-bold
                                          text-slate-700">

                                <svg class="w-4 h-4 text-slate-500"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>

                                Detail Posisi Lemari / Box
                            </label>


                            <input type="text"
                                   name="lokasi_arsip_fisik"
                                   value="{{ old('lokasi_arsip_fisik') }}"
                                   placeholder="Contoh: Rak A-3 Box 12"
                                   class="form-control-custom @error('lokasi_arsip_fisik') form-error @enderror">


                            @error('lokasi_arsip_fisik')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror


                            <div class="mt-4
                                        p-3
                                        bg-slate-50
                                        border-2 border-slate-200
                                        rounded-lg">

                                <p class="text-xs leading-relaxed text-slate-500">
                                    Gunakan format lokasi yang mudah ditemukan kembali,
                                    misalnya
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
             FOOTER BUTTON
        ================================================== --}}
        <div class="flex flex-col-reverse
                    sm:flex-row
                    sm:items-center
                    sm:justify-end
                    gap-3
                    px-5 sm:px-6
                    py-4
                    bg-slate-50
                    border-t-2 border-slate-300">

            <a href="{{ route('surat-masuk.index') }}"
               class="inline-flex items-center
                      justify-center
                      w-full sm:w-auto
                      px-5 py-2.5
                      text-sm font-semibold
                      text-slate-700
                      bg-white
                      border-2 border-slate-300
                      rounded-xl
                      hover:bg-slate-100
                      hover:border-slate-400
                      transition">

                Batal
            </a>


            <button type="submit"
                    id="submit-btn"
                    class="inline-flex items-center
                           justify-center
                           w-full sm:w-auto
                           px-5 py-2.5
                           text-sm font-semibold
                           text-white
                           bg-blue-600
                           border-2 border-blue-600
                           rounded-xl
                           hover:bg-blue-700
                           hover:border-blue-700
                           shadow-md shadow-blue-600/20
                           transition
                           disabled:opacity-50
                           disabled:cursor-not-allowed">

                <svg class="w-4 h-4 mr-2"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M5 13l4 4L19 7"/>
                </svg>

                Simpan Surat Masuk
            </button>
        </div>

    </div>
</form>
```

</div>

{{-- =============================================================
CUSTOM STYLE
============================================================== --}}

<style>

    .form-group {
        width: 100%;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        color: #334155;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .form-control-custom {
        width: 100%;
        min-height: 44px;
        padding: 0.65rem 0.85rem;

        background: #ffffff;
        color: #334155;

        border: 2px solid #94a3b8;
        border-radius: 0.75rem;

        outline: none;

        font-size: 0.875rem;
        line-height: 1.4;

        transition:
            border-color 0.15s ease,
            background-color 0.15s ease,
            box-shadow 0.15s ease;
    }

    .form-control-custom:hover {
        border-color: #64748b;
    }

    .form-control-custom:focus {
        border-color: #2563eb;
        background: #ffffff;

        box-shadow:
            0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .form-control-custom::placeholder {
        color: #94a3b8;
    }

    select.form-control-custom {
        cursor: pointer;
    }

    .form-textarea {
        min-height: 105px;
        resize: vertical;
    }

    .form-error {
        border-color: #f43f5e !important;
        background: #fff1f2 !important;
    }

    .form-error-text {
        margin-top: 0.375rem;
        color: #e11d48;
        font-size: 0.75rem;
        font-weight: 500;
    }

    #camera-container video {
        display: block;
    }

    #camera-container img {
        z-index: 2;
    }

    @media (max-width: 640px) {

        .form-control-custom {
            min-height: 43px;
            font-size: 0.875rem;
        }

        .form-textarea {
            min-height: 100px;
        }
    }

</style>

{{-- =============================================================
JAVASCRIPT
============================================================== --}}
@push('scripts')

<script>

    let videoStream = null;


    /* =========================================================
       SWITCH MODE
    ========================================================== */
    function switchMode(mode) {

        stopCamera();

        const uploadMode = document.getElementById('mode-upload');
        const cameraMode = document.getElementById('mode-camera');

        const uploadBtn = document.getElementById('btn-upload');
        const cameraBtn = document.getElementById('btn-camera');


        if (mode === 'upload') {

            uploadMode.classList.remove('hidden');
            cameraMode.classList.add('hidden');

            uploadBtn.classList.add(
                'bg-white',
                'text-blue-600',
                'shadow-sm'
            );

            cameraBtn.classList.remove(
                'bg-white',
                'text-blue-600',
                'shadow-sm'
            );

            cameraBtn.classList.add('text-slate-600');

            return;
        }


        uploadMode.classList.add('hidden');
        cameraMode.classList.remove('hidden');

        cameraBtn.classList.add(
            'bg-white',
            'text-blue-600',
            'shadow-sm'
        );

        cameraBtn.classList.remove('text-slate-600');

        uploadBtn.classList.remove(
            'bg-white',
            'text-blue-600',
            'shadow-sm'
        );

        uploadBtn.classList.add('text-slate-600');
    }


    /* =========================================================
       FILE NAME
    ========================================================== */
    function updateFileName(input) {

        const textElement =
            document.getElementById('file-label-text');


        if (input.files && input.files.length > 0) {

            textElement.textContent =
                'Terpilih: ' + input.files[0].name;

            textElement.classList.remove(
                'text-slate-700'
            );

            textElement.classList.add(
                'text-blue-600'
            );

        } else {

            textElement.textContent =
                'Klik untuk memilih file';

            textElement.classList.remove(
                'text-blue-600'
            );

            textElement.classList.add(
                'text-slate-700'
            );
        }
    }


    /* =========================================================
       START CAMERA
    ========================================================== */
    async function startCamera() {

        const video =
            document.getElementById('video');

        const placeholder =
            document.getElementById('camera-placeholder');

        const imagePreview =
            document.getElementById('image-preview');

        const startButton =
            document.getElementById('start-cam-btn');

        const captureButton =
            document.getElementById('capture-btn');

        const stopButton =
            document.getElementById('stop-cam-btn');

        const retakeButton =
            document.getElementById('retake-btn');


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

            placeholder.classList.add('hidden');

            imagePreview.classList.add('hidden');

            video.classList.remove('hidden');


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


            startButton.classList.add('hidden');

            captureButton.classList.remove('hidden');

            stopButton.classList.remove('hidden');

            retakeButton.classList.add('hidden');


        } catch (error) {

            console.error(error);

            placeholder.classList.remove('hidden');

            alert(
                'Gagal mengakses kamera. ' +
                'Pastikan izin kamera telah diberikan pada browser.'
            );
        }
    }


    /* =========================================================
       STOP CAMERA
    ========================================================== */
    function stopCamera() {

        if (videoStream) {

            videoStream
                .getTracks()
                .forEach(track => track.stop());

            videoStream = null;
        }


        const video =
            document.getElementById('video');

        if (video) {
            video.srcObject = null;
        }


        const capturedImage =
            document.getElementById('captured_image');

        const placeholder =
            document.getElementById('camera-placeholder');


        if (
            placeholder &&
            capturedImage &&
            capturedImage.value === ''
        ) {

            placeholder.classList.remove('hidden');
        }


        const startButton =
            document.getElementById('start-cam-btn');

        const captureButton =
            document.getElementById('capture-btn');

        const stopButton =
            document.getElementById('stop-cam-btn');


        if (startButton) {
            startButton.classList.remove('hidden');
        }

        if (captureButton) {
            captureButton.classList.add('hidden');
        }

        if (stopButton) {
            stopButton.classList.add('hidden');
        }
    }


    /* =========================================================
       TAKE PHOTO
       Tidak menggunakan canvas.
       Menggunakan ImageCapture jika browser mendukung.
    ========================================================== */
    async function takeSnapshot() {

        const video =
            document.getElementById('video');

        const imagePreview =
            document.getElementById('image-preview');

        const capturedImage =
            document.getElementById('captured_image');

        const container =
            document.getElementById('camera-container');


        if (!video.srcObject) {

            alert(
                'Kamera belum aktif.'
            );

            return;
        }


        try {

            const track =
                video.srcObject.getVideoTracks()[0];


            if (
                typeof ImageCapture !== 'undefined'
            ) {

                const imageCapture =
                    new ImageCapture(track);

                const blob =
                    await imageCapture.takePhoto();


                const reader =
                    new FileReader();


                reader.onloadend = function () {

                    capturedImage.value =
                        reader.result;

                    imagePreview.src =
                        reader.result;

                    imagePreview.classList.remove(
                        'hidden'
                    );

                    video.classList.add(
                        'hidden'
                    );

                    document
                        .getElementById('snapshot-preview')
                        .classList.remove('hidden');


                    container.classList.remove(
                        'border-slate-400'
                    );

                    container.classList.add(
                        'border-emerald-500'
                    );


                    stopCamera();


                    document
                        .getElementById('start-cam-btn')
                        .classList.add('hidden');


                    document
                        .getElementById('retake-btn')
                        .classList.remove('hidden');
                };


                reader.readAsDataURL(blob);

                return;
            }


            /*
             * Fallback untuk browser yang tidak mendukung
             * ImageCapture.
             *
             * Preview tetap menggunakan stream kamera.
             * Data gambar tidak dibuat melalui canvas.
             */

            alert(
                'Browser tidak mendukung pengambilan foto langsung dari kamera. ' +
                'Silakan gunakan Upload File.'
            );


        } catch (error) {

            console.error(error);

            alert(
                'Gagal mengambil foto dari kamera.'
            );
        }
    }


    /* =========================================================
       RETAKE
    ========================================================== */
    function retakeSnapshot() {

        const capturedImage =
            document.getElementById('captured_image');

        const imagePreview =
            document.getElementById('image-preview');

        const video =
            document.getElementById('video');

        const container =
            document.getElementById('camera-container');

        const snapshotPreview =
            document.getElementById('snapshot-preview');


        capturedImage.value = '';

        imagePreview.src = '';

        imagePreview.classList.add(
            'hidden'
        );

        video.classList.remove(
            'hidden'
        );

        snapshotPreview.classList.add(
            'hidden'
        );

        container.classList.remove(
            'border-emerald-500'
        );

        container.classList.add(
            'border-slate-400'
        );


        startCamera();
    }


    /* =========================================================
       SUBMIT
    ========================================================== */
    const formSurat =
        document.getElementById('form-surat');


    if (formSurat) {

        formSurat.addEventListener(
            'submit',
            function () {

                stopCamera();


                const submitButton =
                    document.getElementById(
                        'submit-btn'
                    );


                submitButton.disabled = true;


                submitButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24">

                        <circle class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4">
                        </circle>

                        <path class="opacity-75"
                              fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>

                    </svg>

                    Menyimpan...
                `;
            }
        );
    }


    /* =========================================================
       BEFORE UNLOAD
    ========================================================== */
    window.addEventListener(
        'beforeunload',
        function () {
            stopCamera();
        }
    );

</script>

@endpush

@endsection
