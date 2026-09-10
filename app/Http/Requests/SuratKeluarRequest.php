<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SuratKeluarRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI
    |--------------------------------------------------------------------------
    */

    /**
     * Maksimal ukuran file:
     *
     * 10240 KB = 10 MB.
     */
    private const MAX_FILE_SIZE_KB = 10240;

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
     * MIME type yang diperbolehkan.
     *
     * Digunakan untuk pemeriksaan tambahan.
     */
    private const ALLOWED_FILE_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    /**
     * Status surat yang diperbolehkan.
     */
    private const ALLOWED_STATUSES = [
        'draft',
        'draf',
        'diproses',
        'disetujui',
        'dikirim',
        'diarsipkan',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTHORIZATION
    |--------------------------------------------------------------------------
    */

    /**
     * Hanya Admin dan Pimpinan yang dapat:
     *
     * - membuat surat keluar
     * - mengubah surat keluar
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI ROLE
        |--------------------------------------------------------------------------
        */

        if (
            method_exists(
                $user,
                'normalizedRole'
            )
        ) {
            $role = $user->normalizedRole();
        } elseif (
            method_exists(
                User::class,
                'normalizeRole'
            )
        ) {
            $role = User::normalizeRole(
                (string) (
                    $user->role
                    ?? $user->jabatan
                    ?? ''
                )
            );
        } else {
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
            |--------------------------------------------------------------------------
            | NORMALISASI STAF
            |--------------------------------------------------------------------------
            */

            if ($role === 'staf') {
                $role = 'staff';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ADMIN + PIMPINAN
        |--------------------------------------------------------------------------
        */

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

    /**
     * Aturan validasi Surat Keluar.
     */
    public function rules(): array
    {
        /*
        |--------------------------------------------------------------------------
        | ROUTE MODEL
        |--------------------------------------------------------------------------
        |
        | Mendukung route model binding:
        |
        | /surat-keluar/{suratKeluar}
        |
        | maupun:
        |
        | /surat-keluar/{surat_keluar}
        |
        */

        $suratKeluar =
            $this->route('suratKeluar')
            ?? $this->route('surat_keluar');

        /*
        |--------------------------------------------------------------------------
        | ID SURAT SAAT UPDATE
        |--------------------------------------------------------------------------
        */

        $suratKeluarId =
            is_object($suratKeluar)
                ? $suratKeluar->id
                : (
                    is_numeric($suratKeluar)
                        ? (int) $suratKeluar
                        : null
                );

        return [

            /*
            |--------------------------------------------------------------------------
            | NOMOR SURAT
            |--------------------------------------------------------------------------
            */

            'nomor_surat' => [
                'nullable',
                'string',
                'max:255',

                Rule::unique(
                    'surat_keluars',
                    'nomor_surat'
                )->ignore(
                    $suratKeluarId
                ),
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
            | TANGGAL KELUAR
            |--------------------------------------------------------------------------
            */

            'tanggal_keluar' => [
                'required',
                'date',
                'after_or_equal:tanggal_surat',
            ],

            /*
            |--------------------------------------------------------------------------
            | TUJUAN / PENGIRIM SURAT
            |--------------------------------------------------------------------------
            |
            | Sesuai dengan nama kolom database:
            |
            | pengirim
            |
            */

            'pengirim' => [
                'required',
                'string',
                'max:150',
            ],

            /*
            |--------------------------------------------------------------------------
            | KATEGORI
            |--------------------------------------------------------------------------
            |
            | Sesuai dengan database:
            |
            | kategori_surat_id
            |
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
            | LAMPIRAN
            |--------------------------------------------------------------------------
            |
            | File:
            |
            | PDF
            | JPG
            | JPEG
            | PNG
            |
            | Maksimal:
            |
            | 10 MB
            |
            */

            'lampiran_file' => [
                'nullable',
                'file',
                'max:' . self::MAX_FILE_SIZE_KB,
                'mimes:' . implode(
                    ',',
                    self::ALLOWED_FILE_EXTENSIONS
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            'status' => [
                'nullable',
                Rule::in(
                    self::ALLOWED_STATUSES
                ),
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

    /*
    |--------------------------------------------------------------------------
    | PREPARE DATA
    |--------------------------------------------------------------------------
    */

    /**
     * Membersihkan input sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        /*
        |--------------------------------------------------------------------------
        | NOMOR SURAT
        |--------------------------------------------------------------------------
        */

        $nomorSurat =
            $this->input(
                'nomor_surat'
            );

        /*
        |--------------------------------------------------------------------------
        | TANGGAL SURAT
        |--------------------------------------------------------------------------
        */

        $tanggalSurat =
            $this->input(
                'tanggal_surat'
            );

        /*
        |--------------------------------------------------------------------------
        | TANGGAL KELUAR
        |--------------------------------------------------------------------------
        */

        $tanggalKeluar =
            $this->input(
                'tanggal_keluar'
            );

        /*
        |--------------------------------------------------------------------------
        | PENGIRIM
        |--------------------------------------------------------------------------
        */

        $pengirim =
            $this->input(
                'pengirim'
            );

        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategoriId =
            $this->input(
                'kategori_surat_id'
            );

        /*
        |--------------------------------------------------------------------------
        | PERIHAL
        |--------------------------------------------------------------------------
        */

        $perihal =
            $this->input(
                'perihal'
            );

        /*
        |--------------------------------------------------------------------------
        | RINGKASAN
        |--------------------------------------------------------------------------
        */

        $ringkasan =
            $this->input(
                'ringkasan'
            );

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status =
            $this->input(
                'status'
            );

        /*
        |--------------------------------------------------------------------------
        | PENANDATANGAN
        |--------------------------------------------------------------------------
        */

        $penandatangan =
            $this->input(
                'ditandatangani_oleh'
            );

        /*
        |--------------------------------------------------------------------------
        | MERGE DATA
        |--------------------------------------------------------------------------
        */

        $this->merge([

            /*
            |----------------------------------------------------------------------
            | NOMOR SURAT
            |----------------------------------------------------------------------
            */

            'nomor_surat' =>
                $this->nullableString(
                    $nomorSurat
                ),

            /*
            |----------------------------------------------------------------------
            | TANGGAL SURAT
            |----------------------------------------------------------------------
            */

            'tanggal_surat' =>
                $this->nullableString(
                    $tanggalSurat
                ),

            /*
            |----------------------------------------------------------------------
            | TANGGAL KELUAR
            |----------------------------------------------------------------------
            */

            'tanggal_keluar' =>
                $this->nullableString(
                    $tanggalKeluar
                ),

            /*
            |----------------------------------------------------------------------
            | PENGIRIM
            |----------------------------------------------------------------------
            */

            'pengirim' =>
                $this->nullableString(
                    $pengirim
                ),

            /*
            |----------------------------------------------------------------------
            | KATEGORI
            |----------------------------------------------------------------------
            */

            'kategori_surat_id' =>
                $this->nullableInteger(
                    $kategoriId
                ),

            /*
            |----------------------------------------------------------------------
            | PERIHAL
            |----------------------------------------------------------------------
            */

            'perihal' =>
                $this->nullableString(
                    $perihal
                ),

            /*
            |----------------------------------------------------------------------
            | RINGKASAN
            |----------------------------------------------------------------------
            */

            'ringkasan' =>
                $this->nullableString(
                    $ringkasan
                ),

            /*
            |----------------------------------------------------------------------
            | STATUS
            |----------------------------------------------------------------------
            */

            'status' =>
                $this->normalizeStatus(
                    $status
                ),

            /*
            |----------------------------------------------------------------------
            | PENANDATANGAN
            |----------------------------------------------------------------------
            */

            'ditandatangani_oleh' =>
                $this->nullableInteger(
                    $penandatangan
                ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATOR
    |--------------------------------------------------------------------------
    */

    /**
     * Pemeriksaan tambahan setelah rules utama.
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
                | FILE
                |--------------------------------------------------------------------------
                */

                $file =
                    $this->file(
                        'lampiran_file'
                    );

                /*
                |--------------------------------------------------------------------------
                | TIDAK ADA FILE
                |--------------------------------------------------------------------------
                |
                | Pada edit, file memang boleh kosong.
                |
                */

                if (!$file) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | DEBUG LOG
                |--------------------------------------------------------------------------
                */

                if (
                    config('app.debug')
                ) {
                    Log::info(
                        'SURAT KELUAR - UPLOAD',
                        [
                            'original_name' =>
                                $file->getClientOriginalName(),

                            'client_extension' =>
                                $file->getClientOriginalExtension(),

                            'client_mime' =>
                                $file->getClientMimeType(),

                            'detected_mime' =>
                                $file->getMimeType(),

                            'size' =>
                                $file->getSize(),

                            'error' =>
                                $file->getError(),

                            'is_valid' =>
                                $file->isValid(),
                        ]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | ERROR UPLOAD PHP
                |--------------------------------------------------------------------------
                */

                if (
                    !$file->isValid()
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
                | UKURAN FILE
                |--------------------------------------------------------------------------
                */

                $fileSize =
                    $file->getSize();

                if (
                    $fileSize !== false
                    && $fileSize > (
                        self::MAX_FILE_SIZE_KB * 1024
                    )
                ) {
                    $validator->errors()->add(
                        'lampiran_file',
                        'Ukuran file lampiran maksimal 10 MB.'
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
                            (string)
                            $file->getClientOriginalExtension()
                        )
                    );

                if (
                    !in_array(
                        $extension,
                        self::ALLOWED_FILE_EXTENSIONS,
                        true
                    )
                ) {
                    $validator->errors()->add(
                        'lampiran_file',
                        'Format file tidak didukung. ' .
                        'Gunakan PDF, JPG, JPEG, atau PNG.'
                    );

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | MIME
                |--------------------------------------------------------------------------
                |
                | getMimeType() dapat berbeda pada beberapa server,
                | sehingga pemeriksaan ini tidak dibuat terlalu ketat.
                |
                | Validasi utama tetap menggunakan mimes.
                |
                */

                $mime =
                    strtolower(
                        trim(
                            (string)
                            $file->getMimeType()
                        )
                    );

                if (
                    $mime !== ''
                    && !in_array(
                        $mime,
                        self::ALLOWED_FILE_MIMES,
                        true
                    )
                ) {
                    /*
                    |------------------------------------------------------------------
                    | Beberapa server dapat mengembalikan MIME yang sedikit berbeda.
                    | Jangan langsung menolak jika extension valid.
                    | Controller akan melakukan pemeriksaan gambar/PDF kembali.
                    |------------------------------------------------------------------
                    */
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER INPUT
    |--------------------------------------------------------------------------
    */

    /**
     * Mengubah input menjadi nullable string.
     */
    private function nullableString(
        mixed $value
    ): ?string {
        if (
            !is_scalar($value)
        ) {
            return null;
        }

        $value = trim(
            (string) $value
        );

        return $value !== ''
            ? $value
            : null;
    }

    /**
     * Mengubah input menjadi nullable integer.
     */
    private function nullableInteger(
        mixed $value
    ): ?int {
        if (
            $value === null
            || $value === ''
        ) {
            return null;
        }

        if (
            !is_numeric($value)
        ) {
            return null;
        }

        $value = (int) $value;

        return $value > 0
            ? $value
            : null;
    }

    /**
     * Normalisasi status.
     *
     * draf -> draft
     */
    private function normalizeStatus(
        mixed $status
    ): string {
        if (
            !is_scalar($status)
        ) {
            return 'draft';
        }

        $status = strtolower(
            trim(
                (string) $status
            )
        );

        if ($status === '') {
            return 'draft';
        }

        if ($status === 'draf') {
            return 'draft';
        }

        return $status;
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD ERROR
    |--------------------------------------------------------------------------
    */

    /**
     * Mengubah kode error upload PHP
     * menjadi pesan Bahasa Indonesia.
     */
    private function getUploadErrorMessage(
        int $error
    ): string {
        return match ($error) {

            UPLOAD_ERR_INI_SIZE =>
                'Ukuran file melebihi batas upload PHP.',

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
                'Upload dihentikan oleh ekstensi PHP.',

            default =>
                'File gagal diupload oleh PHP. ' .
                'Kode error: ' .
                $error,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | MESSAGES
    |--------------------------------------------------------------------------
    */

    /**
     * Pesan validasi Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [

            /*
            |----------------------------------------------------------------------
            | NOMOR SURAT
            |----------------------------------------------------------------------
            */

            'nomor_surat.string' =>
                'Nomor surat harus berupa teks.',

            'nomor_surat.max' =>
                'Nomor surat maksimal 255 karakter.',

            'nomor_surat.unique' =>
                'Nomor surat tersebut sudah digunakan.',

            /*
            |----------------------------------------------------------------------
            | TANGGAL SURAT
            |----------------------------------------------------------------------
            */

            'tanggal_surat.required' =>
                'Tanggal surat wajib diisi.',

            'tanggal_surat.date' =>
                'Tanggal surat harus berupa tanggal yang valid.',

            /*
            |----------------------------------------------------------------------
            | TANGGAL KELUAR
            |----------------------------------------------------------------------
            */

            'tanggal_keluar.required' =>
                'Tanggal keluar wajib diisi.',

            'tanggal_keluar.date' =>
                'Tanggal keluar harus berupa tanggal yang valid.',

            'tanggal_keluar.after_or_equal' =>
                'Tanggal keluar tidak boleh sebelum tanggal surat.',

            /*
            |----------------------------------------------------------------------
            | PENGIRIM
            |----------------------------------------------------------------------
            */

            'pengirim.required' =>
                'Tujuan surat wajib diisi.',

            'pengirim.string' =>
                'Tujuan surat harus berupa teks.',

            'pengirim.max' =>
                'Tujuan surat maksimal 150 karakter.',

            /*
            |----------------------------------------------------------------------
            | KATEGORI
            |----------------------------------------------------------------------
            */

            'kategori_surat_id.required' =>
                'Kategori surat wajib dipilih.',

            'kategori_surat_id.integer' =>
                'Kategori surat tidak valid.',

            'kategori_surat_id.exists' =>
                'Kategori surat yang dipilih tidak tersedia.',

            /*
            |----------------------------------------------------------------------
            | PERIHAL
            |----------------------------------------------------------------------
            */

            'perihal.required' =>
                'Perihal surat wajib diisi.',

            'perihal.string' =>
                'Perihal surat harus berupa teks.',

            'perihal.max' =>
                'Perihal surat maksimal 255 karakter.',

            /*
            |----------------------------------------------------------------------
            | RINGKASAN
            |----------------------------------------------------------------------
            */

            'ringkasan.string' =>
                'Ringkasan harus berupa teks.',

            'ringkasan.max' =>
                'Ringkasan maksimal 5000 karakter.',

            /*
            |----------------------------------------------------------------------
            | LAMPIRAN
            |----------------------------------------------------------------------
            */

            'lampiran_file.file' =>
                'Lampiran harus berupa file yang valid.',

            'lampiran_file.mimes' =>
                'Lampiran hanya boleh berupa PDF, JPG, JPEG, atau PNG.',

            'lampiran_file.max' =>
                'Ukuran file lampiran maksimal 10 MB.',

            /*
            |----------------------------------------------------------------------
            | STATUS
            |----------------------------------------------------------------------
            */

            'status.in' =>
                'Status surat yang dipilih tidak valid.',

            /*
            |----------------------------------------------------------------------
            | PENANDATANGAN
            |----------------------------------------------------------------------
            */

            'ditandatangani_oleh.integer' =>
                'Data penandatangan tidak valid.',

            'ditandatangani_oleh.exists' =>
                'Pengguna penandatangan tidak ditemukan.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTES
    |--------------------------------------------------------------------------
    */

    /**
     * Nama field yang ditampilkan
     * pada pesan validasi.
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
                'Tujuan Surat',

            'kategori_surat_id' =>
                'Kategori Surat',

            'perihal' =>
                'Perihal Surat',

            'ringkasan' =>
                'Ringkasan',

            'lampiran_file' =>
                'Berkas Lampiran',

            'status' =>
                'Status Surat',

            'ditandatangani_oleh' =>
                'Penandatangan',
        ];
    }
}