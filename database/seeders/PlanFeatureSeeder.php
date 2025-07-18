<?php

namespace Database\Seeders;

use App\Models\General\Plan;
use App\Models\General\PlanFeature;
use Illuminate\Database\Seeder;

class PlanFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $plans = Plan::all();

        foreach ($plans as $plan) {
            PlanFeature::create([
                'plan_id' => $plan->id,
                'title' => 'Users',
                'description' => 'Number of users allowed on this plan.',
                'value' => rand(1, 100),
                'unit' => 'users',
                'is_unlimited' => false,
                'sort_order' => 1,
            ]);

            PlanFeature::create([
                'plan_id' => $plan->id,
                'title' => 'Storage',
                'description' => 'Amount of storage included.',
                'value' => rand(1, 50),
                'unit' => 'GB',
                'is_unlimited' => false,
                'sort_order' => 2,
            ]);

            PlanFeature::create([
                'plan_id' => $plan->id,
                'title' => 'Priority Support',
                'description' => 'Access to priority customer support.',
                'value' => null,
                'unit' => null,
                'is_unlimited' => true,
                'sort_order' => 3,
            ]);
        }
    }
}
