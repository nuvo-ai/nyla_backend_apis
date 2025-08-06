<?php

namespace App\Models\General;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class UserModulePreference extends Model
{
    protected $fillable = [
        'user_id',
        'module_preference_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
