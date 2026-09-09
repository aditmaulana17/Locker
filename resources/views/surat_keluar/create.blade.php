@extends('layouts.app')

@section('title', 'Catat Surat Keluar')

@section('content')
<div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-800">
                Catat Surat Keluar
            </h1>

            <p class="mt-0.5 text-sm text-slate-500">
                Isi formulir di bawah ini untuk menambahkan data arsip surat keluar baru.
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

    {{-- Form --}}
    <form
        method="POST"
        action="{{ route('surat-keluar.store') }}"
        enctype="multipart/form-data"
        id="form-surat"
    >
        @csrf

        <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-sm overflow-hidden">

            {{-- Main Content --}}
            <div class="p-4 sm:p-5">

                {{-- Informasi Utama --}}
                <section>
                    <div class="section-heading">
                        <div class="section-marker bg-blue-600"></div>

                        <div>
                            <h2 class="section-title">
                                Informasi Utama Surat
                            </h2>

                            <p class="section-description">
                                Lengkapi identitas dan informasi utama surat keluar.
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
                                value="{{ old('nomor_surat') }}"
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
                                value="{{ old('pengirim') }}"
                                required
                                placeholder="Contoh: PT Maju Takgentar"
                                autocomplete="off"
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
                                value="{{ old('tanggal_surat', date('Y-m-d')) }}"
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
                                value="{{ old('tanggal_keluar', date('Y-m-d')) }}"
                                required
                                class="form-control-custom @error('tanggal_keluar') form-error @enderror"
                            >

                            @error('tanggal_keluar')
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

                            <select
                                id="kategori_surat_id"
                                name="kategori_surat_id"
                                required
                                class="form-control-custom @error('kategori_surat_id') form-error @enderror"
                            >
                                <option
                                    value=""
                                    disabled
                                    {{ old('kategori_surat_id') ? '' : 'selected' }}
                                >
                                    Pilih kategori surat
                                </option>

                                @foreach($kategoris as $k)
                                    <option
                                        value="{{ $k->id }}"
                                        {{ old('kategori_surat_id') == $k->id ? 'selected' : '' }}
                                    >
                                        {{ $k->nama_kategori }}

                                        @if(isset($k->sifat) && $k->sifat)
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
                            <label
                                for="status"
                                class="form-label"
                            >
                                Status Surat
                                <span class="text-rose-500">*</span>
                            </label>

                            <select
                                id="status"
                                name="status"
                                required
                                class="form-control-custom @error('status') form-error @enderror"
                            >
                                <option
                                    value="draft"
                                    {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}
                                >
                                    Draft
                                </option>

                                <option
                                    value="diproses"
                                    {{ old('status') === 'diproses' ? 'selected' : '' }}
                                >
                                    Diproses
                                </option>

                                <option
                                    value="disetujui"
                                    {{ old('status') === 'disetujui' ? 'selected' : '' }}
                                >
                                    Disetujui
                                </option>

                                <option
                                    value="dikirim"
                                    {{ old('status') === 'dikirim' ? 'selected' : '' }}
                                >
                                    Dikirim
                                </option>

                                <option
                                    value="diarsipkan"
                                    {{ old('status') === 'diarsipkan' ? 'selected' : '' }}
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
                            >{{ old('perihal') }}</textarea>

                            @error('perihal')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                    </div>
                </section>

                {{-- Lampiran --}}
                <section class="mt-5">
                    <div class="section-heading">
                        <div class="section-marker bg-indigo-600"></div>

                        <div>
                            <h2 class="section-title">
                                Lampiran Berkas Digital
                            </h2>

                            <p class="section-description">
                                Upload dokumen atau gunakan kamera untuk mengambil scan.
                            </p>
                        </div>
                    </div>

                    <div class="archive-card">

                        <div class="archive-card-header">
                            <div>
                                <h3 class="archive-card-title">
                                    Dokumen Surat
                                </h3>

                                <p class="archive-card-description">
                                    Pilih salah satu metode untuk menambahkan lampiran.
                                </p>
                            </div>

                            <span class="archive-card-badge">
                                Opsional
                            </span>
                        </div>

                        {{-- Mode --}}
                        <div class="flex flex-wrap gap-2 mb-3">
                            <button
                                type="button"
                                id="mode-upload"
                                class="attachment-mode-button attachment-mode-active"
                            >
                                Upload File
                            </button>

                            <button
                                type="button"
                                id="mode-camera"
                                class="attachment-mode-button"
                            >
                                Gunakan Kamera
                            </button>
                        </div>

                        {{-- Upload Section --}}
                        <div id="upload-section">

                            <label
                                id="upload-box-label"
                                for="lampiran_file"
                                class="upload-box @error('lampiran_file') upload-box-error @enderror"
                            >
                                <div class="upload-icon">
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
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
                                    Klik untuk memilih file
                                </span>

                                <span class="mt-0.5 text-xs text-slate-400">
                                    PDF, JPG, JPEG, PNG · Maks. 15MB
                                </span>

                                <input
                                    type="file"
                                    name="lampiran_file"
                                    id="lampiran_file"
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
                        </div>

                        {{-- Camera Section --}}
                        <div
                            id="camera-section"
                            class="hidden"
                        >
                            <div class="camera-container">

                                <video
                                    id="camera-preview"
                                    autoplay
                                    playsinline
                                    muted
                                ></video>

                                <div
                                    id="camera-placeholder"
                                    class="camera-placeholder"
                                >
                                    <svg
                                        class="w-8 h-8 mb-2"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 7h4l2-2h6l2 2h4a1 1 0 011 1v11a1 1 0 01-1 1H3a1 1 0 01-1-1V8a1 1 0 011-1z"
                                        />

                                        <circle
                                            cx="12"
                                            cy="13"
                                            r="4"
                                            stroke-width="2"
                                        />
                                    </svg>

                                    <p class="text-sm font-semibold">
                                        Kamera belum aktif
                                    </p>

                                    <p class="text-xs mt-1">
                                        Klik tombol Aktifkan Kamera.
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 mt-2">

                                <button
                                    type="button"
                                    id="start-camera"
                                    class="camera-button camera-button-primary"
                                >
                                    Aktifkan Kamera
                                </button>

                                <button
                                    type="button"
                                    id="capture-camera"
                                    class="camera-button camera-button-success"
                                    disabled
                                >
                                    Ambil Foto
                                </button>

                                <button
                                    type="button"
                                    id="stop-camera"
                                    class="camera-button camera-button-secondary"
                                    disabled
                                >
                                    Matikan Kamera
                                </button>

                            </div>

                            <div
                                id="camera-result"
                                class="hidden mt-2"
                            >
                                <div class="camera-result-box">

                                    <div>
                                        <p class="text-xs font-bold text-slate-700">
                                            Hasil Scan
                                        </p>

                                        <p
                                            id="camera-result-text"
                                            class="text-xs text-emerald-600 mt-0.5"
                                        >
                                            Gambar siap disimpan.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        id="retake-camera"
                                        class="camera-button camera-button-secondary"
                                    >
                                        Ambil Ulang
                                    </button>
                                </div>
                            </div>

                            <p class="mt-2 text-xs text-slate-400">
                                Kamera menghasilkan JPG dan maksimal 15MB.
                            </p>

                            @error('captured_image')
                                <p class="form-error-text mt-1.5">
                                    {{ $message }}
                                </p>
                            @enderror

                            <input
                                type="hidden"
                                name="captured_image"
                                id="captured_image"
                                value="{{ old('captured_image') }}"
                            >
                        </div>

                    </div>
                </section>

                {{-- Validation Error --}}
                @if ($errors->any())
                    <div class="mt-4 p-3 bg-rose-50 border-2 border-rose-200 text-rose-700 rounded-lg text-xs">

                        <strong class="font-bold">
                            Terjadi Kesalahan Validasi:
                        </strong>

                        <ul class="mt-1 space-y-0.5 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>

                    </div>
                @endif

            </div>

            {{-- Footer --}}
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
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>

                    Simpan Surat Keluar
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

    .attachment-mode-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .45rem .7rem;
        border: 2px solid #cbd5e1;
        border-radius: .55rem;
        background: #fff;
        color: #475569;
        font-size: .7rem;
        font-weight: 700;
        transition: all .15s ease;
    }

    .attachment-mode-button:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }

    .attachment-mode-active {
        border-color: #2563eb;
        background: #eff6ff;
        color: #2563eb;
    }

    .upload-box {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 145px;
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
        border-radius: 9999px;
        border: 2px solid #dbeafe;
        background: #eff6ff;
        color: #2563eb;
    }

    .camera-container {
        position: relative;
        width: 100%;
        min-height: 280px;
        overflow: hidden;
        border: 2px solid #64748b;
        border-radius: .7rem;
        background: #0f172a;
    }

    .camera-container video {
        display: block;
        width: 100%;
        height: 100%;
        min-height: 280px;
        object-fit: cover;
        background: #0f172a;
    }

    .camera-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #cbd5e1;
        background: #0f172a;
    }

    .camera-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .45rem .7rem;
        border: 2px solid;
        border-radius: .55rem;
        font-size: .7rem;
        font-weight: 700;
        transition: all .15s ease;
    }

    .camera-button:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .camera-button-primary {
        color: #2563eb;
        border-color: #bfdbfe;
        background: #eff6ff;
    }

    .camera-button-success {
        color: #15803d;
        border-color: #bbf7d0;
        background: #f0fdf4;
    }

    .camera-button-secondary {
        color: #475569;
        border-color: #cbd5e1;
        background: #fff;
    }

    .camera-result-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .7rem;
        padding: .65rem;
        background: #fff;
        border: 2px solid #bbf7d0;
        border-radius: .6rem;
    }

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

        .camera-container,
        .camera-container video {
            min-height: 240px;
        }
    }
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('form-surat');

    const modeUpload = document.getElementById('mode-upload');
    const modeCamera = document.getElementById('mode-camera');

    const uploadSection = document.getElementById('upload-section');
    const cameraSection = document.getElementById('camera-section');

    const fileInput = document.getElementById('lampiran_file');
    const fileLabelText = document.getElementById('file-label-text');
    const fileInfo = document.getElementById('file-info');

    const video = document.getElementById('camera-preview');
    const cameraPlaceholder = document.getElementById('camera-placeholder');

    const startCameraButton = document.getElementById('start-camera');
    const captureCameraButton = document.getElementById('capture-camera');
    const stopCameraButton = document.getElementById('stop-camera');
    const retakeCameraButton = document.getElementById('retake-camera');

    const cameraResult = document.getElementById('camera-result');
    const cameraResultText = document.getElementById('camera-result-text');

    const capturedImageInput = document.getElementById('captured_image');
    const submitButton = document.getElementById('submit-btn');

    const MAX_FILE_SIZE = 15 * 1024 * 1024;

    let mediaStream = null;
    let isSubmitting = false;

    function resetFileInput() {
        fileInput.value = '';

        fileLabelText.textContent =
            'Klik untuk memilih file';

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

    function clearCameraResult() {
        capturedImageInput.value = '';
        cameraResult.classList.add('hidden');

        cameraResultText.textContent =
            'Gambar siap disimpan.';
    }

    function setUploadMode() {
        modeUpload.classList.add(
            'attachment-mode-active'
        );

        modeCamera.classList.remove(
            'attachment-mode-active'
        );

        uploadSection.classList.remove(
            'hidden'
        );

        cameraSection.classList.add(
            'hidden'
        );

        clearCameraResult();
        stopCamera();
    }

    function setCameraMode() {
        modeCamera.classList.add(
            'attachment-mode-active'
        );

        modeUpload.classList.remove(
            'attachment-mode-active'
        );

        cameraSection.classList.remove(
            'hidden'
        );

        uploadSection.classList.add(
            'hidden'
        );

        resetFileInput();
    }

    modeUpload.addEventListener(
        'click',
        setUploadMode
    );

    modeCamera.addEventListener(
        'click',
        setCameraMode
    );

    /*
    |--------------------------------------------------------------------------
    | FILE UPLOAD
    |--------------------------------------------------------------------------
    |
    | Penting:
    | Jangan memeriksa file.type di browser.
    | Validasi format dilakukan oleh Laravel Request.
    |
    */

    fileInput.addEventListener(
        'change',
        function () {
            if (
                !fileInput.files ||
                fileInput.files.length === 0
            ) {
                resetFileInput();
                return;
            }

            const file =
                fileInput.files[0];

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

                fileInfo.classList.remove(
                    'hidden'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | HANYA FILE ATAU KAMERA
            |--------------------------------------------------------------------------
            */

            capturedImageInput.value = '';

            cameraResult.classList.add(
                'hidden'
            );

            /*
            |--------------------------------------------------------------------------
            | TAMPILKAN FILE
            |--------------------------------------------------------------------------
            */

            fileLabelText.textContent =
                'Terpilih: ' + file.name;

            fileLabelText.classList.remove(
                'text-slate-700',
                'text-rose-600'
            );

            fileLabelText.classList.add(
                'text-blue-600'
            );

            const sizeInMb =
                (file.size / 1024 / 1024)
                    .toFixed(2);

            fileInfo.textContent =
                'Ukuran: ' +
                sizeInMb +
                ' MB';

            fileInfo.classList.remove(
                'hidden'
            );
        }
    );

    /*
    |--------------------------------------------------------------------------
    | CAMERA
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

            mediaStream =
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
                mediaStream;

            cameraPlaceholder.classList.add(
                'hidden'
            );

            startCameraButton.disabled =
                true;

            captureCameraButton.disabled =
                false;

            stopCameraButton.disabled =
                false;

        } catch (error) {
            console.error(
                'Camera error:',
                error
            );

            alert(
                'Kamera tidak dapat digunakan. Pastikan izin kamera sudah diberikan.'
            );
        }
    }

    function stopCamera() {
        if (mediaStream) {
            mediaStream
                .getTracks()
                .forEach(function (track) {
                    track.stop();
                });

            mediaStream = null;
        }

        video.srcObject = null;

        cameraPlaceholder.classList.remove(
            'hidden'
        );

        startCameraButton.disabled =
            false;

        captureCameraButton.disabled =
            true;

        stopCameraButton.disabled =
            true;
    }

    async function captureCamera() {
        if (!mediaStream) {
            alert(
                'Aktifkan kamera terlebih dahulu.'
            );

            return;
        }

        const track =
            mediaStream.getVideoTracks()[0];

        if (!track) {
            alert(
                'Kamera tidak tersedia.'
            );

            return;
        }

        try {
            /*
            |--------------------------------------------------------------------------
            | ImageCapture
            |--------------------------------------------------------------------------
            */

            if (
                typeof ImageCapture !== 'undefined'
            ) {
                const imageCapture =
                    new ImageCapture(track);

                const blob =
                    await imageCapture.takePhoto();

                if (!blob) {
                    throw new Error(
                        'Blob hasil kamera kosong.'
                    );
                }

                await processCameraBlob(
                    blob
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | FALLBACK VIDEO FRAME
            |--------------------------------------------------------------------------
            |
            | Untuk browser yang tidak memiliki ImageCapture.
            |
            */

            const canvas =
                document.createElement('canvas');

            const width =
                video.videoWidth || 1280;

            const height =
                video.videoHeight || 720;

            canvas.width = width;
            canvas.height = height;

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
                width,
                height
            );

            canvas.toBlob(
                async function (blob) {
                    if (!blob) {
                        alert(
                            'Gagal membuat gambar dari kamera.'
                        );

                        return;
                    }

                    try {
                        await processCameraBlob(
                            blob
                        );
                    } catch (error) {
                        console.error(error);

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

    async function processCameraBlob(blob) {
        if (
            blob.size > MAX_FILE_SIZE
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
            typeof dataUrl !== 'string'
            || !dataUrl.startsWith(
                'data:image/'
            )
        ) {
            throw new Error(
                'Data hasil kamera tidak valid.'
            );
        }

        capturedImageInput.value =
            dataUrl;

        resetFileInput();

        cameraResult.classList.remove(
            'hidden'
        );

        cameraResultText.textContent =
            'Gambar JPG siap disimpan.';

        /*
        |--------------------------------------------------------------------------
        | Kamera boleh dimatikan setelah foto diambil.
        |--------------------------------------------------------------------------
        */

        stopCamera();
    }

    function retakeCamera() {
        clearCameraResult();

        startCamera();
    }

    startCameraButton.addEventListener(
        'click',
        startCamera
    );

    captureCameraButton.addEventListener(
        'click',
        captureCamera
    );

    stopCameraButton.addEventListener(
        'click',
        stopCamera
    );

    retakeCameraButton.addEventListener(
        'click',
        retakeCamera
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
                fileInput.files &&
                fileInput.files.length > 0;

            const hasCamera =
                capturedImageInput.value.trim() !== '';

            /*
            |--------------------------------------------------------------------------
            | FILE + KAMERA
            |--------------------------------------------------------------------------
            */

            if (
                hasFile &&
                hasCamera
            ) {
                event.preventDefault();

                alert(
                    'Gunakan salah satu metode lampiran: Upload File atau Kamera.'
                );

                isSubmitting = false;
                submitButton.disabled = false;

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | SUBMIT
            |--------------------------------------------------------------------------
            */

            isSubmitting = true;

            submitButton.disabled =
                true;

            submitButton.innerHTML = `
                <svg
                    class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
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
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                    ></path>
                </svg>

                Menyimpan...
            `;

            stopCamera();
        }
    );

    /*
    |--------------------------------------------------------------------------
    | HENTIKAN KAMERA SAAT HALAMAN DITINGGALKAN
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'beforeunload',
        stopCamera
    );

    /*
    |--------------------------------------------------------------------------
    | STATE AWAL
    |--------------------------------------------------------------------------
    */

    setUploadMode();
});
</script>
@endpush

@endsection