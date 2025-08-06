<?php

namespace App\Http\Resources\Hospital;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title ?? null,
            'patient_name' => $this->patient_name ?? null,
            'appointment_type' => $this->appointment_type,
            'appointment_date' => $this->appointment_date->format('Y-m-d'),
            'appointment_time' => $this->appointment_time->format('H:i'),
            'note' => $this->note ?? null,
            'source' => $this->source ?? null,
            'status' => $this->status,
            'hospital' => new HospitalRegistrationResource($this->whenLoaded('hospital')),
            'doctor' => new HospitalUsersResource($this->whenLoaded('doctor')),
            'scheduler' => new UserResource($this->whenLoaded('scheduler')),
            'created_at' => formatDate($this->created_at),
            'updated_at' => formatDate($this->updated_at),
        ];
    }
}
