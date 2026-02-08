<?php

namespace App\Http\Controllers;

use App\Models\BME280Data;
use App\Models\Buoy;
use App\Http\Requests\BME280DataRequest;
use App\Http\Resources\BME280DataResource;
use App\Traits\HttpResponses;

class BME280DataController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of BME280 data WITH buoy
     */
    public function index()
    {
        return BME280DataResource::collection(
            BME280Data::with('buoy')->get()
        );
    }

    /**
     * Store BME280 sensor data
     */
    public function store(BME280DataRequest $request)
    {
        $validated = $request->validated();

        // Find buoy
        $buoy = Buoy::find($validated['buoy_id']);

        if (!$buoy) {
            return $this->error(
                null,
                'Buoy not found',
                404
            );
        }

        // Normalize + round values
        $temperatureCelsius = round($validated['temperature_celsius'], 2);
        $humidity           = round($validated['humidity'], 2);
        $pressureHpa        = round($validated['pressure_hpa'], 2);
        $altitude           = round($validated['altitude'], 2);

        // Derived values
        $temperatureFahrenheit = round(($temperatureCelsius * 9 / 5) + 32, 2);
        $pressureMbar          = $pressureHpa; // 1 hPa = 1 mbar

        // Server-handled timestamp
        $recordedAt = $validated['recorded_at'] ?? now();

        // Prevent duplicate consecutive entries
        $lastData = BME280Data::where('buoy_id', $buoy->id)
            ->latest('recorded_at')
            ->first();

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

        // Store BME280 data
        $bme280 = BME280Data::create([
            'buoy_id' => $buoy->id,

            'temperature_celsius'    => $temperatureCelsius,
            'temperature_fahrenheit' => $temperatureFahrenheit,

            'humidity' => $humidity,

            'pressure_hpa'  => $pressureHpa,
            'pressure_mbar' => $pressureMbar,

            'altitude' => $altitude,

            'recorded_at' => $recordedAt,
        ]);

        // Return response WITH buoy info
        return $this->success(
            new BME280DataResource($bme280->load('buoy')),
            'BME280 data recorded successfully',
            201
        );
    }
}
