<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratMasukRequest extends FormRequest
{
    /**
     * Menentukan apakah user boleh melakukan request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $role = strtolower(
            trim(
                (string) (
                    $user->role
                    ?? $user->jabatan
                    ?? ''
                )
            )
        );

        /*
         * Normalisasi legacy:
         * staf -> staff
         */
        if ($role === 'staf') {
            $role = 'staff';
        }

        /*
         * Hanya admin dan pimpinan yang dapat
         * membuat/mengubah surat masuk.
         *
         * Staff hanya melihat.
         */
        return in_array(
            $role,
            [
                'admin',
                'pimpinan',
            ],
            true
        );
    }

    /**
     * Rules validasi.
     */
    public function rules(): array
    {
        $suratMasuk = $this->route('suratMasuk')
            ?? $this->route('surat_masuk');

        $suratMasukId = is_object($suratMasuk)
            ? $suratMasuk->id
            : $suratMasuk;

        $isCreate = $this->isMethod('post');

        return [

            /*
             * =====================================================
             * NOMOR AGENDA
             * =====================================================
             */
            'nomor_agenda' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique(
                    'surat_masuks',
                    'nomor_agenda'
                )->ignore($suratMasukId),
            ],

            /*
             * =====================================================
             * NOMOR SURAT
             * =====================================================
             */
            'nomor_surat' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * =====================================================
             * PENGIRIM
             * =====================================================
             */
            'pengirim' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * =====================================================
             * TANGGAL
             * =====================================================
             */
            'tanggal_surat' => [
                'required',
                'date',
            ],

            'tanggal_terima' => [
                'required',
                'date',
                'after_or_equal:tanggal_surat',
            ],

            /*
             * =====================================================
             * KATEGORI
             * =====================================================
             */
            'kategori_surat_id' => [
                'required',
                'integer',
                'exists:kategori_surats,id',
            ],

            /*
             * =====================================================
             * PERIHAL
             * =====================================================
             */
            'perihal' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * =====================================================
             * RINGKASAN
             * =====================================================
             */
            'ringkasan' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
             * =====================================================
             * FILE LAMPIRAN
             * =====================================================
             *
             * Bisa:
             * - PDF
             * - JPG
             * - JPEG
             * - PNG
             * - WEBP
             *
             * Maksimal 10 MB.
             *
             * Pada EDIT nullable karena file lama
             * tetap dipertahankan jika tidak ada file baru.
             */
            'lampiran_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:10240',
            ],

            /*
             * =====================================================
             * HASIL KAMERA
             * =====================================================
             */
            'captured_image' => [
                'nullable',
                'string',
                'regex:/^data:image\/(png|jpeg|jpg|webp);base64,/i',
            ],

            /*
             * =====================================================
             * STATUS
             * =====================================================
             */
            'status' => [
                'required',
                Rule::in([
                    'baru',
                    'diproses',
                    'didisposisikan',
                    'selesai',
                    'diarsipkan',
                ]),
            ],

            /*
             * =====================================================
             * LOKASI ARSIP FISIK
             * =====================================================
             */
            'lokasi_arsip_fisik' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Validasi tambahan.
     *
     * Pada CREATE harus ada minimal salah satu:
     * - lampiran_file
     * - captured_image
     *
     * Pada EDIT tidak wajib upload karena file lama
     * tetap dipertahankan.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->isMethod('post')) {
                return;
            }

            $hasFile =
                $this->hasFile('lampiran_file') &&
                $this->file('lampiran_file') &&
                $this->file('lampiran_file')->isValid();

            $hasCamera =
                $this->filled('captured_image');

            if (!$hasFile && !$hasCamera) {
                $validator->errors()->add(
                    'lampiran_file',
                    'Berkas digital wajib diupload atau discan menggunakan kamera.'
                );
            }

            if ($hasFile && $hasCamera) {
                $validator->errors()->add(
                    'lampiran_file',
                    'Pilih salah satu saja: upload file atau scan menggunakan kamera.'
                );
            }
        });
    }

    /**
     * Pesan validasi.
     */
    public function messages(): array
    {
        return [

            'nomor_agenda.unique' =>
                'Nomor agenda ini sudah digunakan oleh surat lain.',

            'nomor_surat.required' =>
                ':attribute wajib diisi.',

            'nomor_surat.string' =>
                ':attribute harus berupa teks.',

            'nomor_surat.max' =>
                ':attribute maksimal 255 karakter.',

            'pengirim.required' =>
                ':attribute wajib diisi.',

            'pengirim.string' =>
                ':attribute harus berupa teks.',

            'pengirim.max' =>
                ':attribute maksimal 255 karakter.',

            'tanggal_surat.required' =>
                ':attribute wajib diisi.',

            'tanggal_surat.date' =>
                ':attribute harus berupa tanggal yang valid.',

            'tanggal_terima.required' =>
                ':attribute wajib diisi.',

            'tanggal_terima.date' =>
                ':attribute harus berupa tanggal yang valid.',

            'tanggal_terima.after_or_equal' =>
                ':attribute tidak boleh lebih awal daripada tanggal surat.',

            'kategori_surat_id.required' =>
                ':attribute wajib dipilih.',

            'kategori_surat_id.integer' =>
                ':attribute tidak valid.',

            'kategori_surat_id.exists' =>
                ':attribute yang dipilih tidak tersedia.',

            'perihal.required' =>
                ':attribute wajib diisi.',

            'perihal.string' =>
                ':attribute harus berupa teks.',

            'perihal.max' =>
                ':attribute maksimal 255 karakter.',

            'ringkasan.string' =>
                ':attribute harus berupa teks.',

            'ringkasan.max' =>
                ':attribute maksimal 5.000 karakter.',

            'lampiran_file.file' =>
                ':attribute harus berupa file yang valid.',

            'lampiran_file.mimes' =>
                ':attribute harus berformat PDF, JPG, JPEG, PNG, atau WEBP.',

            'lampiran_file.max' =>
                ':attribute maksimal berukuran 10 MB.',

            'captured_image.regex' =>
                ':attribute bukan hasil gambar kamera yang valid.',

            'captured_image.string' =>
                ':attribute tidak valid.',

            'status.required' =>
                ':attribute wajib dipilih.',

            'status.in' =>
                'Pilihan :attribute tidak valid.',

            'lokasi_arsip_fisik.string' =>
                ':attribute harus berupa teks.',

            'lokasi_arsip_fisik.max' =>
                ':attribute maksimal 255 karakter.',
        ];
    }

    /**
     * Nama atribut untuk pesan validasi.
     */
    public function attributes(): array
    {
        return [
            'nomor_agenda' =>
                'Nomor Agenda',

            'nomor_surat' =>
                'Nomor Surat',

            'pengirim' =>
                'Pengirim',

            'tanggal_surat' =>
                'Tanggal Surat',

            'tanggal_terima' =>
                'Tanggal Diterima',

            'kategori_surat_id' =>
                'Kategori Surat',

            'perihal' =>
                'Perihal Surat',

            'ringkasan' =>
                'Ringkasan',

            'lampiran_file' =>
                'Berkas Lampiran',

            'captured_image' =>
                'Hasil Scan Kamera',

            'status' =>
                'Status Surat',

            'lokasi_arsip_fisik' =>
                'Lokasi Arsip Fisik',
        ];
    }

    /**
     * Membersihkan data sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nomor_agenda' =>
                $this->cleanInput('nomor_agenda'),

            'nomor_surat' =>
                $this->cleanInput('nomor_surat'),

            'pengirim' =>
                $this->cleanInput('pengirim'),

            'perihal' =>
                $this->cleanInput('perihal'),

            'ringkasan' =>
                $this->cleanInput('ringkasan'),

            'status' =>
                $this->cleanStatus(),

            'lokasi_arsip_fisik' =>
                $this->cleanInput('lokasi_arsip_fisik'),

            /*
             * Jangan mengubah file upload.
             */
            'captured_image' =>
                $this->cleanCapturedImage(),
        ]);
    }

    /**
     * Membersihkan input string.
     */
    private function cleanInput(string $key): ?string
    {
        if (!$this->filled($key)) {
            return null;
        }

        $value = trim(
            (string) $this->input($key)
        );

        return $value !== ''
            ? $value
            : null;
    }

    /**
     * Membersihkan status.
     */
    private function cleanStatus(): string
    {
        $status = strtolower(
            trim(
                (string) $this->input(
                    'status',
                    'baru'
                )
            )
        );

        return $status !== ''
            ? $status
            : 'baru';
    }

    /**
     * Membersihkan hasil kamera.
     */
    private function cleanCapturedImage(): ?string
    {
        if (!$this->filled('captured_image')) {
            return null;
        }

        $value = trim(
            (string) $this->input('captured_image')
        );

        return $value !== ''
            ? $value
            : null;
    }
}