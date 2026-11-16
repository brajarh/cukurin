@include('partials.nav-admin')
<h1>Tambah Cabang</h1>
@include('partials.flash')
<form method="POST" action="{{ route('outlet.store') }}">
    @csrf
    @include('outlet._form')
    <button type="submit">Simpan</button>
    <a href="{{ route('outlet.index') }}">Batal</a>
</form>
