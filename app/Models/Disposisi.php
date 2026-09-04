<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disposisi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'disposisis';

    protected $fillable = [
        'surat_masuk_id',
        'dari_user_id',
        'kepada_user_id',
        'isi_disposisi',
        'catatan',
        'sifat',
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
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id');
    }

    public function dari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    public function kepada(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepada_user_id');
    }

    public function scopeFilter(Builder $query, array $filters = []): Builder
    {
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $q) use ($search) {
                $q->where('isi_disposisi', 'like', "%{$search}%")
                    ->orWhere('catatan', 'like', "%{$search}%")
                    ->orWhereHas('suratMasuk', function (Builder $surat) use ($search) {
                        $surat->where('nomor_surat', 'like', "%{$search}%")
                            ->orWhere('nomor_agenda', 'like', "%{$search}%")
                            ->orWhere('perihal', 'like', "%{$search}%");
                    })
                    ->orWhereHas('kepada', function (Builder $user) use ($search) {
                        $user->where('name', 'like', "%{$search}%")
                            ->orWhere('jabatan', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['status'])) {
            $statuses = is_array($filters['status'])
                ? $filters['status']
                : [$filters['status']];

            $statuses = array_values(array_filter(array_map(
                static fn ($status) => strtolower(trim((string) $status)),
                $statuses
            )));

            if (!empty($statuses)) {
                $query->whereIn('status', $statuses);
            }
        }

        if (!empty($filters['dari_tanggal'])) {
            $query->whereDate('batas_waktu', '>=', $filters['dari_tanggal']);
        }

        if (!empty($filters['sampai_tanggal'])) {
            $query->whereDate('batas_waktu', '<=', $filters['sampai_tanggal']);
        }

        return $query;
    }

    public function scopeBelumSelesai(Builder $query): Builder
    {
        return $query->where('status', '!=', 'selesai');
    }

    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('status', 'selesai');
    }

    public function scopeUntukUser(Builder $query, int $userId): Builder
    {
        return $query->where('kepada_user_id', $userId);
    }

    public function scopeDibuatOleh(Builder $query, int $userId): Builder
    {
        return $query->where('dari_user_id', $userId);
    }

    public function isSelesai(): bool
    {
        return $this->status === 'selesai';
    }

    public function isAktif(): bool
    {
        return in_array($this->status, ['menunggu', 'diproses'], true);
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

        return !$this->isSelesai() && $this->batas_waktu->isPast();
    }
}