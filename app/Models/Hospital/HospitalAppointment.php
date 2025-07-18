<?php

namespace App\Models\Hospital;

use App\Constants\General\StatusConstants;
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

    public static function hasConflict(array $data, bool $checkPatient = false): bool
    {
        $query = self::where('hospital_id', $data['hospital_id'])
            ->where('doctor_id', $data['doctor_id'])
            ->where('appointment_date', $data['appointment_date'])
            ->where('appointment_time', $data['appointment_time'])
            ->whereIn('status', [
                StatusConstants::PENDING,
                StatusConstants::SCHEDULED,
            ]);

        if ($checkPatient) {
            $query->where(function ($q) use ($data) {
                $q->where('patient_name', $data['patient_name'])
                    ->orWhere('scheduler_id', $data['scheduler_id']);
            });
        }
        return $query->exists();
    }
}
