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
        'baru'           => 'status-baru',
        'diproses'       => 'status-diproses',
        'didisposisikan' => 'status-disposisi',
        'selesai'        => 'status-selesai',
        'diarsipkan'     => 'status-arsip',
    ];

    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
    $statusClass = $statusClasses[$status] ?? 'status-baru';


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
    | USER & HAK AKSES
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
        padding: 10px 16px 30px;
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
        border: 2px solid;
        border-radius: 10px;
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
        flex: 0 0 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .sm-alert-text {
        margin: 0;
        font-size: 10px;
        line-height: 1.45;
        font-weight: 700;
    }

    .sm-alert-close {
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 26px;
        border: 0;
        border-radius: 6px;
        background: transparent;
        cursor: pointer;
    }

    .sm-alert-success {
        border-color: #86efac;
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
        border-color: #fca5a5;
        background: #fff1f2;
        color: #991b1b;
    }

    .sm-alert-error .sm-alert-icon {
        background: #fee2e2;
        color: #dc2626;
    }

    .sm-alert-error .sm-alert-close {
        color: #f87171;
    }


    /* =========================================================
       HEADER
    ========================================================== */

    .sm-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 12px;
        padding: 13px 15px;
        border: 2px solid #334155;
        border-radius: 12px;
        background:
            linear-gradient(
                135deg,
                #0f172a,
                #1e293b 55%,
                #312e81
            );
        color: #fff;
        box-shadow:
            0 4px 10px rgba(15, 23, 42, .10);
    }

    .sm-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .sm-back {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 9px;
        background: rgba(255,255,255,.08);
        color: #fff;
        text-decoration: none;
        transition: .15s ease;
    }

    .sm-back:hover {
        background: rgba(255,255,255,.16);
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
        letter-spacing: -.02em;
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
        border-radius: 8px;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: .15s ease;
    }

    .sm-header-btn-edit {
        border: 1px solid rgba(245,158,11,.35);
        background: rgba(245,158,11,.14);
        color: #fde68a;
    }

    .sm-header-btn-edit:hover {
        background: rgba(245,158,11,.24);
    }

    .sm-header-btn-print {
        border: 1px solid rgba(16,185,129,.35);
        background: rgba(16,185,129,.12);
        color: #a7f3d0;
    }

    .sm-header-btn-print:hover {
        background: rgba(16,185,129,.22);
    }

    .sm-header-btn-label {
        border: 1px solid rgba(96,165,250,.35);
        background: rgba(96,165,250,.12);
        color: #bfdbfe;
    }

    .sm-header-btn-label:hover {
        background: rgba(96,165,250,.22);
    }


    /* =========================================================
       MAIN GRID
    ========================================================== */

    .sm-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.85fr) minmax(300px, .9fr);
        gap: 12px;
        align-items: start;
    }


    /* =========================================================
       CARD
    ========================================================== */

    .sm-card {
        overflow: hidden;
        border: 2px solid #475569;
        border-radius: 11px;
        background: #fff;
        box-shadow:
            0 4px 10px rgba(15,23,42,.06);
    }

    .sm-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 14px;
        border-bottom: 2px solid #94a3b8;
        background:
            linear-gradient(
                to right,
                #f8fafc,
                #ffffff
            );
    }

    .sm-card-header-content {
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
        box-shadow: 0 0 0 3px #e0e7ff;
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
        border-radius: 6px;
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

    .sm-letter-number {
        font-size: 9px;
        color: #64748b;
        overflow-wrap: anywhere;
    }

    .sm-letter-number strong {
        color: #1e293b;
    }

    .sm-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 0 10px;
        flex: 0 0 auto;
        border: 2px solid;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status-baru {
        border-color: #93c5fd;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .status-diproses {
        border-color: #fcd34d;
        background: #fffbeb;
        color: #b45309;
    }

    .status-disposisi {
        border-color: #c4b5fd;
        background: #f5f3ff;
        color: #6d28d9;
    }

    .status-selesai {
        border-color: #86efac;
        background: #f0fdf4;
        color: #15803d;
    }

    .status-arsip {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #475569;
    }


    /* =========================================================
       CARD BODY
    ========================================================== */

    .sm-card-body {
        padding: 13px;
    }


    /* =========================================================
       INFO TABLE
    ========================================================== */

    .sm-info-table {
        width: 100%;
        overflow: hidden;
        border: 2px solid #475569;
        border-radius: 8px;
        background: #fff;
    }

    .sm-info-grid {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .sm-info-item {
        min-width: 0;
        min-height: 68px;
        padding: 9px 10px;
        border-right: 2px solid #94a3b8;
        border-bottom: 2px solid #94a3b8;
        background: #fff;
    }

    .sm-info-item:nth-child(2n) {
        border-right: 0;
    }

    .sm-info-item:nth-last-child(-n + 2) {
        border-bottom: 0;
    }

    .sm-info-label {
        display: block;
        margin-bottom: 4px;
        font-size: 8px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #64748b;
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
        color: #475569;
    }


    /* =========================================================
       CONTENT SECTION
    ========================================================== */

    .sm-section {
        margin-top: 12px;
        padding-top: 12px;
        border-top: 2px solid #94a3b8;
    }

    .sm-section-heading {
        display: flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 7px;
    }

    .sm-section-marker {
        width: 4px;
        height: 19px;
        border-radius: 999px;
        background: #4f46e5;
    }

    .sm-section-title {
        margin: 0;
        font-size: 10px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #334155;
    }

    .sm-content-box {
        padding: 10px 11px;
        border: 2px solid #94a3b8;
        border-radius: 8px;
        background: #f8fafc;
    }

    .sm-content-text {
        margin: 0;
        font-size: 10px;
        line-height: 1.65;
        font-weight: 550;
        color: #334155;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .sm-summary-box {
        background: #fff;
    }


    /* =========================================================
       LAMPIRAN
    ========================================================== */

    .sm-attachment-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
    }

    .sm-attachment-title {
        margin: 0;
        font-size: 10px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #334155;
    }

    .sm-attachment-name {
        margin: 3px 0 0;
        font-size: 8px;
        line-height: 1.4;
        color: #64748b;
        overflow-wrap: anywhere;
    }

    .sm-extension {
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
    }

    .sm-file-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 8px;
        padding: 8px 9px;
        border: 2px solid #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
    }

    .sm-file-meta {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 0;
    }

    .sm-file-icon {
        width: 28px;
        height: 28px;
        flex: 0 0 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #e0e7ff;
        color: #4338ca;
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

    .sm-file-status {
        margin: 2px 0 0;
        font-size: 7.5px;
        color: #64748b;
    }

    .sm-file-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .sm-file-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 30px;
        padding: 0 9px;
        border-radius: 7px;
        font-size: 8.5px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .sm-file-btn-open {
        border: 2px solid #4338ca;
        background: #4f46e5;
        color: #fff;
    }

    .sm-file-btn-open:hover {
        background: #4338ca;
    }

    .sm-file-btn-download {
        border: 2px solid #94a3b8;
        background: #fff;
        color: #475569;
    }

    .sm-file-btn-download:hover {
        border-color: #64748b;
        background: #f8fafc;
    }

    .sm-viewer {
        overflow: hidden;
        padding: 6px;
        border: 2px solid #475569;
        border-radius: 8px;
        background: #0f172a;
    }

    .sm-image-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 230px;
        border-radius: 6px;
        background: #111827;
    }

    .sm-image {
        display: block;
        width: auto;
        max-width: 100%;
        max-height: 600px;
        object-fit: contain;
    }

    .sm-pdf {
        width: 100%;
        height: 610px;
        display: block;
        border: 0;
        border-radius: 6px;
        background: #fff;
    }

    .sm-viewer-empty {
        min-height: 230px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 25px 15px;
        border-radius: 6px;
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
        max-width: 420px;
        margin: 3px auto 0;
        font-size: 8px;
        line-height: 1.5;
        color: #64748b;
    }

    .sm-no-file {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px;
        border: 2px dashed #94a3b8;
        border-radius: 8px;
        background: #f8fafc;
        color: #64748b;
    }

    .sm-no-file p {
        margin: 0;
        font-size: 9px;
        line-height: 1.5;
        font-weight: 600;
    }


    /* =========================================================
       DISPOSISI CARD
    ========================================================== */

    .sm-disposition-card {
        overflow: hidden;
        border: 2px solid #475569;
        border-radius: 11px;
        background: #fff;
        box-shadow:
            0 4px 10px rgba(15,23,42,.06);
    }

    .sm-disposition-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 11px 12px;
        border-bottom: 2px solid #94a3b8;
        background:
            linear-gradient(
                to right,
                #f5f3ff,
                #fff
            );
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
        color: #64748b;
    }

    .sm-create-disposition {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 29px;
        padding: 0 8px;
        border: 2px solid #7c3aed;
        border-radius: 7px;
        background: #7c3aed;
        color: #fff;
        font-size: 8px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .sm-create-disposition:hover {
        background: #6d28d9;
    }

    .sm-disposition-body {
        padding: 11px 12px;
    }

    .sm-disposition-list {
        position: relative;
    }

    .sm-disposition-item {
        position: relative;
        padding-left: 21px;
        padding-bottom: 14px;
    }

    .sm-disposition-item:last-child {
        padding-bottom: 0;
    }

    .sm-disposition-line {
        position: absolute;
        top: 7px;
        bottom: 0;
        left: 5px;
        width: 2px;
        background: #ddd6fe;
    }

    .sm-disposition-item:last-child .sm-disposition-line {
        display: none;
    }

    .sm-disposition-dot {
        position: absolute;
        top: 4px;
        left: 0;
        width: 12px;
        height: 12px;
        border: 3px solid #ede9fe;
        border-radius: 999px;
        background: #7c3aed;
    }

    .sm-disposition-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 7px;
    }

    .sm-disposition-users {
        min-width: 0;
    }

    .sm-disposition-route {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 4px;
        font-size: 8.5px;
        font-weight: 800;
        line-height: 1.4;
    }

    .sm-disposition-from {
        color: #1e293b;
        overflow-wrap: anywhere;
    }

    .sm-disposition-to {
        color: #6d28d9;
        overflow-wrap: anywhere;
    }

    .sm-disposition-status {
        display: inline-flex;
        align-items: center;
        min-height: 21px;
        padding: 0 6px;
        flex: 0 0 auto;
        border: 1px solid;
        border-radius: 5px;
        font-size: 7px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .disp-menunggu {
        border-color: #c4b5fd;
        background: #f5f3ff;
        color: #6d28d9;
    }

    .disp-diproses {
        border-color: #fcd34d;
        background: #fffbeb;
        color: #b45309;
    }

    .disp-selesai {
        border-color: #86efac;
        background: #f0fdf4;
        color: #15803d;
    }

    .disp-ditolak {
        border-color: #fca5a5;
        background: #fff1f2;
        color: #be123c;
    }

    .sm-disposition-content {
        margin-top: 6px;
        padding: 8px 9px;
        border: 2px solid #cbd5e1;
        border-radius: 7px;
        background: #f8fafc;
    }

    .sm-disposition-text {
        margin: 0;
        font-size: 8.5px;
        line-height: 1.55;
        color: #475569;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    .sm-disposition-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 4px 8px;
        margin-top: 5px;
    }

    .sm-disposition-date {
        font-size: 7.5px;
        font-weight: 700;
        color: #94a3b8;
    }

    .sm-disposition-deadline {
        font-size: 7.5px;
        font-weight: 700;
        color: #d97706;
    }

    .sm-empty {
        padding: 24px 8px;
        text-align: center;
    }

    .sm-empty-icon {
        width: 40px;
        height: 40px;
        margin: 0 auto 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #e2e8f0;
        border-radius: 999px;
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


    /* =========================================================
       FOOTER
    ========================================================== */

    .sm-system-footer {
        margin-top: 9px;
        padding-top: 9px;
        border-top: 2px solid #e2e8f0;
        text-align: center;
    }

    .sm-system-footer span {
        font-size: 7px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 980px) {

        .sm-main-grid {
            grid-template-columns: 1fr;
        }

        .sm-disposition-card {
            order: 2;
        }

        .sm-pdf {
            height: 570px;
        }
    }


    @media (max-width: 700px) {

        .sm-detail-page {
            padding: 8px 10px 20px;
        }

        .sm-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .sm-header-actions {
            width: 100%;
            justify-content: flex-start;
            padding-top: 8px;
            border-top: 1px solid rgba(255,255,255,.10);
        }

        .sm-header-btn {
            flex: 1 1 auto;
        }

        .sm-card-header {
            flex-direction: column;
        }

        .sm-status {
            align-self: flex-start;
        }

        .sm-card-body {
            padding: 10px;
        }

        .sm-info-grid {
            grid-template-columns: 1fr;
        }

        .sm-info-item,
        .sm-info-item:nth-child(2n) {
            border-right: 0;
            border-bottom: 2px solid #94a3b8;
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

        .sm-attachment-head {
            align-items: flex-start;
        }

        .sm-pdf {
            height: 500px;
        }

        .sm-disposition-header {
            align-items: flex-start;
        }
    }


    @media (max-width: 460px) {

        .sm-header-title {
            font-size: 16px;
        }

        .sm-header-subtitle {
            font-size: 8px;
        }

        .sm-letter-title {
            font-size: 16px;
        }

        .sm-letter-number {
            font-size: 8px;
        }

        .sm-header-actions {
            flex-direction: column;
        }

        .sm-header-btn {
            width: 100%;
        }

        .sm-pdf {
            height: 450px;
        }

        .sm-disposition-top {
            flex-direction: column;
        }

        .sm-disposition-status {
            align-self: flex-start;
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
                aria-label="Kembali ke surat masuk"
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
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"
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
         MAIN GRID
    ========================================================== --}}

    <div class="sm-main-grid">


        {{-- =====================================================
             KOLOM UTAMA
        ====================================================== --}}

        <div>

            {{-- =================================================
                 SURAT
            ================================================== --}}

            <div class="sm-card">

                <div class="sm-card-header">

                    <div class="sm-card-header-content">

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

                            <span class="sm-letter-number">
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


                <div class="sm-card-body">

                    {{-- =================================================
                         INFORMASI UTAMA
                    ================================================== --}}

                    <div class="sm-info-table">

                        <div class="sm-info-grid">


                            {{-- PENGIRIM --}}

                            <div class="sm-info-item">

                                <span class="sm-info-label">
                                    Pengirim
                                </span>

                                <p class="sm-info-value">
                                    {{ $suratMasuk->pengirim ?? '-' }}
                                </p>

                            </div>


                            {{-- KATEGORI --}}

                            <div class="sm-info-item">

                                <span class="sm-info-label">
                                    Kategori Surat
                                </span>

                                <p class="sm-info-value">
                                    {{ $suratMasuk->kategori?->nama_kategori ?? '-' }}
                                </p>

                            </div>


                            {{-- TANGGAL SURAT --}}

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


                            {{-- TANGGAL DITERIMA --}}

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


                            {{-- DITERIMA OLEH --}}

                            <div class="sm-info-item">

                                <span class="sm-info-label">
                                    Diterima Oleh
                                </span>

                                <p class="sm-info-value">
                                    {{ $suratMasuk->penerima?->name ?? '-' }}
                                </p>

                            </div>


                            {{-- LOKASI ARSIP --}}

                            <div class="sm-info-item">

                                <span class="sm-info-label">
                                    Lokasi Arsip Fisik
                                </span>

                                <p class="sm-info-value">
                                    {{ $suratMasuk->lokasi_arsip_fisik ?: '-' }}
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         PERIHAL
                    ================================================== --}}

                    <section class="sm-section">

                        <div class="sm-section-heading">

                            <div class="sm-section-marker"></div>

                            <p class="sm-section-title">
                                Perihal Surat
                            </p>

                        </div>

                        <div class="sm-content-box">

                            <p class="sm-content-text">
                                {{ $suratMasuk->perihal ?? 'Tanpa Perihal' }}
                            </p>

                        </div>

                    </section>


                    {{-- =================================================
                         RINGKASAN
                    ================================================== --}}

                    @if(filled($suratMasuk->ringkasan))

                        <section class="sm-section">

                            <div class="sm-section-heading">

                                <div
                                    class="sm-section-marker"
                                    style="background:#4f46e5;"
                                ></div>

                                <p class="sm-section-title">
                                    Ringkasan Surat
                                </p>

                            </div>

                            <div class="sm-content-box sm-summary-box">

                                <p class="sm-content-text">
                                    {{ $suratMasuk->ringkasan }}
                                </p>

                            </div>

                        </section>

                    @endif


                    {{-- =================================================
                         LAMPIRAN
                    ================================================== --}}

                    <section class="sm-section">

                        <div class="sm-attachment-head">

                            <div class="min-w-0">

                                <h3 class="sm-attachment-title">
                                    Berkas Lampiran Digital
                                </h3>

                                @if($lampiranNama)

                                    <p class="sm-attachment-name">
                                        {{ $lampiranNama }}
                                    </p>

                                @endif

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

                            {{-- FILE BAR --}}

                            <div class="sm-file-bar">

                                <div class="sm-file-meta">

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
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.414L18 8.414V19a2 2 0 01-2 2z"
                                            />
                                        </svg>

                                    </div>

                                    <div class="min-w-0">

                                        <p class="sm-file-name">
                                            {{ $lampiranNama }}
                                        </p>

                                        <p class="sm-file-status">
                                            Dokumen tersimpan
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


                            {{-- VIEWER --}}

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
                                            class="w-9 h-9 text-slate-400"
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


        {{-- =====================================================
             KOLOM DISPOSISI
        ====================================================== --}}

        <aside>

            <div class="sm-disposition-card">

                {{-- HEADER --}}

                <div class="sm-disposition-header">

                    <div class="min-w-0">

                        <h3 class="sm-disposition-title">
                            Riwayat Disposisi
                        </h3>

                        <p class="sm-disposition-subtitle">
                            Instruksi dan tindak lanjut surat.
                        </p>

                    </div>


                    @if(
                        $canManage &&
                        Route::has('disposisi.create')
                    )

                        <a
                            href="{{ route('disposisi.create', $suratMasuk) }}"
                            class="sm-create-disposition"
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


                {{-- BODY --}}

                <div class="sm-disposition-body">

                    @if($disposisis->count())

                        <div class="sm-disposition-list">

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
                                        'menunggu' => 'disp-menunggu',
                                        'diproses' => 'disp-diproses',
                                        'selesai'  => 'disp-selesai',
                                        'ditolak'  => 'disp-ditolak',
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
                                        ?? 'disp-menunggu';


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


                                <div class="sm-disposition-item">

                                    <div class="sm-disposition-line"></div>

                                    <div class="sm-disposition-dot"></div>


                                    <div class="sm-disposition-top">

                                        <div class="sm-disposition-users">

                                            <div class="sm-disposition-route">

                                                <span class="sm-disposition-from">
                                                    {{ $d->dari?->name ?? '-' }}
                                                </span>

                                                <svg
                                                    class="w-2.5 h-2.5 text-purple-400"
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

                                                <span class="sm-disposition-to">
                                                    {{ $d->kepada?->name ?? '-' }}
                                                </span>

                                            </div>

                                        </div>


                                        <span
                                            class="sm-disposition-status {{ $disposisiClass }}"
                                        >
                                            {{ $disposisiLabel }}
                                        </span>

                                    </div>


                                    <div class="sm-disposition-content">

                                        <p class="sm-disposition-text">

                                            @if(
                                                filled(
                                                    $isiDisposisi
                                                )
                                            )

                                                {{ $isiDisposisi }}

                                            @else

                                                <span class="text-slate-400">
                                                    Tidak ada instruksi atau catatan.
                                                </span>

                                            @endif

                                        </p>

                                    </div>


                                    <div class="sm-disposition-meta">

                                        @if($batasWaktu)

                                            <span class="sm-disposition-deadline">
                                                Batas:
                                                {{ $batasWaktu }}
                                            </span>

                                        @endif

                                        <span class="sm-disposition-date">
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
                                    class="sm-create-disposition"
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