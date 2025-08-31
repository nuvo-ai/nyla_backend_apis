<?php

namespace App\Services\Billing\Subscription;

use App\Models\General\Plan;
use App\Models\General\Subscription;
use App\Services\Billing\Paystack\PaystackService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class SubscriptionService
{
    protected PaystackService $paystack;

    public function __construct()
    {
        $this->paystack = new PaystackService;
    }

    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'plan_id' => 'required|exists:plans,id',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function initializePayment($user, $data)
    {
        $plan = Plan::findOrFail($data['plan_id']);

        return $this->paystack->initializePayment(
            $user,
            $plan->amount * 100,
            $plan->plan_code,
            [
                'plan_id'  => $plan->id,
                'portal'   => $data['portal']   ?? 'pharmacy',
                'platform' => $data['platform'] ?? 'web',
            ]
        );
    }

    public function verifyTransaction(string $reference)
    {
        return $this->paystack->verifyTransaction($reference);
    }

    public function createSubscription($user, $plan_id, $paymentData)
    {
        $plan = Plan::findOrFail($plan_id);
        $subscriptionCode = $paymentData['subscription'] ?? $paymentData['reference'];

        if (Subscription::where('subscription_code', $subscriptionCode)->exists()) {
            return null;
        }

        return $this->storeSubscription($user, $plan, $paymentData);
    }

    public function storeSubscription($user, $plan, $paymentData)
    {
        return Subscription::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'subscription_code' => $paymentData['subscription'] ?? $paymentData['reference'],
            'email_token' => $paymentData['email_token'] ?? null,
            'customer_code' => $paymentData['customer']['customer_code'] ?? null,
            'payment_gateway_id' => 1,
            'payment_method' => $paymentData['authorization']['channel'] ?? null,
            'status' => 'active',
            'starts_at' => Carbon::now(),
            'ends_at' => $plan->getPlanEndsAt(),
            'authorization_reusable' => $paymentData['authorization']['reusable'] ?? false,
            'next_payment_date' => $paymentData['next_payment_date'] ?? $plan->getPlanEndsAt(),
            'meta' => json_encode($paymentData),
        ]);
    }

    public function subscribeCustomer($user, $plan_id, $paymentData)
    {
        return DB::transaction(function () use ($user, $plan_id, $paymentData) {
            $this->validate(['plan_id' => $plan_id]);
            $planCode = $paymentData['plan_code'] ?? null;
            $authorizationCode = $paymentData['authorization_code'] ?? null;
            $metadata = $paymentData['metadata'] ?? [];

            if (!$planCode || !$authorizationCode) {
                throw new Exception('Missing plan_code or authorization_code');
            }

            $this->paystack->subscribeCustomerToPlan(
                $user->email,
                $planCode,
                $authorizationCode,
                $metadata
            );
            return $this->createSubscription($user, $plan_id, $paymentData);
        });
    }
}
