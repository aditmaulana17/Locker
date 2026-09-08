@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')

@php
    $roleFilter = strtolower(trim((string) request('role', '')));

    // Database: admin, pimpinan, staff
    if ($roleFilter === 'staf') {
        $roleFilter = 'staff';
    }
@endphp

<div class="space-y-4 pb-12 sm:space-y-6">

    {{-- ============================================================
         HEADER
    ============================================================ --}}

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">
                Manajemen Pengguna
            </h1>

            <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                Kelola akun pengguna sistem, hak akses, dan peranan jabatan.
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

            <span>Tambah Pengguna</span>
        </a>
    </div>

    {{-- ============================================================
         FILTER & PENCARIAN
    ============================================================ --}}

    <div class="rounded-2xl border border-slate-300 bg-white p-3 shadow-sm sm:p-4">
        <form
            method="GET"
            action="{{ route('users.index') }}"
            class="flex flex-col gap-2.5 sm:gap-3 md:flex-row md:items-center"
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
                    class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 pl-10 text-xs text-slate-800 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                >
            </div>

            {{-- ROLE + BUTTON --}}
            <div class="flex w-full items-center gap-2 md:w-auto">

                <div class="min-w-0 flex-1 md:w-48">
                    <select
                        name="role"
                        onchange="this.form.submit()"
                        class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 text-xs font-medium text-slate-700 outline-none transition-all focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                    >
                        <option value="">Semua Role</option>

                        <option
                            value="admin"
                            {{ $roleFilter === 'admin' ? 'selected' : '' }}
                        >
                            Admin
                        </option>

                        <option
                            value="pimpinan"
                            {{ $roleFilter === 'pimpinan' ? 'selected' : '' }}
                        >
                            Pimpinan
                        </option>

                        <option
                            value="staff"
                            {{ $roleFilter === 'staff' ? 'selected' : '' }}
                        >
                            Staf
                        </option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="h-11 shrink-0 rounded-xl bg-slate-900 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:px-5 sm:text-sm"
                >
                    Cari
                </button>

                @if(request('search') || request('role'))
                    <a
                        href="{{ route('users.index') }}"
                        class="inline-flex h-11 shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-slate-100 px-3.5 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-200 sm:px-4 sm:text-sm"
                    >
                        Reset
                    </a>
                @endif

            </div>
        </form>
    </div>

    {{-- ============================================================
         TABLE
    ============================================================ --}}

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
                            $userRole = strtolower(
                                trim((string) ($user->role ?? ''))
                            );

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

                            $isActive = (bool) (
                                $user->is_active
                                ?? (
                                    strtolower(
                                        (string) ($user->status ?? 'aktif')
                                    ) === 'aktif'
                                )
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

                                @elseif(in_array($userRole, ['staff', 'staf'], true))

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
                                        <span>Aktif</span>
                                    </span>

                                @else

                                    <span class="user-status inactive">
                                        <span class="status-dot"></span>
                                        <span>Nonaktif</span>
                                    </span>

                                @endif

                            </td>

                            {{-- AKSI --}}
                            <td class="whitespace-nowrap text-center">

                                <div class="user-actions">

                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('users.edit', $user->id) }}"
                                        title="Edit User"
                                        class="user-action edit"
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
                                    @if((int) auth()->id() !== (int) $user->id)

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
        @if(method_exists($users, 'hasPages') && $users->hasPages())
            <div class="users-pagination">
                {{ $users->appends(request()->query())->links() }}
            </div>
        @endif

    </div>

</div>

{{-- ================================================================
     STYLE TABLE
================================================================ --}}

@push('styles')
<style>
/* ==========================================================================
   USERS TABLE
   ========================================================================== */

.users-table-wrapper {
    overflow: hidden;
    border: 1px solid #94a3b8;
    border-radius: 16px;
    background: #fff;
    box-shadow:
        0 1px 3px rgba(15, 23, 42, .06),
        0 8px 24px rgba(15, 23, 42, .04);
}

.users-table-scroll {
    overflow-x: auto;
}

.users-table {
    width: 100%;
    min-width: 820px;
    border-collapse: collapse;
    border-spacing: 0;
    background: #fff;
}

.users-table thead {
    background: #f1f5f9;
}

.users-table thead th {
    padding: 13px 16px;
    border-right: 1px solid #cbd5e1;
    border-bottom: 2px solid #94a3b8;
    color: #475569;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .05em;
    line-height: 1.4;
    text-align: left;
    text-transform: uppercase;
    vertical-align: middle;
}

.users-table thead th:last-child {
    border-right: 0;
}

.users-table tbody tr {
    background: #fff;
    transition: background-color .15s ease;
}

.users-table tbody tr:nth-child(even) {
    background: #f8fafc;
}

.users-table tbody tr:hover {
    background: #eff6ff;
}

.users-table tbody td {
    padding: 14px 16px;
    border-right: 1px solid #cbd5e1;
    border-bottom: 1px solid #cbd5e1;
    color: #475569;
    font-size: 12px;
    line-height: 1.5;
    vertical-align: middle;
}

.users-table tbody td:last-child {
    border-right: 0;
}

.users-table tbody tr:last-child td {
    border-bottom: 0;
}

/* ==========================================================================
   COLUMN
   ========================================================================== */

.user-column {
    min-width: 280px;
}

.action-column {
    width: 110px;
}

/* ==========================================================================
   USER INFO
   ========================================================================== */

.user-avatar {
    display: flex;
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
    border: 1px solid #bfdbfe;
    border-radius: 12px;
    background: #eff6ff;
    color: #2563eb;
    font-size: 12px;
    font-weight: 800;
}

.user-name {
    overflow: hidden;
    max-width: 280px;
    color: #1e293b;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.4;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-email {
    overflow: hidden;
    max-width: 280px;
    margin-top: 2px;
    color: #94a3b8;
    font-size: 10px;
    font-weight: 500;
    line-height: 1.4;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.user-position {
    color: #475569 !important;
    font-weight: 600;
}

/* ==========================================================================
   ROLE BADGE
   ========================================================================== */

.user-role {
    display: inline-flex;
    align-items: center;
    min-height: 26px;
    border-width: 1px;
    border-radius: 8px;
    padding: 0 10px;
    font-size: 10px;
    font-weight: 800;
}

.user-role.admin {
    border-color: #e9d5ff;
    background: #faf5ff;
    color: #7e22ce;
}

.user-role.pimpinan {
    border-color: #fde68a;
    background: #fffbeb;
    color: #b45309;
}

.user-role.staff {
    border-color: #bfdbfe;
    background: #eff6ff;
    color: #1d4ed8;
}

.user-role.default {
    border-color: #cbd5e1;
    background: #f1f5f9;
    color: #475569;
}

/* ==========================================================================
   STATUS
   ========================================================================== */

.user-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    min-height: 27px;
    border-width: 1px;
    border-radius: 999px;
    padding: 0 10px;
    font-size: 10px;
    font-weight: 800;
}

.user-status.active {
    border-color: #a7f3d0;
    background: #ecfdf5;
    color: #047857;
}

.user-status.inactive {
    border-color: #fecdd3;
    background: #fff1f2;
    color: #be123c;
}

.status-dot {
    width: 6px;
    height: 6px;
    flex-shrink: 0;
    border-radius: 999px;
    background: currentColor;
}

/* ==========================================================================
   ACTION
   ========================================================================== */

.user-actions {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
}

.user-action {
    display: inline-flex;
    width: 31px;
    height: 31px;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    color: #64748b;
    transition:
        background-color .15s ease,
        color .15s ease;
}

.user-action.edit:hover {
    background: #fef3c7;
    color: #d97706;
}

.user-action.delete:hover {
    background: #ffe4e6;
    color: #e11d48;
}

/* ==========================================================================
   EMPTY
   ========================================================================== */

.user-empty {
    padding: 50px 16px;
    text-align: center;
}

.user-empty-icon {
    display: flex;
    width: 50px;
    height: 50px;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    background: #f1f5f9;
    color: #cbd5e1;
}

/* ==========================================================================
   PAGINATION
   ========================================================================== */

.users-pagination {
    border-top: 1px solid #cbd5e1;
    background: #f8fafc;
    padding: 13px 16px;
}

/* ==========================================================================
   RESPONSIVE
   ========================================================================== */

@media (max-width: 767px) {
    .users-table {
        min-width: 800px;
    }

    .users-table thead th,
    .users-table tbody td {
        padding: 12px 13px;
    }

    .user-avatar {
        width: 36px;
        height: 36px;
        font-size: 11px;
    }
}
</style>
@endpush

{{-- ================================================================
     SWEETALERT DELETE
================================================================ --}}

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const deleteButtons = document.querySelectorAll('.delete-btn');

    if (!deleteButtons.length) {
        return;
    }

    deleteButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const form = button.closest('.delete-form');

            if (!form) {
                return;
            }

            if (typeof Swal === 'undefined') {

                if (confirm('Apakah Anda yakin ingin menghapus pengguna ini?')) {
                    form.submit();
                }

                return;
            }

            Swal.fire({
                title: 'Hapus pengguna?',
                text: 'Data pengguna akan dihapus dari sistem.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    confirmButton:
                        'rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white mx-1',

                    cancelButton:
                        'rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 mx-1'
                }
            }).then(function (result) {

                if (result.isConfirmed) {
                    form.submit();
                }

            });

        });

    });

});
</script>
@endpush

@endsection