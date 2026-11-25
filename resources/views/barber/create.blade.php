@extends('layouts.app')
@section('content')
<h1>Tambah Barber</h1>
<form method="POST" action="{{ route('barber.store') }}">
    @csrf
    @include('barber._form')
    <button type="submit">Simpan</button>
    <a href="{{ route('barber.index') }}">Batal</a>
</form>
@endsection
