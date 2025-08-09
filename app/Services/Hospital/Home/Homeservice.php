<?php

namespace App\Services\Hospital\Home;

use App\Models\Hospital\HospitalAppointment;
use App\Models\Hospital\HospitalPatient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Homeservice
{
    public function getData()
    {
        return [
            'stats' => $this->stats(),
            'recent_appointments' => $this->getRecentAppointments(),
        ];
    }

    private function stats()
    {
        $currentStartDate = Carbon::now()->startOfMonth();
        $previousStartDate = Carbon::now()->subMonth()->startOfMonth();
        $currentEndDate = Carbon::now()->endOfMonth();
        $previousEndDate = Carbon::now()->subMonth()->endOfMonth();
        $today = Carbon::today();

        // Monthly patient count for this and previous month
        $currentMonthPatients = HospitalPatient::whereBetween('created_at', [$currentStartDate, $currentEndDate])->count();
        $previousMonthPatients = HospitalPatient::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();

        // Patients registered today
        $todayAppointments = HospitalAppointment::whereDate('created_at', $today)->count();
        $yesterdayAppointments = HospitalAppointment::whereDate('created_at', Carbon::yesterday())->count();

        // Active staff count (example logic)
        $currentMonthStaffs = HospitalPatient::whereBetween('created_at', [$currentStartDate, $currentEndDate])->count(); // Replace with Staff model if applicable
        $previousMonthStaffs = HospitalPatient::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();

        return [
            'total_monthly_patients' => [
                'count' => $currentMonthPatients,
                'percentage' => $this->calculatePercentageChange($currentMonthPatients, $previousMonthPatients),
            ],
            'today_appointments' => [
                'count' => $todayAppointments,
                'percentage' => $this->calculatePercentageChange($todayAppointments, $yesterdayAppointments),
            ],
            'active_staffs' => [
                'count' => $currentMonthStaffs,
                'percentage' => $this->calculatePercentageChange($currentMonthStaffs, $previousMonthStaffs),
            ],
        ];
    }


    private function getRecentAppointments()
    {
        $appointments = HospitalAppointment::where('created_at', '>=', Carbon::now()->today())
            ->latest('created_at')->limit(3)->get();
        return $appointments;
    }

    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }
}
