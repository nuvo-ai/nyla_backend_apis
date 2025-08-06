<?php

namespace App\Http\Resources\AI\V1;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PharmacyAIAssistanceResource extends JsonResource
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
            'message' => $this->content,
            'conversation_id' => $this->conversation_id ?? null,
            'created_at' => formatDate($this->created_at),
        ];
    }
}
