<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->foreignId('id_booking')->unique()->constrained('booking', 'id_booking');
            $table->foreignId('id_user')->constrained('users');
            $table->string('metode', 15);
            $table->unsignedInteger('jumlah');
            $table->timestamp('dibayar_pada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};