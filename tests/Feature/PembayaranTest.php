<?php

namespace Tests\Feature;

use App\Models\Barber;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_pembayaran_mengubah_status_booking_menjadi_selesai(): void
    {
        $admin = User::factory()->create();
        $role = Role::firstOrCreate(['nama_role' => 'admin']);
        UserRole::create(['user_id' => $admin->id, 'role_id' => $role->id]);

        $outlet = Outlet::create(['nama_outlet' => 'Cabang A', 'alamat_outlet' => 'Jl. A']);
        $barber = Barber::create(['nama_barber' => 'Budi', 'no_hp' => '0812', 'id_outlet' => $outlet->id_outlet]);
        $service = Service::create(['nama_service' => 'Potong', 'harga_service' => 35000, 'durasi' => 30]);
        $cust = Customer::create(['nama' => 'Andi', 'no_hp' => '0813']);

        $booking = Booking::create([
            'kode_booking' => 'CKTEST01',
            'id_user' => $admin->id,
            'id_customer' => $cust->id_customer,
            'id_barber' => $barber->id_barber,
            'id_service' => $service->id_service,
            'tanggal' => now()->addDay()->toDateString(),
            'jam_mulai' => '10:00:00',
            'jam_selesai' => '10:30:00',
            'status' => Booking::STATUS_DIPESAN,
        ]);

        $this->actingAs($admin)
            ->post('/pembayaran', ['id_booking' => $booking->id_booking, 'metode' => 'tunai'])
            ->assertRedirect(route('pembayaran.index'));

        $this->assertDatabaseHas('pembayaran', ['id_booking' => $booking->id_booking, 'jumlah' => 35000]);
        $this->assertSame(Booking::STATUS_SELESAI, $booking->fresh()->status);
    }
}