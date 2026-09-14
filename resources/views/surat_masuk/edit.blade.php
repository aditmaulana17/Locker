@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')

@php
    use Illuminate\Support\Carbon;

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
        'baru'           => 'Baru',
        'diproses'       => 'Diproses',
        'didisposisikan' => 'Didisposisikan',
        'selesai'        => 'Selesai',
        'diarsipkan'     => 'Diarsipkan',
    ];
@endphp

<style>
/* ============================================================
   PAGE
============================================================ */

.edit-page {
    width: 100%;
    max-width: 1040px;
    margin: 0 auto;
    padding: 12px 18px 32px;
    color: #334155;
}

.edit-page *,
.edit-page *::before,
.edit-page *::after {
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
    width: 100%;
    margin-bottom: 12px;
    padding: 11px 13px;
    border: 1.5px solid #fecaca;
    border-radius: 9px;
    background: #fff7f7;
}

.error-box-title {
    margin: 0 0 4px;
    font-size: 10px;
    font-weight: 800;
    color: #991b1b;
}

.error-box-list {
    margin: 0;
    padding-left: 17px;
    font-size: 9px;
    line-height: 1.5;
    color: #b91c1c;
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
   SYSTEM HEADER
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

.body {
    padding: 16px;
}


/* ============================================================
   SECTION
============================================================ */

.section + .section {
    margin-top: 17px;
    padding-top: 17px;
    border-top: 1.5px solid #e2e8f0;
}

.section-head {
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

.field-label {
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

.control.border-red-500 {
    border-color: #ef4444 !important;
    background: #fff8f8;
}


/* ============================================================
   FIELD ERROR
============================================================ */

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
    grid-template-columns:
        minmax(0, 1fr)
        minmax(0, 1fr);
    gap: 12px;
    align-items: stretch;
}

.attachment-card {
    display: flex;
    flex-direction: column;
    min-width: 0;
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

.attachment-title {
    margin: 0;
    font-size: 10px;
    line-height: 1.3;
    font-weight: 800;
    color: #1e293b;
}

.attachment-desc {
    margin: 2px 0 0;
    font-size: 8px;
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
    white-space: nowrap;
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

.info {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    padding: 8px;
    border: 1px solid #bfdbfe;
    border-radius: 7px;
    background: #eff6ff;
}

.info-icon {
    width: 14px;
    height: 14px;
    flex: 0 0 14px;
    margin-top: 1px;
    color: #2563eb;
}

.info p {
    margin: 0;
    font-size: 8px;
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

.mode-button {
    min-height: 42px;
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px;
    border: 1.5px solid #cbd5e1;
    border-radius: 7px;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    transition: all .15s ease;
}

.mode-button:hover {
    border-color: #818cf8;
    background: #eef2ff;
}

.mode-button.active {
    border-color: #6366f1;
    background: #eef2ff;
    color: #4338ca;
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
    font-size: 8px;
    line-height: 1.3;
    font-weight: 800;
    color: #334155;
}

.mode-desc {
    display: block;
    margin-top: 1px;
    font-size: 7px;
    line-height: 1.3;
    color: #64748b;
}


/* ============================================================
   CURRENT FILE
============================================================ */

.current-file {
    display: flex;
    align-items: center;
    gap: 7px;
    min-height: 44px;
    padding: 7px;
    border: 1.5px solid #dbe2ea;
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
    font-size: 7px;
    line-height: 1.2;
    font-weight: 800;
    color: #64748b;
}

.current-file-name {
    display: block;
    margin-top: 1px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 8px;
    line-height: 1.3;
    font-weight: 700;
    color: #334155;
}

.current-file-empty {
    margin: 0;
    font-size: 8px;
    font-weight: 700;
    color: #b45309;
}

.current-file-empty-text {
    margin: 1px 0 0;
    font-size: 7px;
    color: #64748b;
}

.view-btn {
    min-height: 27px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 8px;
    border-radius: 6px;
    background: #e0e7ff;
    color: #4338ca;
    text-decoration: none;
    font-size: 7.5px;
    font-weight: 800;
    white-space: nowrap;
}

.view-btn:hover {
    background: #c7d2fe;
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
    font-size: 8.5px;
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
    margin-top: 2px;
    font-size: 6.5px;
    color: #94a3b8;
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
    border: 1px dashed rgba(255,255,255,.28);
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
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
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
    border: 1px solid #64748b;
    background: #ffffff;
    color: #334155;
}

.camera-btn-secondary:hover:not(:disabled) {
    background: #f1f5f9;
}


/* ============================================================
   SCAN RESULT
============================================================ */

.scan-result {
    padding: 7px;
    border-top: 1.5px solid #6ee7b7;
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
    font-size: 8px;
    line-height: 1.3;
    font-weight: 800;
    color: #065f46;
}

.scan-result-desc {
    margin: 2px 0 0;
    font-size: 6.8px;
    line-height: 1.4;
    color: #047857;
}

.retake-btn {
    min-height: 24px;
    padding: 0 7px;
    border: 1px solid #6ee7b7;
    border-radius: 6px;
    background: #ffffff;
    color: #047857;
    font-size: 7px;
    font-weight: 800;
    cursor: pointer;
}

.scan-image {
    display: block;
    width: 100%;
    max-height: 240px;
    margin-top: 5px;
    object-fit: contain;
    border: 1px solid #a7f3d0;
    border-radius: 6px;
    background: #ffffff;
}

.scan-info {
    margin-top: 3px;
    text-align: center;
    font-size: 7px;
    line-height: 1.3;
    font-weight: 800;
    color: #047857;
}


/* ============================================================
   PHYSICAL ARCHIVE
============================================================ */

.physical-box {
    display: flex;
    flex: 1;
    flex-direction: column;
    justify-content: center;
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

.physical-desc {
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


/* ============================================================
   NOTE
============================================================ */

.bottom-note {
    margin-top: auto;
    padding: 7px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
}

.bottom-note p {
    margin: 0;
    font-size: 7px;
    line-height: 1.45;
    color: #64748b;
}


/* ============================================================
   FOOTER
============================================================ */

.footer {
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

    .edit-page {
        max-width: 760px;
        padding-left: 12px;
        padding-right: 12px;
    }

    .attachment-grid {
        grid-template-columns: 1fr;
    }
}


@media (max-width: 640px) {

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

    .body {
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

    .footer {
        flex-direction: column-reverse;
    }

    .footer-btn {
        width: 100%;
    }
}


@media (max-width: 420px) {

    .edit-page {
        padding-left: 8px;
        padding-right: 8px;
    }

    .system-header {
        padding: 12px;
    }

    .system-header-title {
        font-size: 15px;
    }

    .body {
        padding: 9px;
    }

    .field {
        padding: 9px;
    }

    .attachment-body {
        padding: 8px;
    }

    input.control,
    select.control {
        height: 39px;
        font-size: 11px;
    }

    textarea.control {
        min-height: 78px;
    }
}
</style>


<div class="edit-page">

    {{-- =========================================================
         AKSI KEMBALI
    ========================================================== --}}

    <div class="top-action">
        <a
            href="{{ route('surat-masuk.index') }}"
            class="top-back-btn"
        >
            <svg
                class="h-3.5 w-3.5"
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

                        <h2 class="system-header-title">
                            Edit Surat Masuk
                        </h2>

                        <p class="system-header-subtitle">
                            Perbarui data surat masuk yang sudah tersimpan dalam sistem arsip.
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
                                Perbarui identitas dan informasi utama surat masuk.
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
                                    maxlength="255"
                                    autocomplete="off"
                                    value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}"
                                    placeholder="Contoh: 005/B/I/2026"
                                    class="control @error('nomor_surat') border-red-500 @enderror"
                                >

                                @error('nomor_surat')
                                    <p class="field-error">{{ $message }}</p>
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
                                    maxlength="255"
                                    autocomplete="organization"
                                    value="{{ old('pengirim', $suratMasuk->pengirim) }}"
                                    placeholder="Nama instansi pengirim"
                                    class="control @error('pengirim') border-red-500 @enderror"
                                >

                                @error('pengirim')
                                    <p class="field-error">{{ $message }}</p>
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
                                    <p class="field-error">{{ $message }}</p>
                                @enderror

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
                                    required
                                    value="{{ $tanggalTerima }}"
                                    class="control @error('tanggal_terima') border-red-500 @enderror"
                                >

                                @error('tanggal_terima')
                                    <p class="field-error">{{ $message }}</p>
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

                                @if(
                                    !isset($kategoris) ||
                                    $kategoris->isEmpty()
                                )
                                    <p class="field-error">
                                        Belum ada kategori surat yang tersedia.
                                    </p>
                                @endif

                                @error('kategori_surat_id')
                                    <p class="field-error">{{ $message }}</p>
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
                                    <p class="field-error">{{ $message }}</p>
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
                                    maxlength="1000"
                                    required
                                    placeholder="Tuliskan perihal surat"
                                    class="control @error('perihal') border-red-500 @enderror"
                                >{{ old('perihal', $suratMasuk->perihal) }}</textarea>

                                @error('perihal')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror

                            </div>

                        </div>

                    </div>

                </section>


                {{-- =================================================
                     LAMPIRAN DAN FISIK
                ================================================== --}}

                <section class="section">

                    <div class="section-head">

                        <div class="section-marker section-marker-indigo"></div>

                        <div>

                            <h2 class="section-title">
                                Lampiran Dokumen & Arsip Fisik
                            </h2>

                            <p class="section-description">
                                Kelola dokumen digital dan lokasi penyimpanan arsip fisik.
                            </p>

                        </div>

                    </div>


                    <div class="attachment-grid">

                        {{-- =================================================
                             LAMPIRAN DIGITAL
                        ================================================== --}}

                        <div class="attachment-card">

                            <div class="attachment-header">

                                <div class="min-w-0">

                                    <h3 class="attachment-title">
                                        Berkas Lampiran
                                    </h3>

                                    <p class="attachment-desc">
                                        Upload file baru atau gunakan scan kamera.
                                    </p>

                                </div>

                                <span class="attachment-badge">
                                    Opsional
                                </span>

                            </div>


                            <div class="attachment-body">

                                <div class="info">

                                    <svg
                                        class="info-icon"
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

                                    <p>
                                        Maksimal <strong>10 MB</strong>.
                                        PDF tetap PDF.
                                        JPG/JPEG/PNG dikompres otomatis.
                                    </p>

                                </div>


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
                                                aria-hidden="true"
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
                                                aria-hidden="true"
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


                                <div class="current-file">

                                    <div class="current-file-icon">

                                        <svg
                                            class="h-3.5 w-3.5"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
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
                                                Upload atau scan dokumen baru.
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
                                                aria-hidden="true"
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
                                                aria-hidden="true"
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
                                                File siap digunakan
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
                                        berarti lampiran lama tetap dipertahankan.
                                    </p>

                                </div>


                                @error('lampiran_file')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror

                                @error('captured_image')
                                    <p class="field-error">{{ $message }}</p>
                                @enderror

                            </div>

                        </div>


                        {{-- =================================================
                             ARSIP FISIK
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
                                            maxlength="255"
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
                                            Lokasi fisik hanya menyimpan informasi
                                            posisi arsip dan tidak memengaruhi
                                            dokumen digital.
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
                    type="submit"
                    id="submit-btn"
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
                        Perbarui Surat Masuk
                    </span>

                </button>

            </div>

        </div>

    </form>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

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
    const submitIcon = document.getElementById('submit-icon');
    const submitLoading = document.getElementById('submit-loading');
    const submitText = document.getElementById('submit-text');

    if (!form || !fileInput || !capturedInput) {
        return;
    }

    const MAX_FILE_SIZE = 10 * 1024 * 1024;
    const IMAGE_TARGET_SIZE = 2.5 * 1024 * 1024;
    const IMAGE_MAX_SIZE = 5 * 1024 * 1024;
    const IMAGE_MAX_DIMENSION = 2200;
    const IMAGE_MIN_DIMENSION = 1000;

    const IMAGE_QUALITIES = [
        0.86, 0.82, 0.78, 0.74,
        0.70, 0.66, 0.62, 0.58,
        0.54, 0.50, 0.46, 0.42
    ];

    const CAMERA_MAX_WIDTH = 1600;
    const CAMERA_MAX_HEIGHT = 1600;
    const CAMERA_TARGET_SIZE = 2.5 * 1024 * 1024;
    const CAMERA_MAX_DATA_URL_LENGTH = 7 * 1024 * 1024;

    let cameraStream = null;
    let previewObjectUrl = null;
    let submitting = false;

    function formatFileSize(bytes) {

        if (!Number.isFinite(bytes) || bytes <= 0) {
            return '0 KB';
        }

        if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(1) + ' KB';
        }

        return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
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

    function getFileExtension(file) {

        return String(file?.name || '')
            .split('.')
            .pop()
            .toLowerCase();
    }

    function isAllowedExtension(extension) {

        return [
            'pdf',
            'jpg',
            'jpeg',
            'png'
        ].includes(extension);
    }

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

    function activateUploadMode() {

        stopCamera();

        uploadPanel.classList.remove('hidden');
        scanPanel.classList.add('hidden');

        modeUploadBtn.classList.add('active');
        modeScanBtn.classList.remove('active');

        modeUploadBtn.setAttribute('aria-selected', 'true');
        modeScanBtn.setAttribute('aria-selected', 'false');

        clearCameraError();
    }

    function activateScanMode() {

        uploadPanel.classList.add('hidden');
        scanPanel.classList.remove('hidden');

        modeUploadBtn.classList.remove('active');
        modeScanBtn.classList.add('active');

        modeUploadBtn.setAttribute('aria-selected', 'false');
        modeScanBtn.setAttribute('aria-selected', 'true');

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

    function showSelectedFile(file, note) {

        if (!selectedFile) {
            return;
        }

        selectedFile.classList.remove('hidden');

        selectedFileName.textContent = file.name;

        selectedFileSize.textContent =
            formatFileSize(file.size) +
            (note ? ' • ' + note : '');

        uploadBox?.classList.add('has-file');
    }

    function clearFileSelection() {

        if (fileInput) {
            fileInput.value = '';
        }

        if (selectedFile) {
            selectedFile.classList.add('hidden');
        }

        if (selectedFileName) {
            selectedFileName.textContent = '';
        }

        if (selectedFileSize) {
            selectedFileSize.textContent = '';
        }

        uploadBox?.classList.remove('has-file');
    }

    clearFileBtn?.addEventListener(
        'click',
        clearFileSelection
    );

    fileInput.addEventListener(
        'change',
        async function () {

            const file = fileInput.files?.[0];

            if (!file) {
                return;
            }

            clearCameraError();

            const extension = getFileExtension(file);

            if (!isAllowedExtension(extension)) {

                clearFileSelection();

                alert(
                    'Format file tidak didukung.\n\n' +
                    'Gunakan PDF, JPG, JPEG, atau PNG.'
                );

                return;
            }

            if (file.size <= 0) {

                clearFileSelection();

                alert('File kosong atau tidak valid.');

                return;
            }

            if (file.size > MAX_FILE_SIZE) {

                clearFileSelection();

                alert(
                    'Ukuran file terlalu besar.\n\n' +
                    'Maksimal file asli adalah 10 MB.'
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
                    'PDF • tanpa kompresi'
                );

                return;
            }

            try {

                showSelectedFile(
                    file,
                    'Sedang mengompres...'
                );

                const originalSize = file.size;

                const compressedFile =
                    await compressImageFile(file);

                let finalFile = compressedFile;

                if (compressedFile.size >= originalSize) {
                    finalFile = file;
                }

                if (finalFile.size > MAX_FILE_SIZE) {

                    clearFileSelection();

                    alert(
                        'Gambar masih terlalu besar setelah dikompres.\n\n' +
                        'Silakan pilih gambar dengan resolusi lebih rendah.'
                    );

                    return;
                }

                const dataTransfer = new DataTransfer();

                dataTransfer.items.add(finalFile);
                fileInput.files = dataTransfer.files;

                capturedInput.value = '';
                clearScanResult();

                const reduction =
                    getReductionPercent(
                        originalSize,
                        finalFile.size
                    );

                const note =
                    finalFile === file
                        ? 'Gambar asli digunakan • ' +
                          formatFileSize(finalFile.size)
                        : 'Dikompres ' +
                          reduction +
                          '% • ' +
                          formatFileSize(originalSize) +
                          ' → ' +
                          formatFileSize(finalFile.size);

                showSelectedFile(
                    finalFile,
                    note
                );

            } catch (error) {

                console.error(
                    'Compression error:',
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

    function loadImage(file) {

        return new Promise(function (resolve, reject) {

            const objectUrl =
                URL.createObjectURL(file);

            const image = new Image();

            image.onload = function () {

                URL.revokeObjectURL(objectUrl);
                resolve(image);
            };

            image.onerror = function () {

                URL.revokeObjectURL(objectUrl);

                reject(
                    new Error(
                        'Gambar tidak dapat dibaca oleh browser.'
                    )
                );
            };

            image.src = objectUrl;
        });
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

        maxDimensions.forEach(function (maxDimension) {

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
                result.some(function (existing) {

                    return (
                        existing.width ===
                            dimensions.width &&
                        existing.height ===
                            dimensions.height
                    );
                });

            if (!duplicate) {
                result.push(dimensions);
            }
        });

        return result;
    }

    function canvasToFile(
        image,
        width,
        height,
        quality,
        originalName
    ) {

        return new Promise(function (resolve, reject) {

            const canvas =
                document.createElement('canvas');

            canvas.width = width;
            canvas.height = height;

            const context =
                canvas.getContext(
                    '2d',
                    { alpha: false }
                );

            if (!context) {

                reject(
                    new Error(
                        'Browser tidak mendukung pemrosesan gambar.'
                    )
                );

                return;
            }

            context.fillStyle = '#ffffff';

            context.fillRect(
                0,
                0,
                width,
                height
            );

            context.imageSmoothingEnabled = true;
            context.imageSmoothingQuality = 'high';

            context.drawImage(
                image,
                0,
                0,
                width,
                height
            );

            canvas.toBlob(
                function (blob) {

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
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            }
                        )
                    );
                },
                'image/jpeg',
                quality
            );
        });
    }

    async function compressImageFile(file) {

        const image = await loadImage(file);

        const width =
            image.naturalWidth ||
            image.width;

        const height =
            image.naturalHeight ||
            image.height;

        if (width <= 0 || height <= 0) {

            throw new Error(
                'Dimensi gambar tidak valid.'
            );
        }

        const dimensionList =
            buildDimensionList(
                width,
                height
            );

        let smallest = null;

        for (const dimensions of dimensionList) {

            for (const quality of IMAGE_QUALITIES) {

                const compressed =
                    await canvasToFile(
                        image,
                        dimensions.width,
                        dimensions.height,
                        quality,
                        file.name
                    );

                if (
                    !smallest ||
                    compressed.size <
                        smallest.size
                ) {
                    smallest = compressed;
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
            smallest.size <= IMAGE_MAX_SIZE
        ) {
            return smallest;
        }

        throw new Error(
            'Gambar masih terlalu besar setelah dikompres.'
        );
    }

    async function startCamera() {

        clearCameraError();

        if (!window.isSecureContext) {

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
                            ideal: CAMERA_MAX_WIDTH
                        },

                        height: {
                            ideal: CAMERA_MAX_HEIGHT
                        }
                    },

                    audio: false
                });

            cameraVideo.srcObject =
                cameraStream;

            await cameraVideo.play();

            cameraPlaceholder.classList.add('hidden');

            startCameraBtn.disabled = true;
            captureBtn.disabled = false;
            stopCameraBtn.disabled = false;

        } catch (error) {

            console.error(
                'Camera error:',
                error
            );

            let message =
                'Kamera tidak dapat digunakan.';

            if (
                error.name === 'NotAllowedError' ||
                error.name === 'PermissionDeniedError'
            ) {

                message =
                    'Izin kamera ditolak. Izinkan kamera pada browser.';

            } else if (
                error.name === 'NotFoundError' ||
                error.name === 'DevicesNotFoundError'
            ) {

                message =
                    'Kamera tidak ditemukan.';

            } else if (
                error.name === 'NotReadableError'
            ) {

                message =
                    'Kamera sedang digunakan aplikasi lain.';

            } else if (
                error.name === 'SecurityError'
            ) {

                message =
                    'Akses kamera diblokir oleh browser.';
            }

            showCameraError(message);
        }
    }

    startCameraBtn.addEventListener(
        'click',
        startCamera
    );

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

                const dimensions =
                    calculateDimensions(
                        cameraVideo.videoWidth,
                        cameraVideo.videoHeight,
                        CAMERA_MAX_WIDTH
                    );

                const canvas =
                    document.createElement('canvas');

                canvas.width =
                    dimensions.width;

                canvas.height =
                    dimensions.height;

                const context =
                    canvas.getContext(
                        '2d',
                        { alpha: false }
                    );

                if (!context) {

                    throw new Error(
                        'Browser tidak mendukung canvas.'
                    );
                }

                context.fillStyle = '#ffffff';

                context.fillRect(
                    0,
                    0,
                    canvas.width,
                    canvas.height
                );

                context.imageSmoothingEnabled = true;
                context.imageSmoothingQuality = 'high';

                context.drawImage(
                    cameraVideo,
                    0,
                    0,
                    canvas.width,
                    canvas.height
                );

                const dataUrl =
                    await compressCameraCanvas(
                        canvas
                    );

                if (
                    dataUrl.length >
                    CAMERA_MAX_DATA_URL_LENGTH
                ) {

                    throw new Error(
                        'Hasil scan terlalu besar. Silakan scan ulang.'
                    );
                }

                capturedInput.value =
                    dataUrl;

                await showScanResult(
                    dataUrl
                );

                clearFileSelection();
                stopCamera();

            } catch (error) {

                console.error(
                    'Capture error:',
                    error
                );

                showCameraError(
                    error?.message ||
                    'Gagal mengambil hasil scan.'
                );
            }
        }
    );

    async function compressCameraCanvas(canvas) {

        let currentCanvas = canvas;
        let quality = 0.82;

        let dataUrl =
            currentCanvas.toDataURL(
                'image/jpeg',
                quality
            );

        while (
            getDataUrlBinarySize(dataUrl) >
                CAMERA_TARGET_SIZE &&
            quality >
                0.40
        ) {

            quality -= 0.06;

            dataUrl =
                currentCanvas.toDataURL(
                    'image/jpeg',
                    quality
                );
        }

        let attempt = 0;

        while (
            getDataUrlBinarySize(dataUrl) >
                CAMERA_TARGET_SIZE &&
            attempt < 4
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
                    { alpha: false }
                );

            if (!smallerContext) {

                throw new Error(
                    'Browser tidak mendukung resize kamera.'
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

            quality = 0.60;

            dataUrl =
                currentCanvas.toDataURL(
                    'image/jpeg',
                    quality
                );
        }

        return dataUrl;
    }

    function getDataUrlBinarySize(dataUrl) {

        const commaIndex =
            dataUrl.indexOf(',');

        if (commaIndex === -1) {
            return 0;
        }

        const base64 =
            dataUrl.substring(
                commaIndex + 1
            );

        const padding =
            base64.endsWith('==')
                ? 2
                : base64.endsWith('=')
                    ? 1
                    : 0;

        return Math.floor(
            (base64.length * 3) / 4
        ) - padding;
    }

    async function showScanResult(dataUrl) {

        if (previewObjectUrl) {

            URL.revokeObjectURL(
                previewObjectUrl
            );

            previewObjectUrl = null;
        }

        const response =
            await fetch(dataUrl);

        const blob =
            await response.blob();

        previewObjectUrl =
            URL.createObjectURL(blob);

        scanPreviewImage.src =
            previewObjectUrl;

        scanCompressionInfo.textContent =
            'Hasil scan: ' +
            formatFileSize(blob.size) +
            ' • JPEG terkompresi';

        scanResult.classList.remove(
            'hidden'
        );
    }

    function clearScanResult() {

        capturedInput.value = '';

        scanResult.classList.add(
            'hidden'
        );

        scanPreviewImage.src = '';

        scanCompressionInfo.textContent = '';

        if (previewObjectUrl) {

            URL.revokeObjectURL(
                previewObjectUrl
            );

            previewObjectUrl = null;
        }
    }

    function stopCameraTracks() {

        if (!cameraStream) {
            return;
        }

        cameraStream
            .getTracks()
            .forEach(function (track) {
                track.stop();
            });

        cameraStream = null;
    }

    function stopCamera() {

        stopCameraTracks();

        if (cameraVideo) {
            cameraVideo.srcObject = null;
        }

        cameraPlaceholder.classList.remove(
            'hidden'
        );

        startCameraBtn.disabled = false;
        captureBtn.disabled = true;
        stopCameraBtn.disabled = true;
    }

    stopCameraBtn.addEventListener(
        'click',
        function () {

            stopCamera();

            if (capturedInput.value) {
                cameraPlaceholder.classList.add(
                    'hidden'
                );
            }
        }
    );

    retakeBtn.addEventListener(
        'click',
        async function () {

            clearScanResult();
            capturedInput.value = '';

            await startCamera();
        }
    );

    form.addEventListener(
        'submit',
        function (event) {

            if (submitting) {

                event.preventDefault();

                return;
            }

            const selectedFileElement =
                fileInput.files?.[0] || null;

            const hasFile =
                !!selectedFileElement;

            const hasCamera =
                capturedInput.value.trim() !== '';

            if (hasFile && hasCamera) {

                event.preventDefault();

                alert(
                    'Gunakan salah satu metode saja: Upload File atau Scan Kamera.'
                );

                return;
            }

            if (
                hasFile &&
                selectedFileElement.size >
                    MAX_FILE_SIZE
            ) {

                event.preventDefault();

                alert(
                    'Ukuran file melebihi 10 MB.'
                );

                return;
            }

            if (
                hasCamera &&
                capturedInput.value.length >
                    CAMERA_MAX_DATA_URL_LENGTH
            ) {

                event.preventDefault();

                alert(
                    'Hasil scan terlalu besar. Silakan scan ulang.'
                );

                return;
            }

            submitting = true;

            stopCamera();

            submitBtn.disabled = true;

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

    if (capturedInput.value) {

        activateScanMode();

        scanResult.classList.remove(
            'hidden'
        );

        scanPreviewImage.src =
            capturedInput.value;

        scanCompressionInfo.textContent =
            'Hasil scan sebelumnya masih tersedia.';
    }

    window.addEventListener(
        'beforeunload',
        function () {

            stopCameraTracks();

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