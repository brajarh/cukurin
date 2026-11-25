@extends('layouts.app')
@section('title', 'Ubah Jadwal Kerja')
@section('content')
<h1 class="h3 mb-3">Ubah Jadwal Kerja</h1>
<form method="POST" action="{{ route('jadwal.update', $j->id_schedule) }}">
    @csrf
    @method('PUT')
    @include('jadwal._form')
    <button type="submit" class="btn btn-primary">Perbarui</button>
    <a class="btn btn-outline-secondary" href="{{ route('jadwal.index') }}">Batal</a>
</form>
@endsection
