<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class testingWeather extends Controller
{
    public function getWeather()
    {
        // 14.653742001749142, 120.99476198312918
        $latitude =  14.6514;
        $longitude = 120.9902;

        $weather = Http::get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'daily' => 'temperature_2m_max,temperature_2m_min,precipitation_probability_max,weathercode,windspeed_10m_max',
            'forecast_days' => 7,
            'timezone' => 'Asia/Manila',
        ]);
        $data = $weather->json();
          return $data;
        
    }
}
