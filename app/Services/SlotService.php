<?php

namespace App\Services;

use App\Models\BarberSchedule;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;

/**
 * Logika inti booking: menghitung jam yang masih kosong.
 *
 * Aturan:
 *  1. Hanya jam di dalam jadwal kerja barber pada hari itu.
 *  2. Satu slot = jam mulai + durasi layanan harus muat sebelum jam selesai kerja.
 *  3. Slot tidak boleh bentrok dengan booking lain milik barber yang sama (status bukan batal).
 *  4. Jam yang sudah lewat tidak ditampilkan.
 */
class SlotService
{
    const ZONA = 'Asia/Jakarta';
    const LANGKAH_MENIT = 30; // jarak antar pilihan jam mulai

    public static function sekarang(): Carbon
    {
        return Carbon::now(self::ZONA);
    }

    public static function waktu(string $tanggal, string $jam): Carbon
    {
        return Carbon::parse($tanggal . ' ' . $jam, self::ZONA);
    }

    /**
     * @return array<int, array{mulai:string, selesai:string}>
     */
    public function slotTersedia(int $idBarber, int $idService, string $tanggal, ?int $abaikanBooking = null): array
    {
        $service = Service::find($idService);
        if (!$service) {
            return [];
        }

        $durasi = (int) $service->durasi;
        if ($durasi <= 0) {
            return [];
        }

        $tanggal = Carbon::parse($tanggal, self::ZONA)->format('Y-m-d');
        $hariKe = Carbon::parse($tanggal, self::ZONA)->dayOfWeekIso;

        $jadwal = BarberSchedule::where('id_barber', $idBarber)
            ->where('hari', $hariKe)
            ->first();
        if (!$jadwal) {
            return [];
        }

        $buka = self::waktu($tanggal, $jadwal->jam_mulai);
        $tutup = self::waktu($tanggal, $jadwal->jam_selesai);

        $booking = Booking::where('id_barber', $idBarber)
            ->where('tanggal', $tanggal)
            ->where('status', '!=', Booking::STATUS_BATAL)
            ->when($abaikanBooking, fn ($q) => $q->where('id_booking', '!=', $abaikanBooking))
            ->get();

        $sekarang = self::sekarang();
        $hasil = [];

        for ($mulai = $buka->copy(); $mulai->copy()->addMinutes($durasi) <= $tutup; $mulai->addMinutes(self::LANGKAH_MENIT)) {
            $selesai = $mulai->copy()->addMinutes($durasi);

            if ($mulai <= $sekarang) {
                continue;
            }

            // dua rentang waktu bentrok jika: mulaiBaru < selesaiLama DAN selesaiBaru > mulaiLama
            $bentrok = $booking->contains(function ($b) use ($tanggal, $mulai, $selesai) {
                $mulaiLama = self::waktu($tanggal, $b->jam_mulai);
                $selesaiLama = self::waktu($tanggal, $b->jam_selesai);
                return $mulai < $selesaiLama && $selesai > $mulaiLama;
            });

            if (!$bentrok) {
                $hasil[] = [
                    'mulai' => $mulai->format('H:i'),
                    'selesai' => $selesai->format('H:i'),
                ];
            }
        }

        return $hasil;
    }

    public function masihTersedia(int $idBarber, int $idService, string $tanggal, string $jamMulai, ?int $abaikanBooking = null): bool
    {
        foreach ($this->slotTersedia($idBarber, $idService, $tanggal, $abaikanBooking) as $slot) {
            if ($slot['mulai'] === $jamMulai) {
                return true;
            }
        }
        return false;
    }
}
