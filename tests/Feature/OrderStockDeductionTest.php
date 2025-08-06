<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User\User;
use App\Models\Pharmacy\Pharmacy;
use App\Models\Pharmacy\MedicationType;
use App\Models\Pharmacy\Medication;
use App\Models\Pharmacy\Order;
use App\Models\Pharmacy\OrderItem;

class OrderStockDeductionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $pharmacy;
    protected $medicationType;
    protected $medication;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Add the order_note column to the test database
        \DB::statement('ALTER TABLE orders ADD COLUMN order_note TEXT NULL');

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

        // Create a medication type
        $this->medicationType = MedicationType::create([
            'pharmacy_id' => $this->pharmacy->id,
            'name' => 'Test Type',
            'description' => 'Test Description',
            'is_active' => true,
        ]);

        // Create a medication with initial stock
        $this->medication = Medication::create([
            'pharmacy_id' => $this->pharmacy->id,
            'medication_type_id' => $this->medicationType->id,
            'name' => 'Test Medication',
            'description' => 'Test Description',
            'stock' => 100,
            'price' => 10.50,
            'is_active' => true,
        ]);

        // Create an order
        $this->order = Order::create([
            'pharmacy_id' => $this->pharmacy->id,
            'patient_id' => $this->user->id,
            'priority' => 'normal',
            'status' => 'pending',
            'total_price' => 25.50,
            'prescription_url' => 'https://example.com/prescription.pdf',
            'order_note' => 'Please deliver in the morning',
            'created_by' => $this->user->id,
        ]);

        // Create order items
        OrderItem::create([
            'order_id' => $this->order->id,
            'medication_id' => $this->medication->id,
            'quantity' => 5,
            'price' => 25.50,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_stock_is_deducted_when_order_is_delivered()
    {
        $initialStock = $this->medication->stock;
        $orderQuantity = 5;

        // Update order status to delivered
        $response = $this->putJson("/api/pharmacy/orders/{$this->order->id}", [
            'status' => 'delivered'
        ]);

        $response->assertStatus(200);

        // Refresh medication to get updated stock
        $this->medication->refresh();

        // Assert that stock was deducted
        $this->assertEquals($initialStock - $orderQuantity, $this->medication->stock);
    }

    public function test_stock_is_not_deducted_when_order_status_is_not_delivered()
    {
        $initialStock = $this->medication->stock;

        // Update order status to processing (not delivered)
        $response = $this->putJson("/api/pharmacy/orders/{$this->order->id}", [
            'status' => 'processing'
        ]);

        $response->assertStatus(200);

        // Refresh medication to get updated stock
        $this->medication->refresh();

        // Assert that stock was not deducted
        $this->assertEquals($initialStock, $this->medication->stock);
    }

    public function test_stock_is_not_deducted_twice_when_order_already_delivered()
    {
        // First delivery
        $response = $this->putJson("/api/pharmacy/orders/{$this->order->id}", [
            'status' => 'delivered'
        ]);
        $response->assertStatus(200);

        $firstDeliveryStock = $this->medication->fresh()->stock;

        // Try to deliver again
        $response = $this->putJson("/api/pharmacy/orders/{$this->order->id}", [
            'status' => 'delivered'
        ]);
        $response->assertStatus(200);

        $secondDeliveryStock = $this->medication->fresh()->stock;

        // Assert that stock was not deducted again
        $this->assertEquals($firstDeliveryStock, $secondDeliveryStock);
    }

    public function test_stock_deduction_handles_multiple_items()
    {
        // Create another medication
        $medication2 = Medication::create([
            'pharmacy_id' => $this->pharmacy->id,
            'medication_type_id' => $this->medicationType->id,
            'name' => 'Test Medication 2',
            'description' => 'Test Description 2',
            'stock' => 50,
            'price' => 15.00,
            'is_active' => true,
        ]);

        // Add another item to the order
        OrderItem::create([
            'order_id' => $this->order->id,
            'medication_id' => $medication2->id,
            'quantity' => 3,
            'price' => 15.00,
            'status' => 'pending',
        ]);

        $initialStock1 = $this->medication->stock;
        $initialStock2 = $medication2->stock;

        // Update order status to delivered
        $response = $this->putJson("/api/pharmacy/orders/{$this->order->id}", [
            'status' => 'delivered'
        ]);

        $response->assertStatus(200);

        // Refresh medications to get updated stock
        $this->medication->refresh();
        $medication2->refresh();

        // Assert that stock was deducted for both medications
        $this->assertEquals($initialStock1 - 5, $this->medication->stock);
        $this->assertEquals($initialStock2 - 3, $medication2->stock);
    }

    public function test_stock_deduction_logs_activity()
    {
        // Update order status to delivered
        $response = $this->putJson("/api/pharmacy/orders/{$this->order->id}", [
            'status' => 'delivered'
        ]);

        $response->assertStatus(200);

        // Check that pharmacy activity was logged
        $activities = \App\Models\Pharmacy\PharmacyActivity::where('pharmacy_id', $this->pharmacy->id)
            ->where('action', 'Stock deducted for delivered order')
            ->get();

        $this->assertCount(1, $activities);
        $this->assertEquals($this->order->id, $activities->first()->meta['order_id']);
        $this->assertEquals($this->medication->id, $activities->first()->meta['medication_id']);
    }
}
