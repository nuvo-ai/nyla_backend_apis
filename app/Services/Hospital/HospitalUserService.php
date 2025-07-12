<?php

namespace App\Services\Hospital;

use App\Models\Hospital\HospitalUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class HospitalUserService
{
    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'role' => ['required', 'string'],
            'hospital_id' => ['required', 'exists:hospitals,id'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
    public function listHospitalUsers(array $filters = []): Collection
    {
        $query = HospitalUser::with('user');

        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);
            $query->whereHas('user', function ($q) use ($status) {
                $q->whereRaw('LOWER(status) = ?', [$status]);
            });
        }
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (!empty($filters['department'])) {
            $query->whereHas('doctor', function ($q) use ($filters) {
                $q->where('departments', 'like', '%' . $filters['department'] . '%');
            });
            $query->orWhereHas('frontdesk', function ($q) use ($filters) {
                $q->where('departments', 'like', '%' . $filters['department'] . '%');
            });
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }
        if (!empty($filters['hospital_id'])) {
            $query->where('hospital_id', $filters['hospital_id']);
        }
        return $query->get();
    }

    public function deleteHospitalUser($id = null)
    {
        DB::beginTransaction();
        try {
            $hospitalUser = HospitalUser::findOrFail($id);
            $hospitalUser->delete();
            DB::commit();
            return $hospitalUser;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function assignRoleToUser(array $data, $userId)
    {
        DB::beginTransaction();
        try {
            $user = HospitalUser::findOrFail($userId);
            $validated = self::validate($data);

            if (isset($validated['role'])) {
                $user->role = $validated['role'];
            }

            if (isset($validated['hospital_id'])) {
                $user->hospital_id = $validated['hospital_id'];
            }

            $user->save();

            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
