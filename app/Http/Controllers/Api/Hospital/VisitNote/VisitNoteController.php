<?php

namespace App\Http\Controllers\Api\Hospital\VisitNote;

use App\Http\Controllers\Controller;
use App\Helpers\ApiHelper;
use App\Constants\General\ApiConstants;
use App\Services\Hospital\VisitNote\VisitNoteService;
use App\Http\Resources\Hospital\VisitNoteResource;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Exception;

class VisitNoteController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new VisitNoteService;
    }

    public function index(Request $request)
    {
        try {
            $visitNotes = $this->service->list($request->all());
            return ApiHelper::validResponse("Visit notes retrieved successfully", VisitNoteResource::collection($visitNotes));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to retrieve visit notes", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $visitNote = $this->service->save($request->all());
            DB::commit();
            return ApiHelper::validResponse("Visit note created successfully", VisitNoteResource::make($visitNote));
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiHelper::inputErrorResponse($e->getMessage(), ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Failed to create visit note", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function show($id)
    {
        try {
            $visitNote = $this->service->getById($id);
            return ApiHelper::validResponse("Visit note retrieved successfully", VisitNoteResource::make($visitNote));
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Visit note not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to retrieve visit note", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $visitNote = $this->service->save($request->all(), $id);
            DB::commit();
            return ApiHelper::validResponse("Visit note updated successfully", VisitNoteResource::make($visitNote));
        } catch (ValidationException $e) {
            DB::rollBack();
            return ApiHelper::inputErrorResponse($e->getMessage(), ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Visit note not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            DB::rollBack();
            return ApiHelper::problemResponse("Failed to update visit note", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->delete($id);
            return ApiHelper::validResponse("Visit note deleted successfully");
        } catch (ModelNotFoundException $e) {
            return ApiHelper::problemResponse("Visit note not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Failed to delete visit note", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
