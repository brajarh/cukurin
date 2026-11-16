<?php

namespace App\Http\Controllers;

use App\Http\Requests\BarberScheduleRequest;
use App\Models\Barber;
use App\Models\BarberSchedule;
use Illuminate\Http\Request;

class BarberScheduleController extends Controller
{
    public function index(Request $request)
    {
        $idBarber = $request->query('id_barber');

        $data = BarberSchedule::with('barber.outlet')
            ->when($idBarber, fn ($qr) => $qr->where('id_barber', $idBarber))
            ->orderBy('id_barber')
            ->orderBy('hari')
            ->get();
        $barber = Barber::orderBy('nama_barber')->get();

        return view('jadwal.index', compact('data', 'barber', 'idBarber'));
    }

    public function create()
    {
        $barber = Barber::with('outlet')->orderBy('nama_barber')->get();
        return view('jadwal.create', compact('barber'));
    }

    public function store(BarberScheduleRequest $request)
    {
        BarberSchedule::create($request->validated());

        return redirect()->route('jadwal.index')
            ->with('success', 'Jadwal kerja berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $j = BarberSchedule::findOrFail($id);
        $barber = Barber::with('outlet')->orderBy('nama_barber')->get();
        return view('jadwal.edit', compact('j', 'barber'));
    }

    public function update(BarberScheduleRequest $request, $id)
    {
        $j = BarberSchedule::findOrFail($id);
        $j->update($request->validated());

        return redirect()->route('jadwal.index')
            ->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    public function destroy($id)
    {
        BarberSchedule::findOrFail($id)->delete();

        return redirect()->route('jadwal.index')
            ->with('success', 'Jadwal kerja berhasil dihapus.');
    }
}
