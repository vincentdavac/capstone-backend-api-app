<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pusher\Pusher;

class TestPusherController extends Controller
{
    public function testConnection()
    {
        try {
            $pusher = new Pusher(
                env('PUSHER_APP_KEY'),
                env('PUSHER_APP_SECRET'),
                env('PUSHER_APP_ID'),
                [
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'useTLS' => true
                ]
            );

            $result = $pusher->trigger('test-channel', 'test-event', [
                'message' => 'Hello from Pusher!'
            ]);

            return response()->json([
                'success' => true,
                'result' => $result,
                'config' => [
                    'key' => env('PUSHER_APP_KEY'),
                    'cluster' => env('PUSHER_APP_CLUSTER'),
                    'app_id' => env('PUSHER_APP_ID'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

// Add this route to routes/api.php
// Route::get('/test-pusher', [TestPusherController::class, 'testConnection']);
