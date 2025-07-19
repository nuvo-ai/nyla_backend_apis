<?php

namespace App\Models\Pharmacy;

use App\Models\Pharmacy\Medication;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * Order status values: pending, processing, accepted, completed, delivered, dispensed, declined
     * Order priority values: urgent, normal
     */
    protected $fillable = [
        'pharmacy_id',
        'patient_id',
        'priority',
        'status',
        'total_price',
        'prescription_url',
        'created_by',
    ];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function medications()
    {
        return $this->hasManyThrough(Medication::class, OrderItem::class, 'order_id', 'id', 'id', 'medication_id');
    }
}
