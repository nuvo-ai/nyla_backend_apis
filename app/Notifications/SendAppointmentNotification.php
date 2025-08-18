<?php

namespace App\Notifications\User;

use App\Models\User\MedicationReminder;
use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class MedicationDueNotification extends Notification
{
    use Queueable;

    protected MedicationReminder $reminder;
    protected User $recipient;

    public function __construct(MedicationReminder $reminder, User $recipient)
    {
        $this->reminder = $reminder;
        $this->recipient = $recipient;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        return $this->buildData($notifiable);
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->buildData($notifiable));
    }

    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->buildData($notifiable);

        $mail = (new MailMessage)
            ->subject($data['title'])
            ->greeting("Hello {$notifiable->full_name},")
            ->line("⏰ It's time to take your medication!")
            ->line("Here are the details of your reminder:")
            ->line('💊 Medication: **' . $this->reminder->name . '**')
            ->line('📦 Dosage: ' . (!empty($this->reminder->dosage) ? $this->reminder->dosage : 'Not specified'))
            ->line('🕒 Time: ' . $this->reminder->time);

        if (!empty($data['link'])) {
            $mail->action('View Reminder', url($data['link']));
        }

        return $mail
            ->line('✅ Please make sure to take your medication on time for best health results.')
            ->salutation('— Your Health Reminder Assistant');
    }


    protected function buildData($notifiable): array
    {
        $title = "Medication Reminder: {$this->reminder->name}";

        $message = "Hey {$notifiable->full_name}!" . PHP_EOL
            . "It's time to take your medication '{$this->reminder->name}'"
            . (!empty($this->reminder->dosage) ? " (Dosage: {$this->reminder->dosage})" : "")
            . " at {$this->reminder->time}.";

        return [
            'title' => $title,
            'message' => $message,
            'medication_name' => $this->reminder->name,
            'dosage' => $this->reminder->dosage,
            'time' => $this->reminder->time,
            'link' => null,
        ];
    }
}
