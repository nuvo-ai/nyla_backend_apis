<?php

namespace App\Models\General;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    protected $fillable = [
        'uuid',
        'user_id',
        'plan_id',
        'start_date',
        'end_date',
        'subscription_code',
        'email_token',
        'customer_code',
        'meta',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}