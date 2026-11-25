<?php

namespace App\Http\Controllers;

use App\Http\Requests\PembayaranRequest;
use App\Models\Booking;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $cari = $request->query('q');

        $data = Pembayaran::with(['booking.customer', 'booking.service', 'kasir'])
            ->when($cari, fn ($qr) => $qr->whereHas('booking',
                fn ($b) => $b->where('kode_booking', 'like', "%{$cari}%")))
            ->orderByDesc('dibayar_pada')
            ->paginate(10)
            ->withQueryString();

        $belumBayar = Booking::with(['customer', 'barber', 'service'])
            ->where('status', Booking::STATUS_DIPESAN)
            ->doesntHave('pembayaran')
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get();

        return view('pembayaran.index', compact('data', 'belumBayar', 'cari'));
    }

    public function create($idBooking)
    {
        $b = Booking::with(['customer', 'barber', 'service'])->findOrFail($idBooking);

        if ($b->status !== Booking::STATUS_DIPESAN || $b->pembayaran()->exists()) {
            return redirect()->route('pembayaran.index')
                ->with('error', 'Booking ini tidak bisa dibayar.');
        }

        return view('pembayaran.create', compact('b'));
    }

    public function store(PembayaranRequest $request)
    {
        $d = $request->validated();

        try {
            DB::transaction(function () use ($d) {
                $b = Booking::with('service')->lockForUpdate()->findOrFail($d['id_booking']);

                if ($b->status !== Booking::STATUS_DIPESAN) {
                    throw new \DomainException('Hanya booking berstatus dipesan yang bisa dibayar.');
                }
                if ($b->pembayaran()->exists()) {
                    throw new \DomainException('Booking ini sudah dibayar.');
                }

                Pembayaran::create([
                    'id_booking' => $b->id_booking,
                    'id_user' => auth()->id(),
                    'metode' => $d['metode'],
                    'jumlah' => $b->service->harga_service,
                    'dibayar_pada' => now(),
                ]);

                $b->update(['status' => Booking::STATUS_SELESAI]);
            });
        } catch (\DomainException $e) {
            return redirect()->route('pembayaran.index')->with('error', $e->getMessage());
        }

        return redirect()->route('pembayaran.index')
            ->with('success', 'Pembayaran tercatat dan booking diselesaikan.');
    }
}