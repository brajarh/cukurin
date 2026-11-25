@extends('layouts.app')

@section('content')

<h1>Status dan Riwayat Booking</h1>

<form method="GET" action="{{ route('booking.riwayat') }}">

    <input
        name="cari"
        value="{{ $cari }}"
        placeholder="Kode booking atau nomor HP"
        size="30"
    >

    <button type="submit">Cari</button>

</form>

<table border="1" cellpadding="4">

    <thead>
        <tr>
            <th>Kode</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th>Layanan</th>
            <th>Total Harga</th>
            <th>Total Durasi</th>
            <th>Barber</th>
            <th>Cabang</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>

        @forelse ($data as $b)

            <tr>

                <td>
                    <a href="{{ route('booking.receipt', $b->kode_booking) }}">
                        {{ $b->kode_booking }}
                    </a>
                </td>

                <td>{{ $b->tanggal->format('d-m-Y') }}</td>

                <td>
                    {{ formatJam($b->jam_mulai) }} -
                    {{ formatJam($b->jam_selesai) }}
                </td>

                <td>

                    @foreach ($b->detail as $dt)

                        {{ $dt->service->nama_service }}<br>

                    @endforeach

                </td>

                <td>
                    {{ numberToRupiah($b->totalHarga()) }}
                </td>

                <td>
                    {{ formatDurasi($b->totalDurasi()) }}
                </td>

                <td>{{ $b->barber->nama_barber }}</td>

                <td>{{ $b->barber->outlet->nama_outlet }}</td>

                <td>{{ $b->status }}</td>

            </tr>

        @empty

            <tr>
                <td colspan="9">
                    Booking tidak ditemukan.
                </td>
            </tr>

        @endforelse

    </tbody>

</table>

<p>
    <a href="{{ route('booking.create') }}">
        Booking baru
    </a>
</p>

@endsection