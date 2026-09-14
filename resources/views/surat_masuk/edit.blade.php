@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')

@php
    use Illuminate\Support\Carbon;

    /*
    |--------------------------------------------------------------------------
    | TANGGAL SURAT
    |--------------------------------------------------------------------------
    */

    $tanggalSurat = old('tanggal_surat');

    if ($tanggalSurat === null && $suratMasuk->tanggal_surat) {
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


    /*
    |--------------------------------------------------------------------------
    | TANGGAL TERIMA
    |--------------------------------------------------------------------------
    */

    $tanggalTerima = old('tanggal_terima');

    if ($tanggalTerima === null && $suratMasuk->tanggal_terima) {
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


    /*
    |--------------------------------------------------------------------------
    | KATEGORI
    |--------------------------------------------------------------------------
    */

    $selectedKategori = old(
        'kategori_surat_id',
        $suratMasuk->kategori_surat_id
    );


    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | LAMPIRAN LAMA
    |--------------------------------------------------------------------------
    */

    $hasCurrentAttachment = !empty(
        trim(
            (string) $suratMasuk->lampiran_file
        )
    );

    $currentAttachmentPath = $hasCurrentAttachment
        ? trim(
            (string) $suratMasuk->lampiran_file
        )
        : null;

    $currentAttachmentName = $hasCurrentAttachment
        ? basename(
            $currentAttachmentPath
        )
        : null;

    $currentAttachmentExtension = $hasCurrentAttachment
        ? strtolower(
            pathinfo(
                $currentAttachmentPath,
                PATHINFO_EXTENSION
            )
        )
        : null;
@endphp


<style>
/* ============================================================
   PAGE
============================================================ */

.sme-page {
    width: 100%;
    max-width: 1040px;
    margin: 0 auto;
    padding: 12px 18px 32px;
    color: #334155;
}

.sme-page *,
.sme-page *::before,
.sme-page *::after {
    box-sizing: border-box;
}


/* ============================================================
   TOP ACTION
============================================================ */

.sme-top-action {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
}

.sme-back-btn {
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

.sme-back-btn:hover {
    border-color: #6366f1;
    background: #eef2ff;
    color: #4338ca;
}


/* ============================================================
   ERROR
============================================================ */

.sme-error-box {
    margin-bottom: 12px;
    padding: 11px 13px;
    border: 1.5px solid #fecaca;
    border-radius: 9px;
    background: #fff7f7;
}

.sme-error-inner {
    display: flex;
    align-items: flex-start;
    gap: 9px;
}

.sme-error-icon {
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

.sme-error-title {
    margin: 0;
    font-size: 10px;
    line-height: 1.35;
    font-weight: 800;
    color: #be123c;
}

.sme-error-list {
    margin: 3px 0 0;
    padding-left: 16px;
    font-size: 9px;
    line-height: 1.5;
    color: #e11d48;
}


/* ============================================================
   MAIN CARD
============================================================ */

.sme-main-card {
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

.sme-system-header {
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

.sme-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}

.sme-header-icon {
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

.sme-header-label {
    margin: 0;
    font-size: 8px;
    line-height: 1.2;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #4f46e5;
}

.sme-header-title {
    margin: 2px 0 0;
    font-size: 17px;
    line-height: 1.3;
    font-weight: 800;
    color: #1e293b;
}

.sme-header-subtitle {
    margin: 3px 0 0;
    font-size: 9px;
    line-height: 1.4;
    color: #64748b;
}

.sme-header-badge {
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

.sme-body {
    padding: 16px;
}


/* ============================================================
   SECTION
============================================================ */

.sme-section {
    width: 100%;
}

.sme-section + .sme-section {
    margin-top: 17px;
    padding-top: 17px;
    border-top: 1.5px solid #e2e8f0;
}

.sme-section-head {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    margin-bottom: 10px;
}

.sme-section-marker {
    width: 4px;
    height: 27px;
    flex: 0 0 4px;
    margin-top: 1px;
    border-radius: 999px;
    background: #2563eb;
}

.sme-section-marker-indigo {
    background: #4f46e5;
}

.sme-section-title {
    margin: 0;
    font-size: 13px;
    line-height: 1.3;
    font-weight: 800;
    color: #1e293b;
}

.sme-section-description {
    margin: 3px 0 0;
    font-size: 8.5px;
    line-height: 1.4;
    color: #64748b;
}


/* ============================================================
   FIELD TABLE
============================================================ */

.sme-field-table {
    overflow: hidden;
    border: 1.5px solid #94a3b8;
    border-radius: 9px;
    background: #ffffff;
}

.sme-field-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.sme-field {
    min-width: 0;
    padding: 11px 12px;
    border-right: 1.5px solid #cbd5e1;
    border-bottom: 1.5px solid #cbd5e1;
    background: #ffffff;
}

.sme-field:nth-child(2n) {
    border-right: 0;
}

.sme-field-full {
    grid-column: 1 / -1;
    border-right: 0;
}

.sme-field:last-child {
    border-bottom: 0;
}


/* ============================================================
   LABEL
============================================================ */

.sme-field-label {
    display: block;
    margin-bottom: 5px;
    font-size: 9px;
    line-height: 1.3;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .025em;
    color: #475569;
}

.sme-required {
    color: #dc2626;
}


/* ============================================================
   CONTROL
============================================================ */

.sme-control {
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

input.sme-control,
select.sme-control {
    height: 40px;
    padding: 0 10px;
}

textarea.sme-control {
    min-height: 82px;
    padding: 9px 10px;
    line-height: 1.5;
    resize: vertical;
}

.sme-control::placeholder {
    color: #94a3b8;
}

.sme-control:hover {
    border-color: #64748b;
}

.sme-control:focus {
    border-color: #2563eb;
    box-shadow:
        0 0 0 3px rgba(37,99,235,.08);
}

.sme-control-error {
    border-color: #ef4444 !important;
    background: #fff7f7 !important;
}

.sme-field-error {
    margin: 4px 0 0;
    font-size: 8px;
    line-height: 1.4;
    font-weight: 700;
    color: #dc2626;
}


/* ============================================================
   ATTACHMENT GRID
============================================================ */

.sme-attachment-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    align-items: stretch;
}

.sme-attachment-card {
    min-width: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1.5px solid #94a3b8;
    border-radius: 9px;
    background: #ffffff;
}

.sme-attachment-header {
    min-height: 54px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 9px 11px;
    border-bottom: 1.5px solid #cbd5e1;
    background: #f8fafc;
}

.sme-attachment-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}

.sme-attachment-icon {
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

.sme-attachment-icon.physical {
    border-color: #c7d2fe;
    color: #4f46e5;
}

.sme-attachment-title {
    margin: 0;
    font-size: 10px;
    line-height: 1.3;
    font-weight: 800;
    color: #1e293b;
}

.sme-attachment-description {
    margin: 2px 0 0;
    font-size: 7.5px;
    line-height: 1.4;
    color: #64748b;
}

.sme-attachment-badge {
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

.sme-attachment-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 8px;
    padding: 10px;
}


/* ============================================================
   INFO BOX
============================================================ */

.sme-info {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    padding: 8px;
    border: 1px solid #bfdbfe;
    border-radius: 7px;
    background: #eff6ff;
}

.sme-info svg {
    width: 14px;
    height: 14px;
    flex: 0 0 14px;
    margin-top: 1px;
    color: #2563eb;
}

.sme-info p {
    margin: 0;
    font-size: 7.5px;
    line-height: 1.5;
    color: #1d4ed8;
}


/* ============================================================
   MODE
============================================================ */

.sme-mode-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 6px;
}

.sme-mode-btn {
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

.sme-mode-btn:hover {
    border-color: #818cf8;
    background: #eef2ff;
}

.sme-mode-btn.active {
    border-color: #6366f1;
    background: #eef2ff;
    color: #4338ca;
}

.sme-mode-icon {
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

.sme-mode-btn.active .sme-mode-icon {
    background: #e0e7ff;
    color: #4f46e5;
}

.sme-mode-name {
    display: block;
    font-size: 8px;
    line-height: 1.3;
    font-weight: 800;
    color: #334155;
}

.sme-mode-desc {
    display: block;
    margin-top: 1px;
    font-size: 7px;
    line-height: 1.3;
    color: #64748b;
}


/* ============================================================
   CURRENT FILE
============================================================ */

.sme-current-file {
    display: flex;
    align-items: center;
    gap: 7px;
    min-height: 46px;
    padding: 7px;
    border: 1.5px solid #dbe2ea;
    border-radius: 7px;
    background: #f8fafc;
}

.sme-current-file-icon {
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

.sme-current-file-content {
    min-width: 0;
    flex: 1;
}

.sme-current-file-label {
    margin: 0;
    font-size: 7px;
    line-height: 1.2;
    font-weight: 800;
    color: #64748b;
}

.sme-current-file-name {
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

.sme-current-file-empty {
    margin: 0;
    font-size: 8px;
    line-height: 1.3;
    font-weight: 800;
    color: #b45309;
}

.sme-current-file-empty-text {
    margin: 1px 0 0;
    font-size: 7px;
    color: #64748b;
}

.sme-view-btn {
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

.sme-view-btn:hover {
    background: #c7d2fe;
}


/* ============================================================
   UPLOAD BOX
============================================================ */

.sme-upload-box {
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

.sme-upload-box:hover {
    border-color: #6366f1;
    background: #eef2ff;
}

.sme-upload-box.has-file {
    border-color: #34d399;
    background: #ecfdf5;
}

.sme-upload-icon {
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

.sme-upload-box.has-file .sme-upload-icon {
    background: #d1fae5;
    color: #059669;
}

.sme-upload-title {
    font-size: 8px;
    line-height: 1.3;
    font-weight: 800;
    color: #334155;
}

.sme-upload-format {
    margin-top: 2px;
    font-size: 7px;
    color: #64748b;
}

.sme-upload-limit {
    margin-top: 3px;
    padding: 3px 7px;
    border-radius: 999px;
    background: #dbeafe;
    color: #2563eb;
    font-size: 6.5px;
    font-weight: 700;
}

.sme-upload-input {
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

.sme-selected-file {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px;
    border: 1.5px solid #a7f3d0;
    border-radius: 7px;
    background: #ecfdf5;
}

.sme-selected-icon {
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

.sme-selected-content {
    min-width: 0;
    flex: 1;
}

.sme-selected-title {
    margin: 0;
    font-size: 7px;
    line-height: 1.2;
    font-weight: 800;
    color: #047857;
}

.sme-selected-name {
    margin: 1px 0 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 7.5px;
    line-height: 1.3;
    font-weight: 700;
    color: #334155;
}

.sme-selected-size {
    margin: 1px 0 0;
    font-size: 6.5px;
    line-height: 1.3;
    color: #64748b;
}

.sme-clear-btn {
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

.sme-clear-btn:hover {
    background: #fee2e2;
    color: #dc2626;
}


/* ============================================================
   COMPRESSION
============================================================ */

.sme-compression {
    padding: 8px 9px;
    border: 1px solid #c7d2fe;
    border-radius: 7px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 7px;
    line-height: 1.55;
}

.sme-compression.success {
    border-color: #a7f3d0;
    background: #ecfdf5;
    color: #047857;
}

.sme-compression.warning {
    border-color: #fde68a;
    background: #fffbeb;
    color: #a16207;
}

.sme-compression.error {
    border-color: #fecaca;
    background: #fff1f2;
    color: #be123c;
}


/* ============================================================
   CAMERA
============================================================ */

.sme-camera-box {
    overflow: hidden;
    border: 1.5px solid #475569;
    border-radius: 8px;
    background: #0f172a;
}

.sme-camera-preview {
    position: relative;
    min-height: 220px;
    background: #0f172a;
}

.sme-camera-video {
    width: 100%;
    height: 220px;
    display: block;
    object-fit: contain;
    background: #0f172a;
}

.sme-camera-image {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    display: block;
    object-fit: contain;
    background: #0f172a;
}

.sme-camera-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px;
    background: #0f172a;
    text-align: center;
}

.sme-camera-placeholder-icon {
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

.sme-camera-placeholder-title {
    margin: 0;
    font-size: 8px;
    font-weight: 800;
    color: #cbd5e1;
}

.sme-camera-placeholder-desc {
    margin: 2px 0 0;
    font-size: 6.5px;
    line-height: 1.35;
    color: #64748b;
}

.sme-camera-error {
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

.sme-camera-frame {
    position: absolute;
    inset: 22px;
    border: 1px dashed rgba(255,255,255,.28);
    border-radius: 6px;
    pointer-events: none;
}

.sme-camera-actions {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 5px;
    padding: 6px;
    background: #111827;
}

.sme-camera-btn {
    min-height: 31px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
    border-radius: 6px;
    font-size: 7px;
    font-weight: 800;
    cursor: pointer;
}

.sme-camera-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.sme-camera-primary {
    border: 1px solid #4338ca;
    background: #4f46e5;
    color: #ffffff;
}

.sme-camera-success {
    border: 1px solid #059669;
    background: #10b981;
    color: #ffffff;
}

.sme-camera-secondary {
    border: 1px solid #64748b;
    background: #ffffff;
    color: #334155;
}


/* ============================================================
   SCAN RESULT
============================================================ */

.sme-scan-result {
    padding: 8px;
    border-top: 1.5px solid #6ee7b7;
    background: #ecfdf5;
}

.sme-scan-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
}

.sme-scan-title {
    margin: 0;
    font-size: 8px;
    line-height: 1.3;
    font-weight: 800;
    color: #065f46;
}

.sme-scan-desc {
    margin: 2px 0 0;
    font-size: 6.8px;
    line-height: 1.4;
    color: #047857;
}

.sme-scan-preview {
    display: block;
    width: 100%;
    max-height: 240px;
    margin-top: 6px;
    object-fit: contain;
    border: 1px solid #a7f3d0;
    border-radius: 6px;
    background: #ffffff;
}

.sme-scan-info {
    margin-top: 4px;
    text-align: center;
    font-size: 7px;
    line-height: 1.4;
    font-weight: 800;
    color: #047857;
}

.sme-retake {
    min-height: 25px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 8px;
    border: 1px solid #6ee7b7;
    border-radius: 6px;
    background: #ffffff;
    color: #047857;
    font-size: 7px;
    font-weight: 800;
    cursor: pointer;
}


/* ============================================================
   PHYSICAL
============================================================ */

.sme-physical-box {
    display: flex;
    flex: 1;
    flex-direction: column;
    justify-content: center;
    padding: 13px;
    border: 1.5px dashed #94a3b8;
    border-radius: 8px;
    background: #f8fafc;
}

.sme-physical-icon {
    width: 37px;
    height: 37px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    background: #e0e7ff;
    color: #4f46e5;
}

.sme-physical-title {
    margin: 7px 0 0;
    font-size: 10px;
    line-height: 1.3;
    font-weight: 800;
    color: #334155;
}

.sme-physical-desc {
    margin: 3px 0 0;
    font-size: 7.5px;
    line-height: 1.5;
    color: #64748b;
}

.sme-physical-field {
    margin-top: 11px;
}

.sme-example {
    margin-top: 6px;
    padding: 7px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
}

.sme-example p {
    margin: 0;
    font-size: 7px;
    line-height: 1.45;
    color: #64748b;
}

.sme-example strong {
    color: #334155;
}

.sme-note {
    margin-top: auto;
    padding: 7px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
}

.sme-note p {
    margin: 0;
    font-size: 7px;
    line-height: 1.45;
    color: #64748b;
}


/* ============================================================
   FOOTER
============================================================ */

.sme-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 7px;
    padding: 10px 14px;
    border-top: 1.5px solid #cbd5e1;
    background: #f8fafc;
}

.sme-footer-btn {
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

.sme-footer-cancel {
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
}

.sme-footer-cancel:hover {
    background: #f1f5f9;
}

.sme-footer-submit {
    border: 1.5px solid #2563eb;
    background: #2563eb;
    color: #ffffff;
}

.sme-footer-submit:hover:not(:disabled) {
    background: #1d4ed8;
}

.sme-footer-submit:disabled {
    opacity: .6;
    cursor: not-allowed;
}


/* ============================================================
   UTILITY
============================================================ */

.sme-hidden {
    display: none !important;
}


/* ============================================================
   RESPONSIVE
============================================================ */

@media (max-width: 820px) {

    .sme-page {
        max-width: 760px;
        padding-left: 12px;
        padding-right: 12px;
    }

    .sme-attachment-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 640px) {

    .sme-page {
        padding: 6px 9px 20px;
    }

    .sme-top-action {
        justify-content: stretch;
    }

    .sme-back-btn {
        width: 100%;
    }

    .sme-system-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .sme-header-badge {
        align-self: flex-start;
    }

    .sme-body {
        padding: 11px;
    }

    .sme-field-grid {
        grid-template-columns: 1fr;
    }

    .sme-field,
    .sme-field:nth-child(2n) {
        padding: 10px;
        border-right: 0;
    }

    .sme-field-full {
        grid-column: auto;
    }

    .sme-mode-grid {
        grid-template-columns: 1fr;
    }

    .sme-camera-actions {
        grid-template-columns: 1fr;
    }

    .sme-scan-top {
        flex-direction: column;
    }

    .sme-retake {
        width: 100%;
    }

    .sme-footer {
        flex-direction: column-reverse;
        align-items: stretch;
    }

    .sme-footer-btn {
        width: 100%;
    }
}

@media (max-width: 420px) {

    .sme-system-header {
        padding: 12px;
    }

    .sme-header-title {
        font-size: 15px;
    }

    .sme-body {
        padding: 9px;
    }

    .sme-field {
        padding: 9px;
    }

    .sme-attachment-body {
        padding: 8px;
    }

    .sme-control {
        font-size: 11px;
    }

    .sme-camera-preview {
        min-height: 200px;
    }

    .sme-camera-video {
        height: 200px;
    }
}
</style>


<div class="sme-page">

    {{-- =========================================================
         KEMBALI
    ========================================================== --}}

    <div class="sme-top-action">

        <a
            href="{{ route('surat-masuk.index') }}"
            class="sme-back-btn"
        >

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

        <div class="sme-error-box">

            <div class="sme-error-inner">

                <div class="sme-error-icon">

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
                            d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 013.42 0z"
                        />
                    </svg>

                </div>

                <div>

                    <p class="sme-error-title">
                        Data belum dapat diperbarui.
                    </p>

                    <ul class="sme-error-list">

                        @foreach($errors->all() as $error)

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
        id="sme-form"
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


        <div class="sme-main-card">

            {{-- =================================================
                 HEADER
            ================================================== --}}

            <div class="sme-system-header">

                <div class="sme-header-left">

                    <div class="sme-header-icon">

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

                        <p class="sme-header-label">
                            Sistem Arsip Masuk
                        </p>

                        <h1 class="sme-header-title">
                            Edit Surat Masuk
                        </h1>

                        <p class="sme-header-subtitle">
                            Perbarui data surat masuk yang tersimpan
                            dalam arsip digital.
                        </p>

                    </div>

                </div>


                <span class="sme-header-badge">
                    Arsip Digital
                </span>

            </div>


            {{-- =================================================
                 BODY
            ================================================== --}}

            <div class="sme-body">

                {{-- =================================================
                     INFORMASI UTAMA
                ================================================== --}}

                <section class="sme-section">

                    <div class="sme-section-head">

                        <div class="sme-section-marker"></div>

                        <div>

                            <h2 class="sme-section-title">
                                Informasi Utama Surat
                            </h2>

                            <p class="sme-section-description">
                                Perbarui identitas dan informasi utama surat masuk.
                            </p>

                        </div>

                    </div>


                    <div class="sme-field-table">

                        <div class="sme-field-grid">

                            {{-- NOMOR SURAT --}}

                            <div class="sme-field">

                                <label
                                    for="nomor_surat"
                                    class="sme-field-label"
                                >
                                    Nomor Surat
                                    <span class="sme-required">*</span>
                                </label>

                                <input
                                    id="nomor_surat"
                                    name="nomor_surat"
                                    type="text"
                                    maxlength="255"
                                    required
                                    autocomplete="off"
                                    value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}"
                                    placeholder="Contoh: 005/B/I/2026"
                                    class="sme-control {{ $errors->has('nomor_surat') ? 'sme-control-error' : '' }}"
                                >

                                @error('nomor_surat')

                                    <p class="sme-field-error">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>


                            {{-- PENGIRIM --}}

                            <div class="sme-field">

                                <label
                                    for="pengirim"
                                    class="sme-field-label"
                                >
                                    Instansi Pengirim
                                    <span class="sme-required">*</span>
                                </label>

                                <input
                                    id="pengirim"
                                    name="pengirim"
                                    type="text"
                                    maxlength="255"
                                    required
                                    autocomplete="organization"
                                    value="{{ old('pengirim', $suratMasuk->pengirim) }}"
                                    placeholder="Nama instansi pengirim"
                                    class="sme-control {{ $errors->has('pengirim') ? 'sme-control-error' : '' }}"
                                >

                                @error('pengirim')

                                    <p class="sme-field-error">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>


                            {{-- TANGGAL SURAT --}}

                            <div class="sme-field">

                                <label
                                    for="tanggal_surat"
                                    class="sme-field-label"
                                >
                                    Tanggal Surat
                                    <span class="sme-required">*</span>
                                </label>

                                <input
                                    id="tanggal_surat"
                                    name="tanggal_surat"
                                    type="date"
                                    required
                                    value="{{ $tanggalSurat }}"
                                    class="sme-control {{ $errors->has('tanggal_surat') ? 'sme-control-error' : '' }}"
                                >

                                @error('tanggal_surat')

                                    <p class="sme-field-error">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>


                            {{-- TANGGAL TERIMA --}}

                            <div class="sme-field">

                                <label
                                    for="tanggal_terima"
                                    class="sme-field-label"
                                >
                                    Tanggal Diterima
                                    <span class="sme-required">*</span>
                                </label>

                                <input
                                    id="tanggal_terima"
                                    name="tanggal_terima"
                                    type="date"
                                    required
                                    value="{{ $tanggalTerima }}"
                                    class="sme-control {{ $errors->has('tanggal_terima') ? 'sme-control-error' : '' }}"
                                >

                                @error('tanggal_terima')

                                    <p class="sme-field-error">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>


                            {{-- KATEGORI --}}

                            <div class="sme-field">

                                <label
                                    for="kategori_surat_id"
                                    class="sme-field-label"
                                >
                                    Kategori Surat
                                    <span class="sme-required">*</span>
                                </label>

                                <select
                                    id="kategori_surat_id"
                                    name="kategori_surat_id"
                                    required
                                    class="sme-control {{ $errors->has('kategori_surat_id') ? 'sme-control-error' : '' }}"
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

                                    <p class="sme-field-error">
                                        Belum ada kategori surat yang tersedia.
                                    </p>

                                @endif

                                @error('kategori_surat_id')

                                    <p class="sme-field-error">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>


                            {{-- STATUS --}}

                            <div class="sme-field">

                                <label
                                    for="status"
                                    class="sme-field-label"
                                >
                                    Status Surat
                                    <span class="sme-required">*</span>
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    required
                                    class="sme-control {{ $errors->has('status') ? 'sme-control-error' : '' }}"
                                >

                                    @foreach($statusOptions as $value => $label)

                                        <option
                                            value="{{ $value }}"
                                            @selected(
                                                $currentStatus === $value
                                            )
                                        >
                                            {{ $label }}
                                        </option>

                                    @endforeach

                                </select>

                                @error('status')

                                    <p class="sme-field-error">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>


                            {{-- PERIHAL --}}

                            <div class="sme-field sme-field-full">

                                <label
                                    for="perihal"
                                    class="sme-field-label"
                                >
                                    Perihal
                                    <span class="sme-required">*</span>
                                </label>

                                <textarea
                                    id="perihal"
                                    name="perihal"
                                    rows="3"
                                    maxlength="1000"
                                    required
                                    placeholder="Tuliskan perihal surat"
                                    class="sme-control {{ $errors->has('perihal') ? 'sme-control-error' : '' }}"
                                >{{ old('perihal', $suratMasuk->perihal) }}</textarea>

                                @error('perihal')

                                    <p class="sme-field-error">
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

                <section class="sme-section">

                    <div class="sme-section-head">

                        <div class="sme-section-marker sme-section-marker-indigo"></div>

                        <div>

                            <h2 class="sme-section-title">
                                Lampiran Dokumen & Arsip Fisik
                            </h2>

                            <p class="sme-section-description">
                                Periksa lampiran lama atau upload/scan
                                dokumen pengganti.
                            </p>

                        </div>

                    </div>


                    <div class="sme-attachment-grid">

                        {{-- =================================================
                             DIGITAL
                        ================================================== --}}

                        <div class="sme-attachment-card">

                            <div class="sme-attachment-header">

                                <div class="sme-attachment-header-left">

                                    <div class="sme-attachment-icon">

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

                                        <h3 class="sme-attachment-title">
                                            Berkas Digital
                                        </h3>

                                        <p class="sme-attachment-description">
                                            PDF, JPG, JPEG, PNG · Maksimal 10 MB
                                        </p>

                                    </div>

                                </div>


                                <span class="sme-attachment-badge">
                                    Opsional
                                </span>

                            </div>


                            <div class="sme-attachment-body">

                                {{-- INFO --}}

                                <div class="sme-info">

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
                                            d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000-18z"
                                        />
                                    </svg>

                                    <p>
                                        <strong>Maksimal 10 MB.</strong>
                                        PDF tetap PDF.
                                        JPG/JPEG/PNG otomatis diproses,
                                        di-resize bila diperlukan,
                                        lalu dikompres menjadi JPG.
                                    </p>

                                </div>


                                {{-- MODE --}}

                                <div class="sme-mode-grid">

                                    <button
                                        type="button"
                                        id="sme-mode-upload"
                                        class="sme-mode-btn active"
                                        aria-selected="true"
                                    >

                                        <span class="sme-mode-icon">

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

                                            <span class="sme-mode-name">
                                                Upload File
                                            </span>

                                            <span class="sme-mode-desc">
                                                Pilih dari perangkat
                                            </span>

                                        </span>

                                    </button>


                                    <button
                                        type="button"
                                        id="sme-mode-camera"
                                        class="sme-mode-btn"
                                        aria-selected="false"
                                    >

                                        <span class="sme-mode-icon">

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

                                        </span>

                                        <span>

                                            <span class="sme-mode-name">
                                                Scan Kamera
                                            </span>

                                            <span class="sme-mode-desc">
                                                Scan dokumen baru
                                            </span>

                                        </span>

                                    </button>

                                </div>


                                {{-- FILE LAMA --}}

                                <div class="sme-current-file">

                                    <div class="sme-current-file-icon">

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
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a2 2 0 011.414.414l4.414 4.414A2 2 0 0118 8.414V19a2 2 0 01-2 2z"
                                            />
                                        </svg>

                                    </div>


                                    <div class="sme-current-file-content">

                                        @if($hasCurrentAttachment)

                                            <p class="sme-current-file-label">
                                                Lampiran tersimpan
                                            </p>

                                            <span
                                                class="sme-current-file-name"
                                                title="{{ $currentAttachmentName }}"
                                            >
                                                {{ $currentAttachmentName }}
                                            </span>

                                        @else

                                            <p class="sme-current-file-empty">
                                                Belum ada lampiran
                                            </p>

                                            <p class="sme-current-file-empty-text">
                                                Anda dapat upload atau scan dokumen baru.
                                            </p>

                                        @endif

                                    </div>


                                    @if($hasCurrentAttachment)

                                        <a
                                            href="{{ route('surat-masuk.preview-lampiran', $suratMasuk->id) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="sme-view-btn"
                                        >
                                            Lihat
                                        </a>

                                    @endif

                                </div>


                                {{-- =================================================
                                     UPLOAD PANEL
                                ================================================== --}}

                                <div id="sme-upload-panel">

                                    <label
                                        id="sme-upload-box"
                                        for="lampiran_file"
                                        class="sme-upload-box"
                                    >

                                        <span class="sme-upload-icon">

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
                                                    d="M12 16V4m0 0L7 9m5-5l5 5M5 20h14"
                                                />
                                            </svg>

                                        </span>


                                        <span
                                            id="sme-upload-title"
                                            class="sme-upload-title"
                                        >
                                            Pilih file baru
                                        </span>


                                        <span class="sme-upload-format">
                                            PDF, JPG, JPEG, PNG
                                        </span>


                                        <span class="sme-upload-limit">
                                            Maksimal 10 MB
                                        </span>


                                        <input
                                            type="file"
                                            id="lampiran_file"
                                            name="lampiran_file"
                                            accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                            class="sme-upload-input"
                                        >

                                    </label>


                                    {{-- SELECTED FILE --}}

                                    <div
                                        id="sme-selected-file"
                                        class="sme-selected-file sme-hidden"
                                    >

                                        <div class="sme-selected-icon">

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


                                        <div class="sme-selected-content">

                                            <p class="sme-selected-title">
                                                File siap digunakan
                                            </p>

                                            <p
                                                id="sme-selected-name"
                                                class="sme-selected-name"
                                            ></p>

                                            <p
                                                id="sme-selected-size"
                                                class="sme-selected-size"
                                            ></p>

                                        </div>


                                        <button
                                            type="button"
                                            id="sme-clear-file"
                                            class="sme-clear-btn"
                                            title="Hapus file baru"
                                            aria-label="Hapus file baru"
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


                                    {{-- COMPRESSION STATUS --}}

                                    <div
                                        id="sme-compression"
                                        class="sme-compression sme-hidden"
                                    ></div>

                                </div>


                                {{-- =================================================
                                     CAMERA PANEL
                                ================================================== --}}

                                <div
                                    id="sme-camera-panel"
                                    class="sme-hidden"
                                >

                                    <div class="sme-camera-box">

                                        <div class="sme-camera-preview">

                                            <video
                                                id="sme-camera-video"
                                                class="sme-camera-video"
                                                autoplay
                                                muted
                                                playsinline
                                            ></video>


                                            <img
                                                id="sme-camera-image"
                                                src=""
                                                alt="Hasil scan dokumen"
                                                class="sme-camera-image sme-hidden"
                                            >


                                            <div
                                                id="sme-camera-placeholder"
                                                class="sme-camera-placeholder"
                                            >

                                                <div>

                                                    <div class="sme-camera-placeholder-icon">

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
                                                                d="M3 7h4l2-3h6l2 3h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z"
                                                            />

                                                            <circle
                                                                cx="12"
                                                                cy="13"
                                                                r="3"
                                                            />
                                                        </svg>

                                                    </div>


                                                    <p class="sme-camera-placeholder-title">
                                                        Kamera belum aktif
                                                    </p>


                                                    <p class="sme-camera-placeholder-desc">
                                                        Aktifkan kamera untuk scan dokumen.
                                                    </p>

                                                </div>

                                            </div>


                                            <div
                                                id="sme-camera-error"
                                                class="sme-camera-error sme-hidden"
                                                role="alert"
                                            ></div>


                                            <div class="sme-camera-frame"></div>

                                        </div>


                                        <div class="sme-camera-actions">

                                            <button
                                                type="button"
                                                id="sme-start-camera"
                                                class="sme-camera-btn sme-camera-primary"
                                            >
                                                Aktifkan Kamera
                                            </button>


                                            <button
                                                type="button"
                                                id="sme-capture"
                                                class="sme-camera-btn sme-camera-success"
                                                disabled
                                            >
                                                Ambil Gambar
                                            </button>


                                            <button
                                                type="button"
                                                id="sme-stop-camera"
                                                class="sme-camera-btn sme-camera-secondary"
                                                disabled
                                            >
                                                Matikan
                                            </button>

                                        </div>


                                        <div
                                            id="sme-scan-result"
                                            class="sme-scan-result sme-hidden"
                                        >

                                            <div class="sme-scan-top">

                                                <div class="min-w-0">

                                                    <p class="sme-scan-title">
                                                        Hasil scan siap
                                                    </p>

                                                    <p class="sme-scan-desc">
                                                        Hasil ini akan menggantikan lampiran lama
                                                        setelah update berhasil.
                                                    </p>

                                                </div>


                                                <button
                                                    type="button"
                                                    id="sme-retake"
                                                    class="sme-retake"
                                                >
                                                    Scan Ulang
                                                </button>

                                            </div>


                                            <img
                                                id="sme-scan-preview"
                                                src=""
                                                alt="Preview hasil scan"
                                                class="sme-scan-preview"
                                            >


                                            <div
                                                id="sme-scan-info"
                                                class="sme-scan-info"
                                            ></div>

                                        </div>

                                    </div>


                                    <input
                                        type="hidden"
                                        id="sme-captured-image"
                                        name="captured_image"
                                        value="{{ old('captured_image') }}"
                                    >

                                </div>


                                <div class="sme-note">

                                    <p>
                                        Tidak memilih file baru dan tidak melakukan scan
                                        berarti lampiran lama tetap dipertahankan.
                                    </p>

                                </div>


                                @error('lampiran_file')

                                    <p class="sme-field-error">
                                        {{ $message }}
                                    </p>

                                @enderror


                                @error('captured_image')

                                    <p class="sme-field-error">
                                        {{ $message }}
                                    </p>

                                @enderror

                            </div>

                        </div>


                        {{-- =================================================
                             ARSIP FISIK
                        ================================================== --}}

                        <div class="sme-attachment-card">

                            <div class="sme-attachment-header">

                                <div class="sme-attachment-header-left">

                                    <div class="sme-attachment-icon physical">

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

                                        <h3 class="sme-attachment-title">
                                            Lokasi Arsip Fisik
                                        </h3>

                                        <p class="sme-attachment-description">
                                            Posisi penyimpanan dokumen fisik.
                                        </p>

                                    </div>

                                </div>


                                <span class="sme-attachment-badge">
                                    Opsional
                                </span>

                            </div>


                            <div class="sme-attachment-body">

                                <div class="sme-physical-box">

                                    <div class="sme-physical-icon">

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


                                    <h4 class="sme-physical-title">
                                        Detail Lokasi Penyimpanan
                                    </h4>


                                    <p class="sme-physical-desc">
                                        Perbarui posisi rak, lemari, box,
                                        atau map tempat dokumen fisik disimpan.
                                    </p>


                                    <div class="sme-physical-field">

                                        <label
                                            for="lokasi_arsip_fisik"
                                            class="sme-field-label"
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
                                            class="sme-control {{ $errors->has('lokasi_arsip_fisik') ? 'sme-control-error' : '' }}"
                                        >


                                        @error('lokasi_arsip_fisik')

                                            <p class="sme-field-error">
                                                {{ $message }}
                                            </p>

                                        @enderror

                                    </div>


                                    <div class="sme-example">

                                        <p>
                                            <strong>Contoh:</strong>
                                            Rak A-3 Box 12 atau
                                            Lemari B-2 Map 07.
                                        </p>

                                    </div>


                                    <div class="sme-note">

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

            <div class="sme-footer">

                <a
                    href="{{ route('surat-masuk.index') }}"
                    class="sme-footer-btn sme-footer-cancel"
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
                    id="sme-submit"
                    class="sme-footer-btn sme-footer-submit"
                >

                    <svg
                        id="sme-submit-icon"
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
                        id="sme-submit-loading"
                        class="sme-hidden h-3.5 w-3.5"
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


                    <span id="sme-submit-text">
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

    /* =========================================================
       ELEMENT
    ========================================================= */

    const form =
        document.getElementById('sme-form');

    const modeUpload =
        document.getElementById('sme-mode-upload');

    const modeCamera =
        document.getElementById('sme-mode-camera');

    const uploadPanel =
        document.getElementById('sme-upload-panel');

    const cameraPanel =
        document.getElementById('sme-camera-panel');

    const fileInput =
        document.getElementById('lampiran_file');

    const uploadBox =
        document.getElementById('sme-upload-box');

    const uploadTitle =
        document.getElementById('sme-upload-title');

    const selectedFile =
        document.getElementById('sme-selected-file');

    const selectedName =
        document.getElementById('sme-selected-name');

    const selectedSize =
        document.getElementById('sme-selected-size');

    const clearFileBtn =
        document.getElementById('sme-clear-file');

    const compression =
        document.getElementById('sme-compression');

    const cameraVideo =
        document.getElementById('sme-camera-video');

    const cameraImage =
        document.getElementById('sme-camera-image');

    const cameraPlaceholder =
        document.getElementById('sme-camera-placeholder');

    const cameraError =
        document.getElementById('sme-camera-error');

    const startCameraBtn =
        document.getElementById('sme-start-camera');

    const captureBtn =
        document.getElementById('sme-capture');

    const stopCameraBtn =
        document.getElementById('sme-stop-camera');

    const scanResult =
        document.getElementById('sme-scan-result');

    const scanPreview =
        document.getElementById('sme-scan-preview');

    const scanInfo =
        document.getElementById('sme-scan-info');

    const retakeBtn =
        document.getElementById('sme-retake');

    const capturedInput =
        document.getElementById('sme-captured-image');

    const submitBtn =
        document.getElementById('sme-submit');

    const submitIcon =
        document.getElementById('sme-submit-icon');

    const submitLoading =
        document.getElementById('sme-submit-loading');

    const submitText =
        document.getElementById('sme-submit-text');


    if (
        !form ||
        !fileInput ||
        !capturedInput
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

    const CAMERA_MAX_DATA_URL_LENGTH =
        7 * 1024 * 1024;


    let cameraStream =
        null;

    let previewObjectUrl =
        null;

    let submitting =
        false;


    /* =========================================================
       FORMAT SIZE
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


    /* =========================================================
       REDUCTION
    ========================================================= */

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


    /* =========================================================
       EXTENSION
    ========================================================= */

    function getExtension(file) {

        return String(
            file?.name || ''
        )
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
            'sme-hidden'
        );
    }


    function clearCameraError() {

        if (!cameraError) {
            return;
        }

        cameraError.textContent =
            '';

        cameraError.classList.add(
            'sme-hidden'
        );
    }


    /* =========================================================
       COMPRESSION STATUS
    ========================================================= */

    function setCompressionStatus(
        message,
        type = ''
    ) {

        if (!compression) {
            return;
        }

        compression.classList.remove(
            'sme-hidden',
            'success',
            'warning',
            'error'
        );

        compression.classList.add(
            type
        );

        compression.innerHTML =
            message;
    }


    function clearCompressionStatus() {

        if (!compression) {
            return;
        }

        compression.innerHTML =
            '';

        compression.classList.remove(
            'success',
            'warning',
            'error'
        );

        compression.classList.add(
            'sme-hidden'
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


    function activateUploadMode() {

        stopCamera();

        uploadPanel.classList.remove(
            'sme-hidden'
        );

        cameraPanel.classList.add(
            'sme-hidden'
        );

        setModeButton(
            modeUpload,
            true
        );

        setModeButton(
            modeCamera,
            false
        );

        clearCameraError();

    }


    function activateCameraMode() {

        uploadPanel.classList.add(
            'sme-hidden'
        );

        cameraPanel.classList.remove(
            'sme-hidden'
        );

        setModeButton(
            modeUpload,
            false
        );

        setModeButton(
            modeCamera,
            true
        );

        clearFileSelection();
        clearCameraError();

    }


    modeUpload?.addEventListener(
        'click',
        activateUploadMode
    );


    modeCamera?.addEventListener(
        'click',
        activateCameraMode
    );


    /* =========================================================
       FILE DISPLAY
    ========================================================= */

    function showSelectedFile(
        file,
        note
    ) {

        if (!selectedFile) {
            return;
        }

        selectedFile.classList.remove(
            'sme-hidden'
        );

        selectedName.textContent =
            file.name;

        selectedSize.textContent =
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
            'File baru siap digunakan';
    }


    function clearFileSelection() {

        fileInput.value =
            '';

        selectedFile.classList.add(
            'sme-hidden'
        );

        selectedName.textContent =
            '';

        selectedSize.textContent =
            '';

        uploadBox.classList.remove(
            'has-file'
        );

        uploadTitle.textContent =
            'Pilih file baru';

        clearCompressionStatus();
    }


    clearFileBtn?.addEventListener(
        'click',
        function () {

            clearFileSelection();
        }
    );


    /* =========================================================
       LOAD IMAGE
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

                        resolve(image);
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

        const dimensions = [
            IMAGE_MAX_DIMENSION,
            2000,
            1800,
            1600,
            1400,
            1200,
            IMAGE_MIN_DIMENSION
        ];

        const result = [];

        dimensions.forEach(
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
                            item
                        ) {

                            return (
                                item.width ===
                                    size.width &&
                                item.height ===
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
            'Gambar masih terlalu besar setelah dikompres.'
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

            clearCameraError();


            const extension =
                getExtension(file);


            if (
                !isAllowedExtension(
                    extension
                )
            ) {

                clearFileSelection();

                window.Swal
                    ? Swal.fire({
                        icon: 'warning',
                        title: 'Format tidak didukung',
                        text: 'Gunakan PDF, JPG, JPEG, atau PNG.'
                    })
                    : alert(
                        'Gunakan PDF, JPG, JPEG, atau PNG.'
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
                    'Ukuran file asli melebihi 10 MB.'
                );

                return;
            }


            /* =================================================
               PDF
            ================================================== */

            if (
                extension === 'pdf'
            ) {

                capturedInput.value =
                    '';

                clearScanResult();

                showSelectedFile(
                    file,
                    'PDF • tanpa kompresi'
                );

                setCompressionStatus(
                    '✓ <strong>PDF tetap PDF.</strong><br>' +
                    'Ukuran file yang akan dikirim: <strong>' +
                    formatFileSize(file.size) +
                    '</strong>.',
                    'success'
                );

                return;
            }


            /* =================================================
               IMAGE
            ================================================== */

            setCompressionStatus(
                'Sedang memproses gambar dan mencari hasil kompresi terbaik...'
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


                /*
                 * Jika hasil kompresi lebih besar
                 * daripada file asli, file asli digunakan.
                 */

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

                    clearFileSelection();

                    setCompressionStatus(
                        'Hasil gambar masih melebihi batas 10 MB.',
                        'error'
                    );

                    alert(
                        'Gambar masih terlalu besar setelah dikompres.'
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


                capturedInput.value =
                    '';

                clearScanResult();


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
                        'File asli digunakan'
                    );

                    setCompressionStatus(
                        '⚠ <strong>Hasil kompresi tidak lebih kecil.</strong><br>' +
                        'File asli digunakan untuk mempertahankan kualitas.<br>' +
                        'Ukuran: <strong>' +
                        formatFileSize(
                            originalSize
                        ) +
                        '</strong>.',
                        'warning'
                    );

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


                    setCompressionStatus(
                        '✓ <strong>Kompresi berhasil.</strong><br>' +
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
                }

            } catch (error) {

                console.error(
                    'Compression error:',
                    error
                );

                clearFileSelection();

                setCompressionStatus(
                    'Gagal memproses gambar.',
                    'error'
                );

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

    startCameraBtn?.addEventListener(
        'click',
        startCamera
    );


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


            cameraPlaceholder.classList.add(
                'sme-hidden'
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
                    'NotAllowedError' ||
                error.name ===
                    'PermissionDeniedError'
            ) {

                message =
                    'Izin kamera ditolak. Izinkan kamera pada browser.';

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


    /* =========================================================
       CAPTURE CAMERA
    ========================================================= */

    captureBtn?.addEventListener(
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


                const imageData =
                    await compressCameraCanvas(
                        canvas
                    );


                if (
                    imageData.length >
                    CAMERA_MAX_DATA_URL_LENGTH
                ) {

                    throw new Error(
                        'Hasil scan terlalu besar. Silakan scan ulang.'
                    );
                }


                capturedInput.value =
                    imageData;


                cameraImage.src =
                    imageData;


                cameraImage.classList.remove(
                    'sme-hidden'
                );


                cameraVideo.classList.add(
                    'sme-hidden'
                );


                cameraPlaceholder.classList.add(
                    'sme-hidden'
                );


                const size =
                    getDataUrlBinarySize(
                        imageData
                    );


                scanPreview.src =
                    imageData;


                scanInfo.textContent =
                    'Format JPG • Ukuran hasil: ' +
                    formatFileSize(
                        size
                    ) +
                    ' • Sudah dikompres';


                scanResult.classList.remove(
                    'sme-hidden'
                );


                clearFileSelection();


                stopCamera();


                startCameraBtn.classList.add(
                    'sme-hidden'
                );

                captureBtn.classList.add(
                    'sme-hidden'
                );

                stopCameraBtn.classList.add(
                    'sme-hidden'
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
       COMPRESS CAMERA
    ========================================================= */

    async function compressCameraCanvas(
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
            getDataUrlBinarySize(
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
            getDataUrlBinarySize(
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
                    'Resize kamera tidak tersedia.'
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


            quality =
                0.58;


            dataUrl =
                currentCanvas.toDataURL(
                    'image/jpeg',
                    quality
                );
        }


        return dataUrl;
    }


    /* =========================================================
       BASE64 SIZE
    ========================================================= */

    function getDataUrlBinarySize(
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
       CLEAR SCAN RESULT
    ========================================================= */

    function clearScanResult() {

        capturedInput.value =
            '';

        scanResult.classList.add(
            'sme-hidden'
        );

        scanPreview.src =
            '';

        scanInfo.textContent =
            '';

        cameraImage.src =
            '';

        cameraImage.classList.add(
            'sme-hidden'
        );
    }


    /* =========================================================
       CAMERA TRACKS
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

    function stopCamera() {

        stopCameraTracks();

        if (cameraVideo) {
            cameraVideo.srcObject =
                null;
        }

        cameraPlaceholder.classList.remove(
            'sme-hidden'
        );

        if (!capturedInput.value) {

            cameraVideo.classList.remove(
                'sme-hidden'
            );
        }

        startCameraBtn.disabled =
            false;

        captureBtn.disabled =
            true;

        stopCameraBtn.disabled =
            true;
    }


    stopCameraBtn?.addEventListener(
        'click',
        function () {

            stopCamera();

            if (
                capturedInput.value
            ) {

                cameraPlaceholder.classList.add(
                    'sme-hidden'
                );
            }
        }
    );


    /* =========================================================
       RETAKE
    ========================================================= */

    retakeBtn?.addEventListener(
        'click',
        async function () {

            clearScanResult();

            clearCameraError();

            modeCamera.click();

            await startCamera();
        }
    );


    /* =========================================================
       SUBMIT
    ========================================================= */

    form.addEventListener(
        'submit',
        function (
            event
        ) {

            if (submitting) {

                event.preventDefault();

                return;
            }


            const file =
                fileInput.files?.[0] ||
                null;


            const hasFile =
                !!file;


            const hasCamera =
                capturedInput.value.trim() !== '';


            /*
            |--------------------------------------------------------------------------
            | FILE + CAMERA BERSAMAAN
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | FILE VALIDATION
            |--------------------------------------------------------------------------
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

                    alert(
                        'Format file tidak didukung.'
                    );

                    return;
                }


                if (
                    file.size <= 0
                ) {

                    event.preventDefault();

                    alert(
                        'File kosong atau tidak valid.'
                    );

                    return;
                }


                if (
                    file.size >
                    MAX_FILE_SIZE
                ) {

                    event.preventDefault();

                    alert(
                        'Ukuran file maksimal 10 MB.'
                    );

                    return;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | CAMERA VALIDATION
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | LOCK SUBMIT
            |--------------------------------------------------------------------------
            */

            submitting =
                true;


            stopCamera();


            submitBtn.disabled =
                true;


            submitIcon.classList.add(
                'sme-hidden'
            );


            submitLoading.classList.remove(
                'sme-hidden'
            );


            submitText.textContent =
                hasCamera
                    ? 'Menyimpan hasil scan...'
                    : 'Memperbarui...';
        }
    );


    /* =========================================================
       OLD CAMERA DATA
    ========================================================= */

    if (
        capturedInput.value
    ) {

        activateCameraMode();

        cameraImage.src =
            capturedInput.value;

        cameraImage.classList.remove(
            'sme-hidden'
        );

        cameraVideo.classList.add(
            'sme-hidden'
        );

        cameraPlaceholder.classList.add(
            'sme-hidden'
        );

        scanPreview.src =
            capturedInput.value;

        const size =
            getDataUrlBinarySize(
                capturedInput.value
            );

        scanInfo.textContent =
            'Hasil scan sebelumnya • Format JPG • Ukuran sekitar ' +
            formatFileSize(size);

        scanResult.classList.remove(
            'sme-hidden'
        );

        startCameraBtn.classList.add(
            'sme-hidden'
        );

        captureBtn.classList.add(
            'sme-hidden'
        );

        stopCameraBtn.classList.add(
            'sme-hidden'
        );
    }


    /* =========================================================
       CLEANUP
    ========================================================= */

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


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection