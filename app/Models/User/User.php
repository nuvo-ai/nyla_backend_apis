<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use ApiPlatform\Metadata\ApiResource;
use App\Models\Hospital\Doctor;
use App\Models\Hospital\FrontDesk;
use App\Models\Hospital\HospitalContact;
use App\Models\Hospital\HospitalUser;
use App\Models\Hospital\LabTechnician;
use App\Models\Portal;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function names()
    {
        return implode(' ', array_filter([$this->first_name, $this->middle_name, $this->last_name]));
    }

    public function getFullNameAttribute()
    {
        return $this->names();
    }

    public function hospitalContact()
    {
        return $this->belongsTo(HospitalContact::class);
    }

    public function portal()
    {
        return $this->belongsTo(Portal::class, 'portal_id');
    }

    public function hospitalUser()
    {
        return $this->hasOne(HospitalUser::class, 'user_id');
    }

    public function labTechnician()
    {
        return $this->hasOne(LabTechnician::class);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function frontDesk()
    {
        return $this->hasOne(FrontDesk::class);
    }
}
