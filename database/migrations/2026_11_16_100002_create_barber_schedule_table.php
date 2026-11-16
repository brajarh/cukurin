<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barber_schedule', function (Blueprint $table) {
            $table->id('id_schedule');
            $table->foreignId('id_barber')
                ->constrained('barber', 'id_barber')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('hari'); // 1 = Senin ... 7 = Minggu
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->timestamps();

            // satu barber hanya punya satu jadwal per hari
            $table->unique(['id_barber', 'hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barber_schedule');
    }
};
