<?php

namespace App\Models\General;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    protected $fillable = [
        'uuid',
        'user_id',
        'plan_id',
        'starts_at',
        'ends_at',
        'subscription_code',
        'email_token',
        'customer_code',
        'meta',
        'payment_gateway_id',
        'payment_method',
        'authorization_reusable',
        'next_payment_date',
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
    public function isExpired()
    {
        return $this->status === 'is_expired' && $this->ends_at && $this->ends_at->isPast();
    }
}
