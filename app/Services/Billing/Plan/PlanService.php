<?php

namespace App\Services\Billing\Plan;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\General\Currency;
use App\Models\General\Plan;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PlanService
{
    public function validate(array $data)
    {

        $validator = Validator::make($data, [
            'name' => 'required|string',
            'amount' => 'required|integer:min:0',
            'interval' => 'required|string|in:daily,weekly,monthly,annually',
            'currency_id' => 'required|exists:currencies,id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $this->validate($data);

            $currency = Currency::find($data['currency_id']);
            if (!$currency) {
                throw new Exception("Currency not found.");
            }

            $existingPlan = Plan::where('name', $data['name'])
                ->where('amount', $data['amount'])
                ->where('currency_id', $currency->id)
                ->first();

            if ($existingPlan) {
                throw new Exception("A plan with the same name, amount, and currency already exists.");
            }

            $planCode = null;

            if ($data['amount'] > 0) {
                $response = Http::withToken(config('services.paystack.secret_key'))
                    ->post('https://api.paystack.co/plan', [
                        'name' => $data['name'],
                        'amount' => $data['amount'] * 100, // Paystack expects amount in kobo
                        'interval' => $data['interval'],
                        'currency' => strtoupper($currency->short_name) ?? 'NGN',
                    ]);

                $res = $response->json();
                if (!$res['status']) {
                    throw new Exception($res['message'] ?? 'Paystack error');
                }

                $planCode = $res['data']['plan_code'];
            } else {
                // fallback dummy plan code in local
                $planCode = 'LOCAL_PLAN_' . uniqid();
            }


            $plan = Plan::create([
                'name' => $data['name'],
                'plan_code' => $planCode,
                'interval' => $data['interval'],
                'amount' => $data['amount'],
                'currency_id' => $currency->id,
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'portal' => $data['portal'] ?? 'Hospital',
                'plan_type' => $data['plan_type'] ?? 'Standard',
            ]);

            if (!empty($data['features'])) {
                app(PlanFeatureService::class)->createMany($data['features'], $plan);
            }

            return $plan;
        });
    }



    public function update(array $data, $planCode)
    {
        return DB::transaction(function () use ($data, $planCode) {
            $this->validate($data);

            $plan = Plan::where('plan_code', $planCode)->first();
            if (!$plan) {
                throw new ModelNotFoundException("Plan with code '{$planCode}' not found.");
            }

            $currency = Currency::find($data['currency_id']);
            if (!$currency) {
                throw new Exception("Currency not found.");
            }

            if ($data['amount'] > 0) {
                $response = Http::withToken(config('services.paystack.secret_key'))
                    ->put("https://api.paystack.co/plan/{$planCode}", [
                        'name' => $data['name'],
                        'description' => $data['description'] ?? null,
                    ]);

                if (!$response->ok()) {
                    throw new Exception("Paystack API error: " . $response->body());
                }

                $res = $response->json();
                if (!isset($res['status']) || !$res['status']) {
                    throw new Exception($res['message'] ?? 'Paystack error');
                }
            }

            $plan->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (!empty($data['features'])) {
                app(PlanFeatureService::class)->updateMany($data['features'], $plan);
            }

            return $plan;
        });
    }


    public function getPlan($planCode)
    {
        $plan = Plan::with('features')->where('plan_code', $planCode)->first();

        if (!$plan) {
            throw new ModelNotFoundException("Plan with code '{$planCode}' not found in database.");
        }
        return $plan;
    }


    public function list()
    {
        return Plan::with('features')->active()->get();
    }

    public function delete($planCode)
    {
        $plan = Plan::where('plan_code', $planCode)->first();

        if (!$plan) {
            throw new ModelNotFoundException("Plan with code '{$planCode}' not found.");
        }

        $response = Http::withToken(config('services.paystack.secret_key'))
            ->delete("https://api.paystack.co/plan/{$plan->plan_code}");

        if (!$response->successful()) {
            throw new Exception("Failed to delete plan from Paystack: " . $response->body());
        }
        $plan->delete();
        return $plan;
    }

    public function isSinglePlan(array $data): bool
    {
        return isset($data['name']) && isset($data['amount']);
    }

    public function hospitalPlan(): ?Plan
    {
        return Plan::with('features')->where('portal', 'Hospital')->first();
    }

    public function phamacyPlan(): ?Plan
    {
        return Plan::with('features')->where('portal', 'Pharmacy')->first();
    }
}
