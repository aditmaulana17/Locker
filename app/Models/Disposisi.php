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

    protected $table = 'disposisis';

    protected $fillable = [
        'surat_masuk_id',
        'dari_user_id',
        'kepada_user_id',
        'instruksi',
        'isi_disposisi',
        'catatan',
        'batas_waktu',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'batas_waktu' => 'date',
        ];
    }

    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(
            SuratMasuk::class,
            'surat_masuk_id'
        );
    }

    public function dari(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'dari_user_id'
        );
    }

    public function kepada(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'kepada_user_id'
        );
    }

    public function scopeFilter(
        Builder $query,
        array $filters = []
    ): Builder {
        $search = isset($filters['search'])
            ? trim((string) $filters['search'])
            : '';

        if ($search !== '') {
            $keyword = "%{$search}%";

            $query->where(function (Builder $q) use ($keyword) {
                $q->where('instruksi', 'like', $keyword)
                    ->orWhere('isi_disposisi', 'like', $keyword)
                    ->orWhere('catatan', 'like', $keyword)
                    ->orWhereHas('suratMasuk', function (Builder $surat) use ($keyword) {
                        $surat
                            ->where('nomor_surat', 'like', $keyword)
                            ->orWhere('nomor_agenda', 'like', $keyword)
                            ->orWhere('pengirim', 'like', $keyword)
                            ->orWhere('perihal', 'like', $keyword);
                    })
                    ->orWhereHas('kepada', function (Builder $user) use ($keyword) {
                        $user
                            ->where('name', 'like', $keyword)
                            ->orWhere('nama', 'like', $keyword)
                            ->orWhere('jabatan', 'like', $keyword);
                    });
            });
        }

        $allowedStatuses = [
            'menunggu',
            'diproses',
            'selesai',
        ];

        $statuses = collect($filters['status'] ?? [])
            ->flatten()
            ->filter(fn ($status) => is_scalar($status))
            ->map(fn ($status) => strtolower(trim((string) $status)))
            ->filter(fn ($status) => in_array($status, $allowedStatuses, true))
            ->unique()
            ->values()
            ->all();

        if (!empty($statuses)) {
            $query->whereIn(
                'status',
                $statuses
            );
        }

        $dateColumn = self::getFilterDateColumn();

        $dariTanggal = $filters['dari_tanggal'] ?? null;
        $sampaiTanggal = $filters['sampai_tanggal'] ?? null;

        if ($this->validDate($dariTanggal)) {
            $query->whereDate(
                $dateColumn,
                '>=',
                $dariTanggal
            );
        }

        if ($this->validDate($sampaiTanggal)) {
            $query->whereDate(
                $dateColumn,
                '<=',
                $sampaiTanggal
            );
        }

        return $query;
    }

    public static function getFilterDateColumn(): string
    {
        return Schema::hasColumn(
            'disposisis',
            'tanggal_disposisi'
        )
            ? 'tanggal_disposisi'
            : 'created_at';
    }

    public function scopeBelumSelesai(
        Builder $query
    ): Builder {
        return $query->whereRaw(
            'LOWER(TRIM(status)) != ?',
            ['selesai']
        );
    }

    public function scopeSelesai(
        Builder $query
    ): Builder {
        return $query->whereRaw(
            'LOWER(TRIM(status)) = ?',
            ['selesai']
        );
    }

    public function scopeUntukUser(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where(
            'kepada_user_id',
            $userId
        );
    }

    public function scopeDibuatOleh(
        Builder $query,
        int $userId
    ): Builder {
        return $query->where(
            'dari_user_id',
            $userId
        );
    }

    public function scopeStatus(
        Builder $query,
        ?string $status
    ): Builder {
        if (!$status) {
            return $query;
        }

        $status = strtolower(
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

        return $query->where(
            'status',
            $status
        );
    }

    public function isSelesai(): bool
    {
        return strtolower(
            trim((string) $this->status)
        ) === 'selesai';
    }

    public function isAktif(): bool
    {
        return in_array(
            strtolower(
                trim((string) $this->status)
            ),
            [
                'menunggu',
                'diproses',
            ],
            true
        );
    }

    public function memilikiBatasWaktu(): bool
    {
        return $this->batas_waktu !== null;
    }

    public function sudahLewatBatasWaktu(): bool
    {
        if (!$this->batas_waktu) {
            return false;
        }

        return !$this->isSelesai()
            && $this->batas_waktu->isPast();
    }

    private function validDate(
        mixed $value
    ): bool {
        if (!$value) {
            return false;
        }

        $value = trim((string) $value);

        if (!preg_match(
            '/^\d{4}-\d{2}-\d{2}$/',
            $value
        )) {
            return false;
        }

        [$year, $month, $day] = array_map(
            'intval',
            explode('-', $value)
        );

        return checkdate(
            $month,
            $day,
            $year
        );
    }
}