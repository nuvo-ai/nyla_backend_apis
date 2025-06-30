<?php

namespace App\Http\Controllers\Api\Hospital\Registration;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\HospitalRegistrationResource;
use App\Models\User;
use App\Services\Hospital\Registration\HotelRegistrationService;
use App\Services\User\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class HospitalRegistrationController extends Controller
{
    public $hospital_registration_service;
    public $user;
    public function __construct()
    {
        $this->hospital_registration_service = new HotelRegistrationService;
        $this->user = new UserService;
    }

    public function registerHospital(Request $request)
    {
        try {
            $user = $this->user->create($this->requestedUserDataduringHospitalRegistration($request));
            $hospital_data = $request->except(['user_name', 'user_email', 'user_phone_number', 'portal', 'password', 'generated_password']);
            $hospital_data['user_id'] = $user->id;
            $hospital = $this->hospital_registration_service->registerHospital($hospital_data);

            return ApiHelper::validResponse("Hospital created successfully", HospitalRegistrationResource::make($hospital));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    private function requestedUserDataduringHospitalRegistration(Request $request)
    {
        $generated_password = $this->generateRandomPasswordDuringHospitalRegistration();

        $request->merge(['generated_password' => $generated_password]);

        return [
            'user_name' => $request->input('user_name'),
            'user_email' => $request->input('user_email'),
            'user_phone_number' => $request->input('user_phone_number'),
            'portal' => $request->input('portal'),
            'role' => $request->input('role'),
            'password' => $generated_password,
        ];
    }


    private function generateRandomPasswordDuringHospitalRegistration(int $length = 10): string
    {
        return Str::random($length);
    }
}
