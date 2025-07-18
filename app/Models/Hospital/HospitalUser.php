<?php

namespace App\Models\Hospital;

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

    public function scopeSearch($query, $search)
    {
        return $query->whereHas('user', function ($q) use ($search) {
            $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"])
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
        })->orWhere('role', 'like', "%{$search}%");
    }

 public function labTechnician()
    {
        return $this->hasOne(LabTechnician::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function frontDesk()
    {
        return $this->hasOne(FrontDesk::class);
    }

}
