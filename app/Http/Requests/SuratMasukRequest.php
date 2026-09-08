<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratMasukRequest extends FormRequest
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
        $suratMasuk = $this->route('suratMasuk') ?? $this->route('surat_masuk');
        $suratMasukId = is_object($suratMasuk) ? $suratMasuk->id : $suratMasuk;

        return [
            'nomor_agenda' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('surat_masuks', 'nomor_agenda')->ignore($suratMasukId),
            ],
            'nomor_surat' => [
                'required',
                'string',
                'max:255',
            ],
            'pengirim' => [
                'required',
                'string',
                'max:255',
            ],
            'tanggal_surat' => [
                'required',
                'date',
            ],
            'tanggal_terima' => [
                'required',
                'date',
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
                    'baru',
                    'diproses',
                    'didisposisikan',
                    'selesai',
                    'diarsipkan',
                ]),
            ],
            'lokasi_arsip_fisik' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_agenda.unique' => 'Nomor agenda ini sudah digunakan oleh surat lain.',
            'nomor_surat.required' => ':attribute wajib diisi.',
            'nomor_surat.string' => ':attribute harus berupa teks.',
            'nomor_surat.max' => ':attribute maksimal 255 karakter.',
            'pengirim.required' => ':attribute wajib diisi.',
            'pengirim.string' => ':attribute harus berupa teks.',
            'pengirim.max' => ':attribute maksimal 255 karakter.',
            'tanggal_surat.required' => ':attribute wajib diisi.',
            'tanggal_surat.date' => ':attribute harus berupa tanggal yang valid.',
            'tanggal_terima.required' => ':attribute wajib diisi.',
            'tanggal_terima.date' => ':attribute harus berupa tanggal yang valid.',
            'kategori_surat_id.required' => ':attribute wajib dipilih.',
            'kategori_surat_id.integer' => ':attribute tidak valid.',
            'kategori_surat_id.exists' => ':attribute yang dipilih tidak tersedia.',
            'perihal.required' => ':attribute wajib diisi.',
            'perihal.string' => ':attribute harus berupa teks.',
            'perihal.max' => ':attribute maksimal 255 karakter.',
            'ringkasan.string' => ':attribute harus berupa teks.',
            'lampiran_file.file' => ':attribute harus berupa file yang valid.',
            'lampiran_file.mimes' => ':attribute harus berformat PDF, JPG, JPEG, PNG, atau WEBP.',
            'lampiran_file.max' => ':attribute maksimal berukuran 10 MB.',
            'captured_image.string' => ':attribute tidak valid.',
            'status.in' => 'Pilihan :attribute tidak valid.',
            'lokasi_arsip_fisik.string' => ':attribute harus berupa teks.',
            'lokasi_arsip_fisik.max' => ':attribute maksimal 255 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nomor_agenda' => 'Nomor Agenda',
            'nomor_surat' => 'Nomor Surat',
            'pengirim' => 'Pengirim',
            'tanggal_surat' => 'Tanggal Surat',
            'tanggal_terima' => 'Tanggal Diterima',
            'kategori_surat_id' => 'Kategori Surat',
            'perihal' => 'Perihal Surat',
            'ringkasan' => 'Ringkasan',
            'lampiran_file' => 'Berkas Lampiran',
            'captured_image' => 'Hasil Scan Kamera',
            'status' => 'Status Surat',
            'lokasi_arsip_fisik' => 'Lokasi Arsip Fisik',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nomor_agenda' => $this->cleanInput('nomor_agenda'),
            'nomor_surat' => $this->cleanInput('nomor_surat'),
            'pengirim' => $this->cleanInput('pengirim'),
            'perihal' => $this->cleanInput('perihal'),
            'ringkasan' => $this->cleanInput('ringkasan'),
            'status' => $this->cleanStatus(),
            'lokasi_arsip_fisik' => $this->cleanInput('lokasi_arsip_fisik'),
        ]);
    }

    private function cleanInput(string $key): ?string
    {
        if (!$this->filled($key)) {
            return null;
        }

        $value = trim((string) $this->input($key));

        return $value !== '' ? $value : null;
    }

    private function cleanStatus(): ?string
    {
        if (!$this->filled('status')) {
            return 'baru';
        }

        $status = strtolower(trim((string) $this->input('status')));

        return $status !== '' ? $status : 'baru';
    }
}