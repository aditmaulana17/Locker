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

    /**
     * Nama tabel database.
     */
    protected $table = 'disposisis';

    /**
     * Kolom yang boleh diisi melalui mass assignment.
     *
     * Sesuai dengan struktur tabel production:
     *
     * id
     * surat_masuk_id
     * dari_user_id
     * kepada_user_id
     * instruksi
     * isi_disposisi
     * catatan
     * batas_waktu
     * status
     * created_at
     * updated_at
     * deleted_at
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
     * Relasi ke user yang membuat/mengirim disposisi.
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
     * Scope filter data disposisi.
     *
     * Contoh:
     *
     * Disposisi::filter([
     *     'search' => 'surat',
     *     'status' => 'menunggu',
     *     'dari_tanggal' => '2026-01-01',
     *     'sampai_tanggal' => '2026-12-31',
     * ])->get();
     */
    public function scopeFilter(
        Builder $query,
        array $filters = []
    ): Builder {
        /*
         * ==============================
         * PENCARIAN
         * ==============================
         */
        if (!empty($filters['search'])) {
            $search = trim(
                (string) $filters['search']
            );

            $query->where(function (Builder $q) use ($search) {
                $keyword = "%{$search}%";

                /*
                 * Cari berdasarkan instruksi.
                 */
                $q->where(
                    'instruksi',
                    'like',
                    $keyword
                )

                /*
                 * Cari berdasarkan isi_disposisi.
                 */
                ->orWhere(
                    'isi_disposisi',
                    'like',
                    $keyword
                )

                /*
                 * Cari berdasarkan catatan.
                 */
                ->orWhere(
                    'catatan',
                    'like',
                    $keyword
                )

                /*
                 * Cari berdasarkan data surat masuk.
                 */
                ->orWhereHas(
                    'suratMasuk',
                    function (Builder $surat) use ($keyword) {
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
                                'perihal',
                                'like',
                                $keyword
                            );
                    }
                )

                /*
                 * Cari berdasarkan penerima.
                 */
                ->orWhereHas(
                    'kepada',
                    function (Builder $user) use ($keyword) {
                        $user
                            ->where(
                                'name',
                                'like',
                                $keyword
                            )
                            ->orWhere(
                                'nama',
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
            });
        }

        /*
         * ==============================
         * FILTER STATUS
         * ==============================
         */
        if (!empty($filters['status'])) {
            $statuses = is_array($filters['status'])
                ? $filters['status']
                : [$filters['status']];

            $statuses = array_values(
                array_filter(
                    array_map(
                        static function ($status) {
                            return strtolower(
                                trim((string) $status)
                            );
                        },
                        $statuses
                    )
                )
            );

            if (!empty($statuses)) {
                $query->whereIn(
                    'status',
                    $statuses
                );
            }
        }

        /*
         * ==============================
         * FILTER TANGGAL MULAI
         * ==============================
         */
        if (!empty($filters['dari_tanggal'])) {
            $query->whereDate(
                'batas_waktu',
                '>=',
                $filters['dari_tanggal']
            );
        }

        /*
         * ==============================
         * FILTER TANGGAL SELESAI
         * ==============================
         */
        if (!empty($filters['sampai_tanggal'])) {
            $query->whereDate(
                'batas_waktu',
                '<=',
                $filters['sampai_tanggal']
            );
        }

        return $query;
    }

    /**
     * Scope untuk disposisi yang belum selesai.
     */
    public function scopeBelumSelesai(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            '!=',
            'selesai'
        );
    }

    /**
     * Scope untuk disposisi yang sudah selesai.
     */
    public function scopeSelesai(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            'selesai'
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
     * Scope berdasarkan user pengirim.
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
     * Mengecek apakah disposisi sudah selesai.
     */
    public function isSelesai(): bool
    {
        return $this->status === 'selesai';
    }

    /**
     * Mengecek apakah disposisi masih aktif.
     */
    public function isAktif(): bool
    {
        return in_array(
            $this->status,
            [
                'menunggu',
                'diproses',
            ],
            true
        );
    }

    /**
     * Mengecek apakah mempunyai batas waktu.
     */
    public function memilikiBatasWaktu(): bool
    {
        return $this->batas_waktu !== null;
    }

    /**
     * Mengecek apakah batas waktu sudah lewat.
     */
    public function sudahLewatBatasWaktu(): bool
    {
        if (!$this->batas_waktu) {
            return false;
        }

        return !$this->isSelesai()
            && $this->batas_waktu->isPast();
    }
}