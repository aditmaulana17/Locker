<?php

namespace App\Exports;

use App\Models\SuratKeluar;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class SuratKeluarExport implements FromCollection
{
    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    |
    | Filter dikirim dari halaman Surat Keluar melalui:
    |
    | - search
    | - kategori_surat_id
    | - kategori_id
    | - status
    | - pengirim
    | - dari_tanggal
    | - sampai_tanggal
    |
    */

    protected array $filters;

    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(
        array $filters = []
    ) {
        $this->filters = $filters;
    }

    /*
    |--------------------------------------------------------------------------
    | COLLECTION
    |--------------------------------------------------------------------------
    */

    /**
     * Mengambil data surat keluar untuk export Excel.
     *
     * Data yang diexport:
     *
     * 1. Mengikuti filter halaman Surat Keluar
     * 2. Hanya data yang memenuhi filter
     * 3. Tidak menggunakan pagination
     * 4. Diurutkan berdasarkan tanggal surat
     * 5. Kemudian berdasarkan ID
     */
    public function collection(): Collection
    {
        return SuratKeluar::query()

            /*
            |--------------------------------------------------------------------------
            | RELATIONSHIP
            |--------------------------------------------------------------------------
            */

            ->with([
                'kategori',
                'pembuat',
                'penandatangan',
            ])

            /*
            |--------------------------------------------------------------------------
            | FILTER
            |--------------------------------------------------------------------------
            |
            | Menggunakan scopeFilter() dari model SuratKeluar.
            |
            */

            ->filter(
                $this->filters
            )

            /*
            |--------------------------------------------------------------------------
            | SORTING
            |--------------------------------------------------------------------------
            */

            ->orderByRaw(
                'tanggal_keluar IS NULL ASC'
            )

            ->orderBy(
                'tanggal_keluar',
                'asc'
            )

            ->orderBy(
                'id',
                'asc'
            )

            /*
            |--------------------------------------------------------------------------
            | EXPORT SEMUA HASIL FILTER
            |--------------------------------------------------------------------------
            |
            | Tidak menggunakan paginate().
            |
            */

            ->get();
    }
}