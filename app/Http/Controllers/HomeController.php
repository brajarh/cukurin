<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Models\Barber;
use App\Models\Outlet;
use App\Models\Service;

class HomeController extends Controller
{


    // Halaman About asli
    public function about(): View
    {
        return view('about');
    }

    // Halaman Contact asli
    public function contact(): View
    {
        return view('home.contact');
    }


    // Dashboard pengguna setelah login
    public function index(): View
    {
        return view('home');
    }
    public function landing(): View
{
    $layanan = Service::orderBy('nama_service')->get();
    $barber = Barber::with('outlet')->orderBy('nama_barber')->get();
    $outlet = Outlet::orderBy('nama_outlet')->get();

    return view('welcome', compact('layanan', 'barber', 'outlet'));
}
}
