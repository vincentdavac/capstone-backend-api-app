<?php

namespace App\Http\Controllers;

use App\Models\Barangay;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use App\Models\Buoy;
use App\Models\User;
use App\Models\recent_alerts;



class AdminDashboardController extends Controller
{
    use HttpResponses;

public function userStats()
{
    $now = Carbon::now();

    // Week range (Mon–Sun)
    $weekStart = $now->copy()->startOfWeek();
    $weekEnd   = $now->copy()->endOfWeek();

    // Total REGISTERED users
    $totalUsers = User::where('user_type', 'barangay')
        ->where('registration_status', true)
        ->count();

    // REGISTERED users added this week
    $currentWeekCount = User::where('user_type', 'barangay')
        ->where('registration_status', true)
        ->whereBetween('created_at', [
            $weekStart,
            $weekEnd
        ])->count();

    /**
     * Average registered users per week
     */
    $firstUserDate = User::where('user_type', 'barangay')
        ->where('registration_status', true)
        ->min('created_at');

    if ($firstUserDate) {
        $totalWeeks = max(
            Carbon::parse($firstUserDate)->diffInWeeks($now),
            1
        );
        $averagePerWeek = $totalUsers / $totalWeeks;
    } else {
        $averagePerWeek = 0;
    }

    /**
     * Percentage of this week's registrations
     * relative to total registered users
     */
    $percentageOfTotal = $totalUsers > 0
        ? ($currentWeekCount / $totalUsers) * 100
        : 0;

    /**
     * Trend logic
     */
    $trend = $currentWeekCount >= $averagePerWeek ? 'up' : 'down';

    /**
     * Badge color logic
     */
    if ($totalUsers === 0) {
        $badgeColor = 'light';
    } elseif ($currentWeekCount === 0) {
        $badgeColor = 'warning';
    } elseif ($trend === 'up') {
        $badgeColor = 'success';
    } else {
        $badgeColor = 'error';
    }

    return $this->success([
        'total'               => $totalUsers,
        'current_week'        => $currentWeekCount,
        'average_per_week'    => round($averagePerWeek, 2),
        'percentage_of_total' => round($percentageOfTotal, 2),
        'trend'               => $trend,
        'badge_color'         => $badgeColor,
    ], 'Registered user statistics fetched successfully');
}

public function barangayStats()
{
    $now = Carbon::now();

    // Week range (Mon–Sun)
    $weekStart = $now->copy()->startOfWeek();
    $weekEnd   = $now->copy()->endOfWeek();

    // Total barangays
    $totalBarangays = Barangay::count();

    // Barangays added this week
    $currentWeekCount = Barangay::whereBetween('created_at', [
        $weekStart,
        $weekEnd
    ])->count();

    /**
     * Average barangays per week
     */
    $firstBarangayDate = Barangay::min('created_at');

    if ($firstBarangayDate) {
        $totalWeeks = max(
            Carbon::parse($firstBarangayDate)->diffInWeeks($now),
            1
        );
        $averagePerWeek = $totalBarangays / $totalWeeks;
    } else {
        $averagePerWeek = 0;
    }

    /**
     * Percentage of this week's additions
     * relative to total
     */
    $percentageOfTotal = $totalBarangays > 0
        ? ($currentWeekCount / $totalBarangays) * 100
        : 0;

    /**
     * Trend logic
     */
    $trend = $currentWeekCount >= $averagePerWeek ? 'up' : 'down';

    /**
     * Badge color logic
     */
    if ($totalBarangays === 0) {
        $badgeColor = 'light';
    } elseif ($currentWeekCount === 0) {
        $badgeColor = 'warning';
    } elseif ($trend === 'up') {
        $badgeColor = 'success';
    } else {
        $badgeColor = 'error';
    }

    return $this->success([
        'total'               => $totalBarangays,
        'current_week'        => $currentWeekCount,
        'average_per_week'    => round($averagePerWeek, 2),
        'percentage_of_total' => round($percentageOfTotal, 2),
        'trend'               => $trend,
        'badge_color'         => $badgeColor, // ← FRONTEND READY
    ], 'Barangay statistics fetched successfully');
}


public function buoyStats()
{
    $now = Carbon::now();

    // Week range (Mon–Sun)
    $weekStart = $now->copy()->startOfWeek();
    $weekEnd   = $now->copy()->endOfWeek();

    // Total ACTIVE buoys
    $totalActiveBuoys = Buoy::where('status', 'active')->count();

    // ACTIVE buoys added this week
    $currentWeekCount = Buoy::where('status', 'active')
        ->whereBetween('created_at', [
            $weekStart,
            $weekEnd
        ])->count();

    /**
     * Average active buoys per week
     */
    $firstBuoyDate = Buoy::where('status', 'active')->min('created_at');

    if ($firstBuoyDate) {
        $totalWeeks = max(
            Carbon::parse($firstBuoyDate)->diffInWeeks($now),
            1
        );
        $averagePerWeek = $totalActiveBuoys / $totalWeeks;
    } else {
        $averagePerWeek = 0;
    }

    /**
     * Percentage of this week's additions
     * relative to total active buoys
     */
    $percentageOfTotal = $totalActiveBuoys > 0
        ? ($currentWeekCount / $totalActiveBuoys) * 100
        : 0;

    /**
     * Trend logic
     */
    $trend = $currentWeekCount >= $averagePerWeek ? 'up' : 'down';

    /**
     * Badge color logic (same rules as Barangay)
     */
    if ($totalActiveBuoys === 0) {
        $badgeColor = 'light';
    } elseif ($currentWeekCount === 0) {
        $badgeColor = 'warning';
    } elseif ($trend === 'up') {
        $badgeColor = 'success';
    } else {
        $badgeColor = 'error';
    }

    return $this->success([
        'total'               => $totalActiveBuoys,
        'current_week'        => $currentWeekCount,
        'average_per_week'    => round($averagePerWeek, 2),
        'percentage_of_total' => round($percentageOfTotal, 2),
        'trend'               => $trend,
        'badge_color'         => $badgeColor,
    ], 'Active buoy statistics fetched successfully');
}

public function recentAlertsStats()
{
    $now = Carbon::now();

    // Week range (Mon–Sun)
    $weekStart = $now->copy()->startOfWeek();
    $weekEnd   = $now->copy()->endOfWeek();

    // Total alerts
    $totalAlerts = recent_alerts::count();

    // Alerts recorded this week (USING recorded_at)
    $currentWeekCount = recent_alerts::whereBetween('recorded_at', [
        $weekStart,
        $weekEnd
    ])->count();

    /**
     * Average alerts per week
     */
    $firstAlertDate = recent_alerts::min('recorded_at');

    if ($firstAlertDate) {
        $totalWeeks = max(
            Carbon::parse($firstAlertDate)->diffInWeeks($now),
            1
        );
        $averagePerWeek = $totalAlerts / $totalWeeks;
    } else {
        $averagePerWeek = 0;
    }

    /**
     * Percentage of this week's alerts
     * relative to total
     */
    $percentageOfTotal = $totalAlerts > 0
        ? ($currentWeekCount / $totalAlerts) * 100
        : 0;

    /**
     * Trend logic
     * (More alerts than average = up)
     */
    $trend = $currentWeekCount >= $averagePerWeek ? 'up' : 'down';

    /**
     * Badge color logic
     */
    if ($totalAlerts === 0) {
        // No data yet
        $badgeColor = 'light';

    } elseif ($currentWeekCount === 0) {
        // No alerts this week = GOOD
        $badgeColor = 'success';

    } elseif ($trend === 'up') {
        // Alerts increasing = BAD
        $badgeColor = 'error';

    } else {
        // Alerts decreasing = GOOD
        $badgeColor = 'success';
    }


    return $this->success([
        'total'               => $totalAlerts,
        'current_week'        => $currentWeekCount,
        'average_per_week'    => round($averagePerWeek, 2),
        'percentage_of_total' => round($percentageOfTotal, 2),
        'trend'               => $trend,
        'badge_color'         => $badgeColor,
    ], 'Recent alerts statistics fetched successfully');
}

public function dashboardStats()
{
    $now = Carbon::now();

    // Week range (Mon–Sun)
    $weekStart = $now->copy()->startOfWeek();
    $weekEnd   = $now->copy()->endOfWeek();

    /* ===================== USERS ===================== */
    $totalUsers = User::where('user_type', 'barangay')
        ->where('registration_status', true)
        ->count();

    $usersThisWeek = User::where('user_type', 'barangay')
        ->where('registration_status', true)
        ->whereBetween('created_at', [$weekStart, $weekEnd])
        ->count();

    $firstUserDate = User::where('user_type', 'barangay')
        ->where('registration_status', true)
        ->min('created_at');

    $userWeeks = $firstUserDate
        ? max(Carbon::parse($firstUserDate)->diffInWeeks($now), 1)
        : 1;

    $userAvg = $totalUsers / $userWeeks;
    $userTrend = $usersThisWeek >= $userAvg ? 'up' : 'down';

    $userBadge = $totalUsers === 0
        ? 'light'
        : ($usersThisWeek === 0
            ? 'warning'
            : ($userTrend === 'up' ? 'success' : 'error'));

    /* ===================== BARANGAYS ===================== */
    $totalBarangays = Barangay::count();
    $barangaysThisWeek = Barangay::whereBetween('created_at', [$weekStart, $weekEnd])->count();

    $firstBarangayDate = Barangay::min('created_at');
    $barangayWeeks = $firstBarangayDate
        ? max(Carbon::parse($firstBarangayDate)->diffInWeeks($now), 1)
        : 1;

    $barangayAvg = $totalBarangays / $barangayWeeks;
    $barangayTrend = $barangaysThisWeek >= $barangayAvg ? 'up' : 'down';

    $barangayBadge = $totalBarangays === 0
        ? 'light'
        : ($barangaysThisWeek === 0
            ? 'warning'
            : ($barangayTrend === 'up' ? 'success' : 'error'));

    /* ===================== BUOYS ===================== */
    $totalBuoys = Buoy::where('status', 'active')->count();
    $buoysThisWeek = Buoy::where('status', 'active')
        ->whereBetween('created_at', [$weekStart, $weekEnd])
        ->count();

    $firstBuoyDate = Buoy::where('status', 'active')->min('created_at');
    $buoyWeeks = $firstBuoyDate
        ? max(Carbon::parse($firstBuoyDate)->diffInWeeks($now), 1)
        : 1;

    $buoyAvg = $totalBuoys / $buoyWeeks;
    $buoyTrend = $buoysThisWeek >= $buoyAvg ? 'up' : 'down';

    $buoyBadge = $totalBuoys === 0
        ? 'light'
        : ($buoysThisWeek === 0
            ? 'warning'
            : ($buoyTrend === 'up' ? 'success' : 'error'));

    /* ===================== RECENT ALERTS ===================== */
    $totalAlerts = recent_alerts::count();
    $alertsThisWeek = recent_alerts::whereBetween('recorded_at', [$weekStart, $weekEnd])->count();

    $firstAlertDate = recent_alerts::min('recorded_at');
    $alertWeeks = $firstAlertDate
        ? max(Carbon::parse($firstAlertDate)->diffInWeeks($now), 1)
        : 1;

    $alertAvg = $totalAlerts / $alertWeeks;
    $alertTrend = $alertsThisWeek >= $alertAvg ? 'up' : 'down';

    // Alerts are inverse KPI
    if ($totalAlerts === 0) {
        $alertBadge = 'light';
    } elseif ($alertsThisWeek === 0) {
        $alertBadge = 'success';
    } elseif ($alertTrend === 'up') {
        $alertBadge = 'error';
    } else {
        $alertBadge = 'success';
    }

    return $this->success([
        'users' => [
            'total' => $totalUsers,
            'current_week' => $usersThisWeek,
            'average_per_week' => round($userAvg, 2),
            'percentage_of_total' => $totalUsers > 0 ? round(($usersThisWeek / $totalUsers) * 100, 2) : 0,
            'trend' => $userTrend,
            'badge_color' => $userBadge,
        ],
        'barangays' => [
            'total' => $totalBarangays,
            'current_week' => $barangaysThisWeek,
            'average_per_week' => round($barangayAvg, 2),
            'percentage_of_total' => $totalBarangays > 0 ? round(($barangaysThisWeek / $totalBarangays) * 100, 2) : 0,
            'trend' => $barangayTrend,
            'badge_color' => $barangayBadge,
        ],
        'buoys' => [
            'total' => $totalBuoys,
            'current_week' => $buoysThisWeek,
            'average_per_week' => round($buoyAvg, 2),
            'percentage_of_total' => $totalBuoys > 0 ? round(($buoysThisWeek / $totalBuoys) * 100, 2) : 0,
            'trend' => $buoyTrend,
            'badge_color' => $buoyBadge,
        ],
        'recent_alerts' => [
            'total' => $totalAlerts,
            'current_week' => $alertsThisWeek,
            'average_per_week' => round($alertAvg, 2),
            'percentage_of_total' => $totalAlerts > 0 ? round(($alertsThisWeek / $totalAlerts) * 100, 2) : 0,
            'trend' => $alertTrend,
            'badge_color' => $alertBadge,
        ],
    ], 'Dashboard statistics fetched successfully');
}
}
