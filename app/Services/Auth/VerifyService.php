<?php

namespace App\Services\Auth;

use App\Constants\Auth\OtpConstants;
use App\Exceptions\Auth\OtpException;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VerifyService
{
    public $pin_service;
    function __construct()
    {
        $this->pin_service = new PinService;
    }

    public function sendPin(User $user)
    {
        // dd("Sending pin to user");
        $pin_expiry = now()->addSeconds(config("system.configuration.pin_expiry"));
        $email = $user->email;
        $this->pin_service->create($user, [
            "type" => OtpConstants::TYPE_EMAIL_VERIFICATION,
            "expires_at" => $pin_expiry,
            "email" => $email,
            "length" => 4,
            "code_type" => "int",
        ]);
    }


    public function request(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data = Validator::make($data, [
                "type" => "required|string|" . Rule::in(array_keys(OtpConstants::TITLES)),
                "email" => "required|email|exists:users,email",
            ])->validate();

            $user = User::firstWhere("email", $data["email"]);

            $pin_expiry = now()->addSeconds(config("system.configuration.pin_expiry"));

            $this->pin_service->create($user, [
                "type" => $data["type"],
                "email" => $data["email"],
                "expires_at" => $pin_expiry,
                "length" => config("system.configuration.length", 4),
                "code_type" => "int",
            ]);
        });
    }

    public function verify(array $data)
    {
        return DB::transaction(function () use ($data) {
            $data = Validator::make($data, [
                "code" => "required|string",
                "email" => [auth("sanctum")->check() ? "nullable" : "required", "email"],
                "type" => "nullable"
            ])->validate();

            $data["type"] ??= OtpConstants::TYPE_EMAIL_VERIFICATION;

            $check = $this->pin_service->verify($data);

            $pin = $check["pin"];
            $user = $check["user"] ?? null;

            if (auth("sanctum")->check() && !empty($user)) {
                if ($user->id != auth("sanctum")->id()) {
                    throw new OtpException("The code is invalid. Kindly request a new code.");
                }
            } else {
                if (strtolower($pin->user->email) != strtolower($data["email"])) {
                    throw new OtpException("The email address does not match the code. Kindly request a new code.");
                }
            }

            if ($data["type"] == OtpConstants::TYPE_EMAIL_VERIFICATION) {
                $user?->update([
                    "email_verified_at" => now(),
                ]);
            }
        });
    }
}
