@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<h1 class="h3 mb-3">Dashboard Admin</h1>

<div class="row mb-4">
    @foreach ([
        'Booking hari ini' => $stat['booking_hari_ini'],
        'Menunggu dilayani' => $stat['dipesan'],
        'Selesai' => $stat['selesai'],
        'Jumlah barber' => $stat['barber'],
    ] as $label => $nilai)
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="display-6">{{ $nilai }}</div>
                    <div class="text-muted">{{ $label }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<p>Pendapatan hari ini: <strong>Rp {{ number_format($stat['pendapatan_hari_ini'], 0, ',', '.') }}</strong></p>

<h2 class="h5">Booking terbaru</h2>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Pelanggan</th>
            <th>Layanan</th>
            <th>Barber</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($terbaru as $b)
            <tr>
                <td>{{ $b->kode_booking }}</td>
                <td>{{ $b->customer->nama }}</td>
                <td>{{ $b->service->nama_service }}</td>
                <td>{{ $b->barber->nama_barber }}</td>
                <td>{{ $b->status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endsection