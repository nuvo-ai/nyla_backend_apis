<?php

namespace App\Http\Controllers\Api\Analytics;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Analytics\AnalyticsService;
use App\Constants\General\ApiConstants;
use Illuminate\Validation\ValidationException;

class AnalyticsController extends Controller
{
    public $analytics_service;

    public function __construct()
    {
        $this->analytics_service = new AnalyticsService;
    }

    /**
     * Get user analytics
     * GET /api/v1/analytics/users
     */
    public function users(Request $request)
    {
        try {
            $data = $this->analytics_service->getUserAnalytics($request->all());
            return ApiHelper::validResponse("User analytics retrieved successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::GENERAL_ERR_CODE, null, $e);
        }
    }

    /**
     * Get hospital analytics
     * GET /api/v1/analytics/hospitals
     */
    public function hospitals(Request $request)
    {
        try {
            $data = $this->analytics_service->getHospitalAnalytics($request->all());
            return ApiHelper::validResponse("Hospital analytics retrieved successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::GENERAL_ERR_CODE, null, $e);
        }
    }

    /**
     * Get pharmacy analytics
     * GET /api/v1/analytics/pharmacies
     */
    public function pharmacies(Request $request)
    {
        try {
            $data = $this->analytics_service->getPharmacyAnalytics($request->all());
            return ApiHelper::validResponse("Pharmacy analytics retrieved successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::GENERAL_ERR_CODE, null, $e);
        }
    }

    /**
     * Get revenue analytics
     * GET /api/v1/analytics/revenue
     */
    public function revenue(Request $request)
    {
        try {
            $data = $this->analytics_service->getRevenueAnalytics($request->all());
            return ApiHelper::validResponse("Revenue analytics retrieved successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::GENERAL_ERR_CODE, null, $e);
        }
    }

    /**
     * Export analytics data
     * POST /api/v1/analytics/export
     */
    public function export(Request $request)
    {
        try {
            $data = $this->analytics_service->exportAnalytics($request->all());
            return ApiHelper::validResponse("Analytics export initiated successfully", $data);
        } catch (ValidationException $e) {
            report_error($e);
            $message = $e->validator->errors()->first();
            return ApiHelper::inputErrorResponse($message, ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::GENERAL_ERR_CODE, null, $e);
        }
    }
}
