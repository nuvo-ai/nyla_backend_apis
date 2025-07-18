<?php

namespace App\Models\Hospital;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HospitalContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'hospital_id',
        'name',
        'email',
        'phone',
        'role',
        'type'
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(Hospital::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
