@extends('layouts.app')
@section('title', $b->nama_barber)
@section('content')
<h1 class="h3">{{ $b->nama_barber }}</h1>
<p>Cabang: {{ $b->outlet->nama_outlet }} | HP: {{ $b->no_hp }}
    | Rating: {{ $b->jumlah_ulasan > 0 ? number_format($b->rating_rata, 2) . ' (' . $b->jumlah_ulasan . ' ulasan)' : 'belum ada ulasan' }}</p>

<h5>Layanan</h5>
<ul>
    @forelse ($b->service as $s)
        <li>{{ $s->nama_service }} ({{ numberToRupiah($s->harga_service) }}, {{ formatDurasi($s->durasi) }})</li>
    @empty
        <li>Belum ada layanan.</li>
    @endforelse
</ul>

<h5>Jadwal Kerja</h5>
<ul>
    @forelse ($b->schedule->sortBy('hari') as $j)
        <li>{{ \App\Models\BarberSchedule::HARI[$j->hari] }}: {{ formatJam($j->jam_mulai) }} - {{ formatJam($j->jam_selesai) }}</li>
    @empty
        <li>Belum ada jadwal.</li>
    @endforelse
</ul>

<a class="btn btn-warning" href="{{ route('barber.edit', $b->id_barber) }}">Ubah</a>
<a class="btn btn-outline-secondary" href="{{ route('barber.index') }}">Kembali</a>
@endsection
