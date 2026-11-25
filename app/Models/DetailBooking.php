<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBooking extends Model
{
    protected $table = 'detail_booking';

    protected $primaryKey = 'id_detail_booking';

    protected $fillable = [
        'id_booking',
        'id_service',
        'harga',
        'durasi',
    ];

    public function booking()
    {
        return $this->belongsTo(
            Booking::class,
            'id_booking',
            'id_booking'
        );
    }

    public function service()
    {
        return $this->belongsTo(
            Service::class,
            'id_service',
            'id_service'
        );
    }
}