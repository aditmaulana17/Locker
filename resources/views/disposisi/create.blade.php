@extends('layouts.app')

@section('title', 'Buat Disposisi Surat')

@section('content')
<div class="max-w-4xl mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6">

    <!-- Flash Error Notification (Blade Validation) -->
    @if ($errors->any())
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-xs transition duration-200">
            <div class="flex items-start gap-3">
                <div class="p-1.5 bg-rose-100 rounded-xl text-rose-600 shrink-0 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-xs sm:text-sm font-bold">Gagal menyimpan disposisi:</h3>
                    <ul class="list-disc list-inside text-xs space-y-0.5 text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Action Bar Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200/80 shadow-xs">
        <div class="flex items-center gap-4">
            <a href="{{ route('surat-masuk.show', $suratMasuk) }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition shrink-0" title="Kembali ke Detail Surat">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-400 font-medium mb-1">
                    <a href="{{ route('surat-masuk.index') }}" class="hover:text-slate-600 transition">Surat Masuk</a>
                    <span>/</span>
                    <a href="{{ route('surat-masuk.show', $suratMasuk) }}" class="hover:text-slate-600 transition">Detail</a>
                    <span>/</span>
                    <span class="text-slate-600">Buat Disposisi</span>
                </nav>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">Buat Disposisi Surat</h1>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('surat-masuk.show', $suratMasuk) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Main Card Container -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 overflow-hidden">
        
        <!-- Header Referensi Dokumen -->
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-5 sm:p-6 text-white border-b border-slate-800">
            <div class="flex items-start gap-4">
                <div class="p-2.5 bg-white/10 rounded-xl text-indigo-400 shrink-0 backdrop-blur-xs border border-white/10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="space-y-1 min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">Referensi Surat</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-white/10 text-indigo-200 border border-white/10">
                            #{{ $suratMasuk->nomor_agenda ? 'AG/' . $suratMasuk->nomor_agenda : $suratMasuk->id }}
                        </span>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold text-white leading-snug break-words">
                        {{ $suratMasuk->perihal ?? 'Tanpa Perihal' }}
                    </h2>
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-300 font-medium pt-0.5">
                        <span>No. Surat: <strong class="text-white">{{ $suratMasuk->nomor_surat ?? '-' }}</strong></span>
                        <span class="text-slate-500">&bull;</span>
                        <span>Pengirim: <strong class="text-white">{{ $suratMasuk->pengirim ?? ($suratMasuk->instansi->nama_instansi ?? '-') }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Disposisi -->
        <form method="POST" action="{{ route('disposisi.store') }}" class="p-6 sm:p-8 space-y-6">
            @csrf
            <input type="hidden" name="surat_masuk_id" value="{{ $suratMasuk->id }}">

            <!-- Tujuan & Penerima Disposisi -->
            <div class="space-y-2">
                <label for="kepada_user_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Disposisikan Kepada <span class="text-rose-500">*</span>
                </label>
                
                @if(isset($users) && count($users) > 0)
                    <select id="kepada_user_id" name="kepada_user_id" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm py-3 px-4 transition text-slate-800 shadow-2xs font-medium">
                        <option value="" disabled selected>-- Pilih Pejabat / Staf Tujuan --</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(old('kepada_user_id') == $u->id)>
                                {{ $u->name }} {{ isset($u->jabatan) && $u->jabatan ? '('.$u->jabatan.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" id="penerima" name="penerima" 
                        value="{{ old('penerima') }}" required 
                        placeholder="Contoh: Sekuriti / Staff Keuangan" 
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm py-3 px-4 transition text-slate-800 shadow-2xs font-medium">
                @endif
                <p class="text-[11px] text-slate-400 font-medium">Pilih personil yang bertanggung jawab menindaklanjuti instruksi surat ini.</p>
            </div>

            <!-- Instruksi Penanganan -->
            <div class="space-y-2">
                <label for="instruksi" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Instruksi Penanganan <span class="text-rose-500">*</span>
                </label>
                <textarea id="instruksi" name="instruksi" rows="3" required placeholder="Contoh: Mohon ditindaklanjuti segera dan koordinasikan dengan tim terkait." class="w-full rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm p-4 transition text-slate-800 placeholder-slate-400 shadow-2xs leading-relaxed">{{ old('instruksi') }}</textarea>
            </div>

            <!-- Catatan Tambahan -->
            <div class="space-y-2">
                <label for="catatan" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Catatan Tambahan <span class="text-slate-400 font-normal lowercase">(opsional)</span>
                </label>
                <textarea id="catatan" name="catatan" rows="2" placeholder="Tuliskan catatan khusus atau petunjuk teknis tambahan..." class="w-full rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm p-4 transition text-slate-800 placeholder-slate-400 shadow-2xs leading-relaxed">{{ old('catatan') }}</textarea>
            </div>

                <!-- Batas Waktu -->
                <div class="space-y-2">
                    <label for="batas_waktu" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                        Batas Waktu Penyelesaian
                    </label>
                    <input type="date" id="batas_waktu" name="batas_waktu" value="{{ old('batas_waktu') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 focus:bg-white focus:border-purple-500 focus:ring-4 focus:ring-purple-500/10 text-xs sm:text-sm py-3 px-4 transition text-slate-800 shadow-2xs font-medium">
                </div>

                <!-- Status Pekerjaan (Radio Buttons konsisten dengan 'diproses') -->
                <div class="space-y-2 sm:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">Status Awal Disposisi <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['menunggu' => 'Menunggu', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $stKey => $stLabel)
                            <label class="flex items-center justify-center p-3 border rounded-xl text-xs sm:text-sm font-semibold cursor-pointer transition has-[:checked]:bg-purple-50 has-[:checked]:border-purple-500 has-[:checked]:text-purple-700 text-slate-600 bg-slate-50/50 border-slate-200 hover:bg-slate-100">
                                <input type="radio" name="status" value="{{ $stKey }}" @checked(old('status', 'menunggu') == $stKey) class="hidden">
                                <span>{{ $stLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

            </div>

            <!-- Footer Action Buttons -->
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-6 border-t border-slate-100 mt-6">
                <a href="{{ route('surat-masuk.show', $suratMasuk) }}" class="w-full sm:w-auto text-center px-5 py-2.5 text-xs font-semibold rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                    Batal
                </a>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 text-xs font-bold rounded-xl bg-purple-600 hover:bg-purple-700 text-white shadow-xs transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    <span>Kirim Disposisi</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection