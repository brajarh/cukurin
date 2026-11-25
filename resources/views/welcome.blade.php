@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
<div class="p-5 mb-4 bg-white rounded border text-center">
    <h1 class="display-5 fw-bold">Cukur.in</h1>
    <p class="lead">Booking barbershop tanpa antre. Pilih layanan, barber, dan jam favoritmu.</p>
    @guest
        <a href="{{ route('login') }}" class="btn btn-dark btn-lg">Login untuk Booking</a>
        <a href="{{ route('register') }}" class="btn btn-outline-dark btn-lg">Daftar</a>
    @else
        <a href="{{ route('home') }}" class="btn btn-dark btn-lg">Ke Dashboard</a>
    @endguest
</div>

<h3>Layanan</h3>
<div class="row mb-4">
    @foreach ($layanan as $s)
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">{{ $s->nama_service }}</h5>
                    <p class="mb-0">Rp {{ number_format($s->harga_service, 0, ',', '.') }}</p>
                    <small class="text-muted">{{ $s->durasi }} menit</small>
                </div>
            </div>
        </div>
    @endforeach
</div>

<h3>Barber Kami</h3>
<div class="row mb-4">
    @foreach ($barber as $b)
        <div class="col-md-3 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">{{ $b->nama_barber }}</h5>
                    <p class="mb-0">{{ $b->outlet->nama_outlet }}</p>
                    @if ($b->rating_rata)
                        <small class="text-muted">Rating {{ $b->rating_rata }} ({{ $b->jumlah_ulasan }} ulasan)</small>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

<h3>Cabang</h3>
<ul>
    @foreach ($outlet as $o)
        <li><strong>{{ $o->nama_outlet }}</strong> — {{ $o->alamat_outlet }}</li>
    @endforeach
</ul>
@endsection
