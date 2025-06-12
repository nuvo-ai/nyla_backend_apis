<?php

namespace App\Services\User;

use App\Constants\User\UserConstants;
use App\Constants\General\AppConstants;
use App\Constants\General\StatusConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserService
{
    public User $user;
    public $interest_service;

    public function __construct() {}

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): User
    {
        $model = User::where($column, $key)->first();
        if (empty($model)) {
            throw new ModelNotFoundException("User not found");
        }
        return $model;
    }

    public function validate(array $data, $id = null): array
    {
        $validator = Validator::make($data, [
            'fcm_token' => 'nullable|string',
            "first_name" => "nullable|string",
            "last_name" => "nullable|string",
            "role" => "nullable|" . Rule::in(UserConstants::ROLES),
            "email" => "required|email|unique:users,email,$id|" . Rule::requiredIf(empty($id)),
            "status" => "nullable|string",
            'password' => [Rule::requiredIf(empty($id))],
            "phone_number" => "nullable",
            "gender" => Rule::in(AppConstants::GENDERS) . "|nullable",
            "dob" => 'nullable|date_format:Y-m-d|before:today',
        ], [
            'email.unique' => "The email address has already been used by another user",
            'username.unique' => "The email address has already been used by another user",
            'dob.date_format' => 'The date of birth must be in the format dd/mm/yyyy',
            'dob.before' => 'The date of birth must be a date before today',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }


    public function create(array $data): User
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data);
            $data = array_merge([
                'status' => StatusConstants::ACTIVE,
                'role' => $data["role"] ?? UserConstants::USER
            ], $data);

            $data['password'] = !empty($data['password'] ?? null) ? Hash::make($data['password']) : null;
            $user = User::create($data);
            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function update(array $data, $id = null)
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data, $id);

            $user = !empty($id) ? $this->getById($id) : auth()->user();

            if (isset($data["password"])) {
                $data["password"] = Hash::make($data["password"]);
            }

            $user->update($data);

            DB::commit();
            return $user->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    // delete user
    public function delete($id = null)
    {
        DB::beginTransaction();
        try {
            $user = !empty($id) ? $this->getById($id) : auth()->user();
            // delete user
            $user->delete();
            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function logout($id = null)
    {
        DB::beginTransaction();
        try {
            $user = !empty($id) ? $this->getById($id) : auth()->user();
            // delete user
            $user->tokens()->delete();
            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
