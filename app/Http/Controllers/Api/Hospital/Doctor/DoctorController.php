<?php

namespace App\Http\Controllers\Api\Hospital\Doctor;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\DoctorResource;
use App\Services\Hospital\Doctor\DashboardStatsService;
use App\Services\Hospital\Doctor\DoctorService;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    protected $doctor_service;
    protected $user_service;
    protected $doctor_dashboard_service;


    public function __construct()
    {
        $this->doctor_service = new DoctorService;
        $this->user_service = new UserService;
        $this->doctor_dashboard_service = new DashboardStatsService;
    }

    public function index(Request $request)
    {
        try {
            $doctors = $this->doctor_service->listDoctors($request->all());
            return ApiHelper::validResponse("Doctor list retrieved successfully", DoctorResource::collection($doctors));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    private function requestedDoctorDataDuringCreation(): array
    {
        return [
            'departments',
            'medical_number',
            'medical_specialties',
            'hospital_id',
            'status',
        ];
    }

  public function store(Request $request)
{
    DB::beginTransaction();
    try {
        $userData = $request->except($this->requestedDoctorDataDuringCreation());
        $userData['portal'] = 'Hospital';

        $user = $this->user_service->create($userData);
        $hospitalUser = $user->hospitalUser;
        $requestDoctorData = $request->only($this->requestedDoctorDataDuringCreation());

        $doctorPayload = array_merge($requestDoctorData, [
            'user_id' => $user->id,
        ]);
        $doctor = $this->doctor_service->save($doctorPayload);
         (new Helper)->sendLoginDetails($request, $doctor->user->id);
        DB::commit();
        return ApiHelper::validResponse("Doctor created successfully", DoctorResource::make($doctor));
    } catch (ValidationException $e) {
        DB::rollBack();
        $message = $e->getMessage() ?: $this->serverErrorMessage;
        return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
    } catch (Exception $e) {
        DB::rollBack();
        return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
    }
}



    public function show($doctor)
    {
        try {
            $doctor = $this->doctor_service::getById($doctor);
            return ApiHelper::validResponse("Doctor retrieved successfully", DoctorResource::make($doctor));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Doctor not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function update(Request $request, $doctor)
    {
        DB::beginTransaction();
        try {
            $doctor = $this->doctor_service->save($request->all(), $doctor);
            DB::commit();
            return ApiHelper::validResponse("Doctor updated successfully", DoctorResource::make($doctor));
        } catch (ValidationException $e) {
            DB::rollBack();
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Doctor not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function destroy($doctor)
    {
        try {
            $doctor = $this->doctor_service::getById($doctor);
            $doctor->delete();
            return ApiHelper::validResponse("Doctor deleted successfully");
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Doctor not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function getDashboardData()
    {
        try {
            $stats = $this->doctor_dashboard_service->getStats();
            return ApiHelper::validResponse("Doctor dashboard stats retrieved successfully", $stats);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
