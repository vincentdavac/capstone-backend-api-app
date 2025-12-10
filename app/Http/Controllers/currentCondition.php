<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class currentCondition extends Controller{
    public function getWind(){
       DB::table('wind_readings', )->select('wind_speed_m_s')->latest('recorded_at')->first();
       return DB::table('wind_readings')->latest('recorded_at')->first();
        
    }
    public function getWater() {
        return DB::table('depth_readings')->latest()->first();
        
    }
    public function getHumidity(){
        return DB::table('bme280_humidity_readings')->latest('recorded_at')->first();
        
    }
    public function getRain(){
        return DB::table('rain_sensor_readings')->latest('recorded_at')->first();
        
    }
    public function getSurrrounding(){
        return DB::table('bme280_temperature_readings')->latest('recorded_at')->first();       
    }
    public function getWaterTemp() {
        return DB::table('water_temperature_readings')->latest('recorded_at')->first();
    }

    public function getCurrentCondition(){
        $data = DB::transaction(function () {
            return [
                'wind' => $this->getWind(),
                'water' => $this->getWater(),
                'humidity' => $this->getHumidity(),
                'rain' => $this->getRain(),
                'surrounding' => $this->getSurrrounding(),
                'water_temp' => $this->getWaterTemp(),
            ];
        });
        
        return response()->json(['success' => true, 'data' => $data], 200,[],JSON_PRETTY_PRINT);
    }
}
