<?php

namespace App\Http\Resources\Pharmacy;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
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
            'order_id' => $this->order_id,
            'medication_id' => $this->medication_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'status' => $this->status,
            'medication' => $this->whenLoaded('medication', function () {
                return [
                    'id' => $this->medication->id,
                    'name' => $this->medication->name,
                    'description' => $this->medication->description,
                    'price' => $this->medication->price,
                    'stock' => $this->medication->stock,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
