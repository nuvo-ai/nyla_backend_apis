<?php

namespace App\Services\Billing\Plan;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\General\Plan;
use App\Models\General\PlanFeature;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PlanFeatureService
{
    public function validate(array $features)
    {
        foreach ($features as $feature) {
            $validator = Validator::make($feature, [
                'name' => 'required|string',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
    }

    public function createMany(array $features, Plan $plan)
    {
        $this->validate($features);

        $planFeatures = [];

        foreach ($features as $feature) {
            $planFeatures[] = new PlanFeature([
                'name' => $feature['name'],
                'description' => $feature['description'] ?? null,
                'is_active' => true
            ]);
        }
        $plan->features()->saveMany($planFeatures);
        return $planFeatures;
    }

    public function updateMany(array $features, Plan $plan)
    {
        $plan->features()->delete();
        $this->createMany($features, $plan);
        return $plan->features;
    }

    public function getFeature($id)
    {
        $feature = PlanFeature::find($id);
        if (!$feature) {
            throw new ModelNotFoundException("Plan feature not found.");
        }
        return $feature;
    }

    public function list($plan_id)
    {
        $plan = Plan::find($plan_id);
        if (!$plan) {
            throw new ModelNotFoundException("Plan not found.");
        }
        return $plan->features;
    }

    public function delete($id)
    {
        $feature = PlanFeature::find($id);
        if (!$feature) {
            throw new ModelNotFoundException("Plan feature not found.");
        }
        $feature->delete();
        return true;
    }
}
