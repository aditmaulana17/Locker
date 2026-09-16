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
            $formErrors->put(
                'default',
                $messageBag
            );
        }
    }

    $compressionResult =
        session('compression_result');
@endphp

<style>
.sm-page,
.sm-page * {
    box-sizing: border-box;
}

.sm-page {
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
    padding: 16px 18px 32px;
    color: #1e293b;
}

.sm-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 14px;
}

.sm-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    color: #64748b;
}

.sm-breadcrumb strong {
    color: #1e293b;
    font-weight: 800;
}

.sm-back {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 36px;
    padding: 0 13px;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #fff;
    color: #475569;
    text-decoration: none;
    font-size: 11px;
    font-weight: 800;
    transition: .15s ease;
}

.sm-back:hover {
    border-color: #818cf8;
    background: #eef2ff;
    color: #4338ca;
}

.sm-shell {
    overflow: hidden;
    border: 1px solid #dbe3ef;
    border-radius: 16px;
    background: #fff;
    box-shadow:
        0 14px 38px rgba(15, 23, 42, .07);
}

.sm-header {
    position: relative;
    overflow: hidden;
    padding: 22px 24px;
    background:
        linear-gradient(
            135deg,
            #2563eb 0%,
            #4f46e5 54%,
            #0f766e 100%
        );
    color: #fff;
}

.sm-header::after {
    content: "";
    position: absolute;
    right: -80px;
    bottom: -90px;
    width: 250px;
    height: 250px;
    border-radius: 50%;
    background: rgba(255,255,255,.09);
}

.sm-header-inner {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
}

.sm-header-main {
    display: flex;
    align-items: center;
    gap: 13px;
    min-width: 0;
}

.sm-header-icon {
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255,255,255,.28);
    border-radius: 12px;
    background: rgba(255,255,255,.14);
    backdrop-filter: blur(5px);
}

.sm-header-kicker {
    margin: 0;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: rgba(255,255,255,.78);
}

.sm-header-title {
    margin: 3px 0 0;
    font-size: 22px;
    line-height: 1.25;
    font-weight: 800;
}

.sm-header-desc {
    margin: 5px 0 0;
    max-width: 720px;
    font-size: 10px;
    line-height: 1.55;
    color: rgba(255,255,255,.82);
}

.sm-header-badge {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 10px;
    border: 1px solid rgba(255,255,255,.24);
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    font-size: 9px;
    font-weight: 800;
    white-space: nowrap;
}

.sm-body {
    padding: 20px;
}

.sm-error {
    margin-bottom: 16px;
    padding: 12px 14px;
    border: 1px solid #fecaca;
    border-radius: 10px;
    background: #fff7f7;
    color: #be123c;
}

.sm-error-title {
    margin: 0;
    font-size: 11px;
    font-weight: 800;
}

.sm-error-list {
    margin: 5px 0 0;
    padding-left: 18px;
    font-size: 9px;
    line-height: 1.55;
}

.sm-server-result {
    margin-bottom: 16px;
    padding: 13px 14px;
    border: 1px solid #a7f3d0;
    border-radius: 10px;
    background: #ecfdf5;
    color: #047857;
}

.sm-server-result-title {
    margin: 0 0 7px;
    font-size: 11px;
    font-weight: 800;
}

.sm-server-result-grid {
    display: grid;
    grid-template-columns:
        repeat(
            4,
            minmax(0,1fr)
        );
    gap: 7px;
}

.sm-server-result-item {
    padding: 8px;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    background: rgba(255,255,255,.65);
}

.sm-server-result-label {
    display: block;
    margin-bottom: 2px;
    font-size: 7px;
    color: #64748b;
}

.sm-server-result-value {
    font-size: 9px;
    font-weight: 800;
    color: #047857;
}

.sm-section + .sm-section {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.sm-section-head {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 12px;
}

.sm-section-accent {
    width: 4px;
    min-height: 30px;
    flex: 0 0 4px;
    border-radius: 99px;
    background: #2563eb;
}

.sm-section-accent.green {
    background: #0f766e;
}

.sm-section-title {
    margin: 0;
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
}

.sm-section-desc {
    margin: 3px 0 0;
    font-size: 9px;
    line-height: 1.45;
    color: #64748b;
}

.sm-data-grid {
    display: grid;
    grid-template-columns:
        repeat(
            2,
            minmax(0,1fr)
        );
    overflow: hidden;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
}

.sm-field {
    min-width: 0;
    padding: 13px;
    background: #fff;
    border-right: 1px solid #cbd5e1;
    border-bottom: 1px solid #cbd5e1;
}

.sm-field:nth-child(2n) {
    border-right: 0;
}

.sm-field.full {
    grid-column: 1 / -1;
    border-right: 0;
}

.sm-field-label,
.sm-physical-label {
    display: block;
    margin-bottom: 6px;
    font-size: 9px;
    font-weight: 800;
    color: #475569;
}

.sm-required {
    color: #dc2626;
}

.sm-control {
    width: 100%;
    min-height: 40px;
    border: 1px solid #94a3b8;
    border-radius: 8px;
    background: #fff;
    color: #0f172a;
    padding: 0 11px;
    font-size: 11px;
    outline: none;
    transition: .15s ease;
}

textarea.sm-control {
    min-height: 86px;
    padding: 10px 11px;
    line-height: 1.5;
    resize: vertical;
}

.sm-control:hover {
    border-color: #64748b;
}

.sm-control:focus {
    border-color: #2563eb;
    box-shadow:
        0 0 0 3px
        rgba(37,99,235,.08);
}

.sm-control-error {
    border-color: #ef4444 !important;
    background: #fff8f8 !important;
}

.sm-field-error {
    margin: 5px 0 0;
    font-size: 8px;
    line-height: 1.4;
    color: #dc2626;
    font-weight: 700;
}

.sm-attachment-grid {
    display: grid;
    grid-template-columns:
        minmax(0,1.35fr)
        minmax(320px,.65fr);
    gap: 14px;
    align-items: stretch;
}

.sm-card {
    min-width: 0;
    overflow: hidden;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    background: #fff;
}

.sm-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 11px 13px;
    background: #f8fafc;
    border-bottom: 1px solid #cbd5e1;
}

.sm-card-title-wrap {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 0;
}

.sm-card-icon {
    width: 34px;
    height: 34px;
    flex: 0 0 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    background: #eff6ff;
    color: #2563eb;
}

.sm-card-icon.green {
    border-color: #99f6e4;
    background: #f0fdfa;
    color: #0f766e;
}

.sm-card-title {
    margin: 0;
    font-size: 10px;
    font-weight: 800;
    color: #0f172a;
}

.sm-card-desc {
    margin: 2px 0 0;
    font-size: 7.5px;
    color: #64748b;
}

.sm-badge {
    padding: 5px 8px;
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 7px;
    font-weight: 800;
    white-space: nowrap;
}

.sm-badge.optional {
    border-color: #cbd5e1;
    background: #fff;
    color: #64748b;
}

.sm-card-body {
    padding: 13px;
}

.sm-info {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 10px;
    padding: 9px;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 8px;
    line-height: 1.5;
}

.sm-mode {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 7px;
    margin-bottom: 10px;
}

.sm-mode-btn {
    min-height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #475569;
    font-size: 8px;
    font-weight: 800;
    cursor: pointer;
    transition: .15s ease;
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

.sm-upload-box {
    position: relative;
    min-height: 116px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 16px;
    border: 1.5px dashed #94a3b8;
    border-radius: 10px;
    background: #f8fafc;
    text-align: center;
    cursor: pointer;
    transition: .15s ease;
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
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 3px;
    border-radius: 9px;
    background: #e0e7ff;
    color: #4f46e5;
}

.sm-upload-box.has-file .sm-upload-icon {
    background: #d1fae5;
    color: #059669;
}

.sm-upload-title {
    font-size: 10px;
    font-weight: 800;
    color: #334155;
}

.sm-upload-format {
    font-size: 8px;
    color: #64748b;
}

.sm-upload-limit {
    margin-top: 2px;
    padding: 4px 8px;
    border-radius: 999px;
    background: #dbeafe;
    color: #2563eb;
    font-size: 7px;
    font-weight: 800;
}

.sm-upload-input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.sm-selected-file {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 9px;
    padding: 8px;
    border: 1px solid #a7f3d0;
    border-radius: 8px;
    background: #ecfdf5;
}

.sm-selected-icon {
    width: 28px;
    height: 28px;
    flex: 0 0 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    background: #d1fae5;
    color: #059669;
}

.sm-selected-content {
    min-width: 0;
    flex: 1;
}

.sm-selected-title {
    margin: 0;
    font-size: 8px;
    font-weight: 800;
    color: #047857;
}

.sm-selected-name {
    margin: 2px 0 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 8px;
    font-weight: 700;
    color: #334155;
}

.sm-selected-size {
    margin: 2px 0 0;
    font-size: 7px;
    color: #64748b;
}

.sm-clear {
    width: 27px;
    height: 27px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #a7f3d0;
    border-radius: 7px;
    background: #fff;
    color: #059669;
    cursor: pointer;
}

.sm-compression-status {
    margin-top: 9px;
    padding: 10px;
    border: 1px solid #c7d2fe;
    border-radius: 8px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 7.5px;
    line-height: 1.6;
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

.sm-compression-status.processing {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #1d4ed8;
}

.sm-compression-progress {
    position: relative;
    height: 7px;
    overflow: hidden;
    margin-top: 8px;
    border-radius: 999px;
    background: #dbeafe;
}

.sm-compression-progress-bar {
    width: 0%;
    height: 100%;
    border-radius: inherit;
    background: #2563eb;
    transition: width .2s ease;
}

.sm-compression-table {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    margin-top: 7px;
}

.sm-compression-item {
    padding: 7px 8px;
    border: 1px solid rgba(148,163,184,.28);
    border-radius: 7px;
    background: rgba(255,255,255,.55);
}

.sm-compression-label {
    display: block;
    margin-bottom: 2px;
    font-size: 6.5px;
    color: #64748b;
}

.sm-compression-value {
    font-size: 8px;
    font-weight: 800;
    color: #334155;
}

.sm-file-preview {
    margin-top: 10px;
    overflow: hidden;
    border: 1px solid #cbd5e1;
    border-radius: 9px;
    background: #f8fafc;
}

.sm-file-preview-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 8px 10px;
    border-bottom: 1px solid #cbd5e1;
    background: #f8fafc;
    font-size: 8px;
    font-weight: 800;
    color: #334155;
}

.sm-file-preview-content {
    padding: 8px;
    background: #fff;
}

.sm-file-preview-image {
    display: block;
    width: 100%;
    max-height: 430px;
    object-fit: contain;
    border-radius: 6px;
    background: #fff;
}

.sm-file-preview-pdf {
    display: block;
    width: 100%;
    height: 460px;
    border: 0;
    background: #fff;
}

.sm-preview-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 7px;
    border: 1px solid #bfdbfe;
    border-radius: 999px;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 6.5px;
    font-weight: 800;
}

.sm-camera-box {
    overflow: hidden;
    border: 1px solid #475569;
    border-radius: 10px;
    background: #0f172a;
}

.sm-camera-preview {
    position: relative;
    min-height: 280px;
    background: #0f172a;
}

.sm-camera-video,
.sm-camera-image {
    width: 100%;
    height: 280px;
    display: block;
    object-fit: contain;
    background: #0f172a;
}

.sm-camera-image {
    position: absolute;
    inset: 0;
}

.sm-camera-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px;
    background: #0f172a;
    text-align: center;
}

.sm-camera-placeholder-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 7px;
    border-radius: 10px;
    background: rgba(255,255,255,.07);
    color: #94a3b8;
}

.sm-camera-placeholder-title {
    margin: 0;
    font-size: 9px;
    font-weight: 800;
    color: #e2e8f0;
}

.sm-camera-placeholder-desc {
    margin: 3px 0 0;
    font-size: 7px;
    line-height: 1.4;
    color: #64748b;
}

.sm-camera-error {
    position: absolute;
    left: 9px;
    right: 9px;
    bottom: 9px;
    z-index: 5;
    padding: 8px;
    border-radius: 8px;
    background: rgba(127,29,29,.94);
    color: #fecaca;
    font-size: 7px;
    line-height: 1.4;
}

.sm-camera-frame {
    position: absolute;
    inset: 22px;
    border: 1px dashed rgba(255,255,255,.22);
    border-radius: 8px;
    pointer-events: none;
}

.sm-camera-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 7px;
    padding: 9px;
    background: #111827;
}

.sm-camera-btn {
    min-height: 32px;
    min-width: 120px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    padding: 0 11px;
    font-size: 7.5px;
    font-weight: 800;
    cursor: pointer;
    border: 1px solid transparent;
}

.sm-camera-btn-primary {
    border-color: #4338ca;
    background: #4f46e5;
    color: #fff;
}

.sm-camera-btn-success {
    border-color: #059669;
    background: #10b981;
    color: #fff;
}

.sm-camera-btn-secondary {
    border-color: #64748b;
    background: #fff;
    color: #334155;
}

.sm-camera-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.sm-snapshot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: 9px;
    padding: 8px;
    border: 1px solid #a7f3d0;
    border-radius: 8px;
    background: #ecfdf5;
    color: #047857;
    font-size: 8px;
    font-weight: 800;
}

.sm-snapshot-text {
    min-width: 0;
    flex: 1;
    line-height: 1.45;
}

.sm-retake {
    min-height: 29px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 0 9px;
    border: 1px solid #c7d2fe;
    border-radius: 7px;
    background: #fff;
    color: #4338ca;
    font-size: 7px;
    font-weight: 800;
    cursor: pointer;
}

.sm-physical-box {
    min-height: 100%;
    display: flex;
    flex-direction: column;
    padding: 15px;
    border: 1.5px dashed #94a3b8;
    border-radius: 10px;
    background: #f8fafc;
}

.sm-physical-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: #e0e7ff;
    color: #4f46e5;
}

.sm-physical-title {
    margin: 9px 0 0;
    font-size: 12px;
    font-weight: 800;
    color: #334155;
}

.sm-physical-desc {
    margin: 4px 0 0;
    font-size: 8px;
    line-height: 1.5;
    color: #64748b;
}

.sm-physical-field {
    margin-top: 15px;
}

.sm-example {
    margin-top: 8px;
    padding: 8px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    font-size: 7.5px;
    line-height: 1.45;
    color: #64748b;
}

.sm-note {
    margin-top: auto;
    padding-top: 14px;
    font-size: 7px;
    line-height: 1.5;
    color: #64748b;
}

.sm-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 12px 20px;
    border-top: 1px solid #dbe3ef;
    background: #f8fafc;
}

.sm-footer-btn {
    min-height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 14px;
    border-radius: 8px;
    font-size: 8.5px;
    font-weight: 800;
    text-decoration: none;
    cursor: pointer;
}

.sm-cancel {
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #475569;
}

.sm-submit {
    border: 1px solid #2563eb;
    background: #2563eb;
    color: #fff;
}

.sm-submit:hover:not(:disabled) {
    background: #1d4ed8;
    border-color: #1d4ed8;
}

.sm-submit:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.sm-hidden {
    display: none !important;
}

@media (max-width: 900px) {
    .sm-attachment-grid {
        grid-template-columns: 1fr;
    }

    .sm-server-result-grid {
        grid-template-columns:
            repeat(
                2,
                minmax(0,1fr)
            );
    }
}

@media (max-width: 700px) {
    .sm-page {
        padding: 10px 10px 24px;
    }

    .sm-topbar {
        align-items: stretch;
        flex-direction: column;
    }

    .sm-back {
        width: 100%;
    }

    .sm-header-inner {
        align-items: flex-start;
        flex-direction: column;
    }

    .sm-header-badge {
        align-self: flex-start;
    }

    .sm-body {
        padding: 12px;
    }

    .sm-data-grid {
        grid-template-columns: 1fr;
    }

    .sm-field,
    .sm-field:nth-child(2n) {
        border-right: 0;
    }

    .sm-field.full {
        grid-column: auto;
    }

    .sm-footer {
        flex-direction: column-reverse;
        align-items: stretch;
        padding: 11px 12px;
    }

    .sm-footer-btn {
        width: 100%;
    }
}

@media (max-width: 460px) {
    .sm-header {
        padding: 17px 15px;
    }

    .sm-header-title {
        font-size: 19px;
    }

    .sm-card-body {
        padding: 10px;
    }

    .sm-camera-preview,
    .sm-camera-video,
    .sm-camera-image {
        min-height: 230px;
        height: 230px;
    }

    .sm-mode {
        grid-template-columns: 1fr;
    }

    .sm-snapshot {
        align-items: stretch;
        flex-direction: column;
    }

    .sm-retake {
        width: 100%;
    }

    .sm-server-result-grid,
    .sm-compression-table {
        grid-template-columns: 1fr;
    }

    .sm-file-preview-pdf {
        height: 360px;
    }
}
</style>

<div class="sm-page">
        <a
            href="{{ route('surat-masuk.index') }}"
            class="sm-back"
        >
            <svg
                width="15"
                height="15"
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

            Kembali ke Surat Masuk
        </a>

    </div>

    @if($formErrors->any())

        <div class="sm-error">

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

    @endif

    @if(is_array($compressionResult))

        <div class="sm-server-result">

            <p class="sm-server-result-title">
                Hasil Compression Server
            </p>

            <div class="sm-server-result-grid">

                <div class="sm-server-result-item">
                    <span class="sm-server-result-label">
                        Jenis
                    </span>

                    <span class="sm-server-result-value">
                        {{ strtoupper($compressionResult['type'] ?? 'PDF') }}
                    </span>
                </div>

                <div class="sm-server-result-item">
                    <span class="sm-server-result-label">
                        Ukuran Asli
                    </span>

                    <span class="sm-server-result-value">
                        {{ number_format(($compressionResult['original_size'] ?? 0) / 1048576, 2) }}
                        MB
                    </span>
                </div>

                <div class="sm-server-result-item">
                    <span class="sm-server-result-label">
                        Ukuran Akhir
                    </span>

                    <span class="sm-server-result-value">
                        {{ number_format(($compressionResult['compressed_size'] ?? 0) / 1048576, 2) }}
                        MB
                    </span>
                </div>

                <div class="sm-server-result-item">
                    <span class="sm-server-result-label">
                        Penghematan
                    </span>

                    <span class="sm-server-result-value">
                        {{ number_format((float) ($compressionResult['saving_percent'] ?? 0), 2) }}%
                    </span>
                </div>

            </div>

            @if(!empty($compressionResult['profile']))

                <div
                    style="
                        margin-top:8px;
                        font-size:7.5px;
                        color:#047857;
                    "
                >
                    Profile:
                    <strong>
                        {{ $compressionResult['profile'] }}
                    </strong>
                </div>

            @endif

            @if(
                ($compressionResult['status'] ?? '') ===
                'unchanged'
            )

                <div
                    style="
                        margin-top:6px;
                        font-size:7.5px;
                        color:#a16207;
                    "
                >
                    Hasil compression tidak lebih kecil
                    dari file asli sehingga file asli
                    dipertahankan.
                </div>

            @endif

        </div>

    @endif

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

        <div class="sm-shell">

            <header class="sm-header">

                <div class="sm-header-inner">

                    <div class="sm-header-main">

                        <div class="sm-header-icon">

                            <svg
                                width="22"
                                height="22"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M5 5a2 2 0 012-2h6l6 6v10a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13 3v6h6M8 13h8M8 17h6"
                                />
                            </svg>

                        </div>

                        <div>

                            <p class="sm-header-kicker">
                                Sistem E-Arsip
                            </p>

                            <h1 class="sm-header-title">
                                Catat Surat Masuk
                            </h1>

                            <p class="sm-header-desc">
                                Lengkapi data surat, lihat preview file,
                                proses compression, dan catat lokasi arsip fisik.
                            </p>

                        </div>

                    </div>

                    <span class="sm-header-badge">

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
                                d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7l7-4z"
                            />
                        </svg>

                        Arsip Terintegrasi

                    </span>

                </div>

            </header>

            <div class="sm-body">

                <section class="sm-section">

                    <div class="sm-section-head">

                        <div class="sm-section-accent"></div>

                        <div>

                            <h2 class="sm-section-title">
                                Informasi Utama Surat
                            </h2>

                            <p class="sm-section-desc">
                                Isi identitas surat dan status pengarsipan.
                            </p>

                        </div>

                    </div>

                    <div class="sm-data-grid">

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
                                placeholder="Nama instansi pengirim"
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
                                            old(
                                                'status',
                                                'baru'
                                            ) === $value
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

                        <div class="sm-field full">

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
                                placeholder="Tuliskan perihal atau pokok isi surat"
                                class="sm-control {{ $formErrors->has('perihal') ? 'sm-control-error' : '' }}"
                            >{{ old('perihal') }}</textarea>

                            @if($formErrors->has('perihal'))
                                <p class="sm-field-error">
                                    {{ $formErrors->first('perihal') }}
                                </p>
                            @endif

                        </div>

                    </div>

                </section>

                <section class="sm-section">

                    <div class="sm-section-head">

                        <div class="sm-section-accent green"></div>

                        <div>

                            <h2 class="sm-section-title">
                                Lampiran Digital & Arsip Fisik
                            </h2>

                            <p class="sm-section-desc">
                                Upload file, lihat preview, dan proses compression
                                sebelum surat disimpan.
                            </p>

                        </div>

                    </div>

                    <div class="sm-attachment-grid">

                        {{-- DIGITAL --}}

                        <div class="sm-card">

                            <div class="sm-card-header">

                                <div class="sm-card-title-wrap">

                                    <div class="sm-card-icon">

                                        <svg
                                            width="17"
                                            height="17"
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
                                                d="M14 3v5h5M8 13h8M8 17h5"
                                            />
                                        </svg>

                                    </div>

                                    <div>

                                        <h3 class="sm-card-title">
                                            Berkas Digital
                                        </h3>

                                        <p class="sm-card-desc">
                                            PDF, JPG, JPEG, PNG · maksimal 10 MB
                                        </p>

                                    </div>

                                </div>

                                <span class="sm-badge">
                                    Wajib
                                </span>

                            </div>

                            <div class="sm-card-body">

                                <div class="sm-info">

                                    <svg
                                        width="14"
                                        height="14"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
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

                                    <div>

                                        <strong>PDF:</strong>
                                        preview langsung ditampilkan,
                                        kemudian compression diproses
                                        server dengan Ghostscript.

                                        <br>

                                        <strong>JPG/JPEG/PNG:</strong>
                                        dikompresi menjadi JPG di browser
                                        sebelum dikirim ke server.

                                    </div>

                                </div>

                                <div class="sm-mode">

                                    <button
                                        type="button"
                                        id="sm-btn-upload"
                                        class="sm-mode-btn active"
                                        aria-selected="true"
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
                                                d="M12 16V4m0 0L8 8m4-4l4 4M5 14v4a2 2 0 002 2h10a2 2 0 002-2v-4"
                                            />
                                        </svg>

                                        Upload File

                                    </button>

                                    <button
                                        type="button"
                                        id="sm-btn-camera"
                                        class="sm-mode-btn"
                                        aria-selected="false"
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
                                                d="M4 7h3l1.5-2h7L17 7h3a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V8a1 1 0 011-1z"
                                            />

                                            <circle
                                                cx="12"
                                                cy="13"
                                                r="3.5"
                                            />
                                        </svg>

                                        Scan Kamera

                                    </button>

                                </div>

                                {{-- UPLOAD PANEL --}}

                                <div id="sm-upload-panel">

                                    <label
                                        id="sm-upload-box"
                                        for="lampiran_file"
                                        class="sm-upload-box"
                                    >

                                        <span class="sm-upload-icon">

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
                                                    d="M12 16V5m0 0L8 9m4-4l4 4M5 15v2a2 2 0 002 2h10a2 2 0 002-2v-2"
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

                                    <div
                                        id="sm-selected-file"
                                        class="sm-selected-file sm-hidden"
                                    >

                                        <div class="sm-selected-icon">

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
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>

                                        </div>

                                        <div class="sm-selected-content">

                                            <p
                                                id="sm-selected-title"
                                                class="sm-selected-title"
                                            >
                                                File siap digunakan
                                            </p>

                                            <p
                                                id="sm-selected-file-name"
                                                class="sm-selected-name"
                                            ></p>

                                            <p
                                                id="sm-selected-file-size"
                                                class="sm-selected-size"
                                            ></p>

                                        </div>

                                        <button
                                            type="button"
                                            id="sm-clear-file-btn"
                                            class="sm-clear"
                                            aria-label="Hapus file"
                                            title="Hapus file"
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
                                                    d="M6 18L18 6M6 6l12 12"
                                                />
                                            </svg>

                                        </button>

                                    </div>

                                    <div
                                        id="sm-compression-status"
                                        class="sm-compression-status sm-hidden"
                                    ></div>

                                    <div
                                        id="sm-file-preview"
                                        class="sm-file-preview sm-hidden"
                                    ></div>

                                </div>

                                {{-- CAMERA PANEL --}}

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
                                        class="sm-snapshot sm-hidden"
                                    >

                                        <div
                                            id="sm-snapshot-text"
                                            class="sm-snapshot-text"
                                        ></div>

                                        <button
                                            type="button"
                                            id="sm-retake"
                                            class="sm-retake"
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

                        {{-- ARSIP FISIK --}}

                        <div class="sm-card">

                            <div class="sm-card-header">

                                <div class="sm-card-title-wrap">

                                    <div class="sm-card-icon green">

                                        <svg
                                            width="17"
                                            height="17"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0-2V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                            />
                                        </svg>

                                    </div>

                                    <div>

                                        <h3 class="sm-card-title">
                                            Lokasi Arsip Fisik
                                        </h3>

                                        <p class="sm-card-desc">
                                            Posisi dokumen asli disimpan.
                                        </p>

                                    </div>

                                </div>

                                <span class="sm-badge optional">
                                    Opsional
                                </span>

                            </div>

                            <div class="sm-card-body">

                                <div class="sm-physical-box">

                                    <div class="sm-physical-icon">

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
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0-2V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                                            />
                                        </svg>

                                    </div>

                                    <h4 class="sm-physical-title">
                                        Detail Lokasi Penyimpanan
                                    </h4>

                                    <p class="sm-physical-desc">
                                        Cantumkan rak, lemari, box,
                                        atau map tempat dokumen
                                        fisik disimpan.
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

                                    <div class="sm-example">

                                        <strong>Contoh:</strong>
                                        Rak A-3 Box 12,
                                        Lemari B-2 Map 07,
                                        atau Box Arsip 2026-03.

                                    </div>

                                    <div class="sm-note">

                                        Lokasi fisik hanya mencatat
                                        posisi dokumen asli dan tidak
                                        mengubah berkas digital
                                        yang tersimpan di server.

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </section>

            </div>

            <div class="sm-footer">

                <a
                    href="{{ route('surat-masuk.index') }}"
                    class="sm-footer-btn sm-cancel"
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
                    id="sm-submit-btn"
                    type="submit"
                    class="sm-footer-btn sm-submit"
                >

                    <svg
                        id="sm-submit-icon"
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
                        id="sm-submit-loading"
                        class="sm-hidden"
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
                            opacity=".3"
                        />

                        <path
                            d="M21 12a9 9 0 00-9-9"
                            stroke="currentColor"
                            stroke-width="3"
                            stroke-linecap="round"
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        'use strict';

        const form =
            document.getElementById(
                'form-surat-masuk'
            );

        const btnUpload =
            document.getElementById(
                'sm-btn-upload'
            );

        const btnCamera =
            document.getElementById(
                'sm-btn-camera'
            );

        const uploadPanel =
            document.getElementById(
                'sm-upload-panel'
            );

        const cameraPanel =
            document.getElementById(
                'sm-camera-panel'
            );

        const fileInput =
            document.getElementById(
                'lampiran_file'
            );

        const uploadBox =
            document.getElementById(
                'sm-upload-box'
            );

        const uploadTitle =
            document.getElementById(
                'sm-upload-title'
            );

        const selectedFile =
            document.getElementById(
                'sm-selected-file'
            );

        const selectedFileTitle =
            document.getElementById(
                'sm-selected-title'
            );

        const selectedFileName =
            document.getElementById(
                'sm-selected-file-name'
            );

        const selectedFileSize =
            document.getElementById(
                'sm-selected-file-size'
            );

        const clearFileBtn =
            document.getElementById(
                'sm-clear-file-btn'
            );

        const compressionStatus =
            document.getElementById(
                'sm-compression-status'
            );

        const filePreview =
            document.getElementById(
                'sm-file-preview'
            );

        const video =
            document.getElementById(
                'sm-video'
            );

        const imagePreview =
            document.getElementById(
                'sm-image-preview'
            );

        const capturedImage =
            document.getElementById(
                'sm-captured-image'
            );

        const startCameraBtn =
            document.getElementById(
                'sm-start-camera'
            );

        const captureBtn =
            document.getElementById(
                'sm-capture'
            );

        const stopCameraBtn =
            document.getElementById(
                'sm-stop-camera'
            );

        const retakeBtn =
            document.getElementById(
                'sm-retake'
            );

        const cameraPlaceholder =
            document.getElementById(
                'sm-camera-placeholder'
            );

        const cameraPlaceholderText =
            document.getElementById(
                'sm-camera-placeholder-text'
            );

        const cameraError =
            document.getElementById(
                'sm-camera-error'
            );

        const snapshotPreview =
            document.getElementById(
                'sm-snapshot-preview'
            );

        const snapshotText =
            document.getElementById(
                'sm-snapshot-text'
            );

        const submitBtn =
            document.getElementById(
                'sm-submit-btn'
            );

        const submitIcon =
            document.getElementById(
                'sm-submit-icon'
            );

        const submitLoading =
            document.getElementById(
                'sm-submit-loading'
            );

        const submitText =
            document.getElementById(
                'sm-submit-text'
            );

        if (
            !form ||
            !fileInput ||
            !capturedImage
        ) {
            return;
        }

        const MAX_FILE_SIZE =
            10 *
            1024 *
            1024;

        const IMAGE_TARGET_SIZE =
            2.5 *
            1024 *
            1024;

        const IMAGE_MAX_RESULT_SIZE =
            9.5 *
            1024 *
            1024;

        const IMAGE_MAX_DIMENSION =
            2200;

        const IMAGE_MIN_DIMENSION =
            1000;

        const JPEG_QUALITIES = [
            .90,
            .86,
            .82,
            .78,
            .74,
            .70,
            .66,
            .62,
            .58,
            .54,
            .50,
            .46,
            .42,
            .38
        ];

        const CAMERA_MAX_WIDTH =
            1600;

        const CAMERA_MAX_HEIGHT =
            1600;

        const CAMERA_TARGET_SIZE =
            2.5 *
            1024 *
            1024;

        const CAMERA_MAX_DATA_SIZE =
            7 *
            1024 *
            1024;

        const PREVIEW_COMPRESSION_URL =
            @json(route('surat-masuk.preview-compression'));

        let cameraStream =
            null;

        let submitting =
            false;

        let compressionToken =
            0;

        let previewUrl =
            null;

        /*
        |--------------------------------------------------------------------------
        | UTILITIES
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
                bytes < 1024 * 1024
            ) {
                return (
                    (
                        bytes / 1024
                    ).toFixed(1) +
                    ' KB'
                );
            }

            return (
                (
                    bytes /
                    (
                        1024 *
                        1024
                    )
                ).toFixed(2) +
                ' MB'
            );
        }

        function getReductionPercent(
            originalSize,
            finalSize
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
                        (
                            finalSize /
                            originalSize
                        )
                    ) *
                    100
                )
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

            return [
                'pdf',
                'jpg',
                'jpeg',
                'png'
            ].includes(
                extension
            );
        }

        function isImageExtension(
            extension
        ) {

            return [
                'jpg',
                'jpeg',
                'png'
            ].includes(
                extension
            );
        }

        function showAlert(
            icon,
            title,
            message
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
                            message ?? ''
                        ),
                    confirmButtonText:
                        'Mengerti',
                    confirmButtonColor:
                        '#2563eb'
                });

                return;
            }

            window.alert(
                String(
                    message ?? ''
                )
            );
        }

        function setCompressionStatus(
            html,
            type = 'processing',
            progress = null
        ) {

            compressionStatus.classList.remove(
                'sm-hidden',
                'success',
                'warning',
                'error',
                'processing'
            );

            if (type) {
                compressionStatus.classList.add(
                    type
                );
            }

            let progressHtml =
                '';

            if (
                progress !== null
            ) {

                progressHtml =
                    `
                    <div class="sm-compression-progress">
                        <div
                            class="sm-compression-progress-bar"
                            style="width:${Math.max(
                                0,
                                Math.min(
                                    100,
                                    Number(progress)
                                )
                            )}%"
                        ></div>
                    </div>
                    `;
            }

            compressionStatus.innerHTML =
                String(
                    html ?? ''
                ) +
                progressHtml;
        }

        function updateCompressionProgress(
            progress
        ) {

            const bar =
                compressionStatus.querySelector(
                    '.sm-compression-progress-bar'
                );

            if (!bar) {
                return;
            }

            bar.style.width =
                Math.max(
                    0,
                    Math.min(
                        100,
                        Number(progress)
                    )
                ) +
                '%';
        }

        function clearCompressionStatus() {

            compressionStatus.innerHTML =
                '';

            compressionStatus.classList.remove(
                'success',
                'warning',
                'error',
                'processing'
            );

            compressionStatus.classList.add(
                'sm-hidden'
            );
        }

        function clearPreviewUrl() {

            if (
                previewUrl
            ) {

                URL.revokeObjectURL(
                    previewUrl
                );

                previewUrl =
                    null;
            }
        }

        function clearFilePreview() {

            clearPreviewUrl();

            filePreview.innerHTML =
                '';

            filePreview.classList.add(
                'sm-hidden'
            );
        }

        function showPdfPreview(
            file,
            title = 'Preview PDF'
        ) {

            clearPreviewUrl();

            previewUrl =
                URL.createObjectURL(
                    file
                );

            filePreview.innerHTML =
                `
                <div class="sm-file-preview-header">
                    <span>${title}</span>

                    <span class="sm-preview-badge">
                        PDF
                    </span>
                </div>

                <div class="sm-file-preview-content">

                    <iframe
                        class="sm-file-preview-pdf"
                        src="${previewUrl}"
                        title="${title}"
                    ></iframe>

                </div>
                `;

            filePreview.classList.remove(
                'sm-hidden'
            );
        }

        function showImagePreview(
            file,
            title = 'Preview Gambar'
        ) {

            clearPreviewUrl();

            previewUrl =
                URL.createObjectURL(
                    file
                );

            filePreview.innerHTML =
                `
                <div class="sm-file-preview-header">
                    <span>${title}</span>

                    <span class="sm-preview-badge">
                        JPG
                    </span>
                </div>

                <div class="sm-file-preview-content">

                    <img
                        class="sm-file-preview-image"
                        src="${previewUrl}"
                        alt="${title}"
                    >

                </div>
                `;

            filePreview.classList.remove(
                'sm-hidden'
            );
        }

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

        function showCameraError(
            message
        ) {

            cameraError.textContent =
                String(
                    message ?? ''
                );

            cameraError.classList.remove(
                'sm-hidden'
            );
        }

        function hideCameraError() {

            cameraError.textContent =
                '';

            cameraError.classList.add(
                'sm-hidden'
            );
        }

        function clearCameraError() {
            hideCameraError();
        }

        /*
        |--------------------------------------------------------------------------
        | FILE VISUAL
        |--------------------------------------------------------------------------
        */

        function showSelectedFile(
            file,
            note = ''
        ) {

            selectedFile.classList.remove(
                'sm-hidden'
            );

            selectedFileTitle.textContent =
                'File siap digunakan';

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
                'File siap digunakan';
        }

        function clearSelectedFile() {

            compressionToken++;

            fileInput.value =
                '';

            selectedFile.classList.add(
                'sm-hidden'
            );

            selectedFileName.textContent =
                '';

            selectedFileSize.textContent =
                '';

            uploadBox.classList.remove(
                'has-file'
            );

            uploadTitle.textContent =
                'Klik untuk memilih file';

            clearCompressionStatus();

            clearFilePreview();
        }

        clearFileBtn?.addEventListener(
            'click',
            function () {
                clearSelectedFile();
            }
        );

        /*
        |--------------------------------------------------------------------------
        | IMAGE COMPRESSION
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
                                    'Gambar tidak dapat dibaca browser.'
                                )
                            );
                        };

                    image.src =
                        objectUrl;
                }
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
                width:
                    Math.max(
                        1,
                        Math.round(
                            width * scale
                        )
                    ),

                height:
                    Math.max(
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
                                existing
                            ) {

                                return (
                                    existing.width ===
                                        dimensions.width &&
                                    existing.height ===
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

                    if (!context) {

                        reject(
                            new Error(
                                'Browser tidak mendukung canvas.'
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
                                        'Browser gagal membuat JPG.'
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

        async function compressImageFile(
            file,
            progressCallback = null
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

            const dimensionList =
                buildDimensionList(
                    width,
                    height
                );

            let smallest =
                null;

            const totalSteps =
                Math.max(
                    1,
                    dimensionList.length *
                    JPEG_QUALITIES.length
                );

            let currentStep =
                0;

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

                    currentStep++;

                    if (
                        typeof progressCallback ===
                        'function'
                    ) {

                        progressCallback(
                            Math.round(
                                (
                                    currentStep /
                                    totalSteps
                                ) *
                                100
                            )
                        );
                    }

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
                    IMAGE_MAX_RESULT_SIZE
            ) {

                return smallest;
            }

            throw new Error(
                'Gambar masih terlalu besar setelah dikompres.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PDF COMPRESSION
        |--------------------------------------------------------------------------
        */

        async function previewPdfCompression(
            file
        ) {

            const formData =
                new FormData();

            formData.append(
                'lampiran_file',
                file
            );

            const csrfToken =
                document
                    .querySelector(
                        'input[name="_token"]'
                    )
                    ?.value || '';

            setCompressionStatus(
                '<strong>⏳ PDF sedang diproses server...</strong><br>' +
                'File sedang dikirim ke server untuk diproses Ghostscript. ' +
                'Jangan pilih file lain sampai proses selesai.',
                'processing',
                10
            );

            try {

                const response =
                    await fetch(
                        PREVIEW_COMPRESSION_URL,
                        {
                            method:
                                'POST',

                            headers: {
                                'X-CSRF-TOKEN':
                                    csrfToken,

                                'Accept':
                                    'application/json'
                            },

                            body:
                                formData
                        }
                    );

                updateCompressionProgress(
                    45
                );

                let payload =
                    null;

                try {
                    payload =
                        await response.json();
                } catch (
                    jsonError
                ) {
                    throw new Error(
                        'Server mengembalikan respons yang tidak valid.'
                    );
                }

                if (
                    !response.ok ||
                    !payload?.success
                ) {

                    throw new Error(
                        payload?.message ||
                        'Compression PDF gagal.'
                    );
                }

                updateCompressionProgress(
                    80
                );

                const originalSize =
                    Number(
                        payload.original_size ||
                        file.size
                    );

                const compressedSize =
                    Number(
                        payload.compressed_size ||
                        file.size
                    );

                const saving =
                    Number(
                        payload.saving_percent ||
                        0
                    );

                const profile =
                    payload.profile ||
                    '-';

                const compressed =
                    !!payload.compressed;

                setCompressionStatus(
                    (
                        compressed
                            ? '<strong>✓ PDF berhasil dikompresi oleh server.</strong>'
                            : '<strong>✓ PDF tidak menjadi lebih kecil.</strong>'
                    ) +

                    '<div class="sm-compression-table">' +

                    '<div class="sm-compression-item">' +
                    '<span class="sm-compression-label">Ukuran asli</span>' +
                    '<span class="sm-compression-value">' +
                    formatFileSize(
                        originalSize
                    ) +
                    '</span>' +
                    '</div>' +

                    '<div class="sm-compression-item">' +
                    '<span class="sm-compression-label">Ukuran hasil</span>' +
                    '<span class="sm-compression-value">' +
                    formatFileSize(
                        compressedSize
                    ) +
                    '</span>' +
                    '</div>' +

                    '<div class="sm-compression-item">' +
                    '<span class="sm-compression-label">Penghematan</span>' +
                    '<span class="sm-compression-value">' +
                    saving.toFixed(2) +
                    '%</span>' +
                    '</div>' +

                    '<div class="sm-compression-item">' +
                    '<span class="sm-compression-label">Profile</span>' +
                    '<span class="sm-compression-value">' +
                    escapeHtml(
                        profile
                    ) +
                    '</span>' +
                    '</div>' +

                    '</div>',

                    compressed
                        ? 'success'
                        : 'warning',

                    100
                );

                if (
                    payload.preview_url
                ) {

                    showRemotePdfPreview(
                        payload.preview_url,
                        compressed
                            ? 'Preview PDF hasil compression server'
                            : 'Preview PDF asli'
                    );

                }

                return payload;

            } catch (
                error
            ) {

                console.error(
                    'PDF compression error:',
                    error
                );

                setCompressionStatus(
                    '<strong>✕ Compression PDF gagal.</strong><br>' +
                    escapeHtml(
                        error?.message ||
                        'Server gagal memproses PDF.'
                    ),
                    'error'
                );

                throw error;
            }
        }

        function showRemotePdfPreview(
            url,
            title
        ) {

            clearPreviewUrl();

            filePreview.innerHTML =
                `
                <div class="sm-file-preview-header">
                    <span>${escapeHtml(title)}</span>

                    <span class="sm-preview-badge">
                        PDF SERVER
                    </span>
                </div>

                <div class="sm-file-preview-content">

                    <iframe
                        class="sm-file-preview-pdf"
                        src="${escapeHtml(url)}"
                        title="${escapeHtml(title)}"
                    ></iframe>

                </div>
                `;

            filePreview.classList.remove(
                'sm-hidden'
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

                if (!file) {
                    return;
                }

                const currentToken =
                    ++compressionToken;

                hideCameraError();
                clearFilePreview();
                clearCompressionStatus();

                const extension =
                    getExtension(
                        file
                    );

                /*
                |--------------------------------------------------------------------------
                | EXTENSION
                |--------------------------------------------------------------------------
                */

                if (
                    !isAllowedExtension(
                        extension
                    )
                ) {

                    clearSelectedFile();

                    showAlert(
                        'warning',
                        'Format tidak didukung',
                        'Gunakan PDF, JPG, JPEG, atau PNG.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | EMPTY
                |--------------------------------------------------------------------------
                */

                if (
                    file.size <= 0
                ) {

                    clearSelectedFile();

                    showAlert(
                        'warning',
                        'File tidak valid',
                        'File yang dipilih kosong.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | MAX
                |--------------------------------------------------------------------------
                */

                if (
                    file.size >
                    MAX_FILE_SIZE
                ) {

                    clearSelectedFile();

                    showAlert(
                        'warning',
                        'File terlalu besar',
                        'Ukuran file asli maksimal 10 MB.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | PDF
                |--------------------------------------------------------------------------
                */

                if (
                    extension === 'pdf'
                ) {

                    capturedImage.value =
                        '';

                    clearCapturedPreview();

                    showSelectedFile(
                        file,
                        'PDF • siap diproses'
                    );

                    /*
                    |--------------------------------------------------------------
                    | PREVIEW ASLI SEGERA
                    |--------------------------------------------------------------
                    */

                    showPdfPreview(
                        file,
                        'Preview PDF yang dipilih'
                    );

                    /*
                    |--------------------------------------------------------------
                    | COMPRESSION SERVER
                    |--------------------------------------------------------------
                    */

                    try {

                        await previewPdfCompression(
                            file
                        );

                    } catch (
                        error
                    ) {

                        if (
                            currentToken !==
                            compressionToken
                        ) {
                            return;
                        }

                    }

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

                const originalSize =
                    file.size;

                showSelectedFile(
                    file,
                    'Sedang mengompres...'
                );

                setCompressionStatus(
                    '<strong>⏳ Sedang mengompres gambar...</strong><br>' +
                    'Browser sedang melakukan resize dan mencari hasil JPG yang optimal.',
                    'processing',
                    0
                );

                try {

                    const compressed =
                        await compressImageFile(
                            file,
                            function (
                                progress
                            ) {

                                updateCompressionProgress(
                                    progress
                                );
                            }
                        );

                    if (
                        currentToken !==
                        compressionToken
                    ) {
                        return;
                    }

                    if (
                        compressed.size >
                        MAX_FILE_SIZE
                    ) {

                        throw new Error(
                            'Ukuran hasil compression masih lebih dari 10 MB.'
                        );
                    }

                    const transfer =
                        new DataTransfer();

                    transfer.items.add(
                        compressed
                    );

                    fileInput.files =
                        transfer.files;

                    capturedImage.value =
                        '';

                    clearCapturedPreview();

                    const reduction =
                        getReductionPercent(
                            originalSize,
                            compressed.size
                        );

                    showSelectedFile(
                        compressed,
                        'JPG • hasil kompresi'
                    );

                    setCompressionStatus(
                        '<strong>✓ Kompresi gambar berhasil.</strong>' +

                        '<div class="sm-compression-table">' +

                        '<div class="sm-compression-item">' +
                        '<span class="sm-compression-label">Format asli</span>' +
                        '<span class="sm-compression-value">' +
                        extension.toUpperCase() +
                        '</span>' +
                        '</div>' +

                        '<div class="sm-compression-item">' +
                        '<span class="sm-compression-label">Format akhir</span>' +
                        '<span class="sm-compression-value">JPG</span>' +
                        '</div>' +

                        '<div class="sm-compression-item">' +
                        '<span class="sm-compression-label">Ukuran asli</span>' +
                        '<span class="sm-compression-value">' +
                        formatFileSize(
                            originalSize
                        ) +
                        '</span>' +
                        '</div>' +

                        '<div class="sm-compression-item">' +
                        '<span class="sm-compression-label">Ukuran hasil</span>' +
                        '<span class="sm-compression-value">' +
                        formatFileSize(
                            compressed.size
                        ) +
                        '</span>' +
                        '</div>' +

                        '<div class="sm-compression-item">' +
                        '<span class="sm-compression-label">Penghematan</span>' +
                        '<span class="sm-compression-value">' +
                        reduction +
                        '%</span>' +
                        '</div>' +

                        '<div class="sm-compression-item">' +
                        '<span class="sm-compression-label">Status</span>' +
                        '<span class="sm-compression-value">Siap disimpan</span>' +
                        '</div>' +

                        '</div>',

                        'success',

                        100
                    );

                    showImagePreview(
                        compressed,
                        'Preview JPG hasil kompresi'
                    );

                } catch (
                    error
                ) {

                    console.error(
                        'Image compression error:',
                        error
                    );

                    if (
                        currentToken !==
                        compressionToken
                    ) {
                        return;
                    }

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

        /*
        |--------------------------------------------------------------------------
        | CAMERA
        |--------------------------------------------------------------------------
        */

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

        function calculateCameraDimensions(
            width,
            height
        ) {

            const scale =
                Math.min(
                    CAMERA_MAX_WIDTH / width,
                    CAMERA_MAX_HEIGHT / height,
                    1
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
                base64.endsWith(
                    '=='
                )
                    ? 2
                    : base64.endsWith(
                        '='
                    )
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

        function buildCameraImage() {

            const dimensions =
                calculateCameraDimensions(
                    video.videoWidth,
                    video.videoHeight
                );

            let canvas =
                document.createElement(
                    'canvas'
                );

            canvas.width =
                dimensions.width;

            canvas.height =
                dimensions.height;

            let context =
                canvas.getContext(
                    '2d',
                    {
                        alpha:
                            false
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
                .82;

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
                    .38
            ) {

                quality -=
                    .05;

                dataUrl =
                    canvas.toDataURL(
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
                attempt < 5
            ) {

                attempt++;

                const smallerCanvas =
                    document.createElement(
                        'canvas'
                    );

                smallerCanvas.width =
                    Math.max(
                        1,
                        Math.round(
                            canvas.width *
                            .75
                        )
                    );

                smallerCanvas.height =
                    Math.max(
                        1,
                        Math.round(
                            canvas.height *
                            .75
                        )
                    );

                const smallerContext =
                    smallerCanvas.getContext(
                        '2d',
                        {
                            alpha:
                                false
                        }
                    );

                if (!smallerContext) {

                    throw new Error(
                        'Resize kamera tidak tersedia.'
                    );
                }

                smallerContext.fillStyle =
                    '#ffffff';

                smallerContext.fillRect(
                    0,
                    0,
                    smallerCanvas.width,
                    smallerCanvas.height
                );

                smallerContext.imageSmoothingEnabled =
                    true;

                smallerContext.imageSmoothingQuality =
                    'high';

                smallerContext.drawImage(
                    canvas,
                    0,
                    0,
                    smallerCanvas.width,
                    smallerCanvas.height
                );

                canvas =
                    smallerCanvas;

                dataUrl =
                    canvas.toDataURL(
                        'image/jpeg',
                        .58
                    );
            }

            return dataUrl;
        }

        function setUploadMode() {

            compressionToken++;

            stopCameraTracks();

            video.srcObject =
                null;

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

            resetCameraButtons();
        }

        function setCameraMode() {

            compressionToken++;

            stopCameraTracks();

            video.srcObject =
                null;

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

            clearFilePreview();

            clearCameraError();

            resetCameraButtons();

            if (
                !capturedImage.value
            ) {

                cameraPlaceholder.classList.remove(
                    'sm-hidden'
                );

                video.classList.remove(
                    'sm-hidden'
                );
            }
        }

        btnUpload?.addEventListener(
            'click',
            setUploadMode
        );

        btnCamera?.addEventListener(
            'click',
            setCameraMode
        );

        startCameraBtn?.addEventListener(
            'click',
            async function () {

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
                        await navigator
                            .mediaDevices
                            .getUserMedia({
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

                                audio:
                                    false
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

                } catch (
                    error
                ) {

                    console.error(
                        'Camera error:',
                        error
                    );

                    let message =
                        'Kamera tidak dapat digunakan.';

                    if (
                        error?.name ===
                            'NotAllowedError' ||
                        error?.name ===
                            'PermissionDeniedError'
                    ) {

                        message =
                            'Akses kamera ditolak. Izinkan kamera pada browser.';

                    } else if (
                        error?.name ===
                        'NotFoundError'
                    ) {

                        message =
                            'Kamera tidak ditemukan.';

                    } else if (
                        error?.name ===
                        'NotReadableError'
                    ) {

                        message =
                            'Kamera sedang digunakan aplikasi lain.';

                    } else if (
                        error?.name ===
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
        );

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

                    snapshotText.innerHTML =
                        '✓ Hasil scan siap disimpan<br>' +
                        'Format: JPG<br>' +
                        'Ukuran: ' +
                        formatFileSize(
                            binarySize
                        );

                    snapshotPreview.classList.remove(
                        'sm-hidden'
                    );

                    setCompressionStatus(
                        '<strong>✓ Scan berhasil dikompres.</strong>' +

                        '<div class="sm-compression-table">' +

                        '<div class="sm-compression-item">' +
                        '<span class="sm-compression-label">Format</span>' +
                        '<span class="sm-compression-value">JPG</span>' +
                        '</div>' +

                        '<div class="sm-compression-item">' +
                        '<span class="sm-compression-label">Ukuran hasil</span>' +
                        '<span class="sm-compression-value">' +
                        formatFileSize(
                            binarySize
                        ) +
                        '</span>' +
                        '</div>' +

                        '<div class="sm-compression-item">' +
                        '<span class="sm-compression-label">Target</span>' +
                        '<span class="sm-compression-value">≤ 2.5 MB</span>' +
                        '</div>' +

                        '<div class="sm-compression-item">' +
                        '<span class="sm-compression-label">Status</span>' +
                        '<span class="sm-compression-value">Siap disimpan</span>' +
                        '</div>' +

                        '</div>',

                        'success',
                        100
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

                } catch (
                    error
                ) {

                    console.error(
                        'Capture error:',
                        error
                    );

                    showCameraError(
                        error?.message ||
                        'Gagal mengambil scan.'
                    );
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

        retakeBtn?.addEventListener(
            'click',
            async function () {

                clearCapturedPreview();

                clearCompressionStatus();

                hideCameraError();

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

                resetCameraButtons();

                startCameraBtn.click();
            }
        );

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

                const tanggalSurat =
                    document.getElementById(
                        'tanggal_surat'
                    )?.value ||
                    '';

                const tanggalTerima =
                    document.getElementById(
                        'tanggal_terima'
                    )?.value ||
                    '';

                if (
                    tanggalSurat &&
                    tanggalTerima &&
                    tanggalTerima <
                        tanggalSurat
                ) {

                    event.preventDefault();

                    showAlert(
                        'warning',
                        'Tanggal tidak valid',
                        'Tanggal diterima tidak boleh lebih awal dari tanggal surat.'
                    );

                    return;
                }

                const file =
                    fileInput.files?.[0] ||
                    null;

                const hasFile =
                    !!file;

                const hasCamera =
                    capturedImage.value.trim() !==
                    '';

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
                    hasFile
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
                            'Format tidak didukung',
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

                    if (
                        extension ===
                        'pdf'
                    ) {

                        setCompressionStatus(
                            '<strong>⏳ PDF sedang diproses...</strong><br>' +
                            'Ghostscript server akan memproses PDF sebelum file disimpan.',
                            'processing',
                            20
                        );

                        submitText.textContent =
                            'Memproses PDF...';
                    }
                }

                if (
                    hasCamera
                ) {

                    const binarySize =
                        estimateBinarySize(
                            capturedImage.value
                        );

                    if (
                        binarySize >
                        CAMERA_MAX_DATA_SIZE
                    ) {

                        event.preventDefault();

                        showAlert(
                            'warning',
                            'Hasil scan terlalu besar',
                            'Silakan scan ulang agar ukurannya lebih kecil.'
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

                if (
                    hasCamera
                ) {

                    submitText.textContent =
                        'Menyimpan hasil scan...';

                } else if (
                    file &&
                    getExtension(file) ===
                        'pdf'
                ) {

                    submitText.textContent =
                        'Memproses PDF...';

                } else {

                    submitText.textContent =
                        'Menyimpan...';
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | OLD CAMERA VALUE
        |--------------------------------------------------------------------------
        */

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

            const oldSize =
                estimateBinarySize(
                    capturedImage.value
                );

            snapshotText.innerHTML =
                '✓ Hasil scan sebelumnya tersedia<br>' +
                'Format: JPG<br>' +
                'Ukuran: ' +
                formatFileSize(
                    oldSize
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
        }

        window.addEventListener(
            'beforeunload',
            function () {

                stopCameraTracks();

                clearPreviewUrl();
            }
        );

    }
);
</script>

@endsection