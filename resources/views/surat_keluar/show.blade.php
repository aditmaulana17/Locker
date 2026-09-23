@extends('layouts.app')

@section('title', 'Detail Surat Keluar')

@section('content')

@php
/*
|--------------------------------------------------------------------------
| ROLE PENGGUNA
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

if ($userRole === 'staf') {
    $userRole = 'staff';
}

$canManageSurat = in_array(
    $userRole,
    [
        'admin',
        'pimpinan',
    ],
    true
);

/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

$status = strtolower(
    trim(
        (string) (
            $suratKeluar->status
            ?? 'draft'
        )
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
    'draft'      => 'sk-status-draft',
    'diproses'   => 'sk-status-diproses',
    'disetujui'  => 'sk-status-disetujui',
    'dikirim'    => 'sk-status-dikirim',
    'diarsipkan' => 'sk-status-arsip',
];

$statusLabel =
    $statusLabels[$status]
    ?? ucfirst($status);

$statusClass =
    $statusClasses[$status]
    ?? 'sk-status-draft';

/*
|--------------------------------------------------------------------------
| TANGGAL
|--------------------------------------------------------------------------
*/

$tanggalSurat = '-';
$tanggalKeluar = '-';

try {
    if ($suratKeluar->tanggal_surat) {
        $tanggalSurat =
            \Illuminate\Support\Carbon::parse(
                $suratKeluar->tanggal_surat
            )->translatedFormat('d F Y');
    }
} catch (\Throwable $e) {
    $tanggalSurat = '-';
}

try {
    if ($suratKeluar->tanggal_keluar) {
        $tanggalKeluar =
            \Illuminate\Support\Carbon::parse(
                $suratKeluar->tanggal_keluar
            )->translatedFormat('d F Y');
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
    (string) (
        $suratKeluar->pengirim
        ?? ''
    )
);

if ($tujuanSurat === '') {
    $tujuanSurat = '-';
}

$nomorSurat = trim(
    (string) (
        $suratKeluar->nomor_surat
        ?? ''
    )
);

if ($nomorSurat === '') {
    $nomorSurat = '-';
}

$perihal = trim(
    (string) (
        $suratKeluar->perihal
        ?? ''
    )
);

if ($perihal === '') {
    $perihal = 'Tanpa Perihal';
}

$kategoriNama =
    $suratKeluar->kategori?->nama_kategori
    ?? '-';

$pembuatNama =
    $suratKeluar->pembuat?->name
    ?? '-';

/*
|--------------------------------------------------------------------------
| LAMPIRAN
|--------------------------------------------------------------------------
*/

$lampiranPath =
    $suratKeluar->lampiran_file
    ?? null;

$lampiranUrl = null;

if (!empty($lampiranPath)) {

    if (
        filter_var(
            $lampiranPath,
            FILTER_VALIDATE_URL
        )
    ) {
        $lampiranUrl =
            $lampiranPath;

    } elseif (
        \Illuminate\Support\Facades\Route::has(
            'surat-keluar.preview-lampiran'
        )
    ) {
        $lampiranUrl =
            route(
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

$isImage =
    in_array(
        $lampiranExtension,
        [
            'jpg',
            'jpeg',
            'png',
            'webp',
            'gif',
        ],
        true
    );

$isPdf =
    $lampiranExtension === 'pdf';

@endphp

<style>
    /* =========================================================
       PAGE
    ========================================================== */

    .sk-detail-page {
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        padding: 14px 18px 36px;
        color: #334155;
    }

    .sk-detail-page *,
    .sk-detail-page *::before,
    .sk-detail-page *::after {
        box-sizing: border-box;
    }

    /* =========================================================
       ALERT
    ========================================================== */

    .sk-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
        padding: 11px 14px;
        border-radius: 11px;
    }

    .sk-alert-success {
        background: #f0fdf4;
        color: #166534;
    }

    .sk-alert-error {
        background: #fff1f2;
        color: #991b1b;
    }

    .sk-alert-inner {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .sk-alert-icon {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        border-radius: 8px;
    }

    .sk-alert-success .sk-alert-icon {
        background: #dcfce7;
        color: #16a34a;
    }

    .sk-alert-error .sk-alert-icon {
        background: #ffe4e6;
        color: #dc2626;
    }

    .sk-alert-text {
        margin: 0;
        font-size: 11px;
        line-height: 1.5;
        font-weight: 700;
    }

    .sk-alert-close {
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

    .sk-alert-close:hover {
        background: rgba(15, 23, 42, .05);
    }

    /* =========================================================
       HEADER
    ========================================================== */

    .sk-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        min-height: 84px;
        margin-bottom: 16px;
        padding: 17px 20px;
        overflow: hidden;
        border-radius: 16px;
        background:
            linear-gradient(
                135deg,
                #0f172a 0%,
                #1e293b 55%,
                #064e3b 100%
            );
        color: #fff;
        box-shadow:
            0 10px 30px rgba(15, 23, 42, .10);
    }

    .sk-header::after {
        content: "";
        position: absolute;
        width: 180px;
        height: 180px;
        right: -70px;
        top: -100px;
        border-radius: 50%;
        background: rgba(16, 185, 129, .14);
        pointer-events: none;
    }

    .sk-header-left {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .sk-back {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 40px;
        border-radius: 10px;
        background: rgba(255, 255, 255, .09);
        color: #fff;
        text-decoration: none;
        transition: .16s ease;
    }

    .sk-back:hover {
        background: rgba(255, 255, 255, .17);
        transform: translateX(-2px);
    }

    .sk-header-content {
        min-width: 0;
    }

    .sk-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 3px;
        font-size: 9px;
        color: #94a3b8;
    }

    .sk-breadcrumb a {
        color: #cbd5e1;
        text-decoration: none;
    }

    .sk-breadcrumb a:hover {
        color: #fff;
    }

    .sk-header-title {
        margin: 0;
        font-size: 20px;
        line-height: 1.3;
        font-weight: 800;
        letter-spacing: -.02em;
    }

    .sk-header-subtitle {
        margin: 4px 0 0;
        font-size: 10px;
        line-height: 1.5;
        color: #cbd5e1;
    }

    .sk-header-action {
        position: relative;
        z-index: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 35px;
        padding: 0 12px;
        border-radius: 8px;
        background: rgba(245, 158, 11, .15);
        color: #fde68a;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: .15s ease;
    }

    .sk-header-action:hover {
        background: rgba(245, 158, 11, .25);
    }

    /* =========================================================
       MAIN GRID
    ========================================================== */

    .sk-top-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 2fr)
            minmax(330px, .9fr);
        gap: 16px;
        align-items: stretch;
    }

    .sk-left-column {
        min-width: 0;
        min-height: 100%;
        display: flex;
        flex-direction: column;
    }

    .sk-right-column {
        min-width: 0;
        min-height: 100%;
        display: flex;
    }

    /* =========================================================
       CARD
    ========================================================== */

    .sk-card {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow:
            0 7px 25px rgba(15, 23, 42, .055);
    }

    /* =========================================================
       CARD HEADER
    ========================================================== */

    .sk-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 61px;
        padding: 11px 15px;
        border-bottom: 1px solid #e5e7eb;
        background:
            linear-gradient(
                135deg,
                #f8fafc 0%,
                #ffffff 55%,
                #ecfdf5 100%
            );
    }

    .sk-card-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .sk-card-heading-icon {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        border-radius: 9px;
        background: #ecfdf5;
        color: #059669;
    }

    .sk-card-title {
        margin: 0;
        font-size: 12px;
        line-height: 1.35;
        font-weight: 800;
        color: #1e293b;
    }

    .sk-card-subtitle {
        margin: 2px 0 0;
        font-size: 9px;
        line-height: 1.4;
        color: #94a3b8;
    }

    /* =========================================================
       STATUS
    ========================================================== */

    .sk-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 27px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .sk-status-draft {
        background: #f1f5f9;
        color: #475569;
    }

    .sk-status-diproses {
        background: #fffbeb;
        color: #b45309;
    }

    .sk-status-disetujui {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .sk-status-dikirim {
        background: #f0fdf4;
        color: #15803d;
    }

    .sk-status-arsip {
        background: #f5f3ff;
        color: #6d28d9;
    }

    /* =========================================================
       INFORMASI SURAT
    ========================================================== */

    .sk-info-card {
        flex: 0 0 auto;
        overflow: hidden;
    }

    .sk-info-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .sk-info-table {
        width: 100%;
        min-width: 760px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .sk-info-table td {
        width: 33.333%;
        height: 108px;
        padding: 18px 19px;
        vertical-align: top;
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
    }

    .sk-info-table td:last-child {
        border-right: 0;
    }

    .sk-info-table tr:last-child td {
        border-bottom: 0;
    }

    .sk-info-table tr:first-child td:nth-child(odd),
    .sk-info-table tr:last-child td:nth-child(odd) {
        background: #f8fafc;
    }

    .sk-info-label {
        display: block;
        margin-bottom: 9px;
        font-size: 8px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
    }

    .sk-info-value {
        margin: 0;
        font-size: 12px;
        line-height: 1.6;
        font-weight: 750;
        color: #1e293b;
        overflow-wrap: anywhere;
    }

    .sk-info-date {
        display: flex;
        align-items: flex-start;
        gap: 7px;
    }

    .sk-date-icon {
        width: 15px;
        height: 15px;
        flex: 0 0 15px;
        margin-top: 2px;
        color: #059669;
    }

    /* =========================================================
       PERIHAL
    ========================================================== */

    .sk-perihal-card {
        width: 100%;
        min-height: 0;
        margin-top: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
    }

    .sk-perihal-body {
        flex: 1 1 auto;
        display: flex;
        padding: 16px 17px 17px;
    }

    .sk-perihal-box {
        width: 100%;
        min-height: 110px;
        flex: 1 1 auto;
        display: flex;
        align-items: flex-start;
        padding: 15px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
        background: #f8fafc;
    }

    .sk-perihal-text {
        margin: 0;
        font-size: 12px;
        line-height: 1.75;
        color: #475569;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }

    /* =========================================================
       SIDE
    ========================================================== */

    .sk-side-card {
        width: 100%;
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .sk-side-body {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        padding: 17px;
    }

    .sk-side-status {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 17px 10px;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
        background: #f8fafc;
    }

    .sk-side-status .sk-status {
        min-height: 34px;
        padding: 0 15px;
        font-size: 10px;
    }

    .sk-side-list {
        margin-top: 12px;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
        overflow: hidden;
    }

    .sk-side-item {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 14px;
        border-bottom: 1px solid #e5e7eb;
    }

    .sk-side-item:last-child {
        border-bottom: 0;
    }

    .sk-side-label {
        flex: 0 0 42%;
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #94a3b8;
    }

    .sk-side-value {
        margin: 0;
        text-align: right;
        font-size: 10px;
        line-height: 1.5;
        font-weight: 750;
        color: #1e293b;
        overflow-wrap: anywhere;
    }

    .sk-side-note {
        margin-top: 12px;
        padding: 12px 13px;
        border-radius: 10px;
        background: #ecfdf5;
        color: #047857;
    }

    .sk-side-note-title {
        margin: 0 0 4px;
        font-size: 9px;
        font-weight: 800;
    }

    .sk-side-note-text {
        margin: 0;
        font-size: 8px;
        line-height: 1.6;
    }

    /* =========================================================
       ATTACHMENT
    ========================================================== */

    .sk-attachment-card {
        width: 100%;
        margin-top: 16px;
        overflow: hidden;
    }

    .sk-attachment-body {
        padding: 16px 17px 18px;
    }

    .sk-extension {
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
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
    }

    /* =========================================================
       FILE BAR
    ========================================================== */

    .sk-file-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        width: 100%;
        margin-bottom: 12px;
        padding: 11px;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
        background: #f8fafc;
    }

    .sk-file-info {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .sk-file-icon {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 35px;
        border-radius: 9px;
        background: #ecfdf5;
        color: #059669;
    }

    .sk-file-name {
        margin: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 10px;
        font-weight: 750;
        color: #334155;
    }

    .sk-file-state {
        margin: 2px 0 0;
        font-size: 8px;
        color: #94a3b8;
    }

    .sk-file-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex: 0 0 auto;
    }

    .sk-file-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        min-height: 32px;
        padding: 0 11px;
        border-radius: 8px;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: .15s ease;
    }

    .sk-file-btn-open {
        background: #059669;
        color: #fff;
    }

    .sk-file-btn-open:hover {
        background: #047857;
    }

    .sk-file-btn-download {
        border: 1px solid #e5e7eb;
        background: #fff;
        color: #475569;
    }

    .sk-file-btn-download:hover {
        background: #f1f5f9;
    }

    /* =========================================================
       VIEWER
    ========================================================== */

    .sk-viewer {
        width: 100%;
        overflow: hidden;
        padding: 5px;
        border-radius: 12px;
        background: #0f172a;
    }

    .sk-image-wrapper {
        width: 100%;
        min-height: 340px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: auto;
        border-radius: 8px;
        background: #111827;
    }

    .sk-image {
        display: block;
        width: auto;
        max-width: 100%;
        max-height: 720px;
        object-fit: contain;
    }

    .sk-pdf {
        display: block;
        width: 100%;
        height: 720px;
        border: 0;
        border-radius: 8px;
        background: #fff;
    }

    .sk-viewer-empty {
        min-height: 300px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 30px;
        border-radius: 8px;
        background: #fff;
        text-align: center;
    }

    .sk-viewer-empty-title {
        margin: 9px 0 0;
        font-size: 11px;
        font-weight: 800;
        color: #334155;
    }

    .sk-viewer-empty-text {
        max-width: 450px;
        margin: 5px auto 0;
        font-size: 9px;
        line-height: 1.6;
        color: #94a3b8;
    }

    .sk-no-file {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 13px;
        border-radius: 10px;
        background: #f8fafc;
        color: #94a3b8;
    }

    .sk-no-file p {
        margin: 0;
        font-size: 9px;
        line-height: 1.5;
    }

    /* =========================================================
       FOOTER
    ========================================================== */

    .sk-system-footer {
        margin-top: 12px;
        text-align: center;
    }

    .sk-system-footer span {
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
        .sk-top-grid {
            grid-template-columns: 1fr;
            align-items: start;
        }

        .sk-right-column {
            min-height: auto;
        }

        .sk-side-card {
            height: auto;
        }

        .sk-perihal-card {
            flex: 0 0 auto;
        }

        .sk-perihal-body {
            flex: 0 0 auto;
        }
    }

    /* =========================================================
       MOBILE
    ========================================================== */

    @media (max-width: 700px) {
        .sk-detail-page {
            padding: 9px 10px 25px;
        }

        .sk-header {
            align-items: flex-start;
            flex-direction: column;
            padding: 15px;
        }

        .sk-header-left {
            width: 100%;
        }

        .sk-header-action {
            width: 100%;
        }

        .sk-card-header {
            padding: 11px 13px;
        }

        .sk-card-subtitle {
            font-size: 8px;
        }

        .sk-info-table-wrap {
            overflow-x: auto;
        }

        .sk-info-table {
            min-width: 680px;
        }

        .sk-info-table td {
            height: 100px;
            padding: 15px;
        }

        .sk-perihal-body,
        .sk-attachment-body {
            padding: 14px;
        }

        .sk-file-bar {
            align-items: stretch;
            flex-direction: column;
        }

        .sk-file-actions {
            width: 100%;
        }

        .sk-file-btn {
            flex: 1;
        }

        .sk-image-wrapper {
            min-height: 250px;
        }

        .sk-pdf {
            height: 550px;
        }
    }

    /* =========================================================
       SMALL MOBILE
    ========================================================== */

    @media (max-width: 470px) {
        .sk-header-title {
            font-size: 18px;
        }

        .sk-header-subtitle {
            font-size: 9px;
        }

        .sk-info-table {
            min-width: 0;
        }

        .sk-info-table,
        .sk-info-table tbody,
        .sk-info-table tr,
        .sk-info-table td {
            display: block;
            width: 100%;
        }

        .sk-info-table td {
            height: auto;
            min-height: 82px;
            border-right: 0;
        }

        .sk-info-value {
            font-size: 11px;
        }

        .sk-file-actions {
            flex-direction: column;
        }

        .sk-file-btn {
            width: 100%;
        }

        .sk-pdf {
            height: 450px;
        }
    }
</style>

<div class="sk-detail-page">

{{-- =====================================================
     ALERT SUCCESS
====================================================== --}}

@if(session('success'))
    <div
        class="sk-alert sk-alert-success"
        role="alert"
    >
        <div class="sk-alert-inner">

            <div class="sk-alert-icon">

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

            <p class="sk-alert-text">
                {{ session('success') }}
            </p>

        </div>

        <button
            type="button"
            class="sk-alert-close"
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

{{-- =====================================================
     ALERT ERROR
====================================================== --}}

@if(session('error'))
    <div
        class="sk-alert sk-alert-error"
        role="alert"
    >
        <div class="sk-alert-inner">

            <div class="sk-alert-icon">

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

            <p class="sk-alert-text">
                {{ session('error') }}
            </p>

        </div>

        <button
            type="button"
            class="sk-alert-close"
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

{{-- =====================================================
     HEADER
====================================================== --}}

<div class="sk-header">

    <div class="sk-header-left">

        <a
            href="{{ route('surat-keluar.index') }}"
            class="sk-back"
            title="Kembali"
            aria-label="Kembali ke Surat Keluar"
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

        <div class="sk-header-content">

            <div class="sk-breadcrumb">

                <a href="{{ route('surat-keluar.index') }}">
                    Surat Keluar
                </a>

                <span>/</span>

                <span>Detail Arsip</span>

            </div>

            <h1 class="sk-header-title">
                Detail Surat Keluar
            </h1>

            <p class="sk-header-subtitle">
                Informasi surat, status arsip, dan berkas digital.
            </p>

        </div>

    </div>

    {{-- =================================================
         EDIT HANYA ADMIN / PIMPINAN
    ================================================== --}}

    @if(
        $canManageSurat &&
        \Illuminate\Support\Facades\Route::has(
            'surat-keluar.edit'
        )
    )

        <a
            href="{{ route('surat-keluar.edit', $suratKeluar) }}"
            class="sk-header-action"
            title="Edit arsip surat"
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
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h5m5.5-1.5a2.121 2.121 0 003 0l5.5-5.5a2.121 2.121 0 003-3L15 10.5V13h-2.5z"
                />
            </svg>

            Edit Arsip
        </a>

    @endif

</div>

{{-- =====================================================
     TOP GRID
====================================================== --}}

<div class="sk-top-grid">

    {{-- =================================================
         KIRI
    ================================================== --}}

    <div class="sk-left-column">

        {{-- INFORMASI SURAT --}}

        <div class="sk-card sk-info-card">

            <div class="sk-card-header">

                <div class="sk-card-heading">

                    <div class="sk-card-heading-icon">

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

                        <h2 class="sk-card-title">
                            Informasi Surat
                        </h2>

                        <p class="sk-card-subtitle">
                            Data utama arsip surat keluar.
                        </p>

                    </div>

                </div>

                <span class="sk-status {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>

            </div>

            <div class="sk-info-table-wrap">

                <table class="sk-info-table">

                    <tbody>

                        <tr>

                            <td>

                                <span class="sk-info-label">
                                    Nomor Surat
                                </span>

                                <p class="sk-info-value">
                                    {{ $nomorSurat }}
                                </p>

                            </td>

                            <td>

                                <span class="sk-info-label">
                                    Tujuan Surat
                                </span>

                                <p class="sk-info-value">
                                    {{ $tujuanSurat }}
                                </p>

                            </td>

                            <td>

                                <span class="sk-info-label">
                                    Kategori Surat
                                </span>

                                <p class="sk-info-value">
                                    {{ $kategoriNama }}
                                </p>

                            </td>

                        </tr>

                        <tr>

                            <td>

                                <span class="sk-info-label">
                                    Tanggal Surat
                                </span>

                                <p class="sk-info-value sk-info-date">

                                    <svg
                                        class="sk-date-icon"
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

                            <td>

                                <span class="sk-info-label">
                                    Tanggal Keluar
                                </span>

                                <p class="sk-info-value sk-info-date">

                                    <svg
                                        class="sk-date-icon"
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
                                        {{ $tanggalKeluar }}
                                    </span>

                                </p>

                            </td>

                            <td>

                                <span class="sk-info-label">
                                    Dibuat Oleh
                                </span>

                                <p class="sk-info-value">
                                    {{ $pembuatNama }}
                                </p>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

        {{-- PERIHAL --}}

        <div class="sk-card sk-perihal-card">

            <div class="sk-card-header">

                <div class="sk-card-heading">

                    <div class="sk-card-heading-icon">

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
                                d="M4 6h16M4 12h16M4 18h10"
                            />
                        </svg>

                    </div>

                    <div>

                        <h2 class="sk-card-title">
                            Perihal Surat
                        </h2>

                        <p class="sk-card-subtitle">
                            Pokok atau tujuan utama surat.
                        </p>

                    </div>

                </div>

            </div>

            <div class="sk-perihal-body">

                <div class="sk-perihal-box">

                    <p class="sk-perihal-text">
                        {{ $perihal }}
                    </p>

                </div>

            </div>

        </div>

    </div>

    {{-- =================================================
         KANAN
    ================================================== --}}

    <div class="sk-right-column">

        <div class="sk-card sk-side-card">

            <div class="sk-card-header">

                <div class="sk-card-heading">

                    <div class="sk-card-heading-icon">

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

                        <h2 class="sk-card-title">
                            Status Arsip
                        </h2>

                        <p class="sk-card-subtitle">
                            Status dan informasi pengelolaan surat.
                        </p>

                    </div>

                </div>

            </div>

            <div class="sk-side-body">

                <div class="sk-side-status">

                    <span class="sk-status {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>

                </div>

                <div class="sk-side-list">

                    <div class="sk-side-item">

                        <span class="sk-side-label">
                            Nomor Arsip
                        </span>

                        <p class="sk-side-value">
                            #{{ $suratKeluar->id }}
                        </p>

                    </div>

                    <div class="sk-side-item">

                        <span class="sk-side-label">
                            Nomor Surat
                        </span>

                        <p class="sk-side-value">
                            {{ $nomorSurat }}
                        </p>

                    </div>

                    <div class="sk-side-item">

                        <span class="sk-side-label">
                            Kategori
                        </span>

                        <p class="sk-side-value">
                            {{ $kategoriNama }}
                        </p>

                    </div>

                    <div class="sk-side-item">

                        <span class="sk-side-label">
                            Dibuat Oleh
                        </span>

                        <p class="sk-side-value">
                            {{ $pembuatNama }}
                        </p>

                    </div>

                </div>

                <div class="sk-side-note">

                    <p class="sk-side-note-title">
                        Informasi Arsip
                    </p>

                    <p class="sk-side-note-text">
                        Surat keluar tersimpan sebagai bagian
                        dari arsip digital sistem.
                    </p>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- =====================================================
     LAMPIRAN DIGITAL
====================================================== --}}

<div class="sk-card sk-attachment-card">

    <div class="sk-card-header">

        <div class="sk-card-heading">

            <div class="sk-card-heading-icon">

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

                <h2 class="sk-card-title">
                    Berkas Lampiran Digital
                </h2>

                <p class="sk-card-subtitle">
                    Dokumen digital yang tersimpan pada arsip surat ini.
                </p>

            </div>

        </div>

        @if($lampiranExtension)

            <span class="sk-extension">
                .{{ $lampiranExtension }}
            </span>

        @endif

    </div>

    <div class="sk-attachment-body">

        @if(
            !empty($lampiranPath) &&
            !empty($lampiranUrl)
        )

            {{-- FILE BAR --}}

            <div class="sk-file-bar">

                <div class="sk-file-info">

                    <div class="sk-file-icon">

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
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A1.5 1.5 0 0118 8.5V19a2 2 0 01-2 2"
                            />
                        </svg>

                    </div>

                    <div class="min-w-0">

                        <p class="sk-file-name">
                            {{ $lampiranNama }}
                        </p>

                        <p class="sk-file-state">
                            Lampiran tersimpan
                        </p>

                    </div>

                </div>

                <div class="sk-file-actions">

                    <a
                        href="{{ $lampiranUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="sk-file-btn sk-file-btn-open"
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
                        class="sk-file-btn sk-file-btn-download"
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

            {{-- VIEWER --}}

            <div class="sk-viewer">

                @if($isImage)

                    <div class="sk-image-wrapper">

                        <img
                            src="{{ $lampiranUrl }}"
                            alt="Lampiran Surat Keluar"
                            class="sk-image"
                            loading="lazy"
                        >

                    </div>

                @elseif($isPdf)

                    <iframe
                        src="{{ $lampiranUrl }}"
                        class="sk-pdf"
                        title="Pratinjau PDF Surat Keluar"
                        loading="lazy"
                    ></iframe>

                @else

                    <div class="sk-viewer-empty">

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

                        <p class="sk-viewer-empty-title">
                            Pratinjau tidak tersedia
                        </p>

                        <p class="sk-viewer-empty-text">
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

            <div class="sk-no-file">

                <svg
                    class="w-4 h-4 shrink-0"
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

{{-- =====================================================
     FOOTER
====================================================== --}}

<div class="sk-system-footer">

    <span>
        Sistem Kendali Surat Keluar
    </span>

</div>

</div>

@endsection
