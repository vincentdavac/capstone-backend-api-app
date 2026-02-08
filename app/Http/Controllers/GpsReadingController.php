<?php

namespace App\Http\Controllers;

use App\Models\GpsReading;
use App\Http\Requests\GpsReadingRequest;
use App\Http\Resources\GpsReadingResource;
use App\Traits\HttpResponses;
use App\Models\Buoy;

class GpsReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return GpsReadingResource::collection(GpsReading::all());
    }

    public function storeLongitudeAndLatitude(GpsReadingRequest $request)
    {
        $validated = $request->validated();

        // Find buoy by buoy_id only
        $buoy = Buoy::find($validated['buoy_id']);

        if (!$buoy) {
            return $this->error(
                null,
                'Buoy not found',
                404
            );
        }

        // Optional recorded_at (or default to now)
        $recordedAt = $validated['recorded_at'] ?? now();

        // Prevent duplicate GPS entries (same location)
        $lastReading = GpsReading::where('buoy_id', $buoy->id)
            ->latest('recorded_at')
            ->first();

        if (
            $lastReading &&
            $lastReading->latitude == $validated['latitude'] &&
            $lastReading->longitude == $validated['longitude']
        ) {
            return $this->success(
                null,
                'GPS location unchanged',
                200
            );
        }

        // Store GPS reading
        $gps = GpsReading::create([
            'buoy_id'     => $buoy->id,
            'latitude'    => $validated['latitude'],
            'longitude'   => $validated['longitude'],
            'recorded_at' => now(),
        ]);

        // Return using resource
        return $this->success(
            new GpsReadingResource($gps),
            'GPS reading stored successfully',
            201
        );
    }

    public function store(GpsReadingRequest $request)
    {
        $validated = $request->validated();
        $gps = GpsReading::create($validated);

        return (new GpsReadingResource($gps))
            ->response()
            ->setStatusCode(201);
    }
}
