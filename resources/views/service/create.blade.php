@extends('layouts.app')
@section('content')
<h1>Tambah Layanan</h1>
<form method="POST" action="{{ route('service.store') }}">
    @csrf
    @include('service._form')
    <button type="submit">Simpan</button>
    <a href="{{ route('service.index') }}">Batal</a>
</form>
@endsection
