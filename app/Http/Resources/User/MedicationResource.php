<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class MedicationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'dosage'         => $this->dosage,
            'frequency'      => $this->frequency, // e.g. once daily, twice daily
            'time'           => $this->time,
            'is_active'      => $this->is_active,
            'notes'          => $this->notes, // Additional notes for the reminder
            'user' => new UserResource($this->whenLoaded('user')),
            "created_at"        => formatDate($this->created_at),
            "updated_at"        => formatDate($this->updated_at),
        ];
    }
}
