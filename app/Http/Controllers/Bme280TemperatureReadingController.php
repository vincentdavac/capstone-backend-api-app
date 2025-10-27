<?php

namespace App\Http\Controllers;

use App\Models\Bme280TemperatureReading;
use App\Http\Requests\StoreBme280TemperatureReadingRequest;
use App\Http\Requests\UpdateBme280TemperatureReadingRequest;
use App\Http\Resources\Bme280TemperatureReadingResource;
use App\Traits\HttpResponses;

class Bme280TemperatureReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return Bme280TemperatureReadingResource::collection(Bme280TemperatureReading::all());
    }

    public function store(StoreBme280TemperatureReadingRequest $request)
    {
        $validated = $request->validated();
        $temp = Bme280TemperatureReading::create($validated);

        return (new Bme280TemperatureReadingResource($temp))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Bme280TemperatureReading $bme280TemperatureReading)
    {
        return new Bme280TemperatureReadingResource($bme280TemperatureReading);
    }

    public function update(UpdateBme280TemperatureReadingRequest $request, Bme280TemperatureReading $bme280TemperatureReading)
    {
        $bme280TemperatureReading->update($request->validated());
        return new Bme280TemperatureReadingResource($bme280TemperatureReading);
    }

    public function destroy(Bme280TemperatureReading $bme280TemperatureReading)
    {
        $bme280TemperatureReading->delete();
        return $this->success('', 'Temperature reading deleted successfully', 200);
    }
}
