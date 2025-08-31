<?php

namespace App\Models\User;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use ApiPlatform\Metadata\ApiResource;
use App\Models\General\ModulePreference;
use App\Models\General\Subscription;
use App\Models\Hospital\Doctor;
use App\Models\Hospital\FrontDesk;
use App\Models\Hospital\Hospital;
use App\Models\Hospital\HospitalContact;
use App\Models\Hospital\HospitalPatient;
use App\Models\Hospital\HospitalUser;
use App\Models\Hospital\LabTechnician;
use App\Models\NotificationPreference;
use App\Models\Pharmacy\Pharmacy;
use App\Models\Portal;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

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

    public function getAvatarAttribute($avatar)
    {
        return $avatar ? asset('storage/' . $avatar) : null;
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
    public function hospitals()
    {
        return $this->hasMany(Hospital::class, 'user_id');
    }

    public function hospitalUser()
    {
        return $this->hasOne(HospitalUser::class);
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

    public function notificationPreference()
    {
        return $this->hasOne(NotificationPreference::class);
    }

    protected static function booted()
    {
        static::created(function ($user) {
            $user->notificationPreference()->create([
                'email' => true,
                'sms' => false,
                'push' => false,
                'appointment_reminders' => false,
            ]);
        });
    }

    public function patient()
    {
        return $this->hasOne(HospitalPatient::class, 'user_id');
    }

    public function pharmacy()
    {
        return $this->hasOne(Pharmacy::class, 'user_id');
    }
    public static function getAuthenticatedUser()
    {
        return Auth::user()->load('hospitalUser.hospital');
    }

    public function modulePreferences()
    {
        return $this->belongsToMany(ModulePreference::class, 'user_module_preferences');
    }

    public function medicationReminders()
    {
        return $this->hasMany(MedicationReminder::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function latestSubscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function hasActiveSubscription()
    {
        $subscription = $this->latestSubscription()->first();

        return $subscription && $subscription->status === 'active';
    }

    public function reactivateSubscription()
    {
        $subscription = $this->latestSubscription()->first();

        if ($subscription && $subscription->status !== 'active') {
            $subscription->update(['status' => 'active']);
        }

        return $subscription;
    }
}
