<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyActivity extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'user_id',
        'action',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User\User::class);
    }
}
