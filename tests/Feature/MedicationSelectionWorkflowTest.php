<?php

namespace Tests\Feature;

use App\Models\Pharmacy\Medication;
use App\Models\Pharmacy\MedicationDosage;
use App\Models\Pharmacy\MedicationType;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicationSelectionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $medication;
    protected $medicationType;

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

        // Create a medication type
        $this->medicationType = MedicationType::create([
            'name' => 'Analgesics',
            'description' => 'Pain relief medications',
            'is_active' => true,
        ]);

        // Create a medication
        $this->medication = Medication::create([
            'pharmacy_id' => 1,
            'medication_type_id' => $this->medicationType->id,
            'name' => 'Paracetamol',
            'description' => 'Pain relief medication',
            'stock' => 100,
            'price' => 25.50,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_patient_can_browse_medications_by_type()
    {
        // Patient browses medication types
        $response = $this->getJson('/api/pharmacy/medication-types');
        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertNotEmpty($responseData['data']);

        // Patient selects a medication type and gets medications
        $response = $this->getJson('/api/pharmacy/medications?medication_type_id=' . $this->medicationType->id);
        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertNotEmpty($responseData['data']);
    }

    public function test_patient_can_select_medication_and_view_dosages()
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

        MedicationDosage::create([
            'medication_id' => $this->medication->id,
            'strength' => '120mg/5ml',
            'form' => 'liquid',
            'unit' => 'ml',
            'quantity' => 5,
            'frequency' => 'every 4-6 hours',
            'instructions' => 'Shake well before use',
            'is_active' => true,
        ]);

        // Patient selects a medication and views available dosages
        $response = $this->getJson("/api/pharmacy/medications/{$this->medication->id}/dosages");
        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertCount(3, $responseData['data']);

        // Verify dosage information is complete
        $dosages = $responseData['data'];
        foreach ($dosages as $dosage) {
            $this->assertArrayHasKey('strength', $dosage);
            $this->assertArrayHasKey('form', $dosage);
            $this->assertArrayHasKey('frequency', $dosage);
            $this->assertArrayHasKey('instructions', $dosage);
            $this->assertArrayHasKey('full_dosage', $dosage);
        }
    }

    public function test_patient_can_filter_dosages_by_form()
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

        // Patient gets available forms
        $response = $this->getJson("/api/pharmacy/medications/{$this->medication->id}/forms");
        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertContains('tablet', $responseData['data']);
        $this->assertContains('liquid', $responseData['data']);

        // Patient filters dosages by form
        $response = $this->getJson("/api/pharmacy/medication-dosages?medication_id={$this->medication->id}&form=tablet");
        $response->assertStatus(200);

        $responseData = $response->json();
        $this->assertNotEmpty($responseData['data']);

        // Verify all returned dosages are tablets
        foreach ($responseData['data'] as $dosage) {
            $this->assertEquals('tablet', $dosage['form']);
        }
    }

    public function test_patient_can_view_complete_medication_information()
    {
        // Create a dosage with complete information
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

        // Patient views specific dosage details
        $response = $this->getJson("/api/pharmacy/medication-dosages/{$dosage->id}");
        $response->assertStatus(200);

        $responseData = $response->json();
        $dosageData = $responseData['data'];

        // Verify all required information is present
        $this->assertEquals('500mg', $dosageData['strength']);
        $this->assertEquals('tablet', $dosageData['form']);
        $this->assertEquals('mg', $dosageData['unit']);
        $this->assertEquals(500, $dosageData['quantity']);
        $this->assertEquals('every 4-6 hours', $dosageData['frequency']);
        $this->assertEquals('Take with food if stomach upset occurs', $dosageData['instructions']);
        $this->assertStringContainsString('500.00mg tablet', $dosageData['full_dosage']);
    }

    public function test_medication_dosage_relationship_works_correctly()
    {
        // Create dosages for the medication
        $dosage1 = MedicationDosage::create([
            'medication_id' => $this->medication->id,
            'strength' => '500mg',
            'form' => 'tablet',
            'unit' => 'mg',
            'quantity' => 500,
            'is_active' => true,
        ]);

        $dosage2 = MedicationDosage::create([
            'medication_id' => $this->medication->id,
            'strength' => '1000mg',
            'form' => 'tablet',
            'unit' => 'mg',
            'quantity' => 1000,
            'is_active' => true,
        ]);

        // Test the relationship
        $medication = Medication::with('dosages')->find($this->medication->id);
        $this->assertCount(2, $medication->dosages);
        $this->assertEquals($dosage1->id, $medication->dosages[0]->id);
        $this->assertEquals($dosage2->id, $medication->dosages[1]->id);

        // Test the reverse relationship
        $dosage = MedicationDosage::with('medication')->find($dosage1->id);
        $this->assertEquals($this->medication->id, $dosage->medication->id);
        $this->assertEquals('Paracetamol', $dosage->medication->name);
    }
}
