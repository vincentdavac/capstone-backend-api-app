<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class alertController extends Controller{
    protected $firebase;
    protected string $reftblName;
    public function __construct(FirebaseServices $firebaseService){
        $this->firebase = $firebaseService->getDatabase();
    }
    public function setTemperatureAlert(Request $request){
        $user= $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        if (empty($firebaseData)) {
            return response()->json(['status' => 'error', 'message' => 'No data found in Firebase', 'data' => []], 404);
        }
        $barangay = DB::table('users')->join('barangays', 'users.barangay_id', '=','barangays.id')
        ->where('users.barangay_id', $user->barangay_id)->value('barangays.name');
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
                $description = "WHITE Alert: Mag-ingat sa init! Naitala ang $surroundingTemp °C sa $barangay ($currentTime). Mag-ingat dahil maaaring magdulot ng pagkapagod ang matagal na pananatili sa labas.";
                $alert = 'White';
            } else if ($surroundingTemp >= 33 && $surroundingTemp <= 41) {
                $description = "BLUE Alert: Labis na mag-ingat sa init! Naitala ang $surroundingTemp °C sa $barangay ($currentTime). Mag-ingat dahil posibleng makaramdam ng muscle cramps.";
                $alert = 'Blue';
            } else if ($surroundingTemp >= 42 && $surroundingTemp <= 51) {
                $description = "RED Alert: Mapanganib na init! Naitala ang $surroundingTemp °C sa $barangay ($currentTime). Mataas ang posibilidad ng pagkapagod kaya manatili sa lilim at uminom ng tubig.";
                $alert = 'Red';
            } else if ($surroundingTemp > 52) {
                $alert = 'Red';
                $description = "RED Alert: Matinding init! Naitala ang $surroundingTemp °C sa $barangay ($currentTime). Mataas ang posibilidad ng heat stroke kaya manatili sa lilim at uminom ng tubig.";
            }
            if (is_null($description)) {
                return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
            }
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $prototype->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            if($surroundingTemp == 0|| is_null($surroundingTemp)){
               return;
            }
            $insert = false;

            if (!$lastAlert) {
                $insert = true;
            } else {
                $lastAlertTime = Carbon::parse($lastAlert->recorded_at);
                $minutesDiff = $lastAlertTime->diffInMinutes($recorded);
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true; 
                } elseif ($minutesDiff >= 15) {
                    $insert = true; 
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;

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
    }
    public function setWaterTemperatureAlert(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        if (empty($firebaseData)) {
            return response()->json(['status' => 'error', 'message' => 'No data found in Firebase', 'data' => []], 404);
        }
        $barangay = DB::table('users')->join('barangays', 'users.barangay_id', '=','barangays.id')
        ->where('users.barangay_id', $user->barangay_id)->value('barangays.name');
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
                $description = "WHITE Alert: Katamtamang temperatura ng tubig! Naitala ang $waterTemp °C sa $barangay ($currentTime). Ligtas ang tubig para sa aktibidad at mababa ang panganib na dala nito.";
                $alert = "White";
            } else if ($waterTemp >= 20 && $waterTemp <= 25) {
                $alert = "Blue";
                $description = "BLUE Alert: Malamig ang tubig! Naitala ang $waterTemp °C sa $barangay ($currentTime). Malamig ang tubig kaya dapat mag-ingat ang bawat isa lalo na ang mga bata at matatanda.";
            } else if ($waterTemp < 20) {
                $alert = "Red";
                $description = "RED Alert: Matinding lamig ng tubig! Naitala ang $waterTemp °C sa $barangay ($currentTime); Possible ang biglaang lamig sa katawan kaya iwasan ang matagal na pananatili sa tubig.";
            } else if ($waterTemp > 30) {
                $alert = "Red";
                $description = "RED Alert: Matinding init ng tubig! Naitala ang $waterTemp °C sa $barangay ($currentTime). Posibleng magdulot ng sobrang init sa katawan at pagkapagod habang nasa tubig.";
            }
            if (is_null($description)) {
                return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
            }
           $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $prototype->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            if($waterTemp == 0|| is_null($waterTemp)){
               return;
            }
            $insert = false;

            if (!$lastAlert) {
                $insert = true;
            } else {
                $lastAlertTime = Carbon::parse($lastAlert->recorded_at);
                $minutesDiff = $lastAlertTime->diffInMinutes($recorded);
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true; 
                } elseif ($minutesDiff >= 15) {
                    $insert = true; 
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;

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
    }
    public function setHumidityAlert(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        if (empty($firebaseData)) {
            return response()->json(['status' => 'error', 'message' => 'No data found in Firebase', 'data' => []], 404);
        }
        $barangay = DB::table('users')->join('barangays', 'users.barangay_id', '=','barangays.id')
        ->where('users.barangay_id', $user->barangay_id)->value('barangays.name');
        foreach ($firebaseData as $prototypeName => $buoyData) {
            if (!isset($buoyData['BME280']['HUMIDITY'])) {
                continue;
            }
            $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
            if (!$prototype) {
                continue;
            }
            $bme280 = $buoyData['BME280'];
            $humidityData = $bme280['HUMIDITY'];
            $description = null;
            $alertLevel = null;
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $sensorType = 'HUMIDITY';
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

            if ($humidityData >= 30 && $humidityData <= 59) {
                $description = "WHITE Alert: Normal na antas ng alinsangan! Naitala ang $humidityData% sa $barangay ($currentTime), na itinuturing na ligtas at komportable sa karamihan ng residente. ";
                $alertLevel = "White";
            } else if ($humidityData >= 60 && $humidityData <= 69) {
                $description = "BLUE Alert:  Patas o mataas na alinsangan! Naitala ang $humidityData% sa $barangay ($currentTime). Bahagyang maalinsangan ang hangin kaya tiyaking maayos ang daloy ng hangin.";
                $alertLevel = "Blue";
            } else if ($humidityData >= 25 && $humidityData <= 29) {
                $alertLevel = "Blue";
                $description = "BLUE Alert: Patas o mababang alinsangan! Naitala ang $humidityData% sa $barangay ($currentTime). Bahagyang tuyo ang hangin kaya posibleng maging hindi komportable.";
            } else if ($humidityData < 25) {
                $description = "RED Alert: Mahina o mababang alinsangan! Naitala ang $humidityData% sa $barangay ($currentTime). Mag-ingat sa tuyong hangin na posibleng makairita sa balat o mata.";
                $alertLevel = "Red";
            } else if ($humidityData > 70) {
                $description = "RED Alert: Mahina o mataas na alinsangan! Naitala ang $humidityData% sa $barangay ($currentTime). Mag-ingat sa labis na kahalumigmigan na posibleng magdulot ng bacteria.";
                $alertLevel = "Red";
            }
            if (is_null($description)) {
                return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
            }
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $prototype->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            if($humidityData == 0||is_null($humidityData)){
               return;
            }
            $insert = false;

            if (!$lastAlert) {
                $insert = true;
            } else {
                $lastAlertTime = Carbon::parse($lastAlert->recorded_at);
                $minutesDiff = $lastAlertTime->diffInMinutes($recorded);
                if ($lastAlert->alert_level !== $alertLevel) {
                    $insert = true; 
                } elseif ($minutesDiff >= 15) {
                    $insert = true; 
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;

                DB::table('recent_alerts')->insert([
                    'alertId' => $alertId,
                    'buoy_id' => $prototype->id,
                    'description' => $description,
                    'alert_level' => $alertLevel,
                    'sensor_type' => $sensorType,
                    'recorded_at' => $recorded
                ]);
            }
        }
    }
    public function setAtmosphericAlert(Request $request){
        $user= $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        if (empty($firebaseData)) {
            return response()->json(['status' => 'error', 'message' => 'No data found in Firebase', 'data' => []], 404);
        }
        $barangay = DB::table('users')->join('barangays', 'users.barangay_id', '=','barangays.id')
        ->where('users.barangay_id', $user->barangay_id)->value('barangays.name');
        foreach ($firebaseData as $prototypeName => $buoyData) {
            if (!isset($buoyData['BME280']['ATMOSPHERIC_PRESSURE'])) {
                continue;
            }
            $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
            if (!$prototype) {
                continue;
            }
            $bme280 = $buoyData['BME280'];
            $atmosphericData = $bme280['ATMOSPHERIC_PRESSURE'];
            $description = null;
            $alert = null;
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $sensorType = 'ATMOSPHERIC PRESSURE';
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

            if ($atmosphericData > 1013.2) {
                $description = "WHITE Alert: Mataas na lakas ng hangin! Naitala ang $atmosphericData hPa sa $barangay ($currentTime). Inaasahan ang malinaw na kalangitan at mahinahong panahon.";
                $alert = "White";
            } else if ($atmosphericData >= 1010 && $atmosphericData <= 1012) {
                $description = "WHITE Alert: Katamtamang lakas ng hangin! Naitala ang $atmosphericData hPa sa $barangay ($currentTime). Maaayos at payapa ang panahon na may banayad na kondisyon.";
                $alert = "White";
            } else if ($atmosphericData >= 1007 && $atmosphericData <= 1009) {
                $description = "BLUE Alert: Mababang lakas ng hangin! Naitala ang $atmosphericData hPa sa $barangay ($currentTime). Dumarami ang mga ulap at posibleng umulan nang bahagya.";
                $alert = "Blue";
            } else if ($atmosphericData < 1006) {
                $description = "RED Alert: Napakababang lakas ng hangin! Naitala ang $atmosphericData hPa sa $barangay ($currentTime). Bagyo na may malakas na ulan at malakas na hangin ang inaasahan.";
                $alert = "Red";
            }

            if (is_null($description)) {
                return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
            }
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $prototype->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            if($atmosphericData == 0||is_null($atmosphericData)){
               return;
            }
            $insert = false;

            if (!$lastAlert) {
                $insert = true;
            } else {
                $lastAlertTime = Carbon::parse($lastAlert->recorded_at);
                $minutesDiff = $lastAlertTime->diffInMinutes($recorded);
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true; 
                } elseif ($minutesDiff >= 15) {
                    $insert = true; 
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;

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
    }
    public function setWindAlert(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        if (empty($firebaseData)) {
            return response()->json(['status' => 'error', 'message' => 'No data found in Firebase', 'data' => []], 404);
        }
        $barangay = DB::table('users')->join('barangays', 'users.barangay_id', '=','barangays.id')
        ->where('users.barangay_id', $user->barangay_id)->value('barangays.name');
        foreach ($firebaseData as $prototypeName => $buoyData) {
            if (!isset($buoyData['ANEMOMETER']['WIND_SPEED_km_h'])) {
                continue;
            }
            $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
            if (!$prototype) {
                continue;
            }
            $anemometer = $buoyData['ANEMOMETER'];
            $windSpeedData = $anemometer['WIND_SPEED_km_h'];
            $description = null;
            $alert = null;
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $sensorType = 'WIND SPEED';
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

            if ($windSpeedData >= 39 && $windSpeedData <= 61) {
                $description = "WHITE Alert: Wind Signal No.1! Naitala ang $windSpeedData km/h sa $barangay ($currentTime). Posibleng magdulot ng kaunting pinsala sa mga bahay, puno, o ari-arian, mag-ingat.";
                $alert = "White";
            } else if ($windSpeedData >= 62 && $windSpeedData <= 88) {
                $description = "BLUE Alert: Wind Signal No.2! Naitala ang $windSpeedData km/h sa $barangay ($currentTime). Posibleng magdulot ng kaunti hanggang katamtamang pinsala sa bahay kaya mag-ingat.";
                $alert = "Blue";
            } else if ($windSpeedData >= 89 && $windSpeedData <= 117) {
                $description = "BLUE Alert: Wind Signal No.3! Naitala ang $windSpeedData km/h sa $barangay ($currentTime). Mag-ingat sa lumilipad na debris na maaaring makasugat o makasira ng ari-arian.";
                $alert = "Blue";
            } else if ($windSpeedData >= 118 && $windSpeedData <= 184) {
                $description = "RED Alert: Wind Signal No.4! Naitala ang $windSpeedData km/h sa $barangay ($currentTime). Mag-ingat sa posibleng pagbagsak ng pader na maaaring makasugat o makasira ng bahay.";
                $alert = "Red";
            } else if ($windSpeedData > 185) {
                $description = "RED Alert: Wind Signal No.5! Naitala ang $windSpeedData km/h sa $barangay ($currentTime). Manatili sa ligtas na lugar dahil posibleng magdulot ito ng matinding pinsala.";
                $alert = "Red";
            }


            if (is_null($description)) {
                return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
            }
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $prototype->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            if($windSpeedData == 0|| is_null($windSpeedData)){
               return;
            }
            $insert = false;

            if (!$lastAlert) {
                $insert = true;
            } else {
                $lastAlertTime = Carbon::parse($lastAlert->recorded_at);
                $minutesDiff = $lastAlertTime->diffInMinutes($recorded);
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true; 
                } elseif ($minutesDiff >= 15) {
                    $insert = true; 
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;

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
    }
    public function setRainPercentageAlert(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        if (empty($firebaseData)) {
            return response()->json(['status' => 'error', 'message' => 'No data found in Firebase', 'data' => []], 404);
        }
        $barangay = DB::table('users')->join('barangays', 'users.barangay_id', '=','barangays.id')
        ->where('users.barangay_id', $user->barangay_id)->value('barangays.name');
        foreach ($firebaseData as $prototypeName => $buoyData) {
            if (!isset($buoyData['RAIN_GAUGE']['FALL_COUNT_MILIMETERS'])) {
                continue;
            }
            $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
            if (!$prototype) {
                continue;
            }
            $rainSensor = $buoyData['RAIN_GAUGE'];
            $rainData  = $rainSensor['FALL_COUNT_MILIMETERS'];
            $description = null;
            $alert = null;
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $sensorType = 'RAIN GAUGE';
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

            if ($rainData < 1) {
                $description = "WHITE Alert: Napakahinang pag-ulan! Naitala ang < $rainData mm/hr sa $barangay ($currentTime). May Pabugso-bugsong patak ng ulan pero hindi pa nababasa ang karamihan ng lugar.";
                $alert = "White";
            } else if ($rainData >= 1 && $rainData <= 3) {
                $description = "WHITE Alert: Mahinang ulan! Naitala ang $rainData mm/hr sa $barangay ($currentTime). Unti-unti nang nababasa ang mga kalsada at lupa.";
                $alert = "White";
            } else if ($rainData >= 4 && $rainData <= 8) {
                $description = "BLUE Alert: Katamtamang ulan! Naitala ang $rainData mm/hr sa $barangay ($currentTime). Mabilis na naiipon ang tubig sa paligid kaya mag-ingat sa paglakad o pagmamaneho.";
                $alert = "Blue";
            } else if ($rainData > 8) {
                $description = "RED Alert: Malakas na ulan! Naitala ang $rainData mm/hr sa $barangay ($currentTime). Matindi ang pag-ulan na maaaring magdulot ng malakas na ingay at abala sa bahay.";
                $alert = "Red";
            }
            if (is_null($description)) {
                return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
            }
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $prototype->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            if($rainData == 0|| is_null($rainData)){
               return;
            }
            $insert = false;
            if (!$lastAlert) {
                $insert = true;
            } else {
                $lastAlertTime = Carbon::parse($lastAlert->recorded_at);
                $minutesDiff = $lastAlertTime->diffInMinutes($recorded);
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true; 
                } elseif ($minutesDiff >= 15) {
                    $insert = true; 
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;
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
    }
    public function setWaterLevel(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        if (empty($firebaseData)) {
            return response()->json(['status' => 'error', 'message' => 'No data found in Firebase', 'data' => []], 404);
        }
        $barangay = DB::table('users')->join('barangays', 'users.barangay_id', '=','barangays.id')
        ->where('users.barangay_id', $user->barangay_id)->value('barangays.name');
        foreach ($firebaseData as $prototypeName => $buoyData) {
            if (!isset($buoyData['MS5837']['WATER_LEVEL_FEET'])) {
                continue;
            }
            $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
            if (!$prototype) {
                continue;
            }
            $waterlevel = $buoyData['MS5837'];
            $waterlevelData  = $waterlevel['WATER_LEVEL_FEET'];
            $description = null;
            $alert = null;
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $sensorType = 'WATER LEVEL';
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
            $brgyWhiteLevel= DB::table('users')->join('barangays', 'users.barangay_id', '=','barangays.id')->where('users.barangay_id', $user->barangay_id)
            ->value('barangays.white_level_alert');
            $brgBlueLevel= DB::table('users')->join('barangays', 'users.barangay_id', '=','barangays.id')->where('users.barangay_id', $user->barangay_id)
            ->value('barangays.blue_level_alert');
            $brgRedLevel= DB::table('users')->join('barangays', 'users.barangay_id', '=','barangays.id')->where('users.barangay_id', $user->barangay_id)
            ->value('barangays.red_level_alert');

            if ($waterlevelData < $brgyWhiteLevel) {
                $description = "WHITE Alert: Maging alerto sa lebel ng tubig! Naitala ang $waterlevelData feet kapasidad sa $barangay ($currentTime). Bantayan ang tubig at mag-ingat sa posibleng pagbaha.";
                $alert = "White";
            }else if($waterlevelData <= $brgBlueLevel){
                $description = "BLUE Alert: Maging alarma at mapanuri sa lebel ng tubig! Naitala ang $waterlevelData feet kapasidad sa $barangay ($currentTime). Malaki ang posibilidad ng pag-apaw ng tubig.";
                $alert ="Blue";
            }else if($waterlevelData >= $brgRedLevel){
                $description= "RED Alert: Maging mapanuri sa lebel ng tubig! Naitala ang $waterlevelData feet kapasidad sa $barangay ($currentTime). Agad na lumikas upang maiwasan ang panganib ng pagbaha.";
                 $alert ="Red";
            }
            if (is_null($description)) {
                return response()->json(['status' => 'error', 'message' => 'No valid temperature data found', 'data' => []], 404);
            }
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $prototype->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            if($waterlevelData == 0|| is_null($waterlevelData)){
               return;
            }
            $insert = false;
            if (!$lastAlert) {
                $insert = true;
            } else {
                $lastAlertTime = Carbon::parse($lastAlert->recorded_at);
                $minutesDiff = $lastAlertTime->diffInMinutes($recorded);
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true; 
                } elseif ($minutesDiff >= 15) {
                    $insert = true; 
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;
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
    }
    public function insertSensorData(Request $request){
        $firebaseData = $this->firebase->getReference()->getValue();
        $request->validate(['alert_id' => 'required|integer', 'buoy_code' => 'required|string',]);
        if (empty($firebaseData)) {
            return response()->json(['status' => 'error', 'message' => 'No data found in Firebase', 'data' => []], 404);
        }
        foreach ($firebaseData as $prototypeName => $buoyData) {
            $prototype = DB::table('buoys')->where('buoy_code', operator: $prototypeName)->first();
            if ($prototypeName === $request->buoy_code) {
                if (!$prototype) {
                    continue;
                }
                $bme280 = $buoyData['BME280'];
                $waterData = $buoyData['MS5837'];
                $windData = $buoyData['ANEMOMETER'];
                $RAIN_GAUGE = $buoyData['RAIN_GAUGE'];
                $RAIN_SENSOR = $buoyData['RAIN_SENSOR'];
                $ALTITUDE = $bme280['ALTITUDE'];
                $ATMOSPHERIC_PRESSURE = $bme280['ATMOSPHERIC_PRESSURE'];
                $surrounding_temp = $bme280['SURROUNDING_TEMPERATURE'];
                $humidity = $bme280['HUMIDITY'];
                $WATER_LEVEL_METER = $waterData['WATER_LEVEL_METER'];
                $WATER_LEVEL_FEET = $waterData['WATER_LEVEL_FEET'];
                $WATER_TEMPERATURE = $waterData['WATER_TEMPERATURE'];
                $WATER_ALTITUDE = $waterData['WATER_ALTITUDE'];
                $WATER_PRESSURE = $waterData['WATER_PRESSURE'];
                $WIND_SPEED_km_h = $windData['WIND_SPEED_km_h'];
                $WIND_SPEED_m_s = $windData['WIND_SPEED_m_s'];
                $FALL_COUNT_MILIMETERS = $RAIN_GAUGE['FALL_COUNT_MILIMETERS'];
                $TIP_COUNT = $RAIN_GAUGE['TIP_COUNT'];
                $RAIN_PERCENTAGE = $RAIN_SENSOR['RAIN_PERCENTAGE'];
                $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
                $f = $surrounding_temp * 1.8 + 32;

                $wf = $WATER_TEMPERATURE * 1.8 + 32;
                DB::table('bme280_atmospheric_readings')->insert([
                    'buoy_id' =>  $request->alert_id,
                    'pressure_hpa' => $ATMOSPHERIC_PRESSURE,
                    'pressure_mbar' => $ATMOSPHERIC_PRESSURE,
                    'altitude' => $ALTITUDE,
                    'recorded_at' => $recorded
                ]);

                DB::table('bme280_humidity_readings')->insert([
                    'buoy_id' =>  $request->alert_id,
                    'humidity' => $humidity,
                ]);

                DB::table('bme280_temperature_readings')->insert([
                    'buoy_id' =>  $request->alert_id,
                    'temperature_celsius' => $surrounding_temp,
                    'temperature_fahrenheit' => $f,
                ]);

                DB::table('depth_readings')->insert([
                    'buoy_id' =>  $request->alert_id,
                    'pressure_mbar' => $WATER_PRESSURE,
                    'pressure_hpa' => $WATER_PRESSURE,
                    'depth_m' => $WATER_LEVEL_METER,
                    'depth_ft' => $WATER_LEVEL_FEET,
                    'water_altitude' => $WATER_ALTITUDE,
                    'recorded_at' => $recorded
                ]);

                DB::table('water_temperature_readings')->insert([
                    'buoy_id' =>  $request->alert_id,
                    'temperature_celsius' => $WATER_TEMPERATURE,
                    'temperature_fahrenheit' => $wf,
                ]);

                DB::table('wind_readings')->insert([
                    'buoy_id' =>  $request->alert_id,
                    'wind_speed_m_s' => $WIND_SPEED_m_s,
                    'wind_speed_k_h' => $WIND_SPEED_km_h,
                ]);
                DB::table('rain_gauge_readings')->insert([
                    'buoy_id' =>  $request->alert_id,
                    'rainfall_mm' => $FALL_COUNT_MILIMETERS,
                    'tip_count' => $TIP_COUNT,
                ]);
                DB::table('rain_sensor_readings')->insert([
                    'buoy_id' =>  $request->alert_id,
                    'percentage' => $RAIN_PERCENTAGE,
                    'recorded_at' => $recorded
                ]);
            }
        }
        // return response()->json(['status' => 'success', 'data' => $id], 200, [], JSON_PRETTY_PRINT);
    }
    public function allAlerts(){
        DB::transaction(function () {
            $request = request();
            $this->setTemperatureAlert($request);
            $this->setWaterTemperatureAlert($request);
            $this->setHumidityAlert($request);
            $this->setAtmosphericAlert($request);
            $this->setWindAlert($request);
            $this->setRainPercentageAlert($request);
            $this->setWaterLevel($request);
            // $this->insertSensorData($request);
        });
        return response()->json(['success' => true, 'message' => 'All alerts processed successfully'], 200);
    }
}
