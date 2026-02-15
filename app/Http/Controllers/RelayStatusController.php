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
}
