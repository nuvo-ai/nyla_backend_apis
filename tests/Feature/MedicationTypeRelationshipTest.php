<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User\User;
use App\Models\Pharmacy\Pharmacy;
use App\Models\Pharmacy\MedicationType;
use App\Models\Pharmacy\Medication;

class MedicationTypeRelationshipTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $pharmacy;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and pharmacy
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->pharmacy = \DB::table('pharmacies')->insertGetId([
            'user_id' => $this->user->id,
            'uuid' => \Illuminate\Support\Str::uuid(),
            'name' => 'Test Pharmacy',
            'license_number' => 'LIC123456',
            'pharmacist_in_charge_name' => 'Dr. Test Pharmacist',
            'phone' => '1234567890',
            'email' => 'pharmacy@test.com',
            'street_address' => '123 Test Street',
            'city' => 'Test City',
            'state' => 'Test State',
            'country' => 'Test Country',
            'nafdac_certificate' => 'cert123.pdf',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->pharmacy = Pharmacy::find($this->pharmacy);

        Sanctum::actingAs($this->user);
    }

    public function test_medication_type_can_be_created_with_pharmacy_id()
    {
        $medicationTypeData = [
            'pharmacy_id' => $this->pharmacy->id,
            'name' => 'Test Medication Type',
            'description' => 'Test Description',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/pharmacy/medication-types', $medicationTypeData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'pharmacy_id',
                    'name',
                    'description',
                    'is_active',
                    'pharmacy',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'pharmacy_id' => $this->pharmacy->id,
                    'name' => 'Test Medication Type',
                    'description' => 'Test Description',
                    'is_active' => true,
                ]
            ]);
    }

    public function test_medication_can_be_created_with_medication_type_id()
    {
        // Create a medication type first
        $medicationType = MedicationType::create([
            'pharmacy_id' => $this->pharmacy->id,
            'name' => 'Test Type',
            'description' => 'Test Description',
            'is_active' => true,
        ]);

        $medicationData = [
            'pharmacy_id' => $this->pharmacy->id,
            'medication_type_id' => $medicationType->id,
            'name' => 'Test Medication',
            'description' => 'Test Description',
            'stock' => 100,
            'price' => 10.50,
            'is_active' => true,
        ];

        $response = $this->postJson('/api/pharmacy/medications', $medicationData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'pharmacy_id',
                    'medication_type_id',
                    'name',
                    'description',
                    'stock',
                    'price',
                    'is_active',
                    'medication_type',
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'pharmacy_id' => $this->pharmacy->id,
                    'medication_type_id' => $medicationType->id,
                    'name' => 'Test Medication',
                    'description' => 'Test Description',
                    'stock' => 100,
                    'price' => 10.50,
                    'is_active' => true,
                ]
            ]);
    }

    public function test_medication_list_includes_medication_type_relationship()
    {
        // Create a medication type
        $medicationType = MedicationType::create([
            'pharmacy_id' => $this->pharmacy->id,
            'name' => 'Test Type',
            'description' => 'Test Description',
            'is_active' => true,
        ]);

        // Create a medication with the type
        Medication::create([
            'pharmacy_id' => $this->pharmacy->id,
            'medication_type_id' => $medicationType->id,
            'name' => 'Test Medication',
            'description' => 'Test Description',
            'stock' => 100,
            'price' => 10.50,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/pharmacy/medications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'pharmacy_id',
                        'medication_type_id',
                        'name',
                        'description',
                        'stock',
                        'price',
                        'is_active',
                        'medication_type',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'medication_type_id' => $medicationType->id,
                        'medication_type' => [
                            'id' => $medicationType->id,
                            'name' => $medicationType->name,
                            'description' => $medicationType->description,
                        ]
                    ]
                ]
            ]);
    }

    public function test_medication_type_list_includes_pharmacy_relationship()
    {
        // Create a medication type
        $medicationType = MedicationType::create([
            'pharmacy_id' => $this->pharmacy->id,
            'name' => 'Test Type',
            'description' => 'Test Description',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/pharmacy/medication-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'pharmacy_id',
                        'name',
                        'description',
                        'is_active',
                        'pharmacy',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'pharmacy_id' => $this->pharmacy->id,
                        'pharmacy' => [
                            'id' => $this->pharmacy->id,
                            'name' => $this->pharmacy->name,
                            'email' => $this->pharmacy->email,
                        ]
                    ]
                ]
            ]);
    }
}
