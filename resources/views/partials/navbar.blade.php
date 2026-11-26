@php
    $user = auth()->user();
    $isAdmin = $user?->hasRole('admin');
    $isPelanggan = $user?->hasRole('pelanggan');
@endphp
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">Cukur.in</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ url('/') }}">Beranda</a></li>
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ url('/about') }}">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ url('/contact') }}">Kontak</a></li>
                @endguest

                @if ($isAdmin)
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Dashboard</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Data Master</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('service.index') }}">Layanan</a></li>
                            <li><a class="dropdown-item" href="{{ route('outlet.index') }}">Cabang</a></li>
                            <li><a class="dropdown-item" href="{{ route('barber.index') }}">Barber</a></li>
                            <li><a class="dropdown-item" href="{{ route('jadwal.index') }}">Jadwal Kerja</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.booking.index') }}">Booking</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.booking.kalender') }}">Kalender</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('pembayaran.index') }}">Pembayaran</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('ulasan.index') }}">Ulasan</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.laporan') }}">Laporan</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.perubahan.index') }}">Ubah Jadwal</a></li>
                @endif

                @if ($isPelanggan)
                    <li class="nav-item"><a class="nav-link" href="{{ route('home') }}">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('booking.create') }}">Buat Booking</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('booking.riwayat') }}">Riwayat</a></li>
                @endif
            </ul>

            <ul class="navbar-nav ms-auto">
                @guest
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">{{ $user->name }}</a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button class="dropdown-item" type="submit">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
