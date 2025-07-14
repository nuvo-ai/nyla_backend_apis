<?php

namespace App\Models;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    protected $casts = [
        'email' => 'boolean',
        'sms' => 'boolean',
        'push' => 'boolean',
        'appointment_reminders' => 'boolean',
    ];
    protected $fillable = ['email', 'sms', 'push', 'appointment_reminders'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
