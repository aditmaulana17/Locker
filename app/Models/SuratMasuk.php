<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratMasuk extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nama tabel.
     */
    protected $table = 'surat_masuks';

    /**
     * Kolom yang dapat diisi melalui mass assignment.
     */
    protected $fillable = [
        'nomor_agenda',
        'nomor_surat',
        'pengirim',
        'tanggal_surat',
        'tanggal_terima',
        'kategori_surat_id',
        'perihal',
        'ringkasan',
        'lampiran_file',
        'status',
        'lokasi_arsip_fisik',
        'diterima_oleh',
    ];

    /**
     * Casting atribut.
     */
    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_terima' => 'date',
        ];
    }

    /**
     * Relasi ke kategori surat.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(
            KategoriSurat::class,
            'kategori_surat_id'
        );
    }

    /**
     * Relasi ke user yang menerima/mencatat surat.
     */
    public function penerima(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'diterima_oleh'
        );
    }

    /**
     * Relasi ke disposisi.
     */
    public function disposisi(): HasMany
    {
        return $this->hasMany(
            Disposisi::class,
            'surat_masuk_id'
        );
    }

    /**
     * Generate nomor agenda otomatis.
     *
     * Format:
     * AG/0001/09/2026
     */
    public static function generateNomorAgenda(): string
    {
        $bulan = now()->format('m');
        $tahun = now()->format('Y');

        $records = self::withTrashed()
            ->whereYear(
                'created_at',
                $tahun
            )
            ->whereMonth(
                'created_at',
                $bulan
            )
            ->pluck('nomor_agenda');

        $maxUrutan = 0;

        foreach ($records as $nomor) {
            if (
                preg_match(
                    '/^AG\/(\d+)\/\d{2}\/\d{4}$/',
                    (string) $nomor,
                    $matches
                )
            ) {
                $maxUrutan = max(
                    $maxUrutan,
                    (int) $matches[1]
                );
            }
        }

        return sprintf(
            'AG/%04d/%s/%s',
            $maxUrutan + 1,
            $bulan,
            $tahun
        );
    }

    /**
     * Scope filter surat masuk.
     *
     * Mendukung:
     * - pencarian
     * - kategori
     * - status
     * - tanggal diterima
     */
    public function scopeFilter(
        Builder $query,
        array $filters = []
    ): Builder {
        /*
         * =========================================================
         * SEARCH
         * =========================================================
         */
        $search = isset($filters['search'])
            ? trim(
                (string) $filters['search']
            )
            : '';

        if ($search !== '') {
            $keyword =
                "%{$search}%";

            $query->where(
                function (
                    Builder $q
                ) use ($keyword): void {

                    $q->where(
                        'perihal',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'nomor_surat',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'nomor_agenda',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'pengirim',
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
                    )
                    ->orWhereHas(
                        'penerima',
                        function (
                            Builder $user
                        ) use ($keyword): void {
                            $user
                                ->where(
                                    'name',
                                    'like',
                                    $keyword
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    $keyword
                                )
                                ->orWhere(
                                    'jabatan',
                                    'like',
                                    $keyword
                                );
                        }
                    );
                }
            );
        }

        /*
         * =========================================================
         * FILTER KATEGORI
         * =========================================================
         *
         * Mendukung:
         * kategori_surat_id
         * kategori_id sebagai parameter lama.
         */
        $rawKategoriIds =
            $filters['kategori_surat_id']
            ?? $filters['kategori_id']
            ?? [];

        if (
            is_scalar($rawKategoriIds) &&
            trim(
                (string) $rawKategoriIds
            ) !== ''
        ) {
            $rawKategoriIds = [
                $rawKategoriIds,
            ];
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

        if (!empty($kategoriIds)) {
            $query->whereIn(
                'kategori_surat_id',
                $kategoriIds
            );
        }

        /*
         * =========================================================
         * FILTER STATUS
         * =========================================================
         */
        $allowedStatuses = [
            'baru',
            'diproses',
            'didisposisikan',
            'selesai',
            'diarsipkan',
        ];

        $rawStatuses =
            $filters['status'] ?? [];

        if (
            is_scalar($rawStatuses) &&
            trim(
                (string) $rawStatuses
            ) !== ''
        ) {
            $rawStatuses = [
                $rawStatuses,
            ];
        }

        $statuses = collect(
            $rawStatuses
        )
            ->flatten()
            ->filter(
                fn ($status) =>
                    is_scalar($status)
            )
            ->map(
                fn ($status) =>
                    strtolower(
                        trim((string) $status)
                    )
            )
            ->filter(
                fn ($status) =>
                    in_array(
                        $status,
                        $allowedStatuses,
                        true
                    )
            )
            ->unique()
            ->values()
            ->all();

        if (!empty($statuses)) {
            $query->whereIn(
                'status',
                $statuses
            );
        }

        /*
         * =========================================================
         * FILTER TANGGAL
         * =========================================================
         */
        $dariTanggal =
            $filters['dari_tanggal'] ?? null;

        $sampaiTanggal =
            $filters['sampai_tanggal'] ?? null;

        $dariTanggal =
            is_scalar($dariTanggal)
                ? trim(
                    (string) $dariTanggal
                )
                : '';

        $sampaiTanggal =
            is_scalar($sampaiTanggal)
                ? trim(
                    (string) $sampaiTanggal
                )
                : '';

        $validDariTanggal =
            self::validDate(
                $dariTanggal
            );

        $validSampaiTanggal =
            self::validDate(
                $sampaiTanggal
            );

        /*
         * Jika tanggal awal lebih besar,
         * otomatis ditukar.
         */
        if (
            $validDariTanggal &&
            $validSampaiTanggal &&
            $dariTanggal > $sampaiTanggal
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

        return $query;
    }

    /**
     * Scope surat masuk untuk staff tertentu.
     *
     * Staff hanya melihat surat yang memiliki
     * disposisi kepada dirinya.
     */
    public function scopeUntukStaff(
        Builder $query,
        int $userId
    ): Builder {
        return $query->whereHas(
            'disposisi',
            function (
                Builder $disposisi
            ) use ($userId): void {
                $disposisi->where(
                    'kepada_user_id',
                    $userId
                );
            }
        );
    }

    /**
     * Scope berdasarkan status.
     */
    public function scopeStatus(
        Builder $query,
        ?string $status
    ): Builder {
        if (
            $status === null ||
            trim($status) === ''
        ) {
            return $query;
        }

        $status =
            strtolower(
                trim($status)
            );

        if (!in_array(
            $status,
            [
                'baru',
                'diproses',
                'didisposisikan',
                'selesai',
                'diarsipkan',
            ],
            true
        )) {
            return $query;
        }

        return $query->whereRaw(
            'LOWER(TRIM(status)) = ?',
            [$status]
        );
    }

    /**
     * Scope berdasarkan kategori.
     */
    public function scopeKategori(
        Builder $query,
        ?int $kategoriId
    ): Builder {
        if (
            $kategoriId === null ||
            $kategoriId <= 0
        ) {
            return $query;
        }

        return $query->where(
            'kategori_surat_id',
            $kategoriId
        );
    }

    /**
     * Scope surat terbaru.
     */
    public function scopeTerbaru(
        Builder $query
    ): Builder {
        return $query
            ->latest('tanggal_terima')
            ->latest('id');
    }

    /**
     * Scope surat yang belum selesai.
     */
    public function scopeBelumSelesai(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'status',
            [
                'baru',
                'diproses',
                'didisposisikan',
            ]
        );
    }

    /**
     * Scope surat selesai atau sudah diarsipkan.
     */
    public function scopeSelesai(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'status',
            [
                'selesai',
                'diarsipkan',
            ]
        );
    }

    /**
     * Label status untuk tampilan.
     */
    public function getStatusLabelAttribute(): string
    {
        $status =
            strtolower(
                trim(
                    (string) $this->status
                )
            );

        return match ($status) {
            'baru' =>
                'Baru',

            'diproses' =>
                'Diproses',

            'didisposisikan' =>
                'Didisposisikan',

            'selesai' =>
                'Selesai',

            'diarsipkan' =>
                'Diarsipkan',

            default =>
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $status
                    )
                ),
        };
    }

    /**
     * Class badge status untuk tampilan.
     */
    public function getStatusClassAttribute(): string
    {
        return match (
            strtolower(
                trim(
                    (string) $this->status
                )
            )
        ) {
            'baru' =>
                'bg-blue-100 text-blue-700',

            'diproses' =>
                'bg-yellow-100 text-yellow-700',

            'didisposisikan' =>
                'bg-purple-100 text-purple-700',

            'selesai' =>
                'bg-green-100 text-green-700',

            'diarsipkan' =>
                'bg-gray-100 text-gray-700',

            default =>
                'bg-gray-100 text-gray-700',
        };
    }

    /**
     * Status yang telah dinormalisasi.
     */
    public function getStatusNormalizedAttribute(): string
    {
        return strtolower(
            trim(
                (string) $this->status
            )
        );
    }

    /**
     * Mengecek apakah surat memiliki lampiran.
     */
    public function hasLampiran(): bool
    {
        return !empty(
            trim(
                (string) $this->lampiran_file
            )
        );
    }

    /**
     * Alias bahasa Indonesia.
     */
    public function memilikiLampiran(): bool
    {
        return $this->hasLampiran();
    }

    /**
     * Mengambil nama file lampiran.
     */
    public function getNamaLampiranAttribute(): ?string
    {
        if (!$this->hasLampiran()) {
            return null;
        }

        return basename(
            (string) $this->lampiran_file
        );
    }

    /**
     * Mengecek apakah surat memiliki disposisi.
     */
    public function hasDisposisi(): bool
    {
        return $this->disposisi()->exists();
    }

    /**
     * Alias bahasa Indonesia.
     */
    public function memilikiDisposisi(): bool
    {
        return $this->hasDisposisi();
    }

    /**
     * Mengecek apakah surat sudah selesai.
     */
    public function isSelesai(): bool
    {
        return in_array(
            strtolower(
                trim(
                    (string) $this->status
                )
            ),
            [
                'selesai',
                'diarsipkan',
            ],
            true
        );
    }

    /**
     * Mengecek apakah surat masih aktif.
     */
    public function isAktif(): bool
    {
        return in_array(
            strtolower(
                trim(
                    (string) $this->status
                )
            ),
            [
                'baru',
                'diproses',
                'didisposisikan',
            ],
            true
        );
    }

    /**
     * Validasi tanggal YYYY-MM-DD.
     */
    private static function validDate(
        mixed $value
    ): bool {
        if ($value === null) {
            return false;
        }

        $value =
            trim(
                (string) $value
            );

        if (!preg_match(
            '/^\d{4}-\d{2}-\d{2}$/',
            $value
        )) {
            return false;
        }

        [
            $year,
            $month,
            $day,
        ] = array_map(
            'intval',
            explode(
                '-',
                $value
            )
        );

        return checkdate(
            $month,
            $day,
            $year
        );
    }
}