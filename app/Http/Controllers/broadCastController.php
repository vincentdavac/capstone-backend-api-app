<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\alerts;
use Illuminate\Support\Facades\Auth;
use App\Services\FirebaseServices;
use App\Models\recent_alerts;
use Illuminate\Support\Facades\DB;
use App\Models\User;

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
        $testData = null;
        foreach ($firebaseData as $prototypeName => $buoyData) {
            if (!isset($buoyData['RELAY_STATE'])) {
                continue;
            }
            if ($prototypeName === $request->buoy_code) {
                $this->firebase->getReference($prototypeName . '/RELAY_STATE')->set(true);
                $users = User::where('is_admin', 0)->get();
                $usersContact = DB::table("users")->select("contact_number")->get();
                foreach ($users as $userId) {
                    foreach($usersContact as $contactNo){
                        alerts::create([
                        'alert_id' => $request->alert_id,
                        'broadcast_by' => $user->last_name . ", " . $user->first_name,
                        'user_id' => $userId->id,
                        'is_read' => false,
                        'recorded_at' => now(),
                    ]);
                   $testData= $contactNo->contact_number;
                    }
                }
            } else {
                $this->firebase->getReference($prototypeName . '/RELAY_STATE')->set(false);
            }
        }
        DB::table('recent_alerts')->where('sensor_type', $request->sensorTypes)->update(['alert_shown' => true]);
        return response()->json(['message' => 'Alert broadcasted successfully.', 'reset' => $resetTime,"dadadadada"=>$testData], 201);
    }
    public function resetRelay(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $request->validate(['buoy_code' => 'required|string',]);
        $this->firebase->getReference($request->buoy_code . '/RELAY_STATE')->set(false);
        return response()->json(['message' => 'RELAY reset successfully'], 200);
    }
}
