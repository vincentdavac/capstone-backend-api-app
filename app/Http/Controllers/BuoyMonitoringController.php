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
use App\Http\Requests\RelayStatusRequest;

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
}
