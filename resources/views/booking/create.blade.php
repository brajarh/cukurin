<h1>Booking Cukur.in</h1>
@include('partials.flash')
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

    <p>
        <label>2. Pilih Layanan</label><br>
        <select id="id_service" name="id_service" required>
            <option value="">-- pilih layanan --</option>
            @foreach ($service as $sv)
                <option value="{{ $sv->id_service }}" {{ (string) old('id_service') === (string) $sv->id_service ? 'selected' : '' }}>
                    {{ $sv->nama_service }} - {{ numberToRupiah($sv->harga_service) }} ({{ formatDurasi($sv->durasi) }})
                </option>
            @endforeach
        </select>
    </p>

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
    const selService = document.getElementById('id_service');
    const selBarber = document.getElementById('id_barber');
    const inTanggal = document.getElementById('tanggal');
    const boxSlot = document.getElementById('slot');
    let barberLama = "{{ old('id_barber') }}";

    // Barber yang tampil = barber di cabang terpilih yang bisa mengerjakan layanan terpilih
    function isiBarber() {
        const pilih = selBarber.value || barberLama;
        barberLama = '';
        const o = parseInt(selOutlet.value);
        const s = parseInt(selService.value);

        selBarber.innerHTML = '';
        if (!o || !s) {
            selBarber.add(new Option('-- pilih cabang dan layanan dulu --', ''));
            muatSlot();
            return;
        }

        const cocok = barbers.filter(b => b.id_outlet === o && b.service.includes(s));
        selBarber.add(new Option(cocok.length ? '-- pilih barber --' : 'Tidak ada barber yang cocok', ''));
        cocok.forEach(b => {
            const opt = new Option(b.nama, b.id);
            if (String(b.id) === String(pilih)) opt.selected = true;
            selBarber.add(opt);
        });
        muatSlot();
    }

    async function muatSlot() {
        boxSlot.textContent = '';
        if (!selBarber.value || !selService.value || !inTanggal.value) {
            boxSlot.textContent = 'Pilih barber, layanan, dan tanggal dulu.';
            return;
        }

        boxSlot.textContent = 'Memuat jam...';
        const url = `{{ route('booking.slot') }}?id_barber=${selBarber.value}&id_service=${selService.value}&tanggal=${inTanggal.value}`;
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

    selOutlet.addEventListener('change', isiBarber);
    selService.addEventListener('change', isiBarber);
    selBarber.addEventListener('change', muatSlot);
    inTanggal.addEventListener('change', muatSlot);
    isiBarber();
</script>
