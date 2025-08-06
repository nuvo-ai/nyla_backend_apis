<?php

namespace Database\Seeders;

use App\Models\General\ModulePreference;
use Illuminate\Database\Seeder;

class ModulePreferencesSeeder extends Seeder
{
    public function run(): void
    {
        $preferences = [
            [
                'name' => 'Weight Management',
                'slug' => 'weight-management',
                'description' => 'Support for healthy weight loss or gain through personalized nutrition and activity plans.'
            ],
            [
                'name' => 'Fitness & Exercise',
                'slug' => 'fitness-exercise',
                'description' => 'Guidance on physical activity routines to improve strength, endurance, and flexibility.'
            ],
            [
                'name' => 'Nutrition & Diet',
                'slug' => 'nutrition-diet',
                'description' => 'Personalized nutrition insights and meal planning to promote a healthy lifestyle.'
            ],
            [
                'name' => 'Mental Health',
                'slug' => 'mental-health',
                'description' => 'Tools and strategies to manage stress, anxiety, mood, and overall mental wellness.'
            ],
            [
                'name' => 'Sleep Improvement',
                'slug' => 'sleep-improvement',
                'description' => 'Support for developing better sleep habits and improving sleep quality.'
            ],
            [
                'name' => 'Chronic Disease Management',
                'slug' => 'chronic-disease-management',
                'description' => 'Assistance with managing long-term conditions like diabetes, hypertension, and heart disease.'
            ],
            [
                'name' => 'Pregnancy & Fertility',
                'slug' => 'pregnancy-fertility',
                'description' => 'Resources to support pre-conception health, fertility, pregnancy, and postpartum wellness.'
            ],
            [
                'name' => 'Menopause Support',
                'slug' => 'menopause-support',
                'description' => 'Care and guidance for managing symptoms and health changes during menopause.'
            ],
            [
                'name' => 'Hormone Balance',
                'slug' => 'hormone-balance',
                'description' => 'Support for managing hormonal fluctuations and achieving hormonal health.'
            ],
            [
                'name' => 'Preventive Health',
                'slug' => 'preventive-health',
                'description' => 'Focus on screenings, habits, and early interventions to prevent illness and maintain wellness.'
            ],
        ];

        foreach ($preferences as $pref) {
            ModulePreference::updateOrCreate(
                ['slug' => $pref['slug']],
                [
                    'name' => $pref['name'],
                    'description' => $pref['description'],
                    'status' => 'active',
                ]
            );
        }
    }
}
