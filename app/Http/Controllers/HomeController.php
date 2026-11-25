<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Booking;
use App\Models\Outlet;
use App\Models\Pembayaran;
use App\Models\Service;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function landing(): View
    {
        $layanan = Service::orderBy('nama_service')->get();
        $barber = Barber::with('outlet')->orderBy('nama_barber')->get();
        $outlet = Outlet::orderBy('nama_outlet')->get();

        return view('welcome', compact('layanan', 'barber', 'outlet'));
    }

    public function index(): View
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            $hariIni = now('Asia/Jakarta')->toDateString();
            $stat = [
                'booking_hari_ini' => Booking::where('tanggal', $hariIni)
                    ->where('status', '!=', Booking::STATUS_BATAL)->count(),
                'dipesan' => Booking::where('status', Booking::STATUS_DIPESAN)->count(),
                'selesai' => Booking::where('status', Booking::STATUS_SELESAI)->count(),
                'barber' => Barber::count(),
                'pendapatan_hari_ini' => Pembayaran::whereDate('dibayar_pada', $hariIni)->sum('jumlah'),
            ];

            $terbaru = Booking::with(['customer', 'barber', 'service'])
                ->orderByDesc('id_booking')->limit(5)->get();

            return view('dashboard.admin', compact('stat', 'terbaru'));
        }

        abort_unless($user->hasRole('pelanggan'), 403);

        $booking = Booking::with(['barber', 'service', 'ulasan'])
            ->where('id_user', $user->id)
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get();

        return view('dashboard.pelanggan', compact('booking'));
    }

    public function about()
    {
        return view('about');
    }

    public function contact()
    {
        return view('home.contact');
    }
}