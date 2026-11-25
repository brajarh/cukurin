@extends('layouts.app')

@section('title', 'Catat Pembayaran')

@section('content')
<h1 class="h3">Catat Pembayaran</h1>

<div class="card mb-3">
    <div class="card-body">
        <p class="mb-1">Kode: <strong>{{ $b->kode_booking }}</strong></p>
        <p class="mb-1">Pelanggan: {{ $b->customer->nama }}</p>
        <p class="mb-1">Layanan: {{ $b->service->nama_service }} ({{ $b->barber->nama_barber }})</p>
        <p class="mb-0">Total: <strong>Rp {{ number_format($b->service->harga_service, 0, ',', '.') }}</strong></p>
    </div>
</div>

<form method="POST" action="{{ route('pembayaran.store') }}">
    @csrf
    <input type="hidden" name="id_booking" value="{{ $b->id_booking }}">
    <div class="mb-3">
        <label class="form-label">Metode pembayaran</label>
        <select name="metode" class="form-select @error('metode') is-invalid @enderror">
            <option value="">-- pilih --</option>
            @foreach (\App\Models\Pembayaran::METODE as $m)
                <option value="{{ $m }}" @selected(old('metode') === $m)>{{ strtoupper($m) }}</option>
            @endforeach
        </select>
        @error('metode')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <button class="btn btn-dark" type="submit">Simpan Pembayaran</button>
    <a class="btn btn-link" href="{{ route('pembayaran.index') }}">Batal</a>
</form>
@endsection