<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Transaksi T5: satu baris = satu kali perubahan jadwal booking
class PerubahanJadwal extends Model
{
    protected $table = 'perubahan_jadwal';
    protected $primaryKey = 'id_perubahan';

    protected $fillable = [
        'id_booking',
        'id_user',
        'tanggal_lama',
        'jam_mulai_lama',
        'jam_selesai_lama',
        'tanggal_baru',
        'jam_mulai_baru',
        'jam_selesai_baru',
        'alasan',
    ];

    protected $casts = [
        'tanggal_lama' => 'date:Y-m-d',
        'tanggal_baru' => 'date:Y-m-d',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'id_booking', 'id_booking');
    }

    // Pelaku perubahan (pelanggan pemilik booking)
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
