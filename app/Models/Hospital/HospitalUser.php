<?php

namespace App\Models;

use App\Models\Hospital\Hospital;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class HospitalUser extends Model
{
    protected $fillable = [
        'user_id',
        'hospital_id',
        'role',
        'user_account_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hospital()
    {
        return $this->belongsTo(Hospital::class, 'hospital_id');
    }
}
