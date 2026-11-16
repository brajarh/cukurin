@include('partials.nav-admin')
<h1>Tambah Barber</h1>
@include('partials.flash')
<form method="POST" action="{{ route('barber.store') }}">
    @csrf
    @include('barber._form')
    <button type="submit">Simpan</button>
    <a href="{{ route('barber.index') }}">Batal</a>
</form>
