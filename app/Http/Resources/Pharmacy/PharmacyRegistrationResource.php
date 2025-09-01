<?php

namespace App\Http\Resources\Pharmacy;

use App\Http\Resources\OperatingHourResource;
use App\Http\Resources\Pharmacy\PharmacyContactResource;
use App\Http\Resources\Pharmacy\PharmacyServiceResource;
use App\Http\Resources\User\UserResource;
use App\Models\Pharmacy\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyRegistrationResource extends JsonResource
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
            'logo_url' => $this->logo_url ?? null,
            'nafdac_certificate_url' => $this->nafdac_certificate_url,
            'license_number'  => $this->license_number,
            'request_onsite_setup' => (bool) $this->request_onsite_setup,
            'accept_terms' => (bool) $this->accept_terms,
            'street_address' => $this->street_address,
            'city' => $this->city,
            'state' => $this->state,
            'google_maps_location' => $this->google_maps_location,
            'status' => $this->status,
            "created_at" => formatDate($this->created_at),
            "updated_at" => formatDate($this->updated_at),
            'contacts'       => PharmacyContactResource::collection($this->contacts),
            'services'       => PharmacyServiceResource::collection($this->services),
            'operating_hours' => OperatingHourResource::collection($this->getOrderedOperatingHours()),
        ];
    }
}
