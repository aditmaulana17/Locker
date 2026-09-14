@extends('layouts.app')

@section('title', 'Detail Surat Masuk')

@section('content')

@php
    use Illuminate\Support\Carbon;

    /*
    |--------------------------------------------------------------------------
    | DATA SURAT
    |--------------------------------------------------------------------------
    */

    $status = strtolower(
        trim(
            (string) ($suratMasuk->status ?? 'baru')
        )
    );

    $statusLabels = [
        'baru'           => 'Baru',
        'diproses'       => 'Diproses',
        'didisposisikan' => 'Didisposisikan',
        'selesai'        => 'Selesai',
        'diarsipkan'     => 'Diarsipkan',
    ];

    $statusClasses = [
        'baru'           => 'sm-status-baru',
        'diproses'       => 'sm-status-diproses',
        'didisposisikan' => 'sm-status-disposisi',
        'selesai'        => 'sm-status-selesai',
        'diarsipkan'     => 'sm-status-arsip',
    ];

    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
    $statusClass = $statusClasses[$status] ?? 'sm-status-baru';


    /*
    |--------------------------------------------------------------------------
    | TANGGAL
    |--------------------------------------------------------------------------
    */

    $tanggalSurat = '-';
    $tanggalTerima = '-';

    try {
        if ($suratMasuk->tanggal_surat) {
            $tanggalSurat = Carbon::parse(
                $suratMasuk->tanggal_surat
            )->translatedFormat('d F Y');
        }
    } catch (\Throwable $e) {
        $tanggalSurat = (string) $suratMasuk->tanggal_surat;
    }

    try {
        if ($suratMasuk->tanggal_terima) {
            $tanggalTerima = Carbon::parse(
                $suratMasuk->tanggal_terima
            )->translatedFormat('d F Y');
        }
    } catch (\Throwable $e) {
        $tanggalTerima = (string) $suratMasuk->tanggal_terima;
    }


    /*
    |--------------------------------------------------------------------------
    | LAMPIRAN
    |--------------------------------------------------------------------------
    */

    $lampiranPath = $suratMasuk->lampiran_file ?? null;
    $lampiranUrl = null;
    $lampiranExtension = '';

    if (!empty($lampiranPath)) {
        if (
            filter_var(
                $lampiranPath,
                FILTER_VALIDATE_URL
            )
        ) {
            $lampiranUrl = $lampiranPath;
        } elseif (
            Route::has('surat-masuk.preview-lampiran')
        ) {
            $lampiranUrl = route(
                'surat-masuk.preview-lampiran',
                $suratMasuk
            );
        }

        $cleanPath =
            parse_url(
                $lampiranPath,
                PHP_URL_PATH
            ) ?: $lampiranPath;

        $lampiranExtension = strtolower(
            pathinfo(
                $cleanPath,
                PATHINFO_EXTENSION
            )
        );
    }

    $isImage = in_array(
        $lampiranExtension,
        [
            'jpg',
            'jpeg',
            'png',
            'webp',
            'gif'
        ],
        true
    );

    $isPdf = $lampiranExtension === 'pdf';

    $lampiranNama =
        !empty($lampiranPath)
            ? basename($lampiranPath)
            : null;


    /*
    |--------------------------------------------------------------------------
    | HAK AKSES
    |--------------------------------------------------------------------------
    */

    $user = auth()->user();

    $userRole = strtolower(
        trim(
            (string) (
                $user->role
                ?? $user->jabatan
                ?? ''
            )
        )
    );

    if ($userRole === 'staff') {
        $userRole = 'staf';
    }

    $canManage = in_array(
        $userRole,
        [
            'admin',
            'pimpinan'
        ],
        true
    );


    /*
    |--------------------------------------------------------------------------
    | DISPOSISI
    |--------------------------------------------------------------------------
    */

    $disposisis =
        $suratMasuk->disposisi ?? collect();
@endphp

<style>
    /* =========================================================
       PAGE
    ========================================================== */

    .sm-detail-page {
        width: 100%;
        max-width: 1220px;
        margin: 0 auto;
        padding: 14px 16px 34px;
        color: #334155;
    }

    .sm-detail-page *,
    .sm-detail-page *::before,
    .sm-detail-page *::after {
        box-sizing: border-box;
    }


    /* =========================================================
       ALERT
    ========================================================== */

    .sm-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 13px;
        padding: 11px 13px;
        border-radius: 12px;
    }

    .sm-alert-success {
        background: #f0fdf4;
        color: #166534;
    }

    .sm-alert-error {
        background: #fff1f2;
        color: #991b1b;
    }

    .sm-alert-inner {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .sm-alert-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 32px;
        border-radius: 9px;
    }

    .sm-alert-success .sm-alert-icon {
        background: #dcfce7;
        color: #16a34a;
    }

    .sm-alert-error .sm-alert-icon {
        background: #ffe4e6;
        color: #dc2626;
    }

    .sm-alert-text {
        margin: 0;
        font-size: 11px;
        line-height: 1.5;
        font-weight: 700;
    }

    .sm-alert-close {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 28px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        cursor: pointer;
    }

    .sm-alert-success .sm-alert-close {
        color: #4ade80;
    }

    .sm-alert-error .sm-alert-close {
        color: #fb7185;
    }

    .sm-alert-close:hover {
        background: rgba(15, 23, 42, .04);
    }


    /* =========================================================
       HEADER
    ========================================================== */

    .sm-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 15px;
        padding: 17px 19px;
        overflow: hidden;
        border-radius: 18px;
        background:
            linear-gradient(
                135deg,
                #0f172a 0%,
                #1e293b 55%,
                #312e81 100%
            );
        color: #fff;
        box-shadow:
            0 14px 34px rgba(15, 23, 42, .12);
    }

    .sm-header::after {
        content: "";
        position: absolute;
        width: 185px;
        height: 185px;
        right: -70px;
        top: -100px;
        border-radius: 999px;
        background: rgba(99, 102, 241, .18);
        pointer-events: none;
    }

    .sm-header-left {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .sm-back {
        width: 41px;
        height: 41px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 41px;
        border-radius: 11px;
        background: rgba(255, 255, 255, .08);
        color: #fff;
        text-decoration: none;
        transition: .16s ease;
    }

    .sm-back:hover {
        background: rgba(255, 255, 255, .16);
        transform: translateX(-2px);
    }

    .sm-header-content {
        min-width: 0;
    }

    .sm-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 3px;
        font-size: 9px;
        color: #94a3b8;
    }

    .sm-breadcrumb a {
        color: #cbd5e1;
        text-decoration: none;
    }

    .sm-breadcrumb a:hover {
        color: #fff;
    }

    .sm-header-title {
        margin: 0;
        font-size: 20px;
        line-height: 1.25;
        font-weight: 800;
        letter-spacing: -.025em;
    }

    .sm-header-subtitle {
        margin: 4px 0 0;
        font-size: 10px;
        line-height: 1.5;
        color: #cbd5e1;
    }

    .sm-header-actions {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 7px;
    }

    .sm-header-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 35px;
        padding: 0 12px;
        border-radius: 9px;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: .16s ease;
    }

    .sm-header-btn-edit {
        background: rgba(245, 158, 11, .13);
        color: #fde68a;
    }

    .sm-header-btn-edit:hover {
        background: rgba(245, 158, 11, .23);
    }

    .sm-header-btn-print {
        background: rgba(16, 185, 129, .11);
        color: #a7f3d0;
    }

    .sm-header-btn-print:hover {
        background: rgba(16, 185, 129, .21);
    }

    .sm-header-btn-label {
        background: rgba(96, 165, 250, .10);
        color: #bfdbfe;
    }

    .sm-header-btn-label:hover {
        background: rgba(96, 165, 250, .20);
    }


    /* =========================================================
       TOP CONTENT
       KIRI = INFORMASI SURAT
       KANAN = RIWAYAT DISPOSISI
    ========================================================== */

    .sm-top-layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1.65fr)
            minmax(320px, .85fr);
        gap: 15px;
        align-items: stretch;
    }


    /* =========================================================
       GENERAL CARD
    ========================================================== */

    .sm-card {
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow:
            0 8px 28px rgba(15, 23, 42, .055);
    }


    /* =========================================================
       INFORMASI SURAT
    ========================================================== */

    .sm-info-card {
        overflow: hidden;
        height: 100%;
    }

    .sm-info-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        background:
            linear-gradient(
                135deg,
                #fafbff 0%,
                #f8fafc 58%,
                #f5f3ff 100%
            );
    }

    .sm-info-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .sm-info-heading-icon {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        border-radius: 10px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .sm-info-heading-title {
        margin: 0;
        font-size: 12px;
        line-height: 1.35;
        font-weight: 800;
        color: #1e293b;
    }

    .sm-info-heading-subtitle {
        margin: 2px 0 0;
        font-size: 9px;
        color: #94a3b8;
    }

    .sm-info-card-body {
        padding: 0;
    }

    .sm-info-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .sm-info-table td {
        padding: 13px 15px;
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
    }

    .sm-info-table tr:last-child td {
        border-bottom: 0;
    }

    .sm-info-table td:last-child {
        border-right: 0;
    }

    .sm-info-table td:nth-child(odd) {
        width: 25%;
        background: #f8fafc;
    }

    .sm-info-table td:nth-child(even) {
        width: 25%;
        background: #fff;
    }

    .sm-table-label {
        display: block;
        margin-bottom: 5px;
        font-size: 8px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #94a3b8;
    }

    .sm-table-value {
        margin: 0;
        font-size: 11px;
        line-height: 1.55;
        font-weight: 750;
        color: #1e293b;
        overflow-wrap: anywhere;
    }

    .sm-table-value-date {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .sm-table-date-icon {
        width: 14px;
        height: 14px;
        flex: 0 0 14px;
        color: #6366f1;
    }

    .sm-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 25px;
        padding: 0 9px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .sm-status-baru {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .sm-status-diproses {
        background: #fffbeb;
        color: #b45309;
    }

    .sm-status-disposisi {
        background: #f5f3ff;
        color: #6d28d9;
    }

    .sm-status-selesai {
        background: #f0fdf4;
        color: #15803d;
    }

    .sm-status-arsip {
        background: #f1f5f9;
        color: #475569;
    }


    /* =========================================================
       PERIHAL
    ========================================================== */

    .sm-perihal-card {
        margin-top: 15px;
        overflow: hidden;
    }

    .sm-perihal-head {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 13px 16px;
        border-bottom: 1px solid #e5e7eb;
        background:
            linear-gradient(
                135deg,
                #fafbff,
                #ffffff
            );
    }

    .sm-perihal-icon {
        width: 31px;
        height: 31px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 31px;
        border-radius: 9px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .sm-perihal-title {
        margin: 0;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #334155;
    }

    .sm-perihal-subtitle {
        margin: 2px 0 0;
        font-size: 9px;
        color: #94a3b8;
    }

    .sm-perihal-body {
        padding: 14px 16px;
    }

    .sm-perihal-content {
        margin: 0;
        padding: 12px 13px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f8fafc;
        font-size: 11px;
        line-height: 1.7;
        color: #475569;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }


    /* =========================================================
       DISPOSISI
    ========================================================== */

    .sm-disposition {
        overflow: hidden;
        height: 100%;
    }

    .sm-disposition-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 9px;
        padding: 14px 15px;
        border-bottom: 1px solid #e5e7eb;
        background:
            linear-gradient(
                135deg,
                #faf8ff,
                #ffffff
            );
    }

    .sm-disposition-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .sm-disposition-icon {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        border-radius: 10px;
        background: #f5f3ff;
        color: #7c3aed;
    }

    .sm-disposition-title {
        margin: 0;
        font-size: 12px;
        font-weight: 800;
        color: #1e293b;
    }

    .sm-disposition-subtitle {
        margin: 2px 0 0;
        font-size: 9px;
        color: #94a3b8;
    }

    .sm-create-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 29px;
        padding: 0 9px;
        border-radius: 8px;
        background: #7c3aed;
        color: #fff;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: .15s ease;
    }

    .sm-create-btn:hover {
        background: #6d28d9;
    }

    .sm-disposition-body {
        padding: 13px 15px 15px;
    }

    .sm-timeline {
        position: relative;
    }

    .sm-timeline-item {
        position: relative;
        padding-left: 23px;
        padding-bottom: 17px;
    }

    .sm-timeline-item:last-child {
        padding-bottom: 0;
    }

    .sm-timeline-line {
        position: absolute;
        top: 8px;
        bottom: 0;
        left: 5px;
        width: 1px;
        background: #ede9fe;
    }

    .sm-timeline-item:last-child .sm-timeline-line {
        display: none;
    }

    .sm-timeline-dot {
        position: absolute;
        top: 4px;
        left: 0;
        width: 11px;
        height: 11px;
        border-radius: 999px;
        background: #7c3aed;
        box-shadow:
            0 0 0 4px #f5f3ff;
    }

    .sm-disp-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
    }

    .sm-disp-route {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        min-width: 0;
        font-size: 10px;
        line-height: 1.5;
        font-weight: 800;
    }

    .sm-disp-from {
        color: #334155;
        overflow-wrap: anywhere;
    }

    .sm-disp-to {
        color: #7c3aed;
        overflow-wrap: anywhere;
    }

    .sm-disp-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 22px;
        padding: 0 8px;
        flex: 0 0 auto;
        border-radius: 7px;
        font-size: 8px;
        font-weight: 800;
        white-space: nowrap;
    }

    .sm-disp-menunggu {
        background: #f5f3ff;
        color: #6d28d9;
    }

    .sm-disp-diproses {
        background: #fffbeb;
        color: #b45309;
    }

    .sm-disp-selesai {
        background: #f0fdf4;
        color: #15803d;
    }

    .sm-disp-ditolak {
        background: #fff1f2;
        color: #be123c;
    }

    .sm-disp-content {
        margin-top: 7px;
        padding: 10px 11px;
        border-radius: 9px;
        background: #f8fafc;
    }

    .sm-disp-text {
        margin: 0;
        font-size: 10px;
        line-height: 1.65;
        color: #64748b;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .sm-disp-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 6px 9px;
        margin-top: 6px;
    }

    .sm-disp-date {
        font-size: 9px;
        font-weight: 700;
        color: #94a3b8;
    }

    .sm-disp-deadline {
        font-size: 9px;
        font-weight: 700;
        color: #d97706;
    }

    .sm-empty {
        padding: 30px 8px 23px;
        text-align: center;
    }

    .sm-empty-icon {
        width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        border-radius: 13px;
        background: #f8fafc;
        color: #94a3b8;
    }

    .sm-empty-title {
        margin: 0;
        font-size: 10px;
        font-weight: 800;
        color: #475569;
    }

    .sm-empty-text {
        margin: 4px 0 0;
        font-size: 9px;
        color: #94a3b8;
    }


    /* =========================================================
       LAMPIRAN DIGITAL
       FULL WIDTH DI BAGIAN PALING BAWAH
    ========================================================== */

    .sm-attachment-card {
        margin-top: 15px;
        overflow: hidden;
    }

    .sm-attachment-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 11px;
        padding: 14px 16px;
        border-bottom: 1px solid #e5e7eb;
        background:
            linear-gradient(
                135deg,
                #fafbff 0%,
                #f8fafc 58%,
                #f5f3ff 100%
            );
    }

    .sm-attachment-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .sm-attachment-icon {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        border-radius: 10px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .sm-attachment-title {
        margin: 0;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #334155;
    }

    .sm-attachment-description {
        margin: 2px 0 0;
        font-size: 9px;
        line-height: 1.45;
        color: #94a3b8;
    }

    .sm-extension {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 24px;
        padding: 0 9px;
        border-radius: 7px;
        background: #f1f5f9;
        color: #64748b;
        font-family:
            ui-monospace,
            SFMono-Regular,
            Menlo,
            Monaco,
            Consolas,
            monospace;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .sm-attachment-body {
        padding: 15px 16px 16px;
    }

    .sm-file-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 11px;
        margin-bottom: 10px;
        padding: 10px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f8fafc;
    }

    .sm-file-info {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .sm-file-icon {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        border-radius: 9px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .sm-file-name {
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 10px;
        font-weight: 750;
        color: #334155;
    }

    .sm-file-state {
        margin: 2px 0 0;
        font-size: 9px;
        color: #94a3b8;
    }

    .sm-file-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .sm-file-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 31px;
        padding: 0 10px;
        border-radius: 8px;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: .15s ease;
    }

    .sm-file-btn-open {
        background: #4f46e5;
        color: #fff;
    }

    .sm-file-btn-open:hover {
        background: #4338ca;
    }

    .sm-file-btn-download {
        background: #fff;
        color: #475569;
        border: 1px solid #e5e7eb;
    }

    .sm-file-btn-download:hover {
        background: #f1f5f9;
    }


    /* =========================================================
       VIEWER
    ========================================================== */

    .sm-viewer {
        overflow: hidden;
        padding: 5px;
        border-radius: 12px;
        background: #0f172a;
    }

    .sm-image-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 280px;
        border-radius: 8px;
        background: #111827;
    }

    .sm-image {
        display: block;
        width: auto;
        max-width: 100%;
        max-height: 680px;
        object-fit: contain;
    }

    .sm-pdf {
        width: 100%;
        height: 680px;
        display: block;
        border: 0;
        border-radius: 8px;
        background: #fff;
    }

    .sm-viewer-empty {
        min-height: 260px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 28px;
        border-radius: 8px;
        background: #fff;
        text-align: center;
    }

    .sm-viewer-empty-title {
        margin: 8px 0 0;
        font-size: 11px;
        font-weight: 800;
        color: #334155;
    }

    .sm-viewer-empty-text {
        max-width: 430px;
        margin: 5px auto 0;
        font-size: 9px;
        line-height: 1.6;
        color: #94a3b8;
    }

    .sm-no-file {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 12px;
        border-radius: 10px;
        background: #f8fafc;
        color: #94a3b8;
    }

    .sm-no-file p {
        margin: 0;
        font-size: 9px;
        line-height: 1.55;
    }


    /* =========================================================
       FOOTER
    ========================================================== */

    .sm-system-footer {
        margin-top: 10px;
        padding-top: 10px;
        text-align: center;
    }

    .sm-system-footer span {
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #cbd5e1;
    }


    /* =========================================================
       TABLET
    ========================================================== */

    @media (max-width: 980px) {
        .sm-top-layout {
            grid-template-columns: 1fr;
        }

        .sm-disposition {
            height: auto;
        }
    }


    /* =========================================================
       MOBILE
    ========================================================== */

    @media (max-width: 700px) {
        .sm-detail-page {
            padding: 9px 10px 24px;
        }

        .sm-header {
            align-items: flex-start;
            flex-direction: column;
            padding: 15px;
        }

        .sm-header-actions {
            width: 100%;
            justify-content: flex-start;
            padding-top: 9px;
            border-top: 1px solid rgba(255,255,255,.08);
        }

        .sm-header-btn {
            flex: 1 1 auto;
        }

        .sm-info-card-head,
        .sm-attachment-head {
            padding: 13px;
        }

        .sm-info-table {
            table-layout: auto;
        }

        .sm-info-table tr {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
        }

        .sm-info-table td {
            width: auto !important;
            border-right: 1px solid #e5e7eb;
        }

        .sm-info-table td:nth-child(even) {
            border-right: 0;
        }

        .sm-perihal-head,
        .sm-perihal-body,
        .sm-attachment-body {
            padding-left: 13px;
            padding-right: 13px;
        }

        .sm-file-bar {
            align-items: stretch;
            flex-direction: column;
        }

        .sm-file-actions {
            width: 100%;
        }

        .sm-file-btn {
            flex: 1;
        }

        .sm-pdf {
            height: 520px;
        }
    }


    /* =========================================================
       SMALL MOBILE
    ========================================================== */

    @media (max-width: 470px) {
        .sm-header-title {
            font-size: 18px;
        }

        .sm-header-subtitle {
            font-size: 9px;
        }

        .sm-header-actions {
            flex-direction: column;
        }

        .sm-header-btn {
            width: 100%;
        }

        .sm-info-heading-subtitle,
        .sm-attachment-description {
            display: none;
        }

        .sm-info-table tr {
            grid-template-columns: 1fr;
        }

        .sm-info-table td {
            border-right: 0;
        }

        .sm-info-table td:nth-child(odd) {
            background: #f8fafc;
        }

        .sm-info-table td:nth-child(even) {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }

        .sm-info-table tr:last-child td:last-child {
            border-bottom: 0;
        }

        .sm-disp-top {
            flex-direction: column;
        }

        .sm-disp-status {
            align-self: flex-start;
        }

        .sm-disp-route {
            font-size: 9px;
        }

        .sm-disp-text {
            font-size: 9px;
        }

        .sm-pdf {
            height: 440px;
        }
    }
</style>

<div class="sm-detail-page">

    {{-- =========================================================
         ALERT SUCCESS
    ========================================================== --}}

    @if(session('success'))
        <div
            class="sm-alert sm-alert-success"
            role="alert"
        >
            <div class="sm-alert-inner">
                <div class="sm-alert-icon">
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

                <p class="sm-alert-text">
                    {{ session('success') }}
                </p>
            </div>

            <button
                type="button"
                class="sm-alert-close"
                onclick="this.closest('[role=alert]')?.remove()"
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
         ALERT ERROR
    ========================================================== --}}

    @if(session('error'))
        <div
            class="sm-alert sm-alert-error"
            role="alert"
        >
            <div class="sm-alert-inner">
                <div class="sm-alert-icon">
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

                <p class="sm-alert-text">
                    {{ session('error') }}
                </p>
            </div>

            <button
                type="button"
                class="sm-alert-close"
                onclick="this.closest('[role=alert]')?.remove()"
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

    <div class="sm-header">

        <div class="sm-header-left">

            <a
                href="{{ route('surat-masuk.index') }}"
                class="sm-back"
                title="Kembali"
                aria-label="Kembali ke Surat Masuk"
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

            <div class="sm-header-content">

                <div class="sm-breadcrumb">
                    <a href="{{ route('surat-masuk.index') }}">
                        Surat Masuk
                    </a>

                    <span>/</span>

                    <span>Detail Arsip</span>
                </div>

                <h1 class="sm-header-title">
                    Detail Surat Masuk
                </h1>

                <p class="sm-header-subtitle">
                    Informasi surat, riwayat disposisi, dan berkas digital.
                </p>

            </div>

        </div>


        <div class="sm-header-actions">

            @if(
                $canManage &&
                Route::has('surat-masuk.edit')
            )
                <a
                    href="{{ route('surat-masuk.edit', $suratMasuk) }}"
                    class="sm-header-btn sm-header-btn-edit"
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

                    Edit Surat
                </a>
            @endif


            @if(Route::has('surat-masuk.cetak-disposisi'))
                <a
                    href="{{ route('surat-masuk.cetak-disposisi', $suratMasuk) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="sm-header-btn sm-header-btn-print"
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
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4h4m2 5h6a2 2 0 002-2v-3H8v3a2 2 0 002 2z"
                        />
                    </svg>

                    Cetak Disposisi
                </a>
            @endif


            @if(Route::has('surat-masuk.cetak-label'))
                <a
                    href="{{ route('surat-masuk.cetak-label', $suratMasuk) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="sm-header-btn sm-header-btn-label"
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
                            d="M7 7h10M7 11h10M7 15h10M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"
                        />
                    </svg>

                    Cetak Label
                </a>
            @endif

        </div>

    </div>


    {{-- =========================================================
         TOP LAYOUT
         KIRI  : INFORMASI SURAT
         KANAN : RIWAYAT DISPOSISI
    ========================================================== --}}

    <div class="sm-top-layout">

        {{-- =====================================================
             KIRI
             INFORMASI SURAT
        ====================================================== --}}

        <div>

            <div class="sm-card sm-info-card">

                <div class="sm-info-card-head">

                    <div class="sm-info-heading">

                        <div class="sm-info-heading-icon">
                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A1.5 1.5 0 0118 8.5V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </div>

                        <div>
                            <h2 class="sm-info-heading-title">
                                Informasi Surat
                            </h2>

                            <p class="sm-info-heading-subtitle">
                                Data utama arsip surat masuk.
                            </p>
                        </div>

                    </div>

                    <span class="sm-status {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>

                </div>


                <div class="sm-info-card-body">

                    <table class="sm-info-table">

                        <tbody>

                            <tr>

                                <td>
                                    <span class="sm-table-label">
                                        Nomor Surat
                                    </span>

                                    <p class="sm-table-value">
                                        {{ $suratMasuk->nomor_surat ?? '-' }}
                                    </p>
                                </td>

                                <td>
                                    <span class="sm-table-label">
                                        Pengirim
                                    </span>

                                    <p class="sm-table-value">
                                        {{ $suratMasuk->pengirim ?? '-' }}
                                    </p>
                                </td>

                                <td>
                                    <span class="sm-table-label">
                                        Kategori Surat
                                    </span>

                                    <p class="sm-table-value">
                                        {{ $suratMasuk->kategori?->nama_kategori ?? '-' }}
                                    </p>
                                </td>

                                <td>
                                    <span class="sm-table-label">
                                        Tanggal Surat
                                    </span>

                                    <p class="sm-table-value sm-table-value-date">

                                        <svg
                                            class="sm-table-date-icon"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
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
                                </td>

                            </tr>


                            <tr>

                                <td>
                                    <span class="sm-table-label">
                                        Tanggal Diterima
                                    </span>

                                    <p class="sm-table-value sm-table-value-date">

                                        <svg
                                            class="sm-table-date-icon"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M8 2v4M16 2v4M3 10h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                                            />
                                        </svg>

                                        <span>
                                            {{ $tanggalTerima }}
                                        </span>

                                    </p>
                                </td>

                                <td>
                                    <span class="sm-table-label">
                                        Lokasi Arsip Fisik
                                    </span>

                                    <p class="sm-table-value">
                                        {{ $suratMasuk->lokasi_arsip_fisik ?: '-' }}
                                    </p>
                                </td>

                                <td>
                                    <span class="sm-table-label">
                                        Nomor Agenda
                                    </span>

                                    <p class="sm-table-value">
                                        {{ $suratMasuk->nomor_agenda ?? '#' . $suratMasuk->id }}
                                    </p>
                                </td>

                                <td>
                                    <span class="sm-table-label">
                                        Diterima Oleh
                                    </span>

                                    <p class="sm-table-value">
                                        {{ $suratMasuk->penerima?->name ?? '-' }}
                                    </p>
                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- =================================================
                 PERIHAL
            ================================================== --}}

            <div class="sm-card sm-perihal-card">

                <div class="sm-perihal-head">

                    <div class="sm-perihal-icon">
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
                                d="M4 6h16M4 12h16M4 18h10"
                            />
                        </svg>
                    </div>

                    <div>
                        <p class="sm-perihal-title">
                            Perihal Surat
                        </p>

                        <p class="sm-perihal-subtitle">
                            Pokok atau tujuan utama surat.
                        </p>
                    </div>

                </div>

                <div class="sm-perihal-body">

                    <p class="sm-perihal-content">
                        {{ $suratMasuk->perihal ?? 'Tanpa Perihal' }}
                    </p>

                </div>

            </div>

        </div>


        {{-- =====================================================
             KANAN
             RIWAYAT DISPOSISI
        ====================================================== --}}

        <aside>

            <div class="sm-card sm-disposition">

                <div class="sm-disposition-head">

                    <div class="sm-disposition-heading">

                        <div class="sm-disposition-icon">
                            <svg
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"
                                />
                            </svg>
                        </div>

                        <div class="min-w-0">

                            <h3 class="sm-disposition-title">
                                Riwayat Disposisi
                            </h3>

                            <p class="sm-disposition-subtitle">
                                Instruksi dan tindak lanjut surat.
                            </p>

                        </div>

                    </div>


                    @if(
                        $canManage &&
                        Route::has('disposisi.create')
                    )
                        <a
                            href="{{ route('disposisi.create', $suratMasuk) }}"
                            class="sm-create-btn"
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
                                    stroke-width="2.5"
                                    d="M12 4v16m8-8H4"
                                />
                            </svg>

                            Buat
                        </a>
                    @endif

                </div>


                <div class="sm-disposition-body">

                    @if($disposisis->count())

                        <div class="sm-timeline">

                            @foreach($disposisis as $d)

                                @php
                                    $disposisiStatus =
                                        strtolower(
                                            trim(
                                                (string) (
                                                    $d->status
                                                    ?? 'menunggu'
                                                )
                                            )
                                        );

                                    $disposisiLabels = [
                                        'menunggu' => 'Menunggu',
                                        'diproses' => 'Diproses',
                                        'selesai'  => 'Selesai',
                                        'ditolak'  => 'Ditolak',
                                    ];

                                    $disposisiClasses = [
                                        'menunggu' => 'sm-disp-menunggu',
                                        'diproses' => 'sm-disp-diproses',
                                        'selesai'  => 'sm-disp-selesai',
                                        'ditolak'  => 'sm-disp-ditolak',
                                    ];

                                    $disposisiLabel =
                                        $disposisiLabels[
                                            $disposisiStatus
                                        ]
                                        ?? ucfirst(
                                            $disposisiStatus
                                        );

                                    $disposisiClass =
                                        $disposisiClasses[
                                            $disposisiStatus
                                        ]
                                        ?? 'sm-disp-menunggu';


                                    $tanggalDisposisi = '-';

                                    if ($d->created_at) {
                                        try {
                                            $tanggalDisposisi =
                                                Carbon::parse(
                                                    $d->created_at
                                                )->translatedFormat(
                                                    'd/m/Y H:i'
                                                );
                                        } catch (\Throwable $e) {
                                            $tanggalDisposisi =
                                                (string) $d->created_at;
                                        }
                                    }


                                    $batasWaktu = null;

                                    if ($d->batas_waktu) {
                                        try {
                                            $batasWaktu =
                                                Carbon::parse(
                                                    $d->batas_waktu
                                                )->translatedFormat(
                                                    'd/m/Y'
                                                );
                                        } catch (\Throwable $e) {
                                            $batasWaktu =
                                                (string) $d->batas_waktu;
                                        }
                                    }


                                    $isiDisposisi =
                                        $d->isi_disposisi
                                        ?? $d->instruksi
                                        ?? $d->catatan
                                        ?? '';
                                @endphp


                                <div class="sm-timeline-item">

                                    <div class="sm-timeline-line"></div>

                                    <div class="sm-timeline-dot"></div>


                                    <div class="sm-disp-top">

                                        <div class="sm-disp-route">

                                            <span class="sm-disp-from">
                                                {{ $d->dari?->name ?? '-' }}
                                            </span>

                                            <svg
                                                class="w-3 h-3 text-purple-300"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M14 5l7 7m0 0l-7 7m7-7H3"
                                                />
                                            </svg>

                                            <span class="sm-disp-to">
                                                {{ $d->kepada?->name ?? '-' }}
                                            </span>

                                        </div>


                                        <span
                                            class="sm-disp-status {{ $disposisiClass }}"
                                        >
                                            {{ $disposisiLabel }}
                                        </span>

                                    </div>


                                    <div class="sm-disp-content">

                                        <p class="sm-disp-text">

                                            @if(filled($isiDisposisi))

                                                {{ $isiDisposisi }}

                                            @else

                                                <span class="text-slate-300">
                                                    Tidak ada instruksi atau catatan.
                                                </span>

                                            @endif

                                        </p>

                                    </div>


                                    <div class="sm-disp-meta">

                                        @if($batasWaktu)

                                            <span class="sm-disp-deadline">
                                                Batas:
                                                {{ $batasWaktu }}
                                            </span>

                                        @endif

                                        <span class="sm-disp-date">
                                            {{ $tanggalDisposisi }}
                                        </span>

                                    </div>

                                </div>

                            @endforeach

                        </div>

                    @else

                        <div class="sm-empty">

                            <div class="sm-empty-icon">
                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
                                    />
                                </svg>
                            </div>

                            <p class="sm-empty-title">
                                Belum ada disposisi
                            </p>

                            <p class="sm-empty-text">
                                Belum ada instruksi atau tindak lanjut.
                            </p>


                            @if(
                                $canManage &&
                                Route::has('disposisi.create')
                            )
                                <a
                                    href="{{ route('disposisi.create', $suratMasuk) }}"
                                    class="sm-create-btn"
                                    style="margin-top:11px;"
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
                                            stroke-width="2.5"
                                            d="M12 4v16m8-8H4"
                                        />
                                    </svg>

                                    Buat Disposisi
                                </a>
                            @endif

                        </div>

                    @endif

                </div>

            </div>

        </aside>

    </div>


    {{-- =========================================================
         LAMPIRAN DIGITAL
         SATU-SATUNYA BAGIAN FULL WIDTH DI BAWAH
    ========================================================== --}}

    <div class="sm-card sm-attachment-card">

        <div class="sm-attachment-head">

            <div class="sm-attachment-heading">

                <div class="sm-attachment-icon">
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
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

                    <h3 class="sm-attachment-title">
                        Berkas Lampiran Digital
                    </h3>

                    <p class="sm-attachment-description">
                        Dokumen digital yang tersimpan pada arsip surat ini.
                    </p>

                </div>

            </div>


            @if($lampiranExtension)

                <span class="sm-extension">
                    .{{ $lampiranExtension }}
                </span>

            @endif

        </div>


        <div class="sm-attachment-body">

            @if(
                !empty($lampiranPath) &&
                !empty($lampiranUrl)
            )

                <div class="sm-file-bar">

                    <div class="sm-file-info">

                        <div class="sm-file-icon">
                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
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

                            <p class="sm-file-name">
                                {{ $lampiranNama }}
                            </p>

                            <p class="sm-file-state">
                                Lampiran tersimpan
                            </p>

                        </div>

                    </div>


                    <div class="sm-file-actions">

                        <a
                            href="{{ $lampiranUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="sm-file-btn sm-file-btn-open"
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

                            Buka
                        </a>


                        <a
                            href="{{ $lampiranUrl }}"
                            download
                            class="sm-file-btn sm-file-btn-download"
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

                            Unduh
                        </a>

                    </div>

                </div>


                {{-- =================================================
                     VIEWER
                ================================================== --}}

                <div class="sm-viewer">

                    @if($isImage)

                        <div class="sm-image-wrapper">

                            <img
                                src="{{ $lampiranUrl }}"
                                alt="Lampiran Surat Masuk"
                                class="sm-image"
                                loading="lazy"
                            >

                        </div>

                    @elseif($isPdf)

                        <iframe
                            src="{{ $lampiranUrl }}"
                            title="Pratinjau PDF Surat Masuk"
                            class="sm-pdf"
                            loading="lazy"
                        ></iframe>

                    @else

                        <div class="sm-viewer-empty">

                            <svg
                                class="w-10 h-10 text-slate-300"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A1.5 1.5 0 0118 8.5V19a2 2 0 01-2 2z"
                                />
                            </svg>

                            <p class="sm-viewer-empty-title">
                                Pratinjau tidak tersedia
                            </p>

                            <p class="sm-viewer-empty-text">
                                Format
                                <strong>
                                    .{{ $lampiranExtension ?: 'dokumen' }}
                                </strong>
                                tidak dapat ditampilkan langsung.
                                Gunakan tombol Buka atau Unduh.
                            </p>

                        </div>

                    @endif

                </div>

            @else

                <div class="sm-no-file">

                    <svg
                        class="w-4 h-4 shrink-0 text-slate-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"
                        />
                    </svg>

                    <p>
                        Tidak ada berkas digital yang dilampirkan pada surat ini.
                    </p>

                </div>

            @endif

        </div>

    </div>


    {{-- =========================================================
         FOOTER
    ========================================================== --}}

    <div class="sm-system-footer">
        <span>
            Sistem Kendali Surat Masuk
        </span>
    </div>

</div>

@endsection