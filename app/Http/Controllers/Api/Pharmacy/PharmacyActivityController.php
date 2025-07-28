<?php

namespace App\Http\Controllers\Api\Pharmacy;

use Exception;
use App\Helpers\ApiHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pharmacy\PharmacyActivity;

class PharmacyActivityController extends Controller
{
    public function index(Request $request)
    {
        try {
            $pharmacy_id = $request->get('pharmacy_id');
            $limit = $request->get('limit', 20);
            if (!$pharmacy_id) {
                return ApiHelper::inputErrorResponse('pharmacy_id is required', 422);
            }
            $activities = PharmacyActivity::where('pharmacy_id', $pharmacy_id)
                ->orderByDesc('created_at')
                ->with('user')
                ->limit($limit)
                ->get();
            return ApiHelper::validResponse('Pharmacy activities retrieved successfully', $activities);
        } catch (Exception $e) {
            return ApiHelper::problemResponse(ApiHelper::SERVER_ERROR_MESSAGE, 500, null, $e);
        }
    }
}
