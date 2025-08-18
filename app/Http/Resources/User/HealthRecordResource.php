<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class HealthRecordResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'blood_pressure' => $this->blood_pressure,
            'heart_rate'     => $this->heart_rate,
            'weight'         => $this->weight,
            'height'         => $this->height,
            'allergies'      => $this->allergies,
            'conditions'     => $this->conditions,
            'medications'    => $this->healthRecordMedications,
            'user' => new UserResource($this->whenLoaded('user')),
            "created_at"        => formatDate($this->created_at),
            "updated_at"        => formatDate($this->updated_at),
        ];
    }
}
