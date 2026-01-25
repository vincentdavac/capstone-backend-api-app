<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class alertNotif extends Controller{
    public function getAlertNotif(Request $request){
        $user = $request->user();
        $alerts = DB::table('alerts')->join('recent_alerts', 'alerts.alert_id', '=', 'recent_alerts.id')
            ->join('buoys', 'recent_alerts.buoy_id', '=', 'buoys.id')
            ->where('buoys.barangay_id', $user->barangay_id)->where('alerts.user_id', $user->id)
            ->select('alerts.*','recent_alerts.description', 'recent_alerts.alert_level','recent_alerts.sensor_type','recent_alerts.recorded_at as alert_time',)
            ->orderBy('recent_alerts.recorded_at', 'desc')
            ->first();
         return response()->json(['Success' =>true, 'data'=>$alerts], 200);
    }

    public function getCount(Request $request){
        $user =$request->user();
        $count = DB::table('alerts')->where('user_id', $user->id)->where('is_read', 0)->count();
        return response()->json(['Success' =>true, 'data'=>$count], 200);
    }
    public function isShown(Request $request){
        $user= $request->user();
        $isRead = 1;
        DB::table('alerts')->where('user_id', $user->id)->where('is_read', 0)->update(['is_read' => $isRead]);
        return response()->json(['Success' =>true,], 200);
    }
}
