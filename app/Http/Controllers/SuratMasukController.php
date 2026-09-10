<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratMasukRequest;
use App\Models\ActivityLog;
use App\Models\KategoriSurat;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Filesystem\FilesystemAdapter;
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
    /*
    |--------------------------------------------------------------------------
    | CONFIGURATION
    |--------------------------------------------------------------------------
    */

    /**
     * Status surat masuk yang valid.
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
     * Batas maksimum file upload.
     *
     * 10 MB.
     */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    /**
     * Target maksimum hasil compression gambar.
     *
     * Dibuat 9 MB agar masih memiliki margin
     * terhadap batas maksimum 10 MB.
     */
    private const MAX_COMPRESSED_IMAGE_SIZE = 9 * 1024 * 1024;

    /**
     * Dimensi maksimum gambar.
     */
    private const MAX_IMAGE_WIDTH = 2500;

    private const MAX_IMAGE_HEIGHT = 2500;

    /**
     * Kualitas JPEG awal.
     */
    private const JPEG_QUALITY = 82;

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    /**
     * Menampilkan daftar surat masuk.
     */
    public function index(Request $request)
    {
        $this->ensureAuthenticated();

        /*
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        */

        $rawKategoriIds = $request->input(
            'kategori_surat_id',
            $request->input(
                'kategori_id',
                []
            )
        );

        if (
            is_scalar($rawKategoriIds)
            && trim((string) $rawKategoriIds) !== ''
        ) {
            $rawKategoriIds = [
                $rawKategoriIds,
            ];
        }

        if (!is_array($rawKategoriIds)) {
            $rawKategoriIds = [];
        }

        $kategoriIds = collect(
            $rawKategoriIds
        )
            ->flatten()
            ->filter(
                fn ($id) =>
                    is_scalar($id)
                    && is_numeric($id)
                    && (int) $id > 0
            )
            ->map(
                fn ($id) =>
                    (int) $id
            )
            ->unique()
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        $rawStatuses = $request->input(
            'status',
            []
        );

        if (!is_array($rawStatuses)) {
            $rawStatuses = [
                $rawStatuses,
            ];
        }

        $statuses = collect(
            $rawStatuses
        )
            ->filter(
                fn ($status) =>
                    is_scalar($status)
            )
            ->map(
                fn ($status) =>
                    strtolower(
                        trim(
                            (string) $status
                        )
                    )
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

        /*
        |--------------------------------------------------------------------------
        | QUERY
        |--------------------------------------------------------------------------
        */

        $query = SuratMasuk::query()
            ->with([
                'kategori',
                'penerima',
            ]);

        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        if ($search !== '') {
            $keyword =
                '%' . $search . '%';

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

        /*
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        */

        if (!empty($kategoriIds)) {
            $query->whereIn(
                'kategori_surat_id',
                $kategoriIds
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        if (!empty($statuses)) {
            $query->whereIn(
                'status',
                $statuses
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL
        |--------------------------------------------------------------------------
        */

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
            $this->isValidDate(
                $dariTanggal
            );

        $validSampaiTanggal =
            $this->isValidDate(
                $sampaiTanggal
            );

        if (
            $validDariTanggal
            && $validSampaiTanggal
            && $dariTanggal > $sampaiTanggal
        ) {
            [
                $dariTanggal,
                $sampaiTanggal,
            ] = [
                $sampaiTanggal,
                $dariTanggal,
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

        /*
        |--------------------------------------------------------------------------
        | FILTER STAFF
        |--------------------------------------------------------------------------
        |
        | isStaff() berasal dari Controller parent.
        |
        */

        if ($this->isStaff()) {
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

        /*
        |--------------------------------------------------------------------------
        | PAGINATION
        |--------------------------------------------------------------------------
        */

        $suratMasuks = $query
            ->orderByDesc(
                'tanggal_terima'
            )
            ->orderByDesc(
                'id'
            )
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategoris = KategoriSurat::query()
            ->orderBy(
                'nama_kategori'
            )
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

    /**
     * Form tambah surat masuk.
     *
     * ensureCanManageSurat() berasal dari Controller parent.
     */
    public function create()
    {
        $this->ensureCanManageSurat();

        $kategoris = KategoriSurat::query()
            ->orderBy(
                'nama_kategori'
            )
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

    /**
     * Menyimpan surat masuk baru.
     */
    public function store(
        SuratMasukRequest $request
    ) {
        $this->ensureCanManageSurat();

        $data =
            $request->validated();

        $diskName =
            $this->getStorageDisk();

        $uploadedPath = null;

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | NOMOR AGENDA
            |--------------------------------------------------------------------------
            */

            $nomorInput =
                trim(
                    (string) $request->input(
                        'nomor_agenda',
                        ''
                    )
                );

            if (
                $nomorInput === ''
                || SuratMasuk::where(
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
            | USER PENERIMA
            |--------------------------------------------------------------------------
            */

            $data['diterima_oleh'] =
                (int) Auth::id();

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $status =
                strtolower(
                    trim(
                        (string) (
                            $data['status']
                            ?? 'baru'
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
            | CAMERA
            |--------------------------------------------------------------------------
            */

            if (
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
            | FILE UPLOAD
            |--------------------------------------------------------------------------
            */

            elseif (
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
            }

            /*
            |--------------------------------------------------------------------------
            | CLEAN FIELD
            |--------------------------------------------------------------------------
            */

            unset(
                $data['captured_image']
            );

            /*
            |--------------------------------------------------------------------------
            | DATABASE
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
                ->route(
                    'surat-masuk.index'
                )
                ->with(
                    'success',
                    'Surat masuk berhasil dicatat dengan nomor agenda ' .
                    $surat->nomor_agenda
                );
        } catch (Throwable $e) {
            DB::rollBack();

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

    /**
     * Menampilkan detail surat masuk.
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
                ->orderBy(
                    'name'
                )
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
    | DISPOSISI
    |--------------------------------------------------------------------------
    */

    /**
     * Menyimpan disposisi surat masuk.
     */
    public function storeDisposisi(
        Request $request,
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanManageSurat();

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
                            function (
                                $query
                            ): void {
                                $query
                                    ->where(
                                        'is_active',
                                        true
                                    )
                                    ->where(
                                        function (
                                            $q
                                        ): void {
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
            (int) $validated[
                'tujuan_user_id'
            ];

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
                    (string) $validated[
                        'instruksi'
                    ]
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
                    $validated[
                        'batas_waktu'
                    ] ?? null,
            ];

            /*
            |--------------------------------------------------------------------------
            | KOMPATIBILITAS KOLOM
            |--------------------------------------------------------------------------
            */

            if (
                Schema::hasColumn(
                    'disposisis',
                    'isi_disposisi'
                )
            ) {
                $disposisiData[
                    'isi_disposisi'
                ] = $instruksi;
            }

            if (
                Schema::hasColumn(
                    'disposisis',
                    'sifat'
                )
            ) {
                $disposisiData[
                    'sifat'
                ] = 'biasa';
            }

            /*
            |--------------------------------------------------------------------------
            | CREATE DISPOSISI
            |--------------------------------------------------------------------------
            */

            $suratMasuk
                ->disposisi()
                ->create(
                    $disposisiData
                );

            /*
            |--------------------------------------------------------------------------
            | UPDATE STATUS
            |--------------------------------------------------------------------------
            */

            $suratMasuk->update(
                [
                    'status' =>
                        'didisposisikan',
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | LOG
            |--------------------------------------------------------------------------
            */

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

    /**
     * Form edit surat masuk.
     */
    public function edit(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanManageSurat();

        $kategoris =
            KategoriSurat::query()
                ->orderBy(
                    'nama_kategori'
                )
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

    /**
     * Memperbarui surat masuk.
     *
     * File lama dipertahankan jika tidak
     * ada file baru.
     */
    public function update(
        SuratMasukRequest $request,
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanManageSurat();

        $data =
            $request->validated();

        $diskName =
            $this->getStorageDisk();

        $oldFile =
            $suratMasuk->lampiran_file;

        $newFile = null;

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
                            (string) $data[
                                'status'
                            ]
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
            | CAMERA
            |--------------------------------------------------------------------------
            */

            if (
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
            }

            /*
            |--------------------------------------------------------------------------
            | FILE BARU
            |--------------------------------------------------------------------------
            */

            elseif (
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
            }

            /*
            |--------------------------------------------------------------------------
            | TIDAK ADA FILE BARU
            |--------------------------------------------------------------------------
            */

            else {
                unset(
                    $data['lampiran_file']
                );
            }

            unset(
                $data['captured_image']
            );

            /*
            |--------------------------------------------------------------------------
            | UPDATE
            |--------------------------------------------------------------------------
            */

            $suratMasuk->update(
                $data
            );

            /*
            |--------------------------------------------------------------------------
            | ACTIVITY
            |--------------------------------------------------------------------------
            */

            $this->logActivity(
                'update',
                'surat_masuk',
                'Mengubah surat masuk ' .
                $suratMasuk->nomor_agenda
            );

            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | DELETE FILE LAMA
            |--------------------------------------------------------------------------
            */

            if (
                $newFile
                && $oldFile
                && $oldFile !== $newFile
            ) {
                $this->deleteStorageFile(
                    $oldFile,
                    $diskName
                );
            }

            return redirect()
                ->route(
                    'surat-masuk.index'
                )
                ->with(
                    'success',
                    'Surat masuk berhasil diperbarui.'
                );
        } catch (Throwable $e) {
            DB::rollBack();

            if (
                $newFile
                && $newFile !== $oldFile
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

    /**
     * Soft delete surat masuk.
     */
    public function destroy(
        SuratMasuk $suratMasuk
    ) {
        $this->ensureCanManageSurat();

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

    /**
     * Cetak label surat masuk.
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

    /**
     * Preview lampiran.
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

        /*
        |--------------------------------------------------------------------------
        | EXTERNAL URL
        |--------------------------------------------------------------------------
        */

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

            if (!$disk->exists($file)) {
                abort(
                    404,
                    'File lampiran tidak ditemukan di storage.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SUPABASE
            |--------------------------------------------------------------------------
            */

            if (
                $diskName === 'supabase'
            ) {
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

                return redirect()->away(
                    $url
                );
            }

            /*
            |--------------------------------------------------------------------------
            | LOCAL
            |--------------------------------------------------------------------------
            */

            if (
                method_exists(
                    $disk,
                    'response'
                )
            ) {
                return $disk->response(
                    $file
                );
            }

            $content =
                $disk->get(
                    $file
                );

            return response(
                $content,
                200,
                [
                    'Content-Type' =>
                        $this->getMimeTypeFromPath(
                            $file
                        ),

                    'Content-Disposition' =>
                        'inline',

                    'Cache-Control' =>
                        'private, max-age=300',
                ]
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

    /**
     * Download lampiran.
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

            if (!$disk->exists($file)) {
                abort(
                    404,
                    'File lampiran tidak ditemukan di storage.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SUPABASE
            |--------------------------------------------------------------------------
            */

            if (
                $diskName === 'supabase'
            ) {
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

                return redirect()->away(
                    $url
                );
            }

            /*
            |--------------------------------------------------------------------------
            | LOCAL
            |--------------------------------------------------------------------------
            */

            if (
                method_exists(
                    $disk,
                    'download'
                )
            ) {
                return $disk->download(
                    $file,
                    basename($file)
                );
            }

            $content =
                $disk->get(
                    $file
                );

            return response(
                $content,
                200,
                [
                    'Content-Type' =>
                        $this->getMimeTypeFromPath(
                            $file
                        ),

                    'Content-Disposition' =>
                        'attachment; filename="' .
                        basename($file) .
                        '"',
                ]
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

    /**
     * Cetak disposisi.
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
    |
    | ensureAuthenticated()
    | ensureCanManageSurat()
    | isStaff()
    |
    | semuanya diwarisi dari Controller parent.
    |
    */

    /**
     * Memastikan user boleh melihat surat tertentu.
     *
     * Admin dan pimpinan dapat melihat semua surat.
     * Staff hanya dapat melihat surat yang mempunyai
     * disposisi kepada dirinya.
     */
    private function ensureCanView(
        SuratMasuk $suratMasuk
    ): void {
        $this->ensureAuthenticated();

        $user =
            Auth::user();

        if (!$user) {
            abort(
                401,
                'Anda harus login terlebih dahulu.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ROLE
        |--------------------------------------------------------------------------
        */

        $role =
            strtolower(
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

        /*
        |--------------------------------------------------------------------------
        | ADMIN / PIMPINAN
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | STAFF
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | DENY
        |--------------------------------------------------------------------------
        */

        abort(
            403,
            'Anda tidak memiliki izin untuk melihat surat masuk ini.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STORAGE
    |--------------------------------------------------------------------------
    */

    /**
     * Mendapatkan disk storage aktif.
     */
    private function getStorageDisk(): string
    {
        $disk =
            strtolower(
                trim(
                    (string) config(
                        'filesystems.default',
                        'public'
                    )
                )
            );

        return $disk === 'supabase'
            ? 'supabase'
            : 'public';
    }

    /**
     * Mendapatkan filesystem adapter.
     */
    private function storage(): FilesystemAdapter
    {
        return Storage::disk(
            $this->getStorageDisk()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UPLOAD ERROR
    |--------------------------------------------------------------------------
    */

    /**
     * Mengubah error PHP upload menjadi
     * pesan berbahasa Indonesia.
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

    /**
     * Menyimpan file upload.
     *
     * PDF tidak diubah.
     *
     * JPG/JPEG/PNG diproses menggunakan GD.
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

        /*
        |--------------------------------------------------------------------------
        | SIZE
        |--------------------------------------------------------------------------
        */

        $fileSize =
            $file->getSize();

        if (
            $fileSize !== false
            && $fileSize > self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                'Ukuran file lampiran maksimal 10 MB.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | EXTENSION
        |--------------------------------------------------------------------------
        */

        $extension =
            strtolower(
                trim(
                    (string) $file->getClientOriginalExtension()
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
                'Format file tidak didukung. ' .
                'Gunakan PDF, JPG, JPEG, atau PNG.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PDF
        |--------------------------------------------------------------------------
        */

        if ($extension === 'pdf') {
            $contents =
                file_get_contents(
                    $file->getRealPath()
                );

            if (
                $contents === false
                || $contents === ''
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
        */

        $contents =
            file_get_contents(
                $file->getRealPath()
            );

        if (
            $contents === false
            || $contents === ''
        ) {
            throw new RuntimeException(
                'Gagal membaca file gambar.'
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

    /**
     * Menyimpan hasil scan kamera.
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

        /*
        |--------------------------------------------------------------------------
        | VALIDASI HEADER BASE64
        |--------------------------------------------------------------------------
        */

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

        if ($commaPosition === false) {
            throw new RuntimeException(
                'Data gambar kamera tidak valid.'
            );
        }

        $encoded =
            substr(
                $base64String,
                $commaPosition + 1
            );

        /*
        |--------------------------------------------------------------------------
        | DECODE
        |--------------------------------------------------------------------------
        */

        $decodedData =
            base64_decode(
                $encoded,
                true
            );

        if (
            $decodedData === false
            || $decodedData === ''
        ) {
            throw new RuntimeException(
                'Gagal memproses gambar kamera. Data Base64 tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATE IMAGE
        |--------------------------------------------------------------------------
        */

        $imageInfo =
            @getimagesizefromstring(
                $decodedData
            );

        if ($imageInfo === false) {
            throw new RuntimeException(
                'Data kamera bukan gambar yang valid.'
            );
        }

        $actualMime =
            strtolower(
                (string) (
                    $imageInfo['mime']
                    ?? ''
                )
            );

        if ($actualMime === 'image/jpg') {
            $actualMime =
                'image/jpeg';
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
            throw new RuntimeException(
                'Jenis gambar hasil scan tidak didukung.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | COMPRESSION
        |--------------------------------------------------------------------------
        */

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

    /**
     * Compression gambar menggunakan GD.
     *
     * Hasil akhir dikonversi menjadi JPG.
     */
    private function storeCompressedImage(
        string $contents,
        string $diskName,
        string $prefix
    ): string {
        /*
        |--------------------------------------------------------------------------
        | CHECK GD
        |--------------------------------------------------------------------------
        */

        if (
            !function_exists(
                'imagecreatefromstring'
            )
            || !function_exists(
                'imagejpeg'
            )
        ) {
            throw new RuntimeException(
                'PHP GD belum tersedia. Aktifkan ekstensi GD pada server.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | IMAGE INFO
        |--------------------------------------------------------------------------
        */

        $imageInfo =
            @getimagesizefromstring(
                $contents
            );

        if ($imageInfo === false) {
            throw new RuntimeException(
                'File bukan gambar yang valid.'
            );
        }

        $mime =
            strtolower(
                (string) (
                    $imageInfo['mime']
                    ?? ''
                )
            );

        if ($mime === 'image/jpg') {
            $mime =
                'image/jpeg';
        }

        if (
            !in_array(
                $mime,
                [
                    'image/jpeg',
                    'image/png',
                ],
                true
            )
        ) {
            throw new RuntimeException(
                'Format gambar tidak didukung.'
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

        if ($source === false) {
            throw new RuntimeException(
                'Gagal membaca gambar menggunakan GD.'
            );
        }

        $sourceWidth =
            imagesx($source);

        $sourceHeight =
            imagesy($source);

        if (
            $sourceWidth <= 0
            || $sourceHeight <= 0
        ) {
            imagedestroy(
                $source
            );

            throw new RuntimeException(
                'Dimensi gambar tidak valid.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SCALE
        |--------------------------------------------------------------------------
        */

        $scale = min(
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
                    $sourceWidth * $scale
                )
            );

        $newHeight =
            max(
                1,
                (int) round(
                    $sourceHeight * $scale
                )
            );

        /*
        |--------------------------------------------------------------------------
        | CREATE CANVAS
        |--------------------------------------------------------------------------
        */

        $canvas =
            imagecreatetruecolor(
                $newWidth,
                $newHeight
            );

        if ($canvas === false) {
            imagedestroy(
                $source
            );

            throw new RuntimeException(
                'Gagal membuat canvas gambar.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | WHITE BACKGROUND
        |--------------------------------------------------------------------------
        |
        | Untuk PNG transparan, background dibuat putih
        | karena hasil akhir dikonversi menjadi JPEG.
        |
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

        imagecopyresampled(
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
        );

        imagedestroy(
            $source
        );

        /*
        |--------------------------------------------------------------------------
        | COMPRESSION LOOP
        |--------------------------------------------------------------------------
        */

        $qualities = [
            self::JPEG_QUALITY,
            72,
            62,
            52,
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
                !$success
                || $output === false
                || $output === ''
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
                )
                <= self::MAX_COMPRESSED_IMAGE_SIZE
            ) {
                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | RESIZE KEDUA
        |--------------------------------------------------------------------------
        */

        if (
            $compressedData === null
            || strlen(
                $compressedData
            )
            > self::MAX_COMPRESSED_IMAGE_SIZE
        ) {
            $ratio =
                0.75;

            $smallerWidth =
                max(
                    1,
                    (int) floor(
                        $newWidth * $ratio
                    )
                );

            $smallerHeight =
                max(
                    1,
                    (int) floor(
                        $newHeight * $ratio
                    )
                );

            $smallerCanvas =
                imagecreatetruecolor(
                    $smallerWidth,
                    $smallerHeight
                );

            if ($smallerCanvas === false) {
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

            imagecopyresampled(
                $smallerCanvas,
                $canvas,
                0,
                0,
                0,
                0,
                $smallerWidth,
                $smallerHeight,
                $newWidth,
                $newHeight
            );

            imagedestroy(
                $canvas
            );

            ob_start();

            $success =
                imagejpeg(
                    $smallerCanvas,
                    null,
                    50
                );

            $compressedData =
                ob_get_clean();

            imagedestroy(
                $smallerCanvas
            );

            if (
                !$success
                || $compressedData === false
                || $compressedData === ''
            ) {
                throw new RuntimeException(
                    'Gagal melakukan compression lanjutan gambar.'
                );
            }
        } else {
            imagedestroy(
                $canvas
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL CHECK
        |--------------------------------------------------------------------------
        */

        if (
            $compressedData === false
            || $compressedData === ''
        ) {
            throw new RuntimeException(
                'Hasil compression gambar kosong.'
            );
        }

        if (
            strlen(
                $compressedData
            )
            > self::MAX_FILE_SIZE
        ) {
            throw new RuntimeException(
                'Gambar masih melebihi batas 10 MB setelah compression.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | STORE
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

    /**
     * Menyimpan binary data ke storage.
     */
    private function storeBinaryFile(
        string $contents,
        string $extension,
        string $mimeType,
        string $prefix,
        string $diskName
    ): string {
        if ($contents === '') {
            throw new RuntimeException(
                'Data file kosong.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILE NAME
        |--------------------------------------------------------------------------
        */

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
            $extension;

        /*
        |--------------------------------------------------------------------------
        | DIRECTORY
        |--------------------------------------------------------------------------
        */

        $directory =
            'lampiran/surat_masuk';

        $path =
            $directory .
            '/' .
            $fileName;

        /*
        |--------------------------------------------------------------------------
        | OPTIONS
        |--------------------------------------------------------------------------
        */

        $options = [
            'ContentType' =>
                $mimeType,
        ];

        if (
            $diskName === 'supabase'
        ) {
            $options[
                'visibility'
            ] = 'private';
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

        try {
            $disk =
                Storage::disk(
                    $diskName
                );

            $saved =
                $disk->put(
                    $path,
                    $contents,
                    $options
                );

            if (!$saved) {
                throw new RuntimeException(
                    'File gagal disimpan ke storage.'
                );
            }

            return $path;
        } catch (Throwable $e) {
            Log::error(
                'Gagal menyimpan binary Surat Masuk.',
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
                        $diskName,
                ]
            );

            throw new RuntimeException(
                'Gagal menyimpan file ke storage: ' .
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

    /**
     * Membuat URL file.
     */
    private function getFileUrl(
        ?string $file
    ): ?string {
        if (!$file) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | EXTERNAL URL
        |--------------------------------------------------------------------------
        */

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

            if (!$disk->exists($file)) {
                return null;
            }

            /*
            |--------------------------------------------------------------------------
            | SUPABASE
            |--------------------------------------------------------------------------
            */

            if (
                $diskName === 'supabase'
            ) {
                if (
                    !method_exists(
                        $disk,
                        'temporaryUrl'
                    )
                ) {
                    return null;
                }

                return $disk->temporaryUrl(
                    $file,
                    now()->addMinutes(30)
                );
            }

            /*
            |--------------------------------------------------------------------------
            | LOCAL
            |--------------------------------------------------------------------------
            */

            if (
                method_exists(
                    $disk,
                    'url'
                )
            ) {
                return $disk->url(
                    $file
                );
            }

            return null;
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
    | DELETE STORAGE
    |--------------------------------------------------------------------------
    */

    /**
     * Menghapus file dari storage.
     */
    private function deleteStorageFile(
        ?string $file,
        ?string $diskName = null
    ): void {
        if (
            !$file
            || filter_var(
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

            if (
                $disk->exists(
                    $file
                )
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

    /**
     * Mendapatkan MIME type dari extension.
     */
    private function getMimeTypeFromPath(
        string $file
    ): string {
        $extension =
            strtolower(
                pathinfo(
                    $file,
                    PATHINFO_EXTENSION
                )
            );

        return match (
            $extension
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

    /**
     * Validasi tanggal format YYYY-MM-DD.
     */
    private function isValidDate(
        ?string $date
    ): bool {
        if ($date === null) {
            return false;
        }

        $date =
            trim($date);

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

    /**
     * Mencatat activity log.
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
                )
                && method_exists(
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
}