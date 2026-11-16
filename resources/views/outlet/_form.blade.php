<p>
    <label>Nama Cabang</label><br>
    <input name="nama_outlet" value="{{ old('nama_outlet', $o->nama_outlet ?? '') }}">
</p>
<p>
    <label>Alamat</label><br>
    <input name="alamat_outlet" size="50" value="{{ old('alamat_outlet', $o->alamat_outlet ?? '') }}">
</p>
