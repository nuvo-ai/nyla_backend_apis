<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medication extends Model
{
    protected $fillable = [
        'pharmacy_id',
        'medication_type_id',
        'name',
        'description',
        'stock',
        'price',
        'is_active',
        'manufacturer',
        'expiry_date',
        'batch_number',
        'low_stock_threshold',
    ];

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function medicationType(): BelongsTo
    {
        return $this->belongsTo(MedicationType::class);
    }

    public function dosages(): HasMany
    {
        return $this->hasMany(MedicationDosage::class);
    }
}
