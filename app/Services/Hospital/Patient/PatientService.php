<?php

namespace App\Services\Hospital\Patient;

use App\Constants\General\AppConstants;
use App\Constants\General\StatusConstants;
use App\Constants\User\UserConstants;
use App\Http\Resources\Hospital\DoctorResource;
use App\Models\Hospital\Doctor;
use App\Models\Hospital\HospitalPatient;
use App\Models\Hospital\HospitalUser;
use App\Models\User\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PatientService
{
    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'doctor_id'             => [
                'nullable',
                'exists:doctors,id',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $doctor = Doctor::find($value);
                        if (!$doctor) {
                            $fail('The selected doctor does not exist.');
                        }
                    }
                }
            ],
            'chief_complaints'      => ['nullable', 'string'],
            'temperature'           => ['nullable', 'string', 'max:50'],
            'weight'                => ['nullable', 'string', 'max:50'],
            'height'                => ['nullable', 'string', 'max:50'],
            'blood_pressure'        => ['nullable', 'string', 'max:50'],
            'heart_rate'            => ['nullable', 'string', 'max:50'],
            'respiratory_rate'      => ['nullable', 'string', 'max:50'],
            'oxygen_saturation'     => ['nullable', 'string', 'max:50'],
            'last_visit'            => ['nullable', 'date'],
            'emergency_contact_name'   => ['nullable', 'string'],
            'emergency_contact_phone'  => ['nullable', 'string'],
            'current_symptoms'         => ['nullable', 'array'],
            'pain_level'               => ['nullable', 'integer', 'between:0,10'],
            'know_allergies'           => ['nullable', 'array'],
            'visit_priority'           => ['nullable'],
            'medical_history'          => ['nullable', 'string'],
            'current_medications'      => ['nullable', 'array'],
            'insurance_info'           => ['nullable', 'string'],
            'visit_type'               => ['nullable', 'string'],
            'referral_source'          => ['nullable', 'string'],
            'status'                   => ['nullable'],
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
            $user = User::getAuthenticatedUser();
            $existingUserId = $id ? HospitalPatient::find($id)->user_id : null;

            if (!empty($data['user_id']) && $data['user_id'] != $existingUserId) {
                $hospital_patient = HospitalPatient::where('hospital_id', $user?->hospitalUser?->_user_id)
                    ->where('user_id', $data['user_id'])
                    ->where('id', '<>', (int)$id);

                if ($hospital_patient->exists()) {
                    throw ValidationException::withMessages([
                        'duplicate' => ['This patient already exists in our database.'],
                    ]);
                }
            }

            $payload = [
                'hospital_id'             => $user?->hospitalUser?->_user_id,
                'user_id'                 => $data['user_id'] ?? null,
                'doctor_id'               => $validated['doctor_id'] ?? null,
                'chief_complaints'        => $validated['chief_complaints'] ?? null,
                'temperature'             => $validated['temperature'] ?? null,
                'weight'                  => $validated['weight'] ?? null,
                'height'                  => $validated['height'] ?? null,
                'blood_pressure'          => $validated['blood_pressure'] ?? null,
                'heart_rate'              => $validated['heart_rate'] ?? null,
                'respiratory_rate'        => $validated['respiratory_rate'] ?? null,
                'oxygen_saturation'       => $validated['oxygen_saturation'] ?? null,
                'last_visit'              => $validated['last_visit'] ?? null,
                'emergency_contact_name'  => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
                'current_symptoms'        => $validated['current_symptoms'] ?? [],
                'pain_level'              => $validated['pain_level'] ?? null,
                'know_allergies'          => $validated['know_allergies'] ?? [],
                'visit_priority'          => $validated['visit_priority'] ?? 'normal',
                'medical_history'         => $validated['medical_history'] ?? null,
                'current_medications'     => $validated['current_medications'] ?? [],
                'insurance_info'          => $validated['insurance_info'] ?? null,
                'visit_type'              => $validated['visit_type'] ?? null,
                'referral_source'         => $validated['referral_source'] ?? null,
                'status'                  => $validated['status'] ?? StatusConstants::ACTIVE,
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
        $query = HospitalPatient::with(['user', 'hospital', 'doctor'])
            ->where('user_id', User::getAuthenticatedUser()?->hospitalUser?->_user_id);

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

    public function discharge(Request $request, $patient_id)
    {
        $status = $request->status;
        if ($status !== StatusConstants::DISCHARGE) {
            throw ValidationException::withMessages([
                'status' => ['bad method provided.']
            ]);
        }
        $patient = HospitalPatient::findOrFail($patient_id);
        if ($patient->status === StatusConstants::DISCHARGE) {
            throw ValidationException::withMessages([
                'status' => ['Patient has already been discharged.']
            ]);
        }
        $patient->status = $status;
        $patient->save();
        return [
            'id' => $patient->id,
            'status' => $patient->status,
        ];
    }


    public function assign(Request $request, $patient)
    {
        return DB::transaction(function () use ($request, $patient) {
            $patient = $this->getById($patient);
            $patient->doctor_id = $request->input('doctor_id');
            $patient->save();
            $doctor = $patient->doctor()->with(['user', 'hospital'])->first();
            return [
                'id' => $patient->id,
                'doctor' => DoctorResource::make($doctor),
            ];
        });
    }

    public function stat()
    {
        return [
            'total_patients' => HospitalPatient::where('user_id', User::getAuthenticatedUser()?->hospitalUser?->_user_id)->count(),
            'active_patients' => HospitalPatient::where('user_id', User::getAuthenticatedUser()?->hospitalUser?->_user_id)->where('status', StatusConstants::ACTIVE)->count(),
            'admitted_patients' => HospitalPatient::where('user_id', User::getAuthenticatedUser()?->hospitalUser?->_user_id)->where('status', StatusConstants::ADMITTED)->count(),
            'discharged_patients' => HospitalPatient::where('user_id', User::getAuthenticatedUser()?->hospitalUser?->_user_id)->where('status', StatusConstants::DISCHARGE)->count()
        ];
    }
}
