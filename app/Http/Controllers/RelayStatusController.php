<?php

namespace App\Http\Controllers;

use App\Models\RelayStatus;
use App\Http\Requests\RelayStatusRequest;
use App\Http\Resources\RelayStatusResource;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Log;
use App\Models\Buoy;
use App\Services\FirebaseServices;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class RelayStatusController extends Controller
{
    use HttpResponses;

    protected FirebaseServices $firebase;

    public function __construct(FirebaseServices $firebase)
    {
        $this->firebase = $firebase;
    }

    public function index()
    {
        // Include related buoy in the response
        return RelayStatusResource::collection(RelayStatus::with('buoy', 'triggeredBy')->get());
    }

    public function relaySwitch(RelayStatusRequest $request)
    {
        $validated = $request->validated();

        // Find buoy by buoy_id
        $buoy = Buoy::find($validated['buoy_id']);

        if (!$buoy) {
            return $this->error(
                null,
                'Buoy not found',
                404
            );
        }

        // Normalize relay_state to lowercase "on" / "off"
        $relayState = strtolower($validated['relay_state']);

        // Store relay status in database with triggered_by and recorded_at
        $relayStatus = RelayStatus::create([
            'buoy_id'      => $buoy->id,
            'relay_state'  => $relayState,
            'triggered_by' => $request->user()->id ?? null,
            'recorded_at'  => now(),
        ]);

        // Eager load buoy and triggeredBy for resource response
        $relayStatus->load('buoy', 'triggeredBy');

        // Update Firebase
        try {
            $firebase = $this->firebase;
            $buoyCode = str_replace(' ', '_', $buoy->buoy_code);

            // Convert "on"/"off" to boolean for Firebase
            $firebaseRelayState = $relayState === 'on';

            $reference = $firebase->getReference("{$buoyCode}/RELAY_STATE");
            $reference->set($firebaseRelayState);

            Log::info("Firebase RELAY_STATE updated for {$buoyCode}: {$firebaseRelayState}");
        } catch (\Throwable $e) {
            Log::error("Firebase write error for {$buoy->buoy_code}: " . $e->getMessage());
        }

        // Return response using resource
        // Eager load buoy and triggeredBy, then return in the resource
        return $this->success(
            new RelayStatusResource($relayStatus->load('buoy', 'triggeredBy')),
            'Relay status stored successfully',
            201
        );
    }

    public function show(RelayStatus $relayStatus)
    {
        $relayStatus->load('buoy', 'triggeredBy');
        return new RelayStatusResource($relayStatus);
    }

    public function fetchAllRelayStatus(RelayStatusRequest $request)
    {
        try {
            $validated = $request->validated();

            Log::info('fetchAllRelayStatus request validated data', $validated);

            $query = RelayStatus::with('buoy', 'triggeredBy')
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

            Log::info('fetchAllRelayStatus SQL query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $statuses = $query->get();

            foreach ($statuses as $status) {
                if (!$status->buoy) {
                    Log::warning("RelayStatus reading has missing buoy relation", [
                        'status_id'    => $status->id,
                        'relay_state'  => $status->relay_state,
                        'triggered_by' => $status->triggered_by,
                        'recorded_at'  => $status->recorded_at,
                    ]);
                }
            }

            return $this->success(
                RelayStatusResource::collection($statuses),
                'Relay statuses fetched successfully'
            );
        } catch (\Exception $e) {

            Log::error('fetchAllRelayStatus failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);

            return $this->error(
                null,
                'Failed to fetch relay statuses: ' . $e->getMessage(),
                500
            );
        }
    }

    public function generateReportRelayStatus(Request $request)
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

            // ----------------- Fetch Relay Status Data -----------------
            $statuses = RelayStatus::where('buoy_id', $buoy->id)
                ->whereBetween('recorded_at', [$from, $to])
                ->orderBy('recorded_at', 'asc')
                ->get();

            if ($statuses->isEmpty()) {
                return $this->error(null, "No relay status data found for selected date range.", 404);
            }

            $user = Auth::user();
            $formattedFrom = $from->format('F d Y - h:i A');
            $formattedTo   = $to->format('F d Y - h:i A');
            $generatedDate = Carbon::now()->format('F d Y - h:i A');

            // ----------------- Prepare Chart -----------------
            $labels = $statuses->pluck('recorded_at')
                ->map(fn($d) => Carbon::parse($d)->format('m/d H:i'))
                ->toArray();

            // Convert on/off to numeric (1 = ON, 0 = OFF)
            $relayData = $statuses->map(function ($status) {
                return strtolower($status->relay_state) === 'on' ? 1 : 0;
            })->toArray();

            $chartConfig = [
                'type' => 'line',
                'data' => [
                    'labels' => $labels,
                    'datasets' => [
                        [
                            'label' => 'Relay State (1=ON, 0=OFF)',
                            'data' => $relayData,
                            'borderColor' => 'rgba(255, 99, 132, 1)',
                            'fill' => false,
                            'stepped' => true,
                        ],
                    ],
                ],
                'options' => [
                    'plugins' => ['legend' => ['position' => 'top']],
                    'scales' => [
                        'x' => ['title' => ['display' => true, 'text' => 'Time']],
                        'y' => [
                            'title' => ['display' => true, 'text' => 'Relay State'],
                            'ticks' => [
                                'callback' => "function(value){ return value == 1 ? 'ON' : 'OFF'; }"
                            ],
                            'min' => 0,
                            'max' => 1
                        ]
                    ]
                ]
            ];

            $chartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($chartConfig));
            $chartImageData = @file_get_contents($chartUrl);
            $chartBase64 = $chartImageData
                ? 'data:image/png;base64,' . base64_encode($chartImageData)
                : null;

            // ----------------- Generate PDF -----------------
            $pdf = Pdf::loadView('reports.relaystatus-report', [
                'buoy'          => $buoy,
                'buoyCode'      => $buoyCode,
                'statuses'      => $statuses,
                'chartBase64'   => $chartBase64,
                'fromFormatted' => $formattedFrom,
                'toFormatted'   => $formattedTo,
                'generatedBy'   => $user ? $user->first_name . ' ' . $user->last_name : 'System',
                'generatedDate' => $generatedDate,
            ])->setPaper('a4', 'portrait');

            return $pdf->download("RelayStatus_Report_{$buoyCode}.pdf");
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), 500);
        }
    }
}
