<?php

namespace App\Services\Hospital;

use App\Constants\User\UserConstants;
use App\Models\Hospital\HospitalUser;
use App\Models\User\User;
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
            'hospital_id' => ['nullable', 'exists:hospitals,id'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
    public function listHospitalUsers(array $filters = [])
    {
        $query = HospitalUser::with(['user', 'hospital', 'doctor', 'frontdesk'])
            ->where('hospital_id', User::getAuthenticatedUser()->hospitalUser?->hospital?->id);

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
            $query->where(function ($q) use ($filters) {
                $q->whereHas('doctor', function ($q2) use ($filters) {
                    $q2->where('departments', 'like', '%' . $filters['department'] . '%');
                })
                    ->orWhereHas('frontdesk', function ($q2) use ($filters) {
                        $q2->where('departments', 'like', '%' . $filters['department'] . '%');
                    });
            });
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['hospital_id'])) {
            $query->where('hospital_id', $filters['hospital_id']);
        }

        return $query->paginate(30);
    }


    public function deleteHospitalUser($id = null)
{
    DB::beginTransaction();
    try {
        $hospitalUser = HospitalUser::with(['doctor', 'frontDesk', 'user'])->findOrFail($id);

        if ($hospitalUser->role === UserConstants::ADMIN) {
            $otherAdminExists = HospitalUser::where('hospital_id', $hospitalUser->hospital_id)
                ->where('id', '!=', $hospitalUser->id)
                ->where('role', UserConstants::ADMIN)
                ->exists();

            if (!$otherAdminExists) {
                throw ValidationException::withMessages([
                    'admin' => ['You must assign a new admin before deleting this hospital admin.']
                ]);
            }
        }

        if ($hospitalUser->doctor) {
            $hospitalUser->doctor->delete();
        }

        if ($hospitalUser->frontDesk) {
            $hospitalUser->frontDesk->delete();
        }

        if ($hospitalUser->user) {
            $hospitalUser->user->delete();
        }

        $hospitalUser->delete();

        DB::commit();
        return true;
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
            $user->save();

            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
