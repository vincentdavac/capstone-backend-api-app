<?php

namespace App\Http\Controllers;

use App\Models\MS5837Data;
use App\Http\Requests\MS5837DataRequest;
use App\Http\Resources\MS5837DataResource;
use App\Traits\HttpResponses;

class MS5837DataController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return MS5837DataResource::collection(MS5837Data::with('buoy')->get());
    }

    public function store(MS5837DataRequest $request)
    {
        // Normalize and compute values
        $temperatureC = round($request->temperature_celsius, 2);
        $temperatureF = round(($temperatureC * 9 / 5) + 32, 2); // Fahrenheit
        $depthM       = round($request->depth_m, 2);
        $depthFt      = round($request->depth_ft, 2);
        $waterAlt     = round($request->water_altitude, 2);
        $waterPressure = round($request->water_pressure, 2);

        // Get last saved MS5837 data for this buoy
        $lastData = MS5837Data::where('buoy_id', $request->buoy_id)
            ->latest('recorded_at')
            ->first();

        // Reject if data is unchanged
        if (
            $lastData &&
            $lastData->temperature_celsius     == $temperatureC &&
            $lastData->depth_m                 == $depthM &&
            $lastData->depth_ft                == $depthFt &&
            $lastData->water_altitude          == $waterAlt &&
            $lastData->water_pressure          == $waterPressure
        ) {
            return $this->success(
                null,
                'MS5837 sensor data unchanged',
                200
            );
        }

        // Save new MS5837 data
        $ms5837 = MS5837Data::create([
            'buoy_id'                => $request->buoy_id,
            'temperature_celsius'    => $temperatureC,
            'temperature_fahrenheit' => $temperatureF,
            'depth_m'                => $depthM,
            'depth_ft'               => $depthFt,
            'water_altitude'         => $waterAlt,
            'water_pressure'         => $waterPressure,
            'recorded_at'            => now(), // Server-handled timestamp
        ]);

        // Return response with buoy info
        return $this->success(
            new MS5837DataResource($ms5837->load('buoy')),
            'MS5837 sensor data recorded successfully',
            201
        );
    }
}
