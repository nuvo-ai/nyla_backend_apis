<?php

namespace App\Http\Resources\Hospital;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
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
            'medical_number'  => $this->medical_number,
            'departments' => $this->departments,
            'medical_specialties' => $this->medical_specialties,
            'user'            => new UserResource($this->whenLoaded('user')),
            'hospital_user' => new HospitalUsersResource($this->whenLoaded('hospitalUser')),
            'hospital' => new HospitalRegistrationResource($this->whenLoaded('hospital')),
            'status' => $this->status,
            "created_at" => formatDate($this->created_at),
            "updated_at" => formatDate($this->updated_at),
        ];
    }
}
