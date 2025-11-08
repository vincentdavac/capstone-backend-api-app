<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function broadCastAlerts(Request $request)
    {
        $validated = $request->validate([
            'alert_id' => 'nullable|exists:alerts,id',
        ]);
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
        $users = User::where('is_admin', 0)->get();
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'alert_id' => $validated['alert_id'] ?? null,
                'created_at' => $recorded
            ]);
        }

        return response()->json([
            'message' => 'Notification sent to all  user!',
        ]);
    }
    public function getNotifications(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::with('alertbyid')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ], 200);
    }
}
