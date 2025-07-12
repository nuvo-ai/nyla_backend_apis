<?php

namespace App\Notifications;

use App\Models\Hospital\Appointment;
use App\Models\Hospital\Hospital;
use App\Models\Hospital\HospitalAppointment;
use App\Models\User\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SendAppointmentNotification extends Notification
{
    use Queueable;

    protected HospitalAppointment $appointment;
    protected User $recipient;
    protected bool $isUpdate;

    public function __construct(HospitalAppointment $appointment, User $recipient, bool $isUpdate = false)
    {
        $this->appointment = $appointment;
        $this->recipient = $recipient;
        $this->isUpdate = $isUpdate;
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
            ->line($data['message'])
            ->line('Patient Name: ' . $this->appointment->patient_name)
            ->line('Appointment Date: ' . $this->appointment->appointment_date)
            ->line('Appointment Time: ' . $this->appointment->appointment_time)
            ->line('Status: ' . ucfirst($this->appointment->status));

        if (!empty($data['link'])) {
            $mail->action('View Appointment', url($data['link']));
        }

        return $mail->line('Thank you for your attention.');
    }


    protected function buildData($notifiable): array
    {
        $doctorId = optional($this->appointment->doctor)->id;
        $schedulerId = $this->appointment->scheduler_id;

        $isDoctor = $this->recipient->id === $doctorId;
        $isScheduler = $this->recipient->id === $schedulerId;

        $doctorName = optional(optional($this->appointment->doctor)->user)->full_name ?? 'Doctor';
        $patientName = $this->appointment->patient_name;

        if ($this->isUpdate) {
            if ($isDoctor) {
                $title = "Appointment Updated with Patient: $patientName";
                $message = "The appointment with $patientName has been rescheduled to {$this->appointment->appointment_date} at {$this->appointment->appointment_time}.";
            } elseif ($isScheduler) {
                $title = "Your Appointment Has Been Updated";
                $message = "Your appointment with Dr. $doctorName has been rescheduled to {$this->appointment->appointment_date} at {$this->appointment->appointment_time}.";
            } else {
                $title = "Appointment Updated";
                $message = "An appointment has been updated.";
            }
        } else {
            if ($isDoctor) {
                $title = "New Appointment with Patient: $patientName";
                $message = "You have a new appointment scheduled with $patientName.";
            } elseif ($isScheduler) {
                $title = "Appointment Scheduled Successfully";
                $message = "Your appointment with Dr. $doctorName has been successfully scheduled.";
            } else {
                $title = "New Appointment Notification";
                $message = "A new appointment has been scheduled.";
            }
        }

        return [
            'title' => $title,
            'message' => $message,
            'appointment_id' => $this->appointment->id,
            'patient_name' => $this->appointment->patient_name,
            'appointment_date' => $this->appointment->appointment_date,
            'appointment_time' => $this->appointment->appointment_time,
            'status' => $this->appointment->status,
            'link' => null,
        ];
    }
}
