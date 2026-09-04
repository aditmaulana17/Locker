<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            // Wajib hapus foreign key terlebih dahulu sebelum drop kolom
            $table->dropForeign('surat_masuks_instansi_id_foreign');
            
            // Baru hapus kolom instansi_id
            $table->dropColumn('instansi_id');
            
            // Tambahkan kembali kolom pengirim sebagai teks bebas
            $table->string('pengirim')->after('nomor_surat');
        });
    }

    public function down(): void
    {
        Schema::table('surat_masuks', function (Blueprint $table) {
            $table->dropColumn('pengirim');
            $table->unsignedBigInteger('instansi_id')->nullable();
            // Jika ingin rollback sempurna, bisa tambahkan foreign key lagi di sini jika diperlukan
        });
    }
};