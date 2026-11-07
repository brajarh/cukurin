<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    //
    public function myHome()
    {
    return 'Halaman dari HomeController';
    }

    public function about(): View
    {

    return view('about');
    }

    public function contact(): View
    {

    return view('home.contact');
    }

    public function login(): View
    {

    return view('halaman.login');
    }
}
