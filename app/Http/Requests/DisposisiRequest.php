<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class DisposisiRequest extends FormRequest
{
    /**
     * Menentukan apakah user boleh melakukan request.
     */
    public function authorize(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        $role = strtolower(
            trim((string) $user->role)
        );

        /*
         * Normalisasi staff menjadi staf.
         */
        if ($role === 'staff') {
            $role = 'staf';
        }

        /*
         * Yang boleh membuat/mengelola disposisi:
         * - admin
         * - pimpinan
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
     * Normalisasi input sebelum proses validasi.
     *
     * Sistem lama menggunakan "instruksi",
     * sedangkan sistem baru menggunakan "isi_disposisi".
     *
     * Kita dukung keduanya.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        /*
         * =====================================================
         * INSTRUKSI / ISI DISPOSISI
         * =====================================================
         *
         * Prioritas:
         *
         * 1. instruksi
         * 2. isi_disposisi
         * 3. catatan
         *
         * Karena database production mewajibkan
         * kolom "instruksi", kita pastikan field tersebut
         * selalu mendapatkan nilai.
         */

        $instruksi = $this->input('instruksi');

        if (
            $instruksi === null ||
            trim((string) $instruksi) === ''
        ) {
            $instruksi = $this->input('isi_disposisi');
        }

        if (
            $instruksi === null ||
            trim((string) $instruksi) === ''
        ) {
            $instruksi = $this->input('catatan');
        }

        if ($instruksi !== null) {
            $instruksi = trim(
                (string) $instruksi
            );

            /*
             * Simpan ke kedua nama field agar kompatibel
             * dengan kode lama dan kode baru.
             */
            $data['instruksi'] = $instruksi;
            $data['isi_disposisi'] = $instruksi;
        }

        /*
         * =====================================================
         * CATATAN
         * =====================================================
         */
        if ($this->has('catatan')) {
            $catatan = $this->input('catatan');

            $data['catatan'] = $catatan !== null
                ? trim((string) $catatan)
                : null;
        }

        /*
         * =====================================================
         * STATUS
         * =====================================================
         */
        if ($this->filled('status')) {
            $data['status'] = strtolower(
                trim(
                    (string) $this->input('status')
                )
            );
        }

        /*
         * =====================================================
         * PENERIMA
         * =====================================================
         *
         * "penerima" bukan foreign key database.
         * Field ini hanya dipertahankan untuk kompatibilitas
         * dengan form lama.
         */
        if ($this->has('penerima')) {
            $penerima = $this->input('penerima');

            $data['penerima'] = $penerima !== null
                ? trim((string) $penerima)
                : null;
        }

        /*
         * =====================================================
         * KEPADA USER
         * =====================================================
         */
        if ($this->filled('kepada_user_id')) {
            $data['kepada_user_id'] = (int) $this->input(
                'kepada_user_id'
            );
        }

        /*
         * =====================================================
         * SURAT MASUK
         * =====================================================
         */
        if ($this->filled('surat_masuk_id')) {
            $data['surat_masuk_id'] = (int) $this->input(
                'surat_masuk_id'
            );
        }

        /*
         * =====================================================
         * BATAS WAKTU
         * =====================================================
         */
        if ($this->has('batas_waktu')) {
            $batasWaktu = $this->input('batas_waktu');

            $data['batas_waktu'] = $batasWaktu !== null
                ? trim((string) $batasWaktu)
                : null;
        }

        /*
         * Jangan menggunakan "sifat" karena kolom tersebut
         * tidak ada pada tabel disposisis production.
         */

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Aturan validasi.
     */
    public function rules(): array
    {
        $isUpdate = $this->isUpdateRequest();

        return [

            /*
             * =================================================
             * SURAT MASUK
             * =================================================
             */
            'surat_masuk_id' => [
                $isUpdate
                    ? 'sometimes'
                    : 'required',

                'integer',

                'exists:surat_masuks,id',
            ],

            /*
             * =================================================
             * PENERIMA DISPOSISI
             * =================================================
             */
            'kepada_user_id' => [
                'required',
                'integer',
                'exists:users,id',

                function (
                    string $attribute,
                    mixed $value,
                    \Closure $fail
                ): void {

                    $recipient = User::query()
                        ->whereKey($value)
                        ->where(
                            'is_active',
                            true
                        )
                        ->where(function ($query) {
                            $query
                                ->whereRaw(
                                    'LOWER(role) IN (?, ?)',
                                    [
                                        'staf',
                                        'staff',
                                    ]
                                )
                                ->orWhereRaw(
                                    'LOWER(jabatan) IN (?, ?)',
                                    [
                                        'staf',
                                        'staff',
                                    ]
                                );
                        })
                        ->first();

                    /*
                     * Pastikan penerima adalah Staff aktif.
                     */
                    if (!$recipient) {
                        $fail(
                            'Penerima disposisi harus merupakan Staf aktif.'
                        );

                        return;
                    }

                    /*
                     * Tidak boleh mengirim ke diri sendiri.
                     */
                    if (
                        (int) $recipient->getKey()
                        === (int) Auth::id()
                    ) {
                        $fail(
                            'Anda tidak dapat mengirim disposisi kepada diri sendiri.'
                        );
                    }
                },
            ],

            /*
             * =================================================
             * PENERIMA - KOMPATIBILITAS FORM LAMA
             * =================================================
             */
            'penerima' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * =================================================
             * INSTRUKSI
             * =================================================
             *
             * Field ini menjadi field utama.
             */
            'instruksi' => [
                $isUpdate
                    ? 'sometimes'
                    : 'required',

                'string',

                'max:5000',
            ],

            /*
             * =================================================
             * ISI DISPOSISI
             * =================================================
             *
             * Dipertahankan untuk kompatibilitas kode lama.
             */
            'isi_disposisi' => [
                $isUpdate
                    ? 'sometimes'
                    : 'required',

                'string',

                'max:5000',
            ],

            /*
             * =================================================
             * CATATAN
             * =================================================
             */
            'catatan' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
             * =================================================
             * BATAS WAKTU
             * =================================================
             */
            'batas_waktu' => [
                'nullable',
                'date',
            ],

            /*
             * =================================================
             * STATUS
             * =================================================
             */
            'status' => [
                'nullable',
                'string',
                'in:menunggu,diproses,selesai',
            ],
        ];
    }

    /**
     * Validasi tambahan.
     */
    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (Validator $validator): void {

                /*
                 * =============================================
                 * SURAT MASUK
                 * =============================================
                 */
                if (
                    !$this->isUpdateRequest() &&
                    !$this->filled('surat_masuk_id')
                ) {
                    $validator->errors()->add(
                        'surat_masuk_id',
                        'Surat masuk wajib dipilih.'
                    );
                }

                /*
                 * =============================================
                 * INSTRUKSI
                 * =============================================
                 */
                if (
                    !$this->filled('instruksi')
                ) {
                    $validator->errors()->add(
                        'instruksi',
                        'Instruksi disposisi wajib diisi.'
                    );
                }

                /*
                 * =============================================
                 * ISI DISPOSISI
                 * =============================================
                 *
                 * Pada dasarnya instruksi dan isi_disposisi
                 * menggunakan nilai yang sama.
                 */
                if (
                    !$this->filled('isi_disposisi')
                ) {
                    $validator->errors()->add(
                        'isi_disposisi',
                        'Instruksi disposisi wajib diisi.'
                    );
                }

                /*
                 * =============================================
                 * PENERIMA
                 * =============================================
                 */
                if (
                    !$this->filled('kepada_user_id')
                ) {
                    $validator->errors()->add(
                        'kepada_user_id',
                        'Staf penerima disposisi wajib dipilih.'
                    );
                }

                /*
                 * =============================================
                 * BATAS WAKTU
                 * =============================================
                 */
                if (
                    $this->filled('batas_waktu')
                ) {
                    try {

                        $tanggalBatas =
                            \Carbon\Carbon::parse(
                                $this->input(
                                    'batas_waktu'
                                )
                            );

                        if (
                            !$tanggalBatas->isValid()
                        ) {
                            $validator->errors()->add(
                                'batas_waktu',
                                'Batas waktu tidak valid.'
                            );
                        }

                    } catch (\Throwable) {

                        $validator->errors()->add(
                            'batas_waktu',
                            'Format batas waktu tidak valid.'
                        );
                    }
                }
            }
        );
    }

    /**
     * Mengecek apakah request merupakan update.
     */
    private function isUpdateRequest(): bool
    {
        return $this->isMethod('put')
            || $this->isMethod('patch');
    }

    /**
     * Pesan validasi.
     */
    public function messages(): array
    {
        return [

            'surat_masuk_id.required' =>
                'Surat masuk wajib dipilih.',

            'surat_masuk_id.integer' =>
                'ID surat masuk tidak valid.',

            'surat_masuk_id.exists' =>
                'Data surat masuk tidak ditemukan.',

            'kepada_user_id.required' =>
                'Staf penerima disposisi wajib dipilih.',

            'kepada_user_id.integer' =>
                'ID penerima disposisi tidak valid.',

            'kepada_user_id.exists' =>
                'Data penerima tidak ditemukan.',

            'penerima.string' =>
                'Nama penerima harus berupa teks.',

            'penerima.max' =>
                'Nama penerima maksimal 255 karakter.',

            'instruksi.required' =>
                'Instruksi disposisi wajib diisi.',

            'instruksi.string' =>
                'Instruksi disposisi harus berupa teks.',

            'instruksi.max' =>
                'Instruksi disposisi maksimal 5.000 karakter.',

            'isi_disposisi.required' =>
                'Instruksi disposisi wajib diisi.',

            'isi_disposisi.string' =>
                'Instruksi disposisi harus berupa teks.',

            'isi_disposisi.max' =>
                'Instruksi disposisi maksimal 5.000 karakter.',

            'catatan.string' =>
                'Catatan harus berupa teks.',

            'catatan.max' =>
                'Catatan maksimal 5.000 karakter.',

            'batas_waktu.date' =>
                'Format batas waktu tidak valid.',

            'status.in' =>
                'Status disposisi harus Menunggu, Diproses, atau Selesai.',
        ];
    }

    /**
     * Nama atribut untuk pesan validasi.
     */
    public function attributes(): array
    {
        return [

            'surat_masuk_id' =>
                'surat masuk',

            'kepada_user_id' =>
                'penerima disposisi',

            'penerima' =>
                'penerima',

            'instruksi' =>
                'instruksi disposisi',

            'isi_disposisi' =>
                'instruksi disposisi',

            'catatan' =>
                'catatan',

            'batas_waktu' =>
                'batas waktu',

            'status' =>
                'status',
        ];
    }
}