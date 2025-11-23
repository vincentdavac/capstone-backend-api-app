<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class alertMonitoring extends Controller
{
    public function getActiveAlerts($buoyId){
        $alert = DB::table('recent_alerts')->where('buoy_id', $buoyId)
            ->whereIn('alert_level', ['Blue', 'Red'])->where('alert_shown', false)
            ->orderBy('recorded_at', 'desc')->first();  
        return response()->json(['alerts' => $alert ? [$alert] : [],'has_new_alerts' => $alert !== null]);
    }
    public function markAlertAsShown(Request $request){
        $validated = $request->validate(['alert_id' => 'required|exists:recent_alerts,id']);
        DB::table('recent_alerts')->where('id', $validated['alert_id'])->update(['alert_shown' => true]);
        return response()->json(['success' => true]);
    }
    public function checkAlertStatus($buoyId){
        $sensorTypes = ['WATER TEMPERATURE', 'SURROUNDING TEMPERATURE'];
        $currentLvl = [];
        $reset = false;
        foreach ($sensorTypes as $sensorType) {
            $latestAlert = DB::table('recent_alerts')->where('buoy_id', $buoyId)
                ->where('sensor_type', $sensorType)->orderBy('recorded_at', 'desc')->first();
            if ($latestAlert) {
                $currentLvl[$sensorType] = $latestAlert->alert_level;
                if ($latestAlert->alert_level === 'White') {
                    $updated = DB::table('recent_alerts')->where('buoy_id', $buoyId)
                        ->where('sensor_type', $sensorType)->whereIn('alert_level', ['Blue', 'Red']) 
                        ->where('recorded_at', '<', $latestAlert->recorded_at)->update(['alert_shown' => true]); 
                    if ($updated > 0) {
                        $reset = true;
                    }
                }
            }
        }
        return response()->json(['current_levels' => $currentLvl,'reset_triggered' => $reset]);
  }
}
