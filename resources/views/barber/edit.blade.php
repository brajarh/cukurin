@extends('layouts.app')
@section('title', 'Ubah Barber')
@section('content')
<h1 class="h3 mb-3">Ubah Barber</h1>
<form method="POST" action="{{ route('barber.update', $b->id_barber) }}">
    @csrf
    @method('PUT')
    @include('barber._form')
    <button type="submit" class="btn btn-primary">Perbarui</button>
    <a class="btn btn-outline-secondary" href="{{ route('barber.index') }}">Batal</a>
</form>
@endsection
