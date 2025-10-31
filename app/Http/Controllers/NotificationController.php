<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function broadCastAlerts(Request $request)
    {
        $validated = $request->validate([
            'alert_id' => 'nullable|exists:alerts,id',
        ]);
        $users = User::where('is_admin', 0)->get();
        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'alert_id' => $validated['alert_id'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Notification sent to all  user!',
        ]);
    }
}
