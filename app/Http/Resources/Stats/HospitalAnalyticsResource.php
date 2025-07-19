<?php

namespace App\Http\Resources\Stats;

use Illuminate\Http\Resources\Json\JsonResource;

class HospitalAnalyticsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'cards' => $this['cards'], // Patient, Appointment, Staff card metrics
            'dashboard_data' => [
                'totalPatients' => $this['dashboard_data']['totalPatients'],
                'appointments' => $this['dashboard_data']['appointments'],
                'activeStaffs' => $this['dashboard_data']['activeStaffs'],
                'departmentDistribution' => $this['dashboard_data']['departmentDistribution'],
                'graph' => [
                    'labels' => $this->generateLabels($request->period ?? 'month'),
                    'patientVolume' => $this['dashboard_data']['totalPatients'],
                ]
            ]
        ];
    }

    private function generateLabels($period)
    {
        switch ($period) {
            case 'day':
                return range(0, 23); // hours
            case 'week':
                return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            case 'month':
                return range(1, now()->daysInMonth); // 1 to 30/31
            case 'year':
                return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            default:
                return [];
        }
    }
}
