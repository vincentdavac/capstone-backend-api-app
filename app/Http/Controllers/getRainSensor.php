<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class getRainSensor extends Controller
{
    public function getrainsensorChart(){
        $getChart = DB::table('rain_sensor_readings')->get();
        return response()->json($getChart);
    }
}
