<?php

namespace App\Models\General;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class ModulePreference extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_module_preferences');
    }
}
