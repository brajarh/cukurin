<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutletRequest;
use App\Models\Outlet;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function index(Request $request)
    {
        $cari = $request->query('q');
        $data = Outlet::withCount('barber')
            ->when($cari, fn ($qr) => $qr->where('nama_outlet', 'like', "%{$cari}%")
                ->orWhere('alamat_outlet', 'like', "%{$cari}%"))
            ->orderBy('nama_outlet')
            ->paginate(5)
            ->withQueryString();

        return view('outlet.index', compact('data', 'cari'));
    }

    public function create()
    {
        return view('outlet.create');
    }

    public function store(OutletRequest $request)
    {
        Outlet::create($request->validated());

        return redirect()->route('outlet.index')
            ->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $outlet = Outlet::findOrFail($id);

        return view('outlet.edit', compact('outlet'));
    }

    public function update(OutletRequest $request, $id)
    {
        $outlet = Outlet::findOrFail($id);
        $outlet->update($request->validated());

        return redirect()->route('outlet.index')
            ->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $o = Outlet::findOrFail($id);
        if ($o->barber()->exists()) {
            return redirect()->route('outlet.index')
                ->with('error', 'Cabang tidak bisa dihapus karena masih memiliki barber.');
        }

        $o->delete();

        return redirect()->route('outlet.index')
            ->with('success', 'Cabang berhasil dihapus.');
    }
}