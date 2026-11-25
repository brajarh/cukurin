<?php

namespace Database\Seeders;

use App\Models\Outlet;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Cukur.in Pusat', 'Jl. Contoh No. 1, Gresik'],
            ['Cukur.in Surabaya', 'Jl. Contoh No. 2, Surabaya'],
            ['Cukur.in Sidoarjo', 'Jl. Contoh No. 3, Sidoarjo'],
            ['Cukur.in Malang', 'Jl. Contoh No. 4, Malang'],
            ['Cukur.in Mojokerto', 'Jl. Contoh No. 5, Mojokerto'],
            ['Cukur.in Jember', 'Jl. Contoh No. 6, Jember'],
        ];

        foreach ($data as [$nama, $alamat]) {
            Outlet::firstOrCreate(
                ['nama_outlet' => $nama],
                ['alamat_outlet' => $alamat]
            );
        }
    }
}