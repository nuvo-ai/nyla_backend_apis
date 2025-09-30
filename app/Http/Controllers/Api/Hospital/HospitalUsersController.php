<?php

namespace App\Http\Controllers\Api\Hospital;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\HospitalUsersResource;
use App\Services\Hospital\HospitalUserService;
use App\Services\User\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class HospitalUsersController extends Controller
{
    public $hospital_user_service;
    public $user_service;
    public function __construct()
    {
        $this->user_service = new UserService;
        $this->hospital_user_service = new HospitalUserService;
    }
    public function list(Request $request)
    {
        try {
            // $filters = $request->only(['status', 'type', 'search']);
            $hospitals = $this->hospital_user_service->listHospitalUsers($request->all());
            return ApiHelper::validResponse("Hospital users retrieved successfully", HospitalUsersResource::collection($hospitals));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function create(Request $request)
    {
        try {
            $user = $this->user_service->create($request->all());
            $hospitalUser = $user->hospitalUser()->with('user')->first();
            return ApiHelper::validResponse("Hospital user create successfully", HospitalUsersResource::make($hospitalUser));
        } catch (ValidationException $e) {
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function delete($id)
    {
        try {
            $data = $this->hospital_user_service->deleteHospitalUser($id);
            return ApiHelper::validResponse("Hospital user deleted successfully", $data);
        } catch (ValidationException $e) {
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function assignRole(Request $request, $id)
    {
        try {
            $data = $this->hospital_user_service->assignRoleToUser($request->all(), $id);
            return ApiHelper::validResponse("Hospital user role updated successfully", $data);
        } catch (ValidationException $e) {
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
