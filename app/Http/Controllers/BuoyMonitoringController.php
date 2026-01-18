<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Buoy;
use App\Models\GpsReading;
use App\Models\BatteryHealth;
use App\Models\RelayStatus;

use App\Traits\HttpResponses;
use App\Http\Resources\BuoyResource;
use App\Http\Requests\StoreGpsReadingRequest;
use App\Http\Requests\StoreBatteryHealthRequest;
use App\Http\Requests\StoreRelayStatusRequest;

use App\Http\Resources\RelayStatusResource;

use App\Services\FirebaseServices;



class BuoyMonitoringController extends Controller
{
    use HttpResponses;

    protected FirebaseServices $firebase;

    public function __construct(FirebaseServices $firebase)
    {
        $this->firebase = $firebase;
    }

    public function show(Buoy $buoy)
    {
        return $this->success(
            new BuoyResource($buoy),
            'Buoy data'
        );
    }

    public function storeLongitudeAndLatitude(StoreGpsReadingRequest $request)
    {
        $validated = $request->validated();

        // Find buoy by buoy_code
        $buoy = Buoy::where('buoy_code', $validated['buoy_code'])->first();

        if (!$buoy) {
            return $this->error(
                null,
                'Buoy not found',
                404
            );
        }

        // Prevent duplicate GPS entries (same location)
        $lastReading = GpsReading::where('buoy_id', $buoy->id)
            ->latest()
            ->first();

        if (
            $lastReading &&
            $lastReading->latitude == $validated['latitude'] &&
            $lastReading->longitude == $validated['longitude']
        ) {
            return $this->success(
                null,
                'GPS location unchanged'
            );
        }

        // Store GPS reading
        $gps = GpsReading::create([
            'buoy_id'   => $buoy->id,
            'latitude'  => $validated['latitude'],
            'longitude' => $validated['longitude'],
        ]);

        return $this->success(
            $gps,
            'GPS reading stored successfully',
            201
        );
    }

    public function storeBatteryHealth(StoreBatteryHealthRequest $request)
    {
        $validated = $request->validated();

        // Find buoy by buoy_code (like storeGPS)
        $buoy = Buoy::where('buoy_code', $validated['buoy_code'] ?? null)->first();

        if (!$buoy) {
            return $this->error(
                null,
                'Buoy not found',
                404
            );
        }

        // Round values to 2 decimal places
        $percentage = round($validated['percentage'], 2);
        $voltage    = round($validated['voltage'], 2);

        // Prevent duplicate consecutive entries with same battery data
        $lastBattery = BatteryHealth::where('buoy_id', $buoy->id)
            ->latest()
            ->first();

        if (
            $lastBattery &&
            $lastBattery->percentage == $percentage &&
            $lastBattery->voltage == $voltage
        ) {
            return $this->success(
                null,
                'Battery health unchanged'
            );
        }

        // Store new battery health reading
        $battery = BatteryHealth::create([
            'buoy_id'    => $buoy->id,
            'percentage' => $percentage,
            'voltage'    => $voltage,
        ]);

        return $this->success(
            $battery,
            'Battery health stored successfully',
            201
        );
    }


    public function relaySwitch(StoreRelayStatusRequest $request)
    {
        $validated = $request->validated();

        // Find buoy by buoy_code
        $buoy = Buoy::where('buoy_code', $validated['buoy_code'])->first();

        if (!$buoy) {
            return $this->error(
                null,
                'Buoy not found',
                404
            );
        }

        // Normalize relay_state to lowercase "on" / "off"
        $relayState = strtolower($validated['relay_state']);

        // Optional: prevent duplicate consecutive entries
        // $lastStatus = RelayStatus::where('buoy_id', $buoy->id)
        //     ->latest()
        //     ->first();

        // if ($lastStatus && $lastStatus->relay_state === $relayState) {
        //     return $this->success(
        //         new RelayStatusResource($lastStatus),
        //         'Relay status unchanged'
        //     );
        // }

        // Store relay status in database
        $relayStatus = RelayStatus::create([
            'buoy_id' => $buoy->id,
            'relay_state' => $relayState,
        ]);

        // Update Firebase
        try {
            $firebase = $this->firebase;
            $buoyCode = str_replace(' ', '_', $buoy->buoy_code);

            // Convert "on"/"off" to boolean for Firebase
            $firebaseRelayState = $relayState === 'on' ? true : false;

            $reference = $firebase->getReference("{$buoyCode}/RELAY_STATE");
            $reference->set($firebaseRelayState);

            Log::info("Firebase RELAY_STATE updated for {$buoyCode}: {$firebaseRelayState}");
        } catch (\Throwable $e) {
            Log::error("Firebase write error for {$buoy->buoy_code}: " . $e->getMessage());
        }

        // Return response using resource
        return $this->success(
            new RelayStatusResource($relayStatus),
            'Relay status stored successfully',
            201
        );
    }
}
