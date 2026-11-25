<?php

namespace Tests\Feature;

use App\Models\Barber;
use App\Models\Outlet;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutletTest extends TestCase
{
    use RefreshDatabase;

    private function userDenganRole(string $role): User
    {
        $user = User::factory()->create();
        $r = Role::firstOrCreate(['nama_role' => $role]);
        UserRole::create(['user_id' => $user->id, 'role_id' => $r->id]);

        return $user;
    }

    public function test_guest_dialihkan_ke_login(): void
    {
        $this->get('/outlet')->assertRedirect('/login');
    }

    public function test_pelanggan_tidak_boleh_membuka_outlet(): void
    {
        $this->actingAs($this->userDenganRole('pelanggan'))
            ->get('/outlet')->assertStatus(403);
    }

    public function test_admin_bisa_menambah_outlet(): void
    {
        $this->actingAs($this->userDenganRole('admin'))
            ->post('/outlet', ['nama_outlet' => 'Cabang Uji', 'alamat_outlet' => 'Jl. Uji 1'])
            ->assertRedirect(route('outlet.index'));

        $this->assertDatabaseHas('outlet', ['nama_outlet' => 'Cabang Uji']);
    }

    public function test_validasi_nama_outlet_wajib(): void
    {
        $this->actingAs($this->userDenganRole('admin'))
            ->post('/outlet', ['nama_outlet' => '', 'alamat_outlet' => 'x'])
            ->assertSessionHasErrors('nama_outlet');
    }

    public function test_outlet_yang_punya_barber_tidak_bisa_dihapus(): void
    {
        $o = Outlet::create(['nama_outlet' => 'Cabang A', 'alamat_outlet' => 'Jl. A']);
        Barber::create(['nama_barber' => 'Budi', 'no_hp' => '0812', 'id_outlet' => $o->id_outlet]);

        $this->actingAs($this->userDenganRole('admin'))
            ->delete('/outlet/' . $o->id_outlet)
            ->assertSessionHas('error');

        $this->assertDatabaseHas('outlet', ['id_outlet' => $o->id_outlet]);
    }
}