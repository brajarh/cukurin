@extends('layouts.app')
@section('content')
<h1>Ubah Cabang</h1>
<form method="POST" action="{{ route('outlet.update', $o->id_outlet) }}">
    @csrf
    @method('PUT')
    @include('outlet._form')
    <button type="submit">Perbarui</button>
    <a href="{{ route('outlet.index') }}">Batal</a>
</form>
@endsection
