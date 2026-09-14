@extends('layouts.app')

@section('title', 'Catat Surat Masuk')

@section('content')

@php
    $formErrors = $errors ?? session('errors');

    if (
        !$formErrors ||
        !is_object($formErrors) ||
        !method_exists($formErrors, 'any')
    ) {
        $formErrors = new \Illuminate\Support\ViewErrorBag();

        $messageBag = session('errors');

        if ($messageBag instanceof \Illuminate\Support\MessageBag) {
            $formErrors->put('default', $messageBag);
        }
    }
@endphp

<style>
/* ============================================================
   PAGE
============================================================ */

.create-surat-page {
    width: 100%;
    max-width: 1040px;
    margin: 0 auto;
    padding: 12px 18px 32px;
    color: #334155;
}

.create-surat-page *,
.create-surat-page *::before,
.create-surat-page *::after {
    box-sizing: border-box;
}


/* ============================================================
   TOP ACTION
============================================================ */

.top-action {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
}

.top-back-btn {
    min-height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 0 13px;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    background: #ffffff;
    color: #475569;
    text-decoration: none;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
    transition: all .15s ease;
}

.top-back-btn:hover {
    border-color: #6366f1;
    background: #eef2ff;
    color: #4338ca;
}


/* ============================================================
   ERROR
============================================================ */

.error-box {
    margin-bottom: 12px;
    padding: 11px 13px;
    border: 1.5px solid #fecaca;
    border-radius: 9px;
    background: #fff7f7;
}

.error-box-inner {
    display: flex;
    align-items: flex-start;
    gap: 9px;
}

.error-icon {
    width: 30px;
    height: 30px;
    flex: 0 0 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #fecdd3;
    border-radius: 7px;
    background: #ffe4e6;
    color: #e11d48;
}

.error-title {
    margin: 0;
    font-size: 10px;
    line-height: 1.35;
    font-weight: 800;
    color: #be123c;
}

.error-list {
    margin: 3px 0 0;
    padding-left: 16px;
    font-size: 9px;
    line-height: 1.5;
    color: #e11d48;
}


/* ============================================================
   MAIN CARD
============================================================ */

.main-card {
    width: 100%;
    overflow: hidden;
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    background: #ffffff;
    box-shadow:
        0 8px 22px rgba(15, 23, 42, .06),
        0 2px 6px rgba(15, 23, 42, .04);
}


/* ============================================================
   HEADER
============================================================ */

.system-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 15px 18px;
    border-bottom: 1.5px solid #dbeafe;
    background: linear-gradient(
        135deg,
        #f8fbff 0%,
        #eef2ff 100%
    );
}

.system-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}

.system-header-icon {
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid #c7d2fe;
    border-radius: 9px;
    background: #ffffff;
    color: #4f46e5;
}

.system-header-label {
    margin: 0;
    font-size: 8px;
    line-height: 1.2;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #4f46e5;
}

.system-header-title {
    margin: 2px 0 0;
    font-size: 17px;
    line-height: 1.3;
    font-weight: 800;
    color: #1e293b;
}

.system-header-subtitle {
    margin: 3px 0 0;
    font-size: 9px;
    line-height: 1.4;
    color: #64748b;
}

.system-header-badge {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    padding: 0 10px;
    border: 1px solid #c7d2fe;
    border-radius: 999px;
    background: #ffffff;
    color: #475569;
    font-size: 8px;
    font-weight: 800;
    white-space: nowrap;
}


/* ============================================================
   BODY
============================================================ */

.main-body {
    padding: 16px;
}


/* ============================================================
   SECTION
============================================================ */

.section {
    width: 100%;
}

.section + .section {
    margin-top: 17px;
    padding-top: 17px;
    border-top: 1.5px solid #e2e8f0;
}

.section-heading {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 10px;
}

.section-marker {
    width: 4px;
    height: 27px;
    flex: 0 0 4px;
    margin-top: 1px;
    border-radius: 999px;
    background: #2563eb;
}

.section-marker-indigo {
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
    margin: 3px 0 0;
    font-size: 8.5px;
    line-height: 1.4;
    color: #64748b;
}


/* ============================================================
   FIELD TABLE
============================================================ */

.field-table {
    overflow: hidden;
    border: 1.5px solid #cbd5e1;
    border-radius: 9px;
    background: #ffffff;
}

.field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.field {
    min-width: 0;
    padding: 11px 12px;
    border-right: 1.5px solid #e2e8f0;
    border-bottom: 1.5px solid #e2e8f0;
    background: #ffffff;
}

.field:nth-child(2n) {
    border-right: 0;
}

.field-full {
    grid-column: 1 / -1;
    border-right: 0;
}

.field:last-child {
    border-bottom: 0;
}


/* ============================================================
   LABEL
============================================================ */

.field-label,
.physical-label {
    display: block;
    margin-bottom: 5px;
    font-size: 9px;
    line-height: 1.3;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .025em;
    color: #475569;
}

.required {
    color: #dc2626;
}


/* ============================================================
   CONTROL
============================================================ */

.control {
    width: 100%;
    min-height: 40px;
    padding: 8px 10px;
    border: 1.5px solid #cbd5e1;
    border-radius: 7px;
    background: #ffffff;
    color: #1e293b;
    outline: none;
    font-size: 12px;
    line-height: 1.35;
    transition:
        border-color .15s ease,
        box-shadow .15s ease;
}

input.control,
select.control {
    height: 40px;
    padding: 0 10px;
}

textarea.control {
    min-height: 82px;
    padding: 9px 10px;
    line-height: 1.5;
    resize: vertical;
}

.control::placeholder {
    color: #94a3b8;
}

.control:hover {
    border-color: #94a3b8;
}

.control:focus {
    border-color: #6366f1;
    box-shadow:
        0 0 0 3px rgba(99,102,241,.08);
}

.control-error {
    border-color: #ef4444 !important;
    background: #fff7f7 !important;
}

.field-error {
    margin: 4px 0 0;
    font-size: 8px;
    line-height: 1.4;
    font-weight: 700;
    color: #dc2626;
}


/* ============================================================
   ATTACHMENT GRID
============================================================ */

.attachment-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    align-items: stretch;
}

.attachment-card {
    min-width: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1.5px solid #cbd5e1;
    border-radius: 9px;
    background: #ffffff;
}

.attachment-header {
    min-height: 54px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 9px 11px;
    border-bottom: 1.5px solid #e2e8f0;
    background: #f8fafc;
}

.attachment-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}

.attachment-icon {
    width: 32px;
    height: 32px;
    flex: 0 0 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #bfdbfe;
    border-radius: 7px;
    background: #ffffff;
    color: #2563eb;
}

.attachment-icon.physical {
    border-color: #c7d2fe;
    color: #4f46e5;
}

.attachment-title {
    margin: 0;
    font-size: 10px;
    line-height: 1.3;
    font-weight: 800;
    color: #1e293b;
}

.attachment-description {
    margin: 2px 0 0;
    font-size: 7.5px;
    line-height: 1.4;
    color: #64748b;
}

.attachment-badge {
    flex-shrink: 0;
    padding: 4px 7px;
    border: 1px solid #cbd5e1;
    border-radius: 999px;
    background: #ffffff;
    color: #64748b;
    font-size: 7px;
    font-weight: 800;
    text-transform: uppercase;
}

.attachment-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 8px;
    padding: 10px;
}


/* ============================================================
   INFO
============================================================ */

.info-box {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    padding: 8px;
    border: 1px solid #bfdbfe;
    border-radius: 7px;
    background: #eff6ff;
}

.info-box svg {
    width: 14px;
    height: 14px;
    flex: 0 0 14px;
    margin-top: 1px;
    color: #2563eb;
}

.info-box-text {
    margin: 0;
    font-size: 7.5px;
    line-height: 1.5;
    color: #1d4ed8;
}


/* ============================================================
   MODE
============================================================ */

.mode-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px;
}

.mode-btn {
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 8px;
    border: 1.5px solid #cbd5e1;
    border-radius: 7px;
    background: #ffffff;
    color: #475569;
    font-size: 8px;
    font-weight: 800;
    cursor: pointer;
    transition: all .15s ease;
}

.mode-btn:hover {
    border-color: #818cf8;
    background: #eef2ff;
}

.mode-btn.active {
    border-color: #4f46e5;
    background: #eef2ff;
    color: #4338ca;
}


/* ============================================================
   UPLOAD
============================================================ */

.upload-box {
    min-height: 108px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 10px;
    border: 1.5px dashed #94a3b8;
    border-radius: 8px;
    background: #f8fafc;
    text-align: center;
    cursor: pointer;
    transition: all .15s ease;
}

.upload-box:hover {
    border-color: #6366f1;
    background: #eef2ff;
}

.upload-box.has-file {
    border-color: #34d399;
    background: #ecfdf5;
}

.upload-icon {
    width: 34px;
    height: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 5px;
    border-radius: 7px;
    background: #e0e7ff;
    color: #4f46e5;
}

.upload-box.has-file .upload-icon {
    background: #d1fae5;
    color: #059669;
}

.upload-title {
    font-size: 8px;
    line-height: 1.3;
    font-weight: 800;
    color: #334155;
}

.upload-format {
    margin-top: 2px;
    font-size: 7px;
    color: #64748b;
}

.upload-limit {
    margin-top: 3px;
    padding: 3px 7px;
    border-radius: 999px;
    background: #dbeafe;
    color: #2563eb;
    font-size: 6.5px;
    font-weight: 700;
}


/* ============================================================
   SELECTED FILE
============================================================ */

.selected-file {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px;
    border: 1.5px solid #a7f3d0;
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
    font-size: 7px;
    line-height: 1.2;
    font-weight: 800;
    color: #047857;
}

.selected-file-name {
    margin: 1px 0 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 7.5px;
    line-height: 1.3;
    font-weight: 700;
    color: #334155;
}

.selected-file-size {
    margin: 1px 0 0;
    font-size: 6.5px;
    line-height: 1.3;
    color: #64748b;
}

.clear-file-btn {
    width: 26px;
    height: 26px;
    flex: 0 0 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #a7f3d0;
    border-radius: 6px;
    background: #ffffff;
    color: #059669;
    cursor: pointer;
}


/* ============================================================
   COMPRESSION
============================================================ */

.compression-status {
    padding: 7px 8px;
    border: 1px solid #bfdbfe;
    border-radius: 7px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 7px;
    line-height: 1.45;
}


/* ============================================================
   CAMERA
============================================================ */

.camera-box {
    overflow: hidden;
    border: 1.5px solid #475569;
    border-radius: 8px;
    background: #0f172a;
}

.camera-preview {
    position: relative;
    min-height: 220px;
    background: #0f172a;
}

.camera-video {
    width: 100%;
    height: 220px;
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
    padding: 12px;
    background: #0f172a;
    text-align: center;
}

.camera-placeholder-icon {
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 5px;
    border-radius: 7px;
    background: rgba(255,255,255,.08);
    color: #94a3b8;
}

.camera-placeholder-title {
    margin: 0;
    font-size: 8px;
    font-weight: 800;
    color: #cbd5e1;
}

.camera-placeholder-desc {
    margin: 2px 0 0;
    font-size: 6.5px;
    line-height: 1.35;
    color: #64748b;
}

.camera-error {
    position: absolute;
    left: 6px;
    right: 6px;
    bottom: 6px;
    z-index: 5;
    padding: 6px;
    border-radius: 6px;
    background: rgba(127,29,29,.94);
    color: #fecaca;
    font-size: 7px;
    font-weight: 700;
    line-height: 1.4;
}

.camera-frame {
    position: absolute;
    inset: 22px;
    border: 1px dashed rgba(255,255,255,.22);
    border-radius: 6px;
    pointer-events: none;
}

.camera-actions {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 5px;
    padding: 6px;
    background: #111827;
}

.camera-btn {
    min-height: 31px;
    padding: 0 5px;
    border-radius: 6px;
    font-size: 7px;
    font-weight: 800;
    cursor: pointer;
}

.camera-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.camera-btn-primary {
    border: 1px solid #4338ca;
    background: #4f46e5;
    color: #ffffff;
}

.camera-btn-success {
    border: 1px solid #059669;
    background: #10b981;
    color: #ffffff;
}

.camera-btn-secondary {
    border: 1px solid #64748b;
    background: #ffffff;
    color: #334155;
}

.snapshot-preview {
    padding: 7px;
    border: 1.5px solid #a7f3d0;
    border-radius: 7px;
    background: #ecfdf5;
    color: #047857;
    text-align: center;
    font-size: 7px;
    font-weight: 800;
}


/* ============================================================
   PHYSICAL ARCHIVE
============================================================ */

.physical-box {
    display: flex;
    flex: 1;
    flex-direction: column;
    padding: 13px;
    border: 1.5px dashed #94a3b8;
    border-radius: 8px;
    background: #f8fafc;
}

.physical-icon {
    width: 37px;
    height: 37px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    background: #e0e7ff;
    color: #4f46e5;
}

.physical-title {
    margin: 7px 0 0;
    font-size: 10px;
    line-height: 1.3;
    font-weight: 800;
    color: #334155;
}

.physical-description {
    margin: 3px 0 0;
    font-size: 7.5px;
    line-height: 1.5;
    color: #64748b;
}

.physical-field {
    margin-top: 11px;
}

.physical-example {
    margin-top: 6px;
    padding: 7px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
}

.physical-example p {
    margin: 0;
    font-size: 7px;
    line-height: 1.45;
    color: #64748b;
}

.physical-example strong {
    color: #334155;
}

.physical-note {
    margin-top: auto;
    padding: 7px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
}

.physical-note p {
    margin: 0;
    font-size: 7px;
    line-height: 1.45;
    color: #64748b;
}


/* ============================================================
   FOOTER
============================================================ */

.form-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 7px;
    padding: 10px 14px;
    border-top: 1.5px solid #e2e8f0;
    background: #f8fafc;
}

.footer-btn {
    min-height: 35px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 13px;
    border-radius: 7px;
    font-size: 8.5px;
    font-weight: 800;
    text-decoration: none;
    cursor: pointer;
    transition: all .15s ease;
}

.footer-cancel {
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
}

.footer-cancel:hover {
    border-color: #94a3b8;
    background: #f1f5f9;
}

.footer-submit {
    border: 1.5px solid #2563eb;
    background: #2563eb;
    color: #ffffff;
    box-shadow: 0 3px 7px rgba(37,99,235,.12);
}

.footer-submit:hover:not(:disabled) {
    background: #1d4ed8;
}

.footer-submit:disabled {
    opacity: .6;
    cursor: not-allowed;
}


/* ============================================================
   UTILITY
============================================================ */

.hidden {
    display: none !important;
}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 820px) {

    .edit-page,
    .create-surat-page {
        max-width: 760px;
    }

    .attachment-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {

    .create-surat-page {
        padding: 6px 9px 20px;
    }

    .top-action {
        justify-content: stretch;
    }

    .top-back-btn {
        width: 100%;
    }

    .system-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .system-header-badge {
        align-self: flex-start;
    }

    .main-body {
        padding: 11px;
    }

    .field-grid {
        grid-template-columns: 1fr;
    }

    .field {
        padding: 10px;
        border-right: 0;
    }

    .field:nth-child(2n) {
        border-right: 0;
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

    .form-footer {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .footer-btn {
        width: 100%;
    }
}

@media (max-width: 420px) {

    .system-header {
        padding: 12px;
    }

    .system-header-title {
        font-size: 15px;
    }

    .main-body {
        padding: 9px;
    }

    .field {
        padding: 9px;
    }

    .attachment-body {
        padding: 8px;
    }

    .control {
        font-size: 11px;
    }
}
</style>


<div class="create-surat-page">

    {{-- =========================================================
         AKSI KEMBALI
    ========================================================== --}}

    <div class="top-action">

        <a
            href="{{ route('surat-masuk.index') }}"
            class="top-back-btn"
        >
            <svg
                class="h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                aria-hidden="true"
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

    @if($formErrors->any())

        <div class="error-box">

            <div class="error-box-inner">

                <div class="error-icon">

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
                            d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z"
                        />
                    </svg>

                </div>

                <div>

                    <p class="error-title">
                        Data belum dapat disimpan.
                    </p>

                    <ul class="error-list">

                        @foreach($formErrors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach

                    </ul>

                </div>

            </div>

        </div>

    @endif


    {{-- =========================================================
         FORM
    ========================================================== --}}

    <form
        id="form-surat"
        method="POST"
        action="{{ route('surat-masuk.store') }}"
        enctype="multipart/form-data"
    >

        @csrf

        <input
            type="hidden"
            name="nomor_agenda"
            value="{{ old('nomor_agenda', $nomorAgenda ?? '') }}"
        >


        <div class="main-card">

            {{-- =================================================
                 HEADER
            ================================================== --}}

            <div class="system-header">

                <div class="system-header-left">

                    <div class="system-header-icon">

                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M4 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M8 11h8M8 15h5"
                            />
                        </svg>

                    </div>


                    <div class="min-w-0">

                        <p class="system-header-label">
                            Sistem Arsip Masuk
                        </p>

                        <h1 class="system-header-title">
                            Input Surat Masuk
                        </h1>

                        <p class="system-header-subtitle">
                            Masukkan data surat masuk untuk disimpan ke dalam arsip digital.
                        </p>

                    </div>

                </div>


                <span class="system-header-badge">
                    Arsip Digital
                </span>

            </div>


            {{-- =================================================
                 BODY
            ================================================== --}}

            <div class="main-body">

                {{-- =================================================
                     INFORMASI UTAMA
                ================================================== --}}

                <section class="section">

                    <div class="section-heading">

                        <div class="section-marker"></div>

                        <div>

                            <h2 class="section-title">
                                Informasi Utama Surat
                            </h2>

                            <p class="section-description">
                                Lengkapi identitas dan informasi utama surat masuk.
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
                                    value="{{ old('nomor_surat') }}"
                                    placeholder="Contoh: 005/B/I/2026"
                                    autocomplete="off"
                                    maxlength="255"
                                    required
                                    class="control {{ $formErrors->has('nomor_surat') ? 'control-error' : '' }}"
                                >

                                @if($formErrors->has('nomor_surat'))
                                    <p class="field-error">
                                        {{ $formErrors->first('nomor_surat') }}
                                    </p>
                                @endif

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
                                    value="{{ old('pengirim') }}"
                                    placeholder="Masukkan nama instansi pengirim"
                                    autocomplete="organization"
                                    maxlength="255"
                                    required
                                    class="control {{ $formErrors->has('pengirim') ? 'control-error' : '' }}"
                                >

                                @if($formErrors->has('pengirim'))
                                    <p class="field-error">
                                        {{ $formErrors->first('pengirim') }}
                                    </p>
                                @endif

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
                                    value="{{ old('tanggal_surat') }}"
                                    required
                                    class="control {{ $formErrors->has('tanggal_surat') ? 'control-error' : '' }}"
                                >

                                @if($formErrors->has('tanggal_surat'))
                                    <p class="field-error">
                                        {{ $formErrors->first('tanggal_surat') }}
                                    </p>
                                @endif

                            </div>


                            {{-- TANGGAL DITERIMA --}}

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
                                    value="{{ old('tanggal_terima', now()->format('Y-m-d')) }}"
                                    required
                                    class="control {{ $formErrors->has('tanggal_terima') ? 'control-error' : '' }}"
                                >

                                @if($formErrors->has('tanggal_terima'))
                                    <p class="field-error">
                                        {{ $formErrors->first('tanggal_terima') }}
                                    </p>
                                @endif

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
                                    class="control {{ $formErrors->has('kategori_surat_id') ? 'control-error' : '' }}"
                                >

                                    <option value="">
                                        Pilih kategori surat
                                    </option>

                                    @foreach(($kategoris ?? collect()) as $kategori)

                                        <option
                                            value="{{ $kategori->id }}"
                                            @selected(
                                                (string) old('kategori_surat_id') ===
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

                                @if($formErrors->has('kategori_surat_id'))
                                    <p class="field-error">
                                        {{ $formErrors->first('kategori_surat_id') }}
                                    </p>
                                @endif

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
                                    class="control {{ $formErrors->has('status') ? 'control-error' : '' }}"
                                >

                                    @foreach([
                                        'baru' => 'Baru',
                                        'diproses' => 'Diproses',
                                        'didisposisikan' => 'Didisposisikan',
                                        'selesai' => 'Selesai',
                                        'diarsipkan' => 'Diarsipkan',
                                    ] as $value => $label)

                                        <option
                                            value="{{ $value }}"
                                            @selected(
                                                old('status', 'baru') === $value
                                            )
                                        >
                                            {{ $label }}
                                        </option>

                                    @endforeach

                                </select>

                                @if($formErrors->has('status'))
                                    <p class="field-error">
                                        {{ $formErrors->first('status') }}
                                    </p>
                                @endif

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
                                    maxlength="1000"
                                    required
                                    placeholder="Tuliskan perihal surat secara jelas"
                                    class="control {{ $formErrors->has('perihal') ? 'control-error' : '' }}"
                                >{{ old('perihal') }}</textarea>

                                @if($formErrors->has('perihal'))
                                    <p class="field-error">
                                        {{ $formErrors->first('perihal') }}
                                    </p>
                                @endif

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     LAMPIRAN
                ================================================== --}}

                <section class="section">

                    <div class="section-heading">

                        <div class="section-marker section-marker-indigo"></div>

                        <div>

                            <h2 class="section-title">
                                Lampiran Dokumen & Arsip Fisik
                            </h2>

                            <p class="section-description">
                                Upload dokumen atau scan menggunakan kamera, lalu isi lokasi arsip fisiknya.
                            </p>

                        </div>

                    </div>


                    <div class="attachment-grid">

                        {{-- =================================================
                             BERKAS DIGITAL
                        ================================================== --}}

                        <div class="attachment-card">

                            <div class="attachment-header">

                                <div class="attachment-header-left">

                                    <div class="attachment-icon">

                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M7 3h7l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M14 3v5h5"
                                            />
                                        </svg>

                                    </div>


                                    <div class="min-w-0">

                                        <h3 class="attachment-title">
                                            Berkas Digital
                                        </h3>

                                        <p class="attachment-description">
                                            PDF, JPG, JPEG, PNG · Maksimal 10 MB
                                        </p>

                                    </div>

                                </div>


                                <span
                                    class="attachment-badge"
                                    style="color:#dc2626;border-color:#fecaca;background:#fff1f2;"
                                >
                                    Wajib
                                </span>

                            </div>


                            <div class="attachment-body">

                                <div class="info-box">

                                    <svg
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000-18z"
                                        />
                                    </svg>

                                    <p class="info-box-text">
                                        <strong>Maksimal 10 MB.</strong>
                                        PDF tetap PDF.
                                        JPG/JPEG/PNG dikompres otomatis
                                        menjadi JPG sebelum dikirim.
                                    </p>

                                </div>


                                {{-- MODE --}}

                                <div class="mode-grid">

                                    <button
                                        type="button"
                                        id="btn-upload"
                                        aria-selected="true"
                                        class="mode-btn active"
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
                                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V4m0 0L8 8m4-4l4 4"
                                            />
                                        </svg>

                                        Upload File

                                    </button>


                                    <button
                                        type="button"
                                        id="btn-camera"
                                        aria-selected="false"
                                        class="mode-btn"
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
                                                d="M3 8h2l2-3h10l2 3h2a2 2 0 012 2v9a2 2 0 01-2 2H3a2 2 0 01-2-2v-9a2 2 0 012-2zm9 3a3 3 0 100 6 3 3 0 000-6z"
                                            />
                                        </svg>

                                        Scan Kamera

                                    </button>

                                </div>


                                {{-- UPLOAD PANEL --}}

                                <div id="upload-panel">

                                    <label
                                        id="upload-box"
                                        for="lampiran_file"
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
                                                    stroke-width="1.8"
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l3 3m-3-3v12"
                                                />
                                            </svg>

                                        </span>

                                        <span
                                            id="upload-title"
                                            class="upload-title"
                                        >
                                            Klik untuk memilih file
                                        </span>

                                        <span class="upload-format">
                                            PDF, JPG, JPEG, PNG
                                        </span>

                                        <span class="upload-limit">
                                            Maksimal 10 MB
                                        </span>

                                        <input
                                            id="lampiran_file"
                                            name="lampiran_file"
                                            type="file"
                                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                            class="sr-only"
                                        >

                                    </label>


                                    <div
                                        id="selected-file"
                                        class="selected-file hidden"
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
                                                File siap diproses
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


                                    <div
                                        id="file-compression-status"
                                        class="compression-status hidden"
                                    ></div>

                                </div>


                                {{-- CAMERA PANEL --}}

                                <div
                                    id="camera-panel"
                                    class="hidden"
                                >

                                    <div class="camera-box">

                                        <div class="camera-preview">

                                            <video
                                                id="video"
                                                autoplay
                                                muted
                                                playsinline
                                                class="camera-video"
                                            ></video>


                                            <img
                                                id="image-preview"
                                                src=""
                                                alt="Preview hasil scan"
                                                hidden
                                                class="absolute inset-0 h-full w-full bg-slate-900 object-contain"
                                            >


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
                                                                stroke-width="1.8"
                                                                d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"
                                                            />

                                                            <circle
                                                                cx="12"
                                                                cy="12"
                                                                r="3.5"
                                                            />
                                                        </svg>

                                                    </div>


                                                    <p class="camera-placeholder-title">
                                                        Kamera belum aktif
                                                    </p>


                                                    <p
                                                        id="camera-placeholder-text"
                                                        class="camera-placeholder-desc"
                                                    >
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
                                                id="start-cam-btn"
                                                class="camera-btn camera-btn-primary"
                                            >
                                                Nyalakan Kamera
                                            </button>


                                            <button
                                                type="button"
                                                id="capture-btn"
                                                class="camera-btn camera-btn-success hidden"
                                            >
                                                Ambil Foto
                                            </button>


                                            <button
                                                type="button"
                                                id="stop-cam-btn"
                                                class="camera-btn camera-btn-secondary hidden"
                                            >
                                                Tutup Kamera
                                            </button>

                                        </div>

                                    </div>


                                    <input
                                        type="hidden"
                                        name="captured_image"
                                        id="captured_image"
                                        value="{{ old('captured_image') }}"
                                    >


                                    <div
                                        id="snapshot-preview"
                                        class="snapshot-preview hidden"
                                    ></div>

                                </div>


                                @if($formErrors->has('lampiran_file'))
                                    <p class="field-error">
                                        {{ $formErrors->first('lampiran_file') }}
                                    </p>
                                @endif


                                @if($formErrors->has('captured_image'))
                                    <p class="field-error">
                                        {{ $formErrors->first('captured_image') }}
                                    </p>
                                @endif

                            </div>

                        </div>


                        {{-- =================================================
                             ARSIP FISIK
                        ================================================== --}}

                        <div class="attachment-card">

                            <div class="attachment-header">

                                <div class="attachment-header-left">

                                    <div class="attachment-icon physical">

                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                            />
                                        </svg>

                                    </div>


                                    <div class="min-w-0">

                                        <h3 class="attachment-title">
                                            Lokasi Arsip Fisik
                                        </h3>

                                        <p class="attachment-description">
                                            Posisi penyimpanan dokumen fisik.
                                        </p>

                                    </div>

                                </div>


                                <span class="attachment-badge">
                                    Opsional
                                </span>

                            </div>


                            <div class="attachment-body">

                                <div class="physical-box">

                                    <div class="physical-icon">

                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.8"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                            />
                                        </svg>

                                    </div>


                                    <h4 class="physical-title">
                                        Detail Lokasi Penyimpanan
                                    </h4>


                                    <p class="physical-description">
                                        Masukkan posisi rak, lemari, box,
                                        atau map tempat arsip fisik disimpan.
                                    </p>


                                    <div class="physical-field">

                                        <label
                                            for="lokasi_arsip_fisik"
                                            class="physical-label"
                                        >
                                            Posisi Rak / Lemari / Box
                                        </label>


                                        <input
                                            id="lokasi_arsip_fisik"
                                            name="lokasi_arsip_fisik"
                                            type="text"
                                            maxlength="255"
                                            value="{{ old('lokasi_arsip_fisik') }}"
                                            placeholder="Contoh: Rak A-3 Box 12"
                                            class="control {{ $formErrors->has('lokasi_arsip_fisik') ? 'control-error' : '' }}"
                                        >


                                        @if($formErrors->has('lokasi_arsip_fisik'))
                                            <p class="field-error">
                                                {{ $formErrors->first('lokasi_arsip_fisik') }}
                                            </p>
                                        @endif

                                    </div>


                                    <div class="physical-example">

                                        <p>
                                            <strong>Contoh:</strong>
                                            Rak A-3 Box 12 atau
                                            Lemari B-2 Map 07.
                                        </p>

                                    </div>


                                    <div class="physical-note">

                                        <p>
                                            Lokasi fisik hanya mencatat posisi
                                            dokumen asli dan tidak memengaruhi
                                            berkas digital.
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

                <a
                    href="{{ route('surat-masuk.index') }}"
                    class="footer-btn footer-cancel"
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

                    Batal

                </a>


                <button
                    id="submit-btn"
                    type="submit"
                    class="footer-btn footer-submit"
                >

                    <svg
                        id="submit-icon"
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


                    <svg
                        id="submit-loading"
                        class="hidden h-3.5 w-3.5 animate-spin"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        />

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                        />
                    </svg>


                    <span id="submit-text">
                        Simpan Surat Masuk
                    </span>

                </button>

            </div>

        </div>

    </form>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    'use strict';

    /* =========================================================
       ELEMENT
    ========================================================= */

    const form =
        document.getElementById('form-surat');

    const btnUpload =
        document.getElementById('btn-upload');

    const btnCamera =
        document.getElementById('btn-camera');

    const uploadPanel =
        document.getElementById('upload-panel');

    const cameraPanel =
        document.getElementById('camera-panel');

    const fileInput =
        document.getElementById('lampiran_file');

    const uploadBox =
        document.getElementById('upload-box');

    const uploadTitle =
        document.getElementById('upload-title');

    const selectedFile =
        document.getElementById('selected-file');

    const selectedFileName =
        document.getElementById('selected-file-name');

    const selectedFileSize =
        document.getElementById('selected-file-size');

    const clearFileBtn =
        document.getElementById('clear-file-btn');

    const compressionStatus =
        document.getElementById('file-compression-status');

    const video =
        document.getElementById('video');

    const imagePreview =
        document.getElementById('image-preview');

    const capturedImage =
        document.getElementById('captured_image');

    const startCamBtn =
        document.getElementById('start-cam-btn');

    const captureBtn =
        document.getElementById('capture-btn');

    const stopCamBtn =
        document.getElementById('stop-cam-btn');

    const cameraPlaceholder =
        document.getElementById('camera-placeholder');

    const cameraPlaceholderText =
        document.getElementById('camera-placeholder-text');

    const cameraError =
        document.getElementById('camera-error');

    const snapshotPreview =
        document.getElementById('snapshot-preview');

    const submitBtn =
        document.getElementById('submit-btn');

    const submitIcon =
        document.getElementById('submit-icon');

    const submitLoading =
        document.getElementById('submit-loading');

    const submitText =
        document.getElementById('submit-text');


    if (
        !form ||
        !fileInput ||
        !capturedImage
    ) {
        return;
    }


    /* =========================================================
       CONFIG
    ========================================================= */

    const MAX_FILE_SIZE =
        10 * 1024 * 1024;

    const IMAGE_TARGET_SIZE =
        2.5 * 1024 * 1024;

    const IMAGE_MAX_SIZE =
        5 * 1024 * 1024;

    const IMAGE_MAX_DIMENSION =
        2200;

    const IMAGE_MIN_DIMENSION =
        1000;

    const JPEG_QUALITIES = [
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
        0.42
    ];

    const CAMERA_MAX_WIDTH =
        1600;

    const CAMERA_MAX_HEIGHT =
        1600;

    const CAMERA_TARGET_SIZE =
        2.5 * 1024 * 1024;

    const CAMERA_MAX_DATA_SIZE =
        7 * 1024 * 1024;

    let cameraStream =
        null;

    let submitting =
        false;


    /* =========================================================
       UTILITY
    ========================================================= */

    function formatFileSize(bytes) {

        if (
            !Number.isFinite(bytes) ||
            bytes <= 0
        ) {
            return '0 KB';
        }

        if (
            bytes <
            1024 * 1024
        ) {
            return (
                (bytes / 1024).toFixed(1) +
                ' KB'
            );
        }

        return (
            (
                bytes /
                (1024 * 1024)
            ).toFixed(2) +
            ' MB'
        );
    }


    function getReductionPercent(
        originalSize,
        compressedSize
    ) {

        if (
            !originalSize ||
            originalSize <= 0
        ) {
            return 0;
        }

        return Math.max(
            0,
            Math.round(
                (
                    1 -
                    compressedSize /
                    originalSize
                ) *
                100
            )
        );
    }


    function getExtension(file) {

        return String(
            file?.name || ''
        )
            .split('.')
            .pop()
            .toLowerCase();
    }


    /* =========================================================
       MODE
    ========================================================= */

    function setModeButton(
        button,
        active
    ) {

        if (!button) {
            return;
        }

        button.classList.toggle(
            'active',
            active
        );

        button.setAttribute(
            'aria-selected',
            active
                ? 'true'
                : 'false'
        );
    }


    function setUploadMode() {

        stopCamera();

        uploadPanel.classList.remove(
            'hidden'
        );

        cameraPanel.classList.add(
            'hidden'
        );

        setModeButton(
            btnUpload,
            true
        );

        setModeButton(
            btnCamera,
            false
        );

        hideCameraError();
    }


    function setCameraMode() {

        uploadPanel.classList.add(
            'hidden'
        );

        cameraPanel.classList.remove(
            'hidden'
        );

        setModeButton(
            btnUpload,
            false
        );

        setModeButton(
            btnCamera,
            true
        );

        clearSelectedFile();
        hideCameraError();
    }


    btnUpload?.addEventListener(
        'click',
        setUploadMode
    );

    btnCamera?.addEventListener(
        'click',
        setCameraMode
    );


    /* =========================================================
       CAMERA ERROR
    ========================================================= */

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


    function hideCameraError() {

        if (!cameraError) {
            return;
        }

        cameraError.textContent =
            '';

        cameraError.classList.add(
            'hidden'
        );
    }


    /* =========================================================
       FILE DISPLAY
    ========================================================= */

    function showSelectedFile(
        file,
        note
    ) {

        selectedFile.classList.remove(
            'hidden'
        );

        selectedFileName.textContent =
            file.name;

        selectedFileSize.textContent =
            formatFileSize(
                file.size
            ) +
            (
                note
                    ? ' • ' + note
                    : ''
            );

        uploadBox.classList.add(
            'has-file'
        );

        uploadTitle.textContent =
            'File berhasil dipilih';
    }


    function clearSelectedFile() {

        fileInput.value =
            '';

        selectedFile.classList.add(
            'hidden'
        );

        selectedFileName.textContent =
            '';

        selectedFileSize.textContent =
            '';

        compressionStatus.classList.add(
            'hidden'
        );

        compressionStatus.textContent =
            '';

        uploadBox.classList.remove(
            'has-file'
        );

        uploadTitle.textContent =
            'Klik untuk memilih file';
    }


    clearFileBtn?.addEventListener(
        'click',
        clearSelectedFile
    );


    /* =========================================================
       IMAGE LOADER
    ========================================================= */

    function loadImage(file) {

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


    /* =========================================================
       DIMENSION
    ========================================================= */

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
                Math.round(
                    width * scale
                )
            ),

            height: Math.max(
                1,
                Math.round(
                    height * scale
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
            2000,
            1800,
            1600,
            1400,
            1200,
            IMAGE_MIN_DIMENSION
        ];

        const result = [];

        maxDimensions.forEach(
            function (
                maxDimension
            ) {

                const size =
                    calculateDimensions(
                        width,
                        height,
                        maxDimension
                    );

                if (
                    size.width <
                        IMAGE_MIN_DIMENSION &&
                    size.height <
                        IMAGE_MIN_DIMENSION
                ) {
                    return;
                }

                const duplicate =
                    result.some(
                        function (
                            existing
                        ) {

                            return (
                                existing.width ===
                                    size.width &&
                                existing.height ===
                                    size.height
                            );
                        }
                    );

                if (!duplicate) {
                    result.push(
                        size
                    );
                }
            }
        );

        return result;
    }


    /* =========================================================
       CANVAS TO FILE
    ========================================================= */

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
                    function (
                        blob
                    ) {

                        if (!blob) {

                            reject(
                                new Error(
                                    'Browser gagal membuat file hasil kompresi.'
                                )
                            );

                            return;
                        }

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

                        const fileName =
                            (
                                baseName ||
                                'lampiran'
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


    /* =========================================================
       COMPRESS IMAGE
    ========================================================= */

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

        const dimensionList =
            buildDimensionList(
                width,
                height
            );

        let smallest =
            null;

        for (
            const dimension
            of dimensionList
        ) {

            for (
                const quality
                of JPEG_QUALITIES
            ) {

                const compressed =
                    await canvasToFile(
                        image,
                        dimension.width,
                        dimension.height,
                        quality,
                        file.name
                    );

                if (
                    !smallest ||
                    compressed.size <
                        smallest.size
                ) {
                    smallest =
                        compressed;
                }

                if (
                    compressed.size <=
                    IMAGE_TARGET_SIZE
                ) {
                    return compressed;
                }
            }
        }

        if (
            smallest &&
            smallest.size <=
            IMAGE_MAX_SIZE
        ) {
            return smallest;
        }

        throw new Error(
            'Gambar masih terlalu besar setelah kompresi.'
        );
    }


    /* =========================================================
       FILE CHANGE
    ========================================================= */

    fileInput.addEventListener(
        'change',
        async function () {

            const file =
                fileInput.files?.[0];

            if (!file) {
                return;
            }

            const extension =
                getExtension(file);

            if (
                ![
                    'pdf',
                    'jpg',
                    'jpeg',
                    'png'
                ].includes(
                    extension
                )
            ) {

                clearSelectedFile();

                alert(
                    'Format file tidak didukung.\n\n' +
                    'Gunakan PDF, JPG, JPEG, atau PNG.'
                );

                return;
            }

            if (
                file.size <= 0
            ) {

                clearSelectedFile();

                alert(
                    'File kosong atau tidak valid.'
                );

                return;
            }

            if (
                file.size >
                MAX_FILE_SIZE
            ) {

                clearSelectedFile();

                alert(
                    'Ukuran file terlalu besar.\n\n' +
                    'Maksimal 10 MB.'
                );

                return;
            }

            if (
                extension === 'pdf'
            ) {

                capturedImage.value =
                    '';

                clearCapturedPreview();

                showSelectedFile(
                    file,
                    'PDF • tanpa kompresi'
                );

                compressionStatus.textContent =
                    '✓ PDF tetap PDF dan tidak dikompres.';

                compressionStatus.classList.remove(
                    'hidden'
                );

                return;
            }

            compressionStatus.textContent =
                'Memproses gambar dan melakukan kompresi...';

            compressionStatus.classList.remove(
                'hidden'
            );

            try {

                const originalSize =
                    file.size;

                const compressed =
                    await compressImageFile(
                        file
                    );

                let finalFile =
                    compressed;

                if (
                    compressed.size >=
                    originalSize
                ) {
                    finalFile =
                        file;
                }

                if (
                    finalFile.size >
                    MAX_FILE_SIZE
                ) {

                    clearSelectedFile();

                    alert(
                        'Gambar masih melebihi batas 10 MB setelah kompresi.'
                    );

                    return;
                }

                const dataTransfer =
                    new DataTransfer();

                dataTransfer.items.add(
                    finalFile
                );

                fileInput.files =
                    dataTransfer.files;

                capturedImage.value =
                    '';

                clearCapturedPreview();

                const reduction =
                    getReductionPercent(
                        originalSize,
                        finalFile.size
                    );

                if (
                    finalFile === file
                ) {

                    showSelectedFile(
                        finalFile,
                        'Gambar asli digunakan • ' +
                        formatFileSize(
                            finalFile.size
                        )
                    );

                    compressionStatus.textContent =
                        '✓ File asli digunakan karena hasil kompresi tidak lebih kecil.';

                } else {

                    showSelectedFile(
                        finalFile,
                        'Dikompres ' +
                        reduction +
                        '% • ' +
                        formatFileSize(
                            originalSize
                        ) +
                        ' → ' +
                        formatFileSize(
                            finalFile.size
                        )
                    );

                    compressionStatus.textContent =
                        '✓ Gambar berhasil dikompres menjadi JPG.';
                }

                compressionStatus.classList.remove(
                    'hidden'
                );

            } catch (error) {

                console.error(
                    'Image compression error:',
                    error
                );

                clearSelectedFile();

                alert(
                    error?.message ||
                    'Gagal memproses gambar.'
                );
            }
        }
    );


    /* =========================================================
       CAMERA
    ========================================================= */

    startCamBtn?.addEventListener(
        'click',
        startCamera
    );


    async function startCamera() {

        hideCameraError();

        if (
            !window.isSecureContext
        ) {

            const message =
                'Kamera membutuhkan HTTPS.';

            showCameraError(
                message
            );

            cameraPlaceholder.classList.remove(
                'hidden'
            );

            if (cameraPlaceholderText) {
                cameraPlaceholderText.textContent =
                    message;
            }

            return;
        }

        if (
            !navigator.mediaDevices ||
            !navigator.mediaDevices.getUserMedia
        ) {

            showCameraError(
                'Browser tidak mendukung akses kamera.'
            );

            return;
        }

        stopCamera();

        try {

            cameraStream =
                await navigator.mediaDevices.getUserMedia({

                    video: {
                        facingMode: {
                            ideal:
                                'environment'
                        },

                        width: {
                            ideal:
                                CAMERA_MAX_WIDTH
                        },

                        height: {
                            ideal:
                                CAMERA_MAX_HEIGHT
                        }
                    },

                    audio: false
                });

            video.srcObject =
                cameraStream;

            await video.play();

            video.classList.remove(
                'hidden'
            );

            imagePreview.hidden =
                true;

            cameraPlaceholder.classList.add(
                'hidden'
            );

            startCamBtn.classList.add(
                'hidden'
            );

            captureBtn.classList.remove(
                'hidden'
            );

            stopCamBtn.classList.remove(
                'hidden'
            );

            clearCapturedPreview();

        } catch (error) {

            console.error(
                'Camera error:',
                error
            );

            let message =
                'Kamera tidak dapat digunakan.';

            if (
                error.name ===
                    'NotAllowedError' ||
                error.name ===
                    'PermissionDeniedError'
            ) {

                message =
                    'Akses kamera ditolak. Izinkan kamera pada browser.';

            } else if (
                error.name ===
                    'NotFoundError' ||
                error.name ===
                    'DevicesNotFoundError'
            ) {

                message =
                    'Kamera tidak ditemukan.';

            } else if (
                error.name ===
                    'NotReadableError'
            ) {

                message =
                    'Kamera sedang digunakan aplikasi lain.';

            } else if (
                error.name ===
                    'SecurityError'
            ) {

                message =
                    'Akses kamera diblokir oleh browser.';
            }

            showCameraError(
                message
            );
        }
    }


    /* =========================================================
       CAPTURE
    ========================================================= */

    captureBtn?.addEventListener(
        'click',
        function () {

            hideCameraError();

            if (
                !cameraStream ||
                !video.videoWidth ||
                !video.videoHeight
            ) {

                showCameraError(
                    'Kamera belum siap. Tunggu beberapa saat.'
                );

                return;
            }

            try {

                const imageData =
                    buildCameraImage();

                if (
                    imageData.length >
                    CAMERA_MAX_DATA_SIZE
                ) {

                    throw new Error(
                        'Hasil scan terlalu besar.'
                    );
                }

                capturedImage.value =
                    imageData;

                imagePreview.src =
                    imageData;

                imagePreview.hidden =
                    false;

                video.classList.add(
                    'hidden'
                );

                cameraPlaceholder.classList.add(
                    'hidden'
                );

                captureBtn.classList.add(
                    'hidden'
                );

                startCamBtn.classList.add(
                    'hidden'
                );

                stopCamBtn.classList.add(
                    'hidden'
                );

                const size =
                    estimateBinarySize(
                        imageData
                    );

                snapshotPreview.classList.remove(
                    'hidden'
                );

                snapshotPreview.textContent =
                    '✓ Hasil scan siap disimpan (' +
                    formatFileSize(size) +
                    ').';

                clearSelectedFile();

                compressionStatus.textContent =
                    '✓ Hasil scan sudah dikompres menjadi JPG.';

                compressionStatus.classList.remove(
                    'hidden'
                );

                stopCamera();

                cameraPlaceholder.classList.add(
                    'hidden'
                );

            } catch (error) {

                console.error(
                    'Capture error:',
                    error
                );

                showCameraError(
                    error?.message ||
                    'Gagal mengambil gambar.'
                );
            }
        }
    );


    /* =========================================================
       BUILD CAMERA IMAGE
    ========================================================= */

    function buildCameraImage() {

        const canvas =
            document.createElement(
                'canvas'
            );

        const dimensions =
            calculateDimensions(
                video.videoWidth,
                video.videoHeight,
                CAMERA_MAX_WIDTH
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
            video,
            0,
            0,
            canvas.width,
            canvas.height
        );

        let quality =
            0.82;

        let currentCanvas =
            canvas;

        let dataUrl =
            currentCanvas.toDataURL(
                'image/jpeg',
                quality
            );

        while (
            estimateBinarySize(
                dataUrl
            ) >
                CAMERA_TARGET_SIZE &&
            quality >
                0.40
        ) {

            quality -=
                0.06;

            dataUrl =
                currentCanvas.toDataURL(
                    'image/jpeg',
                    quality
                );
        }

        let attempt =
            0;

        while (
            estimateBinarySize(
                dataUrl
            ) >
                CAMERA_TARGET_SIZE &&
            attempt < 4
        ) {

            attempt++;

            const smaller =
                document.createElement(
                    'canvas'
                );

            smaller.width =
                Math.max(
                    1,
                    Math.round(
                        currentCanvas.width *
                        0.75
                    )
                );

            smaller.height =
                Math.max(
                    1,
                    Math.round(
                        currentCanvas.height *
                        0.75
                    )
                );

            const smallerContext =
                smaller.getContext(
                    '2d',
                    {
                        alpha: false
                    }
                );

            if (!smallerContext) {

                throw new Error(
                    'Canvas resize tidak tersedia.'
                );
            }

            smallerContext.fillStyle =
                '#ffffff';

            smallerContext.fillRect(
                0,
                0,
                smaller.width,
                smaller.height
            );

            smallerContext.imageSmoothingEnabled =
                true;

            smallerContext.imageSmoothingQuality =
                'high';

            smallerContext.drawImage(
                currentCanvas,
                0,
                0,
                smaller.width,
                smaller.height
            );

            currentCanvas =
                smaller;

            dataUrl =
                currentCanvas.toDataURL(
                    'image/jpeg',
                    0.58
                );
        }

        return dataUrl;
    }


    /* =========================================================
       BASE64 SIZE
    ========================================================= */

    function estimateBinarySize(
        dataUrl
    ) {

        const comma =
            dataUrl.indexOf(',');

        if (
            comma === -1
        ) {
            return 0;
        }

        const base64 =
            dataUrl.substring(
                comma + 1
            );

        const padding =
            base64.endsWith('==')
                ? 2
                : base64.endsWith('=')
                    ? 1
                    : 0;

        return Math.floor(
            (
                base64.length *
                3
            ) / 4
        ) - padding;
    }


    /* =========================================================
       CLEAR CAMERA PREVIEW
    ========================================================= */

    function clearCapturedPreview() {

        capturedImage.value =
            '';

        imagePreview.src =
            '';

        imagePreview.hidden =
            true;

        snapshotPreview.classList.add(
            'hidden'
        );

        snapshotPreview.textContent =
            '';
    }


    /* =========================================================
       STOP CAMERA
    ========================================================= */

    stopCamBtn?.addEventListener(
        'click',
        function () {

            stopCamera();

            if (
                capturedImage.value
            ) {

                cameraPlaceholder.classList.add(
                    'hidden'
                );

                imagePreview.hidden =
                    false;

                resetCameraButtons();

            } else {

                cameraPlaceholder.classList.remove(
                    'hidden'
                );

                if (
                    cameraPlaceholderText
                ) {
                    cameraPlaceholderText.textContent =
                        'Kamera belum aktif';
                }

                resetCameraButtons();
            }
        }
    );


    function stopCamera() {

        if (cameraStream) {

            cameraStream
                .getTracks()
                .forEach(
                    function (
                        track
                    ) {
                        track.stop();
                    }
                );

            cameraStream =
                null;
        }

        if (video) {
            video.srcObject =
                null;
        }

        if (
            !capturedImage.value
        ) {
            cameraPlaceholder.classList.remove(
                'hidden'
            );
        }
    }


    function resetCameraButtons() {

        startCamBtn.classList.remove(
            'hidden'
        );

        captureBtn.classList.add(
            'hidden'
        );

        stopCamBtn.classList.add(
            'hidden'
        );
    }


    /* =========================================================
       SUBMIT
    ========================================================= */

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

            const file =
                fileInput.files?.[0];

            const hasFile =
                !!file;

            const hasCamera =
                capturedImage.value.trim() !== '';


            if (
                !hasFile &&
                !hasCamera
            ) {

                event.preventDefault();

                alert(
                    'Berkas digital wajib diupload atau discan menggunakan kamera.'
                );

                return;
            }


            if (
                hasFile &&
                hasCamera
            ) {

                event.preventDefault();

                alert(
                    'Gunakan salah satu metode saja: Upload File atau Scan Kamera.'
                );

                return;
            }


            if (
                hasFile &&
                file.size >
                    MAX_FILE_SIZE
            ) {

                event.preventDefault();

                alert(
                    'Ukuran file terlalu besar. Maksimal 10 MB.'
                );

                return;
            }


            if (
                hasCamera &&
                capturedImage.value.length >
                    CAMERA_MAX_DATA_SIZE
            ) {

                event.preventDefault();

                alert(
                    'Hasil scan terlalu besar. Silakan scan ulang.'
                );

                return;
            }


            submitting =
                true;

            stopCamera();

            submitBtn.disabled =
                true;

            submitIcon.classList.add(
                'hidden'
            );

            submitLoading.classList.remove(
                'hidden'
            );

            submitText.textContent =
                'Menyimpan...';
        }
    );


    /* =========================================================
       OLD CAMERA DATA
    ========================================================= */

    if (
        capturedImage.value
    ) {

        setCameraMode();

        cameraPlaceholder.classList.add(
            'hidden'
        );

        startCamBtn.classList.add(
            'hidden'
        );

        captureBtn.classList.add(
            'hidden'
        );

        stopCamBtn.classList.add(
            'hidden'
        );

        imagePreview.src =
            capturedImage.value;

        imagePreview.hidden =
            false;

        snapshotPreview.classList.remove(
            'hidden'
        );

        snapshotPreview.textContent =
            '✓ Hasil scan sebelumnya masih tersedia.';
    }


    /* =========================================================
       CLEANUP
    ========================================================= */

    window.addEventListener(
        'beforeunload',
        function () {
            stopCamera();
        }
    );

});
</script>

@endsection