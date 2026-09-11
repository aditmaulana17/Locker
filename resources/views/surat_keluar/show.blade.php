@extends('layouts.app')

@section('title', 'Detail Surat Keluar')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    $status = strtolower(
        trim(
            (string) ($suratKeluar->status ?? 'draft')
        )
    );

    if ($status === 'draf') {
        $status = 'draft';
    }

    $statusLabels = [
        'draft'      => 'Draft',
        'diproses'   => 'Diproses',
        'disetujui'  => 'Disetujui',
        'dikirim'    => 'Dikirim',
        'diarsipkan' => 'Diarsipkan',
    ];

    $statusClasses = [
        'draft'      => 'status-draft',
        'diproses'   => 'status-processing',
        'disetujui'  => 'status-approved',
        'dikirim'    => 'status-sent',
        'diarsipkan' => 'status-archived',
    ];

    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
    $statusClass = $statusClasses[$status] ?? 'status-draft';


    /*
    |--------------------------------------------------------------------------
    | TANGGAL
    |--------------------------------------------------------------------------
    */

    $tanggalSurat = '-';
    $tanggalKeluar = '-';

    try {
        if ($suratKeluar->tanggal_surat) {
            $tanggalSurat = \Illuminate\Support\Carbon::parse(
                $suratKeluar->tanggal_surat
            )->format('d/m/Y');
        }
    } catch (\Throwable $e) {
        $tanggalSurat = '-';
    }

    try {
        if ($suratKeluar->tanggal_keluar) {
            $tanggalKeluar = \Illuminate\Support\Carbon::parse(
                $suratKeluar->tanggal_keluar
            )->format('d/m/Y');
        }
    } catch (\Throwable $e) {
        $tanggalKeluar = '-';
    }


    /*
    |--------------------------------------------------------------------------
    | INFORMASI SURAT
    |--------------------------------------------------------------------------
    */

    $tujuanSurat = trim(
        (string) ($suratKeluar->pengirim ?? '')
    );

    if ($tujuanSurat === '') {
        $tujuanSurat = '-';
    }

    $nomorSurat = trim(
        (string) ($suratKeluar->nomor_surat ?? '')
    );

    if ($nomorSurat === '') {
        $nomorSurat = '-';
    }

    $perihal = trim(
        (string) ($suratKeluar->perihal ?? '')
    );

    if ($perihal === '') {
        $perihal = 'Tanpa Perihal';
    }

    $ringkasan = trim(
        (string) ($suratKeluar->ringkasan ?? '')
    );

    $kategoriNama =
        $suratKeluar->kategori?->nama_kategori ?? '-';

    $pembuatNama =
        $suratKeluar->pembuat?->name ?? '-';


    /*
    |--------------------------------------------------------------------------
    | LAMPIRAN
    |--------------------------------------------------------------------------
    */

    $lampiranPath =
        $suratKeluar->lampiran_file ?? null;

    $lampiranUrl = null;

    if (!empty($lampiranPath)) {

        if (
            filter_var(
                $lampiranPath,
                FILTER_VALIDATE_URL
            )
        ) {

            $lampiranUrl = $lampiranPath;

        } elseif (
            \Illuminate\Support\Facades\Route::has(
                'surat-keluar.preview-lampiran'
            )
        ) {

            $lampiranUrl = route(
                'surat-keluar.preview-lampiran',
                $suratKeluar
            );
        }
    }

    $lampiranExtension =
        !empty($lampiranPath)
            ? strtolower(
                pathinfo(
                    $lampiranPath,
                    PATHINFO_EXTENSION
                )
            )
            : '';

    $lampiranNama =
        !empty($lampiranPath)
            ? basename($lampiranPath)
            : null;
@endphp


<style>
    /* =========================================================
       PAGE
    ========================================================== */

    .surat-keluar-detail-page {
        width: 100%;
        max-width: 1120px;
        margin: 0 auto;
        padding: 10px 16px 30px;
        color: #334155;
    }

    .surat-keluar-detail-page *,
    .surat-keluar-detail-page *::before,
    .surat-keluar-detail-page *::after {
        box-sizing: border-box;
    }


    /* =========================================================
       ALERT
    ========================================================== */

    .detail-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 12px;
        padding: 10px 12px;
        border: 2px solid;
        border-radius: 9px;
    }

    .detail-alert-inner {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .detail-alert-icon {
        width: 29px;
        height: 29px;
        flex: 0 0 29px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
    }

    .detail-alert-text {
        margin: 0;
        font-size: 10px;
        line-height: 1.45;
        font-weight: 700;
    }

    .alert-success {
        border-color: #86efac;
        background: #f0fdf4;
        color: #166534;
    }

    .alert-success .detail-alert-icon {
        background: #dcfce7;
        color: #16a34a;
    }

    .alert-error {
        border-color: #fca5a5;
        background: #fff1f2;
        color: #991b1b;
    }

    .alert-error .detail-alert-icon {
        background: #fee2e2;
        color: #dc2626;
    }

    .detail-alert-close {
        width: 27px;
        height: 27px;
        flex: 0 0 27px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 6px;
        background: transparent;
        cursor: pointer;
    }

    .alert-success .detail-alert-close {
        color: #4ade80;
    }

    .alert-error .detail-alert-close {
        color: #f87171;
    }

    .detail-alert-close:hover {
        background: rgba(15, 23, 42, .05);
    }


    /* =========================================================
       HEADER
    ========================================================== */

    .detail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 13px;
        padding: 13px 15px;
        border: 2px solid #334155;
        border-radius: 11px;
        background: linear-gradient(
            135deg,
            #0f172a,
            #1e293b,
            #064e3b
        );
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(15, 23, 42, .10);
    }

    .detail-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .detail-header-back {
        width: 35px;
        height: 35px;
        flex: 0 0 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 8px;
        background: rgba(255,255,255,.08);
        color: #ffffff;
        text-decoration: none;
        transition: .15s ease;
    }

    .detail-header-back:hover {
        background: rgba(255,255,255,.16);
    }

    .detail-header-content {
        min-width: 0;
    }

    .detail-breadcrumb {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 2px;
        font-size: 8px;
        line-height: 1.3;
        color: #94a3b8;
    }

    .detail-breadcrumb a {
        color: #cbd5e1;
        text-decoration: none;
    }

    .detail-breadcrumb a:hover {
        color: #ffffff;
    }

    .detail-header-title {
        margin: 0;
        font-size: 18px;
        line-height: 1.25;
        font-weight: 800;
        letter-spacing: -.02em;
    }

    .detail-header-subtitle {
        margin: 3px 0 0;
        font-size: 9px;
        line-height: 1.45;
        color: #cbd5e1;
    }

    .detail-header-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 35px;
        padding: 0 11px;
        border: 1px solid rgba(245,158,11,.35);
        border-radius: 8px;
        background: rgba(245,158,11,.16);
        color: #fde68a;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: .15s ease;
    }

    .detail-header-action:hover {
        background: rgba(245,158,11,.25);
    }


    /* =========================================================
       MAIN CARD
    ========================================================== */

    .main-card {
        overflow: hidden;
        border: 2px solid #475569;
        border-radius: 11px;
        background: #ffffff;
        box-shadow: 0 4px 10px rgba(15, 23, 42, .06);
    }


    /* =========================================================
       HEADER SURAT
    ========================================================== */

    .letter-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 15px;
        border-bottom: 2px solid #64748b;
        background: linear-gradient(
            to right,
            #f8fafc,
            #ffffff
        );
    }

    .letter-heading-content {
        min-width: 0;
        flex: 1;
    }

    .letter-eyebrow {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 5px;
    }

    .letter-dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #10b981;
        box-shadow: 0 0 0 3px #d1fae5;
    }

    .letter-eyebrow-text {
        font-size: 8px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #047857;
    }

    .letter-title {
        margin: 0;
        font-size: 18px;
        line-height: 1.4;
        font-weight: 850;
        letter-spacing: -.02em;
        color: #0f172a;
        overflow-wrap: anywhere;
    }

    .letter-meta-line {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        margin-top: 7px;
    }

    .letter-id {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 0 8px;
        border: 1px solid #6ee7b7;
        border-radius: 6px;
        background: #ecfdf5;
        color: #047857;
        font-family:
            ui-monospace,
            SFMono-Regular,
            Menlo,
            Monaco,
            Consolas,
            monospace;
        font-size: 8px;
        font-weight: 800;
    }

    .letter-number {
        font-size: 9px;
        line-height: 1.4;
        color: #64748b;
        overflow-wrap: anywhere;
    }

    .letter-number strong {
        color: #1e293b;
    }


    /* =========================================================
       STATUS
    ========================================================== */

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 0 10px;
        border: 2px solid;
        border-radius: 999px;
        font-size: 9px;
        line-height: 1;
        font-weight: 800;
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .status-draft {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #475569;
    }

    .status-processing {
        border-color: #fcd34d;
        background: #fffbeb;
        color: #b45309;
    }

    .status-approved {
        border-color: #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .status-sent {
        border-color: #86efac;
        background: #f0fdf4;
        color: #15803d;
    }

    .status-archived {
        border-color: #c4b5fd;
        background: #f5f3ff;
        color: #6d28d9;
    }


    /* =========================================================
       BODY
    ========================================================== */

    .main-body {
        padding: 14px;
    }


    /* =========================================================
       META TABLE
    ========================================================== */

    .meta-table {
        width: 100%;
        overflow: hidden;
        border: 2px solid #475569;
        border-radius: 9px;
        background: #ffffff;
    }

    .meta-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        width: 100%;
    }

    .meta-item {
        min-width: 0;
        min-height: 66px;
        padding: 10px 12px;
        background: #ffffff;
        border-right: 2px solid #94a3b8;
        border-bottom: 2px solid #94a3b8;
    }

    /* -----------------------------------------
       BARIS PERTAMA
       4 kolom sama besar
    ------------------------------------------ */

    .meta-item:last-child {
        border-right: 0;
    }

    .meta-item:nth-child(4) {
        border-right: 0;
    }


    /* -----------------------------------------
       DIBUAT OLEH
       Full width agar tidak ada ruang kosong
    ------------------------------------------ */

    .meta-created {
        grid-column: 1 / -1;
        border-right: 0;
        border-bottom: 0;
        min-height: 58px;
        background: #f8fafc;
    }


    /* -----------------------------------------
       LABEL
    ------------------------------------------ */

    .meta-label {
        display: block;
        margin-bottom: 5px;
        font-size: 8px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #64748b;
    }


    /* -----------------------------------------
       VALUE
    ------------------------------------------ */

    .meta-value {
        margin: 0;
        font-size: 10px;
        line-height: 1.5;
        font-weight: 750;
        color: #1e293b;
        overflow-wrap: anywhere;
    }


    /* -----------------------------------------
       TANGGAL
    ------------------------------------------ */

    .meta-date {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .meta-date-icon {
        width: 13px;
        height: 13px;
        flex: 0 0 13px;
        color: #475569;
    }


    /* =========================================================
       SECTION
    ========================================================== */

    .detail-section {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 2px solid #64748b;
    }

    .section-heading {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 7px;
    }

    .section-marker {
        width: 4px;
        height: 19px;
        border-radius: 999px;
        background: #059669;
    }

    .section-heading-text {
        margin: 0;
        font-size: 10px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #334155;
    }


    /* =========================================================
       CONTENT
    ========================================================== */

    .content-box {
        padding: 10px 11px;
        border: 2px solid #94a3b8;
        border-radius: 8px;
        background: #f8fafc;
    }

    .content-box-text {
        margin: 0;
        font-size: 10px;
        line-height: 1.65;
        font-weight: 550;
        color: #334155;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .summary-box {
        border-color: #94a3b8;
        background: #ffffff;
    }


    /* =========================================================
       ATTACHMENT
    ========================================================== */

    .attachment-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }

    .attachment-title {
        margin: 0;
        font-size: 10px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #334155;
    }

    .attachment-filename {
        margin: 3px 0 0;
        font-size: 8px;
        line-height: 1.4;
        color: #64748b;
        overflow-wrap: anywhere;
    }

    .attachment-extension {
        display: inline-flex;
        align-items: center;
        min-height: 23px;
        padding: 0 7px;
        border: 1px solid #94a3b8;
        border-radius: 6px;
        background: #f8fafc;
        color: #475569;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .attachment-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-bottom: 9px;
    }

    .attachment-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 33px;
        padding: 0 10px;
        border-radius: 7px;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
        transition: .15s ease;
    }

    .attachment-btn-primary {
        border: 2px solid #047857;
        background: #059669;
        color: #ffffff;
    }

    .attachment-btn-primary:hover {
        background: #047857;
    }

    .attachment-btn-secondary {
        border: 2px solid #94a3b8;
        background: #ffffff;
        color: #475569;
    }

    .attachment-btn-secondary:hover {
        border-color: #64748b;
        background: #f8fafc;
    }


    /* =========================================================
       VIEWER
    ========================================================== */

    .viewer {
        overflow: hidden;
        border: 2px solid #475569;
        border-radius: 8px;
        background: #0f172a;
        padding: 7px;
    }

    .viewer-image-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 180px;
        border-radius: 6px;
        background: #111827;
    }

    .viewer-image {
        display: block;
        width: auto;
        max-width: 100%;
        max-height: 540px;
        object-fit: contain;
    }

    .viewer-pdf {
        width: 100%;
        height: 560px;
        display: block;
        border: 0;
        border-radius: 6px;
        background: #ffffff;
    }

    .viewer-empty {
        padding: 35px 15px;
        border-radius: 6px;
        background: #ffffff;
        text-align: center;
    }

    .viewer-empty-title {
        margin: 7px 0 0;
        font-size: 10px;
        font-weight: 800;
        color: #334155;
    }

    .viewer-empty-text {
        margin: 3px 0 0;
        font-size: 8px;
        line-height: 1.5;
        color: #64748b;
    }

    .viewer-empty code {
        padding: 2px 4px;
        border-radius: 4px;
        background: #f1f5f9;
        color: #1e293b;
        font-size: 8px;
        font-weight: 800;
    }


    /* =========================================================
       NO FILE
    ========================================================== */

    .no-file {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 11px;
        border: 2px dashed #94a3b8;
        border-radius: 8px;
        background: #f8fafc;
        color: #64748b;
    }

    .no-file p {
        margin: 0;
        font-size: 9px;
        line-height: 1.45;
        font-weight: 600;
    }


    /* =========================================================
       TABLET
    ========================================================== */

    @media (max-width: 950px) {

        .meta-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .meta-item {
            border-right: 2px solid #94a3b8;
            border-bottom: 2px solid #94a3b8;
        }

        /* Kolom kanan */

        .meta-item:nth-child(2),
        .meta-item:nth-child(4) {
            border-right: 0;
        }

        /* Dibuat Oleh full width */

        .meta-created {
            grid-column: 1 / -1;
            border-right: 0;
            border-bottom: 0;
            background: #f8fafc;
        }
    }


    /* =========================================================
       MOBILE
    ========================================================== */

    @media (max-width: 700px) {

        .surat-keluar-detail-page {
            padding: 8px 10px 20px;
        }

        .detail-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .detail-header-action {
            width: 100%;
        }

        .letter-heading {
            flex-direction: column;
        }

        .status-badge {
            align-self: flex-start;
        }

        .main-body {
            padding: 11px;
        }
    }


    @media (max-width: 560px) {

        .detail-header-left {
            align-items: flex-start;
        }

        .detail-header-title {
            font-size: 16px;
        }

        .detail-header-subtitle {
            font-size: 8px;
        }

        .meta-grid {
            grid-template-columns: 1fr;
        }

        .meta-item,
        .meta-item:nth-child(1),
        .meta-item:nth-child(2),
        .meta-item:nth-child(3),
        .meta-item:nth-child(4),
        .meta-created {
            grid-column: auto;
            border-right: 0;
            border-bottom: 2px solid #94a3b8;
        }

        .meta-created {
            background: #f8fafc;
        }

        .meta-item:last-child {
            border-bottom: 0;
        }

        .attachment-actions {
            flex-direction: column;
        }

        .attachment-btn {
            width: 100%;
        }

        .viewer-pdf {
            height: 480px;
        }
    }


    /* =========================================================
       SMALL PHONE
    ========================================================== */

    @media (max-width: 400px) {

        .letter-title {
            font-size: 16px;
        }

        .letter-number {
            font-size: 8px;
        }

        .meta-item {
            padding: 9px 10px;
        }

        .meta-value {
            font-size: 9px;
        }
    }
</style>


<div class="surat-keluar-detail-page">

    {{-- =========================================================
         SUCCESS
    ========================================================== --}}

    @if(session('success'))

        <div
            id="success-alert"
            class="detail-alert alert-success"
            role="alert"
        >

            <div class="detail-alert-inner">

                <div class="detail-alert-icon">
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

                <p class="detail-alert-text">
                    {{ session('success') }}
                </p>

            </div>

            <button
                type="button"
                class="detail-alert-close"
                onclick="document.getElementById('success-alert')?.remove()"
                aria-label="Tutup"
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
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>

        </div>

    @endif


    {{-- =========================================================
         ERROR
    ========================================================== --}}

    @if(session('error'))

        <div
            id="error-alert"
            class="detail-alert alert-error"
            role="alert"
        >

            <div class="detail-alert-inner">

                <div class="detail-alert-icon">
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
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>

                <p class="detail-alert-text">
                    {{ session('error') }}
                </p>

            </div>

            <button
                type="button"
                class="detail-alert-close"
                onclick="document.getElementById('error-alert')?.remove()"
                aria-label="Tutup"
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
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>

        </div>

    @endif


    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <div class="detail-header">

        <div class="detail-header-left">

            <a
                href="{{ route('surat-keluar.index') }}"
                class="detail-header-back"
                title="Kembali"
                aria-label="Kembali ke surat keluar"
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


            <div class="detail-header-content">

                <div class="detail-breadcrumb">

                    <a href="{{ route('surat-keluar.index') }}">
                        Surat Keluar
                    </a>

                    <span>/</span>

                    <span>Detail</span>

                </div>

                <h1 class="detail-header-title">
                    Detail Surat Keluar
                </h1>

                <p class="detail-header-subtitle">
                    Informasi lengkap surat keluar dan dokumen digital.
                </p>

            </div>

        </div>


        @if(
            \Illuminate\Support\Facades\Route::has(
                'surat-keluar.edit'
            )
        )

            <a
                href="{{ route('surat-keluar.edit', $suratKeluar) }}"
                class="detail-header-action"
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
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h5m5.5-1.5a2.121 2.121 0 003 0l5.5-5.5a2.121 2.121 0 00-3-3L15 10.5V13h-2.5z"
                    />
                </svg>

                Edit Arsip

            </a>

        @endif

    </div>


    {{-- =========================================================
         MAIN CARD
    ========================================================== --}}

    <div class="main-card">


        {{-- =====================================================
             HEADER SURAT
        ====================================================== --}}

        <div class="letter-heading">

            <div class="letter-heading-content">

                <div class="letter-eyebrow">

                    <span class="letter-dot"></span>

                    <span class="letter-eyebrow-text">
                        Arsip Surat Keluar
                    </span>

                </div>


                <h2 class="letter-title">
                    {{ $perihal }}
                </h2>


                <div class="letter-meta-line">

                    <span class="letter-id">
                        #{{ $suratKeluar->id }}
                    </span>

                    <span class="letter-number">
                        Nomor Surat:

                        <strong>
                            {{ $nomorSurat }}
                        </strong>
                    </span>

                </div>

            </div>


            <span class="status-badge {{ $statusClass }}">
                {{ $statusLabel }}
            </span>

        </div>


        {{-- =====================================================
             BODY
        ====================================================== --}}

        <div class="main-body">


            {{-- =================================================
                 INFORMASI META
            ================================================== --}}

            <div class="meta-table">

                <div class="meta-grid">


                    {{-- =================================================
                         TUJUAN SURAT
                    ================================================== --}}

                    <div class="meta-item">

                        <span class="meta-label">
                            Tujuan Surat
                        </span>

                        <p class="meta-value">
                            {{ $tujuanSurat }}
                        </p>

                    </div>


                    {{-- =================================================
                         KATEGORI SURAT
                    ================================================== --}}

                    <div class="meta-item">

                        <span class="meta-label">
                            Kategori Surat
                        </span>

                        <p class="meta-value">
                            {{ $kategoriNama }}
                        </p>

                    </div>


                    {{-- =================================================
                         TANGGAL SURAT
                    ================================================== --}}

                    <div class="meta-item">

                        <span class="meta-label">
                            Tanggal Surat
                        </span>

                        <p class="meta-value meta-date">

                            <svg
                                class="meta-date-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 2v4M16 2v4M3 10h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                                />
                            </svg>

                            <span>
                                {{ $tanggalSurat }}
                            </span>

                        </p>

                    </div>


                    {{-- =================================================
                         TANGGAL KELUAR
                    ================================================== --}}

                    <div class="meta-item">

                        <span class="meta-label">
                            Tanggal Keluar
                        </span>

                        <p class="meta-value meta-date">

                            <svg
                                class="meta-date-icon"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 2v4M16 2v4M3 10h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                                />
                            </svg>

                            <span>
                                {{ $tanggalKeluar }}
                            </span>

                        </p>

                    </div>


                    {{-- =================================================
                         DIBUAT OLEH
                    ================================================== --}}

                    <div class="meta-item meta-created">

                        <span class="meta-label">
                            Dibuat Oleh
                        </span>

                        <p class="meta-value">
                            {{ $pembuatNama }}
                        </p>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 PERIHAL
            ================================================== --}}

            <section class="detail-section">

                <div class="section-heading">

                    <div class="section-marker"></div>

                    <p class="section-heading-text">
                        Perihal Surat
                    </p>

                </div>


                <div class="content-box">

                    <p class="content-box-text">
                        {{ $perihal }}
                    </p>

                </div>

            </section>


            {{-- =================================================
                 RINGKASAN
            ================================================== --}}

            @if($ringkasan !== '')

                <section class="detail-section">

                    <div class="section-heading">

                        <div
                            class="section-marker"
                            style="background:#4f46e5;"
                        ></div>

                        <p class="section-heading-text">
                            Ringkasan Isi Surat
                        </p>

                    </div>


                    <div class="content-box summary-box">

                        <p class="content-box-text">
                            {{ $ringkasan }}
                        </p>

                    </div>

                </section>

            @endif


            {{-- =================================================
                 LAMPIRAN
            ================================================== --}}

            <section class="detail-section">

                <div class="attachment-header">

                    <div class="min-w-0">

                        <h3 class="attachment-title">
                            Berkas Lampiran Digital
                        </h3>

                        @if($lampiranNama)

                            <p class="attachment-filename">
                                {{ $lampiranNama }}
                            </p>

                        @endif

                    </div>


                    @if($lampiranExtension)

                        <span class="attachment-extension">
                            {{ $lampiranExtension }}
                        </span>

                    @endif

                </div>


                @if(
                    !empty($lampiranPath) &&
                    !empty($lampiranUrl)
                )

                    {{-- BUTTON --}}

                    <div class="attachment-actions">

                        <a
                            href="{{ $lampiranUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="attachment-btn attachment-btn-primary"
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
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                />
                            </svg>

                            Buka Lampiran

                        </a>


                        <a
                            href="{{ $lampiranUrl }}"
                            download
                            class="attachment-btn attachment-btn-secondary"
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
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                />
                            </svg>

                            Unduh Berkas

                        </a>

                    </div>


                    {{-- VIEWER --}}

                    <div class="viewer">

                        @if(
                            in_array(
                                $lampiranExtension,
                                [
                                    'jpg',
                                    'jpeg',
                                    'png'
                                ],
                                true
                            )
                        )

                            <div class="viewer-image-wrapper">

                                <img
                                    src="{{ $lampiranUrl }}"
                                    alt="Lampiran Surat Keluar"
                                    class="viewer-image"
                                    loading="lazy"
                                >

                            </div>


                        @elseif(
                            $lampiranExtension === 'pdf'
                        )

                            <iframe
                                src="{{ $lampiranUrl }}"
                                class="viewer-pdf"
                                title="Pratinjau PDF Surat Keluar"
                                loading="lazy"
                            ></iframe>


                        @else

                            <div class="viewer-empty">

                                <svg
                                    class="mx-auto h-9 w-9 text-slate-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    />
                                </svg>

                                <p class="viewer-empty-title">
                                    Pratinjau langsung tidak tersedia
                                </p>

                                <p class="viewer-empty-text">
                                    Format:

                                    <code>
                                        .{{ $lampiranExtension ?: 'dokumen' }}
                                    </code>

                                    tidak mendukung preview langsung.

                                    Gunakan tombol buka atau unduh.
                                </p>

                            </div>

                        @endif

                    </div>

                @else

                    <div class="no-file">

                        <svg
                            class="w-4 h-4 shrink-0 text-slate-400"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"
                            />
                        </svg>

                        <p>
                            Tidak ada berkas digital yang dilampirkan pada surat ini.
                        </p>

                    </div>

                @endif

            </section>

        </div>

    </div>

</div>

@endsection