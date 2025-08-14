<?php

namespace App\Services\Hospital\VisitNote;

use App\Models\Hospital\VisitNote;
use App\Models\User\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VisitNoteService
{
    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'patient_id'  => ['required', 'exists:hospital_patients,id'],
            'diagnosis_and_assessment' => ['nullable', 'string'],
            'treatment_plan_and_recommendation' => ['nullable', 'string'],
            'visit_date' => ['required', 'date'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function save(array $data, ?int $id = null): VisitNote
    {
        $validated = $this->validate($data);
        return DB::transaction(function () use ($validated, $id) {
            // Prevent duplicate note for same patient, doctor, hospital, and date
            if (!$id) {
               $user = User::getAuthenticatedUser();
                if (!$user->doctor) {
                    throw new \Exception("Authenticated user is not associated with any doctor.");
                }
                $existing = VisitNote::where('hospital_id', $user->hospitalUser->hospital->id)
                    ->where('doctor_id', $user->doctor->id)
                    ->where('patient_id', $validated['patient_id'])
                    ->where('visit_date', $validated['visit_date'])
                    ->first();

                if ($existing) {
                    throw new \Exception("A visit note for this patient by this doctor on this date already exists.");
                }
            }

            if ($id) {
                $visitNote = $this->getById($id);
                $visitNote->update($validated);
            } else {
                $validated['doctor_id'] = User::getAuthenticatedUser()->doctor->id;
                $validated['hospital_id'] = User::getAuthenticatedUser()->hospitalUser->hospital->id;
                $visitNote = VisitNote::create($validated);
            }

            return $visitNote;
        });
    }

    public function getById($id, $column = 'id'): VisitNote
    {
        $visitNote = VisitNote::where($column, $id)->first();
        if (!$visitNote) {
            throw new ModelNotFoundException("Visit note not found");
        }

        return $visitNote;
    }

    public function list(array $filters = [])
    {
        $query = VisitNote::with(['patient', 'doctor']);

        if (!empty($filters['hospital_id'])) {
            $query->where('hospital_id', $filters['hospital_id']);
        }

        if (!empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (!empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (!empty($filters['visit_date'])) {
            $query->where('visit_date', $filters['visit_date']);
        }

        return $query->latest()->get();
    }

    public function delete($id)
    {
        $visitNote = $this->getById($id);
        return $visitNote->delete();
    }
}
