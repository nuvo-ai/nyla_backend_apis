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

class OrderWithMedicationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $pharmacy;
    protected $medicationType;
    protected $medication;

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

        // Create a medication
        $this->medication = Medication::create([
            'pharmacy_id' => $this->pharmacy->id,
            'medication_type_id' => $this->medicationType->id,
            'name' => 'Test Medication',
            'description' => 'Test Description',
            'stock' => 100,
            'price' => 10.50,
            'is_active' => true,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_order_show_includes_medication_information()
    {
        // Create an order with items
        $order = Order::create([
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
            'order_id' => $order->id,
            'medication_id' => $this->medication->id,
            'quantity' => 2,
            'price' => 25.50,
            'status' => 'pending',
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
                    'patient',
                    'creator',
                    'items' => [
                        '*' => [
                            'id',
                            'order_id',
                            'medication_id',
                            'quantity',
                            'price',
                            'status',
                            'medication' => [
                                'id',
                                'name',
                                'description',
                                'price',
                                'stock',
                            ],
                            'created_at',
                            'updated_at',
                        ]
                    ],
                    'created_at',
                    'updated_at',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $order->id,
                    'items' => [
                        [
                            'medication_id' => $this->medication->id,
                            'medication' => [
                                'id' => $this->medication->id,
                                'name' => $this->medication->name,
                                'description' => $this->medication->description,
                                'price' => $this->medication->price,
                                'stock' => $this->medication->stock,
                            ]
                        ]
                    ]
                ]
            ]);
    }

    public function test_order_list_includes_medication_information()
    {
        // Create an order with items
        $order = Order::create([
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
            'order_id' => $order->id,
            'medication_id' => $this->medication->id,
            'quantity' => 2,
            'price' => 25.50,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/pharmacy/orders');

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
                        'patient',
                        'creator',
                        'items' => [
                            '*' => [
                                'id',
                                'order_id',
                                'medication_id',
                                'quantity',
                                'price',
                                'status',
                                'medication' => [
                                    'id',
                                    'name',
                                    'description',
                                    'price',
                                    'stock',
                                ],
                                'created_at',
                                'updated_at',
                            ]
                        ],
                        'created_at',
                        'updated_at',
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'id' => $order->id,
                        'items' => [
                            [
                                'medication_id' => $this->medication->id,
                                'medication' => [
                                    'id' => $this->medication->id,
                                    'name' => $this->medication->name,
                                    'description' => $this->medication->description,
                                    'price' => $this->medication->price,
                                    'stock' => $this->medication->stock,
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
    }
}
