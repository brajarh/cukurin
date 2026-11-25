@extends('layouts.app')
@section('title', 'Jadwal Kerja')
@section('content')
<h1 class="h3 mb-3">Jadwal Kerja Barber</h1>

<form method="GET" action="{{ route('jadwal.index') }}" class="row g-2 mb-3">
    <div class="col-auto"><input class="form-control" name="q" value="{{ $cari }}" placeholder="Cari nama barber"></div>
    <div class="col-auto">
        <select class="form-select" name="id_barber">
            <option value="">Semua barber</option>
            @foreach ($barber as $b)
                <option value="{{ $b->id_barber }}" {{ (string) $idBarber === (string) $b->id_barber ? 'selected' : '' }}>{{ $b->nama_barber }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Filter</button>
        <a class="btn btn-outline-secondary" href="{{ route('jadwal.index') }}">Reset</a>
    </div>
    <div class="col-auto ms-auto"><a class="btn btn-success" href="{{ route('jadwal.create') }}">+ Tambah Jadwal</a></div>
</form>

<table class="table table-striped table-bordered align-middle">
    <thead><tr><th>Barber</th><th>Cabang</th><th>Hari</th><th>Jam Kerja</th><th style="width:170px">Aksi</th></tr></thead>
    <tbody>
    @forelse ($data as $j)
        <tr>
            <td>{{ $j->barber->nama_barber }}</td>
            <td>{{ $j->barber->outlet->nama_outlet }}</td>
            <td>{{ \App\Models\BarberSchedule::HARI[$j->hari] }}</td>
            <td>{{ formatJam($j->jam_mulai) }} - {{ formatJam($j->jam_selesai) }}</td>
            <td>
                <a class="btn btn-sm btn-warning" href="{{ route('jadwal.edit', $j->id_schedule) }}">Ubah</a>
                <form method="POST" class="d-inline" action="{{ route('jadwal.destroy', $j->id_schedule) }}"
                      onsubmit="return confirm('Hapus jadwal ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center">Belum ada jadwal.</td></tr>
    @endforelse
    </tbody>
</table>
{{ $data->links('pagination::bootstrap-5') }}
@endsection
