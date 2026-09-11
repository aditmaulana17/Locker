<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SuratMasukRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | CONSTANT
    |--------------------------------------------------------------------------
    */

    /**
     * Maksimal file yang boleh dikirim user.
     *
     * 10240 KB = 10 MB.
     */
    private const MAX_FILE_SIZE_KB = 10240;

    /**
     * Maksimal hasil kamera setelah Base64 di-decode.
     *
     * 10 MB.
     */
    private const MAX_CAMERA_SIZE = 10 * 1024 * 1024;

    /**
     * Role yang boleh membuat dan mengubah surat masuk.
     */
    private const MANAGE_ROLES = [
        'admin',
        'pimpinan',
    ];

    /**
     * Status surat yang diperbolehkan.
     */
    private const STATUS_OPTIONS = [
        'baru',
        'diproses',
        'didisposisikan',
        'selesai',
        'diarsipkan',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTHORIZE
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | RULES
    |--------------------------------------------------------------------------
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
            | User boleh upload:
            |
            | PDF
            | JPG
            | JPEG
            | PNG
            |
            | Maksimal file asli = 10 MB.
            |
            | PDF:
            |   temporary PHP
            |       ↓
            |   Supabase
            |
            | JPG/PNG:
            |   temporary PHP
            |       ↓
            |   GD
            |       ↓
            |   JPG terkompres
            |       ↓
            |   Supabase
            |
            | File asli gambar tidak disimpan permanen.
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
            | HASIL KAMERA
            |--------------------------------------------------------------------------
            |
            | Data URI Base64:
            |
            | data:image/jpeg;base64,...
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
                Rule::in(
                    self::STATUS_OPTIONS
                ),
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

    /*
    |--------------------------------------------------------------------------
    | PREPARE FOR VALIDATION
    |--------------------------------------------------------------------------
    */

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nomor_agenda' => $this->cleanInput(
                'nomor_agenda'
            ),

            'nomor_surat' => $this->cleanInput(
                'nomor_surat'
            ),

            'pengirim' => $this->cleanInput(
                'pengirim'
            ),

            'perihal' => $this->cleanInput(
                'perihal'
            ),

            'ringkasan' => $this->cleanInput(
                'ringkasan'
            ),

            'status' => $this->cleanStatus(),

            'lokasi_arsip_fisik' => $this->cleanInput(
                'lokasi_arsip_fisik'
            ),

            'captured_image' => $this->cleanCapturedImage(),
        ]);

        $this->logUploadDebug();
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATOR
    |--------------------------------------------------------------------------
    */

    protected function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (Validator $validator): void {

                $file = $this->file(
                    'lampiran_file'
                );

                $hasFile = $this->hasFile(
                    'lampiran_file'
                );

                $hasValidFile =
                    $hasFile
                    && $file !== null
                    && $file->isValid();

                $hasCamera = $this->filled(
                    'captured_image'
                );

                /*
                |--------------------------------------------------------------------------
                | UPLOAD PHP ERROR
                |--------------------------------------------------------------------------
                */

                if (
                    $hasFile
                    && $file !== null
                    && !$file->isValid()
                ) {
                    $validator->errors()->add(
                        'lampiran_file',
                        $this->getUploadErrorMessage(
                            $file->getError()
                        )
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | FILE + CAMERA
                |--------------------------------------------------------------------------
                |
                | Tidak boleh dikirim bersamaan.
                |
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
                | POST harus mempunyai lampiran.
                |
                */

                if (
                    $this->isMethod('POST')
                    && !$hasValidFile
                    && !$hasCamera
                ) {
                    $validator->errors()->add(
                        'lampiran_file',
                        'Berkas digital wajib diupload '
                        . 'atau discan menggunakan kamera.'
                    );

                    return;
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
                | VALIDASI KAMERA
                |--------------------------------------------------------------------------
                */

                $this->validateCapturedImage(
                    $validator
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATE CAMERA
    |--------------------------------------------------------------------------
    */

    private function validateCapturedImage(
        Validator $validator
    ): void {
        $captured = trim(
            (string) $this->input(
                'captured_image'
            )
        );

        /*
        |--------------------------------------------------------------------------
        | DATA URI
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '~^data:image/(jpeg|jpg|png);base64,~i',
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
        | SPLIT HEADER + DATA
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

        [
            $header,
            $encoded
        ] = $parts;

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '~^data:image/(jpeg|jpg|png);base64$~i',
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
        | BASE64
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
        | SIZE
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
        | IMAGE VALIDATION
        |--------------------------------------------------------------------------
        */

        $imageInfo =
            @getimagesizefromstring(
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
        | MIME
        |--------------------------------------------------------------------------
        */

        $actualMime =
            strtolower(
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

    /*
    |--------------------------------------------------------------------------
    | CLEAN INPUT
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | CLEAN STATUS
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | CLEAN CAMERA
    |--------------------------------------------------------------------------
    */

    private function cleanCapturedImage(): ?string
    {
        if (
            !$this->filled(
                'captured_image'
            )
        ) {
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

    /*
    |--------------------------------------------------------------------------
    | UPLOAD ERROR
    |--------------------------------------------------------------------------
    */

    private function getUploadErrorMessage(
        int $errorCode
    ): string {
        return match ($errorCode) {

            UPLOAD_ERR_INI_SIZE =>
                'Ukuran file melebihi batas upload server.',

            UPLOAD_ERR_FORM_SIZE =>
                'Ukuran file melebihi batas upload form.',

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
    }

    /*
    |--------------------------------------------------------------------------
    | DEBUG UPLOAD
    |--------------------------------------------------------------------------
    */

    private function logUploadDebug(): void
    {
        try {
            $file = $this->file(
                'lampiran_file'
            );

            Log::info(
                'SURAT MASUK REQUEST',
                [
                    'method' =>
                        $this->method(),

                    'content_type' =>
                        $_SERVER['CONTENT_TYPE']
                        ?? null,

                    'content_length' =>
                        $_SERVER['CONTENT_LENGTH']
                        ?? null,

                    'has_file' =>
                        $this->hasFile(
                            'lampiran_file'
                        ),

                    'file_exists' =>
                        $file !== null,

                    'file_error' =>
                        $file
                            ? $file->getError()
                            : null,

                    'file_error_message' =>
                        $file
                            ? $file->getErrorMessage()
                            : null,

                    'file_name' =>
                        $file
                            ? $file->getClientOriginalName()
                            : null,

                    'file_extension' =>
                        $file
                            ? $file->getClientOriginalExtension()
                            : null,

                    'file_client_mime' =>
                        $file
                            ? $file->getClientMimeType()
                            : null,

                    'file_size' =>
                        $file
                            ? $file->getSize()
                            : null,

                    'file_valid' =>
                        $file
                            ? $file->isValid()
                            : null,

                    'real_path' =>
                        $file
                            ? $file->getRealPath()
                            : null,

                    'tmp_exists' =>
                        $file
                            ? (
                                $file->getRealPath()
                                    ? file_exists(
                                        $file->getRealPath()
                                    )
                                    : false
                            )
                            : null,

                    'has_captured_image' =>
                        $this->filled(
                            'captured_image'
                        ),
                ]
            );
        } catch (\Throwable $e) {
            Log::error(
                'SURAT MASUK REQUEST DEBUG GAGAL',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MESSAGES
    |--------------------------------------------------------------------------
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
                ':attribute tidak boleh lebih awal '
                . 'daripada tanggal surat.',

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

    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTES
    |--------------------------------------------------------------------------
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