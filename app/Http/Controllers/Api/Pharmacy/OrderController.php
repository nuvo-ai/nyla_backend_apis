<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Services\Pharmacy\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $orders = $this->orderService->list($request->all());
        return response()->json($orders);
    }

    public function show($id)
    {
        $order = $this->orderService->show($id);
        return response()->json($order);
    }

    public function store(Request $request)
    {
        $order = $this->orderService->create($request->all());
        return response()->json($order, 201);
    }

    public function update(Request $request, $id)
    {
        $order = $this->orderService->update($id, $request->all());
        return response()->json($order);
    }

    public function destroy($id)
    {
        $this->orderService->delete($id);
        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function export($id)
    {
        $order = $this->orderService->export($id);
        return response()->json($order); // Placeholder for actual export
    }

    public function emr(Request $request)
    {
        $pharmacy_id = $request->get('pharmacy_id');
        $emr = $this->orderService->emr($pharmacy_id);
        return response()->json($emr);
    }

    public function statistics(Request $request)
    {
        $pharmacy_id = $request->get('pharmacy_id');
        if (!$pharmacy_id) {
            return response()->json(['error' => 'pharmacy_id is required'], 422);
        }
        $stats = $this->orderService->statistics($pharmacy_id);
        return response()->json($stats);
    }
}
