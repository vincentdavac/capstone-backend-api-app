<?php

namespace App\Http\Controllers;

use App\Models\Bme280AtmosphericReading;
use App\Http\Requests\StoreBme280AtmosphericReadingRequest;
use App\Http\Requests\UpdateBme280AtmosphericReadingRequest;
use App\Http\Resources\Bme280AtmosphericReadingResource;
use App\Traits\HttpResponses;

class Bme280AtmosphericReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return Bme280AtmosphericReadingResource::collection(Bme280AtmosphericReading::all());
    }

    public function store(StoreBme280AtmosphericReadingRequest $request)
    {
        $validated = $request->validated();
        $reading = Bme280AtmosphericReading::create($validated);
        return (new Bme280AtmosphericReadingResource($reading))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Bme280AtmosphericReading $bme280AtmosphericReading)
    {
        return new Bme280AtmosphericReadingResource($bme280AtmosphericReading);
    }

    public function update(UpdateBme280AtmosphericReadingRequest $request, Bme280AtmosphericReading $bme280AtmosphericReading)
    {
        $bme280AtmosphericReading->update($request->validated());
        return new Bme280AtmosphericReadingResource($bme280AtmosphericReading);
    }

    public function destroy(Bme280AtmosphericReading $bme280AtmosphericReading)
    {
        $bme280AtmosphericReading->delete();
        return $this->success('', 'BME280 reading deleted successfully', 200);
    }
}
