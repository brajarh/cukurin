<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ulasan extends Model
{
    protected $table = 'ulasan';
    protected $primaryKey = 'id_ulasan';
    protected $fillable = ['id_booking', 'id_user', 'id_barber', 'rating', 'komentar'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'id_booking', 'id_booking');
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'id_barber', 'id_barber');
    }

    public function pelanggan()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}