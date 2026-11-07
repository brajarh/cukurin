<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    //
    protected $table = 'outlet';
    protected $primaryKey = 'id_outlet';
    protected $fillable = ['nama_outlet', 'alamat_outlet'];

    public function barber()
    {
    return $this->hasMany(Barber::class,
    'id_outlet', 'id_outlet');
    }
}
