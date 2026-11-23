@extends('layouts.app')
@section('content')
<h1>Tambah Jadwal Kerja</h1>
<form method="POST" action="{{ route('jadwal.store') }}">
    @csrf
    @include('jadwal._form')
    <button type="submit">Simpan</button>
    <a href="{{ route('jadwal.index') }}">Batal</a>
</form>
@endsection
