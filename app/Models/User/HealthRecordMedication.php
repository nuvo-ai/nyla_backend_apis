<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class HealthRecordMedication extends Model
{
    protected $fillable = [
        'health_record_id',
        'name',
        'dosage', // e.g. 500mg
        'purpose', // e.g. Pain relief, Blood pressure control
    ];

    public function healthRecord()
    {
        return $this->belongsTo(HealthRecord::class);
    }
}
