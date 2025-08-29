<?php

namespace App\Services\User;

use App\Models\User\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Constants\User\UserConstants;
use App\Constants\General\AppConstants;
use Illuminate\Support\Facades\Validator;
use App\Constants\General\StatusConstants;
use App\Constants\General\TitleConstants;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Exceptions\General\ModelNotFoundException;
use App\Mail\SendUserLoginDetailsMail;
use App\Models\Portal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
            'name'        => 'required_without:first_name|required_without:last_name|string',
            'first_name'  => 'required_without:name|string|nullable',
            'last_name'   => 'required_without:name|string|nullable',
            "title" => ['nullable', Rule::in(TitleConstants::TITLES)],
            "role" => "nullable|" . Rule::in(UserConstants::ROLES),
            "email" => "required|email|unique:users,email,$id|" . Rule::requiredIf(empty($id)),
            "status" => "nullable|string",
            'password' => "nullable",
            "phone" => "nullable",
            "gender" => Rule::in(AppConstants::GENDERS) . "|nullable",
            "date_of_birth" => 'nullable|date_format:Y-m-d|before:today',
            'portal' => [
                Rule::requiredIf(function () use ($data) {
                    return !empty($data['hospital_id']) || !empty($data['pharmacy_id']);
                }),
                'string'
            ],
        ], [
            'email.unique' => "The email address has already been used by another user",
            'username.unique' => "The email address has already been used by another user",
            'date_of_birth.date_format' => 'The date of birth must be in the format dd/mm/yyyy',
            'date_of_birth.before' => 'The date of birth must be a date before today',
            'portal.required' => 'Portal is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function create(array $data): User
    {
        try {
            if (!isset($data['first_name']) || !isset($data['last_name'])) {
                if (isset($data['name'])) {
                    $name_parts = preg_split('/\s+/', trim($data['name']));
                    $possible_title = $name_parts[0] ?? null;
                    if (in_array($possible_title, TitleConstants::TITLES)) {
                        $data['title'] = $possible_title;
                        $data['first_name'] = $name_parts[1] ?? null;
                        $data['last_name'] = implode(' ', array_slice($name_parts, 2));
                    } else {
                        $data['first_name'] = $name_parts[0] ?? null;
                        $data['last_name'] = implode(' ', array_slice($name_parts, 1));
                    }
                }
            }

            $validated = self::validate($data);

            if (isset($validated['portal'])) {
                $portal = Portal::firstOrCreate(['name' => $validated['portal']]);
                unset($validated['portal']);
            }

            unset($validated['name']);
            $validated['status'] = $validated['status'] ?? StatusConstants::ACTIVE;
            $validated['role'] = $validated['role'] ?? UserConstants::USER;
            $validated['gender'] = $validated['gender'] ?? null;
            $validated['date_of_birth'] = $validated['date_of_birth'] ?? null;
            $validated['phone'] = $validated['phone'] ?? null;
            $plainPassword = !empty($validated['password'])
                ? $validated['password']
                : Str::random(10);

            $validated['password'] = Hash::make($plainPassword);

            if (isset($portal)) {
                $validated['portal_id'] = $portal->id ?? null;
            }
            $user = User::create($validated);
            $user->plain_password = $plainPassword;
            if ($user->portal && $user->portal->name === 'Hospital') {
                $authUser = auth()->user();
                $hospitalId = $data['hospital_id'] ?? ($authUser && $authUser?->hospitalUser?->hospital ? $authUser?->hospitalUser?->hospital->id : null);
                $userAccountId = $authUser ? $authUser->id : null;

                $user->hospitalUser()->create([
                    'user_id' => $user->id,
                    'hospital_id' => $hospitalId,
                    'role' => $validated['role'],
                    'user_account_id' => $userAccountId,
                ]);
            }
            return $user;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(array $data, $id = null)
    {
        DB::beginTransaction();
        try {
            if (isset($data['user_email'])) {
                $data['email'] = $data['user_email'];
            }
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

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $user = User::withTrashed()->findOrFail($id);
            $user->restore();
            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function suspend($id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            $user->update([
                "status" => "Suspended",
            ]);
            $user->save();
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
