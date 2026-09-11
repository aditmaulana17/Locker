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
        ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        true
    );

    $isPdf = $lampiranExtension === 'pdf';

    $lampiranNama = !empty($lampiranPath)
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
        ['admin', 'pimpinan'],
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
        max-width: 1180px;
        margin: 0 auto;
        padding: 12px 16px 32px;
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
        margin-bottom: 12px;
        padding: 10px 12px;
        border: 1px solid;
        border-radius: 12px;
    }

    .sm-alert-inner {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .sm-alert-icon {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        border-radius: 9px;
    }

    .sm-alert-text {
        margin: 0;
        font-size: 10px;
        line-height: 1.45;
        font-weight: 700;
    }

    .sm-alert-close {
        width: 27px;
        height: 27px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 27px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        cursor: pointer;
    }

    .sm-alert-success {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #166534;
    }

    .sm-alert-success .sm-alert-icon {
        background: #dcfce7;
        color: #16a34a;
    }

    .sm-alert-success .sm-alert-close {
        color: #4ade80;
    }

    .sm-alert-error {
        border-color: #fecdd3;
        background: #fff1f2;
        color: #991b1b;
    }

    .sm-alert-error .sm-alert-icon {
        background: #ffe4e6;
        color: #dc2626;
    }

    .sm-alert-error .sm-alert-close {
        color: #fb7185;
    }

    .sm-alert-close:hover {
        background: rgba(15, 23, 42, .04);
    }


    /* =========================================================
       TOP HEADER
    ========================================================== */

    .sm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
        padding: 14px 16px;
        border: 1px solid #dbe3ef;
        border-radius: 16px;
        background: linear-gradient(
            135deg,
            #0f172a,
            #1e293b 58%,
            #312e81
        );
        color: #fff;
        box-shadow:
            0 8px 25px rgba(15, 23, 42, .10);
    }

    .sm-header-left {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
    }

    .sm-back {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 38px;
        border: 1px solid rgba(255, 255, 255, .14);
        border-radius: 10px;
        background: rgba(255, 255, 255, .08);
        color: #fff;
        text-decoration: none;
        transition: .16s ease;
    }

    .sm-back:hover {
        background: rgba(255, 255, 255, .15);
        transform: translateX(-1px);
    }

    .sm-header-content {
        min-width: 0;
    }

    .sm-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 2px;
        font-size: 8px;
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
        font-size: 18px;
        line-height: 1.25;
        font-weight: 800;
        letter-spacing: -.025em;
    }

    .sm-header-subtitle {
        margin: 3px 0 0;
        font-size: 9px;
        line-height: 1.45;
        color: #cbd5e1;
    }

    .sm-header-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 6px;
    }

    .sm-header-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 0 10px;
        border-radius: 9px;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: .16s ease;
    }

    .sm-header-btn-edit {
        border: 1px solid rgba(245, 158, 11, .28);
        background: rgba(245, 158, 11, .12);
        color: #fde68a;
    }

    .sm-header-btn-edit:hover {
        background: rgba(245, 158, 11, .22);
    }

    .sm-header-btn-print {
        border: 1px solid rgba(16, 185, 129, .28);
        background: rgba(16, 185, 129, .11);
        color: #a7f3d0;
    }

    .sm-header-btn-print:hover {
        background: rgba(16, 185, 129, .20);
    }

    .sm-header-btn-label {
        border: 1px solid rgba(96, 165, 250, .28);
        background: rgba(96, 165, 250, .10);
        color: #bfdbfe;
    }

    .sm-header-btn-label:hover {
        background: rgba(96, 165, 250, .20);
    }


    /* =========================================================
       LAYOUT
    ========================================================== */

    .sm-layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1.75fr)
            minmax(300px, .85fr);
        gap: 14px;
        align-items: start;
    }


    /* =========================================================
       MAIN CARD
    ========================================================== */

    .sm-main-card {
        overflow: hidden;
        border: 1px solid #dbe3ef;
        border-radius: 16px;
        background: #fff;
        box-shadow:
            0 8px 24px rgba(15, 23, 42, .06);
    }


    /* =========================================================
       LETTER HEAD
    ========================================================== */

    .sm-letter-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        padding: 16px;
        background:
            linear-gradient(
                to bottom,
                #ffffff,
                #f8fafc
            );
    }

    .sm-letter-info {
        min-width: 0;
        flex: 1;
    }

    .sm-eyebrow {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 5px;
    }

    .sm-eyebrow-dot {
        width: 7px;
        height: 7px;
        border-radius: 999px;
        background: #4f46e5;
        box-shadow:
            0 0 0 3px #e0e7ff;
    }

    .sm-eyebrow-text {
        font-size: 8px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #4338ca;
    }

    .sm-letter-title {
        margin: 0;
        font-size: 18px;
        line-height: 1.4;
        font-weight: 850;
        letter-spacing: -.02em;
        color: #0f172a;
        overflow-wrap: anywhere;
    }

    .sm-letter-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 7px;
    }

    .sm-agenda {
        display: inline-flex;
        align-items: center;
        min-height: 23px;
        padding: 0 8px;
        border: 1px solid #c7d2fe;
        border-radius: 7px;
        background: #eef2ff;
        color: #4338ca;
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

    .sm-number {
        font-size: 9px;
        line-height: 1.4;
        color: #64748b;
        overflow-wrap: anywhere;
    }

    .sm-number strong {
        color: #1e293b;
    }

    .sm-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 0 10px;
        flex: 0 0 auto;
        border: 1px solid;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .sm-status-baru {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .sm-status-diproses {
        border-color: #fde68a;
        background: #fffbeb;
        color: #b45309;
    }

    .sm-status-disposisi {
        border-color: #ddd6fe;
        background: #f5f3ff;
        color: #6d28d9;
    }

    .sm-status-selesai {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #15803d;
    }

    .sm-status-arsip {
        border-color: #e2e8f0;
        background: #f8fafc;
        color: #475569;
    }


    /* =========================================================
       MAIN BODY
    ========================================================== */

    .sm-main-body {
        padding: 14px 16px 16px;
    }


    /* =========================================================
       INFORMATION GRID
    ========================================================== */

    .sm-info-grid {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
    }

    .sm-info-item {
        min-width: 0;
        min-height: 67px;
        padding: 10px 11px;
        background: #fff;
        transition: background .15s ease;
    }

    .sm-info-item:nth-child(odd) {
        border-right: 1px solid #e2e8f0;
    }

    .sm-info-item:nth-child(-n + 4) {
        border-bottom: 1px solid #e2e8f0;
    }

    .sm-info-item:hover {
        background: #fafbff;
    }

    .sm-info-label {
        display: block;
        margin-bottom: 4px;
        font-size: 8px;
        line-height: 1.25;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #94a3b8;
    }

    .sm-info-value {
        margin: 0;
        font-size: 10px;
        line-height: 1.5;
        font-weight: 750;
        color: #1e293b;
        overflow-wrap: anywhere;
    }

    .sm-info-date {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .sm-date-icon {
        width: 13px;
        height: 13px;
        flex: 0 0 13px;
        color: #64748b;
    }


    /* =========================================================
       CONTENT SECTIONS
    ========================================================== */

    .sm-section {
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid #e2e8f0;
    }

    .sm-section-head {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 7px;
    }

    .sm-section-icon {
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 25px;
        border-radius: 7px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .sm-section-title {
        margin: 0;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #334155;
    }

    .sm-section-subtitle {
        margin: 2px 0 0;
        font-size: 8px;
        color: #94a3b8;
    }

    .sm-text-box {
        padding: 11px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 11px;
        background: #f8fafc;
    }

    .sm-text-content {
        margin: 0;
        font-size: 10px;
        line-height: 1.65;
        color: #475569;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .sm-summary-box {
        background: #fff;
    }


    /* =========================================================
       ATTACHMENT
    ========================================================== */

    .sm-attachment-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }

    .sm-attachment-title {
        margin: 0;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #334155;
    }

    .sm-attachment-description {
        margin: 3px 0 0;
        font-size: 8px;
        color: #94a3b8;
    }

    .sm-extension {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 22px;
        padding: 0 7px;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        background: #f8fafc;
        color: #64748b;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .sm-file-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
        padding: 8px 9px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }

    .sm-file-info {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 0;
    }

    .sm-file-icon {
        width: 29px;
        height: 29px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 29px;
        border-radius: 8px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .sm-file-name {
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 8.5px;
        font-weight: 750;
        color: #334155;
    }

    .sm-file-state {
        margin: 2px 0 0;
        font-size: 7.5px;
        color: #94a3b8;
    }

    .sm-file-actions {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .sm-file-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 29px;
        padding: 0 9px;
        border-radius: 8px;
        font-size: 8px;
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
        border: 1px solid #dbe3ef;
        background: #fff;
        color: #475569;
    }

    .sm-file-btn-download:hover {
        background: #f1f5f9;
    }

    .sm-viewer {
        overflow: hidden;
        padding: 5px;
        border: 1px solid #dbe3ef;
        border-radius: 11px;
        background: #0f172a;
    }

    .sm-image-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 240px;
        border-radius: 7px;
        background: #111827;
    }

    .sm-image {
        display: block;
        width: auto;
        max-width: 100%;
        max-height: 620px;
        object-fit: contain;
    }

    .sm-pdf {
        width: 100%;
        height: 620px;
        display: block;
        border: 0;
        border-radius: 7px;
        background: #fff;
    }

    .sm-viewer-empty {
        min-height: 240px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 25px;
        border-radius: 7px;
        background: #fff;
        text-align: center;
    }

    .sm-viewer-empty-title {
        margin: 7px 0 0;
        font-size: 10px;
        font-weight: 800;
        color: #334155;
    }

    .sm-viewer-empty-text {
        max-width: 400px;
        margin: 4px auto 0;
        font-size: 8px;
        line-height: 1.5;
        color: #94a3b8;
    }

    .sm-no-file {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 11px;
        border: 1px dashed #dbe3ef;
        border-radius: 10px;
        background: #f8fafc;
    }

    .sm-no-file p {
        margin: 0;
        font-size: 8.5px;
        line-height: 1.5;
        color: #94a3b8;
    }


    /* =========================================================
       DISPOSITION
    ========================================================== */

    .sm-disposition {
        overflow: hidden;
        border: 1px solid #dbe3ef;
        border-radius: 16px;
        background: #fff;
        box-shadow:
            0 8px 24px rgba(15,23,42,.06);
    }

    .sm-disposition-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 12px 13px;
        background: linear-gradient(
            to right,
            #faf8ff,
            #fff
        );
        border-bottom: 1px solid #eeeaf9;
    }

    .sm-disposition-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .sm-disposition-heading-icon {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        border-radius: 9px;
        background: #f5f3ff;
        color: #7c3aed;
    }

    .sm-disposition-title {
        margin: 0;
        font-size: 11px;
        font-weight: 800;
        color: #1e293b;
    }

    .sm-disposition-subtitle {
        margin: 2px 0 0;
        font-size: 8px;
        color: #94a3b8;
    }

    .sm-create-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-height: 28px;
        padding: 0 8px;
        border-radius: 8px;
        background: #7c3aed;
        color: #fff;
        font-size: 8px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: .15s ease;
    }

    .sm-create-btn:hover {
        background: #6d28d9;
    }

    .sm-disposition-body {
        padding: 12px 13px;
    }

    .sm-timeline {
        position: relative;
    }

    .sm-timeline-item {
        position: relative;
        padding-left: 22px;
        padding-bottom: 15px;
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
        background: #e9d5ff;
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
        border: 3px solid #f3e8ff;
        border-radius: 999px;
        background: #7c3aed;
    }

    .sm-disp-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 7px;
    }

    .sm-disp-route {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
        min-width: 0;
        font-size: 8.5px;
        line-height: 1.4;
        font-weight: 800;
    }

    .sm-disp-from {
        color: #1e293b;
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
        min-height: 20px;
        padding: 0 6px;
        flex: 0 0 auto;
        border: 1px solid;
        border-radius: 6px;
        font-size: 7px;
        font-weight: 800;
        white-space: nowrap;
    }

    .sm-disp-menunggu {
        border-color: #ddd6fe;
        background: #f5f3ff;
        color: #6d28d9;
    }

    .sm-disp-diproses {
        border-color: #fde68a;
        background: #fffbeb;
        color: #b45309;
    }

    .sm-disp-selesai {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: #15803d;
    }

    .sm-disp-ditolak {
        border-color: #fecdd3;
        background: #fff1f2;
        color: #be123c;
    }

    .sm-disp-content {
        margin-top: 6px;
        padding: 8px 9px;
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        background: #f8fafc;
    }

    .sm-disp-text {
        margin: 0;
        font-size: 8.5px;
        line-height: 1.55;
        color: #64748b;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .sm-disp-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 5px 8px;
        margin-top: 5px;
    }

    .sm-disp-date {
        font-size: 7.5px;
        font-weight: 700;
        color: #94a3b8;
    }

    .sm-disp-deadline {
        font-size: 7.5px;
        font-weight: 700;
        color: #d97706;
    }

    .sm-empty {
        padding: 25px 8px 18px;
        text-align: center;
    }

    .sm-empty-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        border-radius: 12px;
        background: #f8fafc;
        color: #94a3b8;
    }

    .sm-empty-title {
        margin: 0;
        font-size: 9px;
        font-weight: 800;
        color: #475569;
    }

    .sm-empty-text {
        margin: 3px 0 0;
        font-size: 8px;
        color: #94a3b8;
    }

    .sm-system-footer {
        margin-top: 9px;
        padding-top: 8px;
        border-top: 1px solid #eef2f7;
        text-align: center;
    }

    .sm-system-footer span {
        font-size: 7px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #cbd5e1;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 980px) {

        .sm-layout {
            grid-template-columns: 1fr;
        }

        .sm-disposition {
            order: 2;
        }

        .sm-pdf {
            height: 560px;
        }
    }


    @media (max-width: 700px) {

        .sm-detail-page {
            padding: 8px 10px 22px;
        }

        .sm-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .sm-header-actions {
            width: 100%;
            justify-content: flex-start;
            padding-top: 8px;
            border-top: 1px solid rgba(255,255,255,.09);
        }

        .sm-header-btn {
            flex: 1 1 auto;
        }

        .sm-letter-head {
            flex-direction: column;
        }

        .sm-status {
            align-self: flex-start;
        }

        .sm-main-body {
            padding: 11px;
        }

        .sm-info-grid {
            grid-template-columns: 1fr;
        }

        .sm-info-item,
        .sm-info-item:nth-child(odd) {
            border-right: 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .sm-info-item:last-child {
            border-bottom: 0;
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
            height: 500px;
        }
    }


    @media (max-width: 470px) {

        .sm-header-title {
            font-size: 16px;
        }

        .sm-header-subtitle {
            font-size: 8px;
        }

        .sm-letter-title {
            font-size: 16px;
        }

        .sm-header-actions {
            flex-direction: column;
        }

        .sm-header-btn {
            width: 100%;
        }

        .sm-disp-top {
            flex-direction: column;
        }

        .sm-disp-status {
            align-self: flex-start;
        }

        .sm-pdf {
            height: 440px;
        }
    }
</style>


<div class="sm-detail-page">

    {{-- =========================================================
         ALERT
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

                    <span>Detail</span>

                </div>

                <h1 class="sm-header-title">
                    Detail Surat Masuk
                </h1>

                <p class="sm-header-subtitle">
                    Informasi surat, lampiran digital, dan riwayat disposisi.
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


            @if(
                Route::has('surat-masuk.cetak-disposisi')
            )

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
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4h4m2 8h6a2 2 0 002-2v-4H8v4a2 2 0 002 2z"
                        />
                    </svg>

                    Cetak Disposisi
                </a>

            @endif


            @if(
                Route::has('surat-masuk.cetak-label')
            )

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
         CONTENT LAYOUT
    ========================================================== --}}

    <div class="sm-layout">


        {{-- =====================================================
             MAIN
        ====================================================== --}}

        <main>

            <div class="sm-main-card">

                {{-- =================================================
                     LETTER HEADER
                ================================================== --}}

                <div class="sm-letter-head">

                    <div class="sm-letter-info">

                        <div class="sm-eyebrow">

                            <span class="sm-eyebrow-dot"></span>

                            <span class="sm-eyebrow-text">
                                Arsip Surat Masuk
                            </span>

                        </div>


                        <h2 class="sm-letter-title">
                            {{ $suratMasuk->perihal ?? 'Tanpa Perihal' }}
                        </h2>


                        <div class="sm-letter-meta">

                            <span class="sm-agenda">
                                {{ $suratMasuk->nomor_agenda ?? '#' . $suratMasuk->id }}
                            </span>

                            <span class="sm-number">
                                Nomor Surat:
                                <strong>
                                    {{ $suratMasuk->nomor_surat ?? '-' }}
                                </strong>
                            </span>

                        </div>

                    </div>


                    <span class="sm-status {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>

                </div>


                {{-- =================================================
                     MAIN BODY
                ================================================== --}}

                <div class="sm-main-body">


                    {{-- =================================================
                         INFORMASI SURAT
                    ================================================== --}}

                    <div class="sm-info-grid">

                        <div class="sm-info-item">

                            <span class="sm-info-label">
                                Pengirim
                            </span>

                            <p class="sm-info-value">
                                {{ $suratMasuk->pengirim ?? '-' }}
                            </p>

                        </div>


                        <div class="sm-info-item">

                            <span class="sm-info-label">
                                Kategori Surat
                            </span>

                            <p class="sm-info-value">
                                {{ $suratMasuk->kategori?->nama_kategori ?? '-' }}
                            </p>

                        </div>


                        <div class="sm-info-item">

                            <span class="sm-info-label">
                                Tanggal Surat
                            </span>

                            <p class="sm-info-value sm-info-date">

                                <svg
                                    class="sm-date-icon"
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

                                {{ $tanggalSurat }}

                            </p>

                        </div>


                        <div class="sm-info-item">

                            <span class="sm-info-label">
                                Tanggal Diterima
                            </span>

                            <p class="sm-info-value sm-info-date">

                                <svg
                                    class="sm-date-icon"
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

                                {{ $tanggalTerima }}

                            </p>

                        </div>


                        <div class="sm-info-item">

                            <span class="sm-info-label">
                                Diterima Oleh
                            </span>

                            <p class="sm-info-value">
                                {{ $suratMasuk->penerima?->name ?? '-' }}
                            </p>

                        </div>


                        <div class="sm-info-item">

                            <span class="sm-info-label">
                                Lokasi Arsip Fisik
                            </span>

                            <p class="sm-info-value">
                                {{ $suratMasuk->lokasi_arsip_fisik ?: '-' }}
                            </p>

                        </div>

                    </div>


                    {{-- =================================================
                         PERIHAL
                    ================================================== --}}

                    <section class="sm-section">

                        <div class="sm-section-head">

                            <div class="sm-section-icon">

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
                                        d="M4 6h16M4 12h16M4 18h10"
                                    />
                                </svg>

                            </div>

                            <div>

                                <p class="sm-section-title">
                                    Perihal Surat
                                </p>

                                <p class="sm-section-subtitle">
                                    Isi perihal atau pokok surat.
                                </p>

                            </div>

                        </div>


                        <div class="sm-text-box">

                            <p class="sm-text-content">
                                {{ $suratMasuk->perihal ?? 'Tanpa Perihal' }}
                            </p>

                        </div>

                    </section>


                    {{-- =================================================
                         RINGKASAN
                    ================================================== --}}

                    @if(filled($suratMasuk->ringkasan))

                        <section class="sm-section">

                            <div class="sm-section-head">

                                <div
                                    class="sm-section-icon"
                                    style="background:#f5f3ff;color:#7c3aed;"
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
                                            d="M4 5h16M4 9h16M4 13h10M4 17h8"
                                        />
                                    </svg>
                                </div>

                                <div>

                                    <p class="sm-section-title">
                                        Ringkasan Surat
                                    </p>

                                    <p class="sm-section-subtitle">
                                        Ringkasan isi dokumen.
                                    </p>

                                </div>

                            </div>


                            <div class="sm-text-box sm-summary-box">

                                <p class="sm-text-content">
                                    {{ $suratMasuk->ringkasan }}
                                </p>

                            </div>

                        </section>

                    @endif


                    {{-- =================================================
                         LAMPIRAN
                    ================================================== --}}

                    <section class="sm-section">

                        <div class="sm-attachment-top">

                            <div class="min-w-0">

                                <h3 class="sm-attachment-title">
                                    Berkas Lampiran Digital
                                </h3>

                                <p class="sm-attachment-description">
                                    Dokumen yang tersimpan pada arsip surat.
                                </p>

                            </div>


                            @if($lampiranExtension)

                                <span class="sm-extension">
                                    .{{ $lampiranExtension }}
                                </span>

                            @endif

                        </div>


                        @if(
                            !empty($lampiranPath) &&
                            !empty($lampiranUrl)
                        )

                            <div class="sm-file-bar">

                                <div class="sm-file-info">

                                    <div class="sm-file-icon">

                                        <svg
                                            class="w-3.5 h-3.5"
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
                                            class="w-3 h-3"
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
                                            class="w-3 h-3"
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
                                            class="w-9 h-9 text-slate-300"
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

        </main>


        {{-- =====================================================
             DISPOSISI
        ====================================================== --}}

        <aside>

            <div class="sm-disposition">

                <div class="sm-disposition-head">

                    <div class="sm-disposition-heading">

                        <div class="sm-disposition-heading-icon">

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
                                class="w-3 h-3"
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
                                                class="w-2.5 h-2.5 text-purple-300"
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

                                            @if(
                                                filled(
                                                    $isiDisposisi
                                                )
                                            )

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
                                    class="w-5 h-5"
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
                                    style="margin-top:10px;"
                                >
                                    <svg
                                        class="w-3 h-3"
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


                    <div class="sm-system-footer">

                        <span>
                            Sistem Kendali Surat Masuk
                        </span>

                    </div>

                </div>

            </div>

        </aside>

    </div>

</div>

@endsection