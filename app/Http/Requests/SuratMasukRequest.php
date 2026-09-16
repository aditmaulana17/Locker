<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratMasukRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI
    |--------------------------------------------------------------------------
    */

    /**
     * Maksimal ukuran file asli:
     * 10 MB = 10240 KB.
     */
    private const MAX_FILE_SIZE_KB =
        10240;

    /**
     * Maksimal ukuran file dalam byte.
     */
    private const MAX_FILE_SIZE_BYTES =
        10 * 1024 * 1024;

    /**
     * Maksimal ukuran hasil kamera
     * setelah Base64 di-decode.
     */
    private const MAX_CAMERA_SIZE_BYTES =
        10 * 1024 * 1024;

    /**
     * Role yang boleh membuat/mengubah
     * Surat Masuk.
     */
    private const MANAGE_ROLES = [
        'admin',
        'pimpinan',
    ];

    /**
     * Status Surat Masuk.
     */
    private const STATUS_OPTIONS = [
        'baru',
        'diproses',
        'didisposisikan',
        'selesai',
        'diarsipkan',
    ];

    /**
     * Extension file yang diperbolehkan.
     */
    private const ALLOWED_FILE_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
    ];

    /**
     * MIME file yang diperbolehkan.
     */
    private const ALLOWED_FILE_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    /**
     * MIME hasil scan kamera.
     */
    private const ALLOWED_CAMERA_MIMES = [
        'image/jpeg',
        'image/png',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTHORIZE
    |--------------------------------------------------------------------------
    */

    public function authorize(): bool
    {
        $user =
            $this->user();

        if (
            !$user
        ) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | NORMALIZED ROLE
        |--------------------------------------------------------------------------
        */

        if (
            method_exists(
                $user,
                'normalizedRole'
            )
        ) {
            $role =
                $user->normalizedRole();

        } elseif (
            method_exists(
                User::class,
                'normalizeRole'
            )
        ) {
            $role =
                User::normalizeRole(
                    (string) (
                        $user->role ??
                        $user->jabatan ??
                        ''
                    )
                );

        } else {
            $role =
                strtolower(
                    trim(
                        (string) (
                            $user->role ??
                            $user->jabatan ??
                            ''
                        )
                    )
                );

            if (
                $role === 'staf'
            ) {
                $role =
                    'staff';
            }
        }

        return in_array(
            $role,
            self::MANAGE_ROLES,
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
        $suratMasuk =
            $this->route(
                'suratMasuk'
            )
            ??
            $this->route(
                'surat_masuk'
            );

        $suratMasukId =
            null;

        if (
            is_object(
                $suratMasuk
            )
        ) {
            $suratMasukId =
                $suratMasuk->id ??
                null;

        } elseif (
            is_numeric(
                $suratMasuk
            )
        ) {
            $suratMasukId =
                (int) $suratMasuk;
        }

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
                )->ignore(
                    $suratMasukId
                ),
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
            | TANGGAL DITERIMA
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
            | CREATE:
            |   boleh lolos rule dasar di sini,
            |   kemudian diwajibkan pada validator tambahan
            |   bahwa harus ada file atau hasil scan kamera.
            |
            | EDIT:
            |   boleh kosong karena file lama dapat dipertahankan.
            |
            */

            'lampiran_file' => [
                'nullable',
                'file',
                'max:' .
                self::MAX_FILE_SIZE_KB,
                'mimes:pdf,jpg,jpeg,png',
            ],

            /*
            |--------------------------------------------------------------------------
            | CAPTURED IMAGE
            |--------------------------------------------------------------------------
            |
            | Format:
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

            'nomor_agenda' =>
                $this->cleanString(
                    $this->input(
                        'nomor_agenda'
                    )
                ),

            'nomor_surat' =>
                $this->cleanString(
                    $this->input(
                        'nomor_surat'
                    )
                ),

            'pengirim' =>
                $this->cleanString(
                    $this->input(
                        'pengirim'
                    )
                ),

            'tanggal_surat' =>
                $this->cleanString(
                    $this->input(
                        'tanggal_surat'
                    )
                ),

            'tanggal_terima' =>
                $this->cleanString(
                    $this->input(
                        'tanggal_terima'
                    )
                ),

            'kategori_surat_id' =>
                $this->cleanInteger(
                    $this->input(
                        'kategori_surat_id'
                    )
                ),

            'perihal' =>
                $this->cleanString(
                    $this->input(
                        'perihal'
                    )
                ),

            'ringkasan' =>
                $this->cleanString(
                    $this->input(
                        'ringkasan'
                    )
                ),

            'status' =>
                $this->cleanStatus(),

            'lokasi_arsip_fisik' =>
                $this->cleanString(
                    $this->input(
                        'lokasi_arsip_fisik'
                    )
                ),

            'captured_image' =>
                $this->cleanCapturedImage(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATOR TAMBAHAN
    |--------------------------------------------------------------------------
    */

    protected function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (
                Validator $validator
            ): void {

                /*
                |--------------------------------------------------------------------------
                | VALIDASI FILE UPLOAD
                |--------------------------------------------------------------------------
                */

                $this->validateUploadedFile(
                    $validator
                );

                /*
                |--------------------------------------------------------------------------
                | VALIDASI CAPTURE KAMERA
                |--------------------------------------------------------------------------
                */

                $this->validateCapturedImage(
                    $validator
                );

                /*
                |--------------------------------------------------------------------------
                | VALIDASI FILE + KAMERA
                |--------------------------------------------------------------------------
                */

                $this->validateAttachmentMethod(
                    $validator
                );

                /*
                |--------------------------------------------------------------------------
                | CREATE WAJIB ADA LAMPIRAN
                |--------------------------------------------------------------------------
                */

                $this->validateCreateAttachment(
                    $validator
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI UPLOAD FILE
    |--------------------------------------------------------------------------
    */

    private function validateUploadedFile(
        Validator $validator
    ): void {
        $file =
            $this->file(
                'lampiran_file'
            );

        if (
            !$file
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDASI PHP UPLOAD
        |--------------------------------------------------------------------------
        */

        if (
            !$file->isValid()
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    $this->getUploadErrorMessage(
                        $file->getError()
                    )
                );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | UKURAN
        |--------------------------------------------------------------------------
        */

        $size =
            $file->getSize();

        if (
            $size === false ||
            $size <= 0
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'Ukuran file tidak dapat dibaca.'
                );

            return;
        }

        if (
            $size >
            self::MAX_FILE_SIZE_BYTES
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'Ukuran file maksimal 10 MB.'
                );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | EXTENSION
        |--------------------------------------------------------------------------
        */

        $extension =
            strtolower(
                trim(
                    (string) $file
                        ->getClientOriginalExtension()
                )
            );

        if (
            $extension === 'jpeg'
        ) {
            $extension =
                'jpg';
        }

        if (
            !in_array(
                $extension,
                self::ALLOWED_FILE_EXTENSIONS,
                true
            )
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'Format file tidak didukung. ' .
                    'Gunakan PDF, JPG, JPEG, atau PNG.'
                );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | REAL PATH
        |--------------------------------------------------------------------------
        */

        $realPath =
            $file->getRealPath();

        if (
            !$realPath ||
            !is_readable(
                $realPath
            )
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'File upload tidak dapat dibaca oleh server.'
                );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | SIGNATURE FILE
        |--------------------------------------------------------------------------
        */

        $handle =
            fopen(
                $realPath,
                'rb'
            );

        if (
            $handle === false
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'File upload tidak dapat dibuka oleh server.'
                );

            return;
        }

        $header =
            fread(
                $handle,
                16
            );

        fclose(
            $handle
        );

        if (
            $header === false ||
            $header === ''
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'File upload kosong atau tidak valid.'
                );

            return;
        }

        $isPdf =
            str_starts_with(
                $header,
                '%PDF'
            );

        $isJpeg =
            str_starts_with(
                $header,
                "\xFF\xD8\xFF"
            );

        $isPng =
            str_starts_with(
                $header,
                "\x89PNG\r\n\x1a\n"
            );

        /*
        |--------------------------------------------------------------------------
        | VALIDASI SIGNATURE SESUAI EXTENSION
        |--------------------------------------------------------------------------
        */

        if (
            $extension === 'pdf' &&
            !$isPdf
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'File PDF tidak valid.'
                );

            return;
        }

        if (
            $extension === 'jpg' &&
            !$isJpeg
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'File JPG/JPEG tidak valid.'
                );

            return;
        }

        if (
            $extension === 'png' &&
            !$isPng
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'File PNG tidak valid.'
                );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | MIME AKTUAL
        |--------------------------------------------------------------------------
        */

        $mime =
            strtolower(
                (string) $file->getMimeType()
            );

        if (
            $mime === 'image/jpg'
        ) {
            $mime =
                'image/jpeg';
        }

        if (
            !in_array(
                $mime,
                self::ALLOWED_FILE_MIMES,
                true
            )
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'Jenis MIME file tidak didukung.'
                );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | KONSISTENSI EXTENSION + MIME
        |--------------------------------------------------------------------------
        */

        if (
            $extension === 'pdf' &&
            $mime !== 'application/pdf'
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'File PDF memiliki tipe MIME yang tidak valid.'
                );

            return;
        }

        if (
            $extension === 'jpg' &&
            $mime !== 'image/jpeg'
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'File JPG/JPEG memiliki tipe MIME yang tidak valid.'
                );

            return;
        }

        if (
            $extension === 'png' &&
            $mime !== 'image/png'
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'File PNG memiliki tipe MIME yang tidak valid.'
                );

            return;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI HASIL SCAN KAMERA
    |--------------------------------------------------------------------------
    */

    private function validateCapturedImage(
        Validator $validator
    ): void {
        $captured =
            $this->input(
                'captured_image'
            );

        if (
            !is_string(
                $captured
            ) ||
            trim(
                $captured
            ) === ''
        ) {
            return;
        }

        $captured =
            trim(
                $captured
            );

        /*
        |--------------------------------------------------------------------------
        | DATA URI HEADER
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '~^data:image/(jpeg|jpg|png);base64,~i',
                $captured
            )
        ) {
            $validator
                ->errors()
                ->add(
                    'captured_image',
                    'Format hasil scan kamera tidak valid.'
                );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | PISAH HEADER DAN DATA
        |--------------------------------------------------------------------------
        */

        $parts =
            explode(
                ',',
                $captured,
                2
            );

        if (
            count(
                $parts
            ) !== 2
        ) {
            $validator
                ->errors()
                ->add(
                    'captured_image',
                    'Data hasil scan kamera tidak valid.'
                );

            return;
        }

        [
            $header,
            $encoded,
        ] = $parts;

        /*
        |--------------------------------------------------------------------------
        | VALIDASI HEADER
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '~^data:image/(jpeg|jpg|png);base64$~i',
                $header
            )
        ) {
            $validator
                ->errors()
                ->add(
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

        if (
            trim(
                $encoded
            ) === ''
        ) {
            $validator
                ->errors()
                ->add(
                    'captured_image',
                    'Data hasil scan kamera kosong.'
                );

            return;
        }

        $decoded =
            base64_decode(
                $encoded,
                true
            );

        if (
            $decoded === false ||
            $decoded === ''
        ) {
            $validator
                ->errors()
                ->add(
                    'captured_image',
                    'Data Base64 hasil scan kamera tidak valid.'
                );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | SIZE
        |--------------------------------------------------------------------------
        */

        $size =
            strlen(
                $decoded
            );

        if (
            $size <= 0
        ) {
            $validator
                ->errors()
                ->add(
                    'captured_image',
                    'Ukuran hasil scan kamera tidak valid.'
                );

            return;
        }

        if (
            $size >
            self::MAX_CAMERA_SIZE_BYTES
        ) {
            $validator
                ->errors()
                ->add(
                    'captured_image',
                    'Ukuran hasil scan kamera maksimal 10 MB.'
                );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | IMAGE INFO
        |--------------------------------------------------------------------------
        */

        $imageInfo =
            @getimagesizefromstring(
                $decoded
            );

        if (
            $imageInfo === false
        ) {
            $validator
                ->errors()
                ->add(
                    'captured_image',
                    'Hasil scan kamera bukan gambar yang valid.'
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
                    $imageInfo['mime'] ??
                    ''
                )
            );

        if (
            $actualMime === 'image/jpg'
        ) {
            $actualMime =
                'image/jpeg';
        }

        if (
            !in_array(
                $actualMime,
                self::ALLOWED_CAMERA_MIMES,
                true
            )
        ) {
            $validator
                ->errors()
                ->add(
                    'captured_image',
                    'Jenis gambar hasil scan kamera tidak didukung.'
                );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | DIMENSI
        |--------------------------------------------------------------------------
        */

        $width =
            (int) (
                $imageInfo[0] ??
                0
            );

        $height =
            (int) (
                $imageInfo[1] ??
                0
            );

        if (
            $width <= 0 ||
            $height <= 0
        ) {
            $validator
                ->errors()
                ->add(
                    'captured_image',
                    'Dimensi hasil scan kamera tidak valid.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI METODE LAMPIRAN
    |--------------------------------------------------------------------------
    */

    private function validateAttachmentMethod(
        Validator $validator
    ): void {
        $hasFile =
            $this->hasFile(
                'lampiran_file'
            );

        $captured =
            $this->input(
                'captured_image'
            );

        $hasCamera =
            is_string(
                $captured
            ) &&
            trim(
                $captured
            ) !== '';

        /*
        |--------------------------------------------------------------------------
        | FILE + CAMERA
        |--------------------------------------------------------------------------
        */

        if (
            $hasFile &&
            $hasCamera
        ) {
            $message =
                'Gunakan salah satu metode saja: Upload File atau Scan Kamera.';

            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    $message
                );

            $validator
                ->errors()
                ->add(
                    'captured_image',
                    $message
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE WAJIB ADA LAMPIRAN
    |--------------------------------------------------------------------------
    */

    private function validateCreateAttachment(
        Validator $validator
    ): void {
        /*
        |--------------------------------------------------------------------------
        | HANYA CREATE / POST
        |--------------------------------------------------------------------------
        */

        if (
            !$this->isMethod('POST')
        ) {
            return;
        }

        $hasFile =
            $this->hasFile(
                'lampiran_file'
            );

        $captured =
            $this->input(
                'captured_image'
            );

        $hasCamera =
            is_string(
                $captured
            ) &&
            trim(
                $captured
            ) !== '';

        /*
        |--------------------------------------------------------------------------
        | TIDAK ADA KEDUANYA
        |--------------------------------------------------------------------------
        */

        if (
            !$hasFile &&
            !$hasCamera
        ) {
            $validator
                ->errors()
                ->add(
                    'lampiran_file',
                    'Berkas digital wajib diupload atau discan menggunakan kamera.'
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAN STRING
    |--------------------------------------------------------------------------
    */

    private function cleanString(
        mixed $value
    ): ?string {
        if (
            $value === null
        ) {
            return null;
        }

        if (
            !is_scalar(
                $value
            )
        ) {
            return null;
        }

        $value =
            trim(
                (string) $value
            );

        return $value !== ''
            ? $value
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAN INTEGER
    |--------------------------------------------------------------------------
    */

    private function cleanInteger(
        mixed $value
    ): ?int {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        if (
            !is_numeric(
                $value
            )
        ) {
            return null;
        }

        $value =
            (int) $value;

        return $value > 0
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
        $status =
            strtolower(
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
    | CLEAN CAPTURED IMAGE
    |--------------------------------------------------------------------------
    */

    private function cleanCapturedImage(): ?string
    {
        $value =
            $this->input(
                'captured_image'
            );

        if (
            !is_scalar(
                $value
            )
        ) {
            return null;
        }

        $value =
            trim(
                (string) $value
            );

        return $value !== ''
            ? $value
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD ERROR MESSAGE
    |--------------------------------------------------------------------------
    */

    private function getUploadErrorMessage(
        int $errorCode
    ): string {
        return match (
            $errorCode
        ) {

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
                'File gagal diupload. Kode upload PHP: ' .
                $errorCode,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | MESSAGES
    |--------------------------------------------------------------------------
    */

    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | NOMOR AGENDA
            |--------------------------------------------------------------------------
            */

            'nomor_agenda.unique' =>
                'Nomor agenda ini sudah digunakan oleh surat lain.',

            'nomor_agenda.max' =>
                ':attribute maksimal 50 karakter.',

            /*
            |--------------------------------------------------------------------------
            | NOMOR SURAT
            |--------------------------------------------------------------------------
            */

            'nomor_surat.required' =>
                ':attribute wajib diisi.',

            'nomor_surat.string' =>
                ':attribute harus berupa teks.',

            'nomor_surat.max' =>
                ':attribute maksimal 255 karakter.',

            /*
            |--------------------------------------------------------------------------
            | PENGIRIM
            |--------------------------------------------------------------------------
            */

            'pengirim.required' =>
                ':attribute wajib diisi.',

            'pengirim.string' =>
                ':attribute harus berupa teks.',

            'pengirim.max' =>
                ':attribute maksimal 255 karakter.',

            /*
            |--------------------------------------------------------------------------
            | TANGGAL
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | KATEGORI
            |--------------------------------------------------------------------------
            */

            'kategori_surat_id.required' =>
                ':attribute wajib dipilih.',

            'kategori_surat_id.integer' =>
                ':attribute tidak valid.',

            'kategori_surat_id.exists' =>
                ':attribute yang dipilih tidak tersedia.',

            /*
            |--------------------------------------------------------------------------
            | PERIHAL
            |--------------------------------------------------------------------------
            */

            'perihal.required' =>
                ':attribute wajib diisi.',

            'perihal.string' =>
                ':attribute harus berupa teks.',

            'perihal.max' =>
                ':attribute maksimal 255 karakter.',

            /*
            |--------------------------------------------------------------------------
            | RINGKASAN
            |--------------------------------------------------------------------------
            */

            'ringkasan.string' =>
                ':attribute harus berupa teks.',

            'ringkasan.max' =>
                ':attribute maksimal 5.000 karakter.',

            /*
            |--------------------------------------------------------------------------
            | LAMPIRAN
            |--------------------------------------------------------------------------
            */

            'lampiran_file.file' =>
                ':attribute harus berupa file yang valid.',

            'lampiran_file.mimes' =>
                ':attribute harus berformat PDF, JPG, JPEG, atau PNG.',

            'lampiran_file.max' =>
                ':attribute maksimal berukuran 10 MB.',

            /*
            |--------------------------------------------------------------------------
            | CAMERA
            |--------------------------------------------------------------------------
            */

            'captured_image.string' =>
                ':attribute tidak valid.',

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            'status.required' =>
                ':attribute wajib dipilih.',

            'status.in' =>
                'Pilihan :attribute tidak valid.',

            /*
            |--------------------------------------------------------------------------
            | LOKASI FISIK
            |--------------------------------------------------------------------------
            */

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