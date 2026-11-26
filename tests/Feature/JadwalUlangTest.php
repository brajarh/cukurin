<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\PerubahanJadwal;
use App\Services\SlotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\DataUji;
use Tests\TestCase;

/**
 * Tes T5: Perubahan Jadwal Booking (reschedule) beserta log perubahan_jadwal.
 */
class JadwalUlangTest extends TestCase
{
    use RefreshDatabase, DataUji;

    private $pelanggan;
    private $barber;
    private $services;
    private $booking;

    protected function setUp(): void
    {
        parent::setUp();
        $this->siapkanTes();

        $outlet = $this->buatOutlet();
        $potong = $this->buatService('Potong', 35000, 30);
        $jenggot = $this->buatService('Jenggot', 20000, 30);
        $this->services = [$potong, $jenggot]; // total 60 menit
        $this->barber = $this->buatBarber($outlet, $this->services, 'Raka');

        $this->pelanggan = $this->userDenganRole('pelanggan');
        $this->booking = $this->buatBooking($this->barber, $this->services, $this->hariKerja(1), '10:00', Booking::STATUS_DIPESAN, $this->pelanggan);
    }

    /** Tanggal (Y-m-d) mulai besok + $geser hari, melewati hari Minggu (barber libur) */
    private function hariKerja(int $geser = 1): string
    {
        $t = SlotService::sekarang()->startOfDay()->addDays($geser);
        while ($t->dayOfWeekIso === 7) {
            $t->addDay();
        }

        return $t->format('Y-m-d');
    }

    private function kirim(array $data = [], ?Booking $booking = null)
    {
        $booking ??= $this->booking;

        return $this->put("/booking/{$booking->kode_booking}", array_merge([
            'tanggal' => $this->hariKerja(3),
            'jam_mulai' => '13:00',
            'alasan' => 'Ada keperluan keluarga',
        ], $data));
    }

    // ---------------- RBAC ----------------
    public function test_guest_dialihkan_ke_login(): void
    {
        $this->get("/booking/{$this->booking->kode_booking}/ubah")->assertRedirect('/login');
        $this->put("/booking/{$this->booking->kode_booking}", [])->assertRedirect('/login');
    }

    public function test_admin_tidak_boleh_mengubah_jadwal_lewat_halaman_pelanggan(): void
    {
        $this->loginAdmin();

        $this->get("/booking/{$this->booking->kode_booking}/ubah")->assertForbidden();
        $this->kirim()->assertForbidden();
    }

    public function test_pelanggan_tidak_bisa_mengubah_booking_milik_orang_lain(): void
    {
        $this->actingAs($this->userDenganRole('pelanggan'));

        $this->get("/booking/{$this->booking->kode_booking}/ubah")->assertNotFound();
        $this->kirim()->assertNotFound();

        $this->assertSame(0, PerubahanJadwal::count());
    }

    // ---------------- Alur utama ----------------
    public function test_pemilik_bisa_membuka_form_ubah_jadwal(): void
    {
        $this->actingAs($this->pelanggan);

        $this->get("/booking/{$this->booking->kode_booking}/ubah")->assertOk()
            ->assertSee($this->booking->kode_booking)->assertSee('Alasan perubahan');
    }

    public function test_perubahan_jadwal_mengubah_booking_dan_mencatat_log(): void
    {
        $this->actingAs($this->pelanggan);
        $tanggalLama = $this->booking->tanggal->format('Y-m-d');
        $tanggalBaru = $this->hariKerja(3);

        $this->kirim(['tanggal' => $tanggalBaru, 'jam_mulai' => '13:00', 'alasan' => 'Ada keperluan keluarga'])
            ->assertRedirect(route('booking.receipt', $this->booking->kode_booking))
            ->assertSessionHas('success');

        $b = $this->booking->fresh();
        $this->assertSame($tanggalBaru, $b->tanggal->format('Y-m-d'));
        $this->assertSame('13:00', substr($b->jam_mulai, 0, 5));
        $this->assertSame('14:00', substr($b->jam_selesai, 0, 5)); // durasi 2 layanan = 60 menit

        $log = PerubahanJadwal::firstOrFail();
        $this->assertSame($b->id_booking, $log->id_booking);
        $this->assertSame($this->pelanggan->id, $log->id_user);
        $this->assertSame($tanggalLama, $log->tanggal_lama->format('Y-m-d'));
        $this->assertSame('10:00', substr($log->jam_mulai_lama, 0, 5));
        $this->assertSame('11:00', substr($log->jam_selesai_lama, 0, 5));
        $this->assertSame($tanggalBaru, $log->tanggal_baru->format('Y-m-d'));
        $this->assertSame('13:00', substr($log->jam_mulai_baru, 0, 5));
        $this->assertSame('14:00', substr($log->jam_selesai_baru, 0, 5));
        $this->assertSame('Ada keperluan keluarga', $log->alasan);
        $this->assertNotNull($log->created_at);
    }

    public function test_jadwal_booking_sendiri_tidak_dianggap_bentrok(): void
    {
        $this->actingAs($this->pelanggan);

        // 10:00-11:00 digeser ke 10:30-11:30, hari sama: beririsan dengan dirinya sendiri, harus tetap boleh
        $this->kirim(['tanggal' => $this->booking->tanggal->format('Y-m-d'), 'jam_mulai' => '10:30'])
            ->assertSessionHas('success');

        $this->assertSame('11:30', substr($this->booking->fresh()->jam_selesai, 0, 5));
    }

    public function test_form_menampilkan_riwayat_perubahan_booking_itu(): void
    {
        $this->actingAs($this->pelanggan);
        $this->kirim(['alasan' => 'Macet di jalan tol']);

        $this->get("/booking/{$this->booking->kode_booking}/ubah")->assertOk()->assertSee('Macet di jalan tol');
    }

    // ---------------- Penolakan (tanpa log) ----------------
    public function test_jam_yang_bentrok_dengan_booking_lain_ditolak_dan_tidak_ada_log(): void
    {
        $this->actingAs($this->pelanggan);
        $tanggalBaru = $this->hariKerja(3);
        $this->buatBooking($this->barber, $this->services, $tanggalBaru, '13:00'); // 13:00-14:00 milik orang lain

        $this->kirim(['tanggal' => $tanggalBaru, 'jam_mulai' => '13:30'])->assertSessionHasErrors('jam_mulai');

        $this->assertSame(0, PerubahanJadwal::count());
        $this->assertSame('10:00', substr($this->booking->fresh()->jam_mulai, 0, 5));
    }

    public function test_jam_di_luar_jam_kerja_atau_melebihi_jam_tutup_ditolak(): void
    {
        $this->actingAs($this->pelanggan);

        // 16:30 + 60 menit = 17:30, melewati jam tutup 17:00
        $this->kirim(['jam_mulai' => '16:30'])->assertSessionHasErrors('jam_mulai');
        $this->assertSame(0, PerubahanJadwal::count());
    }

    public function test_jadwal_baru_sama_dengan_jadwal_lama_ditolak(): void
    {
        $this->actingAs($this->pelanggan);

        $this->kirim(['tanggal' => $this->booking->tanggal->format('Y-m-d'), 'jam_mulai' => '10:00'])
            ->assertSessionHasErrors('jam_mulai');

        $this->assertSame(0, PerubahanJadwal::count());
    }

    public function test_booking_dengan_status_selain_dipesan_tidak_bisa_diubah(): void
    {
        $this->actingAs($this->pelanggan);
        foreach ([Booking::STATUS_SELESAI, Booking::STATUS_BATAL] as $i => $status) {
            $b = $this->buatBooking($this->barber, $this->services, $this->hariKerja(4 + $i), '10:00', $status, $this->pelanggan);

            $this->get("/booking/{$b->kode_booking}/ubah")->assertRedirect(route('booking.receipt', $b->kode_booking))->assertSessionHas('error');
            $this->kirim([], $b)->assertSessionHas('error');
        }

        $this->assertSame(0, PerubahanJadwal::count());
    }

    public function test_booking_yang_waktunya_sudah_lewat_tidak_bisa_diubah(): void
    {
        $this->actingAs($this->pelanggan);
        $kemarin = SlotService::sekarang()->subDay()->format('Y-m-d');
        $b = $this->buatBooking($this->barber, $this->services, $kemarin, '10:00', Booking::STATUS_DIPESAN, $this->pelanggan);

        $this->kirim([], $b)->assertSessionHas('error');

        $this->assertSame(0, PerubahanJadwal::count());
    }

    public function test_validasi_input_menampilkan_pesan_error(): void
    {
        $this->actingAs($this->pelanggan);

        $this->kirim(['alasan' => ''])->assertSessionHasErrors('alasan');
        $this->kirim(['alasan' => 'abc'])->assertSessionHasErrors('alasan');
        $this->kirim(['tanggal' => '26-11-2026'])->assertSessionHasErrors('tanggal');
        $this->kirim(['jam_mulai' => ''])->assertSessionHasErrors('jam_mulai');

        $this->assertSame(0, PerubahanJadwal::count());
    }

    // ---------------- Riwayat admin ----------------
    public function test_admin_melihat_riwayat_perubahan_jadwal(): void
    {
        $this->actingAs($this->pelanggan);
        $this->kirim(['alasan' => 'Ada rapat mendadak']);
        $this->actingAs($this->userDenganRole('admin'));

        $this->get('/admin/perubahan-jadwal')->assertOk()
            ->assertSee($this->booking->kode_booking)->assertSee('Ada rapat mendadak')->assertSee($this->pelanggan->name);
    }

    public function test_riwayat_perubahan_jadwal_tertutup_untuk_guest_dan_pelanggan(): void
    {
        $this->get('/admin/perubahan-jadwal')->assertRedirect('/login');

        $this->actingAs($this->pelanggan);
        $this->get('/admin/perubahan-jadwal')->assertForbidden();
    }

    public function test_riwayat_bisa_dicari_dan_dipaginasi(): void
    {
        $this->loginAdmin();
        foreach (range(1, 12) as $i) {
            PerubahanJadwal::create([
                'id_booking' => $this->booking->id_booking,
                'id_user' => $this->pelanggan->id,
                'tanggal_lama' => '2026-12-01', 'jam_mulai_lama' => '10:00:00', 'jam_selesai_lama' => '11:00:00',
                'tanggal_baru' => '2026-12-02', 'jam_mulai_baru' => '10:00:00', 'jam_selesai_baru' => '11:00:00',
                'alasan' => 'Alasan nomor ' . $i,
            ]);
        }

        $hal1 = $this->get('/admin/perubahan-jadwal')->viewData('data');
        $this->assertSame(12, $hal1->total());
        $this->assertCount(10, $hal1->items());

        $cari = $this->get('/admin/perubahan-jadwal?q=' . strtolower($this->booking->kode_booking))->viewData('data');
        $this->assertSame(12, $cari->total());

        $kosong = $this->get('/admin/perubahan-jadwal?q=TIDAKADA')->viewData('data');
        $this->assertSame(0, $kosong->total());
    }
}
