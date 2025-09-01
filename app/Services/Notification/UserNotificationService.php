<?php

namespace App\Services\Notification;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserNotification;

class UserNotificationService
{
    public function list($user)
    {
        return $user->notifications()->latest()->get();
    }

    public function markAsRead($user, $notificationId)
    {
        $notification = $user->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
        return $notification;
    }
}
