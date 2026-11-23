@extends('layouts.app')
@section('content')
<h1>Daftar Cabang</h1>

<form method="GET" action="{{ route('outlet.index') }}">
    <input name="q" value="{{ $cari }}" placeholder="Cari nama/alamat cabang">
    <button type="submit">Cari</button>
    <a href="{{ route('outlet.index') }}">Reset</a>
</form>
<p><a href="{{ route('outlet.create') }}">+ Tambah Cabang</a></p>

<table border="1" cellpadding="4">
    <tr><th>Nama Cabang</th><th>Alamat</th><th>Jumlah Barber</th><th>Aksi</th></tr>
    @forelse ($data as $o)
        <tr>
            <td>{{ $o->nama_outlet }}</td>
            <td>{{ $o->alamat_outlet }}</td>
            <td>{{ $o->barber_count }}</td>
            <td>
                <a href="{{ route('barber.index', ['id_outlet' => $o->id_outlet]) }}">Lihat Barber</a> |
                <a href="{{ route('outlet.edit', $o->id_outlet) }}">Ubah</a>
                <form method="POST" style="display:inline"
                      action="{{ route('outlet.destroy', $o->id_outlet) }}"
                      onsubmit="return confirm('Hapus cabang ini?')">
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
@endsection
