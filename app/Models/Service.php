<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    //
    protected $table = 'service';
    protected $primaryKey = 'id_service';
    protected $fillable = ['nama_service', 'harga_service', 'durasi'];

    public function barber()
    {
    return $this->belongsToMany(Barber::class,
    'barberservice', 'id_service', 'id_barber');
    }
}
