<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Kolom durasi di tabel service diubah dari string menjadi integer (satuan menit)
    // supaya bisa dipakai menghitung jam selesai dan slot booking.
    //
    // PostgreSQL tidak mau mengubah tipe teks -> angka secara langsung (->change()),
    // jadi pakai cara aman untuk semua database: kolom baru, salin data, hapus kolom lama, ganti nama.
    public function up(): void
    {
        Schema::table('service', function (Blueprint $table) {
            $table->unsignedSmallInteger('durasi_menit')->default(30);
        });

        foreach (DB::table('service')->get() as $row) {
            // "30 menit" -> 30 ; kalau tidak ada angka, pakai 30
            $angka = (int) preg_replace('/\D/', '', (string) $row->durasi);
            DB::table('service')
                ->where('id_service', $row->id_service)
                ->update(['durasi_menit' => $angka > 0 ? $angka : 30]);
        }

        Schema::table('service', function (Blueprint $table) {
            $table->dropColumn('durasi');
        });

        Schema::table('service', function (Blueprint $table) {
            $table->renameColumn('durasi_menit', 'durasi');
        });
    }

    public function down(): void
    {
        Schema::table('service', function (Blueprint $table) {
            $table->string('durasi_teks', 10)->default('30');
        });

        foreach (DB::table('service')->get() as $row) {
            DB::table('service')
                ->where('id_service', $row->id_service)
                ->update(['durasi_teks' => (string) $row->durasi]);
        }

        Schema::table('service', function (Blueprint $table) {
            $table->dropColumn('durasi');
        });

        Schema::table('service', function (Blueprint $table) {
            $table->renameColumn('durasi_teks', 'durasi');
        });
    }
};
