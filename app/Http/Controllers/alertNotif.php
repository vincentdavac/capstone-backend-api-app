<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class alertNotif extends Controller{
    public function getAlertNotif(Request $request){
        $user = $request->user();
        $alerts = DB::table('alerts')->join('recent_alerts', 'alerts.alert_id', '=', 'recent_alerts.id')
            ->join('buoys', 'recent_alerts.buoy_id', '=', 'buoys.id')->where('buoys.barangay_id', $user->barangay_id??1)
            ->select('alerts.*') ->get();
         return response()->json(['Success' =>true, 'data'=>$alerts], 200);
    }
}
