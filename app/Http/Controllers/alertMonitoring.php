<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class alertMonitoring extends Controller
{
    public function getActiveAlerts($buoyId){
        $alerts = DB::table('recent_alerts')->where('buoy_id', $buoyId)->whereIn('alert_level', ['Blue', 'Red'])
            ->where('alert_shown', false)->orderBy('recorded_at', 'desc')->get();
        return response()->json(['alerts' => $alerts,'has_new_alerts' => $alerts->isNotEmpty()]);
    }

    public function markAlertAsShown(Request $request){
        $validated = $request->validate(['alert_id' => 'required|exists:recent_alerts,id']);
        DB::table('recent_alerts')->where('id', $validated['alert_id'])->update(['alert_shown' => true]);
        return response()->json(['success' => true]);
    }

    public function checkAlertStatus($buoyId){
        $latestAlert = DB::table('recent_alerts')->where('buoy_id', $buoyId)->orderBy('recorded_at', 'desc')->first();
        if ($latestAlert && $latestAlert->alert_level === 'White') {
            DB::table('recent_alerts')->where('buoy_id', $buoyId)->update(['alert_shown' => false]);
        }
        return response()->json(['current_level' => $latestAlert?->alert_level ?? 'White','reset_triggered' => $latestAlert && $latestAlert->alert_level === 'White']);
    }
}
