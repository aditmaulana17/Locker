<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Locker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-blue-500 selection:text-white">
@php
    $authUser = auth()->user();
    $userRole = strtolower(trim($authUser->role ?? $authUser->jabatan ?? 'staf'));
    $userRole = $userRole === 'staff' ? 'staf' : $userRole;
    $isAdmin = $userRole === 'admin';
    $isPimpinan = $userRole === 'pimpinan';
    $isStaf = $userRole === 'staf';
@endphp

<div class="min-h-screen bg-slate-50">
    {{-- MOBILE BACKDROP --}}
    <div id="sidebar-backdrop" class="fixed inset-0 z-30 hidden bg-slate-950/50 backdrop-blur-[2px] transition-opacity duration-300 lg:hidden"></div>

    {{-- SIDEBAR --}}
    <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-slate-800 bg-slate-900 text-slate-300 shadow-2xl transition-transform duration-300 lg:translate-x-0">
        {{-- BRAND --}}
        <div class="flex h-20 shrink-0 items-center justify-between border-b border-slate-800/80 px-5">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 items-center gap-3">
                <div class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 via-blue-600 to-indigo-700 text-white shadow-lg shadow-blue-500/30 ring-1 ring-white/20">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h9a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="absolute -right-1 -top-1 h-3 w-3 rounded-full border-2 border-slate-900 bg-emerald-400"></span>
                </div>
                <div class="min-w-0">
                    <div class="truncate text-base font-extrabold tracking-wider text-white">Locker</div>
                    <div class="truncate text-[10px] font-medium tracking-wide text-slate-400">Sistem Manajemen Arsip</div>
                </div>
            </a>

            {{-- CLOSE MOBILE --}}
            <button type="button" id="mobile-sidebar-close" class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-800 hover:text-white focus:outline-none lg:hidden" aria-label="Tutup menu">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- NAVIGATION --}}
        <nav class="flex-1 overflow-y-auto px-3 py-5">
            <div class="space-y-1.5">
                {{-- DASHBOARD --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-blue-600 font-semibold text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                {{-- SURAT MASUK --}}
                <a href="{{ route('surat-masuk.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('surat-masuk.*') ? 'bg-blue-600 font-semibold text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <span>Surat Masuk</span>
                </a>

                {{-- SURAT KELUAR --}}
                <a href="{{ route('surat-keluar.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('surat-keluar.*') ? 'bg-blue-600 font-semibold text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span>Surat Keluar</span>
                </a>

                {{-- DISPOSISI --}}
                <a href="{{ route('disposisi.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('disposisi.*') ? 'bg-blue-600 font-semibold text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                    <span>Disposisi</span>
                </a>
            </div>

            {{-- MASTER DATA ADMIN --}}
            @if($isAdmin)
                <div class="mb-2 mt-6 px-3.5">
                    <div class="border-t border-slate-800 pt-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.15em] text-slate-500">Master Data</p>
                    </div>
                </div>

                {{-- KATEGORI --}}
                <a href="{{ route('kategori.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('kategori.*') ? 'bg-blue-600 font-semibold text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M11 7h8M11 11h8M11 15h8"/>
                    </svg>
                    <span>Kategori Surat</span>
                </a>

                {{-- USERS --}}
                <a href="{{ route('users.index') }}" class="mt-1.5 flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-blue-600 font-semibold text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Pengguna</span>
                </a>
            @endif
        </nav>

        {{-- SIDEBAR FOOTER --}}
        <div class="shrink-0 border-t border-slate-800/80 px-4 py-4 text-center">
            <p class="text-[11px] font-medium text-slate-500">Locker &copy; {{ date('Y') }}</p>
        </div>
    </aside>

    {{-- MAIN --}}
    <div class="min-h-screen lg:pl-64">
        {{-- TOPBAR --}}
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 shadow-sm backdrop-blur-md">
            <div class="flex min-h-[68px] items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
                {{-- LEFT --}}
                <div class="flex min-w-0 items-center gap-2.5 sm:gap-3">
                    {{-- MOBILE MENU --}}
                    <button type="button" id="mobile-sidebar-open" class="rounded-xl p-2 text-slate-600 transition hover:bg-slate-100 hover:text-blue-600 focus:outline-none lg:hidden" aria-label="Buka menu">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <h1 class="truncate text-base font-bold tracking-tight text-slate-800 sm:text-lg">@yield('title', 'Dashboard')</h1>
                        <p class="hidden text-[11px] text-slate-400 sm:block">Sistem Manajemen Arsip</p>
                    </div>
                </div>

                {{-- RIGHT --}}
                <div class="flex shrink-0 items-center gap-2 sm:gap-4">
                    {{-- USER --}}
                    <div class="flex items-center gap-2 sm:gap-3 sm:border-l sm:border-slate-200 sm:pl-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-blue-200 bg-blue-100 text-sm font-bold text-blue-700 shadow-sm">
                            {{ strtoupper(substr($authUser->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="hidden min-w-0 sm:block">
                            <p class="max-w-[160px] truncate text-sm font-bold leading-tight text-slate-800">{{ $authUser->name ?? 'Pengguna' }}</p>
                            <p class="mt-0.5 max-w-[180px] truncate text-[11px] font-medium text-slate-400">
                                {{ ucfirst($userRole) }}
                                @if(!empty($authUser->jabatan))
                                    &middot; {{ $authUser->jabatan }}
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- LOGOUT --}}
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-200 sm:px-3.5">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        {{-- CONTENT --}}
        <main class="min-h-[calc(100vh-68px)] p-4 sm:p-6 lg:p-8">
            {{-- SUCCESS --}}
            @if(session('success'))
                <div id="flash-success" class="fixed right-4 top-4 z-[60] flex w-[calc(100%-2rem)] max-w-sm items-start gap-3 rounded-2xl border border-emerald-100 bg-white p-4 shadow-xl">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Berhasil</p>
                        <p class="mt-0.5 text-xs font-medium leading-relaxed text-slate-600">{{ session('success') }}</p>
                    </div>
                    <button type="button" onclick="document.getElementById('flash-success')?.remove()" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            {{-- ERROR --}}
            @if(session('error'))
                <div id="flash-error" class="fixed right-4 top-4 z-[60] flex w-[calc(100%-2rem)] max-w-sm items-start gap-3 rounded-2xl border border-red-100 bg-white p-4 shadow-xl">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-100 text-red-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-red-700">Perhatian</p>
                        <p class="mt-0.5 text-xs font-medium leading-relaxed text-slate-600">{{ session('error') }}</p>
                    </div>
                    <button type="button" onclick="document.getElementById('flash-error')?.remove()" class="rounded-lg p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const openButton = document.getElementById('mobile-sidebar-open');
    const closeButton = document.getElementById('mobile-sidebar-close');

    const openSidebar = () => {
        sidebar?.classList.remove('-translate-x-full');
        backdrop?.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    };

    const closeSidebar = () => {
        sidebar?.classList.add('-translate-x-full');
        backdrop?.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    };

    openButton?.addEventListener('click', openSidebar);
    closeButton?.addEventListener('click', closeSidebar);
    backdrop?.addEventListener('click', closeSidebar);

    document.querySelectorAll('#app-sidebar a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) closeSidebar();
        });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) {
            backdrop?.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    });

    setTimeout(() => {
        document.getElementById('flash-success')?.remove();
        document.getElementById('flash-error')?.remove();
    }, 5000);

    document.addEventListener('click', event => {
        const deleteButton = event.target.closest('.delete-btn');
        if (!deleteButton) return;

        const form = deleteButton.closest('.delete-form');
        if (!form) return;

        event.preventDefault();

        if (typeof Swal === 'undefined') {
            if (confirm('Apakah Anda yakin ingin menghapus data ini?')) form.submit();
            return;
        }

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Data yang dihapus akan dipindahkan ke arsip sampah.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            buttonsStyling: false,
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl bg-red-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-600 ml-2',
                cancelButton: 'rounded-xl bg-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-300'
            }
        }).then(result => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>

@stack('scripts')
</body>
</html>