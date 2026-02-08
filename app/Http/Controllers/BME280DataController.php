<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Http\Requests\BME280DataRequest;
use App\Http\Resources\BME280DataResource;
use App\Models\BME280Data;

class BME280DataController extends Controller
{
    use HttpResponses;

    /**
     * Store BME280 sensor data
     */
    public function store(BME280DataRequest $request)
    {
        // Extract validated input
        $temperatureCelsius = round($request->temperature_celsius, 2);
        $humidity           = round($request->humidity, 2);
        $pressureHpa        = round($request->pressure_hpa, 2);
        $altitude           = round($request->altitude, 2);

        // Convert values
        $temperatureFahrenheit = round(($temperatureCelsius * 9 / 5) + 32, 2);
        $pressureMbar          = round($pressureHpa, 2); // 1 hPa = 1 mbar

        // Get last saved data for this buoy
        $lastData = BME280Data::where('buoy_id', $request->buoy_id)
            ->latest('recorded_at')
            ->first();

        // Reject if data is unchanged
        if (
            $lastData &&
            $lastData->temperature_celsius == $temperatureCelsius &&
            $lastData->humidity == $humidity &&
            $lastData->pressure_hpa == $pressureHpa &&
            $lastData->altitude == $altitude
        ) {
            return $this->success(
                null,
                'BME280 data unchanged',
                200
            );
        }

        // Save new data (only if changed)
        $bme280Data = BME280Data::create([
            'buoy_id' => $request->buoy_id,

            'temperature_celsius'    => $temperatureCelsius,
            'temperature_fahrenheit' => $temperatureFahrenheit,

            'humidity' => $humidity,

            'pressure_hpa'  => $pressureHpa,
            'pressure_mbar' => $pressureMbar,

            'altitude' => $altitude,

            'recorded_at' => now(),
        ]);

        return $this->success(
            new BME280DataResource($bme280Data),
            'BME280 data recorded successfully',
            201
        );
    }
}
