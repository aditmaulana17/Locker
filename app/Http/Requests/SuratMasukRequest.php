<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SuratMasukRequest extends FormRequest
{
    /**
     * Menentukan apakah request boleh diproses.
     *
     * Hanya admin dan pimpinan yang dapat:
     * - membuat surat masuk
     * - mengedit surat masuk
     *
     * Staff hanya dapat melihat surat yang
     * didisposisikan kepadanya.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        /*
         * Gunakan helper User::normalizeRole()
         * agar role "staf" otomatis dianggap "staff".
         */
        if (method_exists(User::class, 'normalizeRole')) {
            $role = User::normalizeRole(
                $user->role ?? $user->jabatan ?? ''
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

    /**
     * Rules validasi request.
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
             * =====================================================
             * NOMOR AGENDA
             * =====================================================
             *
             * Saat edit, nomor agenda milik surat yang sama
             * tidak dianggap duplikat.
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
             * =====================================================
             * NOMOR SURAT
             * =====================================================
             */
            'nomor_surat' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * =====================================================
             * PENGIRIM
             * =====================================================
             */
            'pengirim' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * =====================================================
             * TANGGAL SURAT
             * =====================================================
             */
            'tanggal_surat' => [
                'required',
                'date',
            ],

            /*
             * =====================================================
             * TANGGAL DITERIMA
             * =====================================================
             */
            'tanggal_terima' => [
                'required',
                'date',
                'after_or_equal:tanggal_surat',
            ],

            /*
             * =====================================================
             * KATEGORI
             * =====================================================
             */
            'kategori_surat_id' => [
                'required',
                'integer',
                'exists:kategori_surats,id',
            ],

            /*
             * =====================================================
             * PERIHAL
             * =====================================================
             */
            'perihal' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * =====================================================
             * RINGKASAN
             * =====================================================
             */
            'ringkasan' => [
                'nullable',
                'string',
                'max:5000',
            ],

            /*
             * =====================================================
             * LAMPIRAN FILE
             * =====================================================
             *
             * Format yang diizinkan:
             *
             * PDF
             * JPG
             * JPEG
             * PNG
             * WEBP
             *
             * Maksimal 10 MB.
             *
             * Nullable karena pada EDIT user boleh
             * mempertahankan file lama.
             */
            'lampiran_file' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:pdf,jpg,jpeg,png,webp',
                'extensions:pdf,jpg,jpeg,png,webp',
            ],

            /*
             * =====================================================
             * HASIL SCAN KAMERA
             * =====================================================
             *
             * Berupa Data URI Base64.
             *
             * Contoh:
             * data:image/jpeg;base64,...
             * data:image/png;base64,...
             */
            'captured_image' => [
                'nullable',
                'string',
            ],

            /*
             * =====================================================
             * STATUS
             * =====================================================
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
             * =====================================================
             * LOKASI ARSIP FISIK
             * =====================================================
             */
            'lokasi_arsip_fisik' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Validasi tambahan setelah rules utama.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            /*
             * =====================================================
             * CEK CAPTURED IMAGE
             * =====================================================
             */
            if ($this->filled('captured_image')) {

                $capturedImage = trim(
                    (string) $this->input('captured_image')
                );

                if (
                    !preg_match(
                        '/^data:image\/(jpeg|jpg|png|webp);base64,/i',
                        $capturedImage
                    )
                ) {
                    $validator->errors()->add(
                        'captured_image',
                        'Format hasil scan kamera tidak valid. Gunakan JPG, JPEG, PNG, atau WEBP.'
                    );
                }
            }

            /*
             * =====================================================
             * CEK FILE UPLOAD
             * =====================================================
             */
            $hasFile =
                $this->hasFile('lampiran_file') &&
                $this->file('lampiran_file') &&
                $this->file('lampiran_file')->isValid();

            $hasCamera =
                $this->filled('captured_image');

            /*
             * Jangan izinkan upload file dan kamera
             * digunakan secara bersamaan.
             */
            if ($hasFile && $hasCamera) {
                $validator->errors()->add(
                    'lampiran_file',
                    'Pilih salah satu saja: upload file atau scan menggunakan kamera.'
                );
            }

            /*
             * =====================================================
             * CREATE
             * =====================================================
             *
             * Pada CREATE wajib ada dokumen digital:
             * - file upload
             * ATAU
             * - hasil scan kamera
             *
             * Pada EDIT tidak wajib karena file lama
             * tetap digunakan.
             */
            if ($this->isMethod('POST')) {

                if (!$hasFile && !$hasCamera) {
                    $validator->errors()->add(
                        'lampiran_file',
                        'Berkas digital wajib diupload atau discan menggunakan kamera.'
                    );
                }
            }

            /*
             * =====================================================
             * CEK ERROR UPLOAD PHP
             * =====================================================
             *
             * Ini penting karena file JPG/PNG kadang gagal
             * sebelum masuk ke proses validasi Laravel.
             */
            if ($this->hasFile('lampiran_file')) {

                $file = $this->file('lampiran_file');

                if ($file) {

                    $errorCode = $file->getError();

                    if (
                        $errorCode !== UPLOAD_ERR_OK
                    ) {
                        $message = match ($errorCode) {

                            UPLOAD_ERR_INI_SIZE,
                            UPLOAD_ERR_FORM_SIZE =>
                                'Ukuran file terlalu besar. Maksimal 10 MB.',

                            UPLOAD_ERR_PARTIAL =>
                                'File hanya terupload sebagian. Silakan coba lagi.',

                            UPLOAD_ERR_NO_FILE =>
                                'Tidak ada file yang dipilih.',

                            UPLOAD_ERR_NO_TMP_DIR =>
                                'Folder temporary upload PHP tidak tersedia.',

                            UPLOAD_ERR_CANT_WRITE =>
                                'Server gagal menulis file upload.',

                            UPLOAD_ERR_EXTENSION =>
                                'Upload file dihentikan oleh konfigurasi PHP.',

                            default =>
                                'File gagal diupload. Silakan coba lagi.',
                        };

                        $validator->errors()->add(
                            'lampiran_file',
                            $message
                        );
                    }
                }
            }
        });
    }

    /**
     * Membersihkan input sebelum validasi.
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
    }

    /**
     * Membersihkan input string biasa.
     */
    private function cleanInput(string $key): ?string
    {
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
     * Membersihkan data hasil kamera.
     */
    private function cleanCapturedImage(): ?string
    {
        if (!$this->filled('captured_image')) {
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
                ':attribute harus berformat PDF, JPG, JPEG, PNG, atau WEBP.',

            'lampiran_file.extensions' =>
                ':attribute memiliki ekstensi yang tidak didukung. Gunakan PDF, JPG, JPEG, PNG, atau WEBP.',

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
     * Nama field agar pesan validasi lebih mudah dibaca.
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