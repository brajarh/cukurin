<?php

namespace App\Http\Controllers;

use App\Http\Requests\BarberRequest;
use App\Models\Barber;
use App\Models\Outlet;
use App\Models\Service;
use Illuminate\Http\Request;

class BarberController extends Controller
{
    //
    public function index(Request $request)
    {
        $cari = $request->query('q');
        $idOutlet = $request->query('id_outlet');

        $data = Barber::with('outlet')
            ->when($cari, fn ($qr) => $qr->where('nama_barber', 'like', "%{$cari}%"))
            ->when($idOutlet, fn ($qr) => $qr->where('id_outlet', $idOutlet))
            ->orderBy('nama_barber')
            ->get();
        $outlet = Outlet::orderBy('nama_outlet')->get();

        return view('barber.index', compact('data', 'outlet', 'cari', 'idOutlet'));
    }

    public function show($id)
    {
        $b = Barber::with(['service', 'outlet', 'schedule'])->findOrFail($id);
        return view('barber.show', compact('b'));
    }

    public function create()
    {
        $outlet = Outlet::orderBy('nama_outlet')->get();
        $service = Service::orderBy('nama_service')->get();
        return view('barber.create', compact('outlet', 'service'));
    }

    public function store(BarberRequest $request)
    {
        $b = Barber::create($request->safe()->except('service_ids'));
        $b->service()->sync($request->input('service_ids', []));

        return redirect()->route('barber.index')
            ->with('success', 'Barber berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $b = Barber::with('service')->findOrFail($id);
        $outlet = Outlet::orderBy('nama_outlet')->get();
        $service = Service::orderBy('nama_service')->get();
        $idService = $b->service->pluck('id_service')->all();

        return view('barber.edit', compact('b', 'outlet', 'service', 'idService'));
    }

    public function update(BarberRequest $request, $id)
    {
        $b = Barber::findOrFail($id);
        $b->update($request->safe()->except('service_ids'));
        $b->service()->sync($request->input('service_ids', []));

        return redirect()->route('barber.index')
            ->with('success', 'Barber berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $b = Barber::findOrFail($id);

        if ($b->booking()->exists()) {
            return redirect()->route('barber.index')
                ->with('error', 'Barber tidak bisa dihapus karena sudah punya data booking.');
        }

        $b->service()->detach(); // lepas dari pivot barberservice
        $b->delete();            // jadwal kerja ikut terhapus (cascade)

        return redirect()->route('barber.index')
            ->with('success', 'Barber berhasil dihapus.');
    }
}
