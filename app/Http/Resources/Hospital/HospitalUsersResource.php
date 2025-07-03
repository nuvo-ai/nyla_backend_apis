<?php

namespace App\Http\Resources\Hospital;

use App\Http\Resources\OperatingHourResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HospitalUsersResource extends JsonResource
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
            'name' => $this->user->full_name,
            'email' => $this->user->email,
            'phone' => $this->user->phone ?? null,
            'address' => $this->user->address ?? null,
            'state' => $this->user->state ?? null,
            'city' => $this->user->city ?? null,
            'role' => $this->role,
            'user_account_id' => $this->user_account_id,
            'hospital' => new HospitalRegistrationResource($this->whenLoaded('hospital')),
            "created_at" => formatDate($this->created_at),
            "updated_at" => formatDate($this->updated_at),
        ];
    }
}
