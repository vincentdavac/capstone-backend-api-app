<?php

namespace App\Http\Controllers;

use App\Models\BME280Data;
use App\Models\Buoy;
use App\Http\Requests\BME280DataRequest;
use App\Http\Resources\BME280DataResource;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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


    public function fetchAllBME280Data(BME280DataRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllBME280Data request validated data', $validated);

            $query = BME280Data::with('buoy')
                ->orderBy('recorded_at', 'asc');

            // Filter by buoy_id if provided
            if (!empty($validated['buoy_id'])) {
                $query->where('buoy_id', $validated['buoy_id']);
            }

            // Filter by from date
            if (!empty($validated['from'])) {
                $from = Carbon::parse($validated['from']);
                $query->where('recorded_at', '>=', $from);
            }

            // Filter by to date
            if (!empty($validated['to'])) {
                $to = Carbon::parse($validated['to']);
                $query->where('recorded_at', '<=', $to);
            }

            Log::info('fetchAllBME280Data SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $readings = $query->get();

            foreach ($readings as $reading) {
                if (!$reading->buoy) {
                    Log::warning("BME280 reading has missing buoy relation", [
                        'reading_id'           => $reading->id,
                        'temperature_celsius'  => $reading->temperature_celsius,
                        'humidity'             => $reading->humidity,
                        'pressure_hpa'         => $reading->pressure_hpa,
                        'altitude'             => $reading->altitude,
                        'recorded_at'          => $reading->recorded_at,
                    ]);
                }
            }

            return $this->success(
                BME280DataResource::collection($readings),
                'BME280 readings fetched successfully'
            );
        } catch (\Exception $e) {

            Log::error('fetchAllBME280Data failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch BME280 readings: ' . $e->getMessage(),
                500
            );
        }
    }
}
