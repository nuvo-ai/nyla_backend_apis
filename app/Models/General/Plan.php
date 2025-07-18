<?php

namespace App\Models\General;

<<<<<<< HEAD
use Carbon\Carbon;
=======
>>>>>>> bc7f4eef305818723e0ac99546f1c100084b8135
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

    public function getPlanEndsAt()
    {
        return match ($this->interval) {
            'monthly' => Carbon::now()->addMonth(),
            'annually' => Carbon::now()->addYear(),
            default => Carbon::now(),
        };
    }
}
