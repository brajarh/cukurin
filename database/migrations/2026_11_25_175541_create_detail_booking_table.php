<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_booking', function (Blueprint $table) {

            $table->id('id_detail_booking');

            $table->foreignId('id_booking')
                ->constrained('booking', 'id_booking')
                ->cascadeOnDelete();

            $table->foreignId('id_service')
                ->constrained('service', 'id_service');

            $table->integer('harga');
            $table->unsignedSmallInteger('durasi');

            $table->unique(['id_booking', 'id_service']);

            $table->timestamps();
        });

        // Memindahkan layanan dari booking lama ke detail_booking
        DB::table('detail_booking')->insertUsing(
            [
                'id_booking',
                'id_service',
                'harga',
                'durasi',
                'created_at',
                'updated_at'
            ],
            DB::table('booking')
                ->join(
                    'service',
                    'booking.id_service',
                    '=',
                    'service.id_service'
                )
                ->select(
                    'booking.id_booking',
                    'booking.id_service',
                    'service.harga_service',
                    'service.durasi',
                    'booking.created_at',
                    'booking.updated_at'
                )
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_booking');
    }
};