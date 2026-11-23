@extends('layouts.app')
@section('content')
<h1>Jadwal Kerja Barber</h1>

<form method="GET" action="{{ route('jadwal.index') }}">
    <select name="id_barber">
        <option value="">Semua barber</option>
        @foreach ($barber as $b)
            <option value="{{ $b->id_barber }}" {{ (string) $idBarber === (string) $b->id_barber ? 'selected' : '' }}>
                {{ $b->nama_barber }}
            </option>
        @endforeach
    </select>
    <button type="submit">Filter</button>
    <a href="{{ route('jadwal.index') }}">Reset</a>
</form>
<p><a href="{{ route('jadwal.create') }}">+ Tambah Jadwal</a></p>

<table border="1" cellpadding="4">
    <tr><th>Barber</th><th>Cabang</th><th>Hari</th><th>Jam Kerja</th><th>Aksi</th></tr>
    @forelse ($data as $j)
        <tr>
            <td>{{ $j->barber->nama_barber }}</td>
            <td>{{ $j->barber->outlet->nama_outlet }}</td>
            <td>{{ \App\Models\BarberSchedule::HARI[$j->hari] }}</td>
            <td>{{ formatJam($j->jam_mulai) }} - {{ formatJam($j->jam_selesai) }}</td>
            <td>
                <a href="{{ route('jadwal.edit', $j->id_schedule) }}">Ubah</a>
                <form method="POST" style="display:inline"
                      action="{{ route('jadwal.destroy', $j->id_schedule) }}"
                      onsubmit="return confirm('Hapus jadwal ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5">Belum ada jadwal.</td></tr>
    @endforelse
</table>
@endsection
