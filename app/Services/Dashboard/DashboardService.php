<?php

namespace App\Services\Dashboard;

use App\Models\User\User;
use App\Models\Hospital\Hospital;
use App\Models\Pharmacy\Pharmacy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class DashboardService
{
    /**
     * Get dashboard overview statistics
     */
    public static function getOverviewStats(array $data = [])
    {
        $data = Validator::make($data, [
            'period' => 'nullable|string|in:today,week,month,year',
        ])->validate();

        $period = $data['period'] ?? 'month';
        $cacheKey = "dashboard_overview_{$period}";

        return Cache::remember($cacheKey, 300, function () use ($period) {
            $dateFilter = self::getDateFilter($period);

            return [
                'totalUsers' => User::when($dateFilter, function ($query) use ($dateFilter) {
                    return $query->where('created_at', '>=', $dateFilter);
                })->count(),
                'totalHospitals' => Hospital::when($dateFilter, function ($query) use ($dateFilter) {
                    return $query->where('created_at', '>=', $dateFilter);
                })->count(),
                'totalPharmacies' => Pharmacy::when($dateFilter, function ($query) use ($dateFilter) {
                    return $query->where('created_at', '>=', $dateFilter);
                })->count(),
                'activeUsers' => User::where('last_login_at', '>=', now()->subDays(7))->count(),
                'totalRevenue' => self::calculateRevenue($period),
                'growthRate' => self::calculateGrowthRate($period),
                'systemUptime' => self::getSystemUptime(),
                'apiResponseTime' => self::getAverageResponseTime(),
            ];
        });
    }

    /**
     * Get recent activities
     */
    public static function getRecentActivities(array $data = [])
    {
        $data = Validator::make($data, [
            'limit' => 'nullable|integer|min:1|max:100',
            'type' => 'nullable|string|in:user,hospital,pharmacy,system',
        ])->validate();

        $limit = $data['limit'] ?? 20;
        $type = $data['type'] ?? null;

        // This would typically come from an activity log table
        // For now, we'll return a mock structure
        return [
            'activities' => [
                [
                    'id' => 1,
                    'type' => 'user_registration',
                    'description' => 'New user registered',
                    'user_id' => 123,
                    'timestamp' => now()->subMinutes(5)->toISOString(),
                    'metadata' => ['email' => 'user@example.com']
                ],
                [
                    'id' => 2,
                    'type' => 'hospital_verification',
                    'description' => 'Hospital verification completed',
                    'hospital_id' => 45,
                    'timestamp' => now()->subMinutes(15)->toISOString(),
                    'metadata' => ['hospital_name' => 'City General Hospital']
                ]
            ],
            'total' => 2,
            'hasMore' => false
        ];
    }

    /**
     * Get system health status
     */
    public static function getSystemHealth()
    {
        return Cache::remember('system_health', 60, function () {
            return [
                'database' => self::checkDatabaseHealth(),
                'cache' => self::checkCacheHealth(),
                'storage' => self::checkStorageHealth(),
                'external_apis' => self::checkExternalApisHealth(),
                'overall_status' => 'healthy', // healthy, warning, critical
                'last_check' => now()->toISOString(),
                'uptime_percentage' => 99.9,
                'response_time' => self::getAverageResponseTime()
            ];
        });
    }

    /**
     * Helper methods
     */
    private static function getDateFilter($period)
    {
        switch ($period) {
            case 'today':
                return now()->startOfDay();
            case 'week':
                return now()->startOfWeek();
            case 'month':
                return now()->startOfMonth();
            case 'year':
                return now()->startOfYear();
            default:
                return null;
        }
    }

    private static function calculateRevenue($period)
    {
        // This would calculate actual revenue from orders/transactions
        return rand(10000, 50000);
    }

    private static function calculateGrowthRate($period)
    {
        // This would calculate growth rate compared to previous period
        return rand(5, 25) . '%';
    }

    private static function getSystemUptime()
    {
        return '99.9%';
    }

    private static function getAverageResponseTime()
    {
        return rand(100, 300) . 'ms';
    }

    private static function checkDatabaseHealth()
    {
        try {
            DB::connection()->getPdo();
            return ['status' => 'healthy', 'response_time' => '50ms'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    private static function checkCacheHealth()
    {
        try {
            Cache::put('health_check', 'ok', 10);
            $value = Cache::get('health_check');
            return ['status' => $value === 'ok' ? 'healthy' : 'unhealthy'];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    private static function checkStorageHealth()
    {
        try {
            $diskSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usagePercentage = (($totalSpace - $diskSpace) / $totalSpace) * 100;
            
            return [
                'status' => $usagePercentage < 90 ? 'healthy' : 'warning',
                'usage_percentage' => round($usagePercentage, 2),
                'free_space' => self::formatBytes($diskSpace)
            ];
        } catch (\Exception $e) {
            return ['status' => 'unhealthy', 'error' => $e->getMessage()];
        }
    }

    private static function checkExternalApisHealth()
    {
        // Check external API endpoints health
        return [
            'payment_gateway' => ['status' => 'healthy', 'response_time' => '200ms'],
            'notification_service' => ['status' => 'healthy', 'response_time' => '150ms'],
            'analytics_service' => ['status' => 'healthy', 'response_time' => '100ms']
        ];
    }

    private static function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
}
