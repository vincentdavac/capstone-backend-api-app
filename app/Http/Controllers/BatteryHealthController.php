<?php

namespace App\Http\Controllers;

use App\Models\BatteryHealth;
use App\Models\Buoy;
use App\Http\Requests\BatteryHealthRequest;
use App\Http\Resources\BatteryHealthResource;
use App\Traits\HttpResponses;

class BatteryHealthController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return BatteryHealthResource::collection(
            BatteryHealth::with('buoy')->get()
        );
    }

    public function store(BatteryHealthRequest $request)
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

        // Normalize values
        $percentage = round($validated['percentage'], 2);
        $voltage    = round($validated['voltage'], 2);

        // Server-handled timestamp
        $recordedAt = $validated['recorded_at'] ?? now();

        // Prevent duplicate consecutive entries
        $lastBattery = BatteryHealth::where('buoy_id', $buoy->id)
            ->latest('recorded_at')
            ->first();

        if (
            $lastBattery &&
            $lastBattery->percentage == $percentage &&
            $lastBattery->voltage == $voltage
        ) {
            return $this->success(
                null,
                'Battery health unchanged',
                200
            );
        }

        // Store battery health
        $battery = BatteryHealth::create([
            'buoy_id'     => $buoy->id,
            'percentage'  => $percentage,
            'voltage'     => $voltage,
            'recorded_at' => $recordedAt,
        ]);

        // Return response WITH buoy info
        return $this->success(
            new BatteryHealthResource($battery->load('buoy')),
            'Battery health stored successfully',
            201
        );
    }
}
