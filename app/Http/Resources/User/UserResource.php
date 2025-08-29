<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use ApiPlatform\Metadata\ApiResource;
use App\Helpers\Helper;

#[ApiResource]
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $hospitalUserData = null;
        $pharmacyData = null;

        if ($this?->hospitalUser) {
            if (strcasecmp($this->hospitalUser->role, 'Doctor') === 0) {
                $hospitalUserData = [
                    "role"   => $this?->hospitalUser->role,
                    "doctor" => $this?->hospitalUser->doctor ?? null,
                ];
            } elseif (strcasecmp($this->hospitalUser->role, 'FrontDesk') === 0) {
                $hospitalUserData = [
                    "role"      => $this?->hospitalUser->role,
                    "frontdesk" => $this?->hospitalUser->frontdesk ?? null,
                ];
            } else {
                $hospitalUserData = [
                    "role" => $this?->hospitalUser->role,
                ];
            }
        }

        if (strcasecmp($this?->role, 'Pharmacy') === 0) {
            $pharmacyData = $this?->pharmacy ?? null;
        }


        return [
            "id"                => (int) $this->id,
            "first_name"        => $this->first_name,
            "last_name"         => $this->last_name,
            "email"             => (string) $this->email,
            "phone_number"      => $this->phone,
            "role"              => $this->hospitalUser ? $this->hospitalUser->role : $this->role,
            'date_of_birth'     => $this->date_of_birth ?? null,
            'gender'            => $this->gender ?? null,
            "avatar"            => $this->avatar,
            "address"           => $this->address,
            "state"             => $this->state,
            "city"              => $this->city,
            "fcm_token"         => $this->fcm_token,
            "last_login_at"     => formatDate($this->last_login_at),
            'country'           => (new Helper)->getCountry(),
            "email_verified_at" => formatDate($this->email_verified_at),
            "created_at"        => formatDate($this->created_at),
            "updated_at"        => formatDate($this->updated_at),
            "hospital_user"     => $hospitalUserData,
            "pharmacy"          => $pharmacyData,
        ];
    }

    public static function custom($model)
    {
        return [
            "id"       => (int) $model->id,
            "avatar"   => $model->avatar,
            "name"     => $model->full_name,
            "username" => $model->username,
            "email"    => (string) $model->email,
        ];
    }

    public static function customCollection($collections)
    {
        return $collections->map(function ($model) {
            return self::custom($model);
        });
    }
}
