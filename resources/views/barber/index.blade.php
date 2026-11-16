@include('partials.nav-admin')
<h1>Daftar Barber</h1>
@include('partials.flash')

<form method="GET" action="{{ route('barber.index') }}">
    <input name="q" value="{{ $cari }}" placeholder="Cari nama barber">
    <select name="id_outlet">
        <option value="">Semua cabang</option>
        @foreach ($outlet as $o)
            <option value="{{ $o->id_outlet }}" {{ (string) $idOutlet === (string) $o->id_outlet ? 'selected' : '' }}>
                {{ $o->nama_outlet }}
            </option>
        @endforeach
    </select>
    <button type="submit">Cari</button>
    <a href="{{ route('barber.index') }}">Reset</a>
</form>
<p><a href="{{ route('barber.create') }}">+ Tambah Barber</a></p>

<table border="1" cellpadding="4">
    <tr><th>Nama</th><th>No HP</th><th>Cabang</th><th>Aksi</th></tr>
    @forelse ($data as $b)
        <tr>
            <td>{{ $b->nama_barber }}</td>
            <td>{{ $b->no_hp }}</td>
            <td>{{ $b->outlet->nama_outlet }}</td>
            <td>
                <a href="{{ route('barber.show', $b->id_barber) }}">Detail</a> |
                <a href="{{ route('barber.edit', $b->id_barber) }}">Ubah</a>
                <form method="POST" style="display:inline"
                      action="{{ route('barber.destroy', $b->id_barber) }}"
                      onsubmit="return confirm('Hapus barber ini?')">
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
