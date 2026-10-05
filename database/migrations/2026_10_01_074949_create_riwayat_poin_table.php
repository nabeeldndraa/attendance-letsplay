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
        Schema::create('riwayat_poin', function (Blueprint $table) {
            $table->id('id_log');

            // User pemilik poin
            $table->foreignId('id_user')
                ->constrained('users')
                ->cascadeOnDelete();

            // Presensi yang menghasilkan poin
            $table->unsignedBigInteger('id_presensi')->nullable();

            $table->foreign('id_presensi')
                ->references('id_presensi')
                ->on('presensi')
                ->nullOnDelete();

            // Jumlah poin, bisa positif atau negatif
            $table->integer('poin');

            // Contoh: Tepat waktu, Alpha, Bonus manual admin
            $table->string('keterangan');

            // Format: 2026-10
            $table->string('periode_bulan', 7);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_poin');
    }
};