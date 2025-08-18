<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Allergy extends Model
{
    protected $fillable = [
        'health_record_id',
        'name',
        'severity', // e.g. High, Medium, Low
    ];

    public function healthRecord()
    {
        return $this->belongsTo(HealthRecord::class);
    }
}
