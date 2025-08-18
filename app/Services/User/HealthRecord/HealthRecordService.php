<?php

namespace App\Services\User\HealthRecord;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\User\HealthRecord;
use App\Models\User\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class HealthRecordService
{
    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'user_id'        => ['required', 'exists:users,id'],
            'blood_pressure' => ['nullable', 'string'],
            'heart_rate'     => ['nullable', 'string'],
            'weight'         => ['nullable', 'numeric'],
            'height'         => ['nullable', 'numeric'],

            // nested validations
            'allergies'                 => ['array'],
            'allergies.*.name'          => ['required_with:allergies', 'string'],
            'allergies.*.severity'      => ['nullable', 'string'],

            'conditions'                => ['array'],
            'conditions.*.name'         => ['required_with:conditions', 'string'],
            'conditions.*.diagnosed_year' => ['nullable', 'digits:4'],

            'medications'               => ['array'],
            'medications.*.name'        => ['required_with:medications', 'string'],
            'medications.*.dosage'      => ['nullable', 'string'],
            'medications.*.purpose'     => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function save(array $data, ?int $id = null): HealthRecord
    {
        $validated = $this->validate($data);

        return DB::transaction(function () use ($validated, $id) {
            if ($id) {
                $record = HealthRecord::find($id);
                if (!$record) throw new ModelNotFoundException("Health record not found");
                $record->update($validated);
            } else {
                $record = HealthRecord::create($validated);
            }

            // 🔄 Sync Allergies
            if (isset($validated['allergies'])) {
                $record->allergies()->delete();
                foreach ($validated['allergies'] as $allergy) {
                    $record->allergies()->create($allergy);
                }
            }

            // 🔄 Sync Medical Conditions
            if (isset($validated['conditions'])) {
                $record->conditions()->delete();
                foreach ($validated['conditions'] as $condition) {
                    $record->conditions()->create($condition);
                }
            }

            // 🔄 Sync Medications
            if (isset($validated['medications'])) {
                $record->healthRecordMedications()->delete();
                foreach ($validated['medications'] as $medication) {
                    $record->healthRecordMedications()->create($medication);
                }
            }

            return $record->load('allergies', 'conditions', 'healthRecordMedications');
        });
    }

    public function list()
    {
        $userId = User::getAuthenticatedUser()->id;
        return HealthRecord::with(['allergies', 'conditions', 'healthRecordMedications'])
            ->where('user_id', $userId)
            ->first();
    }
}
