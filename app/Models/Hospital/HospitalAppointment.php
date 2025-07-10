<?php

namespace App\Models\Hospital;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class HospitalAppointment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'appointment_date' => 'date',
        'appointment_time' => 'datetime:H:i',
    ];

    public function doctor()
    {
        return $this->belongsTo(HospitalUser::class, 'doctor_id')->with('user');
    }


    public function scheduler()
    {
        return $this->belongsTo(User::class, 'scheduler_id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }
}
