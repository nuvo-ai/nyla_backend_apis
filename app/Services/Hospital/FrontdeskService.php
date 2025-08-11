<?php

namespace App\Services\Hospital;

use App\Constants\User\UserConstants;
use App\Models\Hospital\FrontDesk;
use App\Models\User\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class FrontdeskService
{
    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            // 'user_id' => ['required_if:id,null', 'exists:users,id'],
            // 'hospital_id' => ['required_if:id,null', 'exists:hospitals,id'],
            // 'hospital_user_id' => ['required_if:id,null', 'exists:hospital_users,id'],
            'shift' => ['nullable', 'string'],
            'department' => ['required', 'string'],
            'years_of_experience' => ['nullable', 'string'],
            'id' => ['nullable'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public static function getById($id): FrontDesk
    {
        $model = FrontDesk::find($id);
        if (!$model) {
            throw new ModelNotFoundException("Staff not found");
        }
        return $model;
    }

    public function save(array $data, ?int $id = null): FrontDesk
    {
        $validated = $this->validate(array_merge($data, ['id' => $id]), $id);

        if ($id) {
            $staff = self::getById($id);
            $staff->update($validated);
            $staff->role = $data['role'] ?? UserConstants::FRONT_DESK;
            $staff->save();
        } else {
            $staff = FrontDesk::create($validated);
            $staff->role = $data['role'] ?? UserConstants::FRONT_DESK;
            $staff->save();
        }

        return $staff->load('user', 'hospital', 'hospitalUser');
    }

    public function list(array $filters = [])
    {
        $query = FrontDesk::with(['user', 'hospital', 'hospitalUser'])
            ->where('user_id', User::getAuthenticatedUser()?->hospitalUser?->user_id);

        if (!empty($filters['department'])) {
            $query->where('department', $filters['department']);
        }

        if (!empty($filters['hospital_id'])) {
            $query->where('hospital_id', $filters['hospital_id']);
        }

        return $query->get();
    }
}
