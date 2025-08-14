<?php

namespace App\Services\Hospital\Doctor;

use App\Models\Hospital\Doctor;
use App\Models\Hospital\HospitalAppointment;
use App\Models\Hospital\HospitalPatient;
use App\Models\Hospital\Prescription;
use App\Models\User\User;
use Carbon\Carbon;

class DashboardStatsService
{
    protected $authUser;
    protected $hospital;
    protected $doctorId;
    protected $dateRange;

    public function __construct()
    {
        $this->authUser = User::getAuthenticatedUser();
        $this->hospital = $this->authUser->hospitalUser->hospital ?? null;
        $this->doctorId = $this->hospital
        ? Doctor::where('user_id', $this->authUser->id)
            ->where('hospital_id', $this->hospital->id)
            ->value('id')
        : null;
        $this->dateRange = [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()];
    }

    public function getStats()
    {
        if (!$this->hospital) {
            return [];
        }

        $currentDayPatients = HospitalPatient::where('hospital_id', $this->hospital->id)
            ->whereBetween('created_at', $this->dateRange)
            ->count();

        $completedAppointments = HospitalPatient::where('hospital_id', $this->hospital->id)
            ->where('doctor_id', $this->doctorId)
            ->where('status', 'completed')
            ->whereBetween('created_at', $this->dateRange)
            ->count();

        $pendingAppointments = HospitalPatient::where('hospital_id', $this->hospital->id)
            ->where('doctor_id', $this->doctorId)
            ->where('status', 'pending')
            ->whereBetween('created_at', $this->dateRange)
            ->count();

        $prescriptionCount = Prescription::where('doctor_id', $this->doctorId)
            ->whereBetween('created_at', $this->dateRange)
            ->count();

        return [
            'doctor_dashboard_data' => [
                'stats' => [
                    'current_day_patients' => $currentDayPatients,
                    'completed_appointments' => $completedAppointments,
                    'pending_appointments' => $pendingAppointments,
                    'prescriptions_count' => $prescriptionCount,
                ],
                'recent_patients' => $this->getRecentPatients(),
                'today_appointments' => $this->getTodayAppointments(),
            ],
        ];
    }

    public function getTodayAppointments()
    {
        if (!$this->hospital) {
            return [];
        }

       return HospitalAppointment::with(['hospital', 'doctor', 'scheduler'])
            ->where('hospital_id', $this->hospital->id)
            ->where('doctor_id', $this->doctorId)
            ->whereBetween('appointment_date', $this->dateRange)
            ->get();
    }

    public function getRecentPatients($limit = 10)
    {
        if (!$this->hospital) {
            return [];
        }

        return HospitalPatient::with(['user', 'hospital', 'doctor'])
            ->where('hospital_id', $this->hospital->id)
            ->where('doctor_id', $this->doctorId)
            ->latest()
            ->take($limit)
            ->get();
    }
}
