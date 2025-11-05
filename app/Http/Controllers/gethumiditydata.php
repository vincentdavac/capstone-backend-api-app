<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class gethumiditydata extends Controller
{
   public function gethumidityChart(){
        $getChart = DB::table('bme280_humidity_readings')->get();
        return response()->json($getChart);
    }
}
