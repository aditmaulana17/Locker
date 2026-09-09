@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')

@php
    $currentRole = strtolower(
        trim(
            (string) ($user->role ?? '')
        )
    );

    if ($currentRole === 'staf') {
        $currentRole = 'staff';
    }

    $currentIsActive = (bool) (
        $user->is_active ?? true
    );
@endphp

<div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">

        <div class="min-w-0">

            <div class="flex items-center gap-2">

                <span class="w-2.5 h-2.5 shrink-0 rounded-full bg-amber-500"></span>

                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-800 truncate">
                    Edit Pengguna:
                    {{ $user->name }}
                </h1>

            </div>

            <p class="mt-0.5 text-sm text-slate-500">
                Perbarui informasi akun, hak akses, dan status pengguna di dalam sistem.
            </p>

        </div>

        <a href="{{ route('users.index') }}"
           class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 text-sm font-semibold text-slate-700 bg-white border-2 border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 transition">

            <svg class="w-4 h-4 mr-2"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="1.8"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M15 19l-7-7 7-7"/>
            </svg>

            Kembali ke Daftar
        </a>

    </div>


    {{-- Main Card --}}
    <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-sm overflow-hidden">

        <form method="POST"
              action="{{ route('users.update', $user->id) }}"
              id="form-user">

            @csrf
            @method('PUT')

            <div class="p-4 sm:p-5">

                {{-- =====================================================
                     INFORMASI PERSONAL
                ====================================================== --}}
                <section>

                    <div class="section-heading">

                        <div class="section-icon">
                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 viewBox="0 0 24 24">

                                <circle cx="12"
                                        cy="8"
                                        r="3.5"/>

                                <path stroke-linecap="round"
                                      d="M5 20a7 7 0 0114 0"/>

                            </svg>
                        </div>

                        <div>
                            <h2 class="section-title">
                                Informasi Personal
                            </h2>

                            <p class="section-description">
                                Perbarui identitas pengguna yang tersimpan.
                            </p>
                        </div>

                    </div>


                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3">

                        {{-- Nama --}}
                        <div class="form-group md:col-span-2">

                            <label for="name"
                                   class="form-label">
                                Nama Lengkap
                                <span class="text-rose-500">*</span>
                            </label>

                            <input type="text"
                                   name="name"
                                   id="name"
                                   value="{{ old('name', $user->name) }}"
                                   required
                                   autocomplete="name"
                                   placeholder="Contoh: Budi Santoso, S.Kom"
                                   class="form-control-custom @error('name') form-error @enderror">

                            @error('name')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Email --}}
                        <div class="form-group">

                            <label for="email"
                                   class="form-label">
                                Alamat Email
                                <span class="text-rose-500">*</span>
                            </label>

                            <input type="email"
                                   name="email"
                                   id="email"
                                   value="{{ old('email', $user->email) }}"
                                   required
                                   autocomplete="email"
                                   placeholder="budi@domain.com"
                                   class="form-control-custom @error('email') form-error @enderror">

                            @error('email')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>


                        {{-- Jabatan --}}
                        <div class="form-group">

                            <label for="jabatan"
                                   class="form-label">
                                Jabatan / Posisi
                                <span class="text-rose-500">*</span>
                            </label>

                            <input type="text"
                                   name="jabatan"
                                   id="jabatan"
                                   value="{{ old('jabatan', $user->jabatan) }}"
                                   required
                                   placeholder="Contoh: Kepala Bagian / Staf Agenda"
                                   class="form-control-custom @error('jabatan') form-error @enderror">

                            @error('jabatan')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>

                </section>


                <div class="section-divider"></div>


                {{-- =====================================================
                     HAK AKSES & KEAMANAN
                ====================================================== --}}
                <section>

                    <div class="section-heading">

                        <div class="section-icon">
                            <svg class="w-4 h-4"
                                 fill="none"
                                 stroke="currentColor"
                                 stroke-width="1.8"
                                 viewBox="0 0 24 24">

                                <rect x="4"
                                      y="10"
                                      width="16"
                                      height="10"
                                      rx="2"/>

                                <path stroke-linecap="round"
                                      d="M8 10V7a4 4 0 018 0v3"/>

                            </svg>
                        </div>

                        <div>
                            <h2 class="section-title">
                                Hak Akses & Keamanan Akun
                            </h2>

                            <p class="section-description">
                                Perbarui role, status akun, dan password pengguna.
                            </p>
                        </div>

                    </div>


                    {{-- Role & Status --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-3">

                        {{-- Role --}}
                        <div class="form-group">

                            <label for="role"
                                   class="form-label">
                                Role / Hak Akses
                                <span class="text-rose-500">*</span>
                            </label>

                            <select name="role"
                                    id="role"
                                    required
                                    class="form-control-custom @error('role') form-error @enderror">

                                <option value="admin"
                                    {{ old('role', $currentRole) === 'admin' ? 'selected' : '' }}>
                                    Admin (Akses Penuh)
                                </option>

                                <option value="pimpinan"
                                    {{ old('role', $currentRole) === 'pimpinan' ? 'selected' : '' }}>
                                    Pimpinan (Akses Eksekutif)
                                </option>

                                <option value="staff"
                                    {{ in_array(old('role', $currentRole), ['staff', 'staf'], true) ? 'selected' : '' }}>
                                    Staf (Akses Operator)
                                </option>

                            </select>

                            @error('role')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                            <p class="form-help">
                                Nilai Staf akan disimpan sebagai
                                <strong class="font-semibold text-slate-500">staff</strong>
                                di database.
                            </p>

                        </div>


                        {{-- Status --}}
                        <div class="form-group">

                            <label for="status"
                                   class="form-label">
                                Status Akun
                                <span class="text-rose-500">*</span>
                            </label>

                            <select name="status"
                                    id="status"
                                    required
                                    class="form-control-custom @error('status') form-error @enderror">

                                <option value="aktif"
                                    {{ old('status', $currentIsActive ? 'aktif' : 'nonaktif') === 'aktif' ? 'selected' : '' }}>
                                    Aktif (Dapat Login)
                                </option>

                                <option value="nonaktif"
                                    {{ old('status', $currentIsActive ? 'aktif' : 'nonaktif') === 'nonaktif' ? 'selected' : '' }}>
                                    Nonaktif (Diblokir)
                                </option>

                            </select>

                            @error('status')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    </div>


                    {{-- Password Baru --}}
                    <div class="form-group mt-3">

                        <label for="password"
                               class="form-label">
                            Password Baru

                            <span class="font-normal normal-case text-slate-400">
                                (kosongkan jika tidak ingin mengubah)
                            </span>
                        </label>

                        <div class="relative">

                            <input type="password"
                                   name="password"
                                   id="password"
                                   autocomplete="new-password"
                                   placeholder="Masukkan password baru"
                                   class="form-control-custom pr-11 @error('password') form-error @enderror">

                            <button type="button"
                                    id="togglePassword"
                                    aria-label="Tampilkan atau sembunyikan password"
                                    class="password-toggle">

                                <svg id="eyeOpen"
                                     class="w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M2.5 12s3.5-6 9.5-6s9.5 6 9.5 6s-3.5 6-9.5 6s-9.5-6-9.5-6z"/>

                                    <circle cx="12"
                                            cy="12"
                                            r="3"/>

                                </svg>

                                <svg id="eyeClosed"
                                     class="hidden w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          d="M3 3l18 18"/>

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M6.5 6.5C4 8.3 2.5 12 2.5 12s3.5 6 9.5 6c1.9 0 3.5-.42 4.8-1.05"/>

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M10 5.2A9.7 9.7 0 0112 5c6 0 9.5 7 9.5 7s-1.1 2.1-3.2 3.8"/>

                                </svg>

                            </button>

                        </div>

                        <p class="form-help">
                            Minimal 6 karakter. Kosongkan untuk mempertahankan password lama.
                        </p>

                        @error('password')
                            <p class="form-error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>


                    {{-- Konfirmasi Password --}}
                    <div class="form-group mt-3">

                        <label for="password_confirmation"
                               class="form-label">
                            Konfirmasi Password Baru
                        </label>

                        <div class="relative">

                            <input type="password"
                                   name="password_confirmation"
                                   id="password_confirmation"
                                   autocomplete="new-password"
                                   placeholder="Ulangi password baru"
                                   class="form-control-custom pr-11 @error('password_confirmation') form-error @enderror">

                            <button type="button"
                                    id="togglePasswordConfirmation"
                                    aria-label="Tampilkan atau sembunyikan konfirmasi password"
                                    class="password-toggle">

                                <svg id="eyeConfirmationOpen"
                                     class="w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M2.5 12s3.5-6 9.5-6s9.5 6 9.5 6s-3.5 6-9.5 6s-9.5-6-9.5-6z"/>

                                    <circle cx="12"
                                            cy="12"
                                            r="3"/>

                                </svg>

                                <svg id="eyeConfirmationClosed"
                                     class="hidden w-5 h-5"
                                     fill="none"
                                     stroke="currentColor"
                                     stroke-width="1.8"
                                     viewBox="0 0 24 24">

                                    <path stroke-linecap="round"
                                          d="M3 3l18 18"/>

                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          d="M10.58 10.58A3 3 0 0013.42 13.42"/>

                                </svg>

                            </button>

                        </div>

                        @error('password_confirmation')
                            <p class="form-error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </section>

            </div>


            {{-- Footer --}}
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 px-4 sm:px-5 py-2.5 bg-slate-50 border-t-2 border-slate-300">

                <a href="{{ route('users.index') }}"
                   class="action-button action-button-secondary">
                    Batal
                </a>

                <button type="submit"
                        id="submit-btn"
                        class="action-button action-button-primary">

                    <svg class="w-4 h-4 mr-2"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="1.8"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M5 12.5l4 4L19 7"/>
                    </svg>

                    Simpan Perubahan

                </button>

            </div>

        </form>

    </div>

</div>


{{-- =============================================================
CSS
============================================================= --}}
<style>
    .section-heading {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding-bottom: .45rem;
        margin-bottom: .75rem;
        border-bottom: 2px solid #cbd5e1;
    }

    .section-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
        flex-shrink: 0;
        color: #2563eb;
        background: #eff6ff;
        border: 2px solid #dbeafe;
        border-radius: .6rem;
    }

    .section-title {
        color: #334155;
        font-size: .82rem;
        line-height: 1rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .section-description {
        margin-top: .05rem;
        color: #94a3b8;
        font-size: .68rem;
        line-height: .9rem;
    }

    .section-divider {
        height: 2px;
        margin: 1.25rem 0;
        background: #e2e8f0;
    }

    .form-group {
        width: 100%;
    }

    .form-label {
        display: block;
        margin-bottom: .3rem;
        color: #334155;
        font-size: .68rem;
        line-height: .9rem;
        font-weight: 700;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .form-control-custom {
        width: 100%;
        min-height: 40px;
        padding: .48rem .7rem;
        background: #fff;
        color: #334155;
        border: 2px solid #94a3b8;
        border-radius: .6rem;
        outline: none;
        font-size: .8rem;
        line-height: 1.25;
        transition:
            border-color .15s ease,
            background-color .15s ease,
            box-shadow .15s ease;
    }

    .form-control-custom:hover {
        border-color: #64748b;
    }

    .form-control-custom:focus {
        border-color: #2563eb;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
    }

    .form-control-custom::placeholder {
        color: #94a3b8;
    }

    .form-error {
        border-color: #f43f5e !important;
        background: #fff1f2 !important;
    }

    .form-error-text {
        margin-top: .2rem;
        color: #e11d48;
        font-size: .68rem;
        line-height: .9rem;
        font-weight: 500;
    }

    .form-help {
        margin-top: .25rem;
        color: #94a3b8;
        font-size: .66rem;
        line-height: .9rem;
        font-weight: 500;
    }

    .password-toggle {
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        padding-right: .75rem;
        color: #94a3b8;
        transition: color .15s ease;
    }

    .password-toggle:hover {
        color: #475569;
    }

    .password-toggle:focus {
        outline: none;
    }

    .action-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: .5rem .9rem;
        border-width: 2px;
        border-radius: .6rem;
        font-size: .76rem;
        font-weight: 700;
        transition: all .15s ease;
    }

    .action-button-secondary {
        color: #334155;
        background: #fff;
        border-color: #cbd5e1;
    }

    .action-button-secondary:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    .action-button-primary {
        color: #fff;
        background: #2563eb;
        border-color: #2563eb;
        box-shadow: 0 4px 10px rgba(37, 99, 235, .15);
    }

    .action-button-primary:hover {
        background: #1d4ed8;
        border-color: #1d4ed8;
    }

    .action-button:disabled {
        opacity: .65;
        cursor: not-allowed;
    }

    @media (min-width: 640px) {
        .action-button {
            width: auto;
        }
    }
</style>


{{-- =============================================================
JAVASCRIPT
============================================================= --}}
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

            button.addEventListener('click', function () {

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
            });
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


        const formUser =
            document.getElementById('form-user');

        if (formUser) {

            formUser.addEventListener('submit', function () {

                const submitButton =
                    document.getElementById('submit-btn');

                if (!submitButton) {
                    return;
                }

                submitButton.disabled = true;

                submitButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                         xmlns="http://www.w3.org/2000/svg"
                         fill="none"
                         viewBox="0 0 24 24">

                        <circle class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4">
                        </circle>

                        <path class="opacity-75"
                              fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>

                    </svg>

                    Menyimpan...
                `;
            });
        }

    });
</script>
@endpush

@endsection