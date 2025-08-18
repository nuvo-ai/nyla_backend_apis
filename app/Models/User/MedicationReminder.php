<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class MedicationReminder extends Model
{
     protected $casts = [
        'is_active' => 'boolean'
    ];
    protected $fillable = [
        'user_id',
        'name',
        'dosage', // e.g. 500mg
        'frequency', // e.g. once daily, twice daily
        'time',
        'is_active',
        'notes', // Additional notes for the reminder
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
