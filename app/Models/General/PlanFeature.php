<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;

class PlanFeature extends Model
{
    protected $table = 'plan_features';

    protected $fillable = [
        'plan_id',
        'name',
        'description',
        'is_active',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}