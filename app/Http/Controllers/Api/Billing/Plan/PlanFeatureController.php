<?php

namespace App\Http\Controllers\Api\Billing\Plan;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\Plan\PlanFeatureResource;
use App\Models\General\Plan;
use App\Services\Billing\Plan\PlanFeatureService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;

class PlanFeatureController extends Controller
{
    protected $plan_feature_service;

    public function __construct()
    {
        $this->plan_feature_service = new PlanFeatureService;
    }

    // List all features for a plan
    public function list($plan_id)
    {
        try {
            $features = $this->plan_feature_service->list($plan_id);
            return ApiHelper::validResponse(
                "Plan features retrieved successfully",
                PlanFeatureResource::collection($features)
            );
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to retrieve plan features", 500, null, $e);
        }
    }

    // Get details of a single feature
    public function getFeature($plan_id, $feature_id)
    {
        try {
            $feature = $this->plan_feature_service->getFeature($feature_id);
            return ApiHelper::validResponse(
                "Plan feature retrieved successfully",
                new PlanFeatureResource($feature)
            );
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to retrieve plan feature", 500, null, $e);
        }
    }

    public function create(Request $request, $plan_id)
    {
        try {
            $plan = Plan::findOrFail($plan_id);

            $features = $request->input('features', []);
            $created = $this->plan_feature_service->createMany($features, $plan);

            return ApiHelper::validResponse(
                "Plan features created successfully",
                PlanFeatureResource::collection($created)
            );
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse(
                "Validation failed",
                ApiConstants::VALIDATION_ERR_CODE,
                null,
                $e
            );
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to create plan features", 500, null, $e);
        }
    }

    public function update(Request $request, $plan_id)
    {
        try {
            $plan = Plan::findOrFail($plan_id);

            $features = $request->input('features', []);
            $updated = $this->plan_feature_service->updateMany($features, $plan);

            return ApiHelper::validResponse(
                "Plan features updated successfully",
                PlanFeatureResource::collection($updated)
            );
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse(
                "Validation failed",
                ApiConstants::VALIDATION_ERR_CODE,
                null,
                $e
            );
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to update plan features", 500, null, $e);
        }
    }


    // Delete a feature
    public function delete($plan_id, $feature_id)
    {
        try {
            $this->plan_feature_service->delete($feature_id);
            return ApiHelper::validResponse("Plan feature deleted successfully", null);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to delete plan feature", 500, null, $e);
        }
    }
}
