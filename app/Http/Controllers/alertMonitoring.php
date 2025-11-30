<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\alerts;
use App\Services\FirebaseServices;
use App\Models\recent_alerts;
class alertMonitoring extends Controller
{
    protected $firebase;
    protected string $reftblName;
    public function __construct(FirebaseServices $firebaseService){
        $this->firebase = $firebaseService->getDatabase();
    }
    public function getActiveAlerts($buoyId){
        $alert = DB::table('recent_alerts')->where('buoy_id', $buoyId)
            ->whereIn('alert_level', ['Blue', 'Red'])->where('alert_shown', false)
            ->orderBy('recorded_at', 'desc')->first();
        return response()->json(['alerts' => $alert ? [$alert] : [], 'has_new_alerts' => $alert !== null]);
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
        return response()->json(['current_levels' => $currentLvl, 'reset_triggered' => $reset]);
    }
    public function sendAlert(Request $request){
        $validated =$request->validate(['alert_id' => 'required|integer|exists:recent_alerts,id', 'buoy_code' => 'required|string',]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $recentAlert = recent_alerts::find($request->alert_id);
        $resetTime = null;
        if ($recentAlert->alert_level === "Blue") {
            $resetTime = 5;
        } elseif ($recentAlert->alert_level ==="Red") {
            $resetTime = 10; 
        }
        $firebaseData = $this->firebase->getReference()->getValue();
        $recentAlert = DB::table('recent_alerts')->where('id', $validated['alert_id'])->first();
        if (!$recentAlert) {
            return response()->json(['message' => 'Alert not found'], 404);
        }
       foreach ($firebaseData as $prototypeName =>$buoyData) {
            if (!isset($buoyData['RELAY_STATE'])) {
                continue;
            }
            if ($prototypeName === $request->buoy_code) {
                $this->firebase->getReference($prototypeName . '/RELAY_STATE')->set(true);
                alerts::create([
                    'alert_id' => $request->alert_id,
                    'broadcast_by' => $user->last_name . ", " . $user->first_name,
                    'is_read' => false,
                    'recorded_at' => now(),
                ]);
            }else{
                $this->firebase->getReference($prototypeName . '/RELAY_STATE')->set(false);
            }
        }
        DB::table('recent_alerts')->where('id', $validated['alert_id'])->update(['alert_shown' => true]);
         return response()->json(['message' => 'Alert broadcasted successfully.', 'reset' => $resetTime,], 201);
    }
    public function resetRelayModal(Request $request){
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $request->validate(['buoy_code' => 'required|string',]);
        $this->firebase->getReference($request->buoy_code . '/RELAY_STATE')->set(false);
        return response()->json(['message' => 'RELAY reset successfully'], 200);
    }
}
