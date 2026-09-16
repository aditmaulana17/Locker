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
    | LAMPIRAN LAMA
    |--------------------------------------------------------------------------
    */

    $currentAttachmentPath = trim(
        (string) (
            $suratKeluar->lampiran_file ?? ''
        )
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

    /*
    |--------------------------------------------------------------------------
    | INPUT CLASS
    |--------------------------------------------------------------------------
    */

    $inputClass =
        'block w-full rounded-md border-2 border-slate-400 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-500 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100';

    $errorInputClass =
        'border-rose-400 bg-rose-50 focus:border-rose-500 focus:ring-rose-100';
@endphp

<style>
    .ske-page,
    .ske-page * {
        box-sizing: border-box;
    }

    .ske-page {
        width: 100%;
        max-width: 1180px;
        margin: 0 auto;
        padding: 14px 18px 30px;
        color: #1e293b;
    }

    .ske-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 12px;
    }

    .ske-breadcrumb {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 10px;
        color: #64748b;
    }

    .ske-breadcrumb strong {
        color: #1e293b;
        font-weight: 800;
    }

    .ske-back {
        min-height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #fff;
        color: #475569;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        transition: .15s ease;
    }

    .ske-back:hover {
        border-color: #94a3b8;
        background: #f8fafc;
        color: #1e293b;
    }

    .ske-shell {
        overflow: hidden;
        border: 1.5px solid #94a3b8;
        border-radius: 14px;
        background: #fff;
        box-shadow:
            0 12px 30px rgba(15, 23, 42, .07),
            0 2px 6px rgba(15, 23, 42, .04);
    }

    .ske-header {
        position: relative;
        overflow: hidden;
        padding: 18px 20px;
        background: linear-gradient(
            135deg,
            #047857 0%,
            #059669 45%,
            #2563eb 100%
        );
        color: #fff;
    }

    .ske-header::after {
        content: "";
        position: absolute;
        width: 220px;
        height: 220px;
        right: -75px;
        bottom: -90px;
        border-radius: 50%;
        background: rgba(255,255,255,.08);
    }

    .ske-header-inner {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .ske-header-main {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
    }

    .ske-header-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid rgba(255,255,255,.30);
        border-radius: 10px;
        background: rgba(255,255,255,.13);
        backdrop-filter: blur(5px);
    }

    .ske-header-kicker {
        margin: 0;
        font-size: 8px;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: rgba(255,255,255,.78);
    }

    .ske-header-title {
        margin: 2px 0 0;
        font-size: 19px;
        line-height: 1.3;
        font-weight: 850;
    }

    .ske-header-desc {
        margin: 3px 0 0;
        max-width: 700px;
        font-size: 8.5px;
        line-height: 1.5;
        color: rgba(255,255,255,.83);
    }

    .ske-header-badge {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        min-height: 28px;
        padding: 0 9px;
        border: 1px solid rgba(255,255,255,.25);
        border-radius: 999px;
        background: rgba(255,255,255,.11);
        font-size: 8px;
        font-weight: 800;
        white-space: nowrap;
    }

    .ske-body {
        padding: 16px;
    }

    .ske-error {
        margin-bottom: 12px;
        padding: 11px 13px;
        border: 1px solid #fecaca;
        border-radius: 9px;
        background: #fff7f7;
        color: #be123c;
    }

    .ske-error-title {
        margin: 0;
        font-size: 10px;
        font-weight: 800;
    }

    .ske-error-list {
        margin: 4px 0 0;
        padding-left: 16px;
        font-size: 8px;
        line-height: 1.55;
    }

    .ske-section + .ske-section {
        margin-top: 17px;
        padding-top: 17px;
        border-top: 1.5px solid #e2e8f0;
    }

    .ske-section-head {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 10px;
    }

    .ske-section-marker {
        width: 4px;
        min-height: 28px;
        flex: 0 0 4px;
        border-radius: 999px;
        background: #059669;
    }

    .ske-section-marker-blue {
        background: #2563eb;
    }

    .ske-section-title {
        margin: 0;
        font-size: 13px;
        line-height: 1.3;
        font-weight: 850;
        color: #1e293b;
    }

    .ske-section-desc {
        margin: 3px 0 0;
        font-size: 8px;
        line-height: 1.45;
        color: #64748b;
    }

    .ske-field-table {
        overflow: hidden;
        border: 1.5px solid #94a3b8;
        border-radius: 10px;
    }

    .ske-field-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .ske-field {
        min-width: 0;
        padding: 11px;
        border-right: 1.5px solid #cbd5e1;
        border-bottom: 1.5px solid #cbd5e1;
        background: #fff;
    }

    .ske-field:nth-child(2n) {
        border-right: 0;
    }

    .ske-field-full {
        grid-column: 1 / -1;
        border-right: 0;
    }

    .ske-field-label {
        display: block;
        margin-bottom: 5px;
        font-size: 9px;
        line-height: 1.3;
        font-weight: 850;
        color: #475569;
    }

    .ske-required {
        color: #dc2626;
    }

    .ske-control {
        width: 100%;
        min-height: 40px;
        border: 1.5px solid #94a3b8;
        border-radius: 8px;
        background: #fff;
        color: #1e293b;
        padding: 8px 10px;
        font-size: 11px;
        line-height: 1.4;
        outline: none;
        transition: .15s ease;
    }

    input.ske-control,
    select.ske-control {
        height: 40px;
        padding: 0 10px;
    }

    textarea.ske-control {
        min-height: 82px;
        resize: vertical;
    }

    .ske-control:focus {
        border-color: #059669;
        box-shadow: 0 0 0 3px rgba(5,150,105,.08);
    }

    .ske-control-error {
        border-color: #ef4444 !important;
        background: #fff7f7 !important;
    }

    .ske-field-error {
        margin: 4px 0 0;
        font-size: 7.5px;
        line-height: 1.45;
        color: #dc2626;
        font-weight: 700;
    }

    .ske-attachment-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 12px;
        align-items: stretch;
    }

    .ske-card {
        min-width: 0;
        overflow: hidden;
        border: 1.5px solid #94a3b8;
        border-radius: 10px;
        background: #fff;
    }

    .ske-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 11px;
        border-bottom: 1.5px solid #cbd5e1;
        background: #f8fafc;
    }

    .ske-card-header-left {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .ske-card-icon {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #bfdbfe;
        border-radius: 7px;
        background: #eff6ff;
        color: #2563eb;
    }

    .ske-card-icon.green {
        border-color: #a7f3d0;
        background: #ecfdf5;
        color: #059669;
    }

    .ske-card-title {
        margin: 0;
        font-size: 10px;
        font-weight: 850;
        color: #1e293b;
    }

    .ske-card-desc {
        margin: 2px 0 0;
        font-size: 7px;
        color: #64748b;
    }

    .ske-badge {
        padding: 4px 7px;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #fff;
        color: #64748b;
        font-size: 6.5px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ske-badge-green {
        border-color: #a7f3d0;
        background: #ecfdf5;
        color: #047857;
    }

    .ske-card-body {
        padding: 11px;
    }

    .ske-current-file {
        margin-bottom: 9px;
        padding: 8px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
    }

    .ske-current-file-row {
        display: flex;
        align-items: flex-start;
        gap: 7px;
    }

    .ske-current-file-icon {
        width: 30px;
        height: 30px;
        flex: 0 0 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
        background: #e2e8f0;
        color: #64748b;
    }

    .ske-current-file-content {
        min-width: 0;
        flex: 1;
    }

    .ske-current-file-label {
        margin: 0;
        font-size: 7px;
        font-weight: 800;
        color: #64748b;
    }

    .ske-current-file-name {
        margin: 2px 0 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 8px;
        font-weight: 750;
        color: #334155;
    }

    .ske-current-file-ext {
        display: inline-flex;
        margin-top: 2px;
        padding: 2px 5px;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #fff;
        color: #64748b;
        font-size: 6px;
        font-weight: 850;
    }

    .ske-empty {
        margin: 0;
        font-size: 7.5px;
        color: #64748b;
    }

    .ske-info {
        display: flex;
        align-items: flex-start;
        gap: 7px;
        margin-bottom: 8px;
        padding: 8px;
        border: 1px solid #c7d2fe;
        border-radius: 8px;
        background: #eef2ff;
        color: #4338ca;
    }

    .ske-info svg {
        width: 13px;
        height: 13px;
        flex: 0 0 13px;
        margin-top: 1px;
    }

    .ske-info p {
        margin: 0;
        font-size: 7px;
        line-height: 1.55;
    }

    .ske-view-current {
        width: 100%;
        min-height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        margin-top: 6px;
        padding: 0 8px;
        border: 1px solid #bfdbfe;
        border-radius: 7px;
        background: #eff6ff;
        color: #1d4ed8;
        text-decoration: none;
        font-size: 7.5px;
        font-weight: 850;
    }

    .ske-view-current:hover {
        background: #dbeafe;
    }

    .ske-upload-box {
        position: relative;
        min-height: 130px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 14px;
        border: 1.5px dashed #94a3b8;
        border-radius: 9px;
        background: #f8fafc;
        text-align: center;
        cursor: pointer;
        transition: .15s ease;
    }

    .ske-upload-box:hover {
        border-color: #60a5fa;
        background: #eff6ff;
    }

    .ske-upload-box.has-file {
        border-color: #34d399;
        background: #ecfdf5;
    }

    .ske-upload-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 4px;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        background: #dbeafe;
        color: #2563eb;
    }

    .ske-upload-box.has-file .ske-upload-icon {
        border-color: #a7f3d0;
        background: #d1fae5;
        color: #059669;
    }

    .ske-upload-title {
        font-size: 8.5px;
        font-weight: 850;
        color: #334155;
    }

    .ske-upload-subtitle {
        font-size: 7px;
        color: #64748b;
    }

    .ske-upload-limit {
        margin-top: 2px;
        padding: 3px 7px;
        border-radius: 999px;
        background: #dbeafe;
        color: #2563eb;
        font-size: 6px;
        font-weight: 800;
    }

    .ske-upload-input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .ske-file-info {
        display: none;
        margin-top: 8px;
        padding: 8px;
        border: 1px solid #a7f3d0;
        border-radius: 8px;
        background: #ecfdf5;
    }

    .ske-file-info.show {
        display: block;
    }

    .ske-file-info-title {
        margin: 0;
        font-size: 7.5px;
        font-weight: 850;
        color: #047857;
    }

    .ske-file-info-name {
        margin: 2px 0 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 7.5px;
        font-weight: 750;
        color: #334155;
    }

    .ske-file-info-size {
        margin: 2px 0 0;
        font-size: 6.8px;
        color: #64748b;
    }

    .ske-compression {
        display: none;
        margin-top: 8px;
        padding: 8px;
        border: 1px solid #c7d2fe;
        border-radius: 8px;
        background: #eef2ff;
        color: #4338ca;
    }

    .ske-compression.show {
        display: block;
    }

    .ske-compression.success {
        border-color: #a7f3d0;
        background: #ecfdf5;
        color: #047857;
    }

    .ske-compression.warning {
        border-color: #fde68a;
        background: #fffbeb;
        color: #a16207;
    }

    .ske-compression.error {
        border-color: #fecaca;
        background: #fff1f2;
        color: #be123c;
    }

    .ske-compression.processing {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .ske-compression-title {
        margin: 0 0 6px;
        font-size: 7.5px;
        font-weight: 850;
    }

    .ske-compression-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 5px;
    }

    .ske-compression-item {
        padding: 6px;
        border: 1px solid rgba(148,163,184,.25);
        border-radius: 6px;
        background: rgba(255,255,255,.58);
    }

    .ske-compression-label {
        display: block;
        margin-bottom: 2px;
        font-size: 5.8px;
        color: #64748b;
    }

    .ske-compression-value {
        font-size: 7px;
        font-weight: 800;
        color: #334155;
    }

    .ske-status {
        display: none;
        margin-top: 7px;
        padding: 7px 8px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        background: #f8fafc;
        color: #64748b;
        font-size: 6.8px;
        line-height: 1.5;
    }

    .ske-status.show {
        display: block;
    }

    .ske-status.blue {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .ske-status.green {
        border-color: #a7f3d0;
        background: #ecfdf5;
        color: #047857;
    }

    .ske-status.amber {
        border-color: #fde68a;
        background: #fffbeb;
        color: #a16207;
    }

    .ske-preview {
        display: none;
        overflow: hidden;
        margin-top: 8px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #0f172a;
    }

    .ske-preview.show {
        display: block;
    }

    .ske-preview-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        padding: 7px 8px;
        border-bottom: 1px solid #334155;
        background: #111827;
        color: #fff;
    }

    .ske-preview-title {
        margin: 0;
        font-size: 7.5px;
        font-weight: 800;
    }

    .ske-preview-badge {
        padding: 3px 6px;
        border-radius: 999px;
        background: rgba(255,255,255,.10);
        color: #cbd5e1;
        font-size: 5.5px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .ske-preview-image {
        display: block;
        width: 100%;
        max-height: 340px;
        object-fit: contain;
        background: #fff;
    }

    .ske-preview-pdf {
        display: block;
        width: 100%;
        height: 340px;
        border: 0;
        background: #fff;
    }

    .ske-preview-caption {
        padding: 6px 8px;
        background: #111827;
        color: #94a3b8;
        font-size: 6.5px;
        line-height: 1.45;
    }

    .ske-preview-divider {
        margin: 8px 0;
        height: 1px;
        background: #334155;
    }

    .ske-preview-secondary {
        padding: 7px 8px;
        background: #0f172a;
        color: #cbd5e1;
        font-size: 6.5px;
        line-height: 1.45;
    }

    .ske-submit-progress {
        display: none;
        margin-top: 7px;
        height: 4px;
        overflow: hidden;
        border-radius: 99px;
        background: #dbeafe;
    }

    .ske-submit-progress.show {
        display: block;
    }

    .ske-submit-progress-bar {
        width: 35%;
        height: 100%;
        border-radius: inherit;
        background: #2563eb;
        animation: skeProgress 1.2s ease-in-out infinite;
    }

    @keyframes skeProgress {
        0% {
            transform: translateX(-120%);
        }

        100% {
            transform: translateX(320%);
        }
    }

    .ske-footer {
        display: flex;
        justify-content: flex-end;
        gap: 7px;
        padding: 10px 13px;
        border-top: 1.5px solid #cbd5e1;
        background: #f8fafc;
    }

    .ske-footer-btn {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 13px;
        border-radius: 8px;
        font-size: 8px;
        font-weight: 850;
        text-decoration: none;
        cursor: pointer;
    }

    .ske-cancel {
        border: 1.5px solid #cbd5e1;
        background: #fff;
        color: #475569;
    }

    .ske-submit {
        border: 1.5px solid #059669;
        background: #059669;
        color: #fff;
        box-shadow: 0 3px 8px rgba(5,150,105,.14);
    }

    .ske-submit:hover:not(:disabled) {
        background: #047857;
        border-color: #047857;
    }

    .ske-submit:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    .ske-hidden {
        display: none !important;
    }

    @media (max-width: 900px) {
        .ske-attachment-grid {
            grid-template-columns: 1fr;
        }

        .ske-preview-pdf {
            height: 360px;
        }

        .ske-preview-image {
            max-height: 360px;
        }
    }

    @media (max-width: 640px) {
        .ske-page {
            padding: 7px 9px 20px;
        }

        .ske-topbar {
            flex-direction: column;
            align-items: stretch;
        }

        .ske-back {
            width: 100%;
        }

        .ske-header-inner {
            align-items: flex-start;
            flex-direction: column;
        }

        .ske-header-badge {
            align-self: flex-start;
        }

        .ske-body {
            padding: 10px;
        }

        .ske-field-grid {
            grid-template-columns: 1fr;
        }

        .ske-field,
        .ske-field:nth-child(2n) {
            border-right: 0;
        }

        .ske-field-full {
            grid-column: auto;
        }

        .ske-compression-grid {
            grid-template-columns: 1fr 1fr;
        }

        .ske-footer {
            flex-direction: column-reverse;
            align-items: stretch;
        }

        .ske-footer-btn {
            width: 100%;
        }

        .ske-preview-pdf {
            height: 320px;
        }

        .ske-preview-image {
            max-height: 320px;
        }
    }

    @media (max-width: 420px) {
        .ske-header {
            padding: 14px;
        }

        .ske-header-title {
            font-size: 16px;
        }

        .ske-compression-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="ske-page">

    <div class="ske-topbar">

        <div class="ske-breadcrumb">
            <span>Arsip</span>
            <span>/</span>
            <strong>Surat Keluar</strong>
            <span>/</span>
            <strong>Edit</strong>
        </div>

        <a
            href="{{ route('surat-keluar.index') }}"
            class="ske-back"
        >
            <svg
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M10 19l-7-7m0 0l7-7m-7 7h18"
                />
            </svg>

            Kembali
        </a>

    </div>

    @if($errors->any())

        <div class="ske-error">

            <p class="ske-error-title">
                Data belum dapat diperbarui.
            </p>

            <ul class="ske-error-list">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif

    <form
        id="form-surat-keluar-edit"
        method="POST"
        action="{{ route('surat-keluar.update', $suratKeluar) }}"
        enctype="multipart/form-data"
        novalidate
    >

        @csrf
        @method('PUT')

        <div class="ske-shell">

            <header class="ske-header">

                <div class="ske-header-inner">

                    <div class="ske-header-main">

                        <div class="ske-header-icon">

                            <svg
                                width="21"
                                height="21"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6 3h8l5 5v13H6a2 2 0 01-2-2V5a2 2 0 012-2z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M14 3v6h5M8 13h8M8 17h6"
                                />
                            </svg>

                        </div>

                        <div>

                            <p class="ske-header-kicker">
                                Sistem E-Arsip
                            </p>

                            <h1 class="ske-header-title">
                                Edit Surat Keluar
                            </h1>

                            <p class="ske-header-desc">
                                Perbarui data surat dan lampiran digital.
                                PDF tetap PDF dan dapat dikompresi di server,
                                sedangkan JPG/JPEG/PNG dioptimalkan menjadi JPG.
                            </p>

                        </div>

                    </div>

                    <span class="ske-header-badge">
                        Arsip Surat Keluar
                    </span>

                </div>

            </header>

            <div class="ske-body">

                <section class="ske-section">

                    <div class="ske-section-head">

                        <div class="ske-section-marker"></div>

                        <div>

                            <h2 class="ske-section-title">
                                Informasi Utama Surat
                            </h2>

                            <p class="ske-section-desc">
                                Perbarui identitas dan informasi utama surat keluar.
                            </p>

                        </div>

                    </div>

                    <div class="ske-field-table">

                        <div class="ske-field-grid">

                            <div class="ske-field">

                                <label
                                    for="nomor_surat"
                                    class="ske-field-label"
                                >
                                    Nomor Surat
                                    <span class="ske-required">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="nomor_surat"
                                    name="nomor_surat"
                                    value="{{ old('nomor_surat', $suratKeluar->nomor_surat) }}"
                                    maxlength="255"
                                    required
                                    autocomplete="off"
                                    placeholder="Contoh: 005/SK/I/2026"
                                    class="ske-control @error('nomor_surat') ske-control-error @enderror"
                                >

                                @error('nomor_surat')
                                    <p class="ske-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <div class="ske-field">

                                <label
                                    for="pengirim"
                                    class="ske-field-label"
                                >
                                    Tujuan Surat
                                    <span class="ske-required">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="pengirim"
                                    name="pengirim"
                                    value="{{ old('pengirim', $suratKeluar->pengirim) }}"
                                    maxlength="150"
                                    required
                                    autocomplete="organization"
                                    placeholder="Contoh: PT Maju Takgentar"
                                    class="ske-control @error('pengirim') ske-control-error @enderror"
                                >

                                @error('pengirim')
                                    <p class="ske-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <div class="ske-field">

                                <label
                                    for="tanggal_surat"
                                    class="ske-field-label"
                                >
                                    Tanggal Surat
                                    <span class="ske-required">*</span>
                                </label>

                                <input
                                    type="date"
                                    id="tanggal_surat"
                                    name="tanggal_surat"
                                    value="{{ $tanggalSurat }}"
                                    required
                                    class="ske-control @error('tanggal_surat') ske-control-error @enderror"
                                >

                                @error('tanggal_surat')
                                    <p class="ske-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <div class="ske-field">

                                <label
                                    for="tanggal_keluar"
                                    class="ske-field-label"
                                >
                                    Tanggal Keluar
                                    <span class="ske-required">*</span>
                                </label>

                                <input
                                    type="date"
                                    id="tanggal_keluar"
                                    name="tanggal_keluar"
                                    value="{{ $tanggalKeluar }}"
                                    required
                                    class="ske-control @error('tanggal_keluar') ske-control-error @enderror"
                                >

                                @error('tanggal_keluar')
                                    <p class="ske-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <div class="ske-field">

                                <label
                                    for="kategori_surat_id"
                                    class="ske-field-label"
                                >
                                    Kategori Surat
                                    <span class="ske-required">*</span>
                                </label>

                                <select
                                    id="kategori_surat_id"
                                    name="kategori_surat_id"
                                    required
                                    class="ske-control @error('kategori_surat_id') ske-control-error @enderror"
                                >

                                    <option
                                        value=""
                                        disabled
                                        @selected(!$kategoriSuratId)
                                    >
                                        Pilih kategori surat
                                    </option>

                                    @foreach(($kategoris ?? collect()) as $kategori)

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

                                    <p
                                        class="ske-field-error"
                                        style="color:#a16207;"
                                    >
                                        Belum ada kategori surat yang tersedia.
                                    </p>

                                @endif

                                @error('kategori_surat_id')
                                    <p class="ske-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <div class="ske-field">

                                <label
                                    for="status"
                                    class="ske-field-label"
                                >
                                    Status Surat
                                    <span class="ske-required">*</span>
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    required
                                    class="ske-control @error('status') ske-control-error @enderror"
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
                                    <p class="ske-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <div class="ske-field ske-field-full">

                                <label
                                    for="perihal"
                                    class="ske-field-label"
                                >
                                    Perihal
                                    <span class="ske-required">*</span>
                                </label>

                                <textarea
                                    id="perihal"
                                    name="perihal"
                                    rows="3"
                                    maxlength="255"
                                    required
                                    placeholder="Tuliskan perihal surat..."
                                    class="ske-control @error('perihal') ske-control-error @enderror"
                                >{{ old('perihal', $suratKeluar->perihal) }}</textarea>

                                @error('perihal')
                                    <p class="ske-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                            <div class="ske-field ske-field-full">

                                <label
                                    for="ringkasan"
                                    class="ske-field-label"
                                >
                                    Ringkasan Isi Surat
                                </label>

                                <textarea
                                    id="ringkasan"
                                    name="ringkasan"
                                    rows="3"
                                    maxlength="5000"
                                    placeholder="Tuliskan ringkasan singkat isi surat..."
                                    class="ske-control @error('ringkasan') ske-control-error @enderror"
                                >{{ old('ringkasan', $suratKeluar->ringkasan) }}</textarea>

                                <p
                                    class="ske-field-error"
                                    style="color:#94a3b8;"
                                >
                                    Maksimal 5.000 karakter.
                                </p>

                                @error('ringkasan')
                                    <p class="ske-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </div>

                </section>

                <section class="ske-section">

                    <div class="ske-section-head">

                        <div class="ske-section-marker ske-section-marker-blue"></div>

                        <div>

                            <h2 class="ske-section-title">
                                Lampiran Dokumen Surat
                            </h2>

                            <p class="ske-section-desc">
                                Lihat lampiran saat ini atau upload file pengganti.
                                Hasil compression akan ditampilkan sebelum update.
                            </p>

                        </div>

                    </div>

                    <div class="ske-attachment-grid">

                        {{-- =====================================================
                             LAMPIRAN SAAT INI
                        ====================================================== --}}

                        <div class="ske-card">

                            <div class="ske-card-header">

                                <div class="ske-card-header-left">

                                    <div class="ske-card-icon green">

                                        <svg
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M7 3h7l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M14 3v5h5"
                                            />
                                        </svg>

                                    </div>

                                    <div>

                                        <h3 class="ske-card-title">
                                            Lampiran Saat Ini
                                        </h3>

                                        <p class="ske-card-desc">
                                            Dokumen yang sedang tersimpan.
                                        </p>

                                    </div>

                                </div>

                                <span class="ske-badge ske-badge-green">
                                    Tersimpan
                                </span>

                            </div>

                            <div class="ske-card-body">

                                @if($hasCurrentAttachment)

                                    <div class="ske-current-file">

                                        <div class="ske-current-file-row">

                                            <div class="ske-current-file-icon">

                                                <svg
                                                    width="15"
                                                    height="15"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A1.5 1.5 0 0118 8.5V19a2 2 0 01-2 2z"
                                                    />
                                                </svg>

                                            </div>

                                            <div class="ske-current-file-content">

                                                <p class="ske-current-file-label">
                                                    File tersimpan
                                                </p>

                                                <p
                                                    class="ske-current-file-name"
                                                    title="{{ $currentAttachmentName }}"
                                                >
                                                    {{ $currentAttachmentName }}
                                                </p>

                                                @if($currentAttachmentExtension)

                                                    <span class="ske-current-file-ext">
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
                                        class="ske-view-current"
                                    >

                                        <svg
                                            width="13"
                                            height="13"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"
                                            />

                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="2.8"
                                            />
                                        </svg>

                                        Buka Preview Lampiran

                                    </a>

                                @else

                                    <div class="ske-current-file">

                                        <p class="ske-empty">
                                            Belum ada lampiran tersimpan.
                                        </p>

                                    </div>

                                @endif

                            </div>

                        </div>

                        {{-- =====================================================
                             FILE BARU
                        ====================================================== --}}

                        <div class="ske-card">

                            <div class="ske-card-header">

                                <div class="ske-card-header-left">

                                    <div class="ske-card-icon">

                                        <svg
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 16V4m0 0L8 8m4-4l4 4M5 15v2a2 2 0 002 2h10a2 2 0 002-2v-2"
                                            />
                                        </svg>

                                    </div>

                                    <div>

                                        <h3 class="ske-card-title">
                                            Upload File Baru
                                        </h3>

                                        <p class="ske-card-desc">
                                            File baru akan menggantikan lampiran lama.
                                        </p>

                                    </div>

                                </div>

                                <span class="ske-badge">
                                    Opsional
                                </span>

                            </div>

                            <div class="ske-card-body">

                                <div class="ske-info">

                                    <svg
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="9"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            d="M12 10v6M12 7h.01"
                                        />
                                    </svg>

                                    <p>
                                        <strong>PDF:</strong>
                                        tetap PDF dan dikompresi oleh server
                                        menggunakan Ghostscript.
                                        <br>
                                        <strong>JPG/JPEG/PNG:</strong>
                                        dikompresi di browser dan dikonversi menjadi JPG.
                                        <br>
                                        Maksimal file: <strong>10 MB</strong>.
                                    </p>

                                </div>

                                <label
                                    id="ske-upload-box"
                                    for="lampiran_file"
                                    class="ske-upload-box"
                                >

                                    <div
                                        id="ske-upload-icon"
                                        class="ske-upload-icon"
                                    >

                                        <svg
                                            width="18"
                                            height="18"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 16V4m0 0L8 8m4-4l4 4M5 15v2a2 2 0 002 2h10a2 2 0 002-2v-2"
                                            />
                                        </svg>

                                    </div>

                                    <span
                                        id="ske-upload-title"
                                        class="ske-upload-title"
                                    >
                                        Klik untuk memilih file baru
                                    </span>

                                    <span
                                        id="ske-upload-subtitle"
                                        class="ske-upload-subtitle"
                                    >
                                        PDF, JPG, JPEG, PNG
                                    </span>

                                    <span class="ske-upload-limit">
                                        Maksimal 10 MB
                                    </span>

                                    <input
                                        id="lampiran_file"
                                        name="lampiran_file"
                                        type="file"
                                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                        class="ske-upload-input"
                                    >

                                </label>

                                <div
                                    id="ske-file-info"
                                    class="ske-file-info"
                                >

                                    <p
                                        id="ske-file-info-title"
                                        class="ske-file-info-title"
                                    >
                                        File siap digunakan
                                    </p>

                                    <p
                                        id="ske-file-info-name"
                                        class="ske-file-info-name"
                                    ></p>

                                    <p
                                        id="ske-file-info-size"
                                        class="ske-file-info-size"
                                    ></p>

                                </div>

                                <div
                                    id="ske-compression"
                                    class="ske-compression"
                                >

                                    <p
                                        id="ske-compression-title"
                                        class="ske-compression-title"
                                    >
                                        Informasi Compression
                                    </p>

                                    <div class="ske-compression-grid">

                                        <div class="ske-compression-item">

                                            <span class="ske-compression-label">
                                                Format Asli
                                            </span>

                                            <span
                                                id="ske-original-format"
                                                class="ske-compression-value"
                                            >
                                                -
                                            </span>

                                        </div>

                                        <div class="ske-compression-item">

                                            <span class="ske-compression-label">
                                                Format Akhir
                                            </span>

                                            <span
                                                id="ske-final-format"
                                                class="ske-compression-value"
                                            >
                                                -
                                            </span>

                                        </div>

                                        <div class="ske-compression-item">

                                            <span class="ske-compression-label">
                                                Ukuran Asli
                                            </span>

                                            <span
                                                id="ske-original-size"
                                                class="ske-compression-value"
                                            >
                                                -
                                            </span>

                                        </div>

                                        <div class="ske-compression-item">

                                            <span class="ske-compression-label">
                                                Ukuran Hasil
                                            </span>

                                            <span
                                                id="ske-final-size"
                                                class="ske-compression-value"
                                            >
                                                -
                                            </span>

                                        </div>

                                    </div>

                                    <div
                                        id="ske-compression-message"
                                        style="margin-top:6px;font-size:6.8px;line-height:1.5;"
                                    ></div>

                                </div>

                                <div
                                    id="ske-status"
                                    class="ske-status"
                                ></div>

                                <div
                                    id="ske-submit-progress"
                                    class="ske-submit-progress"
                                >
                                    <div class="ske-submit-progress-bar"></div>
                                </div>

                                {{-- =================================================
                                     PREVIEW
                                ================================================== --}}

                                <div
                                    id="ske-preview"
                                    class="ske-preview"
                                >

                                    <div class="ske-preview-header">

                                        <p
                                            id="ske-preview-title"
                                            class="ske-preview-title"
                                        >
                                            Preview Dokumen
                                        </p>

                                        <span
                                            id="ske-preview-badge"
                                            class="ske-preview-badge"
                                        >
                                            Preview
                                        </span>

                                    </div>

                                    <img
                                        id="ske-preview-image"
                                        src=""
                                        alt="Preview dokumen"
                                        class="ske-preview-image"
                                        style="display:none;"
                                    >

                                    <iframe
                                        id="ske-preview-pdf"
                                        title="Preview PDF"
                                        class="ske-preview-pdf"
                                        style="display:none;"
                                    ></iframe>

                                    <div
                                        id="ske-preview-caption"
                                        class="ske-preview-caption"
                                    >
                                        Preview akan tampil setelah memilih file.
                                    </div>

                                    <div
                                        id="ske-preview-secondary"
                                        class="ske-preview-secondary ske-hidden"
                                    ></div>

                                </div>

                                @error('lampiran_file')

                                    <p class="ske-field-error">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                        </div>

                    </div>

                </section>

            </div>

            <div class="ske-footer">

                <a
                    href="{{ route('surat-keluar.index') }}"
                    class="ske-footer-btn ske-cancel"
                >

                    <svg
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>

                    Batal

                </a>

                <button
                    type="submit"
                    id="ske-submit"
                    class="ske-footer-btn ske-submit"
                >

                    <svg
                        id="ske-submit-icon"
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>

                    <svg
                        id="ske-submit-loading"
                        class="ske-hidden"
                        width="14"
                        height="14"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <circle
                            cx="12"
                            cy="12"
                            r="9"
                            stroke="currentColor"
                            stroke-width="3"
                            opacity=".30"
                        />

                        <path
                            d="M21 12a9 9 0 00-9-9"
                            stroke="currentColor"
                            stroke-width="3"
                            stroke-linecap="round"
                        />
                    </svg>

                    <span id="ske-submit-text">
                        Perbarui Surat Keluar
                    </span>

                </button>

            </div>

        </div>

    </form>

</div>

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        'use strict';

        /*
        |--------------------------------------------------------------------------
        | ELEMENT
        |--------------------------------------------------------------------------
        */

        const form =
            document.getElementById(
                'form-surat-keluar-edit'
            );

        const fileInput =
            document.getElementById(
                'lampiran_file'
            );

        const uploadBox =
            document.getElementById(
                'ske-upload-box'
            );

        const uploadTitle =
            document.getElementById(
                'ske-upload-title'
            );

        const uploadSubtitle =
            document.getElementById(
                'ske-upload-subtitle'
            );

        const fileInfo =
            document.getElementById(
                'ske-file-info'
            );

        const fileInfoTitle =
            document.getElementById(
                'ske-file-info-title'
            );

        const fileInfoName =
            document.getElementById(
                'ske-file-info-name'
            );

        const fileInfoSize =
            document.getElementById(
                'ske-file-info-size'
            );

        const compression =
            document.getElementById(
                'ske-compression'
            );

        const compressionTitle =
            document.getElementById(
                'ske-compression-title'
            );

        const compressionMessage =
            document.getElementById(
                'ske-compression-message'
            );

        const originalFormat =
            document.getElementById(
                'ske-original-format'
            );

        const finalFormat =
            document.getElementById(
                'ske-final-format'
            );

        const originalSize =
            document.getElementById(
                'ske-original-size'
            );

        const finalSize =
            document.getElementById(
                'ske-final-size'
            );

        const statusBox =
            document.getElementById(
                'ske-status'
            );

        const submitProgress =
            document.getElementById(
                'ske-submit-progress'
            );

        const preview =
            document.getElementById(
                'ske-preview'
            );

        const previewTitle =
            document.getElementById(
                'ske-preview-title'
            );

        const previewBadge =
            document.getElementById(
                'ske-preview-badge'
            );

        const previewImage =
            document.getElementById(
                'ske-preview-image'
            );

        const previewPdf =
            document.getElementById(
                'ske-preview-pdf'
            );

        const previewCaption =
            document.getElementById(
                'ske-preview-caption'
            );

        const previewSecondary =
            document.getElementById(
                'ske-preview-secondary'
            );

        const tanggalSurat =
            document.getElementById(
                'tanggal_surat'
            );

        const tanggalKeluar =
            document.getElementById(
                'tanggal_keluar'
            );

        const submitButton =
            document.getElementById(
                'ske-submit'
            );

        const submitIcon =
            document.getElementById(
                'ske-submit-icon'
            );

        const submitLoading =
            document.getElementById(
                'ske-submit-loading'
            );

        const submitText =
            document.getElementById(
                'ske-submit-text'
            );

        if (
            !form ||
            !fileInput
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | KONFIGURASI
        |--------------------------------------------------------------------------
        */

        const MAX_FILE_SIZE =
            10 *
            1024 *
            1024;

        const IMAGE_TARGET_SIZE =
            2.5 *
            1024 *
            1024;

        const IMAGE_HARD_LIMIT =
            9.5 *
            1024 *
            1024;

        const IMAGE_MAX_DIMENSION =
            2500;

        const IMAGE_MIN_DIMENSION =
            1000;

        const JPEG_QUALITIES = [
            0.90,
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
            0.42,
            0.38
        ];

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

        /*
        |--------------------------------------------------------------------------
        | STATE
        |--------------------------------------------------------------------------
        */

        let submitting =
            false;

        let processingToken =
            0;

        let previewObjectUrl =
            null;

        let pdfCompressionReady =
            false;

        let pdfCompressionToken =
            null;

        let pdfOriginalHashToken =
            null;

        /*
        |--------------------------------------------------------------------------
        | UTIL
        |--------------------------------------------------------------------------
        */

        function formatFileSize(
            bytes
        ) {

            if (
                !Number.isFinite(bytes) ||
                bytes <= 0
            ) {
                return '0 KB';
            }

            if (
                bytes < 1024
            ) {
                return bytes + ' B';
            }

            if (
                bytes < 1024 * 1024
            ) {
                return (
                    (
                        bytes /
                        1024
                    ).toFixed(1) +
                    ' KB'
                );
            }

            return (
                (
                    bytes /
                    1024 /
                    1024
                ).toFixed(2) +
                ' MB'
            );
        }

        function getExtension(
            file
        ) {

            return String(
                file?.name || ''
            )
                .split('.')
                .pop()
                .toLowerCase();
        }

        function isAllowedExtension(
            extension
        ) {

            return ALLOWED_EXTENSIONS.includes(
                extension
            );
        }

        function isImageExtension(
            extension
        ) {

            return IMAGE_EXTENSIONS.includes(
                extension
            );
        }

        function getReductionPercent(
            original,
            final
        ) {

            if (
                !original ||
                original <= 0
            ) {
                return 0;
            }

            return Math.max(
                0,
                Math.round(
                    (
                        1 -
                        (
                            final /
                            original
                        )
                    ) *
                    100
                )
            );
        }

        function escapeHtml(
            value
        ) {

            return String(
                value ?? ''
            )
                .replace(
                    /&/g,
                    '&amp;'
                )
                .replace(
                    /</g,
                    '&lt;'
                )
                .replace(
                    />/g,
                    '&gt;'
                )
                .replace(
                    /"/g,
                    '&quot;'
                )
                .replace(
                    /'/g,
                    '&#039;'
                );
        }

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
                    icon,
                    title,
                    text:
                        String(
                            text ?? ''
                        ),
                    confirmButtonText:
                        'Mengerti',
                    confirmButtonColor:
                        '#059669'
                });

                return;
            }

            window.alert(
                String(
                    text ?? ''
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PDF COMPRESSION URL
        |--------------------------------------------------------------------------
        */

        function getPdfCompressionUrl() {

            return @json(
                route(
                    'surat-keluar.preview-compression'
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PREVIEW URL
        |--------------------------------------------------------------------------
        */

        function getPdfTemporaryPreviewUrl(
            token
        ) {

            const baseUrl =
                @json(
                    url(
                        'surat-keluar/pdf-preview'
                    )
                );

            return (
                baseUrl +
                '/' +
                encodeURIComponent(
                    token
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PREVIEW
        |--------------------------------------------------------------------------
        */

        function revokePreviewUrl() {

            if (
                previewObjectUrl
            ) {

                URL.revokeObjectURL(
                    previewObjectUrl
                );

                previewObjectUrl =
                    null;
            }
        }

        function resetPreview() {

            if (!preview) {
                return;
            }

            preview.classList.remove(
                'show'
            );

            previewTitle.textContent =
                'Preview Dokumen';

            previewBadge.textContent =
                'Preview';

            previewCaption.textContent =
                'Preview akan tampil setelah memilih file.';

            previewImage.style.display =
                'none';

            previewPdf.style.display =
                'none';

            previewImage.removeAttribute(
                'src'
            );

            previewPdf.removeAttribute(
                'src'
            );

            previewSecondary.classList.add(
                'ske-hidden'
            );

            previewSecondary.innerHTML =
                '';

            revokePreviewUrl();
        }

        function showImagePreview(
            file,
            title = 'Preview Gambar'
        ) {

            resetPreview();

            preview.classList.add(
                'show'
            );

            previewTitle.textContent =
                title;

            previewBadge.textContent =
                'IMAGE';

            previewImage.style.display =
                'block';

            previewObjectUrl =
                URL.createObjectURL(
                    file
                );

            previewImage.src =
                previewObjectUrl;

            previewCaption.textContent =
                'Preview file gambar yang telah diproses browser.';
        }

        function showOriginalPdfPreview(
            file
        ) {

            resetPreview();

            preview.classList.add(
                'show'
            );

            previewTitle.textContent =
                'Preview PDF Asli';

            previewBadge.textContent =
                'PDF ASLI';

            previewPdf.style.display =
                'block';

            previewObjectUrl =
                URL.createObjectURL(
                    file
                );

            previewPdf.src =
                previewObjectUrl;

            previewCaption.textContent =
                'Ini adalah preview PDF asli yang dipilih. Server sedang menyiapkan hasil compression.';

            previewSecondary.classList.remove(
                'ske-hidden'
            );

            previewSecondary.innerHTML =
                '<strong>Proses berikutnya:</strong> ' +
                'PDF akan diproses Ghostscript di server dan preview hasil compression akan ditampilkan di sini.';
        }

        function showCompressedPdfPreview(
            token,
            profile
        ) {

            if (
                !token
            ) {
                return;
            }

            resetPreview();

            preview.classList.add(
                'show'
            );

            previewTitle.textContent =
                'Preview PDF Hasil Compression';

            previewBadge.textContent =
                'PDF COMPRESSED';

            previewPdf.style.display =
                'block';

            const previewUrl =
                getPdfTemporaryPreviewUrl(
                    token
                );

            previewPdf.src =
                previewUrl;

            previewCaption.textContent =
                'Ini adalah hasil PDF setelah diproses Ghostscript oleh server.';

            if (
                profile
            ) {

                previewSecondary.classList.remove(
                    'ske-hidden'
                );

                previewSecondary.innerHTML =
                    'Profile Ghostscript: <strong>' +
                    escapeHtml(
                        profile
                    ) +
                    '</strong>';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | COMPRESSION UI
        |--------------------------------------------------------------------------
        */

        function resetCompression() {

            if (!compression) {
                return;
            }

            compression.style.display =
                'none';

            compression.classList.remove(
                'success',
                'warning',
                'error',
                'processing'
            );

            compressionTitle.textContent =
                'Informasi Compression';

            compressionMessage.innerHTML =
                '';

            originalFormat.textContent =
                '-';

            finalFormat.textContent =
                '-';

            originalSize.textContent =
                '-';

            finalSize.textContent =
                '-';
        }

        function showCompression(
            data,
            type = ''
        ) {

            compression.style.display =
                'block';

            compression.classList.remove(
                'success',
                'warning',
                'error',
                'processing'
            );

            if (
                type
            ) {

                compression.classList.add(
                    type
                );
            }

            compressionTitle.textContent =
                data.title ||
                'Informasi Compression';

            originalFormat.textContent =
                data.originalFormat ||
                '-';

            finalFormat.textContent =
                data.finalFormat ||
                '-';

            originalSize.textContent =
                data.originalSize ||
                '-';

            finalSize.textContent =
                data.finalSize ||
                '-';

            compressionMessage.innerHTML =
                data.message ||
                '';
        }

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        function resetStatus() {

            if (!statusBox) {
                return;
            }

            statusBox.textContent =
                '';

            statusBox.classList.remove(
                'show',
                'blue',
                'green',
                'amber'
            );
        }

        function showStatus(
            message,
            type = 'blue'
        ) {

            if (!statusBox) {
                return;
            }

            statusBox.textContent =
                message;

            statusBox.classList.remove(
                'show',
                'blue',
                'green',
                'amber'
            );

            statusBox.classList.add(
                'show',
                type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VISUAL FILE
        |--------------------------------------------------------------------------
        */

        function resetFileVisual() {

            uploadBox?.classList.remove(
                'has-file'
            );

            uploadTitle.textContent =
                'Klik untuk memilih file baru';

            uploadSubtitle.textContent =
                'PDF, JPG, JPEG, PNG';

            fileInfo.classList.remove(
                'show'
            );

            fileInfoTitle.textContent =
                'File siap digunakan';

            fileInfoName.textContent =
                '';

            fileInfoSize.textContent =
                '';

            resetCompression();
            resetStatus();
            resetPreview();

            pdfCompressionReady =
                false;

            pdfCompressionToken =
                null;

            pdfOriginalHashToken =
                null;
        }

        function showFileVisual(
            file,
            title = 'File siap digunakan'
        ) {

            uploadBox.classList.add(
                'has-file'
            );

            uploadTitle.textContent =
                'File siap digunakan';

            fileInfo.classList.add(
                'show'
            );

            fileInfoName.textContent =
                file.name;

            fileInfoSize.textContent =
                formatFileSize(
                    file.size
                );

            fileInfoTitle.textContent =
                title;
        }

        function clearFileSelection() {

            processingToken++;

            fileInput.value =
                '';

            resetFileVisual();
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD IMAGE
        |--------------------------------------------------------------------------
        */

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

                            resolve(
                                image
                            );
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

        /*
        |--------------------------------------------------------------------------
        | IMAGE DIMENSION
        |--------------------------------------------------------------------------
        */

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
                    maxDimension /
                        width,

                    maxDimension /
                        height
                );

            return {
                width:
                    Math.max(
                        1,
                        Math.round(
                            width *
                            scale
                        )
                    ),

                height:
                    Math.max(
                        1,
                        Math.round(
                            height *
                            scale
                        )
                    )
            };
        }

        function buildDimensionList(
            width,
            height
        ) {

            const maxDimensions = [
                IMAGE_MAX_DIMENSION,
                2200,
                2000,
                1800,
                1600,
                1400,
                1200,
                IMAGE_MIN_DIMENSION
            ];

            const result =
                [];

            maxDimensions.forEach(
                function (
                    maxDimension
                ) {

                    const dimensions =
                        calculateDimensions(
                            width,
                            height,
                            maxDimension
                        );

                    if (
                        dimensions.width <
                            IMAGE_MIN_DIMENSION &&
                        dimensions.height <
                            IMAGE_MIN_DIMENSION
                    ) {

                        return;
                    }

                    const duplicate =
                        result.some(
                            function (
                                item
                            ) {

                                return (
                                    item.width ===
                                        dimensions.width &&
                                    item.height ===
                                        dimensions.height
                                );
                            }
                        );

                    if (
                        !duplicate
                    ) {

                        result.push(
                            dimensions
                        );
                    }
                }
            );

            return result;
        }

        /*
        |--------------------------------------------------------------------------
        | CANVAS TO FILE
        |--------------------------------------------------------------------------
        */

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
                                alpha:
                                    false
                            }
                        );

                    if (
                        !context
                    ) {

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

                            if (
                                !blob
                            ) {

                                reject(
                                    new Error(
                                        'Browser gagal membuat JPG.'
                                    )
                                );

                                return;
                            }

                            const baseName =
                                String(
                                    originalName ||
                                    'surat_keluar'
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
                                    'surat_keluar'
                                ) +
                                '_compressed.jpg';

                            resolve(
                                new File(
                                    [blob],
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

        /*
        |--------------------------------------------------------------------------
        | COMPRESS IMAGE
        |--------------------------------------------------------------------------
        */

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

            const dimensions =
                buildDimensionList(
                    width,
                    height
                );

            let bestFile =
                null;

            for (
                const dimension
                of dimensions
            ) {

                for (
                    const quality
                    of JPEG_QUALITIES
                ) {

                    const result =
                        await canvasToFile(
                            image,
                            dimension.width,
                            dimension.height,
                            quality,
                            file.name
                        );

                    if (
                        !bestFile ||
                        result.size <
                            bestFile.size
                    ) {

                        bestFile =
                            result;
                    }

                    if (
                        result.size <=
                        IMAGE_TARGET_SIZE
                    ) {

                        return result;
                    }
                }
            }

            if (
                bestFile &&
                bestFile.size <=
                    IMAGE_HARD_LIMIT
            ) {

                return bestFile;
            }

            throw new Error(
                'Hasil kompresi gambar masih terlalu besar.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILE SIGNATURE
        |--------------------------------------------------------------------------
        */

        async function detectPdfSignature(
            file
        ) {

            try {

                const buffer =
                    await file
                        .slice(
                            0,
                            5
                        )
                        .arrayBuffer();

                const bytes =
                    new Uint8Array(
                        buffer
                    );

                const header =
                    new TextDecoder()
                        .decode(
                            bytes
                        );

                return header ===
                    '%PDF-';

            } catch (
                error
            ) {

                return false;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | COMPRESS PDF SERVER
        |--------------------------------------------------------------------------
        */

        async function compressPdfOnServer(
            file,
            token
        ) {

            const formData =
                new FormData();

            formData.append(
                'lampiran_file',
                file
            );

            formData.append(
                '_token',
                @json(csrf_token())
            );

            showCompression(
                {
                    title:
                        '⏳ Memproses PDF di server',

                    originalFormat:
                        'PDF',

                    finalFormat:
                        'PDF',

                    originalSize:
                        formatFileSize(
                            file.size
                        ),

                    finalSize:
                        'Memproses...',

                    message:
                        '<strong>Ghostscript sedang memproses PDF.</strong><br>' +
                        'Server sedang membuat beberapa profile compression ' +
                        'dan memilih hasil PDF dengan ukuran paling kecil.'
                },
                'processing'
            );

            showStatus(
                'Sedang mengompres PDF di server. Jangan tutup halaman.',
                'blue'
            );

            try {

                const response =
                    await fetch(
                        getPdfCompressionUrl(),
                        {
                            method:
                                'POST',

                            body:
                                formData,

                            headers: {
                                'Accept':
                                    'application/json',

                                'X-Requested-With':
                                    'XMLHttpRequest'
                            },

                            credentials:
                                'same-origin'
                        }
                    );

                let data =
                    null;

                try {

                    data =
                        await response.json();

                } catch (
                    error
                ) {

                    throw new Error(
                        'Server mengembalikan response yang tidak valid.'
                    );
                }

                if (
                    token !==
                    processingToken
                ) {
                    return;
                }

                if (
                    !response.ok ||
                    !data?.success
                ) {

                    throw new Error(
                        data?.message ||
                        'Compression PDF gagal diproses server.'
                    );
                }

                pdfCompressionReady =
                    true;

                pdfCompressionToken =
                    data.token ||
                    null;

                /*
                |--------------------------------------------------------------------------
                | PDF TIDAK LEBIH KECIL
                |--------------------------------------------------------------------------
                */

                if (
                    data.compressed === false
                ) {

                    pdfOriginalHashToken =
                        data.token ||
                        null;

                    showCompression(
                        {
                            title:
                                '✓ PDF sudah optimal',

                            originalFormat:
                                'PDF',

                            finalFormat:
                                'PDF',

                            originalSize:
                                data.original_size_text ||
                                formatFileSize(
                                    file.size
                                ),

                            finalSize:
                                data.compressed_size_text ||
                                formatFileSize(
                                    file.size
                                ),

                            message:
                                'Hasil compression server tidak lebih kecil daripada file asli. ' +
                                '<strong>File asli akan dipertahankan</strong> saat update.',
                        },
                        'warning'
                    );

                    showStatus(
                        'PDF tidak menjadi lebih kecil. File asli akan digunakan.',
                        'amber'
                    );

                    showOriginalPdfPreview(
                        file
                    );

                    return data;
                }

                /*
                |--------------------------------------------------------------------------
                | PDF BERHASIL
                |--------------------------------------------------------------------------
                */

                showCompression(
                    {
                        title:
                            '✓ PDF berhasil dikompresi',

                        originalFormat:
                            'PDF',

                        finalFormat:
                            'PDF',

                        originalSize:
                            data.original_size_text ||
                            formatFileSize(
                                file.size
                            ),

                        finalSize:
                            data.compressed_size_text ||
                            '-',

                        message:
                            'Penghematan sekitar <strong>' +
                            (
                                Number(
                                    data.saving_percent ||
                                    0
                                )
                            ).toFixed(2) +
                            '%</strong>.' +
                            (
                                data.profile
                                    ? '<br>Profile: <strong>' +
                                      escapeHtml(
                                          data.profile
                                      ) +
                                      '</strong>'
                                    : ''
                            ),
                    },
                    'success'
                );

                showStatus(
                    'PDF hasil compression siap digunakan. Preview hasil compression ditampilkan di bawah.',
                    'green'
                );

                showCompressedPdfPreview(
                    data.token,
                    data.profile
                );

                return data;

            } catch (
                error
            ) {

                if (
                    token !==
                    processingToken
                ) {
                    return null;
                }

                pdfCompressionReady =
                    false;

                pdfCompressionToken =
                    null;

                showCompression(
                    {
                        title:
                            '✕ Compression PDF gagal',

                        originalFormat:
                            'PDF',

                        finalFormat:
                            'PDF',

                        originalSize:
                            formatFileSize(
                                file.size
                            ),

                        finalSize:
                            '-',

                        message:
                            escapeHtml(
                                error?.message ||
                                'Compression PDF gagal.'
                            )
                    },
                    'error'
                );

                showStatus(
                    'PDF belum siap disimpan karena proses compression server gagal.',
                    'amber'
                );

                showAlert(
                    'error',
                    'Compression PDF gagal',
                    error?.message ||
                    'Server gagal memproses PDF.'
                );

                return null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FILE CHANGE
        |--------------------------------------------------------------------------
        */

        fileInput.addEventListener(
            'change',
            async function () {

                const file =
                    fileInput.files?.[0];

                if (
                    !file
                ) {

                    resetFileVisual();

                    return;
                }

                const token =
                    ++processingToken;

                pdfCompressionReady =
                    false;

                pdfCompressionToken =
                    null;

                pdfOriginalHashToken =
                    null;

                resetCompression();
                resetStatus();
                resetPreview();

                /*
                |--------------------------------------------------------------------------
                | FORMAT
                |--------------------------------------------------------------------------
                */

                const extension =
                    getExtension(
                        file
                    );

                if (
                    !isAllowedExtension(
                        extension
                    )
                ) {

                    clearFileSelection();

                    showAlert(
                        'warning',
                        'Format tidak didukung',
                        'Gunakan PDF, JPG, JPEG, atau PNG.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | UKURAN
                |--------------------------------------------------------------------------
                */

                if (
                    file.size <= 0
                ) {

                    clearFileSelection();

                    showAlert(
                        'warning',
                        'File tidak valid',
                        'File yang dipilih kosong.'
                    );

                    return;
                }

                if (
                    file.size >
                    MAX_FILE_SIZE
                ) {

                    clearFileSelection();

                    showAlert(
                        'warning',
                        'File terlalu besar',
                        'Ukuran file asli maksimal 10 MB.'
                    );

                    return;
                }

                showFileVisual(
                    file
                );

                /*
                |--------------------------------------------------------------------------
                | PDF
                |--------------------------------------------------------------------------
                */

                if (
                    extension === 'pdf'
                ) {

                    uploadSubtitle.textContent =
                        'PDF • akan dikompresi oleh server';

                    showOriginalPdfPreview(
                        file
                    );

                    showCompression(
                        {
                            title:
                                '⏳ Menyiapkan PDF',

                            originalFormat:
                                'PDF',

                            finalFormat:
                                'PDF',

                            originalSize:
                                formatFileSize(
                                    file.size
                                ),

                            finalSize:
                                'Menunggu server...',

                            message:
                                'PDF asli sudah terdeteksi. Sistem sedang mengirim PDF ke server untuk proses Ghostscript.'
                        },
                        'processing'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | SIGNATURE CLIENT
                    |--------------------------------------------------------------------------
                    */

                    const validPdf =
                        await detectPdfSignature(
                            file
                        );

                    if (
                        token !==
                        processingToken
                    ) {
                        return;
                    }

                    if (
                        !validPdf
                    ) {

                        clearFileSelection();

                        showAlert(
                            'error',
                            'PDF tidak valid',
                            'File berekstensi PDF tetapi isi file tidak terdeteksi sebagai PDF.'
                        );

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | SERVER COMPRESSION
                    |--------------------------------------------------------------------------
                    */

                    await compressPdfOnServer(
                        file,
                        token
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | IMAGE
                |--------------------------------------------------------------------------
                */

                if (
                    !isImageExtension(
                        extension
                    )
                ) {

                    return;
                }

                uploadSubtitle.textContent =
                    'Gambar • dikonversi menjadi JPG';

                showImagePreview(
                    file
                );

                showCompression(
                    {
                        title:
                            '⏳ Memproses gambar',

                        originalFormat:
                            extension.toUpperCase(),

                        finalFormat:
                            'JPG',

                        originalSize:
                            formatFileSize(
                                file.size
                            ),

                        finalSize:
                            'Menghitung...',

                        message:
                            'Browser sedang melakukan resize dan kompresi gambar.'
                    },
                    'processing'
                );

                showStatus(
                    'Sedang mengompres gambar sebelum file dikirim.',
                    'blue'
                );

                try {

                    const compressed =
                        await compressImageFile(
                            file
                        );

                    if (
                        token !==
                        processingToken
                    ) {
                        return;
                    }

                    if (
                        compressed.size >
                        MAX_FILE_SIZE
                    ) {

                        throw new Error(
                            'Ukuran hasil gambar masih melebihi 10 MB.'
                        );
                    }

                    const transfer =
                        new DataTransfer();

                    transfer.items.add(
                        compressed
                    );

                    fileInput.files =
                        transfer.files;

                    const reduction =
                        getReductionPercent(
                            file.size,
                            compressed.size
                        );

                    showFileVisual(
                        compressed,
                        'Hasil kompresi siap'
                    );

                    fileInfoName.textContent =
                        compressed.name;

                    fileInfoSize.textContent =
                        formatFileSize(
                            compressed.size
                        );

                    uploadSubtitle.textContent =
                        'JPG • hasil kompresi browser';

                    showImagePreview(
                        compressed,
                        'Preview JPG Hasil Compression'
                    );

                    showCompression(
                        {
                            title:
                                '✓ Kompresi gambar berhasil',

                            originalFormat:
                                extension.toUpperCase(),

                            finalFormat:
                                'JPG',

                            originalSize:
                                formatFileSize(
                                    file.size
                                ),

                            finalSize:
                                formatFileSize(
                                    compressed.size
                                ),

                            message:
                                'Penghematan sekitar <strong>' +
                                reduction +
                                '%</strong>.' +
                                '<br>File final yang akan dikirim adalah JPG.'
                        },
                        'success'
                    );

                    showStatus(
                        'Gambar sudah dioptimalkan dan siap disimpan sebagai lampiran baru.',
                        'green'
                    );

                } catch (
                    error
                ) {

                    console.error(
                        'Compression error:',
                        error
                    );

                    if (
                        token !==
                        processingToken
                    ) {
                        return;
                    }

                    clearFileSelection();

                    showAlert(
                        'error',
                        'Gagal memproses gambar',
                        error?.message ||
                        'Browser gagal mengompres gambar.'
                    );
                }

            }
        );

        /*
        |--------------------------------------------------------------------------
        | SUBMIT
        |--------------------------------------------------------------------------
        */

        form.addEventListener(
            'submit',
            function (
                event
            ) {

                if (
                    submitting
                ) {

                    event.preventDefault();

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | TANGGAL
                |--------------------------------------------------------------------------
                */

                const suratDate =
                    tanggalSurat?.value ||
                    '';

                const keluarDate =
                    tanggalKeluar?.value ||
                    '';

                if (
                    suratDate &&
                    keluarDate &&
                    keluarDate <
                        suratDate
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

                const file =
                    fileInput.files?.[0] ||
                    null;

                if (
                    file
                ) {

                    const extension =
                        getExtension(
                            file
                        );

                    if (
                        !isAllowedExtension(
                            extension
                        )
                    ) {

                        event.preventDefault();

                        showAlert(
                            'warning',
                            'Format file tidak valid',
                            'Gunakan PDF, JPG, JPEG, atau PNG.'
                        );

                        return;
                    }

                    if (
                        file.size <= 0
                    ) {

                        event.preventDefault();

                        showAlert(
                            'warning',
                            'File tidak valid',
                            'File yang dipilih kosong.'
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
                            'Ukuran file maksimal 10 MB.'
                        );

                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | PDF HARUS SUDAH DI-COMPRESS / DIPROSES
                    |--------------------------------------------------------------------------
                    */

                    if (
                        extension ===
                        'pdf'
                    ) {

                        if (
                            !pdfCompressionReady
                        ) {

                            event.preventDefault();

                            showAlert(
                                'warning',
                                'PDF belum siap',
                                'Tunggu sampai proses compression PDF selesai.'
                            );

                            showStatus(
                                'PDF masih diproses oleh server.',
                                'blue'
                            );

                            return;
                        }

                        showStatus(
                            pdfOriginalHashToken
                                ? 'PDF tidak lebih kecil. File asli akan digunakan saat update.'
                                : 'PDF hasil compression siap dikirim ke server.',
                            pdfOriginalHashToken
                                ? 'amber'
                                : 'green'
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | IMAGE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        isImageExtension(
                            extension
                        )
                    ) {

                        showStatus(
                            'File gambar hasil compression siap dikirim. Lampiran lama akan diganti setelah update berhasil.',
                            'amber'
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | LOCK SUBMIT
                |--------------------------------------------------------------------------
                */

                submitting =
                    true;

                submitButton.disabled =
                    true;

                submitIcon.classList.add(
                    'ske-hidden'
                );

                submitLoading.classList.remove(
                    'ske-hidden'
                );

                submitProgress.classList.add(
                    'show'
                );

                if (
                    file &&
                    getExtension(file) ===
                        'pdf'
                ) {

                    submitText.textContent =
                        'Memperbarui dengan PDF...';

                } else if (
                    file
                ) {

                    submitText.textContent =
                        'Menyimpan lampiran...';

                } else {

                    submitText.textContent =
                        'Memperbarui...';
                }

            }
        );

        /*
        |--------------------------------------------------------------------------
        | BEFORE UNLOAD
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'beforeunload',
            function () {

                revokePreviewUrl();

            }
        );

    }
);
</script>

@endpush

@endsection