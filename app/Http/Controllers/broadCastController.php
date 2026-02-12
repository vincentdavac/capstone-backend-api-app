<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\alerts;
use App\Services\FirebaseServices;
use App\Models\recent_alerts;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Events\AlertBroadcast;
use Carbon\Carbon;
class broadCastController extends Controller
{
    protected $firebase;
    protected string $reftblName;
    public function __construct(FirebaseServices $firebaseService)
    {
        $this->firebase = $firebaseService->getDatabase();
    }
    public function sendAlert(Request $request)
    {
        $user = $request->user();
        $request->validate(['alert_id' => 'required|integer|exists:recent_alerts,id', 'buoy_code' => 'required|string', 'sensorTypes' => 'required|string']);
        $recentAlert = recent_alerts::find($request->alert_id);
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $resetTime = null;
        if ($recentAlert->alert_level === "Blue") {
            $resetTime = 5;
        } elseif ($recentAlert->alert_level === "Red") {
            $resetTime = 10;
        }
        $firebaseData = $this->firebase->getReference()->getValue();
        $smsToken = base_path(env('SMS_TOKEN'));
        $message = "";
        $url = 'https://www.iprogsms.com/api/v1/sms_messages/send_bulk';
        $numbers = [];
        try {
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
                        $counts  = DB::table('alerts')->where('user_id', $user->id)->where('is_read', 0)->get()->count();
                        $buoyID = DB::table('recent_alerts')->where('id', $request->alert_id)->value('buoy_id');
                        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
                        $relayState = 'on';
                        broadcast(new AlertBroadcast([
                            'description' => $alertInfo->description,
                            'alert_level' => $alertInfo->alert_level,
                            'broadcast_by' => $alert->broadcast_by,
                            'sensor_type' => $alertInfo->sensor_type,
                            'recorded_at' => $alert->recorded_at,
                            'counts' => $counts,
                        ]));
                        DB::table('relay_status')->insert([
                            'buoy_id' => $buoyID,
                            'relay_state' => $relayState,
                            'triggered_by' => $user->id,
                            'recorded_at' => $recorded
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
            DB::table('recent_alerts')->where('sensor_type', $request->sensorTypes)->update(['alert_shown' => true]);
            return response()->json(['success' => true,'reset' => $resetTime], 201);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'processing failed', 'error' => $e->getMessage()], 500);
        }
    }
    public function resetRelay(Request $request)
    {
        $user = $request->user();
        $request->validate(['buoy_code' => 'required|string',]);
        $this->firebase->getReference($request->buoy_code . '/RELAY_STATE')->set(false);
        return response()->json(['message' => 'reset success'], 200);
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
