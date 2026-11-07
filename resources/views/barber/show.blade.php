<h1>{{ $b->nama_barber }}</h1>
<ul>
@foreach($b->service as $s)
<li>{{ $s->nama_service }} ({{ $s->harga }})</li>
@endforeach
</ul>
