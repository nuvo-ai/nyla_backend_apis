<?php

namespace App\Models\Hospital;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrescriptionMedication extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id',
        'name',
        'dosage',
        'frequency',
        'duration',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }
}
