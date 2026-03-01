<?php

namespace App\Http\Controllers;

use App\Models\RainGaugeReading;
use App\Http\Requests\RainGaugeReadingRequest;
use App\Http\Resources\RainGaugeReadingResource;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Buoy;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;


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

    public function fetchAllRainGaugeReading(RainGaugeReadingRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllRainGaugeReading request validated data', $validated);

            $query = RainGaugeReading::with('buoy')
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

            Log::info('fetchAllRainGaugeReading SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $readings = $query->get();

            foreach ($readings as $reading) {
                if (!$reading->buoy) {
                    Log::warning("RainGauge reading has missing buoy relation", [
                        'reading_id'  => $reading->id,
                        'rainfall_mm' => $reading->rainfall_mm,
                        'tip_count'   => $reading->tip_count,
                        'recorded_at' => $reading->recorded_at,
                    ]);
                }
            }

            return $this->success(
                RainGaugeReadingResource::collection($readings),
                'Rain gauge readings fetched successfully'
            );
        } catch (\Exception $e) {

            Log::error('fetchAllRainGaugeReading failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch rain gauge readings: ' . $e->getMessage(),
                500
            );
        }
    }

    public function generateReportRainGauge(Request $request)
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
                $groupFormat = '%Y-%m-%d %H';   // Hourly
                $labelFormat = 'm/d H:i';
            } elseif ($diffInDays <= 7) {
                $groupFormat = '%Y-%m-%d %H';   // Hourly for up to a week
                $labelFormat = 'm/d H:00';
            } elseif ($diffInDays <= 31) {
                $groupFormat = '%Y-%m-%d';      // Daily
                $labelFormat = 'm/d/Y';
            } else {
                $groupFormat = '%Y-%m-%d';      // Monthly
                $labelFormat = 'm/d/Y';
            }

            // ============================================
            // AGGREGATED QUERY USING SUM
            // ============================================
            $readings = RainGaugeReading::selectRaw("
            DATE_FORMAT(recorded_at, '{$groupFormat}') as grouped_time,
            COALESCE(SUM(rainfall_mm), 0) as total_rainfall,
            COALESCE(SUM(tip_count), 0) as total_tip_count
        ")
                ->where('buoy_id', $buoy->id)
                ->whereBetween('recorded_at', [$from, $to])
                ->groupBy('grouped_time')
                ->orderBy('grouped_time', 'asc')
                ->get();

            if ($readings->isEmpty()) {
                return $this->error(null, "No rain gauge data found for selected date range.", 404);
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
                ->map(fn($d) => Carbon::createFromFormat(
                    strlen($d) === 13 ? 'Y-m-d H' : 'Y-m-d',
                    $d
                )->format($labelFormat))
                ->toArray();

            $rainfallData = $readings->pluck('total_rainfall')->map(fn($v) => round($v, 2))->toArray();
            $tipCountData = $readings->pluck('total_tip_count')->map(fn($v) => round($v, 0))->toArray();

            // ============================================
            // QUICKCHART CONFIG (POST via Guzzle)
            // ============================================
            $chartConfig = [
                'type' => 'line',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Rainfall (mm)',
                            'data' => $rainfallData,
                            'borderColor' => 'rgba(54, 162, 235, 1)',
                            'fill' => false,
                            'tension' => 0.3,
                        ],
                        [
                            'label' => 'Tip Count',
                            'data' => $tipCountData,
                            'borderColor' => 'rgba(75, 192, 192, 1)',
                            'fill' => false,
                            'tension' => 0.3,
                        ],
                    ],
                ],
                'options' => [
                    'responsive' => true,
                    'plugins' => ['legend' => ['position' => 'top']],
                    'scales' => [
                        'x' => [
                            'title' => ['display' => true, 'text' => 'Time Range'],
                            'ticks' => ['maxRotation' => 45, 'minRotation' => 45]
                        ],
                        'y' => ['title' => ['display' => true, 'text' => 'Value'], 'beginAtZero' => true]
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

            $pdf = Pdf::loadView('reports.raingauge-report', [
                'buoy'          => $buoy,
                'buoyCode'      => $buoyCode,
                'readings'      => $readings,
                'chartBase64'   => $chartBase64,
                'fromFormatted' => $formattedFrom,
                'toFormatted'   => $formattedTo,
                'generatedBy'   => $user ? $user->first_name . ' ' . $user->last_name : 'System',
                'generatedDate' => $generatedDate,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("RainGauge_Report_{$buoyCode}.pdf");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
