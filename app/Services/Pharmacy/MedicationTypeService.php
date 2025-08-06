<?php

namespace App\Services\Pharmacy;

use App\Models\Pharmacy\MedicationType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MedicationTypeService
{
    public function list(array $filters = [])
    {
        $query = MedicationType::with(['pharmacy', 'medications']);
        if (isset($filters['pharmacy_id'])) {
            $query->where('pharmacy_id', $filters['pharmacy_id']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        return $query->get();
    }

    public function show($id)
    {
        return MedicationType::with(['pharmacy', 'medications'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $medicationType = MedicationType::create($validator->validated());
        return $medicationType->load(['pharmacy', 'medications']);
    }

    public function update($id, array $data)
    {
        $medicationType = MedicationType::findOrFail($id);

        $validator = Validator::make($data, [
            'pharmacy_id' => 'sometimes|required|exists:pharmacies,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $medicationType->update($validator->validated());
        return $medicationType->refresh()->load(['pharmacy', 'medications']);
    }

    public function delete($id)
    {
        $medicationType = MedicationType::findOrFail($id);
        $medicationType->delete();
        return true;
    }
}
