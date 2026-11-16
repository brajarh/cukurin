<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $cari = $request->query('q');

        $data = Service::when($cari, fn ($qr) => $qr->where('nama_service', 'like', "%{$cari}%"))
            ->orderBy('nama_service')
            ->get();

        return view('service.index', compact('data', 'cari'));
    }

    public function create()
    {
        return view('service.create');
    }

    public function store(ServiceRequest $request)
    {
        Service::create($request->validated());

        return redirect()->route('service.index')
            ->with('success', 'Layanan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $s = Service::findOrFail($id);
        return view('service.edit', compact('s'));
    }

    public function update(ServiceRequest $request, $id)
    {
        $s = Service::findOrFail($id);
        $s->update($request->validated());

        return redirect()->route('service.index')
            ->with('success', 'Layanan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $s = Service::findOrFail($id);

        if (Booking::where('id_service', $id)->exists()) {
            return redirect()->route('service.index')
                ->with('error', 'Layanan tidak bisa dihapus karena sudah dipakai di data booking.');
        }

        $s->barber()->detach(); // lepas dari tabel pivot barberservice dulu
        $s->delete();

        return redirect()->route('service.index')
            ->with('success', 'Layanan berhasil dihapus.');
    }
}
