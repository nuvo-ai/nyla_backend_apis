<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class HealthRecord extends Model
{
    protected $fillable = [
        'user_id',
        'blood_pressure',
        'heart_rate',
        'weight',
        'height',
    ];

    public function allergies()
    {
        return $this->hasMany(Allergy::class);
    }

    public function conditions()
    {
        return $this->hasMany(MedicalCondition::class);
    }

    public function healthRecordMedications()
    {
        return $this->hasMany(HealthRecordMedication::class);
    }
}
