<?php

namespace App\Models\Pharmacy;

use App\Models\General\OperatingHour;
use App\Models\General\Service;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pharmacy extends Model
{
    protected $guarded = [];

    protected $table = 'pharmacies';

    protected $casts = [
        'terms_accepted' => 'boolean',
        'request_onsite_setup' => 'boolean',
        'number_of_beds' => 'integer',
        'is_active' => 'boolean',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(PharmacyContact::class);
    }


    public function primaryContact(): HasOne
    {
        return $this->hasOne(PharmacyContact::class)->where('type', 'primary');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'pharmacy_services')
            ->withTimestamps();
    }

    public function operatingHours(): HasMany
    {
        return $this->hasMany(OperatingHour::class, 'pharmacy_id');
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

    public function getNafdacCertificateUrlAttribute()
    {
        return $this->nafdac_certificate ? asset('storage/' . $this->nafdac_certificate) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
