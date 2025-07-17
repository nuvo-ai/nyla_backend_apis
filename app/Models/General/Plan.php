<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    // use SoftDeletes;

    protected $table = 'plans';

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'plan_code',
        'interval',
        'amount',
        'currency_id',
        'description',
        'is_active',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function features()
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
