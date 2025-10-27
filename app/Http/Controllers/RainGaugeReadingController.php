<?php

namespace App\Http\Controllers;

use App\Models\RainGaugeReading;
use App\Http\Requests\StoreRainGaugeReadingRequest;
use App\Http\Requests\UpdateRainGaugeReadingRequest;
use App\Http\Resources\RainGaugeReadingResource;
use App\Traits\HttpResponses;

class RainGaugeReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return RainGaugeReadingResource::collection(RainGaugeReading::all());
    }

    public function store(StoreRainGaugeReadingRequest $request)
    {
        $validated = $request->validated();
        $rainGauge = RainGaugeReading::create($validated);
        return (new RainGaugeReadingResource($rainGauge))
            ->response()
            ->setStatusCode(201);
    }

    public function show(RainGaugeReading $rainGaugeReading)
    {
        return new RainGaugeReadingResource($rainGaugeReading);
    }

    public function update(UpdateRainGaugeReadingRequest $request, RainGaugeReading $rainGaugeReading)
    {
        $rainGaugeReading->update($request->validated());
        return new RainGaugeReadingResource($rainGaugeReading);
    }

    public function destroy(RainGaugeReading $rainGaugeReading)
    {
        $rainGaugeReading->delete();
        return $this->success('', 'Rain Gauge Reading deleted successfully', 200);
    }
}
