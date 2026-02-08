<?php

namespace App\Http\Controllers;

use App\Models\WindReading;
use App\Http\Requests\WindReadingRequest;
use App\Http\Resources\WindReadingResource;
use App\Traits\HttpResponses;

class WindReadingController extends Controller
{
    use HttpResponses;

    /**
     * List all wind readings with optional buoy info.
     */
    public function index()
    {
        return WindReadingResource::collection(WindReading::with('buoy')->get());
    }

    /**
     * Store a new wind reading.
     */
    public function store(WindReadingRequest $request)
    {
        // Normalize and round values
        $windM_s = round($request->wind_speed_m_s, 2);
        $windK_h = round($request->wind_speed_k_h, 2);

        // Get last reading for this buoy
        $lastReading = WindReading::where('buoy_id', $request->buoy_id)
            ->latest('recorded_at')
            ->first();

        // Reject if unchanged
        if (
            $lastReading &&
            $lastReading->wind_speed_m_s == $windM_s &&
            $lastReading->wind_speed_k_h == $windK_h
        ) {
            return $this->success(
                null,
                'Wind reading unchanged',
                200
            );
        }

        // Save new reading
        $reading = WindReading::create([
            'buoy_id'        => $request->buoy_id,
            'wind_speed_m_s' => $windM_s,
            'wind_speed_k_h' => $windK_h,
            'report_status'  => $request->report_status ?? 'Pending',
            'recorded_at'    => now(), // server-handled
        ]);

        // Return resource with buoy info
        return $this->success(
            new WindReadingResource($reading->load('buoy')),
            'Wind reading recorded successfully',
            201
        );
    }
}
