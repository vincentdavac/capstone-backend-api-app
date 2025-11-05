<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class getwaterTemp extends Controller
{
    public function getwatertempChart(){
        $getChart = DB::table('water_temperature_readings')->get();
        return response()->json($getChart);
    }
}
