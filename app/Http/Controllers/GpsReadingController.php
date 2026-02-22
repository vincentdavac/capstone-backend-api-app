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
use Illuminate\Support\Facades\Auth;





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
        try {

            $request->validate([
                'buoy_id' => 'required|exists:buoys,id',
                'from'    => 'required|date',
                'to'      => 'sometimes|date',
            ]);

            $from = Carbon::parse($request->from);
            $to   = Carbon::parse($request->to);

            if ($from->greaterThan($to)) {
                return $this->error(null, "'From' date must not be greater than 'To' date", 422);
            }

            $buoy = Buoy::find($request->buoy_id);
            $buoyCode = $buoy->buoy_code;

            /*
        ======================================================
        INITIAL LOCATION (BUOY TABLE)
        ======================================================
        */
            $initialLat  = $buoy->latitude;
            $initialLng  = $buoy->longitude;
            $initialDate = Carbon::parse($buoy->created_at)
                ->format('F d Y - h:i A');

            /*
        ======================================================
        GPS READINGS WITHIN DATE RANGE
        ======================================================
        */
            $readings = GpsReading::where('buoy_id', $buoy->id)
                ->whereBetween('recorded_at', [$from, $to])
                ->orderBy('recorded_at', 'asc')
                ->get();

            if ($readings->isEmpty()) {
                return $this->error(null, "No GPS data found for selected date range.", 404);
            }

            /*
        ======================================================
        CURRENT LOCATION (LATEST READING IN RANGE)
        ======================================================
        */
            $latest = $readings->last();

            $currentLat  = $latest->latitude;
            $currentLng  = $latest->longitude;
            $currentDate = Carbon::parse($latest->recorded_at)
                ->format('F d Y - h:i A');

            /*
        ======================================================
        OTHER REPORT DATA
        ======================================================
        */
            $user = Auth::user();

            $formattedFrom = $from->format('F d Y - h:i A');
            $formattedTo   = $to->format('F d Y - h:i A');
            $generatedDate = Carbon::now()->format('F d Y - h:i A');

            /*
        ======================================================
        GENERATE PDF (NO MAP INCLUDED)
        ======================================================
        */
            $pdf = Pdf::loadView('reports.gps-historical-report', [
                'buoy'          => $buoy,
                'buoyCode'      => $buoyCode,
                'readings'      => $readings,
                'initialLat'    => $initialLat,
                'initialLng'    => $initialLng,
                'initialDate'   => $initialDate,
                'currentLat'    => $currentLat,
                'currentLng'    => $currentLng,
                'currentDate'   => $currentDate,
                'fromFormatted' => $formattedFrom,
                'toFormatted'   => $formattedTo,
                'generatedBy'   => $user ? $user->first_name . ' ' . $user->last_name : 'System',
                'generatedDate' => $generatedDate,
            ])
                ->setPaper('a4', 'portrait');

            return $pdf->download('GPS_Historical_Report.pdf');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }

    public function fetchAllReadings(GpsReadingRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllReadings request validated data', $validated);

            $query = GpsReading::with('buoy')
                ->orderBy('recorded_at', 'asc');

            if (!empty($validated['buoy_id'])) {
                $query->where('buoy_id', $validated['buoy_id']);
            }

            if (!empty($validated['from'])) {
                $from = Carbon::parse($validated['from']);
                $query->where('recorded_at', '>=', $from);
            }

            if (!empty($validated['to'])) {
                $to = Carbon::parse($validated['to']);
                $query->where('recorded_at', '<=', $to);
            }

            Log::info('fetchAllReadings SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $readings = $query->get();

            foreach ($readings as $reading) {
                if (!$reading->buoy) {
                    Log::warning("GPS reading has missing buoy relation", [
                        'reading_id'  => $reading->id,
                        'latitude'    => $reading->latitude,
                        'longitude'   => $reading->longitude,
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
