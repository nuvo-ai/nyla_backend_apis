<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Auth\SanctumService;
use App\Constants\General\ApiConstants;
use App\Http\Resources\User\UserResource;
use Illuminate\Support\Facades\Validator;
use App\Services\Auth\RegistrationService;
use Illuminate\Validation\ValidationException;
use App\Services\Auth\DriverRegistrationService;
use App\Http\Requests\Auth\RegistrationFormRequest;

class RegisterController extends Controller
{
    public $register_service;

    function __construct()
    {
        $this->register_service = new RegistrationService;
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|min:3',
                'last_name' => 'required|min:3',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'confirm_password' => 'required|same:password',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $data = $validator->validated();
            $user = $this->register_service->create($data);
            $data["token"] = $user->createToken(SanctumService::SESSION_KEY)->plainTextToken;
            $data["user"] =  UserResource::make($user);
            $this->register_service->postRegisterActions($user);
            return ApiHelper::validResponse("User registered successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }

}
