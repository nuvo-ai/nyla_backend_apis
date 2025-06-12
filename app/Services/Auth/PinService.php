<?php

namespace App\Services\Auth;

use App\Constants\Auth\OtpConstants;
use App\Exceptions\Auth\OtpException;
use App\Helpers\Helper;
use App\Models\Pin;
use App\Models\User;
use App\Services\Notification\AppMailerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PinService
{
    public  function create(User $user, array $data)
    {
        $data = Validator::make($data, [
            "type" => "required|string|" . Rule::in(array_keys(OtpConstants::TITLES)),
            "expires_at" => "required|date",
            "length" => "required|numeric",
            "email" => "required|email",
            "code_type" => "required|in:int,string",
        ])->validate();

        $query = ['type' => $data["type"]];
        if (!empty($user->id)) {
            $query["user_id"] = $user->id;
            $query["email"] = $user->email;
        } else {
            $query["email"] = $user->email;
        }


        $pin = Pin::updateOrCreate($query, [
            'code' => Helper::generateRandomDigits($data["length"]),
            'expires_at' => $data["expires_at"],
        ]);

        AppMailerService::sendNow([
            "data" => [
                "pin" => $pin,
                'user' => $user,
                "expires_at" => Carbon::parse($data["expires_at"])->diffForHumans()
            ],
            "to" => $user->email,
            "template" => "emails.auth.pin." . $data["type"],
            "subject" => OtpConstants::TITLES[$data["type"]],
        ]);

        return $pin;
    }


    public static function verify(array $data)
    {
        $validator = Validator::make($data, [
            "code" => "required|string",
            "type" => "required|string",
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $data = $validator->validated();
        $pin = Pin::firstWhere($data);

        if (empty($pin)) {
            throw new OtpException("The code is invalid. Kindly request a new code.");
        }

        if (!empty($ex = $pin->expires_at) && Carbon::parse($ex)->isPast()) {
            $pin->delete();
            throw new OtpException("Code has expired, kindly request a new code");
        }

        $user = $pin->user;

        if ($data["type"] !== OtpConstants::TYPE_RESET_PASSWORD) {
            $pin->delete();
        }

        return [
            "user" => $user,
            "pin" => $pin
        ];
    }
}
