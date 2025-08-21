<?php

namespace App\Http\Resources\Hospital;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'user'            => new UserResource($this->whenLoaded('user')),
            'hospital_id'             => $this->hospital_id,
            'doctor_id'               => $this->doctor_id,
            'chief_complaints'        => $this->chief_complaints,
            'temperature'             => $this->temperature,
            'weight'                  => $this->weight,
            'height'                  => $this->height,
            'blood_pressure'          => $this->blood_pressure,
            'heart_rate'              => $this->heart_rate,
            'respiratory_rate'        => $this->respiratory_rate,
            'oxygen_saturation'       => $this->oxygen_saturation,
            'last_visit'              => $this->last_visit,
            'emergency_contact_name'  => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'current_symptoms'        => $this->current_symptoms ?? [],
            'pain_level'              => $this->pain_level,
            'know_allergies'          => $this->know_allergies ?? [],
            'visit_priority'          => $this->visit_priority ?? 'normal',
            'medical_history'         => $this->medical_history,
            'current_medications'     => $this->current_medications ?? [],
            'insurance_info'          => $this->insurance_info,
            'visit_type'              => $this->visit_type,
            'referral_source'         => $this->referral_source,
            'status'                  => $this->status,
            "created_at" => formatDate($this->created_at),
            "updated_at" => formatDate($this->updated_at),
        ];
    }

    private function getTitle()
    {
        $title = match (strtolower($this->gender)) {
            'male' => 'Mr',
            'female' => 'Mrs',
            default => 'Mx',
        };

        return $title;
    }
}
