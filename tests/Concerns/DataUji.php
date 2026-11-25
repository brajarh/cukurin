<?php

namespace Tests\Concerns;

use App\Models\Barber;
use App\Models\BarberSchedule;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\DetailBooking;
use App\Models\Outlet;
use App\Models\Pembayaran;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\UserRole;
use App\Services\SlotService;

/**
 * Pembantu pembuat data uji untuk tes M3 (Barber, Jadwal) dan Laporan.
 * Disesuaikan dengan skema main: header booking + detail_booking + pembayaran.
 */
trait DataUji
{
    private int $urutanHp = 0;

    protected function siapkanTes(): void
    {
        // layout memakai @vite; di test tidak perlu file hasil build
        $this->withoutVite();
    }

    protected function userDenganRole(string $role): User
    {
        $user = User::factory()->create();
        $r = Role::firstOrCreate(['nama_role' => $role]);
        UserRole::create(['user_id' => $user->id, 'role_id' => $r->id]);

        return $user;
    }

    protected function loginAdmin(): User
    {
        $admin = $this->userDenganRole('admin');
        $this->actingAs($admin);

        return $admin;
    }

    protected function buatOutlet(string $nama = 'Cabang A'): Outlet
    {
        return Outlet::create(['nama_outlet' => $nama, 'alamat_outlet' => 'Jl. Contoh No. 1']);
    }

    protected function buatService(string $nama = 'Potong Rambut', int $harga = 35000, int $durasi = 30): Service
    {
        return Service::create(['nama_service' => $nama, 'harga_service' => $harga, 'durasi' => $durasi]);
    }

    /** Barber + layanan yang dikuasai + jadwal kerja Senin-Sabtu 09:00-17:00 */
    protected function buatBarber(Outlet $outlet, array $services, string $nama = 'Raka'): Barber
    {
        $barber = Barber::create([
            'nama_barber' => $nama,
            'no_hp' => '081234567890',
            'id_outlet' => $outlet->id_outlet,
        ]);
        $barber->service()->sync(collect($services)->pluck('id_service')->all());

        foreach (range(1, 6) as $hari) {
            BarberSchedule::create([
                'id_barber' => $barber->id_barber,
                'hari' => $hari,
                'jam_mulai' => '09:00:00',
                'jam_selesai' => '17:00:00',
            ]);
        }

        return $barber;
    }

    /** Tanggal (Y-m-d) di masa depan */
    protected function tanggalNanti(int $hariKe = 2): string
    {
        return SlotService::sekarang()->startOfDay()->addDays($hariKe)->format('Y-m-d');
    }

    /** Booking (header) + baris detail untuk tiap layanan, tanpa pembayaran */
    protected function buatBooking(Barber $barber, array $services, ?string $tanggal = null, string $jam = '10:00', string $status = Booking::STATUS_DIPESAN, ?User $user = null): Booking
    {
        $services = collect($services);
        $totalDurasi = (int) $services->sum('durasi');
        $tanggal ??= $this->tanggalNanti();
        $this->urutanHp++;

        $customer = Customer::create([
            'nama' => 'Pelanggan ' . $this->urutanHp,
            'no_hp' => '0899' . str_pad((string) $this->urutanHp, 8, '0', STR_PAD_LEFT),
        ]);

        $booking = Booking::create([
            'kode_booking' => Booking::buatKode(),
            'id_customer' => $customer->id_customer,
            'id_user' => ($user ?? User::factory()->create())->id,
            'id_barber' => $barber->id_barber,
            'id_service' => $services->first()->id_service,
            'tanggal' => $tanggal,
            'jam_mulai' => $jam . ':00',
            'jam_selesai' => SlotService::waktu($tanggal, $jam)->addMinutes($totalDurasi)->format('H:i:s'),
            'status' => $status,
        ]);

        foreach ($services as $s) {
            DetailBooking::create([
                'id_booking' => $booking->id_booking,
                'id_service' => $s->id_service,
                'harga' => (int) $s->harga_service,
                'durasi' => (int) $s->durasi,
            ]);
        }

        return $booking;
    }

    /** Catat pembayaran untuk sebuah booking (langsung lewat model, tanpa lewat controller) */
    protected function bayar(Booking $booking, int $jumlah, $dibayarPada = null): Pembayaran
    {
        return Pembayaran::create([
            'id_booking' => $booking->id_booking,
            'id_user' => User::factory()->create()->id,
            'metode' => 'tunai',
            'jumlah' => $jumlah,
            'dibayar_pada' => $dibayarPada ?? SlotService::sekarang(),
        ]);
    }
}
