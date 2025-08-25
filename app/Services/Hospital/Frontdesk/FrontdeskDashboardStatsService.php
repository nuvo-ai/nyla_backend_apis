<?php

namespace App\Services\Hospital\Frontdesk;

use App\Http\Resources\Hospital\AppointmentResource;
use App\Http\Resources\Hospital\EMRResource;
use App\Models\Hospital\HospitalAppointment;
use App\Models\Hospital\HospitalPatient;
use App\Models\Hospital\HospitalEMR;
use App\Models\User\User;
use Carbon\Carbon;

class FrontdeskDashboardStatsService
{
    protected $authUser;
    protected $hospital;
    protected $dateRange;

    public function __construct()
    {
        $this->authUser = User::getAuthenticatedUser();
        $this->hospital = $this->authUser?->hospitalUser?->hospital ?? null;
        $this->dateRange = [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()];
    }

    public function getStats()
    {
        if (!$this->hospital) {
            return [];
        }

        // Count patients that are still active
        $activePatients = HospitalPatient::where('hospital_id', $this->hospital?->id)
            ->where('status', 'active')
            ->count();

        // Today's appointments
        $todaysAppointments = HospitalAppointment::where('hospital_id', $this->hospital?->id)
            ->whereBetween('appointment_date', $this->dateRange)
            ->count();

        // Total EMR records in hospital
        $totalEmrRecords = HospitalEMR::where('hospital_id', $this->hospital?->id)->count();

        // Discharged patients
        $dischargedPatients = HospitalPatient::where('hospital_id', $this->hospital?->id)
            ->where('status', 'discharged')
            ->count();

        return [
            'frontdesk_dashboard_data' => [
                'stats' => [
                    'active_patients'       => $activePatients,
                    'todays_appointments'   => $todaysAppointments,
                    'total_emr_records'     => $totalEmrRecords,
                    'discharged_patients'   => $dischargedPatients,
                ],
                'today_appointments' => $this->getTodayAppointments(),
                'recent_emr_records' => $this->getRecentEmrRecords(),
            ],
        ];
    }

    public function getTodayAppointments()
    {
        if (!$this->hospital) {
            return [];
        }

        $appointments = HospitalAppointment::with(['hospital', 'doctor', 'scheduler'])
            ->where('hospital_id', $this->hospital->id)
            ->whereBetween('appointment_date', $this->dateRange)
            ->latest()
            ->get();

        return AppointmentResource::collection($appointments);
    }


    public function getRecentEmrRecords($limit = 5)
    {
        if (!$this->hospital) {
            return [];
        }

        $emrs = HospitalEmr::with(['patient.user', 'hospital'])
            ->where('hospital_id', $this->hospital->id)
            ->latest()
            ->take($limit)
            ->get();

        return EMRResource::collection($emrs);
    }
}
