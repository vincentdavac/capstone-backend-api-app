<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class surroundingtemperatureAlertController extends Controller{
    protected $firebase;
    protected string $reftblName;
    public function __construct(FirebaseServices $firebaseService){
        $this->firebase = $firebaseService->getDatabase();
        $this->reftblName = 'BME280';
        //BME280
    }

    public function setTemperatureAlert(Request $request){
        $ref = $this->firebase->getReference($this->reftblName);
        $surroundingData = $ref->getValue();
        $surroundingTemp = $surroundingData['SURROUNDING_TEMPERATURE'];
        $description = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $alert = null;
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        $sensorType = 'Temperature';
        if ($surroundingTemp >= 27 && $surroundingTemp <=32) {
            $description = " Heat index has reached $surroundingTemp °C in Barangay Zone A at $currentTime. Stay alert, prolonged outdoor activity may cause fatigue.";
            $alert = 'White';
        }else if($surroundingTemp >= 33 && $surroundingTemp <= 41){
            $description = "Heat index has reached $surroundingTemp °C in Barangay Zone B at $currentTime. Take precautions, heat cramps and heat exhaustion are possible.";
            $alert = 'Blue';
        }else if($surroundingTemp >=42 && $surroundingTemp <= 51){
            $description = "Heat index has reached $surroundingTemp °C in Barangay Zone C at $currentTime. Danger, heat exhaustion is likely and heat stroke is probable with continued exposure.";
            $alert = 'Red';
        }else if($surroundingTemp > 52){
            $alert = 'Red';
            $description ="Heat index has reached $surroundingTemp °C in Barangay Zone D at $currentTime. Extreme danger, heat stroke imminent.";
        }
        if (is_null($description)) {
            return response()->json(['status' => 'error','message' => 'No valid temperature data found','data' => []], 404);
        }
        DB::table('alerts')->insert([
            'alertId' => $alertId,
            'description' => $description,
            'alert_level' => $alert,
            'sensor_type' => $sensorType,
            'recorded_at' => $currentTime
        ]);
        return response()->json(['status' => 'success','data' => [ 'alertId' => $alertId,'description' => $description,'alert_level' => 
             $alert,'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }
}
