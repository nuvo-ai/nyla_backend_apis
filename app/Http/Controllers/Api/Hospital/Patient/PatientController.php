<?php

namespace App\Http\Controllers\Api\Hospital\Patient;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\PatientResource;
use App\Services\Hospital\Patient\PatientService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Str;

class PatientController extends Controller
{
    protected $patient_service;
    public $user;

    public function __construct()
    {
        $this->patient_service = new PatientService;
        $this->user = new UserService;
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
        dd($request->all());
        try {
            $userData = $this->requestedUserDataDuringPatientRegistration($request);
            $userResult = $this->user->create($userData);
            $user = $userResult['user'];
            $patientData = $request->except(array_keys($userData));
            $patientData['user_id'] = $user->id;
            $patient = $this->patient_service->save($patientData);
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
            $existingPatient = $this->patient_service::getById($patient);
            if ($existingPatient->user_id) {
                $userData = $this->requestedUserDataDuringPatientRegistration($request);
                $this->user->update($userData, $existingPatient->user_id);
            }
            $patientData = $request->except(array_keys($this->requestedUserDataDuringPatientRegistration($request)));
            $patientData['user_id'] = $existingPatient->user_id;
            $updatedPatient = $this->patient_service->save($patientData, $patient);
            return ApiHelper::validResponse("Patient updated successfully", PatientResource::make($updatedPatient));
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

    private function requestedUserDataDuringPatientRegistration(Request $request): array
    {
        $generated_password = $this->generateRandomPasswordDuringHospitalRegistration();

        $request->merge(['generated_password' => $generated_password]);

        return [
            'email'       => $request->input('user_email'),
            'phone'       => $request->input('user_phone'),
            'portal'      => $request->input('portal'),
            'role'        => $request->input('role'),
            'password'    => $generated_password,
            'name'        => $request->input('user_name'),
            'gender'      => $request->input('gender'),
            'date_of_birth' => $request->input('date_of_birth'),
        ];
    }


    private function generateRandomPasswordDuringHospitalRegistration(int $length = 10): string
    {
        return Str::random($length);
    }
}
