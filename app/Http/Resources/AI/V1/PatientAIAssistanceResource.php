<?php

namespace App\Http\Resources\AI\V1;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientAIAssistanceResource extends JsonResource
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
            'user' => [
                'id' => $this->conversation->user->id,
                'first_name' => $this->conversation->user->first_name,
                'last_name' => $this->conversation->user->last_name,
                'email' => $this->conversation->user->email,
                'avatar' => $this->conversation->user->avatar,
            ],
        ];
    }
}
