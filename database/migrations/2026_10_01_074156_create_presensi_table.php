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
        Schema::create('presensi', function (Blueprint $table) {
            $table->id('id_presensi');

            // User yang melakukan presensi
            $table->foreignId('id_user')
                ->constrained('users')
                ->cascadeOnDelete();

            // Waktu presensi
            $table->date('tanggal');
            $table->time('jam_presensi');

            // Jenis kerja
            $table->enum('tipe_kerja', ['WFO', 'WFH']);

            // Lokasi kantor, NULL jika WFH
            $table->unsignedBigInteger('id_lokasi')->nullable();

            $table->foreign('id_lokasi')
                ->references('id_lokasi')
                ->on('lokasi_kantor')
                ->nullOnDelete();

            // Koordinat GPS user
            $table->decimal('latitude_user', 10, 8);
            $table->decimal('longitude_user', 11, 8);

            // Jarak user dari kantor dalam meter
            $table->integer('jarak_dari_kantor')->nullable();

            // Progress yang dikerjakan hari itu
            $table->text('progress_hari_ini');

            // Status presensi
            $table->enum('status_presensi', [
                'Tepat Waktu',
                'Terlambat',
                'Alpha',
                'Menunggu Konfirmasi'
            ]);

            // Status pengajuan konfirmasi keterlambatan
            $table->enum('status_konfirmasi', [
                '-',
                'Diajukan',
                'Disetujui',
                'Ditolak'
            ])->default('-');

            // Admin yang melakukan konfirmasi
            $table->foreignId('id_admin_konfirmasi')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Catatan dari admin
            $table->text('catatan_admin')->nullable();

            // Poin yang didapatkan dari presensi
            $table->integer('point_didapat')->default(0);

            $table->timestamps();

            // Satu user hanya boleh punya satu presensi per tanggal
            $table->unique(['id_user', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
