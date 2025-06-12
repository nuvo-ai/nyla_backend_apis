<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\User\WelcomeNotification;
use App\Services\User\UserService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class RegistrationService
{

    public $user_service;
    public $verify_service;

    public function __construct()
    {
        $this->user_service = new UserService;
        $this->verify_service = new VerifyService;
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->user_service->create($data);
            $this->verify_service->sendPin($user);
            return $user;
        });
    }

    public  function postRegisterActions(User $user)
    {
        $this->sendWelcomeMessage($user);
    }

    private function sendWelcomeMessage(User $user)
    {
        Notification::send($user, new WelcomeNotification);
    }
}
