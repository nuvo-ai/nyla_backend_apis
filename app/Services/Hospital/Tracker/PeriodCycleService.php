<?php

namespace App\Services\Hospital\Tracker;
use App\Models\Hospital\PeriodCycle;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PeriodCycleService
{
    public function validate(array $data)
    {
        $validator = Validator::make($data, [
            'last_period_start_date' => ['required', 'date'],
            'cycle_length' => ['required', 'integer', 'min:21', 'max:35'],
            'period_length' => ['required', 'integer', 'min:2', 'max:10'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function store(array $data): PeriodCycle
    {
        $validated = $this->validate($data);
        $userId = User::getAuthenticatedUser()->id;

        return PeriodCycle::updateOrCreate(
            ['user_id' => $userId],
            $validated
        );
    }

    public function show(): PeriodCycle
    {
        $cycle = PeriodCycle::where('user_id', User::getAuthenticatedUser()->id)->firstOrFail();
        return $cycle;
    }
}
