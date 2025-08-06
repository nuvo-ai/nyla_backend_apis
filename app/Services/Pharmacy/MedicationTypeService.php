<?php

namespace App\Services\Pharmacy;

use App\Models\Pharmacy\MedicationType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MedicationTypeService
{
    public function list(array $filters = [])
    {
        $query = MedicationType::query();
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        return $query->get();
    }

    public function show($id)
    {
        return MedicationType::findOrFail($id);
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return MedicationType::create($validator->validated());
    }

    public function update($id, array $data)
    {
        $medicationType = MedicationType::findOrFail($id);

        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $medicationType->update($validator->validated());
        return $medicationType->refresh();
    }

    public function delete($id)
    {
        $medicationType = MedicationType::findOrFail($id);
        $medicationType->delete();
        return true;
    }
}
