<?php

namespace Database\Seeders;

use App\Models\Pharmacy\Medication;
use App\Models\Pharmacy\MedicationDosage;
use Illuminate\Database\Seeder;

class MedicationDosageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some medications to create dosages for
        $medications = Medication::take(5)->get();

        if ($medications->isEmpty()) {
            // Check if there's a user, if not create one
            $user = \App\Models\User\User::first();
            if (!$user) {
                $user = \App\Models\User\User::create([
                    'first_name' => 'Sample',
                    'last_name' => 'User',
                    'email' => 'sample@user.com',
                    'password' => bcrypt('password'),
                ]);
            }

            // Check if there's a pharmacy, if not create one
            $pharmacy = \App\Models\Pharmacy\Pharmacy::first();
            if (!$pharmacy) {
                $pharmacy = \App\Models\Pharmacy\Pharmacy::create([
                    'user_id' => $user->id,
                    'uuid' => 'sample-uuid',
                    'name' => 'Sample Pharmacy',
                    'license_number' => 'SAMPLE123',
                    'pharmacist_in_charge_name' => 'Sample Pharmacist',
                    'phone' => '1234567890',
                    'email' => 'sample@pharmacy.com',
                    'street_address' => '123 Sample St',
                    'city' => 'Sample City',
                    'state' => 'Sample State',
                    'country' => 'Sample Country',
                    'nafdac_certificate' => 'sample-cert',
                    'terms_accepted' => true,
                    'is_active' => true,
                ]);
            }

            // Create a sample medication
            $medication = Medication::create([
                'pharmacy_id' => $pharmacy->id,
                'name' => 'Paracetamol',
                'description' => 'Pain relief medication',
                'stock' => 100,
                'price' => 5.99,
                'is_active' => true,
            ]);
            $medications = collect([$medication]);
        }

        foreach ($medications as $medication) {
            // Create multiple dosages for each medication
            $dosages = [
                [
                    'medication_id' => $medication->id,
                    'strength' => '500mg',
                    'form' => 'tablet',
                    'unit' => 'mg',
                    'quantity' => 500,
                    'frequency' => 'every 4-6 hours',
                    'instructions' => 'Take with food if stomach upset occurs',
                    'is_active' => true,
                ],
                [
                    'medication_id' => $medication->id,
                    'strength' => '250mg',
                    'form' => 'tablet',
                    'unit' => 'mg',
                    'quantity' => 250,
                    'frequency' => 'every 4-6 hours',
                    'instructions' => 'Take with food if stomach upset occurs',
                    'is_active' => true,
                ],
                [
                    'medication_id' => $medication->id,
                    'strength' => '120mg/5ml',
                    'form' => 'liquid',
                    'unit' => 'ml',
                    'quantity' => 5,
                    'frequency' => 'every 4-6 hours',
                    'instructions' => 'Shake well before use',
                    'is_active' => true,
                ],
            ];

            foreach ($dosages as $dosage) {
                MedicationDosage::create($dosage);
            }
        }

        // Create additional dosages for different medication types
        $additionalDosages = [
            [
                'medication_id' => $medications->first()->id,
                'strength' => '1000mg',
                'form' => 'tablet',
                'unit' => 'mg',
                'quantity' => 1000,
                'frequency' => 'twice daily',
                'instructions' => 'Take with a full glass of water',
                'is_active' => true,
            ],
            [
                'medication_id' => $medications->first()->id,
                'strength' => '10mg',
                'form' => 'capsule',
                'unit' => 'mg',
                'quantity' => 10,
                'frequency' => 'once daily',
                'instructions' => 'Take on empty stomach',
                'is_active' => true,
            ],
        ];

        foreach ($additionalDosages as $dosage) {
            MedicationDosage::create($dosage);
        }
    }
}
