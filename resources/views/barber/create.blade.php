@extends('layouts.app')
@section('title', 'Tambah Barber')
@section('content')
<h1 class="h3 mb-3">Tambah Barber</h1>
<form method="POST" action="{{ route('barber.store') }}">
    @csrf
    @include('barber._form')
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a class="btn btn-outline-secondary" href="{{ route('barber.index') }}">Batal</a>
</form>
@endsection
