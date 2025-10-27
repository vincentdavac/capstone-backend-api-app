<?php

namespace App\Http\Controllers;

use App\Models\RainReading;
use App\Http\Requests\StoreRainReadingRequest;
use App\Http\Requests\UpdateRainReadingRequest;
use App\Http\Resources\RainReadingResource;
use App\Traits\HttpResponses;

class RainReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return RainReadingResource::collection(RainReading::all());
    }

    public function store(StoreRainReadingRequest $request)
    {
        $validated = $request->validated();
        $rain = RainReading::create($validated);

        return (new RainReadingResource($rain))
            ->response()
            ->setStatusCode(201);
    }

    public function show(RainReading $rainReading)
    {
        return new RainReadingResource($rainReading);
    }

    public function update(UpdateRainReadingRequest $request, RainReading $rainReading)
    {
        $rainReading->update($request->validated());
        return new RainReadingResource($rainReading);
    }

    public function destroy(RainReading $rainReading)
    {
        $rainReading->delete();
        return $this->success('', 'Rain reading deleted successfully', 200);
    }
}
