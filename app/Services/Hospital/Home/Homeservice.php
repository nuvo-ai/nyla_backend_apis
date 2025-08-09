<?php

namespace App\Services\Hospital\Home;

use App\Http\Resources\Hospital\AppointmentResource;
use App\Models\Hospital\HospitalAppointment;
use App\Models\Hospital\HospitalPatient;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Homeservice
{
    public function getData()
    {
        return [
            'stats' => $this->stats(),
            'today_appointments' => $this->getRecentAppointments(),
        ];
    }

    private function stats()
    {
        $hospital = User::getAuthenticatedUser()->hospitalUser->hospital;
        if (!$hospital) {
            return [];
        }
        $currentStartDate = Carbon::now()->startOfMonth();
        $previousStartDate = Carbon::now()->subMonth()->startOfMonth();
        $currentEndDate = Carbon::now()->endOfMonth();
        $previousEndDate = Carbon::now()->subMonth()->endOfMonth();
        $today = Carbon::today();

        // Monthly patient count for this and previous month
        $currentMonthPatients = HospitalPatient::where('hospital_id', $hospital?->id)->whereBetween('created_at', [$currentStartDate, $currentEndDate])->count();
        $previousMonthPatients = HospitalPatient::where('hospital_id', $hospital?->id)->whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();

        // Patients registered today
        $todayAppointments = HospitalAppointment::where('hospital_id', $hospital?->id)
            ->whereDate('appointment_date', $today)
            ->count();

        $yesterdayAppointments = HospitalAppointment::where('hospital_id', $hospital?->id)
            ->whereDate('appointment_date', Carbon::yesterday())
            ->count();

        // Active staff count (example logic)
        $currentMonthStaffs = HospitalPatient::where('hospital_id', $hospital?->id)->whereBetween('created_at', [$currentStartDate, $currentEndDate])->count(); // Replace with Staff model if applicable
        $previousMonthStaffs = HospitalPatient::where('hospital_id', $hospital?->id)->whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();

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
        $hospital = User::getAuthenticatedUser()->hospitalUser->hospital;
        if (!$hospital) {
            return [];
        }
        $appointments = HospitalAppointment::whereDate('appointment_date', now()->toDateString())
            ->with(['hospital', 'scheduler, doctor'])->where('hospital_id', $hospital->id)
            ->orderBy('appointment_time')
            ->get();
        return AppointmentResource::collection($appointments);
    }

    private function calculatePercentageChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }
}
