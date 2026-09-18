@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')

@php
/*
|--------------------------------------------------------------------------
| NORMALISASI ROLE
|--------------------------------------------------------------------------
*/

$role = strtolower(
    trim((string) ($user->role ?? ''))
);

if ($role === 'staf') {
    $role = 'staff';
}

/*
|--------------------------------------------------------------------------
| STATUS AKUN
|--------------------------------------------------------------------------
*/

$isActive = (bool) ($user->is_active ?? false);

/*
|--------------------------------------------------------------------------
| LABEL ROLE
|--------------------------------------------------------------------------
*/

$roleLabel = match ($role) {
    'admin'    => 'Admin',
    'pimpinan' => 'Pimpinan',
    'staff'    => 'Staf',
    default    => ucfirst($role ?: 'User'),
};

$roleDescription = match ($role) {
    'admin'    => 'Akses penuh sistem',
    'pimpinan' => 'Akses pimpinan',
    'staff'    => 'Akses operator / staf',
    default    => 'Pengguna sistem',
};

/*
|--------------------------------------------------------------------------
| AVATAR
|--------------------------------------------------------------------------
*/

$userName = trim(
    (string) ($user->name ?? '')
);

$avatarText = strtoupper(
    mb_substr(
        $userName !== '' ? $userName : 'US',
        0,
        2
    )
);


@endphp

<div class="user-detail-page w-full max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">


{{-- =========================================================
     HEADER
========================================================== --}}

<div class="detail-page-header">

    <div class="detail-header-left">

        <div class="detail-title-icon">
            <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 19l-7-7 7-7"
                />
            </svg>
        </div>

        <div class="min-w-0">

            <div class="detail-title-line">

                <h1 class="detail-page-title">
                    Detail Pengguna
                </h1>

                @if($isActive)

                    <span class="top-status active">
                        <span class="status-dot"></span>
                        Aktif
                    </span>

                @else

                    <span class="top-status inactive">
                        <span class="status-dot"></span>
                        Nonaktif
                    </span>

                @endif

            </div>

            <p class="detail-page-subtitle">
                Informasi lengkap akun pengguna yang tersimpan di sistem.
            </p>

        </div>

    </div>


    {{-- =====================================================
         ACTION BUTTON
    ====================================================== --}}

    <div class="detail-header-actions">

        <a
            href="{{ route('users.index') }}"
            class="detail-action secondary"
        >
            <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15 19l-7-7 7-7"
                />
            </svg>

            <span>Kembali</span>
        </a>


        <a
            href="{{ route('users.edit', $user->id) }}"
            class="detail-action primary"
        >
            <svg
                class="w-4 h-4"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 20h9"
                />

                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M16.5 3.5a2.12 2.12 0 013 3L8 18l-4 1 1-4L16.5 3.5z"
                />
            </svg>

            <span>Edit Pengguna</span>
        </a>

    </div>

</div>


{{-- =========================================================
     PROFILE CARD
========================================================== --}}

<div class="profile-card">

    <div class="profile-card-main">

        <div class="profile-avatar">
            {{ $avatarText }}
        </div>


        <div class="profile-info">

            <h2 class="profile-name">
                {{ $user->name ?? '-' }}
            </h2>

            <p class="profile-email">
                {{ $user->email ?? '-' }}
            </p>


            <div class="profile-meta">

                {{-- ROLE --}}

                @if($role === 'admin')

                    <span class="role-badge admin">

                        <svg
                            class="w-3.5 h-3.5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 3l7 4v5c0 4-2.8 7.2-7 9-4.2-1.8-7-5-7-9V7l7-4z"
                            />
                        </svg>

                        Admin

                    </span>

                @elseif($role === 'pimpinan')

                    <span class="role-badge pimpinan">

                        <svg
                            class="w-3.5 h-3.5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M3 21h18"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M5 21V7l7-4 7 4v14"
                            />

                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M9 21v-5h6v5"
                            />
                        </svg>

                        Pimpinan

                    </span>

                @elseif($role === 'staff')

                    <span class="role-badge staff">

                        <svg
                            class="w-3.5 h-3.5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                cx="12"
                                cy="8"
                                r="3.5"
                            />

                            <path
                                stroke-linecap="round"
                                d="M5 20a7 7 0 0114 0"
                            />
                        </svg>

                        Staf

                    </span>

                @else

                    <span class="role-badge default">
                        {{ $roleLabel }}
                    </span>

                @endif


                {{-- STATUS --}}

                @if($isActive)

                    <span class="account-status active">
                        <span class="status-dot"></span>
                        Akun Aktif
                    </span>

                @else

                    <span class="account-status inactive">
                        <span class="status-dot"></span>
                        Akun Nonaktif
                    </span>

                @endif

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     INFORMATION GRID
========================================================== --}}

<div class="detail-grid">


    {{-- =====================================================
         IDENTITAS PENGGUNA
    ====================================================== --}}

    <div class="detail-card">

        <div class="detail-card-header">

            <div class="detail-card-icon blue">

                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    viewBox="0 0 24 24"
                >
                    <circle
                        cx="12"
                        cy="8"
                        r="3.5"
                    />

                    <path
                        stroke-linecap="round"
                        d="M5 20a7 7 0 0114 0"
                    />
                </svg>

            </div>


            <div class="min-w-0">

                <h2 class="detail-card-title">
                    Identitas Pengguna
                </h2>

                <p class="detail-card-description">
                    Informasi dasar akun pengguna
                </p>

            </div>

        </div>


        <div class="detail-card-body">

            <div class="detail-row">

                <span class="detail-label">
                    ID Pengguna
                </span>

                <span class="detail-value strong">
                    #{{ $user->id }}
                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Nama Lengkap
                </span>

                <span class="detail-value">
                    {{ $user->name ?: '-' }}
                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Alamat Email
                </span>

                <span class="detail-value detail-break">
                    {{ $user->email ?: '-' }}
                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Jabatan / Posisi
                </span>

                <span class="detail-value">
                    {{ $user->jabatan ?: '-' }}
                </span>

            </div>

        </div>

    </div>


    {{-- =====================================================
         HAK AKSES
    ====================================================== --}}

    <div class="detail-card">

        <div class="detail-card-header">

            <div class="detail-card-icon purple">

                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    viewBox="0 0 24 24"
                >
                    <rect
                        x="4"
                        y="10"
                        width="16"
                        height="10"
                        rx="2"
                    />

                    <path
                        stroke-linecap="round"
                        d="M8 10V7a4 4 0 018 0v3"
                    />

                </svg>

            </div>


            <div class="min-w-0">

                <h2 class="detail-card-title">
                    Hak Akses
                </h2>

                <p class="detail-card-description">
                    Role dan kewenangan akun
                </p>

            </div>

        </div>


        <div class="detail-card-body">

            <div class="detail-row">

                <span class="detail-label">
                    Role
                </span>

                <span class="detail-value">

                    @if($role === 'admin')

                        <span class="role-badge admin">
                            Admin
                        </span>

                    @elseif($role === 'pimpinan')

                        <span class="role-badge pimpinan">
                            Pimpinan
                        </span>

                    @elseif($role === 'staff')

                        <span class="role-badge staff">
                            Staf
                        </span>

                    @else

                        <span class="role-badge default">
                            {{ $roleLabel }}
                        </span>

                    @endif

                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Keterangan Role
                </span>

                <span class="detail-value">
                    {{ $roleDescription }}
                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Status Akun
                </span>

                <span class="detail-value">

                    @if($isActive)

                        <span class="account-status active">
                            <span class="status-dot"></span>
                            Aktif
                        </span>

                    @else

                        <span class="account-status inactive">
                            <span class="status-dot"></span>
                            Nonaktif
                        </span>

                    @endif

                </span>

            </div>

        </div>

    </div>


    {{-- =====================================================
         INFORMASI SISTEM
    ====================================================== --}}

    <div class="detail-card detail-card-system">

        <div class="detail-card-header">

            <div class="detail-card-icon slate">

                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    viewBox="0 0 24 24"
                >
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                    />

                    <path
                        stroke-linecap="round"
                        d="M12 7v5l3 2"
                    />

                </svg>

            </div>


            <div class="min-w-0">

                <h2 class="detail-card-title">
                    Informasi Sistem
                </h2>

                <p class="detail-card-description">
                    Informasi waktu akun dibuat dan diperbarui
                </p>

            </div>

        </div>


        <div class="detail-card-body">

            <div class="detail-row">

                <span class="detail-label">
                    Dibuat Pada
                </span>

                <span class="detail-value">

                    @if($user->created_at)

                        {{ $user->created_at->format('d/m/Y H:i:s') }}

                    @else

                        -

                    @endif

                </span>

            </div>


            <div class="detail-row">

                <span class="detail-label">
                    Terakhir Diperbarui
                </span>

                <span class="detail-value">

                    @if($user->updated_at)

                        {{ $user->updated_at->format('d/m/Y H:i:s') }}

                    @else

                        -

                    @endif

                </span>

            </div>

        </div>

    </div>

</div>


</div>

<style>

/* =========================================================
   PAGE
========================================================= */

.user-detail-page {
    color: #0f172a;
}


/* =========================================================
   HEADER
========================================================= */

.detail-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 18px;
}

.detail-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.detail-title-icon {
    display: flex;
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #2563eb;
}

.detail-title-line {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.detail-page-title {
    color: #0f172a;
    font-size: 21px;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -.02em;
}

.detail-page-subtitle {
    margin-top: 3px;
    color: #64748b;
    font-size: 11px;
    line-height: 1.45;
}

.detail-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.detail-action {
    display: inline-flex;
    min-height: 40px;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 0 14px;
    border: 1.5px solid;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 800;
    white-space: nowrap;
    text-decoration: none;
    transition:
        transform .15s ease,
        background .15s ease,
        border-color .15s ease,
        box-shadow .15s ease;
}

.detail-action:hover {
    transform: translateY(-1px);
}

.detail-action.secondary {
    color: #334155;
    background: #fff;
    border-color: #cbd5e1;
}

.detail-action.secondary:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    box-shadow: 0 4px 12px rgba(15,23,42,.06);
}

.detail-action.primary {
    color: #fff;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    border-color: #2563eb;
    box-shadow: 0 6px 14px rgba(37,99,235,.18);
}

.detail-action.primary:hover {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    box-shadow: 0 8px 18px rgba(37,99,235,.22);
}


/* =========================================================
   TOP STATUS
========================================================= */

.top-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 22px;
    padding: 0 9px;
    border: 1px solid;
    border-radius: 999px;
    font-size: 8px;
    font-weight: 800;
    white-space: nowrap;
}

.top-status.active {
    color: #047857;
    background: #ecfdf5;
    border-color: #a7f3d0;
}

.top-status.inactive {
    color: #be123c;
    background: #fff1f2;
    border-color: #fecdd3;
}

.status-dot {
    width: 5px;
    height: 5px;
    flex-shrink: 0;
    border-radius: 999px;
    background: currentColor;
}


/* =========================================================
   PROFILE CARD
========================================================= */

.profile-card {
    overflow: hidden;
    margin-bottom: 16px;
    border: 1px solid #dbe3ef;
    border-radius: 18px;
    background:
        linear-gradient(
            135deg,
            #ffffff 0%,
            #ffffff 68%,
            #f8fbff 100%
        );
    box-shadow:
        0 8px 30px rgba(15,23,42,.055),
        0 2px 8px rgba(15,23,42,.03);
}

.profile-card-main {
    display: flex;
    align-items: center;
    gap: 16px;
    width: 100%;
    min-width: 0;
    padding: 20px;
}

.profile-avatar {
    display: flex;
    width: 68px;
    height: 68px;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border: 2px solid #bfdbfe;
    border-radius: 18px;
    background:
        linear-gradient(
            135deg,
            #eff6ff,
            #dbeafe
        );
    color: #2563eb;
    font-size: 19px;
    font-weight: 900;
    letter-spacing: -.02em;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.85);
}

.profile-info {
    min-width: 0;
}

.profile-name {
    overflow: hidden;
    color: #0f172a;
    font-size: 19px;
    font-weight: 850;
    line-height: 1.35;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.profile-email {
    margin-top: 3px;
    overflow: hidden;
    color: #64748b;
    font-size: 11px;
    line-height: 1.45;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.profile-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 7px;
    margin-top: 11px;
}


/* =========================================================
   BADGES
========================================================= */

.role-badge,
.account-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    min-height: 25px;
    padding: 0 9px;
    border: 1px solid;
    border-radius: 8px;
    font-size: 9px;
    font-weight: 800;
    white-space: nowrap;
}

.role-badge.admin {
    color: #7e22ce;
    background: #faf5ff;
    border-color: #e9d5ff;
}

.role-badge.pimpinan {
    color: #b45309;
    background: #fffbeb;
    border-color: #fde68a;
}

.role-badge.staff {
    color: #1d4ed8;
    background: #eff6ff;
    border-color: #bfdbfe;
}

.role-badge.default {
    color: #475569;
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.account-status {
    border-radius: 999px;
}

.account-status.active {
    color: #047857;
    background: #ecfdf5;
    border-color: #a7f3d0;
}

.account-status.inactive {
    color: #be123c;
    background: #fff1f2;
    border-color: #fecdd3;
}


/* =========================================================
   DETAIL GRID
========================================================= */

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}


/* =========================================================
   DETAIL CARD
========================================================= */

.detail-card {
    overflow: hidden;
    min-width: 0;
    border: 1px solid #dbe3ef;
    border-radius: 16px;
    background: #fff;
    box-shadow:
        0 5px 18px rgba(15,23,42,.04),
        0 1px 4px rgba(15,23,42,.025);
    transition:
        transform .15s ease,
        box-shadow .15s ease,
        border-color .15s ease;
}

.detail-card:hover {
    border-color: #cbd5e1;
    box-shadow:
        0 8px 24px rgba(15,23,42,.055),
        0 2px 7px rgba(15,23,42,.025);
}

.detail-card-system {
    grid-column: 1 / -1;
}


/* =========================================================
   CARD HEADER
========================================================= */

.detail-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 15px;
    border-bottom: 1px solid #e2e8f0;
    background:
        linear-gradient(
            180deg,
            #ffffff,
            #f8fafc
        );
}

.detail-card-icon {
    display: flex;
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border: 1px solid;
    border-radius: 10px;
}

.detail-card-icon.blue {
    color: #2563eb;
    background: #eff6ff;
    border-color: #bfdbfe;
}

.detail-card-icon.purple {
    color: #7e22ce;
    background: #faf5ff;
    border-color: #e9d5ff;
}

.detail-card-icon.slate {
    color: #475569;
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.detail-card-title {
    color: #334155;
    font-size: 10px;
    font-weight: 900;
    letter-spacing: .045em;
    line-height: 1.25;
    text-transform: uppercase;
}

.detail-card-description {
    margin-top: 2px;
    color: #94a3b8;
    font-size: 8px;
    line-height: 1.3;
}


/* =========================================================
   CARD BODY
========================================================= */

.detail-card-body {
    padding: 0 15px;
}

.detail-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 11px 0;
    border-bottom: 1px solid #edf2f7;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    flex-shrink: 0;
    color: #64748b;
    font-size: 8px;
    font-weight: 900;
    letter-spacing: .055em;
    line-height: 1.45;
    text-transform: uppercase;
}

.detail-value {
    max-width: 68%;
    color: #334155;
    font-size: 10.5px;
    font-weight: 700;
    line-height: 1.5;
    text-align: right;
}

.detail-value.strong {
    color: #0f172a;
    font-weight: 850;
}

.detail-break {
    overflow-wrap: anywhere;
    word-break: break-word;
}


/* =========================================================
   RESPONSIVE - TABLET
========================================================= */

@media (max-width: 900px) {

    .detail-grid {
        grid-template-columns: 1fr;
    }

    .detail-card-system {
        grid-column: auto;
    }
}


/* =========================================================
   RESPONSIVE - MOBILE
========================================================= */

@media (max-width: 767px) {

    .detail-page-header {
        align-items: flex-start;
        flex-direction: column;
        gap: 12px;
    }

    .detail-header-actions {
        width: 100%;
    }

    .detail-action {
        flex: 1;
    }

    .profile-card-main {
        align-items: flex-start;
        padding: 16px;
    }

    .profile-name {
        white-space: normal;
    }

    .profile-email {
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .detail-row {
        gap: 8px;
    }

    .detail-value {
        max-width: 62%;
    }
}


/* =========================================================
   RESPONSIVE - SMALL MOBILE
========================================================= */

@media (max-width: 640px) {

    .detail-page-header {
        margin-bottom: 14px;
    }

    .detail-title-icon {
        width: 38px;
        height: 38px;
    }

    .detail-page-title {
        font-size: 18px;
    }

    .detail-page-subtitle {
        font-size: 9px;
    }

    .detail-header-actions {
        gap: 7px;
    }

    .detail-action {
        min-height: 38px;
        padding: 0 11px;
        font-size: 10px;
    }

    .profile-card {
        border-radius: 15px;
    }

    .profile-card-main {
        gap: 12px;
        padding: 15px;
    }

    .profile-avatar {
        width: 56px;
        height: 56px;
        border-radius: 15px;
        font-size: 16px;
    }

    .profile-name {
        font-size: 16px;
    }

    .profile-email {
        font-size: 9px;
    }

    .profile-meta {
        gap: 5px;
        margin-top: 9px;
    }

    .detail-card {
        border-radius: 14px;
    }

    .detail-card-header {
        padding: 12px 13px;
    }

    .detail-card-body {
        padding: 0 13px;
    }

    .detail-row {
        flex-direction: column;
        gap: 5px;
        padding: 10px 0;
    }

    .detail-value {
        width: 100%;
        max-width: 100%;
        text-align: left;
    }

    .detail-label {
        font-size: 7.5px;
    }

    .detail-value {
        font-size: 10px;
    }
}


/* =========================================================
   RESPONSIVE - VERY SMALL
========================================================= */

@media (max-width: 420px) {

    .detail-header-left {
        align-items: flex-start;
    }

    .detail-title-line {
        gap: 6px;
    }

    .detail-page-title {
        font-size: 17px;
    }

    .top-status {
        min-height: 20px;
        padding: 0 7px;
        font-size: 7.5px;
    }

    .detail-action span {
        font-size: 9px;
    }

    .detail-action {
        gap: 5px;
        padding: 0 9px;
    }

    .profile-meta {
        gap: 5px;
    }

    .role-badge,
    .account-status {
        font-size: 8px;
    }
}

</style>

@endsection
