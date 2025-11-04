<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\DB;
use App\Models\Buoy;
use Carbon\Carbon;
use function Laravel\Prompts\select;

class insertSensorReadings extends Controller
{
    protected $firebase;
    protected string $ref_tblName;
    public function __construct(FirebaseServices $firebaseService)
    {
        $this->firebase = $firebaseService->getDatabase();
        $this->ref_tblName = 'BUOY-2025-8664';
    }
    public function insertSensorData()
    {
        $reference = $this->firebase->getReference($this->ref_tblName);
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
        $TIP_COUNT =$RAIN_GAUGE['TIP_COUNT'];
        $RAIN_PERCENTAGE = $RAIN_SENSOR['RAIN_PERCENTAGE'];
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
        $f = $surrounding_temp * 1.8 + 32;

        $wf = $WATER_TEMPERATURE * 1.8 + 32;
        DB::table('bme280_atmospheric_readings')->insert([
            'buoy_id' => $id->id,
            'pressure_hpa' => $ATMOSPHERIC_PRESSURE,
            'pressure_mbar'=>$ATMOSPHERIC_PRESSURE,
            'altitude' => $ALTITUDE, 
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
        ]);
        return response()->json(['status' => 'success', 'data' => $id], 200, [], JSON_PRETTY_PRINT);
    }
}
