<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class getwaterdata extends Controller
{
    public function getwaterChart(){
        $getChart = DB::table('depth_readings')->get();
        return response()->json($getChart);
    }
}
