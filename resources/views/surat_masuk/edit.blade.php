@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')

@php
    use Illuminate\Support\Carbon;

    /*
    |--------------------------------------------------------------------------
    | TANGGAL SURAT
    |--------------------------------------------------------------------------
    */

    $tanggalSurat = old('tanggal_surat');

    if ($tanggalSurat === null && $suratMasuk->tanggal_surat) {
        try {
            $tanggalSurat = Carbon::parse(
                $suratMasuk->tanggal_surat
            )->format('Y-m-d');
        } catch (\Throwable $e) {
            $tanggalSurat = substr(
                (string) $suratMasuk->tanggal_surat,
                0,
                10
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TANGGAL DITERIMA
    |--------------------------------------------------------------------------
    */

    $tanggalTerima = old('tanggal_terima');

    if ($tanggalTerima === null && $suratMasuk->tanggal_terima) {
        try {
            $tanggalTerima = Carbon::parse(
                $suratMasuk->tanggal_terima
            )->format('Y-m-d');
        } catch (\Throwable $e) {
            $tanggalTerima = substr(
                (string) $suratMasuk->tanggal_terima,
                0,
                10
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | KATEGORI
    |--------------------------------------------------------------------------
    */

    $selectedKategori = old(
        'kategori_surat_id',
        $suratMasuk->kategori_surat_id
    );

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    $currentStatus = strtolower(
        trim(
            (string) old(
                'status',
                $suratMasuk->status ?: 'baru'
            )
        )
    );

    $statusOptions = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'didisposisikan' => 'Didisposisikan',
        'selesai' => 'Selesai',
        'diarsipkan' => 'Diarsipkan',
    ];
@endphp


{{-- ===================================================================== --}}
{{-- HEADER --}}
{{-- ===================================================================== --}}

<div class="page-header">

    <div class="page-title-wrap">

        <div class="page-title">

            <div class="page-title-icon">
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
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-7.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 7.5-7.5z"
                    />
                </svg>
            </div>

            <h1>Edit Surat Masuk</h1>

        </div>

        <p class="page-subtitle">
            Perbarui informasi arsip surat masuk yang tersimpan di dalam sistem.
        </p>

    </div>


    <a
        href="{{ route('surat-masuk.index') }}"
        class="back-button"
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
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
            />
        </svg>

        Kembali
    </a>

</div>


{{-- ===================================================================== --}}
{{-- VALIDATION ERROR --}}
{{-- ===================================================================== --}}

@if($errors->any())

    <div class="validation-error">

        <div class="validation-error-icon">

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

        <div>

            <p class="validation-error-title">
                Data belum dapat diperbarui.
            </p>

            <ul class="validation-error-list">

                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach

            </ul>

        </div>

    </div>

@endif


{{-- ===================================================================== --}}
{{-- FORM --}}
{{-- ===================================================================== --}}

<form
    id="form-surat"
    action="{{ route('surat-masuk.update', $suratMasuk->id) }}"
    method="POST"
    enctype="multipart/form-data"
>

    @csrf
    @method('PUT')


    {{-- NOMOR AGENDA --}}
    <input
        type="hidden"
        name="nomor_agenda"
        value="{{ old('nomor_agenda', $suratMasuk->nomor_agenda) }}"
    >


    <div class="form-card">


        {{-- ================================================================= --}}
        {{-- AGENDA BAR --}}
        {{-- ================================================================= --}}

        <div class="agenda-bar">

            <div class="agenda-info">

                <div class="agenda-icon">
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
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 013-3h0a3 3 0 013 3"
                        />
                    </svg>
                </div>

                <span class="agenda-label">
                    Nomor Agenda Sistem
                </span>

                <span class="agenda-number">
                    {{ $suratMasuk->nomor_agenda }}
                </span>

            </div>

            <span class="agenda-note">
                Nomor agenda tidak diubah
            </span>

        </div>


        <div class="form-body">


            {{-- ============================================================= --}}
            {{-- INFORMASI SURAT --}}
            {{-- ============================================================= --}}

            <section class="form-section">

                <div class="section-header">

                    <div class="section-line section-line-blue"></div>

                    <div>

                        <h2 class="section-title">
                            Informasi Utama Surat
                        </h2>

                        <p class="section-description">
                            Perbarui identitas dan informasi utama surat masuk.
                        </p>

                    </div>

                </div>


                <div class="form-grid">


                    {{-- NOMOR SURAT --}}
                    <div class="form-group">

                        <label
                            for="nomor_surat"
                            class="form-label"
                        >
                            Nomor Surat
                            <span class="required">*</span>
                        </label>

                        <input
                            id="nomor_surat"
                            name="nomor_surat"
                            type="text"
                            required
                            autocomplete="off"
                            value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}"
                            placeholder="Contoh: 005/B/I/2026"
                            class="form-input @error('nomor_surat') input-error @enderror"
                        >

                        @error('nomor_surat')
                            <p class="error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- PENGIRIM --}}
                    <div class="form-group">

                        <label
                            for="pengirim"
                            class="form-label"
                        >
                            Instansi Pengirim
                            <span class="required">*</span>
                        </label>

                        <input
                            id="pengirim"
                            name="pengirim"
                            type="text"
                            required
                            autocomplete="organization"
                            value="{{ old('pengirim', $suratMasuk->pengirim) }}"
                            placeholder="Masukkan nama instansi pengirim"
                            class="form-input @error('pengirim') input-error @enderror"
                        >

                        @error('pengirim')
                            <p class="error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- TANGGAL SURAT --}}
                    <div class="form-group">

                        <label
                            for="tanggal_surat"
                            class="form-label"
                        >
                            Tanggal Surat
                            <span class="required">*</span>
                        </label>

                        <input
                            id="tanggal_surat"
                            name="tanggal_surat"
                            type="date"
                            required
                            value="{{ $tanggalSurat }}"
                            class="form-input @error('tanggal_surat') input-error @enderror"
                        >

                        @error('tanggal_surat')
                            <p class="error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- TANGGAL DITERIMA --}}
                    <div class="form-group">

                        <label
                            for="tanggal_terima"
                            class="form-label"
                        >
                            Tanggal Diterima
                            <span class="required">*</span>
                        </label>

                        <input
                            id="tanggal_terima"
                            name="tanggal_terima"
                            type="date"
                            required
                            value="{{ $tanggalTerima }}"
                            class="form-input @error('tanggal_terima') input-error @enderror"
                        >

                        @error('tanggal_terima')
                            <p class="error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- KATEGORI --}}
                    <div class="form-group">

                        <label
                            for="kategori_surat_id"
                            class="form-label"
                        >
                            Kategori Surat
                            <span class="required">*</span>
                        </label>

                        <select
                            id="kategori_surat_id"
                            name="kategori_surat_id"
                            required
                            class="form-select @error('kategori_surat_id') input-error @enderror"
                        >

                            <option
                                value=""
                                disabled
                                @selected(!$selectedKategori)
                            >
                                Pilih kategori surat
                            </option>

                            @foreach(($kategoris ?? collect()) as $kategori)

                                <option
                                    value="{{ $kategori->id }}"
                                    @selected(
                                        (string) $selectedKategori ===
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

                        @error('kategori_surat_id')
                            <p class="error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- STATUS --}}
                    <div class="form-group">

                        <label
                            for="status"
                            class="form-label"
                        >
                            Status Surat
                            <span class="required">*</span>
                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                            class="form-select @error('status') input-error @enderror"
                        >

                            @foreach($statusOptions as $value => $label)

                                <option
                                    value="{{ $value }}"
                                    @selected($currentStatus === $value)
                                >
                                    {{ $label }}
                                </option>

                            @endforeach

                        </select>

                        @error('status')
                            <p class="error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- PERIHAL --}}
                    <div class="form-group form-group-full">

                        <label
                            for="perihal"
                            class="form-label"
                        >
                            Perihal
                            <span class="required">*</span>
                        </label>

                        <textarea
                            id="perihal"
                            name="perihal"
                            rows="3"
                            required
                            placeholder="Tuliskan perihal surat"
                            class="form-textarea @error('perihal') input-error @enderror"
                        >{{ old('perihal', $suratMasuk->perihal) }}</textarea>

                        @error('perihal')
                            <p class="error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- RINGKASAN --}}
                    <div class="form-group form-group-full">

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
                            class="form-textarea @error('ringkasan') input-error @enderror"
                        >{{ old('ringkasan', $suratMasuk->ringkasan) }}</textarea>

                        @error('ringkasan')
                            <p class="error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>

            </section>


            {{-- ============================================================= --}}
            {{-- LAMPIRAN --}}
            {{-- ============================================================= --}}

            <section class="form-section">

                <div class="section-header">

                    <div class="section-line section-line-indigo"></div>

                    <div>

                        <h2 class="section-title">
                            Lampiran Dokumen & Arsip Fisik
                        </h2>

                        <p class="section-description">
                            Ganti dokumen melalui upload atau scan,
                            serta perbarui lokasi arsip fisik.
                        </p>

                    </div>

                </div>


                <div class="archive-grid">


                    {{-- ===================================================== --}}
                    {{-- DOKUMEN DIGITAL --}}
                    {{-- ===================================================== --}}

                    <div class="archive-card">

                        <div class="archive-header">

                            <div>

                                <h3 class="archive-title">
                                    Berkas Digital
                                </h3>

                                <p class="archive-description">
                                    Upload file baru atau scan menggunakan kamera.
                                </p>

                            </div>

                            <span class="optional-badge">
                                Opsional
                            </span>

                        </div>


                        {{-- INFO KOMPRESI --}}

                        <div class="mb-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2">

                            <p class="text-[10px] leading-relaxed text-blue-600">

                                Maksimal <strong>10 MB</strong>.

                                PDF disimpan tanpa kompresi.

                                JPG, JPEG, dan PNG akan diperkecil
                                serta dikompres otomatis di browser
                                sebelum dikirim ke server.

                            </p>

                        </div>


                        {{-- ================================================= --}}
                        {{-- MODE SELECTOR --}}
                        {{-- ================================================= --}}

                        <div class="mode-grid">

                            <button
                                type="button"
                                id="mode-upload-btn"
                                class="mode-button active"
                                aria-controls="upload-panel"
                                aria-selected="true"
                            >

                                <span class="mode-icon">

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
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                        />
                                    </svg>

                                </span>

                                <span>

                                    <span class="mode-title">
                                        Upload File
                                    </span>

                                    <span class="mode-subtitle">
                                        Pilih dari perangkat
                                    </span>

                                </span>

                            </button>


                            <button
                                type="button"
                                id="mode-scan-btn"
                                class="mode-button"
                                aria-controls="scan-panel"
                                aria-selected="false"
                            >

                                <span class="mode-icon">

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
                                            d="M4 7V5a1 1 0 011-1h2M17 4h2a1 1 0 011 1v2M20 17v2a1 1 0 01-1 1h-2M7 20H5a1 1 0 01-1-1v-2M7 12h10M7 9h10M7 15h6"
                                        />
                                    </svg>

                                </span>

                                <span>

                                    <span class="mode-title">
                                        Scan Dokumen
                                    </span>

                                    <span class="mode-subtitle">
                                        Gunakan kamera
                                    </span>

                                </span>

                            </button>

                        </div>


                        {{-- ================================================= --}}
                        {{-- FILE LAMA --}}
                        {{-- ================================================= --}}

                        @if($suratMasuk->lampiran_file)

                            <div class="current-file">

                                <div class="file-icon">

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
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.414l4.414 4.414A2 2 0 0118 8.414V19a2 2 0 01-2 2z"
                                        />
                                    </svg>

                                </div>

                                <div class="file-info">

                                    <p class="file-title">
                                        Dokumen saat ini
                                    </p>

                                    <p
                                        class="file-name"
                                        title="{{ basename($suratMasuk->lampiran_file) }}"
                                    >
                                        {{ basename($suratMasuk->lampiran_file) }}
                                    </p>

                                </div>


                                <a
                                    href="{{ route('surat-masuk.preview-lampiran', $suratMasuk->id) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="view-file"
                                >
                                    Lihat
                                </a>

                            </div>

                        @else

                            <div class="current-file">

                                <div class="file-icon file-icon-warning">

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
                                            d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3z"
                                        />
                                    </svg>

                                </div>

                                <div class="file-info">

                                    <p class="file-title">
                                        Belum ada dokumen
                                    </p>

                                    <p class="file-name">
                                        Upload atau scan dokumen baru.
                                    </p>

                                </div>

                            </div>

                        @endif


                        {{-- ================================================= --}}
                        {{-- UPLOAD PANEL --}}
                        {{-- ================================================= --}}

                        <div id="upload-panel">

                            <label
                                for="lampiran_file"
                                id="upload-box"
                                class="upload-box @error('lampiran_file') error @enderror"
                            >

                                <span class="upload-icon">

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
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                        />
                                    </svg>

                                </span>


                                <span
                                    id="file-label-text"
                                    class="upload-label"
                                >
                                    Pilih file baru
                                </span>


                                <span class="upload-help">
                                    PDF, JPG, JPEG, PNG
                                </span>


                                <span class="upload-size">
                                    Gambar otomatis dikompres
                                    • Maksimal 10 MB
                                </span>


                                <input
                                    type="file"
                                    id="lampiran_file"
                                    name="lampiran_file"
                                    accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                    class="sr-only"
                                >

                            </label>


                            {{-- FILE BARU --}}
                            <div
                                id="selected-file"
                                class="selected-file hidden"
                            >

                                <div class="file-icon">

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


                                <div class="selected-file-info">

                                    <div class="selected-file-title">
                                        File baru dipilih
                                    </div>

                                    <div
                                        id="selected-file-name"
                                        class="selected-file-name"
                                    ></div>

                                    <div
                                        id="selected-file-size"
                                        class="selected-file-size"
                                    ></div>

                                </div>


                                <button
                                    type="button"
                                    id="clear-file-btn"
                                    class="clear-file"
                                    title="Hapus file"
                                    aria-label="Hapus file"
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


                        {{-- ================================================= --}}
                        {{-- SCAN PANEL --}}
                        {{-- ================================================= --}}

                        <div
                            id="scan-panel"
                            class="scan-panel"
                            style="display: none;"
                        >

                            <div class="scan-wrapper">


                                <div class="scan-header">

                                    <div>

                                        <h4 class="scan-title">
                                            Scan Dokumen
                                        </h4>

                                        <p class="scan-description">
                                            Arahkan kamera ke dokumen lalu
                                            ambil gambar. Hasil scan akan
                                            dikompres otomatis.
                                        </p>

                                    </div>

                                    <span class="camera-badge">
                                        Kamera
                                    </span>

                                </div>


                                {{-- CAMERA --}}
                                <div class="camera-container">

                                    <video
                                        id="camera-video"
                                        autoplay
                                        playsinline
                                        muted
                                    ></video>


                                    <div
                                        id="camera-placeholder"
                                        class="camera-placeholder"
                                    >

                                        <svg
                                            class="w-10 h-10 text-slate-400"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M3 7h4l2-3h6l2 3h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z"
                                            />

                                            <circle
                                                cx="12"
                                                cy="13"
                                                r="3"
                                            />
                                        </svg>


                                        <p class="mt-2 text-sm font-semibold text-slate-400">
                                            Kamera belum aktif
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            Klik "Aktifkan Kamera".
                                        </p>

                                    </div>


                                    <div
                                        id="camera-error"
                                        class="camera-error hidden"
                                        role="alert"
                                    ></div>


                                    <div class="camera-frame"></div>

                                </div>


                                {{-- CAMERA ACTIONS --}}
                                <div class="scan-actions">

                                    <button
                                        type="button"
                                        id="start-camera-btn"
                                        class="scan-button scan-start"
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
                                                d="M3 7h4l2-3h6l2 3h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z"
                                            />
                                        </svg>

                                        Aktifkan Kamera

                                    </button>


                                    <button
                                        type="button"
                                        id="capture-btn"
                                        class="scan-button scan-capture"
                                        disabled
                                    >

                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="9"
                                            />

                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="3"
                                            />
                                        </svg>

                                        Ambil Gambar

                                    </button>


                                    <button
                                        type="button"
                                        id="stop-camera-btn"
                                        class="scan-button scan-stop"
                                        disabled
                                    >
                                        Matikan Kamera
                                    </button>

                                </div>


                                {{-- ================================================= --}}
                                {{-- HASIL SCAN --}}
                                {{-- ================================================= --}}

                                <div
                                    id="scan-result"
                                    class="scan-result hidden"
                                >

                                    <div class="scan-result-header">

                                        <div>

                                            <div class="scan-result-title">
                                                Dokumen hasil scan
                                            </div>

                                            <div class="scan-result-text">
                                                Hasil scan telah dikompres
                                                sebelum dikirim ke server.
                                                Dokumen ini akan menggantikan
                                                lampiran lama setelah disimpan.
                                            </div>

                                        </div>


                                        <button
                                            type="button"
                                            id="retake-btn"
                                            class="retake-button"
                                        >
                                            Scan Ulang
                                        </button>

                                    </div>


                                    <div class="scan-preview">

                                        <img
                                            id="scan-preview-image"
                                            src=""
                                            alt="Hasil scan dokumen"
                                        >

                                    </div>


                                    <div
                                        id="scan-compression-info"
                                        class="mt-2 text-center text-xs font-semibold text-emerald-600"
                                    ></div>

                                </div>


                                {{-- HASIL SCAN --}}
                                <input
                                    type="hidden"
                                    name="captured_image"
                                    id="captured_image"
                                    value="{{ old('captured_image') }}"
                                >

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- INFORMASI --}}
                        {{-- ================================================= --}}

                        <div class="mt-2 px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg">

                            <p class="text-xs leading-relaxed text-slate-500">

                                Jika tidak memilih file baru dan tidak
                                melakukan scan, dokumen lama akan tetap
                                dipertahankan.

                                PDF tidak dikompres, sedangkan JPG, JPEG,
                                dan PNG akan dikompres otomatis sebelum upload.

                            </p>

                        </div>


                        {{-- ERROR FILE --}}
                        @error('lampiran_file')
                            <p class="error-text">
                                {{ $message }}
                            </p>
                        @enderror


                        {{-- ERROR SCAN --}}
                        @error('captured_image')
                            <p class="error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- ===================================================== --}}
                    {{-- ARSIP FISIK --}}
                    {{-- ===================================================== --}}

                    <div class="archive-card">

                        <div class="archive-header">

                            <div>

                                <h3 class="archive-title">
                                    Lokasi Arsip Fisik
                                </h3>

                                <p class="archive-description">
                                    Perbarui lokasi penyimpanan arsip fisik.
                                </p>

                            </div>

                            <span class="optional-badge">
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


                            <div class="location-title">
                                Lokasi Penyimpanan
                            </div>


                            <p class="location-description">
                                Masukkan posisi rak, lemari, box,
                                atau map tempat arsip disimpan.
                            </p>


                            <div class="location-input-wrap">

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
                                    class="form-input @error('lokasi_arsip_fisik') input-error @enderror"
                                >

                                @error('lokasi_arsip_fisik')
                                    <p class="error-text">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            <div class="location-example">

                                Contoh:

                                <strong>
                                    Rak A-3 Box 12
                                </strong>

                                atau

                                <strong>
                                    Lemari B-2 Map 07
                                </strong>

                            </div>

                        </div>

                    </div>

                </div>

            </section>

        </div>


        {{-- ================================================================= --}}
        {{-- FOOTER --}}
        {{-- ================================================================= --}}

        <div class="form-footer">

            <a
                href="{{ route('surat-masuk.index') }}"
                class="action-button action-cancel"
            >
                Batal
            </a>


            <button
                type="submit"
                id="submit-btn"
                class="action-button action-save"
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


{{-- ======================================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ======================================================================= --}}

<script>
document.addEventListener('DOMContentLoaded', () => {

    'use strict';


    /*
    |--------------------------------------------------------------------------
    | ELEMENT
    |--------------------------------------------------------------------------
    */

    const form =
        document.getElementById('form-surat');

    const modeUploadBtn =
        document.getElementById('mode-upload-btn');

    const modeScanBtn =
        document.getElementById('mode-scan-btn');

    const uploadPanel =
        document.getElementById('upload-panel');

    const scanPanel =
        document.getElementById('scan-panel');

    const fileInput =
        document.getElementById('lampiran_file');

    const uploadBox =
        document.getElementById('upload-box');

    const selectedFile =
        document.getElementById('selected-file');

    const selectedFileName =
        document.getElementById('selected-file-name');

    const selectedFileSize =
        document.getElementById('selected-file-size');

    const clearFileBtn =
        document.getElementById('clear-file-btn');

    const cameraVideo =
        document.getElementById('camera-video');

    const cameraPlaceholder =
        document.getElementById('camera-placeholder');

    const cameraError =
        document.getElementById('camera-error');

    const startCameraBtn =
        document.getElementById('start-camera-btn');

    const captureBtn =
        document.getElementById('capture-btn');

    const stopCameraBtn =
        document.getElementById('stop-camera-btn');

    const retakeBtn =
        document.getElementById('retake-btn');

    const scanResult =
        document.getElementById('scan-result');

    const scanPreviewImage =
        document.getElementById('scan-preview-image');

    const scanCompressionInfo =
        document.getElementById('scan-compression-info');

    const capturedInput =
        document.getElementById('captured_image');

    const submitBtn =
        document.getElementById('submit-btn');

    const submitText =
        document.getElementById('submit-text');


    if (!form || !fileInput || !capturedInput) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI
    |--------------------------------------------------------------------------
    */

    const MAX_FILE_SIZE =
        10 * 1024 * 1024;

    const TARGET_IMAGE_SIZE =
        2.5 * 1024 * 1024;

    const MAX_COMPRESSED_IMAGE_SIZE =
        5 * 1024 * 1024;

    const MAX_IMAGE_WIDTH =
        2200;

    const MAX_IMAGE_HEIGHT =
        2200;

    const JPEG_QUALITIES = [
        0.82,
        0.76,
        0.70,
        0.64,
        0.58,
        0.52,
        0.46,
        0.40
    ];


    let cameraStream = null;
    let previewObjectUrl = null;


    /*
    |--------------------------------------------------------------------------
    | FORMAT FILE SIZE
    |--------------------------------------------------------------------------
    */

    function formatFileSize(bytes) {

        if (
            !Number.isFinite(bytes) ||
            bytes <= 0
        ) {
            return '0 KB';
        }


        if (bytes < 1024 * 1024) {

            return (
                (bytes / 1024).toFixed(1) +
                ' KB'
            );
        }


        return (
            (bytes / (1024 * 1024)).toFixed(2) +
            ' MB'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REDUCTION
    |--------------------------------------------------------------------------
    */

    function getReductionPercent(
        originalSize,
        compressedSize
    ) {

        if (
            !originalSize ||
            originalSize <= 0
        ) {
            return 0;
        }


        return Math.max(
            0,
            Math.round(
                (
                    1 -
                    compressedSize /
                    originalSize
                ) * 100
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CAMERA ERROR
    |--------------------------------------------------------------------------
    */

    function showCameraError(message) {

        if (!cameraError) {
            return;
        }

        cameraError.textContent =
            message;

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


    /*
    |--------------------------------------------------------------------------
    | UPLOAD MODE
    |--------------------------------------------------------------------------
    */

    function activateUploadMode() {

        modeUploadBtn?.classList.add(
            'active'
        );

        modeScanBtn?.classList.remove(
            'active'
        );


        modeUploadBtn?.setAttribute(
            'aria-selected',
            'true'
        );

        modeScanBtn?.setAttribute(
            'aria-selected',
            'false'
        );


        uploadPanel?.style.setProperty(
            'display',
            ''
        );

        scanPanel?.style.setProperty(
            'display',
            'none'
        );


        stopCamera();

        clearCameraError();

        clearScanResult();
    }


    /*
    |--------------------------------------------------------------------------
    | SCAN MODE
    |--------------------------------------------------------------------------
    */

    function activateScanMode() {

        modeUploadBtn?.classList.remove(
            'active'
        );

        modeScanBtn?.classList.add(
            'active'
        );


        modeUploadBtn?.setAttribute(
            'aria-selected',
            'false'
        );

        modeScanBtn?.setAttribute(
            'aria-selected',
            'true'
        );


        uploadPanel?.style.setProperty(
            'display',
            'none'
        );

        scanPanel?.style.setProperty(
            'display',
            ''
        );


        clearFileSelection();

        clearCameraError();
    }


    modeUploadBtn?.addEventListener(
        'click',
        activateUploadMode
    );

    modeScanBtn?.addEventListener(
        'click',
        activateScanMode
    );


    /*
    |--------------------------------------------------------------------------
    | FILE INPUT
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
                    MAX_FILE_SIZE
                ) {

                    clearFileSelection();

                    alert(
                        'Ukuran PDF terlalu besar.\n\n' +
                        'Maksimal ukuran PDF adalah 10 MB.'
                    );

                    return;
                }


                // PDF tidak menggunakan hasil scan.
                capturedInput.value = '';

                clearScanResult();


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

            const supportedImages = [
                'image/jpeg',
                'image/jpg',
                'image/png'
            ];


            if (
                !supportedImages.includes(
                    file.type
                )
            ) {

                clearFileSelection();

                alert(
                    'Format file tidak didukung.\n\n' +
                    'Gunakan PDF, JPG, JPEG, atau PNG.'
                );

                return;
            }


            if (
                file.size >
                MAX_FILE_SIZE
            ) {

                clearFileSelection();

                alert(
                    'Ukuran gambar terlalu besar.\n\n' +
                    'Maksimal ukuran gambar adalah 10 MB.'
                );

                return;
            }


            const originalSize =
                file.size;


            try {

                /*
                |--------------------------------------------------------------------------
                | KOMPRES
                |--------------------------------------------------------------------------
                */

                const compressedFile =
                    await compressImageFile(
                        file
                    );


                /*
                |--------------------------------------------------------------------------
                | JANGAN GUNAKAN HASIL KOMPRESI
                | JIKA LEBIH BESAR DARI FILE ASLI
                |--------------------------------------------------------------------------
                */

                const finalFile =
                    compressedFile.size <
                    originalSize
                        ? compressedFile
                        : file;


                const dataTransfer =
                    new DataTransfer();


                dataTransfer.items.add(
                    finalFile
                );


                fileInput.files =
                    dataTransfer.files;


                // Upload baru membatalkan scan.
                capturedInput.value = '';

                clearScanResult();


                const reduction =
                    getReductionPercent(
                        originalSize,
                        finalFile.size
                    );


                let note;


                if (
                    finalFile === file
                ) {

                    note =
                        `Tidak dikompres • ${formatFileSize(finalFile.size)}`;

                } else {

                    note =
                        `Dikompres ${reduction}% • ${formatFileSize(finalFile.size)}`;
                }


                showSelectedFile(
                    finalFile,
                    note
                );


            } catch (error) {

                console.error(
                    'Compression upload gagal:',
                    error
                );


                clearFileSelection();


                alert(
                    error?.message ||
                    'Gagal memproses gambar.'
                );
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | SHOW SELECTED FILE
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | CLEAR FILE
    |--------------------------------------------------------------------------
    */

    function clearFileSelection() {

        fileInput.value = '';

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
    | LOAD IMAGE
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


    /*
    |--------------------------------------------------------------------------
    | COMPRESS IMAGE FILE
    |--------------------------------------------------------------------------
    */

    async function compressImageFile(
        file
    ) {

        const image =
            await loadImageFromFile(
                file
            );


        return compressImageElement(
            image,
            file.name
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPRESS IMAGE ELEMENT
    |--------------------------------------------------------------------------
    */

    function compressImageElement(
        image,
        originalName
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


                /*
                |--------------------------------------------------------------------------
                | RESIZE
                |--------------------------------------------------------------------------
                */

                const scale =
                    Math.min(
                        MAX_IMAGE_WIDTH /
                            width,

                        MAX_IMAGE_HEIGHT /
                            height,

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


                /*
                |--------------------------------------------------------------------------
                | WHITE BACKGROUND
                |--------------------------------------------------------------------------
                */

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


                compressCanvasToTarget(
                    canvas,
                    originalName
                )
                    .then(resolve)
                    .catch(reject);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | COMPRESS CANVAS
    |--------------------------------------------------------------------------
    */

    function compressCanvasToTarget(
        canvas,
        originalName = 'lampiran.jpg'
    ) {

        return new Promise(
            (resolve, reject) => {

                let qualityIndex = 0;


                function attempt() {

                    if (
                        qualityIndex >=
                        JPEG_QUALITIES.length
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


                                if (
                                    blob.size >
                                    MAX_COMPRESSED_IMAGE_SIZE
                                ) {

                                    reject(
                                        new Error(
                                            'Gambar masih terlalu besar setelah dikompres. Silakan gunakan gambar dengan resolusi lebih rendah.'
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
                            0.36
                        );

                        return;
                    }


                    const quality =
                        JPEG_QUALITIES[
                            qualityIndex
                        ];


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
                                TARGET_IMAGE_SIZE
                            ) {

                                resolve(
                                    createCompressedFile(
                                        blob,
                                        originalName
                                    )
                                );

                                return;
                            }


                            qualityIndex++;

                            attempt();
                        },
                        'image/jpeg',
                        quality
                    );
                }


                attempt();
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE COMPRESSED FILE
    |--------------------------------------------------------------------------
    */

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


        return new File(
            [blob],
            `${baseName || 'lampiran'}_compressed.jpg`,
            {
                type: 'image/jpeg',
                lastModified: Date.now()
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CAMERA START
    |--------------------------------------------------------------------------
    */

    startCameraBtn?.addEventListener(
        'click',
        startCamera
    );


    async function startCamera() {

        clearCameraError();


        if (
            !navigator.mediaDevices?.getUserMedia
        ) {

            showCameraError(
                'Browser atau perangkat ini tidak mendukung akses kamera.'
            );

            return;
        }


        try {

            stopCameraTracksOnly();


            cameraStream =
                await navigator.mediaDevices.getUserMedia(
                    {
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
                    }
                );


            cameraVideo.srcObject =
                cameraStream;


            await cameraVideo.play();


            cameraPlaceholder?.classList.add(
                'hidden'
            );


            startCameraBtn.disabled =
                true;

            captureBtn.disabled =
                false;

            stopCameraBtn.disabled =
                false;


        } catch (error) {

            console.error(
                'Camera error:',
                error
            );


            showCameraError(
                'Kamera tidak dapat diakses. Pastikan izin kamera diberikan pada browser.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CAPTURE
    |--------------------------------------------------------------------------
    */

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

                let width =
                    cameraVideo.videoWidth;

                let height =
                    cameraVideo.videoHeight;


                /*
                |--------------------------------------------------------------------------
                | RESIZE
                |--------------------------------------------------------------------------
                */

                const scale =
                    Math.min(
                        MAX_IMAGE_WIDTH /
                            width,

                        MAX_IMAGE_HEIGHT /
                            height,

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
                    cameraVideo,
                    0,
                    0,
                    width,
                    height
                );


                /*
                |--------------------------------------------------------------------------
                | COMPRESS
                |--------------------------------------------------------------------------
                */

                const compressedFile =
                    await compressCanvasToTarget(
                        canvas,
                        'scan_dokumen.jpg'
                    );


                /*
                |--------------------------------------------------------------------------
                | DATA URL
                |--------------------------------------------------------------------------
                */

                capturedInput.value =
                    await fileToDataUrl(
                        compressedFile
                    );


                /*
                |--------------------------------------------------------------------------
                | PREVIEW
                |--------------------------------------------------------------------------
                */

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


                scanResult?.classList.remove(
                    'hidden'
                );


                if (scanCompressionInfo) {

                    scanCompressionInfo.textContent =
                        `Hasil scan: ${formatFileSize(compressedFile.size)} • sudah dikompres sebelum dikirim ke server`;
                }


                /*
                |--------------------------------------------------------------------------
                | JIKA SCAN DIPAKAI, UPLOAD FILE DIHAPUS
                |--------------------------------------------------------------------------
                */

                clearFileSelection();


                /*
                |--------------------------------------------------------------------------
                | STOP CAMERA
                |--------------------------------------------------------------------------
                */

                stopCameraTracksOnly();


                if (cameraVideo) {

                    cameraVideo.srcObject =
                        null;
                }


                captureBtn.disabled =
                    true;

                stopCameraBtn.disabled =
                    true;

                startCameraBtn.disabled =
                    false;


                cameraPlaceholder?.classList.remove(
                    'hidden'
                );


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


    /*
    |--------------------------------------------------------------------------
    | RETAKE
    |--------------------------------------------------------------------------
    */

    retakeBtn?.addEventListener(
        'click',
        async () => {

            clearScanResult();

            capturedInput.value = '';

            await startCamera();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLEAR SCAN
    |--------------------------------------------------------------------------
    */

    function clearScanResult() {

        capturedInput.value = '';


        scanResult?.classList.add(
            'hidden'
        );


        if (scanPreviewImage) {

            scanPreviewImage.src = '';
        }


        if (scanCompressionInfo) {

            scanCompressionInfo.textContent =
                '';
        }


        if (previewObjectUrl) {

            URL.revokeObjectURL(
                previewObjectUrl
            );

            previewObjectUrl = null;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | FILE TO DATA URL
    |--------------------------------------------------------------------------
    */

    function fileToDataUrl(file) {

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
                            'Gagal menyiapkan data scan.'
                        )
                    );
                };


                reader.readAsDataURL(
                    file
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STOP CAMERA
    |--------------------------------------------------------------------------
    */

    stopCameraBtn?.addEventListener(
        'click',
        stopCamera
    );


    function stopCamera() {

        stopCameraTracksOnly();


        if (cameraVideo) {

            cameraVideo.srcObject =
                null;
        }


        cameraPlaceholder?.classList.remove(
            'hidden'
        );


        if (captureBtn) {

            captureBtn.disabled =
                true;
        }


        if (stopCameraBtn) {

            stopCameraBtn.disabled =
                true;
        }


        if (startCameraBtn) {

            startCameraBtn.disabled =
                false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | STOP CAMERA TRACKS
    |--------------------------------------------------------------------------
    */

    function stopCameraTracksOnly() {

        if (!cameraStream) {
            return;
        }


        cameraStream
            .getTracks()
            .forEach(track => {
                track.stop();
            });


        cameraStream = null;
    }


    /*
    |--------------------------------------------------------------------------
    | SUBMIT
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        event => {

            /*
            |--------------------------------------------------------------------------
            | VALIDASI HASIL SCAN
            |--------------------------------------------------------------------------
            */

            if (
                capturedInput.value &&
                capturedInput.value.length >
                    7_000_000
            ) {

                event.preventDefault();


                alert(
                    'Hasil scan terlalu besar. Silakan scan ulang dengan resolusi lebih rendah.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | DISABLE SUBMIT
            |--------------------------------------------------------------------------
            */

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
    | CLEANUP
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'beforeunload',
        () => {

            stopCameraTracksOnly();


            if (previewObjectUrl) {

                URL.revokeObjectURL(
                    previewObjectUrl
                );

                previewObjectUrl = null;
            }
        }
    );

});
</script>

@endsection