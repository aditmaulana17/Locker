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
        box-shadow: 0 14px 38px rgba(15, 23, 42, .07);
    }

    .sm-header {
        position: relative;
        overflow: hidden;
        padding: 22px 24px;
        border-bottom: 1px solid rgba(255,255,255,.18);
        background: linear-gradient(135deg, #2563eb 0%, #4f46e5 54%, #0f766e 100%);
        color: #fff;
    }

    .sm-header::after {
        content: "";
        position: absolute;
        inset: auto -80px -90px auto;
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

    .sm-section {
        width: 100%;
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
        grid-template-columns: repeat(2, minmax(0, 1fr));
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        overflow: hidden;
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

    .sm-control::placeholder {
        color: #94a3b8;
    }

    .sm-control:hover {
        border-color: #64748b;
    }

    .sm-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.08);
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
        grid-template-columns: minmax(0, 1.35fr) minmax(320px, .65fr);
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
        padding: 8px 9px;
        border: 1px solid #c7d2fe;
        border-radius: 8px;
        background: #eef2ff;
        color: #4338ca;
        font-size: 7.5px;
        line-height: 1.5;
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
        box-shadow: 0 4px 10px rgba(37,99,235,.12);
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
    }
</style>

<div class="sm-page">

    <div class="sm-topbar">
        <div class="sm-breadcrumb">
            <span>Arsip</span>
            <span>/</span>
            <strong>Surat Masuk</strong>
            <span>/</span>
            <strong>Catat</strong>
        </div>

        <a href="{{ route('surat-masuk.index') }}" class="sm-back">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Surat Masuk
        </a>
    </div>

    @if($formErrors->any())
        <div class="sm-error">
            <p class="sm-error-title">Data belum dapat disimpan.</p>
            <ul class="sm-error-list">
                @foreach($formErrors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="form-surat-masuk" method="POST" action="{{ route('surat-masuk.store') }}" enctype="multipart/form-data">
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
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 5a2 2 0 012-2h6l6 6v10a2 2 0 01-2 2H7a2 2 0 01-2-2V5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 3v6h6M8 13h8M8 17h6"/>
                            </svg>
                        </div>

                        <div>
                            <p class="sm-header-kicker">Sistem E-Arsip</p>
                            <h1 class="sm-header-title">Catat Surat Masuk</h1>
                            <p class="sm-header-desc">
                                Lengkapi data surat, lampiran digital, dan lokasi arsip fisik agar dokumen tercatat rapi dalam sistem.
                            </p>
                        </div>
                    </div>

                    <span class="sm-header-badge">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 4v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V7l7-4z"/>
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
                            <h2 class="sm-section-title">Informasi Utama Surat</h2>
                            <p class="sm-section-desc">Isi identitas surat dan status pengarsipan.</p>
                        </div>
                    </div>

                    <div class="sm-data-grid">

                        <div class="sm-field">
                            <label for="nomor_surat" class="sm-field-label">
                                Nomor Surat <span class="sm-required">*</span>
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
                                <p class="sm-field-error">{{ $formErrors->first('nomor_surat') }}</p>
                            @endif
                        </div>

                        <div class="sm-field">
                            <label for="pengirim" class="sm-field-label">
                                Instansi Pengirim <span class="sm-required">*</span>
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
                                <p class="sm-field-error">{{ $formErrors->first('pengirim') }}</p>
                            @endif
                        </div>

                        <div class="sm-field">
                            <label for="tanggal_surat" class="sm-field-label">
                                Tanggal Surat <span class="sm-required">*</span>
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
                                <p class="sm-field-error">{{ $formErrors->first('tanggal_surat') }}</p>
                            @endif
                        </div>

                        <div class="sm-field">
                            <label for="tanggal_terima" class="sm-field-label">
                                Tanggal Diterima <span class="sm-required">*</span>
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
                                <p class="sm-field-error">{{ $formErrors->first('tanggal_terima') }}</p>
                            @endif
                        </div>

                        <div class="sm-field">
                            <label for="kategori_surat_id" class="sm-field-label">
                                Kategori Surat <span class="sm-required">*</span>
                            </label>
                            <select
                                id="kategori_surat_id"
                                name="kategori_surat_id"
                                required
                                class="sm-control {{ $formErrors->has('kategori_surat_id') ? 'sm-control-error' : '' }}"
                            >
                                <option value="">Pilih kategori surat</option>
                                @foreach(($kategoris ?? collect()) as $kategori)
                                    <option
                                        value="{{ $kategori->id }}"
                                        @selected((string) old('kategori_surat_id') === (string) $kategori->id)
                                    >
                                        {{ $kategori->nama_kategori }}
                                        @if(!empty($kategori->sifat))
                                            ({{ ucfirst($kategori->sifat) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @if($formErrors->has('kategori_surat_id'))
                                <p class="sm-field-error">{{ $formErrors->first('kategori_surat_id') }}</p>
                            @endif
                        </div>

                        <div class="sm-field">
                            <label for="status" class="sm-field-label">
                                Status Surat <span class="sm-required">*</span>
                            </label>
                            <select
                                id="status"
                                name="status"
                                required
                                class="sm-control {{ $formErrors->has('status') ? 'sm-control-error' : '' }}"
                            >
                                @foreach([
                                    'baru'           => 'Baru',
                                    'diproses'       => 'Diproses',
                                    'didisposisikan' => 'Didisposisikan',
                                    'selesai'        => 'Selesai',
                                    'diarsipkan'     => 'Diarsipkan',
                                ] as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('status', 'baru') === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @if($formErrors->has('status'))
                                <p class="sm-field-error">{{ $formErrors->first('status') }}</p>
                            @endif
                        </div>

                        <div class="sm-field full">
                            <label for="perihal" class="sm-field-label">
                                Perihal <span class="sm-required">*</span>
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
                                <p class="sm-field-error">{{ $formErrors->first('perihal') }}</p>
                            @endif
                        </div>

                    </div>
                </section>

                <section class="sm-section">
                    <div class="sm-section-head">
                        <div class="sm-section-accent green"></div>
                        <div>
                            <h2 class="sm-section-title">Lampiran Digital & Arsip Fisik</h2>
                            <p class="sm-section-desc">Simpan salinan digital dan catat posisi dokumen fisik secara bersamaan.</p>
                        </div>
                    </div>

                    <div class="sm-attachment-grid">

                        <div class="sm-card">
                            <div class="sm-card-header">
                                <div class="sm-card-title-wrap">
                                    <div class="sm-card-icon">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5M8 13h8M8 17h5"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="sm-card-title">Berkas Digital</h3>
                                        <p class="sm-card-desc">PDF, JPG, JPEG, PNG · maksimal 10 MB</p>
                                    </div>
                                </div>
                                <span class="sm-badge">Wajib</span>
                            </div>

                            <div class="sm-card-body">

                                <div class="sm-info">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path stroke-linecap="round" d="M12 10v6M12 7h.01"/>
                                    </svg>
                                    <div>
                                        <strong>PDF:</strong> akan otomatis dikompresi di server sebelum disimpan.
                                        <strong>JPG/JPEG/PNG:</strong> akan dioptimalkan untuk arsip digital.
                                    </div>
                                </div>

                                <div class="sm-mode">
                                    <button type="button" id="sm-btn-upload" class="sm-mode-btn active" aria-selected="true">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4l4 4M5 14v4a2 2 0 002 2h10a2 2 0 002-2v-4"/>
                                        </svg>
                                        Upload File
                                    </button>

                                    <button type="button" id="sm-btn-camera" class="sm-mode-btn" aria-selected="false">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h3l1.5-2h7L17 7h3a1 1 0 011 1v10a1 1 0 01-1 1H4a1 1 0 01-1-1V8a1 1 0 011-1z"/>
                                            <circle cx="12" cy="13" r="3.5"/>
                                        </svg>
                                        Scan Kamera
                                    </button>
                                </div>

                                <div id="sm-upload-panel">
                                    <label id="sm-upload-box" for="lampiran_file" class="sm-upload-box">
                                        <span class="sm-upload-icon">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V5m0 0L8 9m4-4l4 4M5 15v2a2 2 0 002 2h10a2 2 0 002-2v-2"/>
                                            </svg>
                                        </span>

                                        <span id="sm-upload-title" class="sm-upload-title">Klik untuk memilih file</span>
                                        <span class="sm-upload-format">PDF, JPG, JPEG, PNG</span>
                                        <span class="sm-upload-limit">Maksimal 10 MB</span>

                                        <input
                                            id="lampiran_file"
                                            name="lampiran_file"
                                            type="file"
                                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                            class="sm-upload-input"
                                        >
                                    </label>

                                    <div id="sm-selected-file" class="sm-selected-file sm-hidden">
                                        <div class="sm-selected-icon">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>

                                        <div class="sm-selected-content">
                                            <p class="sm-selected-title">File siap digunakan</p>
                                            <p id="sm-selected-file-name" class="sm-selected-name"></p>
                                            <p id="sm-selected-file-size" class="sm-selected-size"></p>
                                        </div>

                                        <button type="button" id="sm-clear-file-btn" class="sm-clear" aria-label="Hapus file" title="Hapus file">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <div id="sm-compression-status" class="sm-compression-status sm-hidden"></div>
                                </div>

                                <div id="sm-camera-panel" class="sm-hidden">

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

                                            <div id="sm-camera-placeholder" class="sm-camera-placeholder">
                                                <div>
                                                    <div class="sm-camera-placeholder-icon">
                                                        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                                                            <circle cx="12" cy="12" r="3.5"/>
                                                        </svg>
                                                    </div>

                                                    <p class="sm-camera-placeholder-title">Kamera belum aktif</p>

                                                    <p id="sm-camera-placeholder-text" class="sm-camera-placeholder-desc">
                                                        Aktifkan kamera untuk scan dokumen.
                                                    </p>
                                                </div>
                                            </div>

                                            <div id="sm-camera-error" class="sm-camera-error sm-hidden" role="alert"></div>
                                            <div class="sm-camera-frame"></div>
                                        </div>

                                        <div class="sm-camera-actions">
                                            <button type="button" id="sm-start-camera" class="sm-camera-btn sm-camera-btn-primary">
                                                Nyalakan Kamera
                                            </button>

                                            <button type="button" id="sm-capture" class="sm-camera-btn sm-camera-btn-success sm-hidden">
                                                Ambil Foto
                                            </button>

                                            <button type="button" id="sm-stop-camera" class="sm-camera-btn sm-camera-btn-secondary sm-hidden">
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

                                    <div id="sm-snapshot-preview" class="sm-snapshot sm-hidden">
                                        <div id="sm-snapshot-text" class="sm-snapshot-text"></div>

                                        <button type="button" id="sm-retake" class="sm-retake">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h5M20 20v-5h-5M5.5 15a7 7 0 0011.9 2.9L20 15M4 9l2.6-2.9A7 7 0 0118.5 9"/>
                                            </svg>
                                            Scan Ulang
                                        </button>
                                    </div>
                                </div>

                                @if($formErrors->has('lampiran_file'))
                                    <p class="sm-field-error">{{ $formErrors->first('lampiran_file') }}</p>
                                @endif

                                @if($formErrors->has('captured_image'))
                                    <p class="sm-field-error">{{ $formErrors->first('captured_image') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="sm-card">
                            <div class="sm-card-header">
                                <div class="sm-card-title-wrap">
                                    <div class="sm-card-icon green">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0-2V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="sm-card-title">Lokasi Arsip Fisik</h3>
                                        <p class="sm-card-desc">Posisi dokumen asli disimpan.</p>
                                    </div>
                                </div>
                                <span class="sm-badge optional">Opsional</span>
                            </div>

                            <div class="sm-card-body">
                                <div class="sm-physical-box">

                                    <div class="sm-physical-icon">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0-2V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>

                                    <h4 class="sm-physical-title">Detail Lokasi Penyimpanan</h4>

                                    <p class="sm-physical-desc">
                                        Cantumkan rak, lemari, box, atau map tempat dokumen fisik disimpan.
                                    </p>

                                    <div class="sm-physical-field">
                                        <label for="lokasi_arsip_fisik" class="sm-physical-label">
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
                                            <p class="sm-field-error">{{ $formErrors->first('lokasi_arsip_fisik') }}</p>
                                        @endif
                                    </div>

                                    <div class="sm-example">
                                        <strong>Contoh:</strong>
                                        Rak A-3 Box 12, Lemari B-2 Map 07, atau Box Arsip 2026-03.
                                    </div>

                                    <div class="sm-note">
                                        Lokasi fisik hanya mencatat posisi dokumen asli dan tidak mengubah berkas digital yang tersimpan di server.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

            </div>

            <div class="sm-footer">
                <a href="{{ route('surat-masuk.index') }}" class="sm-footer-btn sm-cancel">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Batal
                </a>

                <button id="sm-submit-btn" type="submit" class="sm-footer-btn sm-submit">
                    <svg id="sm-submit-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>

                    <svg id="sm-submit-loading" class="sm-hidden" width="14" height="14" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" opacity=".3"/>
                        <path d="M21 12a9 9 0 00-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                    </svg>

                    <span id="sm-submit-text">Simpan Surat Masuk</span>
                </button>
            </div>

        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    'use strict';


    /* =========================================================================
       ELEMENT
    ========================================================================= */

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


    /* =========================================================================
       CONFIG
    ========================================================================= */

    const MAX_FILE_SIZE =
        10 * 1024 * 1024;

    const IMAGE_TARGET_SIZE =
        2.5 * 1024 * 1024;

    const IMAGE_MAX_RESULT_SIZE =
        9.5 * 1024 * 1024;

    const IMAGE_MAX_DIMENSION =
        2200;

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

    let compressionToken =
        0;


    /* =========================================================================
       UTILITY
    ========================================================================= */

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
                    finalSize /
                    originalSize
                ) * 100
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
                confirmButtonText:
                    'Mengerti',
                confirmButtonColor:
                    '#2563eb'
            });

            return;
        }

        window.alert(
            text
        );
    }


    /* =========================================================================
       CAMERA ERROR
    ========================================================================= */

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


    /* =========================================================================
       COMPRESSION STATUS
    ========================================================================= */

    function setCompressionStatus(
        html,
        type = ''
    ) {

        if (!compressionStatus) {
            return;
        }

        compressionStatus.classList.remove(
            'sm-hidden',
            'success',
            'warning',
            'error'
        );

        if (type) {

            compressionStatus.classList.add(
                type
            );
        }

        compressionStatus.innerHTML =
            html;
    }


    function clearCompressionStatus() {

        if (!compressionStatus) {
            return;
        }

        compressionStatus.innerHTML =
            '';

        compressionStatus.classList.remove(
            'success',
            'warning',
            'error'
        );

        compressionStatus.classList.add(
            'sm-hidden'
        );
    }


    /* =========================================================================
       MODE
    ========================================================================= */

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


    function setUploadMode() {

        compressionToken++;

        stopCameraTracks();

        if (video) {
            video.srcObject =
                null;
        }

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

        resetCameraButtons();
    }


    function setCameraMode() {

        compressionToken++;

        stopCameraTracks();

        if (video) {
            video.srcObject =
                null;
        }

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


    /* =========================================================================
       SELECTED FILE
    ========================================================================= */

    function showSelectedFile(
        file,
        note = ''
    ) {

        selectedFile.classList.remove(
            'sm-hidden'
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
    }


    clearFileBtn?.addEventListener(
        'click',
        function () {

            clearSelectedFile();
        }
    );


    /* =========================================================================
       LOAD IMAGE
    ========================================================================= */

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


    /* =========================================================================
       DIMENSIONS
    ========================================================================= */

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

        const result = [];


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

                if (!duplicate) {

                    result.push(
                        dimensions
                    );
                }

            }
        );

        return result;
    }


    /* =========================================================================
       CANVAS TO FILE
    ========================================================================= */

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
                                    'Browser gagal membuat file JPG.'
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


    /* =========================================================================
       COMPRESS IMAGE
    ========================================================================= */

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
                IMAGE_MAX_RESULT_SIZE
        ) {

            return smallest;
        }


        throw new Error(
            'Gambar masih terlalu besar setelah dikompres.'
        );
    }


    /* =========================================================================
       FILE CHANGE
    ========================================================================= */

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


            const extension =
                getExtension(
                    file
                );


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


            /* ================================================================
               PDF
            ================================================================= */

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


                setCompressionStatus(
                    '✓ <strong>PDF tetap PDF.</strong><br>' +
                    'Ukuran file yang akan dikirim: <strong>' +
                    formatFileSize(
                        file.size
                    ) +
                    '</strong>.',
                    'success'
                );


                return;
            }


            /* ================================================================
               IMAGE
            ================================================================= */

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
                '⏳ <strong>Sedang memproses gambar...</strong><br>' +
                'Gambar sedang di-resize dan dikonversi menjadi JPG.'
            );


            try {

                const compressed =
                    await compressImageFile(
                        file
                    );


                if (
                    currentToken !==
                    compressionToken
                ) {
                    return;
                }


                const finalFile =
                    compressed;


                if (
                    finalFile.size >
                    MAX_FILE_SIZE
                ) {

                    clearSelectedFile();


                    setCompressionStatus(
                        '✕ Hasil kompresi masih lebih besar dari 10 MB.',
                        'error'
                    );


                    showAlert(
                        'error',
                        'Kompresi gagal',
                        'Hasil gambar masih melebihi batas 10 MB.'
                    );


                    return;
                }


                /*
                 * Ganti file input dengan file JPG
                 * hasil kompresi.
                 */

                const transfer =
                    new DataTransfer();


                transfer.items.add(
                    finalFile
                );


                fileInput.files =
                    transfer.files;


                /*
                 * Pastikan mode kamera tidak ikut terkirim.
                 */

                capturedImage.value =
                    '';


                clearCapturedPreview();


                const reduction =
                    getReductionPercent(
                        originalSize,
                        finalFile.size
                    );


                showSelectedFile(
                    finalFile,
                    'JPG • hasil kompresi'
                );


                setCompressionStatus(
                    '✓ <strong>Kompresi berhasil.</strong><br>' +
                    'Format asli: <strong>' +
                    extension.toUpperCase() +
                    '</strong><br>' +
                    'Ukuran asli: <strong>' +
                    formatFileSize(
                        originalSize
                    ) +
                    '</strong><br>' +
                    'Ukuran hasil: <strong>' +
                    formatFileSize(
                        finalFile.size
                    ) +
                    '</strong><br>' +
                    'Penghematan: <strong>' +
                    reduction +
                    '%</strong><br>' +
                    'Format akhir: <strong>JPG</strong>.',
                    'success'
                );


            } catch (error) {

                console.error(
                    'Compression error:',
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


    /* =========================================================================
       CAMERA
    ========================================================================= */

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


    /* =========================================================================
       CAPTURE
    ========================================================================= */

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
                    '✓ Hasil scan siap disimpan • ' +
                    formatFileSize(
                        binarySize
                    ) +
                    ' • JPG';


                snapshotPreview.classList.remove(
                    'sm-hidden'
                );


                setCompressionStatus(
                    '✓ <strong>Scan berhasil.</strong><br>' +
                    'Format: <strong>JPG</strong><br>' +
                    'Ukuran hasil: <strong>' +
                    formatFileSize(
                        binarySize
                    ) +
                    '</strong>.',
                    'success'
                );


                /*
                 * Hapus file upload.
                 */

                clearSelectedFile();


                /*
                 * Matikan kamera.
                 */

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


    /* =========================================================================
       BUILD CAMERA IMAGE
    ========================================================================= */

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
            estimateBinarySize(
                dataUrl
            ) >
                CAMERA_TARGET_SIZE &&
            quality >
                0.38
        ) {

            quality -=
                0.05;


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
            attempt <
                5
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
                currentCanvas,
                0,
                0,
                smallerCanvas.width,
                smallerCanvas.height
            );


            currentCanvas =
                smallerCanvas;


            dataUrl =
                currentCanvas.toDataURL(
                    'image/jpeg',
                    0.58
                );
        }


        return dataUrl;
    }


    /* =========================================================================
       BASE64 SIZE
    ========================================================================= */

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


    /* =========================================================================
       CLEAR CAMERA
    ========================================================================= */

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


    /* =========================================================================
       RETAKE
    ========================================================================= */

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


            try {

                await startCamera();

            } catch (error) {

                console.error(
                    'Retake error:',
                    error
                );


                showCameraError(
                    'Kamera gagal diaktifkan kembali.'
                );
            }
        }
    );


    /* =========================================================================
       STOP CAMERA TRACKS
    ========================================================================= */

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


    /* =========================================================================
       STOP CAMERA
    ========================================================================= */

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


    /* =========================================================================
       SUBMIT
    ========================================================================= */

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
                )?.value || '';


            const tanggalTerima =
                document.getElementById(
                    'tanggal_terima'
                )?.value || '';


            /*
             * Tanggal diterima tidak boleh
             * lebih awal dari tanggal surat.
             */

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
                capturedImage.value.trim() !== '';


            /*
             * Salah satu lampiran wajib ada.
             */

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


            /*
             * Tidak boleh dua metode sekaligus.
             */

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


            /*
             * VALIDASI FILE.
             */

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
            }


            /*
             * VALIDASI CAMERA.
             */

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


            /*
             * LOCK SUBMIT.
             */

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


    /* =========================================================================
       INITIAL STATE
    ========================================================================= */

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


    /* =========================================================================
       CLEANUP
    ========================================================================= */

    window.addEventListener(
        'beforeunload',
        function () {

            stopCameraTracks();
        }
    );

});
</script>

@endsection