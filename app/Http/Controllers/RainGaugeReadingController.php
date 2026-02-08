<?php

namespace App\Http\Controllers;

use App\Models\RainGaugeReading;
use App\Http\Requests\RainGaugeReadingRequest;
use App\Http\Resources\RainGaugeReadingResource;
use App\Traits\HttpResponses;

class RainGaugeReadingController extends Controller
{
    use HttpResponses;

    public function index()
    {
        // Return all RainGauge readings with their buoy info
        return RainGaugeReadingResource::collection(RainGaugeReading::with('buoy')->get());
    }

    public function store(RainGaugeReadingRequest $request)
    {
        // Normalize values
        $rainfall = round($request->rainfall_mm, 2);
        $tipCount = (int) $request->tip_count;

        // Get last saved reading for this buoy
        $lastReading = RainGaugeReading::where('buoy_id', $request->buoy_id)
            ->latest('recorded_at')
            ->first();

        // Reject if data is unchanged
        if (
            $lastReading &&
            $lastReading->rainfall_mm == $rainfall &&
            $lastReading->tip_count  == $tipCount
        ) {
            return $this->success(
                null,
                'Rain gauge data unchanged',
                200
            );
        }

        // Save new reading
        $rainGauge = RainGaugeReading::create([
            'buoy_id'      => $request->buoy_id,
            'rainfall_mm'  => $rainfall,
            'tip_count'    => $tipCount,
            'recorded_at'  => now(), // Server-handled timestamp
        ]);

        return $this->success(
            new RainGaugeReadingResource($rainGauge->load('buoy')),
            'Rain gauge data recorded successfully',
            201
        );
    }
}
