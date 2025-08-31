<?php

namespace App\Http\Resources\Billing;

use App\Http\Resources\Billing\Plan\PlanFeatureResource;
use App\Http\Resources\Billing\Plan\PlanResource;
use App\Http\Resources\User\UserResource;
use Carbon\Carbon;
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
        $startsAt = Carbon::parse($this->starts_at);
        $endsAt = Carbon::parse($this->ends_at);

        // Calculate next billing
        $nextBilling = null;
        if ($this->status === 'active') {
            if ($this->plan->interval === 'monthly') {
                $nextBilling = $startsAt->copy()->addMonth()->format('M d, Y');
            } elseif ($this->plan->interval === 'yearly') {
                $nextBilling = $startsAt->copy()->addYear()->format('M d, Y');
            }
        }
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'subscription_code' => $this->subscription_code,
            'user_id' => $this->user_id,
            'plan_id' => $this->plan_id,
            'subscription_code' => $this->subscription_code,
            'email_token' => $this->email_token,
            'customer_code' => $this->customer_code,
            'starts_at' => formatDate($this->starts_at),
            'ends_at' => formatDate($this->ends_at),
            'is_expired' => $this->isExpired(),
            'next_billing' =>  $this->next_payment_date
                ? Carbon::parse($this->next_payment_date)->format('M d, Y')
                : null,
            'payment_gateway_id' => $this->payment_gateway_id,
            'payment_method' => $this->payment_method,
            'authorization_reusable' => $this->authorization_reusable ? true : false,
            'next_payment_date' => $this->next_payment_date,
            'status' => $this->status,
            'meta' => $this->meta,
            "created_at" => formatDate($this->created_at),
            "updated_at" => formatDate($this->updated_at),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
