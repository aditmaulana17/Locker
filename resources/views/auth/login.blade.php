<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk - Locker</title>

@vite(['resources/css/app.css'])

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>

<!-- Alpine.js -->
<script
    defer
    src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"
></script>

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
</style>

</head>

<body class="bg-slate-900 antialiased selection:bg-brand-500 selection:text-white">

<div class="min-h-screen flex flex-col lg:flex-row text-slate-800">

    <!-- =========================================================
         SISI KIRI : BRANDING PANEL
    ========================================================== -->

    <div class="hidden lg:flex lg:w-1/2 xl:w-7/12 relative bg-slate-900 flex-col justify-between p-8 xl:p-12 overflow-hidden">

        <div class="absolute inset-0 bg-gradient-to-tr from-brand-900/80 via-slate-900 to-slate-900 z-0"></div>

        <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-blue-600/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="absolute inset-0 bg-[linear-gradient(to_right,#1e293b_1px,transparent_1px),linear-gradient(to_bottom,#1e293b_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] opacity-30 z-0"></div>

        <!-- Logo -->
        <div class="relative z-10 flex items-center gap-3">

            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center text-white shadow-lg shadow-brand-500/30">

                <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                    />
                </svg>

            </div>

            <span class="text-xl font-bold text-white tracking-wide">
                Loc<span class="text-brand-400">ker</span>
            </span>

        </div>

        <!-- Branding -->
        <div class="relative z-10 max-w-lg space-y-4 my-auto py-12">

            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-500/20 border border-blue-400/40 text-blue-300 text-xs font-semibold uppercase tracking-wider shadow-sm">

                <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>

                Sistem Informasi Persuratan

            </div>

            <h1 class="text-3xl lg:text-4xl xl:text-5xl font-extrabold text-white leading-tight">
                Pengelolaan Dokumen & Surat Lebih Terstruktur.
            </h1>

            <p class="text-slate-400 text-sm lg:text-base leading-relaxed">
                Kelola surat masuk, surat keluar, disposisi, hingga pencetakan label secara terpusat, cepat, dan terorganisir.
            </p>

        </div>

        <!-- Footer -->
        <div class="relative z-10 pt-6 border-t border-slate-800 text-xs text-slate-500 flex items-center justify-between">

            <span>
                &copy; {{ date('Y') }} Locker. All rights reserved.
            </span>

            <span>
                v1.0.0
            </span>

        </div>

    </div>


    <!-- =========================================================
         SISI KANAN : FORM LOGIN
    ========================================================== -->

    <div class="w-full lg:w-1/2 xl:w-5/12 bg-white flex flex-col justify-center px-6 sm:px-8 lg:px-12 xl:px-16 py-8 sm:py-12 min-h-screen lg:min-h-0 overflow-y-auto">

        <div class="w-full max-w-md mx-auto space-y-6">

            <!-- =================================================
                 MOBILE HEADER
            ================================================== -->

            <div class="lg:hidden flex items-center gap-3 mb-2">

                <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-md">

                    <svg
                        class="w-5 h-5 sm:w-6 sm:h-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        />
                    </svg>

                </div>

                <span class="text-lg sm:text-xl font-bold text-slate-800">
                    Locker
                </span>

            </div>


            <!-- =================================================
                 TITLE
            ================================================== -->

            <div class="space-y-1">

                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    Selamat Datang Kembali
                </h2>

                <p class="text-xs sm:text-sm text-slate-500">
                    Silakan masukkan kredensial Anda untuk mengakses dashboard.
                </p>

            </div>


            <!-- =================================================
                 NOTIFIKASI AKUN DINONAKTIFKAN
            ================================================== -->

            @if (session('account_blocked'))

                <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm">

                    <div class="flex items-start gap-3">

                        <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">

                            <svg
                                class="w-5 h-5 text-amber-600"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15a2 2 0 001.73 3h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z"
                                />
                            </svg>

                        </div>

                        <div class="flex-1">

                            <div class="font-bold text-amber-900 mb-1">
                                Akun Dinonaktifkan
                            </div>

                            <p class="leading-relaxed text-amber-700">
                                {{ session('account_blocked') }}
                            </p>

                        </div>

                    </div>

                </div>

            @endif


            <!-- =================================================
                 SESSION STATUS
            ================================================== -->

            @if (session('status'))

                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs sm:text-sm">

                    <div class="flex items-center gap-2">

                        <svg
                            class="w-4 h-4 shrink-0"
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

                        <span>
                            {{ session('status') }}
                        </span>

                    </div>

                </div>

            @endif


            <!-- =================================================
                 ERROR LOGIN
            ================================================== -->

            @if ($errors->any())

                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm space-y-1">

                    <div class="font-semibold flex items-center gap-2">

                        <svg
                            class="w-4 h-4 text-rose-500 shrink-0"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>

                        Login Gagal

                    </div>

                    <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-600 pl-1">

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            <!-- =================================================
                 FORM LOGIN
            ================================================== -->

            <form
                id="loginForm"
                method="POST"
                action="{{ route('login.attempt') }}"
                class="space-y-4"
            >

                @csrf


                <!-- =================================================
                     EMAIL
                ================================================== -->

                <div>

                    <label
                        for="email"
                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2"
                    >
                        Alamat Email
                        <span class="text-rose-500">*</span>
                    </label>

                    <div class="relative">

                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">

                            <svg
                                class="w-4 h-4 sm:w-5 sm:h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"
                                />
                            </svg>

                        </div>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="admin@admin.com"
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        >

                    </div>

                </div>


                <!-- =================================================
                     PASSWORD
                ================================================== -->

                <div>

                    <label
                        for="password"
                        class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2"
                    >
                        Kata Sandi
                        <span class="text-rose-500">*</span>
                    </label>

                    <div
                        class="relative"
                        x-data="{ showPassword: false }"
                    >

                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">

                            <svg
                                class="w-4 h-4 sm:w-5 sm:h-5"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                                />
                            </svg>

                        </div>

                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            name="password"
                            required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-12 py-3 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                        >

                        <!-- Toggle Password -->
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center justify-center text-slate-400 hover:text-slate-600 focus:outline-none transition-colors"
                            aria-label="Tampilkan atau sembunyikan kata sandi"
                        >

                            <!-- Password Visible -->
                            <svg
                                x-show="showPassword"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                />
                            </svg>


                            <!-- Password Hidden -->
                            <svg
                                x-show="!showPassword"
                                class="w-5 h-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.07 10.07 0 014.136-5.404"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9.88 9.88l-3.29-3.29"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M14.12 14.12l3.29 3.29"
                                />

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M3 3l18 18"
                                />
                            </svg>

                        </button>

                    </div>

                </div>


                <!-- =================================================
                     REMEMBER ME
                ================================================== -->

                <div class="flex items-center justify-between text-xs sm:text-sm">

                    <label class="flex items-center gap-2 cursor-pointer">

                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >

                        <span class="text-slate-600">
                            Ingat saya
                        </span>

                    </label>

                </div>


                <!-- =================================================
                     SUBMIT
                ================================================== -->

                <div class="pt-2">

                    <button
                        type="submit"
                        class="w-full py-3.5 px-4 bg-blue-600 hover:bg-blue-700 active:scale-[0.99] text-white font-semibold text-xs sm:text-sm rounded-xl transition-all shadow-md shadow-blue-600/20 flex items-center justify-center gap-2"
                    >

                        <span>
                            Masuk ke Akun
                        </span>

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
                                d="M14 5l7 7m0 0l-7 7m7-7H3"
                            />
                        </svg>

                    </button>

                </div>

            </form>


            <!-- =================================================
                 LINK REGISTER
            ================================================== -->

            <p class="text-center text-xs sm:text-sm text-slate-500 pt-2">

                Belum memiliki akun?

                <a
                    href="{{ route('register') }}"
                    class="font-semibold text-blue-600 hover:text-blue-700 hover:underline"
                >
                    Daftar Akun Baru
                </a>

            </p>


            <!-- =================================================
                 AKUN DEMO
            ================================================== -->

            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-600 text-xs space-y-1">

                <div class="font-semibold flex items-center gap-1.5 text-slate-700">

                    <svg
                        class="w-4 h-4 text-blue-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>

                    Akun Demo:

                </div>

                <div class="font-mono text-[11px] text-slate-500">
                    Email: name@gmail.com &nbsp;&nbsp; Pass: password
                </div>

            </div>

        </div>

    </div>

</div>


<!-- =============================================================
     REMEMBER ME - LOCAL STORAGE
============================================================= -->

<script>
    document.addEventListener("DOMContentLoaded", function () {

        const emailInput = document.getElementById("email");
        const passwordInput = document.getElementById("password");
        const rememberCheckbox = document.getElementById("remember");
        const loginForm = document.getElementById("loginForm");

        if (
            !emailInput ||
            !passwordInput ||
            !rememberCheckbox ||
            !loginForm
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD DATA
        |--------------------------------------------------------------------------
        */

        if (
            localStorage.getItem("remember_status") === "true"
        ) {

            emailInput.value =
                localStorage.getItem("saved_email") || "";

            passwordInput.value =
                localStorage.getItem("saved_password") || "";

            rememberCheckbox.checked = true;
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE / REMOVE DATA
        |--------------------------------------------------------------------------
        */

        loginForm.addEventListener("submit", function () {

            if (rememberCheckbox.checked) {

                localStorage.setItem(
                    "saved_email",
                    emailInput.value
                );

                localStorage.setItem(
                    "saved_password",
                    passwordInput.value
                );

                localStorage.setItem(
                    "remember_status",
                    "true"
                );

            } else {

                localStorage.removeItem(
                    "saved_email"
                );

                localStorage.removeItem(
                    "saved_password"
                );

                localStorage.removeItem(
                    "remember_status"
                );
            }
        });

    });
</script>
</body>
</html>
