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
        | Hanya membuat index jika index tersebut belum tersedia.
        | Ini diperlukan karena percobaan migration sebelumnya sempat
        | membuat sebagian index sebelum gagal pada instansi_id.
        |
        */
        $suratMasukIndexes = array_column(
            Schema::getIndexes('surat_masuks'),
            'name'
        );

        Schema::table('surat_masuks', function (Blueprint $table) use ($suratMasukIndexes) {
            if (! in_array('surat_masuks_created_at_index', $suratMasukIndexes, true)) {
                $table->index(
                    'created_at',
                    'surat_masuks_created_at_index'
                );
            }

            if (! in_array('surat_masuks_kategori_index', $suratMasukIndexes, true)) {
                $table->index(
                    'kategori_surat_id',
                    'surat_masuks_kategori_index'
                );
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Surat Keluar
        |--------------------------------------------------------------------------
        */
        $suratKeluarIndexes = array_column(
            Schema::getIndexes('surat_keluars'),
            'name'
        );

        Schema::table('surat_keluars', function (Blueprint $table) use ($suratKeluarIndexes) {
            if (! in_array('surat_keluars_created_at_index', $suratKeluarIndexes, true)) {
                $table->index(
                    'created_at',
                    'surat_keluars_created_at_index'
                );
            }

            if (! in_array('surat_keluars_kategori_index', $suratKeluarIndexes, true)) {
                $table->index(
                    'kategori_surat_id',
                    'surat_keluars_kategori_index'
                );
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Disposisi
        |--------------------------------------------------------------------------
        */
        $disposisiIndexes = array_column(
            Schema::getIndexes('disposisis'),
            'name'
        );

        Schema::table('disposisis', function (Blueprint $table) use ($disposisiIndexes) {
            if (! in_array('disposisis_user_status_index', $disposisiIndexes, true)) {
                $table->index(
                    ['kepada_user_id', 'status'],
                    'disposisis_user_status_index'
                );
            }

            if (! in_array('disposisis_created_at_index', $disposisiIndexes, true)) {
                $table->index(
                    'created_at',
                    'disposisis_created_at_index'
                );
            }
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
        $suratMasukIndexes = array_column(
            Schema::getIndexes('surat_masuks'),
            'name'
        );

        Schema::table('surat_masuks', function (Blueprint $table) use ($suratMasukIndexes) {
            if (in_array('surat_masuks_created_at_index', $suratMasukIndexes, true)) {
                $table->dropIndex('surat_masuks_created_at_index');
            }

            if (in_array('surat_masuks_kategori_index', $suratMasukIndexes, true)) {
                $table->dropIndex('surat_masuks_kategori_index');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Surat Keluar
        |--------------------------------------------------------------------------
        */
        $suratKeluarIndexes = array_column(
            Schema::getIndexes('surat_keluars'),
            'name'
        );

        Schema::table('surat_keluars', function (Blueprint $table) use ($suratKeluarIndexes) {
            if (in_array('surat_keluars_created_at_index', $suratKeluarIndexes, true)) {
                $table->dropIndex('surat_keluars_created_at_index');
            }

            if (in_array('surat_keluars_kategori_index', $suratKeluarIndexes, true)) {
                $table->dropIndex('surat_keluars_kategori_index');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Disposisi
        |--------------------------------------------------------------------------
        */
        $disposisiIndexes = array_column(
            Schema::getIndexes('disposisis'),
            'name'
        );

        Schema::table('disposisis', function (Blueprint $table) use ($disposisiIndexes) {
            if (in_array('disposisis_user_status_index', $disposisiIndexes, true)) {
                $table->dropIndex('disposisis_user_status_index');
            }

            if (in_array('disposisis_created_at_index', $disposisiIndexes, true)) {
                $table->dropIndex('disposisis_created_at_index');
            }
        });
    }
};