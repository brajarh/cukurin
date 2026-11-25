@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
<h1 class="h3">Pembayaran</h1>

<h2 class="h5 mt-4">Menunggu pembayaran</h2>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Pelanggan</th>
            <th>Layanan</th>
            <th>Barber</th>
            <th>Jadwal</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($belumBayar as $b)
            <tr>
                <td>{{ $b->kode_booking }}</td>
                <td>{{ $b->customer->nama }}</td>
                <td>{{ $b->service->nama_service }}</td>
                <td>{{ $b->barber->nama_barber }}</td>
                <td>{{ $b->tanggal->format('d/m/Y') }} {{ formatJam($b->jam_mulai) }}</td>
                <td>
                    <a class="btn btn-sm btn-dark" href="{{ route('pembayaran.create', $b->id_booking) }}">Bayar</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada booking yang menunggu pembayaran.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<h2 class="h5 mt-4">Riwayat pembayaran</h2>
<form method="GET" action="{{ route('pembayaran.index') }}" class="mb-2 d-flex gap-2" style="max-width: 360px;">
    <input name="q" value="{{ $cari }}" class="form-control" placeholder="Cari kode booking">
    <button type="submit" class="btn btn-dark">Cari</button>
</form>

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Metode</th>
            <th>Jumlah</th>
            <th>Dicatat oleh</th>
            <th>Waktu</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $p)
            <tr>
                <td>{{ $p->booking->kode_booking }}</td>
                <td>{{ strtoupper($p->metode) }}</td>
                <td>Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                <td>{{ $p->kasir->name }}</td>
                <td>{{ $p->dibayar_pada->format('d/m/Y H:i') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada pembayaran.</td>
            </tr>
        @endforelse
    </tbody>
</table>
{{ $data->links() }}
@endsection