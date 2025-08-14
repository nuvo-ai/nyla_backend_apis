<?php

namespace App\Http\Resources\Hospital;

use Illuminate\Http\Resources\Json\JsonResource;

class VisitNoteResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'diagnosis_and_assessment' => $this->diagnosis_and_assessment,
            'treatment_plan_and_recommendation' => $this->treatment_plan_and_recommendation,
            'visit_date' => $this->visit_date->toDateString(),
            'doctor' => DoctorResource::make($this->doctor),
            'patient' => $this->patient,
            'hospital' => HospitalRegistrationResource::make($this->hospital),
            "created_at" => formatDate($this->created_at),
            "updated_at" => formatDate($this->updated_at),
        ];
    }
}
