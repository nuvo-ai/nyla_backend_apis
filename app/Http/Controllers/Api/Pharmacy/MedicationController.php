<?php

namespace App\Http\Controllers\Api\Pharmacy;

use App\Http\Controllers\Controller;
use App\Services\Pharmacy\MedicationService;
use Illuminate\Http\Request;

class MedicationController extends Controller
{
    protected $medicationService;

    public function __construct(MedicationService $medicationService)
    {
        $this->medicationService = $medicationService;
    }

    public function index(Request $request)
    {
        $medications = $this->medicationService->list($request->all());
        return response()->json($medications);
    }

    public function show($id)
    {
        $medication = $this->medicationService->show($id);
        return response()->json($medication);
    }

    public function store(Request $request)
    {
        $medication = $this->medicationService->create($request->all());
        return response()->json($medication, 201);
    }

    public function update(Request $request, $id)
    {
        $medication = $this->medicationService->update($id, $request->all());
        return response()->json($medication);
    }

    public function destroy($id)
    {
        $this->medicationService->delete($id);
        return response()->json(['message' => 'Medication deleted successfully']);
    }
}
