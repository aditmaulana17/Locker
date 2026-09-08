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
            strtoupper(trim($kodeKategori)),
            $bulan,
            $tahun
        );
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $search = isset($filters['search'])
            ? trim((string) $filters['search'])
            : '';

        $kategoriIds = collect($filters['kategori_id'] ?? [])
            ->filter(fn ($id) => is_scalar($id) && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $statuses = collect($filters['status'] ?? [])
            ->filter(fn ($status) => is_scalar($status) && $status !== '')
            ->map(fn ($status) => strtolower(trim((string) $status)))
            ->filter(fn ($status) => in_array($status, [
                'draf',
                'diproses',
                'disetujui',
                'dikirim',
                'diarsipkan',
            ], true))
            ->unique()
            ->values()
            ->all();

        $pengirim = isset($filters['pengirim'])
            ? trim((string) $filters['pengirim'])
            : '';

        $dariTanggal = $filters['dari_tanggal'] ?? null;
        $sampaiTanggal = $filters['sampai_tanggal'] ?? null;

        $query
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('perihal', 'like', "%{$search}%")
                        ->orWhere('pengirim', 'like', "%{$search}%");
                });
            })
            ->when(!empty($kategoriIds), function (Builder $query) use ($kategoriIds) {
                $query->whereIn('kategori_surat_id', $kategoriIds);
            })
            ->when(!empty($statuses), function (Builder $query) use ($statuses) {
                $query->whereIn('status', $statuses);
            })
            ->when($pengirim !== '', function (Builder $query) use ($pengirim) {
                $query->where('pengirim', 'like', "%{$pengirim}%");
            })
            ->when($dariTanggal, function (Builder $query) use ($dariTanggal) {
                $query->whereDate('tanggal_surat', '>=', $dariTanggal);
            })
            ->when($sampaiTanggal, function (Builder $query) use ($sampaiTanggal) {
                $query->whereDate('tanggal_surat', '<=', $sampaiTanggal);
            });

        return $query;
    }
}