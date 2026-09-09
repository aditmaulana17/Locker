@extends('layouts.app')

@section('title', 'Edit Disposisi')

@section('content')
<div class="w-full max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-6">

    {{-- Error Validation --}}
    @if ($errors->any())
        <div class="mb-3 p-3 bg-rose-50 border-2 border-rose-200 text-rose-800 rounded-xl">
            <div class="flex items-start gap-2.5">

                <div class="flex items-center justify-center w-8 h-8 bg-rose-100 border-2 border-rose-200 rounded-lg text-rose-600 shrink-0">
                    <svg class="w-4 h-4"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>

                <div class="min-w-0">
                    <h3 class="text-xs sm:text-sm font-bold">
                        Gagal menyimpan pembaruan disposisi:
                    </h3>

                    <ul class="mt-1 list-disc list-inside text-xs space-y-0.5 text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
    @endif


    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">

        <div class="flex items-center gap-2.5 min-w-0">

            <a href="{{ route('disposisi.index') }}"
               class="flex items-center justify-center w-9 h-9 bg-white border-2 border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 hover:border-slate-400 transition shrink-0"
               title="Kembali">

                <svg class="w-4 h-4"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>

            </a>

            <div class="min-w-0">

                <nav class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-400 font-medium mb-0.5">

                    <a href="{{ route('disposisi.index') }}"
                       class="hover:text-slate-600 transition">
                        Disposisi
                    </a>

                    <span>/</span>

                    <span class="text-slate-600">
                        Edit Disposisi
                    </span>

                </nav>

                <h1 class="text-xl sm:text-2xl font-bold text-slate-800 tracking-tight">
                    Edit Disposisi Surat
                </h1>

            </div>
        </div>

        <a href="{{ route('disposisi.index') }}"
           class="inline-flex items-center justify-center w-full sm:w-auto px-4 py-2 text-sm font-semibold text-slate-700 bg-white border-2 border-slate-300 rounded-xl hover:bg-slate-50 hover:border-slate-400 transition">
            Batal
        </a>

    </div>


    {{-- Main Card --}}
    <div class="bg-white border-2 border-slate-300 rounded-2xl shadow-sm overflow-hidden">

        {{-- Referensi Surat --}}
        @if(isset($disposisi->suratMasuk))
            <div class="px-4 sm:px-5 py-3 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border-b-2 border-slate-700 text-white">

                <div class="flex items-start gap-3">

                    <div class="flex items-center justify-center w-9 h-9 bg-white/10 border-2 border-white/10 rounded-lg text-indigo-400 shrink-0">
                        <svg class="w-5 h-5"
                             fill="none"
                             stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round"
                                  stroke-linejoin="round"
                                  stroke-width="2"
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">

                        <div class="flex flex-wrap items-center gap-1.5">

                            <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">
                                Referensi Surat
                            </span>

                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-white/10 text-indigo-200 border border-white/10">
                                #{{ $disposisi->suratMasuk->nomor_agenda ? 'AG/' . $disposisi->suratMasuk->nomor_agenda : $disposisi->suratMasuk->id }}
                            </span>

                        </div>

                        <h2 class="mt-1 text-sm sm:text-base font-bold text-white leading-snug break-words">
                            {{ $disposisi->suratMasuk->perihal ?? 'Tanpa Perihal' }}
                        </h2>

                        <div class="flex flex-wrap items-center gap-x-2.5 gap-y-0.5 mt-1 text-[11px] text-slate-300 font-medium">

                            <span>
                                No. Surat:
                                <strong class="text-white">
                                    {{ $disposisi->suratMasuk->nomor_surat ?? '-' }}
                                </strong>
                            </span>

                            <span class="text-slate-500">
                                &bull;
                            </span>

                            <span>
                                Pengirim:
                                <strong class="text-white">
                                    {{ $disposisi->suratMasuk->pengirim ?? '-' }}
                                </strong>
                            </span>

                            <a href="{{ route('surat-masuk.show', $disposisi->suratMasuk) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1 ml-auto text-[11px] text-indigo-300 hover:text-indigo-200 font-semibold transition">

                                Lihat Surat

                                <svg class="w-3.5 h-3.5"
                                     fill="none"
                                     stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                          stroke-linejoin="round"
                                          stroke-width="2"
                                          d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>

                            </a>

                        </div>
                    </div>

                </div>
            </div>
        @endif


        {{-- Form --}}
        <form action="{{ route('disposisi.update', $disposisi->id) }}"
              method="POST"
              id="form-disposisi">

            @csrf
            @method('PUT')

            <div class="p-4 sm:p-5">

                {{-- Penerima --}}
                <div class="form-group">

                    <label for="kepada_user_id"
                           class="form-label">
                        Disposisikan Kepada
                        <span class="text-rose-500">*</span>
                    </label>

                    @if(isset($users) && count($users) > 0)

                        <select id="kepada_user_id"
                                name="kepada_user_id"
                                required
                                class="form-control-custom @error('kepada_user_id') form-error @enderror">

                            <option value=""
                                    disabled>
                                -- Pilih Pejabat / Staf Tujuan --
                            </option>

                            @foreach($users as $u)
                                <option value="{{ $u->id }}"
                                    @selected(
                                        old(
                                            'kepada_user_id',
                                            $disposisi->kepada_user_id ?? $disposisi->kepada?->id
                                        ) == $u->id
                                    )>
                                    {{ $u->name }}
                                    @if(isset($u->jabatan) && $u->jabatan)
                                        ({{ $u->jabatan }})
                                    @endif
                                </option>
                            @endforeach

                        </select>

                    @else

                        <input type="text"
                               id="penerima"
                               name="penerima"
                               value="{{ old('penerima', $disposisi->penerima ?? $disposisi->tujuan ?? $disposisi->kepada?->name) }}"
                               required
                               placeholder="Contoh: Sekuriti / Staff Keuangan"
                               class="form-control-custom @error('penerima') form-error @enderror">

                    @endif

                    @error('kepada_user_id')
                        <p class="form-error-text">
                            {{ $message }}
                        </p>
                    @enderror

                    @error('penerima')
                        <p class="form-error-text">
                            {{ $message }}
                        </p>
                    @enderror

                    <p class="form-help">
                        Perbarui personil atau bagian yang ditugaskan.
                    </p>

                </div>


                {{-- Instruksi --}}
                <div class="form-group mt-3">

                    <label for="instruksi"
                           class="form-label">
                        Instruksi Penanganan
                        <span class="text-rose-500">*</span>
                    </label>

                    <textarea id="instruksi"
                              name="instruksi"
                              rows="2"
                              required
                              placeholder="Contoh: Mohon ditindaklanjuti segera dan koordinasikan dengan tim terkait."
                              class="form-control-custom form-textarea @error('instruksi') form-error @enderror">{{ old('instruksi', $disposisi->instruksi ?? $disposisi->isi_disposisi) }}</textarea>

                    @error('instruksi')
                        <p class="form-error-text">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Catatan --}}
                <div class="form-group mt-3">

                    <label for="catatan"
                           class="form-label">
                        Catatan Tambahan
                        <span class="text-slate-400 font-normal">
                            (Opsional)
                        </span>
                    </label>

                    <textarea id="catatan"
                              name="catatan"
                              rows="2"
                              placeholder="Tuliskan catatan khusus atau petunjuk teknis tambahan..."
                              class="form-control-custom form-textarea @error('catatan') form-error @enderror">{{ old('catatan', $disposisi->catatan) }}</textarea>

                    @error('catatan')
                        <p class="form-error-text">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Sifat & Batas Waktu --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">

                    {{-- Sifat --}}
                    @if(isset($disposisi->sifat) || Schema::hasColumn('disposisi', 'sifat'))

                        <div class="form-group">

                            <label for="sifat"
                                   class="form-label">
                                Sifat Disposisi
                            </label>

                            <select id="sifat"
                                    name="sifat"
                                    class="form-control-custom @error('sifat') form-error @enderror">

                                @foreach([
                                    'biasa' => 'Biasa',
                                    'penting' => 'Penting',
                                    'segera' => 'Segera',
                                    'rahasia' => 'Rahasia'
                                ] as $key => $label)

                                    <option value="{{ $key }}"
                                        @selected(
                                            old(
                                                'sifat',
                                                strtolower($disposisi->sifat ?? 'biasa')
                                            ) == $key
                                        )>
                                        {{ $label }}
                                    </option>

                                @endforeach

                            </select>

                            @error('sifat')
                                <p class="form-error-text">
                                    {{ $message }}
                                </p>
                            @enderror

                        </div>

                    @endif


                    {{-- Batas Waktu --}}
                    <div class="form-group">

                        <label for="batas_waktu"
                               class="form-label">
                            Batas Waktu Penyelesaian
                        </label>

                        @php
                            $formattedDate = old('batas_waktu');

                            if (
                                !$formattedDate &&
                                isset($disposisi->batas_waktu)
                            ) {
                                $formattedDate =
                                    $disposisi->batas_waktu instanceof \Carbon\Carbon
                                        ? $disposisi->batas_waktu->format('Y-m-d')
                                        : \Carbon\Carbon::parse($disposisi->batas_waktu)->format('Y-m-d');
                            }
                        @endphp

                        <input type="date"
                               id="batas_waktu"
                               name="batas_waktu"
                               value="{{ $formattedDate }}"
                               class="form-control-custom @error('batas_waktu') form-error @enderror">

                        @error('batas_waktu')
                            <p class="form-error-text">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>

                </div>


                {{-- Status --}}
                <div class="form-group mt-3">

                    <label class="form-label">
                        Status Pekerjaan
                        <span class="text-rose-500">*</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">

                        @foreach([
                            'menunggu' => 'Menunggu',
                            'diproses' => 'Diproses',
                            'selesai' => 'Selesai'
                        ] as $stKey => $stLabel)

                            <label class="status-option">

                                <input type="radio"
                                       name="status"
                                       value="{{ $stKey }}"
                                       @checked(
                                           old(
                                               'status',
                                               strtolower($disposisi->status ?? 'menunggu')
                                           ) == $stKey
                                       )
                                       class="sr-only">

                                <span>
                                    {{ $stLabel }}
                                </span>

                            </label>

                        @endforeach

                    </div>

                    @error('status')
                        <p class="form-error-text">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>


            {{-- Footer --}}
            <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-2 px-4 sm:px-5 py-2.5 bg-slate-50 border-t-2 border-slate-300">

                <a href="{{ route('disposisi.index') }}"
                   class="action-button action-button-secondary">
                    Batal
                </a>

                <button type="submit"
                        id="submit-btn"
                        class="action-button action-button-primary">

                    <svg class="w-4 h-4 mr-2"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>

                    Perbarui Disposisi

                </button>

            </div>

        </form>

    </div>
</div>


{{-- =============================================================
CSS
============================================================= --}}
<style>
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
        border-color: #7c3aed;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, .08);
    }

    .form-control-custom::placeholder {
        color: #94a3b8;
    }

    .form-textarea {
        min-height: 82px;
        resize: vertical;
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

    .status-option {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: .45rem .6rem;
        color: #475569;
        background: #f8fafc;
        border: 2px solid #94a3b8;
        border-radius: .6rem;
        font-size: .72rem;
        line-height: .9rem;
        font-weight: 700;
        cursor: pointer;
        transition:
            background-color .15s ease,
            border-color .15s ease,
            color .15s ease;
    }

    .status-option:hover {
        background: #f1f5f9;
        border-color: #64748b;
    }

    .status-option:has(input:checked) {
        color: #6d28d9;
        background: #f5f3ff;
        border-color: #8b5cf6;
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
        background: #7c3aed;
        border-color: #7c3aed;
        box-shadow: 0 4px 10px rgba(124, 58, 237, .15);
    }

    .action-button-primary:hover {
        background: #6d28d9;
        border-color: #6d28d9;
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
    const formDisposisi =
        document.getElementById('form-disposisi');

    if (formDisposisi) {
        formDisposisi.addEventListener('submit', function () {

            const submitButton =
                document.getElementById('submit-btn');

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

                Memperbarui...
            `;
        });
    }
</script>
@endpush

@endsection