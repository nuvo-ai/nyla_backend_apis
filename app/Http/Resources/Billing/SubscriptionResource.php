<?php

namespace App\Http\Resources\Billing;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            'user_id' => $this->user_id,
            'plan_id' => $this->plan_id,
            'subscription_code' => $this->subscription_code,
            'email_token' => $this->email_token,
            'customer_code' => $this->customer_code,
            'status' => $this->status,
            'meta' => $this->meta,
            "created_at" => formatDate($this->created_at),
            "updated_at" => formatDate($this->updated_at),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'user' => new UserResource($this->whenLoaded('user')),
            // 'features' => PlanFeatureResource::collection($this->whenLoaded('planFeatures')),
        ];
    }
}
