@extends('layouts.app')

@section('title', 'Ubah Jadwal Booking')

@section('content')
<h1 class="h3 mb-3">Ubah Jadwal Booking {{ $b->kode_booking }}</h1>

<div class="card mb-4">
    <div class="card-body">
        <p class="mb-1">Barber: <strong>{{ $b->barber->nama_barber }}</strong> ({{ $b->barber->outlet->nama_outlet }})</p>
        <p class="mb-1">Layanan:
            @foreach ($b->detail as $dt)
                {{ $dt->service->nama_service }} ({{ formatDurasi($dt->durasi) }}){{ $loop->last ? '' : ',' }}
            @endforeach
        </p>
        <p class="mb-1">Total durasi: <strong>{{ formatDurasi($b->totalDurasi()) }}</strong></p>
        <p class="mb-0">Jadwal sekarang:
            <strong>{{ $b->tanggal->format('d-m-Y') }}, {{ formatJam($b->jam_mulai) }} - {{ formatJam($b->jam_selesai) }}</strong>
        </p>
    </div>
</div>

<form method="POST" action="{{ route('booking.perbarui', $b->kode_booking) }}" class="mb-4">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label for="tanggal" class="form-label">Tanggal baru</label>
        <input type="date" id="tanggal" name="tanggal" required
               class="form-control @error('tanggal') is-invalid @enderror"
               min="{{ \App\Services\SlotService::sekarang()->format('Y-m-d') }}"
               value="{{ old('tanggal', $b->tanggal->format('Y-m-d')) }}">
        @error('tanggal') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label class="form-label">Jam baru</label>
        <div id="slot" class="@error('jam_mulai') is-invalid @enderror"></div>
        @error('jam_mulai') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="mb-3">
        <label for="alasan" class="form-label">Alasan perubahan</label>
        <textarea id="alasan" name="alasan" rows="3" maxlength="255" required
                  class="form-control @error('alasan') is-invalid @enderror">{{ old('alasan') }}</textarea>
        @error('alasan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    <a href="{{ route('booking.receipt', $b->kode_booking) }}" class="btn btn-outline-secondary">Kembali</a>
</form>

<h2 class="h5">Riwayat perubahan jadwal booking ini</h2>
<table class="table table-striped table-bordered">
    <thead>
        <tr><th>Waktu</th><th>Jadwal lama</th><th>Jadwal baru</th><th>Alasan</th></tr>
    </thead>
    <tbody>
        @forelse ($riwayat as $r)
            <tr>
                <td>{{ $r->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                <td>{{ $r->tanggal_lama->format('d-m-Y') }}, {{ formatJam($r->jam_mulai_lama) }}</td>
                <td>{{ $r->tanggal_baru->format('d-m-Y') }}, {{ formatJam($r->jam_mulai_baru) }}</td>
                <td>{{ $r->alasan }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center">Belum pernah diubah.</td></tr>
        @endforelse
    </tbody>
</table>

<script>
    const inTanggal = document.getElementById('tanggal');
    const boxSlot = document.getElementById('slot');
    let nomorRequest = 0;

    async function muatSlot() {
        const requestSaatIni = ++nomorRequest;

        if (!inTanggal.value) {
            boxSlot.textContent = 'Pilih tanggal terlebih dahulu.';
            return;
        }

        boxSlot.textContent = 'Memuat jam...';

        const params = new URLSearchParams({
            id_barber: '{{ $b->id_barber }}',
            abaikan: '{{ $b->id_booking }}',
            tanggal: inTanggal.value
        });

        try {
            const res = await fetch(`{{ route('booking.slot') }}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Gagal mengambil slot.');
            const data = await res.json();
            if (requestSaatIni !== nomorRequest) return;

            boxSlot.textContent = '';

            if (!data.slot || data.slot.length === 0) {
                boxSlot.textContent = 'Tidak ada jam kosong pada tanggal ini.';
                return;
            }

            data.slot.forEach(s => {
                const wrap = document.createElement('div');
                wrap.className = 'form-check';
                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'jam_mulai';
                radio.value = s.mulai;
                radio.required = true;
                radio.className = 'form-check-input';
                radio.id = 'jam_' + s.mulai.replace(':', '');
                const label = document.createElement('label');
                label.className = 'form-check-label';
                label.htmlFor = radio.id;
                label.textContent = `${s.mulai} - ${s.selesai}`;
                wrap.append(radio, label);
                boxSlot.append(wrap);
            });
        } catch (e) {
            if (requestSaatIni === nomorRequest) {
                boxSlot.textContent = 'Gagal memuat jam. Coba lagi.';
            }
        }
    }

    inTanggal.addEventListener('change', muatSlot);
    muatSlot();
</script>
@endsection
