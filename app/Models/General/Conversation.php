<?php

namespace App\Models\General;

use App\Models\Hospital\HospitalPatient;
use App\Models\User\User;
use App\Models\Hospital\HospitalUser;
use App\Models\Hospital\Patient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Conversation extends Model
{
    protected $fillable = ['user_id', 'hospital_user_id', 'patient_id', 'ai_type', 'title', 'uuid'];

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hospitalUser()
    {
        return $this->belongsTo(HospitalUser::class);
    }

    public function patient()
    {
        return $this->belongsTo(HospitalPatient::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
