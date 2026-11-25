@extends('layouts.app')
@section('content')
<h1>Booking Cukur.in</h1>
<p><a href="{{ route('booking.riwayat') }}">Cek status / riwayat booking</a></p>

<form method="POST" action="{{ route('booking.konfirmasi') }}">
    @csrf

    <p>
        <label>1. Pilih Cabang</label><br>
        <select id="id_outlet" name="id_outlet" required>
            <option value="">-- pilih cabang --</option>
            @foreach ($outlet as $o)
                <option value="{{ $o->id_outlet }}" {{ (string) old('id_outlet') === (string) $o->id_outlet ? 'selected' : '' }}>
                    {{ $o->nama_outlet }} - {{ $o->alamat_outlet }}
                </option>
            @endforeach
        </select>
    </p>

    <div>
    <label>2. Pilih Layanan</label>

    @foreach ($service as $sv)
        <div>
            <label>
                <input
                    type="checkbox"
                    name="id_service[]"
                    value="{{ $sv->id_service }}"
                    class="pilihan-service"
                    data-harga="{{ $sv->harga_service }}"
                    data-durasi="{{ $sv->durasi }}"
                    @checked(in_array(
                        (string) $sv->id_service,
                        array_map('strval', (array) old('id_service', []))
                    ))
                >

                {{ $sv->nama_service }}
                - {{ numberToRupiah($sv->harga_service) }}
                ({{ formatDurasi($sv->durasi) }})
            </label>
        </div>
    @endforeach

    <p>
        Total Harga: <strong id="totalHarga">Rp0</strong>
    </p>

    <p>
        Total Durasi: <strong id="totalDurasi">0 menit</strong>
    </p>
</div>

    <p>
        <label>3. Pilih Barber</label><br>
        <select id="id_barber" name="id_barber" required>
            <option value="">-- pilih cabang dan layanan dulu --</option>
        </select>
    </p>

    <p>
        <label>4. Pilih Tanggal</label><br>
        <input type="date" id="tanggal" name="tanggal" required
               min="{{ \App\Services\SlotService::sekarang()->format('Y-m-d') }}"
               value="{{ old('tanggal') }}">
    </p>

    <div>
        <label>5. Pilih Jam</label>
        <div id="slot">Pilih barber, layanan, dan tanggal dulu.</div>
    </div>

    <p>
        <label>6. Nama</label><br>
        <input name="nama" value="{{ old('nama') }}" required>
    </p>
    <p>
        <label>No HP</label><br>
        <input name="no_hp" value="{{ old('no_hp') }}" required>
    </p>

    <button type="submit">Lanjut ke Konfirmasi</button>
</form>

<script>
    const barbers = @json($barber);

    const selOutlet = document.getElementById('id_outlet');
    const selBarber = document.getElementById('id_barber');
    const inTanggal = document.getElementById('tanggal');
    const boxSlot = document.getElementById('slot');

    const pilihanService = document.querySelectorAll('.pilihan-service');

    let barberLama = @json(old('id_barber'));
    let nomorRequest = 0;

    // Mengambil seluruh layanan yang dicentang
    function layananDipilih() {
        return Array.from(pilihanService)
            .filter(s => s.checked)
            .map(s => Number(s.value));
    }

    // Menghitung total harga dan durasi
    function hitungTotal() {
        let harga = 0;
        let durasi = 0;

        pilihanService.forEach(s => {
            if (s.checked) {
                harga += Number(s.dataset.harga);
                durasi += Number(s.dataset.durasi);
            }
        });

        document.getElementById('totalHarga').textContent =
            'Rp' + harga.toLocaleString('id-ID');

        document.getElementById('totalDurasi').textContent =
            durasi + ' menit';
    }

    // Menampilkan barber yang menguasai semua layanan
    function isiBarber() {

        const pilih = selBarber.value || barberLama;
        barberLama = '';

        const outlet = Number(selOutlet.value);
        const layanan = layananDipilih();

        hitungTotal();

        selBarber.innerHTML = '';

        if (!outlet || layanan.length === 0) {

            selBarber.add(
                new Option('-- pilih cabang dan layanan dulu --', '')
            );

            muatSlot();
            return;
        }

        const cocok = barbers.filter(b =>
            Number(b.id_outlet) === outlet &&
            layanan.every(id =>
                b.service.map(Number).includes(id)
            )
        );

        selBarber.add(
            new Option(
                cocok.length
                    ? '-- pilih barber --'
                    : 'Tidak ada barber yang cocok',
                ''
            )
        );

        cocok.forEach(b => {

            const opt = new Option(b.nama, b.id);

            if (String(b.id) === String(pilih)) {
                opt.selected = true;
            }

            selBarber.add(opt);
        });

        muatSlot();
    }

    // Mengambil slot berdasarkan semua layanan
    async function muatSlot() {

        const requestSaatIni = ++nomorRequest;
        const layanan = layananDipilih();

        boxSlot.textContent = '';

        if (
            !selBarber.value ||
            layanan.length === 0 ||
            !inTanggal.value
        ) {
            boxSlot.textContent =
                'Pilih barber, layanan, dan tanggal dulu.';

            return;
        }

        boxSlot.textContent = 'Memuat jam...';

        const params = new URLSearchParams();

        params.append('id_barber', selBarber.value);
        params.append('tanggal', inTanggal.value);

        layanan.forEach(id => {
            params.append('id_service[]', id);
        });

        try {
            const res = await fetch(
                `{{ route('booking.slot') }}?${params.toString()}`,
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            if (!res.ok) {
                throw new Error('Gagal mengambil slot');
            }

            const data = await res.json();

            // Abaikan hasil request lama
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

    selOutlet.addEventListener('change', isiBarber);
    selBarber.addEventListener('change', muatSlot);
    inTanggal.addEventListener('change', muatSlot);

    pilihanService.forEach(s => {
        s.addEventListener('change', isiBarber);
    });

    isiBarber();
</script>
@endsection
