<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\alerts;
use App\Services\FirebaseServices;
use App\Models\recent_alerts;
use App\Models\User;
use App\Events\AlertBroadcast;
use Carbon\Carbon;
class alertMonitoring extends Controller
{
    protected $firebase;
    protected string $reftblName;
    public function __construct(FirebaseServices $firebaseService)
    {
        $this->firebase = $firebaseService->getDatabase();
    }
    public function getActiveAlerts($buoyCode)
    {
        $alert = DB::table('recent_alerts')->join('buoys', 'recent_alerts.buoy_id', '=', 'buoys.id')
            ->where('buoys.buoy_code', $buoyCode)->whereIn('alert_level', ['Blue', 'Red'])
            ->where('recent_alerts.alert_shown', false)->orderBy('recent_alerts.recorded_at', 'desc')
            ->select('recent_alerts.*', 'buoys.buoy_code')->first();
        return response()->json(['alerts' => $alert ? [$alert] : [],  'has_new_alerts' => $alert !== null]);
    }
    public function markAlertAsShown(Request $request)
    {
        $validated = $request->validate(['alert_id' => 'required|exists:recent_alerts,id']);
        DB::table('recent_alerts')->where('id', $validated['alert_id'])->update(['alert_shown' => true]);
        return response()->json(['success' => true]);
    }
    public function checkAlertStatus($buoyId)
    {
        $sensorTypes = ['SURROUNDING TEMPERATURE', 'WATER TEMPERATURE', 'HUMIDITY', 'ATMOSPHERIC PRESSURE', 'WIND SPEED', 'RAIN GAUGE', 'WATER LEVEL'];
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
    public function sendAlert(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate(['alert_id' => 'required|integer|exists:recent_alerts,id', 'buoy_code' => 'required|string', 'sensor_stype' => 'required|string',]);
        $user = $request->user();
        $recentAlert = recent_alerts::find($request->alert_id);
        $resetTime = null;
        $url = 'https://www.iprogsms.com/api/v1/sms_messages/send_bulk';
        if ($recentAlert->alert_level === "Blue") {
            $resetTime = 5;
        } elseif ($recentAlert->alert_level === "Red") {
            $resetTime = 10;
        }
        $firebaseData = $this->firebase->getReference()->getValue();
        $recentAlert = DB::table('recent_alerts')->where('id', $validated['alert_id'])->first();
        if (!$recentAlert) {
            return response()->json(['message' => 'Alert not found'], 404);
        }
        $message = "";
        $numbers = [];
        foreach ($firebaseData as $prototypeName => $buoyData) {
            if (!isset($buoyData['RELAY_STATE'])) {
                continue;
            }
            if ($prototypeName === $request->buoy_code) {
                $this->firebase->getReference($prototypeName . '/RELAY_STATE')->set(true);
                $users = User::where('user_type', 'user')->get();
                foreach ($users as $userId) {
                    $normalized = $this->normalizePHNumber($userId->contact_number);
                    $alert = alerts::create([
                        'alert_id' => $request->alert_id,
                        'broadcast_by' => $user->last_name . ", " . $user->first_name,
                        'user_id' => $userId->id,
                        'is_read' => false,
                        'recorded_at' => now(),
                    ]);
                    $alertInfo = DB::table('recent_alerts')->where('id', $request->alert_id)->first();
                    $message = DB::table('recent_alerts')->where('id', $request->alert_id)->value('description');
                    $buoyID = DB::table('recent_alerts')->where('id', $request->alert_id)->value('buoy_id');
                    $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
                    $relayState = 'on';
                    broadcast(new AlertBroadcast([
                        'description' => $alertInfo->description,
                        'alert_level' => $alertInfo->alert_level,
                        'broadcast_by' => $alert->broadcast_by,
                        'sensor_type' => $alertInfo->sensor_type,
                        'recorded_at' => $alert->recorded_at,
                    ]));
                    DB::table('relay_status')->insert([
                        'buoy_id'=> $buoyID,
                        'relay_state'=>$relayState,
                        'triggered_by'=>$user->id,
                        'recorded_at'=>$recorded
                    ]);
                    if ($normalized) {
                        $numbers[] = $normalized;
                    }
                }
            } else {
                $this->firebase->getReference($prototypeName . '/RELAY_STATE')->set(false);
            }
        }
        $phoneNumbers = implode(',', array_unique($numbers));
        $data = [
            'api_token' => '',
            'message' => $message,
            'phone_number' => $phoneNumbers
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_exec($ch);
        curl_close($ch);
        DB::table('recent_alerts')->where('sensor_type', $validated['sensor_stype'])->update(['alert_shown' => true]);
        return response()->json(['message' => 'Alert broadcasted successfully.', 'reset' => $resetTime,], 201);
    }
    public function resetRelayModal(Request $request)
    {
        $user = $request->user();
        $request->validate(['buoy_code' => 'required|string',]);
        $this->firebase->getReference($request->buoy_code . '/RELAY_STATE')->set(false);
        return response()->json(['message' => 'RELAY reset successfully'], 200);
    }
    public function normalizePHNumber($number)
    {
        $number = preg_replace('/[^0-9]/', '', $number);
        if (preg_match('/^09\d{9}$/', $number)) {
            return '63' . substr($number, 1);
        }
        if (preg_match('/^639\d{9}$/', $number)) {
            return $number;
        }
        return null;
    }
}
