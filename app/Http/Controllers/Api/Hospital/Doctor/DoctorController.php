<?php

namespace App\Http\Controllers\Api\Hospital\Doctor;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\DoctorResource;
use App\Services\Hospital\Doctor\DoctorService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class DoctorController extends Controller
{
    protected $doctor_service;
    protected $user_service;

    public function __construct()
    {
        $this->doctor_service = new DoctorService;
        $this->user_service = new UserService;
    }

    public function index(Request $request)
    {
        try {
            $doctors = $this->doctor_service->listDoctors($request->all());
            return ApiHelper::validResponse("Doctor list retrieved successfully", DoctorResource::collection($doctors));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    private function requestedDoctorDataDuringCreation(): array
    {
        return [
            'departments',
            'medical_number',
            'medical_specialties',
            'hospital_id',
            'status',
        ];
    }

    public function store(Request $request)
    {
        try {
            $userData = $request->except($this->requestedDoctorDataDuringCreation());
            $userData['portal'] = 'Hospital';
            $userData['hospital_id'] = $request->hospital_id;

            $user = $this->user_service->create($userData);
            $hospitalUser = $user->hospitalUser;
            $requestDoctorData = $request->only($this->requestedDoctorDataDuringCreation());

            $doctorPayload = array_merge($requestDoctorData, [
                'user_id' => $user->id,
                'hospital_id' => $hospitalUser?->hospital_id,
                'hospital_user_id' => $hospitalUser?->id,
            ]);
            $doctor = $this->doctor_service->save($doctorPayload);

            return ApiHelper::validResponse("Doctor created successfully", DoctorResource::make($doctor));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }


    public function show($doctor)
    {
        try {
            $doctor = $this->doctor_service::getById($doctor);
            return ApiHelper::validResponse("Doctor retrieved successfully", DoctorResource::make($doctor));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Doctor not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function update(Request $request, $doctor)
    {
        try {
            $doctor = $this->doctor_service->save($request->all(), $doctor);
            return ApiHelper::validResponse("Doctor updated successfully", DoctorResource::make($doctor));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Doctor not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function destroy($doctor)
    {
        try {
            $doctor = $this->doctor_service::getById($doctor);
            $doctor->delete();
            return ApiHelper::validResponse("Doctor deleted successfully");
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Doctor not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
