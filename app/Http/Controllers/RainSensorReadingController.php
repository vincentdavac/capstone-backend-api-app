<?php

namespace App\Http\Controllers;

use App\Models\RainSensorReading;
use App\Http\Requests\RainSensorReadingRequest;
use App\Http\Resources\RainReadingResource;
use App\Traits\HttpResponses;

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
}
