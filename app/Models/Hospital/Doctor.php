<?php

namespace App\Models\Hospital;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Doctor extends Model
{
    protected $casts = [
        'departments' => 'array',
        'medical_specialties' => 'array',
    ];

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }

    public function hospitalUser(): BelongsTo
    {
        return $this->belongsTo(HospitalUser::class);
    }
}
