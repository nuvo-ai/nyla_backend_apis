<?php

namespace App\Services\Hospital\Patient;

use App\Constants\General\AppConstants;
use App\Constants\General\StatusConstants;
use App\Models\Hospital\HospitalPatient;
use App\Models\Hospital\HospitalHospitalPatient;
use App\Models\Hospital\HospitalUser;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PatientService
{

    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'hospital_id'         => ['required', 'exists:hospitals,id'],
            'user_id'             => ['nullable', 'exists:users,id'],
            'doctor_id'           => [
                'nullable',
                'exists:hospital_users,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $doctor = HospitalUser::find($value);
                        if (!$doctor || $doctor->role !== AppConstants::HOSPITAL_DOCTOR) {
                            $fail('The selected doctor does not have the correct role.');
                        }
                    }
                }
            ],
            'age'                 => ['nullable', 'integer', 'min:0'],
            'gender'              => ['required', 'in:male,female,other'],
            'temperature'         => ['nullable', 'string', 'max:50'],
            'weight'              => ['nullable', 'string', 'max:50'],
            'height'              => ['nullable', 'string', 'max:50'],
            'blood_pressure'      => ['nullable', 'string', 'max:50'],
            'heart_rate'          => ['nullable', 'string', 'max:50'],
            'respiratory_rate'    => ['nullable', 'string', 'max:50'],
            'oxygen_saturation'   => ['nullable', 'string', 'max:50'],
            'complaints'          => ['nullable', 'array'],
            'last_visit'          => ['nullable', 'date'],
            'status'              => ['nullable'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public static function getById($key, $column = "id"): HospitalPatient
    {
        $model = HospitalPatient::where($column, $key)->first();
        if (empty($model)) {
            throw new ModelNotFoundException("Patient not found");
        }
        return $model;
    }

    public function save(array $data, ?int $id = null): HospitalPatient
    {
        return DB::transaction(function () use ($data, $id) {
            $validated = $this->validate($data);
            $hospital_patient = HospitalPatient::where('user_id', $validated['user_id']);
            if ($id) {
                $hospital_patient->where('id', '!=', $id);
            }
            if ($hospital_patient->exists()) {
                throw ValidationException::withMessages([
                    'duplicate' => ['This patient already exists in our database.'],
                ]);
            }
            $payload = [
                'hospital_id'        => $validated['hospital_id'],
                'user_id'            => $validated['user_id'] ?? null,
                'doctor_id'          => $validated['doctor_id'] ?? null,
                'age'                => $validated['age'] ?? null,
                'gender'             => $validated['gender'],
                'temperature'        => $validated['temperature'] ?? null,
                'weight'             => $validated['weight'] ?? null,
                'height'             => $validated['height'] ?? null,
                'blood_pressure'     => $validated['blood_pressure'] ?? null,
                'heart_rate'         => $validated['heart_rate'] ?? null,
                'respiratory_rate'   => $validated['respiratory_rate'] ?? null,
                'oxygen_saturation'  => $validated['oxygen_saturation'] ?? null,
                'complaints'         => $validated['complaints'] ?? [],
                'status'             => $validated['status'] ?? StatusConstants::ACTIVE,
            ];

            if ($id) {
                $patient = $this->getById($id);
                $patient->update($payload);
            } else {
                $patient = HospitalPatient::create($payload);
            }

            return $patient->load('hospital', 'doctor', 'user');
        });
    }

    public function listPatients(array $filters = []): Collection
    {
        $query = HospitalPatient::with(['hospital', 'doctor']);

        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);
            $query->whereRaw('LOWER(status) = ?', [$status]);
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['hospital_id'])) {
            $query->where('hospital_id', $filters['hospital_id']);
        }

        return $query->get();
    }
}
