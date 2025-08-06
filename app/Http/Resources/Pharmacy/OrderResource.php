<?php

namespace App\Http\Resources\Pharmacy;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'patient_id' => $this->patient_id,
            'priority' => $this->priority,
            'status' => $this->status,
            'total_price' => $this->total_price,
            'prescription_url' => $this->prescription_url,
            'order_note' => $this->order_note,
            'created_by' => $this->created_by,
            'pharmacy' => $this->whenLoaded('pharmacy', function () {
                return [
                    'id' => $this->pharmacy->id,
                    'name' => $this->pharmacy->name,
                    'email' => $this->pharmacy->email,
                    'phone' => $this->pharmacy->phone,
                ];
            }),
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'name' => $this->patient->names(),
                    'email' => $this->patient->email,
                    'phone' => $this->patient->phone,
                ];
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->names(),
                    'email' => $this->creator->email,
                ];
            }),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'medications' => $this->whenLoaded('medications', function () {
                return $this->medications->map(function ($medication) {
                    return [
                        'id' => $medication->id,
                        'name' => $medication->name,
                        'description' => $medication->description,
                        'price' => $medication->price,
                    ];
                });
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
