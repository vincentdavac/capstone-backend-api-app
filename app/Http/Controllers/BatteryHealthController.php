<?php

namespace App\Http\Controllers;

use App\Models\BatteryHealth;
use App\Http\Requests\BatteryHealthRequest;
use App\Http\Requests\UpdateVoltageReadingRequest;
use App\Http\Resources\VoltageReadingResource;
use App\Traits\HttpResponses;
use App\Models\Buoy;
use App\Http\Resources\BatteryHealthResource;



class BatteryHealthController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return VoltageReadingResource::collection(BatteryHealth::all());
    }

    public function storeBatteryHealth(BatteryHealthRequest $request)
    {
        $validated = $request->validated();

        // Find buoy directly by buoy_id
        $buoy = Buoy::find($validated['buoy_id']);

        if (!$buoy) {
            return $this->error(
                null,
                'Buoy not found',
                404
            );
        }

        // Round values to 2 decimal places
        $percentage = round($validated['percentage'], 2);
        $voltage    = round($validated['voltage'], 2);

        // Optional recorded_at (or now if not provided)
        $recordedAt = isset($validated['recorded_at'])
            ? $validated['recorded_at']
            : now();

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

        // Store new battery health reading
        $battery = BatteryHealth::create([
            'buoy_id'     => $buoy->id,
            'percentage'  => $percentage,
            'voltage'     => $voltage,
            'recorded_at' => $recordedAt,
        ]);

        // Return using Resource for consistent formatting
        return $this->success(
            new BatteryHealthResource($battery),
            'Battery health stored successfully',
            201
        );
    }


    public function store(BatteryHealthRequest $request)
    {
        $validated = $request->validated();
        $reading = BatteryHealth::create($validated);

        return (new VoltageReadingResource($reading))
            ->response()
            ->setStatusCode(201);
    }

    public function show(BatteryHealth $voltageReading)
    {
        return new VoltageReadingResource($voltageReading);
    }

    public function update(UpdateVoltageReadingRequest $request, BatteryHealth $voltageReading)
    {
        $voltageReading->update($request->validated());
        return new VoltageReadingResource($voltageReading);
    }

    public function destroy(BatteryHealth $voltageReading)
    {
        $voltageReading->delete();
        return $this->success('', 'Voltage reading deleted successfully', 200);
    }
}
