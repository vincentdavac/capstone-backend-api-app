<?php

namespace App\Http\Controllers;

use App\Models\BatteryHealth;
use App\Models\Buoy;
use App\Http\Requests\BatteryHealthRequest;
use App\Http\Resources\BatteryHealthResource;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;


class BatteryHealthController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return BatteryHealthResource::collection(
            BatteryHealth::with('buoy')->get()
        );
    }

    public function store(BatteryHealthRequest $request)
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

        // Normalize values
        $percentage = round($validated['percentage'], 2);
        $voltage    = round($validated['voltage'], 2);

        // Server-handled timestamp
        $recordedAt = $validated['recorded_at'] ?? now();

        // Prevent duplicate consecutive entries
        $lastBattery = BatteryHealth::where('buoy_id', $buoy->id)
            ->latest('recorded_at')
            ->first();

        if (
            $lastBattery &&
            $lastBattery->percentage == $percentage &&
            $lastBattery->voltage == $voltage
        ) {
            return $this->success(
                null,
                'Battery health unchanged',
                200
            );
        }

        // Store battery health
        $battery = BatteryHealth::create([
            'buoy_id'     => $buoy->id,
            'percentage'  => $percentage,
            'voltage'     => $voltage,
            'recorded_at' => $recordedAt,
        ]);

        // Return response WITH buoy info
        return $this->success(
            new BatteryHealthResource($battery->load('buoy')),
            'Battery health stored successfully',
            201
        );
    }


    public function fetchAllBatteryHealth(BatteryHealthRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllBatteryHealth request validated data', $validated);

            $query = BatteryHealth::with('buoy')
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

            Log::info('fetchAllBatteryHealth SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $readings = $query->get();

            foreach ($readings as $reading) {
                if (!$reading->buoy) {
                    Log::warning("BatteryHealth reading has missing buoy relation", [
                        'reading_id'  => $reading->id,
                        'percentage'  => $reading->percentage,
                        'voltage'     => $reading->voltage,
                        'recorded_at' => $reading->recorded_at,
                    ]);
                }
            }

            return $this->success(
                BatteryHealthResource::collection($readings),
                'Battery health readings fetched successfully'
            );
        } catch (\Exception $e) {

            Log::error('fetchAllBatteryHealth failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch battery health readings: ' . $e->getMessage(),
                500
            );
        }
    }

    public function generateReportBatteryHealth(Request $request)
    {
        try {
            $request->validate([
                'buoy_id' => 'required|exists:buoys,id',
                'from'    => 'required|date',
                'to'      => 'required|date',
            ]);

            $from = Carbon::parse($request->from)->startOfMinute();
            $to   = Carbon::parse($request->to)->endOfMinute();

            if ($from->greaterThan($to)) {
                return $this->error(null, "'From' date must not be greater than 'To' date", 422);
            }

            $buoy = Buoy::findOrFail($request->buoy_id);
            $buoyCode = $buoy->buoy_code;

            // ============================================
            // DETECT RANGE TYPE
            // ============================================
            $diffInDays = $from->diffInDays($to);
            $diffInHours = $from->diffInHours($to);

            // Decide grouping format
            if ($diffInHours <= 24) {
                // TIME - TIME (Same day)
                $groupFormat = '%Y-%m-%d %H:00:00';
                $labelFormat = 'm/d H:i';
            } elseif ($diffInDays <= 7) {
                // DAY - DAY
                $groupFormat = '%Y-%m-%d %H:00:00';
                $labelFormat = 'm/d H:00';
            } elseif ($diffInDays <= 31) {
                // WEEK - WEEK
                $groupFormat = '%Y-%m-%d';
                $labelFormat = 'm/d/Y';
            } else {
                // MONTH - MONTH
                $groupFormat = '%Y-%m-%d';
                $labelFormat = 'm/d/Y';
            }

            // ============================================
            // AGGREGATED QUERY (Prevents Graph Crash)
            // ============================================
            $readings = BatteryHealth::selectRaw("
                DATE_FORMAT(recorded_at, '{$groupFormat}') as grouped_time,
                AVG(percentage) as avg_percentage,
                AVG(voltage) as avg_voltage
            ")
                ->where('buoy_id', $buoy->id)
                ->whereBetween('recorded_at', [$from, $to])
                ->groupBy('grouped_time')
                ->orderBy('grouped_time', 'asc')
                ->get();

            if ($readings->isEmpty()) {
                return $this->error(null, "No battery health data found for selected date range.", 404);
            }

            // ============================================
            // LIMIT MAX POINTS (Safety Protection)
            // ============================================
            if ($readings->count() > 200) {
                $readings = $readings->take(200);
            }

            $labels = $readings->pluck('grouped_time')
                ->map(fn($d) => Carbon::parse($d)->format($labelFormat))
                ->toArray();

            $percentageData = $readings->pluck('avg_percentage')->map(fn($v) => round($v, 2))->toArray();
            $voltageData = $readings->pluck('avg_voltage')->map(fn($v) => round($v, 2))->toArray();

            // ============================================
            // QUICKCHART CONFIG
            // ============================================
            $chartConfig = [
                'type' => 'line',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Battery %',
                            'data' => $percentageData,
                            'borderColor' => 'rgba(70, 95, 255, 1)',
                            'fill' => false,
                            'tension' => 0.3,
                        ],
                        [
                            'label' => 'Voltage (V)',
                            'data' => $voltageData,
                            'borderColor' => 'rgba(255, 99, 132, 1)',
                            'fill' => false,
                            'tension' => 0.3,
                        ],
                    ],
                ],
                'options' => [
                    'responsive' => true,
                    'plugins' => [
                        'legend' => ['position' => 'top'],
                    ],
                    'scales' => [
                        'x' => [
                            'title' => ['display' => true, 'text' => 'Time Range'],
                            'ticks' => ['maxRotation' => 45, 'minRotation' => 45]
                        ],
                        'y' => [
                            'title' => ['display' => true, 'text' => 'Battery Value'],
                            'beginAtZero' => false
                        ]
                    ]
                ]
            ];

            $client = new \GuzzleHttp\Client();
            $response = $client->post('https://quickchart.io/chart', [
                'json' => [
                    'chart' => $chartConfig,
                    'width' => 1000,
                    'height' => 400,
                    'format' => 'png'
                ]
            ]);

            $chartImageData = $response->getBody()->getContents();
            $chartBase64 = $chartImageData
                ? 'data:image/png;base64,' . base64_encode($chartImageData)
                : null;

            $user = Auth::user();
            $formattedFrom = $from->format('F d Y - h:i A');
            $formattedTo   = $to->format('F d Y - h:i A');
            $generatedDate = Carbon::now()->format('F d Y - h:i A');

            $pdf = Pdf::loadView('reports.battery-health-report', [
                'buoy'          => $buoy,
                'buoyCode'      => $buoyCode,
                'readings'      => $readings,
                'chartBase64'   => $chartBase64,
                'fromFormatted' => $formattedFrom,
                'toFormatted'   => $formattedTo,
                'generatedBy'   => $user ? $user->first_name . ' ' . $user->last_name : 'System',
                'generatedDate' => $generatedDate,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("Battery_Health_Report_{$buoyCode}.pdf");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
