<?php

namespace App\Services;

use App\Models\BarberSchedule;
use App\Models\Booking;
use App\Models\Service;
use Carbon\Carbon;

class SlotService
{
    const ZONA = 'Asia/Jakarta';
    const LANGKAH_MENIT = 30;

    public static function sekarang(): Carbon
    {
        return Carbon::now(self::ZONA);
    }

    public static function waktu(string $tanggal, string $jam): Carbon
    {
        return Carbon::parse(
            $tanggal . ' ' . $jam,
            self::ZONA
        );
    }

    // Untuk booking satu layanan
    public function slotTersedia(
        int $idBarber,
        int $idService,
        string $tanggal,
        ?int $abaikanBooking = null
    ): array
    {
        $service = Service::find($idService);

        if (!$service) {
            return [];
        }

        return $this->slotTersediaDurasi(
            $idBarber,
            (int) $service->durasi,
            $tanggal,
            $abaikanBooking
        );
    }

    // Menghitung slot berdasarkan total durasi
    public function slotTersediaDurasi(
        int $idBarber,
        int $durasi,
        string $tanggal,
        ?int $abaikanBooking = null
    ): array
    {
        if ($durasi <= 0) {
            return [];
        }

        $tanggal = Carbon::parse(
            $tanggal,
            self::ZONA
        )->format('Y-m-d');

        $hariKe = Carbon::parse(
            $tanggal,
            self::ZONA
        )->dayOfWeekIso;

        $jadwal = BarberSchedule::where('id_barber', $idBarber)
            ->where('hari', $hariKe)
            ->first();

        if (!$jadwal) {
            return [];
        }

        $buka = self::waktu(
            $tanggal,
            $jadwal->jam_mulai
        );

        $tutup = self::waktu(
            $tanggal,
            $jadwal->jam_selesai
        );

        $booking = Booking::where('id_barber', $idBarber)
            ->where('tanggal', $tanggal)
            ->where('status', '!=', Booking::STATUS_BATAL)
            ->when($abaikanBooking, function ($query) use ($abaikanBooking) {
                $query->where('id_booking', '!=', $abaikanBooking);
            })
            ->get();

        $sekarang = self::sekarang();
        $hasil = [];

        for (
            $mulai = $buka->copy();
            $mulai->copy()->addMinutes($durasi) <= $tutup;
            $mulai->addMinutes(self::LANGKAH_MENIT)
        ) {
            $selesai = $mulai->copy()->addMinutes($durasi);

            if ($mulai <= $sekarang) {
                continue;
            }

            $bentrok = $booking->contains(
                function ($b) use ($tanggal, $mulai, $selesai) {

                    $mulaiLama = self::waktu(
                        $tanggal,
                        $b->jam_mulai
                    );

                    $selesaiLama = self::waktu(
                        $tanggal,
                        $b->jam_selesai
                    );

                    return $mulai < $selesaiLama
                        && $selesai > $mulaiLama;
                }
            );

            if (!$bentrok) {
                $hasil[] = [
                    'mulai' => $mulai->format('H:i'),
                    'selesai' => $selesai->format('H:i'),
                ];
            }
        }

        return $hasil;
    }

    // Validasi slot untuk satu layanan
    public function masihTersedia(
        int $idBarber,
        int $idService,
        string $tanggal,
        string $jamMulai,
        ?int $abaikanBooking = null
    ): bool
    {
        foreach (
            $this->slotTersedia(
                $idBarber,
                $idService,
                $tanggal,
                $abaikanBooking
            ) as $slot
        ) {
            if ($slot['mulai'] === $jamMulai) {
                return true;
            }
        }

        return false;
    }

    // Validasi slot berdasarkan total durasi
    public function masihTersediaDurasi(
        int $idBarber,
        int $durasi,
        string $tanggal,
        string $jamMulai,
        ?int $abaikanBooking = null
    ): bool
    {
        foreach (
            $this->slotTersediaDurasi(
                $idBarber,
                $durasi,
                $tanggal,
                $abaikanBooking
            ) as $slot
        ) {
            if ($slot['mulai'] === $jamMulai) {
                return true;
            }
        }

        return false;
    }
}