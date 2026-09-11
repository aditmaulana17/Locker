@extends('layouts.app')

@section('title', 'Detail Disposisi - Arsip Surat')

@section('content')

@php
    $statusClasses = [
        'menunggu' => 'status-waiting',
        'diproses' => 'status-processing',
        'proses'   => 'status-processing',
        'selesai'  => 'status-done',
    ];

    $st = strtolower(trim((string) ($disposisi->status ?? 'menunggu')));

    $statusLabel = [
        'menunggu' => 'Menunggu',
        'diproses' => 'Diproses',
        'proses'   => 'Diproses',
        'selesai'  => 'Selesai',
    ][$st] ?? ucfirst($st);

    $isAdmin = auth()->user()->isAdmin();
    $isSender = auth()->id() === $disposisi->dari_user_id;
    $isReceiver = auth()->id() === $disposisi->kepada_user_id;
@endphp

<style>
    /* =========================================================
       PAGE
    ========================================================= */

    .disposisi-detail-page {
        width: 100%;
        max-width: 1120px;
        margin: 0 auto;
        padding: 12px 16px 32px;
        color: #334155;
    }

    .disposisi-detail-page *,
    .disposisi-detail-page *::before,
    .disposisi-detail-page *::after {
        box-sizing: border-box;
    }

    /* =========================================================
       FLASH
    ========================================================= */

    .flash-success {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        padding: 10px 13px;
        border: 2px solid #86efac;
        border-radius: 10px;
        background: #f0fdf4;
        color: #166534;
    }

    .flash-success-icon {
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #86efac;
        border-radius: 8px;
        background: #dcfce7;
        color: #16a34a;
    }

    .flash-success-text {
        margin: 0;
        font-size: 11px;
        line-height: 1.45;
        font-weight: 700;
    }

    /* =========================================================
       HEADER
    ========================================================= */

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
        padding: 13px 15px;
        border: 2px solid #64748b;
        border-radius: 11px;
        background: #ffffff;
        box-shadow: 0 3px 8px rgba(15, 23, 42, .05);
    }

    .page-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .page-back {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
        color: #475569;
        text-decoration: none;
        transition: .15s ease;
    }

    .page-back:hover {
        border-color: #94a3b8;
        background: #f1f5f9;
        color: #1e293b;
    }

    .page-header-content {
        min-width: 0;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 2px;
        font-size: 9px;
        color: #94a3b8;
    }

    .breadcrumb a {
        color: #64748b;
        text-decoration: none;
    }

    .breadcrumb a:hover {
        color: #334155;
    }

    .page-title {
        margin: 0;
        font-size: 18px;
        line-height: 1.25;
        font-weight: 800;
        letter-spacing: -.02em;
        color: #0f172a;
    }

    .page-subtitle {
        margin: 3px 0 0;
        font-size: 10px;
        line-height: 1.45;
        color: #64748b;
    }

    .page-actions {
        display: flex;
        align-items: center;
        gap: 7px;
        flex: 0 0 auto;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 0 11px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: .15s ease;
    }

    .action-btn-secondary {
        border: 2px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
    }

    .action-btn-secondary:hover {
        border-color: #94a3b8;
        background: #f1f5f9;
    }

    .action-btn-edit {
        border: 2px solid #d97706;
        background: #f59e0b;
        color: #ffffff;
        box-shadow: 0 2px 5px rgba(245, 158, 11, .15);
    }

    .action-btn-edit:hover {
        background: #d97706;
    }

    /* =========================================================
       MAIN LAYOUT
    ========================================================= */

    .content-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(300px, .9fr);
        gap: 14px;
        align-items: start;
    }

    /* =========================================================
       CARD
    ========================================================= */

    .card {
        overflow: hidden;
        border: 2px solid #64748b;
        border-radius: 11px;
        background: #ffffff;
        box-shadow:
            0 3px 8px rgba(15, 23, 42, .05),
            0 12px 24px rgba(15, 23, 42, .025);
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 11px 13px;
        border-bottom: 2px solid #94a3b8;
        background: #f8fafc;
    }

    .card-header-title {
        margin: 0;
        font-size: 10px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #475569;
    }

    /* =========================================================
       STATUS
    ========================================================= */

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 25px;
        padding: 0 9px;
        border-radius: 999px;
        border: 1.5px solid;
        font-size: 9px;
        font-weight: 800;
        text-transform: capitalize;
        white-space: nowrap;
    }

    .status-waiting {
        border-color: #fcd34d;
        background: #fffbeb;
        color: #b45309;
    }

    .status-processing {
        border-color: #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .status-done {
        border-color: #86efac;
        background: #f0fdf4;
        color: #15803d;
    }

    /* =========================================================
       PRIMARY DETAIL BODY
    ========================================================= */

    .primary-body {
        padding: 13px;
    }

    /* =========================================================
       META TABLE
    ========================================================= */

    .meta-table {
        overflow: hidden;
        border: 2px solid #64748b;
        border-radius: 8px;
    }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .meta-item {
        min-width: 0;
        padding: 10px;
        border-right: 2px solid #94a3b8;
        border-bottom: 2px solid #94a3b8;
        background: #ffffff;
    }

    .meta-item:nth-child(2n) {
        border-right: 0;
    }

    .meta-item:nth-last-child(-n + 2) {
        border-bottom: 0;
    }

    .meta-label {
        display: block;
        margin-bottom: 4px;
        font-size: 8px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #94a3b8;
    }

    .meta-value {
        margin: 0;
        font-size: 11px;
        line-height: 1.45;
        font-weight: 750;
        color: #1e293b;
        word-break: break-word;
    }

    .meta-secondary {
        margin: 2px 0 0;
        font-size: 9px;
        line-height: 1.35;
        color: #64748b;
    }

    /* =========================================================
       INSTRUCTION
    ========================================================= */

    .section-block {
        margin-top: 13px;
    }

    .section-label {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 6px;
    }

    .section-marker {
        width: 4px;
        height: 18px;
        flex: 0 0 4px;
        border-radius: 999px;
        background: #4f46e5;
    }

    .section-label-text {
        margin: 0;
        font-size: 10px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #334155;
    }

    .instruction-box {
        min-height: 95px;
        padding: 10px 11px;
        border: 2px solid #94a3b8;
        border-radius: 8px;
        background: #f8fafc;
        color: #334155;
        font-size: 10px;
        line-height: 1.65;
        font-weight: 550;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    /* =========================================================
       STATUS UPDATE
    ========================================================= */

    .status-update-box {
        margin-top: 13px;
        padding-top: 13px;
        border-top: 2px solid #94a3b8;
    }

    .status-form {
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .status-select {
        flex: 1;
        min-width: 0;
        height: 35px;
        padding: 0 9px;
        border: 2px solid #94a3b8;
        border-radius: 8px;
        background: #ffffff;
        color: #334155;
        font-size: 10px;
        font-weight: 700;
        outline: none;
        transition: .15s ease;
    }

    .status-select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, .08);
    }

    .status-submit {
        min-height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 11px;
        border: 2px solid #4338ca;
        border-radius: 8px;
        background: #4f46e5;
        color: #ffffff;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
        transition: .15s ease;
        white-space: nowrap;
    }

    .status-submit:hover {
        background: #4338ca;
    }

    /* =========================================================
       RELATED LETTER CARD
    ========================================================= */

    .related-header {
        padding: 12px 13px;
        border-bottom: 2px solid #334155;
        background: linear-gradient(
            135deg,
            #0f172a,
            #312e81
        );
        color: #ffffff;
    }

    .related-header-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
    }

    .related-title {
        margin: 0;
        font-size: 10px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #c7d2fe;
    }

    .related-agenda {
        display: inline-flex;
        align-items: center;
        min-height: 22px;
        padding: 0 7px;
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 6px;
        background: rgba(255,255,255,.08);
        color: #e0e7ff;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 8px;
        font-weight: 800;
        white-space: nowrap;
    }

    .related-body {
        padding: 13px;
    }

    .related-item {
        padding: 10px;
        border: 2px solid #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
    }

    .related-item + .related-item {
        margin-top: 8px;
    }

    .related-label {
        display: block;
        margin-bottom: 3px;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #94a3b8;
    }

    .related-value {
        margin: 0;
        font-size: 10px;
        line-height: 1.5;
        font-weight: 750;
        color: #1e293b;
        overflow-wrap: anywhere;
    }

    .related-detail-btn {
        width: 100%;
        min-height: 36px;
        margin-top: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 2px solid #334155;
        border-radius: 8px;
        background: #1e293b;
        color: #ffffff;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        transition: .15s ease;
    }

    .related-detail-btn:hover {
        background: #0f172a;
    }

    .empty-related {
        padding: 16px 10px;
        text-align: center;
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
    }

    .empty-related p {
        margin: 0;
        font-size: 10px;
        line-height: 1.5;
        color: #94a3b8;
        font-style: italic;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 900px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 680px) {
        .disposisi-detail-page {
            padding: 8px 10px 20px;
        }

        .page-header {
            align-items: stretch;
            flex-direction: column;
        }

        .page-actions {
            width: 100%;
        }

        .action-btn {
            flex: 1;
        }

        .meta-grid {
            grid-template-columns: 1fr;
        }

        .meta-item,
        .meta-item:nth-child(2n),
        .meta-item:nth-last-child(-n + 2) {
            border-right: 0;
            border-bottom: 2px solid #94a3b8;
        }

        .meta-item:last-child {
            border-bottom: 0;
        }

        .status-form {
            flex-direction: column;
            align-items: stretch;
        }

        .status-select,
        .status-submit {
            width: 100%;
        }
    }

    @media (max-width: 450px) {
        .page-header-left {
            align-items: flex-start;
        }

        .page-title {
            font-size: 16px;
        }

        .page-subtitle {
            font-size: 9px;
        }

        .page-actions {
            flex-direction: column;
        }

        .action-btn {
            width: 100%;
        }

        .related-header-top {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>


<div class="disposisi-detail-page">

    {{-- =========================================================
         FLASH SUCCESS
    ========================================================== --}}
    @if(session('success'))
        <div class="flash-success">

            <div class="flash-success-icon">
                <svg
                    class="w-4 h-4"
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

            <p class="flash-success-text">
                {{ session('success') }}
            </p>

        </div>
    @endif


    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <div class="page-header">

        <div class="page-header-left">

            <a
                href="{{ route('disposisi.index') }}"
                class="page-back"
                title="Kembali ke daftar disposisi"
            >
                <svg
                    class="w-4 h-4"
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
            </a>


            <div class="page-header-content">

                <div class="breadcrumb">
                    <a href="{{ route('disposisi.index') }}">
                        Disposisi
                    </a>

                    <span>/</span>

                    <span>
                        Detail
                    </span>
                </div>

                <h1 class="page-title">
                    Detail Disposisi Surat
                </h1>

                <p class="page-subtitle">
                    Informasi lengkap disposisi dan surat masuk yang terkait.
                </p>

            </div>

        </div>


        <div class="page-actions">

            <a
                href="{{ route('disposisi.index') }}"
                class="action-btn action-btn-secondary"
            >
                <svg
                    class="w-3.5 h-3.5"
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


            @if($isAdmin || $isSender)

                <a
                    href="{{ route('disposisi.edit', $disposisi) }}"
                    class="action-btn action-btn-edit"
                >
                    <svg
                        class="w-3.5 h-3.5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                        />
                    </svg>

                    Edit Disposisi
                </a>

            @endif

        </div>

    </div>


    {{-- =========================================================
         MAIN GRID
    ========================================================== --}}
    <div class="content-grid">


        {{-- =====================================================
             LEFT / PRIMARY
        ====================================================== --}}
        <div class="card">

            <div class="card-header">

                <p class="card-header-title">
                    Informasi Disposisi
                </p>


                <span class="status-badge {{ $statusClasses[$st] ?? 'status-processing' }}">
                    {{ $statusLabel }}
                </span>

            </div>


            <div class="primary-body">

                {{-- =================================================
                     META TABLE
                ================================================== --}}
                <div class="meta-table">

                    <div class="meta-grid">


                        {{-- PENGIRIM --}}
                        <div class="meta-item">

                            <span class="meta-label">
                                Pengirim Disposisi
                            </span>

                            <p class="meta-value">
                                {{ $disposisi->dari?->name
                                    ?? $disposisi->pengirim
                                    ?? '-' }}
                            </p>

                            @if($disposisi->dari?->jabatan)

                                <p class="meta-secondary">
                                    {{ $disposisi->dari->jabatan }}
                                </p>

                            @endif

                        </div>


                        {{-- PENERIMA --}}
                        <div class="meta-item">

                            <span class="meta-label">
                                Penerima Disposisi
                            </span>

                            <p class="meta-value">
                                {{ $disposisi->kepada?->name
                                    ?? $disposisi->penerima
                                    ?? $disposisi->tujuan
                                    ?? '-' }}
                            </p>

                            @if($disposisi->kepada?->jabatan)

                                <p class="meta-secondary">
                                    {{ $disposisi->kepada->jabatan }}
                                </p>

                            @endif

                        </div>


                        {{-- BATAS WAKTU --}}
                        <div class="meta-item">

                            <span class="meta-label">
                                Batas Waktu
                            </span>

                            <p class="meta-value">

                                @if($disposisi->batas_waktu)

                                    {{ \Carbon\Carbon::parse(
                                        $disposisi->batas_waktu
                                    )->translatedFormat('d F Y') }}

                                @else

                                    -

                                @endif

                            </p>

                        </div>


                        {{-- TANGGAL --}}
                        <div class="meta-item">

                            <span class="meta-label">
                                Tanggal Dibuat
                            </span>

                            <p class="meta-value">

                                @if($disposisi->created_at)

                                    {{ $disposisi->created_at->translatedFormat(
                                        'd F Y H:i'
                                    ) }}

                                @else

                                    -

                                @endif

                            </p>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     INSTRUKSI
                ================================================== --}}
                <div class="section-block">

                    <div class="section-label">

                        <div class="section-marker"></div>

                        <p class="section-label-text">
                            Instruksi & Catatan Penanganan
                        </p>

                    </div>


                    <div class="instruction-box">
                        {{ $disposisi->instruksi
                            ?? $disposisi->catatan
                            ?? $disposisi->isi_disposisi
                            ?? 'Tidak ada instruksi atau catatan khusus.' }}
                    </div>

                </div>


                {{-- =================================================
                     UPDATE STATUS
                ================================================== --}}
                @if($isAdmin || $isReceiver)

                    <div class="status-update-box">

                        <div class="section-label">

                            <div
                                class="section-marker"
                                style="background:#7c3aed;"
                            ></div>

                            <p class="section-label-text">
                                Perbarui Status Pekerjaan
                            </p>

                        </div>


                        <form
                            action="{{ route('disposisi.status', $disposisi) }}"
                            method="POST"
                            class="status-form"
                        >
                            @csrf
                            @method('PATCH')

                            <select
                                name="status"
                                class="status-select"
                            >

                                <option
                                    value="menunggu"
                                    @selected($st === 'menunggu')
                                >
                                    Menunggu
                                </option>

                                <option
                                    value="diproses"
                                    @selected(
                                        $st === 'diproses' ||
                                        $st === 'proses'
                                    )
                                >
                                    Diproses
                                </option>

                                <option
                                    value="selesai"
                                    @selected($st === 'selesai')
                                >
                                    Selesai
                                </option>

                            </select>


                            <button
                                type="submit"
                                class="status-submit"
                            >

                                <svg
                                    class="w-3.5 h-3.5"
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

                                Simpan Status

                            </button>

                        </form>

                    </div>

                @endif

            </div>

        </div>


        {{-- =====================================================
             RIGHT / RELATED LETTER
        ====================================================== --}}
        <div class="card">

            <div class="related-header">

                <div class="related-header-top">

                    <h2 class="related-title">
                        Surat Masuk Terkait
                    </h2>


                    @if($disposisi->suratMasuk)

                        <span class="related-agenda">

                            #{{ $disposisi->suratMasuk->nomor_agenda
                                ? 'AG/' . $disposisi->suratMasuk->nomor_agenda
                                : $disposisi->suratMasuk->id }}

                        </span>

                    @endif

                </div>

            </div>


            <div class="related-body">

                @if($disposisi->suratMasuk)

                    {{-- PERIHAL --}}
                    <div class="related-item">

                        <span class="related-label">
                            Perihal
                        </span>

                        <p class="related-value">
                            {{ $disposisi->suratMasuk->perihal
                                ?? 'Tanpa Perihal' }}
                        </p>

                    </div>


                    {{-- NOMOR SURAT --}}
                    <div class="related-item">

                        <span class="related-label">
                            Nomor Surat
                        </span>

                        <p class="related-value">
                            {{ $disposisi->suratMasuk->nomor_surat ?? '-' }}
                        </p>

                    </div>


                    {{-- PENGIRIM --}}
                    <div class="related-item">

                        <span class="related-label">
                            Asal / Pengirim Surat
                        </span>

                        <p class="related-value">
                            {{ $disposisi->suratMasuk->pengirim
                                ?? $disposisi->suratMasuk->instansi?->nama_instansi
                                ?? '-' }}
                        </p>

                    </div>


                    {{-- DETAIL --}}
                    <a
                        href="{{ route(
                            'surat-masuk.show',
                            $disposisi->suratMasuk
                        ) }}"
                        class="related-detail-btn"
                    >

                        Lihat Detail Surat

                        <svg
                            class="w-3.5 h-3.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                            />
                        </svg>

                    </a>

                @else

                    <div class="empty-related">

                        <p>
                            Data surat masuk terkait tidak ditemukan.
                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>

</div>

@endsection