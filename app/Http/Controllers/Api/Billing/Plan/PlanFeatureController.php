<?php

namespace App\Http\Controllers\Api\Billing\Plan;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\PlanFeatureResource;
use App\Models\General\Plan;
use App\Services\Billing\Plan\PlanFeatureService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PlanFeatureController extends Controller
{
    protected $plan_feature_service;

    public function __construct()
    {
        $this->plan_feature_service = new PlanFeatureService;
    }

    public function list($plan_id)
    {
        try {
            $features = $this->plan_feature_service->list($plan_id);
            return ApiHelper::validResponse("Plan features retrieved successfully", PlanFeatureResource::collection($features));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to retrieve plan features", 500, null, $e);
        }
    }

    public function getFeature($id)
    {
        try {
            $feature = $this->plan_feature_service->getFeature($id);
            return ApiHelper::validResponse("Plan feature retrieved successfully", PlanFeatureResource::make($feature));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to retrieve plan feature", 500, null, $e);
        }
    }

    public function create(Request $request)
    {
        try {
            $feature = $this->plan_feature_service->createMany($request->all());
            return ApiHelper::validResponse("Plan feature created successfully", new PlanFeatureResource($feature));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to create plan feature", 500, null, $e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $feature = $this->plan_feature_service->updateMany($request->all(), $id);
            return ApiHelper::validResponse("Plan feature updated successfully", new PlanFeatureResource($feature));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to update plan feature", 500, null, $e);
        }
    }

    public function delete($id)
    {
        try {
            $feature = $this->plan_feature_service->delete($id);
            return ApiHelper::validResponse("Plan feature deleted successfully", new PlanFeatureResource($feature));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to delete plan feature", 500, null, $e);
        }
    }
}
