<?php

namespace App\Http\Resources\Hospital;

use Illuminate\Http\Resources\Json\JsonResource;

class EMRResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'        => $this->id,
            'patient'   => new PatientResource($this->whenLoaded('patient')),
            'hospital'  => new HospitalRegistrationResource($this->whenLoaded('hospital')),
            'status'    => $this->status,
            'created_at'=> formatDate($this->created_at),
            'updated_at'=> formatDate($this->updated_at),
        ];
    }
}
