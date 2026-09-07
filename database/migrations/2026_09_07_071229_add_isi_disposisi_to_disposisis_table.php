<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->text('isi_disposisi')->nullable()->after('instruksi');
        });
    }

    public function down(): void
    {
        Schema::table('disposisis', function (Blueprint $table) {
            $table->dropColumn('isi_disposisi');
        });
    }
};