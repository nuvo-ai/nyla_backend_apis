<?php

namespace App\Services\Notification;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NotificationService
{
    protected $mailer_service;

    public function __construct()
    {
        $this->mailer_service = new AppMailerService;
    }

    public function validateData(array $data)
    {
        $messages = [
            'email.required' => 'The email field is required.',
            'email.email' => 'The email provided is not a valid email address.',

            'sms.boolean' => 'SMS preference must be true or false.',
            'push.boolean' => 'Push notification preference must be true or false.',
            'appointment_reminders.boolean' => 'Appointment reminder preference must be true or false.',
        ];

        $validator = Validator::make($data, [
            'email' => 'nullable|boolean',
            'sms' => 'nullable|boolean',
            'push' => 'nullable|boolean',
            'appointment_reminders' => 'nullable|boolean',
        ], $messages);

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'message' => 'Validation failed. Please check the submitted preferences.',
                'errors' => $validator->errors()
            ]);
        }

        return $validator->validated();
    }
   public function setPreferences(array $preferences)
{
    return DB::transaction(function () use ($preferences) {
        $validated = $this->validateData($preferences);

        $user = auth()->user();

        $preference = $user->notificationPreference()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'email' => $validated['email'] ?? true,
                'sms' => $validated['sms'] ?? false,
                'push' => $validated['push'] ?? false,
                'appointment_reminders' => $validated['appointment_reminders'] ?? false,
            ]
        );

        return $preference;
    });
}

}
