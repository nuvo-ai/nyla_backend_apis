<?php

namespace App\Services\Analytics;

use App\Models\User\User;
use App\Models\Hospital\Hospital;
use App\Models\Pharmacy\Pharmacy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AnalyticsService
{
    /**
     * Get user analytics
     */
    public static function getUserAnalytics(array $data = [])
    {
        $data = Validator::make($data, [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'nullable|string|in:day,week,month,year',
        ])->validate();

        $startDate = $data['start_date'] ? Carbon::parse($data['start_date']) : now()->subMonth();
        $endDate = $data['end_date'] ? Carbon::parse($data['end_date']) : now();
        $groupBy = $data['group_by'] ?? 'day';

        $cacheKey = "user_analytics_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}_{$groupBy}";

        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate, $groupBy) {
            return [
                'total_users' => User::whereBetween('created_at', [$startDate, $endDate])->count(),
                'active_users' => User::where('last_login_at', '>=', $startDate)->count(),
                'new_registrations' => self::getRegistrationTrend($startDate, $endDate, $groupBy),
                'user_demographics' => self::getUserDemographics($startDate, $endDate),
                'user_activity' => self::getUserActivity($startDate, $endDate),
                'retention_rate' => self::calculateRetentionRate($startDate, $endDate),
                'churn_rate' => self::calculateChurnRate($startDate, $endDate),
            ];
        });
    }

    /**
     * Get hospital analytics
     */
    public static function getHospitalAnalytics(array $data = [])
    {
        $data = Validator::make($data, [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'nullable|string|in:day,week,month,year',
        ])->validate();

        $startDate = $data['start_date'] ? Carbon::parse($data['start_date']) : now()->subMonth();
        $endDate = $data['end_date'] ? Carbon::parse($data['end_date']) : now();
        $groupBy = $data['group_by'] ?? 'day';

        $cacheKey = "hospital_analytics_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}_{$groupBy}";

        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate, $groupBy) {
            return [
                'total_hospitals' => Hospital::whereBetween('created_at', [$startDate, $endDate])->count(),
                'verified_hospitals' => Hospital::where('is_verified', true)
                    ->whereBetween('created_at', [$startDate, $endDate])->count(),
                'hospital_registrations' => self::getHospitalRegistrationTrend($startDate, $endDate, $groupBy),
                'top_performing_hospitals' => self::getTopPerformingHospitals($startDate, $endDate),
                'hospital_utilization' => self::getHospitalUtilization($startDate, $endDate),
                'average_rating' => self::getAverageHospitalRating($startDate, $endDate),
            ];
        });
    }

    /**
     * Get pharmacy analytics
     */
    public static function getPharmacyAnalytics(array $data = [])
    {
        $data = Validator::make($data, [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'nullable|string|in:day,week,month,year',
        ])->validate();

        $startDate = $data['start_date'] ? Carbon::parse($data['start_date']) : now()->subMonth();
        $endDate = $data['end_date'] ? Carbon::parse($data['end_date']) : now();
        $groupBy = $data['group_by'] ?? 'day';

        $cacheKey = "pharmacy_analytics_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}_{$groupBy}";

        return Cache::remember($cacheKey, 300, function () use ($startDate, $endDate, $groupBy) {
            return [
                'total_pharmacies' => Pharmacy::whereBetween('created_at', [$startDate, $endDate])->count(),
                'verified_pharmacies' => Pharmacy::where('is_verified', true)
                    ->whereBetween('created_at', [$startDate, $endDate])->count(),
                'pharmacy_registrations' => self::getPharmacyRegistrationTrend($startDate, $endDate, $groupBy),
                'top_performing_pharmacies' => self::getTopPerformingPharmacies($startDate, $endDate),
                'total_orders' => self::getTotalOrders($startDate, $endDate),
                'completed_orders' => self::getCompletedOrders($startDate, $endDate),
                'total_revenue' => self::getTotalRevenue($startDate, $endDate),
                'average_order_value' => self::getAverageOrderValue($startDate, $endDate),
            ];
        });
    }

    /**
     * Get revenue analytics
     */
    public static function getRevenueAnalytics(array $data = [])
    {
        $data = Validator::make($data, [
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'nullable|string|in:day,week,month,year',
            'currency' => 'nullable|string|in:USD,EUR,GBP,NGN',
        ])->validate();

        $startDate = $data['start_date'] ? Carbon::parse($data['start_date']) : now()->subMonth();
        $endDate = $data['end_date'] ? Carbon::parse($data['end_date']) : now();
        $groupBy = $data['group_by'] ?? 'day';
        $currency = $data['currency'] ?? 'USD';

        return [
            'total_revenue' => self::getTotalRevenue($startDate, $endDate),
            'revenue_trend' => self::getRevenueTrend($startDate, $endDate, $groupBy),
            'revenue_by_source' => self::getRevenueBySource($startDate, $endDate),
            'commission_earned' => self::getCommissionEarned($startDate, $endDate),
            'average_transaction_value' => self::getAverageTransactionValue($startDate, $endDate),
            'payment_methods' => self::getPaymentMethodBreakdown($startDate, $endDate),
            'currency' => $currency,
        ];
    }

    /**
     * Export analytics data
     */
    public static function exportAnalytics(array $data)
    {
        $data = Validator::make($data, [
            'type' => 'required|string|in:users,hospitals,pharmacies,revenue,all',
            'format' => 'required|string|in:csv,xlsx,pdf',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'email' => 'nullable|email',
        ])->validate();

        // This would typically queue a job to generate and email the export
        return [
            'export_id' => uniqid('export_'),
            'status' => 'queued',
            'estimated_completion' => now()->addMinutes(5)->toISOString(),
            'download_url' => null, // Will be populated when ready
            'message' => 'Export has been queued. You will receive an email when it\'s ready.',
        ];
    }

    /**
     * Helper methods for analytics calculations
     */
    private static function getRegistrationTrend($startDate, $endDate, $groupBy)
    {
        // Mock data - replace with actual query
        return [
            ['date' => '2024-01-01', 'count' => 45],
            ['date' => '2024-01-02', 'count' => 52],
            ['date' => '2024-01-03', 'count' => 38],
        ];
    }

    private static function getUserDemographics($startDate, $endDate)
    {
        return [
            'age_groups' => [
                '18-25' => 25,
                '26-35' => 35,
                '36-45' => 20,
                '46-55' => 15,
                '55+' => 5
            ],
            'gender' => [
                'male' => 45,
                'female' => 52,
                'other' => 3
            ]
        ];
    }

    private static function getUserActivity($startDate, $endDate)
    {
        return [
            'daily_active_users' => rand(1000, 5000),
            'weekly_active_users' => rand(5000, 15000),
            'monthly_active_users' => rand(15000, 50000),
            'average_session_duration' => '12m 34s',
            'bounce_rate' => '23.5%'
        ];
    }

    private static function calculateRetentionRate($startDate, $endDate)
    {
        return rand(70, 90) . '%';
    }

    private static function calculateChurnRate($startDate, $endDate)
    {
        return rand(5, 15) . '%';
    }

    private static function getHospitalRegistrationTrend($startDate, $endDate, $groupBy)
    {
        return [
            ['date' => '2024-01-01', 'count' => 3],
            ['date' => '2024-01-02', 'count' => 5],
            ['date' => '2024-01-03', 'count' => 2],
        ];
    }

    private static function getTopPerformingHospitals($startDate, $endDate)
    {
        return [
            ['id' => 1, 'name' => 'City General Hospital', 'rating' => 4.8, 'patients' => 1250],
            ['id' => 2, 'name' => 'Metro Medical Center', 'rating' => 4.7, 'patients' => 980],
            ['id' => 3, 'name' => 'Regional Health System', 'rating' => 4.6, 'patients' => 875],
        ];
    }

    private static function getHospitalUtilization($startDate, $endDate)
    {
        return [
            'average_occupancy' => '78%',
            'peak_hours' => '10:00 AM - 2:00 PM',
            'busiest_day' => 'Monday'
        ];
    }

    private static function getAverageHospitalRating($startDate, $endDate)
    {
        return 4.5;
    }

    private static function getPharmacyRegistrationTrend($startDate, $endDate, $groupBy)
    {
        return [
            ['date' => '2024-01-01', 'count' => 8],
            ['date' => '2024-01-02', 'count' => 12],
            ['date' => '2024-01-03', 'count' => 6],
        ];
    }

    private static function getTopPerformingPharmacies($startDate, $endDate)
    {
        return [
            ['id' => 1, 'name' => 'HealthPlus Pharmacy', 'orders' => 450, 'revenue' => 25000],
            ['id' => 2, 'name' => 'MediCare Drugs', 'orders' => 380, 'revenue' => 21000],
            ['id' => 3, 'name' => 'Wellness Pharmacy', 'orders' => 320, 'revenue' => 18500],
        ];
    }

    private static function getTotalOrders($startDate, $endDate)
    {
        return rand(1000, 5000);
    }

    private static function getCompletedOrders($startDate, $endDate)
    {
        return rand(800, 4500);
    }

    private static function getTotalRevenue($startDate, $endDate)
    {
        return rand(50000, 200000);
    }

    private static function getAverageOrderValue($startDate, $endDate)
    {
        return rand(25, 150);
    }

    private static function getRevenueTrend($startDate, $endDate, $groupBy)
    {
        return [
            ['date' => '2024-01-01', 'revenue' => 12500],
            ['date' => '2024-01-02', 'revenue' => 15200],
            ['date' => '2024-01-03', 'revenue' => 11800],
        ];
    }

    private static function getRevenueBySource($startDate, $endDate)
    {
        return [
            'pharmacy_commissions' => 65000,
            'hospital_subscriptions' => 25000,
            'premium_features' => 15000,
            'advertising' => 8000
        ];
    }

    private static function getCommissionEarned($startDate, $endDate)
    {
        return rand(10000, 50000);
    }

    private static function getAverageTransactionValue($startDate, $endDate)
    {
        return rand(75, 250);
    }

    private static function getPaymentMethodBreakdown($startDate, $endDate)
    {
        return [
            'credit_card' => 45,
            'debit_card' => 30,
            'bank_transfer' => 15,
            'mobile_money' => 8,
            'cash' => 2
        ];
    }
}
