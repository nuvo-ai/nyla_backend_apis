<?php

namespace App\Http\Resources\Pharmacy;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationDosageResource extends JsonResource
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
            'medication_id' => $this->medication_id,
            'strength' => $this->strength,
            'form' => $this->form,
            'unit' => $this->unit,
            'quantity' => $this->quantity,
            'frequency' => $this->frequency,
            'instructions' => $this->instructions,
            'is_active' => $this->is_active,
            'full_dosage' => $this->full_dosage,
            'medication' => $this->whenLoaded('medication', function () {
                return [
                    'id' => $this->medication->id,
                    'name' => $this->medication->name,
                    'description' => $this->medication->description,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
