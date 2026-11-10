<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     */
    public function up(): void
    {
        Schema::create('barber', function (Blueprint $table) {
        $table->id('id_barber');
        $table->string('nama_barber', 100);
        $table->string('no_hp', 20);
        $table->foreignId('id_outlet')
            ->constrained('outlet', 'id_outlet');
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barber');
    }
};
