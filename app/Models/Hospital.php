<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
     protected $casts = [
        'departments' => 'array',
        'services' => 'array',
        'operating_hours' => 'array',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'address',
        'hospital_type',
        'registration_number',
        'logo',
        'license',
        'request_on_site_setup',
        'accept_terms',
        'street_address',
        'city',
        'state',
        'google_maps_location',
        'number_of_beds',
        'departments',
        'services',
        'operating_hours'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->hasMany(HospitalUser::class, 'hospital_id');
    }
}
