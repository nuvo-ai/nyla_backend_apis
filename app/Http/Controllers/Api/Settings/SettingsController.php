<?php

namespace App\Http\Controllers\Api\Settings;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordService;
use App\Services\Notification\NotificationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
     public $password_service;
     public $notification_service;
    public function __construct()
    {
        $this->password_service = new PasswordService;
        $this->notification_service = new NotificationService;
    }


    public function changePassword(Request $request)
    {
         try {
            $data = $this->password_service->changePassword($request);
            return ApiHelper::validResponse("Password updated successfully", $data);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function setPreferences(Request $request)
    {
        try {
            $data  = $this->notification_service->setPreferences($request->all());
            return ApiHelper::validResponse("Notification settings updated successfully", $data);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function updateSystemSettings(Request $request)
    {
        // Logic to update system settings
    }

    public function updatePreferences(Request $request)
    {
        // Logic to update user preferences
    }

    public function getSettings()
    {
        // Logic to retrieve all settings
    }
}
