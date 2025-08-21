<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class HospitalEMR extends Model
{
    protected $table = 'hospital_emrs';

    protected $fillable = [
        'hospital_id',
        'patient_id',
        'status',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function patient()
    {
        return $this->belongsTo(HospitalPatient::class, 'patient_id');
    }
}
