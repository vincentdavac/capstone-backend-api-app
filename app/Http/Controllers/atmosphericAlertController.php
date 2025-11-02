<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
class atmosphericAlertController extends Controller
{
    protected $firebase;
    protected string $reftblName;
    public function __construct(FirebaseServices $firebaseService){
        $this->firebase = $firebaseService->getDatabase();
        $this->reftblName = 'BUOY-2025-8664';
        //BUOY-2025-8664
    }

    public function setAtmosphericAlert(Request $request){
        $reference = $this->firebase->getReference($this->reftblName);
        $data = $reference->getValue();
        $bme280 = $data['BME280'];
        $atmosphericData =$bme280['ATMOSPHERIC_PRESSURE'];
        $description = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $alert = null;
        $sensorType = 'Atmospheric Pressure';
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
        if ($atmosphericData >= 1013.2) {
            $description = "Atmospheric pressure is at $atmosphericData hPa in Barangay Zone A at $currentTime. Clear skies and calm weather conditions expected.";
            $alert = "White";
        }
        else if($atmosphericData >= 1010 && $atmosphericData <= 1012){
            $description = "Atmospheric pressure is at $atmosphericData hPa in Barangay Zone B at  $currentTime. Stable weather with mild conditions.";
            $alert = "White";
        }else if($atmosphericData >= 1007 && $atmosphericData <= 1009){
            $description ="Atmospheric pressure is at $atmosphericData hPa in Barangay Zone C at $currentTime. Increasing cloudiness; light rain possible.";
            $alert = "Blue";
        }else if($atmosphericData <1006){
            $description = "Atmospheric pressure is at $atmosphericData hPa in Barangay Zone D at $currentTime. Stormy conditions likely; prepare for heavy rain and strong winds.";
            $alert = "Red";
        }
        DB::table('alerts')->insert([
            'alertId' => $alertId,
            'description' => $description,
            'alert_level' => $alert,
            'sensor_type' => $sensorType,
            'recorded_at' => $recorded
        ]);
         return response()->json(['status' => 'success','data' => [ 'alertId' => $alertId,'description' => $description,'alert_level' => 
             $alert,'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }
}
