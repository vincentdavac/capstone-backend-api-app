<?php

namespace App\Http\Controllers;

use App\Models\GpsReading;
use App\Models\Buoy;
use App\Http\Requests\GpsReadingRequest;
use App\Http\Resources\GpsReadingResource;
use App\Traits\HttpResponses;

class GpsReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return GpsReadingResource::collection(
            GpsReading::with('buoy')->get()
        );
    }

    public function store(GpsReadingRequest $request)
    {
        $validated = $request->validated();

        // Find buoy
        $buoy = Buoy::find($validated['buoy_id']);

        if (!$buoy) {
            return $this->error(
                null,
                'Buoy not found',
                404
            );
        }

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
            'recorded_at' => now(), // server-handled
        ]);

        // Return response WITH buoy info
        return $this->success(
            new GpsReadingResource($gps->load('buoy')),
            'GPS reading stored successfully',
            201
        );
    }
}
