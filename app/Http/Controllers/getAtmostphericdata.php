<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class getAtmostphericdata extends Controller
{
    public function getAtmosphericChart(){
        $getChart = DB::table('bme280_atmospheric_readings')->get();
        return response()->json($getChart);
    }
}
