@extends('layouts.app')

@section('title', 'Catat Surat Keluar')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-12">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Catat Surat Keluar</h1>
            <p class="text-sm text-slate-500 mt-0.5">Isi formulir di bawah ini untuk menambahkan data arsip surat keluar baru.</p>
        </div>
        <a href="{{ route('surat-keluar.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded-xl shadow-xs hover:bg-slate-50 hover:text-slate-800 focus:outline-none transition w-full sm:w-auto">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('surat-keluar.store') }}" enctype="multipart/form-data" id="form-surat">
        @csrf

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            
            <div class="p-6 sm:p-8 space-y-8">

                <!-- Section 1: Detail Surat -->
                <div class="space-y-5">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <div class="w-2 h-5 bg-blue-600 rounded-full"></div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">Informasi Utama Surat</h2>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <!-- Nomor Surat -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nomor Surat <span class="text-rose-500">*</span></label>
                            <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}" required placeholder="Contoh: 005/SK/I/2026"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 @error('nomor_surat') !border-rose-500 !bg-rose-50/30 @enderror">
                            @error('nomor_surat') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Instansi / Tujuan Pengiriman (Bisa Diketik Teks Bebas) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Instansi Tujuan <span class="text-rose-500">*</span></label>
                            <input type="text" name="pengirim" value="{{ old('pengirim') }}" required placeholder="Contoh: PT Maju Takgentar"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 @error('pengirim') !border-rose-500 !bg-rose-50/30 @enderror">
                            @error('pengirim') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tanggal Surat -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal Surat <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', date('Y-m-d')) }}" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 font-medium @error('tanggal_surat') !border-rose-500 !bg-rose-50/30 @enderror">
                            @error('tanggal_surat') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Tanggal Dikirim -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tanggal Dikirim <span class="text-rose-500">*</span></label>
                            <input type="date" name="tanggal_keluar" value="{{ old('tanggal_keluar', date('Y-m-d')) }}" required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 font-medium @error('tanggal_keluar') !border-rose-500 !bg-rose-50/30 @enderror">
                            @error('tanggal_keluar') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Kategori Surat -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kategori Surat <span class="text-rose-500">*</span></label>
                            <select name="kategori_surat_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 font-medium @error('kategori_surat_id') !border-rose-500 !bg-rose-50/30 @enderror">
                                <option value="" disabled selected>Pilih kategori surat</option>
                                @foreach($kategoris as $k)
                                    <option value="{{ $k->id }}" {{ old('kategori_surat_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama_kategori }} @if(isset($k->sifat))({{ ucfirst($k->sifat) }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_surat_id') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Status Surat <span class="text-rose-500">*</span></label>
                            <select name="status" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3.5 py-2.5 text-sm text-slate-700 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 font-medium @error('status') !border-rose-500 !bg-rose-50/30 @enderror">
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="diproses" {{ old('status', 'diproses') == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                <option value="disetujui" {{ old('status', 'disetujui') == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                <option value="dikirim" {{ old('status', 'dikirim') == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
                                <option value="diarsipkan" {{ old('status') == 'diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
                            </select>
                            @error('status') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Perihal -->
                    <div class="mt-5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Perihal / Isi Ringkas <span class="text-rose-500">*</span></label>
                        <textarea name="perihal" rows="3" required placeholder="Tuliskan perihal atau isi ringkas surat secara jelas..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 text-sm text-slate-700 placeholder-slate-400 focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition duration-150 @error('perihal') !border-rose-500 !bg-rose-50/30 @enderror">{{ old('perihal') }}</textarea>
                        @error('perihal') <p class="text-rose-500 text-xs mt-1.5 font-medium">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Section 2: Lampiran Dokumen Digital -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <div class="w-2 h-5 bg-indigo-600 rounded-full"></div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-600">Lampiran Berkas Digital</h2>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                            Upload Dokumen Surat <span class="text-slate-400 font-normal normal-case">(Opsional)</span>
                        </label>
                        
                        <label class="relative flex items-center justify-between border border-dashed border-slate-300 rounded-xl bg-slate-50/50 hover:bg-slate-50/90 py-3.5 px-4 transition duration-150 group cursor-pointer @error('lampiran_file') !border-rose-500 !bg-rose-50/30 @enderror">
                            <div class="flex items-center space-x-3.5">
                                <div class="w-9 h-9 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 group-hover:scale-105 transition-transform shrink-0 shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                </div>
                                <div class="flex flex-col text-left">
                                    <span class="text-xs font-semibold text-slate-700" id="file-label-text">Pilih atau seret file dokumen ke sini</span>
                                    <span class="text-[11px] text-slate-400">PDF, JPG, JPEG, PNG (Maksimal 10MB)</span>
                                </div>
                            </div>
                            <span class="hidden sm:inline-flex px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 border border-blue-100 rounded-lg group-hover:bg-blue-100 transition shrink-0">
                                Browse File
                            </span>
                            <input type="file" name="lampiran_file" id="lampiran_file" accept=".pdf,.jpg,.jpeg,.png" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" onchange="updateFileName(this)">
                        </label>
                        @error('lampiran_file') <p class="text-rose-500 text-xs font-medium mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-xs">
                        <strong class="font-bold">Terjadi Kesalahan Validasi:</strong>
                        <ul class="mt-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Action Buttons Footer -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 px-6 sm:px-8 py-4 bg-slate-50/80 border-t border-slate-100">
                <a href="{{ route('surat-keluar.index') }}" class="w-full sm:w-auto px-5 py-2.5 text-xs font-semibold text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-100 hover:text-slate-800 transition shadow-xs text-center">
                    Batal
                </a>
                <button type="submit" id="submit-btn" class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700 shadow-md shadow-blue-600/30 transition disabled:opacity-50">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan Surat Keluar
                </button>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
    function updateFileName(input) {
        const textElement = document.getElementById('file-label-text');
        if (input.files && input.files[0]) {
            textElement.textContent = "File terpilih: " + input.files[0].name;
            textElement.classList.add('text-blue-600', 'font-bold');
        } else {
            textElement.textContent = "Pilih atau seret file dokumen ke sini";
            textElement.classList.remove('text-blue-600', 'font-bold');
        }
    }

    document.getElementById('form-surat').addEventListener('submit', function() {
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
        `;
    });
</script>
@endpush
@endsection