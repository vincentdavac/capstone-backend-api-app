<?php

namespace App\Http\Controllers;

use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Hotlines;
use App\Models\Message;

use App\Models\Buoy;
use App\Models\recent_alerts;



class BarangayDashboardController extends Controller
{
    use HttpResponses;

    public function userStats()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        $barangayId = $authUser->barangay_id;

        // Base query
        $baseQuery = User::where('barangay_id', $barangayId)
            ->where('registration_status', 1)
            ->where('user_type', 'user');

        // Total users
        $total = (clone $baseQuery)->count();

        // Current week users
        $currentWeek = (clone $baseQuery)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->count();

        // Previous week users
        $previousWeek = (clone $baseQuery)
            ->whereBetween('created_at', [
                Carbon::now()->subWeek()->startOfWeek(),
                Carbon::now()->subWeek()->endOfWeek(),
            ])
            ->count();

        // Average per week (since first record)
        $firstRecord = (clone $baseQuery)->oldest('created_at')->first();

        $weeks = $firstRecord
            ? max(1, Carbon::parse($firstRecord->created_at)->diffInWeeks(Carbon::now()))
            : 1;

        $averagePerWeek = round($total / $weeks, 2);

        // Percentage of total (current week vs total)
        $percentageOfTotal = $total > 0
            ? round(($currentWeek / $total) * 100, 2)
            : 0;

        // Trend
        $trend = $currentWeek >= $previousWeek ? 'up' : 'down';

        // Badge color based on trend
        $badgeColor = $trend === 'up' ? 'success' : 'warning';

        return $this->success([
            'total' => $total,
            'current_week' => $currentWeek,
            'average_per_week' => $averagePerWeek,
            'percentage_of_total' => $percentageOfTotal,
            'trend' => $trend,
            'badge_color' => $badgeColor,
        ], 'User statistics retrieved successfully.');
    }

    public function hotlineStats()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        $barangayId = $authUser->barangay_id;

        // Base query: barangay hotlines OR global hotlines
        $baseQuery = Hotlines::where(function ($query) use ($barangayId) {
            $query->where('barangay_id', $barangayId)
                ->orWhere('is_global', 1);
        })->where('is_archived', 0);

        // Total hotlines
        $total = (clone $baseQuery)->count();

        // Current week hotlines
        $currentWeek = (clone $baseQuery)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->count();

        // Previous week hotlines
        $previousWeek = (clone $baseQuery)
            ->whereBetween('created_at', [
                Carbon::now()->subWeek()->startOfWeek(),
                Carbon::now()->subWeek()->endOfWeek(),
            ])
            ->count();

        // Average per week
        $firstRecord = (clone $baseQuery)->oldest('created_at')->first();

        $weeks = $firstRecord
            ? max(1, Carbon::parse($firstRecord->created_at)->diffInWeeks(Carbon::now()))
            : 1;

        $averagePerWeek = round($total / $weeks, 2);

        // Percentage of total
        $percentageOfTotal = $total > 0
            ? round(($currentWeek / $total) * 100, 2)
            : 0;

        // Trend
        $trend = $currentWeek >= $previousWeek ? 'up' : 'down';

        // Badge color
        $badgeColor = $trend === 'up' ? 'success' : 'warning';

        return $this->success([
            'total' => $total,
            'current_week' => $currentWeek,
            'average_per_week' => $averagePerWeek,
            'percentage_of_total' => $percentageOfTotal,
            'trend' => $trend,
            'badge_color' => $badgeColor,
        ], 'Hotline statistics retrieved successfully.');
    }

    public function dailyMessageStats()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        // Base query: messages sent OR received by authenticated user
        $baseQuery = Message::where(function ($query) use ($authUser) {
            $query->where('sender_id', $authUser->id)
                ->orWhere('receiver_id', $authUser->id);
        });

        // Total messages
        $total = (clone $baseQuery)->count();

        // Current week messages
        $currentWeek = (clone $baseQuery)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->count();

        // Previous week messages
        $previousWeek = (clone $baseQuery)
            ->whereBetween('created_at', [
                Carbon::now()->subWeek()->startOfWeek(),
                Carbon::now()->subWeek()->endOfWeek(),
            ])
            ->count();

        // Average per week
        $firstRecord = (clone $baseQuery)->oldest('created_at')->first();

        $weeks = $firstRecord
            ? max(1, Carbon::parse($firstRecord->created_at)->diffInWeeks(Carbon::now()))
            : 1;

        $averagePerWeek = round($total / $weeks, 2);

        // Percentage of total
        $percentageOfTotal = $total > 0
            ? round(($currentWeek / $total) * 100, 2)
            : 0;

        // Trend
        $trend = $currentWeek >= $previousWeek ? 'up' : 'down';

        // Badge color
        $badgeColor = $trend === 'up' ? 'success' : 'warning';

        return $this->success([
            'total' => $total,
            'current_week' => $currentWeek,
            'average_per_week' => $averagePerWeek,
            'percentage_of_total' => $percentageOfTotal,
            'trend' => $trend,
            'badge_color' => $badgeColor,
        ], 'Daily message statistics retrieved successfully.');
    }

    public function recentAlertStats()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        $barangayId = $authUser->barangay_id;

        // Get all buoy IDs for this barangay
        $buoyIds = Buoy::where('barangay_id', $barangayId)->pluck('id');

        // If no buoys, return empty stats safely
        if ($buoyIds->isEmpty()) {
            return $this->success([
                'total' => 0,
                'current_week' => 0,
                'average_per_week' => 0,
                'percentage_of_total' => 0,
                'trend' => 'down',
                'badge_color' => 'warning',
            ], 'Recent alert statistics retrieved successfully.');
        }

        // Base query: alerts related to barangay buoys
        $baseQuery = recent_alerts::whereIn('buoy_id', $buoyIds);

        // Total alerts
        $total = (clone $baseQuery)->count();

        // Current week alerts
        $currentWeek = (clone $baseQuery)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->count();

        // Previous week alerts
        $previousWeek = (clone $baseQuery)
            ->whereBetween('created_at', [
                Carbon::now()->subWeek()->startOfWeek(),
                Carbon::now()->subWeek()->endOfWeek(),
            ])
            ->count();

        // Average per week
        $firstRecord = (clone $baseQuery)->oldest('created_at')->first();

        $weeks = $firstRecord
            ? max(1, Carbon::parse($firstRecord->created_at)->diffInWeeks(Carbon::now()))
            : 1;

        $averagePerWeek = round($total / $weeks, 2);

        // Percentage of total
        $percentageOfTotal = $total > 0
            ? round(($currentWeek / $total) * 100, 2)
            : 0;

        // Trend
        $trend = $currentWeek >= $previousWeek ? 'up' : 'down';

        // Badge color
        $badgeColor = $trend === 'up' ? 'success' : 'warning';

        return $this->success([
            'total' => $total,
            'current_week' => $currentWeek,
            'average_per_week' => $averagePerWeek,
            'percentage_of_total' => $percentageOfTotal,
            'trend' => $trend,
            'badge_color' => $badgeColor,
        ], 'Recent alert statistics retrieved successfully.');
    }

    private function buildStatResponse($baseQuery)
    {
        $total = (clone $baseQuery)->count();

        $currentWeek = (clone $baseQuery)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek(),
            ])
            ->count();

        $previousWeek = (clone $baseQuery)
            ->whereBetween('created_at', [
                Carbon::now()->subWeek()->startOfWeek(),
                Carbon::now()->subWeek()->endOfWeek(),
            ])
            ->count();

        $firstRecord = (clone $baseQuery)->oldest('created_at')->first();

        $weeks = $firstRecord
            ? max(1, Carbon::parse($firstRecord->created_at)->diffInWeeks(Carbon::now()))
            : 1;

        $averagePerWeek = round($total / $weeks, 2);

        $percentageOfTotal = $total > 0
            ? round(($currentWeek / $total) * 100, 2)
            : 0;

        $trend = $currentWeek >= $previousWeek ? 'up' : 'down';
        $badgeColor = $trend === 'up' ? 'success' : 'warning';

        return [
            'total' => $total,
            'current_week' => $currentWeek,
            'average_per_week' => $averagePerWeek,
            'percentage_of_total' => $percentageOfTotal,
            'trend' => $trend,
            'badge_color' => $badgeColor,
        ];
    }

    public function dashboardStats()
    {
        $authUser = Auth::user();

        if (!$authUser) {
            return $this->error(null, 'Unauthenticated.', 401);
        }

        $barangayId = $authUser->barangay_id;

        /* ================= USERS ================= */
        $userQuery = User::where('barangay_id', $barangayId)
            ->where('registration_status', 1)
            ->where('user_type', 'user');

        /* ================= HOTLINES ================= */
        $hotlineQuery = Hotlines::where(function ($query) use ($barangayId) {
            $query->where('barangay_id', $barangayId)
                ->orWhere('is_global', 1);
        })->where('is_archived', 0);

        /* ================= MESSAGES ================= */
        $messageQuery = Message::where(function ($query) use ($authUser) {
            $query->where('sender_id', $authUser->id)
                ->orWhere('receiver_id', $authUser->id);
        });

        /* ================= ALERTS ================= */
        $buoyIds = Buoy::where('barangay_id', $barangayId)->pluck('id');

        $alertQuery = $buoyIds->isEmpty()
            ? recent_alerts::whereRaw('1 = 0') // return empty stats safely
            : recent_alerts::whereIn('buoy_id', $buoyIds);

        return $this->success([
            'users' => $this->buildStatResponse($userQuery),
            'hotlines' => $this->buildStatResponse($hotlineQuery),
            'messages' => $this->buildStatResponse($messageQuery),
            'alerts' => $this->buildStatResponse($alertQuery),
        ], 'Barangay dashboard statistics retrieved successfully.');
    }
}
