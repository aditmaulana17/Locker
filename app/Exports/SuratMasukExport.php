<?php

namespace App\Exports;

use App\Models\SuratMasuk;
use Illuminate\Support\Collection;
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
     */
    public function collection(): Collection
    {
        return SuratMasuk::query()
            ->with('kategori')
            ->filter($this->filters)
            ->orderBy('tanggal_terima', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }
}