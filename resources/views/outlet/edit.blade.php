@include('partials.nav-admin')
<h1>Ubah Cabang</h1>
@include('partials.flash')
<form method="POST" action="{{ route('outlet.update', $o->id_outlet) }}">
    @csrf
    @method('PUT')
    @include('outlet._form')
    <button type="submit">Perbarui</button>
    <a href="{{ route('outlet.index') }}">Batal</a>
</form>
