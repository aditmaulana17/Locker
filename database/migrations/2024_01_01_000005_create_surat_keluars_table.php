<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surat_keluars', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique(); 
            $table->date('tanggal_surat');
            $table->string('pengirim'); // Menggantikan instansi_id (teks bebas)
            $table->foreignId('kategori_surat_id')->constrained('kategori_surats')->cascadeOnDelete();
            $table->string('perihal');
            $table->text('ringkasan')->nullable();
            $table->string('lampiran_file')->nullable();
            $table->string('status')->default('draft'); // draft, dikirim, diarsipkan
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ditandatangani_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tanggal_surat', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surat_keluars');
    }
};