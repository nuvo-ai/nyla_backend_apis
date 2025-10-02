<?php

namespace App\Http\Resources\Pharmacy;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicationResource extends JsonResource
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
            'medication_type_id' => $this->medication_type_id,
            'name' => $this->name,
            'description' => $this->description,
            'stock' => $this->stock,
            'price' => $this->price,
            'manufacturer' => $this->manufacturer,
            'expiry_date' => $this->expiry_date,
            'is_active' => $this->is_active,
            'batch_number' => $this->batch_number,
            'is_expired' => $this->expiry_date ? now()->greaterThan(Carbon::parse($this->expiry_date)) : false,
            'low_stock_threshold' => $this->low_stock_threshold,
            'is_low_stock' => $this->low_stock_threshold !== null ? $this->stock <= $this->low_stock_threshold : false,
            'pharmacy' => $this->whenLoaded('pharmacy', function () {
                return [
                    'id' => $this->pharmacy->id,
                    'name' => $this->pharmacy->name,
                    'email' => $this->pharmacy->email,
                ];
            }),
            'medication_type' => $this->whenLoaded('medicationType', function () {
                return [
                    'id' => $this->medicationType->id,
                    'name' => $this->medicationType->name,
                    'description' => $this->medicationType->description,
                ];
            }),
            'dosages' => MedicationDosageResource::collection($this->whenLoaded('dosages')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
