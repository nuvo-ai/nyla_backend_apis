<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationDosage extends Model
{
    protected $fillable = [
        'medication_id',
        'strength',
        'form',
        'unit',
        'quantity',
        'frequency',
        'instructions',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Get the full dosage description
     */
    public function getFullDosageAttribute(): string
    {
        $parts = [];

        if ($this->quantity) {
            $parts[] = $this->quantity . $this->unit;
        } else {
            $parts[] = $this->strength;
        }

        $parts[] = $this->form;

        if ($this->frequency) {
            $parts[] = "($this->frequency)";
        }

        return implode(' ', $parts);
    }
}
