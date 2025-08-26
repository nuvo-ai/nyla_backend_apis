<?php

namespace App\Http\Controllers\Api\Billing\Subscription;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\SubscriptionResource;
use App\Models\General\Subscription;
use App\Models\User\User;
use App\Services\Billing\Subscription\SubscriptionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubscriptionController extends Controller
{
    protected $subscription_service;

    public function __construct()
    {
        $this->subscription_service = new SubscriptionService;
    }

    public function list(Request $request)
    {
        try {
            $user = $request->user();
            $subscriptions = $user->subscriptions()->with('plan')->latest()->get();
            return ApiHelper::validResponse("Subscriptions retrieved", SubscriptionResource::collection($subscriptions));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to retrieve subscriptions", 500, null, $e);
        }
    }

    public function initialize(Request $request)
    {
        try {
            $user = $request->user();
            $this->subscription_service->validate($request->all());

            $init = $this->subscription_service->initializePayment(
                $user,
                $request->all()['amount'] ?? null, // or derive from Plan
                $request->all()['plan_code'] ?? null,
                [
                    'plan_id' => $request->all()['plan_id'],  // ✅ inject plan_id
                ]
            );

            return ApiHelper::validResponse("Payment initialized", [
                'authorization_url' => $init['authorization_url'],
                'access_code' => $init['access_code'],
                'reference' => $init['reference']
            ]);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse("Invalid input", ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to initialize payment", 500, null, $e);
        }
    }



    public function handleCallback(Request $request)
    {
        try {
            $reference = $request->query('reference');
            if (!$reference) {
                throw new Exception('Reference missing from callback');
            }

            $paymentData = $this->subscription_service->verifyTransaction($reference);

            // ✅ get user_id + plan_id from metadata (not auth)
            $user = User::find($paymentData['metadata']['user_id'] ?? null);
            $plan_id = $paymentData['metadata']['plan_id'] ?? null;

            if (!$user || !$plan_id) {
                throw new Exception('User or Plan ID missing from metadata');
            }

            $subscriptionCode = $paymentData['subscription'] ?? $paymentData['reference'];

            $existing = Subscription::where('subscription_code', $subscriptionCode)->first();
            if ($existing) {
                return ApiHelper::validResponse("Subscription already exists", SubscriptionResource::make($existing));
            }

            $subscription = $this->subscription_service->createSubscription($user, $plan_id, $paymentData);

            return ApiHelper::validResponse("Subscription created", SubscriptionResource::make($subscription));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to verify payment or create subscription", 500, null, $e);
        }
    }




    // public function subscribe(Request $request)
    // {
    //     try {
    //         $user = $request->user();
    //         $subscription = $this->subscription_service->subscribe($user, $request->all());
    //         return ApiHelper::validResponse("Subscription successful", SubscriptionResource::make($subscription));
    //     } catch (ValidationException $e) {
    //         return ApiHelper::inputErrorResponse("Invalid input", ApiConstants::VALIDATION_ERR_CODE, null, $e);
    //     } catch (Exception $e) {
    //         return ApiHelper::problemResponse("Unable to subscribe", 500, null, $e);
    //     }
    // }

    public function getSubscription($subscription_code)
    {
        try {
            $subscription = Subscription::where('subscription_code', $subscription_code)->with('plan', 'user')->firstOrFail();
            return ApiHelper::validResponse("Subscription details", SubscriptionResource::make($subscription));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to retrieve subscription", 500, null, $e);
        }
    }

    public function update(Request $request, $subscription_code)
    {
        try {
            $subscription = Subscription::where('subscription_code', $subscription_code)->firstOrFail();
            $subscription->update($request->only('status'));
            return ApiHelper::validResponse("Subscription updated", SubscriptionResource::make($subscription));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to update subscription", 500, null, $e);
        }
    }

    public function delete($subscription_code)
    {
        try {
            $subscription = Subscription::where('subscription_code', $subscription_code)->firstOrFail();
            $subscription->delete();
            return ApiHelper::validResponse("Subscription deleted");
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to delete subscription", 500, null, $e);
        }
    }

    public function current()
    {
        $auth_user = User::getAuthenticatedUser();
        $user = $auth_user;

        $subscription = $user->currentSubscription()?->with('plan.features')->first();

        if (!$subscription) {
            return ApiHelper::validResponse("No current subscription", []);
        }
        return ApiHelper::validResponse("Current subscription", SubscriptionResource::make($subscription));
    }
}
