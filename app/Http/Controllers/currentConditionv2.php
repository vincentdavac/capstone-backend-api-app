<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class currentConditionv2 extends Controller
{
    public function getwindKh()
    {
        $kh = DB::table('wind_readings')->select('wind_speed_k_h', 'recorded_at')->latest('recorded_at')->first();
        if ($kh) {
            $windKh = (int)$kh->wind_speed_k_h;
        } else {
            $windKh = 0;
        }
        // $windKh = (int)$kh->wind_speed_k_h;
        $recorded = null;

        // $recorded_at = $kh->recorded_at;
        if ($kh === null || !$kh) {
            $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
        } else {
            $recorded_at = $kh->recorded_at;
            if ($recorded_at == null) {
                $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');
            } else {
                $recorded = $recorded_at;
            }
        }
        $status = null;
        if ($windKh >= 39 && $windKh <= 61) {
            $status = "White";
        } else if ($windKh >= 62 && $windKh <= 88) {
            $status = "Blue";
        } else if ($windKh >= 89 && $windKh <= 117) {
            $status = "Blue";
        } else {
            $status = "Red";
        }
        return ['wind_speed_k_h' => $windKh, 'recorded_at' => $recorded, 'status' => $status];
    }
    public function getwindMs(){
        $kh = DB::table('wind_readings')->select('wind_speed_m_s')->latest('recorded_at')->first();
        // $windMs = (int)$kh->wind_speed_m_s;
        if ($kh) {
            $windMs = (int)$kh->wind_speed_k_h;
        } else {
            $windMs = 0;
        }
        $status = null;
        if ($windMs >= 39 && $windMs <= 61) {
            $status = "White";
        } else if ($windMs >= 62 && $windMs <= 88) {
            $status = "Blue";
        } else if ($windMs >= 89 && $windMs <= 117) {
            $status = "Blue";
        } else {
            $status = "Red";
        }
        return ['wind_speed_m_s' => $windMs, 'status' => $status];
    }
    public function getdepth()
    {
        $depth = DB::table('depth_readings')->select('depth_ft')->latest()->first();
        // $depthFeet = $depth->depth_ft;
        if ($depth) {
            $depthFeet = $depth->depth_ft;
        } else {
            $depthFeet = 0;
        }
        $div = $depthFeet / 17;
        $finalData = $div * 100;
        $status = null;
        if ($finalData <= 40) {
            $status = 'White';
        } else if ($finalData >= 41 && $finalData <= 60) {
            $status = 'Blue';
        } else if ($finalData >= 61 && $finalData <= 99) {
            $status = 'Blue';
        } else {
            $status = 'Red';
        }
        return ['depth' => $depthFeet, 'status' => $status];
    }
    public function getHumidity()
    {
        $humidity = DB::table('bme280_humidity_readings')->select('humidity')->latest('recorded_at')->first();
        // $humidityData = $humidity->humidity;
        if ($humidity) {
            $humidityData = $humidity->humidity;
        } else {
            $humidityData = 0;
        }
        $status = null;
        if ($humidityData >= 30 && $humidityData <= 59) {
            $status = "White";
        } else if ($humidityData >= 60 && $humidityData <= 69) {
            $status = "Blue";
        } else if ($humidityData >= 25 && $humidityData <= 29) {
            $status = "Blue";
        } else if ($humidityData < 25) {
            $status = "Red";
        } else if ($humidityData > 70) {
            $status = "Red";
        }
        return ['humidity' => $humidityData, 'status' => $status];
    }
    public function getRain()
    {
        $rain = DB::table('rain_gauge_readings')->select('rainfall_mm')->latest('recorded_at')->first();
        // $rainData = $rain->rainfall_mm;
        if ($rain) {
            $rainData = $rain->rainfall_mm;
        } else {
            $rainData = 0;
        }
        $status = null;
        if ($rainData < 1) {
            $status = "White";
        } else if ($rainData >= 1 && $rainData <= 3) {
            $status = "White";
        } else if ($rainData >= 4 && $rainData <= 8) {
            $status = "Blue";
        } else if ($rainData > 8) {
            $status = "Red";
        }
        return ['rainFall' => $rainData, 'status' => $status];
    }
    public function getSurrrounding()
    {
        $surrounding = DB::table('bme280_temperature_readings')->select('temperature_celsius')->latest('recorded_at')->first();
        // $surroundingData = $surrounding->temperature_celsius;
        if($surrounding) {
            $surroundingData = $surrounding->temperature_celsius;
        }else {
            $surroundingData = 0;
        }
        $status = null;
        if ($surroundingData >= 27 && $surroundingData <= 32) {
            $status = 'White';
        } else if ($surroundingData >= 33 && $surroundingData <= 41) {
            $status = 'Blue';
        } else if ($surroundingData >= 42 && $surroundingData <= 51) {
            $status = 'Red';
        } else if ($surroundingData > 52) {
            $status = 'Red';
        }
        return ['surrounding' => $surroundingData, 'status' => $status];
    }
    public function getwaterData()
    {
        $water = DB::table('water_temperature_readings')->select('temperature_celsius')->latest('recorded_at')->first();
        // $waterData = $water->temperature_celsius;
        if($water) {
            $waterData = $water->temperature_celsius;
        }else {
            $waterData = 0;
        }
        $status = null;
        if ($waterData >= 26 && $waterData <= 30) {
            $status = "White";
        } else if ($waterData >= 20 && $waterData <= 25) {
            $status = "Blue";
        } else if ($waterData < 20) {
            $status = "Red";
        } else if ($waterData > 30) {
            $status = "Red";
        }
        return ['waterTemp' => $waterData, 'status' => $status];
    }
    public function getCurrentCondition()
    {
        $data = DB::transaction(function () {
            return [
                'windKh' => $this->getwindKh(),
                'windms' => $this->getwindMs(),
                'waterDepth' => $this->getdepth(),
                'humidity' => $this->getHumidity(),
                'rain' => $this->getRain(),
                'surrounding' => $this->getSurrrounding(),
                'water_temp' => $this->getwaterData()

            ];
        });
        return response()->json(['success' => true, 'data' => $data], 200, [], JSON_PRETTY_PRINT);
    }
}
