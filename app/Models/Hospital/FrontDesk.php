<?php

namespace App\Models\Hospital;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FrontDesk extends Model
{
    protected $fillable = [
        'user_id',
        'hospital_id',
        'hospital_user_id',
        'shift',
        'department',
        'years_of_expirience',
    ];

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
