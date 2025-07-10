<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class PharmacyService extends Model
{
    protected $fillable = [
        'name',
        'pharmacy_id',
    ];
}
