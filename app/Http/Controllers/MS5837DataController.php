<?php

namespace App\Http\Controllers;

use App\Models\MS5837Data;
use App\Http\Requests\MS5837DataRequest;
use App\Http\Resources\MS5837DataResource;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


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

    public function fetchAllMS5837Data(MS5837DataRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllMS5837Data request validated data', $validated);

            $query = MS5837Data::with('buoy')
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

            Log::info('fetchAllMS5837Data SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $readings = $query->get();

            foreach ($readings as $reading) {
                if (!$reading->buoy) {
                    Log::warning("MS5837 reading has missing buoy relation", [
                        'reading_id'            => $reading->id,
                        'temperature_celsius'   => $reading->temperature_celsius,
                        'depth_m'               => $reading->depth_m,
                        'depth_ft'              => $reading->depth_ft,
                        'water_altitude'        => $reading->water_altitude,
                        'water_pressure'        => $reading->water_pressure,
                        'recorded_at'           => $reading->recorded_at,
                    ]);
                }
            }

            return $this->success(
                MS5837DataResource::collection($readings),
                'MS5837 sensor readings fetched successfully'
            );
        } catch (\Exception $e) {

            Log::error('fetchAllMS5837Data failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch MS5837 sensor readings: ' . $e->getMessage(),
                500
            );
        }
    }
}
