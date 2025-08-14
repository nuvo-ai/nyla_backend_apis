<?php

namespace App\Models\Hospital;

use App\Models\Hospital\Doctor;
use App\Models\Hospital\Hospital;
use App\Models\Hospital\HospitalPatient;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitNote extends Model
{
    use HasFactory;

    protected $casts = [
        'visit_date' => 'date',
    ];


    protected $fillable = [
        'hospital_id',
        'doctor_id',
        'patient_id',
        'diagnosis_and_assessment',
        'treatment_plan_and_recommendation',
        'visit_date',
    ];

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(HospitalPatient::class, 'patient_id');
    }
}
