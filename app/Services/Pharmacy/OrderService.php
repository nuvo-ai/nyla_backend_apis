<?php

namespace App\Services\Pharmacy;

use App\Constants\General\StatusConstants;
use App\Constants\User\UserConstants;
use App\Models\Pharmacy\Order;
use App\Models\Pharmacy\OrderItem;
use App\Models\Pharmacy\Medication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\Notification\AppMailerService;
use App\Models\Pharmacy\Pharmacy;
use App\Models\User\User;
use App\Services\Pharmacy\PharmacyActivityService;
use App\Services\User\UserService;
use Exception;

class OrderService
{
    public function list(array $filters = [])
    {
        $query = Order::query();
        if (isset($filters['pharmacy_id'])) {
            $query->where('pharmacy_id', $filters['pharmacy_id']);
        }
        if (isset($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        return $query->with(['items.medication', 'patient', 'creator'])->get();
    }

    public function show($id)
    {
        return Order::with(['items.medication', 'patient', 'creator'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'patient_id' => 'nullable|exists:users,id',
            'priority' => 'required|in:urgent,normal',
            'status' => 'required|in:pending,processing,accepted,completed,delivered,dispensed,declined',
            'total_price' => 'required|numeric',
            'prescription_url' => 'nullable|string',
            'order_note' => 'nullable|string',
            'items' => 'required|array',
            'items.*.medication_id' => 'required|exists:medications,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data['created_by'] = auth()->id();
        $pharmacy = \App\Models\Pharmacy\Pharmacy::findOrFail($data['pharmacy_id']);
        if (!$pharmacy->is_active) {
            throw ValidationException::withMessages([
                'pharmacy' => ['This pharmacy is not accepting orders at the moment.']
            ]);
        }
        return DB::transaction(function () use ($data) {
    // Ensure patient details have defaults
    if (!isset($data['patient_name']) || empty($data['patient_name'])) {
        $data['patient_name'] = 'patient_name';
    }
    if (!isset($data['patient_phone']) || empty($data['patient_phone'])) {
        $data['patient_phone'] = 'patient_phone';
    }
    if (!isset($data['patient_email']) || empty($data['patient_email'])) {
        $data['patient_email'] = 'patient_email';
    }
    if (!isset($data['patient_address']) || empty($data['patient_address'])) {
        $data['patient_address'] = 'patient_address';
    }

    // Check if patient details exist and create a user if patient_id is not set
    if (empty($data['patient_id']) && !empty($data['patient_email'])) {
        $userService = app()->make(UserService::class);

        $userData = [
            'name'   => $data['patient_name'],
            'email'  => $data['patient_email'],
            'phone'  => $data['patient_phone'],
            'status' => StatusConstants::ACTIVE,
            'role'   => UserConstants::USER,
            'portal' => 'Pharmacy',
        ];

        $user = $userService->create($userData);
        $data['patient_id'] = $user->id; // link order to newly created user
    }

    // Create order
    $order = Order::create([
        'pharmacy_id'     => $data['pharmacy_id'],
        'patient_id'      => $data['patient_id'] ?? null,
        'status'          => $data['status'] ?? 'pending',
        'total_price'     => $data['total_price'],
        'prescription_url'=> $data['prescription_url'] ?? null,
        'order_note'      => $data['order_note'] ?? null,
        'created_by'      => $data['created_by'],
    ]);

    // Create order items
    foreach ($data['items'] as $item) {
        OrderItem::create([
            'order_id'     => $order->id,
            'medication_id'=> $item['medication_id'],
            'quantity'     => $item['quantity'],
            'price'        => $item['price'],
            'status'       => $item['status'] ?? 'pending',
        ]);
    }

    // Log activity: Order created
    PharmacyActivityService::log(
        $order->pharmacy_id,
        $order->created_by,
        'Order created',
        ['order_id' => $order->id]
    );

    return $order->load(['items.medication', 'patient', 'creator']);
});

    }

    public function update($id, array $data)
    {
        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $order->update($data);
        $order->refresh();
        // Optionally update items
        $order->load(['items.medication', 'patient', 'creator', 'pharmacy']);

        // Handle stock deduction when order is delivered
        if (isset($data['status']) && $data['status'] === 'delivered' && $oldStatus !== 'delivered') {
            $this->deductStockFromOrder($order);
        }

        // Log activity: Order status update
        if (isset($data['status'])) {
            $userId = $data['updated_by'] ?? auth()->id() ?? $order->created_by;
            $action = 'Order status updated to ' . ucfirst($order->status);
            PharmacyActivityService::log(
                $order->pharmacy_id,
                $userId,
                $action,
                ['order_id' => $order->id, 'status' => $order->status]
            );
        }

        // Send email to patient if present and email exists
        if ($order->patient && $order->patient->email) {
            $pharmacy = $order->pharmacy ?? Pharmacy::find($order->pharmacy_id);
            $feedback_url = null;
            if (in_array($order->status, ['delivered', 'completed', 'dispensed'])) {
                $feedback_url = url('/feedback?order_id=' . $order->id);
            }
            AppMailerService::send([
                'to' => $order->patient->email,
                'subject' => 'Your Order Status Has Been Updated',
                'template' => 'emails.order.status_update',
                'template_type' => 'markdown',
                'data' => [
                    'user' => $order->patient,
                    'order' => $order,
                    'pharmacy' => $pharmacy,
                    'feedback_url' => $feedback_url,
                ],
            ]);
        }
        return $order;
    }

    public function delete($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return true;
    }

    public function changeStatus(array $data, $id)
    {
        $allowedStatuses = [
            StatusConstants::COMPLETED,
            StatusConstants::CANCELLED,
            StatusConstants::DELIVERED,
            StatusConstants::DECLINED
        ];

        $status = strtolower($data['status']);

        if (!in_array($status, array_map('strtolower', $allowedStatuses))) {
            throw new Exception('Invalid input');
        }

        $order = Order::findOrFail($id);
        $order->update([
            'status' => $status,
        ]);

        return true;
    }


    public function export($id)
    {
        // Placeholder for export logic (CSV/PDF)
        $order = Order::with(['items', 'patient', 'creator'])->findOrFail($id);
        return $order;
    }

    public function emr($pharmacy_id)
    {
        // Aggregate completed/delivered orders for EMR
        return Order::where('pharmacy_id', $pharmacy_id)
            ->whereIn('status', ['completed', 'delivered'])
            ->with(['items', 'patient'])
            ->get();
    }

    public function statistics($pharmacy_id)
    {
        $orders = Order::where('pharmacy_id', $pharmacy_id);
        $totalOrders = $orders->count();
        $totalRevenue = $orders->sum('total_price');
        $totalPatients = $orders->distinct('patient_id')->count('patient_id');
        $totalDispensed = $orders->where('status', 'dispensed')->count();
        $totalCompleted = $orders->where('status', 'completed')->count();
        $totalDelivered = $orders->where('status', 'delivered')->count();

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'total_patients' => $totalPatients,
            'total_orders_dispensed' => $totalDispensed,
            'total_orders_completed' => $totalCompleted,
            'total_orders_delivered' => $totalDelivered,
        ];
    }

    /**
     * Deduct stock from medications when order is delivered
     */
    private function deductStockFromOrder($order)
    {
        foreach ($order->items as $item) {
            $medication = $item->medication;
            if ($medication) {
                // Check if there's enough stock
                if ($medication->stock >= $item->quantity) {
                    // Deduct the quantity from stock
                    $medication->decrement('stock', $item->quantity);

                    // Log the stock deduction activity
                    PharmacyActivityService::log(
                        $order->pharmacy_id,
                        auth()->id() ?? $order->created_by,
                        'Stock deducted for delivered order',
                        [
                            'order_id' => $order->id,
                            'medication_id' => $medication->id,
                            'medication_name' => $medication->name,
                            'quantity_deducted' => $item->quantity,
                            'remaining_stock' => $medication->stock - $item->quantity
                        ]
                    );
                } else {
                    // Log insufficient stock warning
                    PharmacyActivityService::log(
                        $order->pharmacy_id,
                        auth()->id() ?? $order->created_by,
                        'Insufficient stock for order delivery',
                        [
                            'order_id' => $order->id,
                            'medication_id' => $medication->id,
                            'medication_name' => $medication->name,
                            'requested_quantity' => $item->quantity,
                            'available_stock' => $medication->stock
                        ]
                    );
                }
            }
        }
    }
}
