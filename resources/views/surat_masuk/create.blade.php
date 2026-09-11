@extends('layouts.app')

@section('title', 'Catat Surat Masuk')

@section('content')
@php
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

    $inputClass = 'block w-full rounded-md border-2 border-slate-400 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-100';

    $errorInputClass = 'border-rose-400 focus:border-rose-500 focus:ring-rose-100';
@endphp

<div class="mx-auto w-full max-w-5xl px-3 sm:px-4 lg:px-5">

    {{-- ERROR --}}
    @if($formErrors->any())
        <div class="mb-3 rounded-lg border-2 border-rose-300 bg-rose-50 px-3 py-2.5 shadow-sm">
            <div class="flex items-start gap-2">

                <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md border border-rose-200 bg-rose-100 text-rose-600">
                    <svg
                        class="h-3.5 w-3.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z"
                        />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">

                    <p class="text-xs font-bold text-rose-700">
                        Data belum dapat disimpan.
                    </p>

                    <ul class="mt-0.5 space-y-0.5 text-[10px] leading-relaxed text-rose-600">
                        @foreach($formErrors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>

                </div>
            </div>
        </div>
    @endif


    {{-- FORM --}}
    <form
        id="form-surat"
        method="POST"
        action="{{ route('surat-masuk.store') }}"
        enctype="multipart/form-data"
    >

        @csrf

        <input
            type="hidden"
            name="nomor_agenda"
            value="{{ old('nomor_agenda', $nomorAgenda ?? '') }}"
        >


        <div class="overflow-hidden rounded-lg border-2 border-slate-400 bg-white shadow-sm">

            {{-- =====================================================
                 AGENDA
            ====================================================== --}}

            <div class="border-b-2 border-blue-300 bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3">

                <div class="flex items-center justify-between gap-3">

                    <div class="flex items-center gap-2.5">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border-2 border-blue-300 bg-white text-blue-600 shadow-sm">

                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 01-3-3h0a3 3 0 013 3m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                />
                            </svg>

                        </div>

                        <div>

                            <p class="text-[9px] font-bold uppercase tracking-wider text-blue-600">
                                Nomor Agenda Sistem
                            </p>

                            <p class="font-mono text-sm font-bold text-blue-900">
                                {{ $nomorAgenda ?? '-' }}
                            </p>

                        </div>

                    </div>

                    <div class="hidden rounded-md border border-blue-300 bg-white/80 px-3 py-1.5 text-right sm:block">

                        <p class="text-[8px] font-semibold uppercase tracking-wide text-blue-500">
                            Sistem
                        </p>

                        <p class="text-[9px] font-medium text-blue-700">
                            Dibuat otomatis
                        </p>

                    </div>

                </div>

            </div>


            {{-- =====================================================
                 BODY
            ====================================================== --}}

            <div class="p-4 sm:p-5">


                {{-- =================================================
                     INFORMASI UTAMA
                ================================================== --}}

                <section>

                    <div class="mb-3 flex items-start gap-2.5 border-b-2 border-slate-300 pb-2.5">

                        <div class="mt-0.5 h-7 w-1 shrink-0 rounded-full bg-blue-600"></div>

                        <div>

                            <h2 class="text-sm font-bold text-slate-800">
                                Informasi Utama Surat
                            </h2>

                            <p class="text-[10px] text-slate-500">
                                Lengkapi identitas dan informasi utama surat masuk.
                            </p>

                        </div>

                    </div>


                    <div class="overflow-hidden rounded-md border-2 border-slate-400">

                        <div class="grid grid-cols-1 md:grid-cols-2">


                            {{-- NOMOR SURAT --}}

                            <div class="border-b-2 border-slate-300 p-3 md:border-r-2">

                                <label
                                    for="nomor_surat"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
                                >
                                    Nomor Surat
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    id="nomor_surat"
                                    name="nomor_surat"
                                    type="text"
                                    value="{{ old('nomor_surat') }}"
                                    placeholder="Contoh: 005/B/I/2026"
                                    autocomplete="off"
                                    required
                                    class="{{ $inputClass }} {{ $formErrors->has('nomor_surat') ? $errorInputClass : '' }}"
                                >

                                @if($formErrors->has('nomor_surat'))

                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $formErrors->first('nomor_surat') }}
                                    </p>

                                @endif

                            </div>


                            {{-- PENGIRIM --}}

                            <div class="border-b-2 border-slate-300 p-3">

                                <label
                                    for="pengirim"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
                                >
                                    Instansi Pengirim
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    id="pengirim"
                                    name="pengirim"
                                    type="text"
                                    value="{{ old('pengirim') }}"
                                    placeholder="Masukkan nama instansi pengirim..."
                                    autocomplete="organization"
                                    required
                                    class="{{ $inputClass }} {{ $formErrors->has('pengirim') ? $errorInputClass : '' }}"
                                >

                                @if($formErrors->has('pengirim'))

                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $formErrors->first('pengirim') }}
                                    </p>

                                @endif

                            </div>


                            {{-- TANGGAL SURAT --}}

                            <div class="border-b-2 border-slate-300 p-3 md:border-r-2">

                                <label
                                    for="tanggal_surat"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
                                >
                                    Tanggal Surat
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    id="tanggal_surat"
                                    name="tanggal_surat"
                                    type="date"
                                    value="{{ old('tanggal_surat') }}"
                                    required
                                    class="{{ $inputClass }} {{ $formErrors->has('tanggal_surat') ? $errorInputClass : '' }}"
                                >

                                @if($formErrors->has('tanggal_surat'))

                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $formErrors->first('tanggal_surat') }}
                                    </p>

                                @endif

                            </div>


                            {{-- TANGGAL DITERIMA --}}

                            <div class="border-b-2 border-slate-300 p-3">

                                <label
                                    for="tanggal_terima"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
                                >
                                    Tanggal Diterima
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    id="tanggal_terima"
                                    name="tanggal_terima"
                                    type="date"
                                    value="{{ old('tanggal_terima', now()->format('Y-m-d')) }}"
                                    required
                                    class="{{ $inputClass }} {{ $formErrors->has('tanggal_terima') ? $errorInputClass : '' }}"
                                >

                                @if($formErrors->has('tanggal_terima'))

                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $formErrors->first('tanggal_terima') }}
                                    </p>

                                @endif

                            </div>


                            {{-- KATEGORI --}}

                            <div class="border-b-2 border-slate-300 p-3 md:border-r-2">

                                <label
                                    for="kategori_surat_id"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
                                >
                                    Kategori Surat
                                    <span class="text-rose-500">*</span>
                                </label>

                                <select
                                    id="kategori_surat_id"
                                    name="kategori_surat_id"
                                    required
                                    class="{{ $inputClass }} {{ $formErrors->has('kategori_surat_id') ? $errorInputClass : '' }}"
                                >

                                    <option value="">
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

                                @if($formErrors->has('kategori_surat_id'))

                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $formErrors->first('kategori_surat_id') }}
                                    </p>

                                @endif

                            </div>


                            {{-- STATUS --}}

                            <div class="border-b-2 border-slate-300 p-3">

                                <label
                                    for="status"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
                                >
                                    Status Surat
                                    <span class="text-rose-500">*</span>
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    required
                                    class="{{ $inputClass }} {{ $formErrors->has('status') ? $errorInputClass : '' }}"
                                >

                                    @foreach([
                                        'baru' => 'Baru',
                                        'diproses' => 'Diproses',
                                        'didisposisikan' => 'Didisposisikan',
                                        'selesai' => 'Selesai',
                                        'diarsipkan' => 'Diarsipkan'
                                    ] as $value => $label)

                                        <option
                                            value="{{ $value }}"
                                            @selected(
                                                old('status', 'baru') ===
                                                $value
                                            )
                                        >
                                            {{ $label }}
                                        </option>

                                    @endforeach

                                </select>

                                @if($formErrors->has('status'))

                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $formErrors->first('status') }}
                                    </p>

                                @endif

                            </div>


                            {{-- PERIHAL --}}

                            <div class="border-b-2 border-slate-300 p-3 md:col-span-2">

                                <label
                                    for="perihal"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
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
                                    class="{{ $inputClass }} resize-y"
                                >{{ old('perihal') }}</textarea>

                                @if($formErrors->has('perihal'))

                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $formErrors->first('perihal') }}
                                    </p>

                                @endif

                            </div>

                        </div>

                    </div>

                </section>


                <div class="my-4 border-t-2 border-slate-300"></div>


                {{-- =================================================
                     LAMPIRAN
                ================================================== --}}

                <section>

                    <div class="mb-3 flex items-start gap-2.5 border-b-2 border-slate-300 pb-2.5">

                        <div class="mt-0.5 h-7 w-1 shrink-0 rounded-full bg-indigo-600"></div>

                        <div>

                            <h2 class="text-sm font-bold text-slate-800">
                                Lampiran Dokumen & Arsip Fisik
                            </h2>

                            <p class="text-[10px] text-slate-500">
                                Upload dokumen atau scan menggunakan kamera.
                            </p>

                        </div>

                    </div>


                    <div class="mx-auto grid w-full max-w-4xl grid-cols-1 gap-3.5 lg:grid-cols-2">


                        {{-- =================================================
                             BERKAS DIGITAL
                        ================================================== --}}

                        <div class="overflow-hidden rounded-md border-2 border-slate-400 bg-white shadow-sm">

                            <div class="border-b-2 border-blue-300 bg-blue-50 px-3 py-2.5">

                                <div class="flex items-center justify-between gap-2">

                                    <div class="flex min-w-0 items-center gap-2">

                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-blue-300 bg-white text-blue-600">

                                            <svg
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M7 3h7l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z"
                                                />

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M14 3v5h5"
                                                />
                                            </svg>

                                        </div>

                                        <div>

                                            <h3 class="text-[11px] font-bold text-slate-800">
                                                Berkas Digital
                                            </h3>

                                            <p class="text-[9px] text-slate-500">
                                                PDF, JPG, JPEG, PNG • Maksimal 10 MB
                                            </p>

                                        </div>

                                    </div>

                                    <span class="shrink-0 rounded-full border border-rose-200 bg-rose-100 px-1.5 py-0.5 text-[8px] font-bold uppercase text-rose-600">
                                        Wajib
                                    </span>

                                </div>

                            </div>


                            <div class="p-3">


                                {{-- INFO --}}

                                <div class="mb-2.5 flex items-start gap-1.5 rounded-md border border-blue-200 bg-blue-50 px-2.5 py-2">

                                    <svg
                                        class="mt-0.5 h-3 w-3 shrink-0 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000-18z"
                                        />
                                    </svg>

                                    <div class="text-[9px] leading-relaxed">

                                        <p class="font-bold text-blue-700">
                                            Maksimal 10 MB
                                        </p>

                                        <p class="text-blue-600">
                                            PDF tetap PDF.
                                            JPG/JPEG/PNG akan dikompres
                                            menjadi JPG sebelum dikirim.
                                        </p>

                                    </div>

                                </div>


                                {{-- MODE --}}

                                <div class="mb-2.5 grid grid-cols-2 gap-1.5">

                                    <button
                                        type="button"
                                        id="btn-upload"
                                        aria-selected="true"
                                        class="flex items-center justify-center gap-1 rounded-md border-2 border-blue-600 bg-blue-600 px-2.5 py-1.5 text-[9px] font-bold text-white shadow-sm transition hover:bg-blue-700"
                                    >

                                        <svg
                                            class="h-3 w-3"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
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
                                        aria-selected="false"
                                        class="flex items-center justify-center gap-1 rounded-md border-2 border-slate-400 bg-white px-2.5 py-1.5 text-[9px] font-bold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50"
                                    >

                                        <svg
                                            class="h-3 w-3"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
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


                                {{-- UPLOAD PANEL --}}

                                <div id="upload-panel">

                                    <label
                                        id="upload-box"
                                        for="lampiran_file"
                                        class="flex min-h-[115px] cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed border-slate-400 bg-slate-50 px-3 py-4 text-center transition hover:border-blue-400 hover:bg-blue-50"
                                    >

                                        <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-md border border-blue-200 bg-blue-100 text-blue-600">

                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l3 3m-3-3v12"
                                                />
                                            </svg>

                                        </div>


                                        <span
                                            id="upload-title"
                                            class="text-[10px] font-bold text-slate-700"
                                        >
                                            Klik untuk memilih file
                                        </span>


                                        <span class="mt-0.5 text-[9px] text-slate-500">
                                            PDF, JPG, JPEG, PNG
                                        </span>


                                        <span class="mt-1 rounded-full bg-blue-100 px-1.5 py-0.5 text-[8px] font-medium text-blue-600">
                                            Maksimal 10 MB
                                        </span>


                                        <input
                                            id="lampiran_file"
                                            name="lampiran_file"
                                            type="file"
                                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                            class="sr-only"
                                        >

                                    </label>


                                    {{-- FILE TERPILIH --}}

                                    <div
                                        id="selected-file"
                                        class="mt-2 hidden rounded-md border-2 border-emerald-300 bg-emerald-50 p-2"
                                    >

                                        <div class="flex items-center gap-1.5">

                                            <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-emerald-100 text-emerald-600">

                                                <svg
                                                    class="h-3 w-3"
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

                                            </div>


                                            <div class="min-w-0 flex-1">

                                                <p class="text-[9px] font-bold text-emerald-700">
                                                    File siap diproses
                                                </p>

                                                <p
                                                    id="selected-file-name"
                                                    class="truncate text-[9px] text-emerald-600"
                                                ></p>

                                                <p
                                                    id="selected-file-size"
                                                    class="text-[8px] text-emerald-500"
                                                ></p>

                                            </div>


                                            <button
                                                type="button"
                                                id="clear-file-btn"
                                                class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-emerald-200 bg-white text-emerald-600 transition hover:bg-emerald-100"
                                                aria-label="Hapus file"
                                            >

                                                <svg
                                                    class="h-3 w-3"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
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


                                    {{-- STATUS COMPRESSION --}}

                                    <div
                                        id="file-compression-status"
                                        class="mt-2 hidden rounded-md border border-blue-200 bg-blue-50 px-2 py-1.5 text-[9px] leading-relaxed text-blue-700"
                                    ></div>

                                </div>


                                {{-- CAMERA PANEL --}}

                                <div
                                    id="camera-panel"
                                    class="hidden"
                                >

                                    <div class="overflow-hidden rounded-md border-2 border-slate-500 bg-slate-900">

                                        <div class="relative aspect-[4/3] w-full">

                                            <video
                                                id="video"
                                                autoplay
                                                muted
                                                playsinline
                                                class="h-full w-full object-cover"
                                            ></video>


                                            <img
                                                id="image-preview"
                                                src=""
                                                alt="Preview hasil scan"
                                                hidden
                                                class="absolute inset-0 h-full w-full bg-slate-900 object-contain"
                                            >


                                            <div
                                                id="camera-placeholder"
                                                class="absolute inset-0 flex items-center justify-center bg-slate-900 text-slate-400"
                                            >

                                                <div class="px-3 text-center">

                                                    <svg
                                                        class="mx-auto mb-1.5 h-7 w-7"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24"
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


                                                    <p
                                                        id="camera-placeholder-text"
                                                        class="text-[9px] font-medium"
                                                    >
                                                        Kamera belum aktif
                                                    </p>

                                                </div>

                                            </div>


                                            <div
                                                id="camera-error"
                                                class="absolute inset-x-1.5 bottom-1.5 hidden rounded-md bg-rose-600/95 px-2 py-1 text-center text-[9px] font-medium text-white"
                                                role="alert"
                                            ></div>

                                        </div>

                                    </div>


                                    {{-- CAMERA BUTTON --}}

                                    <div class="mt-2 flex flex-wrap justify-center gap-1.5">

                                        <button
                                            type="button"
                                            id="start-cam-btn"
                                            class="rounded-md border-2 border-blue-600 bg-blue-600 px-2.5 py-1.5 text-[9px] font-bold text-white transition hover:bg-blue-700"
                                        >
                                            Nyalakan Kamera
                                        </button>


                                        <button
                                            type="button"
                                            id="capture-btn"
                                            class="hidden rounded-md border-2 border-emerald-600 bg-emerald-600 px-2.5 py-1.5 text-[9px] font-bold text-white transition hover:bg-emerald-700"
                                        >
                                            Ambil Foto
                                        </button>


                                        <button
                                            type="button"
                                            id="retake-btn"
                                            class="hidden rounded-md border-2 border-amber-500 bg-amber-500 px-2.5 py-1.5 text-[9px] font-bold text-white transition hover:bg-amber-600"
                                        >
                                            Foto Ulang
                                        </button>


                                        <button
                                            type="button"
                                            id="stop-cam-btn"
                                            class="hidden rounded-md border-2 border-rose-600 bg-rose-600 px-2.5 py-1.5 text-[9px] font-bold text-white transition hover:bg-rose-700"
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
                                        class="{{ old('captured_image') ? '' : 'hidden' }} mt-2 rounded-md border-2 border-emerald-300 bg-emerald-50 px-2 py-1.5 text-center text-[9px] font-bold text-emerald-600"
                                    >
                                        ✓ Hasil scan siap disimpan.
                                    </div>

                                </div>


                                {{-- ERROR FILE --}}

                                @if($formErrors->has('lampiran_file'))

                                    <p class="mt-1.5 text-[10px] font-medium text-rose-600">
                                        {{ $formErrors->first('lampiran_file') }}
                                    </p>

                                @endif


                                @if($formErrors->has('captured_image'))

                                    <p class="mt-1.5 text-[10px] font-medium text-rose-600">
                                        {{ $formErrors->first('captured_image') }}
                                    </p>

                                @endif

                            </div>

                        </div>


                        {{-- =================================================
                             ARSIP FISIK
                        ================================================== --}}

                        <div class="overflow-hidden rounded-md border-2 border-slate-400 bg-white shadow-sm">

                            <div class="border-b-2 border-indigo-300 bg-indigo-50 px-3 py-2.5">

                                <div class="flex items-center justify-between gap-2">

                                    <div class="flex min-w-0 items-center gap-2">

                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-indigo-300 bg-white text-indigo-600">

                                            <svg
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                                />
                                            </svg>

                                        </div>


                                        <div>

                                            <h3 class="text-[11px] font-bold text-slate-800">
                                                Lokasi Arsip Fisik
                                            </h3>

                                            <p class="text-[9px] text-slate-500">
                                                Lokasi penyimpanan dokumen fisik
                                            </p>

                                        </div>

                                    </div>


                                    <span class="shrink-0 rounded-full border border-slate-300 bg-white px-1.5 py-0.5 text-[8px] font-bold uppercase text-slate-500">
                                        Opsional
                                    </span>

                                </div>

                            </div>


                            <div class="flex min-h-[230px] flex-col p-3">

                                <div class="flex flex-1 flex-col justify-center rounded-md border-2 border-dashed border-slate-400 bg-slate-50 p-3">

                                    <div class="mx-auto w-full max-w-xs text-center">

                                        <div class="mx-auto mb-2.5 flex h-10 w-10 items-center justify-center rounded-md border-2 border-indigo-200 bg-indigo-100 text-indigo-600">

                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                                />
                                            </svg>

                                        </div>


                                        <h4 class="text-[11px] font-bold text-slate-700">
                                            Lokasi Penyimpanan
                                        </h4>


                                        <p class="mx-auto mt-0.5 max-w-xs text-[9px] leading-relaxed text-slate-500">
                                            Masukkan posisi rak, lemari, box,
                                            atau map tempat arsip fisik disimpan.
                                        </p>


                                        <div class="mt-3 text-left">

                                            <label
                                                for="lokasi_arsip_fisik"
                                                class="mb-1.5 block text-[10px] font-bold text-slate-700"
                                            >
                                                Detail Posisi Lemari / Box
                                            </label>


                                            <input
                                                id="lokasi_arsip_fisik"
                                                name="lokasi_arsip_fisik"
                                                type="text"
                                                value="{{ old('lokasi_arsip_fisik') }}"
                                                placeholder="Contoh: Rak A-3 Box 12"
                                                class="{{ $inputClass }} {{ $formErrors->has('lokasi_arsip_fisik') ? $errorInputClass : '' }}"
                                            >


                                            @if($formErrors->has('lokasi_arsip_fisik'))

                                                <p class="mt-1 text-[10px] font-medium text-rose-600">
                                                    {{ $formErrors->first('lokasi_arsip_fisik') }}
                                                </p>

                                            @endif

                                        </div>


                                        <div class="mt-2.5 rounded-md border-2 border-slate-300 bg-white px-2.5 py-2 text-left">

                                            <div class="flex items-start gap-1.5">

                                                <svg
                                                    class="mt-0.5 h-3 w-3 shrink-0 text-indigo-500"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"
                                                    />
                                                </svg>


                                                <p class="text-[9px] leading-relaxed text-slate-500">

                                                    <span class="font-bold text-slate-700">
                                                        Contoh:
                                                    </span>

                                                    Rak A-3 Box 12 atau
                                                    Lemari B-2 Map 07.

                                                </p>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </section>

            </div>


            {{-- =================================================
                 FOOTER
            ================================================== --}}

            <div class="flex flex-col gap-2 border-t-2 border-slate-400 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">

                <a
                    href="{{ route('surat-masuk.index') }}"
                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border-2 border-slate-400 bg-white px-3 py-1.5 text-[10px] font-bold text-slate-700 transition hover:border-slate-500 hover:bg-slate-100 sm:w-auto"
                >

                    <svg
                        class="h-3.5 w-3.5"
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

                    Kembali ke Surat Masuk

                </a>


                <button
                    id="submit-btn"
                    type="submit"
                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border-2 border-blue-600 bg-blue-600 px-3 py-1.5 text-[9px] font-bold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                >

                    <svg
                        id="submit-icon"
                        class="h-3 w-3"
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


                    <svg
                        id="submit-loading"
                        class="hidden h-3 w-3 animate-spin"
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
                        />

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
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


<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    /* ============================================================
       ELEMENT
    ============================================================ */

    const form =
        document.getElementById('form-surat');

    const btnUpload =
        document.getElementById('btn-upload');

    const btnCamera =
        document.getElementById('btn-camera');

    const uploadPanel =
        document.getElementById('upload-panel');

    const cameraPanel =
        document.getElementById('camera-panel');

    const fileInput =
        document.getElementById('lampiran_file');

    const uploadBox =
        document.getElementById('upload-box');

    const uploadTitle =
        document.getElementById('upload-title');

    const selectedFile =
        document.getElementById('selected-file');

    const selectedFileName =
        document.getElementById('selected-file-name');

    const selectedFileSize =
        document.getElementById('selected-file-size');

    const clearFileBtn =
        document.getElementById('clear-file-btn');

    const compressionStatus =
        document.getElementById(
            'file-compression-status'
        );

    const video =
        document.getElementById('video');

    const imagePreview =
        document.getElementById('image-preview');

    const capturedImage =
        document.getElementById('captured_image');

    const startCamBtn =
        document.getElementById('start-cam-btn');

    const captureBtn =
        document.getElementById('capture-btn');

    const retakeBtn =
        document.getElementById('retake-btn');

    const stopCamBtn =
        document.getElementById('stop-cam-btn');

    const cameraPlaceholder =
        document.getElementById(
            'camera-placeholder'
        );

    const cameraPlaceholderText =
        document.getElementById(
            'camera-placeholder-text'
        );

    const cameraError =
        document.getElementById(
            'camera-error'
        );

    const snapshotPreview =
        document.getElementById(
            'snapshot-preview'
        );

    const submitBtn =
        document.getElementById(
            'submit-btn'
        );

    const submitIcon =
        document.getElementById(
            'submit-icon'
        );

    const submitLoading =
        document.getElementById(
            'submit-loading'
        );

    const submitText =
        document.getElementById(
            'submit-text'
        );


    if (
        !form ||
        !fileInput ||
        !capturedImage
    ) {
        return;
    }


    /* ============================================================
       CONFIGURATION
    ============================================================ */

    const MAX_FILE_SIZE =
        10 * 1024 * 1024;

    /*
     * Target browser compression.
     */
    const IMAGE_TARGET_SIZE =
        2.5 * 1024 * 1024;

    /*
     * Maksimal hasil kompresi browser.
     */
    const IMAGE_MAX_SIZE =
        5 * 1024 * 1024;

    /*
     * Maksimal sisi gambar.
     */
    const IMAGE_MAX_DIMENSION =
        2200;

    /*
     * Jangan membuat gambar terlalu kecil.
     */
    const IMAGE_MIN_DIMENSION =
        1000;

    /*
     * Kualitas JPEG bertahap.
     */
    const JPEG_QUALITIES = [
        0.86,
        0.82,
        0.78,
        0.74,
        0.70,
        0.66,
        0.62,
        0.58,
        0.54,
        0.50,
        0.46,
        0.42
    ];

    /*
     * Kamera.
     */
    const CAMERA_MAX_WIDTH =
        1600;

    const CAMERA_MAX_HEIGHT =
        1600;

    const CAMERA_TARGET_SIZE =
        2.5 * 1024 * 1024;

    const CAMERA_MAX_DATA_SIZE =
        7 * 1024 * 1024;


    let cameraStream =
        null;

    let submitting =
        false;


    /* ============================================================
       UTILITY
    ============================================================ */

    function formatFileSize(bytes) {

        if (
            !Number.isFinite(bytes) ||
            bytes <= 0
        ) {
            return '0 KB';
        }

        if (
            bytes <
            1024 * 1024
        ) {
            return (
                (bytes / 1024).toFixed(1) +
                ' KB'
            );
        }

        return (
            (
                bytes /
                (1024 * 1024)
            ).toFixed(2) +
            ' MB'
        );
    }


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
                ) *
                100
            )
        );
    }


    function getExtension(file) {

        return String(
            file?.name || ''
        )
            .split('.')
            .pop()
            .toLowerCase();
    }


    /* ============================================================
       CAMERA ERROR
    ============================================================ */

    function showCameraError(
        message
    ) {

        if (!cameraError) {
            return;
        }

        cameraError.textContent =
            message;

        cameraError.classList.remove(
            'hidden'
        );
    }


    function hideCameraError() {

        if (!cameraError) {
            return;
        }

        cameraError.textContent =
            '';

        cameraError.classList.add(
            'hidden'
        );
    }


    /* ============================================================
       MODE
    ============================================================ */

    function setModeButton(
        button,
        active
    ) {

        if (active) {

            button.classList.remove(
                'border-slate-400',
                'bg-white',
                'text-slate-700'
            );

            button.classList.add(
                'border-blue-600',
                'bg-blue-600',
                'text-white'
            );

            button.setAttribute(
                'aria-selected',
                'true'
            );

        } else {

            button.classList.remove(
                'border-blue-600',
                'bg-blue-600',
                'text-white'
            );

            button.classList.add(
                'border-slate-400',
                'bg-white',
                'text-slate-700'
            );

            button.setAttribute(
                'aria-selected',
                'false'
            );
        }
    }


    function setUploadMode() {

        stopCamera();

        uploadPanel.classList.remove(
            'hidden'
        );

        cameraPanel.classList.add(
            'hidden'
        );

        setModeButton(
            btnUpload,
            true
        );

        setModeButton(
            btnCamera,
            false
        );

        hideCameraError();
    }


    function setCameraMode() {

        uploadPanel.classList.add(
            'hidden'
        );

        cameraPanel.classList.remove(
            'hidden'
        );

        setModeButton(
            btnUpload,
            false
        );

        setModeButton(
            btnCamera,
            true
        );

        clearSelectedFile();

        hideCameraError();
    }


    btnUpload.addEventListener(
        'click',
        setUploadMode
    );

    btnCamera.addEventListener(
        'click',
        setCameraMode
    );


    /* ============================================================
       FILE DISPLAY
    ============================================================ */

    function showSelectedFile(
        file,
        note
    ) {

        selectedFile.classList.remove(
            'hidden'
        );

        selectedFileName.textContent =
            file.name;

        selectedFileSize.textContent =
            formatFileSize(
                file.size
            ) +
            (
                note
                    ? ' • ' + note
                    : ''
            );

        uploadBox.classList.add(
            'border-emerald-400',
            'bg-emerald-50'
        );

        uploadTitle.textContent =
            'File berhasil dipilih';
    }


    function clearSelectedFile() {

        fileInput.value =
            '';

        selectedFile.classList.add(
            'hidden'
        );

        selectedFileName.textContent =
            '';

        selectedFileSize.textContent =
            '';

        compressionStatus.classList.add(
            'hidden'
        );

        compressionStatus.textContent =
            '';

        uploadBox.classList.remove(
            'border-emerald-400',
            'bg-emerald-50'
        );

        uploadTitle.textContent =
            'Klik untuk memilih file';
    }


    clearFileBtn.addEventListener(
        'click',
        clearSelectedFile
    );


    /* ============================================================
       LOAD IMAGE
    ============================================================ */

    function loadImage(
        file
    ) {

        return new Promise(
            function (
                resolve,
                reject
            ) {

                const objectUrl =
                    URL.createObjectURL(
                        file
                    );

                const image =
                    new Image();

                image.onload =
                    function () {

                        URL.revokeObjectURL(
                            objectUrl
                        );

                        resolve(image);
                    };

                image.onerror =
                    function () {

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


    /* ============================================================
       DIMENSION
    ============================================================ */

    function calculateDimensions(
        width,
        height,
        maxDimension
    ) {

        if (
            width <= maxDimension &&
            height <= maxDimension
        ) {

            return {
                width,
                height
            };
        }

        const scale =
            Math.min(
                maxDimension / width,
                maxDimension / height
            );

        return {
            width: Math.max(
                1,
                Math.round(
                    width * scale
                )
            ),

            height: Math.max(
                1,
                Math.round(
                    height * scale
                )
            )
        };
    }


    function buildDimensionList(
        width,
        height
    ) {

        const dimensions =
            [
                IMAGE_MAX_DIMENSION,
                2000,
                1800,
                1600,
                1400,
                1200,
                IMAGE_MIN_DIMENSION
            ];

        const result =
            [];

        dimensions.forEach(
            function (
                maxDimension
            ) {

                const size =
                    calculateDimensions(
                        width,
                        height,
                        maxDimension
                    );

                if (
                    size.width <
                        IMAGE_MIN_DIMENSION &&
                    size.height <
                        IMAGE_MIN_DIMENSION
                ) {
                    return;
                }

                const duplicate =
                    result.some(
                        function (
                            existing
                        ) {

                            return (
                                existing.width ===
                                    size.width &&
                                existing.height ===
                                    size.height
                            );
                        }
                    );

                if (!duplicate) {
                    result.push(size);
                }
            }
        );

        return result;
    }


    /* ============================================================
       CANVAS -> FILE
    ============================================================ */

    function canvasToFile(
        image,
        width,
        height,
        quality,
        originalName
    ) {

        return new Promise(
            function (
                resolve,
                reject
            ) {

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

                canvas.toBlob(
                    function (
                        blob
                    ) {

                        if (!blob) {

                            reject(
                                new Error(
                                    'Browser gagal membuat file hasil kompresi.'
                                )
                            );

                            return;
                        }

                        const baseName =
                            String(
                                originalName ||
                                'lampiran'
                            )
                                .replace(
                                    /\.[^/.]+$/,
                                    ''
                                )
                                .replace(
                                    /[^a-zA-Z0-9_-]/g,
                                    '_'
                                );

                        const fileName =
                            (
                                baseName ||
                                'lampiran'
                            ) +
                            '_compressed.jpg';

                        resolve(
                            new File(
                                [
                                    blob
                                ],
                                fileName,
                                {
                                    type:
                                        'image/jpeg',
                                    lastModified:
                                        Date.now()
                                }
                            )
                        );

                    },
                    'image/jpeg',
                    quality
                );
            }
        );
    }


    /* ============================================================
       COMPRESS IMAGE
    ============================================================ */

    async function compressImageFile(
        file
    ) {

        const image =
            await loadImage(
                file
            );

        const width =
            image.naturalWidth ||
            image.width;

        const height =
            image.naturalHeight ||
            image.height;

        if (
            width <= 0 ||
            height <= 0
        ) {

            throw new Error(
                'Dimensi gambar tidak valid.'
            );
        }

        const dimensionList =
            buildDimensionList(
                width,
                height
            );

        let smallest =
            null;


        /*
        |--------------------------------------------------------------------------
        | Coba beberapa resolusi dan kualitas.
        |--------------------------------------------------------------------------
        */

        for (
            const dimension
            of dimensionList
        ) {

            for (
                const quality
                of JPEG_QUALITIES
            ) {

                const compressed =
                    await canvasToFile(
                        image,
                        dimension.width,
                        dimension.height,
                        quality,
                        file.name
                    );

                if (
                    !smallest ||
                    compressed.size <
                        smallest.size
                ) {

                    smallest =
                        compressed;
                }

                /*
                |--------------------------------------------------------------------------
                | Target 2.5 MB tercapai.
                |--------------------------------------------------------------------------
                */

                if (
                    compressed.size <=
                    IMAGE_TARGET_SIZE
                ) {

                    return compressed;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Masih <= 5 MB masih diterima.
        |--------------------------------------------------------------------------
        */

        if (
            smallest &&
            smallest.size <=
                IMAGE_MAX_SIZE
        ) {

            return smallest;
        }


        throw new Error(
            'Gambar masih terlalu besar setelah kompresi. ' +
            'Silakan gunakan gambar dengan resolusi lebih rendah.'
        );
    }


    /* ============================================================
       FILE CHANGE
    ============================================================ */

    fileInput.addEventListener(
        'change',
        async function () {

            const file =
                fileInput.files?.[0];

            if (!file) {
                return;
            }

            const extension =
                getExtension(
                    file
                );

            hideCameraError();

            if (
                ![
                    'pdf',
                    'jpg',
                    'jpeg',
                    'png'
                ].includes(
                    extension
                )
            ) {

                clearSelectedFile();

                alert(
                    'Format file tidak didukung.\n\n' +
                    'Gunakan PDF, JPG, JPEG, atau PNG.'
                );

                return;
            }

            if (
                file.size <= 0
            ) {

                clearSelectedFile();

                alert(
                    'File kosong atau tidak valid.'
                );

                return;
            }

            if (
                file.size >
                MAX_FILE_SIZE
            ) {

                clearSelectedFile();

                alert(
                    'Ukuran file asli terlalu besar.\n\n' +
                    'Maksimal 10 MB.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | PDF
            |--------------------------------------------------------------------------
            */

            if (
                extension === 'pdf'
            ) {

                capturedImage.value =
                    '';

                clearCapturedPreview();

                showSelectedFile(
                    file,
                    'PDF • tanpa kompresi'
                );

                compressionStatus.textContent =
                    '✓ PDF tetap PDF dan tidak dikompres.';

                compressionStatus.classList.remove(
                    'hidden'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | IMAGE
            |--------------------------------------------------------------------------
            */

            compressionStatus.textContent =
                'Memproses gambar dan melakukan kompresi...';

            compressionStatus.classList.remove(
                'hidden'
            );

            try {

                const originalSize =
                    file.size;

                const compressed =
                    await compressImageFile(
                        file
                    );

                let finalFile =
                    compressed;

                /*
                |--------------------------------------------------------------------------
                | Jika hasil kompresi malah lebih besar,
                | gunakan file asli.
                |--------------------------------------------------------------------------
                */

                if (
                    compressed.size >=
                    originalSize
                ) {

                    finalFile =
                        file;
                }


                /*
                |--------------------------------------------------------------------------
                | Tetap pastikan maksimal 10 MB.
                |--------------------------------------------------------------------------
                */

                if (
                    finalFile.size >
                    MAX_FILE_SIZE
                ) {

                    clearSelectedFile();

                    alert(
                        'Gambar masih melebihi batas 10 MB setelah kompresi.\n\n' +
                        'Silakan pilih gambar dengan resolusi lebih rendah.'
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Replace file input dengan hasil kompresi.
                |--------------------------------------------------------------------------
                */

                const dataTransfer =
                    new DataTransfer();

                dataTransfer.items.add(
                    finalFile
                );

                fileInput.files =
                    dataTransfer.files;


                /*
                |--------------------------------------------------------------------------
                | Hapus sumber kamera.
                |--------------------------------------------------------------------------
                */

                capturedImage.value =
                    '';

                clearCapturedPreview();


                const reduction =
                    getReductionPercent(
                        originalSize,
                        finalFile.size
                    );


                if (
                    finalFile === file
                ) {

                    showSelectedFile(
                        finalFile,
                        'Gambar asli digunakan • ' +
                        formatFileSize(
                            finalFile.size
                        )
                    );

                    compressionStatus.textContent =
                        '✓ Kompresi tidak membuat file lebih kecil, sehingga file asli digunakan.';

                } else {

                    showSelectedFile(
                        finalFile,
                        'Dikompres ' +
                        reduction +
                        '% • ' +
                        formatFileSize(
                            originalSize
                        ) +
                        ' → ' +
                        formatFileSize(
                            finalFile.size
                        )
                    );

                    compressionStatus.textContent =
                        '✓ Gambar berhasil dikompres menjadi JPG sebelum dikirim ke server.';

                }

                compressionStatus.classList.remove(
                    'hidden'
                );

            } catch (error) {

                console.error(
                    'Image compression error:',
                    error
                );

                clearSelectedFile();

                alert(
                    error?.message ||
                    'Gagal memproses gambar.'
                );
            }
        }
    );


    /* ============================================================
       CAMERA
    ============================================================ */

    startCamBtn.addEventListener(
        'click',
        startCamera
    );


    async function startCamera() {

        hideCameraError();

        if (!window.isSecureContext) {

            const message =
                'Kamera membutuhkan HTTPS.';

            showCameraError(
                message
            );

            cameraPlaceholder.classList.remove(
                'hidden'
            );

            cameraPlaceholderText.textContent =
                message;

            return;
        }


        if (
            !navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia
        ) {

            showCameraError(
                'Browser tidak mendukung akses kamera.'
            );

            return;
        }


        stopCamera();


        try {

            cameraStream =
                await navigator.mediaDevices.getUserMedia({

                    video: {
                        facingMode: {
                            ideal: 'environment'
                        },

                        width: {
                            ideal:
                                CAMERA_MAX_WIDTH
                        },

                        height: {
                            ideal:
                                CAMERA_MAX_HEIGHT
                        }
                    },

                    audio: false
                });


            video.srcObject =
                cameraStream;

            await video.play();


            video.classList.remove(
                'hidden'
            );

            imagePreview.hidden =
                true;

            cameraPlaceholder.classList.add(
                'hidden'
            );


            startCamBtn.classList.add(
                'hidden'
            );

            captureBtn.classList.remove(
                'hidden'
            );

            stopCamBtn.classList.remove(
                'hidden'
            );


            capturedImage.value =
                '';

            clearCapturedPreview();


        } catch (error) {

            console.error(
                'Camera error:',
                error
            );

            let message =
                'Kamera tidak dapat digunakan.';

            if (
                error.name ===
                    'NotAllowedError' ||
                error.name ===
                    'PermissionDeniedError'
            ) {

                message =
                    'Akses kamera ditolak. Izinkan kamera pada browser.';

            } else if (
                error.name ===
                    'NotFoundError' ||
                error.name ===
                    'DevicesNotFoundError'
            ) {

                message =
                    'Kamera tidak ditemukan.';

            } else if (
                error.name ===
                    'NotReadableError'
            ) {

                message =
                    'Kamera sedang digunakan aplikasi lain.';

            } else if (
                error.name ===
                    'SecurityError'
            ) {

                message =
                    'Akses kamera diblokir oleh browser.';
            }

            showCameraError(
                message
            );
        }
    }


    /* ============================================================
       CAPTURE
    ============================================================ */

    captureBtn.addEventListener(
        'click',
        function () {

            hideCameraError();

            if (
                !cameraStream ||
                !video.videoWidth ||
                !video.videoHeight
            ) {

                showCameraError(
                    'Kamera belum siap. Tunggu beberapa saat.'
                );

                return;
            }


            try {

                const imageData =
                    buildCameraImage();


                if (
                    imageData.length >
                    CAMERA_MAX_DATA_SIZE
                ) {

                    throw new Error(
                        'Hasil scan terlalu besar.'
                    );
                }


                capturedImage.value =
                    imageData;


                imagePreview.src =
                    imageData;

                imagePreview.hidden =
                    false;


                video.classList.add(
                    'hidden'
                );

                cameraPlaceholder.classList.add(
                    'hidden'
                );


                captureBtn.classList.add(
                    'hidden'
                );

                startCamBtn.classList.add(
                    'hidden'
                );

                retakeBtn.classList.remove(
                    'hidden'
                );

                stopCamBtn.classList.remove(
                    'hidden'
                );


                const size =
                    estimateBinarySize(
                        imageData
                    );


                snapshotPreview.classList.remove(
                    'hidden'
                );

                snapshotPreview.textContent =
                    '✓ Hasil scan siap disimpan (' +
                    formatFileSize(size) +
                    ').';


                clearSelectedFile();


                compressionStatus.textContent =
                    '✓ Hasil scan sudah dikompres menjadi JPG sebelum dikirim.';

                compressionStatus.classList.remove(
                    'hidden'
                );


                stopCamera();


            } catch (error) {

                console.error(
                    'Capture error:',
                    error
                );

                showCameraError(
                    error?.message ||
                    'Gagal mengambil gambar dari kamera.'
                );
            }
        }
    );


    /* ============================================================
       BUILD CAMERA IMAGE
    ============================================================ */

    function buildCameraImage() {

        const canvas =
            document.createElement(
                'canvas'
            );

        let width =
            video.videoWidth;

        let height =
            video.videoHeight;


        const dimensions =
            calculateDimensions(
                width,
                height,
                CAMERA_MAX_WIDTH
            );


        width =
            dimensions.width;

        height =
            dimensions.height;


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


        let quality =
            0.82;

        let dataUrl =
            canvas.toDataURL(
                'image/jpeg',
                quality
            );


        /*
        |--------------------------------------------------------------------------
        | Turunkan kualitas sampai target.
        |--------------------------------------------------------------------------
        */

        while (
            estimateBinarySize(
                dataUrl
            ) >
                CAMERA_TARGET_SIZE &&
            quality >
                0.40
        ) {

            quality -=
                0.06;

            dataUrl =
                canvas.toDataURL(
                    'image/jpeg',
                    quality
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Jika masih besar,
        | resize 75% beberapa kali.
        |--------------------------------------------------------------------------
        */

        let attempt =
            0;


        let currentCanvas =
            canvas;


        while (
            estimateBinarySize(
                dataUrl
            ) >
                CAMERA_TARGET_SIZE &&
            attempt <
                4
        ) {

            attempt++;


            const smaller =
                document.createElement(
                    'canvas'
                );


            smaller.width =
                Math.max(
                    1,
                    Math.round(
                        currentCanvas.width *
                        0.75
                    )
                );


            smaller.height =
                Math.max(
                    1,
                    Math.round(
                        currentCanvas.height *
                        0.75
                    )
                );


            const smallerContext =
                smaller.getContext(
                    '2d',
                    {
                        alpha: false
                    }
                );


            if (!smallerContext) {

                throw new Error(
                    'Canvas resize tidak tersedia.'
                );
            }


            smallerContext.fillStyle =
                '#ffffff';

            smallerContext.fillRect(
                0,
                0,
                smaller.width,
                smaller.height
            );


            smallerContext.imageSmoothingEnabled =
                true;

            smallerContext.imageSmoothingQuality =
                'high';


            smallerContext.drawImage(
                currentCanvas,
                0,
                0,
                smaller.width,
                smaller.height
            );


            currentCanvas =
                smaller;


            dataUrl =
                currentCanvas.toDataURL(
                    'image/jpeg',
                    0.58
                );
        }


        return dataUrl;
    }


    /* ============================================================
       BASE64 SIZE
    ============================================================ */

    function estimateBinarySize(
        dataUrl
    ) {

        const comma =
            dataUrl.indexOf(',');

        if (
            comma === -1
        ) {
            return 0;
        }


        const base64 =
            dataUrl.substring(
                comma + 1
            );


        const padding =
            base64.endsWith('==')
                ? 2
                : base64.endsWith('=')
                    ? 1
                    : 0;


        return Math.floor(
            (
                base64.length *
                3
            ) /
            4
        ) -
        padding;
    }


    /* ============================================================
       RETAKE
    ============================================================ */

    retakeBtn.addEventListener(
        'click',
        async function () {

            clearCapturedPreview();

            capturedImage.value =
                '';

            await startCamera();
        }
    );


    /* ============================================================
       CLEAR CAMERA RESULT
    ============================================================ */

    function clearCapturedPreview() {

        capturedImage.value =
            '';

        imagePreview.src =
            '';

        imagePreview.hidden =
            true;

        snapshotPreview.classList.add(
            'hidden'
        );

        snapshotPreview.textContent =
            '';
    }


    /* ============================================================
       STOP CAMERA
    ============================================================ */

    stopCamBtn.addEventListener(
        'click',
        function () {

            stopCamera();

            if (capturedImage.value) {

                cameraPlaceholder.classList.add(
                    'hidden'
                );

                imagePreview.hidden =
                    false;

                startCamBtn.classList.add(
                    'hidden'
                );

                captureBtn.classList.add(
                    'hidden'
                );

                retakeBtn.classList.remove(
                    'hidden'
                );

                stopCamBtn.classList.add(
                    'hidden'
                );

            } else {

                cameraPlaceholder.classList.remove(
                    'hidden'
                );

                cameraPlaceholderText.textContent =
                    'Kamera belum aktif';

                resetCameraButtons();
            }
        }
    );


    function stopCamera() {

        if (cameraStream) {

            cameraStream
                .getTracks()
                .forEach(
                    function (
                        track
                    ) {

                        track.stop();
                    }
                );

            cameraStream =
                null;
        }


        video.srcObject =
            null;


        if (!capturedImage.value) {

            cameraPlaceholder.classList.remove(
                'hidden'
            );
        }
    }


    function resetCameraButtons() {

        startCamBtn.classList.remove(
            'hidden'
        );

        captureBtn.classList.add(
            'hidden'
        );

        retakeBtn.classList.add(
            'hidden'
        );

        stopCamBtn.classList.add(
            'hidden'
        );
    }


    /* ============================================================
       SUBMIT
    ============================================================ */

    form.addEventListener(
        'submit',
        function (
            event
        ) {

            if (submitting) {

                event.preventDefault();

                return;
            }


            const file =
                fileInput.files?.[0];

            const hasFile =
                !!file;

            const hasCamera =
                capturedImage.value.trim() !== '';


            /*
            |--------------------------------------------------------------------------
            | Create wajib punya file
            | atau kamera.
            |--------------------------------------------------------------------------
            */

            if (
                !hasFile &&
                !hasCamera
            ) {

                event.preventDefault();

                alert(
                    'Berkas digital wajib diupload atau discan menggunakan kamera.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Tidak boleh dua sumber sekaligus.
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
            | File maksimal 10 MB.
            |--------------------------------------------------------------------------
            */

            if (
                hasFile &&
                file.size >
                    MAX_FILE_SIZE
            ) {

                event.preventDefault();

                alert(
                    'Ukuran file terlalu besar. Maksimal 10 MB.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Camera Base64.
            |--------------------------------------------------------------------------
            */

            if (
                hasCamera &&
                capturedImage.value.length >
                    CAMERA_MAX_DATA_SIZE
            ) {

                event.preventDefault();

                alert(
                    'Hasil scan terlalu besar. Silakan scan ulang.'
                );

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | SUBMITTING
            |--------------------------------------------------------------------------
            */

            submitting =
                true;


            stopCamera();


            submitBtn.disabled =
                true;


            submitIcon.classList.add(
                'hidden'
            );


            submitLoading.classList.remove(
                'hidden'
            );


            submitText.textContent =
                'Menyimpan...';
        }
    );


    /* ============================================================
       OLD CAMERA DATA
    ============================================================ */

    if (
        capturedImage.value
    ) {

        setCameraMode();

        cameraPlaceholder.classList.add(
            'hidden'
        );

        startCamBtn.classList.add(
            'hidden'
        );

        captureBtn.classList.add(
            'hidden'
        );

        retakeBtn.classList.remove(
            'hidden'
        );

        stopCamBtn.classList.add(
            'hidden'
        );

        imagePreview.src =
            capturedImage.value;

        imagePreview.hidden =
            false;

        snapshotPreview.classList.remove(
            'hidden'
        );

        snapshotPreview.textContent =
            '✓ Hasil scan sebelumnya masih tersedia.';
    }


    /* ============================================================
       CLEANUP
    ============================================================ */

    window.addEventListener(
        'beforeunload',
        function () {

            stopCamera();
        }
    );
});
</script>

@endsection