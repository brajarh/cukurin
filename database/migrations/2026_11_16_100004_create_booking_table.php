<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->id('id_booking');
            $table->string('kode_booking', 10)->unique();
            $table->foreignId('id_customer')->constrained('customer', 'id_customer');
            $table->foreignId('id_barber')->constrained('barber', 'id_barber');
            $table->foreignId('id_service')->constrained('service', 'id_service');
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('status', 15)->default('dipesan'); // dipesan | selesai | batal
            $table->timestamps();

            $table->index(['id_barber', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};
