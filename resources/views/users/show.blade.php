@extends('layouts.app')

@section('title', 'Detail Pengguna')

@section('content')

@php
/*
|--------------------------------------------------------------------------
| NORMALISASI ROLE
|--------------------------------------------------------------------------
*/

```
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
    'admin' => 'Admin',
    'pimpinan' => 'Pimpinan',
    'staff' => 'Staf',
    default => ucfirst($role ?: 'User'),
};

$roleDescription = match ($role) {
    'admin' => 'Akses penuh sistem',
    'pimpinan' => 'Akses pimpinan',
    'staff' => 'Akses operator/staf',
    default => 'Pengguna sistem',
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
        $userName !== ''
            ? $userName
            : 'US',
        0,
        2
    )
);
```

@endphp

<div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-10">

```
{{-- =========================================================
     HEADER
========================================================== --}}

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">

    <div class="min-w-0">

        <div class="flex items-center gap-2">

            <span class="w-2.5 h-2.5 shrink-0 rounded-full bg-blue-600"></span>

            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-800 truncate">
                Detail Pengguna
            </h1>

        </div>

        <p class="mt-0.5 text-xs sm:text-sm text-slate-500">
            Informasi lengkap akun pengguna yang tersimpan di dalam sistem.
        </p>

    </div>

    <div class="flex flex-col sm:flex-row gap-2">

        <a
            href="{{ route('users.index') }}"
            class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 text-sm font-semibold text-slate-700 bg-white border-2 border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 transition"
        >

            <svg
                class="w-4 h-4 mr-2"
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

            Kembali

        </a>

        <a
            href="{{ route('users.edit', $user->id) }}"
            class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 text-sm font-semibold text-white bg-blue-600 border-2 border-blue-600 rounded-xl hover:bg-blue-700 transition"
        >

            <svg
                class="w-4 h-4 mr-2"
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

            Edit Pengguna

        </a>

    </div>

</div>


{{-- =========================================================
     PROFILE HEADER
========================================================== --}}

<div class="detail-main-card mb-4">

    <div class="detail-profile">

        <div class="detail-avatar">
            {{ $avatarText }}
        </div>

        <div class="detail-profile-info">

            <h2 class="detail-profile-name">
                {{ $user->name ?? '-' }}
            </h2>

            <p class="detail-profile-email">
                {{ $user->email ?? '-' }}
            </p>

            <div class="detail-profile-badges">

                @if($role === 'admin')

                    <span class="detail-role admin">
                        Admin
                    </span>

                @elseif($role === 'pimpinan')

                    <span class="detail-role pimpinan">
                        Pimpinan
                    </span>

                @elseif($role === 'staff')

                    <span class="detail-role staff">
                        Staf
                    </span>

                @else

                    <span class="detail-role default">
                        {{ $roleLabel }}
                    </span>

                @endif


                @if($isActive)

                    <span class="detail-status active">
                        <span class="detail-status-dot"></span>
                        Aktif
                    </span>

                @else

                    <span class="detail-status inactive">
                        <span class="detail-status-dot"></span>
                        Nonaktif
                    </span>

                @endif

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     INFORMASI PENGGUNA
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

            <div>

                <h2 class="detail-card-title">
                    Identitas Pengguna
                </h2>

                <p class="detail-card-description">
                    Informasi dasar pengguna.
                </p>

            </div>

        </div>

        <div class="detail-card-body">

            <div class="detail-row">

                <div class="detail-label">
                    ID Pengguna
                </div>

                <div class="detail-value">
                    #{{ $user->id }}
                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Nama Lengkap
                </div>

                <div class="detail-value">
                    {{ $user->name ?? '-' }}
                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Alamat Email
                </div>

                <div class="detail-value detail-break">
                    {{ $user->email ?? '-' }}
                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Jabatan / Posisi
                </div>

                <div class="detail-value">
                    {{ $user->jabatan ?? '-' }}
                </div>

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

            <div>

                <h2 class="detail-card-title">
                    Hak Akses
                </h2>

                <p class="detail-card-description">
                    Role dan kewenangan akun.
                </p>

            </div>

        </div>

        <div class="detail-card-body">

            <div class="detail-row">

                <div class="detail-label">
                    Role
                </div>

                <div class="detail-value detail-value-right">

                    @if($role === 'admin')

                        <span class="detail-role admin">
                            Admin
                        </span>

                    @elseif($role === 'pimpinan')

                        <span class="detail-role pimpinan">
                            Pimpinan
                        </span>

                    @elseif($role === 'staff')

                        <span class="detail-role staff">
                            Staf
                        </span>

                    @else

                        <span class="detail-role default">
                            {{ $roleLabel }}
                        </span>

                    @endif

                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Keterangan Role
                </div>

                <div class="detail-value">
                    {{ $roleDescription }}
                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Status Akun
                </div>

                <div class="detail-value detail-value-right">

                    @if($isActive)

                        <span class="detail-status active">
                            <span class="detail-status-dot"></span>
                            Aktif
                        </span>

                    @else

                        <span class="detail-status inactive">
                            <span class="detail-status-dot"></span>
                            Nonaktif
                        </span>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         STATUS EMAIL
    ====================================================== --}}

    <div class="detail-card">

        <div class="detail-card-header">

            <div class="detail-card-icon green">

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
                        d="M4 6h16v12H4z"
                    />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4 7l8 6 8-6"
                    />
                </svg>

            </div>

            <div>

                <h2 class="detail-card-title">
                    Verifikasi Email
                </h2>

                <p class="detail-card-description">
                    Status verifikasi alamat email.
                </p>

            </div>

        </div>

        <div class="detail-card-body">

            <div class="detail-row">

                <div class="detail-label">
                    Status Verifikasi
                </div>

                <div class="detail-value detail-value-right">

                    @if($user->email_verified_at)

                        <span class="verification-badge verified">
                            <span class="verification-dot"></span>
                            Terverifikasi
                        </span>

                    @else

                        <span class="verification-badge unverified">
                            <span class="verification-dot"></span>
                            Belum Terverifikasi
                        </span>

                    @endif

                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Terverifikasi Pada
                </div>

                <div class="detail-value">

                    @if($user->email_verified_at)

                        {{ $user->email_verified_at->format('d/m/Y H:i:s') }}

                    @else

                        Belum tersedia

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         INFORMASI SISTEM
    ====================================================== --}}

    <div class="detail-card">

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

            <div>

                <h2 class="detail-card-title">
                    Informasi Sistem
                </h2>

                <p class="detail-card-description">
                    Waktu pembuatan dan perubahan akun.
                </p>

            </div>

        </div>

        <div class="detail-card-body">

            <div class="detail-row">

                <div class="detail-label">
                    Dibuat Pada
                </div>

                <div class="detail-value">

                    @if($user->created_at)

                        {{ $user->created_at->format('d/m/Y H:i:s') }}

                    @else

                        -

                    @endif

                </div>

            </div>


            <div class="detail-row">

                <div class="detail-label">
                    Terakhir Diperbarui
                </div>

                <div class="detail-value">

                    @if($user->updated_at)

                        {{ $user->updated_at->format('d/m/Y H:i:s') }}

                    @else

                        -

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================
         RINGKASAN AKUN
    ====================================================== --}}

    <div class="detail-card detail-card-full">

        <div class="detail-card-header">

            <div class="detail-card-icon orange">

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
                        d="M12 3l8 4v5c0 4.5-3.3 7.8-8 9c-4.7-1.2-8-4.5-8-9V7l8-4z"
                    />

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12l2 2 4-4"
                    />
                </svg>

            </div>

            <div>

                <h2 class="detail-card-title">
                    Ringkasan Akun
                </h2>

                <p class="detail-card-description">
                    Status keseluruhan akun pengguna.
                </p>

            </div>

        </div>

        <div class="detail-summary">

            <div class="summary-item">

                <span class="summary-label">
                    Akun
                </span>

                @if($isActive)

                    <span class="summary-value active">
                        Aktif
                    </span>

                @else

                    <span class="summary-value inactive">
                        Nonaktif
                    </span>

                @endif

            </div>


            <div class="summary-item">

                <span class="summary-label">
                    Role
                </span>

                <span class="summary-value neutral">
                    {{ $roleLabel }}
                </span>

            </div>


            <div class="summary-item">

                <span class="summary-label">
                    Jabatan
                </span>

                <span class="summary-value neutral">
                    {{ $user->jabatan ?? '-' }}
                </span>

            </div>


            <div class="summary-item">

                <span class="summary-label">
                    Email
                </span>

                <span class="summary-value neutral detail-break">
                    {{ $user->email ?? '-' }}
                </span>

            </div>

        </div>

    </div>

</div>


{{-- =========================================================
     FOOTER ACTION
========================================================== --}}

<div class="mt-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">

    <a
        href="{{ route('users.index') }}"
        class="detail-footer-button secondary"
    >

        <svg
            class="w-4 h-4 mr-2"
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

        Kembali ke Daftar

    </a>


    <a
        href="{{ route('users.edit', $user->id) }}"
        class="detail-footer-button primary"
    >

        <svg
            class="w-4 h-4 mr-2"
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

        Edit Pengguna

    </a>

</div>
```

</div>

{{-- =========================================================
CSS
========================================================= --}}

<style>
.detail-main-card{
    overflow:hidden;
    border:2px solid #cbd5e1;
    border-radius:16px;
    background:#fff;
    box-shadow:
        0 1px 3px rgba(15,23,42,.06),
        0 8px 24px rgba(15,23,42,.04)
}

.detail-profile{
    display:flex;
    align-items:center;
    gap:16px;
    padding:20px
}

.detail-avatar{
    display:flex;
    width:68px;
    height:68px;
    flex-shrink:0;
    align-items:center;
    justify-content:center;
    border:2px solid #bfdbfe;
    border-radius:18px;
    background:#eff6ff;
    color:#2563eb;
    font-size:20px;
    font-weight:800
}

.detail-profile-info{
    min-width:0;
    flex:1
}

.detail-profile-name{
    overflow:hidden;
    color:#1e293b;
    font-size:20px;
    font-weight:800;
    line-height:1.35;
    text-overflow:ellipsis;
    white-space:nowrap
}

.detail-profile-email{
    margin-top:3px;
    overflow:hidden;
    color:#64748b;
    font-size:12px;
    line-height:1.4;
    text-overflow:ellipsis;
    white-space:nowrap
}

.detail-profile-badges{
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:7px;
    margin-top:10px
}

.detail-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:14px
}

.detail-card{
    overflow:hidden;
    border:2px solid #cbd5e1;
    border-radius:15px;
    background:#fff;
    box-shadow:
        0 1px 3px rgba(15,23,42,.05),
        0 8px 20px rgba(15,23,42,.03)
}

.detail-card-full{
    grid-column:1 / -1
}

.detail-card-header{
    display:flex;
    align-items:center;
    gap:10px;
    padding:13px 15px;
    border-bottom:2px solid #cbd5e1;
    background:#f8fafc
}

.detail-card-icon{
    display:flex;
    width:34px;
    height:34px;
    flex-shrink:0;
    align-items:center;
    justify-content:center;
    border-radius:9px
}

.detail-card-icon.blue{
    color:#2563eb;
    background:#eff6ff;
    border:1px solid #bfdbfe
}

.detail-card-icon.purple{
    color:#7e22ce;
    background:#faf5ff;
    border:1px solid #e9d5ff
}

.detail-card-icon.green{
    color:#047857;
    background:#ecfdf5;
    border:1px solid #a7f3d0
}

.detail-card-icon.slate{
    color:#475569;
    background:#f1f5f9;
    border:1px solid #cbd5e1
}

.detail-card-icon.orange{
    color:#c2410c;
    background:#fff7ed;
    border:1px solid #fed7aa
}

.detail-card-title{
    color:#334155;
    font-size:11px;
    font-weight:800;
    letter-spacing:.04em;
    line-height:1.25;
    text-transform:uppercase
}

.detail-card-description{
    margin-top:2px;
    color:#94a3b8;
    font-size:8px;
    line-height:1.25
}

.detail-card-body{
    padding:0 15px
}

.detail-row{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:18px;
    padding:12px 0;
    border-bottom:1px solid #e2e8f0
}

.detail-row:last-child{
    border-bottom:0
}

.detail-label{
    flex-shrink:0;
    color:#64748b;
    font-size:9px;
    font-weight:800;
    letter-spacing:.03em;
    line-height:1.4;
    text-transform:uppercase
}

.detail-value{
    color:#334155;
    font-size:11px;
    font-weight:700;
    line-height:1.5;
    text-align:right
}

.detail-value-right{
    display:flex;
    align-items:center;
    justify-content:flex-end
}

.detail-break{
    overflow-wrap:anywhere;
    word-break:break-word
}

.detail-role{
    display:inline-flex;
    min-height:25px;
    align-items:center;
    border:1px solid;
    border-radius:8px;
    padding:0 9px;
    font-size:9px;
    font-weight:800;
    white-space:nowrap
}

.detail-role.admin{
    border-color:#e9d5ff;
    background:#faf5ff;
    color:#7e22ce
}

.detail-role.pimpinan{
    border-color:#fde68a;
    background:#fffbeb;
    color:#b45309
}

.detail-role.staff{
    border-color:#bfdbfe;
    background:#eff6ff;
    color:#1d4ed8
}

.detail-role.default{
    border-color:#cbd5e1;
    background:#f1f5f9;
    color:#475569
}

.detail-status{
    display:inline-flex;
    min-height:25px;
    align-items:center;
    gap:6px;
    border:1px solid;
    border-radius:999px;
    padding:0 9px;
    font-size:9px;
    font-weight:800;
    white-space:nowrap
}

.detail-status.active{
    border-color:#a7f3d0;
    background:#ecfdf5;
    color:#047857
}

.detail-status.inactive{
    border-color:#fecdd3;
    background:#fff1f2;
    color:#be123c
}

.detail-status-dot{
    width:6px;
    height:6px;
    flex-shrink:0;
    border-radius:999px;
    background:currentColor
}

.verification-badge{
    display:inline-flex;
    min-height:25px;
    align-items:center;
    gap:6px;
    border:1px solid;
    border-radius:999px;
    padding:0 9px;
    font-size:9px;
    font-weight:800;
    white-space:nowrap
}

.verification-badge.verified{
    border-color:#a7f3d0;
    background:#ecfdf5;
    color:#047857
}

.verification-badge.unverified{
    border-color:#fde68a;
    background:#fffbeb;
    color:#b45309
}

.verification-dot{
    width:6px;
    height:6px;
    border-radius:999px;
    background:currentColor
}

.detail-summary{
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:10px;
    padding:15px
}

.summary-item{
    min-width:0;
    padding:11px 12px;
    border:1px solid #cbd5e1;
    border-radius:10px;
    background:#f8fafc
}

.summary-label{
    display:block;
    margin-bottom:4px;
    color:#94a3b8;
    font-size:8px;
    font-weight:800;
    letter-spacing:.04em;
    line-height:1.3;
    text-transform:uppercase
}

.summary-value{
    display:block;
    color:#334155;
    font-size:10px;
    font-weight:800;
    line-height:1.4
}

.summary-value.active{
    color:#047857
}

.summary-value.inactive{
    color:#be123c
}

.summary-value.neutral{
    color:#334155
}

.detail-footer-button{
    display:inline-flex;
    width:100%;
    align-items:center;
    justify-content:center;
    padding:.55rem 1rem;
    border:2px solid;
    border-radius:.65rem;
    font-size:.76rem;
    font-weight:700;
    transition:all .15s ease
}

.detail-footer-button.secondary{
    color:#334155;
    background:#fff;
    border-color:#cbd5e1
}

.detail-footer-button.secondary:hover{
    background:#f1f5f9;
    border-color:#94a3b8
}

.detail-footer-button.primary{
    color:#fff;
    background:#2563eb;
    border-color:#2563eb
}

.detail-footer-button.primary:hover{
    background:#1d4ed8;
    border-color:#1d4ed8
}

@media(min-width:640px){

    .detail-footer-button{
        width:auto
    }
}

@media(max-width:767px){

    .detail-grid{
        grid-template-columns:1fr
    }

    .detail-card-full{
        grid-column:auto
    }

    .detail-summary{
        grid-template-columns:repeat(2,minmax(0,1fr))
    }
}

@media(max-width:640px){

    .detail-profile{
        align-items:flex-start;
        padding:16px
    }

    .detail-avatar{
        width:58px;
        height:58px;
        border-radius:15px;
        font-size:17px
    }

    .detail-profile-name{
        font-size:16px
    }

    .detail-profile-email{
        font-size:10px
    }

    .detail-card-body{
        padding:0 13px
    }

    .detail-row{
        flex-direction:column;
        gap:5px
    }

    .detail-value{
        width:100%;
        text-align:left
    }

    .detail-value-right{
        justify-content:flex-start
    }

    .detail-summary{
        grid-template-columns:1fr
    }
}
</style>

@endsection
