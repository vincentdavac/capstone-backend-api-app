<?php

namespace App\Http\Controllers;

use App\Models\GpsReading;
use App\Models\Buoy;
use App\Http\Requests\GpsReadingRequest;
use App\Http\Resources\GpsReadingResource;
use App\Traits\HttpResponses;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;




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


    public function generateReport(Request $request)
    {
        $from = Carbon::parse($request->from);
        $to   = Carbon::parse($request->to);

        $readings = GpsReading::with('buoy')
            ->whereBetween('recorded_at', [$from, $to])
            ->orderBy('recorded_at', 'asc')
            ->get();

        //  Summary Statistics
        $totalReadings = $readings->count();
        $uniqueBuoys   = $readings->pluck('buoy_id')->unique()->count();
        $firstRecord   = $readings->first()?->recorded_at;
        $lastRecord    = $readings->last()?->recorded_at;

        //  Prepare chart data (per day count)
        $chartData = $readings
            ->groupBy(fn($item) => Carbon::parse($item->recorded_at)->format('Y-m-d'))
            ->map(fn($group) => $group->count());

        $pdf = Pdf::loadView('reports.gps-report', [
            'readings' => $readings,
            'from' => $from,
            'to' => $to,
            'totalReadings' => $totalReadings,
            'uniqueBuoys' => $uniqueBuoys,
            'firstRecord' => $firstRecord,
            'lastRecord' => $lastRecord,
            'chartData' => $chartData
        ])->setPaper('a4', 'portrait');

        return $pdf->download('gps-historical-report.pdf');
    }

    public function fetchAllReadings(GpsReadingRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllReadings request validated data', $validated);

            $query = GpsReading::with('buoy')->orderBy('recorded_at', 'asc');

            // Filter by buoy_id if provided
            if (isset($validated['buoy_id'])) {
                $query->where('buoy_id', $validated['buoy_id']);
            }

            // Filter by datetime range if both from/to provided
            if (isset($validated['from']) && isset($validated['to'])) {
                $from = Carbon::createFromFormat('Y-m-d\TH:i', $validated['from']);
                $to   = Carbon::createFromFormat('Y-m-d\TH:i', $validated['to']);

                $query->whereBetween('recorded_at', [$from, $to]);
            }

            // Log the raw SQL query (for debugging)
            Log::info('fetchAllReadings SQL query', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

            $readings = $query->get();

            // Optional: Log if data has unexpected issues
            foreach ($readings as $reading) {
                if (!$reading->buoy) {
                    Log::warning("GPS reading has missing buoy relation", [
                        'reading_id' => $reading->id,
                        'latitude'   => $reading->latitude,
                        'longitude'  => $reading->longitude,
                        'recorded_at' => $reading->recorded_at,
                    ]);
                }
            }

            return $this->success(
                GpsReadingResource::collection($readings),
                'GPS readings fetched successfully'
            );
        } catch (\Exception $e) {
            Log::error('fetchAllReadings failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch GPS readings: ' . $e->getMessage(),
                500
            );
        }
    }
}
