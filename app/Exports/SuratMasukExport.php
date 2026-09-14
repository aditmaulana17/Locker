<?php

namespace App\Exports;

use App\Models\SuratMasuk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;

class SuratMasukExport implements FromCollection
{
    /**
     * Filter yang dikirim dari controller.
     */
    protected array $filters;

    /**
     * Menerima filter/request dari controller.
     */
    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Mengambil data surat masuk untuk export Excel.
     *
     * Hak akses:
     * - Admin    : semua surat
     * - Pimpinan : semua surat
     * - Staff    : hanya surat yang didisposisikan kepadanya
     */
    public function collection(): Collection
    {
        $query = SuratMasuk::query()
            ->with([
                'kategori',
                'penerima',
                'disposisi.kepada',
            ]);

        /*
        |--------------------------------------------------------------------------
        | BATASAN AKSES BERDASARKAN ROLE
        |--------------------------------------------------------------------------
        */

        $user = Auth::user();

        if (!$user) {
            return collect();
        }

        $role = strtolower(
            trim(
                (string) (
                    $user->role ??
                    ''
                )
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Normalisasi role "staf" -> "staff"
        |--------------------------------------------------------------------------
        */

        if ($role === 'staf') {
            $role = 'staff';
        }

        /*
        |--------------------------------------------------------------------------
        | STAFF
        |--------------------------------------------------------------------------
        |
        | Staff hanya mendapatkan surat yang mempunyai disposisi
        | kepada user yang sedang login.
        |
        */

        if ($role === 'staff') {
            $query->whereHas(
                'disposisi',
                function (Builder $disposisi) use ($user): void {
                    $disposisi->where(
                        'kepada_user_id',
                        (int) $user->id
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | FILTER
        |--------------------------------------------------------------------------
        |
        | Filter menggunakan scopeFilter() milik model SuratMasuk.
        |
        | Mendukung:
        | - search
        | - kategori_surat_id
        | - kategori_id
        | - status
        | - dari_tanggal
        | - sampai_tanggal
        |
        */

        $query->filter(
            $this->filters
        );

        /*
        |--------------------------------------------------------------------------
        | URUTAN DATA
        |--------------------------------------------------------------------------
        */

        return $query
            ->orderBy(
                'tanggal_terima',
                'asc'
            )
            ->orderBy(
                'id',
                'asc'
            )
            ->get();
    }
}