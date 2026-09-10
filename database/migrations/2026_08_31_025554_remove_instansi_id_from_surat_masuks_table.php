<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menghapus instansi_id dan memastikan pengirim tersedia.
     */
    public function up(): void
    {
        if (! Schema::hasTable('surat_masuks')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus instansi_id
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('surat_masuks', 'instansi_id')) {
            if (Schema::hasTable('instansis')) {
                $foreignKeys = Schema::getForeignKeys('surat_masuks');

                foreach ($foreignKeys as $foreignKey) {
                    if (
                        isset($foreignKey['name']) &&
                        $foreignKey['name'] === 'surat_masuks_instansi_id_foreign'
                    ) {
                        Schema::table('surat_masuks', function (Blueprint $table) {
                            $table->dropForeign(
                                'surat_masuks_instansi_id_foreign'
                            );
                        });

                        break;
                    }
                }
            }

            Schema::table('surat_masuks', function (Blueprint $table) {
                $table->dropColumn('instansi_id');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Tambahkan pengirim
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('surat_masuks', 'pengirim')) {
            Schema::table('surat_masuks', function (Blueprint $table) {
                $table->string('pengirim')
                    ->after('nomor_surat');
            });
        }
    }

    /**
     * Mengembalikan perubahan migration.
     */
    public function down(): void
    {
        if (! Schema::hasTable('surat_masuks')) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Hapus pengirim
        |--------------------------------------------------------------------------
        */

        if (Schema::hasColumn('surat_masuks', 'pengirim')) {
            Schema::table('surat_masuks', function (Blueprint $table) {
                $table->dropColumn('pengirim');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Kembalikan instansi_id
        |--------------------------------------------------------------------------
        */

        if (! Schema::hasColumn('surat_masuks', 'instansi_id')) {
            Schema::table('surat_masuks', function (Blueprint $table) {
                $table->foreignId('instansi_id')
                    ->nullable()
                    ->constrained('instansis')
                    ->nullOnDelete();
            });
        }
    }
};