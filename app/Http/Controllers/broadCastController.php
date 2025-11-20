<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\alerts;
use Illuminate\Support\Facades\Auth;

class broadCastController extends Controller
{
    public function sendAlert(Request $request){
        $request->validate([
            'alert_id' => 'required|integer|exists:recent_alerts,id',
        ]);
          $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $alert = alerts::create([
            'alert_id' => $request->alert_id,
            'broadcast_by' =>$user->last_name . ", " . $user->first_name,
            'is_read' => false,
            'recorded_at' => now(),
        ]);
        return response()->json(['message' => 'Alert broadcasted successfully.','data' => $alert,], 201);
    }
}
