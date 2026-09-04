@extends('layouts.app')
@section('title', 'Kategori Surat')

@section('content')
<div class="space-y-4 sm:space-y-6">

    <!-- HEADER & TOMBOL TAMBAH -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">Kategori Surat</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-0.5 sm:mt-1">Kelola master data kategori dan klasifikasi jenis surat.</p>
        </div>

        <button type="button" 
                onclick="document.getElementById('modalTambah').classList.remove('hidden')" 
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs sm:text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 rounded-xl transition-colors shadow-sm w-full sm:w-auto">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Kategori</span>
        </button>
    </div>

    <!-- FILTER & SEARCH SECTION -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-3 sm:p-4">
        <form method="GET" action="{{ route('kategori.index') }}" class="flex flex-col sm:flex-row items-center gap-2.5 sm:gap-3">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Cari nama kategori atau kode..." 
                       class="w-full pl-9 pr-3 py-2 rounded-lg border-slate-300 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-initial px-5 py-2 bg-slate-900 text-white rounded-lg text-xs sm:text-sm font-medium hover:bg-slate-800 transition-colors shadow-sm text-center">
                    Cari
                </button>

                @if(request()->filled('search'))
                    <a href="{{ route('kategori.index') }}" class="px-3.5 py-2 bg-slate-100 text-slate-600 rounded-lg text-xs sm:text-sm font-medium hover:bg-slate-200 transition-colors border border-slate-200 text-center">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- TABEL DATA -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs sm:text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase text-[10px] sm:text-xs tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="px-3.5 sm:px-5 py-3 sm:py-4 min-w-[160px]">Nama Kategori</th>
                        <th class="px-3.5 sm:px-5 py-3 sm:py-4">Kode</th>
                        <th class="px-3.5 sm:px-5 py-3 sm:py-4">Sifat</th>
                        <th class="px-3.5 sm:px-5 py-3 sm:py-4 text-center">Jml Surat Masuk</th>
                        <th class="px-3.5 sm:px-5 py-3 sm:py-4 text-center">Jml Surat Keluar</th>
                        <th class="px-3.5 sm:px-5 py-3 sm:py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($kategoris as $k)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <!-- Nama Kategori & Keterangan -->
                        <td class="px-3.5 sm:px-5 py-3 sm:py-4">
                            <div class="font-semibold text-slate-800">{{ $k->nama_kategori }}</div>
                            @if($k->keterangan)
                                <div class="text-[11px] sm:text-xs text-slate-400 truncate max-w-xs mt-0.5" title="{{ $k->keterangan }}">{{ $k->keterangan }}</div>
                            @endif
                        </td>

                        <!-- Kode -->
                        <td class="px-3.5 sm:px-5 py-3 sm:py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] sm:text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200/80 font-mono">
                                {{ $k->kode }}
                            </span>
                        </td>

                        <!-- Sifat -->
                        <td class="px-3.5 sm:px-5 py-3 sm:py-4 whitespace-nowrap">
                            @php
                                $sifatClass = match(strtolower($k->sifat ?? '')) {
                                    'segera'   => 'bg-rose-50 text-rose-700 border-rose-200',
                                    'penting'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'rahasia'  => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'biasa'    => 'bg-blue-50 text-blue-700 border-blue-200',
                                    default    => 'bg-slate-100 text-slate-600 border-slate-200'
                                };
                            @endphp
                            <span class="text-[11px] sm:text-xs font-semibold px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-full border {{ $sifatClass }}">
                                {{ ucfirst($k->sifat ?? 'Biasa') }}
                            </span>
                        </td>

                        <!-- Jml Surat Masuk -->
                        <td class="px-3.5 sm:px-5 py-3 sm:py-4 text-center whitespace-nowrap">
                            <span class="inline-flex items-center justify-center min-w-[24px] sm:min-w-[28px] h-6 sm:h-7 px-1.5 sm:px-2 text-[11px] sm:text-xs font-semibold rounded-full bg-slate-50 text-slate-700 border border-slate-200">
                                {{ $k->surat_masuk_count ?? 0 }}
                            </span>
                        </td>

                        <!-- Jml Surat Keluar -->
                        <td class="px-3.5 sm:px-5 py-3 sm:py-4 text-center whitespace-nowrap">
                            <span class="inline-flex items-center justify-center min-w-[24px] sm:min-w-[28px] h-6 sm:h-7 px-1.5 sm:px-2 text-[11px] sm:text-xs font-semibold rounded-full bg-slate-50 text-slate-700 border border-slate-200">
                                {{ $k->surat_keluar_count ?? 0 }}
                            </span>
                        </td>

                        <!-- Aksi -->
                        <td class="px-3.5 sm:px-5 py-3 sm:py-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center justify-end gap-1">
                                <!-- Edit Button -->
                                <button type="button" 
                                        onclick="document.getElementById('modalEdit{{ $k->id }}').classList.remove('hidden')" 
                                        class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" 
                                        title="Ubah Kategori">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <!-- Delete Form (SweetAlert2) -->
                                <form action="{{ route('kategori.destroy', $k) }}" method="POST" class="inline delete-form">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="button" 
                                            class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors delete-btn" 
                                            title="Hapus Kategori">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-12 sm:py-16 px-4">
                            <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mb-3 sm:mb-4 border border-slate-200">
                                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h4M5 3h14a2 2 0 012-2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                                </div>
                                <h3 class="text-sm sm:text-base font-semibold text-slate-800">Belum ada kategori surat</h3>
                                <p class="text-slate-500 text-xs mt-1 mb-4 sm:mb-5 text-center">Silakan tambahkan data kategori surat baru.</p>
                                <button type="button" 
                                        onclick="document.getElementById('modalTambah').classList.remove('hidden')" 
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
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

    <!-- PAGINATION -->
    @if(isset($kategoris) && method_exists($kategoris, 'links'))
    <div class="pt-2">
        {{ $kategoris->withQueryString()->links() }}
    </div>
    @endif

</div>

<!-- ========================================== -->
<!-- MODAL EDIT (LOOP DI LUAR TABEL) -->
<!-- ========================================== -->
@foreach($kategoris as $k)
<div id="modalEdit{{ $k->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden transform transition-all my-auto">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="p-1.5 sm:p-2 bg-amber-50 text-amber-600 rounded-xl border border-amber-100 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-800">Ubah Kategori Surat</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500">Perbarui rincian informasi kategori surat.</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalEdit{{ $k->id }}').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Form Edit -->
        <form method="POST" action="{{ route('kategori.update', $k) }}">
            @csrf 
            @method('PUT')
            
            <div class="p-4 sm:p-6 space-y-3.5 sm:space-y-4">
                <div>
                    <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Nama Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_kategori" value="{{ $k->nama_kategori }}" required class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Kode Surat <span class="text-rose-500">*</span></label>
                        <input type="text" name="kode" value="{{ $k->kode }}" required class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono text-slate-800 uppercase focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Sifat Default <span class="text-rose-500">*</span></label>
                        <select name="sifat" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            @foreach(['biasa','penting','rahasia','segera'] as $s)
                                <option value="{{ $s }}" @selected(strtolower($k->sifat ?? '') == $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Keterangan <span class="text-slate-400 font-normal lowercase">(opsional)</span></label>
                    <textarea name="keterangan" rows="3" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none">{{ $k->keterangan }}</textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 px-4 sm:px-6 py-3.5 sm:py-4 border-t border-slate-100 bg-slate-50/50">
                <button type="button" onclick="document.getElementById('modalEdit{{ $k->id }}').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors text-center">Batal</button>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 rounded-xl transition-all shadow-sm">Simpan Perubahan</button>
            </div>
        </form>

    </div>
</div>
@endforeach

<!-- ========================================== -->
<!-- MODAL TAMBAH -->
<!-- ========================================== -->
<div id="modalTambah" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden transform transition-all my-auto">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="p-1.5 sm:p-2 bg-blue-50 text-blue-600 rounded-xl border border-blue-100 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h4M5 3h14a2 2 0 012-2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-800">Tambah Kategori Surat</h3>
                    <p class="text-[11px] sm:text-xs text-slate-500">Isi formulir untuk menambahkan kategori baru.</p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Form Tambah -->
        <form method="POST" action="{{ route('kategori.store') }}">
            @csrf
            
            <div class="p-4 sm:p-6 space-y-3.5 sm:space-y-4">
                <div>
                    <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Nama Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_kategori" placeholder="Contoh: Surat Undangan, Surat Perjanjian" required class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 sm:gap-4">
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Kode Surat <span class="text-rose-500">*</span></label>
                        <input type="text" name="kode" placeholder="Contoh: SK, UND, MOU" required class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-mono text-slate-800 uppercase focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                    </div>
                    <div>
                        <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Sifat Default <span class="text-rose-500">*</span></label>
                        <select name="sifat" class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all">
                            <option value="biasa">Biasa</option>
                            <option value="penting">Penting</option>
                            <option value="rahasia">Rahasia</option>
                            <option value="segera">Segera</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] sm:text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 sm:mb-1.5">Keterangan <span class="text-slate-400 font-normal lowercase">(opsional)</span></label>
                    <textarea name="keterangan" rows="3" placeholder="Penjelasan singkat mengenai jenis kategori ini..." class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all resize-none"></textarea>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 px-4 sm:px-6 py-3.5 sm:py-4 border-t border-slate-100 bg-slate-50/50">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="w-full sm:w-auto px-4 py-2 sm:py-2.5 text-xs sm:text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors text-center">Batal</button>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-5 py-2 sm:py-2.5 text-xs sm:text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 rounded-xl transition-all shadow-sm">Simpan Data</button>
            </div>
        </form>

    </div>
</div>
@endsection