@extends('layouts.app')

@section('title', 'Tulis Ulasan')

@section('content')
<h1 class="h3">Ulas Layanan</h1>
<p>{{ $b->service->nama_service }} bersama <strong>{{ $b->barber->nama_barber }}</strong> ({{ $b->kode_booking }})</p>

<form method="POST" action="{{ route('ulasan.store') }}">
    @csrf
    <input type="hidden" name="id_booking" value="{{ $b->id_booking }}">
    <div class="mb-3">
        <label class="form-label">Rating</label>
        <select name="rating" class="form-select @error('rating') is-invalid @enderror">
            <option value="">-- pilih --</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}" @selected((int) old('rating') === $i)>{{ $i }}</option>
            @endfor
        </select>
        @error('rating')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="mb-3">
        <label class="form-label">Komentar (opsional)</label>
        <textarea name="komentar" rows="3" class="form-control @error('komentar') is-invalid @enderror">{{ old('komentar') }}</textarea>
        @error('komentar')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <button class="btn btn-dark" type="submit">Kirim Ulasan</button>
</form>
@endsection