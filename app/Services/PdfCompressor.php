<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class PdfCompressor
{
    /**
     * Kompres file PDF menggunakan Ghostscript.
     *
     * @param string $inputPath  Path PDF asli
     * @param string|null $outputPath Path PDF hasil kompresi
     * @return string Path PDF hasil kompresi
     */
    public function compress(
        string $inputPath,
        ?string $outputPath = null
    ): string {
        if (!is_file($inputPath)) {
            throw new RuntimeException(
                "File PDF tidak ditemukan: {$inputPath}"
            );
        }

        if ($outputPath === null) {
            $outputPath =
                dirname($inputPath) .
                DIRECTORY_SEPARATOR .
                pathinfo($inputPath, PATHINFO_FILENAME) .
                '-compressed.pdf';
        }

        $process = new Process([
            'gs',
            '-sDEVICE=pdfwrite',
            '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/ebook',
            '-dNOPAUSE',
            '-dQUIET',
            '-dBATCH',
            "-sOutputFile={$outputPath}",
            $inputPath,
        ]);

        $process->setTimeout(300);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                'Gagal mengompres PDF: ' .
                trim($process->getErrorOutput())
            );
        }

        if (!is_file($outputPath)) {
            throw new RuntimeException(
                'Ghostscript selesai tetapi file hasil kompresi tidak ditemukan.'
            );
        }

        if (filesize($outputPath) === 0) {
            throw new RuntimeException(
                'File PDF hasil kompresi kosong.'
            );
        }

        return $outputPath;
    }
}