@extends('layouts.app')

@section('title', 'Edit Surat Keluar')

@section('content')
@php
    $currentStatus = strtolower(trim((string) old('status', $suratKeluar->status ?? 'draft')));
    if ($currentStatus === 'draf') {
        $currentStatus = 'draft';
    }

    $tanggalSurat = old('tanggal_surat', optional($suratKeluar->tanggal_surat)->format('Y-m-d'));
    $tanggalKeluar = old('tanggal_keluar', optional($suratKeluar->tanggal_keluar)->format('Y-m-d'));
    $kategoriSuratId = old('kategori_surat_id', $suratKeluar->kategori_surat_id);
@endphp

<div class="mx-auto w-full max-w-5xl px-4 pb-6 sm:px-6 lg:px-8">
    {{-- HEADER --}}
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-800 sm:text-2xl">Edit Surat Keluar</h1>
            <p class="mt-0.5 text-sm text-slate-500">Perbarui informasi arsip surat keluar yang tersimpan.</p>
        </div>

        <a
            href="{{ route('surat-keluar.index') }}"
            class="inline-flex w-full items-center justify-center rounded-xl border-2 border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 sm:w-auto"
        >
            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    {{-- FORM --}}
    <form
        id="form-surat"
        method="POST"
        action="{{ route('surat-keluar.update', $suratKeluar) }}"
        enctype="multipart/form-data"
        novalidate
    >
        @csrf
        @method('PUT')

        <div class="overflow-hidden rounded-2xl border-2 border-slate-300 bg-white shadow-sm">
            {{-- MAIN CONTENT --}}
            <div class="p-4 sm:p-5">
                {{-- INFORMASI UTAMA --}}
                <section>
                    <div class="section-heading">
                        <div class="section-marker bg-blue-600"></div>
                        <div>
                            <h2 class="section-title">Informasi Utama Surat</h2>
                            <p class="section-description">Perbarui identitas dan informasi utama surat keluar.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-x-4 gap-y-3 md:grid-cols-2">
                        {{-- NOMOR SURAT --}}
                        <div class="form-group">
                            <label for="nomor_surat" class="form-label">
                                Nomor Surat <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="nomor_surat"
                                name="nomor_surat"
                                value="{{ old('nomor_surat', $suratKeluar->nomor_surat) }}"
                                placeholder="Contoh: 005/SK/I/2026"
                                autocomplete="off"
                                maxlength="255"
                                required
                                class="form-control-custom @error('nomor_surat') form-error @enderror"
                            >
                            @error('nomor_surat')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- TUJUAN SURAT --}}
                        <div class="form-group">
                            <label for="pengirim" class="form-label">
                                Tujuan Surat <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="pengirim"
                                name="pengirim"
                                value="{{ old('pengirim', $suratKeluar->pengirim) }}"
                                placeholder="Contoh: PT Maju Takgentar"
                                autocomplete="organization"
                                maxlength="255"
                                required
                                class="form-control-custom @error('pengirim') form-error @enderror"
                            >
                            <p class="form-hint">Masukkan instansi, lembaga, organisasi, atau pihak tujuan surat.</p>
                            @error('pengirim')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- TANGGAL SURAT --}}
                        <div class="form-group">
                            <label for="tanggal_surat" class="form-label">
                                Tanggal Surat <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="date"
                                id="tanggal_surat"
                                name="tanggal_surat"
                                value="{{ $tanggalSurat }}"
                                required
                                class="form-control-custom @error('tanggal_surat') form-error @enderror"
                            >
                            @error('tanggal_surat')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- TANGGAL KELUAR --}}
                        <div class="form-group">
                            <label for="tanggal_keluar" class="form-label">
                                Tanggal Keluar <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="date"
                                id="tanggal_keluar"
                                name="tanggal_keluar"
                                value="{{ $tanggalKeluar }}"
                                required
                                class="form-control-custom @error('tanggal_keluar') form-error @enderror"
                            >
                            <p class="form-hint">Tanggal surat resmi keluar atau dikirim.</p>
                            @error('tanggal_keluar')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- KATEGORI --}}
                        <div class="form-group">
                            <label for="kategori_surat_id" class="form-label">
                                Kategori Surat <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="kategori_surat_id"
                                name="kategori_surat_id"
                                required
                                class="form-control-custom @error('kategori_surat_id') form-error @enderror"
                            >
                                <option value="" disabled @selected(!$kategoriSuratId)>Pilih kategori surat</option>
                                @foreach ($kategoris ?? [] as $kategori)
                                    <option
                                        value="{{ $kategori->id }}"
                                        @selected((string) $kategoriSuratId === (string) $kategori->id)
                                    >
                                        {{ $kategori->nama_kategori }}
                                        @if (!empty($kategori->sifat))
                                            ({{ ucfirst($kategori->sifat) }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            @if (!isset($kategoris) || $kategoris->isEmpty())
                                <p class="form-hint text-amber-600">Belum ada kategori surat yang tersedia.</p>
                            @endif

                            @error('kategori_surat_id')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- STATUS --}}
                        <div class="form-group">
                            <label for="status" class="form-label">
                                Status Surat <span class="text-rose-500">*</span>
                            </label>
                            <select
                                id="status"
                                name="status"
                                required
                                class="form-control-custom @error('status') form-error @enderror"
                            >
                                <option value="draft" @selected($currentStatus === 'draft')>Draft</option>
                                <option value="diproses" @selected($currentStatus === 'diproses')>Diproses</option>
                                <option value="disetujui" @selected($currentStatus === 'disetujui')>Disetujui</option>
                                <option value="dikirim" @selected($currentStatus === 'dikirim')>Dikirim</option>
                                <option value="diarsipkan" @selected($currentStatus === 'diarsipkan')>Diarsipkan</option>
                            </select>
                            @error('status')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- PERIHAL --}}
                        <div class="form-group md:col-span-2">
                            <label for="perihal" class="form-label">
                                Perihal <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                id="perihal"
                                name="perihal"
                                rows="3"
                                maxlength="1000"
                                required
                                placeholder="Tuliskan perihal surat secara jelas..."
                                class="form-control-custom form-textarea @error('perihal') form-error @enderror"
                            >{{ old('perihal', $suratKeluar->perihal) }}</textarea>
                            @error('perihal')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- RINGKASAN --}}
                        <div class="form-group md:col-span-2">
                            <label for="ringkasan" class="form-label">Ringkasan</label>
                            <textarea
                                id="ringkasan"
                                name="ringkasan"
                                rows="3"
                                maxlength="2000"
                                placeholder="Tulis ringkasan isi surat secara singkat (opsional)..."
                                class="form-control-custom form-textarea @error('ringkasan') form-error @enderror"
                            >{{ old('ringkasan', $suratKeluar->ringkasan) }}</textarea>
                            <p class="form-hint">Ringkasan bersifat opsional dan membantu pencarian atau identifikasi isi surat.</p>
                            @error('ringkasan')
                                <p class="form-error-text">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                {{-- LAMPIRAN --}}
                <section class="mt-5">
                    <div class="section-heading">
                        <div class="section-marker bg-indigo-600"></div>
                        <div>
                            <h2 class="section-title">Lampiran Dokumen Surat</h2>
                            <p class="section-description">Periksa lampiran yang tersimpan atau upload dokumen pengganti.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 items-stretch gap-3 md:grid-cols-2">
                        {{-- FILE LAMA --}}
                        @if (!empty($suratKeluar->lampiran_file))
                            <div class="archive-card">
                                <div class="archive-card-header">
                                    <div>
                                        <h3 class="archive-card-title">Lampiran Saat Ini</h3>
                                        <p class="archive-card-description">File yang sedang tersimpan sebagai arsip.</p>
                                    </div>
                                    <span class="archive-card-badge">Tersimpan</span>
                                </div>

                                <div class="current-file">
                                    <div class="current-file-icon">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                            />
                                        </svg>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-semibold text-slate-700">Dokumen saat ini</p>
                                        <p
                                            class="mt-0.5 truncate text-xs text-slate-400"
                                            title="{{ basename($suratKeluar->lampiran_file) }}"
                                        >
                                            {{ basename($suratKeluar->lampiran_file) }}
                                        </p>
                                    </div>

                                    <a
                                        href="{{ route('surat-keluar.preview-lampiran', $suratKeluar) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="shrink-0 rounded-lg border-2 border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-600 transition hover:bg-blue-100"
                                    >
                                        Lihat File
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- FILE BARU --}}
                        <div class="archive-card {{ !empty($suratKeluar->lampiran_file) ? '' : 'md:col-span-2' }}">
                            <div class="archive-card-header">
                                <div>
                                    <h3 class="archive-card-title">Lampiran Baru</h3>
                                    <p class="archive-card-description">Upload file baru untuk mengganti lampiran lama.</p>
                                </div>
                                <span class="archive-card-badge">Opsional</span>
                            </div>

                            <div id="upload-section">
                                <label
                                    for="lampiran_file"
                                    class="upload-box @error('lampiran_file') upload-box-error @enderror"
                                >
                                    <div class="upload-icon">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 0115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                                            />
                                        </svg>
                                    </div>

                                    <span id="file-label-text" class="text-sm font-bold text-slate-700">
                                        Klik untuk memilih file baru
                                    </span>

                                    <span class="mt-0.5 text-xs text-slate-400">
                                        PDF, JPG, JPEG, PNG · Maks. 10 MB
                                    </span>

                                    <input
                                        type="file"
                                        id="lampiran_file"
                                        name="lampiran_file"
                                        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                    >
                                </label>

                                @error('lampiran_file')
                                    <p class="form-error-text mt-1.5">{{ $message }}</p>
                                @enderror

                                <p id="file-info" class="mt-1.5 hidden text-xs text-slate-500"></p>

                                <div class="mt-2 rounded-lg border-2 border-slate-200 bg-slate-50 px-3 py-2">
                                    <p class="text-xs leading-relaxed text-slate-500">
                                        Tidak memilih file baru berarti lampiran lama tetap dipertahankan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- VALIDATION ERROR --}}
                @if ($errors->any())
                    <div
                        class="mt-4 rounded-lg border-2 border-rose-200 bg-rose-50 p-3 text-xs text-rose-700"
                        role="alert"
                    >
                        <strong class="font-bold">Terdapat kesalahan pada formulir:</strong>
                        <ul class="mt-1 list-inside list-disc space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- FOOTER --}}
            <div class="flex flex-col-reverse items-center justify-end gap-2 border-t-2 border-slate-300 bg-slate-50 px-4 py-2.5 sm:flex-row sm:px-5">
                <a href="{{ route('surat-keluar.index') }}" class="action-button action-button-secondary">
                    Batal
                </a>

                <button type="submit" id="submit-btn" class="action-button action-button-primary">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Perbarui Surat Keluar
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.section-heading {
    display:flex;
    align-items:center;
    gap:.6rem;
    padding-bottom:.45rem;
    margin-bottom:.75rem;
    border-bottom:2px solid #cbd5e1;
}
.section-marker {
    width:.35rem;
    height:1.2rem;
    flex-shrink:0;
    border-radius:9999px;
}
.section-title {
    color:#334155;
    font-size:.82rem;
    line-height:1rem;
    font-weight:700;
    letter-spacing:.04em;
    text-transform:uppercase;
}
.section-description {
    margin-top:.05rem;
    color:#94a3b8;
    font-size:.68rem;
    line-height:.9rem;
}
.form-group { width:100%; }
.form-label {
    display:block;
    margin-bottom:.3rem;
    color:#334155;
    font-size:.68rem;
    line-height:.9rem;
    font-weight:700;
    letter-spacing:.03em;
    text-transform:uppercase;
}
.form-hint {
    margin-top:.25rem;
    color:#94a3b8;
    font-size:.65rem;
    line-height:1rem;
}
.form-control-custom {
    width:100%;
    min-height:40px;
    padding:.48rem .7rem;
    background:#fff;
    color:#334155;
    border:2px solid #94a3b8;
    border-radius:.6rem;
    outline:none;
    font-size:.8rem;
    line-height:1.25;
    transition:border-color .15s ease, background-color .15s ease, box-shadow .15s ease;
}
.form-control-custom:hover { border-color:#64748b; }
.form-control-custom:focus {
    border-color:#2563eb;
    background:#fff;
    box-shadow:0 0 0 3px rgba(37,99,235,.08);
}
.form-control-custom::placeholder { color:#94a3b8; }
select.form-control-custom { cursor:pointer; }
.form-textarea {
    min-height:82px;
    resize:vertical;
}
.form-error {
    border-color:#f43f5e !important;
    background:#fff1f2 !important;
}
.form-error-text {
    margin-top:.2rem;
    color:#e11d48;
    font-size:.68rem;
    line-height:.9rem;
    font-weight:500;
}
.archive-card {
    display:flex;
    flex-direction:column;
    min-height:205px;
    padding:.8rem;
    background:#f8fafc;
    border:2px solid #94a3b8;
    border-radius:.8rem;
}
.archive-card-header {
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:.6rem;
    margin-bottom:.55rem;
}
.archive-card-title {
    color:#334155;
    font-size:.8rem;
    line-height:1rem;
    font-weight:700;
}
.archive-card-description {
    margin-top:.05rem;
    color:#94a3b8;
    font-size:.66rem;
    line-height:.9rem;
}
.archive-card-badge {
    flex-shrink:0;
    padding:.22rem .45rem;
    background:#fff;
    border:2px solid #cbd5e1;
    border-radius:.4rem;
    color:#475569;
    font-size:.6rem;
    line-height:.8rem;
    font-weight:700;
}
.current-file {
    display:flex;
    align-items:center;
    gap:.65rem;
    width:100%;
    padding:.65rem;
    background:#fff;
    border:2px solid #cbd5e1;
    border-radius:.6rem;
}
.current-file-icon {
    display:flex;
    align-items:center;
    justify-content:center;
    width:2rem;
    height:2rem;
    flex-shrink:0;
    color:#2563eb;
    background:#eff6ff;
    border:2px solid #dbeafe;
    border-radius:.5rem;
}
.upload-box {
    position:relative;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    width:100%;
    min-height:150px;
    padding:.9rem;
    text-align:center;
    background:#fff;
    border:2px dashed #64748b;
    border-radius:.6rem;
    cursor:pointer;
    transition:background-color .15s ease, border-color .15s ease;
}
.upload-box:hover {
    background:#f8fafc;
    border-color:#475569;
}
.upload-box:focus-within {
    border-color:#2563eb;
    background:#eff6ff;
}
.upload-box-error {
    border-color:#f43f5e !important;
    background:#fff1f2 !important;
}
.upload-icon {
    display:flex;
    align-items:center;
    justify-content:center;
    width:2.35rem;
    height:2.35rem;
    margin-bottom:.45rem;
    border:2px solid #dbeafe;
    border-radius:9999px;
    background:#eff6ff;
    color:#2563eb;
}
.action-button {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:100%;
    padding:.5rem .9rem;
    border-width:2px;
    border-radius:.6rem;
    font-size:.76rem;
    font-weight:700;
    transition:all .15s ease;
}
.action-button-secondary {
    background:#fff;
    color:#334155;
    border-color:#cbd5e1;
}
.action-button-secondary:hover {
    background:#f1f5f9;
    border-color:#94a3b8;
}
.action-button-primary {
    background:#2563eb;
    color:#fff;
    border-color:#2563eb;
    box-shadow:0 4px 10px rgba(37,99,235,.15);
}
.action-button-primary:hover {
    background:#1d4ed8;
    border-color:#1d4ed8;
}
.action-button-primary:disabled {
    opacity:.7;
    cursor:not-allowed;
}
@media (min-width:640px) {
    .action-button { width:auto; }
}
@media (max-width:640px) {
    .archive-card { min-height:auto; }
    .upload-box { min-height:155px; }
    .form-control-custom { min-height:40px; }
    .current-file {
        align-items:flex-start;
        flex-wrap:wrap;
    }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const form = document.getElementById('form-surat');
    const fileInput = document.getElementById('lampiran_file');
    const fileLabelText = document.getElementById('file-label-text');
    const fileInfo = document.getElementById('file-info');
    const submitButton = document.getElementById('submit-btn');
    const tanggalSurat = document.getElementById('tanggal_surat');
    const tanggalKeluar = document.getElementById('tanggal_keluar');

    const MAX_FILE_SIZE = 10 * 1024 * 1024;
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];
    let isSubmitting = false;

    function resetFileInput() {
        if (fileInput) fileInput.value = '';

        if (fileLabelText) {
            fileLabelText.textContent = 'Klik untuk memilih file baru';
            fileLabelText.classList.remove('text-blue-600', 'text-rose-600');
            fileLabelText.classList.add('text-slate-700');
        }

        if (fileInfo) {
            fileInfo.textContent = '';
            fileInfo.classList.add('hidden');
        }
    }

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1024 / 1024).toFixed(2) + ' MB';
    }

    function showAlert(icon, title, text) {
        if (typeof window.Swal !== 'undefined') {
            window.Swal.fire({
                icon,
                title,
                text,
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#2563eb'
            });
        } else {
            window.alert(text);
        }
    }

    fileInput?.addEventListener('change', function () {
        if (!fileInput.files || fileInput.files.length === 0) {
            resetFileInput();
            return;
        }

        const file = fileInput.files[0];
        const extension = file.name.split('.').pop()?.toLowerCase() || '';

        if (!ALLOWED_EXTENSIONS.includes(extension)) {
            resetFileInput();

            if (fileLabelText) {
                fileLabelText.textContent = 'Format file tidak didukung';
                fileLabelText.classList.remove('text-slate-700', 'text-blue-600');
                fileLabelText.classList.add('text-rose-600');
            }

            if (fileInfo) {
                fileInfo.textContent = 'Gunakan PDF, JPG, JPEG, atau PNG.';
                fileInfo.classList.remove('hidden');
            }

            return;
        }

        if (file.size > MAX_FILE_SIZE) {
            resetFileInput();

            if (fileLabelText) {
                fileLabelText.textContent = 'Ukuran file melebihi 10 MB';
                fileLabelText.classList.remove('text-slate-700', 'text-blue-600');
                fileLabelText.classList.add('text-rose-600');
            }

            if (fileInfo) {
                fileInfo.textContent = 'Silakan pilih file yang ukurannya tidak lebih dari 10 MB.';
                fileInfo.classList.remove('hidden');
            }

            return;
        }

        if (fileLabelText) {
            fileLabelText.textContent = `Terpilih: ${file.name}`;
            fileLabelText.classList.remove('text-slate-700', 'text-rose-600');
            fileLabelText.classList.add('text-blue-600');
        }

        if (fileInfo) {
            fileInfo.textContent = `Ukuran: ${formatFileSize(file.size)}`;
            fileInfo.classList.remove('hidden');
        }
    });

    form?.addEventListener('submit', function (event) {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        const suratDate = tanggalSurat?.value || '';
        const keluarDate = tanggalKeluar?.value || '';

        if (suratDate && keluarDate && keluarDate < suratDate) {
            event.preventDefault();
            showAlert(
                'warning',
                'Tanggal tidak valid',
                'Tanggal keluar tidak boleh lebih awal dari tanggal surat.'
            );
            return;
        }

        if (fileInput?.files?.length > 0) {
            const file = fileInput.files[0];
            const extension = file.name.split('.').pop()?.toLowerCase() || '';

            if (file.size > MAX_FILE_SIZE) {
                event.preventDefault();
                showAlert(
                    'warning',
                    'File terlalu besar',
                    'Ukuran lampiran maksimal 10 MB.'
                );
                return;
            }

            if (!ALLOWED_EXTENSIONS.includes(extension)) {
                event.preventDefault();
                showAlert(
                    'warning',
                    'Format file tidak didukung',
                    'Gunakan PDF, JPG, JPEG, atau PNG.'
                );
                return;
            }
        }

        isSubmitting = true;

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="mr-2 h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4c0-1.1.1-2.1.4-3.1L4 12z"></path>
                </svg>
                Memperbarui...
            `;
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection