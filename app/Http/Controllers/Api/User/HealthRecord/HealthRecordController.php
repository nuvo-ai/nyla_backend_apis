<?php

namespace App\Http\Controllers\Api\User\HealthRecord;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\HealthRecordResource;
use App\Services\User\HealthRecord\HealthRecordService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\DB;

class HealthRecordController extends Controller
{
    protected $health_record_service;

    public function __construct()
    {
        $this->health_record_service = new HealthRecordService;
    }

    public function index(Request $request)
    {
        try {
            $record = $this->health_record_service->list();
            return ApiHelper::validResponse("Health record retrieved successfully", HealthRecordResource::make($record));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $record = $this->health_record_service->save($request->all());
            DB::commit();
            return ApiHelper::validResponse("Health record created successfully", HealthRecordResource::make($record));
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
            $record = $this->health_record_service->list($id);
            return ApiHelper::validResponse("Health record retrieved successfully", HealthRecordResource::make($record));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Health record not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $record = $this->health_record_service->save($request->all(), $id);
            DB::commit();
            return ApiHelper::validResponse("Health record updated successfully", HealthRecordResource::make($record));
        } catch (ValidationException $e) {
            DB::rollBack();
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Health record not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function destroy($id)
    {
        try {
            $record = $this->health_record_service->list($id);
            if (!$record) {
                throw new ModelNotFoundException("Health record not found");
            }
            $record->delete();
            return ApiHelper::validResponse("Health record deleted successfully");
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Health record not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
