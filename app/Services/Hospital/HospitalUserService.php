<?php

namespace App\Services\Hospital;

use App\Models\Hospital\HospitalUser;
use Illuminate\Support\Collection;

class HospitalUserService
{
    public function listHospitalUsers(array $filters = []): Collection
    {
        $query = HospitalUser::with('user');

        if (!empty($filters['status'])) {
            $status = strtolower($filters['status']);
            $query->whereHas('user', function ($q) use ($status) {
                $q->whereRaw('LOWER(status) = ?', [$status]);
            });
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }
        if (!empty($filters['hospital_id'])) {
            $query->where('hospital_id', $filters['hospital_id']);
        }
        return $query->get();
    }
}
