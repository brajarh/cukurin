<?php

namespace App\Http\Controllers;

use App\Http\Requests\UlasanRequest;
use App\Models\Barber;
use App\Models\Booking;
use App\Models\Ulasan;
use Illuminate\Support\Facades\DB;

class UlasanController extends Controller
{
    public function index()
    {
        $data = Ulasan::with(['barber', 'pelanggan', 'booking'])
            ->orderByDesc('id_ulasan')
            ->paginate(10);

        return view('ulasan.index', compact('data'));
    }

    public function create($idBooking)
    {
        $b = $this->bookingMilikSaya($idBooking);

        if ($b->ulasan()->exists()) {
            return redirect()->route('home')->with('error', 'Booking ini sudah diulas.');
        }

        return view('ulasan.create', compact('b'));
    }

    public function store(UlasanRequest $request)
    {
        $d = $request->validated();
        $b = $this->bookingMilikSaya($d['id_booking']);

        if ($b->ulasan()->exists()) {
            return redirect()->route('home')->with('error', 'Booking ini sudah diulas.');
        }

        DB::transaction(function () use ($d, $b) {
            Ulasan::create([
                'id_booking' => $b->id_booking,
                'id_user' => auth()->id(),
                'id_barber' => $b->id_barber,
                'rating' => $d['rating'],
                'komentar' => $d['komentar'] ?? null,
            ]);

            Barber::where('id_barber', $b->id_barber)->update([
                'rating_rata' => round(Ulasan::where('id_barber', $b->id_barber)->avg('rating'), 2),
                'jumlah_ulasan' => Ulasan::where('id_barber', $b->id_barber)->count(),
            ]);
        });

        return redirect()->route('home')->with('success', 'Terima kasih atas ulasanmu!');
    }

    private function bookingMilikSaya($id): Booking
    {
        $b = Booking::with(['barber', 'service'])
            ->where('id_user', auth()->id())
            ->findOrFail($id);

        abort_unless($b->status === Booking::STATUS_SELESAI, 403, 'Booking belum selesai.');

        return $b;
    }
}