<?php

namespace App\Models\Hospital;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class HospitalPatient extends Model
{
    protected $casts = [
        'current_symptoms' => 'array',
        'know_allergies' => 'array',
        'current_medications' => 'array',
    ];

    protected $guarded = [];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id')->with('user');
    }


    public function emrs()
    {
        return $this->hasMany(HospitalEMR::class);
    }


    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        });
    }
}
