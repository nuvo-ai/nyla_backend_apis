<?php

namespace App\Http\Controllers\Api\Finance\Plan;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\PlanResource;
use App\Services\Billing\Plan\PlanService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PlanController extends Controller
{
    protected $plan_service;
    public function __construct()
    {
        $this->plan_service = new PlanService;
    }

    public function getPlan($plan_code)
    {
        try {
            $plan = $this->plan_service->getPlan($plan_code);
            return ApiHelper::validResponse("Plan details retrieved successfully", PlanResource::make($plan)::make($plan));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to retrieve plan details", 500, null, $e);
        }
    }

    public function list()
    {
        try {
            $plans = $this->plan_service->list();
            return ApiHelper::validResponse("Plans retrieved successfully", PlanResource::collection($plans));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to retrieve plans", 500, null, $e);
        }
    }

    public function create(Request $request)
    {
        try {
            $plan = $this->plan_service->create($request->all());
            return ApiHelper::validResponse("Plan created successfully", $plan);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to create plan", 500, null, $e);
        }
    }

    public function update(Request $request, $plan_code)
    {
        try {
            $plan = $this->plan_service->update($request->all(), $plan_code);
            return ApiHelper::validResponse("Plan updated successfully", PlanResource::make($plan));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to update plan", 500, null, $e);
        }
    }

    public function delete($plan_code)
    {
        try {
          $plan = $this->plan_service->delete($plan_code);
            return ApiHelper::validResponse("Plan deleted successfully", PlanResource::make($plan));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to delete plan", 500, null, $e);
        }
    }
}
