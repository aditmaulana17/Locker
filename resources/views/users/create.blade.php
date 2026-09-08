@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')

@php
    $oldRole = old('role', '');

    /*
     * Normalisasi alias lama:
     * staf -> staff
     */
    if ($oldRole === 'staf') {
        $oldRole = 'staff';
    }

    $oldStatus = old('status', 'aktif');
@endphp

<div class="mx-auto max-w-4xl space-y-4 pb-12 sm:space-y-6">

    {{-- ============================================================
       HEADER
    ============================================================ --}}
    <div class="flex flex-col gap-3 rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:rounded-2xl sm:p-6">

        <div class="min-w-0">

            <div class="flex items-center gap-2">

                <span class="h-2.5 w-2.5 shrink-0 animate-pulse rounded-full bg-blue-600"></span>

                <h1 class="truncate text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">
                    Tambah Pengguna Baru
                </h1>

            </div>

            <p class="mt-1 text-xs leading-relaxed text-slate-500 sm:text-sm">
                Buat akun pengguna baru dan tentukan hak akses serta status akun.
            </p>

        </div>

        <a
            href="{{ route('users.index') }}"
            class="inline-flex w-full shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-200 sm:w-auto"
        >
            <svg
                class="mr-1.5 h-4 w-4 text-slate-500"
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

    </div>


    {{-- ============================================================
       FORM
    ============================================================ --}}
    <div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-sm sm:rounded-2xl">

        <form
            method="POST"
            action="{{ route('users.store') }}"
        >
            @csrf

            <div class="space-y-6 p-4 sm:p-8">

                {{-- ==================================================
                   INFORMASI PERSONAL
                ================================================== --}}
                <section>

                    <div class="mb-4 flex items-center gap-2">

                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
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
                                    r="3.5"
                                />

                                <path
                                    stroke-linecap="round"
                                    d="M5 20a7 7 0 0114 0"
                                />
                            </svg>
                        </div>

                        <h3 class="text-xs font-bold uppercase tracking-wider text-blue-600">
                            Informasi Personal
                        </h3>

                    </div>


                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5">

                        {{-- NAMA --}}
                        <div class="sm:col-span-2">

                            <label
                                for="name"
                                class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                            >
                                Nama Lengkap
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="text"
                                name="name"
                                id="name"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                autocomplete="name"
                                placeholder="Contoh: Budi Santoso, S.Kom"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 sm:py-2.5 sm:text-sm @error('name') border-rose-500 bg-rose-50/30 @enderror"
                            >

                            @error('name')
                                <p class="mt-1.5 text-xs font-medium text-rose-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- EMAIL --}}
                        <div>

                            <label
                                for="email"
                                class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                            >
                                Alamat Email
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                placeholder="budi@domain.com"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 sm:py-2.5 sm:text-sm @error('email') border-rose-500 bg-rose-50/30 @enderror"
                            >

                            @error('email')
                                <p class="mt-1.5 text-xs font-medium text-rose-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- JABATAN --}}
                        <div>

                            <label
                                for="jabatan"
                                class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                            >
                                Jabatan / Posisi
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="text"
                                name="jabatan"
                                id="jabatan"
                                value="{{ old('jabatan') }}"
                                required
                                placeholder="Contoh: Staf Agenda / Kepala Bagian"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 sm:py-2.5 sm:text-sm @error('jabatan') border-rose-500 bg-rose-50/30 @enderror"
                            >

                            @error('jabatan')
                                <p class="mt-1.5 text-xs font-medium text-rose-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </section>


                <hr class="border-slate-100">


                {{-- ==================================================
                   HAK AKSES & KEAMANAN
                ================================================== --}}
                <section>

                    <div class="mb-4 flex items-center gap-2">

                        <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                            <svg
                                class="h-4 w-4"
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

                        <h3 class="text-xs font-bold uppercase tracking-wider text-blue-600">
                            Hak Akses & Keamanan Akun
                        </h3>

                    </div>


                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5">

                        {{-- ROLE --}}
                        <div>

                            <label
                                for="role"
                                class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                            >
                                Role / Hak Akses
                                <span class="text-rose-500">*</span>
                            </label>

                            <select
                                name="role"
                                id="role"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs font-medium text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 sm:py-2.5 sm:text-sm"
                            >
                                <option value="" disabled {{ $oldRole === '' ? 'selected' : '' }}>
                                    Pilih hak akses
                                </option>

                                <option
                                    value="admin"
                                    {{ $oldRole === 'admin' ? 'selected' : '' }}
                                >
                                    Admin (Akses Penuh)
                                </option>

                                <option
                                    value="pimpinan"
                                    {{ $oldRole === 'pimpinan' ? 'selected' : '' }}
                                >
                                    Pimpinan (Akses Eksekutif)
                                </option>

                                <option
                                    value="staff"
                                    {{ $oldRole === 'staff' ? 'selected' : '' }}
                                >
                                    Staf (Akses Operator)
                                </option>
                            </select>

                            @error('role')
                                <p class="mt-1.5 text-xs font-medium text-rose-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- STATUS --}}
                        <div>

                            <label
                                for="status"
                                class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                            >
                                Status Akun
                                <span class="text-rose-500">*</span>
                            </label>

                            <select
                                name="status"
                                id="status"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2 text-xs font-medium text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 sm:py-2.5 sm:text-sm"
                            >
                                <option
                                    value="aktif"
                                    {{ $oldStatus === 'aktif' ? 'selected' : '' }}
                                >
                                    Aktif (Dapat Login)
                                </option>

                                <option
                                    value="nonaktif"
                                    {{ $oldStatus === 'nonaktif' ? 'selected' : '' }}
                                >
                                    Nonaktif (Diblokir)
                                </option>
                            </select>

                            @error('status')
                                <p class="mt-1.5 text-xs font-medium text-rose-500">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>


                    {{-- PASSWORD --}}
                    <div class="mt-4 sm:mt-5">

                        <label
                            for="password"
                            class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                        >
                            Password
                            <span class="text-rose-500">*</span>
                        </label>

                        <div class="relative">

                            <input
                                type="password"
                                name="password"
                                id="password"
                                required
                                autocomplete="new-password"
                                placeholder="Minimal 6 karakter"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-11 text-xs text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 sm:py-2.5 sm:text-sm @error('password') border-rose-500 bg-rose-50/30 @enderror"
                            >

                            <button
                                type="button"
                                id="togglePassword"
                                aria-label="Tampilkan atau sembunyikan password"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition hover:text-slate-600 focus:outline-none"
                            >

                                <svg
                                    id="eyeOpen"
                                    class="h-5 w-5"
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

                                <svg
                                    id="eyeClosed"
                                    class="hidden h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        d="M3 3l18 18"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6.5 6.5C4 8.3 2.5 12 2.5 12s3.5 6 9.5 6c1.9 0 3.5-.42 4.8-1.05"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M10 5.2A9.7 9.7 0 0112 5c6 0 9.5 7 9.5 7s-1.1 2.1-3.2 3.8"
                                    />
                                </svg>

                            </button>

                        </div>

                        <p class="mt-1.5 text-[10px] leading-relaxed text-slate-400 sm:text-[11px]">
                            Gunakan kombinasi huruf, angka, dan karakter khusus untuk keamanan yang lebih baik.
                        </p>

                        @error('password')
                            <p class="mt-1.5 text-xs font-medium text-rose-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- KONFIRMASI PASSWORD --}}
                    <div class="mt-4 sm:mt-5">

                        <label
                            for="password_confirmation"
                            class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-600"
                        >
                            Konfirmasi Password
                            <span class="text-rose-500">*</span>
                        </label>

                        <div class="relative">

                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                required
                                autocomplete="new-password"
                                placeholder="Ulangi password"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2 pl-3.5 pr-11 text-xs text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10 sm:py-2.5 sm:text-sm @error('password_confirmation') border-rose-500 bg-rose-50/30 @enderror"
                            >

                            <button
                                type="button"
                                id="togglePasswordConfirmation"
                                aria-label="Tampilkan atau sembunyikan konfirmasi password"
                                class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 transition hover:text-slate-600 focus:outline-none"
                            >

                                <svg
                                    id="eyeConfirmationOpen"
                                    class="h-5 w-5"
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

                                <svg
                                    id="eyeConfirmationClosed"
                                    class="hidden h-5 w-5"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        d="M3 3l18 18"
                                    />

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M6.5 6.5C4 8.3 2.5 12 2.5 12s3.5 6 9.5 6c1.9 0 3.5-.42 4.8-1.05"
                                    />
                                </svg>

                            </button>

                        </div>

                        @error('password_confirmation')
                            <p class="mt-1.5 text-xs font-medium text-rose-500">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </section>

            </div>


            {{-- ====================================================
               FOOTER ACTION
            ==================================================== --}}
            <div class="flex flex-col-reverse items-center justify-end gap-2.5 border-t border-slate-100 bg-slate-50 px-4 py-3.5 sm:flex-row sm:px-8 sm:py-4">

                <a
                    href="{{ route('users.index') }}"
                    class="w-full rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-center text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-100 hover:text-slate-800 sm:w-auto"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-md shadow-blue-600/30 transition hover:bg-blue-700 sm:w-auto"
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
                            d="M5 12.5l4 4L19 7"
                        />
                    </svg>

                    Simpan Pengguna
                </button>

            </div>

        </form>

    </div>

</div>

@endsection


@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    function setupPasswordToggle(
        buttonId,
        inputId,
        openIconId,
        closedIconId
    ) {
        const button =
            document.getElementById(buttonId);

        const input =
            document.getElementById(inputId);

        const openIcon =
            document.getElementById(openIconId);

        const closedIcon =
            document.getElementById(closedIconId);

        if (
            !button ||
            !input ||
            !openIcon ||
            !closedIcon
        ) {
            return;
        }

        button.addEventListener(
            'click',
            function () {

                const isPassword =
                    input.type === 'password';

                input.type =
                    isPassword
                        ? 'text'
                        : 'password';

                openIcon.classList.toggle(
                    'hidden',
                    isPassword
                );

                closedIcon.classList.toggle(
                    'hidden',
                    !isPassword
                );
            }
        );
    }

    setupPasswordToggle(
        'togglePassword',
        'password',
        'eyeOpen',
        'eyeClosed'
    );

    setupPasswordToggle(
        'togglePasswordConfirmation',
        'password_confirmation',
        'eyeConfirmationOpen',
        'eyeConfirmationClosed'
    );

});
</script>

@endpush