@include('partials.nav-admin')
<h1>Daftar Layanan</h1>
@include('partials.flash')

<form method="GET" action="{{ route('service.index') }}">
    <input name="q" value="{{ $cari }}" placeholder="Cari nama layanan">
    <button type="submit">Cari</button>
    <a href="{{ route('service.index') }}">Reset</a>
</form>
<p><a href="{{ route('service.create') }}">+ Tambah Layanan</a></p>

<table border="1" cellpadding="4">
    <tr><th>Nama</th><th>Harga</th><th>Durasi</th><th>Aksi</th></tr>
    @forelse ($data as $s)
        <tr>
            <td>{{ $s->nama_service }}</td>
            <td>{{ numberToRupiah($s->harga_service) }}</td>
            <td>{{ formatDurasi($s->durasi) }}</td>
            <td>
                <a href="{{ route('service.edit', $s->id_service) }}">Ubah</a>
                <form method="POST" style="display:inline"
                      action="{{ route('service.destroy', $s->id_service) }}"
                      onsubmit="return confirm('Hapus layanan ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit">Hapus</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="4">Belum ada data.</td></tr>
    @endforelse
</table>
