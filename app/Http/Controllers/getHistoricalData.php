<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Buoy;


class getHistoricalData extends Controller
{
    public function fetchHistorical()
    {
        $data = Buoy::with([
            'bme280_atmospheric_readings' => function ($query){
                $query->select('buoy_id','pressure_hpa','recorded_at');
            },
             'Bme280HumidityReading'=> function ($query){
                $query->select('buoy_id','humidity');
            },
            'Bme280TemperatureReading'=> function ($query){
                $query->select('buoy_id','temperature_celsius','temperature_fahrenheit');
            },
            'DepthReading'=> function ($query){
                $query->select('buoy_id','depth_m','pressure_hpa');
            },
            'WaterTemperatureReading'=> function ($query){
                $query->select('buoy_id','temperature_celsius');
            },
            'RainGaugeReading'=> function ($query){
                $query->select('buoy_id','rainfall_mm');
            },
            'RainSensorReading'=> function ($query){
                $query->select('buoy_id','percentage');
            },
            
            'WindReading'=> function ($query){
                $query->select('buoy_id','wind_speed_k_h');
            },
        ])->get();

        return response()->json($data, 200, [], JSON_PRETTY_PRINT);

    }
}
