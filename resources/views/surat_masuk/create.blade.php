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

.sm-create-page {
    width: 100%;
    max-width: 1040px;
    margin: 0 auto;
    padding: 12px 18px 32px;
    color: #334155;
}

.sm-create-page *,
.sm-create-page *::before,
.sm-create-page *::after {
    box-sizing: border-box;
}


/* ============================================================
   TOP ACTION
============================================================ */

.sm-top-action {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
}

.sm-back-btn {
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

.sm-back-btn:hover {
    border-color: #6366f1;
    background: #eef2ff;
    color: #4338ca;
}


/* ============================================================
   ERROR
============================================================ */

.sm-error-box {
    margin-bottom: 12px;
    padding: 11px 13px;
    border: 1.5px solid #fecaca;
    border-radius: 9px;
    background: #fff7f7;
}

.sm-error-inner {
    display: flex;
    align-items: flex-start;
    gap: 9px;
}

.sm-error-icon {
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

.sm-error-title {
    margin: 0;
    font-size: 10px;
    line-height: 1.35;
    font-weight: 800;
    color: #be123c;
}

.sm-error-list {
    margin: 3px 0 0;
    padding-left: 16px;
    font-size: 9px;
    line-height: 1.5;
    color: #e11d48;
}


/* ============================================================
   MAIN CARD
============================================================ */

.sm-main-card {
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

.sm-system-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 15px 18px;
    border-bottom: 1.5px solid #c7d2fe;
    background:
        linear-gradient(
            135deg,
            #f8fbff 0%,
            #eef2ff 52%,
            #ecfdf5 100%
        );
}

.sm-system-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}

.sm-system-header-icon {
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

.sm-system-header-label {
    margin: 0;
    font-size: 8px;
    line-height: 1.2;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #4f46e5;
}

.sm-system-header-title {
    margin: 2px 0 0;
    font-size: 17px;
    line-height: 1.3;
    font-weight: 800;
    color: #1e293b;
}

.sm-system-header-subtitle {
    margin: 3px 0 0;
    font-size: 9px;
    line-height: 1.4;
    color: #64748b;
}

.sm-system-header-badge {
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

.sm-main-body {
    padding: 16px;
}


/* ============================================================
   SECTION
============================================================ */

.sm-section {
    width: 100%;
}

.sm-section + .sm-section {
    margin-top: 17px;
    padding-top: 17px;
    border-top: 1.5px solid #e2e8f0;
}

.sm-section-heading {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 10px;
}

.sm-section-marker {
    width: 4px;
    height: 27px;
    flex: 0 0 4px;
    margin-top: 1px;
    border-radius: 999px;
    background: #2563eb;
}

.sm-section-marker-indigo {
    background: #4f46e5;
}

.sm-section-title {
    margin: 0;
    font-size: 13px;
    line-height: 1.3;
    font-weight: 800;
    color: #1e293b;
}

.sm-section-description {
    margin: 3px 0 0;
    font-size: 8.5px;
    line-height: 1.4;
    color: #64748b;
}


/* ============================================================
   FIELD
============================================================ */

.sm-field-table {
    overflow: hidden;
    border: 1.5px solid #94a3b8;
    border-radius: 9px;
    background: #ffffff;
}

.sm-field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.sm-field {
    min-width: 0;
    padding: 11px 12px;
    border-right: 1.5px solid #cbd5e1;
    border-bottom: 1.5px solid #cbd5e1;
    background: #ffffff;
}

.sm-field:nth-child(2n) {
    border-right: 0;
}

.sm-field-full {
    grid-column: 1 / -1;
    border-right: 0;
}

.sm-field:last-child {
    border-bottom: 0;
}


/* ============================================================
   LABEL
============================================================ */

.sm-field-label,
.sm-physical-label {
    display: block;
    margin-bottom: 5px;
    font-size: 9px;
    line-height: 1.3;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .025em;
    color: #475569;
}

.sm-required {
    color: #dc2626;
}


/* ============================================================
   CONTROL
============================================================ */

.sm-control {
    width: 100%;
    min-height: 40px;
    padding: 8px 10px;
    border: 1.5px solid #94a3b8;
    border-radius: 7px;
    background: #ffffff;
    color: #1e293b;
    outline: none;
    font-size: 12px;
    line-height: 1.35;
    transition:
        border-color .15s ease,
        box-shadow .15s ease,
        background-color .15s ease;
}

input.sm-control,
select.sm-control {
    height: 40px;
    padding: 0 10px;
}

textarea.sm-control {
    min-height: 82px;
    padding: 9px 10px;
    line-height: 1.5;
    resize: vertical;
}

.sm-control::placeholder {
    color: #94a3b8;
}

.sm-control:hover {
    border-color: #64748b;
}

.sm-control:focus {
    border-color: #2563eb;
    box-shadow:
        0 0 0 3px rgba(37,99,235,.08);
}

.sm-control-error {
    border-color: #ef4444 !important;
    background: #fff7f7 !important;
}

.sm-field-error {
    margin: 4px 0 0;
    font-size: 8px;
    line-height: 1.4;
    font-weight: 700;
    color: #dc2626;
}

.sm-help {
    margin: 4px 0 0;
    font-size: 7.5px;
    line-height: 1.45;
    color: #94a3b8;
}


/* ============================================================
   ATTACHMENT GRID
============================================================ */

.sm-attachment-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    align-items: stretch;
}

.sm-attachment-card {
    min-width: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1.5px solid #94a3b8;
    border-radius: 9px;
    background: #ffffff;
}

.sm-attachment-header {
    min-height: 54px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 9px 11px;
    border-bottom: 1.5px solid #cbd5e1;
    background: #f8fafc;
}

.sm-attachment-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}

.sm-attachment-icon {
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

.sm-attachment-icon.physical {
    border-color: #c7d2fe;
    color: #4f46e5;
}

.sm-attachment-title {
    margin: 0;
    font-size: 10px;
    line-height: 1.3;
    font-weight: 800;
    color: #1e293b;
}

.sm-attachment-description {
    margin: 2px 0 0;
    font-size: 7.5px;
    line-height: 1.4;
    color: #64748b;
}

.sm-attachment-badge {
    flex-shrink: 0;
    padding: 4px 7px;
    border: 1px solid #cbd5e1;
    border-radius: 999px;
    background: #ffffff;
    color: #64748b;
    font-size: 7px;
    font-weight: 800;
    text-transform: uppercase;
    white-space: nowrap;
}

.sm-attachment-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 8px;
    padding: 10px;
}


/* ============================================================
   INFO BOX
============================================================ */

.sm-info-box {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    padding: 8px;
    border: 1px solid #bfdbfe;
    border-radius: 7px;
    background: #eff6ff;
}

.sm-info-box svg {
    width: 14px;
    height: 14px;
    flex: 0 0 14px;
    margin-top: 1px;
    color: #2563eb;
}

.sm-info-text {
    margin: 0;
    font-size: 7.5px;
    line-height: 1.5;
    color: #1d4ed8;
}

.sm-info-text strong {
    font-weight: 800;
}


/* ============================================================
   MODE
============================================================ */

.sm-mode-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px;
}

.sm-mode-btn {
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

.sm-mode-btn:hover {
    border-color: #818cf8;
    background: #eef2ff;
}

.sm-mode-btn.active {
    border-color: #4f46e5;
    background: #eef2ff;
    color: #4338ca;
}


/* ============================================================
   UPLOAD
============================================================ */

.sm-upload-box {
    position: relative;
    min-height: 110px;
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

.sm-upload-box:hover {
    border-color: #6366f1;
    background: #eef2ff;
}

.sm-upload-box.has-file {
    border-color: #34d399;
    background: #ecfdf5;
}

.sm-upload-icon {
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

.sm-upload-box.has-file .sm-upload-icon {
    background: #d1fae5;
    color: #059669;
}

.sm-upload-title {
    font-size: 8px;
    line-height: 1.3;
    font-weight: 800;
    color: #334155;
}

.sm-upload-format {
    margin-top: 2px;
    font-size: 7px;
    color: #64748b;
}

.sm-upload-limit {
    margin-top: 3px;
    padding: 3px 7px;
    border-radius: 999px;
    background: #dbeafe;
    color: #2563eb;
    font-size: 6.5px;
    font-weight: 700;
}

.sm-upload-input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    opacity: 0;
}


/* ============================================================
   SELECTED FILE
============================================================ */

.sm-selected-file {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px;
    border: 1.5px solid #a7f3d0;
    border-radius: 7px;
    background: #ecfdf5;
}

.sm-selected-file-icon {
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

.sm-selected-file-content {
    min-width: 0;
    flex: 1;
}

.sm-selected-file-title {
    margin: 0;
    font-size: 7px;
    line-height: 1.2;
    font-weight: 800;
    color: #047857;
}

.sm-selected-file-name {
    margin: 1px 0 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 7.5px;
    line-height: 1.3;
    font-weight: 700;
    color: #334155;
}

.sm-selected-file-size {
    margin: 1px 0 0;
    font-size: 6.5px;
    line-height: 1.3;
    color: #64748b;
}

.sm-clear-file-btn {
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

.sm-clear-file-btn:hover {
    background: #dcfce7;
}


/* ============================================================
   COMPRESSION
============================================================ */

.sm-compression-status {
    padding: 8px 9px;
    border: 1px solid #c7d2fe;
    border-radius: 7px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 7px;
    line-height: 1.55;
}

.sm-compression-status.success {
    border-color: #a7f3d0;
    background: #ecfdf5;
    color: #047857;
}

.sm-compression-status.warning {
    border-color: #fde68a;
    background: #fffbeb;
    color: #a16207;
}

.sm-compression-status.error {
    border-color: #fecaca;
    background: #fff1f2;
    color: #be123c;
}


/* ============================================================
   CAMERA
============================================================ */

.sm-camera-box {
    overflow: hidden;
    border: 1.5px solid #475569;
    border-radius: 8px;
    background: #0f172a;
}

.sm-camera-preview {
    position: relative;
    min-height: 220px;
    background: #0f172a;
}

.sm-camera-video {
    width: 100%;
    height: 220px;
    display: block;
    object-fit: contain;
    background: #0f172a;
}

.sm-camera-image {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    display: block;
    object-fit: contain;
    background: #0f172a;
}

.sm-camera-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
    background: #0f172a;
    text-align: center;
}

.sm-camera-placeholder-icon {
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

.sm-camera-placeholder-title {
    margin: 0;
    font-size: 8px;
    font-weight: 800;
    color: #cbd5e1;
}

.sm-camera-placeholder-desc {
    margin: 2px 0 0;
    font-size: 6.5px;
    line-height: 1.35;
    color: #64748b;
}

.sm-camera-error {
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

.sm-camera-frame {
    position: absolute;
    inset: 22px;
    border: 1px dashed rgba(255,255,255,.22);
    border-radius: 6px;
    pointer-events: none;
}

.sm-camera-actions {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 5px;
    padding: 6px;
    background: #111827;
}

.sm-camera-btn {
    min-height: 31px;
    padding: 0 5px;
    border-radius: 6px;
    font-size: 7px;
    font-weight: 800;
    cursor: pointer;
    transition: all .15s ease;
}

.sm-camera-btn:hover:not(:disabled) {
    filter: brightness(.96);
}

.sm-camera-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.sm-camera-btn-primary {
    border: 1px solid #4338ca;
    background: #4f46e5;
    color: #ffffff;
}

.sm-camera-btn-success {
    border: 1px solid #059669;
    background: #10b981;
    color: #ffffff;
}

.sm-camera-btn-secondary {
    border: 1px solid #64748b;
    background: #ffffff;
    color: #334155;
}


/* ============================================================
   SNAPSHOT
============================================================ */

.sm-snapshot-preview {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 7px;
    padding: 7px;
    border: 1.5px solid #a7f3d0;
    border-radius: 7px;
    background: #ecfdf5;
    color: #047857;
    font-size: 7px;
    font-weight: 800;
}

.sm-snapshot-text {
    min-width: 0;
    flex: 1;
    line-height: 1.45;
}

.sm-retake-btn {
    min-height: 29px;
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 0 9px;
    border: 1px solid #c7d2fe;
    border-radius: 6px;
    background: #ffffff;
    color: #4338ca;
    font-size: 7px;
    font-weight: 800;
    cursor: pointer;
}

.sm-retake-btn:hover {
    border-color: #818cf8;
    background: #eef2ff;
}


/* ============================================================
   PHYSICAL
============================================================ */

.sm-physical-box {
    display: flex;
    flex: 1;
    flex-direction: column;
    padding: 13px;
    border: 1.5px dashed #94a3b8;
    border-radius: 8px;
    background: #f8fafc;
}

.sm-physical-icon {
    width: 37px;
    height: 37px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    background: #e0e7ff;
    color: #4f46e5;
}

.sm-physical-title {
    margin: 7px 0 0;
    font-size: 10px;
    line-height: 1.3;
    font-weight: 800;
    color: #334155;
}

.sm-physical-description {
    margin: 3px 0 0;
    font-size: 7.5px;
    line-height: 1.5;
    color: #64748b;
}

.sm-physical-field {
    margin-top: 11px;
}

.sm-physical-example {
    margin-top: 6px;
    padding: 7px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
}

.sm-physical-example p {
    margin: 0;
    font-size: 7px;
    line-height: 1.45;
    color: #64748b;
}

.sm-physical-example strong {
    color: #334155;
}

.sm-physical-note {
    margin-top: auto;
    padding: 7px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
}

.sm-physical-note p {
    margin: 0;
    font-size: 7px;
    line-height: 1.45;
    color: #64748b;
}


/* ============================================================
   FOOTER
============================================================ */

.sm-form-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 7px;
    padding: 10px 14px;
    border-top: 1.5px solid #cbd5e1;
    background: #f8fafc;
}

.sm-footer-btn {
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

.sm-footer-cancel {
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
}

.sm-footer-cancel:hover {
    border-color: #94a3b8;
    background: #f1f5f9;
}

.sm-footer-submit {
    border: 1.5px solid #2563eb;
    background: #2563eb;
    color: #ffffff;
    box-shadow: 0 3px 7px rgba(37,99,235,.12);
}

.sm-footer-submit:hover:not(:disabled) {
    border-color: #1d4ed8;
    background: #1d4ed8;
}

.sm-footer-submit:disabled {
    opacity: .6;
    cursor: not-allowed;
}


/* ============================================================
   UTILITY
============================================================ */

.sm-hidden {
    display: none !important;
}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 820px) {
    .sm-create-page {
        max-width: 760px;
    }

    .sm-attachment-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {

    .sm-create-page {
        padding: 6px 9px 20px;
    }

    .sm-top-action {
        justify-content: stretch;
    }

    .sm-back-btn {
        width: 100%;
    }

    .sm-system-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .sm-system-header-badge {
        align-self: flex-start;
    }

    .sm-main-body {
        padding: 11px;
    }

    .sm-field-grid {
        grid-template-columns: 1fr;
    }

    .sm-field,
    .sm-field:nth-child(2n) {
        padding: 10px;
        border-right: 0;
        border-bottom: 1.5px solid #cbd5e1;
    }

    .sm-field-full {
        grid-column: auto;
    }

    .sm-mode-grid {
        grid-template-columns: 1fr;
    }

    .sm-camera-actions {
        grid-template-columns: 1fr;
    }

    .sm-snapshot-preview {
        align-items: stretch;
        flex-direction: column;
    }

    .sm-retake-btn {
        width: 100%;
    }

    .sm-form-footer {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .sm-footer-btn {
        width: 100%;
    }
}

@media (max-width: 420px) {

    .sm-system-header {
        padding: 12px;
    }

    .sm-system-header-title {
        font-size: 15px;
    }

    .sm-main-body {
        padding: 9px;
    }

    .sm-field {
        padding: 9px;
    }

    .sm-attachment-body {
        padding: 8px;
    }

    .sm-control {
        font-size: 11px;
    }

    .sm-camera-preview {
        min-height: 200px;
    }

    .sm-camera-video {
        height: 200px;
    }
}
</style>


<div class="sm-create-page">

    {{-- =========================================================
         KEMBALI
    ========================================================== --}}

    <div class="sm-top-action">

        <a
            href="{{ route('surat-masuk.index') }}"
            class="sm-back-btn"
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

        <div class="sm-error-box">

            <div class="sm-error-inner">

                <div class="sm-error-icon">

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
                            d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l8.82-15a2 2 0 003.42 0z"
                        />
                    </svg>

                </div>

                <div>

                    <p class="sm-error-title">
                        Data belum dapat disimpan.
                    </p>

                    <ul class="sm-error-list">

                        @foreach($formErrors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

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
        id="form-surat-masuk"
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

        <div class="sm-main-card">

            {{-- =================================================
                 HEADER
            ================================================== --}}

            <div class="sm-system-header">

                <div class="sm-system-header-left">

                    <div class="sm-system-header-icon">

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

                        <p class="sm-system-header-label">
                            Sistem Arsip Masuk
                        </p>

                        <h1 class="sm-system-header-title">
                            Input Surat Masuk
                        </h1>

                        <p class="sm-system-header-subtitle">
                            Masukkan data surat masuk untuk disimpan ke dalam arsip digital.
                        </p>

                    </div>

                </div>

                <span class="sm-system-header-badge">
                    Arsip Digital
                </span>

            </div>


            {{-- =================================================
                 BODY
            ================================================== --}}

            <div class="sm-main-body">

                {{-- =================================================
                     INFORMASI UTAMA
                ================================================== --}}

                <section class="sm-section">

                    <div class="sm-section-heading">

                        <div class="sm-section-marker"></div>

                        <div>

                            <h2 class="sm-section-title">
                                Informasi Utama Surat
                            </h2>

                            <p class="sm-section-description">
                                Lengkapi identitas dan informasi utama surat masuk.
                            </p>

                        </div>

                    </div>


                    <div class="sm-field-table">

                        <div class="sm-field-grid">

                            {{-- NOMOR SURAT --}}

                            <div class="sm-field">

                                <label
                                    for="nomor_surat"
                                    class="sm-field-label"
                                >
                                    Nomor Surat
                                    <span class="sm-required">*</span>
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
                                    class="sm-control {{ $formErrors->has('nomor_surat') ? 'sm-control-error' : '' }}"
                                >

                                @if($formErrors->has('nomor_surat'))

                                    <p class="sm-field-error">
                                        {{ $formErrors->first('nomor_surat') }}
                                    </p>

                                @endif

                            </div>


                            {{-- PENGIRIM --}}

                            <div class="sm-field">

                                <label
                                    for="pengirim"
                                    class="sm-field-label"
                                >
                                    Instansi Pengirim
                                    <span class="sm-required">*</span>
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
                                    class="sm-control {{ $formErrors->has('pengirim') ? 'sm-control-error' : '' }}"
                                >

                                @if($formErrors->has('pengirim'))

                                    <p class="sm-field-error">
                                        {{ $formErrors->first('pengirim') }}
                                    </p>

                                @endif

                            </div>


                            {{-- TANGGAL SURAT --}}

                            <div class="sm-field">

                                <label
                                    for="tanggal_surat"
                                    class="sm-field-label"
                                >
                                    Tanggal Surat
                                    <span class="sm-required">*</span>
                                </label>

                                <input
                                    id="tanggal_surat"
                                    name="tanggal_surat"
                                    type="date"
                                    value="{{ old('tanggal_surat') }}"
                                    required
                                    class="sm-control {{ $formErrors->has('tanggal_surat') ? 'sm-control-error' : '' }}"
                                >

                                @if($formErrors->has('tanggal_surat'))

                                    <p class="sm-field-error">
                                        {{ $formErrors->first('tanggal_surat') }}
                                    </p>

                                @endif

                            </div>


                            {{-- TANGGAL TERIMA --}}

                            <div class="sm-field">

                                <label
                                    for="tanggal_terima"
                                    class="sm-field-label"
                                >
                                    Tanggal Diterima
                                    <span class="sm-required">*</span>
                                </label>

                                <input
                                    id="tanggal_terima"
                                    name="tanggal_terima"
                                    type="date"
                                    value="{{ old('tanggal_terima', now()->format('Y-m-d')) }}"
                                    required
                                    class="sm-control {{ $formErrors->has('tanggal_terima') ? 'sm-control-error' : '' }}"
                                >

                                @if($formErrors->has('tanggal_terima'))

                                    <p class="sm-field-error">
                                        {{ $formErrors->first('tanggal_terima') }}
                                    </p>

                                @endif

                            </div>


                            {{-- KATEGORI --}}

                            <div class="sm-field">

                                <label
                                    for="kategori_surat_id"
                                    class="sm-field-label"
                                >
                                    Kategori Surat
                                    <span class="sm-required">*</span>
                                </label>

                                <select
                                    id="kategori_surat_id"
                                    name="kategori_surat_id"
                                    required
                                    class="sm-control {{ $formErrors->has('kategori_surat_id') ? 'sm-control-error' : '' }}"
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

                                    <p class="sm-field-error">
                                        {{ $formErrors->first('kategori_surat_id') }}
                                    </p>

                                @endif

                            </div>


                            {{-- STATUS --}}

                            <div class="sm-field">

                                <label
                                    for="status"
                                    class="sm-field-label"
                                >
                                    Status Surat
                                    <span class="sm-required">*</span>
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    required
                                    class="sm-control {{ $formErrors->has('status') ? 'sm-control-error' : '' }}"
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

                                    <p class="sm-field-error">
                                        {{ $formErrors->first('status') }}
                                    </p>

                                @endif

                            </div>


                            {{-- PERIHAL --}}

                            <div class="sm-field sm-field-full">

                                <label
                                    for="perihal"
                                    class="sm-field-label"
                                >
                                    Perihal
                                    <span class="sm-required">*</span>
                                </label>

                                <textarea
                                    id="perihal"
                                    name="perihal"
                                    rows="3"
                                    maxlength="1000"
                                    required
                                    placeholder="Tuliskan perihal surat secara jelas"
                                    class="sm-control {{ $formErrors->has('perihal') ? 'sm-control-error' : '' }}"
                                >{{ old('perihal') }}</textarea>

                                @if($formErrors->has('perihal'))

                                    <p class="sm-field-error">
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

                <section class="sm-section">

                    <div class="sm-section-heading">

                        <div class="sm-section-marker sm-section-marker-indigo"></div>

                        <div>

                            <h2 class="sm-section-title">
                                Lampiran Dokumen & Arsip Fisik
                            </h2>

                            <p class="sm-section-description">
                                Upload dokumen atau scan menggunakan kamera,
                                lalu isi lokasi arsip fisiknya.
                            </p>

                        </div>

                    </div>


                    <div class="sm-attachment-grid">

                        {{-- =================================================
                             BERKAS DIGITAL
                        ================================================== --}}

                        <div class="sm-attachment-card">

                            <div class="sm-attachment-header">

                                <div class="sm-attachment-header-left">

                                    <div class="sm-attachment-icon">

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

                                        <h3 class="sm-attachment-title">
                                            Berkas Digital
                                        </h3>

                                        <p class="sm-attachment-description">
                                            PDF, JPG, JPEG, PNG · Maksimal 10 MB
                                        </p>

                                    </div>

                                </div>

                                <span
                                    class="sm-attachment-badge"
                                    style="color:#dc2626;border-color:#fecaca;background:#fff1f2;"
                                >
                                    Wajib
                                </span>

                            </div>


                            <div class="sm-attachment-body">

                                {{-- INFO KOMPRESI --}}

                                <div class="sm-info-box">

                                    <svg
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"
                                        />
                                    </svg>

                                    <p class="sm-info-text">

                                        <strong>Maksimal 10 MB.</strong>

                                        PDF tetap PDF.
                                        JPG/JPEG/PNG otomatis diproses,
                                        di-resize bila diperlukan,
                                        lalu dikompres menjadi JPG.

                                    </p>

                                </div>


                                {{-- MODE --}}

                                <div class="sm-mode-grid">

                                    <button
                                        type="button"
                                        id="sm-btn-upload"
                                        aria-selected="true"
                                        class="sm-mode-btn active"
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
                                        id="sm-btn-camera"
                                        aria-selected="false"
                                        class="sm-mode-btn"
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


                                {{-- =================================================
                                     UPLOAD PANEL
                                ================================================== --}}

                                <div id="sm-upload-panel">

                                    <label
                                        for="lampiran_file"
                                        id="sm-upload-box"
                                        class="sm-upload-box"
                                    >

                                        <span class="sm-upload-icon">

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
                                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                                />
                                            </svg>

                                        </span>

                                        <span
                                            id="sm-upload-title"
                                            class="sm-upload-title"
                                        >
                                            Klik untuk memilih file
                                        </span>

                                        <span class="sm-upload-format">
                                            PDF, JPG, JPEG, PNG
                                        </span>

                                        <span class="sm-upload-limit">
                                            Maksimal 10 MB
                                        </span>

                                        <input
                                            id="lampiran_file"
                                            name="lampiran_file"
                                            type="file"
                                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                            class="sm-upload-input"
                                        >

                                    </label>


                                    {{-- FILE TERPILIH --}}

                                    <div
                                        id="sm-selected-file"
                                        class="sm-selected-file sm-hidden"
                                    >

                                        <div class="sm-selected-file-icon">

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

                                        <div class="sm-selected-file-content">

                                            <p class="sm-selected-file-title">
                                                File siap diproses
                                            </p>

                                            <p
                                                id="sm-selected-file-name"
                                                class="sm-selected-file-name"
                                            ></p>

                                            <p
                                                id="sm-selected-file-size"
                                                class="sm-selected-file-size"
                                            ></p>

                                        </div>

                                        <button
                                            type="button"
                                            id="sm-clear-file-btn"
                                            class="sm-clear-file-btn"
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
                                        id="sm-compression-status"
                                        class="sm-compression-status sm-hidden"
                                    ></div>

                                </div>


                                {{-- =================================================
                                     CAMERA PANEL
                                ================================================== --}}

                                <div
                                    id="sm-camera-panel"
                                    class="sm-hidden"
                                >

                                    <div class="sm-camera-box">

                                        <div class="sm-camera-preview">

                                            <video
                                                id="sm-video"
                                                autoplay
                                                muted
                                                playsinline
                                                class="sm-camera-video"
                                            ></video>

                                            <img
                                                id="sm-image-preview"
                                                src=""
                                                alt="Preview hasil scan"
                                                class="sm-camera-image sm-hidden"
                                            >

                                            <div
                                                id="sm-camera-placeholder"
                                                class="sm-camera-placeholder"
                                            >

                                                <div>

                                                    <div class="sm-camera-placeholder-icon">

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

                                                    <p class="sm-camera-placeholder-title">
                                                        Kamera belum aktif
                                                    </p>

                                                    <p
                                                        id="sm-camera-placeholder-text"
                                                        class="sm-camera-placeholder-desc"
                                                    >
                                                        Aktifkan kamera untuk scan dokumen.
                                                    </p>

                                                </div>

                                            </div>

                                            <div
                                                id="sm-camera-error"
                                                class="sm-camera-error sm-hidden"
                                                role="alert"
                                            ></div>

                                            <div class="sm-camera-frame"></div>

                                        </div>


                                        <div class="sm-camera-actions">

                                            <button
                                                type="button"
                                                id="sm-start-camera"
                                                class="sm-camera-btn sm-camera-btn-primary"
                                            >
                                                Nyalakan Kamera
                                            </button>

                                            <button
                                                type="button"
                                                id="sm-capture"
                                                class="sm-camera-btn sm-camera-btn-success sm-hidden"
                                            >
                                                Ambil Foto
                                            </button>

                                            <button
                                                type="button"
                                                id="sm-stop-camera"
                                                class="sm-camera-btn sm-camera-btn-secondary sm-hidden"
                                            >
                                                Tutup Kamera
                                            </button>

                                        </div>

                                    </div>


                                    <input
                                        type="hidden"
                                        name="captured_image"
                                        id="sm-captured-image"
                                        value="{{ old('captured_image') }}"
                                    >


                                    <div
                                        id="sm-snapshot-preview"
                                        class="sm-snapshot-preview sm-hidden"
                                    >

                                        <div
                                            id="sm-snapshot-text"
                                            class="sm-snapshot-text"
                                        ></div>

                                        <button
                                            type="button"
                                            id="sm-retake"
                                            class="sm-retake-btn"
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
                                                    d="M4 4v5h5M20 20v-5h-5M5.5 15a7 7 0 0011.9 2.9L20 15M4 9l2.6-2.9A7 7 0 0118.5 9"
                                                />
                                            </svg>

                                            Scan Ulang

                                        </button>

                                    </div>

                                </div>


                                @if($formErrors->has('lampiran_file'))

                                    <p class="sm-field-error">
                                        {{ $formErrors->first('lampiran_file') }}
                                    </p>

                                @endif


                                @if($formErrors->has('captured_image'))

                                    <p class="sm-field-error">
                                        {{ $formErrors->first('captured_image') }}
                                    </p>

                                @endif

                            </div>

                        </div>


                        {{-- =================================================
                             ARSIP FISIK
                        ================================================== --}}

                        <div class="sm-attachment-card">

                            <div class="sm-attachment-header">

                                <div class="sm-attachment-header-left">

                                    <div class="sm-attachment-icon physical">

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

                                        <h3 class="sm-attachment-title">
                                            Lokasi Arsip Fisik
                                        </h3>

                                        <p class="sm-attachment-description">
                                            Posisi penyimpanan dokumen fisik.
                                        </p>

                                    </div>

                                </div>

                                <span class="sm-attachment-badge">
                                    Opsional
                                </span>

                            </div>


                            <div class="sm-attachment-body">

                                <div class="sm-physical-box">

                                    <div class="sm-physical-icon">

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

                                    <h4 class="sm-physical-title">
                                        Detail Lokasi Penyimpanan
                                    </h4>

                                    <p class="sm-physical-description">
                                        Masukkan posisi rak, lemari, box,
                                        atau map tempat arsip fisik disimpan.
                                    </p>

                                    <div class="sm-physical-field">

                                        <label
                                            for="lokasi_arsip_fisik"
                                            class="sm-physical-label"
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
                                            class="sm-control {{ $formErrors->has('lokasi_arsip_fisik') ? 'sm-control-error' : '' }}"
                                        >

                                        @if($formErrors->has('lokasi_arsip_fisik'))

                                            <p class="sm-field-error">
                                                {{ $formErrors->first('lokasi_arsip_fisik') }}
                                            </p>

                                        @endif

                                    </div>

                                    <div class="sm-physical-example">

                                        <p>
                                            <strong>Contoh:</strong>
                                            Rak A-3 Box 12 atau
                                            Lemari B-2 Map 07.
                                        </p>

                                    </div>

                                    <div class="sm-physical-note">

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

            <div class="sm-form-footer">

                <a
                    href="{{ route('surat-masuk.index') }}"
                    class="sm-footer-btn sm-footer-cancel"
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
                    id="sm-submit-btn"
                    type="submit"
                    class="sm-footer-btn sm-footer-submit"
                >

                    <svg
                        id="sm-submit-icon"
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
                        id="sm-submit-loading"
                        class="sm-hidden h-3.5 w-3.5"
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

                    <span id="sm-submit-text">
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
        document.getElementById('form-surat-masuk');

    const btnUpload =
        document.getElementById('sm-btn-upload');

    const btnCamera =
        document.getElementById('sm-btn-camera');

    const uploadPanel =
        document.getElementById('sm-upload-panel');

    const cameraPanel =
        document.getElementById('sm-camera-panel');

    const fileInput =
        document.getElementById('lampiran_file');

    const uploadBox =
        document.getElementById('sm-upload-box');

    const uploadTitle =
        document.getElementById('sm-upload-title');

    const selectedFile =
        document.getElementById('sm-selected-file');

    const selectedFileName =
        document.getElementById('sm-selected-file-name');

    const selectedFileSize =
        document.getElementById('sm-selected-file-size');

    const clearFileBtn =
        document.getElementById('sm-clear-file-btn');

    const compressionStatus =
        document.getElementById('sm-compression-status');

    const video =
        document.getElementById('sm-video');

    const imagePreview =
        document.getElementById('sm-image-preview');

    const capturedImage =
        document.getElementById('sm-captured-image');

    const startCameraBtn =
        document.getElementById('sm-start-camera');

    const captureBtn =
        document.getElementById('sm-capture');

    const stopCameraBtn =
        document.getElementById('sm-stop-camera');

    const retakeBtn =
        document.getElementById('sm-retake');

    const cameraPlaceholder =
        document.getElementById(
            'sm-camera-placeholder'
        );

    const cameraPlaceholderText =
        document.getElementById(
            'sm-camera-placeholder-text'
        );

    const cameraError =
        document.getElementById('sm-camera-error');

    const snapshotPreview =
        document.getElementById('sm-snapshot-preview');

    const snapshotText =
        document.getElementById('sm-snapshot-text');

    const submitBtn =
        document.getElementById('sm-submit-btn');

    const submitIcon =
        document.getElementById('sm-submit-icon');

    const submitLoading =
        document.getElementById('sm-submit-loading');

    const submitText =
        document.getElementById('sm-submit-text');


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
                ) * 100
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
                text,
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#2563eb'
            });

            return;
        }

        window.alert(text);
    }


    /* =========================================================
       CAMERA ERROR
    ========================================================= */

    function showCameraError(
        message
    ) {

        if (!cameraError) {
            return;
        }

        cameraError.textContent =
            message;

        cameraError.classList.remove(
            'sm-hidden'
        );
    }


    function hideCameraError() {

        if (!cameraError) {
            return;
        }

        cameraError.textContent =
            '';

        cameraError.classList.add(
            'sm-hidden'
        );
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

        clearCapturedPreview();

        uploadPanel.classList.remove(
            'sm-hidden'
        );

        cameraPanel.classList.add(
            'sm-hidden'
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
            'sm-hidden'
        );

        cameraPanel.classList.remove(
            'sm-hidden'
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
       SELECTED FILE
    ========================================================= */

    function showSelectedFile(
        file,
        note
    ) {

        selectedFile.classList.remove(
            'sm-hidden'
        );

        selectedFileName.textContent =
            file.name;

        selectedFileSize.textContent =
            formatFileSize(file.size) +
            (
                note
                    ? ' • ' + note
                    : ''
            );

        uploadBox.classList.add(
            'has-file'
        );

        uploadTitle.textContent =
            'File berhasil diproses';
    }


    function clearSelectedFile() {

        fileInput.value =
            '';

        selectedFile.classList.add(
            'sm-hidden'
        );

        selectedFileName.textContent =
            '';

        selectedFileSize.textContent =
            '';

        compressionStatus.classList.add(
            'sm-hidden'
        );

        compressionStatus.textContent =
            '';

        compressionStatus.classList.remove(
            'success',
            'warning',
            'error'
        );

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
                                    'Browser gagal membuat hasil kompresi.'
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

        const dimensions =
            buildDimensionList(
                width,
                height
            );

        let smallest =
            null;

        for (
            const dimension
            of dimensions
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

                showAlert(
                    'warning',
                    'Format file tidak didukung',
                    'Gunakan PDF, JPG, JPEG, atau PNG.'
                );

                return;
            }


            if (
                file.size <= 0
            ) {

                clearSelectedFile();

                showAlert(
                    'warning',
                    'File tidak valid',
                    'File kosong atau tidak dapat digunakan.'
                );

                return;
            }


            if (
                file.size >
                MAX_FILE_SIZE
            ) {

                clearSelectedFile();

                showAlert(
                    'warning',
                    'File terlalu besar',
                    'Ukuran file maksimal 10 MB.'
                );

                return;
            }


            /* =================================================
               PDF
            ================================================== */

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
                    '✓ PDF tetap PDF dan tidak dikompres. Ukuran file: ' +
                    formatFileSize(file.size) +
                    '.';

                compressionStatus.classList.remove(
                    'sm-hidden'
                );

                compressionStatus.classList.add(
                    'success'
                );

                return;
            }


            /* =================================================
               IMAGE
            ================================================== */

            compressionStatus.textContent =
                'Memproses gambar dan mencari ukuran hasil kompresi terbaik...';

            compressionStatus.classList.remove(
                'sm-hidden'
            );

            compressionStatus.classList.remove(
                'success',
                'warning',
                'error'
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

                    showAlert(
                        'error',
                        'Kompresi gagal',
                        'Hasil gambar masih melebihi batas maksimal 10 MB.'
                    );

                    return;
                }


                const transfer =
                    new DataTransfer();

                transfer.items.add(
                    finalFile
                );

                fileInput.files =
                    transfer.files;


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
                        'File asli digunakan • ' +
                        formatFileSize(
                            finalFile.size
                        )
                    );

                    compressionStatus.textContent =
                        '⚠ Hasil kompresi tidak lebih kecil. File asli digunakan. Ukuran: ' +
                        formatFileSize(
                            originalSize
                        ) +
                        '.';

                    compressionStatus.classList.add(
                        'warning'
                    );

                } else {

                    showSelectedFile(
                        finalFile,
                        'Hasil kompresi: ' +
                        formatFileSize(
                            finalFile.size
                        ) +
                        ' • Hemat ' +
                        reduction +
                        '%'
                    );

                    compressionStatus.innerHTML =
                        '✓ Kompresi berhasil.<br>' +
                        'Ukuran asli: <strong>' +
                        formatFileSize(
                            originalSize
                        ) +
                        '</strong> → ' +
                        'hasil kompresi: <strong>' +
                        formatFileSize(
                            finalFile.size
                        ) +
                        '</strong> ' +
                        '(' +
                        reduction +
                        '% lebih kecil).<br>' +
                        'Format akhir: <strong>JPG</strong>.';

                    compressionStatus.classList.add(
                        'success'
                    );
                }

            } catch (error) {

                console.error(
                    'Compression error:',
                    error
                );

                clearSelectedFile();

                showAlert(
                    'error',
                    'Gagal memproses gambar',
                    error?.message ||
                    'Browser gagal mengompres gambar.'
                );
            }
        }
    );


    /* =========================================================
       CAMERA
    ========================================================= */

    startCameraBtn?.addEventListener(
        'click',
        startCamera
    );


    async function startCamera() {

        hideCameraError();

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
                'Browser tidak mendukung akses kamera.'
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
                'sm-hidden'
            );

            imagePreview.classList.add(
                'sm-hidden'
            );

            imagePreview.src =
                '';

            cameraPlaceholder.classList.add(
                'sm-hidden'
            );

            startCameraBtn.classList.add(
                'sm-hidden'
            );

            captureBtn.classList.remove(
                'sm-hidden'
            );

            stopCameraBtn.classList.remove(
                'sm-hidden'
            );

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

                const binarySize =
                    estimateBinarySize(
                        imageData
                    );

                if (
                    binarySize >
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

                imagePreview.classList.remove(
                    'sm-hidden'
                );

                video.classList.add(
                    'sm-hidden'
                );

                cameraPlaceholder.classList.add(
                    'sm-hidden'
                );


                snapshotText.textContent =
                    '✓ Hasil scan siap disimpan • ukuran sekitar ' +
                    formatFileSize(
                        binarySize
                    ) +
                    '.';

                snapshotPreview.classList.remove(
                    'sm-hidden'
                );


                compressionStatus.innerHTML =
                    '✓ Scan kamera berhasil diproses.<br>' +
                    'Format: <strong>JPG</strong> • Ukuran hasil: <strong>' +
                    formatFileSize(
                        binarySize
                    ) +
                    '</strong>.';

                compressionStatus.classList.remove(
                    'sm-hidden'
                );

                compressionStatus.classList.remove(
                    'warning',
                    'error'
                );

                compressionStatus.classList.add(
                    'success'
                );


                clearSelectedFile();

                stopCamera();

                cameraPlaceholder.classList.add(
                    'sm-hidden'
                );

                startCameraBtn.classList.add(
                    'sm-hidden'
                );

                captureBtn.classList.add(
                    'sm-hidden'
                );

                stopCameraBtn.classList.add(
                    'sm-hidden'
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
                'Canvas tidak didukung browser.'
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

        let dataUrl =
            canvas.toDataURL(
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
                canvas.toDataURL(
                    'image/jpeg',
                    quality
                );
        }


        let currentCanvas =
            canvas;

        let attempt =
            0;


        while (
            estimateBinarySize(
                dataUrl
            ) >
                CAMERA_TARGET_SIZE &&
            attempt <
                4
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
        ) -
        padding;
    }


    /* =========================================================
       CLEAR CAMERA
    ========================================================= */

    function clearCapturedPreview() {

        capturedImage.value =
            '';

        imagePreview.src =
            '';

        imagePreview.classList.add(
            'sm-hidden'
        );

        snapshotPreview.classList.add(
            'sm-hidden'
        );

        snapshotText.textContent =
            '';
    }


    /* =========================================================
       RETAKE
    ========================================================= */

    retakeBtn?.addEventListener(
        'click',
        async function () {

            clearCapturedPreview();

            compressionStatus.textContent =
                '';

            compressionStatus.classList.add(
                'sm-hidden'
            );

            compressionStatus.classList.remove(
                'success',
                'warning',
                'error'
            );

            hideCameraError();

            setCameraMode();

            try {

                await startCamera();

            } catch (error) {

                showCameraError(
                    'Kamera gagal diaktifkan kembali.'
                );
            }
        }
    );


    /* =========================================================
       STOP CAMERA TRACKS
    ========================================================= */

    function stopCameraTracks() {

        if (!cameraStream) {
            return;
        }

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


    /* =========================================================
       STOP CAMERA
    ========================================================= */

    stopCameraBtn?.addEventListener(
        'click',
        function () {

            stopCamera();

            if (
                capturedImage.value
            ) {

                imagePreview.classList.remove(
                    'sm-hidden'
                );

                cameraPlaceholder.classList.add(
                    'sm-hidden'
                );

            } else {

                cameraPlaceholder.classList.remove(
                    'sm-hidden'
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

        stopCameraTracks();

        if (video) {
            video.srcObject =
                null;
        }

        if (
            !capturedImage.value
        ) {

            video.classList.remove(
                'sm-hidden'
            );

            cameraPlaceholder.classList.remove(
                'sm-hidden'
            );
        }
    }


    function resetCameraButtons() {

        startCameraBtn.classList.remove(
            'sm-hidden'
        );

        captureBtn.classList.add(
            'sm-hidden'
        );

        stopCameraBtn.classList.add(
            'sm-hidden'
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
                fileInput.files?.[0] ||
                null;

            const hasFile =
                !!file;

            const hasCamera =
                capturedImage.value.trim() !== '';


            if (
                !hasFile &&
                !hasCamera
            ) {

                event.preventDefault();

                showAlert(
                    'warning',
                    'Berkas belum dipilih',
                    'Upload berkas atau gunakan Scan Kamera.'
                );

                return;
            }


            if (
                hasFile &&
                hasCamera
            ) {

                event.preventDefault();

                showAlert(
                    'warning',
                    'Pilih satu metode',
                    'Gunakan Upload File atau Scan Kamera saja.'
                );

                return;
            }


            if (
                hasFile &&
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


            if (
                hasCamera
            ) {

                const cameraSize =
                    estimateBinarySize(
                        capturedImage.value
                    );

                if (
                    cameraSize >
                    CAMERA_MAX_DATA_SIZE
                ) {

                    event.preventDefault();

                    showAlert(
                        'warning',
                        'Hasil scan terlalu besar',
                        'Silakan scan ulang agar ukuran lebih kecil.'
                    );

                    return;
                }
            }


            submitting =
                true;


            stopCamera();


            submitBtn.disabled =
                true;

            submitIcon.classList.add(
                'sm-hidden'
            );

            submitLoading.classList.remove(
                'sm-hidden'
            );

            submitText.textContent =
                hasCamera
                    ? 'Menyimpan hasil scan...'
                    : 'Menyimpan...';

        }
    );


    /* =========================================================
       OLD CAMERA DATA
    ========================================================= */

    if (
        capturedImage.value
    ) {

        setCameraMode();

        imagePreview.src =
            capturedImage.value;

        imagePreview.classList.remove(
            'sm-hidden'
        );

        video.classList.add(
            'sm-hidden'
        );

        cameraPlaceholder.classList.add(
            'sm-hidden'
        );

        snapshotPreview.classList.remove(
            'sm-hidden'
        );

        snapshotText.textContent =
            '✓ Hasil scan sebelumnya masih tersedia.';

        startCameraBtn.classList.add(
            'sm-hidden'
        );

        captureBtn.classList.add(
            'sm-hidden'
        );

        stopCameraBtn.classList.add(
            'sm-hidden'
        );
    }


    /* =========================================================
       CLEANUP
    ========================================================= */

    window.addEventListener(
        'beforeunload',
        function () {
            stopCameraTracks();
        }
    );

});
</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection