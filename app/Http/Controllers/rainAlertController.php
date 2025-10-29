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
        $this->ref_tblName = 'RAIN_SENSOR';
    }
    public function setRainPercentageAlert(){
        $ref = $this->firebase->getReference($this->ref_tblName);
        $data = $ref->getValue();
        $rainData = $data['RAIN_PERCENTAGE'];
        $description = null;
        $alertLevel = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $sensorType = 'Rain';
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        
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


