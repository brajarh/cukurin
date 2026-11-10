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
        Schema::create('barberservice', function (Blueprint $table) {
            $table->foreignId('id_barber')
            ->constrained('barber', 'id_barber');
            $table->foreignId('id_service')
            ->constrained('service', 'id_service');
            $table->primary(['id_barber', 'id_service']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barberservice');
    }
};
