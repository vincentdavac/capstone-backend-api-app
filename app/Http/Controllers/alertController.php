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
            $description = " WHITE Alert: Normal operation, monitoring, & reporting. Heat Alert (Caution):  $surroundingTemp °C in Brgy Zone A at $currentTime. Stay alert; long outdoor exposure may cause fatigue.";
            $alert = 'White';
        } else if ($surroundingTemp >= 33 && $surroundingTemp <= 41) {
            $description = "BLUE Alert: Early emergency stage. 50% DRRMD on duty & standby. Heat Alert (Extreme Caution): $surroundingTemp °C in Brgy Zone B at $currentTime. Take care; heat cramps possible.";
            $alert = 'Blue';
        } else if ($surroundingTemp >= 42 && $surroundingTemp <= 51) {
            $description = "RED Alert: Imminent emergency. 100% DRRMD on duty & standby. Heat Alert (Danger): $surroundingTemp °C in Brgy Zone C at $currentTime. Extreme heat; exhaustion is possible.";
            $alert = 'Red';
        } else if ($surroundingTemp > 52) {
            $alert = 'Red';
            $description = "RED Alert: Imminent emergency. 100% DRRMD on duty & standby. Heat Alert (Extreme Danger): $surroundingTemp °C in Brgy Zone D at $currentTime. Extreme danger; heat stroke possible.";
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
            $description = "WHITE Alert: Normal operations, monitoring, & reporting. Water Temp Alert (Comfortable): $waterTemp °C in Brgy Zone C at $currentTime. Safe for water activity; minimal risk.";
            $alert = "White";
        } else if ($waterTemp >= 20 && $waterTemp <= 25) {
            $alert = "Blue";
            $description = "BLUE Alert: Early emergency stage. 50% DRRMD on duty & standby. Water Temp Alert (Cool): $waterTemp °C in Brgy Zone B at $currentTime. Cool water; caution for kids, elderly.
";
        } else if ($waterTemp < 20) {
            $alert = "Red";
            $description = "RED Alert: Imminent emergency. 100% DRRMD on duty & standby. Water Temp Alert (Very Cold): $waterTemp °C in Brgy Zone A at $currentTime. Risk of cold shock; limit exposure.";
        } else if ($waterTemp > 30) {
            $alert = "Red";
            $description = "RED Alert: Imminent emergency. 100% DRRMD on duty & standby. Water Temp Alert (Very Warm): $waterTemp °C in Zone D at $currentTime. Risk of overheating in water & fatigue.";
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
    public function setHumidityAlert(){
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
            $description = "WHITE Alert: Normal operations, monitoring, and reporting. Humidity Alert (Fair/High): $humidityData% in Zone D at $currentTime. Air slightly humid; ensure ventilation. ";
            $alertLevel = "White";
        } else if ($humidityData > 70) {
            $description = " BLUE Alert: Early emergency stage. 50% DRRMD on duty & standby. Humidity Alert (Poor/High): $humidityData% in Zone E at $currentTime. Excess moisture may cause mold/bacteria.";
            $alertLevel = "Blue";
        } else if ($humidityData >= 20 && $humidityData <= 29) {
            $alertLevel = "White";
            $description = "WHITE Alert: Normal operations, monitoring, & reporting. Humidity Alert (Fair/Low): $humidityData%  in Zone B at $currentTime. Air is slightly dry; it may cause discomfort.";
        } else if ($humidityData < 25) {
            $description = "BLUE Alert: Early emergency stage. 50% DRRMD on duty & standby. Humidity Alert (Poor/Low): $humidityData% in Zone A at $currentTime. Very dry air; may cause irritations.";
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
            $description = "WHITE Alert: Normal operations, monitoring, & reporting. Air Pressure Alert (High): $atmosphericData hPa in Zone A at $currentTime. Clear skies & calm weather expected.";
            $alert = "White";
        } else if ($atmosphericData >= 1010 && $atmosphericData <= 1012) {
            $description = "WHITE Alert: Normal operations, monitoring, & reporting. Air Pressure Alert (Normal): $atmosphericData hPa in Zone B at $currentTime. Stable weather with mild conditions.";
            $alert = "White";
        } else if ($atmosphericData >= 1007 && $atmosphericData <= 1009) {
            $description = "BLUE Alert: Early emergency stage. 50% DRRMD on duty & standby. Air Pressure Alert (Low): $atmosphericData hPa in Zone C at $currentTime. Increasing clouds; light rain possible.";
            $alert = "Blue";
        } else if ($atmosphericData < 1006) {
            $description = "RED Alert: Imminent emergency. 100% DRRMD on duty & standby. Air Pressure Alert (Very Low): $atmosphericData hPa in Zone D at $currentTime. Stormy; expect heavy rain, strong winds. ";
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
            $description = "WHITE Alert: Normal operations, monitoring, & reporting. Wind Alert (Strong Winds/TCWS #1): $windSpeedData km/h in Zone A at $currentTime. Minimal-minor damage is possible.";
            $alertLevel = "White";
        } else if ($windSpeedData >= 62 && $windSpeedData <= 68) {
            $description = "BLUE Alert: Early emergency stage. 50% DRRMD on duty & standby. Wind Alert (Gale-force Winds/TCWS #2): $windSpeedData in Zone B at $currentTime. Minor-moderate damage. ";
            $alertLevel = "Blue";
        } else if ($windSpeedData >= 89 && $windSpeedData <= 117) {
            $description = "BLUE Alert: Early emergency stage. 50% DRRMD on duty & standby. Wind Alert (Storm-force Winds/TCWS #3): $windSpeedData km/h in Zone C at $currentTime. Flying debris expected.";
            $alertLevel = "Blue";
        } else if ($windSpeedData >= 118 && $windSpeedData <= 184) {
            $description = "RED Alert: Imminent emergency. 100% DRRMD on duty & standby. Wind Alert (Typhoon-force Winds/TCWS #4): $windSpeedData km/h in Zone D at $currentTime. Possible wall collapse.";
            $alertLevel = "Red";
        } else if ($windSpeedData > 185) {
            $description = "RED Alert: Imminent emergency. 100% DRRMD on duty & standby. Wind Alert (Typhoon-force Winds/TCWS #5): $windSpeedData km/h in Barangay Zone E at $currentTime.  Extreme damage.";
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
            $description = "WHITE Alert: Normal operations, monitoring, & reporting. Rain Alert (Very Light Rain): $rainData mm/hr in Zone A at $currentTime. Scattered drops; surfaces mostly dry.";
            $alertLevel = "White";
        } else if ($rainData >= 1 && $rainData <= 3) {
            $description = "WHITE Alert: Normal operations, monitoring, & reporting. Rain Alert (Light Rain): $rainData mm/hr in Zone B at $currentTime. Surface wetness developing gradually.";
            $alertLevel = "White";
        } else if ($rainData >= 4 && $rainData <= 8) {
            $description = "BLUE Alert: Early emergency stage. 50% DRRMD on duty & standby. Rain Alert (Moderate Rain): $rainData mm/hr in Zone C at $currentTime. Water accumulations form quickly.";
            $alertLevel = "Blue";
        } else if ($rainData > 8) {
            $description = "RED Alert: Imminent emergency. 100% DRRMD on duty & standby. Rain Alert (Heavy Rain): > $rainData mm/hr in Zone D at $currentTime. Intense rain; may cause noise on roofs.";
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
            $description = "WHITE Alert: Normal operations, monitoring, & reporting. Water Pressure Alert (Minor Surge): $waterPressureData hPa in Zone A at $currentTime. Minor river flooding is possible.";
            $alertLevel = 'White';
        } else if ($waterPressureData >= 100 && $waterPressureData <= 200) {
            $description = "BLUE Alert: Early emergency stage. 50% DRRMD on duty & standby. Water Pressure Alert (Moderate Surge): $waterPressureData hPa in Zone B at $currentTime. Causes moderate disruption.";
            $alertLevel = "Blue";
        } else if ($waterPressureData >= 200 && $waterPressureData <= 300) {
            $description = "RED Alert: Imminent emergency. 100% DRRMD on duty & standby. Water Pressure Alert (Severe Surge): $waterPressureData hPa in Zone C at $currentTime. Severe flooding threat.";
            $alertLevel = "Red";
        } else if ($waterPressureData > 300) {
            $description = "RED Alert: Imminent emergency. 100% DRRMD on duty & standby. Water Pressure Alert (Extreme Surge): $waterPressureData hPa in Zone D at $currentTime. Extreme flooding; take action.";
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
    public function allAlerts(){
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
        return response()->json(['success' => true,'message' => 'All alerts processed successfully'], 200);
    }
}
