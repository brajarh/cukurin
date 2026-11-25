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
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    // Form booking
    public function create()
    {
        $outlet = Outlet::orderBy('nama_outlet')->get();

        $service = Service::orderBy('nama_service')->get();

        $barber = Barber::with('service')
            ->orderBy('nama_barber')
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id_barber,
                'nama' => $b->nama_barber,
                'id_outlet' => $b->id_outlet,
                'service' => $b->service->pluck('id_service')->all(),
            ]);

        return view('booking.create', compact(
            'outlet',
            'service',
            'barber'
        ));
    }


    // Menampilkan slot yang tersedia
    public function slot(Request $request, SlotService $slot)
    {
        $d = $request->validate([
            'id_barber' => 'required|integer|exists:barber,id_barber',
            'tanggal' => 'required|date_format:Y-m-d',
            'abaikan' => 'nullable|integer',
        ]);

        // Slot untuk perubahan jadwal booking
        if (!empty($d['abaikan'])) {

            $booking = Booking::where('id_booking', $d['abaikan'])
                ->where('id_user', $request->user()->id)
                ->where('id_barber', $d['id_barber'])
                ->firstOrFail();

            $durasi = $booking->totalDurasi();

            return response()->json([
                'slot' => $slot->slotTersediaDurasi(
                    (int) $d['id_barber'],
                    (int) $durasi,
                    $d['tanggal'],
                    $booking->id_booking
                ),
            ]);
        }

        // Slot untuk booking baru
        $layanan = $request->validate([
            'id_service' => 'required|array|min:1',
            'id_service.*' => 'required|integer|distinct|exists:service,id_service',
        ]);

        $barber = Barber::findOrFail($d['id_barber']);

        $service = $barber->service()
            ->whereIn('service.id_service', $layanan['id_service'])
            ->get();

        // Barber harus menguasai seluruh layanan
        if ($service->count() !== count($layanan['id_service'])) {
            return response()->json(['slot' => []]);
        }

        $durasi = $service->sum('durasi');

        return response()->json([
            'slot' => $slot->slotTersediaDurasi(
                (int) $d['id_barber'],
                (int) $durasi,
                $d['tanggal']
            ),
        ]);
    }


    // Halaman konfirmasi booking
    public function konfirmasi(
        BookingRequest $request,
        SlotService $slot
    )
    {
        $d = $request->validated();

        $layanan = Service::whereIn(
            'id_service',
            $d['id_service']
        )->get();

        if ($pesan = $this->periksa($d, $slot, $layanan)) {
            return redirect()->route('booking.create')
                ->withInput()
                ->withErrors(['jam_mulai' => $pesan]);
        }

        $barber = Barber::with('outlet')
            ->findOrFail($d['id_barber']);

        $totalHarga = $layanan->sum('harga_service');

        $totalDurasi = $layanan->sum('durasi');

        $selesai = SlotService::waktu(
            $d['tanggal'],
            $d['jam_mulai']
        )->addMinutes((int) $totalDurasi);

        return view('booking.konfirmasi', compact(
            'd',
            'barber',
            'layanan',
            'totalHarga',
            'totalDurasi',
            'selesai'
        ));
    }


    // Menyimpan booking header dan detail
    public function store(
        BookingRequest $request,
        SlotService $slot
    )
    {
        $d = $request->validated();

        $idUser = $request->user()->id;

        $booking = DB::transaction(function () use (
            $d,
            $slot,
            $idUser
        ) {

            // Mengunci barber untuk mencegah booking bersamaan
            Barber::where('id_barber', $d['id_barber'])
                ->lockForUpdate()
                ->firstOrFail();

            // Mengambil seluruh layanan yang dipilih
            $layanan = Service::whereIn(
                'id_service',
                $d['id_service']
            )->get();

            // Validasi layanan dan slot
            if ($pesan = $this->periksa($d, $slot, $layanan)) {
                throw ValidationException::withMessages([
                    'jam_mulai' => $pesan
                ])->redirectTo(route('booking.create'));
            }

            // Menghitung total durasi
            $totalDurasi = $layanan->sum('durasi');

            // Menyimpan atau mengambil data customer
            $customer = Customer::firstOrCreate(
                ['no_hp' => $d['no_hp']],
                ['nama' => $d['nama']]
            );

            // Validasi kepemilikan nomor HP
            if (
                !$customer->wasRecentlyCreated &&
                !Booking::where(
                    'id_customer',
                    $customer->id_customer
                )
                    ->where('id_user', $idUser)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'no_hp' => 'Nomor HP sudah terdaftar pada data customer lain.'
                ])->redirectTo(route('booking.create'));
            }

            // INSERT header booking
            $booking = Booking::create([
                'kode_booking' => Booking::buatKode(),
                'id_customer' => $customer->id_customer,
                'id_user' => $idUser,
                'id_barber' => $d['id_barber'],

                // Sementara untuk kompatibilitas sistem lama
                'id_service' => $layanan->first()->id_service,

                'tanggal' => $d['tanggal'],
                'jam_mulai' => $d['jam_mulai'] . ':00',

                'jam_selesai' => SlotService::waktu(
                    $d['tanggal'],
                    $d['jam_mulai']
                )->addMinutes((int) $totalDurasi)
                    ->format('H:i:s'),

                'status' => Booking::STATUS_DIPESAN,
            ]);

            // INSERT seluruh detail layanan
            foreach ($layanan as $service) {

                $booking->detail()->create([
                    'id_service' => $service->id_service,
                    'harga' => $service->harga_service,
                    'durasi' => $service->durasi,
                ]);
            }

            return $booking;
        });

        return redirect()->route(
            'booking.receipt',
            $booking->kode_booking
        )->with(
            'success',
            'Booking berhasil! Simpan kode booking kamu.'
        );
    }


    // Bukti booking
    public function receipt($kode)
    {
        $b = $this->ambil($kode);

        return view('booking.receipt', compact('b'));
    }


    // Riwayat booking pelanggan
    public function riwayat(Request $request)
    {
        $cari = trim((string) $request->query('cari'));

        $data = Booking::with([
            'customer',
            'barber.outlet',
            'service',
            'detail.service'
        ])
            ->where('id_user', $request->user()->id)

            ->when($cari !== '', function ($query) use ($cari) {

                $query->where(function ($w) use ($cari) {

                    $w->where(
                        'kode_booking',
                        strtoupper($cari)
                    )

                    ->orWhereHas('customer', function ($c) use ($cari) {
                        $c->where('no_hp', $cari);
                    });
                });
            })

            ->orderByDesc('tanggal')
            ->orderByDesc('jam_mulai')
            ->get();

        return view('booking.riwayat', compact(
            'data',
            'cari'
        ));
    }


    // Membatalkan booking
    public function batal($kode)
    {
        $b = $this->ambil($kode);

        if ($pesan = $this->tidakBisaDiubah($b)) {

            return redirect()
                ->route('booking.receipt', $kode)
                ->with('error', $pesan);
        }

        $b->update([
            'status' => Booking::STATUS_BATAL
        ]);

        return redirect()
            ->route('booking.receipt', $kode)
            ->with('success', 'Booking berhasil dibatalkan.');
    }


    // Form perubahan jadwal
    public function ubah($kode)
    {
        $b = $this->ambil($kode);

        if ($pesan = $this->tidakBisaDiubah($b)) {

            return redirect()
                ->route('booking.receipt', $kode)
                ->with('error', $pesan);
        }

        return view('booking.ubah', compact('b'));
    }


    // Menyimpan perubahan jadwal
    public function perbarui(
        Request $request,
        $kode,
        SlotService $slot
    )
    {
        $b = $this->ambil($kode);

        if ($pesan = $this->tidakBisaDiubah($b)) {

            return redirect()
                ->route('booking.receipt', $kode)
                ->with('error', $pesan);
        }

        $d = $request->validate([
            'tanggal' => 'required|date_format:Y-m-d',
            'jam_mulai' => 'required|date_format:H:i',
        ], [
            'tanggal.required' => 'Tanggal baru wajib dipilih.',
            'jam_mulai.required' => 'Jam baru wajib dipilih.',
        ]);

        $ok = DB::transaction(function () use ($b, $d, $slot) {

            // Mengunci barber
            Barber::where('id_barber', $b->id_barber)
                ->lockForUpdate()
                ->firstOrFail();

            // Mengambil ulang booking dengan locking
            $b = Booking::lockForUpdate()
                ->findOrFail($b->id_booking);

            if ($this->tidakBisaDiubah($b)) {
                return false;
            }

            // Menggunakan total durasi dari detail booking
            $durasi = (int) $b->totalDurasi();

            if (!$slot->masihTersediaDurasi(
                $b->id_barber,
                $durasi,
                $d['tanggal'],
                $d['jam_mulai'],
                $b->id_booking
            )) {
                return false;
            }

            $b->update([
                'tanggal' => $d['tanggal'],
                'jam_mulai' => $d['jam_mulai'] . ':00',

                'jam_selesai' => SlotService::waktu(
                    $d['tanggal'],
                    $d['jam_mulai']
                )->addMinutes($durasi)
                    ->format('H:i:s'),
            ]);

            return true;
        });

        if (!$ok) {

            return back()
                ->withInput()
                ->withErrors([
                    'jam_mulai' => 'Jam tersebut sudah tidak tersedia, pilih jam lain.'
                ]);
        }

        return redirect()
            ->route('booking.receipt', $kode)
            ->with('success', 'Jadwal berhasil diubah.');
    }


    // ==========================================
    // HELPER PRIVATE
    // ==========================================


    // Mengambil booking berdasarkan pemilik akun
    private function ambil($kode): Booking
    {
        return Booking::with([
            'customer',
            'barber.outlet',
            'service',
            'detail.service'
        ])
            ->where('kode_booking', strtoupper($kode))
            ->where('id_user', auth()->user()->id)
            ->firstOrFail();
    }


    // Validasi layanan, barber dan slot
    private function periksa(
        array $d,
        SlotService $slot,
        $layanan
    ): ?string
    {
        $barber = Barber::with('service')
            ->find($d['id_barber']);

        if (!$barber) {
            return 'Barber tidak ditemukan.';
        }

        // Validasi cabang barber
        if ((int) $barber->id_outlet !== (int) $d['id_outlet']) {
            return 'Barber tidak berada di cabang yang dipilih.';
        }

        // Validasi jumlah layanan
        if ($layanan->count() !== count($d['id_service'])) {
            return 'Ada layanan yang tidak ditemukan.';
        }

        // Validasi semua layanan dikuasai barber
        foreach ($layanan as $service) {

            if (!$barber->service->contains(
                'id_service',
                $service->id_service
            )) {
                return 'Barber tidak melayani semua layanan yang dipilih.';
            }

            if ((int) $service->durasi <= 0) {
                return 'Durasi layanan tidak valid.';
            }
        }

        $totalDurasi = $layanan->sum('durasi');

        // Validasi ketersediaan slot berdasarkan total durasi
        if (!$slot->masihTersediaDurasi(
            (int) $d['id_barber'],
            (int) $totalDurasi,
            $d['tanggal'],
            $d['jam_mulai']
        )) {
            return 'Jam yang dipilih tidak tersedia.';
        }

        return null;
    }


    // Validasi booking yang boleh diubah atau dibatalkan
    private function tidakBisaDiubah(Booking $b): ?string
    {
        if ($b->status !== Booking::STATUS_DIPESAN) {

            return 'Booking dengan status "' .
                $b->status .
                '" tidak bisa diubah atau dibatalkan.';
        }

        $mulai = SlotService::waktu(
            $b->tanggal->format('Y-m-d'),
            $b->jam_mulai
        );

        if ($mulai <= SlotService::sekarang()) {
            return 'Booking yang waktunya sudah lewat tidak bisa diubah atau dibatalkan.';
        }

        return null;
    }
}