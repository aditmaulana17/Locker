@extends('layouts.app')

@section('title', 'Edit Surat Keluar')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | DATA FORM
    |--------------------------------------------------------------------------
    */

    $currentStatus = strtolower(
        trim(
            (string) old(
                'status',
                $suratKeluar->status ?? 'draft'
            )
        )
    );

    if ($currentStatus === 'draf') {
        $currentStatus = 'draft';
    }

    /*
    |--------------------------------------------------------------------------
    | TANGGAL SURAT
    |--------------------------------------------------------------------------
    */

    try {
        $tanggalSurat = old(
            'tanggal_surat',
            $suratKeluar->tanggal_surat
                ? \Illuminate\Support\Carbon::parse(
                    $suratKeluar->tanggal_surat
                )->format('Y-m-d')
                : ''
        );
    } catch (\Throwable $e) {
        $tanggalSurat = old(
            'tanggal_surat',
            ''
        );
    }

    /*
    |--------------------------------------------------------------------------
    | TANGGAL KELUAR
    |--------------------------------------------------------------------------
    */

    try {
        $tanggalKeluar = old(
            'tanggal_keluar',
            $suratKeluar->tanggal_keluar
                ? \Illuminate\Support\Carbon::parse(
                    $suratKeluar->tanggal_keluar
                )->format('Y-m-d')
                : ''
        );
    } catch (\Throwable $e) {
        $tanggalKeluar = old(
            'tanggal_keluar',
            ''
        );
    }

    /*
    |--------------------------------------------------------------------------
    | KATEGORI
    |--------------------------------------------------------------------------
    */

    $kategoriSuratId = old(
        'kategori_surat_id',
        $suratKeluar->kategori_surat_id
    );

    /*
    |--------------------------------------------------------------------------
    | INPUT CLASS
    |--------------------------------------------------------------------------
    */

    $inputClass =
        'block w-full rounded-md border-2 border-slate-400 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-100';

    $errorInputClass =
        'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-rose-100';

    /*
    |--------------------------------------------------------------------------
    | LAMPIRAN SAAT INI
    |--------------------------------------------------------------------------
    */

    $currentAttachmentPath = trim(
        (string) ($suratKeluar->lampiran_file ?? '')
    );

    $hasCurrentAttachment =
        $currentAttachmentPath !== '';

    $currentAttachmentName =
        $hasCurrentAttachment
            ? basename($currentAttachmentPath)
            : null;

    $currentAttachmentExtension =
        $hasCurrentAttachment
            ? strtolower(
                pathinfo(
                    $currentAttachmentPath,
                    PATHINFO_EXTENSION
                )
            )
            : '';
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
        action="{{ route('surat-keluar.update', $suratKeluar) }}"
        enctype="multipart/form-data"
        novalidate
    >

        @csrf
        @method('PUT')

        {{-- =================================================
             MAIN CARD
        ================================================== --}}

        <div class="overflow-hidden rounded-lg border-2 border-slate-400 bg-white shadow-sm">

            {{-- HEADER --}}

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
                                Edit Surat Keluar
                            </p>

                        </div>

                    </div>

                    <div class="hidden rounded-md border border-emerald-300 bg-white/80 px-3 py-1.5 text-right sm:block">

                        <p class="text-[8px] font-semibold uppercase tracking-wide text-emerald-500">
                            Sistem Arsip
                        </p>

                        <p class="text-[9px] font-medium text-emerald-700">
                            Perbarui data surat
                        </p>

                    </div>

                </div>

            </div>

            {{-- BODY --}}

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
                                Perbarui identitas dan informasi utama surat keluar.
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
                                    type="text"
                                    id="nomor_surat"
                                    name="nomor_surat"
                                    value="{{ old('nomor_surat', $suratKeluar->nomor_surat) }}"
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

                            {{-- TUJUAN --}}

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
                                    value="{{ old('pengirim', $suratKeluar->pengirim) }}"
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
                                    type="date"
                                    id="tanggal_surat"
                                    name="tanggal_surat"
                                    value="{{ $tanggalSurat }}"
                                    required
                                    class="{{ $inputClass }} @error('tanggal_surat') {{ $errorInputClass }} @enderror"
                                >

                                @error('tanggal_surat')
                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            {{-- TANGGAL KELUAR --}}

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
                                    value="{{ $tanggalKeluar }}"
                                    required
                                    class="{{ $inputClass }} @error('tanggal_keluar') {{ $errorInputClass }} @enderror"
                                >

                                <p class="mt-1 text-[9px] leading-relaxed text-slate-400">
                                    Tanggal surat resmi keluar atau dikirim.
                                </p>

                                @error('tanggal_keluar')
                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

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
                                    class="{{ $inputClass }} @error('kategori_surat_id') {{ $errorInputClass }} @enderror"
                                >

                                    <option
                                        value=""
                                        disabled
                                        @selected(!$kategoriSuratId)
                                    >
                                        Pilih kategori surat
                                    </option>

                                    @foreach($kategoris ?? [] as $kategori)

                                        <option
                                            value="{{ $kategori->id }}"
                                            @selected(
                                                (string) $kategoriSuratId ===
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
                                    class="{{ $inputClass }} @error('status') {{ $errorInputClass }} @enderror"
                                >

                                    <option
                                        value="draft"
                                        @selected($currentStatus === 'draft')
                                    >
                                        Draft
                                    </option>

                                    <option
                                        value="diproses"
                                        @selected($currentStatus === 'diproses')
                                    >
                                        Diproses
                                    </option>

                                    <option
                                        value="disetujui"
                                        @selected($currentStatus === 'disetujui')
                                    >
                                        Disetujui
                                    </option>

                                    <option
                                        value="dikirim"
                                        @selected($currentStatus === 'dikirim')
                                    >
                                        Dikirim
                                    </option>

                                    <option
                                        value="diarsipkan"
                                        @selected($currentStatus === 'diarsipkan')
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
                                    maxlength="255"
                                    required
                                    placeholder="Tuliskan perihal surat secara jelas..."
                                    class="{{ $inputClass }} resize-y @error('perihal') {{ $errorInputClass }} @enderror"
                                >{{ old('perihal', $suratKeluar->perihal) }}</textarea>

                                @error('perihal')
                                    <p class="mt-1 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            {{-- RINGKASAN --}}

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
                                >{{ old('ringkasan', $suratKeluar->ringkasan) }}</textarea>

                                <p class="mt-1 text-[9px] leading-relaxed text-slate-400">
                                    Maksimal 5.000 karakter.
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
                     LAMPIRAN
                ================================================== --}}

                <section>

                    <div class="mb-3 flex items-start gap-2.5 border-b-2 border-slate-300 pb-2.5">

                        <div class="mt-0.5 h-7 w-1 shrink-0 rounded-full bg-indigo-600"></div>

                        <div>

                            <h2 class="text-sm font-bold text-slate-800">
                                Lampiran Dokumen Surat
                            </h2>

                            <p class="text-[10px] text-slate-500">
                                Pertahankan lampiran lama atau upload dokumen pengganti.
                            </p>

                        </div>

                    </div>

                    <div class="grid grid-cols-1 gap-3.5 lg:grid-cols-2">

                        {{-- =================================================
                             LAMPIRAN LAMA
                        ================================================== --}}

                        @if($hasCurrentAttachment)

                            <div class="overflow-hidden rounded-md border-2 border-slate-400 bg-white shadow-sm">

                                <div class="border-b-2 border-emerald-300 bg-emerald-50 px-3 py-2.5">

                                    <div class="flex items-center justify-between gap-2">

                                        <div class="flex min-w-0 items-center gap-2">

                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md border border-emerald-300 bg-white text-emerald-600">

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
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A1.5 1.5 0 0118 8.5V19a2 2 0 01-2 2z"
                                                    />
                                                </svg>

                                            </div>

                                            <div class="min-w-0">

                                                <h3 class="text-[11px] font-bold text-slate-800">
                                                    Lampiran Saat Ini
                                                </h3>

                                                <p class="text-[9px] text-slate-500">
                                                    File yang sedang tersimpan.
                                                </p>

                                            </div>

                                        </div>

                                        <span class="shrink-0 rounded-full border border-emerald-200 bg-white px-1.5 py-0.5 text-[8px] font-bold uppercase text-emerald-600">
                                            Tersimpan
                                        </span>

                                    </div>

                                </div>

                                <div class="flex min-h-[220px] flex-col justify-between p-3">

                                    <div class="rounded-md border-2 border-slate-300 bg-slate-50 p-3">

                                        <div class="flex items-start gap-2">

                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border border-emerald-200 bg-emerald-100 text-emerald-600">

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
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                                    />
                                                </svg>

                                            </div>

                                            <div class="min-w-0 flex-1">

                                                <p class="text-[10px] font-bold text-slate-700">
                                                    Dokumen tersimpan
                                                </p>

                                                <p
                                                    class="mt-1 break-all text-[9px] leading-relaxed text-slate-500"
                                                    title="{{ $currentAttachmentName }}"
                                                >
                                                    {{ $currentAttachmentName }}
                                                </p>

                                                @if($currentAttachmentExtension)

                                                    <span class="mt-2 inline-flex rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[8px] font-bold uppercase text-slate-500">
                                                        .{{ $currentAttachmentExtension }}
                                                    </span>

                                                @endif

                                            </div>

                                        </div>

                                    </div>

                                    <a
                                        href="{{ route('surat-keluar.preview-lampiran', $suratKeluar) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="mt-3 inline-flex min-h-[35px] w-full items-center justify-center gap-1.5 rounded-md border-2 border-emerald-600 bg-emerald-600 px-3 py-2 text-[10px] font-bold text-white transition hover:bg-emerald-700"
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
                                                d="M15 10l4.55-2.27A1 1 0 0121 8.62v6.76a1 1 0 01-1.45.89L15 14m0-4v4M4 6h7a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"
                                            />
                                        </svg>

                                        Lihat Lampiran

                                    </a>

                                </div>

                            </div>

                        @endif

                        {{-- =================================================
                             UPLOAD FILE BARU
                        ================================================== --}}

                        <div class="overflow-hidden rounded-md border-2 border-slate-400 bg-white shadow-sm {{ !$hasCurrentAttachment ? 'lg:col-span-2' : '' }}">

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

                                        <div class="min-w-0">

                                            <h3 class="text-[11px] font-bold text-slate-800">
                                                Upload Lampiran Baru
                                            </h3>

                                            <p class="text-[9px] text-slate-500">
                                                File baru akan menggantikan file lama.
                                            </p>

                                        </div>

                                    </div>

                                    <span class="shrink-0 rounded-full border border-slate-300 bg-white px-1.5 py-0.5 text-[8px] font-bold uppercase text-slate-500">
                                        Opsional
                                    </span>

                                </div>

                            </div>

                            <div class="p-3">

                                {{-- =================================================
                                     INFORMASI KOMPRESI
                                ================================================== --}}

                                <div class="mb-2.5 flex items-start gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-2">

                                    <svg
                                        class="mt-0.5 h-3 w-3 shrink-0 text-indigo-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000-18z"
                                        />
                                    </svg>

                                    <div class="text-[9px] leading-relaxed">

                                        <p class="font-bold text-indigo-700">
                                            Kompresi gambar otomatis
                                        </p>

                                        <p class="text-indigo-600">
                                            JPG, JPEG, dan PNG akan diproses,
                                            di-resize bila diperlukan, kemudian
                                            dikompres menjadi JPG.
                                            PDF tetap disimpan sebagai PDF.
                                        </p>

                                    </div>

                                </div>

                                {{-- =================================================
                                     STATUS FILE LAMA
                                ================================================== --}}

                                <div
                                    id="current-file-note"
                                    class="mb-2.5 rounded-md border border-slate-200 bg-slate-50 px-2.5 py-2"
                                >

                                    <p class="text-[9px] leading-relaxed text-slate-500">

                                        Tidak memilih file baru berarti
                                        lampiran saat ini tetap dipertahankan.

                                    </p>

                                </div>

                                {{-- =================================================
                                     INFO BATAS
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
                                            PDF
                                        </p>

                                        <p class="mt-0.5 text-[10px] font-bold text-slate-700">
                                            Tetap PDF
                                        </p>

                                    </div>

                                    <div class="rounded-md border border-slate-200 bg-slate-50 px-2.5 py-2">

                                        <p class="text-[8px] font-bold uppercase tracking-wide text-slate-400">
                                            Gambar
                                        </p>

                                        <p class="mt-0.5 text-[10px] font-bold text-slate-700">
                                            JPG / PNG → JPG
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
                                        class="mb-2.5 flex h-10 w-10 items-center justify-center rounded-md border-2 border-blue-200 bg-blue-100 text-blue-600 transition"
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
                                        Klik untuk memilih file baru
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
                                     COMPRESSION DETAIL
                                ================================================== --}}

                                <div
                                    id="compression-detail"
                                    class="mt-2 hidden overflow-hidden rounded-md border border-slate-200 bg-slate-50"
                                >

                                    <div class="grid grid-cols-1 sm:grid-cols-3">

                                        <div class="border-b border-slate-200 px-3 py-2 sm:border-b-0 sm:border-r">

                                            <p class="text-[8px] font-bold uppercase tracking-wide text-slate-400">
                                                Ukuran Asli
                                            </p>

                                            <p
                                                id="original-size"
                                                class="mt-0.5 text-[10px] font-bold text-slate-700"
                                            >
                                                -
                                            </p>

                                        </div>

                                        <div class="border-b border-slate-200 px-3 py-2 sm:border-b-0 sm:border-r">

                                            <p class="text-[8px] font-bold uppercase tracking-wide text-slate-400">
                                                Estimasi Hasil
                                            </p>

                                            <p
                                                id="compressed-size"
                                                class="mt-0.5 text-[10px] font-bold text-indigo-700"
                                            >
                                                -
                                            </p>

                                        </div>

                                        <div class="px-3 py-2">

                                            <p class="text-[8px] font-bold uppercase tracking-wide text-slate-400">
                                                Pengurangan
                                            </p>

                                            <p
                                                id="compression-percent"
                                                class="mt-0.5 text-[10px] font-bold text-emerald-700"
                                            >
                                                -
                                            </p>

                                        </div>

                                    </div>

                                </div>

                                {{-- =================================================
                                     STATUS KOMPRESI
                                ================================================== --}}

                                <div
                                    id="compression-status"
                                    class="mt-2 hidden rounded-md border border-amber-200 bg-amber-50 px-2.5 py-2 text-[9px] leading-relaxed text-amber-700"
                                ></div>

                                @error('lampiran_file')

                                    <p class="mt-1.5 text-[10px] font-medium text-rose-600">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                        </div>

                    </div>

                </section>

                {{-- =================================================
                     ERROR SUMMARY
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

                <a
                    href="{{ route('surat-keluar.index') }}"
                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border-2 border-slate-400 bg-white px-4 py-2 text-[10px] font-bold text-slate-700 shadow-sm transition hover:border-slate-500 hover:bg-slate-100 sm:w-auto"
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
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>

                    Batal

                </a>

                <button
                    type="submit"
                    id="submit-btn"
                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-md border-2 border-emerald-600 bg-emerald-600 px-4 py-2 text-[10px] font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto"
                >

                    <svg
                        id="submit-icon"
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

                    <svg
                        id="submit-loading"
                        class="hidden h-3.5 w-3.5 animate-spin"
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
                        />

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                        />
                    </svg>

                    <span id="submit-text">
                        Perbarui Surat Keluar
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

    /*
    |--------------------------------------------------------------------------
    | ELEMENT
    |--------------------------------------------------------------------------
    */

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

    const compressionDetail =
        document.getElementById('compression-detail');

    const compressionStatus =
        document.getElementById('compression-status');

    const originalSize =
        document.getElementById('original-size');

    const compressedSize =
        document.getElementById('compressed-size');

    const compressionPercent =
        document.getElementById('compression-percent');

    const uploadBox =
        document.getElementById('uploadBox');

    const uploadIcon =
        document.getElementById('uploadIcon');

    const currentFileNote =
        document.getElementById('current-file-note');

    const submitButton =
        document.getElementById('submit-btn');

    const submitIcon =
        document.getElementById('submit-icon');

    const submitLoading =
        document.getElementById('submit-loading');

    const submitText =
        document.getElementById('submit-text');

    const tanggalSurat =
        document.getElementById('tanggal_surat');

    const tanggalKeluar =
        document.getElementById('tanggal_keluar');

    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI
    |--------------------------------------------------------------------------
    */

    const MAX_FILE_SIZE =
        10 * 1024 * 1024;

    const ALLOWED_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png'
    ];

    const IMAGE_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png'
    ];

    let isSubmitting = false;

    /*
    |--------------------------------------------------------------------------
    | FORMAT UKURAN
    |--------------------------------------------------------------------------
    */

    function formatFileSize(bytes) {

        if (
            !Number.isFinite(bytes) ||
            bytes < 0
        ) {
            return '0 B';
        }

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
    | ALERT
    |--------------------------------------------------------------------------
    */

    function showAlert(
        icon,
        title,
        text
    ) {

        if (
            typeof window.Swal !==
            'undefined'
        ) {

            window.Swal.fire({
                icon: icon,
                title: title,
                text: text,
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#059669'
            });

            return;
        }

        window.alert(text);
    }

    /*
    |--------------------------------------------------------------------------
    | RESET VISUAL
    |--------------------------------------------------------------------------
    */

    function resetVisual() {

        if (fileLabelText) {

            fileLabelText.textContent =
                'Klik untuk memilih file baru';

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
                'text-indigo-600',
                'text-rose-600'
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
        }

        if (compressionInfo) {

            compressionInfo.textContent = '';

            compressionInfo.classList.add(
                'hidden'
            );
        }

        if (compressionDetail) {

            compressionDetail.classList.add(
                'hidden'
            );
        }

        if (compressionStatus) {

            compressionStatus.textContent = '';

            compressionStatus.classList.add(
                'hidden'
            );
        }

        if (originalSize) {
            originalSize.textContent = '-';
        }

        if (compressedSize) {
            compressedSize.textContent = '-';
        }

        if (compressionPercent) {
            compressionPercent.textContent = '-';
        }

        if (currentFileNote) {

            currentFileNote.innerHTML = `
                <p class="text-[9px] leading-relaxed text-slate-500">
                    Tidak memilih file baru berarti
                    lampiran saat ini tetap dipertahankan.
                </p>
            `;
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

            fileInfo.textContent = message;

            fileInfo.classList.remove(
                'hidden',
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
    | ESTIMASI KOMPRESI GAMBAR
    |--------------------------------------------------------------------------
    */

    function estimateImageCompression(file) {

        return new Promise(function (
            resolve,
            reject
        ) {

            const objectUrl =
                URL.createObjectURL(file);

            const image =
                new Image();

            image.onload = function () {

                try {

                    const MAX_WIDTH =
                        2500;

                    const MAX_HEIGHT =
                        2500;

                    const QUALITY =
                        0.78;

                    const scale =
                        Math.min(
                            MAX_WIDTH /
                                image.width,
                            MAX_HEIGHT /
                                image.height,
                            1
                        );

                    const width =
                        Math.max(
                            1,
                            Math.round(
                                image.width *
                                scale
                            )
                        );

                    const height =
                        Math.max(
                            1,
                            Math.round(
                                image.height *
                                scale
                            )
                        );

                    const canvas =
                        document.createElement(
                            'canvas'
                        );

                    canvas.width = width;
                    canvas.height = height;

                    const context =
                        canvas.getContext(
                            '2d'
                        );

                    if (!context) {

                        throw new Error(
                            'Canvas tidak tersedia.'
                        );
                    }

                    /*
                    |--------------------------------------------------------------
                    | Background putih
                    |--------------------------------------------------------------
                    */

                    context.fillStyle =
                        '#ffffff';

                    context.fillRect(
                        0,
                        0,
                        width,
                        height
                    );

                    /*
                    |--------------------------------------------------------------
                    | Gambar
                    |--------------------------------------------------------------
                    */

                    context.drawImage(
                        image,
                        0,
                        0,
                        width,
                        height
                    );

                    /*
                    |--------------------------------------------------------------
                    | JPEG
                    |--------------------------------------------------------------
                    */

                    canvas.toBlob(
                        function (blob) {

                            URL.revokeObjectURL(
                                objectUrl
                            );

                            if (!blob) {

                                reject(
                                    new Error(
                                        'Estimasi kompresi gagal.'
                                    )
                                );

                                return;
                            }

                            const reduction =
                                file.size > 0
                                    ? (
                                        (
                                            1 -
                                            (
                                                blob.size /
                                                file.size
                                            )
                                        ) *
                                        100
                                    )
                                    : 0;

                            resolve({

                                originalSize:
                                    file.size,

                                compressedSize:
                                    blob.size,

                                width:
                                    width,

                                height:
                                    height,

                                reduction:
                                    Math.max(
                                        0,
                                        reduction
                                    )
                            });

                        },
                        'image/jpeg',
                        QUALITY
                    );

                } catch (error) {

                    URL.revokeObjectURL(
                        objectUrl
                    );

                    reject(error);
                }
            };

            image.onerror = function () {

                URL.revokeObjectURL(
                    objectUrl
                );

                reject(
                    new Error(
                        'Gambar tidak dapat dibaca.'
                    )
                );
            };

            image.src =
                objectUrl;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | TAMPILKAN HASIL ESTIMASI
    |--------------------------------------------------------------------------
    */

    function showCompressionResult(
        result
    ) {

        if (compressionDetail) {

            compressionDetail.classList.remove(
                'hidden'
            );
        }

        if (originalSize) {

            originalSize.textContent =
                formatFileSize(
                    result.originalSize
                );
        }

        if (compressedSize) {

            compressedSize.textContent =
                formatFileSize(
                    result.compressedSize
                );
        }

        if (compressionPercent) {

            if (result.reduction > 0) {

                compressionPercent.textContent =
                    result.reduction.toFixed(1) +
                    '% lebih kecil';

            } else {

                compressionPercent.textContent =
                    'Tidak berkurang';
            }
        }

        if (compressionInfo) {

            compressionInfo.textContent =
                'Estimasi hasil kompresi: ' +
                formatFileSize(
                    result.originalSize
                ) +
                ' → ' +
                formatFileSize(
                    result.compressedSize
                ) +
                ' (' +
                result.reduction.toFixed(1) +
                '% lebih kecil).';

            compressionInfo.classList.remove(
                'hidden'
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
        async function () {

            if (
                !fileInput.files ||
                fileInput.files.length === 0
            ) {

                resetVisual();
                return;
            }

            resetVisual();

            const file =
                fileInput.files[0];

            const extension =
                file.name
                    .split('.')
                    .pop()
                    ?.toLowerCase() || '';

            /*
            |--------------------------------------------------------------------------
            | VALIDASI EXTENSION
            |--------------------------------------------------------------------------
            */

            if (
                !ALLOWED_EXTENSIONS.includes(
                    extension
                )
            ) {

                fileInput.value = '';

                showFileError(
                    'Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | VALIDASI SIZE
            |--------------------------------------------------------------------------
            */

            if (
                file.size >
                MAX_FILE_SIZE
            ) {

                fileInput.value = '';

                showFileError(
                    'Ukuran file melebihi batas 10 MB.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | FILE VALID
            |--------------------------------------------------------------------------
            */

            if (fileLabelText) {

                fileLabelText.textContent =
                    'File baru: ' +
                    file.name;

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

                fileInfo.textContent =
                    'Ukuran file asli: ' +
                    formatFileSize(
                        file.size
                    );
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
            | FILE BARU
            |--------------------------------------------------------------------------
            */

            if (currentFileNote) {

                currentFileNote.innerHTML = `
                    <p class="text-[9px] leading-relaxed text-blue-600">
                        File baru dipilih.
                        Setelah update berhasil,
                        file lama akan diganti dengan
                        file baru yang telah diproses.
                    </p>
                `;
            }

            /*
            |--------------------------------------------------------------------------
            | PDF
            |--------------------------------------------------------------------------
            */

            if (extension === 'pdf') {

                if (fileSubLabel) {

                    fileSubLabel.textContent =
                        'PDF • Disimpan sebagai PDF';

                    fileSubLabel.classList.remove(
                        'text-slate-500'
                    );

                    fileSubLabel.classList.add(
                        'text-indigo-600'
                    );
                }

                if (compressionInfo) {

                    compressionInfo.textContent =
                        'PDF tidak diproses sebagai gambar. File akan tetap disimpan dalam format PDF.';

                    compressionInfo.classList.remove(
                        'hidden'
                    );
                }

                if (compressionStatus) {

                    compressionStatus.textContent =
                        'Tidak ada kompresi gambar untuk file PDF.';

                    compressionStatus.classList.remove(
                        'hidden'
                    );

                    compressionStatus.classList.remove(
                        'border-amber-200',
                        'bg-amber-50',
                        'text-amber-700'
                    );

                    compressionStatus.classList.add(
                        'border-blue-200',
                        'bg-blue-50',
                        'text-blue-700'
                    );
                }

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | GAMBAR
            |--------------------------------------------------------------------------
            */

            if (
                IMAGE_EXTENSIONS.includes(
                    extension
                )
            ) {

                if (fileSubLabel) {

                    fileSubLabel.textContent =
                        'Gambar • Akan dikompres menjadi JPG';

                    fileSubLabel.classList.remove(
                        'text-slate-500'
                    );

                    fileSubLabel.classList.add(
                        'text-indigo-600'
                    );
                }

                if (compressionStatus) {

                    compressionStatus.textContent =
                        'Menghitung estimasi hasil kompresi...';

                    compressionStatus.classList.remove(
                        'hidden'
                    );
                }

                try {

                    const result =
                        await estimateImageCompression(
                            file
                        );

                    /*
                    |------------------------------------------------------------------
                    | Pastikan user belum mengganti file
                    |------------------------------------------------------------------
                    */

                    if (
                        !fileInput.files ||
                        fileInput.files.length === 0 ||
                        fileInput.files[0] !== file
                    ) {
                        return;
                    }

                    showCompressionResult(
                        result
                    );

                    if (compressionStatus) {

                        compressionStatus.textContent =
                            'Estimasi selesai. Ukuran hasil aktual dapat sedikit berbeda karena kompresi final dilakukan oleh server.';

                        compressionStatus.classList.remove(
                            'border-amber-200',
                            'bg-amber-50',
                            'text-amber-700'
                        );

                        compressionStatus.classList.add(
                            'border-blue-200',
                            'bg-blue-50',
                            'text-blue-700'
                        );
                    }

                } catch (error) {

                    if (compressionInfo) {

                        compressionInfo.textContent =
                            'Estimasi tidak dapat dihitung di browser. Kompresi final tetap akan dilakukan oleh server.';

                        compressionInfo.classList.remove(
                            'hidden'
                        );
                    }

                    if (compressionStatus) {

                        compressionStatus.textContent =
                            'File gambar valid dan siap diproses oleh server.';

                        compressionStatus.classList.remove(
                            'hidden'
                        );
                    }
                }
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
            | TANGGAL
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

                showAlert(
                    'warning',
                    'Tanggal tidak valid',
                    'Tanggal keluar tidak boleh lebih awal dari tanggal surat.'
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | FILE
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

                    showAlert(
                        'warning',
                        'Format file tidak didukung',
                        'Gunakan PDF, JPG, JPEG, atau PNG.'
                    );

                    return;
                }

                if (
                    file.size >
                    MAX_FILE_SIZE
                ) {

                    event.preventDefault();

                    showAlert(
                        'warning',
                        'File terlalu besar',
                        'Ukuran lampiran maksimal 10 MB.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | STATUS PROSES
                |--------------------------------------------------------------------------
                */

                if (compressionStatus) {

                    compressionStatus.classList.remove(
                        'hidden'
                    );

                    compressionStatus.classList.remove(
                        'border-blue-200',
                        'bg-blue-50',
                        'text-blue-700'
                    );

                    compressionStatus.classList.add(
                        'border-amber-200',
                        'bg-amber-50',
                        'text-amber-700'
                    );

                    if (
                        IMAGE_EXTENSIONS.includes(
                            extension
                        )
                    ) {

                        compressionStatus.textContent =
                            'File sedang dikirim. Server akan melakukan resize dan kompresi final sebelum menyimpan file baru.';

                    } else {

                        compressionStatus.textContent =
                            'File PDF sedang dikirim ke server untuk disimpan.';
                    }
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

                submitButton.classList.add(
                    'opacity-70'
                );
            }

            if (submitIcon) {

                submitIcon.classList.add(
                    'hidden'
                );
            }

            if (submitLoading) {

                submitLoading.classList.remove(
                    'hidden'
                );
            }

            if (submitText) {

                submitText.textContent =
                    'Memperbarui...';
            }
        }
    );

});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endpush

@endsection