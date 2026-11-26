<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Transaksi T5: log perubahan jadwal booking (siapa, kapan, dari jadwal apa ke jadwal apa, dan alasannya)
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perubahan_jadwal', function (Blueprint $table) {
            $table->id('id_perubahan');
            $table->foreignId('id_booking')->constrained('booking', 'id_booking')->cascadeOnDelete();
            $table->foreignId('id_user')->constrained('users');
            $table->date('tanggal_lama');
            $table->time('jam_mulai_lama');
            $table->time('jam_selesai_lama');
            $table->date('tanggal_baru');
            $table->time('jam_mulai_baru');
            $table->time('jam_selesai_baru');
            $table->string('alasan', 255);
            $table->timestamps(); // created_at = waktu perubahan dilakukan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perubahan_jadwal');
    }
};
