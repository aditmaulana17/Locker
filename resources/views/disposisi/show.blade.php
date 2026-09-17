@extends('layouts.app')

@section('title', 'Detail Disposisi - Arsip Surat')

@section('content')

@php
    /* =========================================================
       STATUS
    ========================================================== */

    $statusClasses = [
        'menunggu' => 'dd-status-menunggu',
        'diproses' => 'dd-status-diproses',
        'proses'   => 'dd-status-diproses',
        'selesai'  => 'dd-status-selesai',
    ];

    $statusLabels = [
        'menunggu' => 'Menunggu',
        'diproses' => 'Diproses',
        'proses'   => 'Diproses',
        'selesai'  => 'Selesai',
    ];

    $st = strtolower(
        trim(
            (string) ($disposisi->status ?? 'menunggu')
        )
    );

    $statusLabel =
        $statusLabels[$st]
        ?? ucfirst($st);

    $statusClass =
        $statusClasses[$st]
        ?? 'dd-status-menunggu';


    /* =========================================================
       HAK AKSES
    ========================================================== */

    $isAdmin =
        auth()->user()->isAdmin();

    $isSender =
        auth()->id() ===
        $disposisi->dari_user_id;

    $isReceiver =
        auth()->id() ===
        $disposisi->kepada_user_id;


    /* =========================================================
       DATA DISPOSISI
    ========================================================== */

    $pengirimDisposisi =
        $disposisi->dari?->name
        ?? $disposisi->pengirim
        ?? '-';

    $penerimaDisposisi =
        $disposisi->kepada?->name
        ?? $disposisi->penerima
        ?? $disposisi->tujuan
        ?? '-';

    $jabatanPengirim =
        $disposisi->dari?->jabatan
        ?? null;

    $jabatanPenerima =
        $disposisi->kepada?->jabatan
        ?? null;

    $instruksi =
        $disposisi->instruksi
        ?? $disposisi->catatan
        ?? $disposisi->isi_disposisi
        ?? '';

    if (
        trim(
            (string) $instruksi
        ) === ''
    ) {
        $instruksi =
            'Tidak ada instruksi atau catatan khusus.';
    }


    /* =========================================================
       TANGGAL DISPOSISI
    ========================================================== */

    $batasWaktu = '-';
    $tanggalDibuat = '-';

    try {

        if (
            !empty(
                $disposisi->batas_waktu
            )
        ) {

            $batasWaktu =
                \Illuminate\Support\Carbon::parse(
                    $disposisi->batas_waktu
                )->format(
                    'd/m/Y'
                );
        }

    } catch (
        \Throwable $e
    ) {

        $batasWaktu =
            '-';
    }

    try {

        if (
            !empty(
                $disposisi->created_at
            )
        ) {

            $tanggalDibuat =
                \Illuminate\Support\Carbon::parse(
                    $disposisi->created_at
                )->format(
                    'd/m/Y H:i'
                );
        }

    } catch (
        \Throwable $e
    ) {

        $tanggalDibuat =
            '-';
    }


    /* =========================================================
       SURAT MASUK TERKAIT
    ========================================================== */

    $suratMasuk =
        $disposisi->suratMasuk
        ?? null;


    /* =========================================================
       NOMOR SURAT
    ========================================================== */

    $nomorSurat =
        '-';

    if (
        $suratMasuk &&
        !empty(
            $suratMasuk->nomor_surat
        )
    ) {

        $nomorSurat =
            trim(
                (string) $suratMasuk->nomor_surat
            );

    }


    /* =========================================================
       TANGGAL SURAT
    ========================================================== */

    $tanggalSurat =
        '-';

    if (
        $suratMasuk &&
        !empty(
            $suratMasuk->tanggal_surat
        )
    ) {

        try {

            $tanggalSurat =
                \Illuminate\Support\Carbon::parse(
                    $suratMasuk->tanggal_surat
                )->format(
                    'd/m/Y'
                );

        } catch (
            \Throwable $e
        ) {

            $tanggalSurat =
                '-';
        }
    }
@endphp


<style>
    /* =========================================================
       PAGE
    ========================================================== */

    .dd-detail-page {
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        padding: 14px 18px 36px;
        color: #334155;
    }

    .dd-detail-page *,
    .dd-detail-page *::before,
    .dd-detail-page *::after {
        box-sizing: border-box;
    }


    /* =========================================================
       FLASH
    ========================================================== */

    .dd-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 14px;
        padding: 11px 14px;
        border-radius: 11px;
    }

    .dd-alert-success {
        background: #f0fdf4;
        color: #166534;
    }

    .dd-alert-inner {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .dd-alert-icon {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 30px;
        border-radius: 8px;
        background: #dcfce7;
        color: #16a34a;
    }

    .dd-alert-text {
        margin: 0;
        font-size: 11px;
        line-height: 1.5;
        font-weight: 700;
    }

    .dd-alert-close {
        width: 27px;
        height: 27px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 27px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: #4ade80;
        cursor: pointer;
    }

    .dd-alert-close:hover {
        background: rgba(15, 23, 42, .05);
    }


    /* =========================================================
       HEADER
    ========================================================== */

    .dd-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        min-height: 84px;
        margin-bottom: 16px;
        padding: 17px 20px;
        overflow: hidden;
        border-radius: 16px;
        background:
            linear-gradient(
                135deg,
                #0f172a 0%,
                #1e293b 55%,
                #312e81 100%
            );
        color: #fff;
        box-shadow:
            0 10px 30px rgba(15, 23, 42, .10);
    }

    .dd-header::after {
        content: "";
        position: absolute;
        width: 180px;
        height: 180px;
        right: -70px;
        top: -100px;
        border-radius: 50%;
        background: rgba(99, 102, 241, .15);
        pointer-events: none;
    }

    .dd-header-left {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .dd-back {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 40px;
        border-radius: 10px;
        background: rgba(255,255,255,.09);
        color: #fff;
        text-decoration: none;
        transition: .16s ease;
    }

    .dd-back:hover {
        background: rgba(255,255,255,.17);
        transform: translateX(-2px);
    }

    .dd-header-content {
        min-width: 0;
    }

    .dd-breadcrumb {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 3px;
        font-size: 9px;
        color: #94a3b8;
    }

    .dd-breadcrumb a {
        color: #cbd5e1;
        text-decoration: none;
    }

    .dd-breadcrumb a:hover {
        color: #fff;
    }

    .dd-header-title {
        margin: 0;
        font-size: 20px;
        line-height: 1.3;
        font-weight: 800;
        letter-spacing: -.02em;
    }

    .dd-header-subtitle {
        margin: 4px 0 0;
        font-size: 10px;
        line-height: 1.5;
        color: #cbd5e1;
    }

    .dd-header-actions {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .dd-header-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 34px;
        padding: 0 11px;
        border-radius: 8px;
        font-size: 9px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
        transition: .15s ease;
    }

    .dd-header-btn-back {
        background: rgba(255,255,255,.09);
        color: #e2e8f0;
    }

    .dd-header-btn-back:hover {
        background: rgba(255,255,255,.17);
    }

    .dd-header-btn-edit {
        background: rgba(245,158,11,.15);
        color: #fde68a;
    }

    .dd-header-btn-edit:hover {
        background: rgba(245,158,11,.25);
    }


    /* =========================================================
       MAIN GRID
    ========================================================== */

    .dd-top-grid {
        display: grid;
        grid-template-columns:
            minmax(0, 2fr)
            minmax(330px, .9fr);
        gap: 16px;
        align-items: stretch;
    }

    .dd-left-column {
        min-width: 0;
        min-height: 100%;
        display: flex;
        flex-direction: column;
    }

    .dd-right-column {
        min-width: 0;
        min-height: 100%;
        display: flex;
    }


    /* =========================================================
       CARD
    ========================================================== */

    .dd-card {
        width: 100%;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        background: #fff;
        box-shadow:
            0 7px 25px rgba(15,23,42,.055);
    }

    .dd-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        min-height: 61px;
        padding: 11px 15px;
        border-bottom: 1px solid #e5e7eb;
        background:
            linear-gradient(
                135deg,
                #f8fafc 0%,
                #ffffff 55%,
                #f5f3ff 100%
            );
    }

    .dd-card-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        min-width: 0;
    }

    .dd-card-heading-icon {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 34px;
        border-radius: 9px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .dd-card-title {
        margin: 0;
        font-size: 12px;
        line-height: 1.35;
        font-weight: 800;
        color: #1e293b;
    }

    .dd-card-subtitle {
        margin: 2px 0 0;
        font-size: 9px;
        line-height: 1.4;
        color: #94a3b8;
    }


    /* =========================================================
       STATUS BADGE
    ========================================================== */

    .dd-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 27px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .dd-status-menunggu {
        background: #fffbeb;
        color: #b45309;
    }

    .dd-status-diproses {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .dd-status-selesai {
        background: #f0fdf4;
        color: #15803d;
    }


    /* =========================================================
       DETAIL CARD
    ========================================================== */

    .dd-detail-card {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
    }

    .dd-detail-body {
        flex: 1 1 auto;
        padding: 16px 17px 18px;
        display: flex;
        flex-direction: column;
    }


    /* =========================================================
       META TABLE
    ========================================================== */

    .dd-meta-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .dd-meta-table {
        width: 100%;
        min-width: 700px;
        border-collapse: collapse;
        table-layout: fixed;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
    }

    .dd-meta-table td {
        width: 50%;
        min-height: 104px;
        padding: 16px 17px;
        vertical-align: top;
        border-right: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
    }

    .dd-meta-table td:last-child {
        border-right: 0;
    }

    .dd-meta-table tr:last-child td {
        border-bottom: 0;
    }

    .dd-meta-table tr:nth-child(odd) td:first-child,
    .dd-meta-table tr:nth-child(even) td:last-child {
        background: #f8fafc;
    }

    .dd-meta-label {
        display: block;
        margin-bottom: 8px;
        font-size: 8px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #94a3b8;
    }

    .dd-meta-value {
        margin: 0;
        font-size: 12px;
        line-height: 1.55;
        font-weight: 750;
        color: #1e293b;
        overflow-wrap: anywhere;
    }

    .dd-meta-secondary {
        margin: 3px 0 0;
        font-size: 9px;
        line-height: 1.4;
        color: #64748b;
    }

    .dd-date-value {
        display: flex;
        align-items: flex-start;
        gap: 7px;
    }

    .dd-date-icon {
        width: 15px;
        height: 15px;
        flex: 0 0 15px;
        margin-top: 2px;
        color: #6366f1;
    }


    /* =========================================================
       INSTRUKSI
    ========================================================== */

    .dd-instruction-section {
        margin-top: 16px;
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
    }

    .dd-section-heading {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-bottom: 8px;
    }

    .dd-section-icon {
        width: 31px;
        height: 31px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 31px;
        border-radius: 9px;
        background: #eef2ff;
        color: #4f46e5;
    }

    .dd-section-title {
        margin: 0;
        font-size: 11px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #334155;
    }

    .dd-section-subtitle {
        margin: 2px 0 0;
        font-size: 9px;
        color: #94a3b8;
    }

    .dd-instruction-box {
        flex: 1 1 auto;
        min-height: 140px;
        padding: 15px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
        background: #f8fafc;
    }

    .dd-instruction-text {
        margin: 0;
        font-size: 12px;
        line-height: 1.75;
        color: #475569;
        white-space: pre-line;
        overflow-wrap: anywhere;
    }


    /* =========================================================
       UPDATE STATUS
    ========================================================== */

    .dd-status-update {
        margin-top: 16px;
        padding-top: 15px;
        border-top: 1px solid #e5e7eb;
    }

    .dd-status-form {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dd-status-select {
        flex: 1;
        min-width: 0;
        height: 36px;
        padding: 0 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        color: #334155;
        font-size: 10px;
        font-weight: 700;
        outline: none;
        transition: .15s ease;
    }

    .dd-status-select:focus {
        border-color: #6366f1;
        box-shadow:
            0 0 0 3px rgba(99,102,241,.08);
    }

    .dd-status-submit {
        min-height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 0 12px;
        border: 1px solid #4f46e5;
        border-radius: 8px;
        background: #4f46e5;
        color: #fff;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
        white-space: nowrap;
        transition: .15s ease;
    }

    .dd-status-submit:hover {
        background: #4338ca;
    }


    /* =========================================================
       SURAT TERKAIT
    ========================================================== */

    .dd-related-card {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .dd-related-body {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        padding: 16px;
    }

    .dd-related-items {
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .dd-related-item {
        padding: 13px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f8fafc;
    }

    .dd-related-label {
        display: block;
        margin-bottom: 6px;
        font-size: 8px;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .07em;
        color: #94a3b8;
    }

    .dd-related-value {
        margin: 0;
        font-size: 11px;
        line-height: 1.55;
        font-weight: 750;
        color: #334155;
        overflow-wrap: anywhere;
    }

    .dd-related-button {
        width: 100%;
        min-height: 36px;
        margin-top: auto;
        padding: 0 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border-radius: 8px;
        background: #4f46e5;
        color: #fff;
        text-decoration: none;
        font-size: 9px;
        font-weight: 800;
        transition: .15s ease;
    }

    .dd-related-button:hover {
        background: #4338ca;
    }

    .dd-related-empty {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 280px;
        padding: 28px 12px;
        text-align: center;
    }

    .dd-related-empty-icon {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 11px;
        border-radius: 14px;
        background: #f8fafc;
        color: #94a3b8;
    }

    .dd-related-empty-title {
        margin: 0;
        font-size: 10px;
        font-weight: 800;
        color: #475569;
    }

    .dd-related-empty-text {
        margin: 4px 0 0;
        font-size: 9px;
        line-height: 1.5;
        color: #94a3b8;
    }


    /* =========================================================
       FOOTER
    ========================================================== */

    .dd-footer {
        margin-top: 12px;
        text-align: center;
    }

    .dd-footer span {
        font-size: 8px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #cbd5e1;
    }


    /* =========================================================
       TABLET
    ========================================================== */

    @media (max-width: 980px) {

        .dd-top-grid {
            grid-template-columns: 1fr;
            align-items: start;
        }

        .dd-right-column {
            min-height: auto;
        }

        .dd-related-card {
            height: auto;
        }

        .dd-related-button {
            margin-top: 16px;
        }
    }


    /* =========================================================
       MOBILE
    ========================================================== */

    @media (max-width: 700px) {

        .dd-detail-page {
            padding: 9px 10px 25px;
        }

        .dd-header {
            align-items: flex-start;
            flex-direction: column;
            padding: 15px;
        }

        .dd-header-left {
            width: 100%;
        }

        .dd-header-actions {
            width: 100%;
            justify-content: flex-start;
            padding-top: 9px;
            border-top: 1px solid rgba(255,255,255,.10);
        }

        .dd-header-btn {
            flex: 1 1 auto;
        }

        .dd-card-header {
            padding: 11px 13px;
        }

        .dd-meta-table {
            min-width: 620px;
        }

        .dd-meta-table td {
            min-height: 98px;
            padding: 15px;
        }

        .dd-detail-body,
        .dd-related-body {
            padding: 14px;
        }

        .dd-status-form {
            align-items: stretch;
            flex-direction: column;
        }

        .dd-status-select,
        .dd-status-submit {
            width: 100%;
        }
    }


    /* =========================================================
       SMALL MOBILE
    ========================================================== */

    @media (max-width: 470px) {

        .dd-header-title {
            font-size: 18px;
        }

        .dd-header-subtitle {
            font-size: 9px;
        }

        .dd-header-actions {
            flex-direction: column;
        }

        .dd-header-btn {
            width: 100%;
        }

        .dd-card-subtitle,
        .dd-section-subtitle {
            display: none;
        }

        .dd-meta-table {
            min-width: 0;
        }

        .dd-meta-table,
        .dd-meta-table tbody,
        .dd-meta-table tr,
        .dd-meta-table td {
            display: block;
            width: 100%;
        }

        .dd-meta-table td {
            min-height: 82px;
            border-right: 0;
        }

        .dd-meta-table tr:last-child td:last-child {
            border-bottom: 0;
        }

        .dd-meta-value {
            font-size: 11px;
        }

        .dd-instruction-box {
            min-height: 130px;
        }
    }
</style>


<div class="dd-detail-page">

    {{-- =====================================================
         FLASH SUCCESS
    ====================================================== --}}

    @if(session('success'))

        <div
            class="dd-alert dd-alert-success"
            role="alert"
        >

            <div class="dd-alert-inner">

                <div class="dd-alert-icon">

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

                <p class="dd-alert-text">
                    {{ session('success') }}
                </p>

            </div>


            <button
                type="button"
                class="dd-alert-close"
                onclick="this.closest('[role=alert]')?.remove()"
                aria-label="Tutup"
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
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>

            </button>

        </div>

    @endif


    {{-- =====================================================
         HEADER
    ====================================================== --}}

    <div class="dd-header">

        <div class="dd-header-left">

            <a
                href="{{ route('disposisi.index') }}"
                class="dd-back"
                title="Kembali"
                aria-label="Kembali ke daftar disposisi"
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

            </a>


            <div class="dd-header-content">

                <div class="dd-breadcrumb">

                    <a href="{{ route('disposisi.index') }}">
                        Disposisi
                    </a>

                    <span>/</span>

                    <span>
                        Detail
                    </span>

                </div>


                <h1 class="dd-header-title">
                    Detail Disposisi
                </h1>


                <p class="dd-header-subtitle">
                    Informasi disposisi dan surat masuk yang terkait.
                </p>

            </div>

        </div>


        <div class="dd-header-actions">

            <a
                href="{{ route('disposisi.index') }}"
                class="dd-header-btn dd-header-btn-back"
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
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"
                    />
                </svg>

                Kembali

            </a>


            @if($isAdmin || $isSender)

                <a
                    href="{{ route('disposisi.edit', $disposisi) }}"
                    class="dd-header-btn dd-header-btn-edit"
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
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2.121 2.121 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                        />
                    </svg>

                    Edit Disposisi

                </a>

            @endif

        </div>

    </div>


    {{-- =====================================================
         TOP GRID
    ====================================================== --}}

    <div class="dd-top-grid">


        {{-- =================================================
             KIRI
        ================================================== --}}

        <div class="dd-left-column">

            <div class="dd-card dd-detail-card">


                {{-- =============================================
                     CARD HEADER
                ============================================== --}}

                <div class="dd-card-header">

                    <div class="dd-card-heading">

                        <div class="dd-card-heading-icon">

                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A1.5 1.5 0 0118 8.5V19a2 2 0 01-2 2z"
                                />
                            </svg>

                        </div>


                        <div>

                            <h2 class="dd-card-title">
                                Informasi Disposisi
                            </h2>

                            <p class="dd-card-subtitle">
                                Detail pengiriman dan penerimaan disposisi.
                            </p>

                        </div>

                    </div>


                    <span class="dd-status {{ $statusClass }}">
                        {{ $statusLabel }}
                    </span>

                </div>


                {{-- =============================================
                     BODY
                ============================================== --}}

                <div class="dd-detail-body">


                    {{-- =================================================
                         META
                    ================================================== --}}

                    <div class="dd-meta-table-wrap">

                        <table class="dd-meta-table">

                            <tbody>

                                <tr>

                                    <td>

                                        <span class="dd-meta-label">
                                            Pengirim Disposisi
                                        </span>

                                        <p class="dd-meta-value">
                                            {{ $pengirimDisposisi }}
                                        </p>

                                        @if($jabatanPengirim)

                                            <p class="dd-meta-secondary">
                                                {{ $jabatanPengirim }}
                                            </p>

                                        @endif

                                    </td>


                                    <td>

                                        <span class="dd-meta-label">
                                            Penerima Disposisi
                                        </span>

                                        <p class="dd-meta-value">
                                            {{ $penerimaDisposisi }}
                                        </p>

                                        @if($jabatanPenerima)

                                            <p class="dd-meta-secondary">
                                                {{ $jabatanPenerima }}
                                            </p>

                                        @endif

                                    </td>

                                </tr>


                                <tr>

                                    <td>

                                        <span class="dd-meta-label">
                                            Batas Waktu
                                        </span>

                                        <p class="dd-meta-value dd-date-value">

                                            <svg
                                                class="dd-date-icon"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 2v4M16 2v4M3 10h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                                                />
                                            </svg>

                                            <span>
                                                {{ $batasWaktu }}
                                            </span>

                                        </p>

                                    </td>


                                    <td>

                                        <span class="dd-meta-label">
                                            Tanggal Dibuat
                                        </span>

                                        <p class="dd-meta-value dd-date-value">

                                            <svg
                                                class="dd-date-icon"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M8 2v4M16 2v4M3 10h18M5 5h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                                                />
                                            </svg>

                                            <span>
                                                {{ $tanggalDibuat }}
                                            </span>

                                        </p>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>


                    {{-- =================================================
                         INSTRUKSI
                    ================================================== --}}

                    <section class="dd-instruction-section">

                        <div class="dd-section-heading">

                            <div class="dd-section-icon">

                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.8"
                                        d="M4 6h16M4 12h16M4 18h10"
                                    />
                                </svg>

                            </div>


                            <div>

                                <h3 class="dd-section-title">
                                    Instruksi & Catatan
                                </h3>

                                <p class="dd-section-subtitle">
                                    Arahan atau pekerjaan yang diberikan.
                                </p>

                            </div>

                        </div>


                        <div class="dd-instruction-box">

                            <p class="dd-instruction-text">
                                {{ $instruksi }}
                            </p>

                        </div>

                    </section>


                    {{-- =================================================
                         UPDATE STATUS
                    ================================================== --}}

                    @if($isAdmin || $isReceiver)

                        <div class="dd-status-update">

                            <div class="dd-section-heading">

                                <div
                                    class="dd-section-icon"
                                    style="
                                        background:#f5f3ff;
                                        color:#7c3aed;
                                    "
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
                                            stroke-width="1.8"
                                            d="M12 6v6l4 2"
                                        />
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="9"
                                            stroke-width="1.8"
                                        />
                                    </svg>

                                </div>


                                <div>

                                    <h3 class="dd-section-title">
                                        Perbarui Status
                                    </h3>

                                    <p class="dd-section-subtitle">
                                        Ubah status pekerjaan disposisi.
                                    </p>

                                </div>

                            </div>


                            <form
                                action="{{ route(
                                    'disposisi.status',
                                    $disposisi
                                ) }}"
                                method="POST"
                                class="dd-status-form"
                            >

                                @csrf
                                @method('PATCH')


                                <select
                                    name="status"
                                    class="dd-status-select"
                                >

                                    <option
                                        value="menunggu"
                                        @selected($st === 'menunggu')
                                    >
                                        Menunggu
                                    </option>


                                    <option
                                        value="diproses"
                                        @selected(
                                            $st === 'diproses' ||
                                            $st === 'proses'
                                        )
                                    >
                                        Diproses
                                    </option>


                                    <option
                                        value="selesai"
                                        @selected($st === 'selesai')
                                    >
                                        Selesai
                                    </option>

                                </select>


                                <button
                                    type="submit"
                                    class="dd-status-submit"
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
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>

                                    Simpan Status

                                </button>

                            </form>

                        </div>

                    @endif

                </div>

            </div>

        </div>


        {{-- =================================================
             KANAN
        ================================================== --}}

        <div class="dd-right-column">

            <div class="dd-card dd-related-card">


                <div class="dd-card-header">

                    <div class="dd-card-heading">

                        <div class="dd-card-heading-icon">

                            <svg
                                class="w-4 h-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A1.5 1.5 0 0118 8.5V19a2 2 0 01-2 2z"
                                />
                            </svg>

                        </div>


                        <div>

                            <h2 class="dd-card-title">
                                Surat Masuk Terkait
                            </h2>

                            <p class="dd-card-subtitle">
                                Surat yang menjadi sumber disposisi.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="dd-related-body">

                    @if($suratMasuk)

                        <div class="dd-related-items">

                            {{-- PERIHAL --}}

                            <div class="dd-related-item">

                                <span class="dd-related-label">
                                    Perihal
                                </span>

                                <p class="dd-related-value">
                                    {{ $suratMasuk->perihal ?? 'Tanpa Perihal' }}
                                </p>

                            </div>


                            {{-- NOMOR SURAT --}}

                            <div class="dd-related-item">

                                <span class="dd-related-label">
                                    Nomor Surat
                                </span>

                                <p class="dd-related-value">
                                    {{ $nomorSurat }}
                                </p>

                            </div>


                            {{-- PENGIRIM --}}

                            <div class="dd-related-item">

                                <span class="dd-related-label">
                                    Asal / Pengirim
                                </span>

                                <p class="dd-related-value">
                                    {{
                                        $suratMasuk->pengirim
                                        ?? $suratMasuk->instansi?->nama_instansi
                                        ?? '-'
                                    }}
                                </p>

                            </div>


                            {{-- TANGGAL SURAT --}}

                            <div class="dd-related-item">

                                <span class="dd-related-label">
                                    Tanggal Surat
                                </span>

                                <p class="dd-related-value">
                                    {{ $tanggalSurat }}
                                </p>

                            </div>

                        </div>


                        {{-- DETAIL SURAT --}}

                        <a
                            href="{{ route(
                                'surat-masuk.show',
                                $suratMasuk
                            ) }}"
                            class="dd-related-button"
                        >

                            Lihat Detail Surat

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
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                                />
                            </svg>

                        </a>

                    @else

                        <div class="dd-related-empty">

                            <div class="dd-related-empty-icon">

                                <svg
                                    class="w-6 h-6"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586A1.5 1.5 0 0118 8.5V19a2 2 0 01-2 2H7a2 2 0 01-2-2v-5"
                                    />
                                </svg>

                            </div>


                            <p class="dd-related-empty-title">
                                Surat terkait tidak ditemukan
                            </p>


                            <p class="dd-related-empty-text">
                                Data surat masuk yang menjadi sumber
                                disposisi tidak tersedia.
                            </p>

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         FOOTER
    ====================================================== --}}

    <div class="dd-footer">

        <span>
            Sistem Manajemen Disposisi
        </span>

    </div>

</div>

@endsection