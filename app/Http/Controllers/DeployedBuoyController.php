<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Services\FirebaseServices;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\DeployedBuoyResource;

use App\Models\Buoy;


class DeployedBuoyController extends Controller
{
    use HttpResponses;

    protected FirebaseServices $firebase;

    public function __construct(FirebaseServices $firebase)
    {
        $this->firebase = $firebase;
    }

    public function show($buoyCode)
    {
        try {
            // Validate the route parameter
            if (empty($buoyCode)) {
                return $this->error(
                    null,
                    'Buoy code is required',
                    422
                );
            }

            // Find the buoy in the database with barangay relationship
            $buoy = Buoy::with('barangay')
                ->where('buoy_code', $buoyCode)
                ->first();

            if (!$buoy) {
                return $this->error(
                    null,
                    'Buoy not found in database',
                    404
                );
            }

            // Log the buoy data for debugging
            Log::info("Buoy found", [
                'id' => $buoy->id,
                'buoy_code' => $buoy->buoy_code,
                'barangay_id' => $buoy->barangay_id,
                'barangay_loaded' => $buoy->relationLoaded('barangay'),
                'barangay_exists' => $buoy->barangay !== null
            ]);

            // Prepare Firebase buoy code (replace spaces with underscores)
            $firebaseBuoyCode = str_replace(' ', '_', $buoyCode);

            // Get Firebase database reference
            $database = $this->firebase;

            // Fetch all required data paths from Firebase
            $gpsLatitude = $database->getReference("{$firebaseBuoyCode}/GPS/LATITUDE")->getValue();
            $gpsLongitude = $database->getReference("{$firebaseBuoyCode}/GPS/LONGITUDE")->getValue();
            $batteryPercentage = $database->getReference("{$firebaseBuoyCode}/BATTERY/PERCENTAGE")->getValue();
            $batteryVoltage = $database->getReference("{$firebaseBuoyCode}/BATTERY/VOLTAGE")->getValue();
            $relayState = $database->getReference("{$firebaseBuoyCode}/RELAY_STATE")->getValue();

            Log::info("Successfully retrieved data for buoy: {$buoyCode}");

            // Structure the response data using the resource
            $responseData = [
                'buoy' => new DeployedBuoyResource($buoy),
                'firebaseData' => [
                    'gps' => [
                        'latitude' => $gpsLatitude ?? 0,
                        'longitude' => $gpsLongitude ?? 0
                    ],
                    'battery' => [
                        'percentage' => $batteryPercentage ?? 0,
                        'voltage' => $batteryVoltage ?? 0
                    ],
                    'relayState' => $relayState ?? false
                ]
            ];

            return $this->success(
                $responseData,
                'Buoy data retrieved successfully'
            );
        } catch (\Throwable $e) {
            Log::error("Error retrieving buoy data for {$buoyCode}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            Log::error("Error line: " . $e->getLine());
            Log::error("Error file: " . $e->getFile());
            return $this->error(
                null,
                'Failed to retrieve buoy data',
                500
            );
        }
    }

    public function getAllData($buoyCode)
    {
        try {
            // Validate buoy code
            if (empty($buoyCode)) {
                return $this->error(
                    null,
                    'Buoy code is required',
                    422
                );
            }

            // Check if buoy exists in database
            $buoy = Buoy::where('buoy_code', $buoyCode)->first();

            if (!$buoy) {
                return $this->error(
                    null,
                    'Buoy not found in database',
                    404
                );
            }

            // Prepare Firebase buoy code (replace spaces with underscores)
            $firebaseBuoyCode = str_replace(' ', '_', $buoyCode);

            // Get Firebase database reference
            $database = $this->firebase;
            $reference = $database->getReference($firebaseBuoyCode);

            // Get all data for the buoy
            $allData = $reference->getValue();

            if ($allData === null) {
                return $this->error(
                    null,
                    'Buoy not found in Firebase',
                    404
                );
            }

            Log::info("Successfully retrieved all data for buoy: {$buoyCode}");

            return $this->success(
                [
                    'buoy_code' => $buoyCode,
                    'data' => $allData
                ],
                'Complete buoy data retrieved successfully'
            );
        } catch (\Throwable $e) {
            Log::error("Error retrieving complete buoy data for {$buoyCode}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return $this->error(
                null,
                'Failed to retrieve complete buoy data from Firebase',
                500
            );
        }
    }
}
