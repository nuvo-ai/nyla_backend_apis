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

            if ($medicationTypes->isEmpty()) {
                return ApiHelper::validResponse('No medication types found', [], 200);
            }

            return ApiHelper::validResponse('Medication types retrieved successfully', MedicationTypeResource::collection($medicationTypes));
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve medication types. Please try again later.', 500, null, $e);
        }
    }

    public function show($id)
    {
        try {
            $medicationType = $this->medicationTypeService->show($id);
            return ApiHelper::validResponse('Medication type retrieved successfully', new MedicationTypeResource($medicationType));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication type not found. Please check the medication type ID and try again.', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve medication type. Please try again later.', 500, null, $e);
        }
    }

    public function store(Request $request)
    {
        try {
            $medicationType = $this->medicationTypeService->create($request->all());
            return ApiHelper::validResponse('Medication type created successfully', new MedicationTypeResource($medicationType));
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = 'Please check the following errors: ' . implode(', ', array_keys($errors));
            return ApiHelper::inputErrorResponse($errorMessage, 422, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to create medication type. Please try again later.', 500, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $medicationType = $this->medicationTypeService->update($id, $request->all());
            return ApiHelper::validResponse('Medication type updated successfully', new MedicationTypeResource($medicationType));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication type not found. Please check the medication type ID and try again.', 404, null, $e);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = 'Please check the following errors: ' . implode(', ', array_keys($errors));
            return ApiHelper::inputErrorResponse($errorMessage, 422, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to update medication type. Please try again later.', 500, null, $e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->medicationTypeService->delete($id);
            return ApiHelper::validResponse('Medication type deleted successfully');
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication type not found. Please check the medication type ID and try again.', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to delete medication type. Please try again later.', 500, null, $e);
        }
    }
}
