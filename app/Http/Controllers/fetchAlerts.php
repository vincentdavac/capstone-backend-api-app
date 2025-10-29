<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class fetchAlerts extends Controller
{
   public function getAlerts(){
     $getAllAlerts = DB::table('alerts')->get();
     return response()->json(['status' => 'success', 'data' => $getAllAlerts], 200, [], JSON_PRETTY_PRINT);
   }
}
