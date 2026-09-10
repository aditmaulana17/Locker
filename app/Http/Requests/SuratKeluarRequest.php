<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratKeluarRequest extends FormRequest
{
    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI
    |--------------------------------------------------------------------------
    */

    private const MAX_FILE_SIZE_KB = 10240;

    private const ALLOWED_FILE_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
        'zip',
    ];

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

    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        $role = '';

        if (method_exists($user, 'normalizedRole')) {
            $role = $user->normalizedRole();
        } elseif (method_exists(User::class, 'normalizeRole')) {
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

            if ($role === 'staf') {
                $role = 'staff';
            }
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

    /*
    |--------------------------------------------------------------------------
    | RULES
    |--------------------------------------------------------------------------
    */

    public function rules(): array
    {
        $suratKeluar =
            $this->route('suratKeluar')
            ?? $this->route('surat_keluar');

        $suratKeluarId = null;

        if (is_object($suratKeluar)) {
            $suratKeluarId = $suratKeluar->id ?? null;
        } elseif (is_numeric($suratKeluar)) {
            $suratKeluarId = (int) $suratKeluar;
        }

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
            | LAMPIRAN
            |--------------------------------------------------------------------------
            |
            | PDF / JPG / JPEG / PNG / ZIP
            | Maksimal 10 MB.
            |
            | Tidak menggunakan mimetypes karena MIME browser/server
            | dapat berbeda walaupun file sebenarnya valid.
            |
            */

            'lampiran_file' => [
                'nullable',
                'file',
                'max:' . self::MAX_FILE_SIZE_KB,
                'mimes:pdf,jpg,jpeg,png,zip',
            ],

            'status' => [
                'nullable',
                Rule::in(self::ALLOWED_STATUSES),
            ],

            'ditandatangani_oleh' => [
                'nullable',
                'integer',
                'exists:users,id',
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
        $data = [];

        $data['nomor_surat'] = $this->nullableString(
            $this->input('nomor_surat')
        );

        $data['tanggal_surat'] = $this->nullableString(
            $this->input('tanggal_surat')
        );

        $data['tanggal_keluar'] = $this->nullableString(
            $this->input('tanggal_keluar')
        );

        $data['pengirim'] = $this->nullableString(
            $this->input('pengirim')
        );

        $data['kategori_surat_id'] = $this->nullableInteger(
            $this->input('kategori_surat_id')
        );

        $data['perihal'] = $this->nullableString(
            $this->input('perihal')
        );

        $data['ringkasan'] = $this->nullableString(
            $this->input('ringkasan')
        );

        $data['status'] = $this->normalizeStatus(
            $this->input('status')
        );

        $data['ditandatangani_oleh'] =
            $this->nullableInteger(
                $this->input('ditandatangani_oleh')
            );

        $this->merge($data);
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
            function (Validator $validator): void {

                $file = $this->file('lampiran_file');

                /*
                | Edit tanpa mengganti file
                */

                if (!$file) {
                    return;
                }

                /*
                | Cek upload PHP
                */

                if (!$file->isValid()) {
                    $validator->errors()->add(
                        'lampiran_file',
                        $this->getUploadErrorMessage(
                            $file->getError()
                        )
                    );

                    return;
                }

                /*
                | Cek ukuran
                */

                $fileSize = $file->getSize();

                if (
                    $fileSize === false ||
                    $fileSize <= 0
                ) {
                    $validator->errors()->add(
                        'lampiran_file',
                        'Ukuran file tidak dapat dibaca.'
                    );

                    return;
                }

                if (
                    $fileSize >
                    self::MAX_FILE_SIZE_KB * 1024
                ) {
                    $validator->errors()->add(
                        'lampiran_file',
                        'Ukuran file lampiran maksimal 10 MB.'
                    );

                    return;
                }

                /*
                | Cek extension asli
                */

                $extension = strtolower(
                    trim(
                        (string) $file->getClientOriginalExtension()
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
                        'Gunakan PDF, JPG, JPEG, PNG, atau ZIP.'
                    );
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER STRING
    |--------------------------------------------------------------------------
    */

    private function nullableString(
        mixed $value
    ): ?string {
        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== ''
            ? $value
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | HELPER INTEGER
    |--------------------------------------------------------------------------
    */

    private function nullableInteger(
        mixed $value
    ): ?int {
        if (
            $value === null ||
            $value === ''
        ) {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        $value = (int) $value;

        return $value > 0
            ? $value
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | NORMALIZE STATUS
    |--------------------------------------------------------------------------
    */

    private function normalizeStatus(
        mixed $status
    ): string {
        if (!is_scalar($status)) {
            return 'draft';
        }

        $status = strtolower(
            trim((string) $status)
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
                'Kode error: ' . $error,
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
                'Tujuan surat wajib diisi.',

            'pengirim.string' =>
                'Tujuan surat harus berupa teks.',

            'pengirim.max' =>
                'Tujuan surat maksimal 150 karakter.',

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
                'Lampiran hanya boleh berupa PDF, JPG, JPEG, PNG, atau ZIP.',

            'lampiran_file.max' =>
                'Ukuran file lampiran maksimal 10 MB.',

            'status.in' =>
                'Status surat yang dipilih tidak valid.',

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