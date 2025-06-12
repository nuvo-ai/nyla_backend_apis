<?php

namespace App\Services\Auth;

use App\Constants\User\UserConstants;
use App\Exceptions\Auth\AuthException;
use App\Models\User;
use App\Services\User\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginService
{
    public static function preview($data)
    {
        $data = Validator::make($data, [
            'email' => 'required|string|email|exists:users,email',
        ], [
            'email.exists' => "The email address does not exist in our records.",
        ])->validate();

        $user = User::where('email', $data["email"])->first();
        return $user;
    }

    public static function authenticate(array $data)
    {
       return DB::transaction(function () use ($data) {
            $data = Validator::make($data, [
                "email" => "required|string|email|exists:users,email",
                'password' => ['required', 'string'],
                'fcm_token' => 'nullable|string',
            ])->validate();

            $user = User::where("email", $data["email"])->first();

            if (!Hash::check($data["password"], $user->password)) {
                throw new AuthException("Incorrect password provided.");
            }

            if (!empty($token = $data["fcm_token"] ?? null)) {
                $user->update([
                    "fcm_token" => $token
                ]);
            }

            if (empty($user->email_verified_at)) {
                (new VerifyService())->sendPin($user);
            }

            return $user->refresh();
        });
    }

    public static function ouath(array $payload)
    {
        return DB::transaction(function () use ($payload) {
            $response = (new OAuthLoginService)
                ->setToken($payload["token"])
                ->setProvider($payload["provider"])
                ->byProvider();

            if (empty($response)) {
                throw new AuthException("Unable to login via oauth");
            }

            $email = $payload["email"];
            $full_name = explode(" ", $payload["name"]);
            $user = User::where('email', $email)->first();

            $data["new_user"] = false;
            if (empty($user)) {
                $user = (new UserService)->create([
                    'first_name' => $full_name[0],
                    'last_name' => $full_name[1] ?? $full_name[0],
                    "email" => $email,
                    "role" => UserConstants::USER,
                    'password' => Hash::make(Str::random(64)),
                    'registration_platform' => $payload["provider"],
                    'fcm_token' => $payload["fcm_token"],
                ]);

                $data["new_user"] = true;
            }

            if (empty($user->email_verified_at)) {
                $user->update([
                    "email_verified_at" => now()
                ]);
            }

            if (!empty($token = $data["fcm_token"] ?? null)) {
                $user->update([
                    "fcm_token" => $token
                ]);
            }

            return [
                "data" => $data,
                "user" => $user
            ];
        });
    }

    public static function newLogin(User $user)
    {
        self::updateLogin($user);
    }

    public static function updateLogin(User $user)
    {
        $user->update(attributes: [
            "last_login_at" => now(),
        ]);
    }
}
