<?php

namespace App\Http\Resources\Hospital;

use App\Http\Resources\OperatingHourResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalRegistrationResource extends JsonResource
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
            'uuid' => $this->uuid,
            'user' => new UserResource($this->whenLoaded('user')),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'hospital_type' => $this->hospital_type,
            'registration_number' => $this->registration_number,
            'logo_url' => $this->logo_url ?? null,
            'license_url' => $this->license_url ?? null,
            'request_onsite_setup' => (bool) $this->request_onsite_setup,
            'accept_terms' => (bool) $this->accept_terms,
            'street_address' => $this->street_address,
            'city' => $this->city,
            'state' => $this->state,
            'google_maps_location' => $this->google_maps_location,
            'number_of_beds' => $this->number_of_beds,
            'patient_count' => $this->patients()->count(),
            'appointment_count' => $this->appointments()->count(),
            'status' => $this->status,
            "created_at" => formatDate($this->created_at),
            "updated_at" => formatDate($this->updated_at),
            'departments' => HospitalDepartmentResource::collection($this->departments),
            'contacts'       => HospitalContactResource::collection($this->contacts),
            'services'       => HospitalServiceResource::collection($this->services),
            'operating_ours' => OperatingHourResource::collection($this->operatingHours),
        ];
    }
}
