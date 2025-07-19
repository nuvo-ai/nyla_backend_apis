<?php

namespace App\Http\Controllers\Api\Hospital\Analytic;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Stats\HospitalAnalyticsResource;
use App\Services\Hospital\Analytic\HospitalAnalyticsService;
use Exception;
use Illuminate\Http\Request;

class AnalyticController extends Controller
{
    protected $analytic_service;
    public function __construct()
    {
        $this->analytic_service = new HospitalAnalyticsService();
    }

    public function getAnalytics(Request $request)
    {
       try {
            $data = $this->analytic_service->getAnalytics();
            return ApiHelper::validResponse("Hospital Analytics Data retrieved successfully", HospitalAnalyticsResource::make($data));
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
