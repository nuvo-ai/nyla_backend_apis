<?php

namespace Tests\Feature;

use App\Models\Pharmacy\Medication;
use App\Models\Pharmacy\MedicationType;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicationWithTypeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
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

        $this->medicationType = MedicationType::create([
            'name' => 'Test Type',
            'description' => 'Test description',
            'is_active' => true,
        ]);

        // Create a simple pharmacy for testing
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

        Sanctum::actingAs($this->user);
    }

    public function test_medication_type_relationship_works()
    {
        // Test that we can create a medication type
        $this->assertDatabaseHas('medication_types', [
            'name' => 'Test Type',
            'description' => 'Test description',
            'is_active' => true,
        ]);

        // Test that the medication type can have medications
        $medication = Medication::create([
            'pharmacy_id' => 1, // We'll use a dummy ID for testing
            'medication_type_id' => $this->medicationType->id,
            'name' => 'Test Medication',
            'description' => 'Test medication description',
            'stock' => 100,
            'price' => 25.50,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('medications', [
            'name' => 'Test Medication',
            'medication_type_id' => $this->medicationType->id,
        ]);

        // Test the relationship
        $this->assertEquals($this->medicationType->id, $medication->medication_type_id);
        $this->assertEquals($this->medicationType->name, $medication->medicationType->name);
    }

    public function test_medication_type_service_works()
    {
        // Test that the medication type service can list types
        $medicationTypes = MedicationType::all();
        $this->assertCount(1, $medicationTypes);
        $this->assertEquals('Test Type', $medicationTypes->first()->name);
    }
}
