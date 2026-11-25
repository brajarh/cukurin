@extends('layouts.app')
@section('content')
<h1>Daftar Booking</h1>

<form method="GET" action="{{ route('admin.booking.index') }}">
    <input name="q" value="{{ $cari }}" placeholder="Kode / nama / no HP">

    <select name="id_outlet">
        <option value="">Semua cabang</option>
        @foreach ($outlet as $o)
            <option value="{{ $o->id_outlet }}" {{ (string) $idOutlet === (string) $o->id_outlet ? 'selected' : '' }}>{{ $o->nama_outlet }}</option>
        @endforeach
    </select>

    <select name="id_barber">
        <option value="">Semua barber</option>
        @foreach ($barber as $b)
            <option value="{{ $b->id_barber }}" {{ (string) $idBarber === (string) $b->id_barber ? 'selected' : '' }}>{{ $b->nama_barber }}</option>
        @endforeach
    </select>

    <input type="date" name="tanggal" value="{{ $tanggal }}">

    <select name="status">
        <option value="">Semua status</option>
        @foreach (\App\Models\Booking::DAFTAR_STATUS as $st)
            <option value="{{ $st }}" {{ $status === $st ? 'selected' : '' }}>{{ $st }}</option>
        @endforeach
    </select>

    <button type="submit">Filter</button>
    <a href="{{ route('admin.booking.index') }}">Reset</a>
</form>

<p>Menampilkan maksimal 200 data terbaru.</p>

<table border="1" cellpadding="4">
    <tr>
        <th>Kode</th><th>Tanggal</th><th>Jam</th><th>Customer</th><th>Layanan</th>
        <th>Barber</th><th>Cabang</th><th>Status</th><th>Aksi</th>
    </tr>
    @forelse ($data as $b)
        <tr>
            <td>{{ $b->kode_booking }}</td>
            <td>{{ $b->tanggal->format('d-m-Y') }}</td>
            <td>{{ formatJam($b->jam_mulai) }} - {{ formatJam($b->jam_selesai) }}</td>
            <td>{{ $b->customer->nama }}<br>{{ $b->customer->no_hp }}</td>
            <td>{{ $b->service->nama_service }}</td>
            <td>{{ $b->barber->nama_barber }}</td>
            <td>{{ $b->barber->outlet->nama_outlet }}</td>
            <td>
                <form method="POST" action="{{ route('admin.booking.status', $b->id_booking) }}">
                    @csrf
                    @method('PATCH')
                    <select name="status">
                        @foreach (\App\Models\Booking::DAFTAR_STATUS as $st)
                            <option value="{{ $st }}" {{ $b->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                    <button type="submit">Ubah</button>
                </form>
            </td>
            <td>
                <form method="POST" action="{{ route('admin.booking.destroy', $b->id_booking) }}"
                      onsubmit="return confirm('Hapus data booking ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="9">Belum ada booking.</td></tr>
    @endforelse
</table>
@endsection
