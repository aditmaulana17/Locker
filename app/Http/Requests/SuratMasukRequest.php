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
     * Maksimal ukuran file upload: 10 MB.
     *
     * Rule Laravel max menggunakan satuan KB.
     */
    private const MAX_FILE_SIZE_KB = 10240;

    /**
     * Maksimal ukuran hasil scan kamera: 10 MB.
     */
    private const MAX_CAMERA_SIZE = 10 * 1024 * 1024;

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
            | KATEGORI SURAT
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
            | CREATE:
            | File wajib ada atau menggunakan kamera.
            |
            | EDIT:
            | File boleh kosong sehingga file lama tetap digunakan.
            |
            | Maksimal file asli: 10 MB.
            |
            */
            'lampiran_file' => [
                'nullable',
                'file',
                'max:' . self::MAX_FILE_SIZE_KB,
                'mimes:pdf,jpg,jpeg,png',
            ],

            /*
            |--------------------------------------------------------------------------
            | HASIL SCAN KAMERA
            |--------------------------------------------------------------------------
            |
            | Berisi Data URI Base64 dari kamera.
            |
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
        $file = $this->file('lampiran_file');

        /*
        |--------------------------------------------------------------------------
        | DEBUG UPLOAD
        |--------------------------------------------------------------------------
        |
        | Membantu mengetahui apakah file benar-benar diterima
        | oleh PHP sebelum masuk proses validasi Laravel.
        |
        */
        try {
            Log::info(
                'SURAT MASUK REQUEST DEBUG',
                [
                    'method' => $this->method(),
                    'real_method' => $_SERVER['REQUEST_METHOD'] ?? null,
                    'content_type' => $_SERVER['CONTENT_TYPE'] ?? null,
                    'content_length' => $_SERVER['CONTENT_LENGTH'] ?? null,

                    'has_file' => $this->hasFile('lampiran_file'),

                    'file_exists' => $file !== null,

                    'file_error' => $file
                        ? $file->getError()
                        : null,

                    'file_error_message' => $file
                        ? $file->getErrorMessage()
                        : null,

                    'file_name' => $file
                        ? $file->getClientOriginalName()
                        : null,

                    'file_extension' => $file
                        ? $file->getClientOriginalExtension()
                        : null,

                    'file_client_mime' => $file
                        ? $file->getClientMimeType()
                        : null,

                    'file_size' => $file
                        ? $file->getSize()
                        : null,

                    'file_valid' => $file
                        ? $file->isValid()
                        : null,

                    'real_path' => $file
                        ? $file->getRealPath()
                        : null,

                    'tmp_exists' => $file
                        ? (
                            $file->getRealPath()
                                ? file_exists($file->getRealPath())
                                : false
                        )
                        : null,

                    'has_captured_image' => $this->filled(
                        'captured_image'
                    ),
                ]
            );
        } catch (\Throwable $e) {
            Log::error(
                'SURAT MASUK REQUEST DEBUG GAGAL',
                [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CLEAN INPUT
        |--------------------------------------------------------------------------
        */
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
     * Validasi tambahan setelah rules utama selesai.
     */
    protected function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (Validator $validator): void {
                $hasFile = $this->hasFile('lampiran_file');

                $file = $this->file('lampiran_file');

                $hasValidFile =
                    $hasFile
                    && $file !== null
                    && $file->isValid();

                $hasCamera = $this->filled('captured_image');

                /*
                |--------------------------------------------------------------------------
                | DEBUG HASIL FILE
                |--------------------------------------------------------------------------
                */
                try {
                    Log::info(
                        'SURAT MASUK VALIDATION DEBUG',
                        [
                            'method' => $this->method(),

                            'has_file' => $hasFile,

                            'has_valid_file' => $hasValidFile,

                            'has_camera' => $hasCamera,

                            'error' => $file
                                ? $file->getError()
                                : null,

                            'error_message' => $file
                                ? $file->getErrorMessage()
                                : null,

                            'name' => $file
                                ? $file->getClientOriginalName()
                                : null,

                            'extension' => $file
                                ? $file->getClientOriginalExtension()
                                : null,

                            'client_mime' => $file
                                ? $file->getClientMimeType()
                                : null,

                            'size' => $file
                                ? $file->getSize()
                                : null,

                            'real_path' => $file
                                ? $file->getRealPath()
                                : null,

                            'tmp_exists' => $file
                                ? (
                                    $file->getRealPath()
                                        ? file_exists(
                                            $file->getRealPath()
                                        )
                                        : false
                                )
                                : null,

                            'valid' => $file
                                ? $file->isValid()
                                : null,
                        ]
                    );
                } catch (\Throwable $e) {
                    Log::error(
                        'SURAT MASUK VALIDATION DEBUG GAGAL',
                        [
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
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
                    $message =
                        'Gunakan salah satu metode saja: '
                        . 'upload file atau scan kamera.';

                    $validator->errors()->add(
                        'lampiran_file',
                        $message
                    );

                    $validator->errors()->add(
                        'captured_image',
                        $message
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | CREATE
                |--------------------------------------------------------------------------
                |
                | Saat membuat surat baru, lampiran digital wajib
                | berasal dari upload file atau scan kamera.
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

                        return;
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
                | AMBIL DATA BASE64
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

                $header = $parts[0];
                $encoded = $parts[1];

                /*
                |--------------------------------------------------------------------------
                | VALIDASI HEADER DATA URI
                |--------------------------------------------------------------------------
                */
                if (
                    !preg_match(
                        '/^data:image\/(jpeg|jpg|png);base64$/i',
                        $header
                    )
                ) {
                    $validator->errors()->add(
                        'captured_image',
                        'Header hasil scan kamera tidak valid.'
                    );

                    return;
                }

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
                | BATAS UKURAN SCAN KAMERA
                |--------------------------------------------------------------------------
                */
                if (
                    strlen($decoded)
                    > self::MAX_CAMERA_SIZE
                ) {
                    $validator->errors()->add(
                        'captured_image',
                        'Ukuran hasil kamera maksimal 10 MB.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | VALIDASI GAMBAR
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
                        $imageInfo['mime'] ?? ''
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
                ':attribute maksimal berukuran 10 MB.',

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