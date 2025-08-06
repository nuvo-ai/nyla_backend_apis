<?php

namespace Tests\Feature;

use App\Models\Pharmacy\Order;
use App\Models\Pharmacy\Medication;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PatientOrderHistoryTest extends TestCase
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

    public function test_patient_can_view_their_order_history()
    {
        // Create orders for the authenticated user
        $order1 = Order::create([
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'priority' => 'normal',
            'status' => 'completed',
            'total_price' => 25.50,
            'order_note' => 'First order',
            'created_by' => $this->user->id,
        ]);

        $order2 = Order::create([
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'priority' => 'urgent',
            'status' => 'pending',
            'total_price' => 50.00,
            'order_note' => 'Second order',
            'created_by' => $this->user->id,
        ]);

        // Create an order for a different user (should not appear in results)
        $otherUser = User::create([
            'first_name' => 'Other',
            'last_name' => 'User',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
        ]);

        Order::create([
            'pharmacy_id' => 1,
            'patient_id' => $otherUser->id,
            'priority' => 'normal',
            'status' => 'completed',
            'total_price' => 30.00,
            'created_by' => $otherUser->id,
        ]);

        $response = $this->getJson('/api/pharmacy/patient/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
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
                    ]
                ],
                'code'
            ]);

        $responseData = $response->json();
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Patient order history retrieved successfully', $responseData['message']);
        $this->assertCount(2, $responseData['data']); // Only the authenticated user's orders

        // Verify the orders belong to the authenticated user
        foreach ($responseData['data'] as $order) {
            $this->assertEquals($this->user->id, $order['patient_id']);
        }
    }

    public function test_patient_order_history_with_filters()
    {
        // Create orders with different statuses
        $order1 = Order::create([
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'priority' => 'normal',
            'status' => 'completed',
            'total_price' => 25.50,
            'created_by' => $this->user->id,
        ]);

        $order2 = Order::create([
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'priority' => 'urgent',
            'status' => 'pending',
            'total_price' => 50.00,
            'created_by' => $this->user->id,
        ]);

        // Filter by status
        $response = $this->getJson('/api/pharmacy/patient/orders?status=completed');

        $response->assertStatus(200);
        $responseData = $response->json();
        $this->assertCount(1, $responseData['data']);
        $this->assertEquals('completed', $responseData['data'][0]['status']);
    }

    public function test_patient_order_history_empty_when_no_orders()
    {
        $response = $this->getJson('/api/pharmacy/patient/orders');

        $response->assertStatus(200);
        $responseData = $response->json();
        $this->assertTrue($responseData['success']);
        $this->assertEquals('No order history found. Start by placing your first order!', $responseData['message']);
        $this->assertEmpty($responseData['data']);
    }

    public function test_patient_order_history_includes_order_details()
    {
        $order = Order::create([
            'pharmacy_id' => 1,
            'patient_id' => $this->user->id,
            'priority' => 'normal',
            'status' => 'completed',
            'total_price' => 25.50,
            'order_note' => 'Test order with note',
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/pharmacy/patient/orders');

        $response->assertStatus(200);
        $responseData = $response->json();
        $this->assertCount(1, $responseData['data']);

        $orderData = $responseData['data'][0];
        $this->assertEquals($order->id, $orderData['id']);
        $this->assertEquals($this->user->id, $orderData['patient_id']);
        $this->assertEquals('normal', $orderData['priority']);
        $this->assertEquals('completed', $orderData['status']);
        $this->assertEquals('25.50', $orderData['total_price']);
        $this->assertEquals('Test order with note', $orderData['order_note']);
    }
}
