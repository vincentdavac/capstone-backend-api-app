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
}
