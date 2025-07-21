<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy\PharmacyActivity;
use Illuminate\Http\Request;

class PharmacyActivityController extends Controller
{
    public function index(Request $request)
    {
        $pharmacy_id = $request->get('pharmacy_id');
        $limit = $request->get('limit', 20);
        if (!$pharmacy_id) {
            return response()->json(['error' => 'pharmacy_id is required'], 422);
        }
        $activities = PharmacyActivity::where('pharmacy_id', $pharmacy_id)
            ->orderByDesc('created_at')
            ->with('user')
            ->limit($limit)
            ->get();
        return response()->json($activities);
    }
}
