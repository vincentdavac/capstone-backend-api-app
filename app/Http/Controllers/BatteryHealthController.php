<?php

namespace App\Http\Controllers;

use App\Models\BatteryHealth;
use App\Http\Requests\StoreBatteryHealthRequest;
use App\Http\Requests\UpdateVoltageReadingRequest;
use App\Http\Resources\VoltageReadingResource;
use App\Traits\HttpResponses;

class BatteryHealthController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return VoltageReadingResource::collection(BatteryHealth::all());
    }

    public function store(StoreBatteryHealthRequest $request)
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
