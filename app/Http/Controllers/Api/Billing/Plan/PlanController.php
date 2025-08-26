<?php

namespace App\Http\Controllers\Api\Billing\Plan;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\Plan\PlanResource;
use App\Models\User\User;
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
            $data = $request->all();
            if ($this->plan_service->isSinglePlan($data)) {
                $plan = $this->plan_service->create($data);
                return ApiHelper::validResponse("Plan created successfully", $plan);
            }
            $plans = [];

            foreach ($request->all() as $planData) {
                $plans[] = $this->plan_service->create($planData);
            }
            return ApiHelper::validResponse("Plan created successfully", $plans);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse($this->validationErrorMessage, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to create plan", 500, null, $e);
        }
    }

    public function update(Request $request, $plan_code = null)
    {
        try {
            $data = $request->all();
            if (is_array($data) && array_is_list($data)) {
                $updatedPlans = [];
                foreach ($data as $planData) {
                    if (!isset($planData['plan_code'])) {
                        throw new ValidationException("Missing plan_code in bulk update item.");
                    }
                    $updatedPlans[] = $this->plan_service->update($planData, $planData['plan_code']);
                }
                return ApiHelper::validResponse("Plans updated successfully", PlanResource::collection(collect($updatedPlans)));
            }
            if (!$plan_code && isset($data['plan_code'])) {
                $plan_code = $data['plan_code'];
            }

            if (!$plan_code) {
                throw new ValidationException("plan_code is required for update.");
            }

            $plan = $this->plan_service->update($data, $plan_code);
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

    public function hospitalPlan()
    {
        try {
            $plans = $this->plan_service->hospitalPlan();
            return ApiHelper::validResponse("Hospital plans retrieved successfully", PlanResource::make($plans));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to retrieve hospital plans", 500, null, $e);
        }
    }

    public function pharmacyPlans()
    {
        try {
            $plans = $this->plan_service->phamacyPlans();
            return ApiHelper::validResponse("Pharmacy plans retrieved successfully", PlanResource::make($plans));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to retrieve pharmacy plans", 500, null, $e);
        }
    }
}
