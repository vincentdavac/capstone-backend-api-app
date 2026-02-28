<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\FirebaseServices;
use App\Models\User;

class alertControllerV2 extends Controller
{
    protected $firebase;
    protected string $reftblName;
    public function __construct(FirebaseServices $firebaseService)
    {
        $this->firebase = $firebaseService->getDatabase();
    }
    public function checkAllSensors(Request $request)
    {
        try {
            $user = $request->user();
            $firebaseData = $this->firebase->getReference()->getValue();

            $thresholds = DB::table('users')
                ->join('barangays', 'users.barangay_id', '=', 'barangays.id')
                ->where('users.barangay_id', $user->barangay_id)
                ->select('barangays.white_level_alert', 'barangays.blue_level_alert', 'barangays.red_level_alert')
                ->first();
            $buoyCode = DB::table('buoys')
                ->where('barangay_id', $user->barangay_id)
                ->value('buoy_code');
            $sensors = [
                [
                    'key' => 'WATER_LEVEL_FEET',
                    'path'  => 'MS5837',
                    'type' => 'WATER LEVEL',
                    'reset_times' => ['Blue' => 5, 'Red' => 10],
                    'conditions'  => [
                        ['min' => $thresholds->red_level_alert,   'description' => 'test test test.', 'alert' => 'Red'],
                        ['min' => $thresholds->blue_level_alert,  'description' => 'test test', 'alert' => 'Blue'],
                        ['min' => $thresholds->white_level_alert, 'description' => 'test', 'alert' => 'White'],
                    ],
                ],
            ];

            $usersId   = User::where('user_type', 'user')->get();
            $resetTime = null;

            foreach ($firebaseData as $prototypeName => $buoyData) {
                if ($buoyCode !== $prototypeName) {
                    continue;
                }
                $buoy = DB::table('buoys')->where('buoy_code', $prototypeName)->first();
                if (!$buoy) {
                    continue;
                }

                foreach ($sensors as $sensor) {
                    $result = $this->processSensor($sensor, $buoyData, $buoy, $user, $usersId);
                    if (!is_null($result)) {
                        $resetTime = $result;
                    }
                }
            }

            return response()->json(['success' => true, 'reset' => $resetTime], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'went wrong in checkAllSensors',
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
            ], 500);
        }
        
    }

    private function processSensor(array $sensor, array $buoyData, object $buoy, object $user, $usersId): ?int
    {
        $sensorKey  = $sensor['key'];
        $sensorPath = $sensor['path'];
        $sensorType = $sensor['type'];

        if (!isset($buoyData[$sensorPath][$sensorKey])) {
            return null;
        }
        $sensorValue = $buoyData[$sensorPath][$sensorKey];

        if ($sensorValue == 0 || is_null($sensorValue)) {
            return null;
        }
        $description = null;
        $alert = null;
        foreach ($sensor['conditions'] as $condition) {
            if ($sensorValue >= $condition['min']) {
                $description = $condition['description'];
                $alert = $condition['alert'];
                break;
            }
        }

        if (is_null($alert)) {
            return null;
        }
        $recorded = Carbon::now('Asia/Manila')->format('Y-m-d H:i:s');

        $lastAlert = DB::table('recent_alerts')
            ->where('buoy_id', $buoy->id)
            ->where('sensor_type', $sensorType)
            ->orderBy('recorded_at', 'desc')
            ->first();
        $insert = false;
        if (!$lastAlert || $lastAlert->alert_level !== $alert) {
            $insert = true;
        }
        if (!$insert) {
            return null;
        }
        $alertId = 'ALERT' . Str::uuid();
        $getAlertId = DB::table('recent_alerts')->orderBy('recorded_at', 'desc')->first();
        DB::table('recent_alerts')->insert([
            'alertId'     => $alertId,
            'buoy_id'     => $buoy->id,
            'description' => $description,
            'alert_level' => $alert,
            'sensor_type' => $sensorType,
            'recorded_at' => $recorded,
        ]);

        if (in_array($alert, ['Blue', 'Red'])) {
            $rows = $usersId->map(fn($u) => [
                'alert_id'     => $getAlertId->id,
                'broadcast_by' => $user->last_name . ', ' . $user->first_name,
                'user_id'      => $u->id,
                'is_read'      => false,
                'recorded_at'  => $recorded,
            ])->toArray();

            DB::table('alerts')->insert($rows);
        }

        return $sensor['reset_times'][$alert] ?? null;
    }
}
