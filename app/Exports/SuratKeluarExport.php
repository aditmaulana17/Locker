<?php

namespace App\Exports;

use App\Models\SuratKeluar;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class SuratKeluarExport implements FromCollection
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection(): Collection
    {
        return SuratKeluar::query()
            ->with('kategori')
            ->filter($this->filters)
            ->orderBy('tanggal_surat', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }
}