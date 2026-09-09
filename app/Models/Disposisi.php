<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Disposisi extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nama tabel.
     */
    protected $table = 'disposisis';

    /**
     * Kolom yang boleh diisi melalui mass assignment.
     */
    protected $fillable = [
        'surat_masuk_id',
        'dari_user_id',
        'kepada_user_id',
        'instruksi',
        'isi_disposisi',
        'catatan',
        'batas_waktu',
        'status',
        'sifat',
    ];

    /**
     * Casting atribut.
     */
    protected function casts(): array
    {
        return [
            'batas_waktu' => 'date',
        ];
    }

    /**
     * Relasi ke Surat Masuk.
     */
    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(
            SuratMasuk::class,
            'surat_masuk_id'
        );
    }

    /**
     * Relasi ke user pembuat disposisi.
     */
    public function dari(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dari_user_id'
        );
    }

    /**
     * Relasi ke user penerima disposisi.
     */
    public function kepada(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'kepada_user_id'
        );
    }

    /**
     * Scope filter pencarian, status, dan tanggal.
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
            ? trim((string) $filters['search'])
            : '';

        if ($search !== '') {
            $keyword = "%{$search}%";

            $query->where(
                function (Builder $q) use ($keyword): void {
                    /*
                     * Field disposisi.
                     */
                    $q->where(
                        'instruksi',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'isi_disposisi',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'catatan',
                        'like',
                        $keyword
                    );

                    /*
                     * Field sifat jika tersedia.
                     */
                    if (
                        Schema::hasColumn(
                            'disposisis',
                            'sifat'
                        )
                    ) {
                        $q->orWhere(
                            'sifat',
                            'like',
                            $keyword
                        );
                    }

                    /*
                     * Search Surat Masuk.
                     */
                    $q->orWhereHas(
                        'suratMasuk',
                        function (
                            Builder $surat
                        ) use ($keyword): void {
                            $surat
                                ->where(
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
                                    'perihal',
                                    'like',
                                    $keyword
                                );
                        }
                    );

                    /*
                     * Search user penerima.
                     */
                    $q->orWhereHas(
                        'kepada',
                        function (
                            Builder $user
                        ) use ($keyword): void {
                            $user->where(
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
         * STATUS
         * =========================================================
         */
        $allowedStatuses = [
            'menunggu',
            'diproses',
            'selesai',
        ];

        $rawStatuses =
            $filters['status'] ?? [];

        if (
            is_scalar($rawStatuses) &&
            trim((string) $rawStatuses) !== ''
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
        $dateColumn =
            self::getFilterDateColumn();

        $dariTanggal =
            $filters['dari_tanggal'] ?? null;

        $sampaiTanggal =
            $filters['sampai_tanggal'] ?? null;

        $dariTanggal =
            is_scalar($dariTanggal)
                ? trim((string) $dariTanggal)
                : '';

        $sampaiTanggal =
            is_scalar($sampaiTanggal)
                ? trim((string) $sampaiTanggal)
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
         * tukarkan otomatis.
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
                $dateColumn,
                '>=',
                $dariTanggal
            );
        }

        if ($validSampaiTanggal) {
            $query->whereDate(
                $dateColumn,
                '<=',
                $sampaiTanggal
            );
        }

        return $query;
    }

    /**
     * Menentukan kolom tanggal untuk filter.
     *
     * Menggunakan tanggal_disposisi jika tersedia.
     * Jika tidak tersedia, menggunakan created_at.
     */
    public static function getFilterDateColumn(): string
    {
        return Schema::hasColumn(
            'disposisis',
            'tanggal_disposisi'
        )
            ? 'tanggal_disposisi'
            : 'created_at';
    }

    /**
     * Scope disposisi yang belum selesai.
     */
    public function scopeBelumSelesai(
        Builder $query
    ): Builder {
        return $query->where(
            function (Builder $q): void {
                $q->whereNull('status')
                    ->orWhereRaw(
                        'LOWER(TRIM(status)) != ?',
                        ['selesai']
                    );
            }
        );
    }

    /**
     * Scope disposisi yang selesai.
     */
    public function scopeSelesai(
        Builder $query
    ): Builder {
        return $query->whereRaw(
            'LOWER(TRIM(status)) = ?',
            ['selesai']
        );
    }

    /**
     * Scope berdasarkan user penerima.
     */
    public function scopeUntukUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where(
            'kepada_user_id',
            $userId
        );
    }

    /**
     * Scope berdasarkan user pembuat.
     */
    public function scopeDibuatOleh(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where(
            'dari_user_id',
            $userId
        );
    }

    /**
     * Scope berdasarkan satu status.
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
                'menunggu',
                'diproses',
                'selesai',
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
     * Mengecek apakah disposisi sudah selesai.
     */
    public function isSelesai(): bool
    {
        return strtolower(
            trim(
                (string) $this->status
            )
        ) === 'selesai';
    }

    /**
     * Mengecek apakah disposisi masih aktif.
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
                'menunggu',
                'diproses',
            ],
            true
        );
    }

    /**
     * Mengecek apakah status menunggu.
     */
    public function isMenunggu(): bool
    {
        return strtolower(
            trim(
                (string) $this->status
            )
        ) === 'menunggu';
    }

    /**
     * Mengecek apakah status sedang diproses.
     */
    public function isDiproses(): bool
    {
        return strtolower(
            trim(
                (string) $this->status
            )
        ) === 'diproses';
    }

    /**
     * Mengecek apakah memiliki batas waktu.
     */
    public function memilikiBatasWaktu(): bool
    {
        return $this->batas_waktu !== null;
    }

    /**
     * Mengecek apakah sudah melewati batas waktu.
     */
    public function sudahLewatBatasWaktu(): bool
    {
        if (!$this->batas_waktu) {
            return false;
        }

        return !$this->isSelesai()
            && $this->batas_waktu->isPast();
    }

    /**
     * Mengecek apakah batas waktu masih aktif.
     */
    public function batasWaktuAktif(): bool
    {
        return $this->memilikiBatasWaktu()
            && !$this->isSelesai();
    }

    /**
     * Mengambil status yang sudah dinormalisasi.
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
     * Mengambil instruksi utama.
     *
     * Field instruksi menjadi prioritas.
     * isi_disposisi digunakan sebagai fallback
     * untuk data lama.
     */
    public function getInstruksiUtamaAttribute(): string
    {
        $instruksi =
            trim(
                (string) (
                    $this->instruksi
                    ?? ''
                )
            );

        if ($instruksi !== '') {
            return $instruksi;
        }

        return trim(
            (string) (
                $this->isi_disposisi
                ?? ''
            )
        );
    }

    /**
     * Mengambil label status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match (
            $this->status_normalized
        ) {
            'menunggu' =>
                'Menunggu',

            'diproses' =>
                'Diproses',

            'selesai' =>
                'Selesai',

            default =>
                ucfirst(
                    $this->status_normalized
                ),
        };
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

        /*
         * Format harus:
         * 2026-09-09
         */
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