<div class="mb-3">
    <label class="form-label">Nama Cabang</label>
    <input type="text" name="nama_outlet" value="{{ old('nama_outlet', $outlet->nama_outlet ?? '') }}" class="form-control @error('nama_outlet') is-invalid @enderror">
    @error('nama_outlet')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Alamat Cabang</label>
    <input type="text" name="alamat_outlet" value="{{ old('alamat_outlet', $outlet->alamat_outlet ?? '') }}" class="form-control @error('alamat_outlet') is-invalid @enderror">
    @error('alamat_outlet')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<button type="submit" class="btn btn-dark">Simpan</button>
<a href="{{ route('outlet.index') }}" class="btn btn-link">Batal</a>