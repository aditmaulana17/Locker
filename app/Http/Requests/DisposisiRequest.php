<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class DisposisiRequest extends FormRequest
{
    /**
     * Hak akses request.
     *
     * Hanya admin dan pimpinan yang boleh
     * membuat atau mengubah disposisi.
     */
    public function authorize(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

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
         * Normalisasi staff menjadi staf.
         */
        if ($role === 'staff') {
            $role = 'staf';
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
     * Persiapan data sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        /*
         * =====================================================
         * INSTRUKSI DISPOSISI
         * =====================================================
         *
         * Prioritas:
         * 1. instruksi
         * 2. isi_disposisi
         * 3. catatan
         */
        $instruksi = $this->input('instruksi');

        if (
            $instruksi === null ||
            trim((string) $instruksi) === ''
        ) {
            $instruksi =
                $this->input('isi_disposisi');
        }

        if (
            $instruksi === null ||
            trim((string) $instruksi) === ''
        ) {
            $instruksi =
                $this->input('catatan');
        }

        if ($instruksi !== null) {
            $instruksi =
                trim((string) $instruksi);

            $data['instruksi'] =
                $instruksi;

            $data['isi_disposisi'] =
                $instruksi;
        }

        /*
         * =====================================================
         * CATATAN
         * =====================================================
         */
        if ($this->has('catatan')) {
            $catatan =
                $this->input('catatan');

            $data['catatan'] =
                $catatan !== null
                    ? trim((string) $catatan)
                    : null;
        }

        /*
         * =====================================================
         * SURAT MASUK
         * =====================================================
         */
        if ($this->filled('surat_masuk_id')) {
            $data['surat_masuk_id'] =
                (int) $this->input(
                    'surat_masuk_id'
                );
        }

        /*
         * =====================================================
         * PENERIMA
         * =====================================================
         */
        if ($this->filled('kepada_user_id')) {
            $data['kepada_user_id'] =
                (int) $this->input(
                    'kepada_user_id'
                );
        }

        /*
         * =====================================================
         * BATAS WAKTU
         * =====================================================
         */
        if ($this->has('batas_waktu')) {
            $batasWaktu =
                $this->input('batas_waktu');

            $data['batas_waktu'] =
                $batasWaktu !== null
                    ? trim((string) $batasWaktu)
                    : null;
        }

        /*
         * =====================================================
         * STATUS
         * =====================================================
         */
        $status =
            $this->input('status');

        $data['status'] =
            $status === null ||
            trim((string) $status) === ''
                ? 'menunggu'
                : strtolower(
                    trim((string) $status)
                );

        /*
         * =====================================================
         * FIELD PENERIMA TAMPILAN
         * =====================================================
         *
         * Dipertahankan untuk kompatibilitas form,
         * tetapi controller tidak menyimpannya ke database.
         */
        if ($this->has('penerima')) {
            $penerima =
                $this->input('penerima');

            $data['penerima'] =
                $penerima !== null
                    ? trim((string) $penerima)
                    : null;
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    /**
     * Aturan validasi.
     */
    public function rules(): array
    {
        $isUpdate =
            $this->isUpdateRequest();

        return [
            /*
             * Surat masuk wajib dipilih
             * pada saat create.
             */
            'surat_masuk_id' => [
                $isUpdate
                    ? 'sometimes'
                    : 'required',

                'integer',
                'exists:surat_masuks,id',
            ],

            /*
             * Penerima disposisi.
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
                    $recipient =
                        User::query()
                            ->whereKey($value)
                            ->where(
                                'is_active',
                                true
                            )
                            ->where(
                                function (
                                    $query
                                ) {
                                    $query
                                        ->where(
                                            function (
                                                $query
                                            ) {
                                                $query
                                                    ->whereNotNull(
                                                        'role'
                                                    )
                                                    ->whereRaw(
                                                        'LOWER(TRIM(role)) IN (?, ?)',
                                                        [
                                                            'staf',
                                                            'staff',
                                                        ]
                                                    );
                                            }
                                        )
                                        ->orWhere(
                                            function (
                                                $query
                                            ) {
                                                $query
                                                    ->whereNotNull(
                                                        'jabatan'
                                                    )
                                                    ->whereRaw(
                                                        'LOWER(TRIM(jabatan)) IN (?, ?)',
                                                        [
                                                            'staf',
                                                            'staff',
                                                        ]
                                                    );
                                            }
                                        );
                                }
                            )
                            ->first();

                    /*
                     * Harus staf aktif.
                     */
                    if (!$recipient) {
                        $fail(
                            'Penerima disposisi harus merupakan staf aktif.'
                        );

                        return;
                    }

                    /*
                     * Tidak boleh mengirim ke diri sendiri.
                     */
                    if (
                        (int) $recipient->getKey() ===
                        (int) Auth::id()
                    ) {
                        $fail(
                            'Anda tidak dapat mengirim disposisi kepada diri sendiri.'
                        );
                    }
                },
            ],

            /*
             * Nama penerima untuk kebutuhan form.
             */
            'penerima' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
             * Instruksi utama.
             */
            'instruksi' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
             * Field kompatibilitas.
             */
            'isi_disposisi' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
             * Catatan.
             */
            'catatan' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
             * Batas waktu.
             */
            'batas_waktu' => [
                'nullable',
                'date',
            ],

            /*
             * Status disposisi.
             */
            'status' => [
                'nullable',
                'string',
                'in:menunggu,diproses,selesai',
            ],
        ];
    }

    /**
     * Validasi tambahan setelah rules utama.
     */
    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (
                Validator $validator
            ): void {

                /*
                 * =================================================
                 * SURAT MASUK
                 * =================================================
                 */
                if (
                    !$this->isUpdateRequest() &&
                    !$this->filled('surat_masuk_id')
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'surat_masuk_id',
                            'Surat masuk wajib dipilih.'
                        );
                }

                /*
                 * =================================================
                 * INSTRUKSI
                 * =================================================
                 */
                $instruksi =
                    $this->input('instruksi')
                    ?? $this->input('isi_disposisi')
                    ?? '';

                $instruksi =
                    trim((string) $instruksi);

                if ($instruksi === '') {
                    $validator
                        ->errors()
                        ->add(
                            'instruksi',
                            'Instruksi disposisi wajib diisi.'
                        );
                }

                /*
                 * =================================================
                 * PENERIMA
                 * =================================================
                 */
                if (
                    !$this->filled(
                        'kepada_user_id'
                    )
                ) {
                    $validator
                        ->errors()
                        ->add(
                            'kepada_user_id',
                            'Staf penerima disposisi wajib dipilih.'
                        );
                }

                /*
                 * =================================================
                 * BATAS WAKTU
                 * =================================================
                 */
                if (
                    $this->filled(
                        'batas_waktu'
                    )
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
                            $validator
                                ->errors()
                                ->add(
                                    'batas_waktu',
                                    'Batas waktu tidak valid.'
                                );
                        }
                    } catch (
                        \Throwable
                    ) {
                        $validator
                            ->errors()
                            ->add(
                                'batas_waktu',
                                'Format batas waktu tidak valid.'
                            );
                    }
                }

                /*
                 * =================================================
                 * STATUS
                 * =================================================
                 */
                $status =
                    strtolower(
                        trim(
                            (string) (
                                $this->input(
                                    'status'
                                )
                                ?? 'menunggu'
                            )
                        )
                    );

                if (!in_array(
                    $status,
                    [
                        'menunggu',
                        'diproses',
                        'selesai',
                    ],
                    true
                )) {
                    $validator
                        ->errors()
                        ->add(
                            'status',
                            'Status disposisi tidak valid.'
                        );
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

            'instruksi.string' =>
                'Instruksi disposisi harus berupa teks.',

            'instruksi.max' =>
                'Instruksi disposisi maksimal 5.000 karakter.',

            'isi_disposisi.string' =>
                'Isi disposisi harus berupa teks.',

            'isi_disposisi.max' =>
                'Isi disposisi maksimal 5.000 karakter.',

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
                'Surat Masuk',

            'kepada_user_id' =>
                'Penerima Disposisi',

            'penerima' =>
                'Penerima',

            'instruksi' =>
                'Instruksi Disposisi',

            'isi_disposisi' =>
                'Isi Disposisi',

            'catatan' =>
                'Catatan',

            'batas_waktu' =>
                'Batas Waktu',

            'status' =>
                'Status Disposisi',
        ];
    }
}