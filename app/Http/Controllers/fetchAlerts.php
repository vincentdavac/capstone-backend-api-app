<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\recent_alerts;
use App\Models\User;

class fetchAlerts extends Controller{
  public function getAlerts(Request $request){
    $user =  $request->query('barangay_id');
    $getAllAlerts = DB::table('recent_alerts')->join('buoys', 'recent_alerts.buoy_id', '=', 'buoys.id')
      ->where('buoys.barangay_id', $user)->where('recent_alerts.alert_level', '!=', 'White')
      ->select('recent_alerts.*')->orderBy('recent_alerts.id', 'desc')->get();
    return response()->json(['status' => 'success', 'data' => $getAllAlerts], 200, [], JSON_PRETTY_PRINT);
  }
}
