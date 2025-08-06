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
            return ApiHelper::validResponse('Orders retrieved successfully', OrderResource::collection($orders));
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
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
            return ApiHelper::inputErrorResponse('Validation error', 422, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $order = $this->orderService->update($id, $request->all());
            return ApiHelper::validResponse('Order updated successfully', new OrderResource($order));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse('Validation error', 422, null, $e);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Order not found', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->orderService->delete($id);
            return ApiHelper::validResponse('Order deleted successfully');
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Order not found', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function export($id)
    {
        try {
            $order = $this->orderService->export($id);
            return ApiHelper::validResponse('Order exported successfully', $order);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Order not found', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function emr(Request $request)
    {
        try {
            $pharmacy_id = $request->get('pharmacy_id');
            $emr = $this->orderService->emr($pharmacy_id);
            return ApiHelper::validResponse('EMR retrieved successfully', $emr);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function statistics(Request $request)
    {
        try {
            $pharmacy_id = $request->get('pharmacy_id');
            if (!$pharmacy_id) {
                return ApiHelper::inputErrorResponse('pharmacy_id is required', 422);
            }
            $stats = $this->orderService->statistics($pharmacy_id);
            return ApiHelper::validResponse('Pharmacy statistics retrieved successfully', $stats);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function patientOrderHistory(Request $request)
    {
        try {
            $patient_id = auth()->id();
            $filters = $request->all();
            $filters['patient_id'] = $patient_id;

            $orders = $this->orderService->list($filters);
            return ApiHelper::validResponse('Patient order history retrieved successfully', OrderResource::collection($orders));
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }
}
