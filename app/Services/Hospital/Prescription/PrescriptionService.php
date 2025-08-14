<?php

namespace App\Services\Hospital\Prescription;

use App\Models\Hospital\Prescription;
use App\Models\User\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PrescriptionService
{
    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'patient_id' => ['required', 'exists:hospital_patients,id'],
            'date'       => ['required', 'date'],
            'status'     => ['required', 'string', 'in:pending,sent,approved,dispensed'],
            'notes'      => ['nullable', 'string'],

            'medications'              => ['required', 'array', 'min:1'],
            'medications.*.name'       => ['required', 'string'],
            'medications.*.dosage'     => ['nullable', 'string'],
            'medications.*.frequency'  => ['nullable', 'string'],
            'medications.*.duration'   => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function save(array $data, ?int $id = null): Prescription
    {
        $validated = $this->validate($data);

        return DB::transaction(function () use ($validated, $id) {
            if ($id) {
                $prescription = $this->getById($id);
                $prescription->update($validated);
                $prescription->medications()->delete(); // clear old meds
            } else {
                $validated['doctor_id'] = User::getAuthenticatedUser()->doctor->id;
                $validated['hospital_id'] = User::getAuthenticatedUser()->hospitalUser->hospital->id;
                $prescription = Prescription::create($validated);
            }

            foreach ($validated['medications'] as $med) {
                $prescription->medications()->create($med);
            }

            return $prescription->load(['patient', 'doctor', 'medications']);
        });
    }

    public function getById($key, $column = "id"): Prescription
    {
        $model = Prescription::with(['patient', 'doctor', 'medications'])
            ->where($column, $key)
            ->first();

        if (!$model) {
            throw new ModelNotFoundException("Prescription not found");
        }

        return $model;
    }

    public function list(array $filters = [])
    {
        $query = Prescription::with(['patient', 'doctor', 'medications']);

        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (!empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate(50);
    }

    public function delete($id)
    {
        $prescription = $this->getById($id);
        return $prescription->delete();
    }

    public function sendToFrontdesk($id)
    {
        $prescription = $this->getById($id);
        if ($prescription->status !== 'pending') {
            throw new \Exception("Prescription is not in a state that can be sent to frontdesk");
        }
        $prescription->status = 'sent';
        $prescription->save();
        return $prescription;
    }
}
