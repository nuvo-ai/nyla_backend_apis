<?php

namespace App\Http\Controllers\Api\Hospital\Prescription;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\PrescriptionResource;
use App\Services\Hospital\Prescription\PrescriptionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Exception;

class PrescriptionController extends Controller
{
    protected $prescription_service;

    public function __construct()
    {
        $this->prescription_service = new PrescriptionService;
    }

    public function index(Request $request)
    {
        try {
            $data = $this->prescription_service->list($request->all());
            return ApiHelper::validResponse("Prescriptions retrieved successfully", PrescriptionResource::collection($data));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to retrieve prescriptions", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $prescription = $this->prescription_service->save($request->all());
            DB::commit();
            return ApiHelper::validResponse("Prescription created successfully", PrescriptionResource::make($prescription));
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiHelper::inputErrorResponse($e->getMessage(), ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Failed to create prescription", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function show($id)
    {
        try {
            $prescription = $this->prescription_service->getById($id);
            return ApiHelper::validResponse("Prescription retrieved successfully", PrescriptionResource::make($prescription));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Prescription not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to retrieve prescription", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $prescription = $this->prescription_service->save($request->all(), $id);
            DB::commit();
            return ApiHelper::validResponse("Prescription updated successfully", PrescriptionResource::make($prescription));
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiHelper::inputErrorResponse($e->getMessage(), ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Prescription not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Failed to update prescription", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->prescription_service->delete($id);
            return ApiHelper::validResponse("Prescription deleted successfully");
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Prescription not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to delete prescription", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function sendToFrontdesk($prescription)
    {
        DB::beginTransaction();
        try {
            $prescription = $this->prescription_service->sendToFrontdesk($prescription);
            DB::commit();
            return ApiHelper::validResponse("Prescription sent to frontdesk successfully", PrescriptionResource::make($prescription));
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Prescription not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Failed to send prescription", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
