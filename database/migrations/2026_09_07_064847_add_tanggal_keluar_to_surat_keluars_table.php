<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan tanggal keluar pada tabel surat keluar.
     *
     * Kolom dibuat nullable agar data surat keluar lama
     * tetap dapat digunakan tanpa harus memiliki tanggal keluar.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('surat_keluars', 'tanggal_keluar')) {
            Schema::table('surat_keluars', function (Blueprint $table) {
                $table->date('tanggal_keluar')
                    ->nullable()
                    ->after('tanggal_surat');
            });
        }
    }

    /**
     * Menghapus kolom tanggal keluar.
     */
    public function down(): void
    {
        if (Schema::hasColumn('surat_keluars', 'tanggal_keluar')) {
            Schema::table('surat_keluars', function (Blueprint $table) {
                $table->dropColumn('tanggal_keluar');
            });
        }
    }
};