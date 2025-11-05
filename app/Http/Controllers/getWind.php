<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class getWind extends Controller
{
    public function getwindChart(){
        $getChart = DB::table('wind_readings')->get();
        return response()->json($getChart);
    }
}
