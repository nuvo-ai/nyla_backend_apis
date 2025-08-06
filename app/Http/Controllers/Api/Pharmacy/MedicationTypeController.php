<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Services\Pharmacy\MedicationTypeService;
use App\Http\Resources\Pharmacy\MedicationTypeResource;
use Illuminate\Http\Request;
use App\Helpers\ApiHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class MedicationTypeController extends Controller
{
    protected $medicationTypeService;

    public function __construct(MedicationTypeService $medicationTypeService)
    {
        $this->medicationTypeService = $medicationTypeService;
    }

    public function index(Request $request)
    {
        try {
            $medicationTypes = $this->medicationTypeService->list($request->all());
            return ApiHelper::validResponse('Medication types retrieved successfully', MedicationTypeResource::collection($medicationTypes));
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function show($id)
    {
        try {
            $medicationType = $this->medicationTypeService->show($id);
            return ApiHelper::validResponse('Medication type retrieved successfully', new MedicationTypeResource($medicationType));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication type not found', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function store(Request $request)
    {
        try {
            $medicationType = $this->medicationTypeService->create($request->all());
            return ApiHelper::validResponse('Medication type created successfully', new MedicationTypeResource($medicationType));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse('Validation error', 422, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $medicationType = $this->medicationTypeService->update($id, $request->all());
            return ApiHelper::validResponse('Medication type updated successfully', new MedicationTypeResource($medicationType));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication type not found', 404, null, $e);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse('Validation error', 422, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->medicationTypeService->delete($id);
            return ApiHelper::validResponse('Medication type deleted successfully');
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication type not found', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }
}
