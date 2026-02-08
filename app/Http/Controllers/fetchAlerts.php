<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\recent_alerts;
use App\Models\User;
use Carbon\Carbon;

class fetchAlerts extends Controller
{
  public function getAlerts(Request $request){
    $user =  $request->query('barangay_id');
    $recorded = Carbon::now('Asia/Manila')->subMinutes(2)->format('Y-m-d H:i:s');
    $getAllAlerts = DB::table('recent_alerts')->join('buoys', 'recent_alerts.buoy_id', '=', 'buoys.id')
      ->where('buoys.barangay_id', $user ?? 5)->where('recent_alerts.alert_level', '!=', 'White')
      ->where('recent_alerts.recorded_at', '>', $recorded)->select('recent_alerts.*')
      ->orderBy('recent_alerts.recorded_at', 'asc')->get();
    return response()->json(['status' => true, 'data' => $getAllAlerts], 200, [], JSON_PRETTY_PRINT);
  }
}
