@extends('layouts.app')

@section('title', 'Riwayat Ubah Jadwal')

@section('content')
<h1 class="h3 mb-3">Riwayat Perubahan Jadwal Booking</h1>

<form method="GET" class="row g-2 mb-3">
    <div class="col-auto">
        <input class="form-control" name="q" value="{{ $cari }}" placeholder="Cari kode booking / nama pelanggan">
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-secondary">Cari</button>
    </div>
</form>

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Waktu</th>
            <th>Kode Booking</th>
            <th>Pelanggan</th>
            <th>Barber</th>
            <th>Jadwal Lama</th>
            <th>Jadwal Baru</th>
            <th>Alasan</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $r)
            <tr>
                <td>{{ $r->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                <td>{{ $r->booking->kode_booking }}</td>
                <td>{{ $r->user->name }}</td>
                <td>{{ $r->booking->barber->nama_barber }}</td>
                <td>{{ $r->tanggal_lama->format('d-m-Y') }}, {{ formatJam($r->jam_mulai_lama) }}</td>
                <td>{{ $r->tanggal_baru->format('d-m-Y') }}, {{ formatJam($r->jam_mulai_baru) }}</td>
                <td>{{ $r->alasan }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center">Belum ada perubahan jadwal.</td></tr>
        @endforelse
    </tbody>
</table>
{{ $data->links() }}
@endsection
