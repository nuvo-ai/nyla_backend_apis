<?php

namespace App\Services\Pharmacy;

use App\Models\Pharmacy\PharmacyActivity;

class PharmacyActivityService
{
    public static function log($pharmacy_id, $user_id, $action, $meta = null)
    {
        return PharmacyActivity::create([
            'pharmacy_id' => $pharmacy_id,
            'user_id' => $user_id,
            'action' => $action,
            'meta' => $meta,
        ]);
    }
}
