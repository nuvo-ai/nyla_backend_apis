<?php

namespace Database\Seeders;

use App\Models\Pharmacy\MedicationType;
use Illuminate\Database\Seeder;

class MedicationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a pharmacy for the medication types
        $pharmacy = \App\Models\Pharmacy\Pharmacy::first();

        if (!$pharmacy) {
            // Create a pharmacy if none exists
            $user = \App\Models\User\User::first();
            if (!$user) {
                $user = \App\Models\User\User::create([
                    'first_name' => 'Default',
                    'last_name' => 'User',
                    'email' => 'default@example.com',
                    'password' => bcrypt('password'),
                ]);
            }

            $pharmacy = \App\Models\Pharmacy\Pharmacy::create([
                'user_id' => $user->id,
                'name' => 'Default Pharmacy',
                'email' => 'pharmacy@example.com',
                'phone' => '1234567890',
                'address' => 'Default Address',
                'is_active' => true,
            ]);
        }

        $medicationTypes = [
            [
                'pharmacy_id' => $pharmacy->id,
                'name' => 'Antibiotics',
                'description' => 'Medications used to treat bacterial infections',
                'is_active' => true,
            ],
            [
                'pharmacy_id' => $pharmacy->id,
                'name' => 'Analgesics',
                'description' => 'Pain relief medications',
                'is_active' => true,
            ],
            [
                'pharmacy_id' => $pharmacy->id,
                'name' => 'Antihypertensives',
                'description' => 'Medications to treat high blood pressure',
                'is_active' => true,
            ],
            [
                'pharmacy_id' => $pharmacy->id,
                'name' => 'Antidiabetics',
                'description' => 'Medications to treat diabetes',
                'is_active' => true,
            ],
            [
                'pharmacy_id' => $pharmacy->id,
                'name' => 'Antidepressants',
                'description' => 'Medications to treat depression and anxiety',
                'is_active' => true,
            ],
            [
                'pharmacy_id' => $pharmacy->id,
                'name' => 'Vitamins & Supplements',
                'description' => 'Nutritional supplements and vitamins',
                'is_active' => true,
            ],
            [
                'pharmacy_id' => $pharmacy->id,
                'name' => 'Cough & Cold',
                'description' => 'Medications for cough, cold, and flu symptoms',
                'is_active' => true,
            ],
            [
                'pharmacy_id' => $pharmacy->id,
                'name' => 'Gastrointestinal',
                'description' => 'Medications for digestive system issues',
                'is_active' => true,
            ],
            [
                'pharmacy_id' => $pharmacy->id,
                'name' => 'Cardiovascular',
                'description' => 'Medications for heart and blood vessel conditions',
                'is_active' => true,
            ],
            [
                'pharmacy_id' => $pharmacy->id,
                'name' => 'Respiratory',
                'description' => 'Medications for respiratory conditions like asthma',
                'is_active' => true,
            ],
        ];

        foreach ($medicationTypes as $medicationType) {
            MedicationType::create($medicationType);
        }
    }
}
