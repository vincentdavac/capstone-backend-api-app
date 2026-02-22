<?php

namespace App\Http\Controllers;

use App\Models\MS5837Data;
use App\Http\Requests\MS5837DataRequest;
use App\Http\Resources\MS5837DataResource;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Buoy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class MS5837DataController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return MS5837DataResource::collection(MS5837Data::with('buoy')->get());
    }

    public function store(MS5837DataRequest $request)
    {
        // Normalize and compute values
        $temperatureC = round($request->temperature_celsius, 2);
        $temperatureF = round(($temperatureC * 9 / 5) + 32, 2); // Fahrenheit
        $depthM       = round($request->depth_m, 2);
        $depthFt      = round($request->depth_ft, 2);
        $waterAlt     = round($request->water_altitude, 2);
        $waterPressure = round($request->water_pressure, 2);

        // Get last saved MS5837 data for this buoy
        $lastData = MS5837Data::where('buoy_id', $request->buoy_id)
            ->latest('recorded_at')
            ->first();

        // Reject if data is unchanged
        if (
            $lastData &&
            $lastData->temperature_celsius     == $temperatureC &&
            $lastData->depth_m                 == $depthM &&
            $lastData->depth_ft                == $depthFt &&
            $lastData->water_altitude          == $waterAlt &&
            $lastData->water_pressure          == $waterPressure
        ) {
            return $this->success(
                null,
                'MS5837 sensor data unchanged',
                200
            );
        }

        // Save new MS5837 data
        $ms5837 = MS5837Data::create([
            'buoy_id'                => $request->buoy_id,
            'temperature_celsius'    => $temperatureC,
            'temperature_fahrenheit' => $temperatureF,
            'depth_m'                => $depthM,
            'depth_ft'               => $depthFt,
            'water_altitude'         => $waterAlt,
            'water_pressure'         => $waterPressure,
            'recorded_at'            => now(), // Server-handled timestamp
        ]);

        // Return response with buoy info
        return $this->success(
            new MS5837DataResource($ms5837->load('buoy')),
            'MS5837 sensor data recorded successfully',
            201
        );
    }

    public function fetchAllMS5837Data(MS5837DataRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllMS5837Data request validated data', $validated);

            $query = MS5837Data::with('buoy')
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

            Log::info('fetchAllMS5837Data SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $readings = $query->get();

            foreach ($readings as $reading) {
                if (!$reading->buoy) {
                    Log::warning("MS5837 reading has missing buoy relation", [
                        'reading_id'            => $reading->id,
                        'temperature_celsius'   => $reading->temperature_celsius,
                        'depth_m'               => $reading->depth_m,
                        'depth_ft'              => $reading->depth_ft,
                        'water_altitude'        => $reading->water_altitude,
                        'water_pressure'        => $reading->water_pressure,
                        'recorded_at'           => $reading->recorded_at,
                    ]);
                }
            }

            return $this->success(
                MS5837DataResource::collection($readings),
                'MS5837 sensor readings fetched successfully'
            );
        } catch (\Exception $e) {

            Log::error('fetchAllMS5837Data failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch MS5837 sensor readings: ' . $e->getMessage(),
                500
            );
        }
    }

    public function fetchDepthFtLast24Hours(MS5837DataRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchDepthFtLast24Hours request validated data', $validated);

            if (empty($validated['buoy_id'])) {
                return $this->error(
                    null,
                    'buoy_id is required',
                    422
                );
            }

            $since = Carbon::now()->subHours(24);

            $readings = MS5837Data::where('buoy_id', $validated['buoy_id'])
                ->where('recorded_at', '>=', $since)
                ->orderBy('recorded_at', 'asc')
                ->get(['id', 'buoy_id', 'depth_ft', 'recorded_at'])
                ->map(function ($reading) {
                    return [
                        'id'         => $reading->id,
                        'buoy_id'    => $reading->buoy_id,
                        'depth_ft'   => (int) round($reading->depth_ft), // remove decimals
                        'recorded_at' => Carbon::parse($reading->recorded_at)
                            ->format('F d, Y h:i A') // readable format
                    ];
                });

            Log::info('fetchDepthFtLast24Hours result count', [
                'count' => $readings->count()
            ]);

            return $this->success(
                $readings,
                'Depth (ft) readings for last 24 hours fetched successfully'
            );
        } catch (\Exception $e) {

            Log::error('fetchDepthFtLast24Hours failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch depth (ft) readings: ' . $e->getMessage(),
                500
            );
        }
    }

    public function generateReportMS5837(Request $request)
    {
        try {
            // ----------------- Validate Request -----------------
            $request->validate([
                'buoy_id' => 'required|exists:buoys,id',
                'from'    => 'required|date',
                'to'      => 'required|date',
            ]);

            $from = Carbon::parse($request->from);
            $to   = Carbon::parse($request->to);

            if ($from->greaterThan($to)) {
                return $this->error(null, "'From' date must not be greater than 'To' date", 422);
            }

            // ----------------- Fetch Buoy -----------------
            $buoy = Buoy::find($request->buoy_id);
            $buoyCode = $buoy->buoy_code;

            // ----------------- Fetch MS5837 Data -----------------
            $readings = MS5837Data::where('buoy_id', $buoy->id)
                ->whereBetween('recorded_at', [$from, $to])
                ->orderBy('recorded_at', 'asc')
                ->get();

            if ($readings->isEmpty()) {
                return $this->error(null, "No MS5837 data found for selected date range.", 404);
            }

            $user = Auth::user();
            $formattedFrom = $from->format('F d Y - h:i A');
            $formattedTo   = $to->format('F d Y - h:i A');
            $generatedDate = Carbon::now()->format('F d Y - h:i A');

            // ----------------- Prepare Chart -----------------
            $labels = $readings->pluck('recorded_at')
                ->map(fn($d) => Carbon::parse($d)->format('m/d H:i'))
                ->toArray();

            $depthFtData = $readings->pluck('depth_ft')->toArray();
            $pressureData = $readings->pluck('water_pressure')->toArray();

            $chartConfig = [
                'type' => 'line',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Depth (ft)',
                            'data' => $depthFtData,
                            'borderColor' => 'rgba(75, 192, 192, 1)',
                            'fill' => false,
                        ],
                        [
                            'label' => 'Water Pressure',
                            'data' => $pressureData,
                            'borderColor' => 'rgba(255, 99, 132, 1)',
                            'fill' => false,
                        ],
                    ],
                ],
                'options' => [
                    'plugins' => ['legend' => ['position' => 'top']],
                    'scales' => [
                        'x' => ['title' => ['display' => true, 'text' => 'Time']],
                        'y' => ['title' => ['display' => true, 'text' => 'Value']]
                    ]
                ]
            ];

            $chartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig));
            $chartImageData = @file_get_contents($chartUrl);
            $chartBase64 = $chartImageData
                ? 'data:image/png;base64,' . base64_encode($chartImageData)
                : null;

            // ----------------- Generate PDF -----------------
            $pdf = Pdf::loadView('reports.ms5837-report', [
                'buoy'          => $buoy,
                'buoyCode'      => $buoyCode,
                'readings'      => $readings,
                'chartBase64'   => $chartBase64,
                'fromFormatted' => $formattedFrom,
                'toFormatted'   => $formattedTo,
                'generatedBy'   => $user ? $user->first_name . ' ' . $user->last_name : 'System',
                'generatedDate' => $generatedDate,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("MS5837_Report_{$buoyCode}.pdf");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
