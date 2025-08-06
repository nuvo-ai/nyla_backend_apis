<?php

namespace App\Services\Pharmacy;

use App\Models\Pharmacy\MedicationDosage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MedicationDosageService
{
    public function list(array $filters = [])
    {
        $query = MedicationDosage::with('medication');

        if (isset($filters['medication_id'])) {
            $query->where('medication_id', $filters['medication_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['form'])) {
            $query->where('form', $filters['form']);
        }

        return $query->get();
    }

    public function show($id)
    {
        return MedicationDosage::with('medication')->findOrFail($id);
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'medication_id' => 'required|exists:medications,id',
            'strength' => 'required|string|max:255',
            'form' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'quantity' => 'nullable|numeric|min:0',
            'frequency' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return MedicationDosage::create($validator->validated());
    }

    public function update($id, array $data)
    {
        $medicationDosage = MedicationDosage::findOrFail($id);

        $validator = Validator::make($data, [
            'medication_id' => 'sometimes|required|exists:medications,id',
            'strength' => 'sometimes|required|string|max:255',
            'form' => 'sometimes|required|string|max:255',
            'unit' => 'sometimes|required|string|max:50',
            'quantity' => 'nullable|numeric|min:0',
            'frequency' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $medicationDosage->update($validator->validated());
        return $medicationDosage->refresh();
    }

    public function delete($id)
    {
        $medicationDosage = MedicationDosage::findOrFail($id);
        $medicationDosage->delete();
        return true;
    }

    /**
     * Get dosages for a specific medication
     */
    public function getDosagesByMedication($medicationId)
    {
        return MedicationDosage::where('medication_id', $medicationId)
            ->where('is_active', true)
            ->with('medication')
            ->get();
    }

    /**
     * Get available forms for a medication
     */
    public function getAvailableForms($medicationId)
    {
        return MedicationDosage::where('medication_id', $medicationId)
            ->where('is_active', true)
            ->distinct()
            ->pluck('form');
    }
}
