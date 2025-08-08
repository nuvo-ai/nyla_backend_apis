<?php

namespace App\Http\Controllers\Api\Hospital;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\HospitalRegistrationResource;
use App\Services\Hospital\HospitalService;
use App\Services\User\UserService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class HospitalRegistrationController extends Controller
{
    public $hospital_service;
    public $user;
    public function __construct()
    {
        $this->hospital_service = new HospitalService;
        $this->user = new UserService;
    }

    public function list(Request $request)
    {
        try {
            // $filters = $request->only(['status', 'type', 'search']);
            $hospitals = $this->hospital_service->listHospitals($request->all());
            return ApiHelper::validResponse("Hospital retrieved successfully", HospitalRegistrationResource::collection($hospitals));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function getHospital(string $uuid)
    {
        try {
            $hospitals = $this->hospital_service->getHospital($uuid);
            return ApiHelper::validResponse("Hospital retrieved successfully", HospitalRegistrationResource::make($hospitals));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Hospital with the specified identifier was not found in the system", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function registerHospital(Request $request)
    {
        try {
            $userData = $this->requestedUserDataduringHospitalRegistration($request);
            $user = $this->user->create($userData);

            $hospital_data = $request->except([
                'user_name',
                'user_email',
                'user_phone',
                'portal',
                'password',
                'generated_password'
            ]);

            $hospital_data['user_id'] = $user->id;

            $hospital = $this->hospital_service->createHospital($hospital_data);

            return ApiHelper::validResponse("Hospital created successfully", HospitalRegistrationResource::make($hospital));
        } catch (ValidationException $e) {
             $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }


    public function updateHospital(Request $request, $id)
    {
        // dd($request->all(), $id);
        try {
            $hospital = $this->hospital_service->updateHospital($request->all(), $id);

            return ApiHelper::validResponse("Hospital updated successfully", HospitalRegistrationResource::make($hospital));
        } catch (ValidationException $e) {
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    private function requestedUserDataduringHospitalRegistration(Request $request)
    {
        $generated_password = $this->generateRandomPasswordDuringHospitalRegistration();

        $request->merge(['generated_password' => $generated_password]);
        return [
            'email'       => $request->input('user_email'),
            'phone' => $request->input('user_phone'),
            'portal'      => $request->input('portal'),
            'role'        => $request->input('role'),
            'password'    => $generated_password,
            'name'        => $request->input('user_name'),
        ];
    }

    private function generateRandomPasswordDuringHospitalRegistration(int $length = 10): string
    {
        return Str::random($length);
    }
}
