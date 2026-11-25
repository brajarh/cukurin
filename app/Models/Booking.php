<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    const STATUS_DIPESAN = 'dipesan';
    const STATUS_SELESAI = 'selesai';
    const STATUS_BATAL = 'batal';

    const DAFTAR_STATUS = [
        self::STATUS_DIPESAN,
        self::STATUS_SELESAI,
        self::STATUS_BATAL,
    ];

    protected $table = 'booking';
    protected $primaryKey = 'id_booking';
    protected $fillable = [
    'kode_booking',
    'id_customer',
    'id_user',
    'id_barber',
    'id_service',
    'tanggal',
    'jam_mulai',
    'jam_selesai',
    'status',
];
    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];

    public function user()
{
    return $this->belongsTo(User::class, 'id_user');
}

    public function customer()
    {
        return $this->belongsTo(Customer::class,
            'id_customer', 'id_customer');
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class,
            'id_barber', 'id_barber');
    }

    public function service()
    {
        return $this->belongsTo(Service::class,
            'id_service', 'id_service');
    }

    // Kode unik untuk bukti pemesanan, contoh: CKA1B2C3
    public static function buatKode(): string
    {
        do {
            $kode = 'CK' . strtoupper(Str::random(6));
        } while (self::where('kode_booking', $kode)->exists());

        return $kode;
    }
    public function pembayaran()
{
    return $this->hasOne(Pembayaran::class, 'id_booking', 'id_booking');
}

    public function ulasan()
{
    return $this->hasOne(Ulasan::class, 'id_booking', 'id_booking');
}

    public function detail()
{
    return $this->hasMany(
        DetailBooking::class,
        'id_booking',
        'id_booking'
    );
}

    public function totalHarga()
{
    return $this->detail->sum('harga');
}

public function totalDurasi()
{
    return $this->detail->sum('durasi');
}
}
