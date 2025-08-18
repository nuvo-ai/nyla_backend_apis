<?php

namespace App\Http\Controllers\Api\User\HealthRecord;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\MedicationResource;
use App\Services\User\HealthRecord\MedicationReminderService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;

class MedicationReminderController extends Controller
{
    protected $medication_service;

    public function __construct()
    {
        $this->medication_service = new MedicationReminderService;
    }

    public function index()
    {
        try {
            $records = $this->medication_service->list();
            return ApiHelper::validResponse("Medications retrieved successfully", MedicationResource::collection($records));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $record = $this->medication_service->save($request->all());
            DB::commit();
            return ApiHelper::validResponse("Medication created successfully", MedicationResource::make($record));
        } catch (ValidationException $e) {
            DB::rollBack();
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function show($id)
    {
        try {
            $record = $this->medication_service->list($id);
            return ApiHelper::validResponse("Medication retrieved successfully", MedicationResource::make($record));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Medication not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $record = $this->medication_service->save($request->all(), $id);
            DB::commit();
            return ApiHelper::validResponse("Medication updated successfully", MedicationResource::make($record));
        } catch (ValidationException $e) {
            DB::rollBack();
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Medication not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function destroy($id)
    {
        try {
            $record = $this->medication_service->list($id);
            if (!$record) {
                throw new ModelNotFoundException("Medication not found");
            }
            $record->delete();
            return ApiHelper::validResponse("Medication deleted successfully");
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Medication not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
