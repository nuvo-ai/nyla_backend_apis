<?php

namespace App\Models\General;

use App\Models\Hospital\Hospital;
use App\Models\Pharmacy\Pharmacy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperatingHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_closed'
    ];

    protected $casts = [
        'is_closed' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i'
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }
}
