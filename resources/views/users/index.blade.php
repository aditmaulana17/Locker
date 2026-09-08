@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')

@php
    $roleFilter = strtolower(
        trim(
            (string) request('role', '')
        )
    );

    /*
     * Database menggunakan:
     * admin
     * pimpinan
     * staff
     */
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
            class="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-blue-500/20 transition-all duration-200 hover:bg-blue-700 sm:w-auto sm:text-sm"
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
    <div class="rounded-xl border border-slate-200/80 bg-white p-3 shadow-sm sm:rounded-2xl sm:p-4">

        <form
            method="GET"
            action="{{ route('users.index') }}"
            class="flex flex-col gap-2.5 md:flex-row md:items-center sm:gap-3"
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
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-10 pr-4 text-xs text-slate-800 placeholder-slate-400 transition-all duration-150 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 sm:py-2.5 sm:text-sm"
                >

            </div>


            {{-- ROLE + BUTTON --}}
            <div class="flex w-full items-center gap-2 md:w-auto">

                <div class="min-w-0 flex-1 md:w-48">

                    <select
                        name="role"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs font-medium text-slate-700 transition-all focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 sm:py-2.5 sm:text-sm"
                    >
                        <option value="">
                            Semua Role
                        </option>

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
                    class="shrink-0 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-slate-800 sm:px-5 sm:py-2.5 sm:text-sm"
                >
                    Cari
                </button>


                @if(request('search') || request('role'))

                    <a
                        href="{{ route('users.index') }}"
                        class="shrink-0 rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-center text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 sm:px-4 sm:py-2.5 sm:text-sm"
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
    <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm sm:rounded-2xl">

        <div class="overflow-x-auto">

            <table class="w-full min-w-[680px] border-collapse text-left">

                <thead>

                    <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[10px] font-bold uppercase tracking-wider text-slate-500 sm:text-[11px]">

                        <th class="px-4 py-3 sm:px-6 sm:py-3.5">
                            Pengguna
                        </th>

                        <th class="px-4 py-3 sm:px-6 sm:py-3.5">
                            Role
                        </th>

                        <th class="px-4 py-3 sm:px-6 sm:py-3.5">
                            Jabatan
                        </th>

                        <th class="px-4 py-3 sm:px-6 sm:py-3.5">
                            Status
                        </th>

                        <th class="px-4 py-3 text-right sm:px-6 sm:py-3.5">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">

                    @forelse($users as $user)

                        @php
                            $userRole = strtolower(
                                trim(
                                    (string) ($user->role ?? '')
                                )
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


                        <tr class="transition-colors duration-150 hover:bg-slate-50/60">

                            {{-- =================================================
                               PENGGUNA
                            ================================================= --}}
                            <td class="px-4 py-3 sm:px-6 sm:py-4">

                                <div class="flex items-center gap-2.5 sm:gap-3">

                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border border-blue-200/60 bg-blue-50 text-xs font-bold text-blue-600 shadow-sm sm:h-10 sm:w-10 sm:text-sm">
                                        {{ $avatarText }}
                                    </div>

                                    <div class="flex min-w-0 flex-col">

                                        <span class="truncate font-bold leading-snug text-slate-800">
                                            {{ $user->name ?? '-' }}
                                        </span>

                                        <span class="truncate text-[11px] font-medium text-slate-400 sm:text-xs">
                                            {{ $user->email ?? '-' }}
                                        </span>

                                    </div>

                                </div>

                            </td>


                            {{-- =================================================
                               ROLE
                            ================================================= --}}
                            <td class="whitespace-nowrap px-4 py-3 sm:px-6 sm:py-4">

                                @if($userRole === 'admin')

                                    <span class="inline-flex items-center rounded-lg border border-purple-200/60 bg-purple-50 px-2.5 py-1 text-[11px] font-semibold text-purple-700 sm:text-xs">
                                        Admin
                                    </span>

                                @elseif($userRole === 'pimpinan')

                                    <span class="inline-flex items-center rounded-lg border border-amber-200/60 bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 sm:text-xs">
                                        Pimpinan
                                    </span>

                                @elseif(in_array($userRole, ['staff', 'staf'], true))

                                    <span class="inline-flex items-center rounded-lg border border-blue-200/60 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 sm:text-xs">
                                        Staf
                                    </span>

                                @else

                                    <span class="inline-flex items-center rounded-lg border border-slate-200/80 bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 sm:text-xs">
                                        {{ ucfirst($userRole ?: 'User') }}
                                    </span>

                                @endif

                            </td>


                            {{-- =================================================
                               JABATAN
                            ================================================= --}}
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-slate-600 sm:px-6 sm:py-4">
                                {{ $user->jabatan ?? '-' }}
                            </td>


                            {{-- =================================================
                               STATUS
                            ================================================= --}}
                            <td class="whitespace-nowrap px-4 py-3 sm:px-6 sm:py-4">

                                @if($isActive)

                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200/60 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 sm:text-xs">

                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

                                        <span>Aktif</span>

                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-rose-200/60 bg-rose-50 px-2.5 py-1 text-[11px] font-semibold text-rose-700 sm:text-xs">

                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>

                                        <span>Nonaktif</span>

                                    </span>

                                @endif

                            </td>


                            {{-- =================================================
                               AKSI
                            ================================================= --}}
                            <td class="whitespace-nowrap px-4 py-3 text-right sm:px-6 sm:py-4">

                                <div class="flex items-center justify-end gap-1">

                                    {{-- EDIT --}}
                                    <a
                                        href="{{ route('users.edit', $user->id) }}"
                                        title="Edit User"
                                        class="rounded-xl p-1.5 text-slate-400 transition-all duration-150 hover:bg-amber-50 hover:text-amber-600 sm:p-2"
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
                                            class="delete-form inline-block"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="button"
                                                title="Hapus User"
                                                class="delete-btn rounded-xl p-1.5 text-slate-400 transition-all duration-150 hover:bg-rose-50 hover:text-rose-600 sm:p-2"
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
                                class="px-4 py-10 text-center text-slate-400 sm:px-6 sm:py-12"
                            >

                                <div class="flex flex-col items-center justify-center gap-2">

                                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-300">

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

                                    <p class="text-xs font-medium sm:text-sm">
                                        Tidak ada data pengguna yang ditemukan.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- ============================================================
           PAGINATION
        ============================================================ --}}
        @if(method_exists($users, 'hasPages') && $users->hasPages())

            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3 sm:px-6 sm:py-4">
                {{ $users->appends(request()->query())->links() }}
            </div>

        @endif

    </div>

</div>


{{-- ================================================================
   SWEETALERT DELETE
================================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons =
        document.querySelectorAll('.delete-btn');

    if (!deleteButtons.length) {
        return;
    }

    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const form =
                button.closest('.delete-form');

            if (!form) {
                return;
            }

            if (typeof Swal === 'undefined') {
                if (
                    confirm(
                        'Apakah Anda yakin ingin menghapus pengguna ini?'
                    )
                ) {
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

@endsection