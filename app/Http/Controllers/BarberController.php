<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barber;

class BarberController extends Controller
{
    //
    public function index()
    {
    $data = Barber::with('outlet')->get();
    return view('barber.index', compact('data'));
    }

    public function show($id)
    {$b = Barber::with('service')->findOrFail($id);
    return view('barber.show', compact('b'));
    }
}
