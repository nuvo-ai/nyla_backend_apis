<?php

namespace App\Services\Billing;

use App\Models\General\Plan;
use App\Models\General\Subscription;
use App\Models\General\WebhookLog;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookService
{
    public function handle(Request $request)
    {
        $signature = $request->header('x-paystack-signature');
        $secret = config('services.paystack.secret_key');

        if (!$signature || !hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 403);
        }

        $event = $request->input('event');
        $payload = $request->all();

        WebhookLog::create([
            'event' => $event,
            'payload' => json_encode($payload),
        ]);

        if ($event === 'charge.success') {
            $paymentData = $payload['data'];

            if (!Subscription::where('subscription_code', $paymentData['subscription_code'])->exists()) {
                $plan = Plan::where('plan_code', $paymentData['plan']['plan_code'])->first();
                $user = User::where('email', $paymentData['customer']['email'])->first();

                if ($user && $plan) {
                    Subscription::create([
                        'uuid' => Str::uuid(),
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'subscription_code' => $paymentData['subscription_code'],
                        'email_token' => $paymentData['email_token'],
                        'customer_code' => $paymentData['customer']['customer_code'],
                        'payment_gateway_id' => 1,
                        'payment_method' => $paymentData['authorization']['channel'],
                        'authorization_reusable' => $paymentData['authorization']['reusable'],
                        'status' => 'active',
                        'starts_at' => now(),
                        'ends_at' => Carbon::now()->addDays($plan->getPlanDays()),
                        'next_payment_date' => $paymentData['next_payment_date'] ?? null,
                        'meta' => $paymentData,
                    ]);
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}
