@extends('layouts.app')

@section('title', 'Catat Surat Keluar')

@section('content')

@php
    $inputClass =
        'block w-full rounded-md border-2 border-slate-400 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-100';

    $errorInputClass =
        'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-rose-100';
@endphp

<div class="mx-auto w-full max-w-5xl px-3 pb-6 sm:px-4 lg:px-5">

    {{-- =====================================================
         TOMBOL KEMBALI
    ====================================================== --}}

    <div class="mb-4 flex justify-end">

        <a
            href="{{ route('surat-keluar.index') }}"
            class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-3.5 py-2 text-[10px] font-semibold text-slate-600 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 hover:text-slate-800"
        >
            <svg
                class="h-3.5 w-3.5"
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

    {{-- =====================================================
         FORM
    ====================================================== --}}

    <form
        id="form-surat"
        method="POST"
        action="{{ route('surat-keluar.store') }}"
        enctype="multipart/form-data"
        novalidate
    >

        @csrf

        {{-- =================================================
             MAIN CARD
        ================================================== --}}

        <div class="overflow-hidden rounded-lg border-2 border-slate-400 bg-white shadow-sm">

            {{-- =================================================
                 HEADER
            ================================================== --}}

            <div class="border-b-2 border-emerald-300 bg-gradient-to-r from-emerald-50 to-blue-50 px-4 py-3">

                <div class="flex items-center justify-between gap-3">

                    <div class="flex items-center gap-2.5">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border-2 border-emerald-300 bg-white text-emerald-600 shadow-sm">

                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M6 2h9l5 5v15H6a2 2 0 01-2-2V4a2 2 0 012-2z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M14 2v6h6"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M8 13h8M8 17h6"
                                />
                            </svg>

                        </div>

                        <div>

                            <p class="text-[9px] font-bold uppercase tracking-wider text-emerald-600">
                                Arsip Surat Keluar
                            </p>

                            <p class="text-sm font-bold text-emerald-900">
                                Catat Surat Keluar
                            </p>

                        </div>

                    </div>

                    <div class="hidden rounded-md border border-emerald-300 bg-white/80 px-3 py-1.5 text-right sm:block">

                        <p class="text-[8px] font-semibold uppercase tracking-wide text-emerald-500">
                            Sistem Arsip
                        </p>

                        <p class="text-[9px] font-medium text-emerald-700">
                            Input surat baru
                        </p>

                    </div>

                </div>

            </div>

            {{-- =================================================
                 BODY
            ================================================== --}}

            <div class="p-4 sm:p-5">

                {{-- =================================================
                     INFORMASI UTAMA
                ================================================== --}}

                <section>

                    <div class="mb-3 flex items-start gap-2.5 border-b-2 border-slate-300 pb-2.5">

                        <div class="mt-0.5 h-7 w-1 shrink-0 rounded-full bg-emerald-600"></div>

                        <div>

                            <h2 class="text-sm font-bold text-slate-800">
                                Informasi Utama Surat
                            </h2>

                            <p class="text-[10px] text-slate-500">
                                Lengkapi identitas dan informasi utama surat keluar.
                            </p>

                        </div>

                    </div>

                    {{-- =================================================
                         FIELD GRID
                    ================================================== --}}

                    <div class="overflow-hidden rounded-md border-2 border-slate-400">

                        <div class="grid grid-cols-1 md:grid-cols-2">

                            {{-- =================================================
                                 NOMOR SURAT
                            ================================================== --}}

                            <div class="border-b-2 border-slate-300 p-3 md:border-r-2">

                                <label
                                    for="nomor_surat"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
                                >
                                    Nomor Surat
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="nomor_surat"
                                    name="nomor_surat"
                                    value="{{ old('nomor_surat') }}"
                                    placeholder="Contoh: 005/SK/I/2026"
                                    autocomplete="off"
                                    maxlength="255"
                                    required
                                    class="{{ $inputClass }} @error('nomor_surat') {{ $errorInputClass }} @enderror"
                                >

                                @error('nomor_surat')
                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            {{-- =================================================
                                 TUJUAN SURAT
                            ================================================== --}}

                            <div class="border-b-2 border-slate-300 p-3">

                                <label
                                    for="pengirim"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
                                >
                                    Tujuan Surat
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="pengirim"
                                    name="pengirim"
                                    value="{{ old('pengirim') }}"
                                    placeholder="Contoh: PT Maju Takgentar"
                                    autocomplete="organization"
                                    maxlength="150"
                                    required
                                    class="{{ $inputClass }} @error('pengirim') {{ $errorInputClass }} @enderror"
                                >

                                <p class="mt-1 text-[9px] leading-relaxed text-slate-400">
                                    Instansi, lembaga, organisasi, atau pihak tujuan surat.
                                </p>

                                @error('pengirim')
                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            {{-- =================================================
                                 TANGGAL SURAT
                            ================================================== --}}

                            <div class="border-b-2 border-slate-300 p-3 md:border-r-2">

                                <label
                                    for="tanggal_surat"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
                                >
                                    Tanggal Surat
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    type="date"
                                    id="tanggal_surat"
                                    name="tanggal_surat"
                                    value="{{ old('tanggal_surat', now()->format('Y-m-d')) }}"
                                    required
                                    class="{{ $inputClass }} @error('tanggal_surat') {{ $errorInputClass }} @enderror"
                                >

                                @error('tanggal_surat')
                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            {{-- =================================================
                                 TANGGAL KELUAR
                            ================================================== --}}

                            <div class="border-b-2 border-slate-300 p-3">

                                <label
                                    for="tanggal_keluar"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
                                >
                                    Tanggal Keluar
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    type="date"
                                    id="tanggal_keluar"
                                    name="tanggal_keluar"
                                    value="{{ old('tanggal_keluar', now()->format('Y-m-d')) }}"
                                    required
                                    class="{{ $inputClass }} @error('tanggal_keluar') {{ $errorInputClass }} @enderror"
                                >

                                <p class="mt-1 text-[9px] leading-relaxed text-slate-400">
                                    Tanggal surat keluar atau tanggal surat dikirim.
                                </p>

                                @error('tanggal_keluar')
                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            {{-- =================================================
                                 KATEGORI
                            ================================================== --}}

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
                                    class="{{ $inputClass }} @error('kategori_surat_id') {{ $errorInputClass }} @enderror"
                                >

                                    <option
                                        value=""
                                        disabled
                                        @selected(!old('kategori_surat_id'))
                                    >
                                        Pilih kategori surat
                                    </option>

                                    @foreach($kategoris ?? [] as $kategori)

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

                                @if(
                                    !isset($kategoris) ||
                                    $kategoris->isEmpty()
                                )
                                    <p class="mt-1 text-[9px] leading-relaxed text-amber-600">
                                        Belum ada kategori surat yang tersedia.
                                    </p>
                                @endif

                                @error('kategori_surat_id')
                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            {{-- =================================================
                                 STATUS
                            ================================================== --}}

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
                                    class="{{ $inputClass }} @error('status') {{ $errorInputClass }} @enderror"
                                >

                                    <option
                                        value="draft"
                                        @selected(old('status', 'draft') === 'draft')
                                    >
                                        Draft
                                    </option>

                                    <option
                                        value="diproses"
                                        @selected(old('status') === 'diproses')
                                    >
                                        Diproses
                                    </option>

                                    <option
                                        value="disetujui"
                                        @selected(old('status') === 'disetujui')
                                    >
                                        Disetujui
                                    </option>

                                    <option
                                        value="dikirim"
                                        @selected(old('status') === 'dikirim')
                                    >
                                        Dikirim
                                    </option>

                                    <option
                                        value="diarsipkan"
                                        @selected(old('status') === 'diarsipkan')
                                    >
                                        Diarsipkan
                                    </option>

                                </select>

                                @error('status')
                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            {{-- =================================================
                                 PERIHAL
                            ================================================== --}}

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
                                    maxlength="255"
                                    required
                                    placeholder="Tuliskan perihal surat secara jelas..."
                                    class="{{ $inputClass }} resize-y @error('perihal') {{ $errorInputClass }} @enderror"
                                >{{ old('perihal') }}</textarea>

                                @error('perihal')
                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            {{-- =================================================
                                 RINGKASAN
                            ================================================== --}}

                            <div class="p-3 md:col-span-2">

                                <label
                                    for="ringkasan"
                                    class="mb-1.5 block text-[11px] font-bold text-slate-700"
                                >
                                    Ringkasan Isi Surat
                                </label>

                                <textarea
                                    id="ringkasan"
                                    name="ringkasan"
                                    rows="3"
                                    maxlength="5000"
                                    placeholder="Tuliskan ringkasan singkat isi surat..."
                                    class="{{ $inputClass }} resize-y @error('ringkasan') {{ $errorInputClass }} @enderror"
                                >{{ old('ringkasan') }}</textarea>

                                <p class="mt-1 text-[9px] leading-relaxed text-slate-400">
                                    Maksimal 5.000 karakter. Field ini bersifat opsional.
                                </p>

                                @error('ringkasan')
                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </div>

                </section>

                <div class="my-4 border-t-2 border-slate-300"></div>

                {{-- =================================================
                     LAMPIRAN DIGITAL
                ================================================== --}}

                <section>

                    <div class="mb-3 flex items-start gap-2.5 border-b-2 border-slate-300 pb-2.5">

                        <div class="mt-0.5 h-7 w-1 shrink-0 rounded-full bg-indigo-600"></div>

                        <div>

                            <h2 class="text-sm font-bold text-slate-800">
                                Lampiran Berkas Digital
                            </h2>

                            <p class="text-[10px] text-slate-500">
                                Upload dokumen surat untuk disimpan sebagai arsip digital.
                            </p>

                        </div>

                    </div>

                    <div class="overflow-hidden rounded-md border-2 border-slate-400 bg-white shadow-sm">

                        {{-- =================================================
                             HEADER LAMPIRAN
                        ================================================== --}}

                        <div class="border-b-2 border-blue-300 bg-blue-50 px-3 py-2.5">

                            <div class="flex items-center justify-between gap-2">

                                <div class="flex min-w-0 items-center gap-2">

                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-blue-300 bg-white text-blue-600">

                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
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
                                            Dokumen Surat
                                        </h3>

                                        <p class="text-[9px] text-slate-500">
                                            PDF, JPG, JPEG, atau PNG • Maksimal 10 MB
                                        </p>

                                    </div>

                                </div>

                                <span class="shrink-0 rounded-full border border-slate-300 bg-white px-1.5 py-0.5 text-[8px] font-bold uppercase text-slate-500">
                                    Opsional
                                </span>

                            </div>

                        </div>

                        {{-- =================================================
                             BODY LAMPIRAN
                        ================================================== --}}

                        <div class="p-3 sm:p-4">

                            {{-- =================================================
                                 INFORMASI KOMPRESI
                            ================================================== --}}

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
                                        d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"
                                    />
                                </svg>

                                <div class="text-[9px] leading-relaxed">

                                    <p class="font-bold text-blue-700">
                                        Pemrosesan lampiran otomatis
                                    </p>

                                    <p class="text-blue-600">
                                        PDF akan disimpan sebagai PDF.
                                        JPG, JPEG, dan PNG akan otomatis diproses,
                                        di-resize bila diperlukan, kemudian dikompres
                                        menjadi JPG sebelum disimpan ke server/storage.
                                    </p>

                                </div>

                            </div>

                            {{-- =================================================
                                 INFO BATAS FILE
                            ================================================== --}}

                            <div class="mb-3 grid grid-cols-1 gap-2 sm:grid-cols-3">

                                <div class="rounded-md border border-slate-200 bg-slate-50 px-2.5 py-2">

                                    <p class="text-[8px] font-bold uppercase tracking-wide text-slate-400">
                                        Batas Upload
                                    </p>

                                    <p class="mt-0.5 text-[10px] font-bold text-slate-700">
                                        Maksimal 10 MB
                                    </p>

                                </div>

                                <div class="rounded-md border border-slate-200 bg-slate-50 px-2.5 py-2">

                                    <p class="text-[8px] font-bold uppercase tracking-wide text-slate-400">
                                        Dokumen
                                    </p>

                                    <p class="mt-0.5 text-[10px] font-bold text-slate-700">
                                        PDF
                                    </p>

                                </div>

                                <div class="rounded-md border border-slate-200 bg-slate-50 px-2.5 py-2">

                                    <p class="text-[8px] font-bold uppercase tracking-wide text-slate-400">
                                        Gambar
                                    </p>

                                    <p class="mt-0.5 text-[10px] font-bold text-slate-700">
                                        JPG / JPEG / PNG
                                    </p>

                                </div>

                            </div>

                            {{-- =================================================
                                 UPLOAD BOX
                            ================================================== --}}

                            <label
                                for="lampiran_file"
                                id="uploadBox"
                                class="group relative flex min-h-[155px] cursor-pointer flex-col items-center justify-center rounded-md border-2 border-dashed border-slate-400 bg-slate-50 px-4 py-5 text-center transition hover:border-blue-400 hover:bg-blue-50"
                            >

                                <div
                                    id="uploadIcon"
                                    class="mb-2.5 flex h-10 w-10 items-center justify-center rounded-md border-2 border-blue-200 bg-blue-100 text-blue-600 transition group-hover:border-blue-300 group-hover:bg-blue-100"
                                >

                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                        />
                                    </svg>

                                </div>

                                <span
                                    id="file-label-text"
                                    class="text-[11px] font-bold text-slate-700"
                                >
                                    Klik untuk memilih file
                                </span>

                                <span
                                    id="file-sub-label"
                                    class="mt-1 text-[9px] text-slate-500"
                                >
                                    PDF, JPG, JPEG, PNG
                                </span>

                                <span class="mt-1.5 rounded-full bg-blue-100 px-2 py-0.5 text-[8px] font-semibold text-blue-600">
                                    Maksimal 10 MB
                                </span>

                                <input
                                    type="file"
                                    id="lampiran_file"
                                    name="lampiran_file"
                                    accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                    class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                >

                            </label>

                            {{-- =================================================
                                 FILE INFO
                            ================================================== --}}

                            <div
                                id="file-info"
                                class="mt-2 hidden rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-2 text-[9px] leading-relaxed text-emerald-700"
                            ></div>

                            {{-- =================================================
                                 COMPRESSION INFO
                            ================================================== --}}

                            <div
                                id="compression-info"
                                class="mt-2 hidden rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-2 text-[9px] leading-relaxed text-indigo-700"
                            ></div>

                            {{-- =================================================
                                 ERROR FILE
                            ================================================== --}}

                            @error('lampiran_file')

                                <p class="mt-1.5 text-[10px] font-medium text-rose-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>

                    </div>

                </section>

                {{-- =================================================
                     VALIDATION SUMMARY
                ================================================== --}}

                @if($errors->any())

                    <div
                        class="mt-4 rounded-lg border-2 border-rose-200 bg-rose-50 p-3 text-xs text-rose-700"
                        role="alert"
                    >

                        <div class="flex items-start gap-2">

                            <svg
                                class="mt-0.5 h-4 w-4 shrink-0 text-rose-500"
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

                            <div>

                                <p class="font-bold">
                                    Terdapat kesalahan pada formulir.
                                </p>

                                <ul class="mt-1 list-inside list-disc space-y-0.5">

                                    @foreach($errors->all() as $error)

                                        <li>
                                            {{ $error }}
                                        </li>

                                    @endforeach

                                </ul>

                            </div>

                        </div>

                    </div>

                @endif

            </div>

            {{-- =================================================
                 FOOTER
            ================================================== --}}

            <div class="flex flex-col gap-2 border-t-2 border-slate-400 bg-slate-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-end">

                {{-- BATAL --}}

                <a
                    href="{{ route('surat-keluar.index') }}"
                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border-2 border-slate-400 bg-white px-4 py-2 text-[10px] font-bold text-slate-700 shadow-sm transition hover:border-slate-500 hover:bg-slate-100 sm:w-auto"
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
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>

                    Batal

                </a>

                {{-- SIMPAN --}}

                <button
                    type="submit"
                    id="submit-btn"
                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border-2 border-emerald-600 bg-emerald-600 px-4 py-2 text-[10px] font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                >

                    <svg
                        class="h-3.5 w-3.5"
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

                    <span>
                        Simpan Surat Keluar
                    </span>

                </button>

            </div>

        </div>

    </form>

</div>

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    'use strict';

    const form =
        document.getElementById('form-surat');

    const fileInput =
        document.getElementById('lampiran_file');

    const fileLabelText =
        document.getElementById('file-label-text');

    const fileSubLabel =
        document.getElementById('file-sub-label');

    const fileInfo =
        document.getElementById('file-info');

    const compressionInfo =
        document.getElementById('compression-info');

    const uploadBox =
        document.getElementById('uploadBox');

    const uploadIcon =
        document.getElementById('uploadIcon');

    const submitButton =
        document.getElementById('submit-btn');

    const tanggalSurat =
        document.getElementById('tanggal_surat');

    const tanggalKeluar =
        document.getElementById('tanggal_keluar');

    const MAX_FILE_SIZE =
        10 * 1024 * 1024;

    const ALLOWED_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png'
    ];

    let isSubmitting = false;

    /*
    |--------------------------------------------------------------------------
    | FORMAT UKURAN FILE
    |--------------------------------------------------------------------------
    */

    function formatFileSize(bytes) {

        if (bytes < 1024) {
            return bytes + ' B';
        }

        if (bytes < 1024 * 1024) {
            return (
                (bytes / 1024).toFixed(1) +
                ' KB'
            );
        }

        return (
            (bytes / 1024 / 1024).toFixed(2) +
            ' MB'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESET FILE
    |--------------------------------------------------------------------------
    */

    function resetFileInput() {

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

        if (fileSubLabel) {

            fileSubLabel.textContent =
                'PDF, JPG, JPEG, PNG';

            fileSubLabel.classList.remove(
                'text-rose-600',
                'text-indigo-600'
            );

            fileSubLabel.classList.add(
                'text-slate-500'
            );
        }

        if (fileInfo) {

            fileInfo.textContent = '';

            fileInfo.classList.add(
                'hidden'
            );
        }

        if (compressionInfo) {

            compressionInfo.textContent = '';

            compressionInfo.classList.add(
                'hidden'
            );
        }

        if (uploadBox) {

            uploadBox.classList.remove(
                'border-emerald-400',
                'bg-emerald-50',
                'border-rose-400',
                'bg-rose-50'
            );

            uploadBox.classList.add(
                'border-slate-400',
                'bg-slate-50'
            );
        }

        if (uploadIcon) {

            uploadIcon.classList.remove(
                'border-emerald-300',
                'bg-emerald-100',
                'text-emerald-600',
                'border-rose-300',
                'bg-rose-100',
                'text-rose-600'
            );

            uploadIcon.classList.add(
                'border-blue-200',
                'bg-blue-100',
                'text-blue-600'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ERROR FILE
    |--------------------------------------------------------------------------
    */

    function showFileError(message) {

        if (fileLabelText) {

            fileLabelText.textContent =
                'File tidak dapat digunakan';

            fileLabelText.classList.remove(
                'text-slate-700',
                'text-blue-600'
            );

            fileLabelText.classList.add(
                'text-rose-600'
            );
        }

        if (fileInfo) {

            fileInfo.textContent =
                message;

            fileInfo.classList.remove(
                'hidden'
            );

            fileInfo.classList.remove(
                'border-emerald-200',
                'bg-emerald-50',
                'text-emerald-700'
            );

            fileInfo.classList.add(
                'border-rose-200',
                'bg-rose-50',
                'text-rose-700'
            );
        }

        if (uploadBox) {

            uploadBox.classList.remove(
                'border-slate-400',
                'bg-slate-50'
            );

            uploadBox.classList.add(
                'border-rose-400',
                'bg-rose-50'
            );
        }

        if (uploadIcon) {

            uploadIcon.classList.remove(
                'border-blue-200',
                'bg-blue-100',
                'text-blue-600'
            );

            uploadIcon.classList.add(
                'border-rose-300',
                'bg-rose-100',
                'text-rose-600'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FILE CHANGE
    |--------------------------------------------------------------------------
    */

    fileInput?.addEventListener(
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

            const extension =
                file.name
                    .split('.')
                    .pop()
                    ?.toLowerCase() || '';

            /*
            |--------------------------------------------------------------------------
            | CEK EXTENSION
            |--------------------------------------------------------------------------
            */

            if (
                !ALLOWED_EXTENSIONS.includes(
                    extension
                )
            ) {

                resetFileInput();

                showFileError(
                    'Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | CEK UKURAN
            |--------------------------------------------------------------------------
            */

            if (
                file.size >
                MAX_FILE_SIZE
            ) {

                resetFileInput();

                showFileError(
                    'Ukuran file melebihi batas 10 MB. Silakan pilih file yang lebih kecil.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | TAMPILKAN FILE
            |--------------------------------------------------------------------------
            */

            if (fileLabelText) {

                fileLabelText.textContent =
                    'Terpilih: ' + file.name;

                fileLabelText.classList.remove(
                    'text-slate-700',
                    'text-rose-600'
                );

                fileLabelText.classList.add(
                    'text-blue-600'
                );
            }

            if (fileInfo) {

                fileInfo.classList.remove(
                    'hidden'
                );

                fileInfo.classList.remove(
                    'border-rose-200',
                    'bg-rose-50',
                    'text-rose-700'
                );

                fileInfo.classList.add(
                    'border-emerald-200',
                    'bg-emerald-50',
                    'text-emerald-700'
                );

                fileInfo.textContent =
                    'File siap diproses • Ukuran asli: ' +
                    formatFileSize(file.size);
            }

            if (uploadBox) {

                uploadBox.classList.remove(
                    'border-slate-400',
                    'bg-slate-50'
                );

                uploadBox.classList.add(
                    'border-emerald-400',
                    'bg-emerald-50'
                );
            }

            if (uploadIcon) {

                uploadIcon.classList.remove(
                    'border-blue-200',
                    'bg-blue-100',
                    'text-blue-600'
                );

                uploadIcon.classList.add(
                    'border-emerald-300',
                    'bg-emerald-100',
                    'text-emerald-600'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | INFORMASI PROSES KOMPRESI
            |--------------------------------------------------------------------------
            */

            if (
                compressionInfo &&
                (
                    extension === 'jpg' ||
                    extension === 'jpeg' ||
                    extension === 'png'
                )
            ) {

                compressionInfo.textContent =
                    'Gambar akan otomatis diproses oleh server: ' +
                    'resize bila diperlukan dan kompresi ke format JPG sebelum disimpan.';

                compressionInfo.classList.remove(
                    'hidden'
                );
            } else if (
                compressionInfo &&
                extension === 'pdf'
            ) {

                compressionInfo.textContent =
                    'PDF akan disimpan sebagai PDF tanpa kompresi gambar.';

                compressionInfo.classList.remove(
                    'hidden'
                );
            }

            if (fileSubLabel) {

                fileSubLabel.textContent =
                    extension === 'pdf'
                        ? 'PDF • Disimpan sebagai PDF'
                        : 'Gambar • Otomatis dikompres menjadi JPG';

                fileSubLabel.classList.remove(
                    'text-slate-500',
                    'text-rose-600'
                );

                fileSubLabel.classList.add(
                    'text-indigo-600'
                );
            }
        }
    );

    /*
    |--------------------------------------------------------------------------
    | SUBMIT
    |--------------------------------------------------------------------------
    */

    form?.addEventListener(
        'submit',
        function (event) {

            if (isSubmitting) {

                event.preventDefault();

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDASI TANGGAL
            |--------------------------------------------------------------------------
            */

            const suratDate =
                tanggalSurat?.value || '';

            const keluarDate =
                tanggalKeluar?.value || '';

            if (
                suratDate &&
                keluarDate &&
                keluarDate < suratDate
            ) {

                event.preventDefault();

                if (
                    typeof window.Swal !==
                    'undefined'
                ) {

                    window.Swal.fire({
                        icon: 'warning',
                        title: 'Tanggal tidak valid',
                        text: 'Tanggal keluar tidak boleh lebih awal dari tanggal surat.',
                        confirmButtonText: 'Mengerti',
                        confirmButtonColor: '#059669'
                    });

                } else {

                    window.alert(
                        'Tanggal keluar tidak boleh lebih awal dari tanggal surat.'
                    );
                }

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDASI FILE
            |--------------------------------------------------------------------------
            */

            if (
                fileInput &&
                fileInput.files &&
                fileInput.files.length > 0
            ) {

                const file =
                    fileInput.files[0];

                const extension =
                    file.name
                        .split('.')
                        .pop()
                        ?.toLowerCase() || '';

                if (
                    !ALLOWED_EXTENSIONS.includes(
                        extension
                    )
                ) {

                    event.preventDefault();

                    if (
                        typeof window.Swal !==
                        'undefined'
                    ) {

                        window.Swal.fire({
                            icon: 'warning',
                            title: 'Format file tidak valid',
                            text: 'Gunakan PDF, JPG, JPEG, atau PNG.',
                            confirmButtonText: 'Mengerti',
                            confirmButtonColor: '#059669'
                        });

                    } else {

                        window.alert(
                            'Gunakan PDF, JPG, JPEG, atau PNG.'
                        );
                    }

                    return;
                }

                if (
                    file.size >
                    MAX_FILE_SIZE
                ) {

                    event.preventDefault();

                    if (
                        typeof window.Swal !==
                        'undefined'
                    ) {

                        window.Swal.fire({
                            icon: 'warning',
                            title: 'File terlalu besar',
                            text: 'Ukuran lampiran maksimal 10 MB.',
                            confirmButtonText: 'Mengerti',
                            confirmButtonColor: '#059669'
                        });

                    } else {

                        window.alert(
                            'Ukuran lampiran maksimal 10 MB.'
                        );
                    }

                    return;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | LOCK SUBMIT
            |--------------------------------------------------------------------------
            */

            isSubmitting = true;

            if (submitButton) {

                submitButton.disabled = true;

                submitButton.innerHTML = `
                    <svg
                        class="mr-1.5 h-3.5 w-3.5 animate-spin"
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
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                        ></path>
                    </svg>

                    Menyimpan...
                `;
            }

            /*
            |--------------------------------------------------------------------------
            | UPDATE STATUS PEMROSESAN
            |--------------------------------------------------------------------------
            */

            if (
                compressionInfo &&
                fileInput &&
                fileInput.files &&
                fileInput.files.length > 0
            ) {

                const file =
                    fileInput.files[0];

                const extension =
                    file.name
                        .split('.')
                        .pop()
                        ?.toLowerCase() || '';

                if (
                    extension === 'jpg' ||
                    extension === 'jpeg' ||
                    extension === 'png'
                ) {

                    compressionInfo.textContent =
                        'Sedang mengirim file. Gambar akan dikompres otomatis oleh server sebelum disimpan.';
                } else {

                    compressionInfo.textContent =
                        'Sedang mengirim file PDF ke server.';
                }
            }
        }
    );

});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endpush

@endsection