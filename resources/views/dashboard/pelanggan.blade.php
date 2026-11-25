@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<h1 class="h3 mb-3">Halo, {{ auth()->user()->name }}</h1>
<a href="{{ route('booking.create') }}" class="btn btn-dark mb-3">Buat Booking Baru</a>

<h2 class="h5">Booking saya</h2>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Kode</th>
            <th>Layanan</th>
            <th>Barber</th>
            <th>Jadwal</th>
            <th>Status</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @forelse ($booking as $b)
            <tr>
                <td><a href="{{ route('booking.receipt', $b->kode_booking) }}">{{ $b->kode_booking }}</a></td>
                <td>{{ $b->service->nama_service }}</td>
                <td>{{ $b->barber->nama_barber }}</td>
                <td>{{ $b->tanggal->format('d/m/Y') }} {{ formatJam($b->jam_mulai) }}</td>
                <td>{{ $b->status }}</td>
                <td>
                    @if ($b->status === 'selesai' && !$b->ulasan)
                        <a class="btn btn-sm btn-outline-dark" href="{{ route('ulasan.create', $b->id_booking) }}">Beri ulasan</a>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada booking.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection