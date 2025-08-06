<?php

namespace Tests\Feature;

use App\Models\Pharmacy\MedicationType;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MedicationTypeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_medication_types()
    {
        $response = $this->getJson('/api/pharmacy/medication-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'code'
            ]);
    }

    public function test_can_create_medication_type()
    {
        $medicationTypeData = [
            'name' => 'Test Medication Type',
            'description' => 'Test description',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/pharmacy/medication-types', $medicationTypeData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
                'code'
            ]);

        $this->assertDatabaseHas('medication_types', $medicationTypeData);
    }

    public function test_can_show_medication_type()
    {
        $medicationType = MedicationType::create([
            'name' => 'Test Type',
            'description' => 'Test description',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/pharmacy/medication-types/{$medicationType->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
                'code'
            ]);
    }

    public function test_can_update_medication_type()
    {
        $medicationType = MedicationType::create([
            'name' => 'Original Name',
            'description' => 'Original description',
            'is_active' => true,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ];

        $response = $this->putJson("/api/pharmacy/medication-types/{$medicationType->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('medication_types', $updateData);
    }

    public function test_can_delete_medication_type()
    {
        $medicationType = MedicationType::create([
            'name' => 'Test Type',
            'description' => 'Test description',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/pharmacy/medication-types/{$medicationType->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('medication_types', ['id' => $medicationType->id]);
    }
}
