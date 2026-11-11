<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('jenis', 20);
            $table->text('keterangan');
            $table->string('status', 20)->default('Diajukan');
            $table->foreignId('id_admin_konfirmasi')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->text('catatan_admin')->nullable();
            $table->timestamps();

            $table->unique(['id_user', 'tanggal']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
