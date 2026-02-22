<?php

namespace App\Http\Controllers;

use App\Models\RainSensorReading;
use App\Http\Requests\RainSensorReadingRequest;
use App\Http\Resources\RainReadingResource;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Buoy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class RainSensorReadingController extends Controller
{
    use HttpResponses;

    /**
     * Display all rain sensor readings
     */
    public function index()
    {
        $readings = RainSensorReading::with('buoy')
            ->latest('recorded_at')
            ->get();

        return RainReadingResource::collection($readings);
    }

    /**
     * Store a new rain sensor reading
     */
    public function store(RainSensorReadingRequest $request)
    {
        // Normalize input
        $percentage = round($request->percentage, 2);

        // Get last reading for this buoy
        $lastReading = RainSensorReading::where('buoy_id', $request->buoy_id)
            ->latest('recorded_at')
            ->first();

        // Anti-spam: reject unchanged value
        if ($lastReading && $lastReading->percentage == $percentage) {
            return $this->success(
                null,
                'Rain sensor data unchanged',
                200
            );
        }

        // Save new reading
        $rain = RainSensorReading::create([
            'buoy_id'     => $request->buoy_id,
            'percentage'  => $percentage,
            'recorded_at' => now(),
        ]);

        return $this->success(
            new RainReadingResource(
                $rain->load('buoy')
            ),
            'Rain sensor data recorded successfully',
            201
        );
    }

    public function fetchAllRainSensorData(RainSensorReadingRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllRainSensorData request validated data', $validated);

            $query = RainSensorReading::with('buoy')
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

            Log::info('fetchAllRainSensorData SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $readings = $query->get();

            foreach ($readings as $reading) {
                if (!$reading->buoy) {
                    Log::warning("RainSensor reading has missing buoy relation", [
                        'reading_id'  => $reading->id,
                        'percentage'  => $reading->percentage,
                        'recorded_at' => $reading->recorded_at,
                    ]);
                }
            }

            return $this->success(
                RainReadingResource::collection($readings),
                'Rain sensor readings fetched successfully'
            );
        } catch (\Exception $e) {

            Log::error('fetchAllRainSensorData failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch rain sensor readings: ' . $e->getMessage(),
                500
            );
        }
    }

    public function generateReportRainMonitoring(Request $request)
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

            // ----------------- Fetch Rain Data -----------------
            $readings = RainSensorReading::where('buoy_id', $buoy->id)
                ->whereBetween('recorded_at', [$from, $to])
                ->orderBy('recorded_at', 'asc')
                ->get();

            if ($readings->isEmpty()) {
                return $this->error(null, "No rain monitoring data found for selected date range.", 404);
            }

            $user = Auth::user();
            $formattedFrom = $from->format('F d Y - h:i A');
            $formattedTo   = $to->format('F d Y - h:i A');
            $generatedDate = Carbon::now()->format('F d Y - h:i A');

            // ----------------- Prepare Chart -----------------
            $labels = $readings->pluck('recorded_at')
                ->map(fn($d) => Carbon::parse($d)->format('m/d H:i'))
                ->toArray();

            $rainData = $readings->pluck('percentage')->toArray();

            $chartConfig = [
                'type' => 'line',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Rain Level (%)',
                            'data' => $rainData,
                            'borderColor' => 'rgba(54, 162, 235, 1)',
                            'fill' => false,
                        ],
                    ],
                ],
                'options' => [
                    'plugins' => ['legend' => ['position' => 'top']],
                    'scales' => [
                        'x' => ['title' => ['display' => true, 'text' => 'Time']],
                        'y' => ['title' => ['display' => true, 'text' => 'Rain Percentage (%)']]
                    ]
                ]
            ];

            $chartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig));
            $chartImageData = @file_get_contents($chartUrl);
            $chartBase64 = $chartImageData
                ? 'data:image/png;base64,' . base64_encode($chartImageData)
                : null;

            // ----------------- Generate PDF -----------------
            $pdf = Pdf::loadView('reports.rain-monitoring-report', [
                'buoy'          => $buoy,
                'buoyCode'      => $buoyCode,
                'readings'      => $readings,
                'chartBase64'   => $chartBase64,
                'fromFormatted' => $formattedFrom,
                'toFormatted'   => $formattedTo,
                'generatedBy'   => $user ? $user->first_name . ' ' . $user->last_name : 'System',
                'generatedDate' => $generatedDate,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("Rain_Monitoring_Report_{$buoyCode}.pdf");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
