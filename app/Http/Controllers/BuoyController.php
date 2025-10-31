<?php

namespace App\Http\Controllers;

use App\Models\Buoy;
use Illuminate\Support\Str;
use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Http\Requests\StoreBuoyRequest;
use App\Http\Requests\UpdateBuoyRequest;
use App\Http\Resources\BuoyResource;
use App\Traits\HttpResponses;
use App\Services\FirebaseServices;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\Log;


class BuoyController extends Controller
{
    use HttpResponses;

    protected FirebaseServices $firebase;

    public function __construct(FirebaseServices $firebase)
    {
        $this->firebase = $firebase;
    }


    public function index()
    {
        return BuoyResource::collection(Buoy::all());
    }

    public function store(StoreBuoyRequest $request)
    {
        $validated = $request->validated();

        if (empty($validated['buoy_code'])) {
            $year = Carbon::now()->format('Y');
            $validated['buoy_code'] = "BUOY-{$year}-" . mt_rand(1000, 9999);
        }

        if ($request->hasFile('attachment')) {
            $imageFile = $request->file('attachment');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('buoy_attachment'), $imageName);
            $validated['attachment'] = $imageName;
        }

        $buoy = Buoy::create($validated);

        $buoyCode = str_replace(' ', '_', $validated['buoy_code']);
        $firebaseData = [
            'ANEMOMETER' => ['WIND_SPEED_km_h' => 0, 'WIND_SPEED_m_s' => 0],
            'BATTERY' => ['PERCENTAGE' => 0, 'VOLTAGE' => 0],
            'BME280' => ['ALTITUDE' => 0, 'ATMOSPHERIC_PRESSURE' => 0, 'HUMIDITY' => 0, 'SURROUNDING_TEMPERATURE' => 0],
            'GPS' => ['LATITUDE' => 0, 'LONGITUDE' => 0],
            'MS5837' => ['WATER_ALTITUDE' => 0, 'WATER_LEVEL_FEET' => 0, 'WATER_LEVEL_METER' => 0, 'WATER_PRESSURE' => 0, 'WATER_TEMPERATURE' => 0],
            'RAIN_GAUGE' => ['FALL_COUNT_MILIMETERS' => 0, 'TIP_COUNT' => 0],
            'RAIN_SENSOR' => ['RAIN_PERCENTAGE' => 0],
            'RELAY_STATE' => false,
        ];

        try {
            $database = $this->firebase;
            $reference = $database->getReference($buoyCode);

            Log::info("Firebase reference path: " . $reference->getUri());

            $reference->set($firebaseData);

            $readBack = $reference->getValue();
            Log::info("Data read back from Firebase: " . json_encode($readBack));

            Log::info("Firebase data set successfully for {$buoyCode}");
        } catch (\Throwable $e) {
            Log::error("Firebase write error for {$buoyCode}: " . $e->getMessage());
        }


        return $this->success(
            new BuoyResource($buoy),
            'Buoy created successfully'
        );
    }


    public function show(Buoy $buoy)
    {
        return $this->success(
            new BuoyResource($buoy),
            'Buoy data'
        );
    }

    public function update(UpdateBuoyRequest $request, Buoy $buoy)
    {
        $validated = $request->validated();

        // Store old buoy code before update
        $oldBuoyCode = str_replace(' ', '_', $buoy->buoy_code);

        // Handle new image upload if present
        if ($request->hasFile('attachment')) {
            $imageFile = $request->file('attachment');
            $imageName = Str::random(32) . '.' . $imageFile->getClientOriginalExtension();
            $imageFile->move(public_path('buoy_attachment'), $imageName);

            // Optionally delete old attachment if it exists
            if ($buoy->attachment && file_exists(public_path('buoy_attachment/' . $buoy->attachment))) {
                unlink(public_path('buoy_attachment/' . $buoy->attachment));
            }

            $validated['attachment'] = $imageName;
        }

        $buoy->update($validated);

        // Check if buoy_code was changed
        $newBuoyCode = str_replace(' ', '_', $buoy->buoy_code);

        if ($oldBuoyCode !== $newBuoyCode) {
            // Buoy code changed - need to migrate Firebase data
            try {
                $database = $this->firebase;
                $oldReference = $database->getReference($oldBuoyCode);
                $newReference = $database->getReference($newBuoyCode);

                // Get existing data from old location
                $existingData = $oldReference->getValue();

                if ($existingData) {
                    // Copy data to new location
                    $newReference->set($existingData);
                    Log::info("Firebase data migrated from {$oldBuoyCode} to {$newBuoyCode}");

                    // Delete old location
                    $oldReference->remove();
                    Log::info("Old Firebase node {$oldBuoyCode} removed");
                } else {
                    // No existing data, create new structure
                    $firebaseData = [
                        'ANEMOMETER' => ['WIND_SPEED_km_h' => 0, 'WIND_SPEED_m_s' => 0],
                        'BATTERY' => ['PERCENTAGE' => 0, 'VOLTAGE' => 0],
                        'BME280' => ['ALTITUDE' => 0, 'ATMOSPHERIC_PRESSURE' => 0, 'HUMIDITY' => 0, 'SURROUNDING_TEMPERATURE' => 0],
                        'GPS' => ['LATITUDE' => 0, 'LONGITUDE' => 0],
                        'MS5837' => ['WATER_ALTITUDE' => 0, 'WATER_LEVEL_FEET' => 0, 'WATER_LEVEL_METER' => 0, 'WATER_PRESSURE' => 0, 'WATER_TEMPERATURE' => 0],
                        'RAIN_GAUGE' => ['FALL_COUNT_MILIMETERS' => 0, 'TIP_COUNT' => 0],
                        'RAIN_SENSOR' => ['RAIN_PERCENTAGE' => 0],
                        'RELAY_STATE' => false,
                    ];
                    $newReference->set($firebaseData);
                    Log::info("New Firebase structure created for {$newBuoyCode}");
                }
            } catch (\Throwable $e) {
                Log::error("Firebase update error: " . $e->getMessage());
            }
        } else {
            // Buoy code unchanged - just update metadata if needed
            // You can optionally update specific Firebase fields here
            Log::info("Buoy {$newBuoyCode} updated in database, Firebase structure unchanged");
        }

        return $this->success(
            new BuoyResource($buoy),
            'Buoy updated successfully'
        );
    }

    public function destroy(Buoy $buoy)
    {
        try {
            // Delete attachment file if it exists
            if (!empty($buoy->attachment)) {
                $attachmentPath = public_path('buoy_attachment/' . basename($buoy->attachment));

                if (file_exists($attachmentPath)) {
                    unlink($attachmentPath);
                    Log::info("Attachment deleted: {$attachmentPath}");
                } else {
                    Log::warning("Attachment not found: {$attachmentPath}");
                }
            }

            // Delete corresponding Firebase data (optional)
            if (isset($buoy->buoy_code)) {
                try {
                    $this->firebase->getReference($buoy->buoy_code)->remove();
                    Log::info("Firebase data deleted for buoy: {$buoy->buoy_code}");
                } catch (\Throwable $firebaseError) {
                    Log::error("Firebase delete failed for {$buoy->buoy_code}: " . $firebaseError->getMessage());
                }
            }

            // Delete buoy record
            $buoy->delete();

            return $this->success(null, 'Buoy deleted successfully', 200);
        } catch (\Throwable $e) {
            Log::error("Error deleting buoy ID {$buoy->id}: " . $e->getMessage());
            return $this->error(null, 'Failed to delete buoy', 500);
        }
    }
}
