<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
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

        $role = method_exists($user, 'normalizedRole')
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
            |--------------------------------------------------------------------------
            | NOMOR AGENDA
            |--------------------------------------------------------------------------
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
            |--------------------------------------------------------------------------
            | NOMOR SURAT
            |--------------------------------------------------------------------------
            */
            'nomor_surat' => [
                'required',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | PENGIRIM
            |--------------------------------------------------------------------------
            */
            'pengirim' => [
                'required',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | TANGGAL SURAT
            |--------------------------------------------------------------------------
            */
            'tanggal_surat' => [
                'required',
                'date',
            ],

            /*
            |--------------------------------------------------------------------------
            | TANGGAL TERIMA
            |--------------------------------------------------------------------------
            */
            'tanggal_terima' => [
                'required',
                'date',
                'after_or_equal:tanggal_surat',
            ],

            /*
            |--------------------------------------------------------------------------
            | KATEGORI
            |--------------------------------------------------------------------------
            */
            'kategori_surat_id' => [
                'required',
                'integer',
                'exists:kategori_surats,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | PERIHAL
            |--------------------------------------------------------------------------
            */
            'perihal' => [
                'required',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | RINGKASAN
            |--------------------------------------------------------------------------
            */
            'ringkasan' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
            |--------------------------------------------------------------------------
            | LAMPIRAN FILE
            |--------------------------------------------------------------------------
            |
            | Format:
            | - PDF
            | - JPG
            | - JPEG
            | - PNG
            |
            | Maksimal 15 MB.
            |
            | CREATE:
            | wajib ada file atau hasil kamera.
            |
            | EDIT:
            | file boleh kosong karena file lama dipertahankan.
            |
            */
            'lampiran_file' => [
                'nullable',
                'file',
                'max:15360',
                'mimes:pdf,jpg,jpeg,png',
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
            |--------------------------------------------------------------------------
            | LOKASI ARSIP FISIK
            |--------------------------------------------------------------------------
            */
            'lokasi_arsip_fisik' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Persiapan data sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nomor_agenda' => $this->cleanInput('nomor_agenda'),

            'nomor_surat' => $this->cleanInput('nomor_surat'),

            'pengirim' => $this->cleanInput('pengirim'),

            'perihal' => $this->cleanInput('perihal'),

            'ringkasan' => $this->cleanInput('ringkasan'),

            'status' => $this->cleanStatus(),

            'lokasi_arsip_fisik' => $this->cleanInput(
                'lokasi_arsip_fisik'
            ),

            'captured_image' => $this->cleanCapturedImage(),
        ]);
    }

    /**
     * Validasi tambahan.
     */
    protected function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (Validator $validator): void {
                /*
                |--------------------------------------------------------------------------
                | INFORMASI FILE
                |--------------------------------------------------------------------------
                */
                $hasFile = $this->hasFile('lampiran_file');

                $file = $this->file('lampiran_file');

                $hasValidFile =
                    $hasFile
                    && $file !== null
                    && $file->isValid();

                $hasCamera = $this->filled('captured_image');

                /*
                |--------------------------------------------------------------------------
                | DEBUG UPLOAD
                |--------------------------------------------------------------------------
                |
                | Mengetahui apakah JPG gagal di PHP atau
                | baru gagal di validasi Laravel.
                |
                */
                if ($hasFile && $file !== null) {
                    Log::info(
                        'SURAT MASUK - DEBUG UPLOAD',
                        [
                            'has_file' => $hasFile,
                            'error' => $file->getError(),
                            'original_name' => $file->getClientOriginalName(),
                            'client_extension' => $file->getClientOriginalExtension(),
                            'client_mime' => $file->getClientMimeType(),
                            'detected_mime' => $file->getMimeType(),
                            'size' => $file->getSize(),
                            'real_path' => $file->getRealPath(),
                            'is_valid' => $file->isValid(),
                        ]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | ERROR UPLOAD PHP
                |--------------------------------------------------------------------------
                */
                if (
                    $hasFile
                    && $file !== null
                    && !$file->isValid()
                ) {
                    $errorCode = $file->getError();

                    $message = match ($errorCode) {
                        UPLOAD_ERR_INI_SIZE =>
                            'Ukuran file melebihi batas upload server.',

                        UPLOAD_ERR_FORM_SIZE =>
                            'Ukuran file melebihi batas form.',

                        UPLOAD_ERR_PARTIAL =>
                            'File hanya terupload sebagian. Silakan coba lagi.',

                        UPLOAD_ERR_NO_FILE =>
                            'Tidak ada file yang dipilih.',

                        UPLOAD_ERR_NO_TMP_DIR =>
                            'Folder temporary upload PHP tidak tersedia.',

                        UPLOAD_ERR_CANT_WRITE =>
                            'PHP gagal menulis file upload.',

                        UPLOAD_ERR_EXTENSION =>
                            'Upload file dihentikan oleh konfigurasi PHP.',

                        default =>
                            'File gagal diupload. Kode upload PHP: '
                            . $errorCode,
                    };

                    $validator->errors()->add(
                        'lampiran_file',
                        $message
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | FILE DAN KAMERA TIDAK BOLEH BERSAMAAN
                |--------------------------------------------------------------------------
                */
                if (
                    $hasValidFile
                    && $hasCamera
                ) {
                    $validator->errors()->add(
                        'lampiran_file',
                        'Gunakan salah satu metode saja: upload file atau scan kamera.'
                    );

                    $validator->errors()->add(
                        'captured_image',
                        'Gunakan salah satu metode saja: upload file atau scan kamera.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | CREATE
                |--------------------------------------------------------------------------
                |
                | Saat create:
                | harus ada file atau hasil kamera.
                |
                */
                if ($this->isMethod('POST')) {
                    if (
                        !$hasValidFile
                        && !$hasCamera
                    ) {
                        $validator->errors()->add(
                            'lampiran_file',
                            'Berkas digital wajib diupload atau discan menggunakan kamera.'
                        );
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | TIDAK ADA KAMERA
                |--------------------------------------------------------------------------
                */
                if (!$hasCamera) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | DATA URI KAMERA
                |--------------------------------------------------------------------------
                |
                | Format:
                | data:image/jpeg;base64,...
                | data:image/jpg;base64,...
                | data:image/png;base64,...
                |
                */
                $captured = trim(
                    (string) $this->input('captured_image')
                );

                if (
                    !preg_match(
                        '/^data:image\/(jpeg|jpg|png);base64,/i',
                        $captured
                    )
                ) {
                    $validator->errors()->add(
                        'captured_image',
                        'Format hasil scan kamera tidak valid.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | AMBIL BASE64
                |--------------------------------------------------------------------------
                */
                $parts = explode(
                    ',',
                    $captured,
                    2
                );

                if (count($parts) !== 2) {
                    $validator->errors()->add(
                        'captured_image',
                        'Data hasil kamera tidak valid.'
                    );

                    return;
                }

                $encoded = $parts[1];

                /*
                |--------------------------------------------------------------------------
                | DECODE BASE64
                |--------------------------------------------------------------------------
                */
                $decoded = base64_decode(
                    $encoded,
                    true
                );

                if (
                    $decoded === false
                    || $decoded === ''
                ) {
                    $validator->errors()->add(
                        'captured_image',
                        'Data hasil kamera tidak valid.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | BATAS UKURAN
                |--------------------------------------------------------------------------
                */
                if (
                    strlen($decoded)
                    > 15 * 1024 * 1024
                ) {
                    $validator->errors()->add(
                        'captured_image',
                        'Ukuran hasil kamera maksimal 15 MB.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | PASTIKAN BENAR-BENAR GAMBAR
                |--------------------------------------------------------------------------
                */
                $imageInfo = @getimagesizefromstring(
                    $decoded
                );

                if ($imageInfo === false) {
                    $validator->errors()->add(
                        'captured_image',
                        'Data hasil kamera bukan gambar yang valid.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | CEK MIME GAMBAR
                |--------------------------------------------------------------------------
                */
                $actualMime = strtolower(
                    (string) (
                        $imageInfo['mime']
                        ?? ''
                    )
                );

                if ($actualMime === 'image/jpg') {
                    $actualMime = 'image/jpeg';
                }

                if (
                    !in_array(
                        $actualMime,
                        [
                            'image/jpeg',
                            'image/png',
                        ],
                        true
                    )
                ) {
                    $validator->errors()->add(
                        'captured_image',
                        'Jenis gambar hasil scan tidak didukung.'
                    );
                }
            }
        );
    }

    /**
     * Membersihkan input string.
     */
    private function cleanInput(
        string $key
    ): ?string {
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
                ':attribute harus berformat PDF, JPG, JPEG, atau PNG.',

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