<?php

namespace App\Http\Controllers\Api\Dashboard;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use App\Constants\General\ApiConstants;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    public $dashboard_service;

    public function __construct()
    {
        $this->dashboard_service = new DashboardService;
    }

    /**
     * Get dashboard overview statistics
     * GET /api/v1/dashboard/overview
     */
    public function overview(Request $request)
    {
        try {
            $data = $this->dashboard_service->getOverviewStats($request->all());
            return ApiHelper::validResponse("Dashboard overview retrieved successfully", $data);
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
     * Get recent activities
     * GET /api/v1/dashboard/activities
     */
    public function activities(Request $request)
    {
        try {
            $data = $this->dashboard_service->getRecentActivities($request->all());
            return ApiHelper::validResponse("Recent activities retrieved successfully", $data);
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
     * Get system health status
     * GET /api/v1/dashboard/health
     */
    public function health(Request $request)
    {
        try {
            $data = $this->dashboard_service->getSystemHealth();
            return ApiHelper::validResponse("System health retrieved successfully", $data);
        } catch (Exception $e) {
            report_error($e);
            return ApiHelper::problemResponse($e->getMessage(), ApiConstants::GENERAL_ERR_CODE, null, $e);
        }
    }
}
