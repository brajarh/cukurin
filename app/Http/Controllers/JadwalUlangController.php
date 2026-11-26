<?php

namespace App\Http\Controllers;

use App\Http\Requests\UbahJadwalRequest;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\PerubahanJadwal;
use App\Services\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Transaksi T5: Perubahan Jadwal Booking (reschedule).
 *
 * Pelanggan memindahkan booking miliknya ke tanggal/jam lain beserta alasannya.
 * Setiap perubahan dicatat di tabel perubahan_jadwal (pelaku, waktu, jadwal lama dan baru)
 * dan sekaligus mengubah data lain (tanggal dan jam booking), dalam satu DB::transaction().
 * Admin hanya dapat melihat riwayatnya.
 */
class JadwalUlangController extends Controller
{
    // Status booking yang masih boleh dipindah jadwalnya.
    // Kalau alur status berubah (T2), cukup sesuaikan daftar ini.
    private const STATUS_BOLEH_UBAH = [Booking::STATUS_DIPESAN];

    // Form ubah jadwal (pelanggan)
    public function ubah($kode)
    {
        $b = $this->ambil($kode);

        if ($pesan = $this->tidakBisaDiubah($b)) {
            return redirect()->route('booking.receipt', $kode)->with('error', $pesan);
        }

        $riwayat = PerubahanJadwal::where('id_booking', $b->id_booking)
            ->latest()
            ->get();

        return view('jadwal_ulang.ubah', compact('b', 'riwayat'));
    }

    // Menyimpan perubahan jadwal + mencatat log
    public function perbarui(UbahJadwalRequest $request, $kode, SlotService $slot)
    {
        $b = $this->ambil($kode);

        if ($pesan = $this->tidakBisaDiubah($b)) {
            return redirect()->route('booking.receipt', $kode)->with('error', $pesan);
        }

        $d = $request->validated();

        if ($b->tanggal->format('Y-m-d') === $d['tanggal']
            && substr($b->jam_mulai, 0, 5) === $d['jam_mulai']) {
            return back()->withInput()->withErrors([
                'jam_mulai' => 'Jadwal baru sama dengan jadwal sekarang.',
            ]);
        }

        $hasil = DB::transaction(function () use ($b, $d, $slot, $request) {
            // kunci barber supaya dua pemesanan tidak berebut slot yang sama
            Barber::where('id_barber', $b->id_barber)->lockForUpdate()->firstOrFail();

            $b = Booking::with('detail')->lockForUpdate()->findOrFail($b->id_booking);

            if ($this->tidakBisaDiubah($b)) {
                return 'status';
            }

            $durasi = (int) $b->totalDurasi();

            if (!$slot->masihTersediaDurasi($b->id_barber, $durasi, $d['tanggal'], $d['jam_mulai'], $b->id_booking)) {
                return 'slot';
            }

            $jamSelesaiBaru = SlotService::waktu($d['tanggal'], $d['jam_mulai'])
                ->addMinutes($durasi)
                ->format('H:i:s');

            // 1) catat log (jadwal lama diambil sebelum booking diubah)
            PerubahanJadwal::create([
                'id_booking' => $b->id_booking,
                'id_user' => $request->user()->id,
                'tanggal_lama' => $b->tanggal->format('Y-m-d'),
                'jam_mulai_lama' => $b->jam_mulai,
                'jam_selesai_lama' => $b->jam_selesai,
                'tanggal_baru' => $d['tanggal'],
                'jam_mulai_baru' => $d['jam_mulai'] . ':00',
                'jam_selesai_baru' => $jamSelesaiBaru,
                'alasan' => $d['alasan'],
            ]);

            // 2) ubah data lain: jadwal pada booking
            $b->update([
                'tanggal' => $d['tanggal'],
                'jam_mulai' => $d['jam_mulai'] . ':00',
                'jam_selesai' => $jamSelesaiBaru,
            ]);

            return 'ok';
        });

        if ($hasil === 'status') {
            return redirect()->route('booking.receipt', $kode)
                ->with('error', 'Booking ini sudah tidak bisa diubah jadwalnya.');
        }

        if ($hasil === 'slot') {
            return back()->withInput()->withErrors([
                'jam_mulai' => 'Jam tersebut sudah tidak tersedia, pilih jam lain.',
            ]);
        }

        return redirect()->route('booking.receipt', $kode)
            ->with('success', 'Jadwal berhasil diubah.');
    }

    // Riwayat semua perubahan jadwal (admin, hanya baca)
    public function riwayat(Request $request)
    {
        $cari = trim((string) $request->query('q'));

        $data = PerubahanJadwal::with(['booking.barber', 'user'])
            ->when($cari !== '', function ($q) use ($cari) {
                $q->where(function ($w) use ($cari) {
                    $w->whereHas('booking', fn ($b) => $b->whereLike('kode_booking', "%{$cari}%"))
                        ->orWhereHas('user', fn ($u) => $u->whereLike('name', "%{$cari}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.perubahan_jadwal.index', compact('data', 'cari'));
    }

    // Booking milik pelanggan yang sedang login (404 kalau milik orang lain)
    private function ambil($kode): Booking
    {
        return Booking::with(['customer', 'barber.outlet', 'service', 'detail.service'])
            ->where('kode_booking', strtoupper($kode))
            ->where('id_user', auth()->user()->id)
            ->firstOrFail();
    }

    private function tidakBisaDiubah(Booking $b): ?string
    {
        if (!in_array($b->status, self::STATUS_BOLEH_UBAH, true)) {
            return 'Booking dengan status "' . $b->status . '" tidak bisa diubah jadwalnya.';
        }

        $mulai = SlotService::waktu($b->tanggal->format('Y-m-d'), $b->jam_mulai);

        if ($mulai <= SlotService::sekarang()) {
            return 'Booking yang waktunya sudah lewat tidak bisa diubah jadwalnya.';
        }

        return null;
    }
}
