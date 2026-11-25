@extends('layouts.app')
@section('content')
<h1>Ubah Barber</h1>
<form method="POST" action="{{ route('barber.update', $b->id_barber) }}">
    @csrf
    @method('PUT')
    @include('barber._form')
    <button type="submit">Perbarui</button>
    <a href="{{ route('barber.index') }}">Batal</a>
</form>
@endsection
