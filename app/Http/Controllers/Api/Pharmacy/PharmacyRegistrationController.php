<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\pharmacy\PharmacyRegistrationResource;
use App\Models\Pharmacy\Pharmacy;
use App\Services\Pharmacy\PharmacyService;
use App\Services\User\UserService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class PharmacyRegistrationController extends Controller
{
    public $pharmacy_service;
    public $user;
    public function __construct()
    {
        $this->pharmacy_service = new PharmacyService;
        $this->user = new UserService;
    }

    public function registerpharmacy(Request $request)
    {
        try {
            $user = $this->user->create($this->requestedUserDataduringpharmacyRegistration($request));
            $pharmacy_data = $request->except(['user_name', 'user_email', 'user_phone', 'portal', 'password', 'generated_password']);
            $pharmacy_data['user_id'] = $user->id;
            $pharmacy = $this->pharmacy_service->createPharmacy($pharmacy_data);

            return ApiHelper::validResponse("Pharmacy created successfully", PharmacyRegistrationResource::make($pharmacy));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function updatepharmacy(Request $request, $id)
    {
        try {
            $pharmacy = $this->pharmacy_service->updatePharmacy($request->all(), $id);

            return ApiHelper::validResponse("Pharmacy updated successfully", PharmacyRegistrationResource::make($pharmacy));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    private function requestedUserDataduringPharmacyRegistration(Request $request)
    {
        $generated_password = $this->generateRandomPasswordDuringpharmacyRegistration();

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

    private function generateRandomPasswordDuringPharmacyRegistration(int $length = 10): string
    {
        return Str::random($length);
    }
}
