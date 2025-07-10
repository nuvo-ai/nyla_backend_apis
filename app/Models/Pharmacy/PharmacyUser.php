<?php

namespace App\Models\Pharmacy;

use App\Models\Pharmacy\Pharmacy;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class PharmacyUser extends Model
{
    protected $fillable = [
        'user_id',
        'pharmacy_id',
        'role',
        'user_account_id',
    ];
     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hospital()
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }
}
