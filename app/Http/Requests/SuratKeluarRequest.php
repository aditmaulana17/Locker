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
     * Maksimal ukuran lampiran:
     * 10240 KB = 10 MB.
     */
    private const MAX_FILE_SIZE_KB = 10240;

    /**
     * Ekstensi file yang diperbolehkan.
     */
    private const ALLOWED_FILE_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    /*
    |--------------------------------------------------------------------------
    | AUTHORIZATION
    |--------------------------------------------------------------------------
    */

    /**
     * Hanya Admin dan Pimpinan yang dapat
     * membuat atau mengubah surat keluar.
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
     * Aturan validasi surat keluar.
     */
    public function rules(): array
    {
        /*
        |--------------------------------------------------------------------------
        | ROUTE MODEL
        |--------------------------------------------------------------------------
        */

        $suratKeluar =
            $this->route('suratKeluar')
            ?? $this->route('surat_keluar');

        $suratKeluarId =
            is_object($suratKeluar)
                ? $suratKeluar->id
                : $suratKeluar;

        return [

            /*
            |--------------------------------------------------------------------------
            | NOMOR SURAT
            |--------------------------------------------------------------------------
            |
            | Boleh kosong karena controller/model sebelumnya
            | tetap mendukung generate atau pengisian belakangan.
            |
            */

            'nomor_surat' => [
                'nullable',
                'string',
                'max:255',

                Rule::unique(
                    'surat_keluars',
                    'nomor_surat'
                )->ignore($suratKeluarId),
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
            | TUJUAN SURAT
            |--------------------------------------------------------------------------
            |
            | Field database tetap "pengirim"
            | karena struktur tabel saat ini menggunakannya.
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
            | PDF / JPG / JPEG / PNG
            | Maksimal 10 MB.
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
        $nomorSurat =
            $this->input('nomor_surat');

        $pengirim =
            $this->input('pengirim');

        $perihal =
            $this->input('perihal');

        $ringkasan =
            $this->input('ringkasan');

        $status =
            $this->input('status');

        $this->merge([

            /*
            |--------------------------------------------------------------------------
            | NOMOR SURAT
            |--------------------------------------------------------------------------
            */

            'nomor_surat' =>
                is_scalar($nomorSurat)
                    && trim((string) $nomorSurat) !== ''
                    ? trim((string) $nomorSurat)
                    : null,

            /*
            |--------------------------------------------------------------------------
            | TUJUAN SURAT
            |--------------------------------------------------------------------------
            */

            'pengirim' =>
                is_scalar($pengirim)
                    && trim((string) $pengirim) !== ''
                    ? trim((string) $pengirim)
                    : null,

            /*
            |--------------------------------------------------------------------------
            | PERIHAL
            |--------------------------------------------------------------------------
            */

            'perihal' =>
                is_scalar($perihal)
                    && trim((string) $perihal) !== ''
                    ? trim((string) $perihal)
                    : null,

            /*
            |--------------------------------------------------------------------------
            | RINGKASAN
            |--------------------------------------------------------------------------
            */

            'ringkasan' =>
                is_scalar($ringkasan)
                    && trim((string) $ringkasan) !== ''
                    ? trim((string) $ringkasan)
                    : null,

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            'status' =>
                is_scalar($status)
                    && trim((string) $status) !== ''
                    ? strtolower(trim((string) $status))
                    : 'draft',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOM VALIDATION
    |--------------------------------------------------------------------------
    */

    /**
     * Pemeriksaan tambahan untuk upload file.
     */
    protected function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (Validator $validator): void {

                $file =
                    $this->file('lampiran_file');

                if (!$file) {
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | LOG DEBUG HANYA SAAT DEBUG AKTIF
                |--------------------------------------------------------------------------
                */

                if (config('app.debug')) {

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

                if (!$file->isValid()) {

                    $message =
                        match ($file->getError()) {

                            UPLOAD_ERR_INI_SIZE =>
                                'Ukuran file melebihi batas upload PHP.',

                            UPLOAD_ERR_FORM_SIZE =>
                                'Ukuran file melebihi batas upload form.',

                            UPLOAD_ERR_PARTIAL =>
                                'File hanya terupload sebagian.',

                            UPLOAD_ERR_NO_FILE =>
                                'Tidak ada file yang dipilih.',

                            UPLOAD_ERR_NO_TMP_DIR =>
                                'Folder temporary upload PHP tidak tersedia.',

                            UPLOAD_ERR_CANT_WRITE =>
                                'PHP gagal menulis file upload.',

                            UPLOAD_ERR_EXTENSION =>
                                'Upload dihentikan oleh ekstensi PHP.',

                            default =>
                                'File gagal diupload oleh PHP.',
                        };

                    $validator->errors()->add(
                        'lampiran_file',
                        $message
                    );
                }
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | MESSAGES
    |--------------------------------------------------------------------------
    */

    /**
     * Pesan validasi dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | NOMOR SURAT
            |--------------------------------------------------------------------------
            */

            'nomor_surat.string' =>
                'Nomor surat harus berupa teks.',

            'nomor_surat.max' =>
                'Nomor surat maksimal 255 karakter.',

            'nomor_surat.unique' =>
                'Nomor surat tersebut sudah digunakan.',

            /*
            |--------------------------------------------------------------------------
            | TANGGAL
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | TUJUAN
            |--------------------------------------------------------------------------
            */

            'pengirim.required' =>
                'Tujuan surat wajib diisi.',

            'pengirim.string' =>
                'Tujuan surat harus berupa teks.',

            'pengirim.max' =>
                'Tujuan surat maksimal 150 karakter.',

            /*
            |--------------------------------------------------------------------------
            | KATEGORI
            |--------------------------------------------------------------------------
            */

            'kategori_surat_id.required' =>
                'Kategori surat wajib dipilih.',

            'kategori_surat_id.integer' =>
                'Kategori surat tidak valid.',

            'kategori_surat_id.exists' =>
                'Kategori surat yang dipilih tidak tersedia.',

            /*
            |--------------------------------------------------------------------------
            | PERIHAL
            |--------------------------------------------------------------------------
            */

            'perihal.required' =>
                'Perihal surat wajib diisi.',

            'perihal.string' =>
                'Perihal surat harus berupa teks.',

            'perihal.max' =>
                'Perihal surat maksimal 255 karakter.',

            /*
            |--------------------------------------------------------------------------
            | RINGKASAN
            |--------------------------------------------------------------------------
            */

            'ringkasan.string' =>
                'Ringkasan harus berupa teks.',

            'ringkasan.max' =>
                'Ringkasan maksimal 5000 karakter.',

            /*
            |--------------------------------------------------------------------------
            | LAMPIRAN
            |--------------------------------------------------------------------------
            */

            'lampiran_file.file' =>
                'Lampiran harus berupa file yang valid.',

            'lampiran_file.mimes' =>
                'Lampiran hanya boleh berupa PDF, JPG, JPEG, atau PNG.',

            'lampiran_file.max' =>
                'Ukuran file lampiran maksimal 10 MB.',

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            'status.in' =>
                'Status surat yang dipilih tidak valid.',

            /*
            |--------------------------------------------------------------------------
            | PENANDATANGAN
            |--------------------------------------------------------------------------
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
     * Nama field yang ditampilkan pada pesan error.
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