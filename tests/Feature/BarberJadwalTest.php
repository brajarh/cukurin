<?php

namespace Tests\Feature;

use App\Models\Barber;
use App\Models\BarberSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DataUji;
use Tests\TestCase;

/**
 * Tes M3: data master Barber (beserta jadwal kerjanya).
 * Mencakup RBAC, CRUD, validasi, pencarian, pagination, dan pencegahan hapus.
 */
class BarberJadwalTest extends TestCase
{
    use RefreshDatabase, DataUji;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanTes();
    }

    // ---------------- RBAC ----------------
    public function test_guest_dialihkan_ke_login(): void
    {
        $this->get('/barber')->assertRedirect('/login');
        $this->get('/jadwal')->assertRedirect('/login');
    }

    public function test_pelanggan_tidak_boleh_membuka_barber_dan_jadwal(): void
    {
        $this->actingAs($this->userDenganRole('pelanggan'));

        $this->get('/barber')->assertForbidden();
        $this->get('/jadwal')->assertForbidden();
        $this->post('/barber', [])->assertForbidden();
    }

    // ---------------- BARBER ----------------
    public function test_admin_melihat_daftar_barber(): void
    {
        $this->loginAdmin();
        $this->buatBarber($this->buatOutlet('Pusat'), [$this->buatService()], 'Raka');

        $this->get('/barber')->assertOk()->assertSee('Raka')->assertSee('Pusat');
    }

    public function test_daftar_barber_dipaginasi_10_per_halaman(): void
    {
        $this->loginAdmin();
        $o = $this->buatOutlet();
        $s = $this->buatService();
        foreach (range(1, 12) as $i) {
            $this->buatBarber($o, [$s], 'Barber ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT));
        }

        $hal1 = $this->get('/barber')->assertOk()->viewData('data');
        $this->assertSame(12, $hal1->total());
        $this->assertCount(10, $hal1->items());

        $hal2 = $this->get('/barber?page=2')->assertOk()->viewData('data');
        $this->assertCount(2, $hal2->items());
    }

    public function test_pagination_barber_membawa_kata_kunci_pencarian(): void
    {
        $this->loginAdmin();
        $o = $this->buatOutlet();
        $s = $this->buatService();
        foreach (range(1, 11) as $i) {
            $this->buatBarber($o, [$s], 'Cukur ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT));
        }

        $data = $this->get('/barber?q=Cukur')->viewData('data');

        $this->assertStringContainsString('q=Cukur', $data->nextPageUrl());
    }

    public function test_pencarian_barber_tidak_peduli_huruf_besar_kecil(): void
    {
        $this->loginAdmin();
        $o = $this->buatOutlet();
        $s = $this->buatService();
        $this->buatBarber($o, [$s], 'Raka');
        $this->buatBarber($o, [$s], 'Fajar');

        $data = $this->get('/barber?q=raka')->viewData('data');

        $this->assertSame(['Raka'], $data->pluck('nama_barber')->all());
    }

    public function test_filter_cabang_pada_daftar_barber(): void
    {
        $this->loginAdmin();
        $o1 = $this->buatOutlet('Pusat');
        $o2 = $this->buatOutlet('Timur');
        $s = $this->buatService();
        $this->buatBarber($o1, [$s], 'Raka');
        $this->buatBarber($o2, [$s], 'Fajar');

        $data = $this->get("/barber?id_outlet={$o2->id_outlet}")->viewData('data');

        $this->assertSame(['Fajar'], $data->pluck('nama_barber')->all());
    }

    public function test_tambah_barber_menyimpan_layanan_di_pivot(): void
    {
        $this->loginAdmin();
        $o = $this->buatOutlet();
        $a = $this->buatService('A');
        $b = $this->buatService('B');

        $this->post('/barber', [
            'nama_barber' => 'Dimas', 'no_hp' => '081234567890', 'id_outlet' => $o->id_outlet,
            'service_ids' => [$a->id_service, $b->id_service],
        ])->assertRedirect('/barber');

        $barber = Barber::where('nama_barber', 'Dimas')->firstOrFail();
        $this->assertEqualsCanonicalizing(
            [$a->id_service, $b->id_service],
            $barber->service()->pluck('service.id_service')->all()
        );
    }

    public function test_validasi_barber_menampilkan_pesan_error(): void
    {
        $this->loginAdmin();

        $this->post('/barber', ['nama_barber' => '', 'no_hp' => 'abc', 'id_outlet' => 999])
            ->assertSessionHasErrors(['nama_barber', 'no_hp', 'id_outlet']);

        $this->assertSame(0, Barber::count());
    }

    public function test_ubah_barber_menyinkronkan_ulang_layanan(): void
    {
        $this->loginAdmin();
        $o = $this->buatOutlet();
        $a = $this->buatService('A');
        $b = $this->buatService('B');
        $barber = $this->buatBarber($o, [$a]);

        $this->put("/barber/{$barber->id_barber}", [
            'nama_barber' => 'Raka Baru', 'no_hp' => '081234567890', 'id_outlet' => $o->id_outlet,
            'service_ids' => [$b->id_service],
        ])->assertRedirect('/barber');

        $this->assertSame('Raka Baru', $barber->fresh()->nama_barber);
        $this->assertSame([$b->id_service], $barber->service()->pluck('service.id_service')->all());
    }

    public function test_detail_barber_menampilkan_layanan_dan_jadwal(): void
    {
        $this->loginAdmin();
        $s = $this->buatService('Potong Rambut', 35000, 30);
        $barber = $this->buatBarber($this->buatOutlet(), [$s]);

        $this->get("/barber/{$barber->id_barber}")->assertOk()
            ->assertSee('Potong Rambut')->assertSee('Rp 35.000')->assertSee('Senin')->assertSee('09:00');
    }

    public function test_hapus_barber_tanpa_booking_menghapus_jadwal_dan_pivot(): void
    {
        $this->loginAdmin();
        $barber = $this->buatBarber($this->buatOutlet(), [$this->buatService()]);

        $this->delete("/barber/{$barber->id_barber}")->assertSessionHas('success');

        $this->assertDatabaseMissing('barber', ['id_barber' => $barber->id_barber]);
        $this->assertSame(0, BarberSchedule::count());
        $this->assertDatabaseCount('barberservice', 0);
    }

    public function test_hapus_barber_yang_sudah_punya_booking_dicegah(): void
    {
        $this->loginAdmin();
        $s = $this->buatService();
        $barber = $this->buatBarber($this->buatOutlet(), [$s]);
        $this->buatBooking($barber, [$s]);

        $this->delete("/barber/{$barber->id_barber}")->assertSessionHas('error');

        $this->assertDatabaseHas('barber', ['id_barber' => $barber->id_barber]);
    }

    // ---------------- JADWAL ----------------
    public function test_tambah_jadwal_dan_tolak_hari_kembar_untuk_barber_yang_sama(): void
    {
        $this->loginAdmin();
        $barber = $this->buatBarber($this->buatOutlet(), [$this->buatService()]);
        BarberSchedule::where('id_barber', $barber->id_barber)->delete();

        $data = ['id_barber' => $barber->id_barber, 'hari' => 1, 'jam_mulai' => '09:00', 'jam_selesai' => '17:00'];
        $this->post('/jadwal', $data)->assertRedirect('/jadwal');
        $this->post('/jadwal', $data)->assertSessionHasErrors('hari');

        $this->assertSame(1, BarberSchedule::count());
    }

    public function test_jam_selesai_harus_lebih_besar_dari_jam_mulai(): void
    {
        $this->loginAdmin();
        $barber = $this->buatBarber($this->buatOutlet(), [$this->buatService()]);
        BarberSchedule::where('id_barber', $barber->id_barber)->delete();

        $this->post('/jadwal', ['id_barber' => $barber->id_barber, 'hari' => 2, 'jam_mulai' => '17:00', 'jam_selesai' => '09:00'])
            ->assertSessionHasErrors('jam_selesai');
    }

    public function test_ubah_jadwal_tidak_dianggap_kembar_dengan_dirinya_sendiri(): void
    {
        $this->loginAdmin();
        $barber = $this->buatBarber($this->buatOutlet(), [$this->buatService()]);
        $j = BarberSchedule::where('id_barber', $barber->id_barber)->where('hari', 1)->first();

        $this->put("/jadwal/{$j->id_schedule}", ['id_barber' => $barber->id_barber, 'hari' => 1, 'jam_mulai' => '10:00', 'jam_selesai' => '18:00'])
            ->assertRedirect('/jadwal');

        // SQLite menyimpan "10:00", PostgreSQL "10:00:00": bandingkan 5 karakter pertama
        $this->assertSame('10:00', substr(BarberSchedule::find($j->id_schedule)->jam_mulai, 0, 5));
        $this->assertSame('18:00', substr(BarberSchedule::find($j->id_schedule)->jam_selesai, 0, 5));
    }

    public function test_hapus_jadwal(): void
    {
        $this->loginAdmin();
        $barber = $this->buatBarber($this->buatOutlet(), [$this->buatService()]);
        $j = BarberSchedule::where('id_barber', $barber->id_barber)->first();

        $this->delete("/jadwal/{$j->id_schedule}")->assertSessionHas('success');

        $this->assertDatabaseMissing('barber_schedule', ['id_schedule' => $j->id_schedule]);
    }

    public function test_daftar_jadwal_bisa_difilter_per_barber(): void
    {
        $this->loginAdmin();
        $o = $this->buatOutlet();
        $s = $this->buatService();
        $a = $this->buatBarber($o, [$s], 'Raka');
        $this->buatBarber($o, [$s], 'Fajar');

        $data = $this->get("/jadwal?id_barber={$a->id_barber}")->assertOk()->viewData('data');

        $this->assertSame(6, $data->total());
        $this->assertTrue($data->every(fn ($j) => $j->id_barber === $a->id_barber));
    }

    public function test_daftar_jadwal_bisa_dicari_dengan_nama_barber(): void
    {
        $this->loginAdmin();
        $o = $this->buatOutlet();
        $s = $this->buatService();
        $a = $this->buatBarber($o, [$s], 'Raka');
        $this->buatBarber($o, [$s], 'Fajar');

        $data = $this->get('/jadwal?q=raka')->viewData('data');

        $this->assertSame(6, $data->total());
        $this->assertTrue($data->every(fn ($j) => $j->id_barber === $a->id_barber));
    }

    public function test_daftar_jadwal_dipaginasi_10_per_halaman(): void
    {
        $this->loginAdmin();
        $o = $this->buatOutlet();
        $s = $this->buatService();
        $this->buatBarber($o, [$s], 'Raka');
        $this->buatBarber($o, [$s], 'Fajar'); // 2 barber x 6 hari = 12 jadwal

        $hal1 = $this->get('/jadwal')->assertOk()->viewData('data');
        $this->assertSame(12, $hal1->total());
        $this->assertCount(10, $hal1->items());
        $this->assertCount(2, $this->get('/jadwal?page=2')->viewData('data')->items());
    }
}
