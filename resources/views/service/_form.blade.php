<p>
    <label>Nama Layanan</label><br>
    <input name="nama_service" value="{{ old('nama_service', $s->nama_service ?? '') }}">
</p>
<p>
    <label>Harga (Rp)</label><br>
    <input type="number" name="harga_service" min="0" value="{{ old('harga_service', $s->harga_service ?? '') }}">
</p>
<p>
    <label>Durasi (menit)</label><br>
    <input type="number" name="durasi" min="5" max="480" value="{{ old('durasi', $s->durasi ?? '') }}">
</p>
