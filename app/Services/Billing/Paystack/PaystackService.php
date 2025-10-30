<?php

namespace App\Services\Billing\Paystack;

use Illuminate\Support\Facades\Http;
use Exception;

class PaystackService
{
    public function createCustomer($user)
    {
        $response = Http::withToken(config('services.paystack.secret_key'))
            ->post('https://api.paystack.co/customer', [
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone' => $user->phone,
            ]);

        $json = $response->json();

        if (!$json['status']) {
            throw new Exception($json['message'] ?? 'Failed to create Paystack customer');
        }

        return $json['data'];
    }

    public function initializePayment($user, $amount, $planCode = null, array $metadata = [])
    {
        $redirectUrl = null;
        if (isset($metadata['metadata'])) {
            $metaInput = is_string($metadata['metadata'])
                ? json_decode($metadata['metadata'], true)
                : $metadata['metadata'];

            if (!empty($metaInput['redirect_url'])) {
                $redirectUrl = $metaInput['redirect_url'];
            }
            unset($metadata['metadata']);
        } elseif (!empty($metadata['redirect_url'])) {
            $redirectUrl = $metadata['redirect_url'];
        }

        // ✅ Merge default metadata with the provided values
        $payload = [
            'email' => $user->email,
            'amount' => $amount,
            'callback_url' => route('billings.callback'),
            'metadata' => array_merge([
                'user_id'  => $user->id,
                'platform' => $metadata['platform'] ?? 'web',
                'portal'   => $metadata['portal'] ?? 'hospital',
            ], $metadata, [
                'redirect_url' => $redirectUrl,
            ]),
        ];

        if ($planCode) {
            $payload['plan'] = $planCode;
        }

        $response = Http::withToken(config('services.paystack.secret_key'))
            ->post('https://api.paystack.co/transaction/initialize', $payload);

        $json = $response->json();

        if (!$json['status']) {
            throw new Exception($json['message'] ?? 'Unable to initialize payment');
        }

        return $json['data'];
    }



    public function verifyTransaction(string $reference)
    {
        $response = Http::withToken(config('services.paystack.secret_key'))
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        $json = $response->json();

        if (!$json['status'] || $json['data']['status'] !== 'success') {
            throw new Exception('Payment verification failed');
        }

        return $json['data'];
    }

    public function chargeAuthorization($email, $authorizationCode, $amount)
    {
        $response = Http::withToken(config('services.paystack.secret_key'))
            ->post('https://api.paystack.co/transaction/charge_authorization', [
                'email' => $email,
                'amount' => $amount,
                'authorization_code' => $authorizationCode
            ]);

        $json = $response->json();

        if (!$json['status']) {
            throw new Exception($json['message'] ?? 'Failed to charge saved card');
        }

        return $json['data'];
    }

    public function subscribeCustomerToPlan($email, $planCode, $authorizationCode, array $metadata = [])
    {
        $response = Http::withToken(config('services.paystack.secret_key'))
            ->post('https://api.paystack.co/subscription', [
                'customer' => $email,
                'plan' => $planCode,
                'authorization' => $authorizationCode,
                'metadata' => $metadata,
            ]);

        $json = $response->json();

        if (!$json['status']) {
            throw new Exception($json['message'] ?? 'Failed to subscribe customer to plan');
        }

        return $json['data'];
    }

    public function cancelSubscription($subscriptionCode)
    {
        $response = Http::withToken(config('services.paystack.secret_key'))
            ->post("https://api.paystack.co/subscription/disable", [
                'code' => $subscriptionCode
            ]);

        $json = $response->json();

        if (!$json['status']) {
            throw new Exception($json['message'] ?? 'Failed to cancel subscription');
        }

        return $json['data'];
    }
}
