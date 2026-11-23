@extends('layouts.app')
@section('content')
<h1>Ubah Layanan</h1>
<form method="POST" action="{{ route('service.update', $s->id_service) }}">
    @csrf
    @method('PUT')
    @include('service._form')
    <button type="submit">Perbarui</button>
    <a href="{{ route('service.index') }}">Batal</a>
</form>
@endsection
