<?php

namespace App\Http\Controllers;

use App\Models\WindReading;
use App\Http\Requests\WindReadingRequest;
use App\Http\Resources\WindReadingResource;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Buoy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;



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

    public function generateReportWindSpeed(Request $request)
    {
        try {

            // ============================================
            // VALIDATE REQUEST
            // ============================================
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

            // ============================================
            // FETCH BUOY
            // ============================================
            $buoy = Buoy::findOrFail($request->buoy_id);
            $buoyCode = $buoy->buoy_code;

            // ============================================
            // DETECT RANGE TYPE
            // ============================================
            $diffInDays  = $from->diffInDays($to);
            $diffInHours = $from->diffInHours($to);

            if ($diffInHours <= 24) {
                $groupFormat = '%Y-%m-%d %H:00:00';
                $labelFormat = 'm/d H:i';
            } elseif ($diffInDays <= 7) {
                $groupFormat = '%Y-%m-%d %H:00:00';
                $labelFormat = 'm/d H:00';
            } elseif ($diffInDays <= 31) {
                $groupFormat = '%Y-%m-%d';
                $labelFormat = 'm/d/Y';
            } else {
                $groupFormat = '%Y-%m-%d';
                $labelFormat = 'm/d/Y';
            }

            // ============================================
            // AGGREGATED QUERY (Prevents Graph Crash)
            // ============================================
            $readings = WindReading::selectRaw("
            DATE_FORMAT(recorded_at, '{$groupFormat}') as grouped_time,
            AVG(wind_speed_m_s) as avg_wind_ms,
            AVG(wind_speed_k_h) as avg_wind_kh
        ")
                ->where('buoy_id', $buoy->id)
                ->whereBetween('recorded_at', [$from, $to])
                ->groupBy('grouped_time')
                ->orderBy('grouped_time', 'asc')
                ->get();

            if ($readings->isEmpty()) {
                return $this->error(null, "No wind data found for selected date range.", 404);
            }

            // ============================================
            // LIMIT MAX POINTS (Safety Protection)
            // ============================================
            if ($readings->count() > 200) {
                $readings = $readings->take(200);
            }

            // ============================================
            // PREPARE CHART DATA
            // ============================================
            $labels = $readings->pluck('grouped_time')
                ->map(fn($d) => Carbon::parse($d)->format($labelFormat))
                ->toArray();

            $windMS = $readings->pluck('avg_wind_ms')
                ->map(fn($v) => round($v, 2))
                ->toArray();

            $windKH = $readings->pluck('avg_wind_kh')
                ->map(fn($v) => round($v, 2))
                ->toArray();

            // ============================================
            // QUICKCHART CONFIG (POST SAFE)
            // ============================================
            $chartConfig = [
                'type' => 'line',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Wind Speed (m/s)',
                            'data' => $windMS,
                            'borderColor' => 'rgba(54, 162, 235, 1)',
                            'fill' => false,
                            'tension' => 0.3,
                        ],
                        [
                            'label' => 'Wind Speed (km/h)',
                            'data' => $windKH,
                            'borderColor' => 'rgba(255, 159, 64, 1)',
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
                            'title' => ['display' => true, 'text' => 'Wind Speed'],
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

            // ============================================
            // PDF GENERATION
            // ============================================
            $user = Auth::user();
            $formattedFrom = $from->format('F d Y - h:i A');
            $formattedTo   = $to->format('F d Y - h:i A');
            $generatedDate = Carbon::now()->format('F d Y - h:i A');

            $pdf = Pdf::loadView('reports.wind-report', [
                'buoy'          => $buoy,
                'buoyCode'      => $buoyCode,
                'readings'      => $readings,
                'chartBase64'   => $chartBase64,
                'fromFormatted' => $formattedFrom,
                'toFormatted'   => $formattedTo,
                'generatedBy'   => $user ? $user->first_name . ' ' . $user->last_name : 'System',
                'generatedDate' => $generatedDate,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("Wind_Report_{$buoyCode}.pdf");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
