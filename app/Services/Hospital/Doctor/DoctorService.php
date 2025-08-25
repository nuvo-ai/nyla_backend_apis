<?php

namespace App\Services\Hospital\Doctor;

use App\Constants\General\StatusConstants;
use App\Constants\User\UserConstants;
use App\Models\Hospital\Doctor;
use App\Models\Hospital\HospitalContact;
use App\Models\Hospital\HospitalPatient;
use App\Models\Portal;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DoctorService
{
    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'user_id' => ['required', 'exists:users,id'],
            // 'hospital_id' => ['required', 'exists:hospitals,id'],
            // 'hospital_user_id' => ['required', 'exists:hospital_users,id'],
            'medical_number' => ['required', 'string', 'unique:doctors,medical_number'],
            'departments' => ['nullable', 'array', 'min:1'],
            'departments.*' => ['string'],
            'medical_specialties' => ['nullable', 'array', 'min:1'],
            'medical_specialties.*' => ['string'],
            'status' => ['nullable', 'string'],
            'next_available' => ['nullable', 'date'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function save(array $data, ?int $id = null): Doctor
    {
        $validated = $this->validate($data);
        $user = User::find($validated['user_id']);

        if (!$user->hospitalUser) {
            throw new \Exception("Authenticated user is not associated with any hospital user.");
        }

        if (!$user->hospitalUser->hospital) {
            throw new \Exception("Authenticated user hospital user is not linked to a hospital.");
        }

        $payload = [
            'user_id' => $validated['user_id'],
            'hospital_id' => $user->hospitalUser->hospital->id,
            'hospital_user_id' => $user->hospitalUser->id,
            'medical_number' => $validated['medical_number'],
            'departments' => $validated['departments'] ?? null,
            'status' => $validated['status'] ?? StatusConstants::AVAILABLE,
            'next_available' => $validated['next_available'] ?? null,
        ];

        if (isset($data['medical_specialties'])) {
            $payload['medical_specialties'] = $data['medical_specialties'];
        }

        if ($id) {
            $doctor = $this->getById($id);
            $doctor->update($payload);
        } else {
            $doctor = Doctor::create($payload);
        }

        if (!empty($data['user_id'])) {
            $user = User::find($data['user_id']);
            if ($user) {
                $hospitalContact = HospitalContact::where('hospital_id', $user->hospitalUser?->hospital?->id)->first();
                if ($hospitalContact) {
                    $user->hospital_contact_id = $hospitalContact->id;
                }
                $user->role = $data['role'] ?? UserConstants::USER;
                $user->save();
            }
            if ($user->hospitalUser) {
                $user->hospitalUser->role = $data['role'] ?? UserConstants::DOCTOR;
                $user->hospitalUser->save();
            }
        }

        return $doctor->load(['user', 'hospitalUser', 'hospital']);
    }


    public static function getById($key, $column = "id"): Doctor
    {
        $model = Doctor::where($column, $key)->first();
        if (!$model) {
            throw new ModelNotFoundException("Doctor not found");
        }

        return $model;
    }

    public function listDoctors(array $filters = [])
    {
        $user = User::getAuthenticatedUser();
        $dateRange = [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()];
        $query = Doctor::with([
            'user',
            'hospitalUser',
            'hospital',
            'appointments' => function ($q) use ($dateRange) {
                $q->whereDate('appointment_date', Carbon::today());
            }
        ]);

        $hospitalId = null;

        if ($user->hospitals()->exists()) {
            $hospitalId = $user->hospitals()->first()->id;
        } elseif ($user->hospitalUser?->hospital) {
            $hospitalId = $user->hospitalUser->hospital->id;
        }

        if ($hospitalId) {
            $query->where('hospital_id', $hospitalId);
        }

        if (!empty($filters['hospital_id'])) {
            $query->where('hospital_id', $filters['hospital_id']);
        }

        if ($user?->hospitalUser?->role && strcasecmp($user->hospitalUser->role, 'Doctor') === 0) {
            $doctorId = $user->doctor->id ?? null;
            if ($doctorId) {
                $query->where('id', $doctorId);
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        if (!empty($filters['status'])) {
            $query->whereRaw('LOWER(status) = ?', [strtolower($filters['status'])]);
        }

        // Ensure only doctors (hospital_user role)
        $query->whereHas('hospitalUser', function ($q) {
            $q->whereRaw('LOWER(role) = ?', ['doctor']);
        });

        return $query->get();
    }




    public function assign($doctor)
    {
        return DB::transaction(function () use ($doctor) {
            $doctor = $this->getById($doctor);
        });
    }
}
