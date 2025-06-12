<?php

namespace App\Services\User;

use App\Constants\User\UserConstants;
use App\Constants\General\AppConstants;
use App\Constants\General\StatusConstants;
use App\Exceptions\General\ModelNotFoundException;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileService
{
    public User $user;

    public function __construct() {}

    public static function init(): self
    {
        return app()->make(self::class);
    }

    public static function getById($key, $column = "id"): User
    {
        $user_id = auth()->user()->id;
        $key = !empty($key) ? $key : $user_id;
        $model = User::where($column, $key)->first();
        if (empty($model)) {
            throw new ModelNotFoundException("User not found");
        }
        return $model;
    }

    public function validate(array $data, $id = null): array
    {
        $validator = Validator::make($data, [
            "first_name" => "nullable|string",
            "last_name" => "nullable|string",
            "phone_number" => "nullable",
            "phone_number2" => "nullable",
            "address" => "nullable|string",
            "state" => "nullable|string",
            "city" => "nullable|string",
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function update(array $data, $id = null)
    {
        DB::beginTransaction();
        try {
            $data = self::validate($data, $id);
            $user = !empty($id) ? $this->getById($id) : auth()->user()->id;
            $user->update($data);
            DB::commit();
            return $user->refresh();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
