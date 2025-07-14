<?php

namespace App\Services\Hospital\Home;

use Carbon\Carbon;

class HomeStats
{

    // public function getHomePageStats(array $data = [])
    // {
    //     $period = $data["period"] ?? 'month';

    //     if (!in_array($period, ['day', 'week', 'month', 'year'])) {
    //         $period = 'month';
    //     }

    //     $stats = $this->getDashboardData($period);

    //     $data = [
    //         "cards" => [
    //             [
    //                 "title" => "Total Patients",
    //                 "value" => array_sum($stats['totalPatients']),
    //                 "class" => "primary",
    //                 'period' =>  $period,
    //             ],
    //             [
    //                 "title" => "Total Appointments",
    //                 "value" => array_sum($stats['totalAppointments']),
    //                 "class" => "primary",
    //                 'period' =>  $period,
    //             ],
    //             [
    //                 "title" => "Monthly Revenue",
    //                 "value" => array_sum($stats['monthlyRevenue']),
    //                 "class" => "primary",
    //                 'period' =>  $period,
    //             ],
    //             [
    //                 "title" => "Active Staffs",
    //                 "value" => array_sum($stats['activeStaffs']),
    //                 "class" => "primary",
    //                 'period' =>  $period,
    //             ],

    //         ],

    //         "dashboard_data" => $stats,
    //     ];

    //     return $data;
    // }

    // public function getDashboardData($period = 'month',)
    // {
    //     // Set the current period and previous period based on the selected period
    //     switch ($period) {
    //         case 'day':
    //             $currentStartDate = Carbon::today();
    //             $previousStartDate = Carbon::yesterday();
    //             $interval = 'hour';
    //             $dataPoints = 24; // 24 hours in a day
    //             break;
    //         case 'week':
    //             $currentStartDate = Carbon::now()->startOfWeek();
    //             $previousStartDate = Carbon::now()->subWeek()->startOfWeek();
    //             $interval = 'week';
    //             $dataPoints = 7; // 7 days in a week
    //             break;
    //         case 'month':
    //             $currentStartDate = Carbon::now()->startOfMonth();
    //             $previousStartDate = Carbon::now()->subMonth()->startOfMonth();
    //             $interval = 'month';
    //             $dataPoints = Carbon::now()->daysInMonth; // Days in the current month
    //             break;
    //         case 'year':
    //             $currentStartDate = Carbon::now()->startOfYear();
    //             $previousStartDate = Carbon::now()->subYear()->startOfYear();
    //             $interval = 'year';
    //             $dataPoints = 12; // 12 months in a year
    //             break;
    //         default:
    //             $currentStartDate = Carbon::now()->startOfMonth();
    //             $previousStartDate = Carbon::yesterday();
    //             $interval = 'day';
    //             $dataPoints = Carbon::now()->daysInMonth;
    //             break;
    //     }

    //     // Fetch the current and previous period data
    //     $currentData = $this->fetchData($currentStartDate, $interval, $dataPoints);
    //     $previousData = $this->fetchData($previousStartDate, $interval, $dataPoints);


    //     return [
    //         'totalPatients' => $currentData['total_patients'],
    //         'totalAppointments' => $currentData['total_appointments'],
    //         'monthlyRevenue' => $currentData['monthly_revenue'],
    //         'activeStaffs' => $currentData['active_staffs'],
    //     ];
    // }
    // private function fetchData($startDate, $interval, $dataPoints)
    // {
    //     $new_ongoing_reservations = array_fill(0, $dataPoints, 0);
    //     $total_completed_reservations = array_fill(0, $dataPoints, 0);
    //     $check_ins = array_fill(0, $dataPoints, 0);
    //     $checkout_outs = array_fill(0, $dataPoints, 0);

    //     for ($i = 0; $i < $dataPoints; $i++) {
    //         $startOfInterval = $startDate->copy()->add($i, $interval);
    //         $endOfInterval = $startOfInterval->copy()->endOf($interval);

    //         $room_reservation = RoomReservation::whereHas('room')->where('hotel_id', User::getAuthenticatedUser()->hotel->id)->get();

    //         $new_ongoing_reservations[$i] = $room_reservation->whereNull('checked_out_at')
    //             ->whereBetween('created_at', [$startOfInterval, $endOfInterval])
    //             ->count();

    //         $total_completed_reservations[$i] = $room_reservation->whereNotNull('checked_out_at')
    //             ->whereBetween('created_at', [$startOfInterval, $endOfInterval])
    //             ->count();

    //         $check_ins[$i] = $room_reservation->whereNotNull('checked_in_at')
    //             ->whereBetween('created_at', [$startOfInterval, $endOfInterval])
    //             ->count();

    //         $checkout_outs[$i] = $room_reservation->whereNotNull('checked_out_at')
    //             ->whereBetween('created_at', [$startOfInterval, $endOfInterval])
    //             ->count();
    //     }
    //     return [
    //         'ongoing_reservations' => $new_ongoing_reservations,
    //         'completed_reservations' => $total_completed_reservations,
    //         'checkin_reservations' => $check_ins,
    //         'checkout_reservations' => $checkout_outs,
    //     ];
    // }
}
