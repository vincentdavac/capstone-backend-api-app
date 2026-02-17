<?php

namespace App\Http\Controllers;

use App\Models\RainSensorReading;
use App\Http\Requests\RainSensorReadingRequest;
use App\Http\Resources\RainReadingResource;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RainSensorReadingController extends Controller
{
    use HttpResponses;

    /**
     * Display all rain sensor readings
     */
    public function index()
    {
        $readings = RainSensorReading::with('buoy')
            ->latest('recorded_at')
            ->get();

        return RainReadingResource::collection($readings);
    }

    /**
     * Store a new rain sensor reading
     */
    public function store(RainSensorReadingRequest $request)
    {
        // Normalize input
        $percentage = round($request->percentage, 2);

        // Get last reading for this buoy
        $lastReading = RainSensorReading::where('buoy_id', $request->buoy_id)
            ->latest('recorded_at')
            ->first();

        // Anti-spam: reject unchanged value
        if ($lastReading && $lastReading->percentage == $percentage) {
            return $this->success(
                null,
                'Rain sensor data unchanged',
                200
            );
        }

        // Save new reading
        $rain = RainSensorReading::create([
            'buoy_id'     => $request->buoy_id,
            'percentage'  => $percentage,
            'recorded_at' => now(),
        ]);

        return $this->success(
            new RainReadingResource(
                $rain->load('buoy')
            ),
            'Rain sensor data recorded successfully',
            201
        );
    }

    public function fetchAllRainSensorData(RainSensorReadingRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllRainSensorData request validated data', $validated);

            $query = RainSensorReading::with('buoy')
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

            Log::info('fetchAllRainSensorData SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $readings = $query->get();

            foreach ($readings as $reading) {
                if (!$reading->buoy) {
                    Log::warning("RainSensor reading has missing buoy relation", [
                        'reading_id'  => $reading->id,
                        'percentage'  => $reading->percentage,
                        'recorded_at' => $reading->recorded_at,
                    ]);
                }
            }

            return $this->success(
                RainReadingResource::collection($readings),
                'Rain sensor readings fetched successfully'
            );
        } catch (\Exception $e) {

            Log::error('fetchAllRainSensorData failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch rain sensor readings: ' . $e->getMessage(),
                500
            );
        }
    }
}
