<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    //
    protected $table = 'barber';
    protected $primaryKey = 'id_barber';
    protected $fillable = ['id_outlet', 'nama_barber', 'no_hp'];

    public function outlet()
    {
    return $this->belongsTo(Outlet::class,
    'id_outlet', 'id_outlet');
    }

    public function service()
    {
    return $this->belongsToMany(Service::class,
    'barberservice', 'id_barber', 'id_service');
    }
}
