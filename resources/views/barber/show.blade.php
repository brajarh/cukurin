@extends('layouts.app')
@section('content')
<h1>{{ $b->nama_barber }}</h1>
<p>Cabang: {{ $b->outlet->nama_outlet }} | HP: {{ $b->no_hp }}</p>

<h3>Layanan</h3>
<ul>
    @forelse ($b->service as $s)
        <li>{{ $s->nama_service }} ({{ numberToRupiah($s->harga_service) }}, {{ formatDurasi($s->durasi) }})</li>
    @empty
        <li>Belum ada layanan.</li>
    @endforelse
</ul>

<h3>Jadwal Kerja</h3>
<ul>
    @forelse ($b->schedule->sortBy('hari') as $j)
        <li>{{ \App\Models\BarberSchedule::HARI[$j->hari] }}: {{ formatJam($j->jam_mulai) }} - {{ formatJam($j->jam_selesai) }}</li>
    @empty
        <li>Belum ada jadwal.</li>
    @endforelse
</ul>

<p><a href="{{ route('barber.edit', $b->id_barber) }}">Ubah</a> | <a href="{{ route('barber.index') }}">Kembali</a></p>
@endsection
