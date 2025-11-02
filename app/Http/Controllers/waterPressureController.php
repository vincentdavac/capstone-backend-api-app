<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class waterPressureController extends Controller
{
    protected $firebase;
    protected string $ref_tblName;
    public function __construct(FirebaseServices $firebaseService)
    {
        $this->firebase = $firebaseService->getDatabase();
        $this->ref_tblName = 'BUOY-2025-8664';
        //BUOY-2025-8664
    }
    public function setWaterPressure(){
        $ref = $this->firebase->getReference($this->ref_tblName);
        $data = $ref->getValue();
        $ms5837 = $data['MS5837'];
        $waterPressureData = $ms5837['WATER_PRESSURE'];
        $description = null;
        $alertLevel = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $sensorType = 'Water Pressure';
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
        if($waterPressureData < 100){
            $description = "Water Pressure Alert: Water pressure is around $waterPressureData hPa in Barangay Zone A at $currentTime. Minor river flooding possible; minimal structural risk. Stay alert in low-lying areas.";
            $alertLevel = 'White';
        }else if($waterPressureData >= 100 && $waterPressureData <= 200){
            $description = "Water Pressure Alert: Water pressure is $waterPressureData hPa in Barangay Zone B at $currentTime. Noticeable water surge causing moderate disruption to river infrastructure; monitor closely.";
            $alertLevel = "Blue";
        }else if($waterPressureData >= 200 && $waterPressureData <= 300){
            $description = "Water Pressure Alert: Water pressure is $waterPressureData hPa in Barangay Zone C at $currentTime. Severe flooding conditions; significant threat to communities and riverbank structures. Prepare for evacuation if needed.";
            $alertLevel = "Red";
        }else if ($waterPressureData > 300){
            $description = "Water Pressure Alert: Water pressure is $waterPressureData hPa in Barangay Zone D at $currentTime. Extreme flooding; catastrophic damage to riverine areas likely. Immediate action required.";
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



