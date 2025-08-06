<?php

namespace App\Http\Resources\Pharmacy;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationTypeResource extends JsonResource
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
            'pharmacy_id' => $this->pharmacy_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'pharmacy' => $this->whenLoaded('pharmacy', function () {
                return [
                    'id' => $this->pharmacy->id,
                    'name' => $this->pharmacy->name,
                    'email' => $this->pharmacy->email,
                ];
            }),
            'medications' => $this->whenLoaded('medications', function () {
                return $this->medications->map(function ($medication) {
                    return [
                        'id' => $medication->id,
                        'name' => $medication->name,
                        'description' => $medication->description,
                        'stock' => $medication->stock,
                        'price' => $medication->price,
                        'is_active' => $medication->is_active,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
