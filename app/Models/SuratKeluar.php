<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratKeluar extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | TABLE
    |--------------------------------------------------------------------------
    */

    protected $table = 'surat_keluars';

    /*
    |--------------------------------------------------------------------------
    | FILLABLE
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'nomor_surat',
        'tanggal_surat',
        'tanggal_keluar',
        'pengirim',
        'kategori_surat_id',
        'perihal',
        'ringkasan',
        'lampiran_file',
        'status',
        'dibuat_oleh',
        'ditandatangani_oleh',
    ];

    /*
    |--------------------------------------------------------------------------
    | CASTS
    |--------------------------------------------------------------------------
    */

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_keluar' => 'date',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

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
     * Relasi ke user pembuat surat.
     */
    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dibuat_oleh'
        );
    }

    /**
     * Relasi ke user penandatangan.
     */
    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'ditandatangani_oleh'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NOMOR SURAT
    |--------------------------------------------------------------------------
    */

    /**
     * Generate nomor surat otomatis.
     *
     * Format:
     * 001/KODE/I/2026
     *
     * Nomor urut dihitung berdasarkan tahun berjalan.
     */
    public static function generateNomorSurat(
        string $kodeKategori
    ): string {
        $romawi = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        $tahun = now()->year;
        $bulan = $romawi[now()->month];

        $urutan = self::withTrashed()
            ->whereYear('created_at', $tahun)
            ->count() + 1;

        return sprintf(
            '%03d/%s/%s/%d',
            $urutan,
            strtoupper(trim($kodeKategori)),
            $bulan,
            $tahun
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    */

    /**
     * Scope filter surat keluar.
     *
     * Mendukung:
     * - pencarian
     * - kategori
     * - status
     * - pengirim / tujuan
     * - tanggal keluar
     */
    public function scopeFilter(
        Builder $query,
        array $filters = []
    ): Builder {
        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        */

        $search = isset($filters['search'])
            ? trim((string) $filters['search'])
            : '';

        if ($search !== '') {
            $keyword = "%{$search}%";

            $query->where(
                function (Builder $q) use ($keyword): void {
                    $q->where(
                        'nomor_surat',
                        'like',
                        $keyword
                    )
                    ->orWhere(
                        'perihal',
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
                        function (Builder $kategori) use ($keyword): void {
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
                        'pembuat',
                        function (Builder $user) use ($keyword): void {
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
                    )
                    ->orWhereHas(
                        'penandatangan',
                        function (Builder $user) use ($keyword): void {
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
        |--------------------------------------------------------------------------
        | FILTER KATEGORI
        |--------------------------------------------------------------------------
        |
        | Mendukung:
        | - kategori_surat_id
        | - kategori_id
        |
        */

        $rawKategoriIds =
            $filters['kategori_surat_id']
            ?? $filters['kategori_id']
            ?? [];

        if (
            is_scalar($rawKategoriIds)
            && trim((string) $rawKategoriIds) !== ''
        ) {
            $rawKategoriIds = [
                $rawKategoriIds,
            ];
        }

        $kategoriIds = collect($rawKategoriIds)
            ->flatten()
            ->filter(
                fn ($id) =>
                    is_scalar($id)
                    && is_numeric($id)
                    && (int) $id > 0
            )
            ->map(
                fn ($id) => (int) $id
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
        |--------------------------------------------------------------------------
        | FILTER STATUS
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [
            'draft',
            'diproses',
            'disetujui',
            'dikirim',
            'diarsipkan',
        ];

        $rawStatuses =
            $filters['status']
            ?? [];

        if (
            is_scalar($rawStatuses)
            && trim((string) $rawStatuses) !== ''
        ) {
            $rawStatuses = [
                $rawStatuses,
            ];
        }

        $statuses = collect($rawStatuses)
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
            ->map(
                fn ($status) =>
                    $status === 'draf'
                        ? 'draft'
                        : $status
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
        |--------------------------------------------------------------------------
        | FILTER TUJUAN / PENGIRIM
        |--------------------------------------------------------------------------
        */

        $pengirim = isset($filters['pengirim'])
            ? trim((string) $filters['pengirim'])
            : '';

        if ($pengirim !== '') {
            $query->where(
                'pengirim',
                'like',
                "%{$pengirim}%"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER TANGGAL KELUAR
        |--------------------------------------------------------------------------
        |
        | Karena index Surat Keluar menggunakan tanggal_keluar,
        | filter juga harus menggunakan kolom ini.
        |
        */

        $dariTanggal =
            $filters['dari_tanggal']
            ?? null;

        $sampaiTanggal =
            $filters['sampai_tanggal']
            ?? null;

        $dariTanggal =
            is_scalar($dariTanggal)
                ? trim((string) $dariTanggal)
                : '';

        $sampaiTanggal =
            is_scalar($sampaiTanggal)
                ? trim((string) $sampaiTanggal)
                : '';

        $validDariTanggal =
            self::validDate($dariTanggal);

        $validSampaiTanggal =
            self::validDate($sampaiTanggal);

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI RENTANG
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | TANGGAL MULAI
        |--------------------------------------------------------------------------
        */

        if ($validDariTanggal) {
            $query->whereDate(
                'tanggal_keluar',
                '>=',
                $dariTanggal
            );
        }

        /*
        |--------------------------------------------------------------------------
        | TANGGAL AKHIR
        |--------------------------------------------------------------------------
        */

        if ($validSampaiTanggal) {
            $query->whereDate(
                'tanggal_keluar',
                '<=',
                $sampaiTanggal
            );
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS
    |--------------------------------------------------------------------------
    */

    /**
     * Scope berdasarkan status.
     */
    public function scopeStatus(
        Builder $query,
        ?string $status
    ): Builder {
        if (
            $status === null
            || trim($status) === ''
        ) {
            return $query;
        }

        $status = strtolower(
            trim($status)
        );

        if ($status === 'draf') {
            $status = 'draft';
        }

        $allowedStatuses = [
            'draft',
            'diproses',
            'disetujui',
            'dikirim',
            'diarsipkan',
        ];

        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            return $query;
        }

        return $query->whereRaw(
            'LOWER(TRIM(status)) = ?',
            [$status]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Scope surat yang masih aktif.
     */
    public function scopeAktif(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'status',
            [
                'draft',
                'diproses',
                'disetujui',
            ]
        );
    }

    /**
     * Scope surat yang sudah dikirim.
     */
    public function scopeSudahDikirim(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'status',
            [
                'dikirim',
                'diarsipkan',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS CHECK
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status_normalized === 'draft';
    }

    public function isDiproses(): bool
    {
        return $this->status_normalized === 'diproses';
    }

    public function isDisetujui(): bool
    {
        return $this->status_normalized === 'disetujui';
    }

    public function isDikirim(): bool
    {
        return $this->status_normalized === 'dikirim';
    }

    public function isDiarsipkan(): bool
    {
        return $this->status_normalized === 'diarsipkan';
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS ATTRIBUTE
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil status yang telah dinormalisasi.
     */
    public function getStatusNormalizedAttribute(): string
    {
        $status = strtolower(
            trim(
                (string) $this->status
            )
        );

        return $status === 'draf'
            ? 'draft'
            : $status;
    }

    /**
     * Label status.
     */
    public function getStatusLabelAttribute(): string
    {
        return match (
            $this->status_normalized
        ) {
            'draft' =>
                'Draft',

            'diproses' =>
                'Diproses',

            'disetujui' =>
                'Disetujui',

            'dikirim' =>
                'Dikirim',

            'diarsipkan' =>
                'Diarsipkan',

            default =>
                ucfirst(
                    str_replace(
                        '_',
                        ' ',
                        $this->status_normalized
                    )
                ),
        };
    }

    /**
     * Class badge status.
     */
    public function getStatusClassAttribute(): string
    {
        return match (
            $this->status_normalized
        ) {
            'draft' =>
                'bg-slate-100 text-slate-700',

            'diproses' =>
                'bg-amber-100 text-amber-700',

            'disetujui' =>
                'bg-blue-100 text-blue-700',

            'dikirim' =>
                'bg-emerald-100 text-emerald-700',

            'diarsipkan' =>
                'bg-purple-100 text-purple-700',

            default =>
                'bg-slate-100 text-slate-700',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | LAMPIRAN
    |--------------------------------------------------------------------------
    */

    /**
     * Mengecek apakah memiliki lampiran.
     */
    public function hasLampiran(): bool
    {
        return trim(
            (string) $this->lampiran_file
        ) !== '';
    }

    /**
     * Alias Bahasa Indonesia.
     */
    public function memilikiLampiran(): bool
    {
        return $this->hasLampiran();
    }

    /**
     * Nama file lampiran.
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

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    /**
     * Mengecek apakah surat secara status masih dapat diedit.
     *
     * Helper ini tidak otomatis membatasi controller.
     */
    public function isEditable(): bool
    {
        return in_array(
            $this->status_normalized,
            [
                'draft',
                'diproses',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDASI TANGGAL
    |--------------------------------------------------------------------------
    */

    /**
     * Validasi tanggal YYYY-MM-DD.
     */
    private static function validDate(
        mixed $value
    ): bool {
        if ($value === null) {
            return false;
        }

        $value = trim(
            (string) $value
        );

        if (
            !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $value
            )
        ) {
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