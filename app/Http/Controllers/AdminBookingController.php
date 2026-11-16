<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\Booking;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Sisi admin: daftar booking + filter, ubah status, hapus, dan kalender.
 * Catatan: pasang middleware login/admin (dari Orang 1) di routes/fitur.php.
 */
class AdminBookingController extends Controller
{
    public function index(Request $request)
    {
        $cari = $request->query('q');
        $idOutlet = $request->query('id_outlet');
        $idBarber = $request->query('id_barber');
        $tanggal = $request->query('tanggal');
        $status = $request->query('status');

        $data = Booking::with(['customer', 'barber.outlet', 'service'])
            ->when($idOutlet, fn ($qr) => $qr->whereHas('barber', fn ($b) => $b->where('id_outlet', $idOutlet)))
            ->when($idBarber, fn ($qr) => $qr->where('id_barber', $idBarber))
            ->when($tanggal, fn ($qr) => $qr->where('tanggal', $tanggal))
            ->when($status, fn ($qr) => $qr->where('status', $status))
            ->when($cari, fn ($qr) => $qr->where(function ($w) use ($cari) {
                $w->where('kode_booking', 'like', "%{$cari}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('nama', 'like', "%{$cari}%")
                        ->orWhere('no_hp', 'like', "%{$cari}%"));
            }))
            ->orderByDesc('tanggal')
            ->orderBy('jam_mulai')
            ->limit(200)
            ->get();

        $outlet = Outlet::orderBy('nama_outlet')->get();
        $barber = Barber::orderBy('nama_barber')->get();

        return view('admin.booking.index', compact(
            'data', 'outlet', 'barber', 'cari', 'idOutlet', 'idBarber', 'tanggal', 'status'
        ));
    }

    public function updateStatus(Request $request, $id)
    {
        $d = $request->validate([
            'status' => ['required', Rule::in(Booking::DAFTAR_STATUS)],
        ], [
            'status.in' => 'Status tidak valid.',
        ]);

        Booking::findOrFail($id)->update(['status' => $d['status']]);

        return back()->with('success', 'Status booking berhasil diubah.');
    }

    public function destroy($id)
    {
        Booking::findOrFail($id)->delete();

        return back()->with('success', 'Data booking berhasil dihapus.');
    }

    // Halaman kalender (FullCalendar)
    public function kalender()
    {
        $outlet = Outlet::orderBy('nama_outlet')->get();
        return view('admin.booking.kalender', compact('outlet'));
    }

    // Sumber data event untuk FullCalendar (JSON)
    public function events(Request $request)
    {
        $awal = substr((string) $request->query('start'), 0, 10);
        $akhir = substr((string) $request->query('end'), 0, 10);
        $idOutlet = $request->query('id_outlet');

        $warna = [
            Booking::STATUS_DIPESAN => '#2563eb',
            Booking::STATUS_SELESAI => '#16a34a',
            Booking::STATUS_BATAL => '#9ca3af',
        ];

        $events = Booking::with(['customer', 'barber', 'service'])
            ->when($awal && $akhir, fn ($qr) => $qr->whereBetween('tanggal', [$awal, $akhir]))
            ->when($idOutlet, fn ($qr) => $qr->whereHas('barber', fn ($b) => $b->where('id_outlet', $idOutlet)))
            ->get()
            ->map(fn ($b) => [
                'id' => $b->id_booking,
                'title' => formatJam($b->jam_mulai) . ' ' . $b->customer->nama
                    . ' - ' . $b->service->nama_service . ' (' . $b->barber->nama_barber . ')',
                'start' => $b->tanggal->format('Y-m-d') . 'T' . $b->jam_mulai,
                'end' => $b->tanggal->format('Y-m-d') . 'T' . $b->jam_selesai,
                'color' => $warna[$b->status] ?? '#2563eb',
            ]);

        return response()->json($events);
    }
}
