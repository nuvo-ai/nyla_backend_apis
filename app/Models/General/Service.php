<?php

namespace App\Models\General;

use App\Models\Hospital\Hospital;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function hospitals(): BelongsToMany
    {
        return $this->belongsToMany(Hospital::class, 'hospital_services')
                    ->withTimestamps();
    }

     public function pharmacies(): BelongsToMany
    {
        return $this->belongsToMany(Hospital::class, 'pharcies_services')
                    ->withTimestamps();
    }
}
