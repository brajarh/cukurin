<h1>Bukti Pemesanan</h1>
@include('partials.flash')

<h2>Kode Booking: {{ $b->kode_booking }}</h2>
<p>Simpan kode ini untuk cek status, mengubah, atau membatalkan booking.</p>

<table border="1" cellpadding="4">
    <tr><td>Status</td><td><b>{{ $b->status }}</b></td></tr>
    <tr><td>Nama</td><td>{{ $b->customer->nama }}</td></tr>
    <tr><td>No HP</td><td>{{ $b->customer->no_hp }}</td></tr>
    <tr><td>Cabang</td><td>{{ $b->barber->outlet->nama_outlet }} - {{ $b->barber->outlet->alamat_outlet }}</td></tr>
    <tr><td>Barber</td><td>{{ $b->barber->nama_barber }}</td></tr>
    <tr><td>Layanan</td><td>{{ $b->service->nama_service }} ({{ formatDurasi($b->service->durasi) }})</td></tr>
    <tr><td>Harga</td><td>{{ numberToRupiah($b->service->harga_service) }}</td></tr>
    <tr><td>Tanggal</td><td>{{ $b->tanggal->format('d-m-Y') }}</td></tr>
    <tr><td>Jam</td><td>{{ formatJam($b->jam_mulai) }} - {{ formatJam($b->jam_selesai) }}</td></tr>
</table>

@if ($b->status === \App\Models\Booking::STATUS_DIPESAN)
    <div>
        <a href="{{ route('booking.ubah', $b->kode_booking) }}">Ubah Jadwal</a>
        <form method="POST" style="display:inline"
              action="{{ route('booking.batal', $b->kode_booking) }}"
              onsubmit="return confirm('Batalkan booking ini?')">
            @csrf
            <button type="submit">Batalkan Booking</button>
        </form>
    </div>
@endif

<p>
    <a href="{{ route('booking.riwayat') }}">Riwayat booking</a> |
    <a href="{{ route('booking.create') }}">Booking baru</a>
</p>
