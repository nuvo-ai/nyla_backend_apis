<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'last_period_start_date',
        'cycle_length',
        'period_length',
    ];

    protected $casts = [
        'cycle_length' => 'integer',
        'period_length' => 'integer',
        'last_period_start_date' => 'date',
    ];
}
