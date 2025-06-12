<?php

namespace App\Http\Controllers\Api\Auth;

use App\Constants\General\ApiConstants;
use App\Exceptions\Auth\AuthException;
use App\Exceptions\Auth\OtpException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class PasswordController extends Controller
{
    public $password_service;
    function __construct()
    {
        $this->password_service = new PasswordService;
    }

    public function forgotPassword(Request $request)
    {
        try {
            $this->password_service->sendPasswordResetPin($request->all());
            return ApiHelper::validResponse("Password request sent successfully!");
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (AuthException | OtpException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::throwableResponse($e, $request);
        }
    }



    public function resetPassword(Request $request)
    {
        try {
            $this->password_service->resetPassword($request->all());
            return ApiHelper::validResponse("Password reset successfully");
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (OtpException $e) {
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::throwableResponse($e, $request);
        }
    }
}
