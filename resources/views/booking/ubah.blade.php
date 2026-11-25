@extends('layouts.app')

@section('content')

<h1>Ubah Jadwal Booking {{ $b->kode_booking }}</h1>

<p>
    Barber: {{ $b->barber->nama_barber }}
    ({{ $b->barber->outlet->nama_outlet }})
</p>

<h3>Layanan yang Dipesan</h3>

<ul>

    @foreach ($b->detail as $dt)

        <li>
            {{ $dt->service->nama_service }}
            - {{ formatDurasi($dt->durasi) }}
        </li>

    @endforeach

</ul>

<p>
    Total Harga:
    <strong>
        {{ numberToRupiah($b->totalHarga()) }}
    </strong>
</p>

<p>
    Total Durasi:
    <strong>
        {{ formatDurasi($b->totalDurasi()) }}
    </strong>
</p>

<p>
    Jadwal sekarang:
    {{ $b->tanggal->format('d-m-Y') }},
    {{ formatJam($b->jam_mulai) }} -
    {{ formatJam($b->jam_selesai) }}
</p>

<form method="POST"
      action="{{ route('booking.perbarui', $b->kode_booking) }}">

    @csrf
    @method('PUT')

    <p>

        <label>Tanggal baru</label><br>

        <input
            type="date"
            id="tanggal"
            name="tanggal"
            required
            min="{{ \App\Services\SlotService::sekarang()->format('Y-m-d') }}"
            value="{{ old('tanggal', $b->tanggal->format('Y-m-d')) }}"
        >

    </p>

    <div>

        <label>Jam baru</label>

        <div id="slot"></div>

    </div>

    <p>

        <button type="submit">
            Simpan Perubahan
        </button>

        <a href="{{ route('booking.receipt', $b->kode_booking) }}">
            Batal
        </a>

    </p>

</form>

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

        const url = `{{ route('booking.slot') }}?${params.toString()}`;

        try {

            const res = await fetch(url, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) {
                throw new Error('Gagal mengambil slot.');
            }

            const data = await res.json();

            if (requestSaatIni !== nomorRequest) {
                return;
            }

            boxSlot.textContent = '';

            if (!data.slot || data.slot.length === 0) {

                boxSlot.textContent =
                    'Tidak ada jam kosong pada tanggal ini.';

                return;
            }

            data.slot.forEach(s => {

                const label = document.createElement('label');

                const radio = document.createElement('input');

                radio.type = 'radio';
                radio.name = 'jam_mulai';
                radio.value = s.mulai;
                radio.required = true;

                label.append(
                    radio,
                    ` ${s.mulai} - ${s.selesai}`
                );

                boxSlot.append(
                    label,
                    document.createElement('br')
                );

            });

        } catch (e) {

            if (requestSaatIni === nomorRequest) {

                boxSlot.textContent =
                    'Gagal memuat jam. Coba lagi.';

            }
        }
    }

    inTanggal.addEventListener('change', muatSlot);

    muatSlot();

</script>

@endsection