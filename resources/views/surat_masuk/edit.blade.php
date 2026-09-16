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

    /*
    |--------------------------------------------------------------------------
    | TANGGAL TERIMA
    |--------------------------------------------------------------------------
    */

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
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'didisposisikan' => 'Didisposisikan',
        'selesai' => 'Selesai',
        'diarsipkan' => 'Diarsipkan',
    ];

    /*
    |--------------------------------------------------------------------------
    | LAMPIRAN LAMA
    |--------------------------------------------------------------------------
    */

    $currentAttachmentPath = trim(
        (string) (
            $suratMasuk->lampiran_file ?? ''
        )
    );

    $hasCurrentAttachment =
        $currentAttachmentPath !== '';

    $currentAttachmentName =
        $hasCurrentAttachment
            ? basename(
                $currentAttachmentPath
            )
            : null;

    $currentAttachmentExtension =
        $hasCurrentAttachment
            ? strtolower(
                pathinfo(
                    $currentAttachmentPath,
                    PATHINFO_EXTENSION
                )
            )
            : null;
@endphp

<style>
.sme-page,
.sme-page * {
    box-sizing: border-box;
}

.sme-page {
    width: 100%;
    max-width: 1120px;
    margin: 0 auto;
    padding: 14px 18px 34px;
    color: #334155;
}

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
    background: #fff;
    color: #475569;
    text-decoration: none;
    font-size: 10px;
    font-weight: 800;
    transition: .15s ease;
}

.sme-back-btn:hover {
    border-color: #6366f1;
    background: #eef2ff;
    color: #4338ca;
}

.sme-error-box {
    margin-bottom: 12px;
    padding: 11px 13px;
    border: 1.5px solid #fecaca;
    border-radius: 10px;
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
    margin: 4px 0 0;
    padding-left: 16px;
    font-size: 9px;
    line-height: 1.55;
    color: #e11d48;
}

.sme-main-card {
    width: 100%;
    overflow: hidden;
    border: 1.5px solid #cbd5e1;
    border-radius: 14px;
    background: #fff;
    box-shadow:
        0 12px 30px rgba(15, 23, 42, .07),
        0 2px 7px rgba(15, 23, 42, .04);
}

.sme-system-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    padding: 16px 18px;
    background:
        linear-gradient(
            135deg,
            #1d4ed8 0%,
            #4338ca 58%,
            #4f46e5 100%
        );
    color: #fff;
}

.sme-header-left {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}

.sme-header-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255,255,255,.35);
    border-radius: 10px;
    background: rgba(255,255,255,.12);
}

.sme-header-label {
    margin: 0;
    font-size: 8px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: #dbeafe;
}

.sme-header-title {
    margin: 2px 0 0;
    font-size: 18px;
    line-height: 1.3;
    font-weight: 850;
    color: #fff;
}

.sme-header-subtitle {
    margin: 3px 0 0;
    font-size: 9px;
    line-height: 1.45;
    color: #dbeafe;
}

.sme-header-badge {
    min-height: 29px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 10px;
    border: 1px solid rgba(255,255,255,.3);
    border-radius: 999px;
    background: rgba(255,255,255,.12);
    color: #fff;
    font-size: 8px;
    font-weight: 800;
    white-space: nowrap;
}

.sme-body {
    padding: 16px;
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
    height: 28px;
    flex: 0 0 4px;
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
    font-weight: 850;
    color: #1e293b;
}

.sme-section-description {
    margin: 3px 0 0;
    font-size: 8.5px;
    line-height: 1.45;
    color: #64748b;
}

.sme-field-table {
    overflow: hidden;
    border: 1.5px solid #94a3b8;
    border-radius: 10px;
}

.sme-field-grid {
    display: grid;
    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );
}

.sme-field {
    min-width: 0;
    padding: 11px 12px;
    border-right: 1.5px solid #cbd5e1;
    border-bottom: 1.5px solid #cbd5e1;
    background: #fff;
}

.sme-field:nth-child(2n) {
    border-right: 0;
}

.sme-field-full {
    grid-column: 1 / -1;
    border-right: 0;
    border-bottom: 0;
}

.sme-field-label {
    display: block;
    margin-bottom: 5px;
    font-size: 9px;
    font-weight: 850;
    text-transform: uppercase;
    color: #475569;
}

.sme-required {
    color: #dc2626;
}

.sme-control {
    width: 100%;
    min-height: 40px;
    padding: 8px 10px;
    border: 1.5px solid #94a3b8;
    border-radius: 8px;
    background: #fff;
    color: #1e293b;
    outline: none;
    font-size: 12px;
    transition: .15s ease;
}

input.sme-control,
select.sme-control {
    height: 40px;
}

textarea.sme-control {
    min-height: 82px;
    resize: vertical;
}

.sme-control:hover {
    border-color: #64748b;
}

.sme-control:focus {
    border-color: #2563eb;
    box-shadow:
        0 0 0 3px
        rgba(37,99,235,.09);
}

.sme-control-error {
    border-color: #ef4444 !important;
    background: #fff7f7 !important;
}

.sme-field-error {
    margin: 4px 0 0;
    font-size: 8px;
    line-height: 1.45;
    font-weight: 700;
    color: #dc2626;
}

.sme-attachment-grid {
    display: grid;
    grid-template-columns:
        minmax(0, 1.08fr)
        minmax(0, .92fr);
    gap: 12px;
    align-items: stretch;
}

.sme-attachment-card {
    min-width: 0;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border: 1.5px solid #94a3b8;
    border-radius: 10px;
    background: #fff;
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
    background: #eff6ff;
    color: #2563eb;
}

.sme-attachment-icon.physical {
    border-color: #c7d2fe;
    background: #eef2ff;
    color: #4f46e5;
}

.sme-attachment-title {
    margin: 0;
    font-size: 10px;
    font-weight: 850;
    color: #1e293b;
}

.sme-attachment-description {
    margin: 2px 0 0;
    font-size: 7.5px;
    color: #64748b;
}

.sme-attachment-badge {
    padding: 4px 7px;
    border: 1px solid #cbd5e1;
    border-radius: 999px;
    background: #fff;
    color: #64748b;
    font-size: 7px;
    font-weight: 800;
    text-transform: uppercase;
}

.sme-attachment-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: 8px;
    padding: 10px;
}

.sme-info {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    padding: 8px;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
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
    line-height: 1.6;
    color: #1d4ed8;
}

.sme-mode-grid {
    display: grid;
    grid-template-columns:
        repeat(
            2,
            minmax(0,1fr)
        );
    gap: 7px;
}

.sme-mode-btn {
    min-height: 44px;
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #475569;
    cursor: pointer;
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

.sme-mode-btn.active
.sme-mode-icon {
    background: #e0e7ff;
    color: #4f46e5;
}

.sme-mode-name {
    display: block;
    font-size: 8px;
    font-weight: 850;
}

.sme-mode-desc {
    display: block;
    margin-top: 1px;
    font-size: 7px;
    color: #64748b;
}

.sme-current-file {
    display: flex;
    align-items: center;
    gap: 7px;
    min-height: 48px;
    padding: 7px;
    border: 1.5px solid #dbe2ea;
    border-radius: 8px;
    background: #f8fafc;
}

.sme-current-file-icon {
    width: 28px;
    height: 28px;
    flex: 0 0 28px;
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
    font-weight: 850;
    color: #64748b;
}

.sme-current-file-name {
    display: block;
    margin-top: 1px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 8px;
    font-weight: 750;
    color: #334155;
}

.sme-current-file-ext {
    display: inline-flex;
    margin-top: 2px;
    padding: 2px 5px;
    border: 1px solid #cbd5e1;
    border-radius: 999px;
    background: #fff;
    font-size: 6px;
    font-weight: 850;
    color: #64748b;
}

.sme-current-file-empty {
    margin: 0;
    font-size: 8px;
    font-weight: 850;
    color: #b45309;
}

.sme-current-file-empty-text {
    margin: 1px 0 0;
    font-size: 7px;
    color: #64748b;
}

.sme-view-btn {
    min-height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 0 8px;
    border: 1px solid #c7d2fe;
    border-radius: 6px;
    background: #eef2ff;
    color: #4338ca;
    text-decoration: none;
    font-size: 7.5px;
    font-weight: 850;
}

.sme-upload-box {
    position: relative;
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

.sme-upload-box.has-file
.sme-upload-icon {
    background: #d1fae5;
    color: #059669;
}

.sme-upload-title {
    font-size: 8.5px;
    font-weight: 850;
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
    font-weight: 750;
}

.sme-upload-input {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
    opacity: 0;
}

.sme-selected-file {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 7px;
    border: 1.5px solid #a7f3d0;
    border-radius: 8px;
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
    font-weight: 850;
    color: #047857;
}

.sme-selected-name {
    margin: 1px 0 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 7.5px;
    font-weight: 750;
}

.sme-selected-size {
    margin: 2px 0 0;
    font-size: 6.5px;
    color: #64748b;
}

.sme-clear-btn {
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #a7f3d0;
    border-radius: 6px;
    background: #fff;
    color: #059669;
    cursor: pointer;
}

.sme-compression {
    padding: 9px;
    border: 1px solid #c7d2fe;
    border-radius: 8px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 7px;
    line-height: 1.6;
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

.sme-pdf-preview {
    overflow: hidden;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #f8fafc;
}

.sme-pdf-preview-header {
    padding: 7px 9px;
    border-bottom: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #475569;
    font-size: 7px;
    font-weight: 850;
}

.sme-pdf-preview-frame {
    display: block;
    width: 100%;
    height: 430px;
    border: 0;
    background: #fff;
}

.sme-image-preview {
    overflow: hidden;
    margin-top: 8px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #f8fafc;
}

.sme-image-preview-header {
    padding: 7px 9px;
    border-bottom: 1px solid #cbd5e1;
    background: #f8fafc;
    color: #475569;
    font-size: 7px;
    font-weight: 850;
}

.sme-image-preview-frame {
    display: block;
    width: 100%;
    max-height: 430px;
    min-height: 180px;
    object-fit: contain;
    border: 0;
    background: #fff;
}

.sme-camera-box {
    overflow: hidden;
    border: 1.5px solid #334155;
    border-radius: 8px;
    background: #0f172a;
}

.sme-camera-preview {
    position: relative;
    min-height: 220px;
    background: #0f172a;
}

.sme-camera-video,
.sme-camera-image {
    width: 100%;
    height: 220px;
    display: block;
    object-fit: contain;
    background: #0f172a;
}

.sme-camera-image {
    position: absolute;
    inset: 0;
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
    font-weight: 850;
    color: #cbd5e1;
}

.sme-camera-placeholder-desc {
    margin: 2px 0 0;
    font-size: 6.5px;
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
    grid-template-columns:
        repeat(
            3,
            minmax(0,1fr)
        );
    gap: 5px;
    padding: 6px;
    background: #111827;
}

.sme-camera-btn {
    min-height: 32px;
    border-radius: 6px;
    font-size: 7px;
    font-weight: 850;
    cursor: pointer;
}

.sme-camera-btn:disabled {
    opacity: .45;
    cursor: not-allowed;
}

.sme-camera-primary {
    border: 1px solid #4338ca;
    background: #4f46e5;
    color: #fff;
}

.sme-camera-success {
    border: 1px solid #059669;
    background: #10b981;
    color: #fff;
}

.sme-camera-secondary {
    border: 1px solid #64748b;
    background: #fff;
    color: #334155;
}

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
    font-weight: 850;
    color: #065f46;
}

.sme-scan-desc {
    margin: 2px 0 0;
    font-size: 6.8px;
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
    background: #fff;
}

.sme-scan-info {
    margin-top: 4px;
    text-align: center;
    font-size: 7px;
    font-weight: 850;
    color: #047857;
}

.sme-retake {
    min-height: 26px;
    padding: 0 8px;
    border: 1px solid #6ee7b7;
    border-radius: 6px;
    background: #fff;
    color: #047857;
    font-size: 7px;
    font-weight: 850;
    cursor: pointer;
}

.sme-physical-box {
    display: flex;
    flex: 1;
    flex-direction: column;
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
    font-weight: 850;
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

.sme-example,
.sme-note {
    margin-top: 6px;
    padding: 7px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #fff;
}

.sme-example p,
.sme-note p {
    margin: 0;
    font-size: 7px;
    line-height: 1.45;
    color: #64748b;
}

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
    min-height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 14px;
    border-radius: 8px;
    font-size: 8.5px;
    font-weight: 850;
    text-decoration: none;
    cursor: pointer;
}

.sme-footer-cancel {
    border: 1.5px solid #cbd5e1;
    background: #fff;
    color: #475569;
}

.sme-footer-submit {
    border: 1.5px solid #2563eb;
    background: #2563eb;
    color: #fff;
}

.sme-footer-submit:disabled {
    opacity: .6;
    cursor: not-allowed;
}

.sme-hidden {
    display: none !important;
}

@media (max-width: 900px) {
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

    .sme-mode-grid,
    .sme-camera-actions {
        grid-template-columns: 1fr;
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
    .sme-page {
        padding-left: 8px;
        padding-right: 8px;
    }

    .sme-system-header {
        padding: 12px;
    }

    .sme-header-title {
        font-size: 15px;
    }

    .sme-body {
        padding: 9px;
    }

    .sme-camera-video,
    .sme-camera-image {
        height: 200px;
    }

    .sme-camera-preview {
        min-height: 200px;
    }
}
</style>

<div class="sme-page">

    <div class="sme-top-action">
        <a
            href="{{ route('surat-masuk.index') }}"
            class="sme-back-btn"
        >
            ← Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="sme-error-box">
            <div class="sme-error-inner">

                <div class="sme-error-icon">
                    !
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

            <div class="sme-system-header">

                <div class="sme-header-left">

                    <div class="sme-header-icon">
                        ✎
                    </div>

                    <div>
                        <p class="sme-header-label">
                            Sistem Arsip Masuk
                        </p>

                        <h1 class="sme-header-title">
                            Edit Surat Masuk
                        </h1>

                        <p class="sme-header-subtitle">
                            Perbarui data dan lampiran surat masuk.
                        </p>
                    </div>

                </div>

                <span class="sme-header-badge">
                    Arsip Digital
                </span>

            </div>

            <div class="sme-body">

                {{-- =============================================================
                     INFORMASI
                ============================================================= --}}

                <section class="sme-section">

                    <div class="sme-section-head">

                        <div class="sme-section-marker"></div>

                        <div>
                            <h2 class="sme-section-title">
                                Informasi Utama Surat
                            </h2>

                            <p class="sme-section-description">
                                Perbarui informasi utama surat.
                            </p>
                        </div>

                    </div>

                    <div class="sme-field-table">

                        <div class="sme-field-grid">

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
                                    value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}"
                                    class="sme-control {{ $errors->has('nomor_surat') ? 'sme-control-error' : '' }}"
                                >

                                @error('nomor_surat')
                                    <p class="sme-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

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
                                    value="{{ old('pengirim', $suratMasuk->pengirim) }}"
                                    class="sme-control {{ $errors->has('pengirim') ? 'sme-control-error' : '' }}"
                                >

                                @error('pengirim')
                                    <p class="sme-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

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

                                    <option value="">
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
                                    <p class="sme-field-error">
                                        {{ $message }}
                                    </p>
                                @enderror

                            </div>

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

                {{-- =============================================================
                     LAMPIRAN
                ============================================================= --}}

                <section class="sme-section">

                    <div class="sme-section-head">

                        <div class="sme-section-marker sme-section-marker-indigo"></div>

                        <div>
                            <h2 class="sme-section-title">
                                Lampiran Dokumen & Arsip Fisik
                            </h2>

                            <p class="sme-section-description">
                                PDF, JPG, JPEG, dan PNG dapat dikompres sebelum disimpan.
                            </p>
                        </div>

                    </div>

                    <div class="sme-attachment-grid">

                        {{-- =====================================================
                             DIGITAL
                        ====================================================== --}}

                        <div class="sme-attachment-card">

                            <div class="sme-attachment-header">

                                <div class="sme-attachment-header-left">

                                    <div class="sme-attachment-icon">
                                        📄
                                    </div>

                                    <div>
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

                                <div class="sme-info">
                                    <p>
                                        <strong>PDF:</strong>
                                        dikompresi langsung di browser dan
                                        hasil PDF dapat dilihat sebelum update.
                                        <strong>JPG/JPEG/PNG:</strong>
                                        di-resize dan dikompresi menjadi JPG.
                                    </p>

                                </div>

                                <div class="sme-mode-grid">

                                    <button
                                        type="button"
                                        id="sme-mode-upload"
                                        class="sme-mode-btn active"
                                        aria-selected="true"
                                    >

                                        <span class="sme-mode-icon">
                                            ↑
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
                                            ◉
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
                                        □
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

                                            @if($currentAttachmentExtension)

                                                <span class="sme-current-file-ext">
                                                    .{{ $currentAttachmentExtension }}
                                                </span>

                                            @endif

                                        @else

                                            <p class="sme-current-file-empty">
                                                Belum ada lampiran
                                            </p>

                                            <p class="sme-current-file-empty-text">
                                                Upload atau scan dokumen baru.
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
                                            ↑
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

                                    <div
                                        id="sme-selected-file"
                                        class="sme-selected-file sme-hidden"
                                    >

                                        <div class="sme-selected-icon">
                                            ✓
                                        </div>

                                        <div class="sme-selected-content">

                                            <p
                                                id="sme-selected-title"
                                                class="sme-selected-title"
                                            >
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
                                        >
                                            ×
                                        </button>

                                    </div>

                                    <div
                                        id="sme-compression"
                                        class="sme-compression sme-hidden"
                                    ></div>

                                    <div
                                        id="sme-pdf-preview"
                                        class="sme-pdf-preview sme-hidden"
                                    ></div>

                                    <div
                                        id="sme-image-preview"
                                        class="sme-image-preview sme-hidden"
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
                                                alt="Hasil scan"
                                                class="sme-camera-image sme-hidden"
                                            >

                                            <div
                                                id="sme-camera-placeholder"
                                                class="sme-camera-placeholder"
                                            >

                                                <div>

                                                    <div class="sme-camera-placeholder-icon">
                                                        ◉
                                                    </div>

                                                    <p class="sme-camera-placeholder-title">
                                                        Kamera belum aktif
                                                    </p>

                                                    <p class="sme-camera-placeholder-desc">
                                                        Aktifkan kamera untuk scan.
                                                    </p>

                                                </div>

                                            </div>

                                            <div
                                                id="sme-camera-error"
                                                class="sme-camera-error sme-hidden"
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

                                                <div>
                                                    <p class="sme-scan-title">
                                                        Hasil scan siap
                                                    </p>

                                                    <p class="sme-scan-desc">
                                                        Hasil scan akan menggantikan
                                                        lampiran lama setelah update.
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
                                                alt="Preview scan"
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
                                        Tidak memilih file baru dan tidak melakukan
                                        scan berarti lampiran lama tetap dipertahankan.
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

                        {{-- =====================================================
                             FISIK
                        ====================================================== --}}

                        <div class="sme-attachment-card">

                            <div class="sme-attachment-header">

                                <div class="sme-attachment-header-left">

                                    <div class="sme-attachment-icon physical">
                                        ▣
                                    </div>

                                    <div>
                                        <h3 class="sme-attachment-title">
                                            Lokasi Arsip Fisik
                                        </h3>

                                        <p class="sme-attachment-description">
                                            Posisi dokumen fisik.
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
                                        ▣
                                    </div>

                                    <h4 class="sme-physical-title">
                                        Detail Lokasi Penyimpanan
                                    </h4>

                                    <p class="sme-physical-desc">
                                        Perbarui posisi rak, lemari, box,
                                        atau map dokumen fisik.
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
                                            value="{{ old(
                                                'lokasi_arsip_fisik',
                                                $suratMasuk->lokasi_arsip_fisik
                                            ) }}"
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
                                            Rak A-3 Box 12 atau Lemari B-2 Map 07.
                                        </p>
                                    </div>

                                    <div class="sme-note">
                                        <p>
                                            Lokasi fisik hanya mencatat lokasi dokumen
                                            asli dan tidak memengaruhi file digital.
                                        </p>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </section>

            </div>

            <div class="sme-footer">

                <a
                    href="{{ route('surat-masuk.index') }}"
                    class="sme-footer-btn sme-footer-cancel"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    id="sme-submit"
                    class="sme-footer-btn sme-footer-submit"
                >

                    <span
                        id="sme-submit-icon"
                    >
                        ✓
                    </span>

                    <span
                        id="sme-submit-loading"
                        class="sme-hidden"
                    >
                        ...
                    </span>

                    <span id="sme-submit-text">
                        Perbarui Surat Masuk
                    </span>

                </button>

            </div>

        </div>

    </form>

</div>

@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script type="module">

import initGhostscript
    from 'https://cdn.jsdelivr.net/npm/@jspawn/ghostscript-wasm@0.0.2/gs.mjs';

document.addEventListener(
    'DOMContentLoaded',
    function () {

        'use strict';

        /* ================================================================
           ELEMENT
        ================================================================ */

        const form =
            document.getElementById(
                'sme-form'
            );

        const modeUpload =
            document.getElementById(
                'sme-mode-upload'
            );

        const modeCamera =
            document.getElementById(
                'sme-mode-camera'
            );

        const uploadPanel =
            document.getElementById(
                'sme-upload-panel'
            );

        const cameraPanel =
            document.getElementById(
                'sme-camera-panel'
            );

        const fileInput =
            document.getElementById(
                'lampiran_file'
            );

        const uploadBox =
            document.getElementById(
                'sme-upload-box'
            );

        const uploadTitle =
            document.getElementById(
                'sme-upload-title'
            );

        const selectedFile =
            document.getElementById(
                'sme-selected-file'
            );

        const selectedTitle =
            document.getElementById(
                'sme-selected-title'
            );

        const selectedName =
            document.getElementById(
                'sme-selected-name'
            );

        const selectedSize =
            document.getElementById(
                'sme-selected-size'
            );

        const clearFileBtn =
            document.getElementById(
                'sme-clear-file'
            );

        const compression =
            document.getElementById(
                'sme-compression'
            );

        const pdfPreview =
            document.getElementById(
                'sme-pdf-preview'
            );

        const imagePreview =
            document.getElementById(
                'sme-image-preview'
            );

        const cameraVideo =
            document.getElementById(
                'sme-camera-video'
            );

        const cameraImage =
            document.getElementById(
                'sme-camera-image'
            );

        const cameraPlaceholder =
            document.getElementById(
                'sme-camera-placeholder'
            );

        const cameraError =
            document.getElementById(
                'sme-camera-error'
            );

        const startCameraBtn =
            document.getElementById(
                'sme-start-camera'
            );

        const captureBtn =
            document.getElementById(
                'sme-capture'
            );

        const stopCameraBtn =
            document.getElementById(
                'sme-stop-camera'
            );

        const scanResult =
            document.getElementById(
                'sme-scan-result'
            );

        const scanPreview =
            document.getElementById(
                'sme-scan-preview'
            );

        const scanInfo =
            document.getElementById(
                'sme-scan-info'
            );

        const retakeBtn =
            document.getElementById(
                'sme-retake'
            );

        const capturedInput =
            document.getElementById(
                'sme-captured-image'
            );

        const submitBtn =
            document.getElementById(
                'sme-submit'
            );

        const submitIcon =
            document.getElementById(
                'sme-submit-icon'
            );

        const submitLoading =
            document.getElementById(
                'sme-submit-loading'
            );

        const submitText =
            document.getElementById(
                'sme-submit-text'
            );

        if (
            !form ||
            !fileInput ||
            !capturedInput
        ) {
            return;
        }

        /* ================================================================
           CONFIG
        ================================================================ */

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
            2.5 *
            1024 *
            1024;

        const CAMERA_MAX_DATA_URL_LENGTH =
            7 *
            1024 *
            1024;

        /* ================================================================
           STATE
        ================================================================ */

        let cameraStream =
            null;

        let submitting =
            false;

        let compressionToken =
            0;

        let ghostscriptPromise =
            null;

        let pdfPreviewUrl =
            null;

        /* ================================================================
           UTILITY
        ================================================================ */

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
                        bytes /
                        1024
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

        function reductionPercent(
            original,
            finalSize
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
                            finalSize /
                            original
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
                file?.name ||
                ''
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

        function revokePdfPreview() {

            if (
                pdfPreviewUrl
            ) {

                URL.revokeObjectURL(
                    pdfPreviewUrl
                );

                pdfPreviewUrl =
                    null;
            }
        }

        /* ================================================================
           ALERT
        ================================================================ */

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

        /* ================================================================
           COMPRESSION STATUS
        ================================================================ */

        function setCompressionStatus(
            html,
            type = ''
        ) {

            compression.classList.remove(
                'sme-hidden',
                'success',
                'warning',
                'error'
            );

            if (
                type
            ) {

                compression.classList.add(
                    type
                );
            }

            compression.innerHTML =
                html;
        }

        function clearCompressionStatus() {

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

        /* ================================================================
           PDF PREVIEW
        ================================================================ */

        function showPdfPreview(
            file
        ) {

            revokePdfPreview();

            pdfPreviewUrl =
                URL.createObjectURL(
                    file
                );

            pdfPreview.innerHTML =
                `
                    <div class="sme-pdf-preview-header">
                        Preview PDF hasil kompresi
                    </div>

                    <iframe
                        class="sme-pdf-preview-frame"
                        src="${pdfPreviewUrl}"
                        title="Preview PDF hasil kompresi"
                    ></iframe>
                `;

            pdfPreview.classList.remove(
                'sme-hidden'
            );
        }

        function clearPdfPreview() {

            revokePdfPreview();

            pdfPreview.innerHTML =
                '';

            pdfPreview.classList.add(
                'sme-hidden'
            );
        }

        /* ================================================================
           IMAGE PREVIEW
        ================================================================ */

        let imagePreviewUrl =
            null;

        function revokeImagePreview() {

            if (
                imagePreviewUrl
            ) {

                URL.revokeObjectURL(
                    imagePreviewUrl
                );

                imagePreviewUrl =
                    null;
            }
        }

        function showImagePreview(
            file
        ) {

            if (
                !imagePreview ||
                !file
            ) {
                return;
            }

            revokeImagePreview();

            imagePreviewUrl =
                URL.createObjectURL(
                    file
                );

            imagePreview.innerHTML =
                `
                    <div class="sme-image-preview-header">
                        Preview gambar hasil kompresi
                    </div>

                    <img
                        class="sme-image-preview-frame"
                        src="${imagePreviewUrl}"
                        alt="Preview gambar hasil kompresi"
                    >
                `;

            imagePreview.classList.remove(
                'sme-hidden'
            );
        }

        function clearImagePreview() {

            revokeImagePreview();

            if (!imagePreview) {
                return;
            }

            imagePreview.innerHTML =
                '';

            imagePreview.classList.add(
                'sme-hidden'
            );
        }

        /* ================================================================
           MODE
        ================================================================ */

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

            compressionToken++;

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

            compressionToken++;

            stopCamera();

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

            clearPdfPreview();

            clearCompressionStatus();

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

        /* ================================================================
           FILE UI
        ================================================================ */

        function showSelectedFile(
            file,
            note = ''
        ) {

            selectedFile.classList.remove(
                'sme-hidden'
            );

            selectedTitle.textContent =
                'File siap digunakan';

            selectedName.textContent =
                file.name;

            selectedSize.textContent =
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

        function clearFileSelection() {

            compressionToken++;

            fileInput.value =
                '';

            selectedFile.classList.add(
                'sme-hidden'
            );

            selectedTitle.textContent =
                'File siap digunakan';

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

            clearPdfPreview();

            clearImagePreview();
        }

        clearFileBtn?.addEventListener(
            'click',
            function () {

                clearFileSelection();

            }
        );

        /* ================================================================
           IMAGE
        ================================================================ */

        function loadImage(
            file
        ) {

            return new Promise(
                function (
                    resolve,
                    reject
                ) {

                    const url =
                        URL.createObjectURL(
                            file
                        );

                    const image =
                        new Image();

                    image.onload =
                        function () {

                            URL.revokeObjectURL(
                                url
                            );

                            resolve(
                                image
                            );
                        };

                    image.onerror =
                        function () {

                            URL.revokeObjectURL(
                                url
                            );

                            reject(
                                new Error(
                                    'Gambar tidak dapat dibaca browser.'
                                )
                            );
                        };

                    image.src =
                        url;
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

            const dimensions =
                [
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

            dimensions.forEach(
                function (
                    dimension
                ) {

                    const value =
                        calculateDimensions(
                            width,
                            height,
                            dimension
                        );

                    if (
                        value.width <
                            IMAGE_MIN_DIMENSION &&
                        value.height <
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
                                        value.width &&
                                    item.height ===
                                        value.height
                                );
                            }
                        );

                    if (
                        !duplicate
                    ) {

                        result.push(
                            value
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

                            const name =
                                (
                                    baseName ||
                                    'lampiran'
                                ) +
                                '_compressed.jpg';

                            resolve(
                                new File(
                                    [blob],
                                    name,
                                    {
                                        type:
                                            'image/jpeg'
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

            const list =
                buildDimensionList(
                    width,
                    height
                );

            let smallest =
                null;

            for (
                const dimension
                of list
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
                        !smallest ||
                        result.size <
                            smallest.size
                    ) {

                        smallest =
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
                smallest &&
                smallest.size <=
                    IMAGE_HARD_LIMIT
            ) {

                return smallest;
            }

            throw new Error(
                'Gambar masih terlalu besar setelah kompresi.'
            );
        }

        /* ================================================================
           GHOSTSCRIPT
        ================================================================ */

        async function getGhostscript() {

            if (
                ghostscriptPromise
            ) {

                return ghostscriptPromise;
            }

            ghostscriptPromise =
                initGhostscript(
                    {
                        locateFile:
                            function (
                                file
                            ) {

                                return (
                                    'https://cdn.jsdelivr.net/npm/' +
                                    '@jspawn/ghostscript-wasm@0.0.2/' +
                                    file
                                );
                            }
                    }
                );

            return ghostscriptPromise;
        }

        /* ================================================================
           PDF VALIDATION
        ================================================================ */

        async function readPdfBytes(
            file
        ) {

            const buffer =
                await file.arrayBuffer();

            return new Uint8Array(
                buffer
            );
        }

        function isPdf(
            bytes
        ) {

            return (
                bytes.length >= 5 &&
                bytes[0] === 0x25 &&
                bytes[1] === 0x50 &&
                bytes[2] === 0x44 &&
                bytes[3] === 0x46 &&
                bytes[4] === 0x2D
            );
        }

        /* ================================================================
           PDF COMPRESSION
        ================================================================ */

        async function compressPdfFile(
            file,
            token
        ) {

            const originalBytes =
                await readPdfBytes(
                    file
                );

            if (
                !isPdf(
                    originalBytes
                )
            ) {

                throw new Error(
                    'File PDF tidak valid.'
                );
            }

            setCompressionStatus(
                '⏳ <strong>Menyiapkan PDF...</strong><br>' +
                'Ghostscript WebAssembly sedang dimuat.',
                ''
            );

            const gs =
                await getGhostscript();

            if (
                token !==
                compressionToken
            ) {

                throw new Error(
                    'Proses dibatalkan.'
                );
            }

            const profiles =
                [
                    {
                        name:
                            'ebook',

                        dpi:
                            150
                    },

                    {
                        name:
                            'ebook-low',

                        dpi:
                            120
                    },

                    {
                        name:
                            'screen',

                        dpi:
                            96
                    }
                ];

            let bestBytes =
                null;

            let bestSize =
                null;

            let bestProfile =
                null;

            for (
                const profile
                of profiles
            ) {

                if (
                    token !==
                    compressionToken
                ) {

                    throw new Error(
                        'Proses dibatalkan.'
                    );
                }

                setCompressionStatus(
                    '⏳ <strong>Mengompres PDF...</strong><br>' +
                    'Profile: <strong>' +
                    profile.name +
                    '</strong><br>' +
                    'Ukuran asli: <strong>' +
                    formatFileSize(
                        file.size
                    ) +
                    '</strong>',
                    ''
                );

                await new Promise(
                    function (
                        resolve
                    ) {

                        setTimeout(
                            resolve,
                            40
                        );
                    }
                );

                const inputName =
                    'input_' +
                    Date.now() +
                    '_' +
                    Math.random()
                        .toString(36)
                        .slice(2) +
                    '.pdf';

                const outputName =
                    'output_' +
                    Date.now() +
                    '_' +
                    Math.random()
                        .toString(36)
                        .slice(2) +
                    '.pdf';

                try {

                    try {
                        gs.FS.unlink(
                            inputName
                        );
                    } catch (e) {}

                    try {
                        gs.FS.unlink(
                            outputName
                        );
                    } catch (e) {}

                    gs.FS.writeFile(
                        inputName,
                        originalBytes
                    );

                    gs.callMain(
                        [
                            '-sDEVICE=pdfwrite',
                            '-dCompatibilityLevel=1.4',
                            '-dPDFSETTINGS=/' +
                                profile.name.replace(
                                    '-low',
                                    ''
                                ),
                            '-dNOPAUSE',
                            '-dBATCH',
                            '-dSAFER',
                            '-dQUIET',
                            '-dDetectDuplicateImages=true',
                            '-dCompressFonts=true',
                            '-dDownsampleColorImages=true',
                            '-dColorImageResolution=' +
                                profile.dpi,
                            '-dColorImageDownsampleType=/Bicubic',
                            '-dAutoFilterColorImages=false',
                            '-dColorImageFilter=/DCTEncode',
                            '-dDownsampleGrayImages=true',
                            '-dGrayImageResolution=' +
                                profile.dpi,
                            '-dGrayImageDownsampleType=/Bicubic',
                            '-dAutoFilterGrayImages=false',
                            '-dGrayImageFilter=/DCTEncode',
                            '-dDownsampleMonoImages=true',
                            '-dMonoImageResolution=' +
                                profile.dpi,
                            '-dMonoImageDownsampleType=/Subsample',
                            '-sOutputFile=' +
                                outputName,
                            inputName
                        ]
                    );

                    const outputBytes =
                        gs.FS.readFile(
                            outputName
                        );

                    if (
                        outputBytes &&
                        outputBytes.length > 0 &&
                        isPdf(
                            outputBytes
                        )
                    ) {

                        if (
                            bestSize === null ||
                            outputBytes.length <
                                bestSize
                        ) {

                            bestBytes =
                                new Uint8Array(
                                    outputBytes
                                );

                            bestSize =
                                outputBytes.length;

                            bestProfile =
                                profile.name;
                        }
                    }

                } finally {

                    try {
                        gs.FS.unlink(
                            inputName
                        );
                    } catch (e) {}

                    try {
                        gs.FS.unlink(
                            outputName
                        );
                    } catch (e) {}
                }
            }

            if (
                !bestBytes
            ) {

                throw new Error(
                    'Ghostscript gagal menghasilkan PDF.'
                );
            }

            /*
             * Jangan menggunakan hasil yang
             * justru lebih besar dari asli.
             */

            if (
                bestSize >=
                file.size
            ) {

                return {
                    file,
                    originalSize:
                        file.size,
                    finalSize:
                        file.size,
                    reduction:
                        0,
                    profile:
                        'Asli dipertahankan',
                    compressed:
                        false
                };
            }

            if (
                bestSize >
                MAX_FILE_SIZE
            ) {

                throw new Error(
                    'PDF hasil kompresi masih melebihi 10 MB.'
                );
            }

            const baseName =
                String(
                    file.name
                )
                    .replace(
                        /\.pdf$/i,
                        ''
                    )
                    .replace(
                        /[^a-zA-Z0-9_-]/g,
                        '_'
                    );

            const finalFile =
                new File(
                    [
                        bestBytes
                    ],
                    (
                        baseName ||
                        'lampiran'
                    ) +
                    '_compressed.pdf',
                    {
                        type:
                            'application/pdf'
                    }
                );

            return {
                file:
                    finalFile,

                originalSize:
                    file.size,

                finalSize:
                    finalFile.size,

                reduction:
                    reductionPercent(
                        file.size,
                        finalFile.size
                    ),

                profile:
                    bestProfile,

                compressed:
                    true
            };
        }

        /* ================================================================
           FILE CHANGE
        ================================================================ */

        fileInput.addEventListener(
            'change',
            async function () {

                const file =
                    fileInput.files?.[0];

                if (!file) {
                    return;
                }

                const token =
                    ++compressionToken;

                clearCameraError();

                clearPdfPreview();

                clearImagePreview();

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

                /* ==========================================================
                   PDF
                ========================================================== */

                if (
                    extension ===
                    'pdf'
                ) {

                    capturedInput.value =
                        '';

                    clearScanResult();

                    showSelectedFile(
                        file,
                        'PDF • sedang diproses'
                    );

                    try {

                        const result =
                            await compressPdfFile(
                                file,
                                token
                            );

                        if (
                            token !==
                            compressionToken
                        ) {

                            return;
                        }

                        const finalFile =
                            result.file;

                        const transfer =
                            new DataTransfer();

                        transfer.items.add(
                            finalFile
                        );

                        fileInput.files =
                            transfer.files;

                        showSelectedFile(
                            finalFile,
                            result.compressed
                                ? 'PDF hasil kompresi'
                                : 'PDF asli dipertahankan'
                        );

                        setCompressionStatus(
                            (
                                result.compressed
                                    ? '✓ <strong>Kompresi PDF berhasil.</strong><br>'
                                    : '✓ <strong>PDF siap digunakan.</strong><br>'
                            ) +
                            'Format asli: <strong>PDF</strong><br>' +
                            'Ukuran asli: <strong>' +
                            formatFileSize(
                                result.originalSize
                            ) +
                            '</strong><br>' +
                            'Ukuran hasil: <strong>' +
                            formatFileSize(
                                result.finalSize
                            ) +
                            '</strong><br>' +
                            'Penghematan: <strong>' +
                            result.reduction +
                            '%</strong><br>' +
                            'Format akhir: <strong>PDF</strong><br>' +
                            'Profile: <strong>' +
                            result.profile +
                            '</strong>',
                            result.compressed
                                ? 'success'
                                : 'warning'
                        );

                        showPdfPreview(
                            finalFile
                        );

                    } catch (
                        error
                    ) {

                        console.error(
                            'PDF compression error',
                            error
                        );

                        if (
                            token !==
                            compressionToken
                        ) {

                            return;
                        }

                        clearFileSelection();

                        clearPdfPreview();

                        setCompressionStatus(
                            '✕ <strong>Kompresi PDF gagal.</strong><br>' +
                            (
                                error?.message ||
                                'PDF gagal diproses.'
                            ),
                            'error'
                        );

                        showAlert(
                            'error',
                            'Gagal memproses PDF',
                            error?.message ||
                            'PDF gagal dikompres.'
                        );
                    }

                    return;
                }

                /* ==========================================================
                   IMAGE
                ========================================================== */

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
                    '⏳ <strong>Sedang mengompres gambar...</strong><br>' +
                    'Gambar akan di-resize dan dikonversi menjadi JPG.',
                    ''
                );

                try {

                    const compressed =
                        await compressImageFile(
                            file
                        );

                    if (
                        token !==
                        compressionToken
                    ) {

                        return;
                    }

                    if (
                        compressed.size >
                        MAX_FILE_SIZE
                    ) {

                        throw new Error(
                            'Ukuran hasil masih lebih dari 10 MB.'
                        );
                    }

                    const transfer =
                        new DataTransfer();

                    transfer.items.add(
                        compressed
                    );

                    fileInput.files =
                        transfer.files;

                    capturedInput.value =
                        '';

                    clearScanResult();

                    const reduction =
                        reductionPercent(
                            originalSize,
                            compressed.size
                        );

                    showSelectedFile(
                        compressed,
                        'JPG hasil kompresi'
                    );

                    showImagePreview(
                        compressed
                    );

                    setCompressionStatus(
                        '✓ <strong>Kompresi gambar berhasil.</strong><br>' +
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
                            compressed.size
                        ) +
                        '</strong><br>' +
                        'Penghematan: <strong>' +
                        reduction +
                        '%</strong><br>' +
                        'Format akhir: <strong>JPG</strong>.',
                        'success'
                    );

                } catch (
                    error
                ) {

                    console.error(
                        'Image compression error',
                        error
                    );

                    if (
                        token !==
                        compressionToken
                    ) {

                        return;
                    }

                    clearFileSelection();

                    setCompressionStatus(
                        '✕ <strong>Kompresi gambar gagal.</strong><br>' +
                        (
                            error?.message ||
                            'Gambar gagal diproses.'
                        ),
                        'error'
                    );

                    showAlert(
                        'error',
                        'Gagal memproses gambar',
                        error?.message ||
                        'Gambar gagal dikompres.'
                    );
                }

            }
        );

        /* ================================================================
           CAMERA
        ================================================================ */

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
                    'Browser tidak mendukung kamera.'
                );

                return;
            }

            stopCameraTracks();

            try {

                cameraStream =
                    await navigator
                        .mediaDevices
                        .getUserMedia(
                            {
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
                            }
                        );

                cameraVideo.srcObject =
                    cameraStream;

                await cameraVideo.play();

                cameraPlaceholder.classList.add(
                    'sme-hidden'
                );

                cameraVideo.classList.remove(
                    'sme-hidden'
                );

                startCameraBtn.disabled =
                    true;

                captureBtn.disabled =
                    false;

                stopCameraBtn.disabled =
                    false;

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
                        'Izin kamera ditolak. Izinkan kamera pada browser.';

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
                }

                showCameraError(
                    message
                );
            }
        }

        function showCameraError(
            message
        ) {

            cameraError.textContent =
                String(
                    message ??
                    ''
                );

            cameraError.classList.remove(
                'sme-hidden'
            );
        }

        function clearCameraError() {

            cameraError.textContent =
                '';

            cameraError.classList.add(
                'sme-hidden'
            );
        }

        /* ================================================================
           CAPTURE
        ================================================================ */

        captureBtn?.addEventListener(
            'click',
            async function () {

                clearCameraError();

                if (
                    !cameraVideo.videoWidth ||
                    !cameraVideo.videoHeight
                ) {

                    showCameraError(
                        'Kamera belum siap.'
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
                                alpha:
                                    false
                            }
                        );

                    if (!context) {

                        throw new Error(
                            'Canvas tidak tersedia.'
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

                    let dataUrl =
                        canvas.toDataURL(
                            'image/jpeg',
                            .82
                        );

                    let quality =
                        .82;

                    while (
                        getDataUrlBinarySize(
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

                    cameraImage.src =
                        dataUrl;

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
                            dataUrl
                        );

                    scanPreview.src =
                        dataUrl;

                    scanInfo.textContent =
                        'Format JPG • ' +
                        formatFileSize(
                            size
                        ) +
                        ' • Sudah dikompres';

                    scanResult.classList.remove(
                        'sme-hidden'
                    );

                    clearFileSelection();

                    setCompressionStatus(
                        '✓ <strong>Scan berhasil.</strong><br>' +
                        'Format akhir: <strong>JPG</strong><br>' +
                        'Ukuran hasil: <strong>' +
                        formatFileSize(
                            size
                        ) +
                        '</strong>.',
                        'success'
                    );

                    stopCamera();

                    startCameraBtn.disabled =
                        true;

                    captureBtn.disabled =
                        true;

                    stopCameraBtn.disabled =
                        true;

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

        /* ================================================================
           DATA URL SIZE
        ================================================================ */

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
                base64.endsWith(
                    '=='
                )
                    ? 2
                    : base64.endsWith(
                        '='
                    )
                        ? 1
                        : 0;

            return (
                Math.floor(
                    (
                        base64.length *
                        3
                    ) / 4
                ) -
                padding
            );
        }

        /* ================================================================
           CLEAR SCAN
        ================================================================ */

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

        /* ================================================================
           CAMERA STOP
        ================================================================ */

        function stopCameraTracks() {

            if (
                !cameraStream
            ) {
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

        function stopCamera() {

            stopCameraTracks();

            cameraVideo.srcObject =
                null;

            startCameraBtn.disabled =
                false;

            captureBtn.disabled =
                true;

            stopCameraBtn.disabled =
                true;

            if (
                !capturedInput.value
            ) {

                cameraPlaceholder.classList.remove(
                    'sme-hidden'
                );

                cameraVideo.classList.remove(
                    'sme-hidden'
                );
            }
        }

        stopCameraBtn?.addEventListener(
            'click',
            function () {

                stopCamera();
            }
        );

        /* ================================================================
           RETAKE
        ================================================================ */

        retakeBtn?.addEventListener(
            'click',
            async function () {

                clearScanResult();

                clearCompressionStatus();

                clearCameraError();

                setModeButton(
                    modeUpload,
                    false
                );

                setModeButton(
                    modeCamera,
                    true
                );

                uploadPanel.classList.add(
                    'sme-hidden'
                );

                cameraPanel.classList.remove(
                    'sme-hidden'
                );

                startCameraBtn.classList.remove(
                    'sme-hidden'
                );

                captureBtn.classList.remove(
                    'sme-hidden'
                );

                stopCameraBtn.classList.remove(
                    'sme-hidden'
                );

                await startCamera();
            }
        );

        /* ================================================================
           SUBMIT
        ================================================================ */

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
                    capturedInput.value.trim() !==
                    '';

                if (
                    hasFile &&
                    hasCamera
                ) {

                    event.preventDefault();

                    showAlert(
                        'warning',
                        'Metode lampiran',
                        'Gunakan Upload File atau Scan Kamera saja.'
                    );

                    return;
                }

                if (
                    hasFile
                ) {

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

                if (
                    hasCamera &&
                    capturedInput.value.length >
                        CAMERA_MAX_DATA_URL_LENGTH
                ) {

                    event.preventDefault();

                    showAlert(
                        'warning',
                        'Hasil scan terlalu besar',
                        'Silakan scan ulang.'
                    );

                    return;
                }

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
                        : hasFile
                            ? 'Menyimpan lampiran...'
                            : 'Memperbarui...';
            }
        );

        /* ================================================================
           OLD CAMERA DATA
        ================================================================ */

        if (
            capturedInput.value
        ) {

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
                'Hasil scan sebelumnya • JPG • ' +
                formatFileSize(
                    size
                );

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

        /* ================================================================
           CLEANUP
        ================================================================ */

        window.addEventListener(
            'beforeunload',
            function () {

                stopCameraTracks();

                revokePdfPreview();

                revokeImagePreview();
            }
        );

    }
);

</script>

@endpush

@endsection
