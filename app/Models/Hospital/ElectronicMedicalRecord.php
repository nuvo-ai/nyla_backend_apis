<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;

class ElectronicMedicalRecord extends Model
{
    public function patient()
    {
        return $this->belongsTo(HospitalPatient::class, 'hospital_patient_id');
    }
}
