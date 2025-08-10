<?php

namespace App\Services\Hospital\Analytic;

use App\Models\General\Department;
use App\Models\Hospital\HospitalAppointment;
use App\Models\Hospital\HospitalPatient;
use App\Models\Hospital\HospitalUser;
use App\Models\User\User;
use Carbon\Carbon;

class HospitalAnalyticsService
{

    public function getAnalytics(array $data = [])
    {
        $period = $data["period"] ?? 'month';

        if (!in_array($period, ['day', 'week', 'month', 'year'])) {
            $period = 'month';
        }

        $stats = $this->getAnalyticData($period);

        return [
            "cards" => [
                [
                    "title" => "Total Patients",
                    "value" => array_sum($stats['totalPatients']),
                    "percentage_change" => $stats['totalPatientsChange'],
                    "class" => "primary",
                    'period' =>  $period,
                ],
                [
                    "title" => "Appointments",
                    "value" => array_sum($stats['appointments']),
                    "percentage_change" => $stats['appointmentsChange'],
                    "class" => "primary",
                    'period' =>  $period,
                ],
                [
                    "title" => "Active Staffs",
                    "value" => array_sum($stats['activeStaffs']),
                    "percentage_change" => $stats['staffsChange'],
                    "class" => "info",
                    'period' =>  $period,
                ],
            ],

            "dashboard_data" => $stats,
        ];
    }

    public function getAnalyticData($period = 'month')
    {
        switch ($period) {
            case 'day':
                $currentStartDate = Carbon::today();
                $previousStartDate = Carbon::yesterday();
                $interval = 'hour';
                $dataPoints = 24;
                break;
            case 'week':
                $currentStartDate = Carbon::now()->startOfWeek();
                $previousStartDate = Carbon::now()->subWeek()->startOfWeek();
                $interval = 'day';
                $dataPoints = 7;
                break;
            case 'month':
                $currentStartDate = Carbon::now()->startOfMonth();
                $previousStartDate = Carbon::now()->subMonth()->startOfMonth();
                $interval = 'day';
                $dataPoints = Carbon::now()->daysInMonth;
                break;
            case 'year':
                $currentStartDate = Carbon::now()->startOfYear();
                $previousStartDate = Carbon::now()->subYear()->startOfYear();
                $interval = 'month';
                $dataPoints = 12;
                break;
            default:
                $currentStartDate = Carbon::now()->startOfMonth();
                $previousStartDate = Carbon::yesterday();
                $interval = 'day';
                $dataPoints = Carbon::now()->daysInMonth;
                break;
        }

        $current = $this->fetchHospitalData($currentStartDate, $interval, $dataPoints);
        $previous = $this->fetchHospitalData($previousStartDate, $interval, $dataPoints);

        return [
            'totalPatients' => $current['total_patients'],
            'appointments' => $current['appointments'],
            'activeStaffs' => $current['active_staffs'],
            'departmentDistribution' => $current['department_distribution'],

            // Change rates (percent differences)
            'totalPatientsChange' => $this->calculateChangeRate($current['total_patients'], $previous['total_patients']),
            'appointmentsChange' => $this->calculateChangeRate($current['appointments'], $previous['appointments']),
            'staffsChange' => $this->calculateChangeRate($current['active_staffs'], $previous['active_staffs']),
        ];
    }
    private function fetchHospitalData($startDate, $interval, $dataPoints)
    {
        $patients = array_fill(0, $dataPoints, 0);
        $appointments = array_fill(0, $dataPoints, 0);
        $active_staffs = array_fill(0, $dataPoints, 0);

        for ($i = 0; $i < $dataPoints; $i++) {
            $start = $startDate->copy()->add($i, $interval);
            $end = $start->copy()->endOf($interval);

            $patients[$i] = HospitalPatient::where('user_id', User::getAuthenticatedUser()?->hospitalUser?->user_id)->whereBetween('created_at', [$start, $end])->count();
            $appointments[$i] = HospitalAppointment::where('user_id', User::getAuthenticatedUser()?->hospitalUser?->user_id)->whereBetween('created_at', [$start, $end])->count();
            $active_staffs[$i] = HospitalUser::where('user_id', User::getAuthenticatedUser()?->hospitalUser?->user_id)->whereBetween('created_at', [$start, $end])->count();
        }

        $departments = Department::whereHas('hospitals.patients', function ($query) use ($startDate) {
            $query->whereBetween('created_at', [$startDate, now()]);
        })->withCount(['hospitals as patient_count' => function ($query) use ($startDate) {
            $query->whereHas('patients', function ($q) use ($startDate) {
                $q->whereBetween('created_at', [$startDate, now()]);
            });
        }])->pluck('patient_count', 'name')
            ->toArray();

        return [
            'total_patients' => $patients,
            'appointments' => $appointments,
            'active_staffs' => $active_staffs,
            'department_distribution' => $departments,
        ];
    }
    private function calculateChangeRate(array $currentData, array $previousData)
    {
        $current = array_sum($currentData);
        $previous = array_sum($previousData);

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
