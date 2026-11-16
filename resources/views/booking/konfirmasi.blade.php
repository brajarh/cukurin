<h1>Konfirmasi Booking</h1>
@include('partials.flash')
<p>Periksa kembali rincian pesanan kamu.</p>

<table border="1" cellpadding="4">
    <tr><td>Cabang</td><td>{{ $barber->outlet->nama_outlet }}</td></tr>
    <tr><td>Layanan</td><td>{{ $service->nama_service }} ({{ formatDurasi($service->durasi) }})</td></tr>
    <tr><td>Harga</td><td>{{ numberToRupiah($service->harga_service) }}</td></tr>
    <tr><td>Barber</td><td>{{ $barber->nama_barber }}</td></tr>
    <tr><td>Tanggal</td><td>{{ \Carbon\Carbon::parse($d['tanggal'])->format('d-m-Y') }}</td></tr>
    <tr><td>Jam</td><td>{{ $d['jam_mulai'] }} - {{ $selesai->format('H:i') }}</td></tr>
    <tr><td>Nama</td><td>{{ $d['nama'] }}</td></tr>
    <tr><td>No HP</td><td>{{ $d['no_hp'] }}</td></tr>
</table>

<form method="POST" action="{{ route('booking.store') }}">
    @csrf
    @foreach (['id_barber', 'id_service', 'tanggal', 'jam_mulai', 'nama', 'no_hp'] as $k)
        <input type="hidden" name="{{ $k }}" value="{{ $d[$k] }}">
    @endforeach
    <p>
        <button type="submit">Konfirmasi Booking</button>
        <a href="javascript:history.back()">Ubah</a>
    </p>
</form>
