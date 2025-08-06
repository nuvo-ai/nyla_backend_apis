<?php

namespace Tests\Feature;

use App\Models\Pharmacy\Medication;
use App\Models\Pharmacy\MedicationDosage;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicationDosageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $medication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a pharmacy
        \DB::table('pharmacies')->insert([
            'id' => 1,
            'user_id' => $this->user->id,
            'uuid' => 'test-uuid',
            'name' => 'Test Pharmacy',
            'license_number' => 'TEST123',
            'pharmacist_in_charge_name' => 'Test Pharmacist',
            'phone' => '1234567890',
            'email' => 'pharmacy@test.com',
            'street_address' => '123 Test St',
            'city' => 'Test City',
            'state' => 'Test State',
            'country' => 'Test Country',
            'nafdac_certificate' => 'test-cert',
            'terms_accepted' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create a medication
        $this->medication = Medication::create([
            'pharmacy_id' => 1,
            'name' => 'Test Medication',
            'description' => 'Test medication description',
            'stock' => 100,
            'price' => 25.50,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_can_list_medication_dosages()
    {
        $response = $this->getJson('/api/pharmacy/medication-dosages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'code'
            ]);
    }

    public function test_can_create_medication_dosage()
    {
        $dosageData = [
            'medication_id' => $this->medication->id,
            'strength' => '500mg',
            'form' => 'tablet',
            'unit' => 'mg',
            'quantity' => 500,
            'frequency' => 'every 4-6 hours',
            'instructions' => 'Take with food if stomach upset occurs',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/pharmacy/medication-dosages', $dosageData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'medication_id',
                    'strength',
                    'form',
                    'unit',
                    'quantity',
                    'frequency',
                    'instructions',
                    'is_active',
                    'full_dosage',
                    'created_at',
                    'updated_at',
                ],
                'code'
            ]);

        $this->assertDatabaseHas('medication_dosages', $dosageData);
    }

    public function test_can_show_medication_dosage()
    {
        $dosage = MedicationDosage::create([
            'medication_id' => $this->medication->id,
            'strength' => '500mg',
            'form' => 'tablet',
            'unit' => 'mg',
            'quantity' => 500,
            'frequency' => 'every 4-6 hours',
            'instructions' => 'Take with food if stomach upset occurs',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/pharmacy/medication-dosages/{$dosage->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'medication_id',
                    'strength',
                    'form',
                    'unit',
                    'quantity',
                    'frequency',
                    'instructions',
                    'is_active',
                    'full_dosage',
                    'created_at',
                    'updated_at',
                ],
                'code'
            ]);
    }

    public function test_can_update_medication_dosage()
    {
        $dosage = MedicationDosage::create([
            'medication_id' => $this->medication->id,
            'strength' => '500mg',
            'form' => 'tablet',
            'unit' => 'mg',
            'quantity' => 500,
            'frequency' => 'every 4-6 hours',
            'instructions' => 'Take with food if stomach upset occurs',
            'is_active' => true,
        ]);

        $updateData = [
            'strength' => '1000mg',
            'quantity' => 1000,
            'frequency' => 'twice daily',
        ];

        $response = $this->putJson("/api/pharmacy/medication-dosages/{$dosage->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('medication_dosages', $updateData);
    }

    public function test_can_delete_medication_dosage()
    {
        $dosage = MedicationDosage::create([
            'medication_id' => $this->medication->id,
            'strength' => '500mg',
            'form' => 'tablet',
            'unit' => 'mg',
            'quantity' => 500,
            'frequency' => 'every 4-6 hours',
            'instructions' => 'Take with food if stomach upset occurs',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/pharmacy/medication-dosages/{$dosage->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('medication_dosages', ['id' => $dosage->id]);
    }

    public function test_can_get_dosages_by_medication()
    {
        // Create multiple dosages for the medication
        MedicationDosage::create([
            'medication_id' => $this->medication->id,
            'strength' => '500mg',
            'form' => 'tablet',
            'unit' => 'mg',
            'quantity' => 500,
            'frequency' => 'every 4-6 hours',
            'instructions' => 'Take with food if stomach upset occurs',
            'is_active' => true,
        ]);

        MedicationDosage::create([
            'medication_id' => $this->medication->id,
            'strength' => '1000mg',
            'form' => 'tablet',
            'unit' => 'mg',
            'quantity' => 1000,
            'frequency' => 'twice daily',
            'instructions' => 'Take with a full glass of water',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/pharmacy/medications/{$this->medication->id}/dosages");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'code'
            ]);

        $responseData = $response->json();
        $this->assertCount(2, $responseData['data']);
    }

    public function test_can_get_available_forms()
    {
        // Create dosages with different forms
        MedicationDosage::create([
            'medication_id' => $this->medication->id,
            'strength' => '500mg',
            'form' => 'tablet',
            'unit' => 'mg',
            'quantity' => 500,
            'is_active' => true,
        ]);

        MedicationDosage::create([
            'medication_id' => $this->medication->id,
            'strength' => '120mg/5ml',
            'form' => 'liquid',
            'unit' => 'ml',
            'quantity' => 5,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/pharmacy/medications/{$this->medication->id}/forms");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'code'
            ]);

        $responseData = $response->json();
        $this->assertContains('tablet', $responseData['data']);
        $this->assertContains('liquid', $responseData['data']);
    }
}
