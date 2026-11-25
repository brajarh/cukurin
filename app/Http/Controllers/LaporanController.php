<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use App\Services\SlotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Laporan / rekap dengan QUERY BUILDER (DB::table): join, groupBy, dan fungsi agregat.
 *
 * Pendapatan dihitung dari tabel pembayaran (T3), bukan dari status booking,
 * jadi laporan tidak terpengaruh kalau nilai status booking diubah di T2.
 * Periode memakai tanggal pembayaran (dibayar_pada).
 */
class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'dari' => 'nullable|date_format:Y-m-d',
            'sampai' => 'nullable|date_format:Y-m-d|after_or_equal:dari',
            'id_outlet' => 'nullable|integer',
        ], [
            'dari.date_format' => 'Format tanggal awal tidak valid.',
            'sampai.date_format' => 'Format tanggal akhir tidak valid.',
            'sampai.after_or_equal' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal awal.',
        ]);

        $sekarang = SlotService::sekarang();
        $dari = $request->query('dari') ?: $sekarang->copy()->startOfMonth()->format('Y-m-d');
        $sampai = $request->query('sampai') ?: $sekarang->copy()->endOfMonth()->format('Y-m-d');
        $idOutlet = $request->query('id_outlet');

        // 1. Pendapatan per cabang: join pembayaran - booking - barber - outlet, groupBy cabang
        $perCabang = DB::table('pembayaran as p')
            ->join('booking as b', 'b.id_booking', '=', 'p.id_booking')
            ->join('barber as br', 'br.id_barber', '=', 'b.id_barber')
            ->join('outlet as o', 'o.id_outlet', '=', 'br.id_outlet')
            ->whereDate('p.dibayar_pada', '>=', $dari)
            ->whereDate('p.dibayar_pada', '<=', $sampai)
            ->when($idOutlet, fn ($q) => $q->where('o.id_outlet', $idOutlet))
            ->groupBy('o.id_outlet', 'o.nama_outlet')
            ->selectRaw('o.nama_outlet, COUNT(p.id_pembayaran) as jumlah_transaksi, SUM(p.jumlah) as total_pendapatan')
            ->orderByDesc('total_pendapatan')
            ->get();

        // 2. Pendapatan per bulan: groupBy ekspresi "tahun-bulan" (beda tiap driver database)
        $bulan = $this->ekspresiBulan('p.dibayar_pada');
        $perBulan = DB::table('pembayaran as p')
            ->join('booking as b', 'b.id_booking', '=', 'p.id_booking')
            ->join('barber as br', 'br.id_barber', '=', 'b.id_barber')
            ->whereDate('p.dibayar_pada', '>=', $dari)
            ->whereDate('p.dibayar_pada', '<=', $sampai)
            ->when($idOutlet, fn ($q) => $q->where('br.id_outlet', $idOutlet))
            ->groupByRaw($bulan)
            ->selectRaw("$bulan as bulan, COUNT(p.id_pembayaran) as jumlah_transaksi, SUM(p.jumlah) as total_pendapatan")
            ->orderByRaw($bulan)
            ->get();

        // 3. Kinerja barber: left join supaya barber tanpa pembayaran tetap tampil (pendapatan 0)
        $perBarber = DB::table('barber as br')
            ->join('outlet as o', 'o.id_outlet', '=', 'br.id_outlet')
            ->leftJoin('booking as b', 'b.id_barber', '=', 'br.id_barber')
            ->leftJoin('pembayaran as p', function ($j) use ($dari, $sampai) {
                $j->on('p.id_booking', '=', 'b.id_booking')
                    ->whereDate('p.dibayar_pada', '>=', $dari)
                    ->whereDate('p.dibayar_pada', '<=', $sampai);
            })
            ->when($idOutlet, fn ($q) => $q->where('o.id_outlet', $idOutlet))
            ->groupBy('br.id_barber', 'br.nama_barber', 'o.nama_outlet', 'br.rating_rata', 'br.jumlah_ulasan')
            ->selectRaw('br.nama_barber, o.nama_outlet, COUNT(p.id_pembayaran) as total_dibayar,
                COALESCE(SUM(p.jumlah), 0) as pendapatan, br.rating_rata, br.jumlah_ulasan')
            ->orderByDesc('pendapatan')
            ->orderBy('br.nama_barber')
            ->get();

        // 4. Layanan terlaris: join detail_booking - booking - pembayaran - service - barber
        $perLayanan = DB::table('detail_booking as d')
            ->join('booking as b', 'b.id_booking', '=', 'd.id_booking')
            ->join('pembayaran as p', 'p.id_booking', '=', 'b.id_booking')
            ->join('service as s', 's.id_service', '=', 'd.id_service')
            ->join('barber as br', 'br.id_barber', '=', 'b.id_barber')
            ->whereDate('p.dibayar_pada', '>=', $dari)
            ->whereDate('p.dibayar_pada', '<=', $sampai)
            ->when($idOutlet, fn ($q) => $q->where('br.id_outlet', $idOutlet))
            ->groupBy('s.id_service', 's.nama_service')
            ->selectRaw('s.nama_service, COUNT(d.id_detail_booking) as jumlah_dipesan, SUM(d.harga) as nilai')
            ->orderByDesc('jumlah_dipesan')
            ->orderBy('s.nama_service')
            ->get();

        $outlet = Outlet::orderBy('nama_outlet')->get();
        $totalPendapatan = $perCabang->sum('total_pendapatan');

        return view('admin.laporan.index', compact(
            'dari', 'sampai', 'idOutlet', 'outlet',
            'perCabang', 'perBulan', 'perBarber', 'perLayanan', 'totalPendapatan'
        ));
    }

    // Ekspresi "tahun-bulan" berbeda tiap database
    private function ekspresiBulan(string $kolom): string
    {
        return match (DB::connection()->getDriverName()) {
            'pgsql' => "to_char($kolom, 'YYYY-MM')",
            'mysql', 'mariadb' => "DATE_FORMAT($kolom, '%Y-%m')",
            default => "strftime('%Y-%m', $kolom)",
        };
    }
}
