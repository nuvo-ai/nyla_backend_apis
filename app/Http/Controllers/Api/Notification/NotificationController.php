<?php

namespace App\Http\Controllers\Api\Notification;

use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Notification\NotificationResource;
use App\Models\User\User;
use App\Services\Notification\UserNotificationService;
use Exception;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notification_service;
    public function __construct()
    {
        $this->notification_service = new UserNotificationService;
    }
    public function list()
    {
        try {
            $user = User::getAuthenticatedUser();
            $notifications = $this->notification_service->list($user);
            return ApiHelper::validResponse("Notifications retrieved successfully", NotificationResource::collection($notifications));
        } catch (Exception $e) {
            return ApiHelper::problemResponse("Unable to retrieve notifications", 500, null, $e);
        }
    }

    public function markAsRead($id)
    {
        $user = User::getAuthenticatedUser();
        $notification = $this->notification_service->markAsRead($user, $id);

        return $notification
            ? ApiHelper::validResponse("Notification marked as read", new NotificationResource($notification))
            : ApiHelper::problemResponse("Notification not found", 404);
    }

    public function details()
    {
        //
    }
}
