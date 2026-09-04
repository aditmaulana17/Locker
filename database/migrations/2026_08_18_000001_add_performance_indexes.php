<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->index('created_at', 'surat_masuks_created_at_index');
            $table->index('kategori_surat_id', 'surat_masuks_kategori_index');
            $table->index('instansi_id', 'surat_masuks_instansi_index');
        });

        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->index('created_at', 'surat_keluars_created_at_index');
            $table->index('kategori_surat_id', 'surat_keluars_kategori_index');
            $table->index('instansi_id', 'surat_keluars_instansi_index');
        });

        Schema::table('disposisis', function (Blueprint $table) {
            $table->index(['kepada_user_id', 'status'], 'disposisis_user_status_index');
            $table->index('created_at', 'disposisis_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->dropIndex('surat_masuks_created_at_index');
            $table->dropIndex('surat_masuks_kategori_index');
            $table->dropIndex('surat_masuks_instansi_index');
        });

        Schema::table('surat_keluars', function (Blueprint $table) {
            $table->dropIndex('surat_keluars_created_at_index');
            $table->dropIndex('surat_keluars_kategori_index');
            $table->dropIndex('surat_keluars_instansi_index');
        });

        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropIndex('disposisis_user_status_index');
            $table->dropIndex('disposisis_created_at_index');
        });
    }
};
