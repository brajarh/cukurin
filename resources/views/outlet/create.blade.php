@extends('layouts.app')

@section('title', 'Tambah Cabang')

@section('content')
<h1 class="h3 mb-3">Tambah Cabang</h1>

<form method="POST" action="{{ route('outlet.store') }}">
    @csrf
    @include('outlet._form')
</form>
@endsection