<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class MedicalCondition extends Model
{
    protected $fillable = [
        'health_record_id',
        'name',
        'diagnosed_year', // e.g. 2020
    ];

    public function healthRecord()
    {
        return $this->belongsTo(HealthRecord::class);
    }
}
