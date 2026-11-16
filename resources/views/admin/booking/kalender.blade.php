@include('partials.nav-admin')
<h1>Kalender Booking</h1>

<p>
    <label>Cabang</label>
    <select id="id_outlet">
        <option value="">Semua cabang</option>
        @foreach ($outlet as $o)
            <option value="{{ $o->id_outlet }}">{{ $o->nama_outlet }}</option>
        @endforeach
    </select>
    &nbsp; Biru = dipesan, Hijau = selesai, Abu-abu = batal
</p>

<div id="kalender"></div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    const sel = document.getElementById('id_outlet');
    const kalender = new FullCalendar.Calendar(document.getElementById('kalender'), {
        initialView: 'timeGridWeek',
        firstDay: 1,
        allDaySlot: false,
        slotMinTime: '08:00:00',
        slotMaxTime: '21:00:00',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: {
            url: "{{ route('admin.booking.events') }}",
            extraParams: () => ({ id_outlet: sel.value })
        },
        eventClick: info => alert(info.event.title)
    });
    kalender.render();
    sel.addEventListener('change', () => kalender.refetchEvents());
</script>
