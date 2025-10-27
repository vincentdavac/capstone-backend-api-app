<?php

namespace App\Http\Controllers;

use App\Models\Bme280HumidityReading;
use App\Http\Requests\StoreBme280HumidityReadingRequest;
use App\Http\Requests\UpdateBme280HumidityReadingRequest;
use App\Http\Resources\Bme280HumidityReadingResource;
use App\Traits\HttpResponses;

class Bme280HumidityReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return Bme280HumidityReadingResource::collection(Bme280HumidityReading::all());
    }

    public function store(StoreBme280HumidityReadingRequest $request)
    {
        $validated = $request->validated();
        $humidity = Bme280HumidityReading::create($validated);

        return (new Bme280HumidityReadingResource($humidity))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Bme280HumidityReading $bme280HumidityReading)
    {
        return new Bme280HumidityReadingResource($bme280HumidityReading);
    }

    public function update(UpdateBme280HumidityReadingRequest $request, Bme280HumidityReading $bme280HumidityReading)
    {
        $bme280HumidityReading->update($request->validated());
        return new Bme280HumidityReadingResource($bme280HumidityReading);
    }

    public function destroy(Bme280HumidityReading $bme280HumidityReading)
    {
        $bme280HumidityReading->delete();
        return $this->success('', 'Humidity reading deleted successfully', 200);
    }
}
