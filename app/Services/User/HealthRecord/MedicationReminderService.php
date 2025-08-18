<?php

namespace App\Services\User\HealthRecord;

use App\Exceptions\General\ModelNotFoundException;
use App\Models\User\MedicationReminder;
use App\Models\User\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class MedicationReminderService
{
    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'user_id'        => ['required', 'exists:users,id'],
            'name'           => ['required', 'string'],
            'dosage'         => ['nullable', 'string'],
            'frequency' => ['required', 'integer', 'min:1'], // e.g., 1 = once daily, 2 = twice daily
            'time'           => ['required', 'date_format:H:i:s'],
            'notes'          => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function save(array $data, ?int $id = null): MedicationReminder
    {
        $validated = $this->validate($data);

        return DB::transaction(function () use ($validated, $id) {
            if ($id) {
                $reminder = MedicationReminder::find($id);
                if (!$reminder) throw new ModelNotFoundException("Reminder not found");
                $reminder->update($validated);
            } else {
                $reminder = MedicationReminder::create($validated);
            }
            return $reminder;
        });
    }

    public function list()
    {
        $userId = User::getAuthenticatedUser()->id;
        return MedicationReminder::where('user_id', $userId)->get();
    }
}
