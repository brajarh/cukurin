@extends('layouts.app')

@section('title', 'Ulasan')

@section('content')
<h1 class="h3">Ulasan Pelanggan</h1>

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th>Barber</th>
            <th>Pelanggan</th>
            <th>Rating</th>
            <th>Komentar</th>
            <th>Tanggal</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $u)
            <tr>
                <td>{{ $u->barber->nama_barber }}</td>
                <td>{{ $u->pelanggan->name }}</td>
                <td>{{ $u->rating }}/5</td>
                <td>{{ $u->komentar }}</td>
                <td>{{ $u->created_at->format('d/m/Y') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Belum ada ulasan.</td>
            </tr>
        @endforelse
    </tbody>
</table>
{{ $data->links() }}
@endsection