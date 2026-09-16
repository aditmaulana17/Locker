<?php

namespace App\Exports;

use App\Models\SuratMasuk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Throwable;

class SuratMasukExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithColumnWidths
{
    /*
    |--------------------------------------------------------------------------
    | FILTER
    |--------------------------------------------------------------------------
    |
    | Filter yang diterima dari halaman Surat Masuk:
    |
    | - search
    | - kategori_surat_id
    | - kategori_id
    | - status
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
     * Mengambil semua data surat masuk
     * berdasarkan filter yang dikirim.
     *
     * Hak akses:
     * - Admin    : semua surat
     * - Pimpinan : semua surat
     * - Staff    : hanya surat yang didisposisikan
     *              kepada dirinya sendiri
     */
    public function collection(): Collection
    {
        $query =
            SuratMasuk::query()
                ->with([
                    'kategori',
                    'penerima',
                    'disposisi.kepada',
                ]);

        /*
        |--------------------------------------------------------------------------
        | USER LOGIN
        |--------------------------------------------------------------------------
        */

        $user =
            Auth::user();

        if (!$user) {
            return collect();
        }

        /*
        |--------------------------------------------------------------------------
        | ROLE
        |--------------------------------------------------------------------------
        */

        $role =
            strtolower(
                trim(
                    (string) (
                        $user->role
                        ?? $user->jabatan
                        ?? ''
                    )
                )
            );

        /*
        |--------------------------------------------------------------------------
        | NORMALISASI ROLE
        |--------------------------------------------------------------------------
        |
        | staf -> staff
        |
        */

        if (
            $role === 'staf'
        ) {
            $role = 'staff';
        }

        /*
        |--------------------------------------------------------------------------
        | STAFF
        |--------------------------------------------------------------------------
        |
        | Staff hanya dapat melihat/export surat yang mempunyai
        | disposisi kepada dirinya sendiri.
        |
        */

        if (
            $role === 'staff'
        ) {
            $query->whereHas(
                'disposisi',
                function (
                    Builder $disposisi
                ) use (
                    $user
                ): void {

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
        | Menggunakan scopeFilter() pada model SuratMasuk.
        |
        */

        $query->filter(
            $this->filters
        );

        /*
        |--------------------------------------------------------------------------
        | SORTING
        |--------------------------------------------------------------------------
        |
        | Urutan:
        | 1. Tanggal terima
        | 2. ID
        |
        | Data tidak menggunakan pagination karena
        | seluruh hasil filter harus masuk ke Excel.
        |
        */

        return $query
            ->orderByRaw(
                'tanggal_terima IS NULL ASC'
            )
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

    /*
    |--------------------------------------------------------------------------
    | HEADINGS
    |--------------------------------------------------------------------------
    */

    /**
     * Header tabel Excel.
     */
    public function headings(): array
    {
        return [
            'No',
            'Nomor Surat',
            'Tanggal Surat',
            'Tanggal Terima',
            'Pengirim',
            'Perihal',
            'Kategori',
            'Status',
            'Diterima Oleh',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | MAPPING
    |--------------------------------------------------------------------------
    */

    /**
     * Mengatur format setiap baris Excel.
     */
    public function map(
        $surat
    ): array {
        static $no = 0;

        $no++;

        /*
        |--------------------------------------------------------------------------
        | TANGGAL SURAT
        |--------------------------------------------------------------------------
        */

        $tanggalSurat =
            $this->formatDate(
                $surat->tanggal_surat
            );

        /*
        |--------------------------------------------------------------------------
        | TANGGAL TERIMA
        |--------------------------------------------------------------------------
        */

        $tanggalTerima =
            $this->formatDate(
                $surat->tanggal_terima
            );

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
        | STATUS
        |--------------------------------------------------------------------------
        */

        $status =
            strtolower(
                trim(
                    (string) (
                        $surat->status
                        ?? 'baru'
                    )
                )
            );

        $statusLabels = [
            'baru' =>
                'Baru',

            'proses' =>
                'Diproses',

            'diproses' =>
                'Diproses',

            'didisposisikan' =>
                'Didisposisikan',

            'selesai' =>
                'Selesai',

            'diarsipkan' =>
                'Diarsipkan',
        ];

        $statusLabel =
            $statusLabels[$status]
            ?? ucfirst($status);

        /*
        |--------------------------------------------------------------------------
        | DITERIMA OLEH
        |--------------------------------------------------------------------------
        */

        $diterimaOleh =
            $surat->penerima?->name
            ?? $surat->penerima?->nama
            ?? '-';

        /*
        |--------------------------------------------------------------------------
        | RETURN
        |--------------------------------------------------------------------------
        */

        return [
            $no,

            $this->sanitizeCellValue(
                $surat->nomor_surat
            ),

            $tanggalSurat,

            $tanggalTerima,

            $this->sanitizeCellValue(
                $surat->pengirim
            ),

            $this->sanitizeCellValue(
                $surat->perihal
            ),

            $this->sanitizeCellValue(
                $kategori
            ),

            $statusLabel,

            $this->sanitizeCellValue(
                $diterimaOleh
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | FORMAT DATE
    |--------------------------------------------------------------------------
    */

    /**
     * Format tanggal menjadi dd/mm/YYYY.
     *
     * mixed digunakan karena nilai tanggal Eloquent
     * dapat berupa string, Carbon, DateTime, atau null.
     */
    private function formatDate(
        mixed $date
    ): string {
        if (
            empty($date)
        ) {
            return '-';
        }

        try {
            return \Illuminate\Support\Carbon::parse(
                $date
            )->format(
                'd/m/Y'
            );
        } catch (
            Throwable
        ) {
            return (string) $date;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SANITIZE CELL VALUE
    |--------------------------------------------------------------------------
    */

    /**
     * Membersihkan nilai sebelum dimasukkan ke Excel.
     *
     * Menjaga agar nilai null tidak muncul sebagai
     * warning/error dan tetap tampil sebagai "-".
     */
    private function sanitizeCellValue(
        mixed $value
    ): string {
        if (
            $value === null ||
            trim(
                (string) $value
            ) === ''
        ) {
            return '-';
        }

        return trim(
            (string) $value
        );
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

        $highestRow =
            max(
                $sheet->getHighestRow(),
                1
            );

        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle(
                'A1:I1'
            )
            ->applyFromArray([
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
            $sheet
                ->getStyle(
                    'A2:I' .
                    $highestRow
                )
                ->applyFromArray([
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
            | NOMOR
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    'A2:A' .
                    $highestRow
                )
                ->getAlignment()
                ->setHorizontal(
                    'center'
                );

            /*
            |--------------------------------------------------------------------------
            | TANGGAL SURAT
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    'C2:C' .
                    $highestRow
                )
                ->getAlignment()
                ->setHorizontal(
                    'center'
                );

            /*
            |--------------------------------------------------------------------------
            | TANGGAL TERIMA
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    'D2:D' .
                    $highestRow
                )
                ->getAlignment()
                ->setHorizontal(
                    'center'
                );

            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    'H2:H' .
                    $highestRow
                )
                ->getAlignment()
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
                    $sheet
                        ->getStyle(
                            'A' .
                            $row .
                            ':I' .
                            $row
                        )
                        ->getFill()
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

        $sheet
            ->getRowDimension(
                1
            )
            ->setRowHeight(
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
                $sheet
                    ->getRowDimension(
                        $row
                    )
                    ->setRowHeight(
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
        | Tidak menggunakan WithAutoFilter agar tidak bergantung
        | pada interface yang tidak tersedia pada versi package.
        |
        */

        $sheet->setAutoFilter(
            'A1:I' .
            $highestRow
        );

        /*
        |--------------------------------------------------------------------------
        | ALIGNMENT
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle(
                'A1:I' .
                $highestRow
            )
            ->getAlignment()
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

    /**
     * Lebar kolom dibuat manual agar hasil Excel
     * tidak terlalu padat dan tidak terlalu lebar.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 27,
            'C' => 17,
            'D' => 17,
            'E' => 28,
            'F' => 42,
            'G' => 24,
            'H' => 20,
            'I' => 24,
        ];
    }
}