@extends('layouts.app')

@section('title', 'Kategori Surat')

@section('content')

<div class="space-y-4 sm:space-y-6">

    {{-- ================================================================
         HEADER
    ================================================================= --}}

    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center sm:gap-4">
        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">
                Kategori Surat
            </h1>

            <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                Kelola master data kategori dan klasifikasi jenis surat.
            </p>
        </div>

        <button
            type="button"
            onclick="document.getElementById('modalTambah').classList.remove('hidden')"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-blue-700 sm:w-auto sm:text-sm"
        >
            <svg
                class="h-4 w-4 shrink-0"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4"
                />
            </svg>

            <span>Tambah Kategori</span>
        </button>
    </div>

    {{-- ================================================================
         FILTER & SEARCH
    ================================================================= --}}

    <div class="rounded-2xl border border-slate-300 bg-white p-3 shadow-sm sm:p-4">
        <form
            method="GET"
            action="{{ route('kategori.index') }}"
            class="flex flex-col items-center gap-2.5 sm:flex-row sm:gap-3"
        >
            <div class="relative w-full sm:w-80">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                </div>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari nama kategori atau kode..."
                    autocomplete="off"
                    class="h-11 w-full rounded-xl border border-slate-300 bg-slate-50 pl-9 pr-3 text-xs text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                >
            </div>

            <div class="flex w-full items-center gap-2 sm:w-auto">
                <button
                    type="submit"
                    class="flex-1 rounded-xl bg-slate-900 px-5 py-2.5 text-center text-xs font-semibold text-white shadow-sm transition-colors hover:bg-slate-800 sm:flex-initial sm:text-sm"
                >
                    Cari
                </button>

                @if(request()->filled('search'))
                    <a
                        href="{{ route('kategori.index') }}"
                        class="rounded-xl border border-slate-300 bg-slate-100 px-3.5 py-2.5 text-center text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200 sm:text-sm"
                    >
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ================================================================
         TABLE
    ================================================================= --}}

    <div class="kategori-table-wrapper">

        <div class="kategori-table-scroll">

            <table class="kategori-table">

                <thead>
                    <tr>
                        <th class="category-name-column">
                            Nama Kategori
                        </th>

                        <th>
                            Kode
                        </th>

                        <th>
                            Sifat
                        </th>

                        <th class="text-center">
                            Jml Surat Masuk
                        </th>

                        <th class="text-center">
                            Jml Surat Keluar
                        </th>

                        <th class="text-center action-column">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($kategoris as $k)

                        @php
                            $sifatClass = match (
                                strtolower((string) ($k->sifat ?? ''))
                            ) {
                                'segera' =>
                                    'bg-rose-50 text-rose-700 border-rose-200',

                                'penting' =>
                                    'bg-amber-50 text-amber-700 border-amber-200',

                                'rahasia' =>
                                    'bg-purple-50 text-purple-700 border-purple-200',

                                'biasa' =>
                                    'bg-blue-50 text-blue-700 border-blue-200',

                                default =>
                                    'bg-slate-100 text-slate-600 border-slate-200',
                            };
                        @endphp

                        <tr>

                            {{-- NAMA KATEGORI --}}
                            <td>
                                <div class="category-name">
                                    {{ $k->nama_kategori }}
                                </div>

                                @if($k->keterangan)
                                    <div
                                        class="category-description"
                                        title="{{ $k->keterangan }}"
                                    >
                                        {{ $k->keterangan }}
                                    </div>
                                @endif
                            </td>

                            {{-- KODE --}}
                            <td class="whitespace-nowrap">
                                <span class="category-code">
                                    {{ $k->kode }}
                                </span>
                            </td>

                            {{-- SIFAT --}}
                            <td class="whitespace-nowrap">
                                <span class="category-status {{ $sifatClass }}">
                                    {{ ucfirst($k->sifat ?? 'Biasa') }}
                                </span>
                            </td>

                            {{-- SURAT MASUK --}}
                            <td class="text-center whitespace-nowrap">
                                <span class="category-counter">
                                    {{ $k->surat_masuk_count ?? 0 }}
                                </span>
                            </td>

                            {{-- SURAT KELUAR --}}
                            <td class="text-center whitespace-nowrap">
                                <span class="category-counter">
                                    {{ $k->surat_keluar_count ?? 0 }}
                                </span>
                            </td>

                            {{-- AKSI --}}
                            <td class="text-center whitespace-nowrap">

                                <div class="category-actions">

                                    {{-- EDIT --}}
                                    <button
                                        type="button"
                                        onclick="document.getElementById('modalEdit{{ $k->id }}').classList.remove('hidden')"
                                        class="category-action edit"
                                        title="Ubah Kategori"
                                        aria-label="Ubah kategori"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                            />
                                        </svg>
                                    </button>

                                    {{-- DELETE --}}
                                    <form
                                        action="{{ route('kategori.destroy', $k) }}"
                                        method="POST"
                                        class="delete-form inline"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="button"
                                            class="category-action delete delete-btn"
                                            title="Hapus Kategori"
                                            aria-label="Hapus kategori"
                                        >
                                            <svg
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 01-1-1h-4a1 1 0 01-1 1v3M4 7h16"
                                                />
                                            </svg>
                                        </button>
                                    </form>

                                </div>

                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="6"
                                class="category-empty"
                            >
                                <div class="flex max-w-sm flex-col items-center justify-center mx-auto">

                                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-slate-100 text-slate-400 sm:h-14 sm:w-14">
                                        <svg
                                            class="h-6 w-6 sm:h-7 sm:w-7"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 7h10M7 11h10M7 15h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"
                                            />
                                        </svg>
                                    </div>

                                    <h3 class="text-sm font-semibold text-slate-800 sm:text-base">
                                        Belum ada kategori surat
                                    </h3>

                                    <p class="mt-1 mb-4 text-center text-xs text-slate-500 sm:mb-5">
                                        Silakan tambahkan data kategori surat baru.
                                    </p>

                                    <button
                                        type="button"
                                        onclick="document.getElementById('modalTambah').classList.remove('hidden')"
                                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-blue-700"
                                    >
                                        <svg
                                            class="h-4 w-4"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 4v16m8-8H4"
                                            />
                                        </svg>

                                        <span>Tambah Kategori</span>
                                    </button>

                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    @if(isset($kategoris) && method_exists($kategoris, 'links'))
        <div class="pt-1 sm:pt-2">
            {{ $kategoris->withQueryString()->links() }}
        </div>
    @endif

</div>

{{-- ================================================================
     MODAL EDIT
================================================================ --}}

@foreach($kategoris as $k)

<div
    id="modalEdit{{ $k->id }}"
    class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/50 p-3 backdrop-blur-sm sm:p-4"
>
    <div class="relative mx-auto my-auto flex min-h-full w-full max-w-lg items-center justify-center">

        <div class="relative w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">

            {{-- HEADER --}}
            <div class="flex items-center justify-between border-b border-slate-300 bg-slate-50 px-4 py-3.5 sm:px-6 sm:py-4">

                <div class="flex min-w-0 items-center gap-2.5 sm:gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-amber-200 bg-amber-50 text-amber-600 sm:h-10 sm:w-10">
                        <svg
                            class="h-4 w-4 sm:h-5 sm:w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-bold text-slate-800 sm:text-base">
                            Ubah Kategori Surat
                        </h3>

                        <p class="truncate text-[11px] text-slate-500 sm:text-xs">
                            Perbarui rincian informasi kategori surat.
                        </p>
                    </div>

                </div>

                <button
                    type="button"
                    onclick="document.getElementById('modalEdit{{ $k->id }}').classList.add('hidden')"
                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Tutup"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

            </div>

            {{-- FORM --}}
            <form
                method="POST"
                action="{{ route('kategori.update', $k) }}"
            >
                @csrf
                @method('PUT')

                <div class="space-y-4 p-4 sm:p-6">

                    {{-- NAMA --}}
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-700 sm:text-xs">
                            Nama Kategori
                            <span class="text-rose-500">*</span>
                        </label>

                        <input
                            type="text"
                            name="nama_kategori"
                            value="{{ $k->nama_kategori }}"
                            required
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-xs text-slate-800 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                        >
                    </div>

                    {{-- KODE + SIFAT --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-700 sm:text-xs">
                                Kode Surat
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="text"
                                name="kode"
                                value="{{ $k->kode }}"
                                required
                                class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 font-mono text-xs uppercase text-slate-800 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                            >
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-700 sm:text-xs">
                                Sifat Default
                                <span class="text-rose-500">*</span>
                            </label>

                            <select
                                name="sifat"
                                class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-xs text-slate-800 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                            >
                                @foreach(['biasa','penting','rahasia','segera'] as $s)
                                    <option
                                        value="{{ $s }}"
                                        @selected(strtolower($k->sifat ?? '') === $s)
                                    >
                                        {{ ucfirst($s) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                    </div>

                    {{-- KETERANGAN --}}
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-700 sm:text-xs">
                            Keterangan
                            <span class="font-normal lowercase text-slate-400">
                                (opsional)
                            </span>
                        </label>

                        <textarea
                            name="keterangan"
                            rows="3"
                            class="w-full resize-none rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-800 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                        >{{ $k->keterangan }}</textarea>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="flex flex-col-reverse gap-2 border-t border-slate-300 bg-slate-50 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-end sm:px-6 sm:py-4">

                    <button
                        type="button"
                        onclick="document.getElementById('modalEdit{{ $k->id }}').classList.add('hidden')"
                        class="w-full rounded-xl px-4 py-2.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200 hover:text-slate-800 sm:w-auto sm:text-sm"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-blue-700 sm:w-auto sm:text-sm"
                    >
                        Simpan Perubahan
                    </button>

                </div>
            </form>

        </div>
    </div>
</div>

@endforeach

{{-- ================================================================
     MODAL TAMBAH
================================================================ --}}

<div
    id="modalTambah"
    class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/50 p-3 backdrop-blur-sm sm:p-4"
>
    <div class="relative mx-auto my-auto flex min-h-full w-full max-w-lg items-center justify-center">

        <div class="relative w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">

            {{-- HEADER --}}
            <div class="flex items-center justify-between border-b border-slate-300 bg-slate-50 px-4 py-3.5 sm:px-6 sm:py-4">

                <div class="flex min-w-0 items-center gap-2.5 sm:gap-3">

                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 text-blue-600 sm:h-10 sm:w-10">
                        <svg
                            class="h-4 w-4 sm:h-5 sm:w-5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M7 7h10M7 11h10M7 15h4M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"
                            />
                        </svg>
                    </div>

                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-bold text-slate-800 sm:text-base">
                            Tambah Kategori Surat
                        </h3>

                        <p class="truncate text-[11px] text-slate-500 sm:text-xs">
                            Isi formulir untuk menambahkan kategori baru.
                        </p>
                    </div>

                </div>

                <button
                    type="button"
                    onclick="document.getElementById('modalTambah').classList.add('hidden')"
                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
                    aria-label="Tutup"
                >
                    <svg
                        class="h-5 w-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

            </div>

            {{-- FORM --}}
            <form
                method="POST"
                action="{{ route('kategori.store') }}"
            >
                @csrf

                <div class="space-y-4 p-4 sm:p-6">

                    {{-- NAMA --}}
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-700 sm:text-xs">
                            Nama Kategori
                            <span class="text-rose-500">*</span>
                        </label>

                        <input
                            type="text"
                            name="nama_kategori"
                            placeholder="Contoh: Surat Undangan, Surat Perjanjian"
                            required
                            class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-xs text-slate-800 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                        >
                    </div>

                    {{-- KODE + SIFAT --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-700 sm:text-xs">
                                Kode Surat
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                type="text"
                                name="kode"
                                placeholder="Contoh: SK, UND, MOU"
                                required
                                class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 font-mono text-xs uppercase text-slate-800 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                            >
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-700 sm:text-xs">
                                Sifat Default
                                <span class="text-rose-500">*</span>
                            </label>

                            <select
                                name="sifat"
                                class="h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-xs text-slate-800 outline-none transition-all focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                            >
                                <option value="biasa">Biasa</option>
                                <option value="penting">Penting</option>
                                <option value="rahasia">Rahasia</option>
                                <option value="segera">Segera</option>
                            </select>
                        </div>

                    </div>

                    {{-- KETERANGAN --}}
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-slate-700 sm:text-xs">
                            Keterangan
                            <span class="font-normal lowercase text-slate-400">
                                (opsional)
                            </span>
                        </label>

                        <textarea
                            name="keterangan"
                            rows="3"
                            placeholder="Penjelasan singkat mengenai jenis kategori ini..."
                            class="w-full resize-none rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs text-slate-800 outline-none transition-all placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 sm:text-sm"
                        ></textarea>
                    </div>

                </div>

                {{-- FOOTER --}}
                <div class="flex flex-col-reverse gap-2 border-t border-slate-300 bg-slate-50 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-end sm:px-6 sm:py-4">

                    <button
                        type="button"
                        onclick="document.getElementById('modalTambah').classList.add('hidden')"
                        class="w-full rounded-xl px-4 py-2.5 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-200 hover:text-slate-800 sm:w-auto sm:text-sm"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-blue-700 sm:w-auto sm:text-sm"
                    >
                        Simpan Data
                    </button>

                </div>
            </form>

        </div>
    </div>
</div>

@push('styles')
<style>
/* ==========================================================================
   KATEGORI TABLE
   ========================================================================== */

.kategori-table-wrapper {
    overflow: hidden;
    border: 1px solid #94a3b8;
    border-radius: 16px;
    background: #fff;
    box-shadow:
        0 1px 3px rgba(15,23,42,.06),
        0 8px 24px rgba(15,23,42,.04);
}

.kategori-table-scroll {
    overflow-x: auto;
}

.kategori-table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
    border-spacing: 0;
    background: #fff;
}

.kategori-table thead {
    background: #f1f5f9;
}

.kategori-table thead th {
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

.kategori-table thead th:last-child {
    border-right: 0;
}

.kategori-table tbody tr {
    background: #fff;
    transition: background-color .15s ease;
}

.kategori-table tbody tr:nth-child(even) {
    background: #f8fafc;
}

.kategori-table tbody tr:hover {
    background: #eff6ff;
}

.kategori-table tbody td {
    padding: 14px 16px;
    border-right: 1px solid #cbd5e1;
    border-bottom: 1px solid #cbd5e1;
    color: #475569;
    font-size: 12px;
    line-height: 1.5;
    vertical-align: middle;
}

.kategori-table tbody td:last-child {
    border-right: 0;
}

.kategori-table tbody tr:last-child td {
    border-bottom: 0;
}

.category-name-column {
    min-width: 220px;
}

.action-column {
    width: 110px;
}

.category-name {
    color: #1e293b;
    font-size: 12px;
    font-weight: 700;
}

.category-description {
    max-width: 300px;
    margin-top: 3px;
    overflow: hidden;
    color: #94a3b8;
    font-size: 10px;
    line-height: 1.4;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.category-code {
    display: inline-flex;
    align-items: center;
    min-height: 25px;
    padding: 0 9px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #f1f5f9;
    color: #334155;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: 10px;
    font-weight: 700;
}

.category-status {
    display: inline-flex;
    align-items: center;
    min-height: 25px;
    border-width: 1px;
    border-radius: 999px;
    padding: 0 9px;
    font-size: 10px;
    font-weight: 800;
}

.category-counter {
    display: inline-flex;
    min-width: 30px;
    height: 28px;
    align-items: center;
    justify-content: center;
    padding: 0 8px;
    border: 1px solid #cbd5e1;
    border-radius: 999px;
    background: #f8fafc;
    color: #334155;
    font-size: 10px;
    font-weight: 800;
}

.category-actions {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
}

.category-action {
    display: inline-flex;
    width: 30px;
    height: 30px;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    color: #64748b;
    transition:
        background-color .15s ease,
        color .15s ease;
}

.category-action.edit:hover {
    background: #fef3c7;
    color: #d97706;
}

.category-action.delete:hover {
    background: #ffe4e6;
    color: #e11d48;
}

.category-empty {
    padding: 48px 16px;
    text-align: center;
}

/* ==========================================================================
   MOBILE
   ========================================================================== */

@media (max-width: 767px) {
    .kategori-table {
        min-width: 850px;
    }

    .kategori-table thead th,
    .kategori-table tbody td {
        padding: 12px 13px;
    }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    'use strict';

    function closeModalOnEscape() {
        document.addEventListener(
            'keydown',
            function (event) {
                if (event.key !== 'Escape') {
                    return;
                }

                document
                    .querySelectorAll(
                        '[id^="modalEdit"], #modalTambah'
                    )
                    .forEach(
                        function (modal) {
                            modal.classList.add(
                                'hidden'
                            );
                        }
                    );
            }
        );
    }

    function initializeDeleteConfirmation() {
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

                            if (
                                typeof window.Swal !==
                                'undefined'
                            ) {
                                window.Swal.fire({
                                    title:
                                        'Hapus Kategori?',
                                    text:
                                        'Data kategori yang dihapus akan dipindahkan ke tempat sampah.',
                                    icon:
                                        'warning',
                                    showCancelButton:
                                        true,
                                    confirmButtonColor:
                                        '#ef4444',
                                    cancelButtonColor:
                                        '#64748b',
                                    confirmButtonText:
                                        'Ya, Hapus!',
                                    cancelButtonText:
                                        'Batal',
                                    reverseButtons:
                                        true,
                                    customClass: {
                                        popup:
                                            'rounded-2xl',
                                        confirmButton:
                                            'rounded-xl text-xs font-semibold px-4 py-2.5',
                                        cancelButton:
                                            'rounded-xl text-xs font-semibold px-4 py-2.5'
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

                                return;
                            }

                            if (
                                window.confirm(
                                    'Yakin ingin menghapus kategori ini?'
                                )
                            ) {
                                form.submit();
                            }
                        }
                    );
                }
            );
    }

    closeModalOnEscape();
    initializeDeleteConfirmation();

})();
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection