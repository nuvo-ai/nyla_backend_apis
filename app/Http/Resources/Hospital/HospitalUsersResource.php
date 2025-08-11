<?php

namespace App\Http\Resources\Hospital;

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
            'departments' => is_array($this->doctor->departments ?? null)
                ? ($this->doctor->departments[0] ?? null)
                : ($this->doctor->departments ?? (
                    is_array($this->frontdesk->department ?? null)
                    ? ($this->frontdesk->department[0] ?? null)
                    : $this->frontdesk->department ?? null
                )),
            'user_account_id' => $this->user_account_id,
            'hospital' => new HospitalRegistrationResource($this->whenLoaded('hospital')),
            "created_at" => formatDate($this->created_at),
            "updated_at" => formatDate($this->updated_at),
        ];
    }
}
