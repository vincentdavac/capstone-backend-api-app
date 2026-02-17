<?php

namespace App\Http\Controllers;

use App\Models\WindReading;
use App\Http\Requests\WindReadingRequest;
use App\Http\Resources\WindReadingResource;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


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

    public function fetchAllWindReading(WindReadingRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllWindReading request validated data', $validated);

            $query = WindReading::with('buoy')
                ->orderBy('recorded_at', 'asc');

            // Filter by buoy_id if provided
            if (!empty($validated['buoy_id'])) {
                $query->where('buoy_id', $validated['buoy_id']);
            }

            // Filter by from date
            if (!empty($validated['from'])) {
                $from = Carbon::parse($validated['from']);
                $query->where('recorded_at', '>=', $from);
            }

            // Filter by to date
            if (!empty($validated['to'])) {
                $to = Carbon::parse($validated['to']);
                $query->where('recorded_at', '<=', $to);
            }

            Log::info('fetchAllWindReading SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $readings = $query->get();

            foreach ($readings as $reading) {
                if (!$reading->buoy) {
                    Log::warning("Wind reading has missing buoy relation", [
                        'reading_id'     => $reading->id,
                        'wind_speed_m_s' => $reading->wind_speed_m_s,
                        'wind_speed_k_h' => $reading->wind_speed_k_h,
                        'report_status'  => $reading->report_status,
                        'recorded_at'    => $reading->recorded_at,
                    ]);
                }
            }

            return $this->success(
                WindReadingResource::collection($readings),
                'Wind readings fetched successfully'
            );
        } catch (\Exception $e) {

            Log::error('fetchAllWindReading failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch wind readings: ' . $e->getMessage(),
                500
            );
        }
    }
}
