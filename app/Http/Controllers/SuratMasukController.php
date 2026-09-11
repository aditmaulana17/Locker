<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratMasukRequest;
use App\Models\ActivityLog;
use App\Models\KategoriSurat;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class SuratMasukController extends Controller
{
    private const STATUS_OPTIONS = [
        'baru',
        'diproses',
        'didisposisikan',
        'selesai',
        'diarsipkan',
    ];

    private const ALLOWED_FILE_EXTENSIONS = [
        'pdf',
        'jpg',
        'jpeg',
        'png',
    ];

    private const ALLOWED_IMAGE_MIMES = [
        'image/jpeg',
        'image/png',
    ];

    /*
    |--------------------------------------------------------------------------
    | Batas file asli dari browser
    |--------------------------------------------------------------------------
    */

    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /*
    |--------------------------------------------------------------------------
    | Target maksimal hasil compression gambar
    |--------------------------------------------------------------------------
    */

    private const MAX_COMPRESSED_IMAGE_SIZE = 9 * 1024 * 1024;

    /*
    |--------------------------------------------------------------------------
    | Maksimal dimensi gambar hasil proses
    |--------------------------------------------------------------------------
    */

    private const MAX_IMAGE_WIDTH = 2500;
    private const MAX_IMAGE_HEIGHT = 2500;

    /*
    |--------------------------------------------------------------------------
    | Kualitas awal JPEG
    |--------------------------------------------------------------------------
    */

    private const JPEG_QUALITY = 82;

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $this->ensureUserAuthenticated();

        $rawKategoriIds = $request->input(
            'kategori_surat_id',
            $request->input('kategori_id', [])
        );

        if (
            is_scalar($rawKategoriIds) &&
            trim((string) $rawKategoriIds) !== ''
        ) {
            $rawKategoriIds = [$rawKategoriIds];
        }

        if (!is_array($rawKategoriIds)) {
            $rawKategoriIds = [];
        }

        $kategoriIds = collect($rawKategoriIds)
            ->flatten()
            ->filter(
                fn ($id) =>
                    is_scalar($id) &&
                    is_numeric($id) &&
                    (int) $id > 0
            )
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $rawStatuses = $request->input('status', []);

        if (!is_array($rawStatuses)) {
            $rawStatuses = [$rawStatuses];
        }

        $statuses = collect($rawStatuses)
            ->filter(fn ($status) => is_scalar($status))
            ->map(
                fn ($status) =>
                    strtolower(trim((string) $status))
            )
            ->filter(
                fn ($status) =>
                    in_array(
                        $status,
                        self::STATUS_OPTIONS,
                        true
                    )
            )
            ->unique()
            ->values()
            ->all();

        $query = SuratMasuk::query()
            ->with([
                'kategori',
                'penerima',
            ]);

        $search = trim(
            (string) $request->input('search', '')
        );

        if ($search !== '') {
            $keyword = '%' . $search . '%';

            $query->where(
                function (Builder $q) use ($keyword): void {
                    $q->where(
                        'nomor_agenda',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'nomor_surat',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'pengirim',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'perihal',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'ringkasan',
                        'like',
                        $keyword
                    )
                    ->orWhereHas(
                        'kategori',
                        function (
                            Builder $kategori
                        ) use ($keyword): void {
                            $kategori
                                ->where(
                                    'nama_kategori',
                                    'like',
                                    $keyword
                                )
                                ->orWhere(
                                    'kode',
                                    'like',
                                    $keyword
                                )
                                ->orWhere(
                                    'sifat',
                                    'like',
                                    $keyword
                                );
                        }
                    );
                }
            );
        }

        if (!empty($kategoriIds)) {
            $query->whereIn(
                'kategori_surat_id',
                $kategoriIds
            );
        }

        if (!empty($statuses)) {
            $query->whereIn(
                'status',
                $statuses
            );
        }

        $dariTanggal = trim(
            (string) $request->input(
                'dari_tanggal',
                ''
            )
        );

        $sampaiTanggal = trim(
            (string) $request->input(
                'sampai_tanggal',
                ''
            )
        );

        $validDariTanggal =
            $this->isValidDate($dariTanggal);

        $validSampaiTanggal =
            $this->isValidDate($sampaiTanggal);

        if (
            $validDariTanggal &&
            $validSampaiTanggal &&
            $dariTanggal > $sampaiTanggal
        ) {
            [
                $dariTanggal,
                $sampaiTanggal
            ] = [
                $sampaiTanggal,
                $dariTanggal
            ];
        }

        if ($validDariTanggal) {
            $query->whereDate(
                'tanggal_terima',
                '>=',
                $dariTanggal
            );
        }

        if ($validSampaiTanggal) {
            $query->whereDate(
                'tanggal_terima',
                '<=',
                $sampaiTanggal
            );
        }

        if ($this->userIsStaff()) {
            if (
                method_exists(
                    $query->getModel(),
                    'scopeUntukStaff'
                )
            ) {
                $query->untukStaff(
                    (int) Auth::id()
                );
            } else {
                $query->whereHas(
                    'disposisi',
                    function (
                        Builder $disposisi
                    ): void {
                        $disposisi->where(
                            'kepada_user_id',
                            (int) Auth::id()
                        );
                    }
                );
            }
        }

        $suratMasuks = $query
            ->orderByDesc('tanggal_terima')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $kategoris = KategoriSurat::query()
            ->orderBy('nama_kategori')
            ->get();

        return view(
            'surat_masuk.index',
            compact(
                'suratMasuks',
                'kategoris'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $this->ensureUserCanManageSurat();

        $kategoris = KategoriSurat::query()
            ->orderBy('nama_kategori')
            ->get();

        $nomorAgenda =
            SuratMasuk::generateNomorAgenda();

        return view(
            'surat_masuk.create',
            compact(
                'kategoris',
                'nomorAgenda'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        SuratMasukRequest $request
    ) {
        $this->ensureUserCanManageSurat();

        $data = $request->validated();

        $diskName = $this->getStorageDisk();

        $uploadedPath = null;

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | NOMOR AGENDA
            |--------------------------------------------------------------------------
            */

            $nomorInput = trim(
                (string) $request->input(
                    'nomor_agenda',
                    ''
                )
            );

            if (
                $nomorInput === '' ||
                SuratMasuk::where(
                    'nomor_agenda',
                    $nomorInput
                )->exists()
            ) {
                $data['nomor_agenda'] =
                    SuratMasuk::generateNomorAgenda();
            } else {
                $data['nomor_agenda'] =
                    $nomorInput;
            }

            /*
            |--------------------------------------------------------------------------
            | PENERIMA
            |--------------------------------------------------------------------------
            */

            $data['diterima_oleh'] =
                (int) Auth::id();

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $status = strtolower(
                trim(
                    (string) (
                        $data['status'] ??
                        'baru'
                    )
                )
            );

            $data['status'] =
                in_array(
                    $status,
                    self::STATUS_OPTIONS,
                    true
                )
                    ? $status
                    : 'baru';

            /*
            |--------------------------------------------------------------------------
            | FILE
            |--------------------------------------------------------------------------
            |
            | Upload:
            | Browser
            |   ↓
            | PHP temporary
            |   ↓
            | PDF     -> Supabase
            | JPG/PNG -> GD -> JPG -> Supabase
            |
            | File asli tidak disimpan permanen di server.
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile(
                    'lampiran_file'
                )
            ) {
                $uploadedPath =
                    $this->storeUploadedFile(
                        $request->file(
                            'lampiran_file'
                        ),
                        $diskName
                    );

                $data['lampiran_file'] =
                    $uploadedPath;
            } elseif (
                $request->filled(
                    'captured_image'
                )
            ) {
                $uploadedPath =
                    $this->uploadBase64Image(
                        (string) $request->input(
                            'captured_image'
                        ),
                        $diskName
                    );

                $data['lampiran_file'] =
                    $uploadedPath;
            }

            /*
            |--------------------------------------------------------------------------
            | JANGAN SIMPAN BASE64 KE DATABASE
            |--------------------------------------------------------------------------
            */

            unset(
                $data['captured_image']
            );

            /*
            |--------------------------------------------------------------------------
            | CREATE DATABASE
            |--------------------------------------------------------------------------
            */

            $surat =
                SuratMasuk::create(
                    $data
                );

            /*
            |--------------------------------------------------------------------------
            | ACTIVITY LOG
            |--------------------------------------------------------------------------
            */

            $this->logActivity(
                'create',
                'surat_masuk',
                sprintf(
                    'Menambah surat masuk %s - %s',
                    $surat->nomor_agenda,
                    $surat->perihal
                )
            );

            DB::commit();

            return redirect()
                ->route('surat-masuk.index')
                ->with(
                    'success',
                    'Surat masuk berhasil dicatat dengan nomor agenda ' .
                    $surat->nomor_agenda
                );
        } catch (Throwable $e) {
            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | HAPUS FILE YANG SUDAH TERKIRIM JIKA DATABASE GAGAL
            |--------------------------------------------------------------------------
            */

            if ($uploadedPath) {
                $this->deleteStorageFile(
                    $uploadedPath,
                    $diskName
                );
            }

            Log::error(
                'Gagal menyimpan Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'user_id' =>
                        Auth::id(),

                    'disk' =>
                        $diskName,

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal menyimpan surat masuk: ' .
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanView(
            $suratMasuk
        );

        $suratMasuk->load([
            'kategori',
            'penerima',
            'disposisi.dari',
            'disposisi.kepada',
        ]);

        $daftarStaf =
            User::query()
                ->where(
                    'id',
                    '!=',
                    Auth::id()
                )
                ->where(
                    'is_active',
                    true
                )
                ->where(
                    function ($query): void {
                        $query
                            ->whereRaw(
                                'LOWER(TRIM(role)) = ?',
                                ['staff']
                            )
                            ->orWhereRaw(
                                'LOWER(TRIM(role)) = ?',
                                ['staf']
                            );
                    }
                )
                ->orderBy('name')
                ->get();

        $fileUrl =
            $this->getFileUrl(
                $suratMasuk->lampiran_file
            );

        return view(
            'surat_masuk.show',
            compact(
                'suratMasuk',
                'fileUrl',
                'daftarStaf'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE DISPOSISI
    |--------------------------------------------------------------------------
    */

    public function storeDisposisi(
        Request $request,
        SuratMasuk $suratMasuk
    ) {
        $this->ensureUserCanManageSurat();

        $validated =
            $request->validate(
                [
                    'tujuan_user_id' => [
                        'required',
                        'integer',
                        Rule::exists(
                            'users',
                            'id'
                        )->where(
                            function ($query): void {
                                $query
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->where(
                                        function ($q): void {
                                            $q
                                                ->whereRaw(
                                                    'LOWER(TRIM(role)) = ?',
                                                    ['staff']
                                                )
                                                ->orWhereRaw(
                                                    'LOWER(TRIM(role)) = ?',
                                                    ['staf']
                                                );
                                        }
                                    );
                            }
                        ),
                    ],

                    'instruksi' => [
                        'required',
                        'string',
                        'max:5000',
                    ],

                    'batas_waktu' => [
                        'nullable',
                        'date',
                    ],
                ],
                [
                    'tujuan_user_id.required' =>
                        'Staf tujuan wajib dipilih.',

                    'tujuan_user_id.exists' =>
                        'Staf tujuan tidak valid atau tidak aktif.',

                    'instruksi.required' =>
                        'Instruksi disposisi wajib diisi.',

                    'instruksi.max' =>
                        'Instruksi disposisi maksimal 5.000 karakter.',

                    'batas_waktu.date' =>
                        'Batas waktu disposisi tidak valid.',
                ]
            );

        $tujuanUserId =
            (int) $validated['tujuan_user_id'];

        if (
            $tujuanUserId ===
            (int) Auth::id()
        ) {
            return back()
                ->withInput()
                ->withErrors(
                    [
                        'tujuan_user_id' =>
                            'Anda tidak dapat mengirim disposisi kepada diri sendiri.',
                    ]
                );
        }

        DB::beginTransaction();

        try {
            $instruksi =
                trim(
                    (string) $validated['instruksi']
                );

            $disposisiData = [
                'surat_masuk_id' =>
                    $suratMasuk->id,

                'dari_user_id' =>
                    (int) Auth::id(),

                'kepada_user_id' =>
                    $tujuanUserId,

                'instruksi' =>
                    $instruksi,

                'status' =>
                    'menunggu',

                'batas_waktu' =>
                    $validated['batas_waktu'] ??
                    null,
            ];

            if (
                Schema::hasColumn(
                    'disposisis',
                    'isi_disposisi'
                )
            ) {
                $disposisiData['isi_disposisi'] =
                    $instruksi;
            }

            if (
                Schema::hasColumn(
                    'disposisis',
                    'sifat'
                )
            ) {
                $disposisiData['sifat'] =
                    'biasa';
            }

            $suratMasuk
                ->disposisi()
                ->create(
                    $disposisiData
                );

            $suratMasuk->update(
                [
                    'status' =>
                        'didisposisikan',
                ]
            );

            $this->logActivity(
                'disposisi',
                'surat_masuk',
                'Mendisposisikan surat masuk ' .
                $suratMasuk->nomor_agenda
            );

            DB::commit();

            return back()->with(
                'success',
                'Disposisi surat berhasil dikirim ke staff yang dituju.'
            );
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error(
                'Gagal melakukan disposisi Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'surat_id' =>
                        $suratMasuk->id,

                    'user_id' =>
                        Auth::id(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal memproses disposisi: ' .
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureUserCanManageSurat();

        $kategoris =
            KategoriSurat::query()
                ->orderBy('nama_kategori')
                ->get();

        return view(
            'surat_masuk.edit',
            compact(
                'suratMasuk',
                'kategoris'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        SuratMasukRequest $request,
        SuratMasuk $suratMasuk
    ) {
        $this->ensureUserCanManageSurat();

        $data =
            $request->validated();

        $diskName =
            $this->getStorageDisk();

        $oldFile =
            $suratMasuk->lampiran_file;

        $newFile =
            null;

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            if (
                array_key_exists(
                    'status',
                    $data
                )
            ) {
                $status =
                    strtolower(
                        trim(
                            (string) $data['status']
                        )
                    );

                if (
                    in_array(
                        $status,
                        self::STATUS_OPTIONS,
                        true
                    )
                ) {
                    $data['status'] =
                        $status;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | FILE BARU
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile(
                    'lampiran_file'
                )
            ) {
                $newFile =
                    $this->storeUploadedFile(
                        $request->file(
                            'lampiran_file'
                        ),
                        $diskName
                    );

                $data['lampiran_file'] =
                    $newFile;
            } elseif (
                $request->filled(
                    'captured_image'
                )
            ) {
                $newFile =
                    $this->uploadBase64Image(
                        (string) $request->input(
                            'captured_image'
                        ),
                        $diskName
                    );

                $data['lampiran_file'] =
                    $newFile;
            } else {
                unset(
                    $data['lampiran_file']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | BASE64 JANGAN MASUK DATABASE
            |--------------------------------------------------------------------------
            */

            unset(
                $data['captured_image']
            );

            /*
            |--------------------------------------------------------------------------
            | UPDATE DATABASE
            |--------------------------------------------------------------------------
            */

            $suratMasuk->update(
                $data
            );

            $this->logActivity(
                'update',
                'surat_masuk',
                'Mengubah surat masuk ' .
                $suratMasuk->nomor_agenda
            );

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | HAPUS FILE LAMA
            |--------------------------------------------------------------------------
            */

            if (
                $newFile &&
                $oldFile &&
                $oldFile !== $newFile
            ) {
                $this->deleteStorageFile(
                    $oldFile,
                    $diskName
                );
            }

            return redirect()
                ->route('surat-masuk.index')
                ->with(
                    'success',
                    'Surat masuk berhasil diperbarui.'
                );
        } catch (Throwable $e) {
            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | HAPUS FILE BARU JIKA DATABASE GAGAL
            |--------------------------------------------------------------------------
            */

            if (
                $newFile &&
                $newFile !== $oldFile
            ) {
                $this->deleteStorageFile(
                    $newFile,
                    $diskName
                );
            }

            Log::error(
                'Gagal memperbarui Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'surat_id' =>
                        $suratMasuk->id,

                    'user_id' =>
                        Auth::id(),

                    'disk' =>
                        $diskName,

                    'trace' =>
                        $e->getTraceAsString(),
                ]
            );

            return back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal memperbarui surat masuk: ' .
                    $e->getMessage()
                );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureUserCanManageSurat();

        $nomor =
            $suratMasuk->nomor_agenda;

        try {
            DB::transaction(
                function () use (
                    $suratMasuk,
                    $nomor
                ): void {
                    $suratMasuk->delete();

                    $this->logActivity(
                        'delete',
                        'surat_masuk',
                        'Menghapus surat masuk ' .
                        $nomor
                    );
                }
            );

            return back()->with(
                'success',
                'Surat masuk berhasil dipindahkan ke arsip sampah.'
            );
        } catch (Throwable $e) {
            Log::error(
                'Gagal menghapus Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'surat_id' =>
                        $suratMasuk->id,

                    'user_id' =>
                        Auth::id(),
                ]
            );

            return back()->with(
                'error',
                'Gagal menghapus surat masuk: ' .
                $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CETAK LABEL
    |--------------------------------------------------------------------------
    */

    public function cetakLabel(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanView(
            $suratMasuk
        );

        $suratMasuk->load(
            'kategori'
        );

        return view(
            'surat_masuk.label',
            compact(
                'suratMasuk'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PREVIEW LAMPIRAN
    |--------------------------------------------------------------------------
    */

    public function previewLampiran(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanView(
            $suratMasuk
        );

        $file =
            $suratMasuk->lampiran_file;

        if (!$file) {
            abort(
                404,
                'File lampiran tidak ditemukan.'
            );
        }

        if (
            filter_var(
                $file,
                FILTER_VALIDATE_URL
            )
        ) {
            return redirect()->away(
                $file
            );
        }

        $diskName =
            $this->getStorageDisk();

        try {
            $disk =
                Storage::disk(
                    $diskName
                );

            /*
            |--------------------------------------------------------------------------
            | Jangan gunakan exists() sebagai syarat upload.
            |--------------------------------------------------------------------------
            */

            if (
                !method_exists(
                    $disk,
                    'temporaryUrl'
                )
            ) {
                throw new RuntimeException(
                    'Storage Supabase tidak mendukung temporary URL.'
                );
            }

            $url =
                $disk->temporaryUrl(
                    $file,
                    now()->addMinutes(30)
                );

            if (!$url) {
                throw new RuntimeException(
                    'Supabase gagal membuat URL sementara.'
                );
            }

            return redirect()->away(
                $url
            );
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error(
                'Gagal preview lampiran Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $file,

                    'disk' =>
                        $diskName,

                    'surat_id' =>
                        $suratMasuk->id,
                ]
            );

            return back()->with(
                'error',
                'Gagal membuka lampiran file: ' .
                $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD LAMPIRAN
    |--------------------------------------------------------------------------
    */

    public function downloadLampiran(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanView(
            $suratMasuk
        );

        $file =
            $suratMasuk->lampiran_file;

        if (!$file) {
            abort(
                404,
                'File lampiran tidak ditemukan.'
            );
        }

        if (
            filter_var(
                $file,
                FILTER_VALIDATE_URL
            )
        ) {
            return redirect()->away(
                $file
            );
        }

        $diskName =
            $this->getStorageDisk();

        try {
            $disk =
                Storage::disk(
                    $diskName
                );

            if (
                !method_exists(
                    $disk,
                    'temporaryUrl'
                )
            ) {
                throw new RuntimeException(
                    'Storage Supabase tidak mendukung temporary URL.'
                );
            }

            $url =
                $disk->temporaryUrl(
                    $file,
                    now()->addMinutes(30),
                    [
                        'ResponseContentDisposition' =>
                            'attachment; filename="' .
                            basename($file) .
                            '"',

                        'ResponseContentType' =>
                            $this->getMimeTypeFromPath(
                                $file
                            ),
                    ]
                );

            if (!$url) {
                throw new RuntimeException(
                    'Supabase gagal membuat URL download.'
                );
            }

            return redirect()->away(
                $url
            );
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error(
                'Gagal download lampiran Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $file,

                    'disk' =>
                        $diskName,

                    'surat_id' =>
                        $suratMasuk->id,
                ]
            );

            return back()->with(
                'error',
                'Gagal mengunduh lampiran file: ' .
                $e->getMessage()
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | CETAK DISPOSISI
    |--------------------------------------------------------------------------
    */

    public function cetakDisposisi(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanView(
            $suratMasuk
        );

        $suratMasuk->load([
            'kategori',
            'disposisi.dari',
            'disposisi.kepada',
        ]);

        return view(
            'surat_masuk.disposisi_pdf',
            compact(
                'suratMasuk'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESS CONTROL
    |--------------------------------------------------------------------------
    */

    private function ensureCanView(
        SuratMasuk $suratMasuk
    ): void {
        $this->ensureUserAuthenticated();

        $role =
            $this->resolveSuratUserRole();

        if (
            in_array(
                $role,
                [
                    'admin',
                    'pimpinan',
                ],
                true
            )
        ) {
            return;
        }

        if ($role === 'staff') {
            $hasDisposisi =
                $suratMasuk
                    ->disposisi()
                    ->where(
                        'kepada_user_id',
                        (int) Auth::id()
                    )
                    ->exists();

            if ($hasDisposisi) {
                return;
            }
        }

        abort(
            403,
            'Anda tidak memiliki izin untuk melihat surat masuk ini.'
        );
    }

    private function resolveSuratUserRole(): string
    {
        $user =
            Auth::user();

        if (!$user) {
            return '';
        }

        $role =
            strtolower(
                trim(
                    (string) (
                        $user->role ??
                        ''
                    )
                )
            );

        if (
            $role === '' &&
            isset($user->jabatan)
        ) {
            $role =
                strtolower(
                    trim(
                        (string) $user->jabatan
                    )
                );
        }

        return $role === 'staf'
            ? 'staff'
            : $role;
    }

    /*
    |--------------------------------------------------------------------------
    | STORAGE DISK
    |--------------------------------------------------------------------------
    */

    private function getStorageDisk(): string
    {
        $disk =
            strtolower(
                trim(
                    (string) config(
                        'filesystems.default',
                        ''
                    )
                )
            );

        /*
        |--------------------------------------------------------------------------
        | Wajib Supabase
        |--------------------------------------------------------------------------
        */

        if ($disk !== 'supabase') {
            throw new RuntimeException(
                'FILESYSTEM_DISK harus diset ke "supabase". File surat tidak boleh disimpan permanen di server.'
            );
        }

        return 'supabase';
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD ERROR
    |--------------------------------------------------------------------------
    */

    private function getUploadErrorMessage(
        UploadedFile $file
    ): string {
        return match (
            $file->getError()
        ) {
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
                'File gagal diupload. Kode upload PHP: ' .
                $file->getError(),
        };
    }

    /*
    |--------------------------------------------------------------------------
    | STORE UPLOADED FILE
    |--------------------------------------------------------------------------
    */

    private function storeUploadedFile(
        ?UploadedFile $file,
        string $diskName
    ): string {
        if (!$file) {
            throw new RuntimeException(
                'File lampiran tidak ditemukan.'
            );
        }

        if (!$file->isValid()) {
            throw new RuntimeException(
                $this->getUploadErrorMessage(
                    $file
                )
            );
        }

        $fileSize =
            $file->getSize();

        if (
            $fileSize === false ||
            $fileSize <= 0
        ) {
            throw new RuntimeException(
                'Ukuran file tidak dapat dibaca.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Maksimal 10 MB
        |--------------------------------------------------------------------------
        */

        if (
            $fileSize >
            self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                'Ukuran file lampiran maksimal 10 MB.'
            );
        }

        $extension =
            strtolower(
                trim(
                    (string) $file
                        ->getClientOriginalExtension()
                )
            );

        if ($extension === 'jpeg') {
            $extension = 'jpg';
        }

        if (
            !in_array(
                $extension,
                self::ALLOWED_FILE_EXTENSIONS,
                true
            )
        ) {
            throw new RuntimeException(
                'Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.'
            );
        }

        $realPath =
            $file->getRealPath();

        if (
            !$realPath ||
            !is_readable($realPath)
        ) {
            throw new RuntimeException(
                'File temporary upload tidak dapat dibaca.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        |
        | PDF tetap PDF.
        | Tidak disimpan di filesystem Docker.
        | Langsung dikirim ke Supabase.
        |--------------------------------------------------------------------------
        */

        if ($extension === 'pdf') {
            $mime =
                strtolower(
                    (string) $file->getMimeType()
                );

            if (
                $mime !==
                'application/pdf'
            ) {
                throw new RuntimeException(
                    'File PDF tidak valid.'
                );
            }

            $contents =
                file_get_contents(
                    $realPath
                );

            if (
                $contents === false ||
                $contents === ''
            ) {
                throw new RuntimeException(
                    'Gagal membaca file PDF.'
                );
            }

            return $this->storeBinaryFile(
                $contents,
                'pdf',
                'application/pdf',
                'surat-masuk',
                $diskName
            );
        }

        /*
        |--------------------------------------------------------------------------
        | IMAGE
        |--------------------------------------------------------------------------
        |
        | JPG / JPEG / PNG:
        |
        | temporary file
        |      ↓
        | GD
        |      ↓
        | resize
        |      ↓
        | JPEG compression
        |      ↓
        | Supabase
        |
        |--------------------------------------------------------------------------
        */

        $contents =
            file_get_contents(
                $realPath
            );

        if (
            $contents === false ||
            $contents === ''
        ) {
            throw new RuntimeException(
                'Gagal membaca file gambar.'
            );
        }

        $imageInfo =
            @getimagesizefromstring(
                $contents
            );

        if ($imageInfo === false) {
            throw new RuntimeException(
                'File bukan gambar yang valid.'
            );
        }

        $actualMime =
            strtolower(
                (string) (
                    $imageInfo['mime'] ??
                    ''
                )
            );

        if ($actualMime === 'image/jpg') {
            $actualMime =
                'image/jpeg';
        }

        if (
            !in_array(
                $actualMime,
                self::ALLOWED_IMAGE_MIMES,
                true
            )
        ) {
            throw new RuntimeException(
                'Jenis file gambar tidak didukung.'
            );
        }

        return $this->storeCompressedImage(
            $contents,
            $diskName,
            'surat-masuk'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CAMERA BASE64
    |--------------------------------------------------------------------------
    */

    private function uploadBase64Image(
        string $base64String,
        string $diskName
    ): string {
        $base64String =
            trim($base64String);

        if ($base64String === '') {
            throw new RuntimeException(
                'Data gambar kamera kosong.'
            );
        }

        if (
            !preg_match(
                '~^data:image/(png|jpeg|jpg);base64,~i',
                $base64String
            )
        ) {
            throw new RuntimeException(
                'Format hasil scan kamera tidak valid.'
            );
        }

        $commaPosition =
            strpos(
                $base64String,
                ','
            );

        if (
            $commaPosition === false
        ) {
            throw new RuntimeException(
                'Data gambar kamera tidak valid.'
            );
        }

        $encoded =
            substr(
                $base64String,
                $commaPosition + 1
            );

        $decodedData =
            base64_decode(
                $encoded,
                true
            );

        if (
            $decodedData === false ||
            $decodedData === ''
        ) {
            throw new RuntimeException(
                'Gagal memproses gambar kamera. Data Base64 tidak valid.'
            );
        }

        if (
            strlen($decodedData) >
            self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                'Ukuran hasil scan kamera maksimal 10 MB.'
            );
        }

        $imageInfo =
            @getimagesizefromstring(
                $decodedData
            );

        if (
            $imageInfo === false
        ) {
            throw new RuntimeException(
                'Data kamera bukan gambar yang valid.'
            );
        }

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
                self::ALLOWED_IMAGE_MIMES,
                true
            )
        ) {
            throw new RuntimeException(
                'Jenis gambar hasil scan tidak didukung.'
            );
        }

        return $this->storeCompressedImage(
            $decodedData,
            $diskName,
            'scan'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | COMPRESS IMAGE
    |--------------------------------------------------------------------------
    */

    private function storeCompressedImage(
        string $contents,
        string $diskName,
        string $prefix
    ): string {
        if (
            !function_exists(
                'imagecreatefromstring'
            ) ||
            !function_exists(
                'imagejpeg'
            )
        ) {
            throw new RuntimeException(
                'PHP GD belum tersedia. Aktifkan ekstensi GD pada server.'
            );
        }

        $imageInfo =
            @getimagesizefromstring(
                $contents
            );

        if (
            $imageInfo === false
        ) {
            throw new RuntimeException(
                'File bukan gambar yang valid.'
            );
        }

        $mime =
            strtolower(
                (string) (
                    $imageInfo['mime'] ??
                    ''
                )
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
                self::ALLOWED_IMAGE_MIMES,
                true
            )
        ) {
            throw new RuntimeException(
                'Format gambar tidak didukung.'
            );
        }

        $sourceWidth =
            (int) (
                $imageInfo[0] ??
                0
            );

        $sourceHeight =
            (int) (
                $imageInfo[1] ??
                0
            );

        if (
            $sourceWidth <= 0 ||
            $sourceHeight <= 0
        ) {
            throw new RuntimeException(
                'Dimensi gambar tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Lindungi memory PHP
        |--------------------------------------------------------------------------
        */

        $pixelCount =
            $sourceWidth *
            $sourceHeight;

        if (
            $pixelCount >
            50000000
        ) {
            throw new RuntimeException(
                'Resolusi gambar terlalu besar. Gunakan gambar dengan resolusi lebih kecil.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD SOURCE
        |--------------------------------------------------------------------------
        */

        $source =
            @imagecreatefromstring(
                $contents
            );

        if (
            $source === false
        ) {
            throw new RuntimeException(
                'Gagal membaca gambar menggunakan GD.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | HITUNG UKURAN BARU
        |--------------------------------------------------------------------------
        */

        $scale =
            min(
                self::MAX_IMAGE_WIDTH /
                    $sourceWidth,

                self::MAX_IMAGE_HEIGHT /
                    $sourceHeight,

                1
            );

        $newWidth =
            max(
                1,
                (int) round(
                    $sourceWidth *
                    $scale
                )
            );

        $newHeight =
            max(
                1,
                (int) round(
                    $sourceHeight *
                    $scale
                )
            );

        /*
        |--------------------------------------------------------------------------
        | CANVAS
        |--------------------------------------------------------------------------
        */

        $canvas =
            @imagecreatetruecolor(
                $newWidth,
                $newHeight
            );

        if (
            $canvas === false
        ) {
            imagedestroy(
                $source
            );

            throw new RuntimeException(
                'Gagal membuat canvas gambar.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BACKGROUND PUTIH
        |--------------------------------------------------------------------------
        */

        $white =
            imagecolorallocate(
                $canvas,
                255,
                255,
                255
            );

        imagefill(
            $canvas,
            0,
            0,
            $white
        );

        /*
        |--------------------------------------------------------------------------
        | RESAMPLE
        |--------------------------------------------------------------------------
        */

        if (
            !imagecopyresampled(
                $canvas,
                $source,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $sourceWidth,
                $sourceHeight
            )
        ) {
            imagedestroy(
                $source
            );

            imagedestroy(
                $canvas
            );

            throw new RuntimeException(
                'Gagal melakukan resize gambar.'
            );
        }

        imagedestroy(
            $source
        );

        /*
        |--------------------------------------------------------------------------
        | COMPRESSION BERTAHAP
        |--------------------------------------------------------------------------
        */

        $qualities = [
            self::JPEG_QUALITY,
            75,
            68,
            60,
            52,
            45,
            38,
        ];

        $compressedData =
            null;

        foreach (
            $qualities as $quality
        ) {
            ob_start();

            $success =
                imagejpeg(
                    $canvas,
                    null,
                    $quality
                );

            $output =
                ob_get_clean();

            if (
                !$success ||
                $output === false ||
                $output === ''
            ) {
                imagedestroy(
                    $canvas
                );

                throw new RuntimeException(
                    'Gagal melakukan compression gambar.'
                );
            }

            $compressedData =
                $output;

            if (
                strlen(
                    $compressedData
                ) <=
                self::MAX_COMPRESSED_IMAGE_SIZE
            ) {
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RESIZE LANJUTAN
        |--------------------------------------------------------------------------
        |
        | Jika masih > 9 MB, kecilkan 75%
        | maksimal 5 kali.
        |--------------------------------------------------------------------------
        */

        $attempt = 0;

        while (
            $compressedData !== null &&
            strlen(
                $compressedData
            ) >
            self::MAX_COMPRESSED_IMAGE_SIZE &&
            $attempt < 5
        ) {
            $attempt++;

            $newWidth =
                max(
                    1,
                    (int) floor(
                        imagesx($canvas) *
                        0.75
                    )
                );

            $newHeight =
                max(
                    1,
                    (int) floor(
                        imagesy($canvas) *
                        0.75
                    )
                );

            $smallerCanvas =
                @imagecreatetruecolor(
                    $newWidth,
                    $newHeight
                );

            if (
                $smallerCanvas === false
            ) {
                imagedestroy(
                    $canvas
                );

                throw new RuntimeException(
                    'Gagal melakukan resize lanjutan gambar.'
                );
            }

            $white =
                imagecolorallocate(
                    $smallerCanvas,
                    255,
                    255,
                    255
                );

            imagefill(
                $smallerCanvas,
                0,
                0,
                $white
            );

            if (
                !imagecopyresampled(
                    $smallerCanvas,
                    $canvas,
                    0,
                    0,
                    0,
                    0,
                    $newWidth,
                    $newHeight,
                    imagesx($canvas),
                    imagesy($canvas)
                )
            ) {
                imagedestroy(
                    $canvas
                );

                imagedestroy(
                    $smallerCanvas
                );

                throw new RuntimeException(
                    'Gagal melakukan resize lanjutan gambar.'
                );
            }

            imagedestroy(
                $canvas
            );

            $canvas =
                $smallerCanvas;

            ob_start();

            $success =
                imagejpeg(
                    $canvas,
                    null,
                    45
                );

            $output =
                ob_get_clean();

            if (
                !$success ||
                $output === false ||
                $output === ''
            ) {
                imagedestroy(
                    $canvas
                );

                throw new RuntimeException(
                    'Gagal melakukan compression lanjutan gambar.'
                );
            }

            $compressedData =
                $output;
        }

        /*
        |--------------------------------------------------------------------------
        | DESTROY CANVAS
        |--------------------------------------------------------------------------
        */

        imagedestroy(
            $canvas
        );

        /*
        |--------------------------------------------------------------------------
        | FINAL CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $compressedData === null ||
            $compressedData === ''
        ) {
            throw new RuntimeException(
                'Hasil compression gambar kosong.'
            );
        }

        if (
            strlen(
                $compressedData
            ) >
            self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                'Gambar masih melebihi batas 10 MB setelah compression.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SIMPAN HANYA HASIL JPEG
        |--------------------------------------------------------------------------
        */

        return $this->storeBinaryFile(
            $compressedData,
            'jpg',
            'image/jpeg',
            $prefix,
            $diskName
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORE BINARY
    |--------------------------------------------------------------------------
    */

    private function storeBinaryFile(
        string $contents,
        string $extension,
        string $mimeType,
        string $prefix,
        string $diskName
    ): string {
        if (
            $contents === ''
        ) {
            throw new RuntimeException(
                'Data file kosong.'
            );
        }

        if (
            strlen($contents) >
            self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                'Data file melebihi batas 10 MB.'
            );
        }

        if (
            $diskName !==
            'supabase'
        ) {
            throw new RuntimeException(
                'Penyimpanan file E-Arsip wajib menggunakan Supabase.'
            );
        }

        $safePrefix =
            Str::slug(
                $prefix,
                '-'
            );

        $fileName =
            $safePrefix .
            '_' .
            now()->format(
                'Ymd_His'
            ) .
            '_' .
            Str::lower(
                Str::random(12)
            ) .
            '.' .
            strtolower(
                trim($extension)
            );

        $path =
            'lampiran/surat_masuk/' .
            $fileName;

        try {
            $disk =
                Storage::disk(
                    'supabase'
                );

            /*
            |--------------------------------------------------------------------------
            | Upload
            |--------------------------------------------------------------------------
            |
            | Yang dikirim hanya $contents.
            |--------------------------------------------------------------------------
            */

            $saved =
                $disk->put(
                    $path,
                    $contents,
                    [
                        'visibility' =>
                            'private',

                        'ContentType' =>
                            $mimeType,
                    ]
                );

            /*
            |--------------------------------------------------------------------------
            | JANGAN menjalankan $disk->exists($path)
            |--------------------------------------------------------------------------
            |
            | Keberhasilan put() digunakan sebagai indikator utama.
            |--------------------------------------------------------------------------
            */

            if (!$saved) {
                throw new RuntimeException(
                    'Supabase menolak penyimpanan file.'
                );
            }

            return $path;
        } catch (Throwable $e) {
            Log::error(
                'Gagal menyimpan binary Surat Masuk ke Supabase.',
                [
                    'message' =>
                        $e->getMessage(),

                    'path' =>
                        $path,

                    'extension' =>
                        $extension,

                    'mime' =>
                        $mimeType,

                    'size' =>
                        strlen($contents),

                    'disk' =>
                        'supabase',
                ]
            );

            throw new RuntimeException(
                'Gagal menyimpan file ke Supabase: ' .
                $e->getMessage(),
                previous: $e
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | FILE URL
    |--------------------------------------------------------------------------
    */

    private function getFileUrl(
        ?string $file
    ): ?string {
        if (!$file) {
            return null;
        }

        if (
            filter_var(
                $file,
                FILTER_VALIDATE_URL
            )
        ) {
            return $file;
        }

        $diskName =
            $this->getStorageDisk();

        try {
            $disk =
                Storage::disk(
                    $diskName
                );

            if (
                !method_exists(
                    $disk,
                    'temporaryUrl'
                )
            ) {
                return null;
            }

            $url =
                $disk->temporaryUrl(
                    $file,
                    now()->addMinutes(30)
                );

            return $url ?: null;
        } catch (Throwable $e) {
            Log::warning(
                'Gagal membuat URL lampiran Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $file,

                    'disk' =>
                        $diskName,
                ]
            );

            return null;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE STORAGE FILE
    |--------------------------------------------------------------------------
    */

    private function deleteStorageFile(
        ?string $file,
        ?string $diskName = null
    ): void {
        if (
            !$file ||
            filter_var(
                $file,
                FILTER_VALIDATE_URL
            )
        ) {
            return;
        }

        $diskName ??=
            $this->getStorageDisk();

        try {
            $disk =
                Storage::disk(
                    $diskName
                );

            /*
            |--------------------------------------------------------------------------
            | exists() hanya digunakan saat DELETE.
            |--------------------------------------------------------------------------
            */

            if (
                $disk->exists($file)
            ) {
                $disk->delete(
                    $file
                );
            }
        } catch (Throwable $e) {
            Log::warning(
                'Gagal menghapus file Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $file,

                    'disk' =>
                        $diskName,
                ]
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | MIME TYPE
    |--------------------------------------------------------------------------
    */

    private function getMimeTypeFromPath(
        string $file
    ): string {
        return match (
            strtolower(
                pathinfo(
                    $file,
                    PATHINFO_EXTENSION
                )
            )
        ) {
            'pdf' =>
                'application/pdf',

            'jpg',
            'jpeg' =>
                'image/jpeg',

            'png' =>
                'image/png',

            default =>
                'application/octet-stream',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | DATE VALIDATION
    |--------------------------------------------------------------------------
    */

    private function isValidDate(
        ?string $date
    ): bool {
        if ($date === null) {
            return false;
        }

        $date =
            trim($date);

        /*
        |--------------------------------------------------------------------------
        | REGEX YANG BENAR
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $date
            )
        ) {
            return false;
        }

        $parts =
            explode(
                '-',
                $date
            );

        if (
            count($parts) !== 3
        ) {
            return false;
        }

        return checkdate(
            (int) $parts[1],
            (int) $parts[2],
            (int) $parts[0]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    private function logActivity(
        string $action,
        string $module,
        string $description
    ): void {
        try {
            if (
                class_exists(
                    ActivityLog::class
                ) &&
                method_exists(
                    ActivityLog::class,
                    'catat'
                )
            ) {
                ActivityLog::catat(
                    $action,
                    $module,
                    $description
                );
            }
        } catch (Throwable $e) {
            Log::warning(
                'Gagal mencatat Activity Log Surat Masuk.',
                [
                    'message' =>
                        $e->getMessage(),

                    'action' =>
                        $action,

                    'module' =>
                        $module,
                ]
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | AUTHENTICATION
    |--------------------------------------------------------------------------
    */

    private function ensureUserAuthenticated(): void
    {
        if (!Auth::check()) {
            abort(
                401,
                'Anda harus login terlebih dahulu.'
            );
        }
    }

    private function ensureUserCanManageSurat(): void
    {
        $this->ensureUserAuthenticated();

        $role =
            $this->resolveSuratUserRole();

        if (
            !in_array(
                $role,
                [
                    'admin',
                    'pimpinan',
                ],
                true
            )
        ) {
            abort(
                403,
                'Anda tidak memiliki izin untuk mengelola surat masuk.'
            );
        }
    }

    private function userIsStaff(): bool
    {
        return
            $this->resolveSuratUserRole() ===
            'staff';
    }
}