<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Services\Pharmacy\MedicationService;
use App\Http\Resources\Pharmacy\MedicationResource;
use Illuminate\Http\Request;
use App\Helpers\ApiHelper;
use App\Http\Resources\Pharmacy\PharmacyRegistrationResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

class MedicationController extends Controller
{
    protected $medicationService;

    public function __construct(MedicationService $medicationService)
    {
        $this->medicationService = $medicationService;
    }

    public function index(Request $request)
    {
        try {
            $medications = $this->medicationService->list($request->all());

            if ($medications->isEmpty()) {
                return ApiHelper::validResponse('No medications found', [], 200);
            }

            return ApiHelper::validResponse('Medications retrieved successfully', MedicationResource::collection($medications));
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve medications. Please try again later.', 500, null, $e);
        }
    }

    public function show($id)
    {
        try {
            $medication = $this->medicationService->show($id);
            return ApiHelper::validResponse('Medication retrieved successfully', new MedicationResource($medication));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication not found. Please check the medication ID and try again.', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to retrieve medication. Please try again later.', 500, null, $e);
        }
    }

    public function store(Request $request)
    {
        try {
            $medication = $this->medicationService->create($request->all());
            return ApiHelper::validResponse('Medication created successfully', new MedicationResource($medication));
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = 'Please check the following errors: ' . implode(', ', array_keys($errors));
            return ApiHelper::inputErrorResponse($errorMessage, 422, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to create medication. Please try again later.', 500, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $medication = $this->medicationService->update($id, $request->all());
            return ApiHelper::validResponse('Medication updated successfully', new MedicationResource($medication));
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = 'Please check the following errors: ' . implode(', ', array_keys($errors));
            return ApiHelper::inputErrorResponse($errorMessage, 422, null, $e);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication not found. Please check the medication ID and try again.', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to update medication. Please try again later.', 500, null, $e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->medicationService->delete($id);
            return ApiHelper::validResponse('Medication deleted successfully');
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Medication not found. Please check the medication ID and try again.', 404, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse('Failed to delete medication. Please try again later.', 500, null, $e);
        }
    }
}
