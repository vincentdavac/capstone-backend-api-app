<?php

namespace App\Http\Controllers;

use App\Models\GpsReading;
use App\Http\Requests\StoreGpsReadingRequest;
use App\Http\Requests\UpdateGpsReadingRequest;
use App\Http\Resources\GpsReadingResource;
use App\Traits\HttpResponses;

class GpsReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return GpsReadingResource::collection(GpsReading::all());
    }

    public function store(StoreGpsReadingRequest $request)
    {
        $validated = $request->validated();
        $gps = GpsReading::create($validated);

        return (new GpsReadingResource($gps))
            ->response()
            ->setStatusCode(201);
    }

    public function show(GpsReading $gpsReading)
    {
        return new GpsReadingResource($gpsReading);
    }

    public function update(UpdateGpsReadingRequest $request, GpsReading $gpsReading)
    {
        $gpsReading->update($request->validated());
        return new GpsReadingResource($gpsReading);
    }

    public function destroy(GpsReading $gpsReading)
    {
        $gpsReading->delete();
        return $this->success('', 'GPS reading deleted successfully', 200);
    }
}
