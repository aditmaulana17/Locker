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

    protected $table = 'surat_keluars';

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

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_keluar' => 'date',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSurat::class, 'kategori_surat_id');
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function penandatangan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditandatangani_oleh');
    }

    public static function generateNomorSurat(string $kodeKategori): string
    {
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
            strtoupper($kodeKategori),
            $bulan,
            $tahun
        );
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $query->when($filters['search'] ?? null, function (Builder $query, $value) {
            $search = trim((string) $value);

            $query->where(function (Builder $query) use ($search) {
                $query->where('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('perihal', 'like', "%{$search}%")
                    ->orWhere('pengirim', 'like', "%{$search}%");
            });
        });

        if (!empty($filters['kategori_id'])) {
            $query->whereIn(
                'kategori_surat_id',
                (array) $filters['kategori_id']
            );
        }

        if (!empty($filters['status'])) {
            $query->whereIn(
                'status',
                (array) $filters['status']
            );
        }

        if (!empty($filters['pengirim'])) {
            $query->where(
                'pengirim',
                'like',
                '%' . $filters['pengirim'] . '%'
            );
        }

        if (!empty($filters['dari_tanggal'])) {
            $query->whereDate(
                'tanggal_surat',
                '>=',
                $filters['dari_tanggal']
            );
        }

        if (!empty($filters['sampai_tanggal'])) {
            $query->whereDate(
                'tanggal_surat',
                '<=',
                $filters['sampai_tanggal']
            );
        }

        return $query;
    }
}