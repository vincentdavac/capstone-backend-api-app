<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Events\AlertBroadcast;
use App\Models\alerts;
use App\Events\SystemNotificationSent;
use App\Models\SystemNotifications;

class alertController extends Controller
{
    protected $firebase;
    protected string $reftblName;
    public function __construct(FirebaseServices $firebaseService){
        $this->firebase = $firebaseService->getDatabase();
    }
    public function normalizePHNumber($number){
        $number = preg_replace('/[^0-9]/', '', $number);
        if (preg_match('/^09\d{9}$/', $number)) {
            return '63' . substr($number, 1);
        }
        if (preg_match('/^639\d{9}$/', $number)) {
            return $number;
        }
        return null;
    }

    public function setTemperatureAlert(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        $usersId = User::where('user_type', 'user')->get();
        if (empty($firebaseData)) {
            return null;
        }
        $barangay = DB::table('users')
            ->join('barangays', 'users.barangay_id', '=', 'barangays.id')
            ->where('users.barangay_id', $user->barangay_id ?? 5)
            ->value('barangays.name');

        $buoyCode = DB::table('buoys')
            ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
            ->where('buoys.barangay_id', $user->barangay_id ?? 5)
            ->value('buoys.buoy_code');
        $resetTime = null;
        foreach ($firebaseData as $buoyName => $buoyData) {
            if (!isset($buoyData['BME280']['SURROUNDING_TEMPERATURE'])) {
                continue;
            }
            if ($buoyCode !== $buoyName) {
                continue;
            }
            $buoy = DB::table('buoys')->where('buoy_code', $buoyName)->first();
            if (!$buoy) {
                continue;
            }
            $bme280  = $buoyData['BME280'];
            $surroundingTemp = (float) $bme280['SURROUNDING_TEMPERATURE'];
            if (is_null($surroundingTemp) || $surroundingTemp == 0) {
                continue;
            }
            $description = null;
            $alert = null;
            $sensorType = 'SURROUNDING TEMPERATURE';
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
            $url = 'https://www.iprogsms.com/api/v1/sms_messages/send_bulk';
            if ($surroundingTemp < 26) {
                $description = "WHITE Alert: Normal na temperatura! Naitala ang $surroundingTemp °C sa $barangay ($currentTime). Normal ang kondisyon ng kapaligiran kaya ligtas ang karaniwang gawain sa labas.";
                $alert = 'White';
            }
            if ($surroundingTemp >= 27 && $surroundingTemp <= 32.99) {
                $description = "BLUE Alert: Labis na mag-ingat sa init! Naitala ang $surroundingTemp °C sa $barangay ($currentTime). Mag-ingat dahil posibleng makaramdam ng muscle cramps.";
                $alert = 'Blue';
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
            $uuid = Str::uuid();

            $alertId = 'ALERT' . $uuid;
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $buoy->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();

            $insert = false;
            if (!$lastAlert) {
                $insert = true;
            } else {
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true;
                } else {
                    $insert = false;
                }
            }
            if ($insert) {
                $uuid    = Str::uuid();
                $alertId = 'ALERT' . $uuid;

                DB::table('recent_alerts')->insert([
                    'alertId' => $alertId,
                    'buoy_id' => $buoy->id,
                    'description' => $description,
                    'alert_level' => $alert,
                    'sensor_type' => $sensorType,
                    'recorded_at' => $recorded,
                ]);
                if ($alert === 'Blue') {
                    $resetTime = 5;
                } elseif ($alert === 'Red') {
                    $resetTime = 10;
                }
                $buoyID = DB::table('buoys')
                    ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
                    ->where('buoys.barangay_id', $user->barangay_id ?? 5)
                    ->value('buoys.id');

                $relayState = 'on';
                $numbers    = [];

                if ($alert === 'Blue' || $alert === 'Red') {
                    $this->firebase->getReference($buoyName . '/RELAY_STATE')->set(true);
                    DB::table('relay_status')->insert([
                        'buoy_id' => $buoyID,
                        'relay_state' => $relayState,
                        'triggered_by' => $user->id,
                        'recorded_at' => $recorded,
                    ]);
                    broadcast(new AlertBroadcast([
                        'description' => $description,
                        'alert_level' => $alert,
                        'broadcast_by' => $user->last_name . ", " . $user->first_name,
                        'sensor_type' => $sensorType,
                        'recorded_at' => $recorded,
                    ]));
                    foreach ($usersId as $usergetId) {
                        $numberNormalized = $this->normalizePHNumber($usergetId->contact_number);
                        alerts::create([
                            'alert_id' => $alertId,
                            'broadcast_by' => $user->last_name . ", " . $user->first_name,
                            'user_id' => $usergetId->id,
                            'is_read' => false,
                            'recorded_at' => now(),
                        ]);

                        if ($numberNormalized) {
                            $numbers[] = $numberNormalized;
                        }
                    }
                    $phoneNumbers = implode(',', array_unique($numbers));

                    $data = [
                        'api_token' => '4e07eee9fca6d25f58f066453b1f258db25a2e5e',
                        'message' => $description,
                        'phone_number' => $phoneNumbers,
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/x-www-form-urlencoded',
                    ]);
                    curl_exec($ch);
                    curl_close($ch);
                    DB::table('recent_alerts')->where('sensor_type', $request->sensorTypes)->update(['alert_shown' => true]);
                    $notification = SystemNotifications::create([
                        'sender_id' => $user->id,
                        'receiver_id' => $user->id,
                        'barangay_id' => $user->barangay_id,
                        'receiver_role' => $user->user_type,
                        'title'  => 'Relay Status',
                        'body'  => 'The relay has been set to ON.',
                        'status'  => 'unread',
                        'created_at' => $recorded,
                    ]);

                    broadcast(new SystemNotificationSent($notification))->toOthers();
                }
            }
        }

        return $resetTime;
    }
    public function setWaterTemperatureAlert(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        $usersId = User::where('user_type', 'user')->get();
        if (empty($firebaseData)) {
            return null;
        }
        $barangay = DB::table('users')
            ->join('barangays', 'users.barangay_id', '=', 'barangays.id')
            ->where('users.barangay_id', $user->barangay_id ?? 5)
            ->value('barangays.name');

        $buoyCode = DB::table('buoys')
            ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
            ->where('buoys.barangay_id', $user->barangay_id ?? 5)
            ->value('buoys.buoy_code');
        $resetTime = null;
        foreach ($firebaseData as $buoyName => $buoyData) {
            if (!isset($buoyData['MS5837']['WATER_TEMPERATURE'])) {
                continue;
            }
            if ($buoyCode !== $buoyName) {
                continue;
            }
            $buoy = DB::table('buoys')->where('buoy_code', $buoyName)->first();
            if (!$buoy) {
                continue;
            }
            $MS5837  = $buoyData['MS5837'];
            $waterTemp= (float)$MS5837['WATER_TEMPERATURE'];
            if (is_null($waterTemp) || $waterTemp == 0) {
                continue;
            }
            $description = null;
            $alert = null;
            $sensorType = 'WATER TEMPERATURE';
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
            $url = 'https://www.iprogsms.com/api/v1/sms_messages/send_bulk';
            if ($waterTemp >= 26 && $waterTemp <= 30.99) {
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
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $buoy->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();

            $insert = false;

            if (!$lastAlert) {
                $insert = true;
            } else {
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true;
                } else {
                    $insert = false;
                }
            }

            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;

                DB::table('recent_alerts')->insert([
                    'alertId' => $alertId,
                    'buoy_id' => $buoy->id,
                    'description' => $description,
                    'alert_level' => $alert,
                    'sensor_type' => $sensorType,
                    'recorded_at' => $recorded,
                ]);
                if ($alert === 'Blue') {
                    $resetTime = 5;
                } elseif ($alert === 'Red') {
                    $resetTime = 10;
                }
                $buoyID = DB::table('buoys')
                    ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
                    ->where('buoys.barangay_id', $user->barangay_id ?? 5)
                    ->value('buoys.id');

                $relayState = 'on';
                $numbers    = [];

                if ($alert === 'Blue' || $alert === 'Red') {
                    $this->firebase->getReference($buoyName . '/RELAY_STATE')->set(true);
                    DB::table('relay_status')->insert([
                        'buoy_id' => $buoyID,
                        'relay_state'  => $relayState,
                        'triggered_by' => $user->id,
                        'recorded_at' => $recorded,
                    ]);
                    broadcast(new AlertBroadcast([
                        'description' => $description,
                        'alert_level' => $alert,
                        'broadcast_by' => $user->last_name . ", " . $user->first_name,
                        'sensor_type' => $sensorType,
                        'recorded_at' => $recorded,
                    ]));
                    foreach ($usersId as $usergetId) {
                        $numberNormalized = $this->normalizePHNumber($usergetId->contact_number);
                        alerts::create([
                            'alert_id' => $alertId,
                            'broadcast_by' => $user->last_name . ", " . $user->first_name,
                            'user_id' => $usergetId->id,
                            'is_read' => false,
                            'recorded_at' => now(),
                        ]);

                        if ($numberNormalized) {
                            $numbers[] = $numberNormalized;
                        }
                    }
                    $phoneNumbers = implode(',', array_unique($numbers));

                    $data = [
                        'api_token' => '4e07eee9fca6d25f58f066453b1f258db25a2e5e',
                        'message' => $description,
                        'phone_number' => $phoneNumbers,
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/x-www-form-urlencoded',
                    ]);
                    curl_exec($ch);
                    curl_close($ch);
                    DB::table('recent_alerts')->where('sensor_type', $request->sensorTypes)->update(['alert_shown' => true]);
                    $notification = SystemNotifications::create([
                        'sender_id' => $user->id,
                        'receiver_id' => $user->id,
                        'barangay_id' => $user->barangay_id,
                        'receiver_role' => $user->user_type,
                        'title'  => 'Relay Status',
                        'body'  => 'The relay has been set to ON.',
                        'status'  => 'unread',
                        'created_at' => $recorded,
                    ]);

                    broadcast(new SystemNotificationSent($notification))->toOthers();
                }
            }
        }

        return $resetTime;
    }
    public function setHumidityAlert(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        $usersId = User::where('user_type', 'user')->get();
        if (empty($firebaseData)) {
            return null;
        }
        $barangay = DB::table('users')
            ->join('barangays', 'users.barangay_id', '=', 'barangays.id')
            ->where('users.barangay_id', $user->barangay_id ?? 5)
            ->value('barangays.name');

        $buoyCode = DB::table('buoys')
            ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
            ->where('buoys.barangay_id', $user->barangay_id ?? 5)
            ->value('buoys.buoy_code');
        $resetTime = null;
        foreach ($firebaseData as $buoyName => $buoyData) {
            if (!isset($buoyData['BME280']['HUMIDITY'])) {
                continue;
            }
            if ($buoyCode !== $buoyName) {
                continue;
            }
            $buoy = DB::table('buoys')->where('buoy_code', $buoyName)->first();
            if (!$buoy) {
                continue;
            }
            $bme280  = $buoyData['BME280'];
            $humidityData = $bme280['HUMIDITY'];
            if (is_null($humidityData) || $humidityData == 0) {
                continue;
            }
            $description = null;
            $alert = null;
            $sensorType = 'HUMIDITY';
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
            $url = 'https://www.iprogsms.com/api/v1/sms_messages/send_bulk';
            if ($humidityData >= 30 && $humidityData <= 59) {
                $description = "WHITE Alert: Normal na antas ng alinsangan! Naitala ang $humidityData% sa $barangay ($currentTime), na itinuturing na ligtas at komportable sa karamihan ng residente. ";
                $alert = "White";
            } else if ($humidityData >= 60 && $humidityData <= 69) {
                $description = "BLUE Alert:  Patas o mataas na alinsangan! Naitala ang $humidityData% sa $barangay ($currentTime). Bahagyang maalinsangan ang hangin kaya tiyaking maayos ang daloy ng hangin.";
                $alert = "Blue";
            } else if ($humidityData >= 25 && $humidityData <= 29) {
                $alert = "Blue";
                $description = "BLUE Alert: Patas o mababang alinsangan! Naitala ang $humidityData% sa $barangay ($currentTime). Bahagyang tuyo ang hangin kaya posibleng maging hindi komportable.";
            } else if ($humidityData < 25) {
                $description = "RED Alert: Mahina o mababang alinsangan! Naitala ang $humidityData% sa $barangay ($currentTime). Mag-ingat sa tuyong hangin na posibleng makairita sa balat o mata.";
                $alert = "Red";
            } else if ($humidityData > 70) {
                $description = "RED Alert: Mahina o mataas na alinsangan! Naitala ang $humidityData% sa $barangay ($currentTime). Mag-ingat sa labis na kahalumigmigan na posibleng magdulot ng bacteria.";
                $alert = "Red";
            }
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $buoy->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            $insert = false;
            if (!$lastAlert) {
                $insert = true;
            } else {
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true;
                } else {
                    $insert = false;
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;
                DB::table('recent_alerts')->insert([
                    'alertId' => $alertId,
                    'buoy_id' => $buoy->id,
                    'description' => $description,
                    'alert_level' => $alert,
                    'sensor_type' => $sensorType,
                    'recorded_at' => $recorded,
                ]);
                if ($alert === 'Blue') {
                    $resetTime = 5;
                } elseif ($alert === 'Red') {
                    $resetTime = 10;
                }
                $buoyID = DB::table('buoys')
                    ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
                    ->where('buoys.barangay_id', $user->barangay_id ?? 5)
                    ->value('buoys.id');

                $relayState = 'on';
                $numbers    = [];

                if ($alert === 'Blue' || $alert === 'Red') {
                    $this->firebase->getReference($buoyName . '/RELAY_STATE')->set(true);
                    DB::table('relay_status')->insert([
                        'buoy_id' => $buoyID,
                        'relay_state' => $relayState,
                        'triggered_by' => $user->id,
                        'recorded_at' => $recorded,
                    ]);
                    broadcast(new AlertBroadcast([
                        'description' => $description,
                        'alert_level' => $alert,
                        'broadcast_by' => $user->last_name . ", " . $user->first_name,
                        'sensor_type' => $sensorType,
                        'recorded_at' => $recorded,
                    ]));
                    foreach ($usersId as $usergetId) {
                        $numberNormalized = $this->normalizePHNumber($usergetId->contact_number);
                        alerts::create([
                            'alert_id' => $alertId,
                            'broadcast_by' => $user->last_name . ", " . $user->first_name,
                            'user_id' => $usergetId->id,
                            'is_read' => false,
                            'recorded_at' => now(),
                        ]);

                        if ($numberNormalized) {
                            $numbers[] = $numberNormalized;
                        }
                    }
                    $phoneNumbers = implode(',', array_unique($numbers));

                    $data = [
                        'api_token' => '4e07eee9fca6d25f58f066453b1f258db25a2e5e',
                        'message' => $description,
                        'phone_number' => $phoneNumbers,
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/x-www-form-urlencoded',
                    ]);
                    curl_exec($ch);
                    curl_close($ch);
                    DB::table('recent_alerts')->where('sensor_type', $request->sensorTypes)->update(['alert_shown' => true]);
                    $notification = SystemNotifications::create([
                        'sender_id' => $user->id,
                        'receiver_id' => $user->id,
                        'barangay_id' => $user->barangay_id,
                        'receiver_role' => $user->user_type,
                        'title'  => 'Relay Status',
                        'body'  => 'The relay has been set to ON.',
                        'status'  => 'unread',
                        'created_at' => $recorded,
                    ]);

                    broadcast(new SystemNotificationSent($notification))->toOthers();
                }
            }
        }

        return $resetTime;
    }
    public function setAtmosphericAlert(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        $usersId = User::where('user_type', 'user')->get();
        if (empty($firebaseData)) {
            return null;
        }
        $barangay = DB::table('users')
            ->join('barangays', 'users.barangay_id', '=', 'barangays.id')
            ->where('users.barangay_id', $user->barangay_id ?? 5)
            ->value('barangays.name');

        $buoyCode = DB::table('buoys')
            ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
            ->where('buoys.barangay_id', $user->barangay_id ?? 5)
            ->value('buoys.buoy_code');
        $resetTime = null;
        foreach ($firebaseData as $buoyName => $buoyData) {
            if (!isset($buoyData['BME280']['ATMOSPHERIC_PRESSURE'])) {
                continue;
            }
            if ($buoyCode !== $buoyName) {
                continue;
            }
            $buoy = DB::table('buoys')->where('buoy_code', $buoyName)->first();
            if (!$buoy) {
                continue;
            }
            $bme280  = $buoyData['BME280'];
            $atmosphericData = $bme280['ATMOSPHERIC_PRESSURE'];
            if (is_null($atmosphericData) || $atmosphericData == 0) {
                continue;
            }
            $description = null;
            $alert = null;
            $sensorType = 'ATMOSPHERIC PRESSURE';
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
            $url = 'https://www.iprogsms.com/api/v1/sms_messages/send_bulk';
            if ($atmosphericData > 1013.2) {
                $description = "WHITE Alert: Mataas na lakas ng hangin! Naitala ang $atmosphericData hPa sa $barangay ($currentTime). Inaasahan ang malinaw na kalangitan at mahinahong panahon.";
                $alert = "White";
            } else if ($atmosphericData >= 1010 && $atmosphericData <= 1012) {
                $description = "WHITE Alert: Katamtamang lakas ng hangin! Naitala ang $atmosphericData hPa sa $barangay ($currentTime). Maaayos at payapa ang panahon na may banayad na kondisyon.";
                $alert = "White";
            } else if ($atmosphericData >= 1007 && $atmosphericData <= 1009) {
                $description = "BLUE Alert: Mababang lakas ng hangin! Naitala ang $atmosphericData hPa sa $barangay ($currentTime). Dumarami ang mga ulap at posibleng umulan nang bahagya.";
                $alert = "Blue";
            } else {
                $description = "RED Alert: Napakababang lakas ng hangin! Naitala ang $atmosphericData hPa sa $barangay ($currentTime). Bagyo na may malakas na ulan at malakas na hangin ang inaasahan.";
                $alert = "Red";
            }
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $buoy->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            $insert = false;
            if (!$lastAlert) {
                $insert = true;
            } else {
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true;
                } else {
                    $insert = false;
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;
                DB::table('recent_alerts')->insert([
                    'alertId' => $alertId,
                    'buoy_id' => $buoy->id,
                    'description' => $description,
                    'alert_level' => $alert,
                    'sensor_type' => $sensorType,
                    'recorded_at' => $recorded,
                ]);
                if ($alert === 'Blue') {
                    $resetTime = 5;
                } elseif ($alert === 'Red') {
                    $resetTime = 10;
                }
                $buoyID = DB::table('buoys')
                    ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
                    ->where('buoys.barangay_id', $user->barangay_id ?? 5)
                    ->value('buoys.id');

                $relayState = 'on';
                $numbers    = [];

                if ($alert === 'Blue' || $alert === 'Red') {
                    $this->firebase->getReference($buoyName . '/RELAY_STATE')->set(true);
                    DB::table('relay_status')->insert([
                        'buoy_id' => $buoyID,
                        'relay_state' => $relayState,
                        'triggered_by' => $user->id,
                        'recorded_at' => $recorded,
                    ]);
                    broadcast(new AlertBroadcast([
                        'description' => $description,
                        'alert_level' => $alert,
                        'broadcast_by' => $user->last_name . ", " . $user->first_name,
                        'sensor_type' => $sensorType,
                        'recorded_at' => $recorded,
                    ]));
                    foreach ($usersId as $usergetId) {
                        $numberNormalized = $this->normalizePHNumber($usergetId->contact_number);
                        alerts::create([
                            'alert_id' => $alertId,
                            'broadcast_by' => $user->last_name . ", " . $user->first_name,
                            'user_id' => $usergetId->id,
                            'is_read' => false,
                            'recorded_at' => now(),
                        ]);

                        if ($numberNormalized) {
                            $numbers[] = $numberNormalized;
                        }
                    }
                    $phoneNumbers = implode(',', array_unique($numbers));

                    $data = [
                        'api_token' => '4e07eee9fca6d25f58f066453b1f258db25a2e5e',
                        'message'  => $description,
                        'phone_number' => $phoneNumbers,
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/x-www-form-urlencoded',
                    ]);
                    curl_exec($ch);
                    curl_close($ch);
                    DB::table('recent_alerts')->where('sensor_type', $request->sensorTypes)->update(['alert_shown' => true]);
                    $notification = SystemNotifications::create([
                        'sender_id' => $user->id,
                        'receiver_id' => $user->id,
                        'barangay_id' => $user->barangay_id,
                        'receiver_role' => $user->user_type,
                        'title'  => 'Relay Status',
                        'body'  => 'The relay has been set to ON.',
                        'status'  => 'unread',
                        'created_at' => $recorded,
                    ]);

                    broadcast(new SystemNotificationSent($notification))->toOthers();
                }
            }
        }

        return $resetTime;
    }
    public function setWindAlert(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        $usersId = User::where('user_type', 'user')->get();
        if (empty($firebaseData)) {
            return null;
        }
        $barangay = DB::table('users')
            ->join('barangays', 'users.barangay_id', '=', 'barangays.id')
            ->where('users.barangay_id', $user->barangay_id ?? 5)
            ->value('barangays.name');

        $buoyCode = DB::table('buoys')
            ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
            ->where('buoys.barangay_id', $user->barangay_id ?? 5)
            ->value('buoys.buoy_code');
        $resetTime = null;
        foreach ($firebaseData as $buoyName => $buoyData) {
            if (!isset($buoyData['ANEMOMETER']['WIND_SPEED_km_h'])) {
                continue;
            }
            if ($buoyCode !== $buoyName) {
                continue;
            }
            $buoy = DB::table('buoys')->where('buoy_code', $buoyName)->first();
            if (!$buoy) {
                continue;
            }
            $anemometer  = $buoyData['ANEMOMETER'];
            $windSpeedData = (float) $anemometer['WIND_SPEED_km_h'];
            if (is_null($windSpeedData) || $windSpeedData == 0) {
                continue;
            }
            $description = null;
            $alert = null;
            $sensorType = 'WIND SPEED';
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
            $url = 'https://www.iprogsms.com/api/v1/sms_messages/send_bulk';
            if ($windSpeedData < 39) {
                $description = "WHITE Alert: $windSpeedData km/h Normal operation, monitoring, coordination & reporting. Walang agarang banta, patuloy ang pagbabantay at paghahanda.";
                $alert = "White";
            } else if ($windSpeedData >= 39 && $windSpeedData <= 61) {
                $description = "BLUE Alert: Wind Signal No.1! Naitala ang $windSpeedData km/h sa $barangay ($currentTime). Posibleng magdulot ng kaunting pinsala sa mga bahay, puno, o ari-arian, mag-ingat.";
                $alert = "Blue";
            } else if ($windSpeedData >= 62 && $windSpeedData <= 88) {
                $description = "BLUE Alert: Wind Signal No.2! Naitala ang $windSpeedData km/h sa $barangay ($currentTime). Posibleng magdulot ng kaunti hanggang katamtamang pinsala sa bahay kaya mag-ingat.";
                $alert = "Blue";
            } else if ($windSpeedData >= 89 && $windSpeedData <= 117) {
                $description = "BLUE Alert: Wind Signal No.3! Naitala ang $windSpeedData km/h sa $barangay ($currentTime). Mag-ingat sa lumilipad na debris na maaaring makasugat o makasira ng ari-arian.";
                $alert = "Blue";
            } else if ($windSpeedData >= 118 && $windSpeedData <= 184) {
                $description = "RED Alert: Wind Signal No.4! Naitala ang $windSpeedData km/h sa $barangay ($currentTime). Mag-ingat sa posibleng pagbagsak ng pader na maaaring makasugat o makasira ng bahay.";
                $alert = "Red";
            } else if ($windSpeedData >= 185) {
                $description = "RED Alert: Wind Signal No.5! Naitala ang $windSpeedData km/h sa $barangay ($currentTime). Manatili sa ligtas na lugar dahil posibleng magdulot ito ng matinding pinsala.";
                $alert = "Red";
            }

            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $buoy->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            $insert = false;
            if (!$lastAlert) {
                $insert = true;
            } else {
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true;
                } else {
                    $insert = false;
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;
                DB::table('recent_alerts')->insert([
                    'alertId' => $alertId,
                    'buoy_id' => $buoy->id,
                    'description' => $description,
                    'alert_level' => $alert,
                    'sensor_type' => $sensorType,
                    'recorded_at' => $recorded,
                ]);
                if ($alert === 'Blue') {
                    $resetTime = 5;
                } elseif ($alert === 'Red') {
                    $resetTime = 10;
                }
                $buoyID = DB::table('buoys')
                    ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
                    ->where('buoys.barangay_id', $user->barangay_id ?? 5)
                    ->value('buoys.id');

                $relayState = 'on';
                $numbers    = [];

                if ($alert === 'Blue' || $alert === 'Red') {
                    $this->firebase->getReference($buoyName . '/RELAY_STATE')->set(true);
                    DB::table('relay_status')->insert([
                        'buoy_id' => $buoyID,
                        'relay_state' => $relayState,
                        'triggered_by' => $user->id,
                        'recorded_at' => $recorded,
                    ]);
                    broadcast(new AlertBroadcast([
                        'description' => $description,
                        'alert_level' => $alert,
                        'broadcast_by' => $user->last_name . ", " . $user->first_name,
                        'sensor_type' => $sensorType,
                        'recorded_at' => $recorded,
                    ]));
                    foreach ($usersId as $usergetId) {
                        $numberNormalized = $this->normalizePHNumber($usergetId->contact_number);
                        alerts::create([
                            'alert_id' => $alertId,
                            'broadcast_by' => $user->last_name . ", " . $user->first_name,
                            'user_id' => $usergetId->id,
                            'is_read' => false,
                            'recorded_at' => now(),
                        ]);

                        if ($numberNormalized) {
                            $numbers[] = $numberNormalized;
                        }
                    }
                    $phoneNumbers = implode(',', array_unique($numbers));

                    $data = [
                        'api_token' => '4e07eee9fca6d25f58f066453b1f258db25a2e5e',
                        'message' => $description,
                        'phone_number' => $phoneNumbers,
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/x-www-form-urlencoded',
                    ]);
                    curl_exec($ch);
                    curl_close($ch);
                    DB::table('recent_alerts')->where('sensor_type', $request->sensorTypes)->update(['alert_shown' => true]);
                    $notification = SystemNotifications::create([
                        'sender_id' => $user->id,
                        'receiver_id' => $user->id,
                        'barangay_id' => $user->barangay_id,
                        'receiver_role' => $user->user_type,
                        'title'  => 'Relay Status',
                        'body'  => 'The relay has been set to ON.',
                        'status'  => 'unread',
                        'created_at' => $recorded,
                    ]);

                    broadcast(new SystemNotificationSent($notification))->toOthers();
                }
            }
        }

        return $resetTime;
    }
    public function setRainPercentageAlert(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        $usersId = User::where('user_type', 'user')->get();
        if (empty($firebaseData)) {
            return null;
        }
        $barangay = DB::table('users')
            ->join('barangays', 'users.barangay_id', '=', 'barangays.id')
            ->where('users.barangay_id', $user->barangay_id ?? 5)
            ->value('barangays.name');

        $buoyCode = DB::table('buoys')
            ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
            ->where('buoys.barangay_id', $user->barangay_id ?? 5)
            ->value('buoys.buoy_code');
        $resetTime = null;
        foreach ($firebaseData as $buoyName => $buoyData) {
            if (!isset($buoyData['RAIN_GAUGE']['FALL_COUNT_MILIMETERS'])) {
                continue;
            }
            if ($buoyCode !== $buoyName) {
                continue;
            }
            $buoy = DB::table('buoys')->where('buoy_code', $buoyName)->first();
            if (!$buoy) {
                continue;
            }
            $rainSensor  = $buoyData['RAIN_GAUGE'];
            $rainData = $rainSensor['FALL_COUNT_MILIMETERS'];
            if (is_null($rainData) || $rainData == 0) {
                continue;
            }
            $description = null;
            $alert = null;
            $sensorType = 'RAIN GAUGE';
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
            $url = 'https://www.iprogsms.com/api/v1/sms_messages/send_bulk';
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
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $buoy->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            $insert = false;
            if (!$lastAlert) {
                $insert = true;
            } else {
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true;
                } else {
                    $insert = false;
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;
                DB::table('recent_alerts')->insert([
                    'alertId' => $alertId,
                    'buoy_id' => $buoy->id,
                    'description' => $description,
                    'alert_level' => $alert,
                    'sensor_type' => $sensorType,
                    'recorded_at' => $recorded,
                ]);
                if ($alert === 'Blue') {
                    $resetTime = 5;
                } elseif ($alert === 'Red') {
                    $resetTime = 10;
                }
                $buoyID = DB::table('buoys')
                    ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
                    ->where('buoys.barangay_id', $user->barangay_id ?? 5)
                    ->value('buoys.id');

                $relayState = 'on';
                $numbers    = [];

                if ($alert === 'Blue' || $alert === 'Red') {
                    $this->firebase->getReference($buoyName . '/RELAY_STATE')->set(true);
                    DB::table('relay_status')->insert([
                        'buoy_id' => $buoyID,
                        'relay_state' => $relayState,
                        'triggered_by' => $user->id,
                        'recorded_at' => $recorded,
                    ]);
                    broadcast(new AlertBroadcast([
                        'description' => $description,
                        'alert_level' => $alert,
                        'broadcast_by' => $user->last_name . ", " . $user->first_name,
                        'sensor_type' => $sensorType,
                        'recorded_at' => $recorded,
                    ]));
                    foreach ($usersId as $usergetId) {
                        $numberNormalized = $this->normalizePHNumber($usergetId->contact_number);
                        alerts::create([
                            'alert_id' => $alertId,
                            'broadcast_by' => $user->last_name . ", " . $user->first_name,
                            'user_id' => $usergetId->id,
                            'is_read' => false,
                            'recorded_at' => now(),
                        ]);

                        if ($numberNormalized) {
                            $numbers[] = $numberNormalized;
                        }
                    }
                    $phoneNumbers = implode(',', array_unique($numbers));

                    $data = [
                        'api_token' => '4e07eee9fca6d25f58f066453b1f258db25a2e5e',
                        'message' => $description,
                        'phone_number' => $phoneNumbers,
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/x-www-form-urlencoded',
                    ]);
                    curl_exec($ch);
                    curl_close($ch);
                    DB::table('recent_alerts')->where('sensor_type', $request->sensorTypes)->update(['alert_shown' => true]);
                    $notification = SystemNotifications::create([
                        'sender_id' => $user->id,
                        'receiver_id' => $user->id,
                        'barangay_id' => $user->barangay_id,
                        'receiver_role' => $user->user_type,
                        'title'  => 'Relay Status',
                        'body'  => 'The relay has been set to ON.',
                        'status'  => 'unread',
                        'created_at' => $recorded,
                    ]);

                    broadcast(new SystemNotificationSent($notification))->toOthers();
                }
            }
        }

        return $resetTime;
    }
    public function setWaterLevel(Request $request){
        $user = $request->user();
        $firebaseData = $this->firebase->getReference()->getValue();
        $usersId = User::where('user_type', 'user')->get();
        if (empty($firebaseData)) {
            return null;
        }
        $barangay = DB::table('users')
            ->join('barangays', 'users.barangay_id', '=', 'barangays.id')
            ->where('users.barangay_id', $user->barangay_id ?? 5)
            ->value('barangays.name');

        $buoyCode = DB::table('buoys')
            ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
            ->where('buoys.barangay_id', $user->barangay_id ?? 5)
            ->value('buoys.buoy_code');
        $resetTime = null;
        foreach ($firebaseData as $buoyName => $buoyData) {
            if (!isset($buoyData['MS5837']['WATER_LEVEL_FEET'])) {
                continue;
            }
            if ($buoyCode !== $buoyName) {
                continue;
            }
            $buoy = DB::table('buoys')->where('buoy_code', $buoyName)->first();
            if (!$buoy) {
                continue;
            }
            $waterlevel  = $buoyData['MS5837'];
            $waterlevelData = $waterlevel['WATER_LEVEL_FEET'];
            if (is_null($waterlevelData) || $waterlevelData == 0) {
                continue;
            }
            $description = null;
            $alert = null;
            $sensorType = 'WATER LEVEL';
            $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
            $url = 'https://www.iprogsms.com/api/v1/sms_messages/send_bulk';
            $brgyWhiteLevel = DB::table('users')->join('barangays', 'users.barangay_id', '=', 'barangays.id')->where('users.barangay_id', $user->barangay_id)
                ->value('barangays.white_level_alert');
            $brgBlueLevel = DB::table('users')->join('barangays', 'users.barangay_id', '=', 'barangays.id')->where('users.barangay_id', $user->barangay_id)
                ->value('barangays.blue_level_alert');
            $brgRedLevel = DB::table('users')->join('barangays', 'users.barangay_id', '=', 'barangays.id')->where('users.barangay_id', $user->barangay_id)
                ->value('barangays.red_level_alert');
            if ($waterlevelData < $brgBlueLevel) {
                $description = "WHITE Alert: Maging alerto sa lebel ng tubig! Naitala ang $waterlevelData feet kapasidad sa $barangay ($currentTime). Bantayan ang tubig at mag-ingat sa posibleng pagbaha.";
                $alert = "White";
            } else if ($waterlevelData < $brgRedLevel) {
                $description = "BLUE Alert: Maging alarma at mapanuri sa lebel ng tubig! Naitala ang $waterlevelData feet kapasidad sa $barangay ($currentTime). Malaki ang posibilidad ng pag-apaw ng tubig.";
                $alert = "Blue";
            } else if ($waterlevelData >= $brgRedLevel) {
                $description = "RED Alert: Maging mapanuri sa lebel ng tubig! Naitala ang $waterlevelData feet kapasidad sa $barangay ($currentTime). Agad na lumikas upang maiwasan ang panganib ng pagbaha.";
                $alert = "Red";
            }
            $uuid = Str::uuid();
            $alertId = 'ALERT' . $uuid;
            $lastAlert = DB::table('recent_alerts')
                ->where('buoy_id', $buoy->id)
                ->where('sensor_type', $sensorType)
                ->orderBy('recorded_at', 'desc')
                ->first();
            $insert = false;
            if (!$lastAlert) {
                $insert = true;
            } else {
                if ($lastAlert->alert_level !== $alert) {
                    $insert = true;
                } else {
                    $insert = false;
                }
            }
            if ($insert) {
                $uuid = Str::uuid();
                $alertId = 'ALERT' . $uuid;
                DB::table('recent_alerts')->insert([
                    'alertId' => $alertId,
                    'buoy_id' => $buoy->id,
                    'description' => $description,
                    'alert_level' => $alert,
                    'sensor_type' => $sensorType,
                    'recorded_at' => $recorded,
                ]);
                if ($alert === 'Blue') {
                    $resetTime = 5;
                } elseif ($alert === 'Red') {
                    $resetTime = 10;
                }
                $buoyID = DB::table('buoys')
                    ->join('barangays', 'buoys.barangay_id', '=', 'barangays.id')
                    ->where('buoys.barangay_id', $user->barangay_id ?? 5)
                    ->value('buoys.id');

                $relayState = 'on';
                $numbers = [];

                if ($alert === 'Blue' || $alert === 'Red') {
                    $this->firebase->getReference($buoyName . '/RELAY_STATE')->set(true);
                    DB::table('relay_status')->insert([
                        'buoy_id' => $buoyID,
                        'relay_state' => $relayState,
                        'triggered_by' => $user->id,
                        'recorded_at' => $recorded,
                    ]);
                    broadcast(new AlertBroadcast([
                        'description' => $description,
                        'alert_level' => $alert,
                        'broadcast_by' => $user->last_name . ", " . $user->first_name,
                        'sensor_type' => $sensorType,
                        'recorded_at' => $recorded,
                    ]));
                    foreach ($usersId as $usergetId) {
                        $numberNormalized = $this->normalizePHNumber($usergetId->contact_number);
                        alerts::create([
                            'alert_id' => $alertId,
                            'broadcast_by' => $user->last_name . ", " . $user->first_name,
                            'user_id' => $usergetId->id,
                            'is_read'  => false,
                            'recorded_at' => now(),
                        ]);

                        if ($numberNormalized) {
                            $numbers[] = $numberNormalized;
                        }
                    }
                    $phoneNumbers = implode(',', array_unique($numbers));

                    $data = [
                        'api_token' => '4e07eee9fca6d25f58f066453b1f258db25a2e5e',
                        'message' => $description,
                        'phone_number' => $phoneNumbers,
                    ];

                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/x-www-form-urlencoded',
                    ]);
                    curl_exec($ch);
                    curl_close($ch);
                    DB::table('recent_alerts')->where('sensor_type', $request->sensorTypes)->update(['alert_shown' => true]);
                    $notification = SystemNotifications::create([
                        'sender_id' => $user->id,
                        'receiver_id' => $user->id,
                        'barangay_id' => $user->barangay_id,
                        'receiver_role' => $user->user_type,
                        'title'  => 'Relay Status',
                        'body'  => 'The relay has been set to ON.',
                        'status'  => 'unread',
                        'created_at' => $recorded,
                    ]);

                    broadcast(new SystemNotificationSent($notification))->toOthers();
                }
            }
        }

        return $resetTime;
    }
    public function allAlerts(){
        try {
            $resetTime = 0;
            DB::transaction(function () use (&$resetTime) {
                $request = request();
                $TempReset = $this->setTemperatureAlert($request);
                $WaterTemp = $this->setWaterTemperatureAlert($request);
                $Humidity = $this->setHumidityAlert($request);
                $Atmospheric = $this->setAtmosphericAlert($request);
                $Wind = $this->setWindAlert($request);
                $RainPercentage = $this->setRainPercentageAlert($request);
                $WaterLevel = $this->setWaterLevel($request);
                $resetTime = max(is_numeric($TempReset) ? (int)$TempReset : 0,
                    is_numeric($WaterTemp) ? (int)$WaterTemp : 0,
                    is_numeric($Humidity) ? (int)$Humidity : 0,
                    is_numeric($Atmospheric) ? (int)$Atmospheric : 0,
                    is_numeric($Wind) ? (int)$Wind : 0,
                    is_numeric($RainPercentage) ? (int)$RainPercentage : 0,
                    is_numeric($WaterLevel) ? (int)$WaterLevel : 0,
                );
            });
            return response()->json(['success' => true, 'message' => 'all alerts processed successfully', 'reset' => $resetTime], 200);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'processing failed', 'error' => $e->getMessage(), 'line' => $e->getLine(),], 500);
        }
    }
}
