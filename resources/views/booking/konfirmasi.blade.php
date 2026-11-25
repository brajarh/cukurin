@extends('layouts.app')

@section('content')

<h1>Konfirmasi Booking</h1>

<p>Periksa kembali rincian pesanan kamu.</p>

<table border="1" cellpadding="4">

    <tr>
        <td>Cabang</td>
        <td>{{ $barber->outlet->nama_outlet }}</td>
    </tr>

    <tr>
        <td>Barber</td>
        <td>{{ $barber->nama_barber }}</td>
    </tr>

    <tr>
        <td>Tanggal</td>
        <td>{{ \Carbon\Carbon::parse($d['tanggal'])->format('d-m-Y') }}</td>
    </tr>

    <tr>
        <td>Jam</td>
        <td>
            {{ $d['jam_mulai'] }} - {{ $selesai->format('H:i') }}
        </td>
    </tr>

    <tr>
        <td>Nama</td>
        <td>{{ $d['nama'] }}</td>
    </tr>

    <tr>
        <td>No HP</td>
        <td>{{ $d['no_hp'] }}</td>
    </tr>

</table>

<h3>Rincian Layanan</h3>

<table border="1" cellpadding="4">

    <thead>
        <tr>
            <th>Layanan</th>
            <th>Harga</th>
            <th>Durasi</th>
        </tr>
    </thead>

    <tbody>

        @foreach ($layanan as $sv)
            <tr>
                <td>{{ $sv->nama_service }}</td>
                <td>{{ numberToRupiah($sv->harga_service) }}</td>
                <td>{{ $sv->durasi }} menit</td>
            </tr>
        @endforeach

        <tr>
            <th>Total</th>
            <th>{{ numberToRupiah($totalHarga) }}</th>
            <th>{{ $totalDurasi }} menit</th>
        </tr>

    </tbody>

</table>

<form method="POST" action="{{ route('booking.store') }}">

    @csrf

    @foreach ([
        'id_outlet',
        'id_barber',
        'tanggal',
        'jam_mulai',
        'nama',
        'no_hp'
    ] as $k)

        <input type="hidden" name="{{ $k }}" value="{{ $d[$k] }}">

    @endforeach

    @foreach ($d['id_service'] as $id)
        <input type="hidden" name="id_service[]" value="{{ $id }}">
    @endforeach

    <p>
        <button type="submit">Konfirmasi Booking</button>

        <a href="javascript:history.back()">Ubah</a>
    </p>

</form>

@endsection