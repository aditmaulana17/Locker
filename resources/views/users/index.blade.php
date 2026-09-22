@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */

    $selectedRoles = request()->input('role', []);
    $selectedStatuses = request()->input('is_active', []);

    $selectedRoles = is_array($selectedRoles)
        ? $selectedRoles
        : [$selectedRoles];

    $selectedStatuses = is_array($selectedStatuses)
        ? $selectedStatuses
        : [$selectedStatuses];

    /*
    |--------------------------------------------------------------------------
    | NORMALISASI ROLE
    |--------------------------------------------------------------------------
    */

    $selectedRoles = collect($selectedRoles)
        ->map(function ($role) {
            $role = strtolower(trim((string) $role));

            if ($role === 'staf') {
                $role = 'staff';
            }

            return $role;
        })
        ->filter(fn ($role) => in_array($role, ['admin', 'pimpinan', 'staff']))
        ->values()
        ->toArray();

    /*
    |--------------------------------------------------------------------------
    | NORMALISASI STATUS
    |--------------------------------------------------------------------------
    */

    $selectedStatuses = collect($selectedStatuses)
        ->map(fn ($status) => (string) $status)
        ->filter(fn ($status) => in_array($status, ['1', '0']))
        ->values()
        ->toArray();

    $search = trim((string) request()->input('search', ''));

    $hasFilters =
        $search !== '' ||
        count($selectedRoles) > 0 ||
        count($selectedStatuses) > 0;

    /*
    |--------------------------------------------------------------------------
    | STATISTIK
    |--------------------------------------------------------------------------
    */

    $totalUsers = method_exists($users, 'total')
        ? $users->total()
        : $users->count();

    /*
    |--------------------------------------------------------------------------
    | LABEL FILTER
    |--------------------------------------------------------------------------
    */

    $roleLabels = [
        'admin' => 'Admin',
        'pimpinan' => 'Pimpinan',
        'staff' => 'Staf',
    ];

    $selectedRoleLabels = collect($selectedRoles)
        ->map(fn ($role) => $roleLabels[$role] ?? ucfirst($role))
        ->values();

    $statusLabels = [
        '1' => 'Aktif',
        '0' => 'Nonaktif',
    ];

    $selectedStatusLabels = collect($selectedStatuses)
        ->map(fn ($status) => $statusLabels[$status] ?? $status)
        ->values();
@endphp


{{-- =========================================================
     PAGE HEADER
========================================================= --}}

<div class="users-page">

    <div class="users-header">

        <div class="users-header-left">

            <div class="users-page-icon">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path
                        d="M16 21V19C16 16.7909 14.2091 15 12 15H6C3.79086 15 2 16.7909 2 19V21"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <circle
                        cx="9"
                        cy="7"
                        r="4"
                        stroke="currentColor"
                        stroke-width="1.8"
                    />
                    <path
                        d="M19 8V14"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M22 11H16"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>
            </div>

            <div>
                <h1>Manajemen Pengguna</h1>
                <p>
                    Kelola akun, role, jabatan, dan status pengguna sistem.
                </p>
            </div>

        </div>

        <a href="{{ route('users.create') }}" class="btn-add-user">

            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path
                    d="M12 5V19"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
                <path
                    d="M5 12H19"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />
            </svg>

            <span>Tambah Pengguna</span>

        </a>

    </div>


    {{-- =====================================================
         SUMMARY
    ====================================================== --}}

    <div class="users-summary">

        <div class="summary-card">

            <div class="summary-icon summary-icon-blue">
                <svg viewBox="0 0 24 24" fill="none">
                    <path
                        d="M16 21V19C16 16.7909 14.2091 15 12 15H6C3.79086 15 2 16.7909 2 19V21"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <circle
                        cx="9"
                        cy="7"
                        r="4"
                        stroke="currentColor"
                        stroke-width="1.8"
                    />
                    <path
                        d="M22 21V19C22 17.1362 20.7252 15.5701 19 15.126"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                    <path
                        d="M16 3.12891C17.7252 3.57295 19 5.13869 19 7.00001C19 8.86132 17.7252 10.4271 16 10.8711"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>
            </div>

            <div class="summary-content">
                <span>Total Pengguna</span>
                <strong>{{ $totalUsers }}</strong>
            </div>

        </div>


        <div class="summary-card">

            <div class="summary-icon summary-icon-green">
                <svg viewBox="0 0 24 24" fill="none">
                    <path
                        d="M20 6L9 17L4 12"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>

            <div class="summary-content">
                <span>Status</span>
                <strong>Aktif</strong>
                <small>Pengguna dapat masuk ke sistem</small>
            </div>

        </div>


        <div class="summary-card">

            <div class="summary-icon summary-icon-purple">
                <svg viewBox="0 0 24 24" fill="none">
                    <path
                        d="M12 2L14.8 8.1L21.5 8.8L16.5 13.3L17.9 20L12 16.5L6.1 20L7.5 13.3L2.5 8.8L9.2 8.1L12 2Z"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>

            <div class="summary-content">
                <span>Role</span>
                <strong>3</strong>
                <small>Admin, Pimpinan, dan Staf</small>
            </div>

        </div>

    </div>


    {{-- =====================================================
         MAIN CARD
    ====================================================== --}}

    <div class="users-card">

        {{-- =================================================
             CARD HEADER
        ================================================== --}}

        <div class="users-card-header">

            <div>

                <h2>Daftar Pengguna</h2>

                <p>
                    Daftar akun yang terdaftar pada sistem.
                </p>

            </div>

            @if($hasFilters)

                <div class="active-filter-indicator">

                    <span class="active-filter-dot"></span>

                    <span>
                        Filter aktif
                    </span>

                </div>

            @endif

        </div>


        {{-- =================================================
             FILTER
        ================================================== --}}

        <form
            action="{{ route('users.index') }}"
            method="GET"
            class="users-filter"
            id="usersFilterForm"
        >

            {{-- SEARCH --}}

            <div class="filter-search">

                <svg viewBox="0 0 24 24" fill="none">
                    <circle
                        cx="11"
                        cy="11"
                        r="7"
                        stroke="currentColor"
                        stroke-width="1.8"
                    />
                    <path
                        d="M20 20L16 16"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari nama, email, atau jabatan..."
                    autocomplete="off"
                >

                @if($search !== '')

                    <button
                        type="button"
                        class="search-clear"
                        onclick="clearSearch()"
                        aria-label="Hapus pencarian"
                    >
                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M6 6L18 18"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                            <path
                                d="M18 6L6 18"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                    </button>

                @endif

            </div>


            {{-- ROLE DROPDOWN --}}

            <div class="filter-dropdown" data-dropdown="role">

                <button
                    type="button"
                    class="filter-trigger"
                    aria-expanded="false"
                >

                    <span class="filter-trigger-left">

                        <svg viewBox="0 0 24 24" fill="none">
                            <path
                                d="M16 21V19C16 16.7909 14.2091 15 12 15H6C3.79086 15 2 16.7909 2 19V21"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            />
                            <circle
                                cx="9"
                                cy="7"
                                r="4"
                                stroke="currentColor"
                                stroke-width="1.7"
                            />
                        </svg>

                        <span class="filter-label">
                            @if(count($selectedRoleLabels) === 0)
                                Semua Role
                            @elseif(count($selectedRoleLabels) === 1)
                                {{ $selectedRoleLabels->first() }}
                            @else
                                {{ count($selectedRoleLabels) }} Role dipilih
                            @endif
                        </span>

                    </span>

                    <svg
                        class="filter-chevron"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M6 9L12 15L18 9"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                </button>


                <div class="filter-menu">

                    <div class="filter-menu-header">

                        <strong>Filter Role</strong>

                        <button
                            type="button"
                            class="dropdown-clear"
                            data-clear="role"
                        >
                            Hapus
                        </button>

                    </div>


                    <label class="filter-option filter-option-all">

                        <input
                            type="checkbox"
                            data-select-all="role"
                        >

                        <span class="custom-checkbox"></span>

                        <span>Semua Role</span>

                    </label>


                    <div class="filter-divider"></div>


                    @foreach([
                        'admin' => 'Admin',
                        'pimpinan' => 'Pimpinan',
                        'staff' => 'Staf',
                    ] as $roleValue => $roleLabel)

                        <label class="filter-option">

                            <input
                                type="checkbox"
                                name="role[]"
                                value="{{ $roleValue }}"
                                data-option="role"
                                {{ in_array($roleValue, $selectedRoles) ? 'checked' : '' }}
                            >

                            <span class="custom-checkbox"></span>

                            <span>{{ $roleLabel }}</span>

                        </label>

                    @endforeach

                </div>

            </div>


            {{-- STATUS DROPDOWN --}}

            <div class="filter-dropdown" data-dropdown="status">

                <button
                    type="button"
                    class="filter-trigger"
                    aria-expanded="false"
                >

                    <span class="filter-trigger-left">

                        <svg viewBox="0 0 24 24" fill="none">
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-width="1.7"
                            />
                            <path
                                d="M8 12L10.7 14.7L16 9.4"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>

                        <span class="filter-label">
                            @if(count($selectedStatusLabels) === 0)
                                Semua Status
                            @elseif(count($selectedStatusLabels) === 1)
                                {{ $selectedStatusLabels->first() }}
                            @else
                                {{ count($selectedStatusLabels) }} Status dipilih
                            @endif
                        </span>

                    </span>

                    <svg
                        class="filter-chevron"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M6 9L12 15L18 9"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                </button>


                <div class="filter-menu">

                    <div class="filter-menu-header">

                        <strong>Filter Status</strong>

                        <button
                            type="button"
                            class="dropdown-clear"
                            data-clear="status"
                        >
                            Hapus
                        </button>

                    </div>


                    <label class="filter-option filter-option-all">

                        <input
                            type="checkbox"
                            data-select-all="status"
                        >

                        <span class="custom-checkbox"></span>

                        <span>Semua Status</span>

                    </label>


                    <div class="filter-divider"></div>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="is_active[]"
                            value="1"
                            data-option="status"
                            {{ in_array('1', $selectedStatuses) ? 'checked' : '' }}
                        >

                        <span class="custom-checkbox"></span>

                        <span class="status-option">

                            <span class="status-dot status-dot-active"></span>

                            Aktif

                        </span>

                    </label>


                    <label class="filter-option">

                        <input
                            type="checkbox"
                            name="is_active[]"
                            value="0"
                            data-option="status"
                            {{ in_array('0', $selectedStatuses) ? 'checked' : '' }}
                        >

                        <span class="custom-checkbox"></span>

                        <span class="status-option">

                            <span class="status-dot status-dot-inactive"></span>

                            Nonaktif

                        </span>

                    </label>

                </div>

            </div>


            {{-- ACTION --}}

            <button
                type="submit"
                class="btn-search"
            >

                <svg viewBox="0 0 24 24" fill="none">
                    <circle
                        cx="11"
                        cy="11"
                        r="7"
                        stroke="currentColor"
                        stroke-width="1.8"
                    />
                    <path
                        d="M20 20L16 16"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                    />
                </svg>

                <span>Cari</span>

            </button>


            @if($hasFilters)

                <a
                    href="{{ route('users.index') }}"
                    class="btn-reset"
                >

                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M3 12C3 7.02944 7.02944 3 12 3C15.3137 3 18.2221 4.79267 19.7751 7.5"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        />
                        <path
                            d="M21 12C21 16.9706 16.9706 21 12 21C8.68629 21 5.77789 19.2073 4.22487 16.5"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        />
                        <path
                            d="M19.8 3.8V7.8H15.8"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M4.2 20.2V16.2H8.2"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>

                    <span>Reset</span>

                </a>

            @endif

        </form>


        {{-- =================================================
             ACTIVE FILTERS
        ================================================== --}}

        @if($hasFilters)

            <div class="active-filters">

                <span class="active-filters-title">
                    Filter:
                </span>


                @if($search !== '')

                    <span class="filter-chip">

                        <span>Pencarian: {{ $search }}</span>

                        <button
                            type="button"
                            onclick="removeSearchFilter()"
                            aria-label="Hapus filter pencarian"
                        >
                            ×
                        </button>

                    </span>

                @endif


                @foreach($selectedRoleLabels as $label)

                    <span class="filter-chip">
                        <span>{{ $label }}</span>
                    </span>

                @endforeach


                @foreach($selectedStatusLabels as $label)

                    <span class="filter-chip">
                        <span>{{ $label }}</span>
                    </span>

                @endforeach

            </div>

        @endif


        {{-- =================================================
             TABLE
        ================================================== --}}

        <div class="users-table-wrapper">

            <table class="users-table">

                <thead>

                    <tr>

                        <th class="column-user">
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

                        <th class="column-action">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($users as $user)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | ROLE USER
                            |--------------------------------------------------------------------------
                            */

                            $userRole = strtolower(
                                trim((string) ($user->role ?? ''))
                            );

                            if ($userRole === 'staf') {
                                $userRole = 'staff';
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | ROLE LABEL
                            |--------------------------------------------------------------------------
                            */

                            $roleLabel = match ($userRole) {
                                'admin' => 'Admin',
                                'pimpinan' => 'Pimpinan',
                                'staff' => 'Staf',
                                default => ucfirst($userRole ?: 'Tidak ada'),
                            };

                            /*
                            |--------------------------------------------------------------------------
                            | ROLE CLASS
                            |--------------------------------------------------------------------------
                            */

                            $roleClass = match ($userRole) {
                                'admin' => 'role-admin',
                                'pimpinan' => 'role-pimpinan',
                                'staff' => 'role-staff',
                                default => 'role-default',
                            };

                            /*
                            |--------------------------------------------------------------------------
                            | INITIAL
                            |--------------------------------------------------------------------------
                            */

                            $name = trim((string) ($user->name ?? ''));

                            $nameParts = preg_split(
                                '/\s+/',
                                $name,
                                -1,
                                PREG_SPLIT_NO_EMPTY
                            );

                            if (count($nameParts) >= 2) {

                                $initials =
                                    mb_substr($nameParts[0], 0, 1) .
                                    mb_substr(end($nameParts), 0, 1);

                            } elseif (count($nameParts) === 1) {

                                $initials =
                                    mb_substr($nameParts[0], 0, 2);

                            } else {

                                $initials = 'U';

                            }

                            $initials = strtoupper($initials);

                            /*
                            |--------------------------------------------------------------------------
                            | STATUS
                            |--------------------------------------------------------------------------
                            */

                            $isActive = (bool) $user->is_active;

                        @endphp


                        <tr>

                            {{-- USER --}}

                            <td>

                                <div class="user-cell">

                                    <div class="user-avatar">
                                        {{ $initials }}
                                    </div>


                                    <div class="user-info">

                                        <a
                                            href="{{ route('users.show', $user) }}"
                                            class="user-name"
                                        >
                                            {{ $user->name }}
                                        </a>

                                        <span class="user-email">
                                            {{ $user->email }}
                                        </span>

                                    </div>

                                </div>

                            </td>


                            {{-- ROLE --}}

                            <td>

                                <span class="role-badge {{ $roleClass }}">

                                    <span class="role-badge-dot"></span>

                                    {{ $roleLabel }}

                                </span>

                            </td>


                            {{-- JABATAN --}}

                            <td>

                                @if(!empty($user->jabatan))

                                    <span class="position-text">
                                        {{ $user->jabatan }}
                                    </span>

                                @else

                                    <span class="position-empty">
                                        —
                                    </span>

                                @endif

                            </td>


                            {{-- STATUS --}}

                            <td>

                                @if($isActive)

                                    <span class="status-badge status-active">

                                        <span class="status-badge-dot"></span>

                                        Aktif

                                    </span>

                                @else

                                    <span class="status-badge status-inactive">

                                        <span class="status-badge-dot"></span>

                                        Nonaktif

                                    </span>

                                @endif

                            </td>


                            {{-- ACTION --}}

                            <td>

                                <div class="user-actions">

                                    {{-- DETAIL --}}

                                    <a
                                        href="{{ route('users.show', $user) }}"
                                        class="action-button action-view"
                                        title="Lihat detail"
                                    >

                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path
                                                d="M2.5 12C3.8 7.8 7.5 5 12 5C16.5 5 20.2 7.8 21.5 12C20.2 16.2 16.5 19 12 19C7.5 19 3.8 16.2 2.5 12Z"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                            />
                                            <circle
                                                cx="12"
                                                cy="12"
                                                r="3"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                            />
                                        </svg>

                                        <span>Detail</span>

                                    </a>


                                    {{-- EDIT --}}

                                    <a
                                        href="{{ route('users.edit', $user) }}"
                                        class="action-button action-edit"
                                        title="Edit pengguna"
                                    >

                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path
                                                d="M12 20H21"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                                stroke-linecap="round"
                                            />
                                            <path
                                                d="M16.5 3.5C17.3284 2.67157 18.6716 2.67157 19.5 3.5C20.3284 4.32843 20.3284 5.67157 19.5 6.5L8 18L3 19L4 14L16.5 3.5Z"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                            />
                                        </svg>

                                        <span>Edit</span>

                                    </a>


                                    {{-- DELETE --}}

                                    @if(auth()->id() !== $user->id)

                                        <form
                                            action="{{ route('users.destroy', $user) }}"
                                            method="POST"
                                            class="delete-user-form"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="action-button action-delete"
                                                title="Hapus pengguna"
                                            >

                                                <svg viewBox="0 0 24 24" fill="none">
                                                    <path
                                                        d="M4 7H20"
                                                        stroke="currentColor"
                                                        stroke-width="1.7"
                                                        stroke-linecap="round"
                                                    />
                                                    <path
                                                        d="M10 11V17"
                                                        stroke="currentColor"
                                                        stroke-width="1.7"
                                                        stroke-linecap="round"
                                                    />
                                                    <path
                                                        d="M14 11V17"
                                                        stroke="currentColor"
                                                        stroke-width="1.7"
                                                        stroke-linecap="round"
                                                    />
                                                    <path
                                                        d="M6 7L7 20H17L18 7"
                                                        stroke="currentColor"
                                                        stroke-width="1.7"
                                                        stroke-linejoin="round"
                                                    />
                                                    <path
                                                        d="M9 7V4H15V7"
                                                        stroke="currentColor"
                                                        stroke-width="1.7"
                                                        stroke-linejoin="round"
                                                    />
                                                </svg>

                                                <span>Hapus</span>

                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="5">

                                <div class="empty-state">

                                    <div class="empty-icon">

                                        <svg viewBox="0 0 24 24" fill="none">
                                            <path
                                                d="M16 21V19C16 16.7909 14.2091 15 12 15H6C3.79086 15 2 16.7909 2 19V21"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                                stroke-linecap="round"
                                            />
                                            <circle
                                                cx="9"
                                                cy="7"
                                                r="4"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                            />
                                            <path
                                                d="M19 8V14"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                                stroke-linecap="round"
                                            />
                                            <path
                                                d="M22 11H16"
                                                stroke="currentColor"
                                                stroke-width="1.7"
                                                stroke-linecap="round"
                                            />
                                        </svg>

                                    </div>


                                    @if($hasFilters)

                                        <h3>Pengguna tidak ditemukan</h3>

                                        <p>
                                            Tidak ada pengguna yang sesuai
                                            dengan filter pencarian.
                                        </p>

                                        <a
                                            href="{{ route('users.index') }}"
                                            class="empty-action"
                                        >
                                            Reset Filter
                                        </a>

                                    @else

                                        <h3>Belum ada pengguna</h3>

                                        <p>
                                            Belum ada akun pengguna yang
                                            terdaftar pada sistem.
                                        </p>

                                        <a
                                            href="{{ route('users.create') }}"
                                            class="empty-action"
                                        >
                                            Tambah Pengguna
                                        </a>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- =================================================
             PAGINATION
        ================================================== --}}

        @if(method_exists($users, 'hasPages') && $users->hasPages())

            <div class="users-pagination">

                <div class="pagination-info">

                    Menampilkan

                    <strong>
                        {{ $users->firstItem() ?? 0 }}
                    </strong>

                    sampai

                    <strong>
                        {{ $users->lastItem() ?? 0 }}
                    </strong>

                    dari

                    <strong>
                        {{ $users->total() }}
                    </strong>

                    pengguna

                </div>


                <div class="pagination-links">

                    {{ $users->appends(request()->query())->links() }}

                </div>

            </div>

        @endif

    </div>

</div>


{{-- =========================================================
     STYLE
========================================================= --}}

@push('styles')

<style>

    /* =====================================================
       BASE
    ====================================================== */

    .users-page {
        width: 100%;
        max-width: 1440px;
        margin: 0 auto;
        padding: 24px;
        color: #172033;
    }


    /* =====================================================
       HEADER
    ====================================================== */

    .users-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .users-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .users-page-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        background: #eef4ff;
        color: #2563eb;
        flex: 0 0 auto;
    }

    .users-page-icon svg {
        width: 25px;
        height: 25px;
    }

    .users-header h1 {
        margin: 0;
        font-size: 24px;
        line-height: 1.25;
        font-weight: 750;
        letter-spacing: -0.025em;
        color: #111827;
    }

    .users-header p {
        margin: 5px 0 0;
        color: #667085;
        font-size: 14px;
    }


    /* =====================================================
       ADD BUTTON
    ====================================================== */

    .btn-add-user {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 16px;
        border-radius: 9px;
        background: #2563eb;
        color: #fff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 650;
        border: 1px solid #2563eb;
        transition: .18s ease;
        white-space: nowrap;
    }

    .btn-add-user:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
        color: #fff;
        transform: translateY(-1px);
    }

    .btn-add-user svg {
        width: 18px;
        height: 18px;
    }


    /* =====================================================
       SUMMARY
    ====================================================== */

    .users-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .summary-card {
        display: flex;
        align-items: center;
        gap: 14px;
        min-height: 92px;
        padding: 16px 18px;
        background: #fff;
        border: 1px solid #e6eaf0;
        border-radius: 13px;
        box-shadow: 0 2px 7px rgba(16, 24, 40, .025);
    }

    .summary-icon {
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        flex: 0 0 auto;
    }

    .summary-icon svg {
        width: 22px;
        height: 22px;
    }

    .summary-icon-blue {
        background: #eff6ff;
        color: #2563eb;
    }

    .summary-icon-green {
        background: #ecfdf3;
        color: #16a34a;
    }

    .summary-icon-purple {
        background: #f5f3ff;
        color: #7c3aed;
    }

    .summary-content {
        min-width: 0;
    }

    .summary-content span {
        display: block;
        font-size: 12px;
        color: #667085;
        margin-bottom: 3px;
    }

    .summary-content strong {
        display: block;
        font-size: 20px;
        line-height: 1.2;
        font-weight: 750;
        color: #111827;
    }

    .summary-content small {
        display: block;
        margin-top: 3px;
        color: #98a2b3;
        font-size: 11px;
    }


    /* =====================================================
       MAIN CARD
    ====================================================== */

    .users-card {
        overflow: visible;
        background: #fff;
        border: 1px solid #e6eaf0;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(16, 24, 40, .025);
    }

    .users-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 20px 22px 17px;
        border-bottom: 1px solid #edf0f4;
    }

    .users-card-header h2 {
        margin: 0;
        font-size: 17px;
        font-weight: 720;
        color: #111827;
    }

    .users-card-header p {
        margin: 4px 0 0;
        color: #667085;
        font-size: 13px;
    }

    .active-filter-indicator {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 6px 10px;
        border-radius: 999px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 12px;
        font-weight: 600;
    }

    .active-filter-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #2563eb;
    }


    /* =====================================================
       FILTER
    ====================================================== */

    .users-filter {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 15px 22px;
        background: #fafbfc;
        border-bottom: 1px solid #edf0f4;
    }

    .filter-search {
        position: relative;
        display: flex;
        align-items: center;
        min-width: 280px;
        flex: 1;
        max-width: 420px;
    }

    .filter-search > svg {
        position: absolute;
        left: 12px;
        width: 18px;
        height: 18px;
        color: #98a2b3;
        pointer-events: none;
    }

    .filter-search input {
        width: 100%;
        height: 40px;
        padding: 0 38px 0 38px;
        border: 1px solid #dfe3e8;
        border-radius: 8px;
        background: #fff;
        color: #101828;
        outline: none;
        font-size: 13px;
        transition: .15s ease;
    }

    .filter-search input::placeholder {
        color: #98a2b3;
    }

    .filter-search input:focus {
        border-color: #84a9ff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }

    .search-clear {
        position: absolute;
        right: 8px;
        width: 25px;
        height: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 6px;
        background: transparent;
        color: #98a2b3;
        cursor: pointer;
    }

    .search-clear:hover {
        background: #f2f4f7;
        color: #344054;
    }

    .search-clear svg {
        width: 15px;
        height: 15px;
    }


    /* =====================================================
       DROPDOWN
    ====================================================== */

    .filter-dropdown {
        position: relative;
    }

    .filter-trigger {
        height: 40px;
        min-width: 155px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 0 11px;
        border: 1px solid #dfe3e8;
        border-radius: 8px;
        background: #fff;
        color: #344054;
        cursor: pointer;
        font-size: 13px;
        transition: .15s ease;
    }

    .filter-trigger:hover,
    .filter-dropdown.is-open .filter-trigger {
        border-color: #b8c4d6;
        background: #fff;
    }

    .filter-trigger-left {
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .filter-trigger-left > svg {
        width: 17px;
        height: 17px;
        color: #667085;
        flex: 0 0 auto;
    }

    .filter-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .filter-chevron {
        width: 15px;
        height: 15px;
        color: #667085;
        transition: transform .18s ease;
    }

    .filter-dropdown.is-open .filter-chevron {
        transform: rotate(180deg);
    }

    .filter-menu {
        position: absolute;
        top: calc(100% + 7px);
        left: 0;
        z-index: 100;
        width: 230px;
        padding: 8px;
        background: #fff;
        border: 1px solid #e4e7ec;
        border-radius: 10px;
        box-shadow:
            0 10px 30px rgba(16, 24, 40, .10),
            0 2px 7px rgba(16, 24, 40, .04);
        opacity: 0;
        visibility: hidden;
        transform: translateY(-5px);
        transition: .15s ease;
    }

    .filter-dropdown.is-open .filter-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .filter-menu-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 7px 9px;
    }

    .filter-menu-header strong {
        color: #344054;
        font-size: 12px;
        font-weight: 700;
    }

    .dropdown-clear {
        padding: 0;
        border: 0;
        background: none;
        color: #2563eb;
        cursor: pointer;
        font-size: 11px;
        font-weight: 600;
    }

    .dropdown-clear:hover {
        text-decoration: underline;
    }

    .filter-divider {
        height: 1px;
        margin: 4px 0 6px;
        background: #f0f2f5;
    }

    .filter-option {
        position: relative;
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 36px;
        padding: 6px 8px;
        border-radius: 7px;
        color: #344054;
        cursor: pointer;
        font-size: 13px;
    }

    .filter-option:hover {
        background: #f8fafc;
    }

    .filter-option input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .custom-checkbox {
        width: 17px;
        height: 17px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #cfd4dc;
        border-radius: 5px;
        background: #fff;
        flex: 0 0 auto;
        transition: .15s ease;
    }

    .filter-option input:checked + .custom-checkbox {
        background: #2563eb;
        border-color: #2563eb;
    }

    .filter-option input:checked + .custom-checkbox::after {
        content: '';
        width: 8px;
        height: 4px;
        border-left: 1.7px solid #fff;
        border-bottom: 1.7px solid #fff;
        transform: rotate(-45deg) translateY(-1px);
    }

    .status-option {
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .status-dot-active {
        background: #16a34a;
    }

    .status-dot-inactive {
        background: #98a2b3;
    }


    /* =====================================================
       BUTTON FILTER
    ====================================================== */

    .btn-search,
    .btn-reset {
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 0 13px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 650;
        cursor: pointer;
        text-decoration: none;
        white-space: nowrap;
        transition: .15s ease;
    }

    .btn-search {
        border: 1px solid #2563eb;
        background: #2563eb;
        color: #fff;
    }

    .btn-search:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    .btn-search svg,
    .btn-reset svg {
        width: 16px;
        height: 16px;
    }

    .btn-reset {
        border: 1px solid #dfe3e8;
        background: #fff;
        color: #475467;
    }

    .btn-reset:hover {
        background: #f8fafc;
        color: #111827;
    }


    /* =====================================================
       ACTIVE FILTER
    ====================================================== */

    .active-filters {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 7px;
        padding: 10px 22px;
        border-bottom: 1px solid #edf0f4;
    }

    .active-filters-title {
        color: #667085;
        font-size: 11px;
        font-weight: 600;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 8px;
        border-radius: 6px;
        background: #f2f4f7;
        color: #475467;
        font-size: 11px;
        font-weight: 550;
    }

    .filter-chip button {
        width: 15px;
        height: 15px;
        padding: 0;
        border: 0;
        background: transparent;
        color: #98a2b3;
        cursor: pointer;
        font-size: 15px;
        line-height: 12px;
    }


    /* =====================================================
       TABLE
    ====================================================== */

    .users-table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .users-table thead th {
        height: 45px;
        padding: 0 22px;
        background: #fafbfc;
        border-bottom: 1px solid #edf0f4;
        color: #667085;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .035em;
        text-align: left;
        white-space: nowrap;
    }

    .users-table tbody td {
        padding: 13px 22px;
        border-bottom: 1px solid #f0f2f5;
        vertical-align: middle;
        font-size: 13px;
    }

    .users-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .users-table tbody tr {
        transition: background .12s ease;
    }

    .users-table tbody tr:hover {
        background: #fcfdff;
    }

    .column-user {
        width: 34%;
    }

    .column-action {
        width: 1%;
        white-space: nowrap;
    }


    /* =====================================================
       USER CELL
    ====================================================== */

    .user-cell {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 230px;
    }

    .user-avatar {
        width: 39px;
        height: 39px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 0 0 auto;
        border-radius: 10px;
        background: #eef4ff;
        color: #2563eb;
        font-size: 12px;
        font-weight: 750;
        letter-spacing: .02em;
    }

    .user-info {
        min-width: 0;
    }

    .user-name {
        display: block;
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #101828;
        font-weight: 650;
        font-size: 13px;
        text-decoration: none;
    }

    .user-name:hover {
        color: #2563eb;
    }

    .user-email {
        display: block;
        max-width: 300px;
        margin-top: 3px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #98a2b3;
        font-size: 11px;
    }


    /* =====================================================
       ROLE
    ====================================================== */

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 27px;
        padding: 0 9px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 650;
        white-space: nowrap;
    }

    .role-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .role-admin {
        background: #eef4ff;
        color: #2563eb;
    }

    .role-admin .role-badge-dot {
        background: #2563eb;
    }

    .role-pimpinan {
        background: #f5f3ff;
        color: #7c3aed;
    }

    .role-pimpinan .role-badge-dot {
        background: #7c3aed;
    }

    .role-staff {
        background: #ecfdf3;
        color: #15803d;
    }

    .role-staff .role-badge-dot {
        background: #16a34a;
    }

    .role-default {
        background: #f2f4f7;
        color: #667085;
    }

    .role-default .role-badge-dot {
        background: #98a2b3;
    }


    /* =====================================================
       POSITION
    ====================================================== */

    .position-text {
        color: #475467;
        font-size: 12px;
    }

    .position-empty {
        color: #98a2b3;
    }


    /* =====================================================
       STATUS
    ====================================================== */

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        min-height: 27px;
        padding: 0 9px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 650;
        white-space: nowrap;
    }

    .status-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .status-active {
        background: #ecfdf3;
        color: #15803d;
    }

    .status-active .status-badge-dot {
        background: #16a34a;
    }

    .status-inactive {
        background: #f2f4f7;
        color: #667085;
    }

    .status-inactive .status-badge-dot {
        background: #98a2b3;
    }


    /* =====================================================
       ACTION
    ====================================================== */

    .user-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 5px;
    }

    .action-button {
        min-height: 31px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
        padding: 0 8px;
        border: 1px solid transparent;
        border-radius: 7px;
        background: transparent;
        font-family: inherit;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: .15s ease;
        white-space: nowrap;
    }

    .action-button svg {
        width: 14px;
        height: 14px;
    }

    .action-view {
        color: #475467;
    }

    .action-view:hover {
        background: #f2f4f7;
        color: #111827;
    }

    .action-edit {
        color: #2563eb;
    }

    .action-edit:hover {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .action-delete {
        color: #dc2626;
    }

    .action-delete:hover {
        background: #fef2f2;
        color: #b91c1c;
    }

    .delete-user-form {
        margin: 0;
        padding: 0;
    }


    /* =====================================================
       EMPTY
    ====================================================== */

    .empty-state {
        display: flex;
        align-items: center;
        flex-direction: column;
        justify-content: center;
        padding: 60px 20px;
        text-align: center;
    }

    .empty-icon {
        width: 56px;
        height: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 13px;
        border-radius: 14px;
        background: #f2f4f7;
        color: #98a2b3;
    }

    .empty-icon svg {
        width: 27px;
        height: 27px;
    }

    .empty-state h3 {
        margin: 0;
        color: #344054;
        font-size: 15px;
        font-weight: 700;
    }

    .empty-state p {
        max-width: 400px;
        margin: 5px 0 15px;
        color: #98a2b3;
        font-size: 12px;
    }

    .empty-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 34px;
        padding: 0 12px;
        border: 1px solid #dfe3e8;
        border-radius: 7px;
        color: #344054;
        background: #fff;
        text-decoration: none;
        font-size: 12px;
        font-weight: 650;
    }

    .empty-action:hover {
        background: #f8fafc;
        color: #111827;
    }


    /* =====================================================
       PAGINATION
    ====================================================== */

    .users-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 14px 22px;
        border-top: 1px solid #edf0f4;
    }

    .pagination-info {
        color: #667085;
        font-size: 11px;
    }

    .pagination-info strong {
        color: #344054;
        font-weight: 650;
    }

    .pagination-links nav {
        display: flex;
        align-items: center;
    }

    .pagination-links nav > div:first-child {
        display: none;
    }

    .pagination-links nav > div:last-child {
        display: flex;
        align-items: center;
    }

    .pagination-links nav a,
    .pagination-links nav span {
        min-width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 7px;
        border: 1px solid #e4e7ec;
        border-left: 0;
        background: #fff;
        color: #475467;
        font-size: 11px;
        text-decoration: none;
    }

    .pagination-links nav > div:last-child > :first-child {
        border-left: 1px solid #e4e7ec;
        border-radius: 7px 0 0 7px;
    }

    .pagination-links nav > div:last-child > :last-child {
        border-radius: 0 7px 7px 0;
    }

    .pagination-links nav span[aria-current="page"] {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }


    /* =====================================================
       TABLET
    ====================================================== */

    @media (max-width: 1000px) {

        .users-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .users-summary .summary-card:last-child {
            grid-column: 1 / -1;
        }

        .users-filter {
            flex-wrap: wrap;
        }

        .filter-search {
            min-width: 100%;
            max-width: none;
        }

    }


    /* =====================================================
       MOBILE
    ====================================================== */

    @media (max-width: 700px) {

        .users-page {
            padding: 14px;
        }

        .users-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .users-header-left {
            width: 100%;
        }

        .users-header h1 {
            font-size: 20px;
        }

        .users-header p {
            font-size: 12px;
            line-height: 1.5;
        }

        .btn-add-user {
            width: 100%;
        }

        .users-summary {
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .users-summary .summary-card:last-child {
            grid-column: auto;
        }

        .summary-card {
            min-height: 78px;
        }

        .users-card-header {
            align-items: flex-start;
            flex-direction: column;
            padding: 17px;
        }

        .users-filter {
            align-items: stretch;
            flex-direction: column;
            padding: 13px 15px;
        }

        .filter-search {
            min-width: 100%;
        }

        .filter-dropdown,
        .filter-trigger {
            width: 100%;
        }

        .filter-trigger {
            min-width: 100%;
        }

        .filter-menu {
            position: fixed;
            top: 50%;
            left: 50%;
            width: calc(100vw - 36px);
            max-width: 360px;
            transform: translate(-50%, -47%);
            z-index: 1000;
        }

        .filter-dropdown.is-open .filter-menu {
            transform: translate(-50%, -50%);
        }

        .btn-search,
        .btn-reset {
            width: 100%;
        }

        .active-filters {
            padding: 10px 15px;
        }

        .users-table-wrapper {
            overflow-x: auto;
        }

        .users-table {
            min-width: 800px;
        }

        .users-pagination {
            align-items: flex-start;
            flex-direction: column;
            padding: 13px 15px;
        }

        .pagination-links {
            width: 100%;
            overflow-x: auto;
        }

    }


    /* =====================================================
       SMALL MOBILE
    ====================================================== */

    @media (max-width: 420px) {

        .users-page {
            padding: 10px;
        }

        .users-page-icon {
            width: 42px;
            height: 42px;
        }

        .users-page-icon svg {
            width: 22px;
            height: 22px;
        }

        .users-header h1 {
            font-size: 18px;
        }

        .users-card {
            border-radius: 11px;
        }

    }

</style>

@endpush


{{-- =========================================================
     SCRIPT
========================================================= --}}

@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | DROPDOWN
    |--------------------------------------------------------------------------
    */

    const dropdowns = document.querySelectorAll('[data-dropdown]');

    function closeAllDropdowns(except = null) {

        dropdowns.forEach(function (dropdown) {

            if (dropdown !== except) {

                dropdown.classList.remove('is-open');

                const trigger =
                    dropdown.querySelector('.filter-trigger');

                if (trigger) {
                    trigger.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }

            }

        });

    }


    dropdowns.forEach(function (dropdown) {

        const trigger =
            dropdown.querySelector('.filter-trigger');

        if (!trigger) {
            return;
        }

        trigger.addEventListener('click', function (event) {

            event.preventDefault();
            event.stopPropagation();

            const isOpen =
                dropdown.classList.contains('is-open');

            closeAllDropdowns(dropdown);

            dropdown.classList.toggle(
                'is-open',
                !isOpen
            );

            trigger.setAttribute(
                'aria-expanded',
                String(!isOpen)
            );

        });


        const type =
            dropdown.dataset.dropdown;

        const optionInputs =
            dropdown.querySelectorAll(
                '[data-option="' + type + '"]'
            );

        const selectAll =
            dropdown.querySelector(
                '[data-select-all="' + type + '"]'
            );

        const clearButton =
            dropdown.querySelector(
                '[data-clear="' + type + '"]'
            );


        /*
        |--------------------------------------------------------------------------
        | SELECT ALL STATE
        |--------------------------------------------------------------------------
        */

        function updateSelectAllState() {

            if (!selectAll || !optionInputs.length) {
                return;
            }

            const checkedCount =
                Array.from(optionInputs)
                    .filter(input => input.checked)
                    .length;

            selectAll.checked =
                checkedCount === optionInputs.length;

            selectAll.indeterminate =
                checkedCount > 0 &&
                checkedCount < optionInputs.length;

            updateLabel();

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE LABEL
        |--------------------------------------------------------------------------
        */

        function updateLabel() {

            const label =
                dropdown.querySelector('.filter-label');

            if (!label) {
                return;
            }

            const checked =
                Array.from(optionInputs)
                    .filter(input => input.checked);

            if (checked.length === 0) {

                label.textContent =
                    type === 'role'
                        ? 'Semua Role'
                        : 'Semua Status';

                return;
            }

            if (checked.length === 1) {

                const text =
                    checked[0]
                        .closest('.filter-option')
                        ?.querySelector('span:last-child')
                        ?.textContent
                        ?.trim();

                label.textContent =
                    text || '1 dipilih';

                return;
            }

            label.textContent =
                checked.length +
                (
                    type === 'role'
                        ? ' Role dipilih'
                        : ' Status dipilih'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | OPTION CHANGE
        |--------------------------------------------------------------------------
        */

        optionInputs.forEach(function (input) {

            input.addEventListener(
                'change',
                updateSelectAllState
            );

        });


        /*
        |--------------------------------------------------------------------------
        | SELECT ALL
        |--------------------------------------------------------------------------
        */

        if (selectAll) {

            selectAll.addEventListener(
                'change',
                function () {

                    optionInputs.forEach(
                        input => {
                            input.checked =
                                selectAll.checked;
                        }
                    );

                    updateLabel();

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CLEAR
        |--------------------------------------------------------------------------
        */

        if (clearButton) {

            clearButton.addEventListener(
                'click',
                function (event) {

                    event.preventDefault();
                    event.stopPropagation();

                    optionInputs.forEach(
                        input => {
                            input.checked = false;
                        }
                    );

                    if (selectAll) {
                        selectAll.checked = false;
                        selectAll.indeterminate = false;
                    }

                    updateLabel();

                }
            );

        }


        updateSelectAllState();

    });


    /*
    |--------------------------------------------------------------------------
    | OUTSIDE CLICK
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            if (
                !event.target.closest(
                    '.filter-dropdown'
                )
            ) {
                closeAllDropdowns();
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ESC
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Escape') {
                closeAllDropdowns();
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | DELETE CONFIRMATION
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.delete-user-form')
        .forEach(function (form) {

            form.addEventListener(
                'submit',
                function (event) {

                    event.preventDefault();

                    const submitDelete =
                        function () {
                            form.submit();
                        };


                    /*
                    |--------------------------------------------------------------------------
                    | SWEET ALERT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        typeof window.Swal !== 'undefined'
                    ) {

                        Swal.fire({

                            title: 'Hapus pengguna?',
                            text: 'Data pengguna yang dihapus tidak dapat dikembalikan.',
                            icon: 'warning',

                            showCancelButton: true,

                            confirmButtonText:
                                'Ya, hapus',

                            cancelButtonText:
                                'Batal',

                            reverseButtons: true,

                            buttonsStyling: true,

                            customClass: {

                                confirmButton:
                                    'swal-confirm-delete',

                                cancelButton:
                                    'swal-cancel-delete'

                            }

                        }).then(function (result) {

                            if (result.isConfirmed) {
                                submitDelete();
                            }

                        });

                    } else {

                        if (
                            window.confirm(
                                'Apakah Anda yakin ingin menghapus pengguna ini?'
                            )
                        ) {
                            submitDelete();
                        }

                    }

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | MOBILE DROPDOWN BACKDROP BEHAVIOR
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'touchstart',
        function (event) {

            const opened =
                document.querySelector(
                    '.filter-dropdown.is-open'
                );

            if (!opened) {
                return;
            }

            if (
                !event.target.closest(
                    '.filter-dropdown'
                )
            ) {
                closeAllDropdowns();
            }

        }
    );

});


/*
|--------------------------------------------------------------------------
| CLEAR SEARCH
|--------------------------------------------------------------------------
*/

function clearSearch() {

    const form =
        document.getElementById(
            'usersFilterForm'
        );

    if (!form) {
        return;
    }

    const input =
        form.querySelector(
            'input[name="search"]'
        );

    if (input) {
        input.value = '';
    }

    form.submit();

}


/*
|--------------------------------------------------------------------------
| REMOVE SEARCH FILTER
|--------------------------------------------------------------------------
*/

function removeSearchFilter() {

    const form =
        document.getElementById(
            'usersFilterForm'
        );

    if (!form) {
        return;
    }

    const input =
        form.querySelector(
            'input[name="search"]'
        );

    if (input) {
        input.value = '';
    }

    form.submit();

}

</script>

@endpush

@endsection