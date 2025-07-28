<?php

namespace App\Http\Resources\Hospital\Tracker;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PeriodCycleResource extends JsonResource
{
    public function toArray($request)
    {
        $lastStart = Carbon::parse($this->last_period_start_date);
        $nextPeriod = $lastStart->copy()->addDays($this->cycle_length);
        $ovulation = $lastStart->copy()->addDays($this->cycle_length - 14);
        $fertileStart = $ovulation->copy()->subDays(5);
        $fertileEnd = $ovulation->copy()->addDay();

        return [
            'last_period_start_date' => $lastStart->toDateString(),
            'cycle_length' => $this->cycle_length,
            'period_length' => $this->period_length,
            'next_period' => $nextPeriod->toDateString(),
            'ovulation_day' => $ovulation->toDateString(),
            'fertile_window' => [
                'start' => $fertileStart->toDateString(),
                'end' => $fertileEnd->toDateString(),
            ],
        ];
    }
}