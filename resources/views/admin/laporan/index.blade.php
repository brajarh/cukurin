@extends('layouts.app')
@section('title', 'Laporan')
@section('content')
<h1 class="h3 mb-3">Laporan dan Rekap</h1>

<form method="GET" action="{{ route('admin.laporan') }}" class="row g-2 mb-4">
    <div class="col-auto"><label class="form-label mb-0">Dari</label><input class="form-control" type="date" name="dari" value="{{ $dari }}"></div>
    <div class="col-auto"><label class="form-label mb-0">Sampai</label><input class="form-control" type="date" name="sampai" value="{{ $sampai }}"></div>
    <div class="col-auto">
        <label class="form-label mb-0">Cabang</label>
        <select class="form-select" name="id_outlet">
            <option value="">Semua cabang</option>
            @foreach ($outlet as $o)
                <option value="{{ $o->id_outlet }}" {{ (string) $idOutlet === (string) $o->id_outlet ? 'selected' : '' }}>{{ $o->nama_outlet }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto align-self-end"><button type="submit" class="btn btn-primary">Tampilkan</button></div>
</form>

<div class="alert alert-info">Total pendapatan (berdasarkan tanggal pembayaran) periode {{ $dari }} s.d. {{ $sampai }}: <strong>{{ numberToRupiah($totalPendapatan) }}</strong></div>

<h5>1. Pendapatan per Cabang</h5>
<table class="table table-striped table-bordered">
    <thead><tr><th>Cabang</th><th class="text-end">Jumlah Transaksi</th><th class="text-end">Total Pendapatan</th></tr></thead>
    <tbody>
    @forelse ($perCabang as $r)
        <tr><td>{{ $r->nama_outlet }}</td><td class="text-end">{{ $r->jumlah_transaksi }}</td><td class="text-end">{{ numberToRupiah($r->total_pendapatan) }}</td></tr>
    @empty
        <tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>

<h5 class="mt-4">2. Pendapatan per Bulan</h5>
<table class="table table-striped table-bordered">
    <thead><tr><th>Bulan</th><th class="text-end">Jumlah Transaksi</th><th class="text-end">Total Pendapatan</th></tr></thead>
    <tbody>
    @forelse ($perBulan as $r)
        <tr><td>{{ $r->bulan }}</td><td class="text-end">{{ $r->jumlah_transaksi }}</td><td class="text-end">{{ numberToRupiah($r->total_pendapatan) }}</td></tr>
    @empty
        <tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>

<h5 class="mt-4">3. Kinerja Barber</h5>
<table class="table table-striped table-bordered">
    <thead><tr><th>Barber</th><th>Cabang</th><th class="text-end">Transaksi Dibayar</th><th class="text-end">Pendapatan</th><th class="text-end">Rating</th></tr></thead>
    <tbody>
    @forelse ($perBarber as $r)
        <tr>
            <td>{{ $r->nama_barber }}</td><td>{{ $r->nama_outlet }}</td>
            <td class="text-end">{{ $r->total_dibayar }}</td>
            <td class="text-end">{{ numberToRupiah($r->pendapatan) }}</td>
            <td class="text-end">{{ $r->jumlah_ulasan > 0 ? number_format($r->rating_rata, 2) . ' (' . $r->jumlah_ulasan . ')' : '-' }}</td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>

<h5 class="mt-4">4. Layanan Terlaris</h5>
<table class="table table-striped table-bordered">
    <thead><tr><th>Layanan</th><th class="text-end">Jumlah Dipesan</th><th class="text-end">Nilai</th></tr></thead>
    <tbody>
    @forelse ($perLayanan as $r)
        <tr><td>{{ $r->nama_service }}</td><td class="text-end">{{ $r->jumlah_dipesan }}</td><td class="text-end">{{ numberToRupiah($r->nilai) }}</td></tr>
    @empty
        <tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
