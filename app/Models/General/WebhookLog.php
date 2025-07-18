<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $table = 'webhook_logs';

    protected $fillable = [
        'event',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}