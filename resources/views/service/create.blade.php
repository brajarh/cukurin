@include('partials.nav-admin')
<h1>Tambah Layanan</h1>
@include('partials.flash')
<form method="POST" action="{{ route('service.store') }}">
    @csrf
    @include('service._form')
    <button type="submit">Simpan</button>
    <a href="{{ route('service.index') }}">Batal</a>
</form>
