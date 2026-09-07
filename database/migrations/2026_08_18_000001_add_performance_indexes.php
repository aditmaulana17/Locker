<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Surat Masuk
        |--------------------------------------------------------------------------
        |
        | Tambahkan index hanya pada kolom yang memang tersedia
        | di tabel surat_masuks.
        |
        */
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->index(
                'created_at',
                'surat_masuks_created_at_index'
            );

            $table->index(
                'kategori_surat_id',
                'surat_masuks_kategori_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Surat Keluar
        |--------------------------------------------------------------------------
        |
        | Tambahkan index hanya pada kolom yang memang tersedia
        | di tabel surat_keluars.
        |
        */
        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->index(
                'created_at',
                'surat_keluars_created_at_index'
            );

            $table->index(
                'kategori_surat_id',
                'surat_keluars_kategori_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Disposisi
        |--------------------------------------------------------------------------
        |
        | Index gabungan untuk pencarian disposisi berdasarkan
        | user tujuan dan status.
        |
        */
        Schema::table('disposisis', function (Blueprint $table) {
            $table->index(
                ['kepada_user_id', 'status'],
                'disposisis_user_status_index'
            );

            $table->index(
                'created_at',
                'disposisis_created_at_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Surat Masuk
        |--------------------------------------------------------------------------
        */
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->dropIndex(
                'surat_masuks_created_at_index'
            );

            $table->dropIndex(
                'surat_masuks_kategori_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Surat Keluar
        |--------------------------------------------------------------------------
        */
        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->dropIndex(
                'surat_keluars_created_at_index'
            );

            $table->dropIndex(
                'surat_keluars_kategori_index'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Disposisi
        |--------------------------------------------------------------------------
        */
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropIndex(
                'disposisis_user_status_index'
            );

            $table->dropIndex(
                'disposisis_created_at_index'
            );
        });
    }
};