<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'date',
        'status',
        'notes',
        'hospital_id',
        'doctor_id',
    ];


    protected $casts = [
        'date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(HospitalPatient::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medications()
    {
        return $this->hasMany(PrescriptionMedication::class);
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
