<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    // Halaman latihan lama
    public function myHome()
    {
        return 'Halaman dari HomeController';
    }

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

    // Halaman login latihan Modul 8
    // Tidak lagi dipakai oleh route /login
    public function login(): View
    {
        return view('halaman.login');
    }

    // Dashboard pengguna setelah login
    public function index(): View
    {
        return view('home');
    }
}
