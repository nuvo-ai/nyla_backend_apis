<?php

namespace App\Http\Controllers\Api\Hospital\Appointment;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\AppointmentResource;
use App\Http\Resources\Hospital\HospitalUsersResource;
use App\Services\Hospital\AppointmentService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public $hospital_appointment_service;
    public function __construct()
    {
        $this->hospital_appointment_service = new AppointmentService;
    }

    public function bookAppointment(Request $request)
    {
        try {
            $appointment = $this->hospital_appointment_service->book($request->all());
            return ApiHelper::validResponse("Appointment booked successfully", AppointmentResource::make($appointment));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $appointment = $this->hospital_appointment_service->updateStatus($request, $id);
            return ApiHelper::validResponse("Appointment status successfully", $appointment);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse($this->validationErrorMessage, ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
