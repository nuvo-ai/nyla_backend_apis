<?php

namespace App\Services\Pharmacy;

use App\Models\Pharmacy\Medication;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\Pharmacy\PharmacyActivityService;

class MedicationService
{
    public function list(array $filters = [])
    {
        $query = Medication::query();
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
        return Medication::findOrFail($id);
    }

    public function create(array $data)
    {
        $validator = Validator::make($data, [
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'stock' => 'required|integer|min:0',
            'price' => 'required|numeric',
            'is_active' => 'boolean',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $medication = Medication::create($validator->validated());
        // Log activity: Medication created
        $userId = $data['created_by'] ?? auth()->id();
        PharmacyActivityService::log(
            $medication->pharmacy_id,
            $userId,
            'Medication created',
            ['medication_id' => $medication->id, 'name' => $medication->name]
        );
        return $medication;
    }

    public function update($id, array $data)
    {
        $medication = Medication::findOrFail($id);
        $medication->update($data);
        // Log activity: Medication updated
        $userId = $data['updated_by'] ?? auth()->id();
        PharmacyActivityService::log(
            $medication->pharmacy_id,
            $userId,
            'Medication updated',
            ['medication_id' => $medication->id, 'name' => $medication->name]
        );
        return $medication->refresh();
    }

    public function delete($id)
    {
        $medication = Medication::findOrFail($id);
        $pharmacy_id = $medication->pharmacy_id;
        $name = $medication->name;
        $medication->delete();
        // Log activity: Medication deleted
        $userId = auth()->id();
        PharmacyActivityService::log(
            $pharmacy_id,
            $userId,
            'Medication deleted',
            ['medication_id' => $id, 'name' => $name]
        );
        return true;
    }
}
