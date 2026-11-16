<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customer';
    protected $primaryKey = 'id_customer';
    protected $fillable = ['nama', 'no_hp'];

    public function booking()
    {
        return $this->hasMany(Booking::class,
            'id_customer', 'id_customer');
    }
}
