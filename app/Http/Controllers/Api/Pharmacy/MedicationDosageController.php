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

            if ($dosages->isEmpty()) {
                return ApiHelper::validResponse('No medication dosages found', [], 200);
            }

            return ApiHelper::validResponse('Medication dosages retrieved successfully', MedicationDosageResource::collection($dosages));
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve medication dosages. Please try again later.', 500, null, $e);
        }
    }

    public function show($id)
    {
        try {
            $dosage = $this->medicationDosageService->show($id);
            return ApiHelper::validResponse('Medication dosage retrieved successfully', new MedicationDosageResource($dosage));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication dosage not found. Please check the dosage ID and try again.', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve medication dosage. Please try again later.', 500, null, $e);
        }
    }

    public function store(Request $request)
    {
        try {
            $dosage = $this->medicationDosageService->create($request->all());
            return ApiHelper::validResponse('Medication dosage created successfully', new MedicationDosageResource($dosage));
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = 'Please check the following errors: ' . implode(', ', array_keys($errors));
            return ApiHelper::inputErrorResponse($errorMessage, 422, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to create medication dosage. Please try again later.', 500, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $dosage = $this->medicationDosageService->update($id, $request->all());
            return ApiHelper::validResponse('Medication dosage updated successfully', new MedicationDosageResource($dosage));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication dosage not found. Please check the dosage ID and try again.', 404, null, $e);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = 'Please check the following errors: ' . implode(', ', array_keys($errors));
            return ApiHelper::inputErrorResponse($errorMessage, 422, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to update medication dosage. Please try again later.', 500, null, $e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->medicationDosageService->delete($id);
            return ApiHelper::validResponse('Medication dosage deleted successfully');
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication dosage not found. Please check the dosage ID and try again.', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to delete medication dosage. Please try again later.', 500, null, $e);
        }
    }

    /**
     * Get dosages for a specific medication
     */
    public function getDosagesByMedication($medicationId)
    {
        try {
            $dosages = $this->medicationDosageService->getDosagesByMedication($medicationId);

            if ($dosages->isEmpty()) {
                return ApiHelper::validResponse('No dosages found for this medication', [], 200);
            }

            return ApiHelper::validResponse('Medication dosages retrieved successfully', MedicationDosageResource::collection($dosages));
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve medication dosages. Please try again later.', 500, null, $e);
        }
    }

    /**
     * Get available forms for a medication
     */
    public function getAvailableForms($medicationId)
    {
        try {
            $forms = $this->medicationDosageService->getAvailableForms($medicationId);

            if (empty($forms)) {
                return ApiHelper::validResponse('No forms available for this medication', [], 200);
            }

            return ApiHelper::validResponse('Available forms retrieved successfully', $forms);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve available forms. Please try again later.', 500, null, $e);
        }
    }
}
