<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationType extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'pharmacy_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class);
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Pharmacy\Pharmacy::class);
    }
}
