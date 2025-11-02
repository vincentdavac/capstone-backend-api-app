<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class rainAlertController extends Controller
{
    protected $firebase;
    protected string $ref_tblName;
    public function __construct(FirebaseServices $firebaseService)
    {
        $this->firebase = $firebaseService->getDatabase();
        $this->ref_tblName = 'BUOY-2025-8664';
        //BUOY-2025-8664
    }
    public function setRainPercentageAlert(){
        $ref = $this->firebase->getReference($this->ref_tblName);
        $data = $ref->getValue();
        $rainSensor = $data['RAIN_SENSOR'];
        $rainData  = $rainSensor['FALL_COUNT_MILIMETERS'];
        $description = null;
        $alertLevel = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $sensorType = 'Rain';
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

        if($rainData < 1){
            $description = "Rain Alert: Rainfall is occurring at a very light rate in Barangay Zone A at $currentTime. Scattered drops; surfaces remain mostly dry.";
            $alertLevel = "White";
        }else if($rainData >= 1 && $rainData <= 3){
            $description = "Rain Alert: Rainfall rate has reached $rainData mm/hr in Barangay Zone B at $currentTime. Individual drops easily identified; Surface wetness developing gradually. Small streams may flow in drains.";
            $alertLevel = "White";
        }else if($rainData >= 4 && $rainData <=8){
            $description = "Rain Alert: Rainfall rate is $rainData mm/hr in Barangay Zone C at $currentTime. Water accumulations form quickly; downpipes flowing freely. Continuous rainfall with overcast sky.";
            $alertLevel = "Blue";
        }else if($rainData > 8){
            $description = " Rainfall rate has exceeded $rainData mm/hr in Barangay Zone D at $currentTime. Intense rain; falls in sheets with misty spray over surfaces. May cause roaring noise on roofs and localized flooding.";
            $alertLevel = "Red";
        }
        DB::table('alerts')->insert([
            'alertId' => $alertId,
            'description' => $description,
            'alert_level' => $alertLevel,
            'sensor_type' => $sensorType,
            'recorded_at' => $recorded
        ]);
        return response()->json(['status' => 'success', 'data' => ['alertId' => $alertId, 'description' => $description, 'alert_level' =>
        $alertLevel, 'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }
}


