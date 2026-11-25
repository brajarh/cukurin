@extends('layouts.app')
@section('title', 'Kalender Booking')
@section('content')
<h1 class="h3 mb-3">Kalender Booking</h1>

<div class="row g-2 align-items-center mb-3">
    <div class="col-auto">
        <label for="id_outlet" class="col-form-label">Cabang</label>
    </div>
    <div class="col-auto">
        <select id="id_outlet" class="form-select">
            <option value="">Semua cabang</option>
            @foreach ($outlet as $o)
                <option value="{{ $o->id_outlet }}">{{ $o->nama_outlet }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto text-muted small">
        Biru = dipesan, Hijau = selesai, Abu-abu = batal
    </div>
</div>

<div id="kalender" class="bg-white p-3 border rounded"></div>

@push('scripts')
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
@endpush
@endsection
