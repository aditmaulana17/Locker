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

    protected $table = 'surat_masuks';

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

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_terima' => 'date',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSurat::class, 'kategori_surat_id');
    }

    public function penerima(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diterima_oleh');
    }

    public function disposisi(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'surat_masuk_id');
    }

    public static function generateNomorAgenda(): string
    {
        $bulan = now()->format('m');
        $tahun = now()->format('Y');

        $records = self::withTrashed()
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->pluck('nomor_agenda');

        $maxUrutan = 0;

        foreach ($records as $nomor) {
            if (preg_match('/^AG\/(\d+)\/\d{2}\/\d{4}$/', (string) $nomor, $matches)) {
                $maxUrutan = max($maxUrutan, (int) $matches[1]);
            }
        }

        return sprintf(
            'AG/%04d/%s/%s',
            $maxUrutan + 1,
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
            ->filter(fn ($id) => is_scalar($id) && ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $statusOptions = [
            'baru',
            'diproses',
            'didisposisikan',
            'selesai',
            'diarsipkan',
        ];

        $statuses = collect($filters['status'] ?? [])
            ->filter(fn ($status) => is_scalar($status))
            ->map(fn ($status) => strtolower(trim((string) $status)))
            ->filter(fn ($status) => in_array($status, $statusOptions, true))
            ->unique()
            ->values()
            ->all();

        $dariTanggal = $filters['dari_tanggal'] ?? null;
        $sampaiTanggal = $filters['sampai_tanggal'] ?? null;

        $query
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('perihal', 'like', "%{$search}%")
                        ->orWhere('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('nomor_agenda', 'like', "%{$search}%")
                        ->orWhere('pengirim', 'like', "%{$search}%");
                });
            })
            ->when(!empty($kategoriIds), function (Builder $query) use ($kategoriIds) {
                $query->whereIn('kategori_surat_id', $kategoriIds);
            })
            ->when(!empty($statuses), function (Builder $query) use ($statuses) {
                $query->whereIn('status', $statuses);
            })
            ->when($dariTanggal, function (Builder $query) use ($dariTanggal) {
                $query->whereDate('tanggal_terima', '>=', $dariTanggal);
            })
            ->when($sampaiTanggal, function (Builder $query) use ($sampaiTanggal) {
                $query->whereDate('tanggal_terima', '<=', $sampaiTanggal);
            });

        return $query;
    }

    public function scopeUntukStaff(Builder $query, int $userId): Builder
    {
        return $query->whereHas('disposisi', function (Builder $query) use ($userId) {
            $query->where('kepada_user_id', $userId);
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (!$status) {
            return $query;
        }

        return $query->where(
            'status',
            strtolower(trim($status))
        );
    }

    public function scopeKategori(Builder $query, ?int $kategoriId): Builder
    {
        if (!$kategoriId) {
            return $query;
        }

        return $query->where(
            'kategori_surat_id',
            $kategoriId
        );
    }

    public function scopeTerbaru(Builder $query): Builder
    {
        return $query
            ->latest('tanggal_terima')
            ->latest('id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match (strtolower((string) $this->status)) {
            'baru' => 'Baru',
            'diproses' => 'Diproses',
            'didisposisikan' => 'Didisposisikan',
            'selesai' => 'Selesai',
            'diarsipkan' => 'Diarsipkan',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusClassAttribute(): string
    {
        return match (strtolower((string) $this->status)) {
            'baru' => 'bg-blue-100 text-blue-700',
            'diproses' => 'bg-yellow-100 text-yellow-700',
            'didisposisikan' => 'bg-purple-100 text-purple-700',
            'selesai' => 'bg-green-100 text-green-700',
            'diarsipkan' => 'bg-gray-100 text-gray-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function hasLampiran(): bool
    {
        return !empty($this->lampiran_file);
    }

    public function hasDisposisi(): bool
    {
        return $this->disposisi()->exists();
    }
}