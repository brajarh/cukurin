@extends('layouts.app')

@section('title', 'Data Cabang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Data Cabang</h1>
    <a href="{{ route('outlet.create') }}" class="btn btn-dark">+ Tambah Cabang</a>
</div>

<form method="GET" action="{{ route('outlet.index') }}" class="mb-3 d-flex gap-2" style="max-width: 400px;">
    <input type="text" name="q" value="{{ $cari }}" class="form-control" placeholder="Cari nama atau alamat cabang...">
    <button type="submit" class="btn btn-dark">Cari</button>
    @if ($cari)
        <a href="{{ route('outlet.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Cabang</th>
            <th>Alamat</th>
            <th>Jumlah Barber</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $i => $o)
            <tr>
                <td>{{ $data->firstItem() + $i }}</td>
                <td>{{ $o->nama_outlet }}</td>
                <td>{{ $o->alamat_outlet }}</td>
                <td>{{ $o->barber_count }}</td>
                <td>
                    <a href="{{ route('outlet.edit', $o->id_outlet) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form method="POST" action="{{ route('outlet.destroy', $o->id_outlet) }}" class="d-inline" onsubmit="return confirm('Hapus cabang ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Data cabang tidak ditemukan.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-3">{{ $data->links() }}</div>
@endsection