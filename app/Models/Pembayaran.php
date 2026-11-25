<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    const METODE = ['tunai', 'transfer', 'qris'];

    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    protected $fillable = ['id_booking', 'id_user', 'metode', 'jumlah', 'dibayar_pada'];
    protected $casts = ['dibayar_pada' => 'datetime'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'id_booking', 'id_booking');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}