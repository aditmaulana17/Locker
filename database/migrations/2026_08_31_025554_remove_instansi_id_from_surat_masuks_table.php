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
        if (! Schema::hasTable('surat_masuks')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove instansi_id
        |--------------------------------------------------------------------------
        |
        | Kolom instansi_id sudah tidak digunakan oleh struktur aplikasi.
        | Pemeriksaan dilakukan terlebih dahulu agar migration aman
        | apabila kolom tersebut sudah tidak ada.
        |
        */
        if (Schema::hasColumn('surat_masuks', 'instansi_id')) {
            $foreignKeys = Schema::getForeignKeys('surat_masuks');

            foreach ($foreignKeys as $foreignKey) {
                if (
                    isset($foreignKey['name']) &&
                    $foreignKey['name'] === 'surat_masuks_instansi_id_foreign'
                ) {
                    Schema::table('surat_masuks', function (Blueprint $table) {
                        $table->dropForeign('surat_masuks_instansi_id_foreign');
                    });

                    break;
                }
            }

            Schema::table('surat_masuks', function (Blueprint $table) {
                $table->dropColumn('instansi_id');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Add pengirim
        |--------------------------------------------------------------------------
        */
        if (! Schema::hasColumn('surat_masuks', 'pengirim')) {
            Schema::table('surat_masuks', function (Blueprint $table) {
                $table->string('pengirim')->after('nomor_surat');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('surat_masuks')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove pengirim
        |--------------------------------------------------------------------------
        */
        if (Schema::hasColumn('surat_masuks', 'pengirim')) {
            Schema::table('surat_masuks', function (Blueprint $table) {
                $table->dropColumn('pengirim');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Restore instansi_id
        |--------------------------------------------------------------------------
        */
        if (! Schema::hasColumn('surat_masuks', 'instansi_id')) {
            Schema::table('surat_masuks', function (Blueprint $table) {
                $table->unsignedBigInteger('instansi_id')->nullable();
            });
        }
    }
};