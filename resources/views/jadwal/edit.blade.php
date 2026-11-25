@extends('layouts.app')
@section('content')
<h1>Ubah Jadwal Kerja</h1>
<form method="POST" action="{{ route('jadwal.update', $j->id_schedule) }}">
    @csrf
    @method('PUT')
    @include('jadwal._form')
    <button type="submit">Perbarui</button>
    <a href="{{ route('jadwal.index') }}">Batal</a>
</form>
@endsection
