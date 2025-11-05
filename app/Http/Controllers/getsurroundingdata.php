<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class getsurroundingdata extends Controller
{
     public function getsurroundingChart(){
        $getChart = DB::table('bme280_temperature_readings')->get();
        return response()->json($getChart);
    }
}
