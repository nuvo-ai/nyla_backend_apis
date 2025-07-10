<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class PharmacyContact extends Model
{
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'role',
        'type',
        'pharmacy_id',
    ];
}
