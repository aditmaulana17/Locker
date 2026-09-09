<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratKeluarRequest extends FormRequest
{
    /**
     * Mengecek apakah user boleh mengelola surat keluar.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $role = method_exists(
            $user,
            'normalizedRole'
        )
            ? $user->normalizedRole()
            : User::normalizeRole(
                (string) (
                    $user->role
                    ?? $user->jabatan
                    ?? ''
                )
            );

        return in_array(
            $role,
            [
                User::ROLE_ADMIN,
                User::ROLE_PIMPINAN,
            ],
            true
        );
    }

    /**
     * Aturan validasi.
     */
    public function rules(): array
    {
        $suratKeluar = $this->route('suratKeluar')
            ?? $this->route('surat_keluar');

        $suratKeluarId = is_object($suratKeluar)
            ? $suratKeluar->id
            : $suratKeluar;

        return [
            'nomor_surat' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(
                    'surat_keluars',
                    'nomor_surat'
                )->ignore($suratKeluarId),
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
                'max:5000',
            ],

            /*
            |--------------------------------------------------------------------------
            | FILE
            |--------------------------------------------------------------------------
            |
            | Maksimal 15MB.
            | File boleh kosong karena edit bisa mempertahankan file lama
            | dan create juga mendukung hasil kamera.
            |
            */
            'lampiran_file' => [
                'nullable',
                'file',
                'max:15360',
                'mimes:pdf,jpg,jpeg,png,webp',
            ],

            /*
            |--------------------------------------------------------------------------
            | HASIL KAMERA
            |--------------------------------------------------------------------------
            */
            'captured_image' => [
                'nullable',
                'string',
            ],

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */
            'status' => [
                'nullable',
                Rule::in([
                    'draft',
                    'draf',
                    'diproses',
                    'disetujui',
                    'dikirim',
                    'diarsipkan',
                ]),
            ],

            /*
            |--------------------------------------------------------------------------
            | PENANDATANGAN
            |--------------------------------------------------------------------------
            */
            'ditandatangani_oleh' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
        ];
    }

    /**
     * Persiapan data sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nomor_surat' => $this->filled('nomor_surat')
                ? trim(
                    (string) $this->input('nomor_surat')
                )
                : null,

            'pengirim' => $this->filled('pengirim')
                ? trim(
                    (string) $this->input('pengirim')
                )
                : null,

            'perihal' => $this->filled('perihal')
                ? trim(
                    (string) $this->input('perihal')
                )
                : null,

            'ringkasan' => $this->filled('ringkasan')
                ? trim(
                    (string) $this->input('ringkasan')
                )
                : null,

            'status' => $this->filled('status')
                ? strtolower(
                    trim(
                        (string) $this->input('status')
                    )
                )
                : 'draft',

            'captured_image' => $this->filled('captured_image')
                ? trim(
                    (string) $this->input('captured_image')
                )
                : null,
        ]);
    }

    /**
     * Validasi tambahan.
     */
    protected function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (Validator $validator) {
                $hasFile = $this->hasFile(
                    'lampiran_file'
                );

                $fileIsValid = $hasFile
                    && $this->file('lampiran_file')?->isValid();

                $capturedImage = trim(
                    (string) $this->input(
                        'captured_image',
                        ''
                    )
                );

                $hasCamera = $capturedImage !== '';

                /*
                |--------------------------------------------------------------------------
                | FILE DAN KAMERA TIDAK BOLEH BERSAMAAN
                |--------------------------------------------------------------------------
                */

                if (
                    $fileIsValid
                    && $hasCamera
                ) {
                    $validator->errors()->add(
                        'lampiran_file',
                        'Gunakan salah satu metode: upload file atau kamera.'
                    );

                    $validator->errors()->add(
                        'captured_image',
                        'Gunakan salah satu metode: upload file atau kamera.'
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | CEK ERROR UPLOAD
                |--------------------------------------------------------------------------
                */

                if (
                    $hasFile
                    && !$fileIsValid
                ) {
                    $file = $this->file(
                        'lampiran_file'
                    );

                    if ($file) {
                        $message = match (
                            $file->getError()
                        ) {
                            UPLOAD_ERR_INI_SIZE =>
                                'Ukuran file melebihi batas upload server.',

                            UPLOAD_ERR_FORM_SIZE =>
                                'Ukuran file melebihi batas form.',

                            UPLOAD_ERR_PARTIAL =>
                                'File hanya terupload sebagian.',

                            UPLOAD_ERR_NO_FILE =>
                                'Tidak ada file yang dipilih.',

                            UPLOAD_ERR_NO_TMP_DIR =>
                                'Folder temporary upload tidak tersedia.',

                            UPLOAD_ERR_CANT_WRITE =>
                                'Server gagal menulis file upload.',

                            UPLOAD_ERR_EXTENSION =>
                                'Upload dihentikan oleh ekstensi PHP.',

                            default =>
                                'File lampiran tidak valid.',
                        };

                        $validator->errors()->add(
                            'lampiran_file',
                            $message
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | VALIDASI HASIL KAMERA
                |--------------------------------------------------------------------------
                */

                if ($hasCamera) {
                    if (
                        !preg_match(
                            '/^data:image\/(jpeg|jpg|png|webp);base64,/i',
                            $capturedImage
                        )
                    ) {
                        $validator->errors()->add(
                            'captured_image',
                            'Format hasil kamera tidak valid.'
                        );
                    } else {
                        [
                            ,
                            $base64
                        ] = explode(
                            ',',
                            $capturedImage,
                            2
                        );

                        $decoded = base64_decode(
                            $base64,
                            true
                        );

                        if ($decoded === false) {
                            $validator->errors()->add(
                                'captured_image',
                                'Data hasil kamera tidak valid.'
                            );
                        } elseif (
                            strlen($decoded)
                            > 15 * 1024 * 1024
                        ) {
                            $validator->errors()->add(
                                'captured_image',
                                'Ukuran hasil kamera maksimal 15MB.'
                            );
                        }
                    }
                }
            }
        );
    }

    /**
     * Pesan validasi.
     */
    public function messages(): array
    {
        return [
            'nomor_surat.string' =>
                'Nomor surat harus berupa teks.',

            'nomor_surat.max' =>
                'Nomor surat maksimal 255 karakter.',

            'nomor_surat.unique' =>
                'Nomor surat tersebut sudah digunakan.',

            'tanggal_surat.required' =>
                'Tanggal surat wajib diisi.',

            'tanggal_surat.date' =>
                'Tanggal surat harus berupa tanggal yang valid.',

            'tanggal_keluar.required' =>
                'Tanggal keluar wajib diisi.',

            'tanggal_keluar.date' =>
                'Tanggal keluar harus berupa tanggal yang valid.',

            'tanggal_keluar.after_or_equal' =>
                'Tanggal keluar tidak boleh sebelum tanggal surat.',

            'pengirim.required' =>
                'Nama pengirim / instansi wajib diisi.',

            'pengirim.string' =>
                'Nama pengirim / instansi harus berupa teks.',

            'pengirim.max' =>
                'Nama pengirim / instansi maksimal 150 karakter.',

            'kategori_surat_id.required' =>
                'Kategori surat wajib dipilih.',

            'kategori_surat_id.integer' =>
                'Kategori surat tidak valid.',

            'kategori_surat_id.exists' =>
                'Kategori surat yang dipilih tidak tersedia.',

            'perihal.required' =>
                'Perihal surat wajib diisi.',

            'perihal.string' =>
                'Perihal surat harus berupa teks.',

            'perihal.max' =>
                'Perihal surat maksimal 255 karakter.',

            'ringkasan.string' =>
                'Ringkasan harus berupa teks.',

            'ringkasan.max' =>
                'Ringkasan maksimal 5000 karakter.',

            'lampiran_file.file' =>
                'Lampiran harus berupa file yang valid.',

            'lampiran_file.mimes' =>
                'Lampiran harus berformat PDF, JPG, JPEG, PNG, atau WEBP.',

            'lampiran_file.max' =>
                'Ukuran file lampiran maksimal 15MB.',

            'captured_image.string' =>
                'Hasil scan kamera tidak valid.',

            'status.in' =>
                'Status surat yang dipilih tidak valid.',

            'ditandatangani_oleh.integer' =>
                'Data penandatangan tidak valid.',

            'ditandatangani_oleh.exists' =>
                'Pengguna penandatangan tidak ditemukan.',
        ];
    }

    /**
     * Nama atribut validasi.
     */
    public function attributes(): array
    {
        return [
            'nomor_surat' =>
                'Nomor Surat',

            'tanggal_surat' =>
                'Tanggal Surat',

            'tanggal_keluar' =>
                'Tanggal Keluar',

            'pengirim' =>
                'Pengirim / Instansi',

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

            'ditandatangani_oleh' =>
                'Penandatangan',
        ];
    }
}