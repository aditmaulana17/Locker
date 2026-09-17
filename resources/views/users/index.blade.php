@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')

@php
/*
|--------------------------------------------------------------------------
| ROLE FILTER
|--------------------------------------------------------------------------
*/


$rawRoles = request('role', []);

if (
    is_scalar($rawRoles) &&
    trim((string) $rawRoles) !== ''
) {
    $rawRoles = [$rawRoles];
}

if (!is_array($rawRoles)) {
    $rawRoles = [];
}

$selectedRoles = collect($rawRoles)
    ->flatten()
    ->filter(
        fn ($role) => is_scalar($role)
    )
    ->map(
        fn ($role) => strtolower(
            trim((string) $role)
        )
    )
    ->map(
        fn ($role) => $role === 'staf'
            ? 'staff'
            : $role
    )
    ->filter(
        fn ($role) => in_array(
            $role,
            [
                'admin',
                'pimpinan',
                'staff',
            ],
            true
        )
    )
    ->unique()
    ->values()
    ->all();


/*
|--------------------------------------------------------------------------
| STATUS FILTER
|--------------------------------------------------------------------------
*/

$rawStatuses = request(
    'is_active',
    []
);

if (
    is_scalar($rawStatuses) &&
    trim((string) $rawStatuses) !== ''
) {
    $rawStatuses = [$rawStatuses];
}

if (!is_array($rawStatuses)) {
    $rawStatuses = [];
}

$selectedStatuses = collect($rawStatuses)
    ->flatten()
    ->filter(
        fn ($status) => is_scalar($status)
    )
    ->map(
        fn ($status) => (string) $status
    )
    ->filter(
        fn ($status) => in_array(
            $status,
            [
                '1',
                '0',
            ],
            true
        )
    )
    ->unique()
    ->values()
    ->all();


/*
|--------------------------------------------------------------------------
| FILTER AKTIF
|--------------------------------------------------------------------------
*/

$hasFilters =
    request()->filled('search') ||
    !empty($selectedRoles) ||
    !empty($selectedStatuses);


@endphp

<div class="space-y-3 pb-12 sm:space-y-4">

{{-- =========================================================
     HEADER
========================================================== --}}

<div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">

    <div class="min-w-0">

        <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">
            Manajemen Pengguna
        </h1>

        <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
            Kelola akun pengguna sistem, hak akses, peranan, dan status akun.
        </p>

    </div>


    <a
        href="{{ route('users.create') }}"
        class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-blue-700 sm:w-auto sm:text-sm"
    >

        <svg
            class="h-4 w-4 sm:h-5 sm:w-5"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            viewBox="0 0 24 24"
        >
            <path
                stroke-linecap="round"
                d="M12 4v16M4 12h16"
            />
        </svg>

        Tambah Pengguna

    </a>

</div>


{{-- =========================================================
     FILTER
========================================================== --}}

<div class="rounded-xl border-2 border-slate-400 bg-white p-3 shadow-sm sm:p-4">

    <form
        method="GET"
        action="{{ route('users.index') }}"
        class="flex flex-col gap-2 sm:flex-row sm:items-center"
    >

        {{-- SEARCH --}}

        <div class="relative w-full flex-1">

            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">

                <svg
                    class="h-4 w-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    viewBox="0 0 24 24"
                >
                    <circle
                        cx="11"
                        cy="11"
                        r="7"
                    />

                    <path
                        stroke-linecap="round"
                        d="M20 20l-4-4"
                    />
                </svg>

            </div>


            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari nama, email, atau jabatan..."
                autocomplete="off"
                class="users-filter-input h-11 w-full rounded-xl pl-10 pr-3 text-xs text-slate-800 outline-none transition-all placeholder:text-slate-400 sm:text-sm"
            >

        </div>


        {{-- ROLE + STATUS + ACTION --}}

        <div class="flex w-full items-center gap-2 sm:w-auto">

            {{-- ROLE DROPDOWN --}}

            <div
                class="users-role-dropdown relative min-w-0 flex-1 sm:w-56"
                data-role-dropdown
            >

                <button
                    type="button"
                    id="roleFilterTrigger"
                    class="users-role-trigger"
                    aria-expanded="false"
                    aria-haspopup="true"
                >

                    <span class="users-role-trigger-icon">

                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                cx="12"
                                cy="8"
                                r="3"
                            />

                            <path
                                stroke-linecap="round"
                                d="M5 20a7 7 0 0114 0"
                            />
                        </svg>

                    </span>


                    <span class="users-role-trigger-content">

                        <span class="users-role-trigger-title">
                            Role Pengguna
                        </span>

                        <span
                            id="roleFilterSubtitle"
                            class="users-role-trigger-subtitle"
                        >
                            Pilih satu atau beberapa role
                        </span>

                    </span>


                    <span
                        id="roleFilterCount"
                        class="users-role-count"
                    >
                        {{ count($selectedRoles) }} dipilih
                    </span>


                    <svg
                        id="roleFilterChevron"
                        class="users-role-chevron h-4 w-4 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 9l6 6 6-6"
                        />
                    </svg>

                </button>


                {{-- ROLE MENU --}}

                <div
                    id="roleFilterMenu"
                    class="users-role-menu hidden"
                >

                    <div class="users-role-menu-header">

                        <div class="min-w-0">

                            <span class="users-role-menu-title">
                                Pilih Role
                            </span>

                            <span class="users-role-menu-description">
                                Checkbox dapat dipilih lebih dari satu
                            </span>

                        </div>


                        <button
                            type="button"
                            id="roleFilterClose"
                            class="users-role-close"
                            aria-label="Tutup"
                        >
                            &times;
                        </button>

                    </div>


                    <div class="users-role-options">

                        {{-- ADMIN --}}

                        <label class="users-role-option">

                            <input
                                type="checkbox"
                                name="role[]"
                                value="admin"
                                class="role-checkbox"
                                @checked(
                                    in_array(
                                        'admin',
                                        $selectedRoles,
                                        true
                                    )
                                )
                            >

                            <span class="users-role-option-icon admin">
                                A
                            </span>

                            <span class="users-role-option-text">

                                <span class="users-role-option-title">
                                    Admin
                                </span>

                                <span class="users-role-option-description">
                                    Akses penuh sistem
                                </span>

                            </span>

                        </label>


                        {{-- PIMPINAN --}}

                        <label class="users-role-option">

                            <input
                                type="checkbox"
                                name="role[]"
                                value="pimpinan"
                                class="role-checkbox"
                                @checked(
                                    in_array(
                                        'pimpinan',
                                        $selectedRoles,
                                        true
                                    )
                                )
                            >

                            <span class="users-role-option-icon pimpinan">
                                P
                            </span>

                            <span class="users-role-option-text">

                                <span class="users-role-option-title">
                                    Pimpinan
                                </span>

                                <span class="users-role-option-description">
                                    Akses pimpinan
                                </span>

                            </span>

                        </label>


                        {{-- STAFF --}}

                        <label class="users-role-option">

                            <input
                                type="checkbox"
                                name="role[]"
                                value="staff"
                                class="role-checkbox"
                                @checked(
                                    in_array(
                                        'staff',
                                        $selectedRoles,
                                        true
                                    )
                                )
                            >

                            <span class="users-role-option-icon staff">
                                S
                            </span>

                            <span class="users-role-option-text">

                                <span class="users-role-option-title">
                                    Staf
                                </span>

                                <span class="users-role-option-description">
                                    Akses staf
                                </span>

                            </span>

                        </label>

                    </div>


                    <div class="users-role-menu-footer">

                        <button
                            type="button"
                            id="roleSelectAll"
                            class="users-role-action select"
                        >
                            Pilih Semua
                        </button>

                        <button
                            type="button"
                            id="roleClearAll"
                            class="users-role-action clear"
                        >
                            Batalkan
                        </button>

                    </div>

                </div>

            </div>


            {{-- STATUS DROPDOWN --}}

            <div
                class="users-status-dropdown relative min-w-0 flex-1 sm:w-52"
                data-status-dropdown
            >

                <button
                    type="button"
                    id="statusFilterTrigger"
                    class="users-status-trigger"
                    aria-expanded="false"
                    aria-haspopup="true"
                >

                    <span class="users-status-trigger-icon">

                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 3v18M5 7h14M7 17h10"
                            />
                        </svg>

                    </span>


                    <span class="users-status-trigger-content">

                        <span class="users-status-trigger-title">
                            Status Akun
                        </span>

                        <span
                            id="statusFilterSubtitle"
                            class="users-status-trigger-subtitle"
                        >
                            Pilih status akun
                        </span>

                    </span>


                    <span
                        id="statusFilterCount"
                        class="users-status-count"
                    >
                        {{ count($selectedStatuses) }} dipilih
                    </span>


                    <svg
                        id="statusFilterChevron"
                        class="users-status-chevron h-4 w-4 shrink-0"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 9l6 6 6-6"
                        />
                    </svg>

                </button>


                {{-- STATUS MENU --}}

                <div
                    id="statusFilterMenu"
                    class="users-status-menu hidden"
                >

                    <div class="users-status-menu-header">

                        <div class="min-w-0">

                            <span class="users-status-menu-title">
                                Pilih Status
                            </span>

                            <span class="users-status-menu-description">
                                Filter akun aktif atau nonaktif
                            </span>

                        </div>


                        <button
                            type="button"
                            id="statusFilterClose"
                            class="users-status-close"
                            aria-label="Tutup"
                        >
                            &times;
                        </button>

                    </div>


                    <div class="users-status-options">

                        {{-- AKTIF --}}

                        <label class="users-status-option">

                            <input
                                type="checkbox"
                                name="is_active[]"
                                value="1"
                                class="status-checkbox"
                                @checked(
                                    in_array(
                                        '1',
                                        $selectedStatuses,
                                        true
                                    )
                                )
                            >

                            <span class="users-status-option-icon active">
                                ✓
                            </span>

                            <span class="users-status-option-text">

                                <span class="users-status-option-title">
                                    Aktif
                                </span>

                                <span class="users-status-option-description">
                                    Dapat masuk ke sistem
                                </span>

                            </span>

                        </label>


                        {{-- NONAKTIF --}}

                        <label class="users-status-option">

                            <input
                                type="checkbox"
                                name="is_active[]"
                                value="0"
                                class="status-checkbox"
                                @checked(
                                    in_array(
                                        '0',
                                        $selectedStatuses,
                                        true
                                    )
                                )
                            >

                            <span class="users-status-option-icon inactive">
                                !
                            </span>

                            <span class="users-status-option-text">

                                <span class="users-status-option-title">
                                    Nonaktif
                                </span>

                                <span class="users-status-option-description">
                                    Diblokir dari sistem
                                </span>

                            </span>

                        </label>

                    </div>


                    <div class="users-status-menu-footer">

                        <button
                            type="button"
                            id="statusSelectAll"
                            class="users-status-action select"
                        >
                            Pilih Semua
                        </button>

                        <button
                            type="button"
                            id="statusClearAll"
                            class="users-status-action clear"
                        >
                            Batalkan
                        </button>

                    </div>

                </div>

            </div>


            {{-- CARI --}}

            <button
                type="submit"
                class="h-11 shrink-0 rounded-xl bg-slate-900 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:px-5 sm:text-sm"
            >
                Cari
            </button>


            {{-- RESET --}}

            @if($hasFilters)

                <a
                    href="{{ route('users.index') }}"
                    class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl border-2 border-slate-300 bg-slate-100 px-3.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-slate-400 hover:bg-slate-200 sm:px-4 sm:text-sm"
                >
                    Reset
                </a>

            @endif

        </div>

    </form>

</div>


{{-- =========================================================
     TABLE
========================================================== --}}

<div class="users-table-wrapper">

    <div class="users-table-scroll">

        <table class="users-table">

            <thead>

                <tr>

                    <th class="user-column">
                        Pengguna
                    </th>

                    <th>
                        Role
                    </th>

                    <th>
                        Jabatan
                    </th>

                    <th>
                        Status
                    </th>

                    <th class="text-center action-column">
                        Aksi
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($users as $user)

                    @php
                        /*
                        |--------------------------------------------------------------------------
                        | NORMALISASI ROLE
                        |--------------------------------------------------------------------------
                        */

                        $userRole = strtolower(
                            trim(
                                (string) (
                                    $user->role ?? ''
                                )
                            )
                        );

                        if ($userRole === 'staf') {
                            $userRole = 'staff';
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | NAMA / AVATAR
                        |--------------------------------------------------------------------------
                        */

                        $userName = trim(
                            (string) (
                                $user->name ?? ''
                            )
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


                        /*
                        |--------------------------------------------------------------------------
                        | STATUS AKUN
                        |--------------------------------------------------------------------------
                        |
                        | Satu-satunya sumber status adalah
                        | users.is_active.
                        |
                        */

                        $isActive =
                            (bool) (
                                $user->is_active ?? false
                            );
                    @endphp


                    <tr>

                        {{-- PENGGUNA --}}

                        <td>

                            <div class="flex items-center gap-2.5 sm:gap-3">

                                <div class="user-avatar">
                                    {{ $avatarText }}
                                </div>

                                <div class="min-w-0">

                                    <div class="user-name">
                                        {{ $user->name ?? '-' }}
                                    </div>

                                    <div class="user-email">
                                        {{ $user->email ?? '-' }}
                                    </div>

                                </div>

                            </div>

                        </td>


                        {{-- ROLE --}}

                        <td class="whitespace-nowrap">

                            @if($userRole === 'admin')

                                <span class="user-role admin">
                                    Admin
                                </span>

                            @elseif($userRole === 'pimpinan')

                                <span class="user-role pimpinan">
                                    Pimpinan
                                </span>

                            @elseif($userRole === 'staff')

                                <span class="user-role staff">
                                    Staf
                                </span>

                            @else

                                <span class="user-role default">
                                    {{ ucfirst($userRole ?: 'User') }}
                                </span>

                            @endif

                        </td>


                        {{-- JABATAN --}}

                        <td class="whitespace-nowrap user-position">
                            {{ $user->jabatan ?? '-' }}
                        </td>


                        {{-- STATUS --}}

                        <td class="whitespace-nowrap">

                            @if($isActive)

                                <span class="user-status active">

                                    <span class="status-dot"></span>

                                    <span>
                                        Aktif
                                    </span>

                                </span>

                            @else

                                <span class="user-status inactive">

                                    <span class="status-dot"></span>

                                    <span>
                                        Nonaktif
                                    </span>

                                </span>

                            @endif

                        </td>


                        {{-- AKSI --}}

                        <td class="whitespace-nowrap text-center">

                            <div class="user-actions">

                                {{-- SHOW / DETAIL --}}

                                <a
                                    href="{{ route('users.show', $user->id) }}"
                                    title="Lihat Detail"
                                    class="user-action view"
                                    aria-label="Lihat detail pengguna"
                                >

                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        viewBox="0 0 24 24"
                                    >

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M2.5 12s3.5-6 9.5-6s9.5 6 9.5 6s-3.5 6-9.5 6s-9.5-6-9.5-6z"
                                        />

                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="3"
                                        />

                                    </svg>

                                </a>


                                {{-- EDIT --}}

                                <a
                                    href="{{ route('users.edit', $user->id) }}"
                                    title="Edit User"
                                    class="user-action edit"
                                    aria-label="Edit user"
                                >

                                    <svg
                                        class="h-4 w-4"
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

                                </a>


                                {{-- DELETE --}}

                                @if(
                                    (int) auth()->id() !==
                                    (int) $user->id
                                )

                                    <form
                                        method="POST"
                                        action="{{ route('users.destroy', $user->id) }}"
                                        class="delete-form inline"
                                    >

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="button"
                                            title="Hapus User"
                                            aria-label="Hapus user"
                                            class="user-action delete delete-btn"
                                        >

                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                                viewBox="0 0 24 24"
                                            >

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M4 7h16"
                                                />

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M10 11v6M14 11v6"
                                                />

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M6 7l1 13h10l1-13"
                                                />

                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M9 7V4h6v3"
                                                />

                                            </svg>

                                        </button>

                                    </form>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="5"
                            class="user-empty"
                        >

                            <div class="flex flex-col items-center justify-center gap-2">

                                <div class="user-empty-icon">

                                    <svg
                                        class="h-7 w-7"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.6"
                                        viewBox="0 0 24 24"
                                    >

                                        <circle
                                            cx="9"
                                            cy="8"
                                            r="3"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            d="M3 20a6 6 0 0112 0"
                                        />

                                        <circle
                                            cx="17"
                                            cy="8"
                                            r="2"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            d="M15 19a5 5 0 014-3.87"
                                        />

                                    </svg>

                                </div>

                                <p class="text-xs font-medium text-slate-400 sm:text-sm">
                                    Tidak ada data pengguna yang ditemukan.
                                </p>

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- PAGINATION --}}

    @if(
        method_exists($users, 'hasPages') &&
        $users->hasPages()
    )

        <div class="users-pagination">

            {{
                $users
                    ->appends(
                        request()->query()
                    )
                    ->links()
            }}

        </div>

    @endif

</div>

</div>

{{-- =========================================================
STYLE
========================================================= --}}

@push('styles')

<style>
.users-filter-input{
    border:2px solid #94a3b8!important;
    background:#fff!important;
    box-shadow:0 1px 2px rgba(15,23,42,.04)
}

.users-filter-input:hover{
    border-color:#64748b!important
}

.users-filter-input:focus{
    border-color:#2563eb!important;
    background:#fff!important;
    box-shadow:0 0 0 3px rgba(37,99,235,.12)!important;
    outline:none
}

.users-role-dropdown,
.users-status-dropdown{
    position:relative;
    min-width:0
}

.users-role-trigger,
.users-status-trigger{
    display:flex;
    width:100%;
    min-height:52px;
    align-items:center;
    gap:8px;
    padding:7px 10px;
    border:2px solid #94a3b8;
    border-radius:12px;
    background:#fff;
    color:#334155;
    text-align:left;
    cursor:pointer;
    transition:
        border-color .15s ease,
        background-color .15s ease,
        box-shadow .15s ease
}

.users-role-trigger:hover,
.users-status-trigger:hover{
    border-color:#64748b;
    background:#f8fafc
}

.users-role-trigger.is-open,
.users-role-trigger:focus,
.users-status-trigger.is-open,
.users-status-trigger:focus{
    outline:none;
    border-color:#2563eb;
    background:#f8fbff;
    box-shadow:0 0 0 3px rgba(37,99,235,.12)
}

.users-role-trigger.is-active,
.users-status-trigger.is-active{
    border-color:#2563eb;
    background:#f8fbff
}

.users-role-trigger-icon,
.users-status-trigger-icon{
    display:flex;
    width:31px;
    height:31px;
    flex-shrink:0;
    align-items:center;
    justify-content:center;
    border-radius:8px;
    background:#eff6ff;
    color:#2563eb
}

.users-role-trigger-content,
.users-status-trigger-content{
    min-width:0;
    flex:1
}

.users-role-trigger-title,
.users-status-trigger-title{
    display:block;
    color:#334155;
    font-size:11px;
    font-weight:800;
    line-height:1.2
}

.users-role-trigger-subtitle,
.users-status-trigger-subtitle{
    display:block;
    margin-top:2px;
    overflow:hidden;
    color:#94a3b8;
    font-size:8px;
    line-height:1.2;
    text-overflow:ellipsis;
    white-space:nowrap
}

.users-role-count,
.users-status-count{
    display:inline-flex;
    min-width:58px;
    min-height:22px;
    align-items:center;
    justify-content:center;
    flex-shrink:0;
    padding:0 7px;
    border:1px solid #bfdbfe;
    border-radius:999px;
    background:#eff6ff;
    color:#2563eb;
    font-size:8px;
    font-weight:800;
    white-space:nowrap
}

.users-role-chevron,
.users-status-chevron{
    color:#64748b;
    transition:transform .2s ease
}

.users-role-trigger.is-open .users-role-chevron,
.users-status-trigger.is-open .users-status-chevron{
    transform:rotate(180deg)
}

.users-role-menu,
.users-status-menu{
    position:absolute;
    top:calc(100% + 6px);
    right:0;
    left:0;
    z-index:9999;
    overflow:hidden;
    border:2px solid #64748b;
    border-radius:12px;
    background:#fff;
    box-shadow:
        0 20px 50px rgba(15,23,42,.14),
        0 5px 15px rgba(15,23,42,.08)
}

.users-role-menu.hidden,
.users-status-menu.hidden{
    display:none!important
}

.users-role-menu-header,
.users-status-menu-header{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:8px;
    padding:10px 11px;
    border-bottom:2px solid #cbd5e1;
    background:#f8fafc
}

.users-role-menu-title,
.users-status-menu-title{
    display:block;
    color:#334155;
    font-size:11px;
    font-weight:800
}

.users-role-menu-description,
.users-status-menu-description{
    display:block;
    margin-top:2px;
    color:#94a3b8;
    font-size:8px
}

.users-role-close,
.users-status-close{
    display:inline-flex;
    width:27px;
    height:27px;
    align-items:center;
    justify-content:center;
    border:0;
    border-radius:7px;
    background:#fff;
    color:#64748b;
    font-size:17px;
    cursor:pointer
}

.users-role-close:hover,
.users-status-close:hover{
    background:#f1f5f9;
    color:#ef4444
}

.users-role-options,
.users-status-options{
    display:flex;
    flex-direction:column;
    gap:6px;
    padding:9px
}

.users-role-option,
.users-status-option{
    display:flex;
    min-height:48px;
    align-items:center;
    gap:8px;
    padding:7px 8px;
    border:1.5px solid #94a3b8;
    border-radius:9px;
    background:#fff;
    cursor:pointer;
    transition:
        border-color .15s ease,
        background-color .15s ease
}

.users-role-option:hover,
.users-role-option.is-selected,
.users-status-option:hover,
.users-status-option.is-selected{
    border-color:#2563eb;
    background:#eff6ff
}

.users-role-option input,
.users-status-option input{
    width:15px;
    height:15px;
    flex-shrink:0;
    margin:0;
    accent-color:#2563eb;
    cursor:pointer
}

.users-role-option-icon,
.users-status-option-icon{
    display:flex;
    width:28px;
    height:28px;
    flex-shrink:0;
    align-items:center;
    justify-content:center;
    border-radius:7px;
    font-size:10px;
    font-weight:850
}

.users-role-option-icon.admin{
    background:#faf5ff;
    color:#7e22ce
}

.users-role-option-icon.pimpinan{
    background:#fffbeb;
    color:#b45309
}

.users-role-option-icon.staff{
    background:#eff6ff;
    color:#1d4ed8
}

.users-status-option-icon.active{
    background:#ecfdf5;
    color:#047857
}

.users-status-option-icon.inactive{
    background:#fff1f2;
    color:#be123c
}

.users-role-option-text,
.users-status-option-text{
    min-width:0;
    display:block
}

.users-role-option-title,
.users-status-option-title{
    display:block;
    color:#334155;
    font-size:10px;
    font-weight:800
}

.users-role-option-description,
.users-status-option-description{
    display:block;
    margin-top:1px;
    color:#94a3b8;
    font-size:7.5px
}

.users-role-menu-footer,
.users-status-menu-footer{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:5px;
    padding:8px 10px;
    border-top:2px solid #cbd5e1;
    background:#f8fafc
}

.users-role-action,
.users-status-action{
    border:0;
    border-radius:7px;
    padding:5px 7px;
    font-size:8px;
    font-weight:800;
    cursor:pointer
}

.users-role-action.select,
.users-status-action.select{
    color:#2563eb
}

.users-role-action.select:hover,
.users-status-action.select:hover{
    background:#eff6ff
}

.users-role-action.clear,
.users-status-action.clear{
    color:#64748b
}

.users-role-action.clear:hover,
.users-status-action.clear:hover{
    background:#f1f5f9
}

.users-table-wrapper{
    overflow:hidden;
    border:2px solid #64748b;
    border-radius:14px;
    background:#fff;
    box-shadow:
        0 1px 3px rgba(15,23,42,.06),
        0 8px 24px rgba(15,23,42,.04)
}

.users-table-scroll{
    overflow-x:auto
}

.users-table{
    width:100%;
    min-width:850px;
    border-collapse:collapse;
    border-spacing:0;
    background:#fff
}

.users-table thead{
    background:#e2e8f0
}

.users-table thead tr{
    border-bottom:2px solid #475569
}

.users-table thead th{
    padding:12px 14px;
    border-right:1.5px solid #64748b;
    border-bottom:2px solid #475569;
    color:#334155;
    font-size:9px;
    font-weight:800;
    letter-spacing:.04em;
    line-height:1.3;
    text-align:left;
    text-transform:uppercase;
    vertical-align:middle
}

.users-table thead th:last-child{
    border-right:0
}

.users-table tbody tr{
    background:#fff;
    transition:background-color .15s ease
}

.users-table tbody tr:nth-child(even){
    background:#f8fafc
}

.users-table tbody tr:hover{
    background:#eff6ff
}

.users-table tbody td{
    padding:12px 14px;
    border-right:1px solid #94a3b8;
    border-bottom:1px solid #94a3b8;
    color:#475569;
    font-size:11px;
    line-height:1.4;
    vertical-align:middle
}

.users-table tbody td:last-child{
    border-right:0
}

.users-table tbody tr:last-child td{
    border-bottom:0
}

.user-column{
    min-width:280px
}

.action-column{
    width:125px
}

.user-avatar{
    display:flex;
    width:40px;
    height:40px;
    flex-shrink:0;
    align-items:center;
    justify-content:center;
    border:1px solid #bfdbfe;
    border-radius:11px;
    background:#eff6ff;
    color:#2563eb;
    font-size:12px;
    font-weight:800
}

.user-name{
    max-width:280px;
    overflow:hidden;
    color:#1e293b;
    font-size:12px;
    font-weight:800;
    line-height:1.4;
    text-overflow:ellipsis;
    white-space:nowrap
}

.user-email{
    max-width:280px;
    margin-top:2px;
    overflow:hidden;
    color:#94a3b8;
    font-size:10px;
    font-weight:500;
    line-height:1.4;
    text-overflow:ellipsis;
    white-space:nowrap
}

.user-position{
    color:#475569!important;
    font-weight:600
}

.user-role{
    display:inline-flex;
    min-height:25px;
    align-items:center;
    border-width:1px;
    border-radius:8px;
    padding:0 9px;
    font-size:10px;
    font-weight:800
}

.user-role.admin{
    border-color:#e9d5ff;
    background:#faf5ff;
    color:#7e22ce
}

.user-role.pimpinan{
    border-color:#fde68a;
    background:#fffbeb;
    color:#b45309
}

.user-role.staff{
    border-color:#bfdbfe;
    background:#eff6ff;
    color:#1d4ed8
}

.user-role.default{
    border-color:#cbd5e1;
    background:#f1f5f9;
    color:#475569
}

.user-status{
    display:inline-flex;
    align-items:center;
    gap:6px;
    min-height:26px;
    border-width:1px;
    border-radius:999px;
    padding:0 9px;
    font-size:10px;
    font-weight:800
}

.user-status.active{
    border-color:#a7f3d0;
    background:#ecfdf5;
    color:#047857
}

.user-status.inactive{
    border-color:#fecdd3;
    background:#fff1f2;
    color:#be123c
}

.status-dot{
    width:6px;
    height:6px;
    flex-shrink:0;
    border-radius:999px;
    background:currentColor
}

.user-actions{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:3px
}

.user-action{
    display:inline-flex;
    width:29px;
    height:29px;
    align-items:center;
    justify-content:center;
    border-radius:7px;
    color:#64748b;
    background:transparent;
    border:0;
    cursor:pointer;
    transition:
        background-color .15s ease,
        color .15s ease
}

.user-action.view:hover{
    background:#dbeafe;
    color:#2563eb
}

.user-action.edit:hover{
    background:#fef3c7;
    color:#d97706
}

.user-action.delete:hover{
    background:#ffe4e6;
    color:#e11d48
}

.user-empty{
    padding:42px 16px;
    text-align:center
}

.user-empty-icon{
    display:flex;
    width:48px;
    height:48px;
    align-items:center;
    justify-content:center;
    border-radius:15px;
    background:#f1f5f9;
    color:#cbd5e1
}

.users-pagination{
    border-top:2px solid #cbd5e1;
    background:#f8fafc;
    padding:12px 16px
}

@media(max-width:767px){

    .users-table{
        min-width:850px
    }

    .users-table thead th,
    .users-table tbody td{
        padding:10px 12px
    }

    .user-avatar{
        width:36px;
        height:36px;
        font-size:11px
    }

    .users-role-menu,
    .users-status-menu{
        position:fixed;
        top:50%;
        left:50%;
        right:auto;
        width:calc(100vw - 24px);
        max-width:430px;
        transform:translate(-50%,-50%)
    }
}
</style>

@endpush

{{-- =========================================================
SCRIPT
========================================================== --}}

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | HELPER DROPDOWN
    |--------------------------------------------------------------------------
    */

    function setupDropdown(config) {

        const trigger =
            document.getElementById(
                config.trigger
            );

        const menu =
            document.getElementById(
                config.menu
            );

        const closeButton =
            document.getElementById(
                config.close
            );

        const selectAllButton =
            document.getElementById(
                config.selectAll
            );

        const clearAllButton =
            document.getElementById(
                config.clearAll
            );

        const countElement =
            document.getElementById(
                config.count
            );

        const subtitleElement =
            document.getElementById(
                config.subtitle
            );

        const checkboxes =
            document.querySelectorAll(
                config.checkbox
            );

        if (
            !trigger ||
            !menu
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE STATE
        |--------------------------------------------------------------------------
        */

        function update() {

            const checked =
                Array.from(
                    checkboxes
                ).filter(
                    checkbox =>
                        checkbox.checked
                );

            const count =
                checked.length;


            checkboxes.forEach(
                function (checkbox) {

                    const option =
                        checkbox.closest(
                            config.option
                        );

                    if (!option) {
                        return;
                    }

                    option.classList.toggle(
                        'is-selected',
                        checkbox.checked
                    );
                }
            );


            if (countElement) {

                countElement.textContent =
                    count + ' dipilih';

            }


            if (subtitleElement) {

                if (count === 0) {

                    subtitleElement.textContent =
                        config.emptyText;

                } else {

                    const labels =
                        checked.map(
                            function (checkbox) {

                                const title =
                                    checkbox
                                        .closest(
                                            config.option
                                        )
                                        ?.querySelector(
                                            config.label
                                        );

                                return title
                                    ? title.textContent.trim()
                                    : checkbox.value;

                            }
                        );

                    subtitleElement.textContent =
                        labels.join(', ');
                }
            }


            trigger.classList.toggle(
                'is-active',
                count > 0
            );
        }


        /*
        |--------------------------------------------------------------------------
        | OPEN MENU
        |--------------------------------------------------------------------------
        */

        function openMenu() {

            document
                .querySelectorAll(
                    '.users-role-menu, .users-status-menu'
                )
                .forEach(
                    function (otherMenu) {

                        if (otherMenu !== menu) {

                            otherMenu.classList.add(
                                'hidden'
                            );

                        }

                    }
                );


            document
                .querySelectorAll(
                    '.users-role-trigger, .users-status-trigger'
                )
                .forEach(
                    function (otherTrigger) {

                        if (otherTrigger !== trigger) {

                            otherTrigger.classList.remove(
                                'is-open'
                            );

                            otherTrigger.setAttribute(
                                'aria-expanded',
                                'false'
                            );

                        }

                    }
                );


            menu.classList.remove(
                'hidden'
            );

            trigger.classList.add(
                'is-open'
            );

            trigger.setAttribute(
                'aria-expanded',
                'true'
            );

            update();
        }


        /*
        |--------------------------------------------------------------------------
        | CLOSE MENU
        |--------------------------------------------------------------------------
        */

        function closeMenu() {

            menu.classList.add(
                'hidden'
            );

            trigger.classList.remove(
                'is-open'
            );

            trigger.setAttribute(
                'aria-expanded',
                'false'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TRIGGER
        |--------------------------------------------------------------------------
        */

        trigger.addEventListener(
            'click',
            function (event) {

                event.preventDefault();
                event.stopPropagation();

                if (
                    menu.classList.contains(
                        'hidden'
                    )
                ) {

                    openMenu();

                } else {

                    closeMenu();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | CLOSE BUTTON
        |--------------------------------------------------------------------------
        */

        if (closeButton) {

            closeButton.addEventListener(
                'click',
                function (event) {

                    event.preventDefault();
                    event.stopPropagation();

                    closeMenu();
                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | MENU CLICK
        |--------------------------------------------------------------------------
        */

        menu.addEventListener(
            'click',
            function (event) {

                event.stopPropagation();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | CHECKBOX
        |--------------------------------------------------------------------------
        */

        checkboxes.forEach(
            function (checkbox) {

                checkbox.addEventListener(
                    'change',
                    update
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | SELECT ALL
        |--------------------------------------------------------------------------
        */

        if (selectAllButton) {

            selectAllButton.addEventListener(
                'click',
                function (event) {

                    event.preventDefault();
                    event.stopPropagation();

                    checkboxes.forEach(
                        function (checkbox) {
                            checkbox.checked = true;
                        }
                    );

                    update();
                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CLEAR ALL
        |--------------------------------------------------------------------------
        */

        if (clearAllButton) {

            clearAllButton.addEventListener(
                'click',
                function (event) {

                    event.preventDefault();
                    event.stopPropagation();

                    checkboxes.forEach(
                        function (checkbox) {
                            checkbox.checked = false;
                        }
                    );

                    update();
                }
            );

        }


        update();

        return {
            closeMenu: closeMenu,
            update: update
        };
    }


    /*
    |--------------------------------------------------------------------------
    | ROLE DROPDOWN
    |--------------------------------------------------------------------------
    */

    const roleDropdown =
        setupDropdown({
            trigger:
                'roleFilterTrigger',

            menu:
                'roleFilterMenu',

            close:
                'roleFilterClose',

            selectAll:
                'roleSelectAll',

            clearAll:
                'roleClearAll',

            count:
                'roleFilterCount',

            subtitle:
                'roleFilterSubtitle',

            checkbox:
                '.role-checkbox',

            option:
                '.users-role-option',

            label:
                '.users-role-option-title',

            emptyText:
                'Pilih satu atau beberapa role'
        });


    /*
    |--------------------------------------------------------------------------
    | STATUS DROPDOWN
    |--------------------------------------------------------------------------
    */

    const statusDropdown =
        setupDropdown({
            trigger:
                'statusFilterTrigger',

            menu:
                'statusFilterMenu',

            close:
                'statusFilterClose',

            selectAll:
                'statusSelectAll',

            clearAll:
                'statusClearAll',

            count:
                'statusFilterCount',

            subtitle:
                'statusFilterSubtitle',

            checkbox:
                '.status-checkbox',

            option:
                '.users-status-option',

            label:
                '.users-status-option-title',

            emptyText:
                'Pilih status akun'
        });


    /*
    |--------------------------------------------------------------------------
    | CLICK OUTSIDE
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const dropdown =
                event.target.closest(
                    '[data-role-dropdown], [data-status-dropdown]'
                );

            if (dropdown) {
                return;
            }

            if (roleDropdown) {
                roleDropdown.closeMenu();
            }

            if (statusDropdown) {
                statusDropdown.closeMenu();
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ESCAPE
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key !==
                'Escape'
            ) {
                return;
            }

            if (roleDropdown) {
                roleDropdown.closeMenu();
            }

            if (statusDropdown) {
                statusDropdown.closeMenu();
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | DELETE USER
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            '.delete-btn'
        )
        .forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function () {

                        const form =
                            this.closest(
                                '.delete-form'
                            );

                        if (!form) {
                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | FALLBACK CONFIRM
                        |--------------------------------------------------------------------------
                        */

                        if (
                            typeof window.Swal ===
                            'undefined'
                        ) {

                            if (
                                window.confirm(
                                    'Apakah Anda yakin ingin menghapus pengguna ini?'
                                )
                            ) {
                                form.submit();
                            }

                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | SWEETALERT
                        |--------------------------------------------------------------------------
                        */

                        window.Swal.fire({

                            title:
                                'Hapus pengguna?',

                            text:
                                'Data pengguna akan dihapus dari sistem.',

                            icon:
                                'warning',

                            showCancelButton:
                                true,

                            confirmButtonText:
                                'Ya, hapus',

                            cancelButtonText:
                                'Batal',

                            reverseButtons:
                                true,

                            buttonsStyling:
                                false,

                            customClass: {

                                confirmButton:
                                    'rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white mx-1',

                                cancelButton:
                                    'rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 mx-1'

                            }

                        }).then(
                            function (result) {

                                if (
                                    result.isConfirmed
                                ) {
                                    form.submit();
                                }

                            }
                        );

                    }
                );

            }
        );

});
</script>

@endpush

@endsection
