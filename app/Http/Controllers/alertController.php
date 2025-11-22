<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class alertController extends Controller
{
    protected $firebase;
    protected string $reftblName;
    public function __construct(FirebaseServices $firebaseService)
    {
        $this->firebase = $firebaseService->getDatabase();
    }
    public function setTemperatureAlert(){
        $firebaseData = $this->firebase->getReference()->getValue();
        if (empty($firebaseData)) {
            return response()->json(['status' => 'error','message' => 'No data found in Firebase','data' => []], 404);
        }
       
        foreach ($firebaseData as $prototypeName => $buoyData) {
            if (!isset($buoyData['BME280']['SURROUNDING_TEMPERATURE'])) {
                continue;
            }
            $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
            if (!$prototype) {
                continue;
            }
            $bme280 = $buoyData['BME280'];
            $surroundingTemp = $bme280['SURROUNDING_TEMPERATURE'];
            $description = null;
            $alert = null;
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $sensorType = 'SURROUNDING TEMPERATURE';
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

            if ($surroundingTemp >= 27 && $surroundingTemp <= 32) {
                $description = "WHITE Alert: Mag-ingat sa init! Naitala ang $surroundingTemp °C sa Brgy Zone A ($currentTime). Mag-ingat dahil maaaring magdulot ng pagkapagod ang matagal na pananatili sa labas.";
                $alert = 'White';
            } else if ($surroundingTemp >= 33 && $surroundingTemp <= 41) {
                $description = "BLUE Alert: Labis na mag-ingat sa init! Naitala ang $surroundingTemp °C sa Brgy Zone B ($currentTime). Mag-ingat dahil posibleng makaramdam ng muscle cramps.";
                $alert = 'Blue';
            } else if ($surroundingTemp >= 42 && $surroundingTemp <= 51) {
                $description = "RED Alert: Mapanganib na init! Naitala ang $surroundingTemp °C sa Brgy Zone C ($currentTime). Mataas ang posibilidad ng pagkapagod kaya manatili sa lilim at uminom ng tubig.";
                $alert = 'Red';
            } else if ($surroundingTemp > 52) {
                $alert = 'Red';
                $description = "RED Alert: Matinding init! Naitala ang $surroundingTemp °C sa Brgy Zone D ($currentTime). Mataas ang posibilidad ng heat stroke kaya manatili sa lilim at uminom ng tubig.";
            }
            if (is_null($description)) {
                return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
            }
            DB::table('recent_alerts')->insert([
                'alertId' => $alertId,
                'buoy_id' => $prototype->id,
                'description' => $description,
                'alert_level' => $alert,
                'sensor_type' => $sensorType,
                'recorded_at' => $recorded
            ]);
        }
    }
    public function setWaterTemperatureAlert(){
        $firebaseData = $this->firebase->getReference()->getValue();
        if (empty($firebaseData)) {
            return response()->json(['status' => 'error','message' => 'No data found in Firebase','data' => []], 404);
        }
       
        foreach ($firebaseData as $prototypeName => $buoyData) {
            if (!isset($buoyData['MS5837']['WATER_TEMPERATURE'])) {
                continue;
            }
            $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
            if (!$prototype) {
                continue;
            }
            $ms5837 = $buoyData['MS5837'];
            $waterTemp = $ms5837['WATER_TEMPERATURE'];
            $description = null;
            $alert = null;
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $sensorType = 'WATER TEMPERATURE';
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

            if ($waterTemp >= 26 && $waterTemp <= 30) {
            $description = "WHITE Alert: Katamtamang temperatura ng tubig! Naitala ang $waterTemp °C sa Brgy Zone C ($currentTime). Ligtas ang tubig para sa aktibidad at mababa ang panganib na dala nito.";
            $alert = "White";
            }
            else if ($waterTemp >= 20 && $waterTemp <= 25) {
                $alert = "Blue";
                $description = "BLUE Alert: Malamig ang tubig! Naitala ang $waterTemp °C sa Brgy Zone B ($currentTime). Malamig ang tubig kaya dapat mag-ingat ang bawat isa lalo na ang mga bata at matatanda.";
            } 
            else if ($waterTemp < 20) {
                $alert = "Red";
                $description = "RED Alert: Matinding lamig ng tubig! Naitala ang $waterTemp °C sa Brgy Zone A ($currentTime); Possible ang biglaang lamig sa katawan kaya iwasan ang matagal na pananatili sa tubig.";
            } 
            else if ($waterTemp > 30) {
                $alert = "Red";
                $description = "RED Alert: Matinding init ng tubig! Naitala ang $waterTemp °C sa Brgy. Zone D ($currentTime). Posibleng magdulot ng sobrang init sa katawan at pagkapagod habang nasa tubig.";
            }
            if (is_null($description)) {
                return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
            }
            DB::table('recent_alerts')->insert([
                'alertId' => $alertId,
                'buoy_id' => $prototype->id,
                'description' => $description,
                'alert_level' => $alert,
                'sensor_type' => $sensorType,
                'recorded_at' => $recorded
            ]);
        }
    }
    // public function setHumidityAlert(){
    //     $firebaseData = $this->firebase->getReference()->getValue();
    //     if (empty($firebaseData)) {
    //         return response()->json(['status' => 'error','message' => 'No data found in Firebase','data' => []], 404);
    //     }
       
    //     foreach ($firebaseData as $prototypeName => $buoyData) {
    //         if (!isset($buoyData['BME280']['HUMIDITY'])) {
    //             continue;
    //         }
    //         $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
    //         if (!$prototype) {
    //             continue;
    //         }
    //         $bme280 = $buoyData['BME280'];
    //         $humidityData = $bme280['HUMIDITY'];
    //         $description = null;
    //         $alertLevel = null;
    //         $uuid = Str::uuid();
    //         $alertId = 'ALERT' . $uuid;
    //         $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
    //         $sensorType = 'Temperature';
    //         $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

    //         if ($humidityData >= 30 && $humidityData <= 59) {
    //             $description = "WHITE Alert: Normal na antas ng alinsangan! Naitala ang $humidityData% sa Brgy Zone C ($currentTime), na itinuturing na ligtas at komportable sa karamihan ng residente. ";
    //             $alertLevel = "White";
    //         } 
    //         else if ($humidityData >= 60 && $humidityData <= 69) {
    //             $description = "BLUE Alert:  Patas o mataas na alinsangan! Naitala ang $humidityData% sa Brgy Zone D ($currentTime). Bahagyang maalinsangan ang hangin kaya tiyaking maayos ang daloy ng hangin.";
    //             $alertLevel = "Blue";
    //         } 
    //         else if ($humidityData >= 25 && $humidityData <= 29) {
    //             $alertLevel = "Blue";
    //             $description = "BLUE Alert: Patas o mababang alinsangan! Naitala ang $humidityData% sa Brgy Zone B ($currentTime). Bahagyang tuyo ang hangin kaya posibleng maging hindi komportable.";
    //         } 
    //         else if ($humidityData < 25) {
    //             $description = "RED Alert: Mahina o mababang alinsangan! Naitala ang $humidityData% sa Brgy Zone A ($currentTime). Mag-ingat sa tuyong hangin na posibleng makairita sa balat o mata.";
    //             $alertLevel = "Red";
    //         }else if($humidityData > 70){
    //             $description = "RED Alert: Mahina o mataas na alinsangan! Naitala ang $humidityData% sa Brgy Zone E ($currentTime). Mag-ingat sa labis na kahalumigmigan na posibleng magdulot ng bacteria.";
    //             $alertLevel = "Red";
    //         }
    //         if (is_null($description)) {
    //             return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
    //         }
    //         DB::table('recent_alerts')->insert([
    //             'alertId' => $alertId,
    //             'buoy_id' => $prototype->id,
    //             'description' => $description,
    //             'alert_level' => $alertLevel,
    //             'sensor_type' => $sensorType,
    //             'recorded_at' => $recorded
    //         ]);
    //     }
    // }
    // public function setAtmosphericAlert(){
    //     $firebaseData = $this->firebase->getReference()->getValue();
    //     if (empty($firebaseData)) {
    //         return response()->json(['status' => 'error','message' => 'No data found in Firebase','data' => []], 404);
    //     }
       
    //     foreach ($firebaseData as $prototypeName => $buoyData) {
    //         if (!isset($buoyData['BME280']['ATMOSPHERIC_PRESSURE'])) {
    //             continue;
    //         }
    //         $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
    //         if (!$prototype) {
    //             continue;
    //         }
    //         $bme280 = $buoyData['BME280'];
    //         $atmosphericData = $bme280['ATMOSPHERIC_PRESSURE'];
    //         $description = null;
    //         $alert = null;
    //         $uuid = Str::uuid();
    //         $alertId = 'ALERT' . $uuid;
    //         $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
    //         $sensorType = 'Temperature';
    //         $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

    //         if ($atmosphericData > 1013.2) {
    //             $description = "WHITE Alert: Mataas na lakas ng hangin! Naitala ang $atmosphericData hPa sa Brgy. Zone A ($currentTime). Inaasahan ang malinaw na kalangitan at mahinahong panahon.";
    //             $alert = "White";
    //         } else if ($atmosphericData >= 1010 && $atmosphericData <= 1012) {
    //             $description = "WHITE Alert: Katamtamang lakas ng hangin! Naitala ang $atmosphericData hPa sa Brgy Zone B ($currentTime). Maaayos at payapa ang panahon na may banayad na kondisyon.";
    //             $alert = "White";
    //         } else if ($atmosphericData >= 1007 && $atmosphericData <= 1009) {
    //             $description = "BLUE Alert: Mababang lakas ng hangin! Naitala ang $atmosphericData hPa sa Brgy Zone C ($currentTime). Dumarami ang mga ulap at posibleng umulan nang bahagya.";
    //             $alert = "Blue";
    //         } else if ($atmosphericData < 1006) {
    //             $description = "RED Alert: Napakababang lakas ng hangin! Naitala ang $atmosphericData hPa sa Brgy Zone D ($currentTime). Bagyo na may malakas na ulan at malakas na hangin ang inaasahan.";
    //             $alert = "Red";
    //         }

    //         if (is_null($description)) {
    //             return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
    //         }
    //         DB::table('recent_alerts')->insert([
    //             'alertId' => $alertId,
    //             'buoy_id' => $prototype->id,
    //             'description' => $description,
    //             'alert_level' => $alert,
    //             'sensor_type' => $sensorType,
    //             'recorded_at' => $recorded
    //         ]);
            
    //     }
        
    // }
    // public function setWindAlert(){
    //     $firebaseData = $this->firebase->getReference()->getValue();
    //     if (empty($firebaseData)) {
    //         return response()->json(['status' => 'error','message' => 'No data found in Firebase','data' => []], 404);
    //     }
       
    //     foreach ($firebaseData as $prototypeName => $buoyData) {
    //         if (!isset($buoyData['ANEMOMETER']['WIND_SPEED_km_h'])) {
    //             continue;
    //         }
    //         $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
    //         if (!$prototype) {
    //             continue;
    //         }
    //         $anemometer = $buoyData['ANEMOMETER'];
    //         $windSpeedData = $anemometer['WIND_SPEED_km_h'];
    //         $description = null;
    //         $alert = null;
    //         $uuid = Str::uuid();
    //         $alertId = 'ALERT' . $uuid;
    //         $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
    //         $sensorType = 'Temperature';
    //         $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

    //        if ($windSpeedData >= 39 && $windSpeedData <= 61) {
    //            $description = "WHITE Alert: Wind Signal No.1! Naitala ang $windSpeedData km/h sa Brgy Zone A ($currentTime). Posibleng magdulot ng kaunting pinsala sa mga bahay, puno, o ari-arian, mag-ingat.";
    //            $alert = "White";
    //        } 
    //        else if ($windSpeedData >= 62 && $windSpeedData <= 88) {
    //             $description = "BLUE Alert: Wind Signal No.2! Naitala ang $windSpeedData km/h sa Brgy Zone B ($currentTime). Posibleng magdulot ng kaunti hanggang katamtamang pinsala sa bahay kaya mag-ingat.";
    //             $alert = "Blue";
    //        } 
    //        else if ($windSpeedData >= 89 && $windSpeedData <= 117) {
    //             $description = "BLUE Alert: Wind Signal No.3! Naitala ang $windSpeedData km/h sa Brgy Zone C ($currentTime). Mag-ingat sa lumilipad na debris na maaaring makasugat o makasira ng ari-arian.";
    //             $alert = "Blue";
    //        }  
    //        else if ($windSpeedData >= 118 && $windSpeedData <= 184) {
    //             $description = "RED Alert: Wind Signal No.4! Naitala ang $windSpeedData km/h sa Brgy Zone D ($currentTime). Mag-ingat sa posibleng pagbagsak ng pader na maaaring makasugat o makasira ng bahay.";
    //             $alert = "Red";
    //        }else if ($windSpeedData > 185) {
    //             $description = "RED Alert: Wind Signal No.5! Naitala ang $windSpeedData km/h sa Brgy Zone E ($currentTime). Manatili sa ligtas na lugar dahil posibleng magdulot ito ng matinding pinsala.";
    //             $alert = "Red";
    //        }


    //         if (is_null($description)) {
    //             return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
    //         }
    //         DB::table('recent_alerts')->insert([
    //             'alertId' => $alertId,
    //             'buoy_id' => $prototype->id,
    //             'description' => $description,
    //             'alert_level' => $alert,
    //             'sensor_type' => $sensorType,
    //             'recorded_at' => $recorded
    //         ]);
           
    //     }
       
    // }
    // public function setRainPercentageAlert(){
    //     $firebaseData = $this->firebase->getReference()->getValue();
    //     if (empty($firebaseData)) {
    //         return response()->json(['status' => 'error','message' => 'No data found in Firebase','data' => []], 404);
    //     }
       
    //     foreach ($firebaseData as $prototypeName => $buoyData) {
    //         if (!isset($buoyData['RAIN_GAUGE']['FALL_COUNT_MILIMETERS'])) {
    //             continue;
    //         }
    //         $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
    //         if (!$prototype) {
    //             continue;
    //         }
    //         $rainSensor = $buoyData['RAIN_GAUGE'];
    //         $rainData  = $rainSensor['FALL_COUNT_MILIMETERS'];
    //         $description = null;
    //         $alert = null;
    //         $uuid = Str::uuid();
    //         $alertId = 'ALERT' . $uuid;
    //         $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
    //         $sensorType = 'Temperature';
    //         $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

    //         if ($rainData < 1) {
    //             $description = "WHITE Alert: Napakahinang pag-ulan! Naitala ang < $rainData mm/hr sa Brgy Zone A ($currentTime). May Pabugso-bugsong patak ng ulan pero hindi pa nababasa ang karamihan ng lugar.";
    //             $alert = "White";
    //         } else if ($rainData >= 1 && $rainData <= 3) {
    //             $description = "WHITE Alert: Mahinang ulan! Naitala ang $rainData mm/hr sa Brgy Zone B ($currentTime). Unti-unti nang nababasa ang mga kalsada at lupa.";
    //             $alert = "White";
    //         } else if ($rainData >= 4 && $rainData <= 8) {
    //             $description = "BLUE Alert: Katamtamang ulan! Naitala ang $rainData mm/hr sa Brgy Zone C ($currentTime). Mabilis na naiipon ang tubig sa paligid kaya mag-ingat sa paglakad o pagmamaneho.";
    //             $alert = "Blue";
    //         } else if ($rainData > 8) {
    //             $description = "RED Alert: Malakas na ulan! Naitala ang $rainData mm/hr sa Brgy Zone D ($currentTime). Matindi ang pag-ulan na maaaring magdulot ng malakas na ingay at abala sa bahay.";
    //             $alert = "Red";
    //         }
    //         if (is_null($description)) {
    //             return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
    //         }
    //         DB::table('recent_alerts')->insert([
    //             'alertId' => $alertId,
    //             'buoy_id' => $prototype->id,
    //             'description' => $description,
    //             'alert_level' => $alert,
    //             'sensor_type' => $sensorType,
    //             'recorded_at' => $recorded
    //         ]);
          
    //     }
        
    // }
    public function allAlerts(){
        DB::transaction(function () {
            $this->setTemperatureAlert();
            $this->setWaterTemperatureAlert();
            // $this->setHumidityAlert();
            // $this->setAtmosphericAlert();
            // $this->setWindAlert();
            // $this->setRainPercentageAlert();
            // $this->insertSensorData();
        });
        return response()->json(['success' => true,'message' => 'All alerts processed successfully'], 200);
    }
}
