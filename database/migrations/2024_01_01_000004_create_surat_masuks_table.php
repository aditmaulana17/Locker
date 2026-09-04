<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_masuks', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_agenda')->unique(); // nomor internal otomatis
            $table->string('nomor_surat'); // nomor surat asli dari pengirim
            $table->date('tanggal_surat');
            $table->date('tanggal_terima');
            $table->foreignId('kategori_surat_id')->constrained('kategori_surats')->cascadeOnDelete();
            $table->string('perihal');
            $table->text('ringkasan')->nullable();
            $table->string('lampiran_file')->nullable(); // path file hasil scan
            $table->string('status')->default('baru'); // baru, diproses, didisposisikan, selesai, diarsipkan
            $table->string('lokasi_arsip_fisik')->nullable(); // rak/box fisik
            $table->foreignId('diterima_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tanggal_terima', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_masuks');
    }
};
