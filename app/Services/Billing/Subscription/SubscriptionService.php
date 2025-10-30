<?php

namespace App\Services\Billing\Subscription;

use App\Constants\User\UserConstants;
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

        // Merge all metadata safely, preserving frontend values
        $metadata = array_merge([
            'plan_id'  => $plan->id,
            'portal'   => $data['portal'] ?? 'hospital',
            'platform' => $data['platform'] ?? 'hospital_onboarding',
            'redirect_url' => $data['redirect_url'] ?? '',
        ], $data['metadata'] ?? []);

        // Initialize payment with full metadata
        return $this->paystack->initializePayment(
            $user,
            $plan->amount * 100,
            $plan->plan_code,
            $metadata
        );
    }


    public function verifyTransaction(string $reference)
    {
        return $this->paystack->verifyTransaction($reference);
    }

    public function createSubscription($user, $plan_id, $paymentData = null, $isTrial = false)
    {
        $plan = Plan::findOrFail($plan_id);

        // Check existing active or pending subscription for user
        $current = Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'trial'])
            ->latest('ends_at')
            ->first();

        if ($current) {
            if (now()->greaterThanOrEqualTo($current->ends_at)) {
                $current->update(['status' => 'expired']);
            } else {
                throw ValidationException::withMessages([
                    'active_subscription' => ['We can not create a duplicate of this subscription because it is still active']
                ]);
            }
        }

        // Skip duplicate subscriptions
        if (!$isTrial) {
            $subscriptionCode = $paymentData['subscription'] ?? $paymentData['reference'] ?? null;
            if ($subscriptionCode && Subscription::where('subscription_code', $subscriptionCode)->exists()) {
                return null;
            }
        }

        return $this->storeSubscription($user, $plan, $paymentData, $isTrial);
    }

    public function storeSubscription($user, $plan, $paymentData = null, $isTrial = false)
    {
        $now = Carbon::now();
        $trialDays = 0;
        $trialEndsAt = null;
        $endsAt = $plan->getPlanEndsAt();

        if ($isTrial) {
            $trialDays = $this->getTrialDays($user);
            $trialEndsAt = $now->copy()->addDays($trialDays);
            $endsAt = $trialEndsAt;
        }

        $subscription = Subscription::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'subscription_code' => $paymentData['subscription'] ?? $paymentData['reference'] ?? Str::random(10),
            'email_token' => $paymentData['email_token'] ?? null,
            'customer_code' => $paymentData['customer']['customer_code'] ?? null,
            'payment_gateway_id' => $paymentData['payment_gateway_id'] ?? 1,
            'payment_method' => $paymentData['authorization']['channel'] ?? ($isTrial ? 'free_trial' : null),
            'status' => $isTrial ? 'trial' : 'active',
            'is_trial' => $isTrial,
            'trial_ends_at' => $trialEndsAt,
            'converted_to_paid' => false,
            'starts_at' => $now,
            'ends_at' => $endsAt,
            'authorization_reusable' => $paymentData['authorization']['reusable'] ?? false,
            'next_payment_date' => $isTrial ? $trialEndsAt : ($paymentData['next_payment_date'] ?? $plan->getPlanEndsAt()),
            'meta' => $paymentData ? json_encode($paymentData) : null,
        ]);

        if ($isTrial) {
            $this->applyFreeTrial($subscription, $user);
        }

        return $subscription;
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

    public function applyFreeTrial(Subscription $subscription, $user)
    {
        $trialDays = $this->getTrialDays($user);

        $subscription->update([
            'is_trial' => true,
            'trial_ends_at' => Carbon::now()->addDays($trialDays),
            'ends_at' => Carbon::now()->addDays($trialDays),
            'payment_method' => 'free_trial',
            'status' => 'trial',
        ]);

        return $subscription;
    }


    public function getTrialDays($user)
    {
        if ($user->getTable() === 'hospital_users') {

            // Count how many unique hospitals have active subscriptions
            $hospitalCount = Subscription::whereHas('user', function ($query) {
                $query->where('role', 'Admin')
                    ->whereHas('hospital');
            })->distinct('user_id')->count();

            // First 5 hospitals get 3-month (90-day) trial
            if ($hospitalCount < 5) {
                return 90;
            }
            return 30;
        }
        if ($user->getTable() === 'users') {

            // Check if this user is a pharmacy admin
            if ($user->role === UserConstants::PHARMACY_ADMIN) {
                // Count how many pharmacy admins already subscribed
                $pharmacyCount = Subscription::whereHas('user', function ($query) {
                    $query->where('role', 'Pharmacy Admin');
                })->distinct('user_id')->count();

                if ($pharmacyCount < 5) {
                    return 90; // first 5 pharmacy admins also get 3 months
                }
                return 30;
            }
            return 30;
        }
        return 0;
    }


    public function cancelSubscription(Subscription $subscription)
    {
        if ($subscription->status !== 'active') {
            throw new Exception('Only active subscriptions can be cancelled.');
        }

        $this->paystack->cancelSubscription($subscription->subscription_code);

        $subscription->status = 'cancelled';
        $subscription->ends_at = Carbon::now();
        $subscription->save();

        return $subscription;
    }
}
