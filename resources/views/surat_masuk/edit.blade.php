@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')

@php
    use Illuminate\Support\Carbon;

    /*
    |--------------------------------------------------------------------------
    | DATA FORM
    |--------------------------------------------------------------------------
    */

    $tanggalSurat = old('tanggal_surat');

    if ($tanggalSurat === null && $suratMasuk->tanggal_surat) {
        try {
            $tanggalSurat = Carbon::parse($suratMasuk->tanggal_surat)->format('Y-m-d');
        } catch (\Throwable $e) {
            $tanggalSurat = substr((string) $suratMasuk->tanggal_surat, 0, 10);
        }
    }

    $tanggalTerima = old('tanggal_terima');

    if ($tanggalTerima === null && $suratMasuk->tanggal_terima) {
        try {
            $tanggalTerima = Carbon::parse($suratMasuk->tanggal_terima)->format('Y-m-d');
        } catch (\Throwable $e) {
            $tanggalTerima = substr((string) $suratMasuk->tanggal_terima, 0, 10);
        }
    }

    $selectedKategori = old(
        'kategori_surat_id',
        $suratMasuk->kategori_surat_id
    );

    $currentStatus = strtolower(
        trim(
            (string) old(
                'status',
                $suratMasuk->status ?: 'baru'
            )
        )
    );

    $statusOptions = [
        'baru'            => 'Baru',
        'diproses'        => 'Diproses',
        'didisposisikan'  => 'Didisposisikan',
        'selesai'         => 'Selesai',
        'diarsipkan'      => 'Diarsipkan',
    ];
@endphp

<style>
    /* ============================================================
       BASE
    ============================================================ */

    .edit-surat-page {
        width: 100%;
        max-width: 1180px;
        margin: 0 auto;
        padding: 4px 0 28px;
        color: #334155;
    }

    .edit-surat-page *,
    .edit-surat-page *::before,
    .edit-surat-page *::after {
        box-sizing: border-box;
    }

    /* ============================================================
       HEADER
    ============================================================ */

    .edit-header {
        margin-bottom: 18px;
    }

    .edit-header-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
    }

    .edit-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .edit-header-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #c7d2fe;
        border-radius: 11px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .edit-header-title {
        margin: 0;
        font-size: 21px;
        line-height: 1.25;
        font-weight: 750;
        letter-spacing: -.02em;
        color: #1e293b;
    }

    .edit-header-description {
        margin: 4px 0 0;
        font-size: 13px;
        line-height: 1.45;
        color: #64748b;
    }

    .edit-back-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 15px;
        flex: 0 0 auto;
        border: 1.5px solid #94a3b8;
        border-radius: 10px;
        background: #ffffff;
        color: #475569;
        font-size: 13px;
        font-weight: 650;
        text-decoration: none;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        transition: .18s ease;
    }

    .edit-back-btn:hover {
        border-color: #64748b;
        background: #f8fafc;
        color: #1e293b;
    }

    /* ============================================================
       ERROR
    ============================================================ */

    .edit-error {
        margin-bottom: 18px;
        padding: 13px 15px;
        border: 1.5px solid #fca5a5;
        border-radius: 11px;
        background: #fff1f2;
    }

    .edit-error-inner {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .edit-error-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #fee2e2;
        color: #dc2626;
    }

    .edit-error-title {
        margin: 0;
        font-size: 13px;
        font-weight: 700;
        color: #991b1b;
    }

    .edit-error-list {
        margin: 5px 0 0;
        padding: 0;
        list-style: none;
        font-size: 12px;
        line-height: 1.55;
        color: #b91c1c;
    }

    /* ============================================================
       MAIN CARD
    ============================================================ */

    .edit-main-card {
        overflow: hidden;
        border: 1.5px solid #64748b;
        border-radius: 15px;
        background: #ffffff;
        box-shadow:
            0 2px 5px rgba(15, 23, 42, .06),
            0 10px 22px rgba(15, 23, 42, .04);
    }

    /* ============================================================
       AGENDA BAR
    ============================================================ */

    .agenda-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        min-height: 66px;
        padding: 11px 18px;
        border-bottom: 1.5px solid #94a3b8;
        background: #f8fafc;
    }

    .agenda-info {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0;
    }

    .agenda-icon {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #c7d2fe;
        border-radius: 9px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .agenda-label {
        margin: 0;
        font-size: 10px;
        line-height: 1.3;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #64748b;
    }

    .agenda-number {
        margin: 2px 0 0;
        font-size: 14px;
        line-height: 1.4;
        font-weight: 750;
        color: #1e293b;
    }

    .agenda-badge {
        display: inline-flex;
        align-items: center;
        min-height: 27px;
        padding: 0 10px;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #ffffff;
        color: #64748b;
        font-size: 10px;
        font-weight: 650;
        white-space: nowrap;
    }

    /* ============================================================
       FORM BODY
    ============================================================ */

    .form-body {
        padding: 24px;
    }

    .form-section + .form-section {
        margin-top: 26px;
        padding-top: 26px;
        border-top: 1.5px solid #cbd5e1;
    }

    .section-heading {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 16px;
    }

    .section-line {
        width: 4px;
        height: 30px;
        flex: 0 0 4px;
        margin-top: 1px;
        border-radius: 999px;
        background: #4f46e5;
    }

    .section-title {
        margin: 0;
        font-size: 16px;
        line-height: 1.35;
        font-weight: 750;
        color: #1e293b;
    }

    .section-description {
        margin: 3px 0 0;
        font-size: 12px;
        line-height: 1.5;
        color: #64748b;
    }

    /* ============================================================
       FORM FIELDS
    ============================================================ */

    .field-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px 18px;
    }

    .field-full {
        grid-column: 1 / -1;
    }

    .form-field {
        min-width: 0;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        line-height: 1.35;
        font-weight: 700;
        color: #334155;
    }

    .required {
        color: #dc2626;
    }

    .form-control,
    .form-select,
    .form-textarea {
        width: 100%;
        border: 1.5px solid #94a3b8;
        border-radius: 9px;
        background: #ffffff;
        color: #1e293b;
        font-size: 13px;
        outline: none;
        transition:
            border-color .15s ease,
            box-shadow .15s ease,
            background .15s ease;
    }

    .form-control,
    .form-select {
        height: 42px;
        padding: 0 12px;
    }

    .form-textarea {
        min-height: 94px;
        padding: 10px 12px;
        resize: vertical;
        line-height: 1.5;
    }

    .form-control::placeholder,
    .form-textarea::placeholder {
        color: #94a3b8;
    }

    .form-control:focus,
    .form-select:focus,
    .form-textarea:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .10);
    }

    .field-error {
        margin: 5px 0 0;
        font-size: 11px;
        line-height: 1.4;
        font-weight: 600;
        color: #dc2626;
    }

    /* ============================================================
       ATTACHMENT GRID
    ============================================================ */

    .attachment-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 18px;
        align-items: stretch;
    }

    .attachment-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
        min-height: 0;
        border: 1.5px solid #64748b;
        border-radius: 13px;
        background: #f8fafc;
        overflow: hidden;
    }

    .attachment-card-header {
        min-height: 65px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 15px;
        border-bottom: 1.5px solid #94a3b8;
        background: #f1f5f9;
    }

    .attachment-card-title {
        margin: 0;
        font-size: 14px;
        line-height: 1.35;
        font-weight: 750;
        color: #1e293b;
    }

    .attachment-card-description {
        margin: 3px 0 0;
        font-size: 11px;
        line-height: 1.45;
        color: #64748b;
    }

    .optional-badge {
        display: inline-flex;
        align-items: center;
        min-height: 24px;
        padding: 0 8px;
        flex: 0 0 auto;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #ffffff;
        color: #64748b;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .attachment-card-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 12px;
        padding: 15px;
    }

    /* ============================================================
       INFO BOX
    ============================================================ */

    .info-box {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 10px 11px;
        border: 1px solid #bfdbfe;
        border-radius: 9px;
        background: #eff6ff;
    }

    .info-box-icon {
        width: 16px;
        height: 16px;
        flex: 0 0 16px;
        margin-top: 1px;
        color: #2563eb;
    }

    .info-box-text {
        margin: 0;
        font-size: 10.5px;
        line-height: 1.55;
        color: #1d4ed8;
    }

    /* ============================================================
       MODE BUTTON
    ============================================================ */

    .mode-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
    }

    .mode-button {
        min-width: 0;
        min-height: 57px;
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 10px;
        border: 1.5px solid #cbd5e1;
        border-radius: 9px;
        background: #ffffff;
        color: #334155;
        text-align: left;
        cursor: pointer;
        transition: .16s ease;
    }

    .mode-button:hover {
        border-color: #818cf8;
        background: #eef2ff;
    }

    .mode-button.active {
        border-color: #4f46e5;
        background: #eef2ff;
        box-shadow: inset 0 0 0 1px rgba(79, 70, 229, .12);
    }

    .mode-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #e2e8f0;
        color: #64748b;
    }

    .mode-button.active .mode-icon {
        background: #e0e7ff;
        color: #4f46e5;
    }

    .mode-title {
        display: block;
        font-size: 11px;
        line-height: 1.35;
        font-weight: 750;
        color: #1e293b;
    }

    .mode-description {
        display: block;
        margin-top: 1px;
        font-size: 9px;
        line-height: 1.35;
        color: #64748b;
    }

    /* ============================================================
       CURRENT FILE
    ============================================================ */

    .current-file {
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 57px;
        padding: 9px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #ffffff;
    }

    .current-file-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #f1f5f9;
        color: #64748b;
    }

    .current-file-content {
        min-width: 0;
        flex: 1;
    }

    .current-file-label {
        margin: 0;
        font-size: 9px;
        line-height: 1.3;
        font-weight: 700;
        color: #64748b;
    }

    .current-file-name {
        display: block;
        margin-top: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 11px;
        line-height: 1.4;
        font-weight: 700;
        color: #334155;
    }

    .current-file-empty {
        margin: 0;
        font-size: 11px;
        font-weight: 700;
        color: #d97706;
    }

    .current-file-empty-text {
        margin: 2px 0 0;
        font-size: 9px;
        color: #64748b;
    }

    .view-file-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 31px;
        padding: 0 10px;
        flex: 0 0 auto;
        border-radius: 7px;
        background: #f1f5f9;
        color: #475569;
        font-size: 10px;
        font-weight: 700;
        text-decoration: none;
        transition: .15s ease;
    }

    .view-file-btn:hover {
        background: #e0e7ff;
        color: #4338ca;
    }

    /* ============================================================
       UPLOAD
    ============================================================ */

    .upload-box {
        min-height: 130px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 15px;
        border: 2px dashed #94a3b8;
        border-radius: 11px;
        background: #ffffff;
        text-align: center;
        cursor: pointer;
        transition: .16s ease;
    }

    .upload-box:hover {
        border-color: #6366f1;
        background: #eef2ff;
    }

    .upload-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 7px;
        border-radius: 10px;
        background: #e0e7ff;
        color: #4f46e5;
    }

    .upload-title {
        font-size: 11px;
        line-height: 1.4;
        font-weight: 750;
        color: #334155;
    }

    .upload-format {
        margin-top: 2px;
        font-size: 9px;
        color: #64748b;
    }

    .upload-limit {
        margin-top: 3px;
        font-size: 8.5px;
        color: #94a3b8;
    }

    /* ============================================================
       SELECTED FILE
    ============================================================ */

    .selected-file {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 9px 10px;
        border: 1px solid #a7f3d0;
        border-radius: 9px;
        background: #ecfdf5;
    }

    .selected-file-icon {
        width: 32px;
        height: 32px;
        flex: 0 0 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #d1fae5;
        color: #059669;
    }

    .selected-file-content {
        min-width: 0;
        flex: 1;
    }

    .selected-file-title {
        margin: 0;
        font-size: 9px;
        font-weight: 750;
        color: #047857;
    }

    .selected-file-name {
        margin: 1px 0 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 10px;
        font-weight: 700;
        color: #334155;
    }

    .selected-file-size {
        margin: 1px 0 0;
        font-size: 9px;
        color: #64748b;
    }

    .clear-file-btn {
        width: 29px;
        height: 29px;
        flex: 0 0 29px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
    }

    .clear-file-btn:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    /* ============================================================
       CAMERA
    ============================================================ */

    .camera-container {
        overflow: hidden;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff;
    }

    .camera-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 10px 12px;
        border-bottom: 1px solid #cbd5e1;
        background: #f8fafc;
    }

    .camera-title {
        margin: 0;
        font-size: 11px;
        font-weight: 750;
        color: #334155;
    }

    .camera-description {
        margin: 2px 0 0;
        font-size: 9px;
        color: #64748b;
    }

    .camera-badge {
        padding: 4px 8px;
        border-radius: 999px;
        background: #e0e7ff;
        color: #4338ca;
        font-size: 8px;
        font-weight: 750;
    }

    .camera-preview {
        position: relative;
        min-height: 225px;
        overflow: hidden;
        background: #0f172a;
    }

    .camera-video {
        width: 100%;
        height: 100%;
        min-height: 225px;
        display: block;
        object-fit: contain;
    }

    .camera-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: #0f172a;
        text-align: center;
    }

    .camera-placeholder-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: rgba(255,255,255,.08);
        color: #94a3b8;
    }

    .camera-placeholder-title {
        margin: 9px 0 0;
        font-size: 11px;
        font-weight: 700;
        color: #cbd5e1;
    }

    .camera-placeholder-description {
        margin: 3px 0 0;
        font-size: 9px;
        color: #64748b;
    }

    .camera-error {
        position: absolute;
        bottom: 8px;
        left: 8px;
        right: 8px;
        padding: 8px 10px;
        border: 1px solid rgba(248,113,113,.4);
        border-radius: 8px;
        background: rgba(69,10,10,.92);
        color: #fecaca;
        font-size: 9px;
        font-weight: 600;
    }

    .camera-frame {
        position: absolute;
        inset: 28px;
        border: 2px dashed rgba(255,255,255,.32);
        border-radius: 10px;
        pointer-events: none;
    }

    .camera-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 7px;
        padding: 10px;
    }

    .camera-btn {
        min-height: 37px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 9px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer;
        transition: .15s ease;
    }

    .camera-btn:disabled {
        cursor: not-allowed;
        opacity: .5;
    }

    .camera-btn-primary {
        border: 1px solid #4338ca;
        background: #4f46e5;
        color: #ffffff;
    }

    .camera-btn-primary:hover:not(:disabled) {
        background: #4338ca;
    }

    .camera-btn-success {
        border: 1px solid #059669;
        background: #10b981;
        color: #ffffff;
    }

    .camera-btn-success:hover:not(:disabled) {
        background: #059669;
    }

    .camera-btn-secondary {
        border: 1px solid #94a3b8;
        background: #ffffff;
        color: #475569;
    }

    .camera-btn-secondary:hover:not(:disabled) {
        background: #f1f5f9;
    }

    /* ============================================================
       SCAN RESULT
    ============================================================ */

    .scan-result {
        padding: 11px;
        border-top: 1px solid #a7f3d0;
        background: #ecfdf5;
    }

    .scan-result-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .scan-result-title {
        margin: 0;
        font-size: 11px;
        font-weight: 750;
        color: #065f46;
    }

    .scan-result-description {
        margin: 3px 0 0;
        font-size: 9px;
        line-height: 1.45;
        color: #047857;
    }

    .retake-btn {
        min-height: 29px;
        padding: 0 9px;
        border: 1px solid #6ee7b7;
        border-radius: 7px;
        background: #ffffff;
        color: #047857;
        font-size: 9px;
        font-weight: 700;
        cursor: pointer;
    }

    .scan-image-wrapper {
        margin-top: 9px;
        overflow: hidden;
        border: 1px solid #a7f3d0;
        border-radius: 8px;
        background: #ffffff;
    }

    .scan-preview-image {
        display: block;
        width: 100%;
        max-height: 330px;
        object-fit: contain;
    }

    .scan-compression-info {
        margin-top: 5px;
        text-align: center;
        font-size: 9px;
        font-weight: 700;
        color: #047857;
    }

    /* ============================================================
       PHYSICAL ARCHIVE
    ============================================================ */

    .physical-content {
        display: flex;
        flex: 1;
        flex-direction: column;
        justify-content: center;
        padding: 16px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        background: #ffffff;
    }

    .physical-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #e0e7ff;
        color: #4f46e5;
    }

    .physical-title {
        margin: 10px 0 0;
        font-size: 13px;
        font-weight: 750;
        color: #334155;
    }

    .physical-description {
        margin: 4px 0 0;
        font-size: 10px;
        line-height: 1.55;
        color: #64748b;
    }

    .physical-field {
        margin-top: 15px;
    }

    .physical-example {
        margin-top: 8px;
        padding: 9px 10px;
        border-radius: 8px;
        background: #f8fafc;
    }

    .physical-example p {
        margin: 0;
        font-size: 9px;
        line-height: 1.5;
        color: #64748b;
    }

    .physical-example strong {
        color: #334155;
    }

    /* ============================================================
       SMALL INFORMATION NOTE
    ============================================================ */

    .attachment-note {
        margin-top: auto;
        padding: 9px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        background: #f8fafc;
    }

    .attachment-note p {
        margin: 0;
        font-size: 9px;
        line-height: 1.55;
        color: #64748b;
    }

    /* ============================================================
       FOOTER
    ============================================================ */

    .form-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        min-height: 65px;
        padding: 11px 18px;
        border-top: 1.5px solid #94a3b8;
        background: #f8fafc;
    }

    .footer-btn {
        min-height: 39px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 16px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: .15s ease;
    }

    .footer-btn-cancel {
        border: 1.5px solid #94a3b8;
        background: #ffffff;
        color: #475569;
    }

    .footer-btn-cancel:hover {
        background: #f1f5f9;
        color: #1e293b;
    }

    .footer-btn-submit {
        border: 1.5px solid #4338ca;
        background: #4f46e5;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(79,70,229,.16);
    }

    .footer-btn-submit:hover:not(:disabled) {
        background: #4338ca;
    }

    .footer-btn-submit:disabled {
        cursor: not-allowed;
        opacity: .65;
    }

    /* ============================================================
       HIDDEN
    ============================================================ */

    .hidden {
        display: none !important;
    }

    /* ============================================================
       RESPONSIVE
    ============================================================ */

    @media (max-width: 900px) {
        .edit-surat-page {
            max-width: 100%;
        }

        .form-body {
            padding: 19px;
        }

        .attachment-grid {
            grid-template-columns: 1fr;
        }

        .attachment-card {
            min-height: auto;
        }

        .physical-content {
            justify-content: flex-start;
        }
    }

    @media (max-width: 700px) {
        .edit-surat-page {
            padding-bottom: 18px;
        }

        .edit-header-inner {
            align-items: flex-start;
            flex-direction: column;
        }

        .edit-back-btn {
            width: 100%;
        }

        .edit-main-card {
            border-radius: 12px;
        }

        .agenda-bar {
            align-items: flex-start;
            flex-direction: column;
            padding: 11px 13px;
        }

        .agenda-badge {
            align-self: flex-start;
        }

        .form-body {
            padding: 15px;
        }

        .field-grid {
            grid-template-columns: 1fr;
            gap: 13px;
        }

        .field-full {
            grid-column: auto;
        }

        .form-section + .form-section {
            margin-top: 22px;
            padding-top: 22px;
        }

        .section-heading {
            margin-bottom: 13px;
        }

        .attachment-grid {
            gap: 13px;
        }

        .attachment-card-header {
            min-height: 61px;
            padding: 10px 12px;
        }

        .attachment-card-body {
            padding: 12px;
        }

        .camera-actions {
            grid-template-columns: 1fr;
        }

        .form-footer {
            flex-direction: column-reverse;
            align-items: stretch;
            padding: 10px 13px;
        }

        .footer-btn {
            width: 100%;
        }
    }

    @media (max-width: 420px) {
        .edit-header-left {
            align-items: flex-start;
        }

        .edit-header-icon {
            width: 38px;
            height: 38px;
            flex-basis: 38px;
        }

        .edit-header-title {
            font-size: 18px;
        }

        .edit-header-description {
            font-size: 11px;
        }

        .mode-grid {
            grid-template-columns: 1fr;
        }

        .mode-button {
            min-height: 52px;
        }

        .current-file {
            align-items: flex-start;
        }

        .view-file-btn {
            align-self: center;
        }
    }
</style>

<div class="edit-surat-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="edit-header">
        <div class="edit-header-inner">

            <div class="edit-header-left">

                <div class="edit-header-icon">
                    <svg class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-7.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 7.5-7.5z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h1 class="edit-header-title">
                        Edit Surat Masuk
                    </h1>

                    <p class="edit-header-description">
                        Perbarui informasi arsip surat masuk yang tersimpan di dalam sistem.
                    </p>
                </div>

            </div>

            <a href="{{ route('surat-masuk.index') }}"
                class="edit-back-btn">

                <svg class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>

                Kembali
            </a>

        </div>
    </div>


    {{-- =========================================================
         VALIDATION ERROR
    ========================================================== --}}
    @if($errors->any())

        <div class="edit-error">

            <div class="edit-error-inner">

                <div class="edit-error-icon">
                    <svg class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>

                <div class="min-w-0">

                    <p class="edit-error-title">
                        Data belum dapat diperbarui.
                    </p>

                    <ul class="edit-error-list">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>

                </div>

            </div>

        </div>

    @endif


    {{-- =========================================================
         FORM
    ========================================================== --}}
    <form id="form-surat"
        action="{{ route('surat-masuk.update', $suratMasuk->id) }}"
        method="POST"
        enctype="multipart/form-data">

        @csrf
        @method('PUT')

        <input type="hidden"
            name="nomor_agenda"
            value="{{ old('nomor_agenda', $suratMasuk->nomor_agenda) }}">


        <div class="edit-main-card">

            {{-- =================================================
                 AGENDA
            ================================================== --}}
            <div class="agenda-bar">

                <div class="agenda-info">

                    <div class="agenda-icon">
                        <svg class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 013-3h0a3 3 0 013 3"/>
                        </svg>
                    </div>

                    <div class="min-w-0">

                        <p class="agenda-label">
                            Nomor Agenda Sistem
                        </p>

                        <p class="agenda-number truncate">
                            {{ $suratMasuk->nomor_agenda ?: '-' }}
                        </p>

                    </div>

                </div>

                <span class="agenda-badge">
                    Nomor agenda tidak diubah
                </span>

            </div>


            {{-- =================================================
                 BODY
            ================================================== --}}
            <div class="form-body">


                {{-- =================================================
                     INFORMASI UTAMA
                ================================================== --}}
                <section class="form-section">

                    <div class="section-heading">

                        <div class="section-line"></div>

                        <div>
                            <h2 class="section-title">
                                Informasi Utama Surat
                            </h2>

                            <p class="section-description">
                                Perbarui identitas dan informasi utama surat masuk.
                            </p>
                        </div>

                    </div>


                    <div class="field-grid">

                        {{-- NOMOR SURAT --}}
                        <div class="form-field">

                            <label for="nomor_surat"
                                class="form-label">
                                Nomor Surat
                                <span class="required">*</span>
                            </label>

                            <input id="nomor_surat"
                                name="nomor_surat"
                                type="text"
                                required
                                autocomplete="off"
                                value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}"
                                placeholder="Contoh: 005/B/I/2026"
                                class="form-control @error('nomor_surat') border-red-400 @enderror">

                            @error('nomor_surat')
                                <p class="field-error">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- PENGIRIM --}}
                        <div class="form-field">

                            <label for="pengirim"
                                class="form-label">
                                Instansi Pengirim
                                <span class="required">*</span>
                            </label>

                            <input id="pengirim"
                                name="pengirim"
                                type="text"
                                required
                                autocomplete="organization"
                                value="{{ old('pengirim', $suratMasuk->pengirim) }}"
                                placeholder="Masukkan nama instansi pengirim"
                                class="form-control @error('pengirim') border-red-400 @enderror">

                            @error('pengirim')
                                <p class="field-error">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- TANGGAL SURAT --}}
                        <div class="form-field">

                            <label for="tanggal_surat"
                                class="form-label">
                                Tanggal Surat
                                <span class="required">*</span>
                            </label>

                            <input id="tanggal_surat"
                                name="tanggal_surat"
                                type="date"
                                required
                                value="{{ $tanggalSurat }}"
                                class="form-control @error('tanggal_surat') border-red-400 @enderror">

                            @error('tanggal_surat')
                                <p class="field-error">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- TANGGAL DITERIMA --}}
                        <div class="form-field">

                            <label for="tanggal_terima"
                                class="form-label">
                                Tanggal Diterima
                                <span class="required">*</span>
                            </label>

                            <input id="tanggal_terima"
                                name="tanggal_terima"
                                type="date"
                                required
                                value="{{ $tanggalTerima }}"
                                class="form-control @error('tanggal_terima') border-red-400 @enderror">

                            @error('tanggal_terima')
                                <p class="field-error">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- KATEGORI --}}
                        <div class="form-field">

                            <label for="kategori_surat_id"
                                class="form-label">
                                Kategori Surat
                                <span class="required">*</span>
                            </label>

                            <select id="kategori_surat_id"
                                name="kategori_surat_id"
                                required
                                class="form-select @error('kategori_surat_id') border-red-400 @enderror">

                                <option value="" disabled @selected(!$selectedKategori)>
                                    Pilih kategori surat
                                </option>

                                @foreach(($kategoris ?? collect()) as $kategori)

                                    <option value="{{ $kategori->id }}"
                                        @selected(
                                            (string) $selectedKategori ===
                                            (string) $kategori->id
                                        )>

                                        {{ $kategori->nama_kategori }}

                                        @if(!empty($kategori->sifat))
                                            ({{ ucfirst($kategori->sifat) }})
                                        @endif

                                    </option>

                                @endforeach

                            </select>

                            @error('kategori_surat_id')
                                <p class="field-error">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- STATUS --}}
                        <div class="form-field">

                            <label for="status"
                                class="form-label">
                                Status Surat
                                <span class="required">*</span>
                            </label>

                            <select id="status"
                                name="status"
                                required
                                class="form-select @error('status') border-red-400 @enderror">

                                @foreach($statusOptions as $value => $label)

                                    <option value="{{ $value }}"
                                        @selected($currentStatus === $value)>
                                        {{ $label }}
                                    </option>

                                @endforeach

                            </select>

                            @error('status')
                                <p class="field-error">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- PERIHAL --}}
                        <div class="form-field field-full">

                            <label for="perihal"
                                class="form-label">
                                Perihal
                                <span class="required">*</span>
                            </label>

                            <textarea id="perihal"
                                name="perihal"
                                rows="4"
                                required
                                placeholder="Tuliskan perihal surat"
                                class="form-textarea @error('perihal') border-red-400 @enderror">{{ old('perihal', $suratMasuk->perihal) }}</textarea>

                            @error('perihal')
                                <p class="field-error">{{ $message }}</p>
                            @enderror

                        </div>


                        {{-- RINGKASAN --}}
                        <div class="form-field field-full">

                            <label for="ringkasan"
                                class="form-label">
                                Ringkasan
                                <span class="font-normal text-slate-400">
                                    (opsional)
                                </span>
                            </label>

                            <textarea id="ringkasan"
                                name="ringkasan"
                                rows="4"
                                placeholder="Ringkasan isi surat, bila diperlukan..."
                                class="form-textarea @error('ringkasan') border-red-400 @enderror">{{ old('ringkasan', $suratMasuk->ringkasan) }}</textarea>

                            @error('ringkasan')
                                <p class="field-error">{{ $message }}</p>
                            @enderror

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     LAMPIRAN
                ================================================== --}}
                <section class="form-section">

                    <div class="section-heading">

                        <div class="section-line"></div>

                        <div>
                            <h2 class="section-title">
                                Lampiran Dokumen & Arsip Fisik
                            </h2>

                            <p class="section-description">
                                Ganti dokumen melalui upload atau scan, serta perbarui lokasi arsip fisik.
                            </p>
                        </div>

                    </div>


                    <div class="attachment-grid">


                        {{-- =================================================
                             BERKAS DIGITAL
                        ================================================== --}}
                        <div class="attachment-card">

                            <div class="attachment-card-header">

                                <div class="min-w-0">

                                    <h3 class="attachment-card-title">
                                        Berkas Digital
                                    </h3>

                                    <p class="attachment-card-description">
                                        Upload file baru atau scan menggunakan kamera.
                                    </p>

                                </div>

                                <span class="optional-badge">
                                    Opsional
                                </span>

                            </div>


                            <div class="attachment-card-body">


                                {{-- INFO --}}
                                <div class="info-box">

                                    <svg class="info-box-icon"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/>
                                    </svg>

                                    <p class="info-box-text">
                                        Maksimal <strong>10 MB</strong>.
                                        PDF disimpan tanpa kompresi.
                                        JPG, JPEG, dan PNG dikompres otomatis.
                                    </p>

                                </div>


                                {{-- MODE --}}
                                <div class="mode-grid">

                                    <button type="button"
                                        id="mode-upload-btn"
                                        aria-selected="true"
                                        class="mode-button active">

                                        <span class="mode-icon">

                                            <svg class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l3 3m-3-3v12"/>
                                            </svg>

                                        </span>

                                        <span class="min-w-0">

                                            <span class="mode-title">
                                                Upload File
                                            </span>

                                            <span class="mode-description">
                                                Dari perangkat
                                            </span>

                                        </span>

                                    </button>


                                    <button type="button"
                                        id="mode-scan-btn"
                                        aria-selected="false"
                                        class="mode-button">

                                        <span class="mode-icon">

                                            <svg class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 7V5a1 1 0 011-1h2M17 4h2a1 1 0 011 1v2M20 17v2a1 1 0 01-1 1h-2M7 20H5a1 1 0 01-1-1v-2M7 12h10M7 9h10M7 15h6"/>
                                            </svg>

                                        </span>

                                        <span class="min-w-0">

                                            <span class="mode-title">
                                                Scan Dokumen
                                            </span>

                                            <span class="mode-description">
                                                Gunakan kamera
                                            </span>

                                        </span>

                                    </button>

                                </div>


                                {{-- FILE LAMA --}}
                                <div class="current-file">

                                    <div class="current-file-icon">

                                        <svg class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.414l4.414 4.414A2 2 0 0118 8.414V19a2 2 0 01-2 2z"/>
                                        </svg>

                                    </div>

                                    <div class="current-file-content">

                                        @if($suratMasuk->lampiran_file)

                                            <p class="current-file-label">
                                                Dokumen saat ini
                                            </p>

                                            <span class="current-file-name"
                                                title="{{ basename($suratMasuk->lampiran_file) }}">
                                                {{ basename($suratMasuk->lampiran_file) }}
                                            </span>

                                        @else

                                            <p class="current-file-empty">
                                                Belum ada dokumen
                                            </p>

                                            <p class="current-file-empty-text">
                                                Upload atau scan dokumen baru.
                                            </p>

                                        @endif

                                    </div>

                                    @if($suratMasuk->lampiran_file)

                                        <a href="{{ route('surat-masuk.preview-lampiran', $suratMasuk->id) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="view-file-btn">
                                            Lihat
                                        </a>

                                    @endif

                                </div>


                                {{-- UPLOAD PANEL --}}
                                <div id="upload-panel">

                                    <label for="lampiran_file"
                                        id="upload-box"
                                        class="upload-box">

                                        <span class="upload-icon">

                                            <svg class="h-5 w-5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l3 3m-3-3v12"/>
                                            </svg>

                                        </span>

                                        <span id="file-label-text"
                                            class="upload-title">
                                            Pilih file baru
                                        </span>

                                        <span class="upload-format">
                                            PDF, JPG, JPEG, PNG
                                        </span>

                                        <span class="upload-limit">
                                            Maksimal 10 MB • Gambar dikompres otomatis
                                        </span>

                                        <input type="file"
                                            id="lampiran_file"
                                            name="lampiran_file"
                                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                            class="sr-only">

                                    </label>


                                    {{-- FILE BARU --}}
                                    <div id="selected-file"
                                        class="selected-file hidden">

                                        <div class="selected-file-icon">

                                            <svg class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 13l4 4L19 7"/>
                                            </svg>

                                        </div>

                                        <div class="selected-file-content">

                                            <p class="selected-file-title">
                                                File baru dipilih
                                            </p>

                                            <p id="selected-file-name"
                                                class="selected-file-name">
                                            </p>

                                            <p id="selected-file-size"
                                                class="selected-file-size">
                                            </p>

                                        </div>

                                        <button type="button"
                                            id="clear-file-btn"
                                            class="clear-file-btn"
                                            title="Hapus file"
                                            aria-label="Hapus file">

                                            <svg class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"/>
                                            </svg>

                                        </button>

                                    </div>

                                </div>


                                {{-- SCAN PANEL --}}
                                <div id="scan-panel" class="hidden">

                                    <div class="camera-container">

                                        <div class="camera-header">

                                            <div>
                                                <h4 class="camera-title">
                                                    Scan Dokumen
                                                </h4>

                                                <p class="camera-description">
                                                    Arahkan kamera ke dokumen lalu ambil gambar.
                                                </p>
                                            </div>

                                            <span class="camera-badge">
                                                Kamera
                                            </span>

                                        </div>


                                        <div class="camera-preview">

                                            <video id="camera-video"
                                                class="camera-video"
                                                autoplay
                                                playsinline
                                                muted>
                                            </video>


                                            <div id="camera-placeholder"
                                                class="camera-placeholder">

                                                <div class="camera-placeholder-icon">

                                                    <svg class="h-6 w-6"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M3 7h4l2-3h6l2 3h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z"/>
                                                        <circle cx="12"
                                                            cy="13"
                                                            r="3"/>
                                                    </svg>

                                                </div>

                                                <p class="camera-placeholder-title">
                                                    Kamera belum aktif
                                                </p>

                                                <p class="camera-placeholder-description">
                                                    Klik tombol "Aktifkan Kamera".
                                                </p>

                                            </div>


                                            <div id="camera-error"
                                                class="camera-error hidden"
                                                role="alert">
                                            </div>


                                            <div class="camera-frame"></div>

                                        </div>


                                        <div class="camera-actions">

                                            <button type="button"
                                                id="start-camera-btn"
                                                class="camera-btn camera-btn-primary">

                                                <svg class="h-3.5 w-3.5"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M3 7h4l2-3h6l2 3h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z"/>
                                                </svg>

                                                Aktifkan Kamera

                                            </button>


                                            <button type="button"
                                                id="capture-btn"
                                                disabled
                                                class="camera-btn camera-btn-success">

                                                <svg class="h-3.5 w-3.5"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <circle cx="12"
                                                        cy="12"
                                                        r="9"/>
                                                    <circle cx="12"
                                                        cy="12"
                                                        r="3"/>
                                                </svg>

                                                Ambil Gambar

                                            </button>


                                            <button type="button"
                                                id="stop-camera-btn"
                                                disabled
                                                class="camera-btn camera-btn-secondary">

                                                Matikan Kamera

                                            </button>

                                        </div>


                                        {{-- SCAN RESULT --}}
                                        <div id="scan-result"
                                            class="scan-result hidden">

                                            <div class="scan-result-header">

                                                <div>
                                                    <p class="scan-result-title">
                                                        Dokumen hasil scan
                                                    </p>

                                                    <p class="scan-result-description">
                                                        Hasil scan akan menggantikan lampiran lama setelah data diperbarui.
                                                    </p>
                                                </div>

                                                <button type="button"
                                                    id="retake-btn"
                                                    class="retake-btn">
                                                    Scan Ulang
                                                </button>

                                            </div>


                                            <div class="scan-image-wrapper">

                                                <img id="scan-preview-image"
                                                    src=""
                                                    alt="Hasil scan dokumen"
                                                    class="scan-preview-image">

                                            </div>


                                            <div id="scan-compression-info"
                                                class="scan-compression-info">
                                            </div>

                                        </div>

                                    </div>


                                    <input type="hidden"
                                        name="captured_image"
                                        id="captured_image"
                                        value="{{ old('captured_image') }}">

                                </div>


                                {{-- CATATAN --}}
                                <div class="attachment-note">

                                    <p>
                                        Jika tidak memilih file baru dan tidak melakukan scan,
                                        dokumen lama tetap dipertahankan.
                                        PDF tidak dikompres, sedangkan JPG, JPEG, dan PNG
                                        dikompres otomatis sebelum upload.
                                    </p>

                                </div>


                                @error('lampiran_file')
                                    <p class="field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                                @error('captured_image')
                                    <p class="field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>


                        {{-- =================================================
                             ARSIP FISIK
                        ================================================== --}}
                        <div class="attachment-card">

                            <div class="attachment-card-header">

                                <div class="min-w-0">

                                    <h3 class="attachment-card-title">
                                        Lokasi Arsip Fisik
                                    </h3>

                                    <p class="attachment-card-description">
                                        Perbarui lokasi penyimpanan arsip fisik.
                                    </p>

                                </div>

                                <span class="optional-badge">
                                    Opsional
                                </span>

                            </div>


                            <div class="attachment-card-body">

                                <div class="physical-content">

                                    <div class="physical-icon">

                                        <svg class="h-5 w-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>

                                    </div>


                                    <h4 class="physical-title">
                                        Lokasi Penyimpanan
                                    </h4>


                                    <p class="physical-description">
                                        Masukkan posisi rak, lemari, box,
                                        atau map tempat arsip disimpan.
                                    </p>


                                    <div class="physical-field">

                                        <label for="lokasi_arsip_fisik"
                                            class="form-label">
                                            Detail Posisi Lemari / Box
                                        </label>

                                        <input type="text"
                                            id="lokasi_arsip_fisik"
                                            name="lokasi_arsip_fisik"
                                            value="{{ old('lokasi_arsip_fisik', $suratMasuk->lokasi_arsip_fisik) }}"
                                            placeholder="Contoh: Rak A-3 Box 12"
                                            class="form-control @error('lokasi_arsip_fisik') border-red-400 @enderror">

                                        @error('lokasi_arsip_fisik')
                                            <p class="field-error">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                    </div>


                                    <div class="physical-example">

                                        <p>
                                            <strong>Contoh:</strong>
                                            Rak A-3 Box 12
                                            <span class="mx-1">atau</span>
                                            Lemari B-2 Map 07
                                        </p>

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
            <div class="form-footer">

                <a href="{{ route('surat-masuk.index') }}"
                    class="footer-btn footer-btn-cancel">
                    Batal
                </a>


                <button type="submit"
                    id="submit-btn"
                    class="footer-btn footer-btn-submit">

                    <svg class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"/>
                    </svg>

                    <span id="submit-text">
                        Perbarui Surat Masuk
                    </span>

                </button>

            </div>

        </div>

    </form>

</div>


{{-- ================================================================
     JAVASCRIPT
================================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const form = document.getElementById('form-surat');

    const modeUploadBtn = document.getElementById('mode-upload-btn');
    const modeScanBtn = document.getElementById('mode-scan-btn');

    const uploadPanel = document.getElementById('upload-panel');
    const scanPanel = document.getElementById('scan-panel');

    const fileInput = document.getElementById('lampiran_file');
    const uploadBox = document.getElementById('upload-box');

    const selectedFile = document.getElementById('selected-file');
    const selectedFileName = document.getElementById('selected-file-name');
    const selectedFileSize = document.getElementById('selected-file-size');
    const clearFileBtn = document.getElementById('clear-file-btn');

    const cameraVideo = document.getElementById('camera-video');
    const cameraPlaceholder = document.getElementById('camera-placeholder');
    const cameraError = document.getElementById('camera-error');

    const startCameraBtn = document.getElementById('start-camera-btn');
    const captureBtn = document.getElementById('capture-btn');
    const stopCameraBtn = document.getElementById('stop-camera-btn');

    const retakeBtn = document.getElementById('retake-btn');
    const scanResult = document.getElementById('scan-result');
    const scanPreviewImage = document.getElementById('scan-preview-image');
    const scanCompressionInfo = document.getElementById('scan-compression-info');

    const capturedInput = document.getElementById('captured_image');

    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');

    if (!form || !fileInput || !capturedInput) {
        return;
    }


    /* ============================================================
       KONFIGURASI
    ============================================================ */

    const MAX_FILE_SIZE = 10 * 1024 * 1024;
    const TARGET_IMAGE_SIZE = 2.5 * 1024 * 1024;
    const MAX_COMPRESSED_IMAGE_SIZE = 5 * 1024 * 1024;

    const MAX_IMAGE_DIMENSION = 2200;
    const MIN_IMAGE_DIMENSION = 1200;

    const JPEG_QUALITIES = [
        0.84, 0.80, 0.76, 0.72, 0.68,
        0.64, 0.60, 0.56, 0.52, 0.48,
        0.44, 0.40, 0.36, 0.32
    ];


    let cameraStream = null;
    let previewObjectUrl = null;
    let isSubmitting = false;


    /* ============================================================
       HELPER
    ============================================================ */

    function formatFileSize(bytes) {
        if (!Number.isFinite(bytes) || bytes <= 0) {
            return '0 KB';
        }

        if (bytes < 1024 * 1024) {
            return `${(bytes / 1024).toFixed(1)} KB`;
        }

        return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
    }


    function getReductionPercent(originalSize, compressedSize) {
        if (!originalSize || originalSize <= 0) {
            return 0;
        }

        return Math.max(
            0,
            Math.round(
                (1 - compressedSize / originalSize) * 100
            )
        );
    }


    /* ============================================================
       CAMERA ERROR
    ============================================================ */

    function showCameraError(message) {
        if (!cameraError) {
            return;
        }

        cameraError.textContent = message;
        cameraError.classList.remove('hidden');
    }


    function clearCameraError() {
        if (!cameraError) {
            return;
        }

        cameraError.textContent = '';
        cameraError.classList.add('hidden');
    }


    /* ============================================================
       MODE UPLOAD
    ============================================================ */

    function activateUploadMode() {

        modeUploadBtn?.classList.add('active');
        modeScanBtn?.classList.remove('active');

        modeUploadBtn?.setAttribute(
            'aria-selected',
            'true'
        );

        modeScanBtn?.setAttribute(
            'aria-selected',
            'false'
        );

        uploadPanel?.classList.remove('hidden');
        scanPanel?.classList.add('hidden');

        stopCamera();
        clearCameraError();
    }


    /* ============================================================
       MODE SCAN
    ============================================================ */

    function activateScanMode() {

        modeUploadBtn?.classList.remove('active');
        modeScanBtn?.classList.add('active');

        modeUploadBtn?.setAttribute(
            'aria-selected',
            'false'
        );

        modeScanBtn?.setAttribute(
            'aria-selected',
            'true'
        );

        uploadPanel?.classList.add('hidden');
        scanPanel?.classList.remove('hidden');

        clearFileSelection();
        clearCameraError();
    }


    modeUploadBtn?.addEventListener(
        'click',
        activateUploadMode
    );

    modeScanBtn?.addEventListener(
        'click',
        activateScanMode
    );


    /* ============================================================
       FILE UPLOAD
    ============================================================ */

    fileInput.addEventListener(
        'change',
        async () => {

            clearCameraError();

            const file = fileInput.files?.[0];

            if (!file) {
                return;
            }


            const fileName = String(
                file.name || ''
            );

            const extension = fileName
                .split('.')
                .pop()
                ?.toLowerCase();


            const allowedExtensions = [
                'pdf',
                'jpg',
                'jpeg',
                'png'
            ];


            if (
                !extension ||
                !allowedExtensions.includes(extension)
            ) {

                clearFileSelection();

                alert(
                    'Format file tidak didukung.\n\n' +
                    'Gunakan PDF, JPG, JPEG, atau PNG.'
                );

                return;
            }


            if (file.size > MAX_FILE_SIZE) {

                clearFileSelection();

                alert(
                    'Ukuran file terlalu besar.\n\n' +
                    'Maksimal ukuran file adalah 10 MB.'
                );

                return;
            }


            if (
                extension === 'pdf' ||
                file.type === 'application/pdf'
            ) {

                capturedInput.value = '';

                clearScanResult();

                showSelectedFile(
                    file,
                    'PDF disimpan tanpa kompresi'
                );

                return;
            }


            try {

                const originalSize = file.size;

                const compressedFile =
                    await compressImageFile(file);

                let finalFile = compressedFile;


                if (
                    compressedFile.size >=
                    originalSize
                ) {
                    finalFile = file;
                }


                if (
                    finalFile.size >
                    MAX_FILE_SIZE
                ) {

                    clearFileSelection();

                    alert(
                        'Gambar masih terlalu besar setelah dikompres.\n\n' +
                        'Silakan pilih gambar dengan resolusi lebih rendah.'
                    );

                    return;
                }


                const dataTransfer =
                    new DataTransfer();

                dataTransfer.items.add(finalFile);

                fileInput.files =
                    dataTransfer.files;


                capturedInput.value = '';

                clearScanResult();


                const reduction =
                    getReductionPercent(
                        originalSize,
                        finalFile.size
                    );


                const note =
                    finalFile === file
                        ? `Tidak dikompres • ${formatFileSize(finalFile.size)}`
                        : `Dikompres ${reduction}% • ${formatFileSize(finalFile.size)}`;


                showSelectedFile(
                    finalFile,
                    note
                );

            } catch (error) {

                console.error(
                    'Compression upload gagal:',
                    error
                );

                clearFileSelection();

                alert(
                    error?.message ||
                    'Gagal memproses gambar.'
                );
            }
        }
    );


    function showSelectedFile(
        file,
        note = ''
    ) {

        if (!selectedFile) {
            return;
        }


        if (selectedFileName) {
            selectedFileName.textContent =
                file.name;
        }


        if (selectedFileSize) {

            selectedFileSize.textContent =
                `${formatFileSize(file.size)}${note ? ` • ${note}` : ''}`;
        }


        selectedFile.classList.remove(
            'hidden'
        );


        uploadBox?.classList.add(
            'border-emerald-400',
            'bg-emerald-50'
        );
    }


    function clearFileSelection() {

        if (fileInput) {
            fileInput.value = '';
        }


        selectedFile?.classList.add(
            'hidden'
        );


        if (selectedFileName) {
            selectedFileName.textContent = '';
        }


        if (selectedFileSize) {
            selectedFileSize.textContent = '';
        }


        uploadBox?.classList.remove(
            'border-emerald-400',
            'bg-emerald-50'
        );
    }


    clearFileBtn?.addEventListener(
        'click',
        clearFileSelection
    );


    /* ============================================================
       IMAGE COMPRESSION
    ============================================================ */

    function loadImageFromFile(file) {

        return new Promise(
            (resolve, reject) => {

                const objectUrl =
                    URL.createObjectURL(file);

                const image =
                    new Image();


                image.onload = () => {

                    URL.revokeObjectURL(
                        objectUrl
                    );

                    resolve(image);
                };


                image.onerror = () => {

                    URL.revokeObjectURL(
                        objectUrl
                    );

                    reject(
                        new Error(
                            'Gambar tidak dapat dibaca oleh browser.'
                        )
                    );
                };


                image.src = objectUrl;
            }
        );
    }


    async function compressImageFile(file) {

        const image =
            await loadImageFromFile(file);

        return compressImageElement(
            image,
            file.name
        );
    }


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
                Math.round(width * scale)
            ),

            height: Math.max(
                1,
                Math.round(height * scale)
            )
        };
    }


    function buildDimensionList(
        sourceWidth,
        sourceHeight
    ) {

        const maxDimensions = [
            2200,
            2000,
            1800,
            1600,
            1400,
            1200
        ];


        const list = [];


        for (
            const maxDimension
            of maxDimensions
        ) {

            const dimensions =
                calculateDimensions(
                    sourceWidth,
                    sourceHeight,
                    maxDimension
                );


            if (
                dimensions.width <
                    MIN_IMAGE_DIMENSION &&
                dimensions.height <
                    MIN_IMAGE_DIMENSION
            ) {
                continue;
            }


            list.push(
                dimensions
            );
        }


        list.unshift(
            calculateDimensions(
                sourceWidth,
                sourceHeight,
                MAX_IMAGE_DIMENSION
            )
        );


        return list.filter(
            (
                item,
                index,
                array
            ) =>
                index ===
                array.findIndex(
                    value =>
                        value.width ===
                            item.width &&
                        value.height ===
                            item.height
                )
        );
    }


    async function compressImageElement(
        image,
        originalName
    ) {

        const sourceWidth =
            image.naturalWidth ||
            image.width;

        const sourceHeight =
            image.naturalHeight ||
            image.height;


        if (
            sourceWidth <= 0 ||
            sourceHeight <= 0
        ) {

            throw new Error(
                'Dimensi gambar tidak valid.'
            );
        }


        const dimensionsList =
            buildDimensionList(
                sourceWidth,
                sourceHeight
            );


        let smallestResult = null;


        for (
            const dimensions
            of dimensionsList
        ) {

            for (
                const quality
                of JPEG_QUALITIES
            ) {

                const result =
                    await compressCanvasAtDimensions(
                        image,
                        dimensions.width,
                        dimensions.height,
                        originalName,
                        quality
                    );


                if (
                    !smallestResult ||
                    result.size <
                        smallestResult.size
                ) {

                    smallestResult =
                        result;
                }


                if (
                    result.size <=
                    TARGET_IMAGE_SIZE
                ) {

                    return result;
                }
            }
        }


        if (
            smallestResult &&
            smallestResult.size <=
                MAX_COMPRESSED_IMAGE_SIZE
        ) {

            return smallestResult;
        }


        throw new Error(
            'Gambar masih terlalu besar setelah dikompres. ' +
            'Silakan gunakan gambar dengan resolusi lebih rendah.'
        );
    }


    function compressCanvasAtDimensions(
        image,
        width,
        height,
        originalName,
        quality
    ) {

        return new Promise(
            (resolve, reject) => {

                const canvas =
                    document.createElement(
                        'canvas'
                    );


                canvas.width = width;
                canvas.height = height;


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
                    blob => {

                        if (!blob) {

                            reject(
                                new Error(
                                    'Browser gagal membuat file hasil kompresi.'
                                )
                            );

                            return;
                        }


                        resolve(
                            createCompressedFile(
                                blob,
                                originalName
                            )
                        );
                    },
                    'image/jpeg',
                    quality
                );
            }
        );
    }


    function createCompressedFile(
        blob,
        originalName
    ) {

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


        return new File(
            [
                blob
            ],
            `${baseName || 'lampiran'}_compressed.jpg`,
            {
                type: 'image/jpeg',
                lastModified: Date.now()
            }
        );
    }


    /* ============================================================
       CAMERA
    ============================================================ */

    startCameraBtn?.addEventListener(
        'click',
        startCamera
    );


    async function startCamera() {

        clearCameraError();


        if (
            !navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia
        ) {

            showCameraError(
                'Browser atau perangkat ini tidak mendukung akses kamera.'
            );

            return;
        }


        try {

            stopCameraTracksOnly();


            cameraStream =
                await navigator.mediaDevices
                    .getUserMedia({
                        video: {
                            facingMode: {
                                ideal: 'environment'
                            },

                            width: {
                                ideal: 1920
                            },

                            height: {
                                ideal: 1080
                            }
                        },

                        audio: false
                    });


            cameraVideo.srcObject =
                cameraStream;


            await cameraVideo.play();


            cameraPlaceholder?.classList.add(
                'hidden'
            );


            startCameraBtn.disabled =
                true;

            captureBtn.disabled =
                false;

            stopCameraBtn.disabled =
                false;

        } catch (error) {

            console.error(
                'Camera error:',
                error
            );


            showCameraError(
                'Kamera tidak dapat diakses. ' +
                'Pastikan izin kamera diberikan pada browser.'
            );
        }
    }


    captureBtn?.addEventListener(
        'click',
        async () => {

            clearCameraError();


            if (
                !cameraVideo.videoWidth ||
                !cameraVideo.videoHeight
            ) {

                showCameraError(
                    'Kamera belum siap. Silakan tunggu sebentar.'
                );

                return;
            }


            try {

                const width =
                    cameraVideo.videoWidth;

                const height =
                    cameraVideo.videoHeight;


                const dimensions =
                    calculateDimensions(
                        width,
                        height,
                        MAX_IMAGE_DIMENSION
                    );


                const canvas =
                    document.createElement(
                        'canvas'
                    );


                canvas.width =
                    dimensions.width;

                canvas.height =
                    dimensions.height;


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
                    canvas.width,
                    canvas.height
                );


                context.imageSmoothingEnabled =
                    true;

                context.imageSmoothingQuality =
                    'high';


                context.drawImage(
                    cameraVideo,
                    0,
                    0,
                    canvas.width,
                    canvas.height
                );


                const image =
                    await canvasToImage(
                        canvas
                    );


                const compressedFile =
                    await compressImageElement(
                        image,
                        'scan_dokumen.jpg'
                    );


                capturedInput.value =
                    await fileToDataUrl(
                        compressedFile
                    );


                if (previewObjectUrl) {

                    URL.revokeObjectURL(
                        previewObjectUrl
                    );
                }


                previewObjectUrl =
                    URL.createObjectURL(
                        compressedFile
                    );


                if (scanPreviewImage) {

                    scanPreviewImage.src =
                        previewObjectUrl;
                }


                scanResult?.classList.remove(
                    'hidden'
                );


                if (scanCompressionInfo) {

                    scanCompressionInfo.textContent =
                        `Hasil scan: ${formatFileSize(compressedFile.size)} • JPEG terkompresi`;
                }


                clearFileSelection();


                stopCameraTracksOnly();


                if (cameraVideo) {

                    cameraVideo.srcObject =
                        null;
                }


                captureBtn.disabled =
                    true;

                stopCameraBtn.disabled =
                    true;

                startCameraBtn.disabled =
                    false;


                cameraPlaceholder?.classList.remove(
                    'hidden'
                );

            } catch (error) {

                console.error(
                    'Gagal mengambil scan:',
                    error
                );


                showCameraError(
                    error?.message ||
                    'Gagal memproses hasil scan.'
                );
            }
        }
    );


    function canvasToImage(canvas) {

        return new Promise(
            (resolve, reject) => {

                const image =
                    new Image();


                image.onload = () => {
                    resolve(image);
                };


                image.onerror = () => {

                    reject(
                        new Error(
                            'Gagal membaca hasil scan.'
                        )
                    );
                };


                image.src =
                    canvas.toDataURL(
                        'image/jpeg',
                        0.95
                    );
            }
        );
    }


    /* ============================================================
       RETAKE
    ============================================================ */

    retakeBtn?.addEventListener(
        'click',
        async () => {

            clearScanResult();

            capturedInput.value = '';

            await startCamera();
        }
    );


    function clearScanResult() {

        capturedInput.value = '';

        scanResult?.classList.add(
            'hidden'
        );


        if (scanPreviewImage) {
            scanPreviewImage.src = '';
        }


        if (scanCompressionInfo) {
            scanCompressionInfo.textContent = '';
        }


        if (previewObjectUrl) {

            URL.revokeObjectURL(
                previewObjectUrl
            );

            previewObjectUrl = null;
        }
    }


    /* ============================================================
       DATA URL
    ============================================================ */

    function fileToDataUrl(file) {

        return new Promise(
            (resolve, reject) => {

                const reader =
                    new FileReader();


                reader.onload = () => {
                    resolve(
                        reader.result
                    );
                };


                reader.onerror = () => {

                    reject(
                        new Error(
                            'Gagal menyiapkan data scan.'
                        )
                    );
                };


                reader.readAsDataURL(
                    file
                );
            }
        );
    }


    /* ============================================================
       STOP CAMERA
    ============================================================ */

    stopCameraBtn?.addEventListener(
        'click',
        stopCamera
    );


    function stopCamera() {

        stopCameraTracksOnly();


        if (cameraVideo) {
            cameraVideo.srcObject = null;
        }


        cameraPlaceholder?.classList.remove(
            'hidden'
        );


        if (captureBtn) {
            captureBtn.disabled = true;
        }


        if (stopCameraBtn) {
            stopCameraBtn.disabled = true;
        }


        if (startCameraBtn) {
            startCameraBtn.disabled = false;
        }
    }


    function stopCameraTracksOnly() {

        if (!cameraStream) {
            return;
        }


        cameraStream
            .getTracks()
            .forEach(
                track => track.stop()
            );


        cameraStream = null;
    }


    /* ============================================================
       SUBMIT
    ============================================================ */

    form.addEventListener(
        'submit',
        event => {

            if (isSubmitting) {

                event.preventDefault();

                return;
            }


            if (
                capturedInput.value &&
                capturedInput.value.length >
                    7_000_000
            ) {

                event.preventDefault();


                alert(
                    'Hasil scan terlalu besar.\n\n' +
                    'Silakan scan ulang dengan resolusi lebih rendah.'
                );

                return;
            }


            const selected =
                fileInput.files?.[0];


            if (
                selected &&
                selected.size >
                    MAX_FILE_SIZE
            ) {

                event.preventDefault();


                alert(
                    'Ukuran file melebihi 10 MB.'
                );

                return;
            }


            stopCameraTracksOnly();


            isSubmitting = true;


            if (submitBtn) {

                submitBtn.disabled = true;

                submitBtn.classList.add(
                    'opacity-70',
                    'cursor-not-allowed'
                );
            }


            if (submitText) {

                submitText.textContent =
                    'Memproses & menyimpan...';
            }
        }
    );


    /* ============================================================
       CLEANUP
    ============================================================ */

    window.addEventListener(
        'beforeunload',
        () => {

            stopCameraTracksOnly();


            if (previewObjectUrl) {

                URL.revokeObjectURL(
                    previewObjectUrl
                );

                previewObjectUrl = null;
            }
        }
    );

});
</script>

@endsection