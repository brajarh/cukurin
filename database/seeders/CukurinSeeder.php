<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\BarberSchedule;
use App\Models\Outlet;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Data dummy untuk testing fitur. Jalankan:
 *   php artisan db:seed --class=CukurinSeeder
 * Aman dijalankan berulang (pakai firstOrCreate / updateOrCreate).
 */
class CukurinSeeder extends Seeder
{
    public function run(): void
    {
        $o1 = Outlet::firstOrCreate(['nama_outlet' => 'Cukur.in Pusat'], ['alamat_outlet' => 'Jl. Contoh No. 1, Gresik']);
        $o2 = Outlet::firstOrCreate(['nama_outlet' => 'Cukur.in Surabaya'], ['alamat_outlet' => 'Jl. Contoh No. 2, Surabaya']);

        $potong = Service::firstOrCreate(['nama_service' => 'Potong Rambut'], ['harga_service' => 35000, 'durasi' => 30]);
        $jenggot = Service::firstOrCreate(['nama_service' => 'Cukur Jenggot'], ['harga_service' => 20000, 'durasi' => 30]);
        $paket = Service::firstOrCreate(['nama_service' => 'Potong + Keramas'], ['harga_service' => 50000, 'durasi' => 60]);
        $warna = Service::firstOrCreate(['nama_service' => 'Pewarnaan Rambut'], ['harga_service' => 150000, 'durasi' => 90]);

        $daftar = [
            ['Raka', '081234567801', $o1, [$potong, $jenggot, $paket]],
            ['Dimas', '081234567802', $o1, [$potong, $paket, $warna]],
            ['Fajar', '081234567803', $o2, [$potong, $jenggot]],
            ['Bayu', '081234567804', $o2, [$potong, $paket, $warna]],
        ];

        foreach ($daftar as [$nama, $hp, $outlet, $layanan]) {
            $barber = Barber::firstOrCreate(
                ['nama_barber' => $nama],
                ['no_hp' => $hp, 'id_outlet' => $outlet->id_outlet]
            );
            $barber->service()->sync(collect($layanan)->pluck('id_service')->all());

            // Senin - Sabtu, 09:00 - 17:00
            foreach (range(1, 6) as $hari) {
                BarberSchedule::updateOrCreate(
                    ['id_barber' => $barber->id_barber, 'hari' => $hari],
                    ['jam_mulai' => '09:00:00', 'jam_selesai' => '17:00:00']
                );
            }
        }
    }
}
