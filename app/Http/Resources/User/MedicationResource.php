<?php

namespace App\Http\Resources\User;

use Carbon\Carbon;
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
            'time'      => $this->time 
            ? Carbon::createFromFormat('H:i:s', $this->time)->format('g:i A') 
            : null, // e.g. "3:00 PM"
            'is_active'      => $this->is_active,
            'notes'          => $this->notes, // Additional notes for the reminder
            'user' => new UserResource($this->whenLoaded('user')),
            "created_at"        => formatDate($this->created_at),
            "updated_at"        => formatDate($this->updated_at),
        ];
    }
}
