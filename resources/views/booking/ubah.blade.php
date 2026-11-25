@extends('layouts.app')
@section('content')
<h1>Ubah Jadwal Booking {{ $b->kode_booking }}</h1>

<p>
    {{ $b->service->nama_service }} dengan {{ $b->barber->nama_barber }} ({{ $b->barber->outlet->nama_outlet }})<br>
    Jadwal sekarang: {{ $b->tanggal->format('d-m-Y') }}, {{ formatJam($b->jam_mulai) }} - {{ formatJam($b->jam_selesai) }}
</p>

<form method="POST" action="{{ route('booking.perbarui', $b->kode_booking) }}">
    @csrf
    @method('PUT')

    <p>
        <label>Tanggal baru</label><br>
        <input type="date" id="tanggal" name="tanggal" required
               min="{{ \App\Services\SlotService::sekarang()->format('Y-m-d') }}"
               value="{{ old('tanggal', $b->tanggal->format('Y-m-d')) }}">
    </p>

    <div>
        <label>Jam baru</label>
        <div id="slot"></div>
    </div>

    <p>
        <button type="submit">Simpan Perubahan</button>
        <a href="{{ route('booking.receipt', $b->kode_booking) }}">Batal</a>
    </p>
</form>

<script>
    const inTanggal = document.getElementById('tanggal');
    const boxSlot = document.getElementById('slot');

    async function muatSlot() {
        boxSlot.textContent = 'Memuat jam...';
        // parameter abaikan = booking ini sendiri, supaya jamnya sendiri tetap bisa dipilih
        const url = `{{ route('booking.slot') }}?id_barber={{ $b->id_barber }}&id_service={{ $b->id_service }}&abaikan={{ $b->id_booking }}&tanggal=${inTanggal.value}`;
        try {
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            boxSlot.textContent = '';

            if (!data.slot || data.slot.length === 0) {
                boxSlot.textContent = 'Tidak ada jam kosong pada tanggal ini.';
                return;
            }
            data.slot.forEach(s => {
                const label = document.createElement('label');
                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'jam_mulai';
                radio.value = s.mulai;
                radio.required = true;
                label.append(radio, ` ${s.mulai} - ${s.selesai}`);
                boxSlot.append(label, document.createElement('br'));
            });
        } catch (e) {
            boxSlot.textContent = 'Gagal memuat jam. Coba lagi.';
        }
    }

    inTanggal.addEventListener('change', muatSlot);
    muatSlot();
</script>
@endsection
