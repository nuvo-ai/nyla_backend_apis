<?php

namespace App\Services\Billing;

use App\Models\General\Plan;
use App\Models\General\Subscription;
use Exception;
use Illuminate\Support\Facades\Http;

class SubscriptionService
{
    public function subscribe($user, $data)
    {
        $plan = Plan::findOrFail($data['plan_id']);

        $res = Http::withToken(config('services.paystack.secret_key'))
            ->post(config('services.paystack.secret_key') .'subscription', [
                'customer' => $user->email,
                'plan' => $plan->plan_code,
            ]);

        $response = $res->json();

        if (!$response['status']) {
            throw new Exception($response['message'] ?? 'Subscription failed');
        }

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'subscription_code' => $response['data']['subscription_code'],
            'email_token' => $response['data']['email_token'],
            'customer_code' => $response['data']['customer'],
            'status' => 'active',
            'meta' => $response['data'],
        ]);

        return $subscription->load(['plan', 'user']);
    }
}