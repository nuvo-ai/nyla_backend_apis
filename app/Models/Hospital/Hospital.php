<?php

namespace App\Models\Hospital;

use App\Models\General\Service;
use App\Models\General\Department;
use App\Models\General\OperatingHour;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Hospital extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'request_onsite_setup' => 'boolean',
        'terms_accepted' => 'boolean',
        'number_of_beds' => 'integer'
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(HospitalContact::class);
    }

    public function primaryContact(): HasOne
    {
        return $this->hasOne(HospitalContact::class)->where('type', 'primary');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'hospital_departments')
                    ->withTimestamps();
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'hospital_services')
                    ->withTimestamps();
    }

    public function operatingHours(): HasMany
    {
        return $this->hasMany(OperatingHour::class, 'hospital_id');
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function getLicenseUrlAttribute()
    {
        return $this->license_path ? asset('storage/' . $this->license_path) : null;
    }
     
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
