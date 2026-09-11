@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')

@php
    use Illuminate\Support\Carbon;

    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    $tanggalSurat = old('tanggal_surat');

    if (
        $tanggalSurat === null &&
        $suratMasuk->tanggal_surat
    ) {
        try {
            $tanggalSurat = Carbon::parse(
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

    $tanggalTerima = old('tanggal_terima');

    if (
        $tanggalTerima === null &&
        $suratMasuk->tanggal_terima
    ) {
        try {
            $tanggalTerima = Carbon::parse(
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
    .edit-page {
        width: 100%;
        max-width: 1040px;
        margin: 0 auto;
        padding: 10px 16px 28px;
    }

    .edit-page *,
    .edit-page *::before,
    .edit-page *::after {
        box-sizing: border-box;
    }

    .edit-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 14px;
    }

    .edit-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .edit-title-icon {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #c7d2fe;
        border-radius: 9px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .edit-title {
        margin: 0;
        font-size: 19px;
        line-height: 1.2;
        font-weight: 750;
        color: #1e293b;
    }

    .edit-subtitle {
        margin: 3px 0 0;
        font-size: 11px;
        line-height: 1.45;
        color: #64748b;
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 36px;
        padding: 0 12px;
        border: 2px solid #94a3b8;
        border-radius: 8px;
        background: #fff;
        color: #475569;
        text-decoration: none;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        transition: .15s ease;
    }

    .btn-back:hover {
        background: #f8fafc;
        border-color: #64748b;
    }

    .error-box {
        margin-bottom: 12px;
        padding: 10px 12px;
        border: 2px solid #fca5a5;
        border-radius: 9px;
        background: #fff1f2;
    }

    .error-box-title {
        margin: 0 0 4px;
        font-size: 11px;
        font-weight: 750;
        color: #991b1b;
    }

    .error-box-list {
        margin: 0;
        padding-left: 16px;
        font-size: 10px;
        line-height: 1.5;
        color: #b91c1c;
    }

    .main-card {
        overflow: hidden;
        border: 2px solid #64748b;
        border-radius: 12px;
        background: #fff;
        box-shadow:
            0 3px 8px rgba(15, 23, 42, .06),
            0 12px 24px rgba(15, 23, 42, .04);
    }

    .agenda-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 10px 14px;
        border-bottom: 2px solid #64748b;
        background: linear-gradient(
            to right,
            #eff6ff,
            #eef2ff
        );
    }

    .agenda-left {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .agenda-icon {
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid #bfdbfe;
        border-radius: 8px;
        background: #fff;
        color: #2563eb;
    }

    .agenda-label {
        margin: 0;
        font-size: 8px;
        line-height: 1.25;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #2563eb;
    }

    .agenda-number {
        margin: 2px 0 0;
        font-size: 13px;
        line-height: 1.3;
        font-weight: 800;
        color: #1e3a8a;
    }

    .agenda-badge {
        padding: 4px 8px;
        border: 1px solid #bfdbfe;
        border-radius: 999px;
        background: #fff;
        color: #475569;
        font-size: 8px;
        font-weight: 700;
        white-space: nowrap;
    }

    .body {
        padding: 16px;
    }

    .section + .section {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 2px solid #94a3b8;
    }

    .section-head {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 10px;
    }

    .section-marker {
        width: 4px;
        height: 25px;
        flex: 0 0 4px;
        border-radius: 999px;
        background: #4f46e5;
    }

    .section-title {
        margin: 0;
        font-size: 13px;
        line-height: 1.3;
        font-weight: 800;
        color: #1e293b;
    }

    .section-description {
        margin: 2px 0 0;
        font-size: 9px;
        line-height: 1.45;
        color: #64748b;
    }

    .field-table {
        overflow: hidden;
        border: 2px solid #64748b;
        border-radius: 8px;
        background: #fff;
    }

    .field-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .field {
        min-width: 0;
        padding: 10px;
        border-right: 2px solid #94a3b8;
        border-bottom: 2px solid #94a3b8;
    }

    .field:nth-child(2n) {
        border-right: 0;
    }

    .field:nth-last-child(-n + 2) {
        border-bottom: 0;
    }

    .field-full {
        grid-column: 1 / -1;
        border-right: 0;
    }

    .field-grid .field-full:last-child {
        border-bottom: 0;
    }

    .field-label {
        display: block;
        margin-bottom: 5px;
        font-size: 10px;
        line-height: 1.3;
        font-weight: 800;
        color: #334155;
    }

    .required {
        color: #dc2626;
    }

    .control {
        width: 100%;
        border: 2px solid #94a3b8;
        border-radius: 7px;
        background: #fff;
        color: #1e293b;
        outline: none;
        font-size: 11px;
        transition: .15s ease;
    }

    input.control,
    select.control {
        height: 36px;
        padding: 0 9px;
    }

    textarea.control {
        min-height: 70px;
        padding: 8px 9px;
        line-height: 1.45;
        resize: vertical;
    }

    .control::placeholder {
        color: #94a3b8;
    }

    .control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, .08);
    }

    .field-error {
        margin: 4px 0 0;
        font-size: 9px;
        line-height: 1.4;
        font-weight: 700;
        color: #dc2626;
    }

    .attachment-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 12px;
        align-items: stretch;
    }

    .attachment-card {
        display: flex;
        flex-direction: column;
        min-width: 0;
        border: 2px solid #64748b;
        border-radius: 9px;
        background: #fff;
        overflow: hidden;
    }

    .attachment-header {
        min-height: 50px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 9px 10px;
        border-bottom: 2px solid #94a3b8;
        background: #f8fafc;
    }

    .attachment-title {
        margin: 0;
        font-size: 11px;
        line-height: 1.3;
        font-weight: 800;
        color: #1e293b;
    }

    .attachment-desc {
        margin: 2px 0 0;
        font-size: 8.5px;
        line-height: 1.35;
        color: #64748b;
    }

    .attachment-badge {
        padding: 4px 7px;
        border: 1px solid #cbd5e1;
        border-radius: 999px;
        background: #fff;
        color: #64748b;
        font-size: 7.5px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .attachment-body {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 9px;
        padding: 10px;
    }

    .info {
        display: flex;
        align-items: flex-start;
        gap: 7px;
        padding: 8px;
        border: 1px solid #bfdbfe;
        border-radius: 7px;
        background: #eff6ff;
    }

    .info p {
        margin: 0;
        font-size: 8.5px;
        line-height: 1.45;
        color: #1d4ed8;
    }

    .mode-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 6px;
    }

    .mode-button {
        min-height: 43px;
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 7px;
        border: 2px solid #94a3b8;
        border-radius: 7px;
        background: #fff;
        color: #475569;
        cursor: pointer;
        transition: .15s ease;
    }

    .mode-button:hover {
        border-color: #6366f1;
        background: #eef2ff;
    }

    .mode-button.active {
        border-color: #4f46e5;
        background: #eef2ff;
        color: #3730a3;
    }

    .mode-icon {
        width: 27px;
        height: 27px;
        flex: 0 0 27px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #f1f5f9;
        color: #64748b;
    }

    .mode-button.active .mode-icon {
        background: #e0e7ff;
        color: #4f46e5;
    }

    .mode-name {
        display: block;
        font-size: 9px;
        font-weight: 800;
        color: #334155;
    }

    .mode-desc {
        display: block;
        margin-top: 1px;
        font-size: 7.5px;
        color: #64748b;
    }

    .current-file {
        display: flex;
        align-items: center;
        gap: 7px;
        min-height: 43px;
        padding: 7px;
        border: 2px solid #cbd5e1;
        border-radius: 7px;
        background: #f8fafc;
    }

    .current-file-icon {
        width: 27px;
        height: 27px;
        flex: 0 0 27px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #e2e8f0;
        color: #64748b;
    }

    .current-file-content {
        min-width: 0;
        flex: 1;
    }

    .current-file-label {
        margin: 0;
        font-size: 7.5px;
        font-weight: 800;
        color: #64748b;
    }

    .current-file-name {
        display: block;
        margin-top: 1px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 8.5px;
        font-weight: 700;
        color: #334155;
    }

    .current-file-empty {
        margin: 0;
        font-size: 8.5px;
        font-weight: 700;
        color: #b45309;
    }

    .current-file-empty-text {
        margin: 1px 0 0;
        font-size: 7.5px;
        color: #64748b;
    }

    .view-btn {
        min-height: 27px;
        padding: 0 8px;
        border-radius: 6px;
        background: #e2e8f0;
        color: #475569;
        text-decoration: none;
        font-size: 8px;
        font-weight: 800;
        white-space: nowrap;
    }

    .view-btn:hover {
        background: #e0e7ff;
        color: #4338ca;
    }

    .upload-box {
        min-height: 105px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 10px;
        border: 2px dashed #94a3b8;
        border-radius: 8px;
        background: #f8fafc;
        text-align: center;
        cursor: pointer;
        transition: .15s ease;
    }

    .upload-box:hover {
        border-color: #6366f1;
        background: #eef2ff;
    }

    .upload-icon {
        width: 33px;
        height: 33px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 5px;
        border-radius: 7px;
        background: #e0e7ff;
        color: #4f46e5;
    }

    .upload-title {
        font-size: 9px;
        font-weight: 800;
        color: #334155;
    }

    .upload-format {
        margin-top: 2px;
        font-size: 7.5px;
        color: #64748b;
    }

    .upload-limit {
        margin-top: 2px;
        font-size: 7px;
        color: #94a3b8;
    }

    .selected-file {
        display: flex;
        align-items: center;
        gap: 7px;
        padding: 7px;
        border: 2px solid #a7f3d0;
        border-radius: 7px;
        background: #ecfdf5;
    }

    .selected-file-icon {
        width: 27px;
        height: 27px;
        flex: 0 0 27px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #d1fae5;
        color: #059669;
    }

    .selected-file-content {
        min-width: 0;
        flex: 1;
    }

    .selected-file-title {
        margin: 0;
        font-size: 7.5px;
        font-weight: 800;
        color: #047857;
    }

    .selected-file-name {
        margin: 1px 0 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 8px;
        font-weight: 700;
        color: #334155;
    }

    .selected-file-size {
        margin: 1px 0 0;
        font-size: 7px;
        color: #64748b;
    }

    .clear-file-btn {
        width: 26px;
        height: 26px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 6px;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
    }

    .clear-file-btn:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .camera-box {
        overflow: hidden;
        border: 2px solid #475569;
        border-radius: 8px;
        background: #0f172a;
    }

    .camera-preview {
        position: relative;
        min-height: 215px;
        background: #0f172a;
    }

    .camera-video {
        width: 100%;
        height: 215px;
        display: block;
        object-fit: contain;
        background: #0f172a;
    }

    .camera-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 14px;
        background: #0f172a;
        text-align: center;
    }

    .camera-placeholder-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 6px;
        border-radius: 8px;
        background: rgba(255,255,255,.08);
        color: #94a3b8;
    }

    .camera-placeholder-title {
        margin: 0;
        font-size: 9px;
        font-weight: 800;
        color: #cbd5e1;
    }

    .camera-placeholder-desc {
        margin: 2px 0 0;
        font-size: 7px;
        color: #64748b;
    }

    .camera-error {
        position: absolute;
        left: 7px;
        right: 7px;
        bottom: 7px;
        padding: 6px 7px;
        border-radius: 6px;
        background: rgba(127,29,29,.94);
        color: #fecaca;
        font-size: 7.5px;
        font-weight: 700;
    }

    .camera-frame {
        position: absolute;
        inset: 25px;
        border: 1.5px dashed rgba(255,255,255,.28);
        border-radius: 7px;
        pointer-events: none;
    }

    .camera-actions {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 5px;
        padding: 7px;
        background: #111827;
    }

    .camera-btn {
        min-height: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 0 6px;
        border-radius: 6px;
        font-size: 8px;
        font-weight: 800;
        cursor: pointer;
    }

    .camera-btn-primary {
        border: 1px solid #4338ca;
        background: #4f46e5;
        color: #fff;
    }

    .camera-btn-success {
        border: 1px solid #059669;
        background: #10b981;
        color: #fff;
    }

    .camera-btn-secondary {
        border: 1px solid #64748b;
        background: #fff;
        color: #334155;
    }

    .camera-btn:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .scan-result {
        padding: 7px;
        border-top: 2px solid #6ee7b7;
        background: #ecfdf5;
    }

    .scan-result-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
    }

    .scan-result-title {
        margin: 0;
        font-size: 9px;
        font-weight: 800;
        color: #065f46;
    }

    .scan-result-desc {
        margin: 2px 0 0;
        font-size: 7.5px;
        line-height: 1.4;
        color: #047857;
    }

    .retake-btn {
        min-height: 25px;
        padding: 0 7px;
        border: 1px solid #6ee7b7;
        border-radius: 6px;
        background: #fff;
        color: #047857;
        font-size: 7.5px;
        font-weight: 800;
        cursor: pointer;
    }

    .scan-image {
        display: block;
        width: 100%;
        max-height: 280px;
        margin-top: 6px;
        object-fit: contain;
        border: 1px solid #a7f3d0;
        border-radius: 6px;
        background: #fff;
    }

    .scan-info {
        margin-top: 4px;
        text-align: center;
        font-size: 7.5px;
        font-weight: 800;
        color: #047857;
    }

    .physical-box {
        display: flex;
        flex: 1;
        flex-direction: column;
        justify-content: center;
        padding: 14px;
        border: 2px dashed #94a3b8;
        border-radius: 8px;
        background: #f8fafc;
    }

    .physical-icon {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background: #e0e7ff;
        color: #4f46e5;
    }

    .physical-title {
        margin: 8px 0 0;
        font-size: 11px;
        font-weight: 800;
        color: #334155;
    }

    .physical-desc {
        margin: 3px 0 0;
        font-size: 8px;
        line-height: 1.5;
        color: #64748b;
    }

    .physical-field {
        margin-top: 12px;
    }

    .physical-example {
        margin-top: 7px;
        padding: 7px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #fff;
    }

    .physical-example p {
        margin: 0;
        font-size: 7.5px;
        line-height: 1.45;
        color: #64748b;
    }

    .physical-example strong {
        color: #334155;
    }

    .bottom-note {
        margin-top: auto;
        padding: 7px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        background: #fff;
    }

    .bottom-note p {
        margin: 0;
        font-size: 7.5px;
        line-height: 1.45;
        color: #64748b;
    }

    .footer {
        display: flex;
        justify-content: flex-end;
        gap: 7px;
        padding: 9px 14px;
        border-top: 2px solid #64748b;
        background: #f8fafc;
    }

    .footer-btn {
        min-height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 12px;
        border-radius: 7px;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: .15s ease;
    }

    .footer-cancel {
        border: 2px solid #94a3b8;
        background: #fff;
        color: #475569;
    }

    .footer-cancel:hover {
        background: #f1f5f9;
    }

    .footer-submit {
        border: 2px solid #4338ca;
        background: #4f46e5;
        color: #fff;
    }

    .footer-submit:hover:not(:disabled) {
        background: #4338ca;
    }

    .footer-submit:disabled {
        opacity: .6;
        cursor: not-allowed;
    }

    .hidden {
        display: none !important;
    }

    @media (max-width: 820px) {
        .edit-page {
            padding-left: 10px;
            padding-right: 10px;
        }

        .attachment-grid {
            grid-template-columns: 1fr;
        }

        .attachment-card {
            min-height: 0;
        }
    }

    @media (max-width: 640px) {
        .edit-header {
            align-items: stretch;
            flex-direction: column;
        }

        .btn-back {
            width: 100%;
        }

        .agenda-bar {
            align-items: flex-start;
            flex-direction: column;
        }

        .field-grid {
            grid-template-columns: 1fr;
        }

        .field,
        .field:nth-child(2n),
        .field:nth-last-child(-n + 2) {
            border-right: 0;
            border-bottom: 2px solid #94a3b8;
        }

        .field:last-child {
            border-bottom: 0;
        }

        .field-full {
            grid-column: auto;
        }

        .mode-grid {
            grid-template-columns: 1fr;
        }

        .camera-actions {
            grid-template-columns: 1fr;
        }

        .footer {
            flex-direction: column-reverse;
        }

        .footer-btn {
            width: 100%;
        }
    }
</style>


<div class="edit-page">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="edit-header">

        <div class="edit-header-left">

            <div class="edit-title-icon">
                <svg
                    class="h-4.5 w-4.5"
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

            <div class="min-w-0">
                <h1 class="edit-title">
                    Edit Surat Masuk
                </h1>

                <p class="edit-subtitle">
                    Perbarui informasi surat dan lampiran arsip.
                </p>
            </div>

        </div>

        <a
            href="{{ route('surat-masuk.index') }}"
            class="btn-back"
        >
            <svg
                class="h-3.5 w-3.5"
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


    {{-- =========================================================
         ERROR
    ========================================================== --}}
    @if($errors->any())

        <div class="error-box">

            <p class="error-box-title">
                Data belum dapat diperbarui.
            </p>

            <ul class="error-box-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif


    {{-- =========================================================
         FORM
    ========================================================== --}}
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


        <div class="main-card">

            {{-- =================================================
                 AGENDA
            ================================================== --}}
            <div class="agenda-bar">

                <div class="agenda-left">

                    <div class="agenda-icon">
                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3"
                            />
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
                    Tidak diubah
                </span>

            </div>


            {{-- =================================================
                 BODY
            ================================================== --}}
            <div class="body">


                {{-- =================================================
                     INFORMASI UTAMA
                ================================================== --}}
                <section class="section">

                    <div class="section-head">

                        <div class="section-marker"></div>

                        <div>
                            <h2 class="section-title">
                                Informasi Utama Surat
                            </h2>

                            <p class="section-description">
                                Perbarui data utama surat masuk.
                            </p>
                        </div>

                    </div>


                    <div class="field-table">

                        <div class="field-grid">

                            {{-- NOMOR SURAT --}}
                            <div class="field">

                                <label
                                    for="nomor_surat"
                                    class="field-label"
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
                                    class="control @error('nomor_surat') border-red-500 @enderror"
                                >

                                @error('nomor_surat')
                                    <p class="field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- PENGIRIM --}}
                            <div class="field">

                                <label
                                    for="pengirim"
                                    class="field-label"
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
                                    placeholder="Nama instansi pengirim"
                                    class="control @error('pengirim') border-red-500 @enderror"
                                >

                                @error('pengirim')
                                    <p class="field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- TANGGAL SURAT --}}
                            <div class="field">

                                <label
                                    for="tanggal_surat"
                                    class="field-label"
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
                                    class="control @error('tanggal_surat') border-red-500 @enderror"
                                >

                                @error('tanggal_surat')
                                    <p class="field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- TANGGAL TERIMA --}}
                            <div class="field">

                                <label
                                    for="tanggal_terima"
                                    class="field-label"
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
                                    class="control @error('tanggal_terima') border-red-500 @enderror"
                                >

                                @error('tanggal_terima')
                                    <p class="field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- KATEGORI --}}
                            <div class="field">

                                <label
                                    for="kategori_surat_id"
                                    class="field-label"
                                >
                                    Kategori Surat
                                    <span class="required">*</span>
                                </label>

                                <select
                                    id="kategori_surat_id"
                                    name="kategori_surat_id"
                                    required
                                    class="control @error('kategori_surat_id') border-red-500 @enderror"
                                >

                                    <option
                                        value=""
                                        disabled
                                        @selected(!$selectedKategori)
                                    >
                                        Pilih kategori
                                    </option>

                                    @foreach(($kategoris ?? collect()) as $kategori)

                                        <option
                                            value="{{ $kategori->id }}"
                                            @selected(
                                                (string) $selectedKategori ===
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

                                @error('kategori_surat_id')
                                    <p class="field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- STATUS --}}
                            <div class="field">

                                <label
                                    for="status"
                                    class="field-label"
                                >
                                    Status Surat
                                    <span class="required">*</span>
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    required
                                    class="control @error('status') border-red-500 @enderror"
                                >

                                    @foreach($statusOptions as $value => $label)

                                        <option
                                            value="{{ $value }}"
                                            @selected($currentStatus === $value)
                                        >
                                            {{ $label }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('status')
                                    <p class="field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- PERIHAL --}}
                            <div class="field field-full">

                                <label
                                    for="perihal"
                                    class="field-label"
                                >
                                    Perihal
                                    <span class="required">*</span>
                                </label>

                                <textarea
                                    id="perihal"
                                    name="perihal"
                                    rows="3"
                                    required
                                    placeholder="Tuliskan perihal surat"
                                    class="control @error('perihal') border-red-500 @enderror"
                                >{{ old('perihal', $suratMasuk->perihal) }}</textarea>

                                @error('perihal')
                                    <p class="field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>


                            {{-- RINGKASAN --}}
                            <div class="field field-full">

                                <label
                                    for="ringkasan"
                                    class="field-label"
                                >
                                    Ringkasan
                                    <span class="font-normal text-slate-400">
                                        (opsional)
                                    </span>
                                </label>

                                <textarea
                                    id="ringkasan"
                                    name="ringkasan"
                                    rows="3"
                                    placeholder="Ringkasan isi surat..."
                                    class="control @error('ringkasan') border-red-500 @enderror"
                                >{{ old('ringkasan', $suratMasuk->ringkasan) }}</textarea>

                                @error('ringkasan')
                                    <p class="field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     LAMPIRAN
                ================================================== --}}
                <section class="section">

                    <div class="section-head">

                        <div class="section-marker"></div>

                        <div>
                            <h2 class="section-title">
                                Lampiran Dokumen & Arsip Fisik
                            </h2>

                            <p class="section-description">
                                Kelola dokumen digital dan lokasi arsip fisik.
                            </p>
                        </div>

                    </div>


                    <div class="attachment-grid">


                        {{-- =================================================
                             BERKAS DIGITAL
                        ================================================== --}}
                        <div class="attachment-card">

                            <div class="attachment-header">

                                <div class="min-w-0">

                                    <h3 class="attachment-title">
                                        Berkas Lampiran
                                    </h3>

                                    <p class="attachment-desc">
                                        Upload file atau scan kamera.
                                    </p>

                                </div>

                                <span class="attachment-badge">
                                    Opsional
                                </span>

                            </div>


                            <div class="attachment-body">


                                {{-- INFO --}}
                                <div class="info">

                                    <svg
                                        class="mt-0.5 h-3.5 w-3.5 shrink-0 text-blue-600"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"
                                        />
                                    </svg>

                                    <p>
                                        Maksimal <strong>10 MB</strong>.
                                        PDF tetap PDF.
                                        JPG/JPEG/PNG diproses menjadi JPG.
                                    </p>

                                </div>


                                {{-- MODE --}}
                                <div class="mode-grid">

                                    <button
                                        type="button"
                                        id="mode-upload-btn"
                                        class="mode-button active"
                                        aria-selected="true"
                                    >

                                        <span class="mode-icon">
                                            <svg
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 16V4m0 0L7 9m5-5l5 5M5 20h14"
                                                />
                                            </svg>
                                        </span>

                                        <span>
                                            <span class="mode-name">
                                                Upload File
                                            </span>

                                            <span class="mode-desc">
                                                Pilih dari perangkat
                                            </span>
                                        </span>

                                    </button>


                                    <button
                                        type="button"
                                        id="mode-scan-btn"
                                        class="mode-button"
                                        aria-selected="false"
                                    >

                                        <span class="mode-icon">
                                            <svg
                                                class="h-3.5 w-3.5"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M4 7V5a1 1 0 011-1h2M17 4h2a1 1 0 011 1v2M20 17v2a1 1 0 01-1 1h-2M7 20H5a1 1 0 01-1-1v-2M7 12h10"
                                                />
                                            </svg>
                                        </span>

                                        <span>
                                            <span class="mode-name">
                                                Scan Kamera
                                            </span>

                                            <span class="mode-desc">
                                                Ambil gambar dokumen
                                            </span>
                                        </span>

                                    </button>

                                </div>


                                {{-- FILE LAMA --}}
                                <div class="current-file">

                                    <div class="current-file-icon">
                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.414l4.414 4.414A2 2 0 0118 8.414V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                    </div>

                                    <div class="current-file-content">

                                        @if($suratMasuk->lampiran_file)

                                            <p class="current-file-label">
                                                File tersimpan
                                            </p>

                                            <span
                                                class="current-file-name"
                                                title="{{ basename($suratMasuk->lampiran_file) }}"
                                            >
                                                {{ basename($suratMasuk->lampiran_file) }}
                                            </span>

                                        @else

                                            <p class="current-file-empty">
                                                Belum ada lampiran
                                            </p>

                                            <p class="current-file-empty-text">
                                                Upload atau scan dokumen.
                                            </p>

                                        @endif

                                    </div>

                                    @if($suratMasuk->lampiran_file)

                                        <a
                                            href="{{ route('surat-masuk.preview-lampiran', $suratMasuk->id) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="view-btn"
                                        >
                                            Lihat
                                        </a>

                                    @endif

                                </div>


                                {{-- UPLOAD PANEL --}}
                                <div id="upload-panel">

                                    <label
                                        for="lampiran_file"
                                        id="upload-box"
                                        class="upload-box"
                                    >

                                        <span class="upload-icon">

                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 16V4m0 0L7 9m5-5l5 5M5 20h14"
                                                />
                                            </svg>

                                        </span>

                                        <span class="upload-title">
                                            Pilih file baru
                                        </span>

                                        <span class="upload-format">
                                            PDF, JPG, JPEG, PNG
                                        </span>

                                        <span class="upload-limit">
                                            Maksimal 10 MB
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
                                        class="selected-file hidden mt-2"
                                    >

                                        <div class="selected-file-icon">

                                            <svg
                                                class="h-3.5 w-3.5"
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

                                        <div class="selected-file-content">

                                            <p class="selected-file-title">
                                                File baru
                                            </p>

                                            <p
                                                id="selected-file-name"
                                                class="selected-file-name"
                                            ></p>

                                            <p
                                                id="selected-file-size"
                                                class="selected-file-size"
                                            ></p>

                                        </div>

                                        <button
                                            type="button"
                                            id="clear-file-btn"
                                            class="clear-file-btn"
                                            aria-label="Hapus file"
                                            title="Hapus file"
                                        >
                                            <svg
                                                class="h-3.5 w-3.5"
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


                                {{-- SCAN PANEL --}}
                                <div
                                    id="scan-panel"
                                    class="hidden"
                                >

                                    <div class="camera-box">

                                        <div class="camera-preview">

                                            <video
                                                id="camera-video"
                                                class="camera-video"
                                                autoplay
                                                playsinline
                                                muted
                                            ></video>


                                            <div
                                                id="camera-placeholder"
                                                class="camera-placeholder"
                                            >

                                                <div>

                                                    <div class="camera-placeholder-icon">

                                                        <svg
                                                            class="h-5 w-5"
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

                                                    </div>

                                                    <p class="camera-placeholder-title">
                                                        Kamera belum aktif
                                                    </p>

                                                    <p class="camera-placeholder-desc">
                                                        Aktifkan kamera untuk scan dokumen.
                                                    </p>

                                                </div>

                                            </div>


                                            <div
                                                id="camera-error"
                                                class="camera-error hidden"
                                                role="alert"
                                            ></div>

                                            <div class="camera-frame"></div>

                                        </div>


                                        <div class="camera-actions">

                                            <button
                                                type="button"
                                                id="start-camera-btn"
                                                class="camera-btn camera-btn-primary"
                                            >
                                                Aktifkan Kamera
                                            </button>

                                            <button
                                                type="button"
                                                id="capture-btn"
                                                class="camera-btn camera-btn-success"
                                                disabled
                                            >
                                                Ambil Gambar
                                            </button>

                                            <button
                                                type="button"
                                                id="stop-camera-btn"
                                                class="camera-btn camera-btn-secondary"
                                                disabled
                                            >
                                                Matikan
                                            </button>

                                        </div>


                                        <div
                                            id="scan-result"
                                            class="scan-result hidden"
                                        >

                                            <div class="scan-result-top">

                                                <div class="min-w-0">

                                                    <p class="scan-result-title">
                                                        Hasil scan siap
                                                    </p>

                                                    <p class="scan-result-desc">
                                                        Hasil scan akan menggantikan lampiran lama.
                                                    </p>

                                                </div>

                                                <button
                                                    type="button"
                                                    id="retake-btn"
                                                    class="retake-btn"
                                                >
                                                    Scan Ulang
                                                </button>

                                            </div>


                                            <img
                                                id="scan-preview-image"
                                                src=""
                                                alt="Hasil scan dokumen"
                                                class="scan-image"
                                            >


                                            <div
                                                id="scan-compression-info"
                                                class="scan-info"
                                            ></div>

                                        </div>

                                    </div>


                                    <input
                                        type="hidden"
                                        name="captured_image"
                                        id="captured_image"
                                        value="{{ old('captured_image') }}"
                                    >

                                </div>


                                <div class="bottom-note">

                                    <p>
                                        Tidak memilih file baru dan tidak melakukan scan
                                        berarti lampiran lama tetap digunakan.
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
                             LOKASI FISIK
                        ================================================== --}}
                        <div class="attachment-card">

                            <div class="attachment-header">

                                <div class="min-w-0">

                                    <h3 class="attachment-title">
                                        Lokasi Arsip Fisik
                                    </h3>

                                    <p class="attachment-desc">
                                        Posisi penyimpanan dokumen fisik.
                                    </p>

                                </div>

                                <span class="attachment-badge">
                                    Opsional
                                </span>

                            </div>


                            <div class="attachment-body">

                                <div class="physical-box">

                                    <div class="physical-icon">

                                        <svg
                                            class="h-4.5 w-4.5"
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


                                    <h4 class="physical-title">
                                        Detail Lokasi
                                    </h4>


                                    <p class="physical-desc">
                                        Isi posisi rak, lemari, box,
                                        atau map tempat dokumen fisik disimpan.
                                    </p>


                                    <div class="physical-field">

                                        <label
                                            for="lokasi_arsip_fisik"
                                            class="field-label"
                                        >
                                            Posisi Lemari / Rak / Box
                                        </label>

                                        <input
                                            id="lokasi_arsip_fisik"
                                            name="lokasi_arsip_fisik"
                                            type="text"
                                            value="{{ old('lokasi_arsip_fisik', $suratMasuk->lokasi_arsip_fisik) }}"
                                            placeholder="Contoh: Rak A-3 Box 12"
                                            class="control @error('lokasi_arsip_fisik') border-red-500 @enderror"
                                        >

                                        @error('lokasi_arsip_fisik')
                                            <p class="field-error">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                    </div>


                                    <div class="physical-example">

                                        <p>
                                            <strong>Contoh:</strong>
                                            Rak A-3 Box 12 atau
                                            Lemari B-2 Map 07.
                                        </p>

                                    </div>


                                    <div class="bottom-note">

                                        <p>
                                            Lokasi fisik hanya berupa informasi posisi arsip
                                            dan tidak memengaruhi dokumen digital.
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
            <div class="footer">

                <a
                    href="{{ route('surat-masuk.index') }}"
                    class="footer-btn footer-cancel"
                >
                    Batal
                </a>


                <button
                    type="submit"
                    id="submit-btn"
                    class="footer-btn footer-submit"
                >

                    <svg
                        class="h-3.5 w-3.5"
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
                        Perbarui Surat
                    </span>

                </button>

            </div>

        </div>

    </form>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form =
        document.getElementById('form-surat');

    const modeUploadBtn =
        document.getElementById('mode-upload-btn');

    const modeScanBtn =
        document.getElementById('mode-scan-btn');

    const uploadPanel =
        document.getElementById('upload-panel');

    const scanPanel =
        document.getElementById('scan-panel');

    const fileInput =
        document.getElementById('lampiran_file');

    const selectedFile =
        document.getElementById('selected-file');

    const selectedFileName =
        document.getElementById('selected-file-name');

    const selectedFileSize =
        document.getElementById('selected-file-size');

    const clearFileBtn =
        document.getElementById('clear-file-btn');

    const cameraVideo =
        document.getElementById('camera-video');

    const cameraPlaceholder =
        document.getElementById('camera-placeholder');

    const cameraError =
        document.getElementById('camera-error');

    const startCameraBtn =
        document.getElementById('start-camera-btn');

    const captureBtn =
        document.getElementById('capture-btn');

    const stopCameraBtn =
        document.getElementById('stop-camera-btn');

    const retakeBtn =
        document.getElementById('retake-btn');

    const scanResult =
        document.getElementById('scan-result');

    const scanPreviewImage =
        document.getElementById('scan-preview-image');

    const scanCompressionInfo =
        document.getElementById('scan-compression-info');

    const capturedInput =
        document.getElementById('captured_image');

    const submitBtn =
        document.getElementById('submit-btn');

    const submitText =
        document.getElementById('submit-text');


    const MAX_FILE_SIZE =
        10 * 1024 * 1024;

    const CAMERA_MAX_WIDTH =
        1600;

    const CAMERA_MAX_HEIGHT =
        1600;

    const CAMERA_TARGET_SIZE =
        2.5 * 1024 * 1024;

    const MAX_SCAN_DATA_SIZE =
        7 * 1024 * 1024;


    let cameraStream = null;
    let previewObjectUrl = null;
    let submitting = false;


    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    function formatFileSize(bytes) {

        if (
            !Number.isFinite(bytes) ||
            bytes <= 0
        ) {
            return '0 KB';
        }

        if (
            bytes < 1024 * 1024
        ) {
            return (
                (bytes / 1024).toFixed(1) +
                ' KB'
            );
        }

        return (
            (bytes / (1024 * 1024)).toFixed(2) +
            ' MB'
        );
    }


    function showCameraError(message) {

        if (!cameraError) {
            return;
        }

        cameraError.textContent =
            message;

        cameraError.classList.remove(
            'hidden'
        );
    }


    function clearCameraError() {

        if (!cameraError) {
            return;
        }

        cameraError.textContent =
            '';

        cameraError.classList.add(
            'hidden'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | MODE
    |--------------------------------------------------------------------------
    */

    function activateUploadMode() {

        modeUploadBtn.classList.add(
            'active'
        );

        modeScanBtn.classList.remove(
            'active'
        );

        modeUploadBtn.setAttribute(
            'aria-selected',
            'true'
        );

        modeScanBtn.setAttribute(
            'aria-selected',
            'false'
        );

        uploadPanel.classList.remove(
            'hidden'
        );

        scanPanel.classList.add(
            'hidden'
        );

        stopCamera();
        clearCameraError();
    }


    function activateScanMode() {

        modeUploadBtn.classList.remove(
            'active'
        );

        modeScanBtn.classList.add(
            'active'
        );

        modeUploadBtn.setAttribute(
            'aria-selected',
            'false'
        );

        modeScanBtn.setAttribute(
            'aria-selected',
            'true'
        );

        uploadPanel.classList.add(
            'hidden'
        );

        scanPanel.classList.remove(
            'hidden'
        );

        clearFileSelection();
        clearCameraError();
    }


    modeUploadBtn.addEventListener(
        'click',
        activateUploadMode
    );

    modeScanBtn.addEventListener(
        'click',
        activateScanMode
    );


    /*
    |--------------------------------------------------------------------------
    | FILE
    |--------------------------------------------------------------------------
    */

    fileInput.addEventListener(
        'change',
        function () {

            const file =
                fileInput.files?.[0];

            if (!file) {
                return;
            }

            clearCameraError();

            const extension =
                String(file.name)
                    .split('.')
                    .pop()
                    .toLowerCase();


            const allowedExtensions = [
                'pdf',
                'jpg',
                'jpeg',
                'png'
            ];


            if (
                !allowedExtensions.includes(
                    extension
                )
            ) {

                clearFileSelection();

                alert(
                    'Format file tidak didukung.'
                );

                return;
            }


            if (
                file.size <= 0
            ) {

                clearFileSelection();

                alert(
                    'File kosong atau tidak valid.'
                );

                return;
            }


            if (
                file.size >
                MAX_FILE_SIZE
            ) {

                clearFileSelection();

                alert(
                    'Ukuran file terlalu besar. Maksimal 10 MB.'
                );

                return;
            }


            capturedInput.value =
                '';

            clearScanResult();


            selectedFileName.textContent =
                file.name;

            selectedFileSize.textContent =
                formatFileSize(
                    file.size
                );

            selectedFile.classList.remove(
                'hidden'
            );

        }
    );


    function clearFileSelection() {

        fileInput.value =
            '';

        selectedFile.classList.add(
            'hidden'
        );

        selectedFileName.textContent =
            '';

        selectedFileSize.textContent =
            '';
    }


    clearFileBtn.addEventListener(
        'click',
        function () {
            clearFileSelection();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | CAMERA
    |--------------------------------------------------------------------------
    */

    async function startCamera() {

        clearCameraError();

        if (
            !window.isSecureContext
        ) {

            showCameraError(
                'Kamera membutuhkan HTTPS.'
            );

            return;
        }

        if (
            !navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia
        ) {

            showCameraError(
                'Browser tidak mendukung kamera.'
            );

            return;
        }


        stopCameraTracks();


        try {

            cameraStream =
                await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: {
                            ideal: 'environment'
                        },
                        width: {
                            ideal: 1600
                        },
                        height: {
                            ideal: 1600
                        }
                    },
                    audio: false
                });


            cameraVideo.srcObject =
                cameraStream;

            await cameraVideo.play();


            cameraPlaceholder.classList.add(
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

            let message =
                'Kamera tidak dapat digunakan.';

            if (
                error.name ===
                    'NotAllowedError'
            ) {

                message =
                    'Izin kamera ditolak. Izinkan kamera di browser.';
            } else if (
                error.name ===
                    'NotFoundError'
            ) {

                message =
                    'Kamera tidak ditemukan.';
            } else if (
                error.name ===
                    'NotReadableError'
            ) {

                message =
                    'Kamera sedang digunakan aplikasi lain.';
            }

            showCameraError(
                message
            );
        }
    }


    startCameraBtn.addEventListener(
        'click',
        startCamera
    );


    /*
    |--------------------------------------------------------------------------
    | CAPTURE
    |--------------------------------------------------------------------------
    */

    captureBtn.addEventListener(
        'click',
        async function () {

            clearCameraError();


            if (
                !cameraVideo.videoWidth ||
                !cameraVideo.videoHeight
            ) {

                showCameraError(
                    'Kamera belum siap. Tunggu beberapa saat.'
                );

                return;
            }


            try {

                const canvas =
                    document.createElement(
                        'canvas'
                    );


                let width =
                    cameraVideo.videoWidth;

                let height =
                    cameraVideo.videoHeight;


                const scale =
                    Math.min(
                        1,
                        CAMERA_MAX_WIDTH /
                            width,
                        CAMERA_MAX_HEIGHT /
                            height
                    );


                width =
                    Math.round(
                        width * scale
                    );

                height =
                    Math.round(
                        height * scale
                    );


                canvas.width =
                    width;

                canvas.height =
                    height;


                const context =
                    canvas.getContext(
                        '2d',
                        {
                            alpha: false
                        }
                    );


                if (!context) {

                    throw new Error(
                        'Canvas browser tidak tersedia.'
                    );
                }


                context.fillStyle =
                    '#ffffff';

                context.fillRect(
                    0,
                    0,
                    width,
                    height
                );


                context.drawImage(
                    cameraVideo,
                    0,
                    0,
                    width,
                    height
                );


                const dataUrl =
                    await createCompressedJpeg(
                        canvas
                    );


                if (
                    dataUrl.length >
                    MAX_SCAN_DATA_SIZE
                ) {

                    throw new Error(
                        'Hasil scan terlalu besar. Silakan scan ulang.'
                    );
                }


                capturedInput.value =
                    dataUrl;


                if (
                    previewObjectUrl
                ) {

                    URL.revokeObjectURL(
                        previewObjectUrl
                    );

                    previewObjectUrl =
                        null;
                }


                const blob =
                    await dataUrlToBlob(
                        dataUrl
                    );


                previewObjectUrl =
                    URL.createObjectURL(
                        blob
                    );


                scanPreviewImage.src =
                    previewObjectUrl;


                scanResult.classList.remove(
                    'hidden'
                );


                scanCompressionInfo.textContent =
                    'Hasil scan sudah dikompres menjadi JPEG.';


                clearFileSelection();


                stopCamera();


            } catch (error) {

                console.error(
                    'Capture error:',
                    error
                );

                showCameraError(
                    error.message ||
                    'Gagal memproses hasil scan.'
                );
            }
        }
    );


    async function createCompressedJpeg(
        canvas
    ) {

        let currentCanvas =
            canvas;


        let quality =
            0.82;


        let dataUrl =
            currentCanvas.toDataURL(
                'image/jpeg',
                quality
            );


        while (
            dataUrl.length >
                CAMERA_TARGET_SIZE &&
            quality > 0.4
        ) {

            quality -= 0.06;

            dataUrl =
                currentCanvas.toDataURL(
                    'image/jpeg',
                    quality
                );
        }


        if (
            dataUrl.length >
                CAMERA_TARGET_SIZE
        ) {

            const smallerCanvas =
                document.createElement(
                    'canvas'
                );


            smallerCanvas.width =
                Math.max(
                    1,
                    Math.round(
                        currentCanvas.width *
                        0.75
                    )
                );


            smallerCanvas.height =
                Math.max(
                    1,
                    Math.round(
                        currentCanvas.height *
                        0.75
                    )
                );


            const smallerContext =
                smallerCanvas.getContext(
                    '2d',
                    {
                        alpha: false
                    }
                );


            smallerContext.fillStyle =
                '#ffffff';

            smallerContext.fillRect(
                0,
                0,
                smallerCanvas.width,
                smallerCanvas.height
            );


            smallerContext.drawImage(
                currentCanvas,
                0,
                0,
                smallerCanvas.width,
                smallerCanvas.height
            );


            dataUrl =
                smallerCanvas.toDataURL(
                    'image/jpeg',
                    0.60
                );
        }


        return dataUrl;
    }


    function dataUrlToBlob(
        dataUrl
    ) {

        return fetch(
            dataUrl
        ).then(
            response =>
                response.blob()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STOP CAMERA
    |--------------------------------------------------------------------------
    */

    function stopCameraTracks() {

        if (!cameraStream) {
            return;
        }

        cameraStream
            .getTracks()
            .forEach(
                track => track.stop()
            );

        cameraStream =
            null;
    }


    function stopCamera() {

        stopCameraTracks();

        if (cameraVideo) {
            cameraVideo.srcObject =
                null;
        }

        cameraPlaceholder.classList.remove(
            'hidden'
        );

        startCameraBtn.disabled =
            false;

        captureBtn.disabled =
            true;

        stopCameraBtn.disabled =
            true;
    }


    stopCameraBtn.addEventListener(
        'click',
        stopCamera
    );


    /*
    |--------------------------------------------------------------------------
    | RETAKE
    |--------------------------------------------------------------------------
    */

    retakeBtn.addEventListener(
        'click',
        async function () {

            clearScanResult();

            capturedInput.value =
                '';

            await startCamera();
        }
    );


    function clearScanResult() {

        capturedInput.value =
            '';

        scanResult.classList.add(
            'hidden'
        );

        scanPreviewImage.src =
            '';

        scanCompressionInfo.textContent =
            '';


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


    /*
    |--------------------------------------------------------------------------
    | SUBMIT
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        function (event) {

            if (submitting) {

                event.preventDefault();

                return;
            }


            const hasFile =
                fileInput.files &&
                fileInput.files.length > 0;


            const hasCamera =
                capturedInput.value.trim() !== '';


            if (
                hasFile &&
                hasCamera
            ) {

                event.preventDefault();

                alert(
                    'Gunakan salah satu metode: Upload File atau Scan Kamera.'
                );

                return;
            }


            if (hasFile) {

                const file =
                    fileInput.files[0];


                if (
                    file.size >
                    MAX_FILE_SIZE
                ) {

                    event.preventDefault();

                    alert(
                        'Ukuran file terlalu besar. Maksimal 10 MB.'
                    );

                    return;
                }
            }


            if (
                hasCamera &&
                capturedInput.value.length >
                    MAX_SCAN_DATA_SIZE
            ) {

                event.preventDefault();

                alert(
                    'Hasil scan terlalu besar. Silakan scan ulang.'
                );

                return;
            }


            submitting =
                true;


            stopCameraTracks();


            submitBtn.disabled =
                true;


            submitText.textContent =
                'Menyimpan...';

        }
    );


    /*
    |--------------------------------------------------------------------------
    | OLD CAMERA DATA
    |--------------------------------------------------------------------------
    */

    if (
        capturedInput.value
    ) {

        activateScanMode();

        scanResult.classList.remove(
            'hidden'
        );

        scanPreviewImage.src =
            capturedInput.value;

        scanCompressionInfo.textContent =
            'Hasil scan sebelumnya masih tersedia.';
    }


    /*
    |--------------------------------------------------------------------------
    | CLEANUP
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'beforeunload',
        function () {

            stopCameraTracks();

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
    );
});
</script>

@endsection