<h1>Daftar Barber</h1>
<table border="1">
<tr><th>Nama</th><th>No HP</th><th>Outlet</th><th></th></tr>
@foreach($data as $b)
<tr>
<td>{{ $b->nama_barber }}</td>
<td>{{ $b->no_hp }}</td>
<td>{{ $b->outlet->nama_outlet }}</td>
<td><a href="/barber/{{ $b->id_barber }}">Detail</a></td>
</tr>
@endforeach
</table>
