<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use ApiPlatform\Metadata\ApiResource;

#[ApiResource]
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            "id" => (int) $this->id,
            "first_name" => $this->first_name,
            "last_name" => $this->last_name,
            "email" => (string) $this->email,
            "phone_number" => $this->phone_number,
            "phone_number2" => $this->phone_number2,
            "role" => $this->role,
            "address" => $this->address,
            "state" => $this->state,
            "city" => $this->city,
            "fcm_token" => $this->fcm_token,
            "last_login_at" =>  formatDate($this->last_login_at),
            "email_verified_at" => formatDate($this->email_verified_at),
            "created_at" => formatDate($this->created_at),
            "updated_at" => formatDate($this->updated_at)
        ];
    }

    public static function custom($model)
    {
        return [
            "id" => (int) $model->id,
            "avatar" => $model->avatar,
            "name" => $model->full_name,
            "username" => $model->username,
            "email" => (string) $model->email,
        ];
    }

    public static function customCollection($collections)
    {
        return $collections->map(function ($model) {
            return self::custom($model);
        });
    }
}
