<?php

namespace App\Services\Billing\Webhook;

use App\Models\General\Plan;
use App\Models\General\Subscription;
use App\Models\General\WebhookLog;
use App\Models\User\User;
use App\Services\Billing\Subscription\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookService
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

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
            $plan = Plan::where('plan_code', $paymentData['plan']['plan_code'])->first();
            $user = User::where('email', $paymentData['customer']['email'])->first();

            if ($user && $plan) {
                $this->subscriptionService->storeSubscription($user, $plan, $paymentData);
            }
        }

        return response()->json(['status' => 'success']);
    }
}
