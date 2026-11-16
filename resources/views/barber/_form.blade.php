<p>
    <label>Nama Barber</label><br>
    <input name="nama_barber" value="{{ old('nama_barber', $b->nama_barber ?? '') }}">
</p>
<p>
    <label>No HP</label><br>
    <input name="no_hp" value="{{ old('no_hp', $b->no_hp ?? '') }}">
</p>
<p>
    <label>Cabang</label><br>
    <select name="id_outlet">
        <option value="">-- pilih cabang --</option>
        @foreach ($outlet as $o)
            <option value="{{ $o->id_outlet }}"
                {{ (string) old('id_outlet', $b->id_outlet ?? '') === (string) $o->id_outlet ? 'selected' : '' }}>
                {{ $o->nama_outlet }}
            </option>
        @endforeach
    </select>
</p>
<p>
    <label>Layanan yang bisa dikerjakan</label><br>
    @foreach ($service as $sv)
        <label>
            <input type="checkbox" name="service_ids[]" value="{{ $sv->id_service }}"
                {{ in_array($sv->id_service, old('service_ids', $idService ?? [])) ? 'checked' : '' }}>
            {{ $sv->nama_service }} ({{ formatDurasi($sv->durasi) }})
        </label><br>
    @endforeach
</p>
