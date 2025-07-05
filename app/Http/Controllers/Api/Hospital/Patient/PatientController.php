<?php

namespace App\Http\Controllers\Api\Hospital\Patient;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\PatientResource;
use App\Services\Hospital\Patient\PatientService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class PatientController extends Controller
{
    protected $patient_service;

    public function __construct()
    {
        $this->patient_service = new PatientService;
    }

    public function index(Request $request)
    {
        try {
            $patients = $this->patient_service->listPatients($request->all());
            return ApiHelper::validResponse("Patient list retrieved successfully", PatientResource::collection($patients));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function store(Request $request)
    {
        try {
            $patient = $this->patient_service->save($request->all());
            return ApiHelper::validResponse("Patient created successfully", PatientResource::make($patient));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function show($patient)
    {
        try {
            $patient = $this->patient_service::getById($patient);
            return ApiHelper::validResponse("Patient retrieved successfully", PatientResource::make($patient));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Patient not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function update(Request $request, $patient)
    {
        try {
            $patient = $this->patient_service->save($request->all(), $patient);
            return ApiHelper::validResponse("Patient updated successfully", PatientResource::make($patient));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Patient not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function destroy($patient)
    {
        try {
            $patient = $this->patient_service::getById($patient);
            $patient->delete();
            return ApiHelper::validResponse("Patient deleted successfully");
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Patient not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
