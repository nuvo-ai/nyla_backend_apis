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
        return $this->belongsTo(Doctor::class);
    }


    public function scheduler()
    {
        return $this->belongsTo(User::class, 'scheduler_id');
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class);
    }

    public static function hasConflict(array $data, bool $checkPatient = false, ?int $excludeId = null): bool
    {
        $query = self::where('hospital_id', $data['hospital_id'] ?? null)
            ->where('doctor_id', $data['doctor_id'] ?? null)
            ->where('appointment_date', $data['appointment_date'] ?? null)
            ->where('appointment_time', $data['appointment_time'] ?? null)
            ->whereIn('status', [
                StatusConstants::PENDING,
                StatusConstants::SCHEDULED,
            ]);

        // Exclude the current appointment when updating
        if (!empty($excludeId)) {
            $query->where('id', '!=', $excludeId);
        }

        // Check patient/scheduler conflicts if required
        if ($checkPatient) {
            $query->where(function ($q) use ($data) {
                $q->where('patient_name', $data['patient_name'] ?? null)
                    ->orWhere('scheduler_id', $data['scheduler_id'] ?? null);
            });
        }

        return $query->exists();
    }
}
