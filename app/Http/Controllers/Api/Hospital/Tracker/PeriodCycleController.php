<?php

namespace App\Http\Controllers\Api\Hospital\Tracker;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Hospital\Tracker\PeriodCycleResource;
use App\Services\Hospital\Tracker\PeriodCycleService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PeriodCycleController extends Controller
{
    public $periodCycleService;

    public function __construct()
    {
        $this->periodCycleService = new PeriodCycleService();
    }

    public function store(Request $request)
    {
        try {
            $cycle = $this->periodCycleService->store($request->all());
            return ApiHelper::validResponse("Period cycle settings saved", new PeriodCycleResource($cycle));
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse("Validation failed", ApiConstants::VALIDATION_ERR_CODE, null, $e);
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Something went wrong", ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    public function show()
    {
        try {
            $cycle = $this->periodCycleService->show();
            return ApiHelper::validResponse("Period cycle settings fetched", new PeriodCycleResource($cycle));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Cycle not found", ApiConstants::NOT_FOUND_ERR_CODE, null, $e);
        }
    }
}
