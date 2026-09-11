@extends('layouts.app')

@section('title', 'Catat Surat Masuk')

@section('content')
@php
$formErrors = $errors ?? session('errors');

```
if (!$formErrors || !is_object($formErrors) || !method_exists($formErrors, 'any')) {
    $formErrors = new \Illuminate\Support\ViewErrorBag();
    $messageBag = session('errors');

    if ($messageBag instanceof \Illuminate\Support\MessageBag) {
        $formErrors->put('default', $messageBag);
    }
}

$inputClass = 'block w-full rounded-lg border-2 border-slate-400 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 hover:border-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-100';
$errorInputClass = 'border-rose-400 focus:border-rose-500 focus:ring-rose-100';
```

@endphp

<div class="mx-auto w-full max-w-5xl px-2 sm:px-0">
    {{-- HEADER --}}
    <div class="mb-4 rounded-xl border-2 border-slate-400 bg-white shadow-sm">
        <div class="flex flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border-2 border-blue-200 bg-blue-50 text-blue-600">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m-6-8h6M6 3h9l3 3v15H6a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg font-bold tracking-tight text-slate-800">Catat Surat Masuk</h1>
                    <p class="text-xs text-slate-500">Tambahkan data surat masuk dan arsip digital ke dalam sistem.</p>
                </div>
            </div>
            <a href="{{ route('surat-masuk.index') }}" class="inline-flex w-full shrink-0 items-center justify-center rounded-lg border-2 border-slate-400 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 shadow-sm transition hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700 sm:w-auto">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </div>

```
{{-- ERROR --}}
@if($formErrors->any())
    <div class="mb-4 rounded-xl border-2 border-rose-300 bg-rose-50 px-3.5 py-3 shadow-sm">
        <div class="flex items-start gap-2.5">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-rose-200 bg-rose-100 text-rose-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86l-8.82 15A2 2 0 003.2 21.86h17.6a2 2 0 001.73-3l-8.82-15a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-rose-700">Data belum dapat disimpan.</p>
                <ul class="mt-1 space-y-0.5 text-xs leading-relaxed text-rose-600">
                    @foreach($formErrors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

{{-- FORM --}}
<form id="form-surat" method="POST" action="{{ route('surat-masuk.store') }}" enctype="multipart/form-data" novalidate>
    @csrf
    <input type="hidden" name="nomor_agenda" value="{{ old('nomor_agenda', $nomorAgenda ?? '') }}">

    <div class="overflow-hidden rounded-xl border-2 border-slate-400 bg-white shadow-sm">
        {{-- AGENDA --}}
        <div class="border-b-2 border-blue-300 bg-gradient-to-r from-blue-50 to-indigo-50 px-4 py-3">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border-2 border-blue-300 bg-white text-blue-600 shadow-sm">
                        <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 003 3h0a3 3 0 003-3M9 5a3 3 0 01-3-3h0a3 3 0 013 3m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold uppercase tracking-wider text-blue-600">Nomor Agenda Sistem</p>
                        <p class="font-mono text-sm font-bold text-blue-900">{{ $nomorAgenda ?? '-' }}</p>
                    </div>
                </div>
                <div class="hidden rounded-lg border border-blue-300 bg-white/80 px-3 py-1.5 text-right sm:block">
                    <p class="text-[9px] font-semibold uppercase tracking-wide text-blue-500">Sistem</p>
                    <p class="text-[10px] font-medium text-blue-700">Dibuat otomatis</p>
                </div>
            </div>
        </div>

        {{-- BODY --}}
        <div class="p-3.5 sm:p-4">
            {{-- INFORMASI UTAMA --}}
            <section>
                <div class="mb-3 flex items-start gap-2.5 border-b-2 border-slate-300 pb-2.5">
                    <div class="mt-0.5 h-7 w-1.5 shrink-0 rounded-full bg-blue-600"></div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Informasi Utama Surat</h2>
                        <p class="text-[11px] text-slate-500">Lengkapi identitas dan informasi utama surat masuk.</p>
                    </div>
                </div>

                <div class="overflow-hidden rounded-lg border-2 border-slate-400">
                    <div class="grid grid-cols-1 md:grid-cols-2">
                        {{-- NOMOR SURAT --}}
                        <div class="border-b-2 border-slate-300 p-3 md:border-r-2">
                            <label for="nomor_surat" class="mb-1 block text-xs font-bold text-slate-700">
                                Nomor Surat <span class="text-rose-500">*</span>
                            </label>
                            <input id="nomor_surat" name="nomor_surat" type="text" value="{{ old('nomor_surat') }}" placeholder="Contoh: 005/B/I/2026" autocomplete="off" required class="{{ $inputClass }} {{ $formErrors->has('nomor_surat') ? $errorInputClass : '' }}">
                            @if($formErrors->has('nomor_surat'))
                                <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $formErrors->first('nomor_surat') }}</p>
                            @endif
                        </div>

                        {{-- PENGIRIM --}}
                        <div class="border-b-2 border-slate-300 p-3">
                            <label for="pengirim" class="mb-1 block text-xs font-bold text-slate-700">
                                Instansi Pengirim <span class="text-rose-500">*</span>
                            </label>
                            <input id="pengirim" name="pengirim" type="text" value="{{ old('pengirim') }}" placeholder="Masukkan nama instansi pengirim..." autocomplete="organization" required class="{{ $inputClass }} {{ $formErrors->has('pengirim') ? $errorInputClass : '' }}">
                            @if($formErrors->has('pengirim'))
                                <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $formErrors->first('pengirim') }}</p>
                            @endif
                        </div>

                        {{-- TANGGAL SURAT --}}
                        <div class="border-b-2 border-slate-300 p-3 md:border-r-2">
                            <label for="tanggal_surat" class="mb-1 block text-xs font-bold text-slate-700">
                                Tanggal Surat <span class="text-rose-500">*</span>
                            </label>
                            <input id="tanggal_surat" name="tanggal_surat" type="date" value="{{ old('tanggal_surat') }}" required class="{{ $inputClass }} {{ $formErrors->has('tanggal_surat') ? $errorInputClass : '' }}">
                            @if($formErrors->has('tanggal_surat'))
                                <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $formErrors->first('tanggal_surat') }}</p>
                            @endif
                        </div>

                        {{-- TANGGAL TERIMA --}}
                        <div class="border-b-2 border-slate-300 p-3">
                            <label for="tanggal_terima" class="mb-1 block text-xs font-bold text-slate-700">
                                Tanggal Diterima <span class="text-rose-500">*</span>
                            </label>
                            <input id="tanggal_terima" name="tanggal_terima" type="date" value="{{ old('tanggal_terima', now()->format('Y-m-d')) }}" required class="{{ $inputClass }} {{ $formErrors->has('tanggal_terima') ? $errorInputClass : '' }}">
                            @if($formErrors->has('tanggal_terima'))
                                <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $formErrors->first('tanggal_terima') }}</p>
                            @endif
                        </div>

                        {{-- KATEGORI --}}
                        <div class="border-b-2 border-slate-300 p-3 md:border-r-2">
                            <label for="kategori_surat_id" class="mb-1 block text-xs font-bold text-slate-700">
                                Kategori Surat <span class="text-rose-500">*</span>
                            </label>
                            <select id="kategori_surat_id" name="kategori_surat_id" required class="{{ $inputClass }} {{ $formErrors->has('kategori_surat_id') ? $errorInputClass : '' }}">
                                <option value="">Pilih kategori surat</option>
                                @foreach(($kategoris ?? collect()) as $kategori)
                                    <option value="{{ $kategori->id }}" @selected((string) old('kategori_surat_id') === (string) $kategori->id)>
                                        {{ $kategori->nama_kategori }}@if(!empty($kategori->sifat)) ({{ ucfirst($kategori->sifat) }})@endif
                                    </option>
                                @endforeach
                            </select>
                            @if($formErrors->has('kategori_surat_id'))
                                <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $formErrors->first('kategori_surat_id') }}</p>
                            @endif
                        </div>

                        {{-- STATUS --}}
                        <div class="border-b-2 border-slate-300 p-3">
                            <label for="status" class="mb-1 block text-xs font-bold text-slate-700">
                                Status Surat <span class="text-rose-500">*</span>
                            </label>
                            <select id="status" name="status" required class="{{ $inputClass }} {{ $formErrors->has('status') ? $errorInputClass : '' }}">
                                @foreach(['baru' => 'Baru', 'diproses' => 'Diproses', 'didisposisikan' => 'Didisposisikan', 'selesai' => 'Selesai', 'diarsipkan' => 'Diarsipkan'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', 'baru') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @if($formErrors->has('status'))
                                <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $formErrors->first('status') }}</p>
                            @endif
                        </div>

                        {{-- PERIHAL --}}
                        <div class="border-b-2 border-slate-300 p-3 md:col-span-2">
                            <label for="perihal" class="mb-1 block text-xs font-bold text-slate-700">
                                Perihal <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="perihal" name="perihal" rows="2" required placeholder="Tuliskan perihal surat secara jelas..." class="{{ $inputClass }} resize-y">{{ old('perihal') }}</textarea>
                            @if($formErrors->has('perihal'))
                                <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $formErrors->first('perihal') }}</p>
                            @endif
                        </div>

                        {{-- RINGKASAN --}}
                        <div class="p-3 md:col-span-2">
                            <label for="ringkasan" class="mb-1 block text-xs font-bold text-slate-700">
                                Ringkasan <span class="font-normal text-slate-400">(Opsional)</span>
                            </label>
                            <textarea id="ringkasan" name="ringkasan" rows="2" placeholder="Tuliskan ringkasan isi surat jika diperlukan..." class="{{ $inputClass }} resize-y">{{ old('ringkasan') }}</textarea>
                            @if($formErrors->has('ringkasan'))
                                <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $formErrors->first('ringkasan') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <div class="my-4 border-t-2 border-slate-300"></div>

            {{-- LAMPIRAN --}}
            <section>
                <div class="mb-3 flex items-start gap-2.5 border-b-2 border-slate-300 pb-2.5">
                    <div class="mt-0.5 h-7 w-1.5 shrink-0 rounded-full bg-indigo-600"></div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Lampiran Dokumen & Arsip Fisik</h2>
                        <p class="text-[11px] text-slate-500">Upload dokumen digital atau scan dokumen menggunakan kamera.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    {{-- BERKAS DIGITAL --}}
                    <div class="overflow-hidden rounded-lg border-2 border-slate-400 bg-white shadow-sm">
                        <div class="border-b-2 border-blue-300 bg-blue-50 px-3 py-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-blue-300 bg-white text-blue-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3h7l4 4v14H7a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14 3v5h5"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-bold text-slate-800">Berkas Digital</h3>
                                        <p class="text-[10px] text-slate-500">PDF, JPG, JPEG, PNG</p>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full border border-rose-200 bg-rose-100 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-rose-600">Wajib</span>
                            </div>
                        </div>

                        <div class="p-3">
                            <div class="mb-2.5 flex items-start gap-2 rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-2">
                                <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                                </svg>
                                <div class="text-[10px] leading-relaxed">
                                    <p class="font-bold text-blue-700">Maksimal 10 MB</p>
                                    <p class="text-blue-600">PDF tanpa kompresi. Gambar otomatis dikompres.</p>
                                </div>
                            </div>

                            <div class="mb-2.5 grid grid-cols-2 gap-1.5">
                                <button type="button" id="btn-upload" aria-selected="true" class="flex items-center justify-center gap-1.5 rounded-lg border-2 border-blue-600 bg-blue-600 px-2.5 py-1.5 text-[10px] font-bold text-white shadow-sm transition hover:bg-blue-700">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 12V4m0 0L8 8m4-4l4 4"/>
                                    </svg>
                                    Upload File
                                </button>
                                <button type="button" id="btn-camera" aria-selected="false" class="flex items-center justify-center gap-1.5 rounded-lg border-2 border-slate-400 bg-white px-2.5 py-1.5 text-[10px] font-bold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8h2l2-3h10l2 3h2a2 2 0 012 2v9a2 2 0 01-2 2H3a2 2 0 01-2-2v-9a2 2 0 012-2zm9 3a3 3 0 100 6 3 3 0 000-6z"/>
                                    </svg>
                                    Scan Kamera
                                </button>
                            </div>

                            {{-- UPLOAD PANEL --}}
                            <div id="upload-panel">
                                <label id="upload-box" for="lampiran_file" class="flex min-h-[125px] cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-400 bg-slate-50 px-3 py-4 text-center transition hover:border-blue-400 hover:bg-blue-50">
                                    <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-lg border border-blue-200 bg-blue-100 text-blue-600">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-700">Klik untuk memilih file</span>
                                    <span class="mt-0.5 text-[10px] text-slate-500">PDF, JPG, JPEG, PNG</span>
                                    <span class="mt-1 rounded-full bg-blue-100 px-2 py-0.5 text-[9px] font-medium text-blue-600">Gambar otomatis dikompres</span>
                                    <input id="lampiran_file" name="lampiran_file" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" class="sr-only">
                                </label>

                                <div id="selected-file" class="mt-2 hidden rounded-lg border-2 border-emerald-300 bg-emerald-50 p-2">
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[10px] font-bold text-emerald-700">File siap diupload</p>
                                            <p id="selected-file-name" class="truncate text-[10px] text-emerald-600"></p>
                                            <p id="selected-file-size" class="text-[9px] text-emerald-500"></p>
                                        </div>
                                        <button type="button" id="clear-file-btn" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border border-emerald-200 bg-white text-emerald-600 transition hover:bg-emerald-100" aria-label="Hapus file">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- CAMERA PANEL --}}
                            <div id="camera-panel" class="hidden">
                                <div class="overflow-hidden rounded-lg border-2 border-slate-500 bg-slate-900">
                                    <div id="camera-container" class="relative aspect-[4/3] w-full">
                                        <video id="video" autoplay muted playsinline class="h-full w-full object-cover"></video>
                                        <img id="image-preview" src="" alt="Preview hasil scan" hidden class="absolute inset-0 h-full w-full bg-slate-900 object-contain">
                                        <div id="camera-placeholder" class="absolute inset-0 flex items-center justify-center bg-slate-900 text-slate-400">
                                            <div class="px-4 text-center">
                                                <svg class="mx-auto mb-2 h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7a2 2 0 012-2h3l1.5-2h5L16 5h3a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                                                    <circle cx="12" cy="12" r="3.5"/>
                                                </svg>
                                                <p id="camera-placeholder-text" class="text-[10px] font-medium">Kamera belum aktif</p>
                                            </div>
                                        </div>
                                        <div id="camera-error" class="absolute inset-x-2 bottom-2 hidden rounded-lg bg-rose-600/95 px-2.5 py-1.5 text-center text-[10px] font-medium text-white" role="alert"></div>
                                    </div>
                                </div>

                                <div class="mt-2 flex flex-wrap justify-center gap-1.5">
                                    <button type="button" id="start-cam-btn" class="rounded-lg border-2 border-blue-600 bg-blue-600 px-3 py-1.5 text-[10px] font-bold text-white transition hover:bg-blue-700">Nyalakan Kamera</button>
                                    <button type="button" id="capture-btn" class="hidden rounded-lg border-2 border-emerald-600 bg-emerald-600 px-3 py-1.5 text-[10px] font-bold text-white transition hover:bg-emerald-700">Ambil Foto</button>
                                    <button type="button" id="retake-btn" class="hidden rounded-lg border-2 border-amber-500 bg-amber-500 px-3 py-1.5 text-[10px] font-bold text-white transition hover:bg-amber-600">Foto Ulang</button>
                                    <button type="button" id="stop-cam-btn" class="hidden rounded-lg border-2 border-rose-600 bg-rose-600 px-3 py-1.5 text-[10px] font-bold text-white transition hover:bg-rose-700">Tutup Kamera</button>
                                </div>

                                <input type="hidden" name="captured_image" id="captured_image" value="{{ old('captured_image') }}">

                                <div id="snapshot-preview" class="{{ old('captured_image') ? '' : 'hidden' }} mt-2 rounded-lg border-2 border-emerald-300 bg-emerald-50 px-2.5 py-1.5 text-center text-[10px] font-bold text-emerald-600">
                                    ✓ Hasil scan berhasil diambil.
                                </div>
                            </div>

                            @if($formErrors->has('lampiran_file'))
                                <p class="mt-1.5 text-[11px] font-medium text-rose-600">{{ $formErrors->first('lampiran_file') }}</p>
                            @endif

                            @if($formErrors->has('captured_image'))
                                <p class="mt-1.5 text-[11px] font-medium text-rose-600">{{ $formErrors->first('captured_image') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- LOKASI ARSIP FISIK --}}
                    <div class="overflow-hidden rounded-lg border-2 border-slate-400 bg-white shadow-sm">
                        <div class="border-b-2 border-indigo-300 bg-indigo-50 px-3 py-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-2.5">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-indigo-300 bg-white text-indigo-600">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-bold text-slate-800">Lokasi Arsip Fisik</h3>
                                        <p class="text-[10px] text-slate-500">Lokasi penyimpanan dokumen fisik</p>
                                    </div>
                                </div>
                                <span class="shrink-0 rounded-full border border-slate-300 bg-white px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-slate-500">Opsional</span>
                            </div>
                        </div>

                        <div class="flex min-h-[260px] flex-col p-3">
                            <div class="flex flex-1 flex-col justify-center rounded-lg border-2 border-dashed border-slate-400 bg-slate-50 p-3.5">
                                <div class="mx-auto w-full max-w-sm text-center">
                                    <div class="mx-auto mb-2.5 flex h-10 w-10 items-center justify-center rounded-lg border-2 border-indigo-200 bg-indigo-100 text-indigo-600">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>

                                    <h4 class="text-xs font-bold text-slate-700">Lokasi Penyimpanan</h4>
                                    <p class="mx-auto mt-1 max-w-xs text-[10px] leading-relaxed text-slate-500">
                                        Masukkan posisi rak, lemari, box, atau map tempat arsip fisik disimpan.
                                    </p>

                                    <div class="mt-3 text-left">
                                        <label for="lokasi_arsip_fisik" class="mb-1 block text-xs font-bold text-slate-700">
                                            Detail Posisi Lemari / Box
                                        </label>
                                        <input id="lokasi_arsip_fisik" name="lokasi_arsip_fisik" type="text" value="{{ old('lokasi_arsip_fisik') }}" placeholder="Contoh: Rak A-3 Box 12" class="{{ $inputClass }} {{ $formErrors->has('lokasi_arsip_fisik') ? $errorInputClass : '' }}">

                                        @if($formErrors->has('lokasi_arsip_fisik'))
                                            <p class="mt-1 text-[11px] font-medium text-rose-600">{{ $formErrors->first('lokasi_arsip_fisik') }}</p>
                                        @endif
                                    </div>

                                    <div class="mt-2.5 rounded-lg border-2 border-slate-300 bg-white px-2.5 py-2 text-left">
                                        <div class="flex items-start gap-1.5">
                                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                                            </svg>
                                            <p class="text-[10px] leading-relaxed text-slate-500">
                                                <span class="font-bold text-slate-700">Contoh:</span>
                                                Rak A-3 Box 12 atau Lemari B-2 Map 07.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        {{-- FOOTER --}}
        <div class="flex flex-col-reverse gap-2 border-t-2 border-slate-400 bg-slate-50 px-3.5 py-3 sm:flex-row sm:items-center sm:justify-end sm:px-4">
            <a href="{{ route('surat-masuk.index') }}" class="inline-flex w-full items-center justify-center rounded-lg border-2 border-slate-400 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-100 sm:w-auto">
                Batal
            </a>

            <button id="submit-btn" type="submit" class="inline-flex w-full items-center justify-center rounded-lg border-2 border-blue-600 bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span id="submit-text">Simpan Surat Masuk</span>
            </button>
        </div>
    </div>
</form>
```

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    const form = document.getElementById('form-surat');
    const uploadBtn = document.getElementById('btn-upload');
    const cameraBtn = document.getElementById('btn-camera');
    const uploadPanel = document.getElementById('upload-panel');
    const cameraPanel = document.getElementById('camera-panel');
    const fileInput = document.getElementById('lampiran_file');
    const uploadBox = document.getElementById('upload-box');
    const selectedFile = document.getElementById('selected-file');
    const selectedFileName = document.getElementById('selected-file-name');
    const selectedFileSize = document.getElementById('selected-file-size');
    const clearFileBtn = document.getElementById('clear-file-btn');
    const video = document.getElementById('video');
    const imagePreview = document.getElementById('image-preview');
    const cameraPlaceholder = document.getElementById('camera-placeholder');
    const cameraPlaceholderText = document.getElementById('camera-placeholder-text');
    const cameraError = document.getElementById('camera-error');
    const startCamBtn = document.getElementById('start-cam-btn');
    const captureBtn = document.getElementById('capture-btn');
    const retakeBtn = document.getElementById('retake-btn');
    const stopCamBtn = document.getElementById('stop-cam-btn');
    const capturedInput = document.getElementById('captured_image');
    const snapshotPreview = document.getElementById('snapshot-preview');
    const submitBtn = document.getElementById('submit-btn');
    const submitText = document.getElementById('submit-text');

    if (!form || !fileInput || !capturedInput) return;

    const CLIENT_MAX_FILE_SIZE = 10 * 1024 * 1024;
    const CLIENT_TARGET_IMAGE_SIZE = 2.5 * 1024 * 1024;
    const MAX_IMAGE_WIDTH = 2200;
    const MAX_IMAGE_HEIGHT = 2200;

    let cameraStream = null;
    let previewObjectUrl = null;
    let isSubmitting = false;

    function formatFileSize(bytes) {
        if (!Number.isFinite(bytes) || bytes <= 0) return '0 KB';
        return bytes < 1024 * 1024
            ? `${(bytes / 1024).toFixed(1)} KB`
            : `${(bytes / (1024 * 1024)).toFixed(2)} MB`;
    }

    function showCameraError(message) {
        if (!cameraError) return;
        cameraError.textContent = message || '';
        cameraError.classList.remove('hidden');
    }

    function clearCameraError() {
        if (!cameraError) return;
        cameraError.textContent = '';
        cameraError.classList.add('hidden');
    }

    function revokePreviewUrl() {
        if (!previewObjectUrl) return;
        URL.revokeObjectURL(previewObjectUrl);
        previewObjectUrl = null;
    }

    function setModeButtonState(uploadActive) {
        if (uploadActive) {
            uploadBtn?.classList.remove('border-slate-400', 'bg-white', 'text-slate-700');
            uploadBtn?.classList.add('border-blue-600', 'bg-blue-600', 'text-white');
            cameraBtn?.classList.remove('border-blue-600', 'bg-blue-600', 'text-white');
            cameraBtn?.classList.add('border-slate-400', 'bg-white', 'text-slate-700');
        } else {
            cameraBtn?.classList.remove('border-slate-400', 'bg-white', 'text-slate-700');
            cameraBtn?.classList.add('border-blue-600', 'bg-blue-600', 'text-white');
            uploadBtn?.classList.remove('border-blue-600', 'bg-blue-600', 'text-white');
            uploadBtn?.classList.add('border-slate-400', 'bg-white', 'text-slate-700');
        }

        uploadBtn?.setAttribute('aria-selected', uploadActive ? 'true' : 'false');
        cameraBtn?.setAttribute('aria-selected', uploadActive ? 'false' : 'true');
    }

    function activateUploadMode() {
        clearCameraError();
        uploadPanel?.classList.remove('hidden');
        cameraPanel?.classList.add('hidden');
        setModeButtonState(true);
        stopCamera();
    }

    function activateCameraMode() {
        clearCameraError();
        uploadPanel?.classList.add('hidden');
        cameraPanel?.classList.remove('hidden');
        setModeButtonState(false);
        clearFileSelection();
    }

    uploadBtn?.addEventListener('click', activateUploadMode);
    cameraBtn?.addEventListener('click', activateCameraMode);

    fileInput.addEventListener('change', async () => {
        clearCameraError();

        const file = fileInput.files?.[0];
        if (!file) return;

        try {
            if (file.type === 'application/pdf') {
                if (file.size > CLIENT_MAX_FILE_SIZE) {
                    clearFileSelection();
                    alert('Ukuran PDF maksimal 10 MB.');
                    return;
                }

                clearCapturedImage();
                showSelectedFile(file, 'PDF disimpan tanpa kompresi');
                return;
            }

            const allowedImageTypes = ['image/jpeg', 'image/jpg', 'image/png'];

            if (!allowedImageTypes.includes(file.type)) {
                clearFileSelection();
                alert('Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.');
                return;
            }

            if (file.size > CLIENT_MAX_FILE_SIZE) {
                clearFileSelection();
                alert('Ukuran gambar maksimal 10 MB.');
                return;
            }

            const originalSize = file.size;
            const compressedFile = await compressImageFile(file);

            if (typeof DataTransfer !== 'undefined') {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(compressedFile);
                fileInput.files = dataTransfer.files;
            }

            clearCapturedImage();

            const reduction = originalSize > 0
                ? Math.max(0, Math.round((1 - compressedFile.size / originalSize) * 100))
                : 0;

            showSelectedFile(compressedFile, `Kompresi browser • ukuran turun ${reduction}%`);
        } catch (error) {
            console.error('Gagal melakukan kompresi:', error);
            clearFileSelection();
            alert(error?.message || 'Gagal memproses gambar.');
        }
    });

    function showSelectedFile(file, note = '') {
        if (!selectedFile) return;

        if (selectedFileName) selectedFileName.textContent = file.name;

        if (selectedFileSize) {
            selectedFileSize.textContent = `${formatFileSize(file.size)}${note ? ` • ${note}` : ''}`;
        }

        selectedFile.classList.remove('hidden');
        uploadBox?.classList.add('border-emerald-400', 'bg-emerald-50');
    }

    function clearFileSelection() {
        if (fileInput) fileInput.value = '';
        selectedFile?.classList.add('hidden');

        if (selectedFileName) selectedFileName.textContent = '';
        if (selectedFileSize) selectedFileSize.textContent = '';

        uploadBox?.classList.remove('border-emerald-400', 'bg-emerald-50');
    }

    clearFileBtn?.addEventListener('click', clearFileSelection);

    function clearCapturedImage() {
        if (capturedInput) capturedInput.value = '';
        snapshotPreview?.classList.add('hidden');
        revokePreviewUrl();

        if (imagePreview) {
            imagePreview.src = '';
            imagePreview.setAttribute('hidden', '');
        }
    }

    function loadImageFromFile(file) {
        return new Promise((resolve, reject) => {
            const objectUrl = URL.createObjectURL(file);
            const image = new Image();

            image.onload = () => {
                URL.revokeObjectURL(objectUrl);
                resolve(image);
            };

            image.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                reject(new Error('Gambar tidak dapat dibaca oleh browser.'));
            };

            image.src = objectUrl;
        });
    }

    async function compressImageFile(file) {
        const image = await loadImageFromFile(file);
        return compressImageElement(image, file.name);
    }

    function compressImageElement(image, originalName = 'lampiran.jpg') {
        return new Promise((resolve, reject) => {
            let width = image.naturalWidth || image.width;
            let height = image.naturalHeight || image.height;

            if (width <= 0 || height <= 0) {
                reject(new Error('Dimensi gambar tidak valid.'));
                return;
            }

            const scale = Math.min(MAX_IMAGE_WIDTH / width, MAX_IMAGE_HEIGHT / height, 1);

            width = Math.max(1, Math.round(width * scale));
            height = Math.max(1, Math.round(height * scale));

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;

            const context = canvas.getContext('2d', { alpha: false });

            if (!context) {
                reject(new Error('Browser tidak mendukung pemrosesan gambar.'));
                return;
            }

            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, width, height);
            context.imageSmoothingEnabled = true;
            context.imageSmoothingQuality = 'high';
            context.drawImage(image, 0, 0, width, height);

            const qualities = [0.78, 0.70, 0.62, 0.54];

            const tryQuality = index => {
                if (index >= qualities.length) {
                    canvas.toBlob(blob => {
                        if (!blob) {
                            reject(new Error('Browser gagal membuat file hasil kompresi.'));
                            return;
                        }

                        resolve(createCompressedFile(blob, originalName));
                    }, 'image/jpeg', 0.48);

                    return;
                }

                canvas.toBlob(blob => {
                    if (!blob) {
                        reject(new Error('Browser gagal membuat file hasil kompresi.'));
                        return;
                    }

                    if (blob.size <= CLIENT_TARGET_IMAGE_SIZE) {
                        resolve(createCompressedFile(blob, originalName));
                        return;
                    }

                    tryQuality(index + 1);
                }, 'image/jpeg', qualities[index]);
            };

            tryQuality(0);
        });
    }

    function createCompressedFile(blob, originalName) {
        const baseName = originalName
            .replace(/\.[^/.]+$/, '')
            .replace(/[^a-zA-Z0-9_-]/g, '_');

        const filename = `${baseName || 'lampiran'}_compressed.jpg`;

        return new File([blob], filename, {
            type: 'image/jpeg',
            lastModified: Date.now()
        });
    }

    startCamBtn?.addEventListener('click', async () => {
        clearCameraError();

        if (!navigator.mediaDevices?.getUserMedia) {
            showCameraError('Browser atau perangkat ini tidak mendukung akses kamera.');
            return;
        }

        try {
            stopCameraTracksOnly();

            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                audio: false
            });

            video.srcObject = cameraStream;
            await video.play();

            cameraPlaceholder?.classList.add('hidden');
            imagePreview?.setAttribute('hidden', '');
            startCamBtn?.classList.add('hidden');
            captureBtn?.classList.remove('hidden');
            retakeBtn?.classList.add('hidden');
            stopCamBtn?.classList.remove('hidden');
        } catch (error) {
            console.error('Camera error:', error);
            stopCameraTracksOnly();
            showCameraError('Kamera tidak dapat diakses. Pastikan izin kamera telah diberikan pada browser.');
        }
    });

    captureBtn?.addEventListener('click', async () => {
        clearCameraError();

        if (!video.videoWidth || !video.videoHeight) {
            showCameraError('Kamera belum siap. Silakan tunggu sebentar.');
            return;
        }

        try {
            const canvas = document.createElement('canvas');

            let width = video.videoWidth;
            let height = video.videoHeight;

            const scale = Math.min(MAX_IMAGE_WIDTH / width, MAX_IMAGE_HEIGHT / height, 1);

            width = Math.max(1, Math.round(width * scale));
            height = Math.max(1, Math.round(height * scale));

            canvas.width = width;
            canvas.height = height;

            const context = canvas.getContext('2d', { alpha: false });

            if (!context) throw new Error('Browser tidak mendukung canvas.');

            context.fillStyle = '#ffffff';
            context.fillRect(0, 0, width, height);
            context.imageSmoothingEnabled = true;
            context.imageSmoothingQuality = 'high';
            context.drawImage(video, 0, 0, width, height);

            const blob = await canvasToCompressedBlob(canvas);

            if (blob.size > CLIENT_MAX_FILE_SIZE) {
                throw new Error('Hasil scan masih terlalu besar.');
            }

            revokePreviewUrl();

            previewObjectUrl = URL.createObjectURL(blob);
            imagePreview.src = previewObjectUrl;
            imagePreview.removeAttribute('hidden');

            video.classList.add('hidden');
            capturedInput.value = await blobToDataUrl(blob);

            clearFileSelection();
            snapshotPreview?.classList.remove('hidden');
            captureBtn?.classList.add('hidden');
            retakeBtn?.classList.remove('hidden');
            cameraPlaceholder?.classList.add('hidden');

            stopCameraTracksOnly();
            stopCamBtn?.classList.add('hidden');
        } catch (error) {
            console.error('Gagal mengambil foto:', error);
            showCameraError(error?.message || 'Gagal memproses hasil scan kamera.');
        }
    });

    function canvasToCompressedBlob(canvas) {
        return new Promise((resolve, reject) => {
            const qualities = [0.78, 0.70, 0.62, 0.54];

            const tryQuality = index => {
                if (index >= qualities.length) {
                    canvas.toBlob(blob => {
                        if (!blob) {
                            reject(new Error('Gagal membuat hasil scan.'));
                            return;
                        }

                        resolve(blob);
                    }, 'image/jpeg', 0.48);

                    return;
                }

                canvas.toBlob(blob => {
                    if (!blob) {
                        reject(new Error('Gagal membuat hasil scan.'));
                        return;
                    }

                    if (blob.size <= CLIENT_TARGET_IMAGE_SIZE) {
                        resolve(blob);
                        return;
                    }

                    tryQuality(index + 1);
                }, 'image/jpeg', qualities[index]);
            };

            tryQuality(0);
        });
    }

    function blobToDataUrl(blob) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = () => resolve(reader.result);
            reader.onerror = () => reject(new Error('Gagal menyiapkan file hasil scan.'));

            reader.readAsDataURL(blob);
        });
    }

    retakeBtn?.addEventListener('click', async () => {
        clearCapturedImage();
        video.classList.remove('hidden');
        retakeBtn?.classList.add('hidden');
        clearCameraError();

        try {
            await startCameraAgain();
        } catch (error) {
            console.error(error);
            showCameraError('Kamera gagal diaktifkan kembali.');
        }
    });

    async function startCameraAgain() {
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('Browser tidak mendukung kamera.');
        }

        stopCameraTracksOnly();

        cameraStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: { ideal: 'environment' },
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            },
            audio: false
        });

        video.srcObject = cameraStream;
        await video.play();

        cameraPlaceholder?.classList.add('hidden');
        captureBtn?.classList.remove('hidden');
        stopCamBtn?.classList.remove('hidden');
        startCamBtn?.classList.add('hidden');
    }

    stopCamBtn?.addEventListener('click', stopCamera);

    function stopCamera() {
        stopCameraTracksOnly();

        if (video) {
            video.srcObject = null;
            video.classList.remove('hidden');
        }

        cameraPlaceholder?.classList.remove('hidden');

        if (cameraPlaceholderText) {
            cameraPlaceholderText.textContent = 'Kamera belum aktif';
        }

        startCamBtn?.classList.remove('hidden');
        captureBtn?.classList.add('hidden');
        retakeBtn?.classList.add('hidden');
        stopCamBtn?.classList.add('hidden');
    }

    function stopCameraTracksOnly() {
        if (!cameraStream) return;

        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }

    form.addEventListener('submit', event => {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        const hasCapturedImage = Boolean(capturedInput.value);
        const hasUploadedFile = Boolean(fileInput.files?.length);

        if (!hasCapturedImage && !hasUploadedFile) {
            event.preventDefault();
            alert('Silakan upload berkas digital atau ambil foto menggunakan kamera.');
            return;
        }

        isSubmitting = true;

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
        }

        if (submitText) {
            submitText.textContent = 'Memproses & menyimpan...';
        }

        stopCameraTracksOnly();
    });

    window.addEventListener('beforeunload', () => {
        stopCameraTracksOnly();
        revokePreviewUrl();
    });
});
</script>

@endsection
