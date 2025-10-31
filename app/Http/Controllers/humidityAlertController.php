<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
class humidityAlertController extends Controller
{
    protected $firebase;
    protected string $reftblName;
    public function __construct(FirebaseServices $firebaseService){
        $this->firebase = $firebaseService->getDatabase();
        $this->reftblName = 'BME280';
    }
    public function setHumidityAlert(){
        $ref = $this->firebase->getReference($this->reftblName);
        $data = $ref->getValue();
        $humidityData = $data['HUMIDITY'];
        $description = null;
        $alertLevel = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $sensorType = 'Humidity';
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
        if($humidityData >= 60 && $humidityData <= 69){
            $description = "Relative humidity has reached " .$humidityData."% "."in Barangay Zone B at " . $currentTime. " Monitor indoor air; mold growth and discomfort may begin.";
            $alertLevel = "White";
        }else if($humidityData > 70){
            $description = " Relative humidity has reached ". $humidityData ."% " . "in Barangay Zone C at " .$currentTime." High humidity may cause discomfort, condensation, and increased risk of mold.";
            $alertLevel = "Blue";
        }else if($humidityData >= 20 && $humidityData <= 29){
            $alertLevel = "White";
            $description ="Relative humidity has dropped to ". $humidityData. "% ". "in Barangay Zone D at ". $currentTime . " Air feels dry; may cause skin, eye, and throat irritation.";
        }else if($humidityData < 25){
            $description ="Relative humidity has fallen to ". $humidityData ."%". "in Barangay Zone E at " .$currentTime . "Very dry air, high risk of irritation and static electricity buildup.";
             $alertLevel = "Blue";
        }else{
            $description ="no data";
        }
         DB::table('alerts')->insert([
            'alertId' => $alertId,
            'description' => $description,
            'alert_level' => $alertLevel,
            'sensor_type' => $sensorType,
            'recorded_at' => $recorded
        ]);
         return response()->json(['status' => 'success','data' => [ 'alertId' => $alertId,'description' => $description,'alert_level' => 
             $alertLevel,'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }
}
