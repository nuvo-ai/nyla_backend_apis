<?php

namespace App\Http\Controllers\Api\Auth;

use App\Constants\General\ApiConstants;
use App\Exceptions\Auth\OtpException;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Services\Auth\VerifyService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class VerificationController extends Controller
{
    public $verify_service;
    function __construct()
    {
        $this->verify_service = new VerifyService;
    }
    public function request(Request $request)
    {
        try {
            $this->verify_service->request($request->all());
            return ApiHelper::validResponse("Verification pin sent successfully");
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::throwableResponse($e, $request);
        }
    }

    public function verify(Request $request)
    {
        try {
            $this->verify_service->verify($request->all());
            return ApiHelper::validResponse("Email verified successfully");
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (OtpException $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::BAD_REQ_ERR_CODE);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::throwableResponse($e, $request);
        }
    }
}
