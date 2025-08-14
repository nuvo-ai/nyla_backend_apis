<?php

namespace App\Http\Resources\Hospital;

use Illuminate\Http\Resources\Json\JsonResource;

class PrescriptionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'date'        => $this->date->toDateString(),
            'status'      => $this->status,
            'notes'       => $this->notes,
            'patient'     => $this->patient,
            'doctor' => DoctorResource::make($this->doctor),
            'medications' => $this->medications,
            'hospital' => HospitalRegistrationResource::make($this->hospital),
            "created_at" => formatDate($this->created_at),
            "updated_at" => formatDate($this->updated_at),
        ];
    }
}
