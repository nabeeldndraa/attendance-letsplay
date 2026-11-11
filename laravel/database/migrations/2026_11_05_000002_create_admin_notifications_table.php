<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_admin')->constrained('users')->cascadeOnDelete();
            $table->string('kategori', 30);
            $table->string('judul');
            $table->text('deskripsi');
            $table->text('detail')->nullable();
            $table->string('sumber_tipe', 30)->nullable();
            $table->unsignedBigInteger('sumber_id')->nullable();
            $table->string('status', 20)->nullable();
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();

            $table->index(['id_admin', 'dibaca_pada', 'created_at']);
            $table->index(['sumber_tipe', 'sumber_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notifications');
    }
};
