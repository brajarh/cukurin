@extends('layouts.app')

@section('title', 'Edit Cabang')

@section('content')
<h1 class="h3 mb-3">Edit Cabang</h1>

<form method="POST" action="{{ route('outlet.update', $outlet->id_outlet) }}">
    @csrf
    @method('PUT')
    @include('outlet._form')
</form>
@endsection