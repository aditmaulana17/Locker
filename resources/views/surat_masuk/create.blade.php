@extends('layouts.app')

@section('title', 'Catat Surat Masuk')

@section('content')

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">

```
{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-800">Catat Surat Masuk</h1>
        <p class="mt-1 text-sm text-slate-500">Isi formulir di bawah ini untuk menambahkan data arsip surat masuk baru.</p>
    </div>

    <a href="{{ route('surat-masuk.index') }}"
       class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border-2 border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali
    </a>
</div>

{{-- Form --}}
<form method="POST" action="{{ route('surat-masuk.store') }}" enctype="multipart/form-data" id="form-surat">
    @csrf
    <input type="hidden" name="nomor_agenda" value="{{ $nomorAgenda }}">

    <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-sm overflow-hidden">

        {{-- Nomor Agenda --}}
        <div class="px-5 sm:px-6 py-3.5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-8 h-8 mr-3 bg-blue-100 border-2 border-blue-200 rounded-lg text-blue-600 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 013-3h0a3 3 0 013 3m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-1.5">
                        <span class="text-sm font-medium text-blue-900">Nomor Agenda Sistem:</span>
                        <strong class="inline-flex items-center w-fit px-3 py-1 bg-white border-2 border-blue-200 rounded-lg text-xs font-bold font-mono text-blue-700">
                            {{ $nomorAgenda }}
                        </strong>
                    </div>
                </div>
                <span class="text-xs font-medium text-blue-600">Nomor agenda dibuat otomatis oleh sistem</span>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="p-5 sm:p-6">

            {{-- Informasi Utama --}}
            <section>
                <div class="section-heading">
                    <div class="section-marker bg-blue-600"></div>
                    <div>
                        <h2 class="section-title">Informasi Utama Surat</h2>
                        <p class="section-description">Lengkapi identitas dan informasi utama surat masuk.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4">

                    {{-- Nomor Surat --}}
                    <div class="form-group">
                        <label class="form-label">Nomor Surat <span class="text-rose-500">*</span></label>
                        <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}" required
                               placeholder="Contoh: 005/B/I/2026"
                               class="form-control-custom @error('nomor_surat') form-error @enderror">
                        @error('nomor_surat')
                            <p class="form-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pengirim --}}
                    <div class="form-group">
                        <label class="form-label">Instansi Pengirim <span class="text-rose-500">*</span></label>
                        <input type="text" name="pengirim" value="{{ old('pengirim') }}" required
                               placeholder="Masukkan nama pengirim surat..."
                               class="form-control-custom @error('pengirim') form-error @enderror">
                        @error('pengirim')
                            <p class="form-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Surat --}}
                    <div class="form-group">
                        <label class="form-label">Tanggal Surat <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat') }}" required
                               class="form-control-custom @error('tanggal_surat') form-error @enderror">
                        @error('tanggal_surat')
                            <p class="form-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Diterima --}}
                    <div class="form-group">
                        <label class="form-label">Tanggal Diterima <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal_terima" value="{{ old('tanggal_terima', date('Y-m-d')) }}" required
                               class="form-control-custom @error('tanggal_terima') form-error @enderror">
                        @error('tanggal_terima')
                            <p class="form-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kategori --}}
                    <div class="form-group">
                        <label class="form-label">Kategori Surat <span class="text-rose-500">*</span></label>
                        <select name="kategori_surat_id" required class="form-control-custom @error('kategori_surat_id') form-error @enderror">
                            <option value="" disabled {{ old('kategori_surat_id') ? '' : 'selected' }}>Pilih kategori surat</option>
                            @foreach($kategoris as $k)
                                <option value="{{ $k->id }}" {{ old('kategori_surat_id') == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kategori }}@if(isset($k->sifat)) ({{ ucfirst($k->sifat) }})@endif
                                </option>
                            @endforeach
                        </select>
                        @error('kategori_surat_id')
                            <p class="form-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="form-group">
                        <label class="form-label">Status Surat <span class="text-rose-500">*</span></label>
                        <select name="status" required class="form-control-custom @error('status') form-error @enderror">
                            <option value="baru" {{ old('status', 'baru') == 'baru' ? 'selected' : '' }}>Baru</option>
                            <option value="diproses" {{ old('status') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                            <option value="didisposisikan" {{ old('status') == 'didisposisikan' ? 'selected' : '' }}>Didisposisikan</option>
                            <option value="selesai" {{ old('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="diarsipkan" {{ old('status') == 'diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
                        </select>
                        @error('status')
                            <p class="form-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Perihal --}}
                    <div class="md:col-span-2 form-group">
                        <label class="form-label">Perihal / Isi Ringkas <span class="text-rose-500">*</span></label>
                        <textarea name="perihal" rows="3" required
                                  placeholder="Tuliskan perihal atau isi ringkas surat secara jelas..."
                                  class="form-control-custom form-textarea @error('perihal') form-error @enderror">{{ old('perihal') }}</textarea>
                        @error('perihal')
                            <p class="form-error-text">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </section>

            {{-- Lampiran --}}
            <section class="mt-7">
                <div class="section-heading">
                    <div class="section-marker bg-indigo-600"></div>
                    <div>
                        <h2 class="section-title">Lampiran Dokumen & Arsip Fisik</h2>
                        <p class="section-description">Lengkapi dokumen digital dan lokasi penyimpanan fisik.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-stretch">

                    {{-- Berkas Digital --}}
                    <div class="archive-card">
                        <div class="archive-card-header">
                            <div>
                                <h3 class="archive-card-title">Berkas Digital <span class="text-rose-500">*</span></h3>
                                <p class="archive-card-description">Upload dokumen atau scan menggunakan kamera.</p>
                            </div>
                            <span class="archive-card-badge required">Wajib</span>
                        </div>

                        <div class="mode-selector">
                            <button type="button" onclick="switchMode('upload')" id="btn-upload" class="mode-button mode-button-active">Upload File</button>
                            <button type="button" onclick="switchMode('camera')" id="btn-camera" class="mode-button">Scan Kamera</button>
                        </div>

                        {{-- Upload --}}
                        <div id="mode-upload" class="flex-1">
                            <label id="upload-box-label" class="upload-box @error('lampiran_file') upload-box-error @enderror">
                                <div class="upload-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <span id="file-label-text" class="text-sm font-bold text-slate-700">Klik untuk memilih file</span>
                                <span class="mt-1 text-xs text-slate-400">PDF, JPG, JPEG, PNG · Maks. 10MB</span>
                                <input type="file" name="lampiran_file" id="lampiran_file"
                                       accept=".pdf,.jpg,.jpeg,.png" required
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                       onchange="updateFileName(this)">
                            </label>
                        </div>

                        {{-- Kamera --}}
                        <div id="mode-camera" class="hidden flex-1">
                            <div id="camera-container" class="camera-container">
                                <video id="video" autoplay playsinline class="w-full h-full object-cover"></video>
                                <img id="image-preview"
                                     class="hidden absolute inset-0 w-full h-full object-contain bg-slate-950"
                                     alt="Preview Scan">
                                <div id="camera-placeholder" class="camera-placeholder">Kamera belum aktif</div>
                            </div>

                            <div class="flex flex-wrap justify-center gap-2 mt-3">
                                <button type="button" id="start-cam-btn" onclick="startCamera()"
                                        class="camera-button bg-blue-600 hover:bg-blue-700 border-blue-600">
                                    Nyalakan Kamera
                                </button>
                                <button type="button" id="capture-btn" onclick="takeSnapshot()"
                                        class="hidden camera-button bg-emerald-600 hover:bg-emerald-700 border-emerald-600">
                                    Ambil Foto
                                </button>
                                <button type="button" id="retake-btn" onclick="retakeSnapshot()"
                                        class="hidden camera-button bg-amber-500 hover:bg-amber-600 border-amber-500">
                                    Foto Ulang
                                </button>
                                <button type="button" id="stop-cam-btn" onclick="stopCamera()"
                                        class="hidden camera-button bg-rose-600 hover:bg-rose-700 border-rose-600">
                                    Tutup Kamera
                                </button>
                            </div>

                            <input type="hidden" name="captured_image" id="captured_image" value="{{ old('captured_image') }}">

                            <div id="snapshot-preview"
                                 class="{{ old('captured_image') ? '' : 'hidden' }} mt-2 text-center text-xs font-semibold text-emerald-600">
                                ✓ Hasil scan berhasil diambil!
                            </div>
                        </div>

                        <div class="mt-2">
                            @error('lampiran_file')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                            @error('captured_image')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Lokasi Arsip Fisik --}}
                    <div class="archive-card">
                        <div class="archive-card-header">
                            <div>
                                <h3 class="archive-card-title">Lokasi Arsip Fisik <span class="text-rose-500">*</span></h3>
                                <p class="archive-card-description">Tentukan lokasi penyimpanan arsip fisik.</p>
                            </div>
                            <span class="archive-card-badge required">Wajib</span>
                        </div>

                        <div class="flex-1">
                            <div class="location-box">

                                <div class="location-icon">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>

                                <h4 class="text-sm font-bold text-slate-700">Lokasi Penyimpanan</h4>

                                <p class="mt-1 max-w-sm text-xs leading-relaxed text-slate-400">
                                    Masukkan posisi rak, lemari, box, atau map tempat arsip disimpan.
                                </p>

                                <div class="w-full max-w-md mt-4">
                                    <label class="form-label text-left">
                                        Detail Posisi Lemari / Box
                                        <span class="text-rose-500">*</span>
                                    </label>

                                    <input type="text"
                                           name="lokasi_arsip_fisik"
                                           value="{{ old('lokasi_arsip_fisik') }}"
                                           placeholder="Contoh: Rak A-3 Box 12"
                                           required
                                           class="form-control-custom @error('lokasi_arsip_fisik') form-error @enderror">

                                    @error('lokasi_arsip_fisik')
                                        <p class="form-error-text text-left">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="w-full max-w-md mt-3 px-3 py-2.5 bg-slate-50 border-2 border-slate-200 rounded-lg">
                                    <p class="text-xs leading-relaxed text-slate-500">
                                        Contoh:
                                        <span class="font-semibold text-slate-700">Rak A-3 Box 12</span>
                                        atau
                                        <span class="font-semibold text-slate-700">Lemari B-2 Map 07</span>.
                                    </p>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </div>

        {{-- Footer --}}
        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 px-5 sm:px-6 py-4 bg-slate-50 border-t-2 border-slate-300">
            <a href="{{ route('surat-masuk.index') }}" class="action-button action-button-secondary">Batal</a>

            <button type="submit" id="submit-btn" class="action-button action-button-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Surat Masuk
            </button>
        </div>

    </div>
</form>
```

</div>

{{-- CSS --}}

<style>
    .section-heading {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding-bottom: .65rem;
        margin-bottom: 1.15rem;
        border-bottom: 2px solid #cbd5e1;
    }

    .section-marker {
        width: .45rem;
        height: 1.4rem;
        border-radius: 9999px;
        flex-shrink: 0;
    }

    .section-title {
        color: #334155;
        font-size: .875rem;
        line-height: 1.2rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .section-description {
        margin-top: .1rem;
        color: #94a3b8;
        font-size: .72rem;
        line-height: 1rem;
    }

    .form-group {
        width: 100%;
    }

    .form-label {
        display: block;
        margin-bottom: .45rem;
        color: #334155;
        font-size: .72rem;
        line-height: 1rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .form-control-custom {
        width: 100%;
        min-height: 42px;
        padding: .55rem .8rem;
        background: #fff;
        color: #334155;
        border: 2px solid #94a3b8;
        border-radius: .7rem;
        outline: none;
        font-size: .85rem;
        line-height: 1.35;
        transition: border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
    }

    .form-control-custom:hover {
        border-color: #64748b;
    }

    .form-control-custom:focus {
        border-color: #2563eb;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
    }

    .form-control-custom::placeholder {
        color: #94a3b8;
    }

    select.form-control-custom {
        cursor: pointer;
    }

    .form-textarea {
        min-height: 96px;
        resize: vertical;
    }

    .form-error {
        border-color: #f43f5e !important;
        background: #fff1f2 !important;
    }

    .form-error-text {
        margin-top: .3rem;
        color: #e11d48;
        font-size: .72rem;
        line-height: 1rem;
        font-weight: 500;
    }

    .archive-card {
        display: flex;
        flex-direction: column;
        min-height: 320px;
        padding: 1rem;
        background: #f8fafc;
        border: 2px solid #94a3b8;
        border-radius: .9rem;
        transition: border-color .15s ease;
    }

    .archive-card:hover {
        border-color: #64748b;
    }

    .archive-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .8rem;
    }

    .archive-card-title {
        color: #334155;
        font-size: .875rem;
        line-height: 1.15rem;
        font-weight: 700;
    }

    .archive-card-description {
        margin-top: .1rem;
        color: #94a3b8;
        font-size: .72rem;
        line-height: 1rem;
    }

    .archive-card-badge {
        flex-shrink: 0;
        padding: .28rem .55rem;
        background: #fff;
        border: 2px solid #cbd5e1;
        border-radius: .5rem;
        color: #475569;
        font-size: .65rem;
        line-height: .9rem;
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
        padding: .2rem;
        margin-bottom: .75rem;
        background: #e2e8f0;
        border: 2px solid #94a3b8;
        border-radius: .7rem;
    }

    .mode-button {
        padding: .42rem .8rem;
        border-radius: .45rem;
        color: #475569;
        font-size: .7rem;
        line-height: 1rem;
        font-weight: 700;
        transition: all .15s ease;
    }

    .mode-button-active {
        background: #fff;
        color: #2563eb;
        border: 1px solid #cbd5e1;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .08);
    }

    .upload-box {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 210px;
        padding: 1.25rem;
        text-align: center;
        background: #fff;
        border: 2px dashed #64748b;
        border-radius: .7rem;
        cursor: pointer;
        transition: background-color .15s ease, border-color .15s ease;
    }

    .upload-box:hover,
    .location-box:hover {
        background: #f8fafc;
        border-color: #475569;
    }

    .upload-box-error {
        border-color: #f43f5e !important;
        background: #fff1f2 !important;
    }

    .upload-icon,
    .location-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.7rem;
        height: 2.7rem;
        margin-bottom: .65rem;
        border-radius: 9999px;
        border: 2px solid #dbeafe;
        background: #eff6ff;
        color: #2563eb;
    }

    .location-icon {
        border-color: #e0e7ff;
        background: #eef2ff;
        color: #4f46e5;
    }

    .location-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 210px;
        padding: 1.25rem;
        text-align: center;
        background: #fff;
        border: 2px dashed #64748b;
        border-radius: .7rem;
        transition: background-color .15s ease, border-color .15s ease;
    }

    .camera-container {
        position: relative;
        width: 100%;
        aspect-ratio: 4 / 3;
        overflow: hidden;
        background: #020617;
        border: 2px solid #64748b;
        border-radius: .7rem;
    }

    .camera-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: .72rem;
        font-weight: 500;
        background: rgba(2, 6, 23, .35);
    }

    .camera-button {
        padding: .55rem .85rem;
        color: #fff;
        border-width: 2px;
        border-radius: .65rem;
        font-size: .7rem;
        line-height: 1rem;
        font-weight: 700;
        transition: background-color .15s ease;
    }

    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: .6rem 1.15rem;
        border-width: 2px;
        border-radius: .7rem;
        font-size: .8rem;
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
        box-shadow: 0 5px 12px rgba(37, 99, 235, .18);
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

        .upload-box,
        .location-box {
            min-height: 190px;
        }

        .form-control-custom {
            min-height: 42px;
        }
    }
</style>

@push('scripts')

<script>
    let videoStream = null;

    function switchMode(mode) {
        stopCamera();

        const uploadMode = document.getElementById('mode-upload');
        const cameraMode = document.getElementById('mode-camera');
        const uploadButton = document.getElementById('btn-upload');
        const cameraButton = document.getElementById('btn-camera');

        if (mode === 'upload') {
            uploadMode.classList.remove('hidden');
            cameraMode.classList.add('hidden');
            uploadButton.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
            cameraButton.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
            cameraButton.classList.add('text-slate-600');
            return;
        }

        uploadMode.classList.add('hidden');
        cameraMode.classList.remove('hidden');
        cameraButton.classList.add('bg-white', 'text-blue-600', 'shadow-sm');
        cameraButton.classList.remove('text-slate-600');
        uploadButton.classList.remove('bg-white', 'text-blue-600', 'shadow-sm');
        uploadButton.classList.add('text-slate-600');
    }

    function updateFileName(input) {
        const textElement = document.getElementById('file-label-text');

        if (input.files && input.files.length > 0) {
            textElement.textContent = 'Terpilih: ' + input.files[0].name;
            textElement.classList.remove('text-slate-700');
            textElement.classList.add('text-blue-600');
        } else {
            textElement.textContent = 'Klik untuk memilih file';
            textElement.classList.remove('text-blue-600');
            textElement.classList.add('text-slate-700');
        }
    }

    async function startCamera() {
        const video = document.getElementById('video');
        const placeholder = document.getElementById('camera-placeholder');
        const imagePreview = document.getElementById('image-preview');

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Browser Anda tidak mendukung akses kamera.');
            return;
        }

        try {
            placeholder.classList.add('hidden');
            imagePreview.classList.add('hidden');
            video.classList.remove('hidden');

            videoStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            });

            video.srcObject = videoStream;

            document.getElementById('start-cam-btn').classList.add('hidden');
            document.getElementById('capture-btn').classList.remove('hidden');
            document.getElementById('stop-cam-btn').classList.remove('hidden');
            document.getElementById('retake-btn').classList.add('hidden');
        } catch (error) {
            console.error(error);
            placeholder.classList.remove('hidden');
            alert('Gagal mengakses kamera. Pastikan izin kamera telah diberikan pada browser.');
        }
    }

    function stopCamera() {
        if (videoStream) {
            videoStream.getTracks().forEach(track => track.stop());
            videoStream = null;
        }

        const video = document.getElementById('video');
        const capturedImage = document.getElementById('captured_image');
        const placeholder = document.getElementById('camera-placeholder');

        if (video) {
            video.srcObject = null;
        }

        if (placeholder && capturedImage && capturedImage.value === '') {
            placeholder.classList.remove('hidden');
        }

        const startButton = document.getElementById('start-cam-btn');
        const captureButton = document.getElementById('capture-btn');
        const stopButton = document.getElementById('stop-cam-btn');

        if (startButton) startButton.classList.remove('hidden');
        if (captureButton) captureButton.classList.add('hidden');
        if (stopButton) stopButton.classList.add('hidden');
    }

    async function takeSnapshot() {
        const video = document.getElementById('video');
        const imagePreview = document.getElementById('image-preview');
        const capturedImage = document.getElementById('captured_image');
        const container = document.getElementById('camera-container');

        if (!video.srcObject) {
            alert('Kamera belum aktif.');
            return;
        }

        try {
            const track = video.srcObject.getVideoTracks()[0];

            if (typeof ImageCapture !== 'undefined') {
                const imageCapture = new ImageCapture(track);
                const blob = await imageCapture.takePhoto();
                const reader = new FileReader();

                reader.onloadend = function () {
                    capturedImage.value = reader.result;
                    imagePreview.src = reader.result;
                    imagePreview.classList.remove('hidden');
                    video.classList.add('hidden');

                    document.getElementById('snapshot-preview').classList.remove('hidden');

                    container.classList.remove('border-slate-400');
                    container.classList.add('border-emerald-500');

                    stopCamera();
                    document.getElementById('start-cam-btn').classList.add('hidden');
                    document.getElementById('retake-btn').classList.remove('hidden');

                    // Input file tidak lagi diwajibkan jika hasil kamera sudah tersedia.
                    document.getElementById('lampiran_file').removeAttribute('required');
                };

                reader.readAsDataURL(blob);
                return;
            }

            alert('Browser ini tidak mendukung pengambilan foto langsung. Silakan gunakan Upload File.');
        } catch (error) {
            console.error(error);
            alert('Gagal mengambil foto dari kamera.');
        }
    }

    function retakeSnapshot() {
        const capturedImage = document.getElementById('captured_image');
        const imagePreview = document.getElementById('image-preview');
        const video = document.getElementById('video');
        const container = document.getElementById('camera-container');
        const snapshotPreview = document.getElementById('snapshot-preview');
        const fileInput = document.getElementById('lampiran_file');

        capturedImage.value = '';
        imagePreview.src = '';
        imagePreview.classList.add('hidden');
        video.classList.remove('hidden');
        snapshotPreview.classList.add('hidden');

        container.classList.remove('border-emerald-500');
        container.classList.add('border-slate-400');

        fileInput.setAttribute('required', 'required');
        startCamera();
    }

    const formSurat = document.getElementById('form-surat');

    if (formSurat) {
        formSurat.addEventListener('submit', function () {
            stopCamera();

            const fileInput = document.getElementById('lampiran_file');
            const capturedImage = document.getElementById('captured_image');
            const submitButton = document.getElementById('submit-btn');

            if (capturedImage && capturedImage.value !== '') {
                fileInput.removeAttribute('required');
            }

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
        });
    }

    window.addEventListener('beforeunload', function () {
        stopCamera();
    });
</script>

@endpush

@endsection
