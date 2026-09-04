<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposisis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_masuk_id')->constrained('surat_masuks')->cascadeOnDelete();
            $table->foreignId('dari_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kepada_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('instruksi'); // contoh: "Mohon ditindaklanjuti", "Untuk diketahui"
            $table->text('catatan')->nullable();
            $table->date('batas_waktu')->nullable();
            $table->string('status')->default('menunggu'); // menunggu, diproses, selesai
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposisis');
    }
};
