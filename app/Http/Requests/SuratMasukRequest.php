<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratMasukRequest extends FormRequest
{
    /**
     * Hanya admin dan pimpinan yang dapat
     * membuat dan mengubah surat masuk.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $role = method_exists(User::class, 'normalizeRole')
            ? User::normalizeRole(
                $user->role ?? $user->jabatan ?? ''
            )
            : strtolower(
                trim(
                    (string) (
                        $user->role
                        ?? $user->jabatan
                        ?? ''
                    )
                )
            );

        if ($role === 'staf') {
            $role = 'staff';
        }

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
             * FILE
             * =====================================================
             *
             * PDF
             * JPG
             * JPEG
             * PNG
             * WEBP
             *
             * Maksimal 15 MB.
             *
             * Pada CREATE akan diverifikasi melalui withValidator().
             * Pada EDIT boleh kosong karena file lama dipertahankan.
             */
            'lampiran_file' => [
                'nullable',
                'file',
                'max:15360',
                'mimes:pdf,jpg,jpeg,png,webp',
            ],

            /*
             * =====================================================
             * CAPTURE KAMERA
             * =====================================================
             */
            'captured_image' => [
                'nullable',
                'string',
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
             * LOKASI FISIK
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
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(
            function (Validator $validator): void {

                $hasFile =
                    $this->hasFile('lampiran_file') &&
                    $this->file('lampiran_file') !== null;

                $file = $this->file('lampiran_file');

                $hasValidFile =
                    $hasFile &&
                    $file !== null &&
                    $file->isValid();

                $hasCamera =
                    $this->filled('captured_image');

                /*
                 * =================================================
                 * CEK FILE + KAMERA
                 * =================================================
                 */
                if ($hasValidFile && $hasCamera) {
                    $validator->errors()->add(
                        'lampiran_file',
                        'Gunakan salah satu metode saja: upload file atau scan kamera.'
                    );
                }

                /*
                 * =================================================
                 * CREATE
                 * =================================================
                 */
                if ($this->isMethod('POST')) {

                    if (!$hasValidFile && !$hasCamera) {
                        $validator->errors()->add(
                            'lampiran_file',
                            'Berkas digital wajib diupload atau discan menggunakan kamera.'
                        );
                    }
                }

                /*
                 * =================================================
                 * ERROR UPLOAD PHP
                 * =================================================
                 */
                if ($hasFile && $file !== null) {

                    $errorCode = $file->getError();

                    if ($errorCode !== UPLOAD_ERR_OK) {

                        $message = match ($errorCode) {

                            UPLOAD_ERR_INI_SIZE,
                            UPLOAD_ERR_FORM_SIZE =>
                                'Ukuran file terlalu besar. Maksimal 15 MB.',

                            UPLOAD_ERR_PARTIAL =>
                                'File hanya terupload sebagian. Silakan coba lagi.',

                            UPLOAD_ERR_NO_FILE =>
                                'Tidak ada file yang dipilih.',

                            UPLOAD_ERR_NO_TMP_DIR =>
                                'Folder temporary upload PHP tidak tersedia.',

                            UPLOAD_ERR_CANT_WRITE =>
                                'Server gagal menulis file upload.',

                            UPLOAD_ERR_EXTENSION =>
                                'Upload file dihentikan oleh ekstensi PHP.',

                            default =>
                                'File gagal diupload. Silakan coba lagi.',
                        };

                        $validator->errors()->add(
                            'lampiran_file',
                            $message
                        );
                    }
                }

                /*
                 * =================================================
                 * VALIDASI CAPTURED IMAGE
                 * =================================================
                 */
                if ($hasCamera) {

                    $captured = trim(
                        (string) $this->input(
                            'captured_image'
                        )
                    );

                    if (
                        !preg_match(
                            '/^data:image\/(jpeg|jpg|png|webp);base64,/i',
                            $captured
                        )
                    ) {
                        $validator->errors()->add(
                            'captured_image',
                            'Format hasil scan kamera tidak valid.'
                        );
                    }
                }
            }
        );
    }

    /**
     * Membersihkan input.
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
            (string) $this->input(
                'captured_image'
            )
        );

        return $value !== ''
            ? $value
            : null;
    }

    /**
     * Pesan validasi.
     */
    public function messages(): array
    {
        return [
            'nomor_agenda.unique' =>
                'Nomor agenda ini sudah digunakan oleh surat lain.',

            'nomor_agenda.max' =>
                ':attribute maksimal 50 karakter.',

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
                ':attribute maksimal berukuran 15 MB.',

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
     * Nama atribut.
     */
    public function attributes(): array
    {
        return [
            'nomor_agenda' =>
                'Nomor Agenda',

            'nomor_surat' =>
                'Nomor Surat',

            'pengirim' =>
                'Instansi Pengirim',

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
                'Berkas Digital',

            'captured_image' =>
                'Hasil Scan Kamera',

            'status' =>
                'Status Surat',

            'lokasi_arsip_fisik' =>
                'Lokasi Arsip Fisik',
        ];
    }
}