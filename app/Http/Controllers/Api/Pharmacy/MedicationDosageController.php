<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Services\Pharmacy\MedicationDosageService;
use App\Http\Resources\Pharmacy\MedicationDosageResource;
use Illuminate\Http\Request;
use App\Helpers\ApiHelper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class MedicationDosageController extends Controller
{
    protected $medicationDosageService;

    public function __construct(MedicationDosageService $medicationDosageService)
    {
        $this->medicationDosageService = $medicationDosageService;
    }

    public function index(Request $request)
    {
        try {
            $dosages = $this->medicationDosageService->list($request->all());
            return ApiHelper::validResponse('Medication dosages retrieved successfully', MedicationDosageResource::collection($dosages));
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function show($id)
    {
        try {
            $dosage = $this->medicationDosageService->show($id);
            return ApiHelper::validResponse('Medication dosage retrieved successfully', new MedicationDosageResource($dosage));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication dosage not found', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function store(Request $request)
    {
        try {
            $dosage = $this->medicationDosageService->create($request->all());
            return ApiHelper::validResponse('Medication dosage created successfully', new MedicationDosageResource($dosage));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse('Validation error', 422, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $dosage = $this->medicationDosageService->update($id, $request->all());
            return ApiHelper::validResponse('Medication dosage updated successfully', new MedicationDosageResource($dosage));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication dosage not found', 404, null, $e);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse('Validation error', 422, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->medicationDosageService->delete($id);
            return ApiHelper::validResponse('Medication dosage deleted successfully');
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication dosage not found', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    /**
     * Get dosages for a specific medication
     */
    public function getDosagesByMedication($medicationId)
    {
        try {
            $dosages = $this->medicationDosageService->getDosagesByMedication($medicationId);
            return ApiHelper::validResponse('Medication dosages retrieved successfully', MedicationDosageResource::collection($dosages));
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }

    /**
     * Get available forms for a medication
     */
    public function getAvailableForms($medicationId)
    {
        try {
            $forms = $this->medicationDosageService->getAvailableForms($medicationId);
            return ApiHelper::validResponse('Available forms retrieved successfully', $forms);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }
}
