@include('partials.nav-admin')
<h1>Ubah Jadwal Kerja</h1>
@include('partials.flash')
<form method="POST" action="{{ route('jadwal.update', $j->id_schedule) }}">
    @csrf
    @method('PUT')
    @include('jadwal._form')
    <button type="submit">Perbarui</button>
    <a href="{{ route('jadwal.index') }}">Batal</a>
</form>
