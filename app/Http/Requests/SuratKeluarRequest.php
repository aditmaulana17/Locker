<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratKeluarRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $role = strtolower(trim((string) ($user->role ?? $user->jabatan ?? '')));

        if ($role === 'staff') {
            $role = 'staf';
        }

        return in_array($role, ['admin', 'pimpinan'], true);
    }

    public function rules(): array
    {
        $suratKeluar = $this->route('suratKeluar') ?? $this->route('surat_keluar');
        $suratKeluarId = is_object($suratKeluar) ? $suratKeluar->id : $suratKeluar;

        return [
            'nomor_surat' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('surat_keluars', 'nomor_surat')->ignore($suratKeluarId),
            ],
            'tanggal_surat' => [
                'required',
                'date',
            ],
            'tanggal_keluar' => [
                'required',
                'date',
                'after_or_equal:tanggal_surat',
            ],
            'pengirim' => [
                'required',
                'string',
                'max:150',
            ],
            'kategori_surat_id' => [
                'required',
                'integer',
                'exists:kategori_surats,id',
            ],
            'perihal' => [
                'required',
                'string',
                'max:255',
            ],
            'ringkasan' => [
                'nullable',
                'string',
            ],
            'lampiran_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:10240',
            ],
            'captured_image' => [
                'nullable',
                'string',
            ],
            'status' => [
                'nullable',
                Rule::in([
                    'draf',
                    'diproses',
                    'disetujui',
                    'dikirim',
                    'diarsipkan',
                ]),
            ],
            'ditandatangani_oleh' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_surat.string' => 'Nomor surat harus berupa teks.',
            'nomor_surat.max' => 'Nomor surat maksimal 255 karakter.',
            'nomor_surat.unique' => 'Nomor surat tersebut sudah digunakan.',
            'tanggal_surat.required' => 'Tanggal surat wajib diisi.',
            'tanggal_surat.date' => 'Tanggal surat harus berupa tanggal yang valid.',
            'tanggal_keluar.required' => 'Tanggal keluar wajib diisi.',
            'tanggal_keluar.date' => 'Tanggal keluar harus berupa tanggal yang valid.',
            'tanggal_keluar.after_or_equal' => 'Tanggal keluar tidak boleh sebelum tanggal surat.',
            'pengirim.required' => 'Nama pengirim / instansi wajib diisi.',
            'pengirim.string' => 'Nama pengirim / instansi harus berupa teks.',
            'pengirim.max' => 'Nama pengirim / instansi maksimal 150 karakter.',
            'kategori_surat_id.required' => 'Kategori surat wajib dipilih.',
            'kategori_surat_id.integer' => 'Kategori surat tidak valid.',
            'kategori_surat_id.exists' => 'Kategori surat yang dipilih tidak tersedia.',
            'perihal.required' => 'Perihal surat wajib diisi.',
            'perihal.string' => 'Perihal surat harus berupa teks.',
            'perihal.max' => 'Perihal surat maksimal 255 karakter.',
            'ringkasan.string' => 'Ringkasan harus berupa teks.',
            'lampiran_file.file' => 'Lampiran harus berupa file yang valid.',
            'lampiran_file.mimes' => 'Lampiran harus berformat PDF, JPG, JPEG, PNG, atau WEBP.',
            'lampiran_file.max' => 'Ukuran file lampiran maksimal 10MB.',
            'captured_image.string' => 'Hasil scan kamera tidak valid.',
            'status.in' => 'Status surat yang dipilih tidak valid.',
            'ditandatangani_oleh.integer' => 'Data penandatangan tidak valid.',
            'ditandatangani_oleh.exists' => 'Pengguna penandatangan tidak ditemukan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nomor_surat' => 'Nomor Surat',
            'tanggal_surat' => 'Tanggal Surat',
            'tanggal_keluar' => 'Tanggal Keluar',
            'pengirim' => 'Pengirim / Instansi',
            'kategori_surat_id' => 'Kategori Surat',
            'perihal' => 'Perihal Surat',
            'ringkasan' => 'Ringkasan',
            'lampiran_file' => 'Berkas Lampiran',
            'captured_image' => 'Hasil Scan Kamera',
            'status' => 'Status Surat',
            'ditandatangani_oleh' => 'Penandatangan',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nomor_surat' => $this->filled('nomor_surat')
                ? trim((string) $this->input('nomor_surat'))
                : null,
            'pengirim' => $this->filled('pengirim')
                ? trim((string) $this->input('pengirim'))
                : null,
            'perihal' => $this->filled('perihal')
                ? trim((string) $this->input('perihal'))
                : null,
            'ringkasan' => $this->filled('ringkasan')
                ? trim((string) $this->input('ringkasan'))
                : null,
            'status' => $this->filled('status')
                ? strtolower(trim((string) $this->input('status')))
                : 'draf',
        ]);
    }
}