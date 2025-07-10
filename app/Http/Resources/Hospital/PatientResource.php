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
            'id'                => $this->id,
            'user'              => new UserResource($this->whenLoaded('user')),
            'title'             => $this->getTitle(),
            'first_name'        => $this->user->first_name,
            'last_name'         => $this->user->last_name,
            'gender'            => $this->gender,
            'phone'             => $this->user->phone ?? null,
            'phone'             => $this->user->phone ?? null,
            'email'             => $this->user->email ?? null,
            'age'               => $this->age,
            'temperature'         => $this->temperature,
            'weight'              => $this->weight,
            'height'              => $this->height,
            'blood_pressure'      => $this->blood_pressure,
            'heart_rate'          => $this->heart_rate,
            'respiratory_rate'    => $this->respiratory_rate,
            'oxygen_saturation'   => $this->oxygen_saturation,
            'complaints' => $this->complaints ?? [],
            'status'            => $this->status,
            'last_visit'        => optional($this->last_visit)->toDateString(),
            'hospital'          => $this->hospital->name ?? null,
            'doctor'            => optional($this->doctor)->user->full_name ?? null,
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
