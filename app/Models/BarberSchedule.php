<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarberSchedule extends Model
{
    // Konstanta class (Modul 11): 1 = Senin ... 7 = Minggu (sama dengan Carbon dayOfWeekIso)
    const HARI = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    protected $table = 'barber_schedule';
    protected $primaryKey = 'id_schedule';
    protected $fillable = ['id_barber', 'hari', 'jam_mulai', 'jam_selesai'];

    public function barber()
    {
        return $this->belongsTo(Barber::class,
            'id_barber', 'id_barber');
    }
}
