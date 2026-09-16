<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_masuks', function (Blueprint $table) {
            /*
            |--------------------------------------------------------------------------
            | IDENTITAS SURAT
            |--------------------------------------------------------------------------
            */

            $table->id();

            $table->string('nomor_agenda')
                ->unique();

            $table->string('nomor_surat');

            $table->date('tanggal_surat');

            $table->date('tanggal_terima');

            /*
            |--------------------------------------------------------------------------
            | KATEGORI
            |--------------------------------------------------------------------------
            */

            $table->foreignId('kategori_surat_id')
                ->constrained('kategori_surats')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | INFORMASI SURAT
            |--------------------------------------------------------------------------
            */

            $table->string('perihal');

            $table->text('ringkasan')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | LAMPIRAN DIGITAL
            |--------------------------------------------------------------------------
            |
            | Berisi path file hasil akhir yang disimpan di Supabase.
            |
            */

            $table->string('lampiran_file')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | INFORMASI COMPRESSION
            |--------------------------------------------------------------------------
            |
            | Kolom berikut digunakan untuk menampilkan hasil compression
            | pada halaman Surat Masuk.
            |
            */

            $table->unsignedBigInteger(
                'lampiran_original_size'
            )->nullable();

            $table->unsignedBigInteger(
                'lampiran_compressed_size'
            )->nullable();

            $table->decimal(
                'lampiran_compression_percent',
                6,
                2
            )->nullable();

            /*
            | Status compression:
            |
            | compressed
            | unchanged
            |
            */

            $table->string(
                'lampiran_compression_status',
                30
            )->nullable();

            /*
            | Jenis file:
            |
            | pdf
            | image
            |
            */

            $table->string(
                'lampiran_compression_type',
                20
            )->nullable();

            /*
            | Profile compression:
            |
            | ebook-150
            | ebook-120
            | screen-96
            | gd-jpeg-82
            | dll.
            |
            */

            $table->string(
                'lampiran_compression_profile',
                50
            )->nullable();

            /*
            |--------------------------------------------------------------------------
            | STATUS SURAT
            |--------------------------------------------------------------------------
            */

            $table->string('status')
                ->default('baru');

            /*
            |--------------------------------------------------------------------------
            | ARSIP FISIK
            |--------------------------------------------------------------------------
            */

            $table->string('lokasi_arsip_fisik')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | USER PENERIMA
            |--------------------------------------------------------------------------
            */

            $table->foreignId('diterima_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | TIMESTAMP & SOFT DELETE
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | INDEX
            |--------------------------------------------------------------------------
            */

            $table->index([
                'tanggal_terima',
                'status',
            ]);

            $table->index(
                'lampiran_compression_status'
            );

            $table->index(
                'lampiran_compression_type'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'surat_masuks'
        );
    }
};