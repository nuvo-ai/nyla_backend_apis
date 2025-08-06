<?php

namespace Tests\Feature;

use App\Models\Pharmacy\Order;
use App\Models\Pharmacy\Medication;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderNoteTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $medication;

    protected function setUp(): void
    {
        parent::setUp();

        // Add the order_note column to the test database
        \DB::statement('ALTER TABLE orders ADD COLUMN order_note TEXT NULL');

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

    public function test_can_create_order_with_note()
    {
        $orderData = [
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'priority' => 'normal',
            'status' => 'pending',
            'total_price' => 25.50,
            'order_note' => 'Please deliver in the morning',
            'created_by' => $this->user->id,
            'items' => [
                [
                    'medication_id' => $this->medication->id,
                    'quantity' => 1,
                    'price' => 25.50,
                    'status' => 'pending',
                ]
            ]
        ];

        $response = $this->postJson('/api/pharmacy/orders', $orderData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'order_note' => 'Please deliver in the morning',
        ]);
    }

    public function test_can_create_order_without_note()
    {
        $orderData = [
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'priority' => 'normal',
            'status' => 'pending',
            'total_price' => 25.50,
            'created_by' => $this->user->id,
            'items' => [
                [
                    'medication_id' => $this->medication->id,
                    'quantity' => 1,
                    'price' => 25.50,
                    'status' => 'pending',
                ]
            ]
        ];

        $response = $this->postJson('/api/pharmacy/orders', $orderData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'order_note' => null,
        ]);
    }

    public function test_can_update_order_note()
    {
        // Create an order first
        $order = Order::create([
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'priority' => 'normal',
            'status' => 'pending',
            'total_price' => 25.50,
            'created_by' => $this->user->id,
        ]);

        $updateData = [
            'order_note' => 'Updated delivery instructions',
        ];

        $response = $this->putJson("/api/pharmacy/orders/{$order->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_note' => 'Updated delivery instructions',
        ]);
    }

    public function test_order_note_is_included_in_response()
    {
        // Create an order with a note
        $order = Order::create([
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'priority' => 'normal',
            'status' => 'pending',
            'total_price' => 25.50,
            'order_note' => 'Special delivery instructions',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/pharmacy/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'pharmacy_id',
                    'patient_id',
                    'priority',
                    'status',
                    'total_price',
                    'prescription_url',
                    'order_note',
                    'created_by',
                    'created_at',
                    'updated_at',
                ],
                'code'
            ]);

        $responseData = $response->json();
        $this->assertEquals('Special delivery instructions', $responseData['data']['order_note']);
    }

    public function test_order_note_validation()
    {
        $orderData = [
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'priority' => 'normal',
            'status' => 'pending',
            'total_price' => 25.50,
            'order_note' => 123, // Invalid: should be string
            'created_by' => $this->user->id,
            'items' => [
                [
                    'medication_id' => $this->medication->id,
                    'quantity' => 1,
                    'price' => 25.50,
                    'status' => 'pending',
                ]
            ]
        ];

        $response = $this->postJson('/api/pharmacy/orders', $orderData);

        $response->assertStatus(422);
    }

    public function test_order_model_accepts_order_note()
    {
        $order = Order::create([
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'priority' => 'normal',
            'status' => 'pending',
            'total_price' => 25.50,
            'order_note' => 'Test note',
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals('Test note', $order->order_note);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_note' => 'Test note',
        ]);
    }
}
