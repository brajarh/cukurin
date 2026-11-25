<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roleAdmin = Role::where(
            'nama_role', 'admin'
        )->firstOrFail();

        $rolePelanggan = Role::where(
            'nama_role', 'pelanggan'
        )->firstOrFail();

        $admin = User::firstOrCreate(
            ['email' => 'admin@cukurin.test'],
            [
                'name' => 'Admin Cukurin',
                'password' => Hash::make('admin123')
            ]
        );

        $pelanggan = User::firstOrCreate(
            ['email' => 'pelanggan@cukurin.test'],
            [
                'name' => 'Pelanggan Cukurin',
                'password' => Hash::make('pelanggan123')
            ]
        );

        UserRole::updateOrCreate(
            ['user_id' => $admin->id],
            ['role_id' => $roleAdmin->id]
        );

        UserRole::updateOrCreate(
            ['user_id' => $pelanggan->id],
            ['role_id' => $rolePelanggan->id]
        );
    }
}