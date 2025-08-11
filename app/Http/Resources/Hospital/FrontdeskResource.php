<?php

namespace App\Http\Resources\Hospital;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FrontdeskResource extends JsonResource
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
            'user' => new UserResource($this->whenLoaded('user')),
            'hospital_user' => $this->hospitalUser,
            'shift' => $this->shift ?? null,
            'department' => $this->department ?? null,
            'years_of_experience' => $this->years_of_experience ?? null,
            'hospital' => new HospitalRegistrationResource($this->whenLoaded('hospital')),
            'created_at' => formatDate($this->created_at),
            'updated_at' => formatDate($this->updated_at),
        ];
    }
}
