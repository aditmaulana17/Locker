<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class DisposisiRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        $role = strtolower(
            trim((string) ($user->role ?? $user->jabatan ?? ''))
        );

        if ($role === 'staff') {
            $role = 'staf';
        }

        return in_array(
            $role,
            ['admin', 'pimpinan'],
            true
        );
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        $instruksi =
            $this->input('instruksi')
            ?? $this->input('isi_disposisi')
            ?? $this->input('catatan');

        if ($instruksi !== null) {
            $instruksi = trim((string) $instruksi);

            $data['instruksi'] = $instruksi;
            $data['isi_disposisi'] = $instruksi;
        }

        if ($this->has('catatan')) {
            $catatan = $this->input('catatan');

            $data['catatan'] = $catatan !== null
                ? trim((string) $catatan)
                : null;
        }

        if ($this->filled('kepada_user_id')) {
            $data['kepada_user_id'] = (int) $this->input(
                'kepada_user_id'
            );
        }

        if ($this->filled('surat_masuk_id')) {
            $data['surat_masuk_id'] = (int) $this->input(
                'surat_masuk_id'
            );
        }

        if ($this->has('batas_waktu')) {
            $batasWaktu = $this->input('batas_waktu');

            $data['batas_waktu'] = $batasWaktu !== null
                ? trim((string) $batasWaktu)
                : null;
        }

        if ($this->filled('status')) {
            $data['status'] = strtolower(
                trim((string) $this->input('status'))
            );
        } else {
            $data['status'] = 'menunggu';
        }

        if ($this->has('penerima')) {
            $penerima = $this->input('penerima');

            $data['penerima'] = $penerima !== null
                ? trim((string) $penerima)
                : null;
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }

    public function rules(): array
    {
        $isUpdate = $this->isUpdateRequest();

        return [
            'surat_masuk_id' => [
                $isUpdate ? 'sometimes' : 'required',
                'integer',
                'exists:surat_masuks,id',
            ],

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
                        ->where('is_active', true)
                        ->where(function ($query) {
                            $query
                                ->where(function ($query) {
                                    $query
                                        ->whereNotNull('role')
                                        ->whereRaw(
                                            'LOWER(TRIM(role)) IN (?, ?)',
                                            ['staf', 'staff']
                                        );
                                })
                                ->orWhere(function ($query) {
                                    $query
                                        ->whereNotNull('jabatan')
                                        ->whereRaw(
                                            'LOWER(TRIM(jabatan)) IN (?, ?)',
                                            ['staf', 'staff']
                                        );
                                });
                        })
                        ->first();

                    if (!$recipient) {
                        $fail(
                            'Penerima disposisi harus merupakan staf aktif.'
                        );

                        return;
                    }

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

            'penerima' => [
                'nullable',
                'string',
                'max:255',
            ],

            'instruksi' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'isi_disposisi' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'catatan' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'batas_waktu' => [
                'nullable',
                'date',
            ],

            'status' => [
                'nullable',
                'string',
                'in:menunggu,diproses,selesai',
            ],
        ];
    }

    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(
            function (Validator $validator): void {
                if (
                    !$this->isUpdateRequest() &&
                    !$this->filled('surat_masuk_id')
                ) {
                    $validator->errors()->add(
                        'surat_masuk_id',
                        'Surat masuk wajib dipilih.'
                    );
                }

                $instruksi =
                    trim((string) (
                        $this->input('instruksi')
                        ?? $this->input('isi_disposisi')
                        ?? ''
                    ));

                if ($instruksi === '') {
                    $validator->errors()->add(
                        'instruksi',
                        'Instruksi disposisi wajib diisi.'
                    );
                }

                if (
                    !$this->filled('kepada_user_id')
                ) {
                    $validator->errors()->add(
                        'kepada_user_id',
                        'Staf penerima disposisi wajib dipilih.'
                    );
                }

                if ($this->filled('batas_waktu')) {
                    try {
                        $tanggalBatas = \Carbon\Carbon::parse(
                            $this->input('batas_waktu')
                        );

                        if (!$tanggalBatas->isValid()) {
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

                if ($this->filled('status')) {
                    $status = strtolower(
                        trim((string) $this->input('status'))
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
                        $validator->errors()->add(
                            'status',
                            'Status disposisi tidak valid.'
                        );
                    }
                }
            }
        );
    }

    private function isUpdateRequest(): bool
    {
        return $this->isMethod('put')
            || $this->isMethod('patch');
    }

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