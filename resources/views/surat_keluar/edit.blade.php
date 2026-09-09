@extends('layouts.app')

@section('title', 'Edit Surat Keluar')

@section('content')

<div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">

        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-800">
                Edit Surat Keluar
            </h1>

            <p class="mt-0.5 text-sm text-slate-500">
                Perbarui informasi data arsip surat keluar yang tersimpan.
            </p>
        </div>

        <a
            href="{{ route('surat-keluar.index') }}"
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
         FORM
    ========================================================== --}}
    <form
        id="form-surat"
        method="POST"
        action="{{ route('surat-keluar.update', $suratKeluar->id) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-sm overflow-hidden">

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
                                Perbarui identitas dan informasi utama surat keluar.
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
                            </label>

                            <input
                                type="text"
                                id="nomor_surat"
                                name="nomor_surat"
                                value="{{ old('nomor_surat', $suratKeluar->nomor_surat) }}"
                                placeholder="Contoh: 005/SK/I/2026"
                                autocomplete="off"
                                class="form-control-custom @error('nomor_surat') form-error @enderror"
                            >

                            @error('nomor_surat')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Instansi Tujuan --}}
                        <div class="form-group">

                            <label
                                for="pengirim"
                                class="form-label"
                            >
                                Instansi Tujuan
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="text"
                                id="pengirim"
                                name="pengirim"
                                value="{{ old('pengirim', $suratKeluar->pengirim) }}"
                                placeholder="Ketik nama instansi tujuan..."
                                autocomplete="off"
                                required
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
                                value="{{ old('tanggal_surat', optional($suratKeluar->tanggal_surat)->format('Y-m-d')) }}"
                                required
                                class="form-control-custom @error('tanggal_surat') form-error @enderror"
                            >

                            @error('tanggal_surat')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Tanggal Dikirim --}}
                        <div class="form-group">

                            <label
                                for="tanggal_keluar"
                                class="form-label"
                            >
                                Tanggal Dikirim
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="date"
                                id="tanggal_keluar"
                                name="tanggal_keluar"
                                value="{{ old('tanggal_keluar', optional($suratKeluar->tanggal_keluar)->format('Y-m-d')) }}"
                                required
                                class="form-control-custom @error('tanggal_keluar') form-error @enderror"
                            >

                            @error('tanggal_keluar')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Kategori Surat --}}
                        <div class="form-group">

                            <label
                                for="kategori_surat_id"
                                class="form-label"
                            >
                                Kategori Surat
                                <span class="text-rose-500">*</span>
                            </label>

                            <select
                                id="kategori_surat_id"
                                name="kategori_surat_id"
                                required
                                class="form-control-custom @error('kategori_surat_id') form-error @enderror"
                            >

                                <option
                                    value=""
                                    disabled
                                >
                                    Pilih kategori surat
                                </option>

                                @foreach ($kategoris as $kategori)

                                    <option
                                        value="{{ $kategori->id }}"
                                        {{ old('kategori_surat_id', $suratKeluar->kategori_surat_id) == $kategori->id ? 'selected' : '' }}
                                    >
                                        {{ $kategori->nama_kategori }}

                                        @if (!empty($kategori->sifat))
                                            ({{ ucfirst($kategori->sifat) }})
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


                        {{-- Status Surat --}}
                        <div class="form-group">

                            <label
                                for="status"
                                class="form-label"
                            >
                                Status Surat
                                <span class="text-rose-500">*</span>
                            </label>

                            @php
                                $currentStatus = strtolower(
                                    trim(
                                        (string) old(
                                            'status',
                                            $suratKeluar->status
                                        )
                                    )
                                );

                                $currentStatus = $currentStatus === 'draf'
                                    ? 'draft'
                                    : $currentStatus;
                            @endphp

                            <select
                                id="status"
                                name="status"
                                required
                                class="form-control-custom @error('status') form-error @enderror"
                            >

                                <option
                                    value="draft"
                                    {{ $currentStatus === 'draft' ? 'selected' : '' }}
                                >
                                    Draft
                                </option>

                                <option
                                    value="diproses"
                                    {{ $currentStatus === 'diproses' ? 'selected' : '' }}
                                >
                                    Diproses
                                </option>

                                <option
                                    value="disetujui"
                                    {{ $currentStatus === 'disetujui' ? 'selected' : '' }}
                                >
                                    Disetujui
                                </option>

                                <option
                                    value="dikirim"
                                    {{ $currentStatus === 'dikirim' ? 'selected' : '' }}
                                >
                                    Dikirim
                                </option>

                                <option
                                    value="diarsipkan"
                                    {{ $currentStatus === 'diarsipkan' ? 'selected' : '' }}
                                >
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
                            >{{ old('perihal', $suratKeluar->perihal) }}</textarea>

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
                                Lampiran Dokumen Surat
                            </h2>

                            <p class="section-description">
                                Periksa file lama atau upload dokumen baru.
                            </p>
                        </div>

                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-stretch">

                        {{-- =================================================
                             FILE LAMA
                        ================================================== --}}
                        @if ($suratKeluar->lampiran_file)

                            <div class="archive-card">

                                <div class="archive-card-header">

                                    <div>
                                        <h3 class="archive-card-title">
                                            File Lampiran Saat Ini
                                        </h3>

                                        <p class="archive-card-description">
                                            Dokumen yang sedang tersimpan.
                                        </p>
                                    </div>

                                    <span class="archive-card-badge">
                                        Tersimpan
                                    </span>

                                </div>


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
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                            />
                                        </svg>

                                    </div>


                                    <div class="min-w-0 flex-1">

                                        <p class="text-xs font-semibold text-slate-700">
                                            Dokumen saat ini
                                        </p>

                                        <p class="mt-0.5 text-xs text-slate-400 truncate">
                                            {{ basename($suratKeluar->lampiran_file) }}
                                        </p>

                                    </div>


                                    <a
                                        href="{{ route('surat-keluar.preview-lampiran', $suratKeluar) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="shrink-0 px-2.5 py-1 text-xs font-bold text-blue-600 bg-blue-50 border-2 border-blue-100 rounded-lg hover:bg-blue-100 transition"
                                    >
                                        Lihat File
                                    </a>

                                </div>

                            </div>

                        @endif


                        {{-- =================================================
                             UPLOAD FILE BARU
                        ================================================== --}}
                        <div
                            class="archive-card {{ $suratKeluar->lampiran_file ? '' : 'md:col-span-2' }}"
                        >

                            <div class="archive-card-header">

                                <div>
                                    <h3 class="archive-card-title">
                                        Lampiran Baru
                                    </h3>

                                    <p class="archive-card-description">
                                        Upload dokumen baru untuk mengganti lampiran lama.
                                    </p>
                                </div>

                                <span class="archive-card-badge">
                                    Opsional
                                </span>

                            </div>


                            <div id="upload-section">

                                <label
                                    for="lampiran_file"
                                    class="upload-box @error('lampiran_file') upload-box-error @enderror"
                                >

                                    <div class="upload-icon">

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
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                            />
                                        </svg>

                                    </div>


                                    <span
                                        id="file-label-text"
                                        class="text-sm font-bold text-slate-700"
                                    >
                                        Klik untuk memilih file baru
                                    </span>


                                    <span class="mt-0.5 text-xs text-slate-400">
                                        PDF, JPG, JPEG, PNG · Maks. 15MB
                                    </span>


                                    <input
                                        type="file"
                                        id="lampiran_file"
                                        name="lampiran_file"
                                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                    >

                                </label>


                                @error('lampiran_file')
                                    <p class="form-error-text mt-1.5">
                                        {{ $message }}
                                    </p>
                                @enderror


                                <p
                                    id="file-info"
                                    class="hidden mt-1.5 text-xs text-slate-500"
                                ></p>


                                <div class="mt-2 px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg">

                                    <p class="text-xs leading-relaxed text-slate-500">
                                        Tidak memilih file baru berarti file lama tetap dipertahankan.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     VALIDATION ERROR
                ================================================== --}}
                @if ($errors->any())

                    <div class="mt-4 p-3 bg-rose-50 border-2 border-rose-200 text-rose-700 rounded-lg text-xs">

                        <strong class="font-bold">
                            Terjadi Kesalahan Validasi:
                        </strong>

                        <ul class="mt-1 space-y-0.5 list-disc list-inside">

                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }}
                                </li>
                            @endforeach

                        </ul>

                    </div>

                @endif

            </div>


            {{-- =========================================================
                 FOOTER
            ========================================================== --}}
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 px-4 sm:px-5 py-2.5 bg-slate-50 border-t-2 border-slate-300">

                <a
                    href="{{ route('surat-keluar.index') }}"
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

                    Perbarui Surat Keluar

                </button>

            </div>

        </div>

    </form>

</div>


{{-- =============================================================
     STYLE
============================================================= --}}
<style>

    /* =========================================================
       SECTION
    ========================================================= */

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
        flex-shrink: 0;
        border-radius: 9999px;
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


    /* =========================================================
       FORM
    ========================================================= */

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


    /* =========================================================
       ARCHIVE
    ========================================================= */

    .archive-card {
        display: flex;
        flex-direction: column;
        min-height: 205px;
        padding: .8rem;
        background: #f8fafc;
        border: 2px solid #94a3b8;
        border-radius: .8rem;
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


    /* =========================================================
       CURRENT FILE
    ========================================================= */

    .current-file {
        display: flex;
        align-items: center;
        gap: .65rem;
        width: 100%;
        padding: .65rem;
        background: #fff;
        border: 2px solid #cbd5e1;
        border-radius: .6rem;
    }

    .current-file-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        flex-shrink: 0;
        color: #2563eb;
        background: #eff6ff;
        border: 2px solid #dbeafe;
        border-radius: .5rem;
    }


    /* =========================================================
       UPLOAD
    ========================================================= */

    .upload-box {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 150px;
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
        border-color: #475569;
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
        border: 2px solid #dbeafe;
        border-radius: 9999px;
        background: #eff6ff;
        color: #2563eb;
    }


    /* =========================================================
       BUTTON
    ========================================================= */

    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
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
        opacity: .7;
        cursor: not-allowed;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (min-width: 640px) {

        .action-button {
            width: auto;
        }

    }

    @media (max-width: 640px) {

        .archive-card {
            min-height: auto;
        }

        .upload-box {
            min-height: 155px;
        }

        .form-control-custom {
            min-height: 40px;
        }

        .current-file {
            align-items: flex-start;
            flex-wrap: wrap;
        }

    }

</style>


{{-- =============================================================
     JAVASCRIPT
============================================================= --}}
@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('form-surat');
    const fileInput = document.getElementById('lampiran_file');
    const fileLabelText = document.getElementById('file-label-text');
    const fileInfo = document.getElementById('file-info');
    const submitButton = document.getElementById('submit-btn');

    const MAX_FILE_SIZE = 15 * 1024 * 1024;

    let isSubmitting = false;


    /* =========================================================
       FILE CHANGE
    ========================================================= */

    fileInput.addEventListener('change', function () {

        if (!fileInput.files || fileInput.files.length === 0) {
            resetFileInput();
            return;
        }

        const file = fileInput.files[0];


        // -----------------------------------------------------
        // Validasi ukuran file
        // -----------------------------------------------------

        if (file.size > MAX_FILE_SIZE) {

            fileInput.value = '';

            fileLabelText.textContent =
                'Ukuran file melebihi 15 MB';

            fileLabelText.classList.remove(
                'text-slate-700',
                'text-blue-600'
            );

            fileLabelText.classList.add(
                'text-rose-600'
            );

            fileInfo.textContent =
                'Silakan pilih file yang ukurannya tidak lebih dari 15 MB.';

            fileInfo.classList.remove('hidden');

            return;
        }


        // -----------------------------------------------------
        // File valid
        // -----------------------------------------------------

        fileLabelText.textContent =
            `Terpilih: ${file.name}`;

        fileLabelText.classList.remove(
            'text-slate-700',
            'text-rose-600'
        );

        fileLabelText.classList.add(
            'text-blue-600'
        );


        const sizeInMb =
            (file.size / 1024 / 1024).toFixed(2);

        fileInfo.textContent =
            `Ukuran: ${sizeInMb} MB`;

        fileInfo.classList.remove('hidden');

    });


    /* =========================================================
       RESET FILE
    ========================================================= */

    function resetFileInput() {

        fileInput.value = '';

        fileLabelText.textContent =
            'Klik untuk memilih file baru';

        fileLabelText.classList.remove(
            'text-blue-600',
            'text-rose-600'
        );

        fileLabelText.classList.add(
            'text-slate-700'
        );

        fileInfo.textContent = '';

        fileInfo.classList.add('hidden');

    }


    /* =========================================================
       FORM SUBMIT
    ========================================================= */

    form.addEventListener('submit', function (event) {

        // Mencegah submit ganda
        if (isSubmitting) {

            event.preventDefault();

            return;
        }


        isSubmitting = true;

        submitButton.disabled = true;

        submitButton.innerHTML = `
            <svg
                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                ></circle>

                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4c0-1.1.1-2.1.4-3.1L4 12z"
                ></path>
            </svg>

            Memperbarui...
        `;

    });

});
</script>

@endpush

@endsection