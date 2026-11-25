@extends('layouts.app')
@section('title', 'Tambah Jadwal Kerja')
@section('content')
<h1 class="h3 mb-3">Tambah Jadwal Kerja</h1>
<form method="POST" action="{{ route('jadwal.store') }}">
    @csrf
    @include('jadwal._form')
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a class="btn btn-outline-secondary" href="{{ route('jadwal.index') }}">Batal</a>
</form>
@endsection
