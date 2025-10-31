<?php

namespace App\Http\Controllers;

use App\Models\RainSensorReading;
use App\Http\Requests\StoreRainSensorReadingRequest;
use App\Http\Requests\UpdateRainReadingRequest;
use App\Http\Resources\RainReadingResource;
use App\Traits\HttpResponses;

class RainSensorReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return RainReadingResource::collection(RainSensorReading::all());
    }

    public function store(StoreRainSensorReadingRequest $request)
    {
        $validated = $request->validated();
        $rain = RainSensorReading::create($validated);

        return (new RainReadingResource($rain))
            ->response()
            ->setStatusCode(201);
    }

    public function show(RainSensorReading $rainReading)
    {
        return new RainReadingResource($rainReading);
    }

    public function update(UpdateRainReadingRequest $request, RainSensorReading $rainReading)
    {
        $rainReading->update($request->validated());
        return new RainReadingResource($rainReading);
    }

    public function destroy(RainSensorReading $rainReading)
    {
        $rainReading->delete();
        return $this->success('', 'Rain reading deleted successfully', 200);
    }
}
