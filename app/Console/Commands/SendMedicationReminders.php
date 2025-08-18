<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User\MedicationReminder;
use App\Notifications\MedicationDueNotification;
use Carbon\Carbon;

class SendMedicationReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send medication reminders to users';

    public function handle()
    {
        $now = Carbon::now()->format('H:i'); // only compare time

        $reminders = MedicationReminder::with('user')->get();

        foreach ($reminders as $reminder) {
            $reminderTime = Carbon::createFromFormat('H:i:s', $reminder->time)->format('H:i');

            if (!$reminder->frequency || $reminder->frequency == 1) {
                // ---- 1. Fixed once-per-day reminder ----
                if ($now === $reminderTime) {
                    $reminder->user?->notify(new MedicationDueNotification($reminder));
                }
            } else {
                // ---- 2. Frequency-based reminder ----
                $interval = floor(24 / (int) $reminder->frequency); // hours between reminders
                $start = Carbon::createFromFormat('H:i:s', $reminder->time);

                for ($i = 0; $i < $reminder->frequency; $i++) {
                    if ($now === $start->format('H:i')) {
                        $reminder->user?->notify(new MedicationDueNotification($reminder));
                    }
                    $start->addHours($interval);
                }
            }
        }

        $this->info("Checked reminders at " . Carbon::now()->toDateTimeString());
    }
}
