@extends('layouts.app')
@section('title', 'Barber')
@section('content')
<h1 class="h3 mb-3">Daftar Barber</h1>

<form method="GET" action="{{ route('barber.index') }}" class="row g-2 mb-3">
    <div class="col-auto"><input class="form-control" name="q" value="{{ $cari }}" placeholder="Cari nama barber"></div>
    <div class="col-auto">
        <select class="form-select" name="id_outlet">
            <option value="">Semua cabang</option>
            @foreach ($outlet as $o)
                <option value="{{ $o->id_outlet }}" {{ (string) $idOutlet === (string) $o->id_outlet ? 'selected' : '' }}>{{ $o->nama_outlet }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Cari</button>
        <a class="btn btn-outline-secondary" href="{{ route('barber.index') }}">Reset</a>
    </div>
    <div class="col-auto ms-auto"><a class="btn btn-success" href="{{ route('barber.create') }}">+ Tambah Barber</a></div>
</form>

<table class="table table-striped table-bordered align-middle">
    <thead><tr><th>Nama</th><th>No HP</th><th>Cabang</th><th>Rating</th><th style="width:230px">Aksi</th></tr></thead>
    <tbody>
    @forelse ($data as $b)
        <tr>
            <td>{{ $b->nama_barber }}</td>
            <td>{{ $b->no_hp }}</td>
            <td>{{ $b->outlet->nama_outlet }}</td>
            <td>{{ $b->jumlah_ulasan > 0 ? number_format($b->rating_rata, 2) . ' (' . $b->jumlah_ulasan . ' ulasan)' : '-' }}</td>
            <td>
                <a class="btn btn-sm btn-info" href="{{ route('barber.show', $b->id_barber) }}">Detail</a>
                <a class="btn btn-sm btn-warning" href="{{ route('barber.edit', $b->id_barber) }}">Ubah</a>
                <form method="POST" class="d-inline" action="{{ route('barber.destroy', $b->id_barber) }}"
                      onsubmit="return confirm('Hapus barber ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center">Belum ada data.</td></tr>
    @endforelse
    </tbody>
</table>
{{ $data->links('pagination::bootstrap-5') }}
@endsection
