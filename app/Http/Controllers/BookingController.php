<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Outlet;
use App\Models\Service;
use App\Services\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Sisi customer (tanpa login): form booking -> konfirmasi -> receipt,
 * lalu riwayat, batal, dan ubah jadwal.
 */
class BookingController extends Controller
{
    // Langkah 1: form pilih cabang, layanan, barber, tanggal, jam, biodata
    public function create()
    {
        $outlet = Outlet::orderBy('nama_outlet')->get();
        $service = Service::orderBy('nama_service')->get();
        $barber = Barber::with('service')->orderBy('nama_barber')->get()->map(fn ($b) => [
            'id' => $b->id_barber,
            'nama' => $b->nama_barber,
            'id_outlet' => $b->id_outlet,
            'service' => $b->service->pluck('id_service')->all(),
        ]);

        return view('booking.create', compact('outlet', 'service', 'barber'));
    }

    // Endpoint JSON untuk daftar jam yang masih kosong (dipanggil fetch dari JavaScript)
    public function slot(Request $request, SlotService $slot)
    {
        $d = $request->validate([
            'id_barber' => 'required|integer',
            'id_service' => 'required|integer',
            'tanggal' => 'required|date_format:Y-m-d',
            'abaikan' => 'nullable|integer',
        ]);

        return response()->json([
            'slot' => $slot->slotTersedia(
                (int) $d['id_barber'],
                (int) $d['id_service'],
                $d['tanggal'],
                isset($d['abaikan']) ? (int) $d['abaikan'] : null
            ),
        ]);
    }

    // Langkah 2: tampilkan rincian untuk diperiksa ulang
    public function konfirmasi(BookingRequest $request, SlotService $slot)
    {
        $d = $request->validated();

        if ($pesan = $this->periksa($d, $slot)) {
            return back()->withInput()->withErrors(['jam_mulai' => $pesan]);
        }

        $barber = Barber::with('outlet')->findOrFail($d['id_barber']);
        $service = Service::findOrFail($d['id_service']);
        $selesai = SlotService::waktu($d['tanggal'], $d['jam_mulai'])->addMinutes((int) $service->durasi);

        return view('booking.konfirmasi', compact('d', 'barber', 'service', 'selesai'));
    }

    // Langkah 3: simpan booking (dibungkus transaction supaya tidak ada dua booking di slot yang sama)
    public function store(BookingRequest $request, SlotService $slot)
    {
        $d = $request->validated();

        $booking = DB::transaction(function () use ($d, $slot) {
            // kunci booking barber di tanggal itu, lalu cek ulang ketersediaan
            Booking::where('id_barber', $d['id_barber'])
                ->where('tanggal', $d['tanggal'])
                ->lockForUpdate()
                ->get();

            if ($this->periksa($d, $slot)) {
                return null;
            }

            $service = Service::findOrFail($d['id_service']);
            $customer = Customer::updateOrCreate(
                ['no_hp' => $d['no_hp']],
                ['nama' => $d['nama']]
            );

            return Booking::create([
                'kode_booking' => Booking::buatKode(),
                'id_customer' => $customer->id_customer,
                'id_barber' => $d['id_barber'],
                'id_service' => $d['id_service'],
                'tanggal' => $d['tanggal'],
                'jam_mulai' => $d['jam_mulai'] . ':00',
                'jam_selesai' => SlotService::waktu($d['tanggal'], $d['jam_mulai'])
                    ->addMinutes((int) $service->durasi)->format('H:i:s'),
                'status' => Booking::STATUS_DIPESAN,
            ]);
        });

        if (!$booking) {
            return redirect()->route('booking.create')
                ->withInput()
                ->withErrors(['jam_mulai' => 'Maaf, jam tersebut baru saja dipesan orang lain. Silakan pilih jam lain.']);
        }

        return redirect()->route('booking.receipt', $booking->kode_booking)
            ->with('success', 'Booking berhasil! Simpan kode booking kamu.');
    }

    // Receipt / bukti pemesanan
    public function receipt($kode)
    {
        $b = $this->ambil($kode);
        return view('booking.receipt', compact('b'));
    }

    // Riwayat dan status: cari pakai kode booking atau nomor HP
    public function riwayat(Request $request)
    {
        $cari = trim((string) $request->query('cari'));
        $data = collect();

        if ($cari !== '') {
            $data = Booking::with(['customer', 'barber.outlet', 'service'])
                ->where(function ($w) use ($cari) {
                    $w->where('kode_booking', strtoupper($cari))
                        ->orWhereHas('customer', fn ($c) => $c->where('no_hp', $cari));
                })
                ->orderByDesc('tanggal')
                ->orderByDesc('jam_mulai')
                ->get();
        }

        return view('booking.riwayat', compact('data', 'cari'));
    }

    public function batal($kode)
    {
        $b = $this->ambil($kode);

        if ($pesan = $this->tidakBisaDiubah($b)) {
            return redirect()->route('booking.receipt', $kode)->with('error', $pesan);
        }

        $b->update(['status' => Booking::STATUS_BATAL]);

        return redirect()->route('booking.receipt', $kode)
            ->with('success', 'Booking berhasil dibatalkan.');
    }

    public function ubah($kode)
    {
        $b = $this->ambil($kode);

        if ($pesan = $this->tidakBisaDiubah($b)) {
            return redirect()->route('booking.receipt', $kode)->with('error', $pesan);
        }

        return view('booking.ubah', compact('b'));
    }

    public function perbarui(Request $request, $kode, SlotService $slot)
    {
        $b = $this->ambil($kode);

        if ($pesan = $this->tidakBisaDiubah($b)) {
            return redirect()->route('booking.receipt', $kode)->with('error', $pesan);
        }

        $d = $request->validate([
            'tanggal' => 'required|date_format:Y-m-d',
            'jam_mulai' => 'required|date_format:H:i',
        ], [
            'tanggal.required' => 'Tanggal baru wajib dipilih.',
            'jam_mulai.required' => 'Jam baru wajib dipilih.',
        ]);

        $ok = DB::transaction(function () use ($b, $d, $slot) {
            Booking::where('id_barber', $b->id_barber)
                ->where('tanggal', $d['tanggal'])
                ->lockForUpdate()
                ->get();

            if (!$slot->masihTersedia($b->id_barber, $b->id_service, $d['tanggal'], $d['jam_mulai'], $b->id_booking)) {
                return false;
            }

            $b->update([
                'tanggal' => $d['tanggal'],
                'jam_mulai' => $d['jam_mulai'] . ':00',
                'jam_selesai' => SlotService::waktu($d['tanggal'], $d['jam_mulai'])
                    ->addMinutes((int) $b->service->durasi)->format('H:i:s'),
            ]);
            return true;
        });

        if (!$ok) {
            return back()->withInput()
                ->withErrors(['jam_mulai' => 'Jam tersebut sudah tidak tersedia, pilih jam lain.']);
        }

        return redirect()->route('booking.receipt', $kode)
            ->with('success', 'Jadwal berhasil diubah.');
    }

    // ---------- helper privat ----------

    private function ambil($kode): Booking
    {
        return Booking::with(['customer', 'barber.outlet', 'service'])
            ->where('kode_booking', strtoupper($kode))
            ->firstOrFail();
    }

    // Kembalikan pesan error (string) jika tidak valid, atau null jika aman
    private function periksa(array $d, SlotService $slot): ?string
    {
        $barber = Barber::with('service')->find($d['id_barber']);

        if (!$barber || !$barber->service->contains('id_service', (int) $d['id_service'])) {
            return 'Barber yang dipilih tidak melayani layanan ini.';
        }

        if (!$slot->masihTersedia((int) $d['id_barber'], (int) $d['id_service'], $d['tanggal'], $d['jam_mulai'])) {
            return 'Jam yang dipilih tidak tersedia. Silakan pilih jam lain.';
        }

        return null;
    }

    private function tidakBisaDiubah(Booking $b): ?string
    {
        if ($b->status !== Booking::STATUS_DIPESAN) {
            return 'Booking dengan status "' . $b->status . '" tidak bisa diubah atau dibatalkan.';
        }

        $mulai = SlotService::waktu($b->tanggal->format('Y-m-d'), $b->jam_mulai);
        if ($mulai <= SlotService::sekarang()) {
            return 'Booking yang waktunya sudah lewat tidak bisa diubah atau dibatalkan.';
        }

        return null;
    }
}
