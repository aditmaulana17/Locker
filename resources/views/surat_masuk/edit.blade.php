@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    $tanggalSurat = old('tanggal_surat');
    $tanggalTerima = old('tanggal_terima');

    if ($tanggalSurat === null && $suratMasuk->tanggal_surat) {
        try {
            $tanggalSurat = \Illuminate\Support\Carbon::parse(
                $suratMasuk->tanggal_surat
            )->format('Y-m-d');
        } catch (\Throwable $e) {
            $tanggalSurat = substr(
                (string) $suratMasuk->tanggal_surat,
                0,
                10
            );
        }
    }

    if ($tanggalTerima === null && $suratMasuk->tanggal_terima) {
        try {
            $tanggalTerima = \Illuminate\Support\Carbon::parse(
                $suratMasuk->tanggal_terima
            )->format('Y-m-d');
        } catch (\Throwable $e) {
            $tanggalTerima = substr(
                (string) $suratMasuk->tanggal_terima,
                0,
                10
            );
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
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'didisposisikan' => 'Didisposisikan',
        'selesai' => 'Selesai',
        'diarsipkan' => 'Diarsipkan',
    ];
@endphp


<style>
    /* =========================================================
       GENERAL
    ========================================================= */

    .edit-page {
        width: 100%;
        max-width: 1050px;
        margin: 0 auto;
        padding: 0 16px 32px;
    }

    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
    }

    .page-title-wrap {
        min-width: 0;
    }

    .page-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .page-title-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 10px;
        background: #2563eb;
        color: white;
    }

    .page-title h1 {
        margin: 0;
        color: #0f172a;
        font-size: 1.35rem;
        line-height: 1.5rem;
        font-weight: 800;
        letter-spacing: -0.025em;
    }

    .page-subtitle {
        margin: 5px 0 0 48px;
        color: #64748b;
        font-size: .8rem;
        line-height: 1.25rem;
    }

    .back-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        flex-shrink: 0;
        padding: 9px 13px;
        border: 2px solid #cbd5e1;
        border-radius: 10px;
        background: white;
        color: #475569;
        font-size: .75rem;
        font-weight: 800;
        text-decoration: none;
        transition: .15s ease;
    }

    .back-button:hover {
        border-color: #94a3b8;
        background: #f8fafc;
        color: #1e293b;
    }


    /* =========================================================
       ERROR
    ========================================================= */

    .validation-error {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 16px;
        padding: 12px;
        border: 2px solid #fecdd3;
        border-radius: 12px;
        background: #fff1f2;
    }

    .validation-error-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        flex-shrink: 0;
        border-radius: 8px;
        background: #ffe4e6;
        color: #e11d48;
    }

    .validation-error-title {
        margin: 0;
        color: #be123c;
        font-size: .75rem;
        font-weight: 800;
    }

    .validation-error-list {
        margin: 3px 0 0;
        padding: 0;
        list-style: none;
        color: #e11d48;
        font-size: .7rem;
        line-height: 1.1rem;
    }


    /* =========================================================
       MAIN CARD
    ========================================================= */

    .form-card {
        overflow: hidden;
        border: 2px solid #cbd5e1;
        border-radius: 16px;
        background: white;
        box-shadow: 0 4px 14px rgba(15, 23, 42, .05);
    }

    .agenda-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 16px;
        border-bottom: 2px solid #bfdbfe;
        background: linear-gradient(
            90deg,
            #eff6ff 0%,
            #eef2ff 100%
        );
    }

    .agenda-info {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .agenda-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        border-radius: 8px;
        background: #2563eb;
        color: white;
    }

    .agenda-label {
        color: #1e3a8a;
        font-size: .75rem;
        font-weight: 700;
    }

    .agenda-number {
        display: inline-flex;
        align-items: center;
        padding: 4px 8px;
        border: 2px solid #bfdbfe;
        border-radius: 7px;
        background: white;
        color: #1d4ed8;
        font-family: monospace;
        font-size: .7rem;
        font-weight: 800;
    }

    .agenda-note {
        color: #2563eb;
        font-size: .65rem;
        font-weight: 600;
    }

    .form-body {
        padding: 18px;
    }


    /* =========================================================
       SECTION
    ========================================================= */

    .form-section + .form-section {
        margin-top: 28px;
    }

    .section-header {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-bottom: 14px;
    }

    .section-line {
        width: 4px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 999px;
    }

    .section-line-blue {
        background: #2563eb;
    }

    .section-line-indigo {
        background: #4f46e5;
    }

    .section-title {
        margin: 0;
        color: #1e293b;
        font-size: .9rem;
        line-height: 1.2rem;
        font-weight: 800;
    }

    .section-description {
        margin: 2px 0 0;
        color: #64748b;
        font-size: .7rem;
        line-height: 1rem;
    }


    /* =========================================================
       FORM INPUT
    ========================================================= */

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .form-group {
        min-width: 0;
    }

    .form-group-full {
        grid-column: 1 / -1;
    }

    .form-label {
        display: block;
        margin-bottom: 5px;
        color: #334155;
        font-size: .7rem;
        font-weight: 800;
    }

    .required {
        color: #e11d48;
    }

    .form-input,
    .form-select,
    .form-textarea {
        display: block;
        width: 100%;
        box-sizing: border-box;
        border: 2px solid #cbd5e1;
        border-radius: 9px;
        background: white;
        color: #1e293b;
        font-size: .8rem;
        outline: none;
        transition: .15s ease;
    }

    .form-input,
    .form-select {
        height: 41px;
        padding: 8px 11px;
    }

    .form-textarea {
        min-height: 82px;
        padding: 9px 11px;
        resize: vertical;
    }

    .form-input:hover,
    .form-select:hover,
    .form-textarea:hover {
        border-color: #94a3b8;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .1);
    }

    .input-error {
        border-color: #fb7185 !important;
        background: #fff1f2 !important;
    }

    .error-text {
        margin: 4px 0 0;
        color: #e11d48;
        font-size: .65rem;
        font-weight: 600;
    }


    /* =========================================================
       ARCHIVE GRID
    ========================================================= */

    .archive-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        align-items: stretch;
    }

    .archive-card {
        display: flex;
        flex-direction: column;
        width: 100%;
        min-width: 0;
        height: 100%;
        box-sizing: border-box;
        overflow: hidden;
        padding: 14px;
        border: 2px solid #cbd5e1;
        border-radius: 13px;
        background: white;
    }

    .archive-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 11px;
    }

    .archive-title {
        margin: 0;
        color: #334155;
        font-size: .8rem;
        font-weight: 800;
    }

    .archive-description {
        margin: 2px 0 0;
        color: #94a3b8;
        font-size: .65rem;
        line-height: .95rem;
    }

    .optional-badge {
        display: inline-flex;
        align-items: center;
        flex-shrink: 0;
        padding: 4px 7px;
        border: 2px solid #cbd5e1;
        border-radius: 999px;
        background: #f8fafc;
        color: #64748b;
        font-size: .6rem;
        font-weight: 800;
    }


    /* =========================================================
       DOCUMENT MODE
    ========================================================= */

    .mode-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-bottom: 10px;
    }

    .mode-button {
        display: flex;
        align-items: center;
        gap: 8px;
        width: 100%;
        min-width: 0;
        padding: 9px;
        border: 2px solid #cbd5e1;
        border-radius: 9px;
        background: white;
        color: #475569;
        text-align: left;
        cursor: pointer;
        transition: .15s ease;
    }

    .mode-button:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }

    .mode-button.active {
        border-color: #2563eb;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .mode-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
    }

    .mode-button.active .mode-icon {
        border-color: #bfdbfe;
        background: #dbeafe;
        color: #2563eb;
    }

    .mode-title {
        display: block;
        font-size: .68rem;
        line-height: .9rem;
        font-weight: 800;
    }

    .mode-subtitle {
        display: block;
        margin-top: 1px;
        color: #94a3b8;
        font-size: .58rem;
    }


    /* =========================================================
       CURRENT FILE
    ========================================================= */

    .current-file {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
        margin-bottom: 9px;
        padding: 8px;
        border: 2px solid #e2e8f0;
        border-radius: 9px;
        background: #f8fafc;
    }

    .file-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 31px;
        height: 31px;
        flex-shrink: 0;
        border-radius: 7px;
        background: #eff6ff;
        border: 2px solid #bfdbfe;
        color: #2563eb;
    }

    .file-icon-warning {
        background: #fffbeb;
        border-color: #fde68a;
        color: #d97706;
    }

    .file-info {
        flex: 1;
        min-width: 0;
    }

    .file-title {
        margin: 0;
        color: #475569;
        font-size: .65rem;
        font-weight: 800;
    }

    .file-name {
        margin: 1px 0 0;
        overflow: hidden;
        color: #64748b;
        font-size: .6rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .view-file {
        flex-shrink: 0;
        padding: 5px 8px;
        border: 2px solid #bfdbfe;
        border-radius: 7px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .6rem;
        font-weight: 800;
        text-decoration: none;
    }

    .view-file:hover {
        background: #dbeafe;
    }


    /* =========================================================
       UPLOAD
    ========================================================= */

    .upload-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 145px;
        box-sizing: border-box;
        padding: 16px;
        border: 2px dashed #94a3b8;
        border-radius: 11px;
        background: #f8fafc;
        cursor: pointer;
        text-align: center;
        transition: .15s ease;
    }

    .upload-box:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .upload-box.error {
        border-color: #fb7185;
        background: #fff1f2;
    }

    .upload-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        margin-bottom: 8px;
        border: 2px solid #bfdbfe;
        border-radius: 10px;
        background: #eff6ff;
        color: #2563eb;
    }

    .upload-label {
        color: #334155;
        font-size: .75rem;
        font-weight: 800;
    }

    .upload-help {
        margin-top: 3px;
        color: #64748b;
        font-size: .6rem;
    }

    .upload-size {
        margin-top: 2px;
        color: #94a3b8;
        font-size: .58rem;
    }

    .selected-file {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        padding: 8px;
        border: 2px solid #a7f3d0;
        border-radius: 9px;
        background: #ecfdf5;
    }

    .selected-file.hidden {
        display: none;
    }

    .selected-file-info {
        flex: 1;
        min-width: 0;
    }

    .selected-file-title {
        color: #047857;
        font-size: .62rem;
        font-weight: 800;
    }

    .selected-file-name {
        overflow: hidden;
        color: #059669;
        font-size: .6rem;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .clear-file {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 27px;
        height: 27px;
        flex-shrink: 0;
        border: 2px solid #a7f3d0;
        border-radius: 7px;
        background: white;
        color: #059669;
        cursor: pointer;
    }


    /* =========================================================
       SCANNER
    ========================================================= */

    .scan-panel {
        width: 100%;
    }

    .scan-wrapper {
        padding: 10px;
        border: 2px solid #cbd5e1;
        border-radius: 11px;
        background: #f8fafc;
    }

    .scan-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 9px;
    }

    .scan-title {
        margin: 0;
        color: #334155;
        font-size: .75rem;
        font-weight: 800;
    }

    .scan-description {
        margin: 2px 0 0;
        color: #64748b;
        font-size: .6rem;
    }

    .camera-badge {
        flex-shrink: 0;
        padding: 4px 7px;
        border: 2px solid #bfdbfe;
        border-radius: 999px;
        background: #eff6ff;
        color: #2563eb;
        font-size: .58rem;
        font-weight: 800;
    }

    .camera-container {
        position: relative;
        width: 100%;
        min-height: 260px;
        overflow: hidden;
        border: 2px solid #1e293b;
        border-radius: 10px;
        background: #0f172a;
    }

    #camera-video {
        display: none;
        width: 100%;
        height: 100%;
        min-height: 260px;
        object-fit: cover;
    }

    #camera-video.active {
        display: block;
    }

    .camera-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        text-align: center;
    }

    .camera-frame {
        position: absolute;
        top: 10%;
        right: 10%;
        bottom: 10%;
        left: 10%;
        border: 2px dashed rgba(255,255,255,.75);
        border-radius: 8px;
        pointer-events: none;
    }

    .scan-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 9px;
    }

    .scan-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 37px;
        padding: 7px 11px;
        border: 2px solid transparent;
        border-radius: 8px;
        font-size: .65rem;
        font-weight: 800;
        cursor: pointer;
        transition: .15s ease;
    }

    .scan-button:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .scan-start {
        background: #2563eb;
        border-color: #2563eb;
        color: white;
    }

    .scan-start:hover:not(:disabled) {
        background: #1d4ed8;
    }

    .scan-capture {
        background: #059669;
        border-color: #059669;
        color: white;
    }

    .scan-capture:hover:not(:disabled) {
        background: #047857;
    }

    .scan-stop {
        border-color: #cbd5e1;
        background: white;
        color: #475569;
    }

    .scan-stop:hover:not(:disabled) {
        background: #f1f5f9;
    }


    /* =========================================================
       SCAN RESULT
    ========================================================= */

    .scan-result {
        margin-top: 9px;
    }

    .scan-result.hidden {
        display: none;
    }

    .scan-result-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 8px;
        border: 2px solid #a7f3d0;
        border-bottom: 0;
        border-radius: 9px 9px 0 0;
        background: #ecfdf5;
    }

    .scan-result-title {
        color: #047857;
        font-size: .65rem;
        font-weight: 800;
    }

    .scan-result-text {
        margin-top: 1px;
        color: #059669;
        font-size: .58rem;
    }

    .retake-button {
        border: 0;
        background: transparent;
        color: #2563eb;
        font-size: .6rem;
        font-weight: 800;
        cursor: pointer;
    }

    .scan-preview {
        overflow: hidden;
        border: 2px solid #a7f3d0;
        border-radius: 0 0 9px 9px;
        background: white;
    }

    .scan-preview img {
        display: block;
        width: 100%;
        max-height: 360px;
        object-fit: contain;
    }


    /* =========================================================
       PHYSICAL ARCHIVE
    ========================================================= */

    .location-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
        padding: 18px 14px;
        border: 2px solid #e2e8f0;
        border-radius: 11px;
        background: #f8fafc;
        text-align: center;
    }

    .location-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        margin-bottom: 9px;
        border: 2px solid #cbd5e1;
        border-radius: 10px;
        background: white;
        color: #475569;
    }

    .location-title {
        color: #334155;
        font-size: .75rem;
        font-weight: 800;
    }

    .location-description {
        max-width: 320px;
        margin: 3px 0 0;
        color: #94a3b8;
        font-size: .6rem;
        line-height: .95rem;
    }

    .location-input-wrap {
        width: 100%;
        margin-top: 14px;
    }

    .location-example {
        width: 100%;
        box-sizing: border-box;
        margin-top: 9px;
        padding: 8px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        color: #64748b;
        font-size: .6rem;
        line-height: .95rem;
        text-align: left;
    }


    /* =========================================================
       FOOTER
    ========================================================= */

    .form-footer {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        padding: 12px 16px;
        border-top: 2px solid #cbd5e1;
        background: #f8fafc;
    }

    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 8px 13px;
        border: 2px solid transparent;
        border-radius: 9px;
        font-size: .7rem;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: .15s ease;
    }

    .action-cancel {
        border-color: #cbd5e1;
        background: white;
        color: #475569;
    }

    .action-cancel:hover {
        background: #f1f5f9;
    }

    .action-save {
        border-color: #2563eb;
        background: #2563eb;
        color: white;
    }

    .action-save:hover {
        background: #1d4ed8;
    }

    .action-save:disabled {
        opacity: .65;
        cursor: not-allowed;
    }


    /* =========================================================
       RESPONSIVE
    ========================================================= */

    @media (max-width: 768px) {

        .page-header {
            align-items: stretch;
            flex-direction: column;
        }

        .back-button {
            width: 100%;
        }

        .agenda-bar {
            align-items: flex-start;
            flex-direction: column;
        }

        .agenda-note {
            margin-left: 43px;
        }

        .form-grid,
        .archive-grid {
            grid-template-columns: 1fr;
        }

        .form-group-full {
            grid-column: auto;
        }

        .form-body {
            padding: 14px;
        }

        .form-footer {
            flex-direction: column-reverse;
        }

        .action-button {
            width: 100%;
        }
    }


    @media (max-width: 480px) {

        .edit-page {
            padding-right: 10px;
            padding-left: 10px;
        }

        .page-title h1 {
            font-size: 1.15rem;
        }

        .page-subtitle {
            margin-left: 0;
        }

        .mode-grid {
            grid-template-columns: 1fr;
        }

        .scan-actions {
            flex-direction: column;
        }

        .scan-button {
            width: 100%;
        }

        .camera-container,
        #camera-video {
            min-height: 230px;
        }
    }
</style>


<div class="edit-page">

    {{-- =====================================================
         HEADER
    ====================================================== --}}
    <div class="page-header">

        <div class="page-title-wrap">

            <div class="page-title">

                <div class="page-title-icon">
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.5-7.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 7.5-7.5z"
                        />
                    </svg>
                </div>

                <h1>
                    Edit Surat Masuk
                </h1>

            </div>

            <p class="page-subtitle">
                Perbarui informasi arsip surat masuk yang tersimpan di dalam sistem.
            </p>

        </div>


        <a
            href="{{ route('surat-masuk.index') }}"
            class="back-button"
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

            Kembali
        </a>

    </div>


    {{-- =====================================================
         VALIDATION ERROR
    ====================================================== --}}
    @if ($errors->any())

        <div class="validation-error">

            <div class="validation-error-icon">

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
                        d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z"
                    />
                </svg>

            </div>

            <div>

                <p class="validation-error-title">
                    Data belum dapat diperbarui.
                </p>

                <ul class="validation-error-list">

                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach

                </ul>

            </div>

        </div>

    @endif


    {{-- =====================================================
         FORM
    ====================================================== --}}
    <form
        id="form-surat"
        action="{{ route('surat-masuk.update', $suratMasuk->id) }}"
        method="POST"
        enctype="multipart/form-data"
    >

        @csrf
        @method('PUT')

        <input
            type="hidden"
            name="nomor_agenda"
            value="{{ old('nomor_agenda', $suratMasuk->nomor_agenda) }}"
        >

        <div class="form-card">


            {{-- =================================================
                 AGENDA
            ================================================== --}}
            <div class="agenda-bar">

                <div class="agenda-info">

                    <div class="agenda-icon">
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
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 013-3h0a3 3 0 013 3"
                            />
                        </svg>
                    </div>

                    <span class="agenda-label">
                        Nomor Agenda Sistem
                    </span>

                    <span class="agenda-number">
                        {{ $suratMasuk->nomor_agenda }}
                    </span>

                </div>

                <span class="agenda-note">
                    Nomor agenda tidak diubah
                </span>

            </div>


            <div class="form-body">


                {{-- =================================================
                     INFORMASI SURAT
                ================================================== --}}
                <section class="form-section">

                    <div class="section-header">

                        <div class="section-line section-line-blue"></div>

                        <div>
                            <h2 class="section-title">
                                Informasi Utama Surat
                            </h2>

                            <p class="section-description">
                                Perbarui identitas dan informasi utama surat masuk.
                            </p>
                        </div>

                    </div>


                    <div class="form-grid">


                        {{-- Nomor Surat --}}
                        <div class="form-group">

                            <label
                                for="nomor_surat"
                                class="form-label"
                            >
                                Nomor Surat
                                <span class="required">*</span>
                            </label>

                            <input
                                id="nomor_surat"
                                name="nomor_surat"
                                type="text"
                                required
                                autocomplete="off"
                                value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}"
                                placeholder="Contoh: 005/B/I/2026"
                                class="form-input @error('nomor_surat') input-error @enderror"
                            >

                            @error('nomor_surat')
                                <p class="error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Pengirim --}}
                        <div class="form-group">

                            <label
                                for="pengirim"
                                class="form-label"
                            >
                                Instansi Pengirim
                                <span class="required">*</span>
                            </label>

                            <input
                                id="pengirim"
                                name="pengirim"
                                type="text"
                                required
                                autocomplete="organization"
                                value="{{ old('pengirim', $suratMasuk->pengirim) }}"
                                placeholder="Masukkan nama instansi pengirim"
                                class="form-input @error('pengirim') input-error @enderror"
                            >

                            @error('pengirim')
                                <p class="error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Tanggal Surat --}}
                        <div class="form-group">

                            <label
                                for="tanggal_surat"
                                class="form-label"
                            >
                                Tanggal Surat
                                <span class="required">*</span>
                            </label>

                            <input
                                id="tanggal_surat"
                                name="tanggal_surat"
                                type="date"
                                required
                                value="{{ $tanggalSurat }}"
                                class="form-input @error('tanggal_surat') input-error @enderror"
                            >

                            @error('tanggal_surat')
                                <p class="error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Tanggal Terima --}}
                        <div class="form-group">

                            <label
                                for="tanggal_terima"
                                class="form-label"
                            >
                                Tanggal Diterima
                                <span class="required">*</span>
                            </label>

                            <input
                                id="tanggal_terima"
                                name="tanggal_terima"
                                type="date"
                                required
                                value="{{ $tanggalTerima }}"
                                class="form-input @error('tanggal_terima') input-error @enderror"
                            >

                            @error('tanggal_terima')
                                <p class="error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Kategori --}}
                        <div class="form-group">

                            <label
                                for="kategori_surat_id"
                                class="form-label"
                            >
                                Kategori Surat
                                <span class="required">*</span>
                            </label>

                            <select
                                id="kategori_surat_id"
                                name="kategori_surat_id"
                                required
                                class="form-select @error('kategori_surat_id') input-error @enderror"
                            >

                                <option
                                    value=""
                                    disabled
                                    {{ $selectedKategori ? '' : 'selected' }}
                                >
                                    Pilih kategori surat
                                </option>

                                @foreach ($kategoris as $kategori)

                                    <option
                                        value="{{ $kategori->id }}"
                                        {{ (string) $selectedKategori === (string) $kategori->id ? 'selected' : '' }}
                                    >
                                        {{ $kategori->nama_kategori }}

                                        @if (!empty($kategori->sifat))
                                            ({{ ucfirst($kategori->sifat) }})
                                        @endif
                                    </option>

                                @endforeach

                            </select>

                            @error('kategori_surat_id')
                                <p class="error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Status --}}
                        <div class="form-group">

                            <label
                                for="status"
                                class="form-label"
                            >
                                Status Surat
                                <span class="required">*</span>
                            </label>

                            <select
                                id="status"
                                name="status"
                                required
                                class="form-select @error('status') input-error @enderror"
                            >

                                @foreach ($statusOptions as $value => $label)

                                    <option
                                        value="{{ $value }}"
                                        {{ $currentStatus === $value ? 'selected' : '' }}
                                    >
                                        {{ $label }}
                                    </option>

                                @endforeach

                            </select>

                            @error('status')
                                <p class="error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Perihal --}}
                        <div class="form-group form-group-full">

                            <label
                                for="perihal"
                                class="form-label"
                            >
                                Perihal / Isi Ringkas
                                <span class="required">*</span>
                            </label>

                            <textarea
                                id="perihal"
                                name="perihal"
                                rows="3"
                                required
                                placeholder="Tuliskan perihal atau isi ringkas surat"
                                class="form-textarea @error('perihal') input-error @enderror"
                            >{{ old('perihal', $suratMasuk->perihal) }}</textarea>

                            @error('perihal')
                                <p class="error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     LAMPIRAN
                ================================================== --}}
                <section class="form-section">

                    <div class="section-header">

                        <div class="section-line section-line-indigo"></div>

                        <div>
                            <h2 class="section-title">
                                Lampiran Dokumen & Arsip Fisik
                            </h2>

                            <p class="section-description">
                                Ganti dokumen melalui upload atau scan, serta perbarui lokasi arsip fisik.
                            </p>
                        </div>

                    </div>


                    <div class="archive-grid">


                        {{-- =================================================
                             DOKUMEN DIGITAL
                        ================================================== --}}
                        <div class="archive-card">

                            <div class="archive-header">

                                <div>
                                    <h3 class="archive-title">
                                        Berkas Digital
                                    </h3>

                                    <p class="archive-description">
                                        Upload file baru atau scan menggunakan kamera.
                                    </p>
                                </div>

                                <span class="optional-badge">
                                    Opsional
                                </span>

                            </div>


                            {{-- MODE --}}
                            <div class="mode-grid">

                                <button
                                    type="button"
                                    id="mode-upload-btn"
                                    class="mode-button active"
                                >

                                    <span class="mode-icon">

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
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                            />
                                        </svg>

                                    </span>

                                    <span>
                                        <span class="mode-title">
                                            Upload File
                                        </span>

                                        <span class="mode-subtitle">
                                            Pilih dari perangkat
                                        </span>
                                    </span>

                                </button>


                                <button
                                    type="button"
                                    id="mode-scan-btn"
                                    class="mode-button"
                                >

                                    <span class="mode-icon">

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
                                                d="M4 7V5a1 1 0 011-1h2M17 4h2a1 1 0 011 1v2M20 17v2a1 1 0 01-1 1h-2M7 20H5a1 1 0 01-1-1v-2M7 12h10M7 9h10M7 15h6"
                                            />
                                        </svg>

                                    </span>

                                    <span>
                                        <span class="mode-title">
                                            Scan Dokumen
                                        </span>

                                        <span class="mode-subtitle">
                                            Gunakan kamera
                                        </span>
                                    </span>

                                </button>

                            </div>


                            {{-- FILE LAMA --}}
                            @if ($suratMasuk->lampiran_file)

                                <div class="current-file">

                                    <div class="file-icon">

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
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414A1 1 0 0118 8.414V19a2 2 0 01-2 2z"
                                            />
                                        </svg>

                                    </div>

                                    <div class="file-info">

                                        <p class="file-title">
                                            Dokumen saat ini
                                        </p>

                                        <p class="file-name">
                                            {{ basename($suratMasuk->lampiran_file) }}
                                        </p>

                                    </div>

                                    <a
                                        href="{{ route('surat-masuk.preview-lampiran', $suratMasuk->id) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="view-file"
                                    >
                                        Lihat
                                    </a>

                                </div>

                            @else

                                <div class="current-file">

                                    <div class="file-icon file-icon-warning">

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
                                                d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z"
                                            />
                                        </svg>

                                    </div>

                                    <div class="file-info">

                                        <p class="file-title">
                                            Belum ada dokumen
                                        </p>

                                        <p class="file-name">
                                            Upload atau scan dokumen baru.
                                        </p>

                                    </div>

                                </div>

                            @endif


                            {{-- =================================================
                                 UPLOAD PANEL
                            ================================================== --}}
                            <div id="upload-panel">

                                <label
                                    for="lampiran_file"
                                    id="upload-box"
                                    class="upload-box @error('lampiran_file') error @enderror"
                                >

                                    <span class="upload-icon">

                                        <svg
                                            class="w-5 h-5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                            />
                                        </svg>

                                    </span>

                                    <span
                                        id="file-label-text"
                                        class="upload-label"
                                    >
                                        Pilih file baru
                                    </span>

                                    <span class="upload-help">
                                        PDF, JPG, JPEG, PNG
                                    </span>

                                    <span class="upload-size">
                                        Maksimal 15 MB
                                    </span>

                                    <input
                                        type="file"
                                        id="lampiran_file"
                                        name="lampiran_file"
                                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                        class="sr-only"
                                    >

                                </label>


                                {{-- FILE BARU --}}
                                <div
                                    id="selected-file"
                                    class="selected-file hidden"
                                >

                                    <div class="file-icon">

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

                                    <div class="selected-file-info">

                                        <div class="selected-file-title">
                                            File baru dipilih
                                        </div>

                                        <div
                                            id="selected-file-name"
                                            class="selected-file-name"
                                        ></div>

                                    </div>

                                    <button
                                        type="button"
                                        id="clear-file-btn"
                                        class="clear-file"
                                        title="Hapus file"
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
                                                d="M6 18L18 6M6 6l12 12"
                                            />
                                        </svg>
                                    </button>

                                </div>

                            </div>


                            {{-- =================================================
                                 SCAN PANEL
                            ================================================== --}}
                            <div
                                id="scan-panel"
                                class="scan-panel"
                                style="display:none;"
                            >

                                <div class="scan-wrapper">

                                    <div class="scan-header">

                                        <div>
                                            <h4 class="scan-title">
                                                Scan Dokumen
                                            </h4>

                                            <p class="scan-description">
                                                Arahkan kamera ke dokumen lalu ambil gambar.
                                            </p>
                                        </div>

                                        <span class="camera-badge">
                                            Kamera
                                        </span>

                                    </div>


                                    <div class="camera-container">

                                        <video
                                            id="camera-video"
                                            autoplay
                                            playsinline
                                            muted
                                        ></video>


                                        <div
                                            id="camera-placeholder"
                                            class="camera-placeholder"
                                        >

                                            <svg
                                                class="w-10 h-10 text-slate-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M3 7h4l2-3h6l2 3h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z"
                                                />

                                                <circle
                                                    cx="12"
                                                    cy="13"
                                                    r="3"
                                                />
                                            </svg>

                                            <p class="mt-2 text-sm font-semibold text-slate-400">
                                                Kamera belum aktif
                                            </p>

                                            <p class="text-xs text-slate-500">
                                                Klik "Aktifkan Kamera".
                                            </p>

                                        </div>

                                        <div class="camera-frame"></div>

                                    </div>


                                    <div class="scan-actions">

                                        <button
                                            type="button"
                                            id="start-camera-btn"
                                            class="scan-button scan-start"
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
                                                    d="M3 7h4l2-3h6l2 3h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z"
                                                />
                                            </svg>

                                            Aktifkan Kamera

                                        </button>


                                        <button
                                            type="button"
                                            id="capture-btn"
                                            class="scan-button scan-capture"
                                            disabled
                                        >

                                            <svg
                                                class="w-4 h-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="9"
                                                />

                                                <circle
                                                    cx="12"
                                                    cy="12"
                                                    r="3"
                                                />
                                            </svg>

                                            Ambil Gambar

                                        </button>


                                        <button
                                            type="button"
                                            id="stop-camera-btn"
                                            class="scan-button scan-stop"
                                            disabled
                                        >
                                            Matikan Kamera
                                        </button>

                                    </div>


                                    {{-- HASIL SCAN --}}
                                    <div
                                        id="scan-result"
                                        class="scan-result hidden"
                                    >

                                        <div class="scan-result-header">

                                            <div>
                                                <div class="scan-result-title">
                                                    Dokumen hasil scan
                                                </div>

                                                <div class="scan-result-text">
                                                    Hasil scan akan menjadi lampiran baru.
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                id="retake-btn"
                                                class="retake-button"
                                            >
                                                Scan Ulang
                                            </button>

                                        </div>

                                        <div class="scan-preview">

                                            <img
                                                id="scan-preview-image"
                                                src=""
                                                alt="Hasil scan dokumen"
                                            >

                                        </div>

                                    </div>


                                    {{-- DATA HASIL SCAN --}}
                                    <input
                                        type="hidden"
                                        name="captured_image"
                                        id="captured_image"
                                        value="{{ old('captured_image') }}"
                                    >

                                </div>

                            </div>


                            <div class="mt-2 px-3 py-2 bg-slate-50 border-2 border-slate-200 rounded-lg">

                                <p class="text-xs leading-relaxed text-slate-500">
                                    Jika tidak memilih file baru dan tidak melakukan scan,
                                    dokumen lama akan tetap dipertahankan.
                                </p>

                            </div>


                            @error('lampiran_file')
                                <p class="error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                            @error('captured_image')
                                <p class="error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- =================================================
                             ARSIP FISIK
                        ================================================== --}}
                        <div class="archive-card">

                            <div class="archive-header">

                                <div>
                                    <h3 class="archive-title">
                                        Lokasi Arsip Fisik
                                    </h3>

                                    <p class="archive-description">
                                        Perbarui lokasi penyimpanan arsip fisik.
                                    </p>
                                </div>

                                <span class="optional-badge">
                                    Opsional
                                </span>

                            </div>


                            <div class="location-box">

                                <div class="location-icon">

                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                        />
                                    </svg>

                                </div>

                                <div class="location-title">
                                    Lokasi Penyimpanan
                                </div>

                                <p class="location-description">
                                    Masukkan posisi rak, lemari, box, atau map tempat arsip disimpan.
                                </p>


                                <div class="location-input-wrap">

                                    <label
                                        for="lokasi_arsip_fisik"
                                        class="form-label"
                                    >
                                        Detail Posisi Lemari / Box
                                    </label>

                                    <input
                                        type="text"
                                        id="lokasi_arsip_fisik"
                                        name="lokasi_arsip_fisik"
                                        value="{{ old('lokasi_arsip_fisik', $suratMasuk->lokasi_arsip_fisik) }}"
                                        placeholder="Contoh: Rak A-3 Box 12"
                                        class="form-input @error('lokasi_arsip_fisik') input-error @enderror"
                                    >

                                    @error('lokasi_arsip_fisik')
                                        <p class="error-text">
                                            {{ $message }}
                                        </p>
                                    @enderror

                                </div>


                                <div class="location-example">

                                    Contoh:

                                    <strong>
                                        Rak A-3 Box 12
                                    </strong>

                                    atau

                                    <strong>
                                        Lemari B-2 Map 07
                                    </strong>

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

                <a
                    href="{{ route('surat-masuk.index') }}"
                    class="action-button action-cancel"
                >
                    Batal
                </a>


                <button
                    type="submit"
                    id="submit-btn"
                    class="action-button action-save"
                >

                    <svg
                        class="w-4 h-4 mr-2"
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

                    <span id="submit-text">
                        Perbarui Surat Masuk
                    </span>

                </button>

            </div>

        </div>

    </form>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | ELEMENT
    |--------------------------------------------------------------------------
    */

    const form = document.getElementById('form-surat');

    const fileInput = document.getElementById('lampiran_file');
    const uploadBox = document.getElementById('upload-box');
    const fileLabel = document.getElementById('file-label-text');

    const selectedFile = document.getElementById('selected-file');
    const selectedFileName = document.getElementById('selected-file-name');
    const clearFileButton = document.getElementById('clear-file-btn');

    const uploadModeButton = document.getElementById('mode-upload-btn');
    const scanModeButton = document.getElementById('mode-scan-btn');

    const uploadPanel = document.getElementById('upload-panel');
    const scanPanel = document.getElementById('scan-panel');

    const video = document.getElementById('camera-video');
    const cameraPlaceholder = document.getElementById('camera-placeholder');

    const startCameraButton = document.getElementById('start-camera-btn');
    const captureButton = document.getElementById('capture-btn');
    const stopCameraButton = document.getElementById('stop-camera-btn');

    const scanResult = document.getElementById('scan-result');
    const scanPreviewImage = document.getElementById('scan-preview-image');
    const retakeButton = document.getElementById('retake-btn');

    const capturedImageInput =
        document.getElementById('captured_image');

    const submitButton =
        document.getElementById('submit-btn');

    const submitText =
        document.getElementById('submit-text');


    /*
    |--------------------------------------------------------------------------
    | CONFIG
    |--------------------------------------------------------------------------
    */

    const MAX_FILE_SIZE = 15 * 1024 * 1024;

    let cameraStream = null;
    let submitting = false;


    /*
    |--------------------------------------------------------------------------
    | MODE
    |--------------------------------------------------------------------------
    */

    function setMode(mode) {

        if (mode === 'upload') {

            uploadPanel.style.display = 'block';
            scanPanel.style.display = 'none';

            uploadModeButton.classList.add('active');
            scanModeButton.classList.remove('active');

            stopCamera();

            return;
        }


        uploadPanel.style.display = 'none';
        scanPanel.style.display = 'block';

        uploadModeButton.classList.remove('active');
        scanModeButton.classList.add('active');

        clearUploadFile();
    }


    uploadModeButton.addEventListener('click', function () {
        setMode('upload');
    });


    scanModeButton.addEventListener('click', function () {
        setMode('scan');
    });


    /*
    |--------------------------------------------------------------------------
    | FILE DISPLAY
    |--------------------------------------------------------------------------
    */

    function resetFileDisplay() {

        fileLabel.textContent =
            'Pilih file baru';

        fileLabel.style.color =
            '';

        selectedFile.classList.add('hidden');
        selectedFileName.textContent = '';

        uploadBox.classList.remove('error');
    }


    function clearUploadFile() {

        fileInput.value = '';

        resetFileDisplay();
    }


    clearFileButton.addEventListener('click', function (event) {

        event.preventDefault();
        event.stopPropagation();

        clearUploadFile();

    });


    /*
    |--------------------------------------------------------------------------
    | FILE INPUT
    |--------------------------------------------------------------------------
    */

    fileInput.addEventListener('change', function () {

        if (!this.files || !this.files.length) {

            resetFileDisplay();

            return;
        }


        const file = this.files[0];


        if (file.size > MAX_FILE_SIZE) {

            this.value = '';

            fileLabel.textContent =
                'Ukuran file melebihi 15 MB';

            fileLabel.style.color =
                '#e11d48';

            selectedFile.classList.add('hidden');

            uploadBox.classList.add('error');

            return;
        }


        /*
         * Pastikan mode upload aktif.
         */
        setMode('upload');


        fileLabel.textContent =
            'File baru dipilih';

        fileLabel.style.color =
            '#2563eb';

        selectedFileName.textContent =
            file.name;

        selectedFile.classList.remove('hidden');

        uploadBox.classList.remove('error');

    });


    /*
    |--------------------------------------------------------------------------
    | CAMERA
    |--------------------------------------------------------------------------
    */

    async function startCamera() {

        if (
            !navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia
        ) {

            alert(
                'Browser tidak mendukung akses kamera.'
            );

            return;
        }


        try {

            stopCamera();


            cameraStream =
                await navigator.mediaDevices.getUserMedia({
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


            video.srcObject =
                cameraStream;

            video.classList.add('active');

            cameraPlaceholder.style.display =
                'none';

            startCameraButton.disabled =
                true;

            captureButton.disabled =
                false;

            stopCameraButton.disabled =
                false;


        } catch (error) {

            console.error(
                'Camera error:',
                error
            );

            alert(
                'Kamera tidak dapat digunakan. Pastikan izin kamera sudah diberikan pada browser.'
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | STOP CAMERA
    |--------------------------------------------------------------------------
    */

    function stopCamera() {

        if (cameraStream) {

            cameraStream
                .getTracks()
                .forEach(function (track) {
                    track.stop();
                });

            cameraStream = null;
        }


        video.srcObject = null;

        video.classList.remove('active');

        cameraPlaceholder.style.display =
            'flex';

        startCameraButton.disabled =
            false;

        captureButton.disabled =
            true;

        stopCameraButton.disabled =
            true;
    }


    startCameraButton.addEventListener(
        'click',
        startCamera
    );


    stopCameraButton.addEventListener(
        'click',
        stopCamera
    );


    /*
    |--------------------------------------------------------------------------
    | CAPTURE
    |--------------------------------------------------------------------------
    */

    captureButton.addEventListener('click', function () {

        if (
            !video.videoWidth ||
            !video.videoHeight
        ) {

            alert(
                'Kamera belum siap. Tunggu sampai kamera aktif.'
            );

            return;
        }


        const canvas =
            document.createElement('canvas');

        canvas.width =
            video.videoWidth;

        canvas.height =
            video.videoHeight;


        const context =
            canvas.getContext('2d');


        if (!context) {

            alert(
                'Browser tidak dapat memproses hasil scan.'
            );

            return;
        }


        context.drawImage(
            video,
            0,
            0,
            canvas.width,
            canvas.height
        );


        const imageData =
            canvas.toDataURL(
                'image/jpeg',
                0.85
            );


        /*
         * Simpan hasil scan.
         */
        capturedImageInput.value =
            imageData;


        /*
         * Tampilkan preview.
         */
        scanPreviewImage.src =
            imageData;

        scanResult.classList.remove(
            'hidden'
        );


        /*
         * Hapus upload jika sebelumnya ada.
         */
        clearUploadFile();


        /*
         * Matikan kamera setelah capture.
         */
        stopCamera();

    });


    /*
    |--------------------------------------------------------------------------
    | RETAKE
    |--------------------------------------------------------------------------
    */

    retakeButton.addEventListener('click', function () {

        capturedImageInput.value = '';

        scanPreviewImage.src = '';

        scanResult.classList.add('hidden');

        startCamera();

    });


    /*
    |--------------------------------------------------------------------------
    | SUBMIT
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', function (event) {

        if (submitting) {

            event.preventDefault();

            return;
        }


        /*
         * Validasi ukuran upload.
         */
        if (
            fileInput.files &&
            fileInput.files.length
        ) {

            const file =
                fileInput.files[0];


            if (file.size > MAX_FILE_SIZE) {

                event.preventDefault();

                alert(
                    'Ukuran file maksimal 15 MB.'
                );

                return;
            }
        }


        /*
         * Upload dan scan tidak boleh
         * dikirim bersamaan.
         */
        if (
            fileInput.files &&
            fileInput.files.length &&
            capturedImageInput.value
        ) {

            event.preventDefault();

            alert(
                'Pilih salah satu: Upload File atau Scan Dokumen.'
            );

            return;
        }


        submitting = true;

        submitButton.disabled =
            true;

        submitText.textContent =
            'Menyimpan...';


        stopCamera();

    });


    /*
    |--------------------------------------------------------------------------
    | CLEANUP
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'beforeunload',
        stopCamera
    );

});
</script>

@endsection