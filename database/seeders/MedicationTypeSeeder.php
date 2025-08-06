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
        $medicationTypes = [
            [
                'name' => 'Antibiotics',
                'description' => 'Medications used to treat bacterial infections',
                'is_active' => true,
            ],
            [
                'name' => 'Analgesics',
                'description' => 'Pain relief medications',
                'is_active' => true,
            ],
            [
                'name' => 'Antihypertensives',
                'description' => 'Medications to treat high blood pressure',
                'is_active' => true,
            ],
            [
                'name' => 'Antidiabetics',
                'description' => 'Medications to treat diabetes',
                'is_active' => true,
            ],
            [
                'name' => 'Antidepressants',
                'description' => 'Medications to treat depression and anxiety',
                'is_active' => true,
            ],
            [
                'name' => 'Vitamins & Supplements',
                'description' => 'Nutritional supplements and vitamins',
                'is_active' => true,
            ],
            [
                'name' => 'Cough & Cold',
                'description' => 'Medications for cough, cold, and flu symptoms',
                'is_active' => true,
            ],
            [
                'name' => 'Gastrointestinal',
                'description' => 'Medications for digestive system issues',
                'is_active' => true,
            ],
            [
                'name' => 'Cardiovascular',
                'description' => 'Medications for heart and blood vessel conditions',
                'is_active' => true,
            ],
            [
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
