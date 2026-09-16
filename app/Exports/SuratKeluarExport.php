<?php

namespace App\Exports;

use App\Models\SuratKeluar;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SuratKeluarExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths,
    ShouldAutoSize
{
    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    |
    | Filter yang diterima dari halaman Surat Keluar:
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

    public function collection(): Collection
    {
        return SuratKeluar::query()
            ->with([
                'kategori',
                'pembuat',
                'penandatangan',
            ])
            ->filter(
                $this->filters
            )
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
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | HEADINGS
    |--------------------------------------------------------------------------
    */

    public function headings(): array
    {
        return [
            'No',
            'Nomor Surat',
            'Tanggal Keluar',
            'Pengirim',
            'Perihal',
            'Kategori',
            'Status',
            'Dibuat Oleh',
            'Penandatangan',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | MAPPING
    |--------------------------------------------------------------------------
    */

    public function map(
        $surat
    ): array {
        static $no = 0;

        $no++;

        /*
        |--------------------------------------------------------------------------
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status = strtolower(
            trim(
                (string) (
                    $surat->status ??
                    'draft'
                )
            )
        );

        if (
            $status === 'draf'
        ) {
            $status = 'draft';
        }

        $statusLabels = [
            'draft' =>
                'Draft',

            'diproses' =>
                'Diproses',

            'disetujui' =>
                'Disetujui',

            'dikirim' =>
                'Dikirim',

            'diarsipkan' =>
                'Diarsipkan',
        ];

        $statusLabel =
            $statusLabels[$status]
            ?? ucfirst($status);

        /*
        |--------------------------------------------------------------------------
        | TANGGAL KELUAR
        |--------------------------------------------------------------------------
        */

        $tanggalKeluar = '-';

        if (
            !empty(
                $surat->tanggal_keluar
            )
        ) {
            try {
                $tanggalKeluar =
                    \Illuminate\Support\Carbon::parse(
                        $surat->tanggal_keluar
                    )->format(
                        'd/m/Y'
                    );
            } catch (
                \Throwable
            ) {
                $tanggalKeluar =
                    (string) $surat->tanggal_keluar;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | KATEGORI
        |--------------------------------------------------------------------------
        */

        $kategori =
            $surat->kategori?->nama_kategori
            ?? '-';

        /*
        |--------------------------------------------------------------------------
        | PEMBUAT
        |--------------------------------------------------------------------------
        */

        $pembuat =
            $surat->pembuat?->name
            ?? $surat->pembuat?->nama
            ?? '-';

        /*
        |--------------------------------------------------------------------------
        | PENANDATANGAN
        |--------------------------------------------------------------------------
        */

        $penandatangan =
            $surat->penandatangan?->name
            ?? $surat->penandatangan?->nama
            ?? '-';

        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return [
            $no,
            $surat->nomor_surat ?? '-',
            $tanggalKeluar,
            $surat->pengirim ?? '-',
            $surat->perihal ?? '-',
            $kategori,
            $statusLabel,
            $pembuat,
            $penandatangan,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | STYLES
    |--------------------------------------------------------------------------
    */

    public function styles(
        Worksheet $sheet
    ): array {
        /*
        |--------------------------------------------------------------------------
        | JUMLAH BARIS
        |--------------------------------------------------------------------------
        */

        $highestRow = max(
            $sheet->getHighestRow(),
            1
        );

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle(
            'A1:I1'
        )->applyFromArray([
            'font' => [
                'bold' =>
                    true,

                'size' =>
                    11,

                'color' => [
                    'rgb' =>
                        '1E293B',
                ],
            ],

            'alignment' => [
                'horizontal' =>
                    'center',

                'vertical' =>
                    'center',

                'wrapText' =>
                    true,
            ],

            'fill' => [
                'fillType' =>
                    'solid',

                'startColor' => [
                    'rgb' =>
                        'E2E8F0',
                ],
            ],

            'borders' => [
                'allBorders' => [
                    'borderStyle' =>
                        'thin',

                    'color' => [
                        'rgb' =>
                            '94A3B8',
                    ],
                ],
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | BODY
        |--------------------------------------------------------------------------
        */

        if (
            $highestRow >= 2
        ) {
            $sheet->getStyle(
                'A2:I' .
                $highestRow
            )->applyFromArray([
                'font' => [
                    'size' =>
                        10,

                    'color' => [
                        'rgb' =>
                            '334155',
                    ],
                ],

                'alignment' => [
                    'vertical' =>
                        'center',

                    'wrapText' =>
                        true,
                ],

                'borders' => [
                    'allBorders' => [
                        'borderStyle' =>
                            'thin',

                        'color' => [
                            'rgb' =>
                                'CBD5E1',
                        ],
                    ],
                ],
            ]);

            /*
            |--------------------------------------------------------------------------
            | KOLOM NO
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle(
                'A2:A' .
                $highestRow
            )->getAlignment()
                ->setHorizontal(
                    'center'
                );

            /*
            |--------------------------------------------------------------------------
            | KOLOM TANGGAL
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle(
                'C2:C' .
                $highestRow
            )->getAlignment()
                ->setHorizontal(
                    'center'
                );

            /*
            |--------------------------------------------------------------------------
            | KOLOM STATUS
            |--------------------------------------------------------------------------
            */

            $sheet->getStyle(
                'G2:G' .
                $highestRow
            )->getAlignment()
                ->setHorizontal(
                    'center'
                );

            /*
            |--------------------------------------------------------------------------
            | ZEBRA ROW
            |--------------------------------------------------------------------------
            */

            for (
                $row = 2;
                $row <= $highestRow;
                $row++
            ) {
                if (
                    $row % 2 === 0
                ) {
                    $sheet->getStyle(
                        'A' .
                        $row .
                        ':I' .
                        $row
                    )->getFill()
                        ->setFillType(
                            'solid'
                        )
                        ->getStartColor()
                        ->setRGB(
                            'F8FAFC'
                        );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | HEADER HEIGHT
        |--------------------------------------------------------------------------
        */

        $sheet->getRowDimension(
            1
        )->setRowHeight(
            30
        );

        /*
        |--------------------------------------------------------------------------
        | BODY HEIGHT
        |--------------------------------------------------------------------------
        */

        if (
            $highestRow >= 2
        ) {
            for (
                $row = 2;
                $row <= $highestRow;
                $row++
            ) {
                $sheet->getRowDimension(
                    $row
                )->setRowHeight(
                    36
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FREEZE HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane(
            'A2'
        );

        /*
        |--------------------------------------------------------------------------
        | AUTO FILTER
        |--------------------------------------------------------------------------
        |
        | Tidak membutuhkan WithAutoFilter.
        | PhpSpreadsheet langsung menyediakan setAutoFilter().
        |
        */

        $sheet->setAutoFilter(
            'A1:I' .
            $highestRow
        );

        /*
        |--------------------------------------------------------------------------
        | VERTICAL ALIGNMENT
        |--------------------------------------------------------------------------
        */

        $sheet->getStyle(
            'A1:I' .
            $highestRow
        )->getAlignment()
            ->setVertical(
                'center'
            );

        return [];
    }

    /*
    |--------------------------------------------------------------------------
    | COLUMN WIDTHS
    |--------------------------------------------------------------------------
    */

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 26,
            'C' => 17,
            'D' => 25,
            'E' => 40,
            'F' => 23,
            'G' => 17,
            'H' => 23,
            'I' => 25,
        ];
    }
}