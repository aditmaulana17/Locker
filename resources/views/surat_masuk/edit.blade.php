@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')
<div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-800">
                Edit Surat Masuk
            </h1>
            <p class="mt-0.5 text-sm text-slate-500">
                Perbarui informasi data arsip surat masuk yang tersimpan di dalam sistem.
            </p>
        </div>

        <a href="{{ route('surat-masuk.index') }}"
           class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 text-sm font-semibold text-slate-700 bg-white border-2 border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 transition">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    {{-- Form --}}
    <form method="POST"
          action="{{ route('surat-masuk.update', $suratMasuk->id) }}"
          enctype="multipart/form-data"
          id="form-surat">

        @csrf
        @method('PUT')

        <input type="hidden"
               name="nomor_agenda"
               value="{{ old('nomor_agenda', $suratMasuk->nomor_agenda) }}">

        <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-sm overflow-hidden">

            {{-- Nomor Agenda --}}
            <div class="px-4 sm:px-5 py-2.5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b-2 border-blue-200">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">

                    <div class="flex items-center min-w-0">
                        <div class="flex items-center justify-center w-8 h-8 mr-2.5 bg-blue-600 border-2 border-blue-600 rounded-lg text-white shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 013-3h0a3 3 0 013 3m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center gap-1 min-w-0">
                            <span class="text-sm font-medium text-blue-900">
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

            {{-- Main Content --}}
            <div class="p-4 sm:p-5">

                {{-- Informasi Utama --}}
                <section>
                    <div class="section-heading">
                        <div class="section-marker bg-blue-600"></div>
                        <div>
                            <h2 class="section-title">Informasi Utama Surat</h2>
                            <p class="section-description">
                                Perbarui identitas dan informasi utama surat masuk.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3">

                        {{-- Nomor Surat --}}
                        <div class="form-group">
                            <label class="form-label">
                                Nomor Surat <span class="text-rose-500">*</span>
                            </label>

                            <input type="text"
                                   name="nomor_surat"
                                   value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}"
                                   required
                                   placeholder="Contoh: 005/B/I/2026"
                                   class="form-control-custom @error('nomor_surat') form-error @enderror">

                            @error('nomor_surat')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pengirim --}}
                        <div class="form-group">
                            <label class="form-label">
                                Instansi Pengirim <span class="text-rose-500">*</span>
                            </label>

                            <input type="text"
                                   name="pengirim"
                                   value="{{ old('pengirim', $suratMasuk->pengirim) }}"
                                   required
                                   placeholder="Masukkan nama instansi pengirim..."
                                   class="form-control-custom @error('pengirim') form-error @enderror">

                            @error('pengirim')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal Surat --}}
                        <div class="form-group">
                            <label class="form-label">
                                Tanggal Surat <span class="text-rose-500">*</span>
                            </label>

                            <input type="date"
                                   name="tanggal_surat"
                                   value="{{ old('tanggal_surat', $suratMasuk->tanggal_surat) }}"
                                   required
                                   class="form-control-custom @error('tanggal_surat') form-error @enderror">

                            @error('tanggal_surat')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tanggal Diterima --}}
                        <div class="form-group">
                            <label class="form-label">
                                Tanggal Diterima <span class="text-rose-500">*</span>
                            </label>

                            <input type="date"
                                   name="tanggal_terima"
                                   value="{{ old('tanggal_terima', $suratMasuk->tanggal_terima) }}"
                                   required
                                   class="form-control-custom @error('tanggal_terima') form-error @enderror">

                            @error('tanggal_terima')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kategori --}}
                        <div class="form-group">
                            <label class="form-label">
                                Kategori Surat <span class="text-rose-500">*</span>
                            </label>

                            <select name="kategori_surat_id"
                                    required
                                    class="form-control-custom @error('kategori_surat_id') form-error @enderror">

                                <option value="" disabled>
                                    Pilih kategori surat
                                </option>

                                @foreach($kategoris as $k)
                                    <option value="{{ $k->id }}"
                                        {{ old('kategori_surat_id', $suratMasuk->kategori_surat_id) == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kategori }}
                                        @if(isset($k->sifat))
                                            ({{ ucfirst($k->sifat) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            @error('kategori_surat_id')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Status --}}
                        <div class="form-group">
                            <label class="form-label">
                                Status Surat <span class="text-rose-500">*</span>
                            </label>

                            <select name="status"
                                    required
                                    class="form-control-custom @error('status') form-error @enderror">

                                <option value="baru" {{ old('status', $suratMasuk->status) == 'baru' ? 'selected' : '' }}>
                                    Baru
                                </option>

                                <option value="diproses" {{ old('status', $suratMasuk->status) == 'diproses' ? 'selected' : '' }}>
                                    Diproses
                                </option>

                                <option value="didisposisikan" {{ old('status', $suratMasuk->status) == 'didisposisikan' ? 'selected' : '' }}>
                                    Didisposisikan
                                </option>

                                <option value="selesai" {{ old('status', $suratMasuk->status) == 'selesai' ? 'selected' : '' }}>
                                    Selesai
                                </option>

                                <option value="diarsipkan" {{ old('status', $suratMasuk->status) == 'diarsipkan' ? 'selected' : '' }}>
                                    Diarsipkan
                                </option>
                            </select>

                            @error('status')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Perihal --}}
                        <div class="md:col-span-2 form-group">
                            <label class="form-label">
                                Perihal / Isi Ringkas <span class="text-rose-500">*</span>
                            </label>

                            <textarea name="perihal"
                                      rows="2"
                                      required
                                      placeholder="Tuliskan perihal atau isi ringkas surat secara jelas..."
                                      class="form-control-custom form-textarea @error('perihal') form-error @enderror">{{ old('perihal', $suratMasuk->perihal) }}</textarea>

                            @error('perihal')
                                <p class="form-error-text">{{ $message }}</p>
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
                                Lampiran Dokumen & Arsip Fisik
                            </h2>
                            <p class="section-description">
                                Perbarui dokumen digital dan lokasi penyimpanan fisik.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-stretch">

                        {{-- Berkas Digital --}}
                        <div class="archive-card">

                            <div class="archive-card-header">
                                <div>
                                    <h3 class="archive-card-title">
                                        Berkas Digital <span class="text-rose-500">*</span>
                                    </h3>
                                    <p class="archive-card-description">
                                        Ganti dokumen atau lakukan scan baru.
                                    </p>
                                </div>

                                <span class="archive-card-badge required">
                                    Wajib
                                </span>
                            </div>

                            {{-- File Lama --}}
                            @if(!empty($suratMasuk->lampiran_file))
                                <div class="current-file">
                                    <div class="current-file-icon">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414A1 1 0 0118 8.414V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>

                                    <div class="min-w-0">
                                        <p class="text-xs font-semibold text-slate-700">
                                            Dokumen saat ini
                                        </p>
                                        <p class="mt-0.5 text-xs text-slate-400 truncate">
                                            {{ basename($suratMasuk->lampiran_file) }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            {{-- Mode --}}
                            <div class="mode-selector">
                                <button type="button"
                                        onclick="switchMode('upload')"
                                        id="btn-upload"
                                        class="mode-button mode-button-active">
                                    Upload File
                                </button>

                                <button type="button"
                                        onclick="switchMode('camera')"
                                        id="btn-camera"
                                        class="mode-button">
                                    Scan Kamera
                                </button>
                            </div>

                            {{-- Upload --}}
                            <div id="mode-upload" class="flex-1">
                                <label id="upload-box-label"
                                       class="upload-box @error('lampiran_file') upload-box-error @enderror">

                                    <div class="upload-icon">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                  stroke-linejoin="round"
                                                  stroke-width="2"
                                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                    </div>

                                    <span id="file-label-text"
                                          class="text-sm font-bold text-slate-700">
                                        Pilih file baru
                                    </span>

                                    <span class="mt-0.5 text-xs text-slate-400">
                                        PDF, JPG, JPEG, PNG · Maks. 10MB
                                    </span>

                                    <input type="file"
                                           name="lampiran_file"
                                           id="lampiran_file"
                                           accept=".pdf,.jpg,.jpeg,.png"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                           onchange="updateFileName(this)">
                                </label>
                            </div>

                            {{-- Kamera --}}
                            <div id="mode-camera" class="hidden flex-1">
                                <div id="camera-container" class="camera-container">
                                    <video id="video"
                                           autoplay
                                           playsinline
                                           class="w-full h-full object-cover"></video>

                                    <img id="image-preview"
                                         class="hidden absolute inset-0 w-full h-full object-contain bg-slate-950"
                                         alt="Preview Scan">

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
                                    ✓ Hasil scan berhasil diambil!
                                </div>
                            </div>

                            <div class="mt-1.5">
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

                            <div class="flex-1">
                                <div class="location-box">

                                    <div class="location-icon">
                                        <svg class="w-5 h-5"
                                             fill="none"
                                             stroke="currentColor"
                                             viewBox="0 0 24 24">
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
                                        <label class="form-label text-left">
                                            Detail Posisi Lemari / Box
                                        </label>

                                        <input type="text"
                                               name="lokasi_arsip_fisik"
                                               value="{{ old('lokasi_arsip_fisik', $suratMasuk->lokasi_arsip_fisik) }}"
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
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M5 13l4 4L19 7"/>
                    </svg>

                    Perbarui Surat Masuk
                </button>
            </div>

        </div>
    </form>
</div>

@push('scripts')
{{-- Script kamera / upload tetap gunakan script yang sudah Anda miliki --}}
@endpush

@endsection