<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Services\User\UserService;
use App\Http\Controllers\Controller;
use App\Services\User\ProfileService;
use App\Constants\General\ApiConstants;
use App\Http\Resources\Hospital\HospitalRegistrationResource;
use App\Http\Resources\User\UserResource;
use App\Models\User\User;
use App\Services\Hospital\HospitalService;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public $user_service;
    public $profile_service;
    public $interest_service;
    public $blocked_user_service;
    public $avatar_service;
    public $social_auth_link_service;
    public $hospital_service;

    public function __construct()
    {
        $this->user_service = new UserService;
        $this->profile_service = new ProfileService;
        $this->hospital_service = new HospitalService;
    }
    public function me()
    {
        try {
            $user = User::with([
                'hospitalUser.doctor',
                'hospitalUser.frontdesk',
                'pharmacy'
            ])->find(auth()->id());
            return ApiHelper::validResponse("User retrieved successfully", UserResource::make($user));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function users()
    {
        try {
            $user = User::latest()->get();
            return ApiHelper::validResponse("Users retrieved successfully", UserResource::collection($user));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function update(Request $request)
    {
        try {
            $user = $this->profile_service->update($request->all(), auth()->id());
            return ApiHelper::validResponse("User updated successfully", UserResource::make($user));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    //delete user
    public function delete()
    {
        try {
            $user = $this->user_service->delete(auth()->id());
            return ApiHelper::validResponse("User deleted successfully", UserResource::make($user));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = $this->user_service->delete($id);
            return ApiHelper::validResponse("User deleted successfully", UserResource::make($user));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    // restore user
    public function restore($id)
    {
        try {
            $user = $this->user_service->restore($id);
            return ApiHelper::validResponse("User restored successfully", UserResource::make($user));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse(
                $this->validationErrorMessage,
                ApiConstants::VALIDATION_ERR_CODE,
                null,
                $e
            );
        } catch (Exception $e) {
            return ApiHelper::problemResponse(
                $this->serverErrorMessage,
                ApiConstants::SERVER_ERR_CODE,
                null,
                $e
            );
        }
    }


    public function getUserHospital()
    {
        try {
            $hospital = $this->hospital_service->getUserHospital();
            if (!$hospital) {
                return ApiHelper::problemResponse("No hospital found for the specified user", ApiConstants::NOT_FOUND_ERR_CODE);
            }
            return ApiHelper::validResponse("Hospital retrieved successfully", HospitalRegistrationResource::make($hospital));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
