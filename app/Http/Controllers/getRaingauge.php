<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class getRaingauge extends Controller
{
    public function getraingaugeChart(){
        $getChart = DB::table('rain_gauge_readings')->get();
        return response()->json($getChart);
    }
}
