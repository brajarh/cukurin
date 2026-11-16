<p>
    <label>Barber</label><br>
    <select name="id_barber">
        <option value="">-- pilih barber --</option>
        @foreach ($barber as $b)
            <option value="{{ $b->id_barber }}"
                {{ (string) old('id_barber', $j->id_barber ?? '') === (string) $b->id_barber ? 'selected' : '' }}>
                {{ $b->nama_barber }} ({{ $b->outlet->nama_outlet }})
            </option>
        @endforeach
    </select>
</p>
<p>
    <label>Hari</label><br>
    <select name="hari">
        <option value="">-- pilih hari --</option>
        @foreach (\App\Models\BarberSchedule::HARI as $no => $nama)
            <option value="{{ $no }}" {{ (string) old('hari', $j->hari ?? '') === (string) $no ? 'selected' : '' }}>
                {{ $nama }}
            </option>
        @endforeach
    </select>
</p>
<p>
    <label>Jam Mulai</label><br>
    <input type="time" name="jam_mulai" value="{{ old('jam_mulai', isset($j) ? formatJam($j->jam_mulai) : '09:00') }}">
</p>
<p>
    <label>Jam Selesai</label><br>
    <input type="time" name="jam_selesai" value="{{ old('jam_selesai', isset($j) ? formatJam($j->jam_selesai) : '17:00') }}">
</p>
