<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Pharmacy\PharmacyRegistrationResource;
use App\Models\Pharmacy\Pharmacy;
use App\Services\Pharmacy\PharmacyService;
use App\Services\User\UserService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function list(Request $request)
    {
        try {
            $pharmacies = $this->pharmacy_service->listPharmacy($request->all());
            return ApiHelper::validResponse("Pharmacies retrieved successfully", PharmacyRegistrationResource::collection($pharmacies));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function getPharmacy(string $uuid)
    {
        try {
            $Pharmacys = $this->pharmacy_service->getPharmacy($uuid);
            return ApiHelper::validResponse("Pharmacy retrieved successfully", PharmacyRegistrationResource::make($Pharmacys));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Pharmacy with the specified identifier was not found in the system", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function registerpharmacy(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->user->create($this->requestedUserDataduringpharmacyRegistration($request));
            $pharmacy_data = $request->except(['user_name', 'user_email', 'user_phone', 'portal', 'password', 'generated_password']);
            $pharmacy_data['user_id'] = $user->id;
            $pharmacy = $this->pharmacy_service->createPharmacy($pharmacy_data);

            return ApiHelper::validResponse("Pharmacy created successfully", PharmacyRegistrationResource::make($pharmacy));
            DB::commit();
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
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

    public function toggleActive($id)
    {
        try {
            $pharmacy = $this->pharmacy_service->toggleActive($id);
            return ApiHelper::validResponse(
                $pharmacy->is_active ? 'Pharmacy activated successfully' : 'Pharmacy deactivated successfully',
                new \App\Http\Resources\Pharmacy\PharmacyRegistrationResource($pharmacy)
            );
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse('Pharmacy not found', ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
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
