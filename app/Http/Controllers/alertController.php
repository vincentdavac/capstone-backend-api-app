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
        $this->reftblName = 'BUOY-2025-8664';
        //BME280
    }
    public function setTemperatureAlert()
    {
        $ref = $this->firebase->getReference($this->reftblName);
        $surroundingData = $ref->getValue();
        $bme280 = $surroundingData['BME280'];
        $surroundingTemp = $bme280['SURROUNDING_TEMPERATURE'];
        $description = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $alert = null;
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        $sensorType = 'Temperature';
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
        if ($surroundingTemp >= 27 && $surroundingTemp <= 32) {
            $description = " Heat index has reached $surroundingTemp °C in Barangay Zone A at $currentTime. Stay alert, prolonged outdoor activity may cause fatigue.";
            $alert = 'White';
        } else if ($surroundingTemp >= 33 && $surroundingTemp <= 41) {
            $description = "Heat index has reached $surroundingTemp °C in Barangay Zone B at $currentTime. Take precautions, heat cramps and heat exhaustion are possible.";
            $alert = 'Blue';
        } else if ($surroundingTemp >= 42 && $surroundingTemp <= 51) {
            $description = "Heat index has reached $surroundingTemp °C in Barangay Zone C at $currentTime. Danger, heat exhaustion is likely and heat stroke is probable with continued exposure.";
            $alert = 'Red';
        } else if ($surroundingTemp > 52) {
            $alert = 'Red';
            $description = "Heat index has reached $surroundingTemp °C in Barangay Zone D at $currentTime. Extreme danger, heat stroke imminent.";
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
        // return response()->json(['status' => 'success', 'data' => ['alertId' => $alertId, 'description' => $description, 'alert_level' =>
        // $alert, 'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }
    public function setWaterTemperatureAlert()
    {
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
        // return response()->json(['status' => 'success', 'data' => ['alertId' => $alertId, 'description' => $description, 'alert_level' =>
        // $alert, 'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }
    public function setHumidityAlert()
    {
        $ref = $this->firebase->getReference($this->reftblName);
        $data = $ref->getValue();
        $bme280 = $data['BME280'];
        $humidityData = $bme280['HUMIDITY'];
        $description = null;
        $alertLevel = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $sensorType = 'Humidity';
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
        if ($humidityData >= 60 && $humidityData <= 69) {
            $description = "Relative humidity has reached " . $humidityData . "% " . "in Barangay Zone B at " . $currentTime . " Monitor indoor air; mold growth and discomfort may begin.";
            $alertLevel = "White";
        } else if ($humidityData > 70) {
            $description = " Relative humidity has reached " . $humidityData . "% " . "in Barangay Zone C at " . $currentTime . " High humidity may cause discomfort, condensation, and increased risk of mold.";
            $alertLevel = "Blue";
        } else if ($humidityData >= 20 && $humidityData <= 29) {
            $alertLevel = "White";
            $description = "Relative humidity has dropped to " . $humidityData . "% " . "in Barangay Zone D at " . $currentTime . " Air feels dry; may cause skin, eye, and throat irritation.";
        } else if ($humidityData < 25) {
            $description = "Relative humidity has fallen to " . $humidityData . "%" . "in Barangay Zone E at " . $currentTime . "Very dry air, high risk of irritation and static electricity buildup.";
            $alertLevel = "Blue";
        } else {
            $description = "no data";
        }
        DB::table('alerts')->insert([
            'alertId' => $alertId,
            'description' => $description,
            'alert_level' => $alertLevel,
            'sensor_type' => $sensorType,
            'recorded_at' => $recorded
        ]);
        // return response()->json(['status' => 'success', 'data' => ['alertId' => $alertId, 'description' => $description, 'alert_level' =>
        // $alertLevel, 'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }
    public function setAtmosphericAlert()
    {
        $reference = $this->firebase->getReference($this->reftblName);
        $data = $reference->getValue();
        $bme280 = $data['BME280'];
        $atmosphericData = $bme280['ATMOSPHERIC_PRESSURE'];
        $description = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $alert = null;
        $sensorType = 'Atmospheric Pressure';
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
        if ($atmosphericData > 1013.2) {
            $description = "Atmospheric pressure is at $atmosphericData hPa in Barangay Zone A at $currentTime. Clear skies and calm weather conditions expected.";
            $alert = "White";
        } else if ($atmosphericData >= 1010 && $atmosphericData <= 1012) {
            $description = "Atmospheric pressure is at $atmosphericData hPa in Barangay Zone B at  $currentTime. Stable weather with mild conditions.";
            $alert = "White";
        } else if ($atmosphericData >= 1007 && $atmosphericData <= 1009) {
            $description = "Atmospheric pressure is at $atmosphericData hPa in Barangay Zone C at $currentTime. Increasing cloudiness; light rain possible.";
            $alert = "Blue";
        } else if ($atmosphericData < 1006) {
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
        // return response()->json(['status' => 'success', 'data' => ['alertId' => $alertId, 'description' => $description, 'alert_level' =>
        // $alert, 'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }

    public function setWindAlert()
    {
        $ref = $this->firebase->getReference($this->reftblName);
        $data = $ref->getValue();
        $anemometer = $data['ANEMOMETER'];
        $windSpeedData = $anemometer['WIND_SPEED_km_h'];
        $description = null;
        $alertLevel = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $sensorType = 'Wind Speed';
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
        if ($windSpeedData >= 39 && $windSpeedData <= 61) {
            $description = "Wind speed has reached $windSpeedData km/h in Barangay Zone A at $currentTime. Minimal to minor damage possible to light structures; trees sway and small branches may break.";
            $alertLevel = "White";
        } else if ($windSpeedData >= 62 && $windSpeedData <= 68) {
            $description = "Wind speed has reached $windSpeedData km/h in Barangay Zone B at $currentTime. Minor to moderate damage to light structures; unsecured objects may become projectiles.";
            $alertLevel = "Blue";
        } else if ($windSpeedData >= 89 && $windSpeedData <= 117) {
            $description = "Wind speed has reached $windSpeedData km/h in Barangay Zone C at $currentTime. Moderate to significant structural damage possible, widespread tree damage likely.";
            $alertLevel = "Blue";
        } else if ($windSpeedData >= 118 && $windSpeedData <= 184) {
            $description = "Wind speed has reached $windSpeedData km/h in Barangay Zone D at $currentTime. Severe damage expected, widespread destruction of buildings and infrastructure likely.";
            $alertLevel = "Red";
        } else if ($windSpeedData > 185) {
            $description = " Wind speed has reached $windSpeedData km/h in Barangay Zone E at $currentTime. Catastrophic damage, almost total destruction expected, very high risk to life and property.";
            $alertLevel = "Red";
        }

        DB::table('alerts')->insert([
            'alertId' => $alertId,
            'description' => $description,
            'alert_level' => $alertLevel,
            'sensor_type' => $sensorType,
            'recorded_at' => $recorded
        ]);
        // return response()->json(['status' => 'success', 'data' => ['alertId' => $alertId, 'description' => $description, 'alert_level' =>
        // $alertLevel, 'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }

    public function setRainPercentageAlert()
    {
        $ref = $this->firebase->getReference($this->reftblName);
        $data = $ref->getValue();
        $rainSensor = $data['RAIN_GAUGE'];
        $rainData  = $rainSensor['FALL_COUNT_MILIMETERS'];
        $description = null;
        $alertLevel = null;
        $uuid = Str::uuid();
        $alertId = 'ALERT' . $uuid;
        $sensorType = 'Rain';
        $currentTime = Carbon::now('Asia/Manila')->format('h:i A');
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

        if ($rainData < 1) {
            $description = "Rain Alert: Rainfall is occurring at a very light rate in Barangay Zone A at $currentTime. Scattered drops; surfaces remain mostly dry.";
            $alertLevel = "White";
        } else if ($rainData >= 1 && $rainData <= 3) {
            $description = "Rain Alert: Rainfall rate has reached $rainData mm/hr in Barangay Zone B at $currentTime. Individual drops easily identified; Surface wetness developing gradually. Small streams may flow in drains.";
            $alertLevel = "White";
        } else if ($rainData >= 4 && $rainData <= 8) {
            $description = "Rain Alert: Rainfall rate is $rainData mm/hr in Barangay Zone C at $currentTime. Water accumulations form quickly; downpipes flowing freely. Continuous rainfall with overcast sky.";
            $alertLevel = "Blue";
        } else if ($rainData > 8) {
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
        // return response()->json(['status' => 'success', 'data' => ['alertId' => $alertId, 'description' => $description, 'alert_level' =>
        // $alertLevel, 'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }

    public function setWaterPressure()
    {
        $ref = $this->firebase->getReference($this->reftblName);
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
        if ($waterPressureData < 100) {
            $description = "Water Pressure Alert: Water pressure is around $waterPressureData hPa in Barangay Zone A at $currentTime. Minor river flooding possible; minimal structural risk. Stay alert in low-lying areas.";
            $alertLevel = 'White';
        } else if ($waterPressureData >= 100 && $waterPressureData <= 200) {
            $description = "Water Pressure Alert: Water pressure is $waterPressureData hPa in Barangay Zone B at $currentTime. Noticeable water surge causing moderate disruption to river infrastructure; monitor closely.";
            $alertLevel = "Blue";
        } else if ($waterPressureData >= 200 && $waterPressureData <= 300) {
            $description = "Water Pressure Alert: Water pressure is $waterPressureData hPa in Barangay Zone C at $currentTime. Severe flooding conditions; significant threat to communities and riverbank structures. Prepare for evacuation if needed.";
            $alertLevel = "Red";
        } else if ($waterPressureData > 300) {
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
        // return response()->json(['status' => 'success', 'data' => ['alertId' => $alertId, 'description' => $description, 'alert_level' =>
        // $alertLevel, 'sensor_type' => $sensorType,]], 200, [], JSON_PRETTY_PRINT);
    }

    public function insertSensorData()
    {
        $reference = $this->firebase->getReference($this->reftblName);
        $data = $reference->getValue();
        $bme280 = $data['BME280'];
        $waterData = $data['MS5837'];
        $windData = $data['ANEMOMETER'];
        $RAIN_GAUGE = $data['RAIN_GAUGE'];
        $RAIN_SENSOR = $data['RAIN_SENSOR'];

        $id = DB::table('buoys')->select('id')->first();
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
            'buoy_id' => $id->id,
            'pressure_hpa' => $ATMOSPHERIC_PRESSURE,
            'pressure_mbar' => $ATMOSPHERIC_PRESSURE,
            'altitude' => $ALTITUDE,
            'recorded_at' => $recorded
        ]);

        DB::table('bme280_humidity_readings')->insert([
            'buoy_id' => $id->id,
            'humidity' => $humidity,
        ]);

        DB::table('bme280_temperature_readings')->insert([
            'buoy_id' => $id->id,
            'temperature_celsius' => $surrounding_temp,
            'temperature_fahrenheit' => $f,
        ]);

        DB::table('depth_readings')->insert([
            'buoy_id' => $id->id,
            'pressure_mbar' => $WATER_PRESSURE,
            'pressure_hpa' => $WATER_PRESSURE,
            'depth_m' => $WATER_LEVEL_METER,
            'depth_ft' => $WATER_LEVEL_FEET,
            'water_altitude' => $WATER_ALTITUDE,
            'recorded_at' => $recorded
        ]);

        DB::table('water_temperature_readings')->insert([
            'buoy_id' => $id->id,
            'temperature_celsius' => $WATER_TEMPERATURE,
            'temperature_fahrenheit' => $wf,
        ]);

        DB::table('wind_readings')->insert([
            'buoy_id' => $id->id,
            'wind_speed_m_s' => $WIND_SPEED_m_s,
            'wind_speed_k_h' => $WIND_SPEED_km_h,
        ]);
        DB::table('rain_gauge_readings')->insert([
            'buoy_id' => $id->id,
            'rainfall_mm' => $FALL_COUNT_MILIMETERS,
            'tip_count' => $TIP_COUNT,
        ]);
        DB::table('rain_sensor_readings')->insert([
            'buoy_id' => $id->id,
            'percentage' => $RAIN_PERCENTAGE,
            'recorded_at' => $recorded
        ]);
        // return response()->json(['status' => 'success', 'data' => $id], 200, [], JSON_PRETTY_PRINT);
    }

    public function allAlerts(Request $request){

        DB::transaction(function () {

            $this->setTemperatureAlert();
            $this->setWaterTemperatureAlert();
            $this->setHumidityAlert();
            $this->setAtmosphericAlert();
            $this->setWindAlert();
            $this->setRainPercentageAlert();
            $this->insertSensorData();
            $this->setWaterPressure();
        });

        return response()->json([
            'success' => true,
            'message' => 'All alerts processed successfully'
        ], 200);
    }
}
