<?php

namespace App\Http\Controllers;

use App\Models\VoltageReading;
use App\Http\Requests\StoreVoltageReadingRequest;
use App\Http\Requests\UpdateVoltageReadingRequest;
use App\Http\Resources\VoltageReadingResource;
use App\Traits\HttpResponses;

class VoltageReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return VoltageReadingResource::collection(VoltageReading::all());
    }

    public function store(StoreVoltageReadingRequest $request)
    {
        $validated = $request->validated();
        $reading = VoltageReading::create($validated);

        return (new VoltageReadingResource($reading))
            ->response()
            ->setStatusCode(201);
    }

    public function show(VoltageReading $voltageReading)
    {
        return new VoltageReadingResource($voltageReading);
    }

    public function update(UpdateVoltageReadingRequest $request, VoltageReading $voltageReading)
    {
        $voltageReading->update($request->validated());
        return new VoltageReadingResource($voltageReading);
    }

    public function destroy(VoltageReading $voltageReading)
    {
        $voltageReading->delete();
        return $this->success('', 'Voltage reading deleted successfully', 200);
    }
}
