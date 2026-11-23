@extends('layouts.app')
@section('content')
<h1>Tambah Cabang</h1>
<form method="POST" action="{{ route('outlet.store') }}">
    @csrf
    @include('outlet._form')
    <button type="submit">Simpan</button>
    <a href="{{ route('outlet.index') }}">Batal</a>
</form>
@endsection
