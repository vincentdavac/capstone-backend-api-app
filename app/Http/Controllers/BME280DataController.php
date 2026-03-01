<?php

namespace App\Http\Controllers;

use App\Models\BME280Data;
use App\Models\Buoy;
use App\Http\Requests\BME280DataRequest;
use App\Http\Resources\BME280DataResource;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class BME280DataController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of BME280 data WITH buoy
     */
    public function index()
    {
        return BME280DataResource::collection(
            BME280Data::with('buoy')->get()
        );
    }

    /**
     * Store BME280 sensor data
     */
    public function store(BME280DataRequest $request)
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

        // Normalize + round values
        $temperatureCelsius = round($validated['temperature_celsius'], 2);
        $humidity           = round($validated['humidity'], 2);
        $pressureHpa        = round($validated['pressure_hpa'], 2);
        $altitude           = round($validated['altitude'], 2);

        // Derived values
        $temperatureFahrenheit = round(($temperatureCelsius * 9 / 5) + 32, 2);
        $pressureMbar          = $pressureHpa; // 1 hPa = 1 mbar

        // Server-handled timestamp
        $recordedAt = $validated['recorded_at'] ?? now();

        // Prevent duplicate consecutive entries
        $lastData = BME280Data::where('buoy_id', $buoy->id)
            ->latest('recorded_at')
            ->first();

        if (
            $lastData &&
            $lastData->temperature_celsius == $temperatureCelsius &&
            $lastData->humidity == $humidity &&
            $lastData->pressure_hpa == $pressureHpa &&
            $lastData->altitude == $altitude
        ) {
            return $this->success(
                null,
                'BME280 data unchanged',
                200
            );
        }

        // Store BME280 data
        $bme280 = BME280Data::create([
            'buoy_id' => $buoy->id,

            'temperature_celsius'    => $temperatureCelsius,
            'temperature_fahrenheit' => $temperatureFahrenheit,

            'humidity' => $humidity,

            'pressure_hpa'  => $pressureHpa,
            'pressure_mbar' => $pressureMbar,

            'altitude' => $altitude,

            'recorded_at' => $recordedAt,
        ]);

        // Return response WITH buoy info
        return $this->success(
            new BME280DataResource($bme280->load('buoy')),
            'BME280 data recorded successfully',
            201
        );
    }


    public function fetchAllBME280Data(BME280DataRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllBME280Data request validated data', $validated);

            $query = BME280Data::with('buoy')
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

            Log::info('fetchAllBME280Data SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $readings = $query->get();

            foreach ($readings as $reading) {
                if (!$reading->buoy) {
                    Log::warning("BME280 reading has missing buoy relation", [
                        'reading_id'           => $reading->id,
                        'temperature_celsius'  => $reading->temperature_celsius,
                        'humidity'             => $reading->humidity,
                        'pressure_hpa'         => $reading->pressure_hpa,
                        'altitude'             => $reading->altitude,
                        'recorded_at'          => $reading->recorded_at,
                    ]);
                }
            }

            return $this->success(
                BME280DataResource::collection($readings),
                'BME280 readings fetched successfully'
            );
        } catch (\Exception $e) {

            Log::error('fetchAllBME280Data failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch BME280 readings: ' . $e->getMessage(),
                500
            );
        }
    }

    public function generateReportBME280(Request $request)
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
            // DETECT RANGE TYPE (Same Logic as Battery)
            // ============================================
            $diffInDays = $from->diffInDays($to);
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
            // AGGREGATED QUERY (Prevents Crash)
            // ============================================
            $readings = BME280Data::selectRaw("
            DATE_FORMAT(recorded_at, '{$groupFormat}') as grouped_time,
            AVG(temperature_celsius) as avg_temperature,
            AVG(humidity) as avg_humidity,
            AVG(pressure_hpa) as avg_pressure
        ")
                ->where('buoy_id', $buoy->id)
                ->whereBetween('recorded_at', [$from, $to])
                ->groupBy('grouped_time')
                ->orderBy('grouped_time', 'asc')
                ->get();

            if ($readings->isEmpty()) {
                return $this->error(null, "No BME280 data found for selected date range.", 404);
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

            $temperatureData = $readings->pluck('avg_temperature')->map(fn($v) => round($v, 2))->toArray();
            $humidityData    = $readings->pluck('avg_humidity')->map(fn($v) => round($v, 2))->toArray();
            $pressureData    = $readings->pluck('avg_pressure')->map(fn($v) => round($v, 2))->toArray();

            // ============================================
            // QUICKCHART CONFIG (POST like Battery)
            // ============================================
            $chartConfig = [
                'type' => 'line',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Temperature (°C)',
                            'data' => $temperatureData,
                            'borderColor' => 'rgba(255, 99, 132, 1)',
                            'fill' => false,
                            'tension' => 0.3,
                        ],
                        [
                            'label' => 'Humidity (%)',
                            'data' => $humidityData,
                            'borderColor' => 'rgba(54, 162, 235, 1)',
                            'fill' => false,
                            'tension' => 0.3,
                        ],
                        [
                            'label' => 'Pressure (hPa)',
                            'data' => $pressureData,
                            'borderColor' => 'rgba(75, 192, 192, 1)',
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
                            'title' => ['display' => true, 'text' => 'Sensor Value'],
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

            $pdf = Pdf::loadView('reports.bme280-report', [
                'buoy'          => $buoy,
                'buoyCode'      => $buoyCode,
                'readings'      => $readings,
                'chartBase64'   => $chartBase64,
                'fromFormatted' => $formattedFrom,
                'toFormatted'   => $formattedTo,
                'generatedBy'   => $user ? $user->first_name . ' ' . $user->last_name : 'System',
                'generatedDate' => $generatedDate,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("BME280_Report_{$buoyCode}.pdf");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
