<div class="mb-3">
    <label class="form-label">Nama Barber</label>
    <input class="form-control" name="nama_barber" value="{{ old('nama_barber', $b->nama_barber ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">No HP</label>
    <input class="form-control" name="no_hp" value="{{ old('no_hp', $b->no_hp ?? '') }}">
</div>
<div class="mb-3">
    <label class="form-label">Cabang</label>
    <select class="form-select" name="id_outlet">
        <option value="">-- pilih cabang --</option>
        @foreach ($outlet as $o)
            <option value="{{ $o->id_outlet }}"
                {{ (string) old('id_outlet', $b->id_outlet ?? '') === (string) $o->id_outlet ? 'selected' : '' }}>
                {{ $o->nama_outlet }}
            </option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Layanan yang bisa dikerjakan</label>
    @foreach ($service as $sv)
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="service_ids[]" value="{{ $sv->id_service }}" id="sv{{ $sv->id_service }}"
                {{ in_array($sv->id_service, old('service_ids', $idService ?? [])) ? 'checked' : '' }}>
            <label class="form-check-label" for="sv{{ $sv->id_service }}">{{ $sv->nama_service }} ({{ formatDurasi($sv->durasi) }})</label>
        </div>
    @endforeach
</div>
