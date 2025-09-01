<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Services\Pharmacy\OrderService;
use App\Http\Resources\Pharmacy\OrderResource;
use Illuminate\Http\Request;
use App\Helpers\ApiHelper;
use App\Http\Resources\Pharmacy\PharmacyRegistrationResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        try {
            $orders = $this->orderService->list($request->all());

            if ($orders->isEmpty()) {
                return ApiHelper::validResponse('No orders found', [], 200);
            }

            return ApiHelper::validResponse('Orders retrieved successfully', OrderResource::collection($orders));
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve orders. Please try again later.', 500, null, $e);
        }
    }

    public function show($id)
    {
        try {
            $order = $this->orderService->show($id);
            return ApiHelper::validResponse('Order retrieved successfully', new OrderResource($order));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Order not found', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function store(Request $request)
    {
        try {
            $order = $this->orderService->create($request->all());
            return ApiHelper::validResponse('Order created successfully', new OrderResource($order));
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = 'Please check the following errors: ' . implode(', ', array_keys($errors));
            return ApiHelper::inputErrorResponse($errorMessage, 422, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to create order. Please try again later.', 500, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $order = $this->orderService->update($id, $request->all());
            return ApiHelper::validResponse('Order updated successfully', new OrderResource($order));
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = 'Please check the following errors: ' . implode(', ', array_keys($errors));
            return ApiHelper::inputErrorResponse($errorMessage, 422, null, $e);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Order not found. Please check the order ID and try again.', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to update order. Please try again later.', 500, null, $e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->orderService->delete($id);
            return ApiHelper::validResponse('Order deleted successfully');
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Order not found. Please check the order ID and try again.', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to delete order. Please try again later.', 500, null, $e);
        }
    }

    public function export($id)
    {
        try {
            $order = $this->orderService->export($id);
            return ApiHelper::validResponse('Order exported successfully', $order);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Order not found. Please check the order ID and try again.', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to export order. Please try again later.', 500, null, $e);
        }
    }

    public function emr(Request $request)
    {
        try {
            $pharmacy_id = $request->get('pharmacy_id');

            if (!$pharmacy_id) {
                return ApiHelper::inputErrorResponse('Pharmacy ID is required to retrieve EMR data.', 422);
            }

            $emr = $this->orderService->emr($pharmacy_id);

            if ($emr->isEmpty()) {
                return ApiHelper::validResponse('No EMR data found for this pharmacy', [], 200);
            }

            return ApiHelper::validResponse('EMR retrieved successfully', $emr);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve EMR data. Please try again later.', 500, null, $e);
        }
    }

    public function statistics(Request $request)
    {
        try {
            $pharmacy_id = $request->get('pharmacy_id');
            if (!$pharmacy_id) {
                return ApiHelper::inputErrorResponse('Pharmacy ID is required to retrieve statistics.', 422);
            }
            $stats = $this->orderService->statistics($pharmacy_id);
            return ApiHelper::validResponse('Pharmacy statistics retrieved successfully', $stats);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve pharmacy statistics. Please try again later.', 500, null, $e);
        }
    }

    public function patientOrderHistory(Request $request)
    {
        try {
            $patient_id = auth()->id();

            if (!$patient_id) {
                return ApiHelper::problemResponse('Authentication required. Please log in to view your order history.', 401);
            }

            $filters = $request->all();
            $filters['patient_id'] = $patient_id;

            $orders = $this->orderService->list($filters);

            if ($orders->isEmpty()) {
                return ApiHelper::validResponse('No order history found. Start by placing your first order!', [], 200);
            }

            return ApiHelper::validResponse('Patient order history retrieved successfully', OrderResource::collection($orders));
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve order history. Please try again later.', 500, null, $e);
        }
    }

    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request, [
            'status' => 'required|string',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        try {
            $result = $this->orderService->changeStatus($request->all(), $id);
            return ApiHelper::validResponse('Order status updated successfully', $result);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse(
                'Order not found. Please check the order ID and try again.',
                404,
                null,
                $e
            );
        } catch (Exception $e) {
            return ApiHelper::problemResponse(
                'Failed to update order status. Please try again later.',
                500,
                null,
                $e
            );
        }
    }
}
