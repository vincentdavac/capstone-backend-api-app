<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class waterTemperatureAlert extends Controller
{
    protected $firebase;
    protected string $reftblName;
    public function __construct(FirebaseServices $firebaseService)
    {
        $this->firebase = $firebaseService->getDatabase();
        $this->reftblName = 'BUOY-2025-8664';
        //BUOY-2025-8664
    }

    public function setWaterTemperatureAlert(Request $request){
        $ref = $this->firebase->getReference($this->reftblName);
        $waterData = $ref->getValue();
        $ms5837 = $waterData['MS5837'];
        $waterTemp = $ms5837['WATER_TEMPERATURE'];
        $description = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $alert = null;
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

        $sensorType = 'Temperature';
        if ($waterTemp >= 26 && $waterTemp <= 30) {
            $description = "River water temperature is $waterTemp °C in Barangay Zone C at $currentTime. Comfortable for general swimming/activities; minimal temperature-related risk under normal conditions.";
            $alert = "White";
        } else if ($waterTemp >= 20 && $waterTemp <= 25) {
            $alert = "Blue";
            $description = "River water temperature is $waterTemp °C in Barangay Zone B at $currentTime. Cool water; comfortable for short durations but caution for children, older persons, or prolonged activity.";
        } else if ($waterTemp < 20) {
            $alert = "Red";
            $description = "River water temperature has dropped to $waterTemp °C in Barangay Zone A at $currentTime. Water is very cold; risk of cold shock or rapid cooling - limit exposure.";
        } else if ($waterTemp > 30) {
            $alert = "Red";
            $description = "River water temperature has reached $waterTemp °C in Barangay Zone D at $currentTime. Very warm water; risk of overheating, fatigue, especially if combined with hot air or high humidity.";
        }
        if (is_null($description)) {
            return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
        }
        DB::table('alerts')->insert([
            'alertId' => $alertId,
            'description' => $description,
            'alert_level' => $alert,
            'sensor_type' => $sensorType,
            'recorded_at' => $recorded
        ]);
        return response()->json(['status' => 'success', 'data' => ['alertId' => $alertId, 'description' => $description, 'alert_level' =>
        $alert, 'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }
}
