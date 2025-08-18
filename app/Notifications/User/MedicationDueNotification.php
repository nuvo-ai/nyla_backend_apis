<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MedicationDueNotification extends Notification
{
    use Queueable;

    protected $reminder;

    public function __construct($reminder)
    {
        $this->reminder = $reminder;
    }

    public function via($notifiable)
    {
        return ['mail', 'database']; // or SMS if using Twilio/Vonage
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject("Medication Reminder")
            ->line("It's time to take your medication: {$this->reminder->name}")
            ->line("Dosage: {$this->reminder->dosage}")
            ->line("Scheduled Time: {$this->reminder->time}");
    }

    public function toDatabase($notifiable)
    {
        return [
            'title'   => 'Medication Reminder',
            'message' => "It's time to take your medication: {$this->reminder->name}",
            'dosage'  => $this->reminder->dosage,
            'time'    => $this->reminder->time,
            'reminder_id' => $this->reminder->id,
        ];
    }
}
