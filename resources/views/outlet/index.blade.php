<h1>Daftar Barber</h1>
<table border="1">
<tr><th>Nama Outlet</th><th>Alamat</th><th></th></tr>
@foreach($data as $b)
<tr>
<td>{{ $o->nama_outlet }}</td>
<td>{{ $o->alamat }}</td>

<td><a href="/outlet/{{ $o->id_outlet }}">Detail</a></td>
</tr>
@endforeach
</table>
