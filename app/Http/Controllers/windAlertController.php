<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class windAlertController extends Controller
{
    protected $firebase;
    protected string $ref_tblName;
    public function __construct(FirebaseServices $firebaseService)
    {
        $this->firebase = $firebaseService->getDatabase();
        $this->ref_tblName = 'ANEMOMETER';
    }
    public function setWindAlert(){
        $ref = $this->firebase->getReference($this->ref_tblName);
        $data = $ref->getValue();
        $windSpeedData = $data['WIND_SPEED_km-h'];
        $description = null;
        $alertLevel = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $sensorType = 'Wind Speed';
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        if($windSpeedData >= 39 && $windSpeedData <= 61){
            $description = "Wind speed has reached $windSpeedData km/h in Barangay Zone A at $currentTime. Minimal to minor damage possible to light structures; trees sway and small branches may break.";
            $alertLevel = "White";
        }else if($windSpeedData >=62 && $windSpeedData <=68){
            $description = "Wind speed has reached $windSpeedData km/h in Barangay Zone B at $currentTime. Minor to moderate damage to light structures; unsecured objects may become projectiles.";
            $alertLevel ="Blue";
        }else if($windSpeedData >=89 && $windSpeedData <=117){
            $description = "Wind speed has reached $windSpeedData km/h in Barangay Zone C at $currentTime. Moderate to significant structural damage possible, widespread tree damage likely.";
            $alertLevel ="Blue";
        }else if($windSpeedData >=118 && $windSpeedData <=184){
            $description = "Wind speed has reached $windSpeedData km/h in Barangay Zone D at $currentTime. Severe damage expected, widespread destruction of buildings and infrastructure likely.";
            $alertLevel ="Red";
        }else if($windSpeedData >185){
            $description = " Wind speed has reached $windSpeedData km/h in Barangay Zone E at $currentTime. Catastrophic damage, almost total destruction expected, very high risk to life and property.";
            $alertLevel ="Red";
        }
        
        DB::table('alerts')->insert([
            'alertId' => $alertId,
            'description' => $description,
            'alert_level' => $alertLevel,
            'sensor_type' => $sensorType,
            'recorded_at' => $currentTime
        ]);
        return response()->json(['status' => 'success', 'data' => ['alertId' => $alertId, 'description' => $description, 'alert_level' =>
        $alertLevel, 'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }
}
