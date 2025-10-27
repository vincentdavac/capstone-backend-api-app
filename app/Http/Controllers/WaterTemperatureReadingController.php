<?php

namespace App\Http\Controllers;

use App\Models\WaterTemperatureReading;
use App\Http\Requests\StoreWaterTemperatureReadingRequest;
use App\Http\Requests\UpdateWaterTemperatureReadingRequest;
use App\Http\Resources\WaterTemperatureReadingResource;
use App\Traits\HttpResponses;

class WaterTemperatureReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return WaterTemperatureReadingResource::collection(WaterTemperatureReading::all());
    }

    public function store(StoreWaterTemperatureReadingRequest $request)
    {
        $validated = $request->validated();
        $reading = WaterTemperatureReading::create($validated);
        return (new WaterTemperatureReadingResource($reading))
            ->response()
            ->setStatusCode(201);
    }

    public function show(WaterTemperatureReading $waterTemperatureReading)
    {
        return new WaterTemperatureReadingResource($waterTemperatureReading);
    }

    public function update(UpdateWaterTemperatureReadingRequest $request, WaterTemperatureReading $waterTemperatureReading)
    {
        $waterTemperatureReading->update($request->validated());
        return new WaterTemperatureReadingResource($waterTemperatureReading);
    }

    public function destroy(WaterTemperatureReading $waterTemperatureReading)
    {
        $waterTemperatureReading->delete();
        return $this->success('', 'Water temperature reading deleted successfully', 200);
    }
}
