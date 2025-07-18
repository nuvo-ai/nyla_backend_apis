<?php

namespace App\Services\User;

use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Constants\User\UserConstants;
use App\Constants\General\AppConstants;
use Illuminate\Support\Facades\Validator;
use App\Constants\General\StatusConstants;
use Illuminate\Validation\ValidationException;
use App\Exceptions\General\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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
            "phone" => "nullable",
            "address" => "nullable|string",
            "state" => "nullable|string",
            "city" => "nullable|string",
            'avatar' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
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
            if (isset($data['avatar']) && !empty($data['avatar'])) {
                if ($user->avatar) {
                    Storage::delete($user->avatar);
                }
                $data['avatar'] = $this->handleFileUpload($data['avatar'] ?? null, 'user-avatars');
                $user->save();
            }
            $user->update($data);
            DB::commit();
            return $user->refresh();
        } catch (\Throwable $th) {  
            DB::rollBack();
            throw $th;
        }
    }

    private function handleFileUpload(?UploadedFile $file, string $directory): ?string
    {
        if (!$file) {
            return null;
        }

        return $file->store($directory, 'public');
    }
}
