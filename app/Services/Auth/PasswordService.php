<?php

namespace App\Services\Auth;

use App\Constants\Auth\OtpConstants;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordService
{
    public $pin_service;
    function __construct()
    {
        $this->pin_service = new PinService;
    }

    public function sendPasswordResetPin(array $data)
    {
        $data = Validator::make($data, [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => "The email address does not exist in our records.",
        ])->validate();

        $user = User::firstWhere($data);

        $pin_expiry = now()->addSeconds(config("system.configuration.pin_expiry"));

        $this->pin_service->create($user, [
            "type" => OtpConstants::TYPE_RESET_PASSWORD,
            "expires_at" => $pin_expiry,
            "length" => config("system.configuration.length", 4),
            "code_type" => "int",
        ]);
    }


    public function resetPassword(array $data)
    {
        $data = Validator::make($data, [
            "code" => "required|string",
            'password' => 'required|string',
            "type" => "nullable|string"
        ])->validate();

        $check = $this->pin_service->verify([
            "code" => $data["code"],
            "type" => $data["type"] ?? OtpConstants::TYPE_RESET_PASSWORD
        ]);

        $user = $check["user"];

        $user->update([
            "password" => Hash::make($data["password"])
        ]);
    }
}
